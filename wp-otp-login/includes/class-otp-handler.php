<?php
/**
 * OTP Handler
 * Manages OTP code generation, storage, and verification
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_OTP_Login_OTP_Handler {
    
    private $otp_length;
    private $otp_expiry;
    private $max_attempts;
    
    public function __construct() {
        $this->otp_length = intval(get_option('wp_otp_login_otp_length', 6));
        $this->otp_expiry = intval(get_option('wp_otp_login_otp_expiry', 300)); // 5 minutes
        $this->max_attempts = intval(get_option('wp_otp_login_max_attempts', 3));
        
        // Add AJAX handlers
        add_action('wp_ajax_wp_otp_request_code', array($this, 'request_otp_code'));
        add_action('wp_ajax_nopriv_wp_otp_request_code', array($this, 'request_otp_code'));
        add_action('wp_ajax_wp_otp_verify_code', array($this, 'verify_otp_code'));
        add_action('wp_ajax_nopriv_wp_otp_verify_code', array($this, 'verify_otp_code'));
    }
    
    /**
     * Generate a random OTP code
     * 
     * @return string Generated OTP code
     */
    public function generate_otp() {
        $code = '';
        for ($i = 0; $i < $this->otp_length; $i++) {
            $code .= mt_rand(0, 9);
        }
        return $code;
    }
    
    /**
     * Store OTP code in database
     * 
     * @param string $phone_number Phone number
     * @param string $otp_code OTP code to store
     * @return bool True on success, false on failure
     */
    public function store_otp($phone_number, $otp_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'otp_codes';
        
        // Invalidate previous OTPs for this phone number
        $wpdb->update(
            $table_name,
            array('used' => 1),
            array('phone_number' => $phone_number, 'used' => 0),
            array('%d'),
            array('%s', '%d')
        );
        
        // Insert new OTP
        $expires_at = date('Y-m-d H:i:s', time() + $this->otp_expiry);
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'phone_number' => sanitize_text_field($phone_number),
                'otp_code' => $otp_code,
                'expires_at' => $expires_at,
                'used' => 0
            ),
            array('%s', '%s', '%s', '%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Verify OTP code
     * 
     * @param string $phone_number Phone number
     * @param string $otp_code OTP code to verify
     * @return array Verification result
     */
    public function verify_otp($phone_number, $otp_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'otp_codes';
        
        // Get the latest unused OTP for this phone number
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE phone_number = %s AND otp_code = %s AND used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1",
            $phone_number,
            $otp_code
        ));
        
        if (!$row) {
            // Check if OTP exists but is expired
            $expired_row = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE phone_number = %s AND otp_code = %s AND used = 0 ORDER BY created_at DESC LIMIT 1",
                $phone_number,
                $otp_code
            ));
            
            if ($expired_row) {
                return array(
                    'success' => false,
                    'message' => __('OTP code has expired', 'wp-otp-login'),
                    'expired' => true
                );
            }
            
            return array(
                'success' => false,
                'message' => __('Invalid OTP code', 'wp-otp-login')
            );
        }
        
        // Mark OTP as used
        $wpdb->update(
            $table_name,
            array('used' => 1),
            array('id' => $row->id),
            array('%d'),
            array('%d')
        );
        
        return array(
            'success' => true,
            'message' => __('OTP verified successfully', 'wp-otp-login')
        );
    }
    
    /**
     * Check rate limiting for phone number
     * 
     * @param string $phone_number Phone number to check
     * @return array Rate limit status
     */
    public function check_rate_limit($phone_number) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'otp_codes';
        
        // Check how many OTPs were sent in the last minute
        $one_minute_ago = date('Y-m-d H:i:s', time() - 60);
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE phone_number = %s AND created_at > %s",
            $phone_number,
            $one_minute_ago
        ));
        
        if ($count >= 2) {
            return array(
                'allowed' => false,
                'message' => __('Please wait before requesting another code', 'wp-otp-login'),
                'retry_after' => 60
            );
        }
        
        // Check how many OTPs were sent in the last hour
        $one_hour_ago = date('Y-m-d H:i:s', time() - 3600);
        $hourly_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE phone_number = %s AND created_at > %s",
            $phone_number,
            $one_hour_ago
        ));
        
        if ($hourly_count >= 10) {
            return array(
                'allowed' => false,
                'message' => __('Too many requests. Please try again later', 'wp-otp-login'),
                'retry_after' => 3600
            );
        }
        
        return array(
            'allowed' => true
        );
    }
    
    /**
     * Clean up expired OTP codes
     */
    public function cleanup_expired_otps() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'otp_codes';
        
        $wpdb->query("DELETE FROM $table_name WHERE expires_at < NOW()");
    }
    
    /**
     * AJAX handler for requesting OTP code
     */
    public function request_otp_code() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_otp_login_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'wp-otp-login')
            ));
        }
        
        $phone_number = isset($_POST['phone_number']) ? sanitize_text_field($_POST['phone_number']) : '';
        
        if (empty($phone_number)) {
            wp_send_json_error(array(
                'message' => __('Phone number is required', 'wp-otp-login')
            ));
        }
        
        // Initialize IPPanel API
        $ippanel = new WP_OTP_Login_IPPanel_API();
        
        // Validate phone number
        if (!$ippanel->validate_phone_number($phone_number)) {
            wp_send_json_error(array(
                'message' => __('Invalid phone number format', 'wp-otp-login')
            ));
        }
        
        // Normalize phone number
        $phone_number = $ippanel->normalize_phone_number($phone_number);
        
        // Check rate limiting
        $rate_limit = $this->check_rate_limit($phone_number);
        if (!$rate_limit['allowed']) {
            wp_send_json_error($rate_limit);
        }
        
        // Generate OTP
        $otp_code = $this->generate_otp();
        
        // Store OTP
        if (!$this->store_otp($phone_number, $otp_code)) {
            wp_send_json_error(array(
                'message' => __('Failed to store OTP code', 'wp-otp-login')
            ));
        }
        
        // Send OTP via SMS
        $pattern_code = get_option('wp_otp_login_pattern_code', '');
        
        if (!empty($pattern_code)) {
            $result = $ippanel->send_otp_with_pattern($phone_number, $otp_code, $pattern_code);
        } else {
            $result = $ippanel->send_otp($phone_number, $otp_code);
        }
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => __('OTP code sent successfully', 'wp-otp-login'),
                'expires_in' => $this->otp_expiry
            ));
        } else {
            wp_send_json_error(array(
                'message' => $result['message']
            ));
        }
    }
    
    /**
     * AJAX handler for verifying OTP code
     */
    public function verify_otp_code() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_otp_login_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'wp-otp-login')
            ));
        }
        
        $phone_number = isset($_POST['phone_number']) ? sanitize_text_field($_POST['phone_number']) : '';
        $otp_code = isset($_POST['otp_code']) ? sanitize_text_field($_POST['otp_code']) : '';
        
        if (empty($phone_number) || empty($otp_code)) {
            wp_send_json_error(array(
                'message' => __('Phone number and OTP code are required', 'wp-otp-login')
            ));
        }
        
        // Initialize IPPanel API
        $ippanel = new WP_OTP_Login_IPPanel_API();
        
        // Normalize phone number
        $phone_number = $ippanel->normalize_phone_number($phone_number);
        
        // Verify OTP
        $result = $this->verify_otp($phone_number, $otp_code);
        
        if ($result['success']) {
            // Create session token for user
            $session_token = bin2hex(random_bytes(32));
            set_transient('wp_otp_verified_' . md5($phone_number), $session_token, $this->otp_expiry);
            
            wp_send_json_success(array(
                'message' => $result['message'],
                'session_token' => $session_token
            ));
        } else {
            wp_send_json_error($result);
        }
    }
}

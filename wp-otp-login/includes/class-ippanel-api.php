<?php
/**
 * IPPanel API Handler
 * Handles communication with IPPanel SMS service according to official documentation
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_OTP_Login_IPPanel_API {
    
    private $api_key;
    private $sender_id;
    private $base_url = 'https://api.ippanel.com/api/v1/sends';
    
    public function __construct() {
        $this->api_key = trim(get_option('wp_otp_login_api_key', ''));
        $this->sender_id = trim(get_option('wp_otp_login_sender_id', ''));
    }
    
    /**
     * Send OTP via SMS using IPPanel API
     * 
     * @param string $phone_number Phone number to send OTP to
     * @param string $otp_code The OTP code to send
     * @return array Result array with success status and message
     */
    public function send_otp($phone_number, $otp_code) {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('API Key is not configured', 'wp-otp-login')
            );
        }
        
        if (empty($this->sender_id)) {
            return array(
                'success' => false,
                'message' => __('Sender ID is not configured', 'wp-otp-login')
            );
        }
        
        if (empty($phone_number)) {
            return array(
                'success' => false,
                'message' => __('Phone number is required', 'wp-otp-login')
            );
        }
        
        // Normalize phone number
        $phone_number = $this->normalize_phone_number($phone_number);
        
        // Validate phone number
        if (!$this->validate_phone_number($phone_number)) {
            return array(
                'success' => false,
                'message' => __('Invalid phone number format', 'wp-otp-login')
            );
        }
        
        // Get message template
        $template = get_option('wp_otp_login_message_template', 'کد تأیید شما: %code%');
        $message = str_replace('%code%', $otp_code, $template);
        
        // Prepare request body according to IPPanel documentation
        $request_body = array(
            'from' => $this->sender_id,
            'to' => array($phone_number),
            'text' => $message
        );
        
        // Make API request
        $response = wp_remote_post($this->base_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'AccessKey ' . $this->api_key
            ),
            'body' => wp_json_encode($request_body),
            'timeout' => 15,
            'sslverify' => true
        ));
        
        // Check for errors
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($status_code === 200 && isset($body['message_id'])) {
            // Log successful send
            $this->log_sms_send($phone_number, $body['message_id'], 'sent');
            
            return array(
                'success' => true,
                'message' => __('OTP sent successfully', 'wp-otp-login'),
                'message_id' => $body['message_id']
            );
        } else {
            $error_message = isset($body['error']) ? $body['error'] : __('Failed to send OTP', 'wp-otp-login');
            
            // Log failed send
            $this->log_sms_send($phone_number, '', 'failed', $error_message);
            
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
    }
    
    /**
     * Send OTP using pattern (if configured in IPPanel)
     * 
     * @param string $phone_number Phone number to send OTP to
     * @param string $otp_code The OTP code to send
     * @param string $pattern_code Pattern code from IPPanel
     * @return array Result array with success status and message
     */
    public function send_otp_with_pattern($phone_number, $otp_code, $pattern_code) {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('API Key is not configured', 'wp-otp-login')
            );
        }
        
        if (empty($this->sender_id)) {
            return array(
                'success' => false,
                'message' => __('Sender ID is not configured', 'wp-otp-login')
            );
        }
        
        // Normalize and validate phone number
        $phone_number = $this->normalize_phone_number($phone_number);
        if (!$this->validate_phone_number($phone_number)) {
            return array(
                'success' => false,
                'message' => __('Invalid phone number format', 'wp-otp-login')
            );
        }
        
        // Prepare request body for pattern sending
        $request_body = array(
            'from' => $this->sender_id,
            'to' => array($phone_number),
            'template_id' => $pattern_code,
            'parameter_data' => array(
                'code' => $otp_code
            )
        );
        
        // Make API request
        $response = wp_remote_post($this->base_url . '/pattern', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'AccessKey ' . $this->api_key
            ),
            'body' => wp_json_encode($request_body),
            'timeout' => 15,
            'sslverify' => true
        ));
        
        // Check for errors
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($status_code === 200 && isset($body['message_id'])) {
            $this->log_sms_send($phone_number, $body['message_id'], 'sent');
            
            return array(
                'success' => true,
                'message' => __('OTP sent successfully', 'wp-otp-login'),
                'message_id' => $body['message_id']
            );
        } else {
            $error_message = isset($body['error']) ? $body['error'] : __('Failed to send OTP', 'wp-otp-login');
            $this->log_sms_send($phone_number, '', 'failed', $error_message);
            
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
    }
    
    /**
     * Check IPPanel credit balance
     * 
     * @return array|false Balance information or false on failure
     */
    public function check_balance() {
        if (empty($this->api_key)) {
            return false;
        }
        
        $response = wp_remote_get('https://api.ippanel.com/api/v1/accounts/credit', array(
            'headers' => array(
                'Authorization' => 'AccessKey ' . $this->api_key
            ),
            'timeout' => 10,
            'sslverify' => true
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($status_code === 200 && isset($body['credit'])) {
            return array(
                'credit' => floatval($body['credit']),
                'currency' => isset($body['currency']) ? $body['currency'] : 'IRR'
            );
        }
        
        return false;
    }
    
    /**
     * Log SMS send attempt
     * 
     * @param string $phone_number Phone number
     * @param string $message_id IPPanel message ID
     * @param string $status Status (sent/failed)
     * @param string $error_message Error message if failed
     */
    private function log_sms_send($phone_number, $message_id, $status = 'sent', $error_message = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'otp_logs';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            return;
        }
        
        $wpdb->insert(
            $table_name,
            array(
                'phone_number' => sanitize_text_field($phone_number),
                'message_id' => sanitize_text_field($message_id),
                'status' => sanitize_text_field($status),
                'error_message' => sanitize_textarea_field($error_message),
                'sent_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Validate phone number format
     * 
     * @param string $phone_number Phone number to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_phone_number($phone_number) {
        // Remove spaces and dashes
        $phone_number = preg_replace('/[\s\-]/', '', $phone_number);
        
        // Check if it's a valid Iranian phone number
        if (preg_match('/^(\+98|98|0)?9\d{9}$/', $phone_number)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Normalize phone number to standard format
     * 
     * @param string $phone_number Phone number to normalize
     * @return string Normalized phone number
     */
    public function normalize_phone_number($phone_number) {
        // Remove spaces and dashes
        $phone_number = preg_replace('/[\s\-]/', '', $phone_number);
        
        // Convert to international format with + prefix
        if (preg_match('/^09\d{9}$/', $phone_number)) {
            $phone_number = '+98' . substr($phone_number, 1);
        } elseif (preg_match('/^989\d{9}$/', $phone_number)) {
            $phone_number = '+98' . substr($phone_number, 2);
        } elseif (preg_match('/^9\d{9}$/', $phone_number)) {
            $phone_number = '+98' . $phone_number;
        }
        
        return $phone_number;
    }
}

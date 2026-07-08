<?php
/**
 * User Authentication Handler
 * Manages user login, registration, and session management
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_OTP_Login_User_Authentication {
    
    public function __construct() {
        // Add custom authentication method
        add_filter('authenticate', array($this, 'authenticate_with_otp'), 30, 3);
        
        // Handle user registration with phone number
        add_action('wp_ajax_wp_otp_register_user', array($this, 'register_user'));
        add_action('wp_ajax_nopriv_wp_otp_register_user', array($this, 'register_user'));
        
        // Handle user login with OTP
        add_action('wp_ajax_wp_otp_login_user', array($this, 'login_user'));
        add_action('wp_ajax_nopriv_wp_otp_login_user', array($this, 'login_user'));
        
        // Add phone number field to user profile
        add_action('show_user_profile', array($this, 'add_phone_number_field'));
        add_action('edit_user_profile', array($this, 'add_phone_number_field'));
        add_action('personal_options_update', array($this, 'save_phone_number_field'));
        add_action('edit_user_profile_update', array($this, 'save_phone_number_field'));
        
        // Clean up expired sessions
        add_action('init', array($this, 'cleanup_expired_sessions'));
    }
    
    /**
     * Authenticate user with OTP
     * 
     * @param WP_User|null $user WP_User object or null
     * @param string $username Username
     * @param string $password Password (OTP code in this case)
     * @return WP_User|WP_Error
     */
    public function authenticate_with_otp($user, $username, $password) {
        // Only proceed if we're using OTP authentication
        if (!isset($_POST['wp_otp_auth']) || !$_POST['wp_otp_auth']) {
            return $user;
        }
        
        if ($user instanceof WP_User) {
            return $user;
        }
        
        // Get phone number from username field
        $phone_number = sanitize_text_field($username);
        
        if (empty($phone_number)) {
            return new WP_Error('empty_phone', __('Phone number is required', 'wp-otp-login'));
        }
        
        // Initialize IPPanel API
        $ippanel = new WP_OTP_Login_IPPanel_API();
        
        // Normalize phone number
        $phone_number = $ippanel->normalize_phone_number($phone_number);
        
        // Verify OTP
        $otp_handler = new WP_OTP_Login_OTP_Handler();
        $result = $otp_handler->verify_otp($phone_number, $password);
        
        if (!$result['success']) {
            return new WP_Error('invalid_otp', $result['message']);
        }
        
        // Check if user exists
        $users = get_users(array(
            'meta_key' => 'phone_number',
            'meta_value' => $phone_number,
            'number' => 1
        ));
        
        if (!empty($users)) {
            return $users[0];
        }
        
        // Auto-register user if enabled
        if (get_option('wp_otp_login_auto_register', '1') === '1') {
            $user_id = $this->auto_register_user($phone_number);
            if ($user_id) {
                return new WP_User($user_id);
            }
        }
        
        return new WP_Error('user_not_found', __('User not found', 'wp-otp-login'));
    }
    
    /**
     * Auto-register user with phone number
     * 
     * @param string $phone_number Phone number
     * @return int|false User ID on success, false on failure
     */
    private function auto_register_user($phone_number) {
        // Generate username from phone number
        $username = 'user_' . preg_replace('/[^0-9]/', '', $phone_number);
        
        // Check if username already exists
        if (username_exists($username)) {
            $username = $username . '_' . time();
        }
        
        // Generate random password
        $password = wp_generate_password(12, true, true);
        
        // Create user
        $user_id = wp_create_user($username, $password, $username . '@example.com');
        
        if (is_wp_error($user_id)) {
            return false;
        }
        
        // Save phone number
        update_user_meta($user_id, 'phone_number', $phone_number);
        
        // Set user role
        $default_role = get_option('wp_otp_login_default_role', 'subscriber');
        $user = new WP_User($user_id);
        $user->set_role($default_role);
        
        // Send welcome notification
        $this->send_welcome_notification($user_id, $phone_number);
        
        return $user_id;
    }
    
    /**
     * Send welcome notification to new user
     * 
     * @param int $user_id User ID
     * @param string $phone_number Phone number
     */
    private function send_welcome_notification($user_id, $phone_number) {
        $user = new WP_User($user_id);
        
        // Send email notification
        $to = $user->user_email;
        $subject = __('Welcome to our website', 'wp-otp-login');
        $message = sprintf(
            __('Hello,\n\nYour account has been created successfully.\n\nPhone Number: %s\nUsername: %s\n\nThank you!', 'wp-otp-login'),
            $phone_number,
            $user->user_login
        );
        
        wp_mail($to, $subject, $message);
    }
    
    /**
     * AJAX handler for user registration
     */
    public function register_user() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_otp_login_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'wp-otp-login')
            ));
        }
        
        $phone_number = isset($_POST['phone_number']) ? sanitize_text_field($_POST['phone_number']) : '';
        $otp_code = isset($_POST['otp_code']) ? sanitize_text_field($_POST['otp_code']) : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        
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
        $otp_handler = new WP_OTP_Login_OTP_Handler();
        $result = $otp_handler->verify_otp($phone_number, $otp_code);
        
        if (!$result['success']) {
            wp_send_json_error($result);
        }
        
        // Check if user already exists
        $users = get_users(array(
            'meta_key' => 'phone_number',
            'meta_value' => $phone_number,
            'number' => 1
        ));
        
        if (!empty($users)) {
            wp_send_json_error(array(
                'message' => __('User already exists with this phone number', 'wp-otp-login')
            ));
        }
        
        // Register user
        $user_id = $this->auto_register_user($phone_number);
        
        if (!$user_id) {
            wp_send_json_error(array(
                'message' => __('Failed to register user', 'wp-otp-login')
            ));
        }
        
        // Update user meta with additional information
        if (!empty($first_name)) {
            update_user_meta($user_id, 'first_name', $first_name);
        }
        
        if (!empty($last_name)) {
            update_user_meta($user_id, 'last_name', $last_name);
        }
        
        // Auto login user
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        
        wp_send_json_success(array(
            'message' => __('Registration successful', 'wp-otp-login'),
            'user_id' => $user_id,
            'redirect_url' => home_url()
        ));
    }
    
    /**
     * AJAX handler for user login
     */
    public function login_user() {
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
        $otp_handler = new WP_OTP_Login_OTP_Handler();
        $result = $otp_handler->verify_otp($phone_number, $otp_code);
        
        if (!$result['success']) {
            wp_send_json_error($result);
        }
        
        // Find user by phone number
        $users = get_users(array(
            'meta_key' => 'phone_number',
            'meta_value' => $phone_number,
            'number' => 1
        ));
        
        if (empty($users)) {
            wp_send_json_error(array(
                'message' => __('User not found', 'wp-otp-login')
            ));
        }
        
        $user = $users[0];
        
        // Login user
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_send_json_success(array(
            'message' => __('Login successful', 'wp-otp-login'),
            'user_id' => $user->ID,
            'redirect_url' => home_url()
        ));
    }
    
    /**
     * Add phone number field to user profile
     * 
     * @param WP_User $user User object
     */
    public function add_phone_number_field($user) {
        ?>
        <h3><?php _e('Phone Number', 'wp-otp-login'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="phone_number"><?php _e('Phone Number', 'wp-otp-login'); ?></label></th>
                <td>
                    <input type="text" name="phone_number" id="phone_number" value="<?php echo esc_attr(get_the_author_meta('phone_number', $user->ID)); ?>" class="regular-text" />
                    <p class="description"><?php _e('Enter your phone number with country code (e.g., +989123456789)', 'wp-otp-login'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save phone number field
     * 
     * @param int $user_id User ID
     */
    public function save_phone_number_field($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        
        if (isset($_POST['phone_number'])) {
            $ippanel = new WP_OTP_Login_IPPanel_API();
            $phone_number = $ippanel->normalize_phone_number(sanitize_text_field($_POST['phone_number']));
            
            if ($ippanel->validate_phone_number($phone_number)) {
                update_user_meta($user_id, 'phone_number', $phone_number);
            }
        }
    }
    
    /**
     * Clean up expired sessions
     */
    public function cleanup_expired_sessions() {
        global $wpdb;
        
        // Clean up old transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_otp_verified_%' AND option_value < UNIX_TIMESTAMP()");
    }
    
    /**
     * Check if user is verified via OTP
     * 
     * @param string $phone_number Phone number
     * @param string $session_token Session token
     * @return bool True if verified, false otherwise
     */
    public function is_user_verified($phone_number, $session_token) {
        $stored_token = get_transient('wp_otp_verified_' . md5($phone_number));
        return $stored_token === $session_token;
    }
}

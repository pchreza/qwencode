<?php
/**
 * Plugin Name: WP OTP Login with IPPanel
 * Plugin URI: https://example.com/wp-otp-login
 * Description: افزونه ورود و ثبت‌نام با پیامک و OTP از طریق IPPanel همراه با ویجت‌های المنتور
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: wp-otp-login
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WP_OTP_LOGIN_VERSION', '1.0.0');
define('WP_OTP_LOGIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_OTP_LOGIN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-ippanel-api.php';
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-otp-handler.php';
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-user-authentication.php';
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-elementor-widgets.php';

// Initialize plugin components
function wp_otp_login_init() {
    new WP_OTP_Login_IPPanel_API();
    new WP_OTP_Login_OTP_Handler();
    new WP_OTP_Login_User_Authentication();
    new WP_OTP_Login_Admin_Settings();
    new WP_OTP_Login_Elementor_Widgets();
}
add_action('plugins_loaded', 'wp_otp_login_init');

// Activation hook
register_activation_hook(__FILE__, 'wp_otp_login_activate');
function wp_otp_login_activate() {
    // Create necessary database tables
    global $wpdb;
    $table_name = $wpdb->prefix . 'otp_codes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        phone_number varchar(20) NOT NULL,
        otp_code varchar(10) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        expires_at datetime NOT NULL,
        used tinyint(1) DEFAULT 0,
        PRIMARY KEY (id),
        KEY phone_number (phone_number),
        KEY expires_at (expires_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Set default options
    add_option('wp_otp_login_api_key', '');
    add_option('wp_otp_login_sender_id', '');
    add_option('wp_otp_login_otp_length', '6');
    add_option('wp_otp_login_otp_expiry', '300');
    add_option('wp_otp_login_max_attempts', '3');
    add_option('wp_otp_login_message_template', 'کد تأیید شما: %code%');
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_otp_login_deactivate');
function wp_otp_login_deactivate() {
    // Cleanup if needed
}

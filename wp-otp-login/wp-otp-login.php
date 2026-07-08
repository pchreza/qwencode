<?php
/**
 * Plugin Name: WP OTP Login with IPPanel
 * Plugin URI: https://example.com/wp-otp-login
 * Description: افزونه ورود و ثبت‌نام با پیامک و OTP از طریق IPPanel همراه با ویجت‌های المنتور
 * Version: 1.0.1
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: wp-otp-login
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WP_OTP_LOGIN_VERSION', '1.0.1');
define('WP_OTP_LOGIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_OTP_LOGIN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_OTP_LOGIN_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-ippanel-api.php';
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-otp-handler.php';
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-user-authentication.php';
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once WP_OTP_LOGIN_PLUGIN_DIR . 'includes/class-elementor-widgets.php';

// Initialize plugin components
function wp_otp_login_init() {
    // Load text domain
    load_plugin_textdomain('wp-otp-login', false, dirname(WP_OTP_LOGIN_PLUGIN_BASENAME) . '/languages');
    
    new WP_OTP_Login_IPPanel_API();
    new WP_OTP_Login_OTP_Handler();
    new WP_OTP_Login_User_Authentication();
    new WP_OTP_Login_Admin_Settings();
    new WP_OTP_Login_Elementor_Widgets();
}
add_action('plugins_loaded', 'wp_otp_login_init');

// Register activation hook
register_activation_hook(__FILE__, 'wp_otp_login_activate');
function wp_otp_login_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Create OTP codes table
    $otp_table = $wpdb->prefix . 'otp_codes';
    $sql_otp = "CREATE TABLE $otp_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        phone_number varchar(20) NOT NULL,
        otp_code varchar(10) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        expires_at datetime NOT NULL,
        used tinyint(1) DEFAULT 0,
        attempt_count int(11) DEFAULT 0,
        ip_address varchar(45) DEFAULT '',
        KEY phone_number (phone_number),
        KEY expires_at (expires_at),
        KEY created_at (created_at),
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    // Create SMS logs table
    $logs_table = $wpdb->prefix . 'otp_logs';
    $sql_logs = "CREATE TABLE $logs_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        phone_number varchar(20) NOT NULL,
        message_id varchar(100) DEFAULT '',
        status varchar(20) DEFAULT 'sent',
        sent_at datetime DEFAULT CURRENT_TIMESTAMP,
        error_message text,
        KEY phone_number (phone_number),
        KEY sent_at (sent_at),
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_otp);
    dbDelta($sql_logs);
    
    // Set default options with proper sanitization
    add_option('wp_otp_login_api_key', '');
    add_option('wp_otp_login_sender_id', '');
    add_option('wp_otp_login_pattern_code', '');
    add_option('wp_otp_login_otp_length', '6');
    add_option('wp_otp_login_otp_expiry', '300');
    add_option('wp_otp_login_max_attempts', '3');
    add_option('wp_otp_login_rate_limit_minutes', '2');
    add_option('wp_otp_login_rate_limit_hourly', '10');
    add_option('wp_otp_login_message_template', 'کد تأیید شما: %code%');
    add_option('wp_otp_login_auto_register', '1');
    add_option('wp_otp_login_default_role', 'subscriber');
    
    // Schedule cleanup cron job
    if (!wp_next_scheduled('wp_otp_login_cleanup_event')) {
        wp_schedule_event(time(), 'hourly', 'wp_otp_login_cleanup_event');
    }
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'wp_otp_login_deactivate');
function wp_otp_login_deactivate() {
    // Clear scheduled hooks
    wp_clear_scheduled_hook('wp_otp_login_cleanup_event');
}

// Cleanup expired OTP codes on cron
add_action('wp_otp_login_cleanup_event', 'wp_otp_login_cleanup_expired');
function wp_otp_login_cleanup_expired() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'otp_codes';
    $wpdb->query("DELETE FROM $table_name WHERE expires_at < NOW()");
}

// Enqueue frontend assets
add_action('wp_enqueue_scripts', 'wp_otp_login_enqueue_assets');
function wp_otp_login_enqueue_assets() {
    wp_enqueue_style(
        'wp-otp-login-frontend',
        WP_OTP_LOGIN_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        WP_OTP_LOGIN_VERSION
    );
    
    wp_enqueue_script(
        'wp-otp-login-frontend',
        WP_OTP_LOGIN_PLUGIN_URL . 'assets/js/frontend.js',
        array('jquery'),
        WP_OTP_LOGIN_VERSION,
        true
    );
}

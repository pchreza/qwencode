<?php
/**
 * Elementor Widgets
 * Provides custom Elementor widgets for OTP login forms
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_OTP_Login_Elementor_Widgets {
    
    public function __construct() {
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_widget_categories'));
    }
    
    /**
     * Add custom widget categories
     */
    public function add_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'wp-otp-login',
            array(
                'title' => __('OTP Login', 'wp-otp-login'),
                'icon' => 'fa fa-plug',
            )
        );
    }
    
    /**
     * Register Elementor widgets
     */
    public function register_widgets($widgets_manager) {
        require_once WP_OTP_LOGIN_PLUGIN_DIR . 'widgets/widget-otp-login-form.php';
        require_once WP_OTP_LOGIN_PLUGIN_DIR . 'widgets/widget-otp-register-form.php';
        require_once WP_OTP_LOGIN_PLUGIN_DIR . 'widgets/widget-otp-input.php';
        require_once WP_OTP_LOGIN_PLUGIN_DIR . 'widgets/widget-user-info.php';
        
        $widgets_manager->register(new WP_OTP_Login_Login_Form_Widget());
        $widgets_manager->register(new WP_OTP_Login_Register_Form_Widget());
        $widgets_manager->register(new WP_OTP_Login_Input_Widget());
        $widgets_manager->register(new WP_OTP_Login_User_Info_Widget());
    }
}

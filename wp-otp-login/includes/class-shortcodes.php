<?php
/**
 * Shortcodes for OTP Login Forms
 * Provides shortcodes to display OTP forms on any page/post
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_OTP_Login_Shortcodes {
    
    public function __construct() {
        add_shortcode('otp_login_form', array($this, 'login_form_shortcode'));
        add_shortcode('otp_register_form', array($this, 'register_form_shortcode'));
        add_shortcode('otp_user_info', array($this, 'user_info_shortcode'));
    }
    
    /**
     * Login form shortcode
     * Usage: [otp_login_form redirect_url="https://example.com/dashboard"]
     */
    public function login_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'redirect_url' => home_url(),
            'title' => __('ورود با شماره موبایل', 'wp-otp-login'),
            'show_title' => 'true',
            'phone_placeholder' => '09xxxxxxxxx',
            'send_button_text' => __('ارسال کد تأیید', 'wp-otp-login'),
            'verify_button_text' => __('ورود به سایت', 'wp-otp-login'),
        ), $atts, 'otp_login_form');
        
        // Enqueue scripts and styles
        wp_enqueue_style('wp-otp-login-frontend');
        wp_enqueue_script('wp-otp-login-frontend');
        
        $widget_id = 'shortcode-' . uniqid();
        $redirect_url = esc_url($atts['redirect_url']);
        
        ob_start();
        ?>
        <div class="wp-otp-login-form" data-widget-type="login">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="wp-otp-form-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <form id="wp-otp-login-form-<?php echo esc_attr($widget_id); ?>">
                <!-- Phone Number Step -->
                <div class="wp-otp-step wp-otp-step-phone">
                    <div class="wp-otp-input-group">
                        <input type="tel" 
                               class="wp-otp-input-field" 
                               id="wp-otp-phone-<?php echo esc_attr($widget_id); ?>" 
                               name="phone_number" 
                               placeholder="<?php echo esc_attr($atts['phone_placeholder']); ?>"
                               required
                               pattern="^(\+98|98|0)?9\d{9}$">
                        <button type="button" 
                                class="wp-otp-button wp-otp-send-code-btn"
                                data-widget-id="<?php echo esc_attr($widget_id); ?>">
                            <?php echo esc_html($atts['send_button_text']); ?>
                        </button>
                    </div>
                    <div class="wp-otp-message"></div>
                </div>
                
                <!-- OTP Verification Step -->
                <div class="wp-otp-step wp-otp-step-verify" style="display: none;">
                    <div class="wp-otp-input-group">
                        <input type="text" 
                               class="wp-otp-input-field wp-otp-code-input" 
                               id="wp-otp-code-<?php echo esc_attr($widget_id); ?>" 
                               name="otp_code" 
                               placeholder="______"
                               maxlength="6"
                               required>
                        <button type="submit" 
                                class="wp-otp-button wp-otp-verify-btn"
                                data-widget-id="<?php echo esc_attr($widget_id); ?>"
                                data-redirect-url="<?php echo $redirect_url; ?>">
                            <?php echo esc_html($atts['verify_button_text']); ?>
                        </button>
                    </div>
                    <div class="wp-otp-message"></div>
                    <div class="wp-otp-resend">
                        <span class="wp-otp-timer"></span>
                        <a href="#" class="wp-otp-resend-link" style="display: none;"><?php _e('ارسال مجدد کد', 'wp-otp-login'); ?></a>
                    </div>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Register form shortcode
     * Usage: [otp_register_form redirect_url="https://example.com/profile"]
     */
    public function register_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'redirect_url' => home_url(),
            'title' => __('ثبت‌نام با شماره موبایل', 'wp-otp-login'),
            'show_title' => 'true',
            'phone_placeholder' => '09xxxxxxxxx',
            'firstname_placeholder' => __('نام', 'wp-otp-login'),
            'lastname_placeholder' => __('نام خانوادگی', 'wp-otp-login'),
            'send_button_text' => __('ارسال کد تأیید', 'wp-otp-login'),
            'verify_button_text' => __('ثبت‌نام', 'wp-otp-login'),
        ), $atts, 'otp_register_form');
        
        // Enqueue scripts and styles
        wp_enqueue_style('wp-otp-login-frontend');
        wp_enqueue_script('wp-otp-login-frontend');
        
        $widget_id = 'shortcode-' . uniqid();
        $redirect_url = esc_url($atts['redirect_url']);
        
        ob_start();
        ?>
        <div class="wp-otp-register-form" data-widget-type="register">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="wp-otp-form-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <form id="wp-otp-register-form-<?php echo esc_attr($widget_id); ?>">
                <!-- Name Fields (Optional) -->
                <div class="wp-otp-name-fields">
                    <div class="wp-otp-input-group">
                        <input type="text" 
                               class="wp-otp-input-field" 
                               id="wp-otp-firstname-<?php echo esc_attr($widget_id); ?>" 
                               name="first_name" 
                               placeholder="<?php echo esc_attr($atts['firstname_placeholder']); ?>">
                        <input type="text" 
                               class="wp-otp-input-field" 
                               id="wp-otp-lastname-<?php echo esc_attr($widget_id); ?>" 
                               name="last_name" 
                               placeholder="<?php echo esc_attr($atts['lastname_placeholder']); ?>">
                    </div>
                </div>
                
                <!-- Phone Number Step -->
                <div class="wp-otp-step wp-otp-step-phone">
                    <div class="wp-otp-input-group">
                        <input type="tel" 
                               class="wp-otp-input-field" 
                               id="wp-otp-phone-<?php echo esc_attr($widget_id); ?>" 
                               name="phone_number" 
                               placeholder="<?php echo esc_attr($atts['phone_placeholder']); ?>"
                               required
                               pattern="^(\+98|98|0)?9\d{9}$">
                        <button type="button" 
                                class="wp-otp-button wp-otp-send-code-btn"
                                data-widget-id="<?php echo esc_attr($widget_id); ?>">
                            <?php echo esc_html($atts['send_button_text']); ?>
                        </button>
                    </div>
                    <div class="wp-otp-message"></div>
                </div>
                
                <!-- OTP Verification Step -->
                <div class="wp-otp-step wp-otp-step-verify" style="display: none;">
                    <div class="wp-otp-input-group">
                        <input type="text" 
                               class="wp-otp-input-field wp-otp-code-input" 
                               id="wp-otp-code-<?php echo esc_attr($widget_id); ?>" 
                               name="otp_code" 
                               placeholder="______"
                               maxlength="6"
                               required>
                        <button type="submit" 
                                class="wp-otp-button wp-otp-verify-btn"
                                data-widget-id="<?php echo esc_attr($widget_id); ?>"
                                data-redirect-url="<?php echo $redirect_url; ?>">
                            <?php echo esc_html($atts['verify_button_text']); ?>
                        </button>
                    </div>
                    <div class="wp-otp-message"></div>
                    <div class="wp-otp-resend">
                        <span class="wp-otp-timer"></span>
                        <a href="#" class="wp-otp-resend-link" style="display: none;"><?php _e('ارسال مجدد کد', 'wp-otp-login'); ?></a>
                    </div>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * User info shortcode
     * Usage: [otp_user_info show_avatar="true" logout_url="/logout"]
     */
    public function user_info_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_avatar' => 'true',
            'avatar_size' => '80',
            'logout_url' => wp_logout_url(home_url()),
            'logged_in_text' => __('خوش آمدید', 'wp-otp-login'),
            'logged_out_text' => __('لطفاً وارد شوید', 'wp-otp-login'),
        ), $atts, 'otp_user_info');
        
        // Enqueue styles
        wp_enqueue_style('wp-otp-login-frontend');
        
        ob_start();
        
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $phone = get_user_meta($current_user->ID, 'phone_number', true);
            ?>
            <div class="wp-otp-user-info">
                <?php if ($atts['show_avatar'] === 'true'): ?>
                    <div class="wp-otp-user-avatar">
                        <?php echo get_avatar($current_user->ID, intval($atts['avatar_size'])); ?>
                    </div>
                <?php endif; ?>
                
                <div class="wp-otp-user-name">
                    <?php echo esc_html($atts['logged_in_text'] . '، ' . $current_user->display_name); ?>
                </div>
                
                <?php if ($phone): ?>
                    <div class="wp-otp-user-phone"><?php echo esc_html($phone); ?></div>
                <?php endif; ?>
                
                <a href="<?php echo esc_url($atts['logout_url']); ?>" class="wp-otp-logout-button">
                    <?php _e('خروج', 'wp-otp-login'); ?>
                </a>
            </div>
            <?php
        } else {
            ?>
            <div class="wp-otp-not-logged-in">
                <?php echo esc_html($atts['logged_out_text']); ?>
            </div>
            <?php
        }
        
        return ob_get_clean();
    }
}

// Initialize shortcodes
new WP_OTP_Login_Shortcodes();

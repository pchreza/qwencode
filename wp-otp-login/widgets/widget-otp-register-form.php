<?php
/**
 * OTP Register Form Widget for Elementor
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_OTP_Login_Register_Form_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'wp_otp_register_form';
    }
    
    public function get_title() {
        return __('OTP Register Form', 'wp-otp-login');
    }
    
    public function get_icon() {
        return 'eicon-form-horizontal';
    }
    
    public function get_categories() {
        return ['wp-otp-login'];
    }
    
    public function get_keywords() {
        return ['register', 'signup', 'otp', 'sms', 'authentication'];
    }
    
    protected function register_controls() {
        // Content Tab - Form Settings
        $this->start_controls_section(
            'section_form_settings',
            [
                'label' => __('Form Settings', 'wp-otp-login'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'wp-otp-login'),
                'label_off' => __('No', 'wp-otp-login'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'form_title',
            [
                'label' => __('Form Title', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('ثبت‌نام با شماره موبایل', 'wp-otp-login'),
                'placeholder' => __('Enter title', 'wp-otp-login'),
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'phone_placeholder',
            [
                'label' => __('Phone Number Placeholder', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('09xxxxxxxxx', 'wp-otp-login'),
            ]
        );
        
        $this->add_control(
            'show_name_fields',
            [
                'label' => __('Show Name Fields', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'wp-otp-login'),
                'label_off' => __('No', 'wp-otp-login'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'first_name_placeholder',
            [
                'label' => __('First Name Placeholder', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('نام', 'wp-otp-login'),
                'condition' => [
                    'show_name_fields' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'last_name_placeholder',
            [
                'label' => __('Last Name Placeholder', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('نام خانوادگی', 'wp-otp-login'),
                'condition' => [
                    'show_name_fields' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'send_code_button_text',
            [
                'label' => __('Send Code Button Text', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('ارسال کد تأیید', 'wp-otp-login'),
            ]
        );
        
        $this->add_control(
            'register_button_text',
            [
                'label' => __('Register Button Text', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('ثبت‌نام', 'wp-otp-login'),
            ]
        );
        
        $this->add_control(
            'redirect_after_register',
            [
                'label' => __('Redirect After Registration', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'wp-otp-login'),
                'default' => [
                    'url' => home_url(),
                    'is_external' => false,
                    'nofollow' => false,
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Tab - Similar to login form
        $this->start_controls_section(
            'section_form_style',
            [
                'label' => __('Form Style', 'wp-otp-login'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'form_background_color',
            [
                'label' => __('Background Color', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-register-form' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'form_border',
                'label' => __('Border', 'wp-otp-login'),
                'selector' => '{{WRAPPER}} .wp-otp-register-form',
            ]
        );
        
        $this->add_control(
            'form_border_radius',
            [
                'label' => __('Border Radius', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-register-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Input Fields Style
        $this->start_controls_section(
            'section_input_style',
            [
                'label' => __('Input Fields', 'wp-otp-login'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'input_background_color',
            [
                'label' => __('Background Color', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-input-field' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'input_text_color',
            [
                'label' => __('Text Color', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-input-field' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'selector' => '{{WRAPPER}} .wp-otp-input-field',
            ]
        );
        
        $this->end_controls_section();
        
        // Button Style
        $this->start_controls_section(
            'section_button_style',
            [
                'label' => __('Buttons', 'wp-otp-login'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'button_background_color',
            [
                'label' => __('Background Color', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-button' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-button' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .wp-otp-button',
            ]
        );
        
        $this->add_control(
            'button_hover_background_color',
            [
                'label' => __('Hover Background Color', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-button:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        wp_enqueue_script(
            'wp-otp-login-frontend',
            WP_OTP_LOGIN_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            WP_OTP_LOGIN_VERSION,
            true
        );
        
        wp_localize_script('wp-otp-login-frontend', 'wpOtpLogin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_otp_login_nonce'),
            'messages' => [
                'invalidPhone' => __('شماره موبایل نامعتبر است', 'wp-otp-login'),
                'codeSent' => __('کد تأیید ارسال شد', 'wp-otp-login'),
                'registerSuccess' => __('ثبت‌نام موفقیت‌آمیز بود', 'wp-otp-login'),
                'error' => __('خطایی رخ داد', 'wp-otp-login'),
            ],
        ]);
        
        ?>
        <div class="wp-otp-register-form" data-widget-type="register">
            <?php if ($settings['show_title'] === 'yes' && !empty($settings['form_title'])): ?>
                <h3 class="wp-otp-form-title"><?php echo esc_html($settings['form_title']); ?></h3>
            <?php endif; ?>
            
            <form id="wp-otp-register-form-<?php echo esc_attr($this->get_id()); ?>">
                <!-- Name Fields (Optional) -->
                <?php if ($settings['show_name_fields'] === 'yes'): ?>
                <div class="wp-otp-name-fields">
                    <div class="wp-otp-input-group">
                        <input type="text" 
                               class="wp-otp-input-field" 
                               id="wp-otp-firstname-<?php echo esc_attr($this->get_id()); ?>" 
                               name="first_name" 
                               placeholder="<?php echo esc_attr($settings['first_name_placeholder']); ?>">
                    </div>
                    <div class="wp-otp-input-group">
                        <input type="text" 
                               class="wp-otp-input-field" 
                               id="wp-otp-lastname-<?php echo esc_attr($this->get_id()); ?>" 
                               name="last_name" 
                               placeholder="<?php echo esc_attr($settings['last_name_placeholder']); ?>">
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Phone Number Step -->
                <div class="wp-otp-step wp-otp-step-phone">
                    <div class="wp-otp-input-group">
                        <input type="tel" 
                               class="wp-otp-input-field" 
                               id="wp-otp-phone-<?php echo esc_attr($this->get_id()); ?>" 
                               name="phone_number" 
                               placeholder="<?php echo esc_attr($settings['phone_placeholder']); ?>"
                               required
                               pattern="^(\+98|98|0)?9\d{9}$">
                        <button type="button" 
                                class="wp-otp-button wp-otp-send-code-btn"
                                data-widget-id="<?php echo esc_attr($this->get_id()); ?>">
                            <?php echo esc_html($settings['send_code_button_text']); ?>
                        </button>
                    </div>
                    <div class="wp-otp-message"></div>
                </div>
                
                <!-- OTP Verification Step -->
                <div class="wp-otp-step wp-otp-step-verify" style="display: none;">
                    <div class="wp-otp-input-group">
                        <input type="text" 
                               class="wp-otp-input-field wp-otp-code-input" 
                               id="wp-otp-code-<?php echo esc_attr($this->get_id()); ?>" 
                               name="otp_code" 
                               placeholder="______"
                               maxlength="6"
                               required>
                        <button type="submit" 
                                class="wp-otp-button wp-otp-verify-btn"
                                data-widget-id="<?php echo esc_attr($this->get_id()); ?>"
                                data-redirect-url="<?php echo esc_url($settings['redirect_after_register']['url']); ?>">
                            <?php echo esc_html($settings['register_button_text']); ?>
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
    }
}

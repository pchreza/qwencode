<?php
/**
 * User Info Widget for Elementor
 * Displays logged-in user information
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_OTP_Login_User_Info_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'wp_otp_user_info';
    }
    
    public function get_title() {
        return __('User Info Display', 'wp-otp-login');
    }
    
    public function get_icon() {
        return 'eicon-user';
    }
    
    public function get_categories() {
        return ['wp-otp-login'];
    }
    
    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Settings', 'wp-otp-login'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'show_avatar',
            [
                'label' => __('Show Avatar', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'wp-otp-login'),
                'label_off' => __('No', 'wp-otp-login'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'avatar_size',
            [
                'label' => __('Avatar Size', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 32,
                        'max' => 200,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 80,
                ],
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-user-avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_avatar' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'show_display_name',
            [
                'label' => __('Show Display Name', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'wp-otp-login'),
                'label_off' => __('No', 'wp-otp-login'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_phone',
            [
                'label' => __('Show Phone Number', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'wp-otp-login'),
                'label_off' => __('No', 'wp-otp-login'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_logout_button',
            [
                'label' => __('Show Logout Button', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'wp-otp-login'),
                'label_off' => __('No', 'wp-otp-login'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'logout_button_text',
            [
                'label' => __('Logout Button Text', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('خروج', 'wp-otp-login'),
                'condition' => [
                    'show_logout_button' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'logged_out_message',
            [
                'label' => __('Logged Out Message', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('شما وارد نشده‌اید', 'wp-otp-login'),
            ]
        );
        
        $this->end_controls_section();
        
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'wp-otp-login'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-user-info' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'name_typography',
                'selector' => '{{WRAPPER}} .wp-otp-user-name',
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user = wp_get_current_user();
            $phone = get_user_meta($user_id, 'phone_number', true);
            
            ?>
            <div class="wp-otp-user-info">
                <?php if ($settings['show_avatar'] === 'yes'): ?>
                    <div class="wp-otp-user-avatar">
                        <?php echo get_avatar($user_id, $settings['avatar_size']['size']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($settings['show_display_name'] === 'yes'): ?>
                    <div class="wp-otp-user-name">
                        <?php echo esc_html($user->display_name); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($settings['show_phone'] === 'yes' && !empty($phone)): ?>
                    <div class="wp-otp-user-phone">
                        <?php echo esc_html($phone); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($settings['show_logout_button'] === 'yes'): ?>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="wp-otp-logout-button">
                        <?php echo esc_html($settings['logout_button_text']); ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php
        } else {
            ?>
            <div class="wp-otp-user-info wp-otp-not-logged-in">
                <?php echo esc_html($settings['logged_out_message']); ?>
            </div>
            <?php
        }
    }
}

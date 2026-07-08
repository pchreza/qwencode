<?php
/**
 * OTP Input Widget for Elementor
 * Standalone OTP code input field
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_OTP_Login_Input_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'wp_otp_input';
    }
    
    public function get_title() {
        return __('OTP Input Field', 'wp-otp-login');
    }
    
    public function get_icon() {
        return 'eicon-text-field';
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
            'input_length',
            [
                'label' => __('Input Length', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 4,
                'max' => 10,
            ]
        );
        
        $this->add_control(
            'input_placeholder',
            [
                'label' => __('Placeholder', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '______',
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
            'input_background_color',
            [
                'label' => __('Background Color', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-code-input' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'input_text_color',
            [
                'label' => __('Text Color', 'wp-otp-login'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wp-otp-code-input' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'selector' => '{{WRAPPER}} .wp-otp-code-input',
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <input type="text" 
               class="wp-otp-input-field wp-otp-code-input" 
               placeholder="<?php echo esc_attr($settings['input_placeholder']); ?>"
               maxlength="<?php echo esc_attr($settings['input_length']); ?>">
        <?php
    }
}

<?php
/**
 * Admin Settings Page
 * Manages plugin settings in WordPress admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_OTP_Login_Admin_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_menu_page(
            __('OTP Login Settings', 'wp-otp-login'),
            __('OTP Login', 'wp-otp-login'),
            'manage_options',
            'wp-otp-login',
            array($this, 'render_settings_page'),
            'dashicons-smartphone',
            30
        );
        
        add_submenu_page(
            'wp-otp-login',
            __('General Settings', 'wp-otp-login'),
            __('General Settings', 'wp-otp-login'),
            'manage_options',
            'wp-otp-login',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'wp-otp-login',
            __('SMS Logs', 'wp-otp-login'),
            __('SMS Logs', 'wp-otp-login'),
            'manage_options',
            'wp-otp-login-logs',
            array($this, 'render_logs_page')
        );
        
        add_submenu_page(
            'wp-otp-login',
            __('Statistics', 'wp-otp-login'),
            __('Statistics', 'wp-otp-login'),
            'manage_options',
            'wp-otp-login-stats',
            array($this, 'render_statistics_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // IPPanel Settings Section
        add_settings_section(
            'wp_otp_login_ippanel_section',
            __('IPPanel Settings', 'wp-otp-login'),
            array($this, 'render_ippanel_section_description'),
            'wp-otp-login'
        );
        
        add_settings_field(
            'wp_otp_login_api_key',
            __('API Key', 'wp-otp-login'),
            array($this, 'render_api_key_field'),
            'wp-otp-login',
            'wp_otp_login_ippanel_section'
        );
        
        add_settings_field(
            'wp_otp_login_sender_id',
            __('Sender ID', 'wp-otp-login'),
            array($this, 'render_sender_id_field'),
            'wp-otp-login',
            'wp_otp_login_ippanel_section'
        );
        
        add_settings_field(
            'wp_otp_login_pattern_code',
            __('Pattern Code (Optional)', 'wp-otp-login'),
            array($this, 'render_pattern_code_field'),
            'wp-otp-login',
            'wp_otp_login_ippanel_section'
        );
        
        // OTP Settings Section
        add_settings_section(
            'wp_otp_login_otp_section',
            __('OTP Settings', 'wp-otp-login'),
            array($this, 'render_otp_section_description'),
            'wp-otp-login'
        );
        
        add_settings_field(
            'wp_otp_login_otp_length',
            __('OTP Length', 'wp-otp-login'),
            array($this, 'render_otp_length_field'),
            'wp-otp-login',
            'wp_otp_login_otp_section'
        );
        
        add_settings_field(
            'wp_otp_login_otp_expiry',
            __('OTP Expiry (seconds)', 'wp-otp-login'),
            array($this, 'render_otp_expiry_field'),
            'wp-otp-login',
            'wp_otp_login_otp_section'
        );
        
        add_settings_field(
            'wp_otp_login_max_attempts',
            __('Max Verification Attempts', 'wp-otp-login'),
            array($this, 'render_max_attempts_field'),
            'wp-otp-login',
            'wp_otp_login_otp_section'
        );
        
        add_settings_field(
            'wp_otp_login_message_template',
            __('Message Template', 'wp-otp-login'),
            array($this, 'render_message_template_field'),
            'wp-otp-login',
            'wp_otp_login_otp_section'
        );
        
        // User Settings Section
        add_settings_section(
            'wp_otp_login_user_section',
            __('User Settings', 'wp-otp-login'),
            array($this, 'render_user_section_description'),
            'wp-otp-login'
        );
        
        add_settings_field(
            'wp_otp_login_auto_register',
            __('Auto Register', 'wp-otp-login'),
            array($this, 'render_auto_register_field'),
            'wp-otp-login',
            'wp_otp_login_user_section'
        );
        
        add_settings_field(
            'wp_otp_login_default_role',
            __('Default User Role', 'wp-otp-login'),
            array($this, 'render_default_role_field'),
            'wp-otp-login',
            'wp_otp_login_user_section'
        );
        
        // Register settings
        register_setting('wp_otp_login_settings_group', 'wp_otp_login_api_key');
        register_setting('wp_otp_login_settings_group', 'wp_otp_login_sender_id');
        register_setting('wp_otp_login_settings_group', 'wp_otp_login_pattern_code');
        register_setting('wp_otp_login_settings_group', 'wp_otp_login_otp_length');
        register_setting('wp_otp_login_settings_group', 'wp_otp_login_otp_expiry');
        register_setting('wp_otp_login_settings_group', 'wp_otp_login_max_attempts');
        register_setting('wp_otp_login_settings_group', 'wp_otp_login_message_template');
        register_setting('wp_otp_login_settings_group', 'wp_otp_login_auto_register');
        register_setting('wp_otp_login_settings_group', 'wp_otp_login_default_role');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('WP OTP Login Settings', 'wp-otp-login'); ?></h1>
            
            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Settings saved successfully!', 'wp-otp-login'); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php settings_fields('wp_otp_login_settings_group'); ?>
                <?php do_settings_sections('wp-otp-login'); ?>
                <?php submit_button(__('Save Settings', 'wp-otp-login')); ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Credit Balance', 'wp-otp-login'); ?></h2>
            <div id="wp-otp-login-balance">
                <?php $this->render_credit_balance(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render logs page
     */
    public function render_logs_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'otp_logs';
        
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($paged - 1) * $per_page;
        
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY sent_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        ?>
        <div class="wrap">
            <h1><?php _e('SMS Logs', 'wp-otp-login'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'wp-otp-login'); ?></th>
                        <th><?php _e('Phone Number', 'wp-otp-login'); ?></th>
                        <th><?php _e('Message ID', 'wp-otp-login'); ?></th>
                        <th><?php _e('Sent At', 'wp-otp-login'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4"><?php _e('No logs found', 'wp-otp-login'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html($log->id); ?></td>
                                <td><?php echo esc_html($log->phone_number); ?></td>
                                <td><?php echo esc_html($log->message_id); ?></td>
                                <td><?php echo esc_html($log->sent_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php
            echo paginate_links(array(
                'total' => ceil($total_items / $per_page),
                'current' => $paged,
                'prev_text' => __('&laquo; Previous', 'wp-otp-login'),
                'next_text' => __('Next &raquo;', 'wp-otp-login')
            ));
            ?>
        </div>
        <?php
    }
    
    /**
     * Render statistics page
     */
    public function render_statistics_page() {
        global $wpdb;
        $otp_table = $wpdb->prefix . 'otp_codes';
        $logs_table = $wpdb->prefix . 'otp_logs';
        
        // Total OTPs sent today
        $today_start = date('Y-m-d 00:00:00');
        $total_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $logs_table WHERE sent_at >= %s",
            $today_start
        ));
        
        // Total successful verifications today
        $successful_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $otp_table WHERE used = 1 AND created_at >= %s",
            $today_start
        ));
        
        // Total users registered via OTP
        $total_users = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->users} u INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id WHERE um.meta_key = 'phone_number'"
        );
        
        // Success rate
        $success_rate = $total_today > 0 ? round(($successful_today / $total_today) * 100, 2) : 0;
        
        ?>
        <div class="wrap">
            <h1><?php _e('Statistics', 'wp-otp-login'); ?></h1>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0;"><?php _e('OTPs Sent Today', 'wp-otp-login'); ?></h3>
                    <p style="font-size: 36px; margin: 10px 0; color: #2271b1;"><?php echo number_format($total_today); ?></p>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0;"><?php _e('Successful Verifications', 'wp-otp-login'); ?></h3>
                    <p style="font-size: 36px; margin: 10px 0; color: #00a32a;"><?php echo number_format($successful_today); ?></p>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0;"><?php _e('Success Rate', 'wp-otp-login'); ?></h3>
                    <p style="font-size: 36px; margin: 10px 0; color: #dba617;"><?php echo $success_rate; ?>%</p>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0;"><?php _e('Total OTP Users', 'wp-otp-login'); ?></h3>
                    <p style="font-size: 36px; margin: 10px 0; color: #8c5ccc;"><?php echo number_format($total_users); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render section descriptions
     */
    public function render_ippanel_section_description() {
        echo '<p>' . __('Configure your IPPanel API credentials and settings.', 'wp-otp-login') . '</p>';
    }
    
    public function render_otp_section_description() {
        echo '<p>' . __('Configure OTP code generation and validation settings.', 'wp-otp-login') . '</p>';
    }
    
    public function render_user_section_description() {
        echo '<p>' . __('Configure user registration and authentication settings.', 'wp-otp-login') . '</p>';
    }
    
    /**
     * Render settings fields
     */
    public function render_api_key_field() {
        $value = get_option('wp_otp_login_api_key', '');
        echo '<input type="text" name="wp_otp_login_api_key" value="' . esc_attr($value) . '" class="regular-text" placeholder="Your IPPanel API Key">';
        echo '<p class="description">' . __('Get your API key from your IPPanel panel.', 'wp-otp-login') . '</p>';
    }
    
    public function render_sender_id_field() {
        $value = get_option('wp_otp_login_sender_id', '');
        echo '<input type="text" name="wp_otp_login_sender_id" value="' . esc_attr($value) . '" class="regular-text" placeholder="e.g., 10001000">';
        echo '<p class="description">' . __('Your sender ID or line number from IPPanel.', 'wp-otp-login') . '</p>';
    }
    
    public function render_pattern_code_field() {
        $value = get_option('wp_otp_login_pattern_code', '');
        echo '<input type="text" name="wp_otp_login_pattern_code" value="' . esc_attr($value) . '" class="regular-text" placeholder="Pattern code from IPPanel">';
        echo '<p class="description">' . __('Optional: Use pattern sending if you have a verified pattern in IPPanel.', 'wp-otp-login') . '</p>';
    }
    
    public function render_otp_length_field() {
        $value = get_option('wp_otp_login_otp_length', 6);
        echo '<input type="number" name="wp_otp_login_otp_length" value="' . esc_attr($value) . '" min="4" max="10" class="small-text">';
        echo '<p class="description">' . __('Number of digits in OTP code (4-10).', 'wp-otp-login') . '</p>';
    }
    
    public function render_otp_expiry_field() {
        $value = get_option('wp_otp_login_otp_expiry', 300);
        echo '<input type="number" name="wp_otp_login_otp_expiry" value="' . esc_attr($value) . '" min="60" max="3600" class="small-text">';
        echo '<p class="description">' . __('OTP validity period in seconds (60-3600).', 'wp-otp-login') . '</p>';
    }
    
    public function render_max_attempts_field() {
        $value = get_option('wp_otp_login_max_attempts', 3);
        echo '<input type="number" name="wp_otp_login_max_attempts" value="' . esc_attr($value) . '" min="1" max="10" class="small-text">';
        echo '<p class="description">' . __('Maximum number of verification attempts allowed.', 'wp-otp-login') . '</p>';
    }
    
    public function render_message_template_field() {
        $value = get_option('wp_otp_login_message_template', 'کد تأیید شما: %code%');
        echo '<textarea name="wp_otp_login_message_template" rows="3" class="large-text">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Use %code% placeholder for the OTP code.', 'wp-otp-login') . '</p>';
    }
    
    public function render_auto_register_field() {
        $value = get_option('wp_otp_login_auto_register', '1');
        echo '<label><input type="checkbox" name="wp_otp_login_auto_register" value="1" ' . checked($value, '1', false) . '> ' . __('Automatically register new users on first login', 'wp-otp-login') . '</label>';
    }
    
    public function render_default_role_field() {
        $value = get_option('wp_otp_login_default_role', 'subscriber');
        $roles = get_editable_roles();
        
        echo '<select name="wp_otp_login_default_role">';
        foreach ($roles as $role_key => $role_info) {
            echo '<option value="' . esc_attr($role_key) . '" ' . selected($value, $role_key, false) . '>' . esc_html($role_info['name']) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Default role for newly registered users.', 'wp-otp-login') . '</p>';
    }
    
    /**
     * Render credit balance
     */
    private function render_credit_balance() {
        $ippanel = new WP_OTP_Login_IPPanel_API();
        $balance = $ippanel->check_balance();
        
        if ($balance) {
            echo '<div style="background: #fff; padding: 15px; border-radius: 8px; display: inline-block;">';
            echo '<p style="margin: 0; font-size: 18px;">';
            echo '<strong>' . __('Current Balance:', 'wp-otp-login') . '</strong> ';
            echo number_format($balance['credit']) . ' ' . $balance['currency'];
            echo '</p>';
            echo '</div>';
        } else {
            echo '<p>' . __('Unable to fetch balance. Please check your API key.', 'wp-otp-login') . '</p>';
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wp-otp-login') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wp-otp-login-admin',
            WP_OTP_LOGIN_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WP_OTP_LOGIN_VERSION
        );
        
        wp_enqueue_script(
            'wp-otp-login-admin',
            WP_OTP_LOGIN_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WP_OTP_LOGIN_VERSION,
            true
        );
        
        wp_localize_script('wp-otp-login-admin', 'wpOtpLoginAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_otp_login_admin_nonce')
        ));
    }
}

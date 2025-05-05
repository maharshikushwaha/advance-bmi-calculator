<?php
if (!defined('ABSPATH')) {
    exit;
}

class BMI_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_menu() {
        add_options_page(
            esc_html__('Advance BMI Calculator Settings', 'advance-bmi-calculator'),
            esc_html__('Advance BMI Calculator', 'advance-bmi-calculator'),
            'manage_options',
            'bmi-calculator',
            [$this, 'render_admin_page']
        );
    }

    public function register_settings() {
        register_setting(
            'bmi_calculator_settings_group',
            'bmi_calculator_settings',
            [
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => []
            ]
        );

        add_settings_section(
            'bmi_calculator_main',
            esc_html__('Advance BMI Calculator Settings', 'advance-bmi-calculator'),
            null,
            'bmi-calculator'
        );

        add_settings_field(
            'primary_color',
            esc_html__('Primary Color', 'advance-bmi-calculator'),
            [$this, 'render_color_field'],
            'bmi-calculator',
            'bmi_calculator_main'
        );

        add_settings_field(
            'success_message',
            esc_html__('Success Message', 'advance-bmi-calculator'),
            [$this, 'render_success_message_field'],
            'bmi-calculator',
            'bmi_calculator_main'
        );

        add_settings_field(
            'bmi_ranges',
            esc_html__('BMI Range Messages', 'advance-bmi-calculator'),
            [$this, 'render_bmi_ranges_field'],
            'bmi-calculator',
            'bmi_calculator_main'
        );
    }

    public function sanitize_settings($input) {
        $sanitized = [];
        
        // Sanitize primary color
        $sanitized['primary_color'] = isset($input['primary_color']) ? sanitize_hex_color($input['primary_color']) : '';
        
        // Sanitize success message
        $sanitized['success_message'] = isset($input['success_message']) ? 
            wp_kses_post($input['success_message']) : 
            esc_html__('Your BMI has been calculated!', 'advance-bmi-calculator');

        // Sanitize BMI ranges
        $ranges = [
            'underweight' => ['max' => 18.5, 'message' => ''],
            'normal' => ['max' => 24.9, 'message' => ''],
            'overweight' => ['max' => 29.9, 'message' => ''],
            'obese' => ['max' => 999, 'message' => '']
        ];

        foreach ($ranges as $key => $range) {
            $sanitized['bmi_ranges'][$key]['max'] = isset($input['bmi_ranges'][$key]['max']) ? 
                floatval($input['bmi_ranges'][$key]['max']) : 
                $range['max'];
            $sanitized['bmi_ranges'][$key]['message'] = isset($input['bmi_ranges'][$key]['message']) ? 
                wp_kses_post($input['bmi_ranges'][$key]['message']) : 
                $range['message'];
        }

        return $sanitized;
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'advance-bmi-calculator'));
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Advance BMI Calculator Settings', 'advance-bmi-calculator'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('bmi_calculator_settings_group');
                do_settings_sections('bmi-calculator');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_color_field() {
        $settings = get_option('bmi_calculator_settings', []);
        $value = isset($settings['primary_color']) ? esc_attr($settings['primary_color']) : '';
        ?>
        <input type="text" 
               name="bmi_calculator_settings[primary_color]" 
               value="<?php echo esc_attr($value); ?>" 
               class="bmi-color-picker" 
               data-default-color="#0073aa">
        <p class="description">
            <?php esc_html_e('Select the primary color for the calculator. Leave empty to use theme default.', 'advance-bmi-calculator'); ?>
        </p>
        <?php
    }

    public function render_success_message_field() {
        $settings = get_option('bmi_calculator_settings', []);
        $value = isset($settings['success_message']) ? 
            wp_kses_post($settings['success_message']) : 
            esc_html__('Your BMI has been calculated!', 'advance-bmi-calculator');
        ?>
        <textarea name="bmi_calculator_settings[success_message]" 
                  rows="4" 
                  style="width: 100%;"><?php echo esc_textarea($value); ?></textarea>
        <p class="description">
            <?php esc_html_e('Enter the message to display after successful calculation.', 'advance-bmi-calculator'); ?>
        </p>
        <?php
    }

    public function render_bmi_ranges_field() {
        $settings = get_option('bmi_calculator_settings', []);
        $ranges = isset($settings['bmi_ranges']) ? $settings['bmi_ranges'] : [
            'underweight' => [
                'max' => 18.5,
                'message' => esc_html__('You are underweight. Consider consulting a nutritionist.', 'advance-bmi-calculator')
            ],
            'normal' => [
                'max' => 24.9,
                'message' => esc_html__('You have a normal weight. Keep maintaining a healthy lifestyle!', 'advance-bmi-calculator')
            ],
            'overweight' => [
                'max' => 29.9,
                'message' => esc_html__('You are overweight. Regular exercise may help.', 'advance-bmi-calculator')
            ],
            'obese' => [
                'max' => 999,
                'message' => esc_html__('You are obese. Please consult a healthcare professional.', 'advance-bmi-calculator')
            ]
        ];

        foreach ($ranges as $key => $range) {
            ?>
            <h4><?php echo esc_html(ucfirst($key)); ?></h4>
            <p>
                <label><?php esc_html_e('Max Value:', 'advance-bmi-calculator'); ?></label><br>
                <input type="number" 
                       step="0.1" 
                       name="bmi_calculator_settings[bmi_ranges][<?php echo esc_attr($key); ?>][max]" 
                       value="<?php echo esc_attr($range['max']); ?>">
            </p>
            <p>
                <label><?php esc_html_e('Message:', 'advance-bmi-calculator'); ?></label><br>
                <textarea name="bmi_calculator_settings[bmi_ranges][<?php echo esc_attr($key); ?>][message]" 
                          rows="3" 
                          style="width: 100%;"><?php echo esc_textarea($range['message']); ?></textarea>
            </p>
            <?php
        }
        ?>
        <p class="description">
            <?php esc_html_e('Customize the BMI ranges and their corresponding messages.', 'advance-bmi-calculator'); ?>
        </p>
        <?php
    }
}

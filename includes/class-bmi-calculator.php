<?php
if (!defined('ABSPATH')) {
    exit;
}

class BMI_Calculator {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function enqueue_scripts() {
        if (!is_admin()) {
            // Register and enqueue CSS
            wp_register_style(
                'bmi-calculator-style',
                BMI_CALC_PLUGIN_URL . 'assets/css/bmi-calculator.css',
                [],
                BMI_CALC_VERSION
            );
            wp_enqueue_style('bmi-calculator-style');

            // Register and enqueue JS
            wp_register_script(
                'bmi-calculator-script',
                BMI_CALC_PLUGIN_URL . 'assets/js/bmi-calculator.js',
                ['jquery'],
                BMI_CALC_VERSION,
                true
            );
            wp_enqueue_script('bmi-calculator-script');

            // Localize script with settings
            $settings = get_option('bmi_calculator_settings', []);
            $primary_color = $this->get_primary_color();
            wp_localize_script(
                'bmi-calculator-script',
                'bmiCalculator',
                [
                    'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                    'nonce' => wp_create_nonce('bmi_calculator_nonce'),
                    'settings' => wp_json_encode($settings),
                    'primary_color' => sanitize_hex_color($primary_color)
                ]
            );
        }
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_bmi-calculator') {
            return;
        }
        // Enqueue WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_register_script(
            'bmi-calculator-admin',
            BMI_CALC_PLUGIN_URL . 'assets/js/bmi-admin.js',
            ['wp-color-picker'],
            BMI_CALC_VERSION,
            true
        );
        wp_enqueue_script('bmi-calculator-admin');
    }

    private function get_primary_color() {
        // Get theme's primary color if defined
        $primary_color = get_theme_mod('primary_color', '#0073aa');
        
        // Override with admin settings if defined
        $settings = get_option('bmi_calculator_settings', []);
        return !empty($settings['primary_color']) ? sanitize_hex_color($settings['primary_color']) : sanitize_hex_color($primary_color);
    }
}

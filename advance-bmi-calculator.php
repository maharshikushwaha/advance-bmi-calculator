<?php
/*
Plugin Name: Advance BMI Calculator
Plugin URI: https://github.com/maharshikushwaha/advance-bmi-calculator
Description: A secure, customizable BMI calculator with admin panel settings, custom messages, and theme color integration. Use shortcode [bmi_calculator] to display.
Version: 1.0.3
Author: Maharshi Kushwaha
Author URI: https://github.com/maharshikushwaha
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: advance-bmi-calculator
Domain Path: /languages
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.8
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BMI_CALC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BMI_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BMI_CALC_VERSION', '1.0.3');

// Load translation
add_action('init', function() {
    load_plugin_textdomain('advance-bmi-calculator', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});

// Include required files
require_once BMI_CALC_PLUGIN_DIR . 'includes/class-bmi-calculator.php';
require_once BMI_CALC_PLUGIN_DIR . 'includes/class-bmi-admin.php';
require_once BMI_CALC_PLUGIN_DIR . 'includes/class-bmi-shortcode.php';

// Initialize the plugin
function bmi_calculator_init() {
    // Check for minimum PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('Advance BMI Calculator requires PHP 7.4 or higher.', 'advance-bmi-calculator'),
            esc_html__('Plugin Activation Error', 'advance-bmi-calculator'),
            ['back_link' => true]
        );
    }

    // Check for minimum WordPress version
    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('Advance BMI Calculator requires WordPress 5.0 or higher.', 'advance-bmi-calculator'),
            esc_html__('Plugin Activation Error', 'advance-bmi-calculator'),
            ['back_link' => true]
        );
    }

    $bmi_calculator = new BMI_Calculator();
    $bmi_admin = new BMI_Admin();
    $bmi_shortcode = new BMI_Shortcode();
}
add_action('plugins_loaded', 'bmi_calculator_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Set default settings
    $default_settings = [
        'primary_color' => '',
        'success_message' => esc_html__('Your BMI has been calculated!', 'advance-bmi-calculator'),
        'bmi_ranges' => [
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
        ]
    ];
    if (!get_option('bmi_calculator_settings')) {
        update_option('bmi_calculator_settings', $default_settings);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if needed (optional)
});

// Uninstall hook
register_uninstall_hook(__FILE__, 'bmi_calculator_uninstall');
function bmi_calculator_uninstall() {
    if (!current_user_can('activate_plugins')) {
        return;
    }
    delete_option('bmi_calculator_settings');
}
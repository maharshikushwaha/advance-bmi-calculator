<?php
if (!defined('ABSPATH')) {
    exit;
}

class BMI_Shortcode {
    public function __construct() {
        add_shortcode('bmi_calculator', [$this, 'render_shortcode']);
        add_action('wp_ajax_calculate_bmi', [$this, 'calculate_bmi']);
        add_action('wp_ajax_nopriv_calculate_bmi', [$this, 'calculate_bmi']);
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts([], $atts, 'bmi_calculator');
        
        ob_start();
        ?>
        <div class="bmi-calculator-wrapper">
            <form id="bmi-calculator-form" class="bmi-calculator-form">
                <div class="bmi-field">
                    <label for="bmi-height">
                        <?php esc_html_e('Height (cm)', 'advance-bmi-calculator'); ?>
                    </label>
                    <input type="number" 
                           id="bmi-height" 
                           name="height" 
                           step="0.1" 
                           min="50" 
                           max="300" 
                           required>
                </div>
                <div class="bmi-field">
                    <label for="bmi-weight">
                        <?php esc_html_e('Weight (kg)', 'advance-bmi-calculator'); ?>
                    </label>
                    <input type="number" 
                           id="bmi-weight" 
                           name="weight" 
                           step="0.1" 
                           min="20" 
                           max="500" 
                           required>
                </div>
                <button type="submit" 
                        class="bmi-submit">
                    <?php esc_html_e('Calculate BMI', 'advance-bmi-calculator'); ?>
                </button>
            </form>
            <div id="bmi-result" style="display: none;"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function calculate_bmi() {
        // Verify nonce
        if (!check_ajax_referer('bmi_calculator_nonce', 'nonce', false)) {
            wp_send_json_error([
                'message' => esc_html__('Security check failed.', 'advance-bmi-calculator')
            ], 403);
        }

        // Validate input
        $height = isset($_POST['height']) ? floatval($_POST['height']) : 0;
        $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;

        // Validate ranges
        if ($height < 50 || $height > 300 || $weight < 20 || $weight > 500) {
            wp_send_json_error([
                'message' => esc_html__('Please enter valid height (50-300 cm) and weight (20-500 kg).', 'advance-bmi-calculator')
            ], 400);
        }

        // Calculate BMI
        $height_m = $height / 100; // Convert cm to meters
        $bmi = round($weight / ($height_m * $height_m), 1);

        // Get settings
        $settings = get_option('bmi_calculator_settings', []);
        $ranges = isset($settings['bmi_ranges']) ? $settings['bmi_ranges'] : [
            'underweight' => ['max' => 18.5, 'message' => esc_html__('You are underweight.', 'advance-bmi-calculator')],
            'normal' => ['max' => 24.9, 'message' => esc_html__('You have a normal weight.', 'advance-bmi-calculator')],
            'overweight' => ['max' => 29.9, 'message' => esc_html__('You are overweight.', 'advance-bmi-calculator')],
            'obese' => ['max' => 999, 'message' => esc_html__('You are obese.', 'advance-bmi-calculator')]
        ];

        // Determine category
        $category = 'obese';
        $message = $ranges['obese']['message'];

        foreach ($ranges as $key => $range) {
            if ($bmi <= floatval($range['max'])) {
                $category = $key;
                $message = wp_kses_post($range['message']);
                break;
            }
        }

        // Send response
        wp_send_json_success([
            'bmi' => $bmi,
            'category' => esc_html(ucfirst($category)),
            'message' => $message,
            'success_message' => wp_kses_post($settings['success_message'] ?? esc_html__('Your BMI has been calculated!', 'advance-bmi-calculator'))
        ]);
    }
}

(function($) {
    'use strict';

    $(document).ready(function() {
        // Set primary color
        if (bmiCalculator.primary_color) {
            document.documentElement.style.setProperty('--bmi-primary-color', bmiCalculator.primary_color);
            // Calculate darker shade for hover
            let color = bmiCalculator.primary_color;
            let r = parseInt(color.slice(1, 3), 16);
            let g = parseInt(color.slice(3, 5), 16);
            let b = parseInt(color.slice(5, 7), 16);
            r = Math.max(0, r - 20);
            g = Math.max(0, g - 20);
            b = Math.max(0, b - 20);
            let hoverColor = `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
            document.documentElement.style.setProperty('--bmi-primary-color-hover', hoverColor);
        }

        $('#bmi-calculator-form').on('submit', function(e) {
            e.preventDefault();

            let $form = $(this);
            let formData = {
                action: 'calculate_bmi',
                nonce: bmiCalculator.nonce,
                height: $('#bmi-height').val(),
                weight: $('#bmi-weight').val()
            };

            $.ajax({
                url: bmiCalculator.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#bmi-result').html(`
                            <h3>${response.data.success_message}</h3>
                            <p>Your BMI: <strong>${response.data.bmi}</strong></p>
                            <p>Category: <strong>${response.data.category}</strong></p>
                            <p>${response.data.message}</p>
                        `).show();
                    } else {
                        $('#bmi-result').html(`
                            <p style="color: red;">${response.data.message}</p>
                        `).show();
                    }
                },
                error: function() {
                    $('#bmi-result').html(`
                        <p style="color: red;">An error occurred. Please try again.</p>
                    `).show();
                }
            });
        });
    });
})(jQuery);
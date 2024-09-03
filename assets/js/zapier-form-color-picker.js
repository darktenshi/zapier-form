jQuery(document).ready(function($) {
    $('.zapier-color-picker').wpColorPicker({
        change: function(event, ui) {
            $(this).val(ui.color.toString());
            $(this).closest('.zapier-color-field-wrapper').find('.zapier-color-picker-hex').val(ui.color.toString());
            updateGradientPreview();
        },
        clear: function() {
            var default_color = $(this).data('default-color') || '';
            $(this).val(default_color);
            $(this).closest('.zapier-color-field-wrapper').find('.zapier-color-picker-hex').val(default_color);
            updateGradientPreview();
        }
    });

    $('.zapier-color-picker-hex').on('input', function() {
        var hexColor = $(this).val();
        if (/^#[0-9A-F]{6}$/i.test(hexColor)) {
            $(this).closest('.zapier-color-field-wrapper').find('.zapier-color-picker').wpColorPicker('color', hexColor);
        }
    });

    
    function updateGradientPreview() {
        var startColor = $('#zapier_gradient_start').val();
        var endColor = $('#zapier_gradient_end').val();
        var headingText1 = $('#zapier_heading_text_1').val();
        var headingText2 = $('#zapier_heading_text_2').val();

        var gradientStyle = 'linear-gradient(90deg, ' + startColor + ' 0%, ' + endColor + ' 100%)';
        
        $('#gradient-preview').html(
            '<span class="normal-text">' + headingText1 + ' </span>' +
            '<span class="gradient-text">' + headingText2 + '</span>'
        );

        $('#gradient-preview .gradient-text').css({
            'background': gradientStyle,
            '-webkit-background-clip': 'text',
            '-webkit-text-fill-color': 'transparent',
            'background-clip': 'text'
        });
    }

    
    $('#zapier_heading_text_1, #zapier_heading_text_2').on('input', updateGradientPreview);

    
    updateGradientPreview();
});
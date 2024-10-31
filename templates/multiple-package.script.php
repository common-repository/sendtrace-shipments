<script>
    jQuery(document).ready(function($) {
        const CUBIC_DIVISOR = <?php echo esc_attr($sendtrace->get_cubic_meter_divisor()) ?>;
        const VOLUMETRIC_DIVISOR = <?php echo esc_attr($sendtrace->get_volumetric_weight_divisor()) ?>;

        $('#multiple-package').on('change', '.qty, .weight, .length, .width, .height', function() {
            var pkg_totals = {
                cubic: 0,
                volumetric_weight: 0,
                actual_weight: 0
            };
            
            $('#multiple-package').find('.package-item').each(function(){
                let qty = parseInt($(this).find('input.qty').val());
                let weight = parseFloat($(this).find('input.weight').val());
                let length = parseFloat($(this).find('input.length').val());
                let width = parseFloat($(this).find('input.width').val());
                let height = parseFloat($(this).find('input.height').val());

                if (!isNaN(qty)) {
                    if (!isNaN(weight)) {
                        let item_actual_weight = qty * weight;
                        pkg_totals.actual_weight += item_actual_weight;
                    }
                    if (!isNaN(length) && !isNaN(width) && !isNaN(height)) {
                        let item_cubic = qty * ((length * width * height) / CUBIC_DIVISOR);
                        let item_volumetric = qty * ((length * width * height) / VOLUMETRIC_DIVISOR);
                        pkg_totals.cubic += item_cubic;
                        pkg_totals.volumetric_weight += item_volumetric;
                    }
                }                
            });

            $('#multiple-package').find('#cubic-meter .value').text(pkg_totals.cubic.toFixed(3));
            $('#multiple-package').find('#volumetric-weight .value').text(pkg_totals.volumetric_weight.toFixed(2));
            $('#multiple-package').find('#actual-weight .value').text(pkg_totals.actual_weight.toFixed(2));
        });
    });
</script>
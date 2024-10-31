<?php
if (!empty($package_data) && !empty($package_data[0]['qty'])) {
    echo "<div id='multiple-package' class='mt-4'>";
        echo "<p class='heading'><strong>" .esc_html(wpst_multiple_package_label()). " </strong></p>";
        echo "<table class='table table-bordered table-responsive-sm d-block d-sm-table'>";
            echo "<thead>";
                if (!empty($package_fields)) {
                    echo "<tr class='package-item'>";
                        foreach ($package_fields as $_key => $field) {
                            echo "<td class='".esc_html($_key)."-heading' ".esc_html($field['td_extras'] ?? '').">";
                                echo "<span class='fw-semibold'>".esc_html($field['label'])."</span>";
                            echo "</td>";
                        }
                    echo "</tr>";
                }
            echo "</thead>";
            echo "<tbody>";
                if (!empty($package_fields)) {
                    if (!empty($package_data)) {
                        foreach ($package_data as $package) {
                            echo "<tr class='package-item'>";
                                foreach ($package_fields as $_key => $field) {
                                    $field['value'] = array_key_exists($_key, $package) ? $package[$_key] : '';
                                    echo "<td class='".esc_html($_key)."'>";
                                        echo esc_html($field['value']);
                                    echo "</td>";
                                }
                            echo "</tr>";
                        }
                    }
                }
            echo "</tbody>";
        echo "</table>";
        echo "<div id='total-weights' class='row'>";
            // Cubic Meter
            echo "<div id='cubic-meter' class='col-md-4 col-sm-12'>";
                echo "<span class='label fw-semibold'>".esc_html(wpst_cubic_unit_label('meter'))."</span>: ";
                echo "<span class='value'>".esc_html($sendtrace->get_package_totals($shipment_id)['cubic'])."</span> ";
                echo "<span class='symbol'>".esc_html($sendtrace->get_symbol_unit('meter'))."<sup>3</sup></span>";
            echo "</div>";
            // Volumetric Weight
            echo "<div id='volumetric-weight' class='col-md-4 col-sm-12'>";
                echo "<span class='label fw-semibold'>".esc_html(wpst_volumetric_weight_label())."</span>: ";
                echo "<span class='value'>".esc_html($sendtrace->get_package_totals($shipment_id)['volumetric_weight'])."</span> ";
                echo "<span class='symbol'>".esc_html($sendtrace->get_symbol_unit('weight'))."</span>";
            echo "</div>";
            // Actual Weight
            echo "<div id='actual-weight' class='col-md-4 col-sm-12'>";
                echo "<span class='label fw-semibold'>".esc_html(wpst_actuual_weight_label())."</span>: ";
                echo "<span class='value'>".esc_html($sendtrace->get_package_totals($shipment_id)['actual_weight'])."</span> ";
                echo "<span class='symbol'>".esc_html($sendtrace->get_symbol_unit('weight'))."</span>";
            echo "</div>";
        echo "</div>";
    echo "</div>";
}
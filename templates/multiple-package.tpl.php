<?php
echo "<div id='multiple-package' class='col-sm-12 mb-3'>";
    echo "<div class='card p-0 mw-100 card-primary'>";
        echo "<div class='card-header'>";
            echo "<h5 class='h4 m-0' data-bs-toggle='collapse' href='#multiple_package_toggle' role='button' aria-expanded='false' aria-controls='multiple_package_toggle'> " .esc_html(wpst_multiple_package_label()). " </h5>";
        echo "</div>";
        echo "<div class='card-body table-responsive collapse show' id='multiple_package_toggle'>";
            echo "<table class='table table-bordered table-responsive-sm d-block d-sm-table repeater'>";
                echo "<thead>";
                    if (!empty($package_fields)) {
                        echo "<tr class='package-item'>";
                            foreach ($package_fields as $_key => $field) {
                                echo "<th class='".esc_html($_key)."-heading' ".esc_html($field['td_extras'] ?? '').">";
                                    echo esc_html($field['label']);
                                echo "</th>";
                            }
                            if ($allow_add_delete) {
                                echo "<th>".__('Action', 'sendtrace-shipments')."</th>";
                            }                            
                        echo "</tr>";
                    }
                echo "</thead>";
                echo "<tbody data-repeater-list='multiple-package'>";
                    if (!empty($package_fields)) {
                        if (!empty($package_data)) {
                            foreach ($package_data as $package) {
                                echo "<tr data-repeater-item class='package-item'>";
                                    foreach ($package_fields as $_key => $field) {
                                        $field['value'] = array_key_exists($_key, $package) ? $package[$_key] : '';
                                        if (in_array($_key, $disabled_fields)) {
                                            $field['extras'] = array_key_exists('extras', $field) ? $field['extras'].' readonly' : 'readonly';
                                        }
                                        echo "<td class='".esc_html($_key)."'>";
                                            WPSTForm::gen_field($field);
                                        echo "</td>";
                                    }
                                    if ($allow_add_delete) {
                                        echo "<td><span data-repeater-delete class='btn btn-sm text-white bg-danger item-delete'>".__('Delete', 'sendtrace-shipments')."</span></td>";
                                    }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr data-repeater-item class='package-item'>";
                                foreach ($package_fields as $_key => $field) {
                                    echo "<td class='".esc_html($_key)."'>";
                                        WPSTForm::gen_field($field);
                                    echo "</td>";
                                }
                                if ($allow_add_delete) {
                                    echo "<td><span data-repeater-delete class='btn btn-sm text-white bg-danger item-delete'>".__('Delete', 'sendtrace-shipments')."</span></td>";
                                }
                            echo "</tr>";
                        }
                    }
                echo "</tbody>";
                if ($allow_add_delete) {
                    echo "<tfoot>";
                        echo "<tr>";
                            echo "<td colspan='".esc_html(count($package_fields)+1)."' align='right'>";
                                echo "<span type='button' data-repeater-create class='btn btn-sm text-white btn-info item-add'>".__('Add', 'sendtrace-shipments')."</span>";
                            echo "</td>";
                        echo "</tr>";
                    echo "</tfoot>";
                }                
            echo "</table>";
            if ($has_pkg_totals) {
                echo "<div id='total-weights' class='row'>";
                    // Cubic Meter
                    echo "<div id='cubic-meter' class='col-md-4 col-sm-12'>";
                        echo "<span class='label'>".esc_html(wpst_cubic_unit_label('meter'))."</span>: ";
                        echo "<span class='value font-weight-bold'>".esc_html($sendtrace->get_package_totals($shipment_id)['cubic'])."</span> ";
                        echo "<span class='symbol font-weight-bold'>".esc_html($sendtrace->get_symbol_unit('meter'))."<sup>3</sup></span>";
                    echo "</div>";
                    // Volumetric Weight
                    echo "<div id='volumetric-weight' class='col-md-4 col-sm-12'>";
                        echo "<span class='label'>".esc_html(wpst_volumetric_weight_label())."</span>: ";
                        echo "<span class='value font-weight-bold'>".esc_html($sendtrace->get_package_totals($shipment_id)['volumetric_weight'])."</span> ";
                        echo "<span class='symbol font-weight-bold'>".esc_html($sendtrace->get_symbol_unit('weight'))."</span>";
                    echo "</div>";
                    // Actual Weight
                    echo "<div id='actual-weight' class='col-md-4 col-sm-12'>";
                        echo "<span class='label'>".esc_html(wpst_actuual_weight_label())."</span>: ";
                        echo "<span class='value font-weight-bold'>".esc_html($sendtrace->get_package_totals($shipment_id)['actual_weight'])."</span> ";
                        echo "<span class='symbol font-weight-bold'>".esc_html($sendtrace->get_symbol_unit('weight'))."</span>";
                    echo "</div>";
                echo "</div>";
            }
            
        echo "</div>";
    echo "</div>";
echo "</div>";

// Enqueue Package Script
if ($has_pkg_totals) {
    require_once wpst_get_template('multiple-package.script');
}
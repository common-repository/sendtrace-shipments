<?php
echo "<div id='shipment-history' class='col-sm-12'>";
    echo "<div class='card p-0 mw-100 card-primary'>";
        echo "<div class='card-header'>";
            echo "<h5 class='h4 m-0' data-bs-toggle='collapse' href='#history_toggle' role='button' aria-expanded='false' aria-controls='history_toggle'> " .esc_html(wpst_history_label()). " </h5>";
        echo "</div>";
        echo "<div class='card-body collapse show' id='history_toggle'>";
            echo "<table class='table table-bordered table-responsive-sm d-block d-sm-table repeater'>";
                echo "<thead>";
                    if (!empty($history_fields)) {
                        echo "<tr class='history-item'>";
                            foreach ($history_fields as $field) {
                                echo "<th class='".esc_html($field['key'])."-heading' ".esc_html($field['td_extras'] ?? '').">";
                                    echo esc_html($field['label']);
                                echo "</th>";
                            }
                            if (wpst_can_modify_history()) {
                                echo "<th>".__('Action', 'sendtrace-shipments')."</th>";
                            }                            
                        echo "</tr>";
                    }
                echo "</thead>";
                echo "<tbody data-repeater-list='shipment-history'>";
                    if (!empty($history_fields) && !empty($history_data)) {
                        foreach ($history_data as $history) {
                            echo "<tr class='package-item' data-repeater-item>";
                                foreach ($history_fields as $field) {
                                    $field['value'] = array_key_exists($field['key'], $history) ? $history[$field['key']] : '';
                                    if ($field['key'] == 'updated_by' && array_key_exists($field['key'], $history) && !empty($history[$field['key']])) {
                                        $updated_by = get_userdata($history[$field['key']]);
                                        if (!empty($updated_by)) {
                                            $field['value'] = $updated_by->display_name;
                                        }
                                    }
                                    
                                    echo "<td class='form-group position-relative ".esc_html($field['key'])."'>";
                                        WPSTForm::gen_field($field);
                                    echo "</td>";
                                }
                                if (wpst_can_modify_history()) {
                                    echo "<td class='action'>";
                                        WPSTForm::gen_button('', 'Delete', 'button', 'btn btn-danger btn-sm', 'data-repeater-delete');
                                    echo "</td>";
                                }
                            echo "</tr>";
                        }
                    }
                echo "</tbody>";
            echo "</table>";
        echo "</div>";
    echo "</div>";
echo "</div>";
?>
<?php
WPSTForm::start();
WPSTForm::nonce(array('field' => 'wpst_setting_nonce_field', 'action' => 'wpst_setting_nonce_action'));
echo "<div class='row'>";
    if (!empty($admin_email_fields)) {
        foreach ($admin_email_fields as $section_field) {
            $section_heading = array_key_exists('heading', $section_field) ? $section_field['heading'] : '';
            echo "<div class='col-md-12'>";
                echo "<h5>" .esc_html($section_heading). "</h5>";
            echo "</div>";
            foreach ($section_field['fields'] as $field) {
                echo "<div class='col-md-12 mb-2 px-4'>";
                    echo "<div class='row'>";
                        echo "<div class='col-md-2'>";
                            echo "<label class='mr-4'>" .esc_html($field['label']). "</label>";
                        echo "</div>";
                        echo "<div class='col-md-10'>";
                            echo WPSTForm::gen_field($field, false, true);
                        echo "</div>";
                    echo "</div>";
                echo "</div>";
            }
        }
    }   
    echo "<div class='col-12 text-right'>";
        WPSTForm::gen_button('btn_save', __('Save Settings'), 'submit', 'btn btn-success');
    echo "</div>";
echo "</div>";
WPSTForm::end();
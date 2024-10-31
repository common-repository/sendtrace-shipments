<?php
WPSTForm::start();
WPSTForm::nonce(array('field' => 'wpst_setting_nonce_field', 'action' => 'wpst_setting_nonce_action'));
echo "<div id='".esc_attr($menu_key)."-container' class='shadow-lg tab-container p-3 ".(($current_tab == $menu_key) ? 'active' : '')."'>";
    echo "<div class='row'>";
        echo "<div class='col-md-6'>";
        if (!empty($general_setting_fields)) {
            foreach ($general_setting_fields as $section_field) {
                $section_heading = array_key_exists('heading', $section_field) ? wpst_sanitize_data($section_field['heading']) : '';
                echo "<div class='card p-0 m-0 mb-3 w-100'>";
                    echo "<h5 class='card-header'>" .esc_html($section_heading). "</h5>";
                    echo "<div class='card-body'>";    
                        foreach ($section_field['fields'] as $field) {
                            WPSTForm::gen_field($field, true);
                            if ($field['key'] == 'company_logo') {
                                echo "";
                            }
                        }                
                    echo "</div>";
                echo "</div>";  
            }
        }
        echo "</div>";
        echo "<div class='col-md-6'>";
            do_action('sendtrace_info');
        echo "</div>";        
        echo "<div class='col-12 p-2'>";
            WPSTForm::gen_button('btn_submit', __('Save Settings', 'sendtrace-shipments'), 'submit', 'btn btn-success');
        echo "</div>";
    echo "</div>";
echo "</div>";
WPSTForm::end();
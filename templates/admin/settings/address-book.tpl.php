<?php
global $WPSTField;
$ab_setting_fields = $WPSTField->settings_field()['address_book'];
WPSTForm::start();
WPSTForm::nonce(array('field' => 'wpst_setting_nonce_field', 'action' => 'wpst_setting_nonce_action'));
echo "<div id='".esc_attr($menu_key)."-container' class='shadow-lg tab-container p-3 ".(($current_tab == $menu_key) ? 'active' : '')."'>";
	if (!wpst_is_ab_premium()) {
		echo '<div class="alert-warning p-3 mb-4">Buy <strong>Address Book Addon</strong> to enjoy unlimited users.</div>';
	}
	echo "<div class='row'>";
        echo "<div class='col-md-6'>";
		if (!empty($ab_setting_fields)) {
			foreach ($ab_setting_fields as $section_field) {
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
        echo "</div>";       
        echo "<div class='col-12 p-2'>";
            WPSTForm::gen_button('btn_submit', __('Save Settings', 'sendtrace-shipments'), 'submit', 'btn btn-success');
        echo "</div>";
    echo "</div>";
echo "</div>";
WPSTForm::end();
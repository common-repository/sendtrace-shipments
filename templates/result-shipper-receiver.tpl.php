<?php
echo "<div id='shipper-receiver-details' class='row'>";
if (!empty($shipment_data)) {
    foreach ($shipment_data as $section => $section_data) {
        $section_heading = apply_filters("track_result_{$section}_heading", $section_data['heading']);

        echo "<div id='".esc_html($section)."' class='col-md-6 col-sm-12'>";
            echo "<p class='mt-5 heading'><strong>".esc_html($section_heading)."</strong></p>";
            if (!empty($section_data['fields'])) {
                foreach ($section_data['fields'] as $data) {
                    $value = $data['value'];
                    if ($data['key'] == 'wpst_shipper_address') {
                        $country = array_key_exists('country', $value) ? $value['country'] : '';
                        unset($value['country']);
                        array_push($value, $country);
                    }
                    if (is_array($value)) {
                        $value = implode(', ', array_filter($value));
                    }
                    echo "<p class='mb-1'><span class='fw-semibold'>".esc_html($data['label'])."</span>: ".esc_html($value)."</p>";
                }
            }
        echo "</div>";
    }
}
echo "</div>";

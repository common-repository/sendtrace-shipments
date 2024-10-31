<?php
if (!empty($shipment_data['fields'])) {
    echo "<div id='shipment_info' class='mt-4'>";
        echo "<p class='heading'><strong>".esc_html(apply_filters('track_result_shipment_details_heading', $shipment_data['heading']))."</strong></p>";
        echo "<div class='row'>";            
            foreach ($shipment_data['fields'] as $data) {
                if (empty($data['value'])) {
                    continue;
                }
                if (is_array($data['value'])) {
                    $data['value'] = implode(', ', $data['value']);
                }
                echo "<p class='mb-1 col-md-6 col-sm-12'><span class='fw-semibold'>".esc_html($data['label'])."</span>: ".esc_html($data['value'])."</p>";
            }
        echo "</div>";
    echo "</div>";
}
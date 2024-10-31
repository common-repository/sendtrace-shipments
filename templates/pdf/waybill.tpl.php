<table width="100%" cellPading="0" cellSpacing="0">
    <tr>
        <td style="padding-right: 15px;">
            <table width="100%">
                <tr>
                    <td align="center"><div style="height: 150px;"><!-- Spacing ---></div></td>
                </tr>
                <tr>
                    <td width="50%" align="center">
                        <h2><a href="<?php echo esc_url($data['site_info']['site_url']) ?>" style="font-size: 48px;"><?php echo esc_html($data['site_info']['name']) ?></a></h2>
                        <p><?php echo esc_html($data['site_info']['description']) ?></p>
                    </td>
                </tr>
            </table>
        </td>
        <td style="padding-left: 15px;">
            <table width="100%">
                <tr>
                    <td width="50%" align="center">
                        <!-- image url is encrypted esc_url might not work -->
                        <img width="550px" height="120px" src="<?php echo esc_attr($data['shipment']['barcode_url']) ?>"/> 
                        <p style="font-size: 34px;"><strong><?php echo esc_html($data['shipment']['tracking_no']) ?></strong></p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td width="50%" style="padding-right: 15px;">
            <!-- Shippper Details -->
            <table width="100%">
                <tr>
                    <td class="border-bottom border-top" style="padding: 15px 5px; border-width: 3px">
                        <?php if (!empty($data['shipment']['custom_fields']['shipper_information']['fields'])) : ?>
                            <h3>SHIPPER</h3>
                            <?php foreach ($data['shipment']['custom_fields']['shipper_information']['fields'] as $field) : 
                                $field['value'] = is_array($field['value']) ? implode(', ', array_filter($field['value'])) : $field['value'];
                            ?>
                                <p><?php echo esc_html($field['label']).': '.esc_html($field['value']) ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <!-- Receiver Details -->
            <table width="100%">
                <tr>
                    <td style="padding: 15px 5px">
                        <?php if (!empty($data['shipment']['custom_fields']['receiver_information']['fields'])) : ?>
                            <h3>RECEIVER</h3>
                            <?php foreach ($data['shipment']['custom_fields']['receiver_information']['fields'] as $field) : 
                                $field['value'] = is_array($field['value']) ? implode(', ', array_filter($field['value'])) : $field['value'];
                            ?>
                                <p><?php echo esc_html($field['label']).': '.esc_html($field['value']) ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </td>
        <td width="50%" style="padding-left: 15px;">   
            <!-- Shipment Info -->
            <table width="100%">
                <tr>
                    <td class="border-top" style="padding: 15px 5px; border-width: 3px;">
                        <?php if (!empty($data['shipment']['custom_fields']['shipment_details']['fields'])) : ?>
                            <h3><?php echo strtoupper($data['shipment']['custom_fields']['shipment_details']['heading']) ?></h3>
                            <?php
                                foreach ($data['shipment']['custom_fields']['shipment_details']['fields'] as $field) : 
                                $field['value'] = is_array($field['value']) ? implode(', ', array_filter($field['value'])) : $field['value'];
                                if (!empty($field['value'])) {
                                    echo "<p>" .esc_html($field['label']).': '.esc_html($field['value']). "</p>";
                                }
                            ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="border-top" style="border-width: 3px; padding: 15px 5px">
            <h3>PACKAGES</h3>
            <?php if (!empty($data['shipment']['packages'])) : ?>
                <table width="100%" border="1">
                    <tr>
                        <?php foreach ($data['shipment']['packages']['fields'] as $field_key => $label) : 
                        $unit = '';
                        if (in_array($field_key, ['weight', 'length', 'width', 'height'])) {
                            $unit = $field_key == 'weight' ? $data['shipment']['packages']['units']['weight'] : $data['shipment']['packages']['units']['dimension'];
                            $unit = '('.$unit.')';
                        }
                        ?>
                            <td style="padding: 6px"> <?php echo esc_html($label.$unit) ?> </td>
                        <?php endforeach; ?>
                    </tr>  
                    <?php if ($data['shipment']['packages']['data']) : ?>
                        <?php foreach ($data['shipment']['packages']['data'] as $package) : ?>
                            <tr>
                            <?php foreach ($package as $value) : ?>
                                <td style="padding: 6px"> <?php echo esc_html($value) ?> </td>
                            <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?> 
                    <?php endif; ?>           
                </table>
            <?php endif; ?>
        </td>
    </tr>
</table>
<!-- Header -->
<table width="100%">
    <tr>
        <td align="left" valign="top">
            <h2><a href="<?php echo esc_url($data['site_info']['site_url']) ?>"  style="font-size: 32px;"><?php echo esc_html($data['site_info']['name']) ?></a></h2>
            <p><?php echo esc_html($data['site_info']['description']) ?></p>
        </td>
        <td align="right" valign="top">
            <h3 style="font-size: 2.2rem; margin-bottom: 1rem"> INVOICE </h3>
            <p> Invoice #: <u> &nbsp;&nbsp;&nbsp;&nbsp;<?php echo esc_html($data['shipment']['tracking_no']) ?>&nbsp;&nbsp;&nbsp;&nbsp; </u></p>
        </td>
    </tr>
</table>

<div style="border-bottom: 1px solid #000; margin: 25px 0;"></div>

<table width="100%">
    <tr>
        <!-- Shipper Details -->
        <td width="50%">
            <?php if (!empty($data['shipment']['custom_fields']['shipper_information']['fields'])) : ?>
                <h3> SHIPPER </h3>
                <?php foreach ($data['shipment']['custom_fields']['shipper_information']['fields'] as $field) : 
                    $field['value'] = is_array($field['value']) ? implode(', ', array_filter($field['value'])) : $field['value'];
                ?>
                    <p><?php echo esc_html($field['label']).': '.esc_html($field['value']) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </td>

        <!-- Receiver Details -->
        <td width="50%">
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

<div style="border-bottom: 1px solid #000; margin: 25px 0;"></div>

<!-- Packages Details-->
<h3>PACKAGES</h3>
<?php if (!empty($data['shipment']['packages'])) : ?>
    <table width="100%" border="1" style="border-collapse: collapse;">
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

<div style="border-bottom: 1px solid #000; margin: 45px 0 25px;"></div>

<!-- Barcode -->
<table width="100%">
    <tr>
        <td align="center"> 
            <!-- image url is encrypted esc_url might not work -->
            <img width="650px" height="120px" src="<?php echo esc_attr($data['shipment']['barcode_url']); ?>"/> 
            <p style="margin-top: 6px; font-size: 34px; letter-spacing: 3px"><strong><?php echo esc_html($data['shipment']['tracking_no']) ?></strong></p>
        </td>
    </tr>
</table>

<div style="border-bottom: 1px solid #000; margin: 20px 0 25px;"></div>
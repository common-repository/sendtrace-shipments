<?php

class WPSTField
{
    function fields($shipment_id=0, $section='') {
        global $sendtrace;
        $fields = array(
            'shipper_information' => array(
                'section_col' => '6',
                'field_col' => '12',
                'heading' => __('Shipper Information'),
                'fields' => array(
                    'wpst_shipper_name' => array(
                        'key' => 'wpst_shipper_name',
                        'label' => __('Shipper Name', 'sendtrace-shipments'),
                        'type' => 'text',
                        'required' => true
                    ),
                    'wpst_shipper_phone_number' => array(
                        'key' => 'wpst_shipper_phone_number',
                        'label' => __('Phone Number', 'sendtrace-shipments'),
                        'type' => 'number',
                        'extras' => 'step=any'
                    ),
                    'wpst_shipper_email' => array(
                        'key' => 'wpst_shipper_email',
                        'label' => __('Email', 'sendtrace-shipments'),
                        'type' => 'email',
                    ),
                    'wpst_shipper_address' => array(
                        'key' => 'wpst_shipper_address',
                        'label' => __('Address', 'sendtrace-shipments'),
                        'type' => 'address'
                    )
                )
            ),
            'receiver_information' => array(
                'section_col' => '6',
                'field_col' => '12',
                'heading' => __('Receiver Information'),
                'fields' => array(
                    'wpst_receiver_name' => array(
                        'key' => 'wpst_receiver_name',
                        'label' => __('Receiver Name', 'sendtrace-shipments'),
                        'type' => 'text',
                        'required' => true
                    ),
                    'wpst_receiver_phone_number' => array(
                        'key' => 'wpst_receiver_phone_number',
                        'label' => __('Phone Number', 'sendtrace-shipments'),
                        'type' => 'number',
                        'extras' => 'step=any'
                    ),
                    'wpst_receiver_email' => array(
                        'key' => 'wpst_receiver_email',
                        'label' => __('Email', 'sendtrace-shipments'),
                        'type' => 'email',
                    ),
                    'wpst_receiver_address' => array(
                        'key' => 'wpst_receiver_address',
                        'label' => __('Address', 'sendtrace-shipments'),
                        'type' => 'address',
                    )
                )
            ),
            'shipment_details' => array(
                'section_col' => '12',
                'field_col' => '6',
                'heading' => __('Shipment Details'),
                'fields' => array(
                    'wpst_transporation_mode' => array(
                        'key' => 'wpst_transporation_mode',
                        'label' => __('Transportation Mode', 'sendtrace-shipments'),
                        'type' => 'select',
                        'options' => $sendtrace->shipment_types(),
                        'class' => 'w-100'
                    ),
                    'wpst_courier' => array(
                        'key' => 'wpst_courier',
                        'label' => __('Courier', 'sendtrace-shipments'),
                        'type' => 'text',
                    ),
                    'wpst_carrier' => array(
                        'key' => 'wpst_carrier',
                        'label' => __('Carrier', 'sendtrace-shipments'),
                        'type' => 'select',
                        'options' => $sendtrace->carriers(),
                        'class' => 'w-100'
                    ),
                    'wpst_origin' => array(
                        'key' => 'wpst_origin',
                        'label' => __('Origin', 'sendtrace-shipments'),
                        'type' => 'text',
                    ),
                    'wpst_destination' => array(
                        'key' => 'wpst_destination',
                        'label' => __('Destination', 'sendtrace-shipments'),
                        'type' => 'text',
                    ),
                    'wpst_pickup_date' => array(
                        'key' => 'wpst_pickup_date',
                        'label' => __('Pickup Date', 'sendtrace-shipments'),
                        'type' => 'date',
                    ),
                    'wpst_pickup_time' => array(
                        'key' => 'wpst_pickup_time',
                        'label' => __('Pickup Time', 'sendtrace-shipments'),
                        'type' => 'time'
                    ),
                    'wpst_departure_time' => array(
                        'key' => 'wpst_departure_time',
                        'label' => __('Departure Time', 'sendtrace-shipments'),
                        'type' => 'time',
                    ),
                    'wpst_expected_delivery_date' => array(
                        'key' => 'wpst_expected_delivery_date',
                        'label' => __('Expected Delivery Date', 'sendtrace-shipments'),
                        'type' => 'date',
                    ),
                    'wpst_remarks' => array(
                        'key' => 'wpst_remarks',
                        'label' => __('Remarks', 'sendtrace-shipments'),
                        'type' => 'textarea',
                        'class' => 'w-100'
                    ),
                )
            )
        );
        
        if ($shipment_id) {
            foreach ($fields as $_section => $info) {
                foreach ($info['fields'] as $field) {
                    $field_value = wpst_sanitize_data(get_post_meta($shipment_id, $field['key'], true)) ?? '';
                    $fields[$_section]['fields'][$field['key']]['value'] = $field_value;
                }
            }
        }

        $fields = apply_filters('wpst_fields', $fields, $shipment_id);
        if (!empty($section)) {
            $fields = array_key_exists($section, $fields) ? [$section => $fields[$section]] : [];
        }
        return $fields;
    }

    function shipment_fields_only($shipment_id=0, $section='') {
        return wpst_merge_array_values(array_column($this->fields($shipment_id, $section), 'fields'));
    }

    function shipment_fields_key_label_pair($section='')
    {
        $fields = $this->fields(0, $section);
        $fields = !empty($fields) ? array_column($fields, 'fields') : [];
        if (!empty($fields)) {
            $new_fields = [];
            foreach ($fields as $idx => $_fields) {
                foreach ($_fields as $_field) {
                    $new_fields[$_field['key']] = $_field['label'];
                }
            }
            $fields = $new_fields;
        }
        return $fields;
    }

    function settings_field() {
        global $sendtrace;
        $pages = wpst_get_pages();
        $admin_default_email = sanitize_email(get_bloginfo('admin_email'));
        $status_list = $sendtrace->status_list();
        $fields = array(
            'general' => array(
                array(
                    'heading' => __('Appearance & Company Info', 'sendtrace-shipments'),
                    'fields' => array(
                        array(
                            'key' => 'company_logo',
                            'label' => __('Compay Logo', 'sendtrace-shipments'),
                            'type' => 'upload',
                            'value' => $sendtrace->get_setting('general', 'company_logo'),
                            'setting' => 'general',
                            'group_class' => 'mb-0'
                        ),
                        array(
                            'key' => 'bg_color',
                            'label' => __('Background Color', 'sendtrace-shipments'),
                            'type' => 'color',
                            'class' => 'form-control-color',
                            'value' => wpst_bg_color(),
                            'setting' => 'general'
                        ),
                        array(
                            'key' => 'fg_color',
                            'label' => __('Foreground Color', 'sendtrace-shipments'),
                            'type' => 'color',
                            'class' => 'form-control-color',
                            'value' => wpst_fg_color(),
                            'setting' => 'general'
                        )
                    )
                ),
                array(
                    'heading' => __('Shipment', 'sendtrace-shipments'),
                    'fields' => array(
                        array(
                            'key' => 'tracking_page',
                            'label' => __('Tracking Page', 'sendtrace-shipments'),
                            'type' => 'select',
                            'class' => 'selectize',
                            'options' =>  $pages,
                            'value' => wpst_get_tracking_page_id(),
                            'description' => 'This will insert shortcode <strong>[sendtrace_form]</strong> into page.',
                            'setting' => 'general'
                        ),
                        array(
                            'key' => 'auto_generate',
                            'label' => __('Auto Generate Tracking No.?', 'sendtrace-shipments'),
                            'type' => 'radio',
                            'options' => array('Yes', 'No'),
                            'value' => $sendtrace->get_setting('general', 'auto_generate', 'Yes'),
                            'setting' => 'general'
                        ),
                        array(
                            'key' => 'tracking_no_length',
                            'label' => __('Tracking No. Length', 'sendtrace-shipments'),
                            'type' => 'number',
                            'value' => !empty($sendtrace->get_setting('general', 'tracking_no_length')) ? $sendtrace->get_setting('general', 'tracking_no_length') : 8,
                            'setting' => 'general',
                            'description' => '<strong>Note:</strong> Prefix and Suffix are not include.'
                        ),
                        array(
                            'key' => 'tracking_no_prefix',
                            'label' => __('Tracking No. Prefix', 'sendtrace-shipments'),
                            'type' => 'text',
                            'value' => !empty($sendtrace->get_setting('general', 'tracking_no_prefix')) ? $sendtrace->get_setting('general', 'tracking_no_prefix') : 'SHIP',
                            'setting' => 'general'
                        ),
                        array(
                            'key' => 'tracking_no_suffix',
                            'label' => __('Tracking No. Suffix', 'sendtrace-shipments'),
                            'type' => 'text',
                            'value' => $sendtrace->get_setting('general', 'tracking_no_suffix'),
                            'setting' => 'general'
                        ),
                        array(
                            'key' => 'status_list',
                            'label' => __('Status Options', 'sendtrace-shipments'),
                            'type' => 'select',
                            'class' => 'selectize rounded border form-control selectize-set-min-height',
                            'options' => $sendtrace->status_list(),
                            'value' => $sendtrace->status_list(),
                            'setting' => 'general',
                            'extras' => 'multiple data-allow_create=true data-has_remove=true style=min-height:55px'
                        ),
                        array(
                            'key' => 'package_types',
                            'label' => __('Package Types', 'sendtrace-shipments'),
                            'type' => 'select',
                            'class' => 'selectize rounded border form-control selectize-set-min-height',
                            'options' => $sendtrace->package_types(),
                            'value' => $sendtrace->package_types(),
                            'setting' => 'general',
                            'extras' => 'multiple data-allow_create=true data-has_remove=true style=min-height:55px'
                        ),
                        array(
                            'key' => 'transportation_mode',
                            'label' => __('Transportation Mode', 'sendtrace-shipments'),
                            'type' => 'select',
                            'class' => 'selectize rounded border form-control selectize-set-min-height',
                            'options' => $sendtrace->shipment_types(),
                            'value' => $sendtrace->shipment_types(),
                            'setting' => 'general',
                            'extras' => 'multiple data-allow_create=true data-has_remove=true style=min-height:55px'
                        ),
                        array(
                            'key' => 'carriers',
                            'label' => __('Carrier Options', 'sendtrace-shipments'),
                            'type' => 'select',
                            'class' => 'selectize rounded border form-control selectize-set-min-height',
                            'options' => $sendtrace->carriers(),
                            'value' => $sendtrace->carriers(),
                            'setting' => 'general',
                            'extras' => 'multiple data-allow_create=true data-has_remove=true style=min-height:55px'
                        ),
                        array(
                            'key' => 'roles_modify_history',
                            'label' => __('Roles can modify shipment history', 'sendtrace-shipments'),
                            'type' => 'checkbox',
                            'options' => wpst_get_user_roles(),
                            'value' => $sendtrace->get_setting('general', 'roles_modify_history'),
                            'setting' => 'general'
                        )
                    )
                ),
                array(
                    'heading' => __('Multiple Pacakge', 'sendtrace-shipments'),
                    'fields' => array(
                        array(
                            'key' => 'weight_unit',
                            'label' => __('Weight Unit', 'sendtrace-shipments'),
                            'type' => 'select',
                            'class' => 'w-100',
                            'options' => $sendtrace->get_weight_units(),
                            'value' => $sendtrace->weight_unit_used(),
                            'setting' => 'general'
                        ),
                        array(
                            'key' => 'dim_unit',
                            'label' => __('Dimension Unit', 'sendtrace-shipments'),
                            'type' => 'select',
                            'class' => 'w-100',
                            'options' => $sendtrace->get_area_units(),
                            'value' => $sendtrace->dim_unit_used(),
                            'setting' => 'general'
                        ),
                        array(
                            'key' => 'volumetric_weight_divisor',
                            'label' => __('Volumetric Weight Dvisor', 'sendtrace-shipments'),
                            'description' => '<strong>Note</strong>: Use to get volumetric weight: (L*W*H) / Divisor',
                            'type' => 'text',
                            'value' => $sendtrace->get_volumetric_weight_divisor(),
                            'setting' => 'general'
                        ),
                    )
                ),
                array(
                    'heading' => __('Fees', 'sendtrace-shipments'),
                    'fields' => array(
                        array(
                            'key' => 'tax',
                            'label' => __('Tax in (%)', 'sendtrace-shipments'),
                            'description' => '<strong>Note</strong>: Apply to all payment transactions.',
                            'type' => 'number',
                            'value' => $sendtrace->get_setting('general', 'tax', 0),
                            'extras' => 'step=any',
                            'setting' => 'general'
                        ),
                    )
                )
            ),
            'email_admin' => array(
                array(
                    'heading' => __('Admin Email Setting', 'sendtrace-shipments'),
                    'fields' => array(
                        array(
                            'key' => 'admin_enable',
                            'label' => __('Enable?', 'sendtrace-shipments'),
                            'type' => 'radio',
                            'required' => true,
                            'class' => '',
                            'group_class' => 'form-check-inline',
                            'options' => array('Yes', 'No'),
                            'value' => $sendtrace->get_setting('email_admin', 'admin_enable', 'Yes'),
                            'setting' => 'email_admin'
                        ),
                        array(
                            'key' => 'admin_mail_to',
                            'label' => __('Mail To', 'sendtrace-shipments'),
                            'type' => 'select',
                            'required' => true,
                            'class' => 'selectize',
                            'options' => $sendtrace->get_setting('email_admin', 'admin_mail_to', array($admin_default_email)),
                            'value' => $sendtrace->get_setting('email_admin', 'admin_mail_to', $admin_default_email),
                            'placeholder' => 'sample@gmail.com',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_cc',
                            'label' => __('Cc', 'sendtrace-shipments'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $sendtrace->get_setting('email_admin', 'admin_cc',  array()),
                            'value' => $sendtrace->get_setting('email_admin', 'admin_cc'),
                            'placeholder' => 'sample@gmail.com',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_bcc',
                            'label' => __('Bcc', 'sendtrace-shipments'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $sendtrace->get_setting('email_admin', 'admin_bcc',  array()),
                            'value' => $sendtrace->get_setting('email_admin', 'admin_bcc'),
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'placeholder' => 'sample@gmail.com',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_subject',
                            'label' => __('Subject', 'sendtrace-shipments'),
                            'type' => 'text',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $sendtrace->get_setting('email_admin', 'admin_subject', 'New Booking'),     
                            'placeholder' => 'New Booking',                       
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_body',
                            'label' => __('Body', 'sendtrace-shipments'),
                            'type' => 'textarea',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $sendtrace->get_setting_html('email_admin', 'admin_body', ''),     
                            'placeholder' => wpst_get_default_admin_mail_body(),                       
                            'extras' => 'rows=6',
                            'allow_html' => true,
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_footer',
                            'label' => __('Footer', 'sendtrace-shipments'),
                            'type' => 'textarea',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $sendtrace->get_setting_html('email_admin', 'admin_footer'),
                            'placeholder' => wpst_get_default_admin_mail_footer(),
                            'extras' => 'rows=4',
                            'allow_html' => true,
                            'setting' => 'email_admin',
                        )
                    )
                )
            ),
            'email_client' => array(
                array(
                    'heading' => esc_html__('Client Email Setting', 'sendtrace-shipments'),
                    'fields' => array(
                        array(
                            'key' => 'client_enable',
                            'label' => __('Enable?', 'sendtrace-shipments'),
                            'type' => 'radio',
                            'required' => true,
                            'class' => '',
                            'group_class' => 'form-check-inline',
                            'options' => array('Yes', 'No'),
                            'value' => $sendtrace->get_setting('email_client', 'client_enable', 'Yes'),
                            'setting' => 'email_client'
                        ),
                        array(
                            'key' => 'enabled_statuses',
                            'label' => esc_html__('Send when status:', 'wpcb_booking'),
                            'type' => 'checkbox',
                            'options' => $status_list,
                            'value' => wpst_send_client_email_in_status_list(),
                            'setting' => 'email_client',
                            'group_class' => 'form-check-inline'
                        ),
                        array(
                            'key' => 'client_mail_to',
                            'label' => __('Mail To', 'sendtrace-shipments'),
                            'type' => 'select',
                            'required' => true,
                            'class' => 'selectize',
                            'options' => $sendtrace->get_setting('email_client', 'client_mail_to', array('{wpst_shipper_email}')),
                            'value' => $sendtrace->get_setting('email_client', 'client_mail_to', '{wpst_shipper_email}'),
                            'placeholder' => 'sample@gmail.com',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_cc',
                            'label' => __('Cc', 'sendtrace-shipments'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $sendtrace->get_setting('email_client', 'client_cc', array()),
                            'value' => $sendtrace->get_setting('email_client', 'client_cc'),
                            'placeholder' => 'sample@gmail.com',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_bcc',
                            'label' => __('Bcc', 'sendtrace-shipments'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $sendtrace->get_setting('email_client', 'client_bcc', array()),
                            'value' => $sendtrace->get_setting('email_client', 'client_bcc'),
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'placeholder' => 'sample@gmail.com',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_subject',
                            'label' => __('Subject', 'sendtrace-shipments'),
                            'type' => 'text',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $sendtrace->get_setting('email_client', 'client_subject', 'Shipment Tracking No. #{tracking_no}'),     
                            'placeholder' => 'Shipment Tracking No. #{tracking_no}',                       
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_body',
                            'label' => __('Body', 'sendtrace-shipments'),
                            'type' => 'textarea',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $sendtrace->get_setting_html('email_client', 'client_body'),     
                            'placeholder' => wpst_get_default_client_mail_body(),                       
                            'extras' => 'rows=6',
                            'allow_html' => true,
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_footer',
                            'label' => __('Footer', 'sendtrace-shipments'),
                            'type' => 'textarea',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $sendtrace->get_setting_html('email_client', 'client_footer'),
                            'placeholder' => wpst_get_default_client_mail_footer(),
                            'extras' => 'rows=4',
                            'allow_html' => true,
                            'setting' => 'email_client',
                        )
                    )
                )
            ),
            'address_book' => array(
                array(
                    'heading' => esc_html__('Fields use for searching', 'sendtrace-shipments'),
                    'fields' => array(
                        array(
                            'key' => 'shipper_search_field',
                            'label' => __('Shipper', 'sendtrace-shipments'),
                            'type' => 'select',
                            'required' => true,
                            'class' => '',
                            'group_class' => 'form-check-inline',
                            'options' => $this->shipment_fields_key_label_pair('shipper_information'),
                            'value' => wpst_ab_shipper_search_field(),
                            'setting' => 'address_book'
                        ),
                        array(
                            'key' => 'receiver_search_field',
                            'label' => __('Receiver', 'sendtrace-shipments'),
                            'type' => 'select',
                            'required' => true,
                            'class' => '',
                            'group_class' => 'form-check-inline',
                            'options' => $this->shipment_fields_key_label_pair('receiver_information'),
                            'value' => wpst_ab_receiver_search_field(),
                            'setting' => 'address_book'
                        ),
                    )
                )
            )
        );
        return apply_filters('wpst_settings_field', $fields);
    }

    function multiple_package() {
        global $sendtrace;
        $fields = array(
            'qty' => array(
                'key' => 'qty',
                'label' => __('Qty', 'sendtrace-shipments'),
                'type' => 'number',
                'class' => 'qty',
                'unit' => 'pcs',
                'order' => 1
            ),
            'package_type' => array(
                'key' => 'package_type',
                'label' => __('Package Type', 'sendtrace-shipments'),
                'type' => 'select',
                'options' => $sendtrace->package_types(),
                'field_col' => '',
                'class' => 'package_type',
                'order' => 2
            ),
            'weight' => array(
                'key' => 'weight',
                'label' => __('Weight', 'sendtrace-shipments'),
                'type' => 'number',
                'extras' => 'step=any min=0',
                'class' => 'weight',
                'unit' => $sendtrace->weight_unit_used(),
                'order' => 3
            ),
            'length' => array(
                'key' => 'length',
                'label' => __('Length', 'sendtrace-shipments'),
                'type' => 'number',
                'extras' => 'step=any min=0',
                'class' => 'length',
                'unit' => $sendtrace->dim_unit_used(),
                'order' => 4
            ),
            'width' => array(
                'key' => 'width',
                'label' => __('Width', 'sendtrace-shipments'),
                'type' => 'number',
                'extras' => 'step=any min=0',
                'class' => 'width',
                'unit' => $sendtrace->dim_unit_used(),
                'order' => 5
            ),
            'height' => array(
                'key' => 'height',
                'label' => __('Height', 'sendtrace-shipments'),
                'type' => 'number',
                'extras' => 'step=any min=0',
                'class' => 'height',
                'unit' => $sendtrace->dim_unit_used(),
                'order' => 6
            )
        );
        $fields = apply_filters('wpst_multiple_package', $fields);
        if (!empty($fields)) {
            foreach ($fields as $key => $field) {
                if (!array_key_exists('order', $field)) {
                    $field['order'] = 0;
                }
                $fields[$key] = $field;
            }
            
            $orders = array_column($fields, 'order');
            array_multisort($orders, SORT_ASC, $fields);
        }
        
        return $fields;
    }

    function history_fields($shipment_id=0, $side_bar=false) {
        global $sendtrace;
        $status_label = $side_bar ? __('New Status', 'sendtrace-shipments') : __('Status', 'sendtrace-shipments');
        $fields = array(
            'sendtrace_status' => array(
                'key' => 'sendtrace_status',
                'label' => $status_label,
                'type' => 'select',
                'options' => $sendtrace->status_list(),
                'value' => !$shipment_id ? wpst_get_default_status() : '',
            ),
            'sendtrace_datetime' => array(
                'key' => 'sendtrace_datetime',
                'label' => __('Date Time', 'sendtrace-shipments'),
                'type' => 'text',
                'class' => 'wpst-datetimepicker',
                'value' => '',
            ),
            'remarks' => array(
                'key' => 'remarks',
                'label' => __('Remarks', 'sendtrace-shipments'),
                'type' => 'textarea',
                'value' => '',
                'extras' => 'rows=auto'
            ),
            'updated_by' => array(
                'key' => 'updated_by',
                'label' => __('Updated By', 'sendtrace-shipments'),
                'type' => 'text',
                'value' => '',
                'extras' => 'readonly'
            ),
        );
        if ($side_bar) {
            unset($fields['sendtrace_datetime']);
            unset($fields['updated_by']);
        }
        return apply_filters('wpst_history_fields', $fields);
    }

    function shipment_list_columns() {
        $columns = array(
            'checkbox' => array(
                'key'=>'checkbox',
                'label' => '<input class="form-check-input m-0 select-all" type="checkbox" id="check-all"/>',
                'class' => 'cb-item'
            ),
            'tracking_no' => array(
                'key' => 'tracking_no',
                'label' => 'Tracking No',
                'class' => 'tracking-no',
                'extras' => 'shipment-{shipment_id}'
            ),
            'shipper' => array(
                'key' => wpst_customer_field('shipper', 'key'),
                'label' => wpst_customer_field('shipper', 'label'),
                'class' => 'shipper',
            ),
            'receiver' => array(
                'key' => wpst_customer_field('receiver', 'key'),
                'label' => wpst_customer_field('receiver', 'label'),
                'class' => 'receiver',
            ),
            'sendtrace_status' => array(
                'key' => 'sendtrace_status',
                'label' => 'Status',
                'class' => 'status'
            ),
            'date_created' => array(
                'key' => 'date_created',
                'type' => 'date',
                'label' => 'Date Created',
                'class' => 'date-created'
            ),
        );
        return apply_filters('wpst_shipment_list_columns', $columns);
    }
}

$WPSTField = new WPSTField;
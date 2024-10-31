<?php

class SendTrace
{
    public $files_dir_path = WPST_PLUGIN_PATH. 'tmp/';
    public $files_dir_url = WPST_PLUGIN_URL. 'tmp/';
    public $tax;

    function __construct()
    {
        add_shortcode('sendtrace_form', array($this, 'wpst_tracking_form'));
        add_action('admin_init', array($this, 'wpst_admin_nonce_form'));
        add_action('admin_init', array($this, 'wpst_post_type_nonce_form'));
        $this->tax = $this->get_setting('general', 'tax', 0);
    }

    function clean_files($extension='pdf')
    {
        foreach(glob($this->files_dir_path.'*.'.$extension) as $file){
            unlink($file);
        }
    }

    function generate_tracking_no()
    {
        global $sendtrace;
        $prefix = $sendtrace->get_setting('general', 'tracking_no_prefix', 'SHIP');
        $suffix = $sendtrace->get_setting('general', 'tracking_no_suffix');
        $length = $sendtrace->get_setting('general', 'tracking_no_length', 8);
        $min_num = str_pad('1', $length, '1');
        $max_num = str_pad('9', $length, '9');
        $track_no = $prefix.wp_rand($min_num, $max_num).$suffix;
        while ($sendtrace->is_tracking_no_exist($track_no)) {
            $track_no = $prefix.wp_rand($min_num, $max_num).$suffix;
        }
        $track_no = apply_filters('wpst_generate_tracking_no', $track_no);
        return wpst_sanitize_data($track_no);
    }

    function is_tracking_no_exist($traking_no)
    {
        global $wpdb;
        $sql = "SELECT ID FROM `{$wpdb->prefix}posts` WHERE post_type = 'sendtrace' AND post_title = %s";
        $result = $wpdb->get_var($wpdb->prepare($sql, $traking_no));
        return !empty($result);
    }

    function is_sendtrace_post($post_id)
    {
        global $wpdb;
        $sql = "SELECT ID FROM `{$wpdb->prefix}posts` WHERE post_type = 'sendtrace' AND ID = %d";
        $result = $wpdb->get_var($wpdb->prepare($sql, $post_id));
        return !empty($result);
    }

    function wpst_tracking_form()
    {
        global $WPSTField;
        $show_form = true;
        $tracking_no = isset($_POST['tracking_no']) ? wpst_sanitize_data($_POST['tracking_no']) : '';
        if (empty($tracking_no) && isset($_GET['tracking_no'])) {
            $tracking_no = wpst_sanitize_data($_GET['tracking_no']);
            $show_form = false;
        }
        ob_start();
        echo "<div class='sendtrace'>";
        if ($show_form) {
            require_once wpst_get_template('track-form.tpl');
        }
                
        if (
            (isset($_POST['sendtrace_trackform_field']) && wp_verify_nonce($_POST['sendtrace_trackform_field'], 'sendtrace_trackform_action'))
            || (isset($_GET['tracking_no']) && isset($_GET['action']) && in_array($_GET['action'], array('track', 'view')))
        ) {
            $shipment = get_page_by_title($tracking_no, OBJECT, 'sendtrace');
            if (empty($shipment)) {
                echo "<h3 class='h3 text-danger text-center mt-5'>No Result Found.</h3>";
            } else {
                $shipment_id = $shipment->ID;
                $shipment_data = $WPSTField->fields($shipment_id);
                echo "<div id='track-result'>";
                    do_action('wpst_track_result', $shipment_id, $shipment_data);
                echo "</div>";
            }
        }
        echo "</div>";
        return ob_get_clean();
    }

    function field_class()
    {
        $field_class = array(
            'text' => 'form-control',
            'password' => 'form-control',
            'number' => 'form-control',
            'email' => 'form-control',
            'textarea' => 'form-control',
            'select' => 'browser-default form-select',
            'checkbox' => 'form-check-input',
            'radio' => 'form-check-input',
            'link' => 'btn btn-link',
            'button' => 'btn btn-primary',
            'date' => 'form-control wpst-datepicker',
            'time' => 'form-control'
        );
        return apply_filters('wpst_field_class', $field_class);
    }

    function status_list()
    {
        global $sendtrace;
        $general =  $sendtrace->get_setting('general');
        $status_list_default = array(
            'Cancelled',
            'Delivered',
            'En Route',
            'In Transit',
            'Pending',
            'Returned'
        );
        $status_list = !empty($general) && array_key_exists('status_list', $general) ? $general['status_list'] : $status_list_default;
        if (!empty(wpst_get_default_status()) && !in_array(wpst_get_default_status(), $status_list)) {
            $status_list[] = wpst_get_default_status();
        } 
        sort($status_list);       
        return $status_list;
    }

    function shipment_types()
    {
        global $sendtrace;
        $general =  $sendtrace->get_setting('general');
        $shipment_types_default = array(
            'Air Freight',
            'International Freight',
            'Sea Freight',
            'Truckload'            
        );
        $shipment_types = !empty($general) && array_key_exists('transportation_mode', $general) ? $general['transportation_mode'] : $shipment_types_default;
        return $shipment_types;
    }

    function carriers()
    {
        global $sendtrace;
        $general =  $sendtrace->get_setting('general');
        $carriers_default = array(
            'DHL',
            'UPS',
            'FedEx'            
        );
        $carriers = !empty($general) && array_key_exists('carriers', $general) ? $general['carriers'] : $carriers_default;        
        return $carriers;
    }

    function package_types()
    {
        global $sendtrace;
        $general =  $sendtrace->get_setting('general');
        $types_default = array(
            'Carton',
            'Pallet',
            'Woven',
            'Bag',            
            'Others'
        );
        $types = !empty($general) && array_key_exists('package_types', $general) ? $general['package_types'] : $types_default;    
        return $types;
    }

    function export_file_format_list(){
        $extension = array(
            'xls' => ",", 
            'xlt' => ",", 
            'xla' => ",", 
            'xlw' => ",",
            'csv' => ","
        );
        return apply_filters( 'wpst_export_file_format_list', $extension );
    }

    function settings_menu()
    {
        $settings_menu = array(
            'general' => array(
                'label' => __('General Setting', 'sendtrace-shipments'),
                'file_path' => wpst_get_template('settings/general.tpl', true),
                'in_save_setting' => true
            ),
            'email' => array(
                'label' => __('Email Setting', 'sendtrace-shipments'),
                'file_path' => wpst_get_template('settings/email.tpl', true),
                'in_save_setting' => true
            ),
            'address_book' => array(
                'label' => __('Address Book', 'sendtrace-shipments'),
                'file_path' => wpst_get_template('settings/address-book.tpl', true),
                'in_save_setting' => true
            )
        );
        return apply_filters('wpst_settings_menu', $settings_menu);
    }

    function setting_keys()
    {
        $setting_keys = [];
        if (!empty($this->settings_menu())) {
            foreach ($this->settings_menu() as $menu => $option) {
                if (in_array('in_save_setting', $option) && $option['in_save_setting']) {
                    if ($menu == 'email') {
                        $setting_keys[] = 'email_admin';
                        $setting_keys[] = 'email_client';
                    } else {
                        $setting_keys[] = $menu;
                    }                    
                }
            }
        }
        return wpst_sanitize_data(apply_filters('wpst_setting_keys', $setting_keys));
    }

    function wpst_settings()
    {
        $wpst_settings = get_option('wpst_settings');
        return !empty($wpst_settings) ? $wpst_settings : array();
    }

    function update_setting($setting_key, $value)
    {
        $wpst_settings = $this->wpst_settings();
        $wpst_settings[$setting_key] = $value;
        update_option('wpst_settings', $wpst_settings);
    }

    function update_setting_field($setting_key, $field_key, $field_value)
    {
        $wpst_settings = $this->wpst_settings();
        $wpst_settings[$setting_key][$field_key] = $field_value;
        update_option('wpst_settings', $wpst_settings);
    }

    function get_setting($setting_key='', $field='', $default_value='', $all_fields=false)
    {
        $wpst_settings = $this->wpst_settings();
        $result = ($all_fields) ? $wpst_settings : $default_value;
        if (!empty($wpst_settings)) {
            if (empty($result) && empty($field)) {
                $result = array();         
            }
            if (array_key_exists($setting_key, $wpst_settings)) {
                $settings = $wpst_settings[$setting_key];
                if (array_key_exists($field, $settings) && !empty($settings[$field])) {
                    $result = $settings[$field];
                } else if (!empty($setting_key) && empty($field)){
                    $result = $settings;
                }
            }
        }        
        return wpst_sanitize_data($result);
    }

    function get_setting_html($setting_key='', $field='', $default_value='', $all_fields=false)
    {
        $wpst_settings = $this->wpst_settings();
        $result = ($all_fields) ? $wpst_settings : $default_value;
        if (empty($result) && empty($field)) {
            $result = array();         
        }
        if (!empty($wpst_settings)) {
            if (array_key_exists($setting_key, $wpst_settings)) {
                $settings = $wpst_settings[$setting_key];
                if (array_key_exists($field, $settings) && !empty($settings[$field])) {
                    $result = $settings[$field];
                } else if (!empty($setting_key) && empty($field)){
                    $result = $settings;
                }
            }
        }  
        $html_result = $result;      
        if (!empty($result)) {
            if (is_array($result)) {
                foreach ($result as $key => $_value) {
                    if (!is_array($_value)) {
                        $html_result[$key] = wp_kses($_value, wpst_allowed_html_tags());
                    } else {
                        foreach ($_value as $__key => $__value) {
                            $html_result[$key][$__key] = wp_kses($__value, wpst_allowed_html_tags());
                        }
                    }                    
                }
            } else {
                $html_result = wp_kses($result, wpst_allowed_html_tags());
            }
        }        
        return $html_result;
    }
    
    function wpst_admin_nonce_form()
    {
        global $WPSTField;
        $this->update_setting_field('general', 'tracking_page', 56);
        // Save Settings
        if (
            isset($_POST['wpst_setting_nonce_field']) && 
            wp_verify_nonce($_POST['wpst_setting_nonce_field'], 'wpst_setting_nonce_action')
        ) {
            if (!empty($WPSTField->settings_field())) {
                foreach ($WPSTField->settings_field() as $setting_key => $settings) {
                    if (isset($_POST[$setting_key])) {
                        $setting_value = array();
                        foreach ($settings as $setting) {
                            if (empty($setting['fields'])) {
                                continue;
                            }
                            
                            foreach ($setting['fields'] as $field) {
                                if (array_key_exists($field['key'], $_POST[$setting_key])) {
                                    $setting_val = $_POST[$setting_key][$field['key']];
                                    if (array_key_exists('allow_html', $field) && $field['allow_html']) {
                                        $setting_value[$field['key']] = wp_kses($setting_val, wpst_allowed_html_tags());
                                    } else {
                                        $setting_value[$field['key']] = wpst_sanitize_data($setting_val);
                                    } 
                                    if ($setting_key == 'general' && $field['key'] == 'tracking_page') {
                                        wpst_insert_content_to_page(wpst_sanitize_data($setting_val), "[sendtrace_form]");
                                    }
                                }                           
                            }
                        } 
                        $this->update_setting($setting_key, $setting_value);
                    }                  
                }
            }
            wpst_set_notification('Settings save successfully.');
            do_action('wpst_after_save_settings', $_POST);
        }
    }

    function wpst_post_type_nonce_form() {
        global $WPSTField;
        // Save Shipment Post
        if (
            isset($_POST['sendtrace_post_nonce_field']) && 
            wp_verify_nonce($_POST['sendtrace_post_nonce_field'], 'sendtrace_post_nonce_action')
        ) {
            $post_fields = $WPSTField->fields();
            $shipment_id = 0;
            $old_status = '';

            $action = isset($_POST['action']) ? wpst_sanitize_data($_POST['action']) : 'new';
            if (empty($action) || !in_array($action, ['new', 'edit'])) {
                return false;
            }

            if (isset($_GET['id']) && is_numeric($_GET['id']) && $this->is_sendtrace_post(wpst_sanitize_data($_GET['id']))) {
                $shipment_id = wpst_sanitize_data($_GET['id']);
            }

            $post_title = isset($_POST['post_title']) ? wpst_sanitize_data($_POST['post_title']) : $this->generate_tracking_no();

            if (empty($post_title)) {
                wpst_set_notification('Invalid tracking no.', 'danger', 'info');
                return false;
            }

            if ($this->is_tracking_no_exist($post_title)) {
                if ($action == 'new' || ($action == 'edit' && $post_title != get_the_title($shipment_id))) {
                    wpst_set_notification('Unable to save. Tracking no. <strong>'.esc_html($post_title).'</strong> is already exist.', 'danger', 'info');
                    return false;
                }                
            }

            $post_args = array(
                'post_title' => $post_title
            );

            if ($action == 'edit') {
                if ($shipment_id) {
                    $post_args['ID'] = $shipment_id;
                    wp_update_post($post_args);
                    $old_status = get_post_meta($shipment_id, 'sendtrace_status',  true);
                }
            } else {
                $post_args['post_type'] = 'sendtrace';
                $post_args['post_status'] = 'publish';
                $post_args['post_author'] = get_current_user_id() ?? 0;
                $shipment_id = wp_insert_post($post_args);
            }

            if (!$shipment_id) {
                wpst_set_notification('Unable to save', 'danger', 'info');
                return false;
            }

            // Save fields
            if (!empty($post_fields)) {
                foreach ($post_fields as $section_key => $section) {
                    if (!empty($section['fields'])) {
                        foreach ($section['fields'] as $field_key => $field) {
                            if (isset($_POST[$field_key])) {
                                update_post_meta($shipment_id, $field_key, wpst_sanitize_data($_POST[$field_key]));
                            }
                        }
                    }
                }
            }

            // Save sendtrace status
            if (isset($_POST['sendtrace_status']) && !empty($_POST['sendtrace_status'])) {
                update_post_meta($shipment_id, 'sendtrace_status', wpst_sanitize_data($_POST['sendtrace_status']));
            } else if ($action == 'new') {
                update_post_meta($shipment_id, 'sendtrace_status', wpst_get_default_status());
            }

            $current_role = wpst_get_user_role();
            $wpst_roles = wpst_get_user_roles();
            if (strtolower($action) == 'new' && array_key_exists($current_role, $wpst_roles)) {
                $self_role = strtolower(str_replace('sendtrace_', '', $current_role));
                update_post_meta($shipment_id, "assigned_{$self_role}", get_current_user_id());
            }

            // Save sendtrace assigned users
            if (isset($_POST['assigned_client'])) {
                update_post_meta($shipment_id, 'assigned_client', wpst_sanitize_data($_POST['assigned_client']));
            }
            if (isset($_POST['assigned_agent'])) {
                update_post_meta($shipment_id, 'assigned_agent', wpst_sanitize_data($_POST['assigned_agent']));
            }
            if (isset($_POST['assigned_editor'])) {
                update_post_meta($shipment_id, 'assigned_editor', wpst_sanitize_data($_POST['assigned_editor']));
            }

            // Save shipment type
            $prev_shipment_type = get_post_meta($shipment_id, 'shipment_type', true);
            $shipment_type = 'default';
            if (isset($_POST['shipment_type']) && !empty($_POST['shipment_type'])) {
                $shipment_type = wpst_sanitize_data($_POST['shipment_type']);
            }
            if (!$prev_shipment_type) {
                update_post_meta($shipment_id, 'shipment_type', $shipment_type);
            }

            $notif_action = $action == 'edit' ? 'updated' : 'added';
            $booking_title = $shipment_id ? get_the_title($shipment_id) : '';
            wpst_set_notification("<strong>{$booking_title}</strong> {$notif_action} successfully!");
            do_action('wpst_after_save_sendtrace_post', $shipment_id, $_POST, $old_status);
            do_action('wpst_after_save_sendtrace_post_send_email', $shipment_id, $_POST, $old_status);
        }
    }

    function get_shortcode_list()
    {
        global $WPSTField;
        $shortcodes = array(
            'general' => array(
                '{tracking_no}' => 'Shpment Number',
                '{sendtrace_status}' => 'Shipment Status',
            )
        );
        if (!empty($WPSTField->fields())) {
            foreach ($WPSTField->fields() as $section_key => $section) {
                if (empty($section)) {
                    continue;
                }

                foreach ($section['fields'] as $field_key => $field) {
                    $shortcodes[ $section['heading']]['{'.$field_key.'}'] = $field['label'];
                }                
            }
        }
        return apply_filters('wpst_shortcode_list', $shortcodes);
    }

    function get_shortcode_values($shipment_id)
    {
        global $sendtrace, $WPSTField;
        $shipment_fields = $WPSTField->shipment_fields_only();
        $meta_values = wpst_get_shipment_meta_details($shipment_id);
        $shortcodes_list = $this->get_shortcode_list();
        $shortcodes_data = [];
        if (!empty($shortcodes_list)) {
            foreach ($shortcodes_list as $heading => $shortcodes) {
                if (empty($shortcodes)) { continue; }
                foreach ($shortcodes as $shortcode => $description) {
                    $shortcode = str_replace(['{','}'], '', $shortcode);
                    $shortcode_value = array_key_exists($shortcode, $meta_values) ? $meta_values[$shortcode] : '{'.$shortcode.'}';
                    if ($shortcode == 'tracking_no') {
                        $shortcode_value = get_the_title($shipment_id);
                    }
                    if (is_array($shortcode_value)) {
                        if (array_key_exists($shortcode, $shipment_fields) && $shipment_fields[$shortcode]['type'] == 'address') {
                            $shortcode_value = implode(', ', array_filter($shortcode_value));
                        } else {
                            $str_value = "<ul style='list-style-type: disc; list-style-position: inside;'>";
                            foreach ($shortcode_value as $_value) {
                                $_value = is_array($_value) ? implode(', ', array_filter($_value)) : $_value;
                                $str_value .= "<li>".esc_html($_value)."</li>";
                            }
                            $str_value .= '</ul>';
                            $shortcode_value = $str_value;
                        }
                        
                    }
                    $shortcodes_data['{'.$shortcode.'}'] = $shortcode_value;
                }
            }
        }
        return apply_filters('wpst_shortcode_values', $shortcodes_data, $shipment_id);
    }

    function draw_shortcode_list($container_class='col-12')
    {
        $shortcodes_list = $this->get_shortcode_list();
        echo "<div class='row'>";
            echo "<div id='shortcode-list' class='" .esc_html($container_class). "'>";
                echo "<table class='table table-bordered'>";
                    echo "<thead>";
                        echo "<tr>";
                            echo "<td class='p-2'><strong>".__('Shortcode', 'sendtrace-shipments')."</strong></td>";
                            echo "<td class='p-2'><strong>".__('Description', 'sendtrace-shipments')."</strong></td>";
                        echo "<tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    if (!empty($shortcodes_list)) {
                        foreach ($shortcodes_list as $heading => $shortcodes) {
                            $heading = ucwords(str_replace('_', ' ', $heading));
                            echo "<tr class='heading'>";
                                echo "<td colspan='2' class='p-2'><strong>".esc_html($heading)."</strong></td>";
                            echo "</tr>";
                            foreach ($shortcodes as $shortcode => $description) {
                                echo "<tr>";
                                    echo "<td width='40%' class='p-2'><span class='shortcode'> ".esc_html($shortcode)." </span></td>";
                                    echo "<td width='60%' class='p-2'> ".esc_html($description)." </td>";
                                echo "</tr>";
                            }
                        }
                    }
                    echo "</tbody>";
                echo "</table>";
            echo "</div>";
        echo "</div>";
    }
    
    function get_shipment_details($shipment_id)
    {
        global $wpdb;
        $sql = "SELECT * FROM `{$wpdb->prefix}postmeta` WHERE post_id = %d";
        $results = $wpdb->get_results($wpdb->prepare($sql, $shipment_id));
        $details = [
            'tracking_no' => get_the_title($shipment_id),
            'date_created' => get_the_date(wpst_date_format())
        ];
        if (!empty($results)) {
            foreach ($results as $result) {
                $details[$result->meta_key] = maybe_unserialize($result->meta_value);
            }
        }
        return $details;
    }

    function dim_unit_used()
    {
        global $sendtrace;
        return $sendtrace->get_setting('general', 'dim_unit', 'cm');;
    }
    function weight_unit_used()
    {
        return $this->get_symbol_unit('weight');
    }

    function get_weight_units()
    {
        $units = array(
            'g' => 'Grams (g)',
            'lbs' => 'Pound (lbs)',
            'kg' => 'Kilogram (kg)',
            't' => 'Tonne (t)'
        );
        return apply_filters('measurement_units', $units);
    }

    function get_area_units()
    {
        $units = array(
            'cm' => 'Centimeter (cm)',
            'ft' => 'Foot (ft)',
            'in' => 'Inch (in)',
            'm' => 'Meter (m)',
            'mm' => 'Millimeter (mm)',
            'mi' => 'Miles (mi)',
            'yd' => 'Yard (yd)'
        );
        return apply_filters('wpst_area_units', $units);
    }

    function get_symbol_unit($unit)
    {
        global $sendtrace;
        $symbol = '';
        switch ($unit) {
            case 'weight':
                $symbol = $sendtrace->get_setting('general', 'weight_unit', 'kg');
                break;
            case 'melimeter':
                $symbol = 'mm';
                break;
            case 'centimeter':
                $symbol = 'cm';
                break;
            case 'meter':
                $symbol = 'm';
                break;
            case 'feet':
                $symbol = 'ft';
                break;
            case 'yard':
                $symbol = 'yd';
                break;
        }
        return apply_filters('wpst_symbol_unit', $symbol);
    }
    
    function cubic_meter_divisor($unit)
    {
        $divisor = 1;
        switch ($unit) {
            case 'm':
                $divisor = 1;
                break;
            case 'cm':
                $divisor = 1000000;
                break;
            case 'mm':
                $divisor = 1000000000;
                break;
            case 'in':
                $divisor = 61023.744095;
                break;
            case 'ft':
                $divisor = 35.314667;
                break;
            case 'yd':
                $divisor = 1.307951;
                break;
        }
        return apply_filters('cubic_unit_divisor', $divisor, $unit);
    }

    function get_volumetric_weight_divisor()
    {
        global $sendtrace;
        return $sendtrace->get_setting('general', 'volumetric_weight_divisor', 5000);
    }

    function get_cubic_meter_divisor()
    {
        return $this->cubic_meter_divisor($this->dim_unit_used());
    }

    function get_package_totals($shipment_id, $include_units=false)
    {
        $totals = array(
            'cubic' => 0,
            'volumetric_weight' => 0,
            'actual_weight'=> 0
        );
        if ($include_units) {
            $totals = array(
                'cubic' => array(
                    'value' => 0,
                    'unit' => $this->get_symbol_unit('meter').'<sup>3</sup>'
                ),
                'volumetric_weight' => array(
                    'value' => 0,
                    'unit' => $this->get_symbol_unit('weight')
                ),
                'actual_weight'=> array(
                    'value' => 0,
                    'unit' => $this->get_symbol_unit('weight')
                )
            );
        }
        $packages = $shipment_id ? get_post_meta($shipment_id, 'multiple-package', true) : array();
        if (!empty($packages)) {
            foreach ($packages as $pkg) {
                if (empty(array_filter($pkg))) {
                    continue;
                }
                if (!$pkg['qty']) {
                    $pkg['qty'] = 1;
                }
                $item_cubic = $pkg['qty'] * (($pkg['length'] * $pkg['width'] * $pkg['height']) / $this->get_cubic_meter_divisor());
                $item_volumetric = $pkg['qty'] * (($pkg['length'] * $pkg['width'] * $pkg['height']) / $this->get_volumetric_weight_divisor());
                $item_actual = $pkg['qty'] * $pkg['weight'];

                if ($include_units) {
                    $totals['cubic']['value'] += $item_cubic ?? 0;
                    $totals['volumetric_weight']['value'] += $item_volumetric ?? 0;
                    $totals['actual_weight']['value'] += $item_actual ?? 0;
                } else {
                    $totals['cubic'] += $item_cubic ?? 0;
                    $totals['volumetric_weight'] += $item_volumetric ?? 0;
                    $totals['actual_weight'] += $item_actual ?? 0;
                }                
            }
        }

        if ($include_units) {
            $totals['cubic']['value'] = wpst_is_decimal($totals['cubic']['value']) ? wpst_number_format($totals['cubic']['value'], false, 3) : $totals['cubic']['value'];
            $totals['volumetric_weight']['value'] = wpst_is_decimal($totals['volumetric_weight']['value']) ? wpst_number_format($totals['volumetric_weight']['value']) : $totals['volumetric_weight']['value'];
            $totals['actual_weight']['value'] = wpst_is_decimal($totals['actual_weight']['value']) ? wpst_number_format($totals['actual_weight']['value']) : $totals['actual_weight']['value'];
        } else {
            $totals['cubic'] = wpst_is_decimal($totals['cubic']) ? wpst_number_format($totals['cubic'], false, 3) : $totals['cubic'];
            $totals['volumetric_weight'] = wpst_is_decimal($totals['volumetric_weight']) ? wpst_number_format($totals['volumetric_weight']) : $totals['volumetric_weight'];
            $totals['actual_weight'] = wpst_is_decimal($totals['actual_weight']) ? wpst_number_format($totals['actual_weight']) : $totals['actual_weight'];
        }

        return apply_filters('wpst_package_totals', $totals);
    }

    function get_barcode_bar_size()
    {
        $sizes = array(
            'invoice' => array(
                'width' => 4,
                'height' => 70
            ),
            'waybill' => array(
                'width' => 4,
                'height' => 60
            )
        );
        return apply_filters('wpst_barcode_sizes', $sizes);
    }

    function generate_barcode_url($shipment_id, $type)
    {
        require WPST_PLUGIN_PATH. 'vendor/autoload.php';
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $tracking_no = get_the_title($shipment_id);
        $width = !empty($type) && array_key_exists($type, $this->get_barcode_bar_size()) ? $this->get_barcode_bar_size()[$type]['width'] : 2;
        $height = !empty($type) && array_key_exists($type, $this->get_barcode_bar_size()) ? $this->get_barcode_bar_size()[$type]['height'] : 70;
        return 'data:image/png;base64,' . base64_encode($generator->getBarcode($tracking_no, $generator::TYPE_CODE_128, $width, $height));
    }
}

$sendtrace = new SendTrace;
<?php

class WPSTHook
{
    function __construct()
    {
        add_action('wpst_before_bottom_sumbit_btn', array($this, 'wpst_assign_client'));
        add_action('wpst_after_assign_client', array($this, 'wpst_assign_agent'), 10);
        add_action('wpst_after_assign_client', array($this, 'wpst_assign_editor'), 11);
        add_action('wpst_after_shipment_fields', array($this, 'wpst_multiple_package'), 10, 3);
        add_action('wpst_after_shipment_fields', array($this, 'wpst_shipment_history'), 10);
        add_action('wpst_after_save_sendtrace_post', array($this, 'wpst_save_multiple_package'), 10, 3);
        add_action('wpst_after_save_sendtrace_post', array($this, 'wpst_save_shipment_history'), 20, 3);
        add_action('wpst_after_save_sendtrace_post_send_email', array($this, 'wpst_send_admin_email_notification'), 50, 3);
        add_action('wpst_after_save_sendtrace_post_send_email', array($this, 'wpst_send_client_email_notification'), 60, 3);
        add_action('wpst_track_result', array($this, 'wpst_track_result_header'), 10, 2);
        add_action('wpst_track_result', array($this, 'wpst_track_result_status'), 20, 2);
        add_action('wpst_track_result', array($this, 'wpst_track_result_shipper_receiver'), 30, 2);
        add_action('wpst_track_result', array($this, 'wpst_track_result_shipment_info'), 40, 2);
        add_action('wpst_track_result', array($this, 'wpst_track_result_multiple_package'), 50, 2);
        add_action('sendtrace_info', array($this, 'product_offers'));
        add_action('wpst_before_body_shipper_information', array($this, 'wpst_shipper_address_book_auto_fill'));
        add_action('wpst_before_body_receiver_information', array($this, 'wpst_receiver_address_book_auto_fill'));
    }

    // Saving
    function wpst_save_multiple_package($shipment_id, $data, $old_status)
    {
        if ($shipment_id && isset($data['multiple-package'])) {
            update_post_meta($shipment_id, 'multiple-package', $data['multiple-package']);
        }
    }

    function wpst_save_shipment_history($shipment_id, $data, $old_status)
    {
        global $WPSTField;
        $action = isset($data['action']) ? wpst_sanitize_data($data['action']) : 'new';
        if (!$shipment_id || empty($action)) {
            return false;
        }

        $history = isset($data['shipment-history']) ? wpst_sanitize_data($data['shipment-history']) : array();
        $new_history = [];
        if (!empty($WPSTField->history_fields())) {
            foreach ($WPSTField->history_fields($shipment_id) as $field) {
                if (isset($data[$field['key']]) && !empty(wpst_sanitize_data($data[$field['key']]))) {
                    $new_history[$field['key']] = wpst_sanitize_data($data[$field['key']]);
                }
            }
        }

        $new_history = array_filter($new_history);
        if (empty($new_history) && $action == 'new') {
            $new_history = array(
                'sendtrace_status' => wpst_get_default_status(),
                'remarks' => ''
            );
        }
        
        if (!empty($new_history)) {
            $new_history['updated_by'] = is_user_logged_in() ? get_current_user_id() : 0;
            $new_history['sendtrace_datetime'] = date(wpst_datetime_format());
            array_unshift($history, $new_history);
        }
        update_post_meta($shipment_id, 'shipment-history', wpst_sanitize_data($history));
    }

    function wpst_send_admin_email_notification($shipment_id, $data, $old_status)
    {
        $force_send = array_key_exists('force_notification_send', $data) ? wpst_sanitize_data($data['force_notification_send']) : false;
        if (!$force_send && (!$shipment_id || get_post_status($shipment_id) != 'publish' || wpst_is_post_modified($shipment_id))) {
            return false;
        }
        global $sendtrace;
        $site_mail = sanitize_email(get_bloginfo('new_admin_email'));
        if (empty($site_mail)) {
            $site_mail = sanitize_email(get_bloginfo('admin_email'));
        }
        $shortcode_values = $sendtrace->get_shortcode_values($shipment_id);
        $mail_setting = $sendtrace->get_setting_html('email_admin');
        $is_enabled = strtoupper($mail_setting['admin_enable'] ?? 'YES') == 'YES';
        if (!$is_enabled) {
            return false;
        }

        if (!empty($mail_setting) && !empty($shortcode_values)) {
            foreach ($shortcode_values as $shortcode => $shortcode_val) {
                $shortcode_val = sanitize_text_field($shortcode_val);
                foreach ($mail_setting as $setting => $setting_val) {
                    if (empty($setting_val) && in_array($setting, array('admin_body', 'admin_footer'))) {
                        if ($setting == 'admin_body') {
                            $setting_val = wpst_get_default_admin_mail_body();
                        }
                        if ($setting == 'admin_footer') {
                            $setting_val = wpst_get_default_admin_mail_footer();
                        }
                    }
                    $mail_setting[$setting] = str_replace($shortcode, $shortcode_val, $setting_val);
                }
            }            
        }
        
        $mail_to = array_key_exists('admin_mail_to', $mail_setting) ? implode(',', wpst_sanitize_data($mail_setting['admin_mail_to'], 'email')) : $site_mail;
        $mail_to = apply_filters('wpst_admin_mail_to', $mail_to, $shipment_id);
        $cc = array_key_exists('admin_cc', $mail_setting) ? implode(',', wpst_sanitize_data($mail_setting['admin_cc'], 'email')) : '';
        $bcc = array_key_exists('admin_bcc', $mail_setting) ? implode(',', wpst_sanitize_data($mail_setting['admin_bcc'], 'email')) : '';
        $subject = array_key_exists('admin_subject', $mail_setting) ? $mail_setting['admin_subject'] : wpst_get_default_admin_mail_subject();
        $body = array_key_exists('admin_body', $mail_setting) ? $mail_setting['admin_body'] : wpst_get_default_admin_mail_body();
        $footer = array_key_exists('admin_footer', $mail_setting) ? $mail_setting['admin_footer'] : wpst_get_default_admin_mail_footer();
        $mail_content = wpst_prepare_html_shortcodes($shipment_id, wpst_construct_mail_body($body, $footer, true));

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $headers[] = "From: " .get_bloginfo('name'). " <".sanitize_email($site_mail).">\r\n";
        $attachments = apply_filters('wpst_admin_mail_attachments', array(), $shipment_id);

        if(!empty($cc)){
            $headers[] = "cc: {$cc}";
        }
        if(!empty($bcc)){
            $headers[] = "Bcc: {$bcc}";
        }

        if (!empty($mail_to)) {
            wp_mail($mail_to, $subject, $mail_content, $headers, $attachments);          
        }
    }

    function wpst_send_client_email_notification($shipment_id, $data, $old_status) {
        global $sendtrace;
        if (get_post_status($shipment_id) != 'publish') {
            return false;
        }
        
        $site_mail = sanitize_email(get_option('new_admin_email'));
        if (empty($site_mail)) {
            $site_mail = sanitize_email(get_bloginfo('admin_email'));
        }
        $tracking_no = get_the_title($shipment_id);
        $shortcode_values = $sendtrace->get_shortcode_values($shipment_id);
        $sendtrace_status = sanitize_text_field(get_post_meta($shipment_id, 'sendtrace_status', true));
        $mail_setting = $sendtrace->get_setting_html('email_client');
        $is_enabled = strtoupper($mail_setting['client_enable'] ?? 'YES') == 'YES';
        
        $force_send = array_key_exists('force_notification_send', $data) ? wpst_sanitize_data($data['force_notification_send']) : false;
        if (!$is_enabled || (!$force_send && ($sendtrace_status == $old_status || !in_array($sendtrace_status, wpst_send_client_email_in_status_list())))) {
            return false;
        }

        if (!empty($mail_setting) && !empty($shortcode_values)) {
            foreach ($shortcode_values as $shortcode => $shortcode_val) {
                foreach ($mail_setting as $setting => $setting_val) {
                    if (empty($setting_val) && in_array($setting, array('client_body', 'client_footer'))) {
                        if ($setting == 'client_body') {
                            $setting_val = wpst_get_default_client_mail_body();
                        }
                        if ($setting == 'client_footer') {
                            $setting_val = wpst_get_default_client_mail_footer();
                        }
                    }
                    $mail_setting[$setting] = str_replace($shortcode, $shortcode_val, $setting_val);
                }
            }            
        }
        
        $mail_to = array_key_exists('client_mail_to', $mail_setting) ? implode(',', wpst_sanitize_data($mail_setting['client_mail_to'], 'email')) : $shortcode_values['{wpst_shipper_email}'];
        $mail_to = apply_filters('wpst_client_mail_to', $mail_to, $shipment_id);
        $cc = array_key_exists('client_cc', $mail_setting) ? implode(',', wpst_sanitize_data($mail_setting['client_cc'], 'email')) : '';
        $bcc = array_key_exists('client_bcc', $mail_setting) ? implode(',', wpst_sanitize_data($mail_setting['client_bcc'], 'email')) : '';
        $subject = array_key_exists('client_subject', $mail_setting) ? $mail_setting['client_subject'] : wpst_get_default_client_mail_subject($shipment_id);
        $body = array_key_exists('client_body', $mail_setting) ? $mail_setting['client_body'] : wpst_get_default_client_mail_body();
        $footer = array_key_exists('client_footer', $mail_setting) ? $mail_setting['client_footer'] : wpst_get_default_client_mail_footer();
        $mail_content = wpst_prepare_html_shortcodes($shipment_id, wpst_construct_mail_body($body, $footer));

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $headers[] = "From: " .get_bloginfo('name'). " <".sanitize_email($site_mail).">\r\n";
        $attachments = apply_filters('wpst_client_mail_attachments', array(), $shipment_id);

        if(!empty($cc)){
            $headers[] = "cc: {$cc}\r\n";
        }
        if(!empty($bcc)){
            $headers[] = "Bcc: {$bcc}\r\n";
        }

        if (!empty($mail_to)) {
            wp_mail($mail_to, $subject, $mail_content, $headers, $attachments);
        }
    }

    // Fields
    function wpst_assign_client($shipment_id)
    {
        $access_roles_assign = wpst_get_roles_can_assign();
        if (!wpst_is_user_admin() && !in_array('sendtrace_client', $access_roles_assign)) {
            return false;
        }
        $clients = wpst_get_users_list(array('sendtrace_client'));
        $assigned_client = $shipment_id ? get_post_meta($shipment_id, 'assigned_client', true) : '';
        echo "<div class='card p-0 my-3'>";
            echo "<h5 class='h5 m-0 card-header'>".__('Assignment', 'sendtrace-shipments')."</h5>";
            echo "<div class='card-body'>";
                WPSTForm::gen_field(array(
                    'key' => 'assigned_client',
                    'type' => 'select',
                    'label' => __('Client', 'sendtrace-shipments'),
                    'class' => 'selectize w-100',
                    'options' => $clients,
                    'description' => !empty($clients) || !wpst_is_user_admin() ? '' : '<span class="text-danger">'.esc_html__('Create sendtrace client user.', 'sendtrace-shipments').'<a href="'.admin_url().'user-new.php" target="_blank"> '.esc_html('here', 'sendtrace-shipments').' <i class="fa fa-external-link"></i></a></span>',
                    'value' => $assigned_client
                ), true);  
                do_action('wpst_after_assign_client', $shipment_id);                    
            echo "</div>";
        echo "</div>";        
    }

    function wpst_assign_agent($shipment_id)
    {
        $access_roles_assign = wpst_get_roles_can_assign();
        if (!wpst_is_user_admin() && !in_array('sendtrace_agent', $access_roles_assign)) {
            return false;
        }
        $agents = wpst_get_users_list(array('sendtrace_agent'));
        $assigned_agent = $shipment_id ? get_post_meta($shipment_id, 'assigned_agent', true) : '';
        WPSTForm::gen_field(array(
            'key' => 'assigned_agent',
            'type' => 'select',
            'label' => __('Agent', 'sendtrace-shipments'),
            'class' => 'selectize w-100',
            'options' => $agents,
            'description' => !empty($agents) || !wpst_is_user_admin() ? '' : '<span class="text-danger">'.esc_html__('Create sendtrace agent user.', 'sendtrace-shipments').'<a href="'.admin_url().'user-new.php" target="_blank"> '.esc_html('here', 'sendtrace-shipments').' <i class="fa fa-external-link"></i></a></span>',
            'value' => $assigned_agent
        ), true);
    }

    function wpst_assign_editor($shipment_id)
    {
        $access_roles_assign = wpst_get_roles_can_assign();
        if (!wpst_is_user_admin() && !in_array('sendtrace_editor', $access_roles_assign)) {
            return false;
        }
        $editors = wpst_get_users_list(array('sendtrace_editor'));
        $assigned_editor = $shipment_id ? get_post_meta($shipment_id, 'assigned_editor', true) : '';
        WPSTForm::gen_field(array(
            'key' => 'assigned_editor',
            'type' => 'select',
            'label' => __('Editor', 'sendtrace-shipments'),
            'class' => 'selectize w-100',
            'options' => $editors,
            'description' => !empty($editors) || !wpst_is_user_admin() ? '' : '<span class="text-danger">'.esc_html__('Create Shiptrack Editor user.', 'sendtrace-shipments').'<a href="'.admin_url().'user-new.php" target="_blank"> '.esc_html('here', 'sendtrace-shipments').' <i class="fa fa-external-link"></i></a></span>',
            'value' => $assigned_editor
        ), true);    
    }

    function wpst_multiple_package($shipment_id, $packages_data=array(), $pkg_type='')
    {
        global $sendtrace, $WPSTField;
        $package_fields = $WPSTField->multiple_package();
        $package_data = $shipment_id ? get_post_meta($shipment_id, 'multiple-package', true) : $packages_data;
        $disabled_fields = array();
        $has_pkg_totals = $pkg_type != 'parcel_rate';
        $allow_add_delete = $pkg_type != 'parcel_rate';
        if ($pkg_type == 'parcel_rate') {
            $disabled_fields = array('qty', 'weight', 'length', 'width', 'height');
        }
        require_once wpst_get_template('multiple-package.tpl');
    }

    function wpst_shipment_history($shipment_id)
    {
        if (!$shipment_id) {
            return false;
        }
        global $WPSTField;
        $history_fields = $WPSTField->history_fields($shipment_id);
        $history_data = $shipment_id ? get_post_meta($shipment_id, 'shipment-history', true) : array();
        require_once wpst_get_template('history.tpl');
    }

    // Track Result
    function wpst_track_result_header($shipment_id, $shipment_data)
    {
        $tracking_no = get_the_title($shipment_id);
        require_once wpst_get_template('result-header.tpl');  
    }
    function wpst_track_result_status($shipment_id, $shipment_data)
    {
        $history_data = array_reverse(get_post_meta($shipment_id, 'shipment-history', true));
        require_once wpst_get_template('result-status.tpl');  
    }

    function wpst_track_result_shipper_receiver($shipment_id, $shipment_data)
    {
        $shipment_data = array_filter($shipment_data, function($key){
            return in_array($key, ['shipper_information', 'receiver_information']);
        }, ARRAY_FILTER_USE_KEY);
        require_once wpst_get_template('result-shipper-receiver.tpl');
    }

    function wpst_track_result_shipment_info($shipment_id, $shipment_data)
    {
        $shipment_data = array_key_exists('shipment_details', $shipment_data) ? $shipment_data['shipment_details'] : array();
        require_once wpst_get_template('result-shipment-info.tpl');
    }

    function wpst_track_result_multiple_package($shipment_id, $shipment_data)
    {
        global $sendtrace, $WPSTField;
        $package_fields = $WPSTField->multiple_package();
        $package_data = $shipment_id ? get_post_meta($shipment_id, 'multiple-package', true) : array();
        require_once wpst_get_template('result-multiple-package.tpl');
    }

    function product_offers() {
        require_once WPST_PLUGIN_PATH .'templates/admin/settings/product-offers.tpl.php';
    }

    function wpst_shipper_address_book_auto_fill()
    {
        global $WPSTField;
        $searchable_field = wpst_ab_shipper_search_field();
        $shipper_section = $WPSTField->fields(0, 'shipper_information');
        $shipper_fields = !empty($shipper_section) ? $shipper_section['shipper_information']['fields'] : array();
        $placeholder = array_key_exists($searchable_field, $shipper_fields) ? $shipper_fields[$searchable_field]['label'] : 'Address Book';
        $shipper_count = wpst_get_ab_counts('shipper');
        
        echo "<div class='card-body border-primary' style='border-bottom: 1px solid'>";
            if ($shipper_count) {
                $extras = "data-type=shipper data-meta_key={$searchable_field}";
                if (!is_user_admin()) {
                    $extras .= " data-post_author=".get_current_user_id();
                }            
                echo WPSTForm::gen_field(array(
                    'type' => 'select',
                    'key' => 'wpst_shipper_autofill',
                    'placeholder' => 'Find '.$placeholder,
                    'class' => 'wpst-ab-autofill w-100',
                    'extras' => $extras
                ));            
            } else {
                echo "<div class='fw-semibold'>Add your shipper address book";
                    echo " <a href='".admin_url('admin.php?page=sendtrace-shipment-address-book&type=shpper')."' target='_blank'>here <i class='fa fa-external-link'></i></a>";
                echo "</div>";
            }
        echo "</div>";

        
    }

    function wpst_receiver_address_book_auto_fill() 
    {
        global $WPSTField;
        $searchable_field = wpst_ab_receiver_search_field();
        $receiver_section = $WPSTField->fields(0, 'receiver_information');
        $receiver_fields = !empty($receiver_section) ? $receiver_section['receiver_information']['fields'] : array();
        $placeholder = array_key_exists($searchable_field, $receiver_fields) ? $receiver_fields[$searchable_field]['label'] : 'Address Book';
        $receiver_count = wpst_get_ab_counts('receiver');

        echo "<div class='card-body border-primary' style='border-bottom: 1px solid'>";
            if ($receiver_count) {
                $extras = "data-type=receiver data-meta_key={$searchable_field}";
                if (!is_user_admin()) {
                    $extras .= " data-post_author=".get_current_user_id();
                }        
                echo WPSTForm::gen_field(array(
                    'type' => 'select',
                    'key' => 'wpst_receiver_autofill',
                    'placeholder' => 'Find '.$placeholder,
                    'class' => 'wpst-ab-autofill w-100',
                    'extras' => $extras
                ));
            } else {
                echo "<div class='fw-semibold'>Add your receiever address book";
                    echo " <a href='".admin_url('admin.php?page=sendtrace-shipment-address-book&type=receiver')."' target='_blank'>here <i class='fa fa-external-link'></i></a>";
                echo "</div>";
            }
        echo "</div>";
    }
}

new WPSTHook;
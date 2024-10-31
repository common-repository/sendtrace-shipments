<?php

function wpst_plugin_slug() {
    return 'sendtrace-shipment';
}

function wpst_datepicker_format() {
    return wpst_sanitize_data(apply_filters('wpst_datepicker_format', 'YYYY-MM-DD'));
}

function wpst_date_format() {
    return apply_filters('wpst_date_format', 'Y-m-d');
}

function wpst_calendar_datetime_format() {
    return wpst_sanitize_data(apply_filters('wpst_calendar_datetime_format', 'YYYY-MM-DD hh:mm a'));
}

function wpst_datetime_format() {
    return wpst_sanitize_data(apply_filters('wpst_datetime_format', 'Y-m-d H:i a'));
}

function wpst_number_format($value, $currency=false, $decimals_count=2) {
    if (!is_numeric($value)) {
        return false;
    }
    $formatted_number = apply_filters('wpst_number_format', number_format($value, $decimals_count));
    if ($currency) {
        $currency_symbol = wpst_get_currency();
        $formatted_number = $currency_symbol.$formatted_number;
    }
    return wpst_sanitize_data($formatted_number);
}

function wpst_is_decimal($value) {
    return is_numeric($value) && floor($value) != $value;
}

function wpst_get_currency(){
    return function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '';
}

function wpst_get_amount_applied_tax($amount, $currency=false, $decimals_count=2) {
    global $sendtrace;
    $amount_with_vat = wpst_get_applied_tax($amount) + $amount;
    return wpst_number_format($amount_with_vat, $currency, $decimals_count);
}

function wpst_get_amount_remove_tax($total_amount, $applied_tax, $currency=false, $decimals_count=2) {
    $total_amount = $total_amount - $applied_tax;
    return wpst_number_format($total_amount, $currency, $decimals_count);
}

function wpst_get_applied_tax($amount) {
    global $sendtrace;
    $vat = ($sendtrace->tax / 100) * $amount;
    return $vat;
}

function wpst_get_template($file_name, $admin_tpl=false){
    $file_slug = strtolower( preg_replace('/\s+/', '_', trim( str_replace( '.tpl', '', $file_name ) ) ) );
    $file_slug = preg_replace('/[^A-Za-z0-9_]/', '_', $file_slug );    
    $admin_folder = $admin_tpl ? 'admin/' : '';
    $template_path   = get_stylesheet_directory()."/sendtrace/{$admin_folder}{$file_name}.php";
    
    if (!file_exists($template_path)) {
        $template_path  = WPST_PLUGIN_PATH."templates/{$admin_folder}{$file_name}.php";
        $template_path  = apply_filters("wpst_locate_template_{$file_slug}", $template_path);
    }
	return esc_html($template_path);
}

function wpst_get_last_date_of_shipments() {
    global $wpdb;
    $add_where = "";
    if (!wpst_is_user_admin() && (wpst_get_user_role() == 'sendtrace_editor') && !wpst_editor_can_access_all_shipments()) {
        $sliced_role = str_replace('sendtrace_', '' ,wpst_get_user_role());
        $add_where = " AND m.meta_key = 'assigned_{$sliced_role}' AND m.meta_value = ".get_current_user_id();
    }
    $sql = "SELECT p.post_date 
            FROM `{$wpdb->prefix}posts` p
            INNER JOIN `{$wpdb->prefix}postmeta` m ON p.ID = m.post_id
            WHERE p.post_type = %s AND p.post_status = 'publish' 
            {$add_where}
            ORDER BY p.post_date DESC LIMIT 1";
    return $wpdb->get_var($wpdb->prepare($sql, 'sendtrace'));
}

function wpst_dd($data, $die=false) {
    if (isset($_GET['debug'])) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($die) {
            die();
        }
    }
}
function wpst_error_handler($error, $class='')
{
    echo "<p class='wpst-error ".esc_html($class)."'>" .wp_kses($error, wpst_allowed_html_tags()). "</p>";
    ?>
    <style>
        .wpst-error {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        padding: 5px 10px;
        border-radius: 3px;
    }
    </style>
    <?php
    die();
}

function wpst_sanitize_data($data, $type='', $allow_html=false) {
    if (is_array($data)) {
        array_walk($data, function(&$value) use ($type, $allow_html){
            if ($type == 'email') {
                $value = !is_array($value) ? sanitize_email($value) : $value;
            } else {
                $value = !is_array($value) ? ($allow_html ? wp_kses_data($value) : sanitize_text_field($value)) : $value;
            }            
        });
    } else {
        if ($type == 'email') {
            $data = sanitize_email($data);
        } else {
            $data = $allow_html ? wp_kses_data($data) : sanitize_text_field($data);
        }   
    }
    return $data;
}

function wpst_sanitize_string($string) {
    $clean =  preg_replace('/[^\da-z ]/i', '', $string); // Removes special chars.
	return $clean;
}

function wpst_woo_is_active()
{
    return class_exists('woocommerce');
}

function wpst_allowed_html_tags() {
    return array(
        'i' => array('class' => array()),
        'br' => array('id' => array(), 'class' => array()), 
        'p' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()), 
        'strong' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'a' => array('id' => array(), 'class' => array(), 'href' => array(), 'style' => array(), 'target' => array(), 'data' => array(), 'data-item' => array()),
        'ul' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'li' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'ol' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'span' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'div' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'h1' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'h2' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'h3' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'h4' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'h5' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'h6' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'table' => array('id' => array(), 'class' => array(), 'style' => array(), 'width'=> array(), 'border' => array(), 'data' => array(), 'data-item' => array()),
        'thead' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'tbody' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'tfooter' => array('id' => array(), 'class' => array(), 'style' => array(), 'data' => array(), 'data-item' => array()),
        'tr' => array('id' => array(), 'class' => array(), 'style' => array(), 'align' => array(), 'data' => array(), 'data-item' => array()),
        'th' => array('id' => array(), 'class' => array(), 'style' => array(), 'align' => array(), 'width'=> array(), 'border' => array(), 'data' => array(), 'data-item' => array()),
        'td' => array('id' => array(), 'class' => array(), 'style' => array(), 'align' => array(), 'width'=> array(), 'border' => array(), 'data' => array(), 'data-item' => array()),
        'img' => array('src' => array(), 'height' => array(), 'style' => array(), 'width' => array(), 'data' => array(), 'data-item' => array()),
        'input' => array('id' => array(), 'class' => array(), 'style' => array(), 'type' => array(), 'data' => array(), 'data-item' => array()),
    );
}

function wpst_address_field() {
    $address_field = array(
        'country' =>  __('Country', 'sendtrace-shipments'),
        'address_1' => __('Address 1', 'sendtrace-shipments'),
        'address_2' => __('Address 2', 'sendtrace-shipments'),
        'city' => __('Town / City', 'sendtrace-shipments'),
        'state' => __('State / Country', 'sendtrace-shipments'),
        'postcode' => __('Postal Code / Zip Code ', 'sendtrace-shipments'),
    );

    return apply_filters('wpst_address_field', $address_field);
}

function wpst_array_to_string($data_arr)
{
    return !empty($data_arr) ? implode(', ', $data_arr) : '';
}

function for_wpst_assets_only() {
    $allow_bootstrap = false;
    if (is_admin()) {
        $page = isset($_GET['page']) ? strtolower(wpst_sanitize_data($_GET['page'])) : '';
        if (strpos($page, wpst_plugin_slug()) !== false) {
            $allow_bootstrap = true;
        }
    }
    return apply_filters('for_wpst_assets_only', $allow_bootstrap);
}

function wpst_bg_color() {
    global $sendtrace;
    $color_code = $sendtrace->get_setting('general', 'bg_color');
    if (empty($color_code)) {
        $color_code = '#ff6b35';
    }
    return $color_code;
}

function wpst_fg_color() {
    global $sendtrace;
    return $sendtrace->get_setting('general', 'fg_color', '#ffffff');
}

function wpst_get_default_status() {
    return apply_filters('wpst_default_sattus', 'Shipment Created');
}

function wpst_get_role_assign_access() {
    $assign_access = array(
        'sendtrace_client' => array(),
        'sendtrace_agent' => array('sendtrace_client'),
        'sendtrace_editor' => array('sendtrace_client', 'sendtrace_agent')
    );
    return apply_filters('wpst_get_role_assign_access', $assign_access);
}

function wpst_get_roles_can_assign($role='') {
    $role = empty($role) ? wpst_get_user_role() : $role;
    $roles_access = wpst_get_role_assign_access();
    $access = array();
    if (array_key_exists($role, $roles_access)) {
        $access = $roles_access[$role];
    }
    return $access;
}

function wpst_get_shipment_actions($capabilities='r') {
    $capabilities = strtoupper($capabilities);
    $capabilities = !empty($capabilities) ? str_split($capabilities) : [];
    $actions = array();
    if (!empty($capabilities)) {
        foreach ($capabilities as $cap) {
            switch ($cap) {
                case 'C':
                    $actions[] = 'create';
                    break;
                case 'R':
                    $actions[] = 'read';
                    break;                    
                case 'U':
                    $actions[] = 'update';
                    break;
                case 'D':
                    $actions[] = 'delete';
                    break;
            }
        }
    }
    return $actions;    
}

function wpst_get_role_shipment_capabilities($role) {
    if (empty($role)) {
        return array();
    }
    $role_capabilities = array(
        'sendtrace_client' => wpst_get_shipment_actions('r'),
        'sendtrace_agent' => wpst_get_shipment_actions('crud'),
        'sendtrace_editor' => wpst_get_shipment_actions('crud')
    );
    $role_capabilities = wpst_sanitize_data(apply_filters('wpst_role_shipment_capabilities', $role_capabilities));
    $capabilities = array_key_exists($role, $role_capabilities) ? $role_capabilities[$role] : array();
    return $capabilities;
}

function wpst_shipment_user_can($action, $role='') {
    if (empty($role) && is_user_logged_in()) {
        $role = wpst_get_user_role();
    }
    $action = wpst_sanitize_data(strtolower($action));
    $role = wpst_sanitize_data(strtolower($role));
    if ($role == 'administrator') {
        return true;
    }
    $role_actions = wpst_get_role_shipment_capabilities($role);
    return in_array($action, $role_actions);
}
function wpst_editor_can_access_all_shipments()
{
    return apply_filters('wpst_editor_can_access_all_shipments', false);
}

function wpst_update_post_status($post_id, $status) {
    if (!$post_id) {
        throw new Exception("Shipment ID not specified");
    }
    $status = sanitize_text_field($status) == 'untrash' ? 'publish' : $status;
    $args = array (
        'ID' => $post_id,
        'post_status' => $status
    );
    wp_update_post($args);
}

function wpst_can_modify_history() {
    global $sendtrace;
    $roles_can_modify = $sendtrace->get_setting('general', 'roles_modify_history');
    $can_modify = false;
    if (wpst_is_user_admin() || (!empty($roles_can_modify) && in_array(wpst_get_user_role(), $roles_can_modify))) {
        $can_modify = true;
    }
    return $can_modify;
}

function wpst_get_user_roles() {
    $wp_roles = wp_roles();
    $roles = array();
    foreach ($wp_roles->roles as $role => $info) {
        if (strpos($role, 'sendtrace') !== false) {
            $roles[$role] = $info['name'];
        }
    }
    return $roles;
}

function wpst_get_users_list($roles=array()) {
    $args = !empty($roles) ? array('role__in' => $roles) : array();
    $users = get_users($args);
    $users_list = array();
    if (!empty($users)) {
        foreach ($users as $user) {
            $users_list[$user->data->ID] = $user->data->display_name;
        }
    }
    return $users_list;
}

function wpst_get_user_data($user_id, $retrieve_field='') {
    $userInfo = (array)get_userdata($user_id)->data;
    if (!empty($retrieve_field)) {
        $retrieve_value = '';
        if (array_key_exists($retrieve_field, $userInfo)) {
            $retrieve_value = $userInfo[$retrieve_field];
        }
        $userInfo = $retrieve_value;
    }
    return $userInfo;
}

function wpst_get_user_role($user_id=0) {
    if (!is_user_logged_in()) {
        return '';
    }
    $userInfo = $user_id ? (array)get_userdata($user_id) : (array)wp_get_current_user();
    return !empty($userInfo) ? $userInfo['roles'][0] : '';
}

function wpst_is_user_admin() {
    return wpst_get_user_role() == 'administrator';
}

function wpst_is_ab_premium()
{
    return apply_filters('wpst_is_ab_premium', false);
}

function wpst_get_ab_counts($type=null)
{   
    global $wpdb;
    $add_where = !empty($type) ? " AND pm.meta_key = '_type' AND pm.meta_value = '{$type}'" : "";
    $sql = "SELECT count(DISTINCT ID) FROM {$wpdb->prefix}posts p 
            INNER JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id
            WHERE p.post_status = 'publish' AND p.post_type = 'address_book' AND post_author = %d
            {$add_where}";
    $count = $wpdb->get_var($wpdb->prepare($sql, get_current_user_id()));
    return $count ?? 0;
}

function wpst_get_shipment_meta_details($shipment_id) {
    global $wpdb;
    $sql = "SELECT * FROM `{$wpdb->prefix}postmeta` WHERE post_id = %d";
    $results = $wpdb->get_results($wpdb->prepare($sql, $shipment_id));
    $details = [];
    if (!empty($results)) {
        foreach ($results as $result) {
            $details[$result->meta_key] = maybe_unserialize($result->meta_value);
        }
    }
    return $details;
}

function wpst_get_shipments_summary($date_from='', $date_to='') {
    global $wpdb;
    $add_where = "";
    if (!empty($date_from) && !empty($date_to)) {
        $date_to = date(wpst_date_format(), strtotime($date_to.' +1 day'));
        $add_where = " AND (p.post_date BETWEEN DATE('{$date_from}') AND DATE('{$date_to}'))";
    }
    $sql = "SELECT DISTINCT p.ID, p.post_date, m.meta_value AS status
            FROM {$wpdb->prefix}posts p
            INNER JOIN {$wpdb->prefix}postmeta m ON p.ID = m.post_id
            WHERE p.post_status = %s AND p.post_type = %s AND m.meta_key = %s
            {$add_where}
            ORDER BY p.post_date, status";
    $results = $wpdb->get_results($wpdb->prepare($sql, 'publish', 'sendtrace', 'sendtrace_status'));
    $summary = array();
    if (!empty($results)) {
        foreach ($results as $result) {
            $summary[] = (array)$result;
        }
    }
    return $summary;
}

function wpst_get_shipments_summary_by_status($summary) {
    global $sendtrace;
    $status_summary = array();
    $status_list = $sendtrace->status_list();
    if (!empty($status_list)) {
        foreach ($status_list as $status) {
            $status_summary[$status] = 0;
        }
    }
    if (!empty($summary)) {
        foreach ($summary as $shipment) {
            if (array_key_exists($shipment['status'], $status_summary)) {
                $status_summary[$shipment['status']] ++;
            } else {
                $status_summary[$shipment['status']] = 1;
            }
        }
    }
    arsort($status_summary);
    return $status_summary;
}

function wpst_get_shipments_summary_monthly($summary) {
    $monthly = array();
    if (!empty($summary)) {
        foreach ($summary as $shipment) {
            $yr_mo = date('Y-M', strtotime($shipment['post_date']));
            $monthly[$yr_mo][] = $shipment;
        }
    }
    if (!empty($monthly)) {
        foreach ($monthly as $_mo => $shipments) {
            $monthly[$_mo] = count($shipments);
        }
    }
    return $monthly;
}
function wpst_get_shipments_summary_yearly($summary) {
    $yearly = array();
    if (!empty($summary)) {
        foreach ($summary as $shipment) {
            $year = date('Y', strtotime($shipment['post_date']));
            $yearly[$year][] = $shipment;
        }
    }
    if (!empty($yearly)) {
        foreach ($yearly as $year => $shipments) {
            $yearly[$year] = count($shipments);
        }
    }
    return $yearly;
}

function wpst_is_tracking_auto_generate() {
    global $sendtrace;
    return $sendtrace->get_setting('general', 'auto_generate', 'Yes') == 'Yes';
}
function wpst_is_post_modified($post_id) {
    if (!$post_id) {
        return false;
    }
    global $wpdb;
    $sql = "SELECT * FROM `{$wpdb->prefix}posts` WHERE ID = %d AND post_type = 'sendtrace' AND post_date = post_modified";
    $result = $wpdb->get_row($wpdb->prepare($sql, $post_id));
    return empty($result);
}
function wpst_set_notification($msg, $alert_type='success', $icon='check') {
    $_POST['wpst_notification'] = [
        'message' => wp_kses_data($msg),
        'type' => $alert_type,
        'icon' => $icon
    ];
}
function wpst_customer_field($type='', $retrieve_field='') {
    $field = array(
        'shipper' => array(
            'key' => 'wpst_shipper_name',
            'label' => __('Shipper Name', 'sendtrace-shipments')
        ),
        'receiver' => array(
            'key' => 'wpst_receiver_name',
            'label' => __('Receiver Name', 'sendtrace-shipments')
        )
    );

    $field = apply_filters('wpst_customer_field', $field);
    if (!empty($type)) {
        $field = array_key_exists($type, $field) ? $field[$type] : array();
        if (!empty($retrieve_field)) {
            $field = array_key_exists($retrieve_field, $field) ? $field[$retrieve_field] : '';
        }
    }

    return $field;
}
function wpst_get_pages() {
    global $wpdb;
    $sql = "SELECT ID, post_title FROM `{$wpdb->prefix}posts` WHERE post_status = 'publish' AND post_type = %s ORDER BY post_title";
    $results = $wpdb->get_results($wpdb->prepare($sql, 'page'));
    $pages = array();
    if (!empty($results)) {
        foreach ($results as $page) {
            $pages[$page->ID] = $page->post_title;
        }
    }
    return !empty($pages) ? $pages : array();
}

function wpst_insert_content_to_page($page_id, $content, $remove_existing_content=false) {
    global $wpdb;

    if (!$page_id) {
        return false;
    }

    $sql1 = "SELECT post_content FROM `{$wpdb->prefix}posts` WHERE ID = %d";
    $post_content = $wpdb->get_var($wpdb->prepare($sql1, $page_id));

    if (strpos($post_content, $content) !== false || empty($content)) {
        return true;
    }
    
    if (!$remove_existing_content && !empty($post_content)) {        
        $content = $post_content ."\n".$content;
    }
    $sql2 = "UPDATE `{$wpdb->prefix}posts` SET post_content = %s WHERE ID = %d";
    $wpdb->query($wpdb->prepare($sql2, $content, $page_id));
}

function wpst_get_tracking_page_id() {
    global $sendtrace;
    return $sendtrace->get_setting('general', 'tracking_page', 0);
}

function wpst_get_company_logo() {
    global $sendtrace;
    $brand_id = $sendtrace->get_setting('general', 'company_logo');
    $default = WPST_PLUGIN_URL. 'assets/images/sendtrace-logo.png';
    return $brand_id ? wp_get_attachment_url($brand_id) : $default;
}

function wpst_get_shipment_id_by_order($order_id)
{
    global $wpdb;
    $sql = "SELECT meta.meta_value AS shipment_id
            FROM wp_woocommerce_order_itemmeta meta 
            INNER JOIN wp_woocommerce_order_items item ON meta.order_item_id = item.order_item_id
            WHERE item.order_id = %d AND meta.meta_key = 'SHIPMENT ID'";
    $shipment_id = $wpdb->get_var($wpdb->prepare($sql, $order_id));
    return $shipment_id ?? 0;
}

function wpst_send_client_email_in_status_list()
{
    global $sendtrace;
    return  $sendtrace->get_setting('email_client', 'enabled_statuses', $sendtrace->status_list());
}
function wpst_get_default_admin_mail_subject() {
    return "New Booking";
}
function wpst_get_default_admin_mail_body() {
    $body = "<p>Dear Admin,</p>\n";
    $body .= "<p>New shipment was created <strong>#{tracking_no}</strong></p>";
    return $body;
}
function wpst_get_default_admin_mail_footer() {
    return "<p>Your Company Address here..</p>";
}
function wpst_get_default_client_mail_body() {
    $body = "<p> Hi {".esc_html(wpst_customer_field('shipper', 'key'))."},</p>\n";
    $body .= "<p>Your shipment tracking <strong>#{tracking_no}</strong> was place to {sendtrace_status} status.</p>\n";
    $body .= "<p>Thank you for getting in touch with us.</p>";
    return $body;
}
function wpst_get_default_client_mail_subject($shipment_id) {
    $tracking_no = get_the_title($shipment_id);
    return "Shipment Tracking #".esc_html($tracking_no);
}
function wpst_get_default_client_mail_footer() {
    return "<p>Your Company Address here..</p>";
}
function wpst_get_email_footer_html($email_footer) {
    $footer = "<table width='100%' style='background-color: ".wpst_bg_color()."; color: ".wpst_fg_color().";'>";
        $footer .= "<tr>";
            $footer .= "<td align='center'>{$email_footer}</td>";
        $footer .= "</tr>";
    $footer .= "</table>";
    return apply_filters('wpst_email_header_html', $footer);
}
// Email Setting
function wpst_construct_mail_body($email_body, $email_footer, $is_admin_tpl=false) {
    $email_header = wpst_get_email_header_html();
    $footer_html = wpst_get_email_footer_html($email_footer);
    $html = "<table width='100%' style='font-family: sans-serif; border-collapse: collapse;'>";
        $html .= "<tr>";
            $html .= "<td".wpst_bg_color()."'>{$email_header}</td>";
        $html .= "</tr>";
        $html .= "<tr>";
            $html .= "<td style='padding: 25px 5px'>{$email_body}</td>";
        $html .= "</tr>";
        $html .= "<tr>";
            $html .= "<td>{$footer_html}</td>";
        $html .= "</tr>";
    $html .= "</table>";
    return wp_kses(apply_filters('wpst_email_content_html', $html, $is_admin_tpl), wpst_allowed_html_tags());
}

function wpst_get_email_header_html() {
    global $sendtrace;
    $company_logo = esc_url(wp_get_attachment_url($sendtrace->get_setting('general', 'company_logo')));
    $html = "<table width='100%' style='background-color: ".wpst_bg_color()."; color: ".wpst_fg_color().";'>";
        $html .= "<tr>";
            $html .= "<td align='center' style='padding: 10px;'>";
            if (!empty($company_logo)) {
                $html .= "<table style='width: 90px; margin-bottom: 8px; height: 90px; border-radius: 50%; background-color: #fff; overflow: hidden;'>";
                    $html .= "<tr>";
                        $html .= "<td align='center'>";
                        $html .= "<img src='{$company_logo}' width='90%' height='auto' style='background-color:".wpst_fg_color()."' />";
                        $html .= "</td>";
                    $html .= "</tr>";
                $html .= "</table>";
            }                
            $html .= "<div style='font-size: 28px;'><span>".get_bloginfo('name')."</span></div>";
            $html .= "</td>";
        $html .= "</tr>";        
    $html .= "</table>";
    return apply_filters('wpst_email_header_html', $html, $company_logo);
}

function wpst_prepare_html_shortcodes($shipment_id, $html) {
    global $sendtrace;
    $shortcode_values = $sendtrace->get_shortcode_values($shipment_id);
    foreach ($shortcode_values as $shortcode => $value) {
        $html = str_replace($shortcode, $value, $html);
    }
    return $html;
}

function wpst_merge_array_values($assoc_array) {
    $new_array = array();
    if (empty($assoc_array)) {
        return $assoc_array;
    }
    if (!is_array(array_values($assoc_array)[0])) {
        return $assoc_array;
    }
    foreach ($assoc_array as $array) {
        $new_array = array_merge_recursive($new_array, $array);
    }
    return $new_array;
}

function wpst_get_packages_data($shipment_id, $data_only=false, $data_is_string=false) {
    global $sendtrace, $WPSTField;
    $package_fields = $WPSTField->multiple_package();
    $package_data = $shipment_id ? get_post_meta($shipment_id, 'multiple-package', true) : array();
    if (empty($package_data)) {
        return array();
    }
    $packages = array(
        'fields' => array_combine(array_column($package_fields, 'key'), array_column($package_fields, 'label')),
        'data' => array(), 'data-item' => array(),
        'totals' => $sendtrace->get_package_totals($shipment_id, true),
        'units' => array(
            'weight' => $sendtrace->weight_unit_used(),
            'dimension' => $sendtrace->dim_unit_used()
        )
    );
    if (!empty($package_data) && !empty($package_fields)) {
        foreach ($package_data as $idx => $package) {
            foreach ($package_fields as $field) {
                $packages['data'][$idx][$field['key']]= array_key_exists($field['key'], $package) ? $package[$field['key']] : '';
            }            
        }
    }

    if ($data_only) {
        $packages = $packages['data'];
        if ($data_is_string) {
            $pkg_str_arr = array();
            foreach ($packages as $pkg) {   
                $package_str = '';             
                foreach ($pkg as $_key => $_val) {                   
                    if (empty($_val)) {
                        break;
                    }
                    foreach ($package_fields as $field) {
                        if ($field['key'] == $_key) {
                            if (!empty($package_str)) {
                                $package_str .= ' | ';
                            }
                            $package_str .= $field['label'].'='.$_val;
                        }
                    }                    
                }
                if (!empty($package_str)) {
                    $pkg_str_arr[] = $package_str;
                }
            }
            $packages = implode(" && \r\n", $pkg_str_arr);
        }
    }
    
    return $packages;
}

function wpst_get_site_email() {
    $site_mail = sanitize_email(get_option('new_admin_email'));
    if (empty($site_mail)) {
        $site_mail = sanitize_email(get_bloginfo('admin_email'));
    }
    return $site_mail;
}

function wpst_get_start_end_date_of_week($format='', $day_off_week=1) {
    $format = !empty($format) ? $format : wpst_date_format();
    $dateTime = new DateTime();
    $dateTime->setISODate(date('Y'), date('W'), $day_off_week);
    $result['start_date'] = $dateTime->format($format);
    $dateTime->modify('+6 days');
    $result['end_date'] = $dateTime->format($format);
    return $result;
}

function wpst_clean_dir($directory) {
    $files = glob($directory.'*');
	foreach($files as $file){
	if(is_file($file))
		unlink($file);
	}
}

function wpst_export_file_format_list() {
    $extension = array(
		'xls' => ",", 
		'xlt' => ",", 
		'xla' => ",", 
		'xlw' => ",",
		'csv' => ","
	);
	return apply_filters( 'wpst_export_file_format_list', $extension );
}

function wpst_create_csv($headers, $data, $filename, $format='csv') {
    $formats = wpst_export_file_format_list();
    $format = array_key_exists($format, $formats) ? $format : 'csv';
    $delimeter = array_key_exists($format, $formats) ? $formats[$format] : ',';
    $file_directory = WPST_PLUGIN_PATH."tmp".DIRECTORY_SEPARATOR;
    $filename_unique = $filename.'-'.time().'.'.trim($format);
    $file_url = WPST_PLUGIN_URL."tmp".DIRECTORY_SEPARATOR.$filename_unique;
    wpst_clean_dir($file_directory);
	$csv_file = fopen($file_directory.$filename_unique, "w");	
    

    //write utf-8 characters to file with fputcsv in php
	fprintf($csv_file, chr(0xEF).chr(0xBB).chr(0xBF));
    if (!empty($headers)) {
        fputcsv($csv_file, $headers, $delimeter);
    }
    if (!empty($data)) {
        foreach ($data as $data_arr) {
            $row_data = array();
            foreach ($headers as $_key => $_label) {
                $_value = '';
                if (array_key_exists($_key, $data_arr)) {
                    $_value = $data_arr[$_key];
                    if (is_array($_value)) {
                        $_value = implode(' | ', $_value);
                    }
                }
                $row_data[$_key] = $_value;
            }  
            fputcsv($csv_file, $row_data, $delimeter);          
        }
    }
    fclose($csv_file);
    return $file_url;
}

function wpst_draw_form_fields($shipment_id=0, $packages_data=array(), $pkg_type='') {
    global $WPSTField;
    $form_fields = !empty($WPSTField->fields()) ? $WPSTField->fields($shipment_id) : array();
    do_action('wpst_before_shipment_fields', $shipment_id, $packages_data, $pkg_type);
    if (!empty($form_fields)) {
        foreach($form_fields as $section => $info) {
            do_action("wpst_before_".esc_html($section), $shipment_id);
            echo "<div id='".esc_html($section)."' class='section mb-3 col-sm-12 col-md-" .esc_html($info['section_col']). "'>";
                echo "<div class='card p-0 mw-100'>";
                    echo "<div class='card-header' data-bs-toggle='collapse' href='#".esc_html($section)."_toggle' role='button' aria-expanded='false' aria-controls='".esc_html($section)."_toggle'>";
                        echo "<h5 class='h5 m-0'>" .esc_html($info['heading']). "</h5>";
                    echo "</div>";
                    do_action("wpst_before_body_".esc_html($section), $shipment_id);
                    echo "<div class='card-body collapse show' id='".esc_html($section)."_toggle'>";
                        do_action("wpst_before_fields_".esc_html($section), $shipment_id);
                        echo "<div class='row'>";                                          
                            foreach ($info['fields'] as $field) {
                                $field_col = 'col-md-'.$info['field_col'];
                                echo "<div class='col-sm-12 " .esc_html($field_col). "'>";
                                    WPSTForm::gen_field($field, true);
                                echo "</div>";
                            }                                        
                        echo "</div>";                                        
                    echo "</div>";
                echo "</div>";
            echo "</div>";
            do_action("wpst_after_".esc_html($section), $shipment_id);
        }
    }
    do_action('wpst_after_shipment_fields', $shipment_id, $packages_data, $pkg_type);
}

function wpst_ab_shipper_search_field()
{
    global $sendtrace;
    return $sendtrace->get_setting('address_book', 'shipper_search_field', 'wpst_shipper_name');
}
function wpst_ab_receiver_search_field()
{
    global $sendtrace;
    return $sendtrace->get_setting('address_book', 'receiver_search_field', 'wpst_receiver_name');
}

// Custom Pagination
function wpst_pagination($args=array()) {
    $defaults = array(
        'range' => 4,
        'custom_query' => FALSE,
        'previous_string' => __( 'Previous', 'sendtrace-shipments' ),
        'next_string' => __( 'Next', 'sendtrace-shipments' ),
        'before_output' => '<nav class="post-nav" aria-label="'.__('Sendtrace Pagination', 'sendtrace-shipments').'"><ul class="pagination pg-blue justify-content-center">',
        'after_output' => '</ul></nav>'
    );
    
    $args = wp_parse_args( 
        $args, 
        apply_filters('wpst_pagination_defaults', $defaults )
    );
    
    $args['range'] = (int) $args['range'] - 1;
    if (!$args['custom_query']) {
        $args['custom_query'] = @$GLOBALS['wp_query'];
    }
    $count = (int) $args['custom_query']->max_num_pages;
    $page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? wpst_sanitize_data($_GET['paged']) : 1;
    $ceil  = ceil($args['range'] / 2);
    
    if ($count <= 1)
        return FALSE;
    
    if (!$page)
        $page = 1;
    
    if ($count > $args['range']) {
        if ($page <= $args['range']) {
            $min = 1;
            $max = $args['range'] + 1;
        } elseif ( $page >= ($count - $ceil) ) {
            $min = $count - $args['range'];
            $max = $count;
        } elseif ( $page >= $args['range'] && $page < ($count - $ceil) ) {
            $min = $page - $ceil;
            $max = $page + $ceil;
        }
    } else {
        $min = 1;
        $max = $count;
    }
    
    $echo = '';
    $previous = intval($page) - 1;
    $previous = wpst_sanitize_data(get_pagenum_link($previous));
    
    $firstpage = wpst_sanitize_data(get_pagenum_link(1));
    if ($firstpage && (1 != $page)) {
        $echo .= '<li class="previous page-item"><a class="btn btn-sm color-primary page-link waves-effect waves-effect" href="' . esc_url($firstpage) . '">' . esc_html__( 'First', 'sendtrace-shipments' ) . '</a></li>';
    }
    if ($previous && (1 != $page)) {
        $echo .= '<li class="page-item" ><a class="btn btn-sm color-primary page-link waves-effect waves-effect" href="' . esc_url($previous) . '" title="' . esc_html__( 'previous', 'sendtrace-shipments') . '">' . wpst_sanitize_data($args['previous_string']) . '</a></li>';
    }
    
    if (!empty($min) && !empty($max)) {
        for ($i = $min; $i <= $max; $i++) {
            if ($page == $i) {
                $echo .= '<li class="page-item active"><span class="btn btn-sm color-primary page-link waves-effect waves-effect">' . str_pad((int)$i, 2, '0', STR_PAD_LEFT) . '</span></li>';
            } else {
                $echo .= sprintf( '<li class="page-item"><a class="btn btn-sm color-primary page-link waves-effect waves-effect" href="%s">%002d</a></li>', sanitize_text_field(get_pagenum_link($i)), $i);
            }
        }
    }
    
    $next = intval($page) + 1;
    $next = wpst_sanitize_data(get_pagenum_link($next));
    if ($next && ($count != $page)) {
        $echo .= '<li class="page-item"><a class="btn btn-sm color-primary page-link waves-effect waves-effect" href="' . esc_url($next) . '" title="' . esc_html__( 'next', 'sendtrace-shipments') . '">' . $args['next_string'] . '</a></li>';
    }
    
    $lastpage = wpst_sanitize_data(get_pagenum_link($count));
    if ($lastpage) {
        $echo .= '<li class="next page-item"><a class="btn btn-sm color-primary page-link waves-effect waves-effect" href="' . esc_url($lastpage) . '">' . esc_html__( 'Last', 'sendtrace-shipments' ) . '</a></li>';
    }
    if (isset($echo)) {
        echo $args['before_output'] .wp_kses($echo, wpst_allowed_html_tags()). $args['after_output'];
    }
}
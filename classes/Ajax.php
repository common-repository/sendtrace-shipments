<?php

class WPSTAjax
{
    function __construct()
    {
        add_action('wp_ajax_bulk_update_item_post_status', array($this, 'wpst_bulk_update_item_post_status'));
        add_action('wp_ajax_bulk_update_post_status', array($this, 'wpst_bulk_update_post_status'));
        add_action('wp_ajax_print_shipment_pdf', array($this, 'wpst_print_shipment_pdf'));
        add_action('wp_ajax_nopriv_print_shipment_pdf', array($this, 'wpst_print_shipment_pdf'));
        add_action('wp_ajax_selectize_search', array($this, 'wpst_selectize_search_callback'));
        add_action('wp_ajax_nopriv_selectize_search', array($this, 'wpst_selectize_search_callback'));
        add_action('wp_ajax_generate_report', array($this, 'wpst_generate_report'));
        add_action('wp_ajax_nopriv_generate_report', array($this, 'wpst_generate_report'));
        add_action('wp_ajax_add_update_address_book', array($this, 'add_update_address_book'));
        add_action('wp_ajax_nopriv_add_update_address_book', array($this, 'add_update_address_book'));
        add_action('wp_ajax_get_address_book_data', array($this, 'get_address_book_data'));
        add_action('wp_ajax_nopriv_get_address_book_data', array($this, 'get_address_book_data'));
        add_action('wp_ajax_ab_autofill_search', array($this, 'wpst_ab_autofill_search_callback'));
        add_action('wp_ajax_nopriv_ab_autofill_search', array($this, 'wpst_ab_autofill_search_callback'));
        add_action('wp_ajax_ab_autofill_get_data', array($this, 'get_address_book_data'));
        add_action('wp_ajax_nopriv_ab_autofill_get_data', array($this, 'get_address_book_data'));
    }

    function wpst_selectize_search_callback()
    {
        global $wpdb;
        $meta_key = isset($_POST['meta_key']) ? wpst_sanitize_data($_POST['meta_key']) : '';
        $post_type = isset($_POST['post_type']) ? wpst_sanitize_data($_POST['post_type']) : 'sendtrace';
        $post_author = isset($_POST['post_author']) ? wpst_sanitize_data($_POST['post_author']) : 0;

        $q = isset($_POST['q']) ? wpst_sanitize_data($_POST['q']) : '';
        $add_where = $post_author ? " AND p.post_author = {$post_author}" : "";
        $sql = "SELECT DISTINCT pm.meta_value FROM `{$wpdb->prefix}posts` p 
                INNER JOIN `{$wpdb->prefix}postmeta` pm ON p.ID = pm.post_id 
                WHERE p.post_type = %s AND p.post_status = 'publish' AND pm.meta_key = %s AND pm.meta_value LIKE '%{$q}%'
                {$add_where}";
        $results = $wpdb->get_results($wpdb->prepare($sql, $post_type, $meta_key));
        if (!empty($results)) {
            $new_result = array();
            foreach ($results as $result) {
                $new_result[][$meta_key] = $result->meta_value;
            }
            $results = $new_result;
        }
        echo json_encode($results);
        die();
    }

    function wpst_ab_autofill_search_callback()
    {
        global $wpdb;
        $meta_key = isset($_POST['meta_key']) ? wpst_sanitize_data($_POST['meta_key']) : '';
        $ab_type = isset($_POST['ab_type']) ? wpst_sanitize_data($_POST['ab_type']) : 'shipper';
        $post_author = isset($_POST['post_author']) ? wpst_sanitize_data($_POST['post_author']) : 0;

        $q = isset($_POST['q']) ? wpst_sanitize_data($_POST['q']) : '';
        $add_where = $post_author ? " AND p.post_author = {$post_author}" : "";
        $sql = "SELECT DISTINCT p.ID, pm.meta_value FROM `{$wpdb->prefix}posts` p 
                INNER JOIN `{$wpdb->prefix}postmeta` pm ON p.ID = pm.post_id  
                INNER JOIN `{$wpdb->prefix}postmeta` pm2 ON p.ID = pm2.post_id  
                WHERE p.post_type = 'address_book' AND p.post_status = 'publish' AND pm.meta_key = %s 
                    AND pm2.meta_key = '_type' AND pm2.meta_value = %s 
                    AND pm.meta_value LIKE '%{$q}%'
                {$add_where}";

        $query_results = $wpdb->get_results($wpdb->prepare($sql, $meta_key, $ab_type));
        $results = array();
        if (!empty($query_results)) {
            $new_result = array();
            foreach ($query_results as $result) {
                $new_result[] = array(
                    'post_id' => $result->ID,
                    'meta_value' => $result->meta_value
                );
            }
            $results = $new_result;
        }
        echo json_encode($results);
        die();
    }

    function wpst_bulk_update_post_status()
    {
        $shipmet_ids = isset($_POST['shipment_ids']) ? wpst_sanitize_data($_POST['shipment_ids']) : array();
        $post_status = isset($_POST['status']) ? wpst_sanitize_data($_POST['status']) : '';
        $result = array('status' => 'error');
        
        if (empty($shipmet_ids) || empty($post_status)) {
            if (empty($shipmet_ids)) {
                $result['error'] = __('No shipment(s) selected.', 'sendtrace-shipments');
            }
            if (empty($post_status)) {
                $result['error'] = __('Status is not set for this action.', 'sendtrace-shipments');
            }
        } else {
            try {
                foreach ($shipmet_ids as $shipment_id) {
                    if ($post_status == 'delete') {
                        wp_delete_post($shipment_id, true);
                    } else {
                        wpst_update_post_status($shipment_id, $post_status);
                    }                
                }
                $result['status'] = 'ok';
                $post_status = $post_status == 'publish' ? 'restore' : $post_status;
                $result['msg'] = esc_html('Selected shipment(s) '.$post_status.' successfully.');
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }        
        }
        echo json_encode($result);
        die();
    }

    function wpst_bulk_update_item_post_status()
    {
        $post_ids = isset($_POST['post_ids']) ? wpst_sanitize_data($_POST['post_ids']) : array();
        $post_status = isset($_POST['status']) ? wpst_sanitize_data($_POST['status']) : '';
        $result = array(
            'status' => 'error', 
            'msg' => 'Someting went wrong.'
        );
        
        if (empty($post_ids) || empty($post_status)) {
            if (empty($post_ids)) {
                $result['msg'] = __('No item selected.', 'sendtrace-shipments');
            }
            if (empty($post_status)) {
                $result['msg'] = __('Status is not set for this action.', 'sendtrace-shipments');
            }
        } else {
            try {
                foreach ($post_ids as $post_id) {
                    if ($post_status == 'delete') {
                        wp_delete_post($post_id, true);
                    } else {
                        wpst_update_post_status($post_id, $post_status);
                    }                
                }
                $result['status'] = 'success';
                $post_status = $post_status == 'publish' ? 'restore' : $post_status;
                $result['msg'] = esc_html('Selected item '.$post_status.' successfully.');
            } catch (Exception $e) {
                $result['msg'] = $e->getMessage();
            }        
        }
        echo json_encode($result);
        die();
    }

    function wpst_print_shipment_pdf()
    {
        $shipment_id = isset($_POST['shipment_id']) && is_numeric($_POST['shipment_id']) ? wpst_sanitize_data($_POST['shipment_id']) : 0;
        $type = isset($_POST['type']) ? wpst_sanitize_data($_POST['type']) : '';

        require_once WPST_PLUGIN_PATH. 'classes/Pdf.php';
        $pdf = new WPSTPdf($shipment_id);
        echo json_encode($pdf->print($type));
        die();
    }

    // Generate Report
    function wpst_generate_report()
    {
        global $sendtrace, $WPSTField;
        require_once WPST_PLUGIN_PATH. 'classes/Report.php';
        $report = new WPSTReport;

        $assigned_client = isset($_POST['assigned_client']) && is_numeric($_POST['assigned_client']) ? sanitize_text_field($_POST['assigned_client']) : 0;
        $shipper = isset($_POST['shipper']) ? sanitize_text_field($_POST['shipper']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';

        $meta_query = array();
        if (!empty($shipper)) {
            $meta_query[] = array(
                'key' => wpst_customer_field('shipper', 'key'),
                'value' => trim($shipper),
                'compare' => '='
            );
        }
        if (!empty($status)) {
            $meta_query[] = array(
                'key' => 'sendtrace_status',
                'value' => trim($status),
                'compare' => '='
            );
        }
        if ($assigned_client) {
            $meta_query[] = array(
                'key' => 'assigned_client',
                'value' => trim($assigned_client),
                'compare' => '='
            );
        }
        $args = array(
            'post_type' => 'sendtrace',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                $meta_query
            )
        );

        if (!empty($date_from) || !empty($date_to)) {
            $args['date_query'] = array();
            if (!empty($date_from)) {
                $args['date_query']['after'] = array(
                    'year' => date('Y', strtotime($date_from)),
                    'month' => date('m', strtotime($date_from)),
                    'day' => date('d', strtotime($date_from))
                );
            }
            if (!empty($date_to)) {
                $args['date_query']['before'] = array(
                    'year' => date('Y', strtotime($date_to)),
                    'month' => date('m', strtotime($date_to)),
                    'day' => date('d', strtotime($date_to))
                );
            }
            $args['date_query']['inclusive'] = true;
        }

        $result = array('status'=>'error');
        $posts = get_posts($args);
        $custom_fields = $WPSTField->fields();

        if (empty($posts)) {
            $result['error'] = __('No record(s) found.', 'sendtrace-shipments');
        }
        if (empty($custom_fields)) {
            $result['error'] = __('Custom fields is empty.', 'sendtrace-shipments');
        }

        $report_data = array();
        $report_headers = array(
            'tracking_no' => __('Tracking No.', 'sendtrace-shipments'), 
            'post_date' => __('Date Created', 'sendtrace-shipments'),
            'sendtrace_status' => __('Status', 'sendtrace-shipments'),
            'assigned_client' => __('Assigned Client', 'sendtrace-shipments')
        );        

        if (!empty($posts) && !empty($custom_fields)) {
            foreach ($posts as $post) {
                $meta_values = $sendtrace->get_shipment_details($post->ID);
                $report_data[$post->ID]['tracking_no'] = $post->post_title;
                $report_data[$post->ID]['post_date'] = date(wpst_date_format(), strtotime($post->post_date));
                $report_data[$post->ID]['sendtrace_status'] = get_post_meta($post->ID, 'sendtrace_status', true);
                $report_data[$post->ID]['assigned_client'] = wpst_get_user_data(get_post_meta($post->ID, 'assigned_client', true), 'display_name');
                foreach ($custom_fields as $section) {
                    foreach ($section['fields'] as $field) {
                        $meta_value = array_key_exists($field['key'], $meta_values) ? $meta_values[$field['key']] : '';
                        if (is_array($meta_value)) {
                            $meta_value = implode(', ', array_filter($meta_value));
                        }
                        $meta_value = apply_filters('wpst_report_data', $meta_value, $field['key'], $post->ID);
                        $report_data[$post->ID][$field['key']] = wpst_sanitize_data($meta_value);
                        $report_headers[$field['key']] = esc_html($field['label']);
                    }
                }
            
                $report_data[$post->ID]['packages'] = wpst_get_packages_data($post->ID, true, true);
            }
            $report_headers['packages'] = __('Pacakges', 'sendtrace-shipments');
        }

        $format = wpst_sanitize_data(apply_filters('wpst_report_file_format', 'csv'));
        $report_headers = wpst_sanitize_data(apply_filters('wpst_report_hearders', $report_headers, $custom_fields));
        $report_data = wpst_sanitize_data(apply_filters('wpst_report_data', $report_data, $posts));
        $report_result = $report->create_report($report_headers, $report_data, $format);  

        if (!array_key_exists('error', $result)) {
            $result['status'] = 'ok';
            $result['fileurl'] = $report_result['fileurl'];
            $result['filename'] = $report_result['filename'];
            $result['msg'] = '('.count($posts).') record(s) generated.';
        }
        
        echo json_encode($result);
        die();
    }

    function wpstcf_dropzone_upload() {
        $uploaded_url = '';
        $attach_id = 0;
        $basename = '';
        $meta_type = isset($_POST['meta_type']) ? wpst_sanitize_data($_POST['meta_type']) : '';

        if (!empty($_FILES)) {
            $_filter = true; // For the anonymous filter callback below.
            add_filter('upload_dir', function( $arr ) use( &$_filter ){
                if ( $_filter ) {
                    $folder = '/sendtrace'; // No trailing slash at the end.
                    $arr['path'] .= $folder;
                    $arr['url'] .= $folder;
                    $arr['subdir'] .= $folder;
                }
                return $arr;
            });

            $uploaded_bits = wp_upload_bits(
                $_FILES['file']['name'][0],
                null, //deprecated
                file_get_contents($_FILES['file']['tmp_name'][0]),
                null //time
            );

            if ( false !== $uploaded_bits['error'] ) {
                return $uploaded_bits['error'];			
            }

            $uploaded_file = $uploaded_bits['file'];
            $uploaded_url = $uploaded_bits['url'];
            $uploaded_filetype = wp_check_filetype( basename( $uploaded_bits['file'] ), null );
            $_filter = false;

            // Insert Attachment
            // Get the path to the upload directory.
            $wp_upload_dir = wp_upload_dir();
            $attachment = array(
                'guid'           => $uploaded_url, 
                'post_mime_type' => $uploaded_filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename($uploaded_file)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            // Insert the attachment.
            $attach_id = wp_insert_attachment($attachment, $uploaded_file);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_file);
            wp_update_attachment_metadata($attach_id, $attach_data);
            if($attach_id){
                update_post_meta( $attach_id, 'sendtrace_attachment', 1 );
            }
            $basename =  preg_replace('/\.[^.]+$/', '', basename($uploaded_file));
            echo json_encode(array( 
                'attactment_id' => $attach_id,
                'basename'      => $basename,
                'image_url'     => $uploaded_url
            ));
            do_action('wpstsd_after_dropdown_save', $meta_type, $attach_id, $basename, $uploaded_url);
        }
        die();
    }

    function add_update_address_book()
    {
        $ab_id = isset($_POST['ab_id']) ? wpst_sanitize_data($_POST['ab_id']) : 0;
        $action = isset($_POST['frm_action']) ? wpst_sanitize_data($_POST['frm_action']) : 0;
        $ab_type = isset($_POST['ab_type']) ? wpst_sanitize_data($_POST['ab_type']) : 'shipper';
        $fields_data = isset($_POST['fieldsData']) ? wpst_sanitize_data($_POST['fieldsData']) : [];
        
        $result = [
            'status' => 'success',
            'message' => 'Address book '.$action. ' successfully.'
        ];

        try {
            if ($action == 'add') {
                if (!wpst_is_ab_premium()) {
                    $count = wpst_get_ab_counts();
                    if ($count >= 8) {
                        throw new Exception('<strong>Address Book</strong> free version is limited only. Buy <strong>Premium Addon</strong> to unlock unlimited users.');
                    }
                }
                $ab_args = array(
                    'post_type' => 'address_book',
                    'post_status' => 'publish'
                );
                $ab_id = wp_insert_post($ab_args);
            }

            if (!$ab_id) {
                throw new Exception('ID not specified.');
            }

            if ($action == 'add') {
                update_post_meta($ab_id, '_type', $ab_type);
            }
            
            if (in_array($action, array('add', 'edit'))) {
                foreach ($fields_data as $key => $value) {
                    update_post_meta($ab_id, $key, $value);
                }
            } else if ($action == 'delete') {
                wp_delete_post($ab_id, true);
            }
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = $e->getMessage();
        }
        echo json_encode($result);
        die();
    }

    function get_address_book_data() {
        $ab_id = isset($_POST['ab_id']) ? wpst_sanitize_data($_POST['ab_id']) : 0;
        $result = [
            'status' => 'success',
            'message' => 'Able to retrieve data',
            'data' => []
        ];

        try {
            if (!$ab_id) {
                throw new Exception('Unable to retrieve data. Invalid ID.');
            }
            $result['data'] = wpst_get_shipment_meta_details($ab_id);
        } catch (Exception $e) {
            $result['status'] = 'danger';
            $result['message'] = $e->getMessage();
        }

        echo json_encode($result);
        die();
    }
}
new WPSTAjax;
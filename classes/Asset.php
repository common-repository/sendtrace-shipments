<?php

class WPSTAsset
{
    function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'wpst_enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'wpst_enqueue_frontend_scripts'));
    }

    function wpst_enqueue_admin_scripts()
    {
        global $sendtrace;
        require_once(WPST_PLUGIN_PATH. 'assets/css-root.php');

        /// Styles
        wp_enqueue_style('wpst-main', WPST_PLUGIN_URL. 'assets/css/main.min.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-selectize', WPST_PLUGIN_URL. 'assets/css/selectize.bootstrap5.min.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-selectize-helper', WPST_PLUGIN_URL. 'assets/css/selectize-helper.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-fontawesome', WPST_PLUGIN_URL. 'assets/css/font-awesome.min.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-bootstrap-datetimepicker', WPST_PLUGIN_URL. 'assets/css/bootstrap-datetimepicker.min.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-sendtrace', WPST_PLUGIN_URL. 'assets/css/admin/sendtrace.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-common', WPST_PLUGIN_URL. 'assets/css/common-styles.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-datatable', WPST_PLUGIN_URL. 'assets/css/dataTables.bootstrap5.min.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-tailwind', WPST_PLUGIN_URL. 'assets/css/tailwind.css', array(), WPST_VERSION);
        if (for_wpst_assets_only()) {
            wp_enqueue_style('wpst-custom-mdb', WPST_PLUGIN_URL. 'assets/css/custom-mdb.css', array(), WPST_VERSION);
            wp_enqueue_style('wpst-bootstrap', WPST_PLUGIN_URL. 'assets/css/bootstrap.min.css', array(), WPST_VERSION);
        }        
        
        // Scripts
        wp_enqueue_script('moment');
        $this->enqueue_core_scripts();
        wp_enqueue_script('wpst-datatable', WPST_PLUGIN_URL. 'assets/js/jquery.dataTables.min.js', array(), WPST_VERSION );
        wp_enqueue_script('wpst-datatable-bootstrap', WPST_PLUGIN_URL. 'assets/js/dataTables.bootstrap5.min.js', array(), WPST_VERSION );
        wp_enqueue_script('wpst-bootstrap-datetimepicker', WPST_PLUGIN_URL. 'assets/js/bootstrap-datetimepicker.min.js', array('jquery'), WPST_VERSION, true);
        wp_enqueue_script('wpst-datetime-picker-helper', WPST_PLUGIN_URL. 'assets/js/datetime-picker-helper.js', array('jquery'), WPST_VERSION, true);
        wp_enqueue_script('wpst-custom-script', WPST_PLUGIN_URL. 'assets/js/admin/wpst-custom-script.js', array('jquery', 'wp-color-picker'), WPST_VERSION);
        wp_enqueue_script('wpst-address-book', WPST_PLUGIN_URL. 'assets/js/address-book.js', array('jquery'), WPST_VERSION);
        
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        // Localize
        $translation = $this->ajax_translation();
        wp_localize_script('wpst-custom-script', 'WPSTAjax', $translation);
        wp_localize_script('wpst-selectize-helper', 'WPSTAjax', $translation);
    }

    function wpst_enqueue_frontend_scripts()
    {
        global $post, $sendtrace;
        require_once(WPST_PLUGIN_PATH. 'assets/css-root.php');
        $enqueue_shptrack_assets = apply_filters('enqueue_shptrack_assets' , false);
        
        if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'sendtrace_form') || $enqueue_shptrack_assets)) {
            // Styles
            $this->enqueue_core_styles();
        }
    }

    function ajax_translation()
    { 
        global $sendtrace, $WPSTField;
        $time_picker_format = new stdClass;
        $time_picker_format->format = 'hh:mm a';
        $date_picker_format = new stdClass;
        $date_picker_format->format = wpst_datepicker_format();
        $datetime_picker_format = new stdClass;
        $datetime_picker_format->format = wpst_calendar_datetime_format();
        $dim_divisor = array(
            'cubic' => $sendtrace->get_cubic_meter_divisor(),
            'volumetric' => $sendtrace->get_volumetric_weight_divisor()
        );

        $translation = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'is_admin' => is_admin() ? true : false,
            'notification' => isset($_POST['wpst_notification']) && !empty($_POST['wpst_notification']) ? wpst_sanitize_data($_POST['wpst_notification'], '', true) : [],
            'time_picker_format' => $time_picker_format,
            'date_picker_format' => $date_picker_format,
            'datetime_picker_format' => $datetime_picker_format,
            'is_debug' => isset($_GET['debug']) ? 1 : 0,
            'dim_divisor' => $dim_divisor,
            'customer_field' => wpst_customer_field(),
            'fields' => $WPSTField->fields(),
            'address_fields' => wpst_address_field()
        );
        return apply_filters('wpst_ajax_translation', $translation);
    }

    function enqueue_core_styles() {
        wp_enqueue_style('wpst-main', WPST_PLUGIN_URL. 'assets/css/main.min.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-selectize', WPST_PLUGIN_URL. 'assets/css/selectize.bootstrap5.min.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-selectize-helper', WPST_PLUGIN_URL. 'assets/css/selectize-helper.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-fontawesome', WPST_PLUGIN_URL. 'assets/css/font-awesome.min.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-bootstrap-datetimepicker', WPST_PLUGIN_URL. 'assets/css/bootstrap-datetimepicker.min.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-sendtrace', WPST_PLUGIN_URL. 'assets/css/admin/sendtrace.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-common', WPST_PLUGIN_URL. 'assets/css/common-styles.css', array(), WPST_VERSION);
        wp_enqueue_style('wpst-tailwind', WPST_PLUGIN_URL. 'assets/css/tailwind.css', array(), WPST_VERSION);
    }

    function enqueue_core_scripts() {
        wp_enqueue_script('wpst-bootstrap', WPST_PLUGIN_URL. 'assets/js/bootstrap.min.js', array('jquery'), WPST_VERSION);
        wp_enqueue_script('wpst-selectize', WPST_PLUGIN_URL. 'assets/js/selectize.min.js', array('jquery'), WPST_VERSION );
        wp_enqueue_script('wpst-selectize-helper', WPST_PLUGIN_URL. 'assets/js/selectize-helper.js', array(), WPST_VERSION );
        wp_enqueue_script('wpst-repeater', WPST_PLUGIN_URL. 'assets/js/repeater.min.js', array('jquery'), WPST_VERSION);
        wp_enqueue_script('wpst-repeater-helper', WPST_PLUGIN_URL. 'assets/js/repeater-helper.js', array('jquery'), WPST_VERSION);
        wp_enqueue_script('wpst-common-script', WPST_PLUGIN_URL. 'assets/js/common-script.js', array(), WPST_VERSION);
        $translation = $this->ajax_translation();
        wp_localize_script('wpst-common-script', 'WPSTAjax', $translation);
    }
}

$WPSTAsset = new WPSTAsset;
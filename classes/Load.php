<?php

class WPSTLoad
{
	static function wpst_load_textdomain()
	{
		load_plugin_textdomain('sendtrace-shipments', false, '/sendtrace/languages');
	}

	static function wpst_plugin_activation()
	{
		self::wpst_create_default_page();
	}

	static function wpst_create_default_page()
	{
		global $wpdb, $sendtrace;
		$sql = "SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE post_status = 'publish' AND `post_type` = %s AND post_content LIKE '%[sendtrace_form]%' LIMIT 1";
		$page_id = $wpdb->get_var($wpdb->prepare($sql, 'page'));
		if (!$page_id) {
			$page_agrs = array(
				'post_title' => 'Shipment Tracking Page',
                'post_content'  => '[sendtrace_form]',
				'post_type' => 'page',
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
				'post_date' => date('Y-m-d H:i:s'),
				'ping_status' => 'closed',
                'comment_status' => 'closed',
			);
			wp_insert_post($page_agrs, false);
		}
	}
}
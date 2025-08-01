<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}
global $wpdb;
$wpdb->query( "DELETE FROM `{$wpdb->postmeta}` WHERE (`meta_key` LIKE '%ns_product_internal_id%' OR `meta_key` LIKE '%netsuite%' `meta_key` LIKE '%ns_item_location_id%' OR `meta_key` LIKE '%ns_guest_customer_internal_id%' OR  `meta_key` LIKE '%ns_order_refund_internal_id%' OR  `meta_key` LIKE '%ns_order_external_id%') " );
$wpdb->query( "DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` LIKE '%ns_customer_internal_id%'" );
$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE (`option_name` LIKE '%tmwni_%' OR  `option_name` LIKE '%netstuite_%' OR `option_name` LIKE '%_cm_options%' OR  `option_name` LIKE '%ns_woo_%') " );
$wpdb->logs_table = $wpdb->prefix . 'tm_woo_netsuite_logs';
$wpdb->dashboard_table_name = $wpdb->prefix . 'tm_woo_netsuite_auto_sync_order_status';
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->logs_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->dashboard_table_name}" );
$wpdb->netsuite_order_logs = $wpdb->prefix . 'tm_woo_netsuite_auto_sync_order_status';
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->netsuite_order_logs}" );
wp_clear_scheduled_hook( 'tm_ns_process_inventories' );
wp_clear_scheduled_hook( 'tm_ns_fetch_order_tracking_info' );

<?php
// Define the WordPress installation abspath.
if ( ! defined( 'WP_ABSPATH' ) ) {
	define( 'WP_ABSPATH', dirname( dirname(__FILE__) ) . '/' );
}

// Include wp-load.php to load the WordPress environment.
require_once( WP_ABSPATH . 'wp-load.php' );

// Exit if accessed directly.
if( ! defined( 'ABSPATH' ) ) {
	die( header( 'HTTP/1.0 403 Forbidden' ) );
}

// Define the trek travel integration plugin abspath.
if ( ! defined( 'TT_NS_INTEGRATION_PLUGIN_ABSPATH' ) ) {
	define( 'TT_NS_INTEGRATION_PLUGIN_ABSPATH', trailingslashit( dirname( 'wp-content/plugins/trek-travel-netsuite-integration/trek-travel-netsuite-integration.php' ) ) );
}

// Include all the NetSuite sync functions.
require_once( WP_ABSPATH . TT_NS_INTEGRATION_PLUGIN_ABSPATH . 'inc/ttnsw-cron-functions.php' );

/**
 * Locking Status sync functions.
 *
 * @param string DEFAULT_TIME_RANGE_LOCKING_STATUS The default time in hours,
 * indicating how many hours back before the sync starts
 * to fetch the modified data from NetSuite.
 */
function tt_ns_wc_sync_cron() {
	tt_add_error_log( '[Server Side Cron][START - Locking Status Sync]', array( 'default_time_range' => DEFAULT_TIME_RANGE_LOCKING_STATUS ), array( 'date_time' => date('Y-m-d H:i:s') ) );
	tt_ns_fetch_registration_ids();
	tt_add_error_log( '[Server Side Cron][END - Locking Status Sync]', array( 'default_time_range' => DEFAULT_TIME_RANGE_LOCKING_STATUS ), array( 'date_time' => date('Y-m-d H:i:s') ) );
}

try {
	// Run the Locking Status Sync Cron.
	tt_ns_wc_sync_cron();
} catch ( Exception $e ) {
	error_log( "TT_NS_LOCKING_STATUS_SYNC_CRON_ERROR: ", $e->getMessage() );
}

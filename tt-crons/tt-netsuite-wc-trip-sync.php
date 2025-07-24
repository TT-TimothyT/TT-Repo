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
 * Trip sync sequence of functions.
 *
 * @param string DEFAULT_TIME_RANGE The default time in hours,
 * indicating how many hours back before the sync starts
 * to fetch the modified data from NetSuite.
 */
function tt_ns_wc_sync_cron() {
	tt_add_error_log( '[Server Side Cron][START - Trips/Products Sync]', array( 'default_time_range' => DEFAULT_TIME_RANGE ), array( 'date_time' => date('Y-m-d H:i:s') ) );
	tt_sync_ns_trips();
	tt_sync_ns_trip_details();
	tt_sync_wc_products_from_ns();

	// Flush the cache.
	$cache_flushed = wp_cache_flush();
	tt_add_error_log( '[Server Side Cron][END - Trips/Products Sync]', array( 'default_time_range' => DEFAULT_TIME_RANGE ), array( 'cache_flushed' => $cache_flushed, 'date_time' => date('Y-m-d H:i:s') ) );
}

try {
	// Run the Trip and Product Sync Cron.
	tt_ns_wc_sync_cron();
} catch ( Exception $e ) {
	error_log( "TT_NS_TRIP_SYNC_CRON_ERROR: " . $e->getMessage() );
}

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

// Include all the NetSuite sync functions.
require_once( WP_ABSPATH . 'wp-content/themes/trek-travel-theme/inc/trek-old-trip-dates.php' );

/**
 * Old trips type update function.
 */
function tt_wc_old_trips_update_type_cron() {
	tt_add_error_log( '[Server Side Cron][START - Old Trips Type Updates]', array( 'default_time_range' => DEFAULT_TIME_RANGE ), array( 'date_time' => date('Y-m-d H:i:s') ) );

	// Create an instance of the TT_Old_Trip_Date_Product_Type class
	$old_trip_date_product_type = new TT_Old_Trip_Date_Product_Type();

	// Call the method to update old trip date products
	$old_trip_date_product_type->tt_update_old_trip_date_products_type();

	// Flush the cache.
	$cache_flushed = wp_cache_flush();
	tt_add_error_log( '[Server Side Cron][END - Old Trips Type Updates]', array( 'default_time_range' => DEFAULT_TIME_RANGE ), array( 'cache_flushed' => $cache_flushed, 'date_time' => date('Y-m-d H:i:s') ) );
}

try {
	// Run the Old Trip Type Update.
	tt_wc_old_trips_update_type_cron();
} catch ( Exception $e ) {
	error_log( "TT_WC_PRODUCT_TYPE_UPDATE ", $e->getMessage() );
}

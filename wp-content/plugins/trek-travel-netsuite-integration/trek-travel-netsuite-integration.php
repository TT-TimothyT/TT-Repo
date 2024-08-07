<?php
/**
 * Plugin Name: Trek Travel NetSuite Integration
 * Description: Trek Travel NetSuite Integration
 * Version: 1.0.0
 * Author: Dharmesh Panchal
 * Text Domain: trek-travel-netsuite-integration
 * WC requires at least: 2.2
 * WC tested up to: 5.6
 */

defined( 'ABSPATH' ) || exit;

add_action('plugins_loaded', 'plugins_loaded_trek_ns_integration', 1);
function plugins_loaded_trek_ns_integration(){
    if (is_multisite()) {
        $active_plugins = get_site_option( 'active_sitewide_plugins' );
        if ( !isset( $active_plugins[ 'woocommerce/woocommerce.php' ] ) &&  !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( '/trek-travel-netsuite-integration/trek-travel-netsuite-integration.php', true );
		}
    }else{
        if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( '/trek-travel-netsuite-integration/trek-travel-netsuite-integration.php', true );
		}
    }
}
register_activation_hook(__FILE__, 'register_activation_trek_ns_integration');
function register_activation_trek_ns_integration(){
  global $wpdb;
  $table_name_1 = $wpdb->prefix . 'netsuite_trips';
  $table_name_2 = $wpdb->prefix . 'netsuite_trip_detail';
  $table_name_3 = $wpdb->prefix . 'netsuite_trip_hotels';
  $table_name_4 = $wpdb->prefix . 'netsuite_trip_bikes';
  $table_name_5 = $wpdb->prefix . 'netsuite_trip_addons';
  $table_name_6 = $wpdb->prefix . 'netsuite_sync_logs';
  $charset_collate = '';
  if (!empty($wpdb->charset)) {
      $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
  }
  if (!empty($wpdb->collate)) {
      $charset_collate .= " COLLATE {$wpdb->collate}";
  }
  // Check if table 1 empty 
  if ($wpdb->get_var($wpdb->prepare('show tables like %s', $table_name_1)) != $table_name_1) {
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      $sql_1 = 'CREATE TABLE `' . $table_name_1 . "` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tripId` int(11) NULL,
          `tripCode` varchar(100) NULL,
          `tripName` varchar(100) NULL,
          `itineraryId` varchar(100) NULL,
          `itineraryCode` varchar(100) NULL,
          `lastModifiedDate` varchar(100) NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
      ) $charset_collate;";
      dbDelta($sql_1);
  }
  if ($wpdb->get_var($wpdb->prepare('show tables like %s', $table_name_2)) != $table_name_2) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql_2 = 'CREATE TABLE `' . $table_name_2 . "` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tripId` int(11) NULL,
        `tripCode` varchar(100) NULL,
        `isRideCamp` varchar(40) NULL,
        `isLateDepositAllowed` varchar(40) NULL,
        `depositBeforeDate` varchar(100) NULL,
        `removeFromStella` varchar(100) NULL,
        `capacity` varchar(100) NULL,
        `booked` varchar(100) NULL,
        `remaining` varchar(100) NULL,
        `status` text NULL,
        `startDate` varchar(100) NULL,
        `endDate` varchar(100) NULL,
        `daysToTrip` varchar(100) NULL,
        `tripYear` varchar(100) NULL,
        `tripSeason` varchar(100) NULL,
        `tripMonth` varchar(100) NULL,
        `tripContinent` varchar(100) NULL,
        `tripCountry` varchar(100) NULL,
        `tripRegion` varchar(100) NULL,
        `itineraryId` varchar(100) NULL,
        `itineraryCode` varchar(100) NULL,
        `lastModifiedDate` varchar(100) NULL,
        `riderType` text NULL,
        `product_line` text NULL,
        `subStyle` text NULL,
        `basePrice` varchar(100) NULL,
        `singleSupplementPrice` varchar(100) NULL,
        `depositAmount` varchar(100) NULL,
        `bikeUpgradePrice` varchar(100) NULL,
        `insurancePercentage` varchar(100) NULL,
        `taxRate` varchar(100) NULL,
        `ssSoldOut` varchar(20) NULL,
        `isOpenToRoommateDisabled` varchar(30) NULL,
        `bookablePeriods` text NULL,
        `hotels` text NULL,
        `bikes` text NULL,
        `addOns` text NULL,
        `tripSpecificMessage` text NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) $charset_collate;";
    dbDelta($sql_2);
  }
  if ($wpdb->get_var($wpdb->prepare('show tables like %s', $table_name_3)) != $table_name_3) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql_3 = 'CREATE TABLE `' . $table_name_3 . "` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tripId` int(11) NULL,
        `tripCode` varchar(100) NULL,
        `hotelId` varchar(100) NULL,
        `hotelName` varchar(100) NULL,
        `rooms` text NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) $charset_collate;";
    dbDelta($sql_3);
  }
  if ($wpdb->get_var($wpdb->prepare('show tables like %s', $table_name_4)) != $table_name_4) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql_4 = 'CREATE TABLE `' . $table_name_4 . "` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tripId` int(11) NULL,
        `tripCode` varchar(100) NULL,
        `bikeId` varchar(100) NULL,
        `bikeDescr` text NULL,
        `bikeType` text NULL,
        `bikeSize` text NULL,
        `total` varchar(100) NULL,
        `allocated` varchar(100) NULL,
        `available` varchar(100) NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) $charset_collate;";
    dbDelta($sql_4);
  }
  if ($wpdb->get_var($wpdb->prepare('show tables like %s', $table_name_5)) != $table_name_5) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql_4 = 'CREATE TABLE `' . $table_name_5 . "` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tripId` int(11) NULL,
        `tripCode` varchar(100) NULL,
        `itemId` varchar(100) NULL,
        `itemDescr` text NULL,
        `itemBasePrice` varchar(100) NULL,
        `total` varchar(100) NULL,
        `allocated` varchar(100) NULL,
        `available` varchar(100) NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) $charset_collate;";
    dbDelta($sql_4);
  }
  if ($wpdb->get_var($wpdb->prepare('show tables like %s', $table_name_6)) != $table_name_6) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql_1 = 'CREATE TABLE `' . $table_name_6 . "` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `scriptId` varchar(100) NULL,
        `scriptArgs` text NULL,
        `response` text NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) $charset_collate;";
    dbDelta($sql_1);
  }
}
define('TTNSW_DIR', plugin_dir_path(__FILE__));
define('TTNSW_URL', plugin_dir_url(__FILE__));
require_once TTNSW_DIR.'inc/ttnsw-settings.php';
require_once TTNSW_DIR.'inc/ttnsw-constants.php';
require_once TTNSW_DIR.'/inc/NetSuiteClient.php';
require_once TTNSW_DIR.'/inc/OAuthRequest.php';
require_once TTNSW_DIR.'inc/ttnsw-cron-functions.php';
require_once TTNSW_DIR.'inc/ttnsw-general-functions.php';
require_once TTNSW_DIR.'inc/ttnsw-fetch-ns-items.php';

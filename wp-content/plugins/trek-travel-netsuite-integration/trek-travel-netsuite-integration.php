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
  // Create E-comm User Data Admin role
  add_role(
      'ecomm_user_data_admin',
      __('E-comm User Data Admin', 'trek-travel-netsuite-integration'),
      array(
          'read' => true,
          'view_admin_dashboard' => true,
          'manage_guest_data' => true // Custom capability
      )
  );

  global $wpdb;
  $table_name_1 = $wpdb->prefix . 'netsuite_trips';
  $table_name_2 = $wpdb->prefix . 'netsuite_trip_detail';
  $table_name_3 = $wpdb->prefix . 'guest_bookings';
  $table_name_4 = $wpdb->prefix . 'netsuite_sync_logs';
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
        `subStyle` text NULL,
        `product_line` text NULL,
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
        `SmugMugLink` varchar(255) NULL,
        `SmugMugPassword` varchar(255) NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) $charset_collate;";
    dbDelta($sql_2);
  }
  // Check if table empty
  if ($wpdb->get_var($wpdb->prepare('show tables like %s', $table_name_3)) != $table_name_3) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql_3 = 'CREATE TABLE `' . $table_name_3 . "` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ns_trip_booking_id` int(11) NULL,
        `guestRegistrationId` int(11) NULL,
        `is_guestreg_cancelled` int(11) DEFAULT 0,
        `guest_index_id` int(11) NULL,
        `order_id` int(11) NULL,
        `product_id` int(11) NULL,
        `user_id` int(11) NULL,
        `netsuite_guest_registration_id` int(11) NULL,
        `itinerary_id` int(11) NULL,
        `tripDateId` int(11) NULL,
        `wantPrivate` int(11) DEFAULT 0,
        `referralSourceType` int(11) DEFAULT NULL,
        `referralSourceName` varchar(100) NULL,
        `bikeUpgradePriceDisplayed` int(11) DEFAULT NULL,
        `specialRoomRequests` varchar(500) NULL,
        `bike_comments` varchar(100) NULL,
        `promoCode` varchar(100) NULL,
        `trip_code` varchar(100) NULL,
        `trip_name` varchar(100) NULL,
        `trip_city` varchar(100) NULL,
        `trip_region` varchar(100) NULL,
        `trip_total_amount` varchar(100) NULL,
        `trip_number_of_guests` tinyint(10) NULL,
        `trip_room_selection` varchar(100) NULL,
        `trip_start_date` varchar(100) NULL,
        `trip_end_date` varchar(100) NULL,
        `guest_is_primary` int(11) DEFAULT 0,
        `guest_first_name` varchar(100) NULL,
        `guest_last_name` varchar(100) NULL,
        `guest_email_address` varchar(100) NULL,
        `guest_phone_number` varchar(100) NULL,
        `guest_gender` varchar(100) NULL,
        `guest_date_of_birth` varchar(100) NULL,
        `bike_selection` varchar(100) NULL,
        `helmet_selection` varchar(100) NULL,
        `rider_height` varchar(100) NULL,
        `rider_level` varchar(100) NULL,
        `activity_level` varchar(100) NULL,
        `bike_id` varchar(100) NULL,
        `bike_size` varchar(100) NULL,
        `pedal_selection` varchar(100) NULL,
        `saddle_height` varchar(100) NULL,
        `saddle_bar_reach_from_saddle` varchar(100) NULL,
        `saddle_bar_height_from_wheel_center` varchar(100) NULL,
        `jersey_style` varchar(100) NULL,
        `tt_jersey_size` varchar(100) NULL,
        `tshirt_size` varchar(100) NULL,
        `race_fit_jersey_size` varchar(100) NULL,
        `shorts_bib_size` varchar(100) NULL,
        `emergency_contact_first_name` varchar(100) NULL,
        `emergency_contact_last_name` varchar(100) NULL,
        `emergency_contact_phone` varchar(100) NULL,
        `emergency_contact_relationship` varchar(100) NULL,
        `medical_conditions` text NULL,
        `medications` text NULL,
        `allergies` text NULL,
        `dietary_restrictions` text NULL,
        `waiver_signed` int(11) DEFAULT 0,
        `passport_number` varchar(100) NULL,
        `passport_issue_date` varchar(100) NULL,
        `passport_expiration_date` varchar(100) NULL,
        `passport_place_of_issue` varchar(100) NULL,
        `full_name_on_passport` varchar(100) NULL,
        `shipping_address_1` varchar(100) NULL,
        `shipping_address_2` varchar(100) NULL,
        `shipping_address_city` varchar(100) NULL,
        `shipping_address_state` varchar(100) NULL,
        `shipping_address_country` varchar(100) NULL,
        `shipping_address_zipcode` varchar(100) NULL,
        `wantsInsurance` varchar(100) NULL,
        `insuranceAmount` varchar(100) NULL,
        `addOnIds` varchar(400) NULL,
        `bikeTypeName` varchar(100) NULL,
        `isBikeUpgrade` int(11) DEFAULT 0,
        `ns_booking_status` int(11) DEFAULT 0,
        `ns_booking_response` text NULL,
        `isDraftBooking` varchar(100) NULL,
        `shouldSendDraftConfirmEmail` varchar(50) NULL,
        `releaseFormId` varchar(50) NULL,
        `modified_at` varchar(100) NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) $charset_collate;";
    dbDelta($sql_3);
  }
  if ($wpdb->get_var($wpdb->prepare('show tables like %s', $table_name_4)) != $table_name_4) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql_4 = 'CREATE TABLE `' . $table_name_4 . "` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `scriptId` varchar(100) NULL,
        `scriptArgs` text NULL,
        `response` text NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) $charset_collate;";
    dbDelta($sql_4);
  }
}

// Add deactivation hook
register_deactivation_hook(__FILE__, 'register_deactivation_trek_ns_integration');
function register_deactivation_trek_ns_integration() {
    // Remove role on plugin deactivation
    remove_role('ecomm_user_data_admin');
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

// Custom WP-CLI Commands.
include_once TTNSW_DIR.'inc/ttnsw-wp-cli.php';

require_once TTNSW_DIR . 'inc/ttnsw-db-helpers.php';

<?php
/**
 * Common constants for all environments
 */
$cancellation_policy_page_id   = get_option( 'tt_opt_cancellation_policy_page_id' ) ? get_option( 'tt_opt_cancellation_policy_page_id' ) : NULL;
$cancellation_policy_page_link = $cancellation_policy_page_id ? get_the_permalink( $cancellation_policy_page_id ) : '';

define( 'TREK_DIR', get_template_directory_uri() );
define( 'TREK_PATH', get_template_directory() );
define( 'TREK_MY_ACCOUNT_PID', get_option('woocommerce_myaccount_page_id') );
define( 'DEFAULT_IMG', 'https://via.placeholder.com/90?text=Trek%20Travel' );
define( 'G_CAPTCHA_SITEKEY', '6LfNqogpAAAAAEoQ66tbnh01t0o_2YXgHVSde0zV' );
define( 'TT_WC_META_PREFIX', 'tt_meta_' );
define( 'TT_OPTION_PREFIX', 'tt_option_' );
define( 'TT_CAN_POLICY_PAGE', $cancellation_policy_page_link );
define( 'TT_LINE_ITEMS_PRODUCTS', ['TTWP23FEES' => ['name' => 'Travel Protection', 'price' => 999], 'TTWP23SUPP' => ['name' => 'Single Supplement Fees', 'price' => 1200], 'TTWP23UPGRADES' => ['name' => 'Bike Upgrades', 'price' => 399]] );
define( 'TT_ACTIVITY_DASHBOARD_NAME_BIKING', 'Cycling' );
define( 'TT_ACTIVITY_DASHBOARD_NAME_HW', 'Hiking &amp; Walking' );
define( 'TT_BOOKING_STATUSES', array( 
    'booking_success'        => array(
        'title'   => 'Booking Success',
        'tooltip' => 'Booking has been successfully created in NetSuite and linked to this order',
        'style'   => 'background: #c6e1c6;color: #5b841b;',
    ),
    'booking_failed'         => array(
        'title'   => 'Booking Failed',
        'tooltip' => 'There is no Booking in NetSuite, linked to this Order! Please review the logs for more information about the error that occurred',
        'style'   => 'background: #eba3a3;color: #761919;',
    ),
    'booking_pending'        => array(
        'title'   => 'Booking Pending',
        'tooltip' => 'In progress! The booking information is not yet available as the order details are still being sent to NetSuite',
        'style'   => 'background: #f8dda7;color: #94660c;',
    ),
    'booking_onhold'         => array(
        'title'   => 'Booking On hold',
        'tooltip' => 'The order was not sent to NetSuite! Something went wrong with importing the details into the guest booking table',
        'style'   => 'background: #f8dda7;color: #94660c;',
    ),
    'booking_cancelled'      => array(
        'title'   => 'Booking Cancelled',
        'tooltip' => 'The Booking is fully canceled in NetSuite; there are no active guest registrations',
        'style'   => 'background: #c8d7e1;color: #2e4453;',
    ),
    'registration_cancelled' => array(
        'title'   => 'Registration Cancelled',
        'tooltip' => 'This Booking has partially canceled guest registrations in NetSuite',
        'style'   => 'background: #c8d7e1;color: #2e4453;',
    ),
    'booking_unknown'        => array(
        'title'   => 'Booking Unknown',
        'tooltip' => 'The Booking status cannot be determined :-(',
        'style'   => 'background: #e5e5e5;color: #777;',
    ),
) );
define( 'TT_HIDE_ORDER_BOOKING_STATUSES', array( 'booking_failed', 'booking_pending', 'booking_onhold' ) );

/**
 * If we are not running the dev environment, load the Production Constants.
 */
if( ! defined( 'DX_DEV' ) ) {
    /**
     * Production Constants.
     */

    // Insurance API Credentials.
    define( 'TREK_INSURANCE_UNAME', 'APIWebUSERTREKTRAV@archroamright.com' );
    define( 'TREK_INSURANCE_PASS', '9w04U5jI]8#0' );
    define( 'TREK_INRURANCE_API_URL', 'https://services.archinsurancesolutions.com/PartnerService/api' );

    // Waiver URL.
    define( 'TT_WAIVER_URL', 'https://661527.extforms.netsuite.com/app/site/hosting/scriptlet.nl?script=40&deploy=1&compid=661527&ns-at=AAEJ7tMQzhyEX0U40Wl4gEO2yqGsbUpiMQcWPLUNM9W00rDQ19A&whence=' );
} else {
    /**
     * Dev Constants.
     */

    // Insurance API Credentials.
    define( 'TREK_INSURANCE_UNAME', 'APIUSERTREKTRAV@test.roamright.com' );
    define( 'TREK_INSURANCE_PASS', 'Hosing+Chips+raps1' );
    define( 'TREK_INRURANCE_API_URL', 'https://testservices.archinsurancesolutions.com/PartnerService/api' );

    // Waiver URL.
    define( 'TT_WAIVER_URL', 'https://661527-sb2.extforms.netsuite.com/app/site/hosting/scriptlet.nl?script=40&deploy=1&compid=661527_SB2&ns-at=AAEJ7tMQFVtLIEl7xtRYIBUG9bmirz5DbmC7CC4I7PLYu17JV-0&whence=' );
}

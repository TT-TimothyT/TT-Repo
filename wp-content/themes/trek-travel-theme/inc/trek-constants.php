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
define( 'TT_ACTIVITY_DASHBOARD_NAME_HW', 'Hiking and Walking' );

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
    define( 'TT_WAIVER_URL', 'https://661527.extforms.netsuite.com/app/site/hosting/scriptlet.nl?script=40&deploy=1&compid=661527&h=1d9367cf147b5322893e&whence=' );
} else {
    /**
     * Dev Constants.
     */

    // Insurance API Credentials.
    define( 'TREK_INSURANCE_UNAME', 'APIUSERTREKTRAV@test.roamright.com' );
    define( 'TREK_INSURANCE_PASS', 'Hosing+Chips+raps1' );
    define( 'TREK_INRURANCE_API_URL', 'https://testservices.archinsurancesolutions.com/PartnerService/api' );

    // Waiver URL.
    define( 'TT_WAIVER_URL', 'https://661527-sb2.extforms.netsuite.com/app/site/hosting/scriptlet.nl?script=40&deploy=1&compid=661527_SB2&h=629b0eb96224bcaa55bd&whence=' );
}

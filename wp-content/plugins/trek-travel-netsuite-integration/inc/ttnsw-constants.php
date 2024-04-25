<?php
 /**
 * If we are not running the dev environment, load the Production scripts
 */

 /**
  * @TODO: we have to update the NetSuite Production API keys and script keys once
  * https://app.asana.com/0/1205472772784381/1206579514981757 is ready
  */
if ( ! defined( 'DX_DEV' ) ) {
    define( 'TT_NS_HOST', '661527.restlets.api.netsuite.com' );
    define( 'TT_NS_RESTLET_URL', 'https://661527.restlets.api.netsuite.com/app/site/hosting/restlet.nl');
    define( 'TT_NS_ACCOUNT_ID', '661527');
    define( 'TT_NS_CONSUMER_KEY', '42903a90e867f7f6e7dea26b86105ac173c2b85775496abff19c1588c817d807');
    define( 'TT_NS_CONSUMER_SECRET', 'cc8428d5300e645dc8aef4524a8be15187852108a33649e073637448cc585cee');
    define( 'TT_NS_TOKEN_ID', 'e785506906fafb0a8aaa8c723fd34f30bd32e4f634781313444fc866f5d1332b');
    define( 'TT_NS_TOKEN_SECRET', '8a7ec82c6bf78cd91199379d6e7548f9268c66bb2d5951a515db3caa9a00001d');
    define( 'TRIPS_SCRIPT_ID', '1296:1' ); //214 -old ID, New ID - 1296
    define( 'BOOKING_SCRIPT_ID', '1298:1' );
    define( 'TRIP_DETAIL_SCRIPT_ID', '1297:1' ); //211 -old ID, New ID - 1297
    define( 'LISTS_SCRIPT_ID', '1299:1' );
    define( 'TRIP_MSG_SCRIPT_ID', 1297 );
    define( 'USER_BOOKINGS_SCRIPT_ID', '1305:1' );
    define( 'GUESTS_TO_SYNC_SCRIPT_ID', '1306:1' );
    define( 'GET_BOOKING_SCRIPT_ID', '1304:1' );
    define( 'GET_REGISTRATIONS_SCRIPT_ID', '1294:1' );
    define( 'REFERRAL_SOURCE_SCRIPT_ID', '1475:1' );
    define( 'CHECKLIST_SCRIPT_ID', '1292:1' );
    define( 'DEFAULT_TIME_RANGE', '-12 hours' ); // Define a default time range for sync, when no parameter is passed.
    define( 'GET_MODIFIED_REGISTRATIONS', '1293:1' );
} else {
    define( 'TT_NS_HOST', '661527-sb2.restlets.api.netsuite.com' );
    define( 'TT_NS_RESTLET_URL', 'https://661527-sb2.restlets.api.netsuite.com/app/site/hosting/restlet.nl');
    define( 'TT_NS_ACCOUNT_ID', '661527_SB2');
    define( 'TT_NS_CONSUMER_KEY', '878e51745ccd718aef92210ff20f325fd91120c762bc595934aa91d1f1d4c4f5');
    define( 'TT_NS_CONSUMER_SECRET', '051cec7bdfc2918977a53c422dcf07112d1aec43900789f25b0a6c0fce486ab6');
    define( 'TT_NS_TOKEN_ID', 'f442d6cbe8c00b2580c2637568cd5a84727ccb4fc8305589ab78b9f0a88a65f0');
    define( 'TT_NS_TOKEN_SECRET', '6f0aed48174e70349aef5f7d14901ce49ecf006beaadb2250cc06b56c70142ef');
    define( 'TRIPS_SCRIPT_ID', '1296:2' ); //214 -old ID, New ID - 1296
    define( 'BOOKING_SCRIPT_ID', '1298:2' );
    define( 'TRIP_DETAIL_SCRIPT_ID', '1297:2' ); //211 -old ID, New ID - 1297
    define( 'LISTS_SCRIPT_ID', '203:2' );
    define( 'TRIP_MSG_SCRIPT_ID', 1297);
    define( 'USER_BOOKINGS_SCRIPT_ID', '1305:2' );
    define( 'GUESTS_TO_SYNC_SCRIPT_ID', '1306:2' );
    define( 'GET_BOOKING_SCRIPT_ID', '1304:2' );
    define( 'GET_REGISTRATIONS_SCRIPT_ID', '1294:2' );
    define( 'REFERRAL_SOURCE_SCRIPT_ID', '1475:2' );
    define( 'CHECKLIST_SCRIPT_ID', '1292:2' );
    define( 'DEFAULT_TIME_RANGE', '-12 hours' ); // Define a default time range for sync, when no parameter is passed.
    define( 'GET_MODIFIED_REGISTRATIONS', '1293:2' );

}

// Alter Query
/*
1. ALTER TABLE `wp_netsuite_trip_detail` ADD `tripSpecificMessage` TEXT NULL DEFAULT NULL AFTER `addOns`;
*/

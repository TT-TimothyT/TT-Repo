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
    define( 'USER_BOOKINGS_SCRIPT_ID', '1305:1' );
    define( 'GUESTS_TO_SYNC_SCRIPT_ID', '1306:1' );
    define( 'GET_BOOKING_SCRIPT_ID', '1304:1' );
    define( 'GET_REGISTRATIONS_SCRIPT_ID', '1294:1' );
    define( 'REFERRAL_SOURCE_SCRIPT_ID', '1475:1' );
    define( 'CHECKLIST_SCRIPT_ID', '1292:1' );
    define( 'DEFAULT_TIME_RANGE', '-9 hours' ); // Define a default time range for sync, when no parameter is passed.
    define( 'DEFAULT_TIME_RANGE_LOCKING_STATUS', '-4 hours' ); // Define a default time range for sync of bike and checklist locking status, when no parameter is passed.
    define( 'GET_MODIFIED_REGISTRATIONS', '1293:1' );
    define( 'GET_BIKE_MODELS_SCRIPT_ID', '1301:1' );
    define( 'HIKER_BIKE_ID', 50919 );
} else {
    define( 'TT_NS_HOST', '661527-sb2.restlets.api.netsuite.com' );
    define( 'TT_NS_RESTLET_URL', 'https://661527-sb2.restlets.api.netsuite.com/app/site/hosting/restlet.nl');
    define( 'TT_NS_ACCOUNT_ID', '661527_SB2');
    define( 'TT_NS_CONSUMER_KEY', 'bd53de620f2e03bf9cb654cfa92eaa8f57c3020d109ccd2cc0187ceab53be352');
    define( 'TT_NS_CONSUMER_SECRET', '85e530c99cc0ce98b2bd8e2ea51f26f7c80d38b92448345dd2900a0eec8481a3');
    define( 'TT_NS_TOKEN_ID', '394688941cfea2edfdc4431a4afa318d9191cf3777a5c3a36e7e8c36e4472a87');
    define( 'TT_NS_TOKEN_SECRET', 'e993e3c52dee6356016ae87ca8dce0ceff7fe908b96f800b9e69932a8db84518');
    define( 'TRIPS_SCRIPT_ID', '1296:2' ); //214 -old ID, New ID - 1296
    define( 'BOOKING_SCRIPT_ID', '1298:2' );
    define( 'TRIP_DETAIL_SCRIPT_ID', '1297:2' ); //211 -old ID, New ID - 1297
    define( 'LISTS_SCRIPT_ID', '1299:2' );
    define( 'USER_BOOKINGS_SCRIPT_ID', '1305:2' );
    define( 'GUESTS_TO_SYNC_SCRIPT_ID', '1306:2' );
    define( 'GET_BOOKING_SCRIPT_ID', '1304:2' );
    define( 'GET_REGISTRATIONS_SCRIPT_ID', '1294:2' );
    define( 'REFERRAL_SOURCE_SCRIPT_ID', '1475:2' );
    define( 'CHECKLIST_SCRIPT_ID', '1292:2' );
    define( 'DEFAULT_TIME_RANGE', '-3 hours' ); // Define a default time range for sync, when no parameter is passed.
    define( 'DEFAULT_TIME_RANGE_LOCKING_STATUS', '-2 hours' ); // Define a default time range for sync of bike and checklist locking status, when no parameter is passed.
    define( 'GET_MODIFIED_REGISTRATIONS', '1293:2' );
    define( 'GET_BIKE_MODELS_SCRIPT_ID', '1301:2' );
    define( 'HIKER_BIKE_ID', 50919 );
}

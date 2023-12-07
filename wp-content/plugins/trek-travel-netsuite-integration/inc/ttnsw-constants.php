<?php
define('TT_NS_HOST', '661527-sb2.restlets.api.netsuite.com');
define('TT_NS_RESTLET_URL', "https://661527-sb2.restlets.api.netsuite.com/app/site/hosting/restlet.nl");
define('TT_NS_ACCOUNT_ID', "661527_SB2");
define('TT_NS_CONSUMER_KEY', "c488f4c7b9c29e0675151134c35ed9ab40177d73107e284fdeba7e2837ef775d");
define('TT_NS_CONSUMER_SECRET', "53ac95afb58b5181e209a31b65403cbafa19fd054bac93ba15e2e0d139b6385e");
define('TT_NS_TOKEN_ID', "e69b6d804a464844c0bf03c4050263a5e8115145702179eeca343a1c552c0be2");
define('TT_NS_TOKEN_SECRET', "2e3ea493c973bff4bb808c6ab4e283ab5775d34a0471cc4cc3942266a4382bb1");
define('TRIPS_SCRIPT_ID', '1296:2'); //214 -old ID, New ID - 1296
define('BOOKING_SCRIPT_ID', 1298);
define('TRIP_DETAIL_SCRIPT_ID', '1297:2'); //211 -old ID, New ID - 1297
define('LISTS_SCRIPT_ID', '203:2');
define('TRIP_MSG_SCRIPT_ID', 1297);
define('USER_BOOKINGS_SCRIPT_ID', '1305:2');
define('REFERRAL_SOURCE_SCRIPT_ID', '1475:2');
define('DEFAULT_TIME_RANGE', '-12 hours'); // Define a default time range for sync, when no parameter is passed.

// Alter Query
/*
1. ALTER TABLE `wp_netsuite_trip_detail` ADD `tripSpecificMessage` TEXT NULL DEFAULT NULL AFTER `addOns`;
*/
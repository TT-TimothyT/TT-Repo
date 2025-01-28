<h2><?php _e('Detailed Sync Functions', 'trek-travel-netsuite-integration'); ?></h2>

<h3><?php _e('Main Sync Steps', 'trek-travel-netsuite-integration'); ?></h3>

<h4><?php _e('Step 1: Get All Trips', 'trek-travel-netsuite-integration'); ?></h4>
<p><?php _e('This step:', 'trek-travel-netsuite-integration'); ?></p>
<ul>
    <li><?php _e('Fetches basic trip data from NetSuite using script ID 1296', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Stores trip IDs, codes, names and itinerary info in netsuite_trips table', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Can be filtered by modification date, trip year or itinerary code', 'trek-travel-netsuite-integration'); ?></li>
</ul>

<h4><?php _e('Step 2: Get Trip Details', 'trek-travel-netsuite-integration'); ?></h4>
<p><?php _e('This step:', 'trek-travel-netsuite-integration'); ?></p>
<ul>
    <li><?php _e('Uses script ID 1297 to get comprehensive trip details', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Retrieves pricing, dates, capacity, bike info, and other specifications', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Updates netsuite_trip_detail table with complete trip information', 'trek-travel-netsuite-integration'); ?></li>
</ul>

<h4><?php _e('Step 3: Create WC Products', 'trek-travel-netsuite-integration'); ?></h4>
<p><?php _e('This step:', 'trek-travel-netsuite-integration'); ?></p>
<ul>
    <li><?php _e('Creates/updates WooCommerce products from synced trip data', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Sets product attributes like dates, prices, and capacity', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Handles special cases like Ride Camp products with nested dates', 'trek-travel-netsuite-integration'); ?></li>
</ul>

<h3><?php _e('Filters & Options', 'trek-travel-netsuite-integration'); ?></h3>
<h4><?php _e('Time Range Filter', 'trek-travel-netsuite-integration'); ?></h4>
<ul>
    <li><?php _e('Last 12 Hours - Sync items modified in the last 12 hours', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Last 24 Hours - Sync items modified in the last day', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Last Week - Sync items modified in the last 7 days', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Last Month - Sync items modified in the last 30 days', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Last Year - Sync items modified in the last 365 days', 'trek-travel-netsuite-integration'); ?></li>
</ul>

<h4><?php _e('Trip Year Filter', 'trek-travel-netsuite-integration'); ?></h4>
<ul>
    <li><?php _e('Current Year - Sync trips for the current year', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Next Year - Sync trips for next year', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Year After Next - Sync trips two years ahead', 'trek-travel-netsuite-integration'); ?></li>
</ul>

<h4><?php _e('Itinerary Code Filter', 'trek-travel-netsuite-integration'); ?></h4>
<p><?php _e('Allows syncing trips for a specific itinerary code only. The list is populated from NetSuite custom lists.', 'trek-travel-netsuite-integration'); ?></p>

<h3><?php _e('Important Notes', 'trek-travel-netsuite-integration'); ?></h3>
<ul>
    <li><?php _e('Always clear caches after manual trip syncs', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Steps should be performed in order (1-2-3) for complete sync', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Use time range filters to optimize sync performance', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Custom Items/Lists should be synced when new options are added in NetSuite', 'trek-travel-netsuite-integration'); ?></li>
</ul>

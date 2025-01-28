<h2><?php _e('NetSuite Sync Overview', 'trek-travel-netsuite-integration'); ?></h2>
<p><?php _e('This page provides manual synchronization options between WooCommerce and NetSuite. The sync process is divided into several steps that should be executed in order.', 'trek-travel-netsuite-integration'); ?></p>

<h3><?php _e('Main Steps', 'trek-travel-netsuite-integration'); ?></h3>
<ul>
    <li><strong><?php _e('Step 1: Get All Trips', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Fetches basic trip information from NetSuite and stores it in the local database', 'trek-travel-netsuite-integration'); ?></li>
    <li><strong><?php _e('Step 2: Get Trip Details', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Retrieves detailed trip information including pricing, dates, and specifications', 'trek-travel-netsuite-integration'); ?></li>
    <li><strong><?php _e('Step 3: Create WC Trip Products', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Creates or updates WooCommerce products based on NetSuite trip data', 'trek-travel-netsuite-integration'); ?></li>
</ul>

<h3><?php _e('Miscellaneous Operations', 'trek-travel-netsuite-integration'); ?></h3>
<ul>
    <li><strong><?php _e('Create WC Trip Products [All]', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Syncs all trips, not just recently modified ones', 'trek-travel-netsuite-integration'); ?></li>
    <li><strong><?php _e('Custom Items/Lists', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Syncs auxiliary data like bike types, sizes, and other custom lists', 'trek-travel-netsuite-integration'); ?></li>
    <li><strong><?php _e('NS<>WC Booking Sync', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Synchronizes booking information between systems', 'trek-travel-netsuite-integration'); ?></li>
</ul>

<h3><?php _e('Additional Tools', 'trek-travel-netsuite-integration'); ?></h3>
<ul>
    <li><strong><?php _e('Manual Order Sync', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Push individual WooCommerce orders to NetSuite', 'trek-travel-netsuite-integration'); ?></li>
    <li><strong><?php _e('Manual Trip Details Sync', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Sync details for a specific trip by ID', 'trek-travel-netsuite-integration'); ?></li>
    <li><strong><?php _e('Manual Trip Sync', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Sync a specific trip product by SKU', 'trek-travel-netsuite-integration'); ?></li>
    <li><strong><?php _e('Guest Bookings Sync', 'trek-travel-netsuite-integration'); ?></strong> - <?php _e('Sync bookings and preferences for a specific NetSuite guest', 'trek-travel-netsuite-integration'); ?></li>
</ul>

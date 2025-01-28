<h2><?php _e('Automated Sync (CRON Jobs)', 'trek-travel-netsuite-integration'); ?></h2>

<h3><?php _e('Production Environment', 'trek-travel-netsuite-integration'); ?></h3>
<p><?php _e('The following CRON jobs run on the production environment:', 'trek-travel-netsuite-integration'); ?></p>

<h4><?php _e('Trip/Product Sync', 'trek-travel-netsuite-integration'); ?></h4>
<ul>
    <li><?php _e('Frequency: Every 3 hours (1-22h)', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Actions:', 'trek-travel-netsuite-integration'); ?>
        <ol>
            <li><?php _e('Gets basic trip info (Step 1)', 'trek-travel-netsuite-integration'); ?></li>
            <li><?php _e('Gets trip details (Step 2)', 'trek-travel-netsuite-integration'); ?></li> 
            <li><?php _e('Creates/updates WC products (Step 3)', 'trek-travel-netsuite-integration'); ?></li>
            <li><?php _e('Flushes WordPress object cache', 'trek-travel-netsuite-integration'); ?></li>
        </ol>
    </li>
    <li><?php printf( __('Uses %s constant to determine how far back to check for changes', 'trek-travel-netsuite-integration'), '<code>DEFAULT_TIME_RANGE</code>' ); ?></li>
</ul>

<h4><?php _e('Guest/Bookings Sync', 'trek-travel-netsuite-integration'); ?></h4>
<ul>
    <li><?php _e('Frequency: Every 3 hours (0-21h)', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Actions:', 'trek-travel-netsuite-integration'); ?>
        <ul>
            <li><?php _e('Syncs guest preferences', 'trek-travel-netsuite-integration'); ?></li>
            <li><?php _e('Creates missing orders for bookings', 'trek-travel-netsuite-integration'); ?></li>
            <li><?php _e('Updates existing booking details', 'trek-travel-netsuite-integration'); ?></li>
        </ul>
    </li>
    <li><?php printf( __('Uses %s constant to determine how far back to check for changes', 'trek-travel-netsuite-integration'), '<code>DEFAULT_TIME_RANGE</code>' ); ?></li>
</ul>

<h4><?php _e('Locking Status Sync', 'trek-travel-netsuite-integration'); ?></h4>
<ul>
    <li><?php _e('Frequency: Every hour', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Actions:', 'trek-travel-netsuite-integration'); ?>
        <ul>
            <li><?php _e('Updates Trip Checklist lock status', 'trek-travel-netsuite-integration'); ?></li>
            <li><?php _e('Updates Bike Selection lock status', 'trek-travel-netsuite-integration'); ?></li>
        </ul>
    </li>
    <li><?php printf( __('Uses %s constant to determine how far back to check for changes', 'trek-travel-netsuite-integration'), '<code>DEFAULT_TIME_RANGE_LOCKING_STATUS</code>' ); ?></li>
</ul>

<h3><?php _e('Staging Environments', 'trek-travel-netsuite-integration'); ?></h3>
<p><?php _e('Staging environments run a reduced set of syncs:', 'trek-travel-netsuite-integration'); ?></p>
<ul>
    <li><?php _e('Trip/Product sync: Every 2 hours', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Locking status sync: Every hour', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Guest/Booking sync: Disabled', 'trek-travel-netsuite-integration'); ?></li>
</ul>

<h3><?php _e('Important Notes', 'trek-travel-netsuite-integration'); ?></h3>
<ul>
    <li><?php printf( __('Trips/Products sync uses %s constant ( %s ) to determine how far back to check for changes', 'trek-travel-netsuite-integration'), '<code>DEFAULT_TIME_RANGE</code>', DEFAULT_TIME_RANGE ); ?></li>
    <li><?php printf( __('Locking status sync uses %s constant ( %s ) to determine how far back to check for changes', 'trek-travel-netsuite-integration'), '<code>DEFAULT_TIME_RANGE_LOCKING_STATUS</code>', DEFAULT_TIME_RANGE_LOCKING_STATUS ); ?></li>
    <li><?php 
        printf(
            __('Each sync writes detailed logs that can be viewed in the %sLogs section%s', 'trek-travel-netsuite-integration'),
            '<a href="' . admin_url( 'admin.php?page=tt-common-logs' ) . '">',
            '</a>'
        ); 
    ?></li>
    <li><?php _e('Failed syncs are automatically retried on the next scheduled run', 'trek-travel-netsuite-integration'); ?></li>
    <li><?php _e('Manual syncs can be triggered from this page if needed', 'trek-travel-netsuite-integration'); ?></li>
    <li><strong><?php _e('Important:', 'trek-travel-netsuite-integration'); ?></strong> <?php _e('WordPress object cache is automatically flushed after each trip sync to ensure fresh data', 'trek-travel-netsuite-integration'); ?></li>
</ul>

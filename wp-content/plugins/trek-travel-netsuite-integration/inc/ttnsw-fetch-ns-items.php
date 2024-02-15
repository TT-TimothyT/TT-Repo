<?php

use TTNetSuite\NetSuiteClient;

add_action('admin_menu', 'trek_ns_intergration_create_menu');
function trek_ns_intergration_create_menu()
{
    //create new top-level menu
    // add_menu_page(
    //     'NetSuite<>WC',
    //     'NetSuite<>WC',
    //     'administrator',
    //     'trek-travel-ns-wc-settings-page',
    //     'trek_travel_ns_wc_settings_page',
    //     'dashicons-location'
    // );
    $tt_menu_slug = 'trek-travel-ns-wc';
    add_menu_page(
        'NetSuite<>WC',
        'NetSuite<>WC',
        'manage_options',
        $tt_menu_slug,
        'tt_admin_menu_page_cb',
        'dashicons-admin-customizer',
        6
    );
    add_submenu_page(
        $tt_menu_slug,
        'Sync',
        'Sync',
        'manage_options',
        $tt_menu_slug,
        'tt_admin_menu_page_cb'
    );
    add_submenu_page(
        $tt_menu_slug,
        'Logs',
        'Logs',
        'manage_options',
        'tt-common-logs',
        'tt_common_logs_admin_menu_page_cb'
    );
    add_submenu_page(
        $tt_menu_slug,
        'Bookings',
        'Bookings',
        'manage_options',
        'tt-bookings',
        'tt_bookings_admin_menu_page_cb'
    );
}
function tt_admin_menu_page_cb()
{
    require_once TTNSW_DIR . 'tt-templates/ttnsw-admin-header.php';
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'tt_wp_manual_sync_action' && isset($_REQUEST['type'])) {
        if ($_REQUEST['type'] == 'trip' && function_exists('tt_sync_ns_trips')) {
            if( $_REQUEST['time_range'] ) {
                tt_sync_ns_trips($_REQUEST['time_range']);
            } else {
                tt_sync_ns_trips();
            }
        }
        if ($_REQUEST['type'] == 'trip-details' && function_exists('tt_sync_ns_trip_details')) {
            if( $_REQUEST['time_range'] ) {
                tt_sync_ns_trip_details( $_REQUEST['time_range'] );
            } else {
                tt_sync_ns_trip_details();
            }
        }
        if ($_REQUEST['type'] == 'hotels' && function_exists('tt_sync_ns_trip_hotels')) {
            if( $_REQUEST['time_range'] ) {
                tt_sync_ns_trip_hotels( $_REQUEST['time_range'] );
            } else {
                tt_sync_ns_trip_hotels();
            }
        }
        if ($_REQUEST['type'] == 'bikes' && function_exists('tt_sync_ns_trip_bikes')) {
            if( $_REQUEST['time_range'] ) {
                tt_sync_ns_trip_bikes( $_REQUEST['time_range'] );
            } else {
                tt_sync_ns_trip_bikes();
            }
        }
        if ($_REQUEST['type'] == 'addons' && function_exists('tt_sync_ns_trip_addons')) {
            if( $_REQUEST['time_range'] ) {
                tt_sync_ns_trip_addons( $_REQUEST['time_range'] );
            } else {
                tt_sync_ns_trip_addons();
            }
        }
        if ($_REQUEST['type'] == 'product-sync' && function_exists('tt_sync_wc_products_from_ns')) {
            if( $_REQUEST['time_range'] ) {
                tt_sync_wc_products_from_ns( false, [], $_REQUEST['time_range'] );
            } else {
                tt_sync_wc_products_from_ns();
            }
        }
        if ($_REQUEST['type'] == 'product-sync-all' && function_exists('tt_sync_wc_products_from_ns')) {
            tt_add_error_log('[Start]', ['type'=> 'Sync All Trips'], ['dateTime' => date('Y-m-d H:i:s')]);
            as_schedule_single_action(time(), 'ns_trips_sync_to_wc_product', array( true ) );
        }
        if ($_REQUEST['type'] == 'custom-items' && function_exists('tt_sync_custom_items')) {
            tt_sync_custom_items();
            tt_ns_fetch_bike_type_info();
        }
        // Manual Sync of guests information from NS to WC.
        if ( $_REQUEST['type'] == 'ns-wc-booking' && function_exists( 'tt_ns_guest_booking_details' ) ) {
            // Last modified guests time range.
            if( $_REQUEST['time_range'] ) {
                tt_add_error_log('[Start]', array( 'type'=> 'Manual Sync of guests information from NS to WC for last modified guests.', 'time_range' => $_REQUEST['time_range'] ), array( 'dateTime' => date('Y-m-d H:i:s') ) );
                tt_ns_guest_booking_details( false, '', '', $_REQUEST['time_range'], true );
            } else {
                // Time range defined in DEFAULT_TIME_RANGE.
                tt_add_error_log('[Start]', array( 'type'=> 'Manual Sync of guests information from NS to WC for last modified guests.', 'time_range' => DEFAULT_TIME_RANGE ), array( 'dateTime' => date('Y-m-d H:i:s') ) );
                tt_ns_guest_booking_details( false, '', '', DEFAULT_TIME_RANGE, true );
            }
        }
    }
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'tt_wp_manual_order_sync_action' && isset($_REQUEST['order_id'])) {
        do_action('tt_trigger_cron_ns_booking', $_REQUEST['order_id'], null);
    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'tt_wp_manual_guest_bookings_sync_action' && isset( $_REQUEST['ns_user_id'] ) ) {
        tt_add_error_log('[Start]', array( 'type'=> 'Manual Sync of guests information from NS to WC for single guest.', 'ns_user_id' => $_REQUEST['ns_user_id'] ), array( 'dateTime' => date('Y-m-d H:i:s') ) );
        tt_ns_guest_booking_details( true, $_REQUEST['ns_user_id'], '', DEFAULT_TIME_RANGE, true );
    }
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'tt_wp_manual_trip_sync_action' && isset($_REQUEST['trip_code'])) {
        $req_trip_code = isset($_REQUEST['trip_code']) ? $_REQUEST['trip_code'] : '';
        $product_id = tt_get_product_by_sku($req_trip_code, true);
        if( $product_id ){
            $ns_tripId = get_post_meta($product_id, TT_WC_META_PREFIX.'tripId', true);
            if($ns_tripId){
                //tt_sync_wc_products_from_ns(false, [$ns_tripId]);
                tt_add_error_log('[Start]', ['type'=> 'Single Trip Sync'], ['dateTime' => date('Y-m-d H:i:s')]);
                as_schedule_single_action(time(), 'ns_trips_sync_to_wc_product', array( false, [$ns_tripId] ));
            }else{
                tt_add_error_log('[Sync] - NS Trip to WC', ['trip_code' => $req_trip_code, 'message' => 'No NS Trip ID found' ], ['dateTime' => date('Y-m-d H:i:s')]);
            }
        }else{
            tt_add_error_log('[Sync] - NS Trip to WC', ['trip_code' => $req_trip_code, 'message' => 'No Product found' ], ['dateTime' => date('Y-m-d H:i:s')]);
        }
    }

    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'tt_wp_manual_checklist_sync_action' ) {
        tt_add_error_log('[Start]', ['type'=> 'User Checklist Sync'], ['dateTime' => date('Y-m-d H:i:s')]);

        if( function_exists( 'tt_ns_fetch_registration_ids' ) ) {
            tt_ns_fetch_registration_ids();
        }

        tt_add_error_log('[End]', ['type'=> 'User Checklist Sync'], ['dateTime' => date('Y-m-d H:i:s')]);

    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dx-rapair-bookings-table' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'netsuite_trip_detail';
        //$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'guestRegistrationId'"  );


        $new_column1_name = 'SmugMugLink';
        $new_column2_name = 'SmugMugPassword';

        $sql = "ALTER TABLE $table_name ADD COLUMN $new_column1_name VARCHAR(255) AFTER tripSpecificMessage,
        ADD COLUMN $new_column2_name VARCHAR(255) AFTER $new_column1_name";

        // Execute the query
        $wpdb->query($sql);

    }
?>
    <div class="tt-admin-page-div tt-pl-40 tt-mt-30">
        <div class="tt-wc-ns-admin-wrap">
            <div id="tt-ns-sync-class">
                <h3>Manual Sync for WC<>NS</h3>
                <form class="tt-wp-manual-sync" action="" method="post">
                    <select name="type" required>
                        <option value="">Select Sync TYPE</option>
                        <option value="trip">Trip By Year from NS</option>
                        <option value="trip-details">Trip Details by TripID - NS</option>
                        <option value="bikes">Bike</option>
                        <option value="hotels">Hotels</option>
                        <option value="addons">Addons</option>
                        <option value="product-sync">Product SyncTo WC - [By Year]</option>
                        <option value="product-sync-all">Product SyncTo WC - [All]</option>
                        <option value="custom-items">Custom Items</option>
                        <option value="ns-wc-booking">NS<>WC Booking Sync</option>
                    </select>
                    <select name="time_range" required>
                        <option value="">Time Range</option>
                        <option value="-12 hours">Last 12 Hours</option>
                        <option value="-24 hours">Last 24 Hours</option>
                        <option value="-1 week">Last Week</option>
                        <option value="-1 month">Last Month</option>
                        <option value="-1 year">Last Year</option>
                    </select>
                    <input type="hidden" name="action" value="tt_wp_manual_sync_action">
                    <input type="submit" name="submit" value="Sync" class="button-primary">
                </form>
            </div>
            <div id="tt-order-sync-admin">
                <h3>Manual WC Order Sync to NS</h3>
                <form action="" class="tt-order-sync" method="post">
                    <input type="number" name="order_id" placeholder="Enter WC Order ID" required>
                    <input type="hidden" name="action" value="tt_wp_manual_order_sync_action">
                    <input type="submit" name="submit" value="Sync Order" class="button-primary">
                </form>
            </div>
            <div id="tt-order-sync-admin">
                <h3>Manual Trip Sync from NS to WC</h3>
                <form action="" class="tt-order-sync" method="post">
                    <input type="text" name="trip_code" placeholder="Enter TRIP Code/SKU" required>
                    <input type="hidden" name="action" value="tt_wp_manual_trip_sync_action">
                    <input type="submit" name="submit" value="Sync Trip" class="button-primary">
                </form>
            </div>
            <div id="tt-bookings-sync-admin">
                <h3>Manual Single Guest Bookings/Preferences Sync from NS to WC</h3>
                <form action="" class="tt-bookings-sync" method="post">
                    <input type="text" name="ns_user_id" placeholder="Enter NetSuite User ID" required>
                    <input type="hidden" name="action" value="tt_wp_manual_guest_bookings_sync_action">
                    <input type="submit" name="submit" value="Sync Guest Bookings" class="button-primary">
                </form>
            </div>
            <div id="tt-checklist-sync">
                <h3>Sync all user meta and checklist locking</h3>
                <form action="" class="tt-checklist-sync" method="post">
                    Click to sync the user checklist locking for the past 2 hours
                    <input type="hidden" name="action" value="tt_wp_manual_checklist_sync_action">
                    <input type="submit" name="submit" value="Sync" class="button-primary">
                </form>
            </div>
            <!-- Temp Code -->
            <div id="tt-order-sync-print_r">
                <h3>Print user & order_meta</h3>
                <form action="" class="tt-order-sync" method="post">
                    <input type="number" name="order_id" placeholder="Enter WC Order ID">
                    <input type="number" name="user_id" placeholder="Enter User ID">
                    <input type="hidden" name="action" value="tt_print_r">
                    <input type="submit" name="submit" value="Print" class="button-primary">
                </form>
                <div id="tt-print_result" style="margin: 5% 0px;">
                        <?php
                        $pr_data = [];
                        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'tt_print_r') {
                            if( isset($_REQUEST['order_id']) && $_REQUEST['order_id'] ){
                                $pr_data = get_post_meta($_REQUEST['order_id']);
                            }
                            if( isset($_REQUEST['user_id']) && $_REQUEST['user_id'] ){
                                $pr_data = get_user_meta($_REQUEST['user_id']);
                            }
                            pr($pr_data);
                        }
                        ?>
                </div>
            </div>
            <!-- End Temp code -->
            <!-- Temp Code -->
            <hr>
            <div id="dx-repair-tools">
                <h3>DX Repair Tools</h3>
                <p>This process below will add the missing column <code>SmugMugLink</code> and <code>SmugMugPassword</code> in the <code>netsuite_trip_details</code> table.</p>
                <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'netsuite_trip_detail';
                    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'SmugMugLink'"  );
                ?>
                <form action="" class="tt-order-sync" method="post">
                    <input type="hidden" name="action" value="dx-rapair-bookings-table">
                    <input type="submit" name="submit" value="Repair Bookings Table" class="button-primary" <?php echo esc_attr( !empty($row) ? 'disabled="true"' : '' ); ?>>
                </form>
                <div id="dx-print_result" style="margin: 2% 0px;">
                    <p><b>Bookings table status: </b>
                        <?php if( empty($row) ) : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#f00; color: white;">Not repaired yet</span>
                        <?php else : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#0f0; color: blue;">Successfully repaired</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <!-- End Temp code -->
        </div>
    </div>
<?php
}
function tt_common_logs_admin_menu_page_cb()
{
    require_once TTNSW_DIR . 'tt-templates/ttnsw-admin-header.php';
    $logs_results = tt_get_common_logs();
    $limit = 10;
    if (isset($_REQUEST['limit']) && $_REQUEST['limit']) {
        $limit = $_REQUEST['limit'];
        $logs_results = tt_get_common_logs($limit);
    }
?>
    <div class="tt-admin-page-div tt-pl-40 tt-mt-30">
        <form name="tt-logs" method="post">
            <div class="tt-button-wrap">
                <!-- <input type="submit" value="Delete logs" class="tt-mt-10 button button-danger" submit-type="delete"> -->
                <input type="hidden" name="action" value="tt-common-logs">
                <div class="input-text-wrap tt-text-right" id="title-wrap">
                    <label for="title">Limit </label>
                    <input type="number" name="limit" value="<?php echo $limit; ?>">
                    <input type="submit" name="update" class="button button-primary" value="Update">
                </div>
            </div>
        </form>
        <div class="tt-wc-ns-admin-wrap">
            <table id="tt-dataTable-logs" class="table-old">
                <thead>
                    <tr>
                        <th>Sr</th>
                        <th>Type</th>
                        <th>Arguments</th>
                        <th>Response</th>
                        <th>Created at</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $logs_results; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Sr</th>
                        <th>Type</th>
                        <th>Arguments</th>
                        <th>Response</th>
                        <th>Created at</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php
}
function tt_bookings_admin_menu_page_cb()
{
    require_once TTNSW_DIR . 'tt-templates/ttnsw-admin-header.php';
    $booking_results = tt_get_bookings();
    $limit = 10;
    if (isset($_REQUEST['limit']) && $_REQUEST['limit']) {
        $limit = $_REQUEST['limit'];
        $booking_results = tt_get_bookings($limit);
    }
?>
    <div class="tt-admin-page-div tt-pl-40 tt-mt-30">
        <form name="tt-logs" method="post">
            <div class="tt-button-wrap">
                <!-- <input type="submit" value="Delete logs" class="tt-mt-10 button button-danger" submit-type="delete"> -->
                <input type="hidden" name="action" value="tt-common-logs">
                <div class="input-text-wrap tt-text-right" id="title-wrap">
                    <label for="title">Limit </label>
                    <input type="number" name="limit" value="<?php echo $limit; ?>">
                    <input type="submit" name="update" class="button button-primary" value="Update">
                </div>
            </div>
        </form>
        <div class="tt-wc-ns-admin-wrap">
            <table id="tt-dataTable-logs" class="table-old tt-booking-table">
                <thead>
                    <tr>
                        <th>SR</th>
                        <th>Booking id</th>
                        <th>Order id</th>
                        <th>User id</th>
                        <th>NS user id</th>
                        <th>Promo code</th>
                        <th>Trip code</th>
                        <th>Trip name</th>
                        <th>No guests</th>
                        <th>Rooms</th>
                        <th>Is primary</th>
                        <th>Guest name</th>
                        <th class="expandable-cell">WC Meta <span class="expand-cell expand-all" title="Expand All"></span></th>
                        <th>NS Booking status</th>
                        <th>Created at</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $booking_results; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>SR</th>
                        <th>Booking id</th>
                        <th>Order id</th>
                        <th>User id</th>
                        <th>NS user id</th>
                        <th>Promo code</th>
                        <th>Trip code</th>
                        <th>Trip name</th>
                        <th>No guests</th>
                        <th>Rooms</th>
                        <th>Is primary</th>
                        <th>Guest name</th>
                        <th>WC Meta</th>
                        <th>NS Booking status</th>
                        <th>Created at</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php
}

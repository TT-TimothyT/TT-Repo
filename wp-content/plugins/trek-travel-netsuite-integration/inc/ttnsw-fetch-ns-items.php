<?php

use TTNetSuite\NetSuiteClient;

add_action('admin_menu', 'trek_ns_intergration_create_menu');
function trek_ns_intergration_create_menu()
{
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
    if ( isset( $_REQUEST['action']) && $_REQUEST['action'] == 'tt_wp_manual_sync_action' && isset( $_REQUEST['type'] ) ) {
        $filter_type  = '';
        $filter_value = '';
        if ( isset( $_REQUEST['filter_type'] ) ) {
            switch ( $_REQUEST['filter_type'] ) {
                case 'modifiedAfter':
                    if ( isset( $_REQUEST['time_range'] ) && ! empty( $_REQUEST['time_range'] ) ) {
                        $filter_type  = 'modifiedAfter';
                        $filter_value = $_REQUEST['time_range'];
                    }
                    break;
                case 'tripYear':
                    if ( isset( $_REQUEST['trip_year'] ) && ! empty( $_REQUEST['trip_year'] ) ) {
                        $filter_type  = 'tripYear';
                        $filter_value = $_REQUEST['trip_year'];
                    }
                    break;
                case 'itineraryCode':
                    if ( isset( $_REQUEST['itinerary_code'] ) && ! empty( $_REQUEST['itinerary_code'] ) ) {
                        $filter_type  = 'itineraryCode';
                        $filter_value = $_REQUEST['itinerary_code'];
                    }
                    break;
                case 'itineraryId':
                    if ( isset( $_REQUEST['itinerary_id'] ) && ! empty( $_REQUEST['itinerary_id'] ) ) {
                        $filter_type  = 'itineraryId';
                        $filter_value = $_REQUEST['itinerary_id'];
                    }
                    break;
                default:
                    // Keep the default values empty, to can work the cehck below $with_filter!
                    $filter_type  = '';
                    $filter_value = '';
                    break;
            }
        }

        $with_filter = ! empty( $filter_type ) && ! empty( $filter_value );
        $start_time  = date('Y-m-d H:i:s'); // For admin notices.

        switch ( $_REQUEST['type'] ) {
            case 'trip':
                if ( function_exists('tt_sync_ns_trips') ) {
                    if ( $with_filter ) {
                        tt_sync_ns_trips( $filter_value, $filter_type );
                    } else {
                        // Will execute with the default time range.
                        tt_sync_ns_trips();
                    }
                    add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_ns_trips' ), 'The sync of basic trip info from NetSuite to the website, started at: ' . $start_time . ' and finished at: ' . date('Y-m-d H:i:s'), 'info' );
                }
                break;
            case 'trip-details':
                if ( function_exists('tt_sync_ns_trip_details') ) {
                    if ( $with_filter ) {
                        tt_sync_ns_trip_details( $filter_value, $filter_type );
                    } else {
                        // Will execute with the default time range.
                        tt_sync_ns_trip_details();
                    }
                    add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_ns_trip_details' ), 'The sync of the trip details from NetSuite to the website, started at: ' . $start_time . ' and finished at: ' . date('Y-m-d H:i:s'), 'info' );
                }
                break;
            case 'hotels':
                if ( function_exists('tt_sync_ns_trip_hotels') ) {
                    if ( $with_filter ) {
                        tt_sync_ns_trip_hotels( $filter_value, $filter_type );
                    } else {
                        // Will execute with the default time range.
                        tt_sync_ns_trip_hotels();
                    }
                    add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_ns_trip_hotels' ), 'The sync of hotels started at: ' . $start_time . ' and finished at: ' . date('Y-m-d H:i:s'), 'info' );
                }
                break;
            case 'bikes':
                if ( function_exists('tt_sync_ns_trip_bikes') ) {
                    if ( $with_filter ) {
                        tt_sync_ns_trip_bikes( $filter_value, $filter_type );
                    } else {
                        // Will execute with the default time range.
                        tt_sync_ns_trip_bikes();
                    }
                    add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_ns_trip_bikes' ), 'The sync of bikes started at: ' . $start_time . ' and finished at: ' . date('Y-m-d H:i:s'), 'info' );
                }
                break;
            case 'addons':
                if ( function_exists('tt_sync_ns_trip_addons') ) {
                    if ( $with_filter ) {
                        tt_sync_ns_trip_addons( $filter_value, $filter_type );
                    } else {
                        // Will execute with the default time range.
                        tt_sync_ns_trip_addons();
                    }
                    add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_ns_trip_addons' ), 'The sync of addons started at: ' . $start_time . ' and finished at: ' . date('Y-m-d H:i:s'), 'info' );
                }
                break;
            case 'product-sync':
                if ( function_exists('tt_sync_wc_products_from_ns') ) {
                    if ( $with_filter ) {
                        tt_sync_wc_products_from_ns( false, [], $filter_value, $filter_type );
                    } else {
                        // Will execute with the default time range.
                        tt_sync_wc_products_from_ns();
                    }
                    add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_wc_products_from_ns' ), 'The sync of products started at: ' . $start_time . ' and finished at: ' . date('Y-m-d H:i:s'), 'info' );
                }
                break;
            case 'product-sync-all':
                if ( function_exists('tt_sync_wc_products_from_ns') ) {
                    tt_add_error_log('[Start]', ['type'=> 'Sync All Trips (Products)'], ['dateTime' => date('Y-m-d H:i:s')] );
                    as_schedule_single_action( time(), 'ns_trips_sync_to_wc_product', array( true ) );
                    add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_wc_products_from_ns' ), 'The sync of all products is scheduled at: ' . date('Y-m-d H:i:s') . ' and will start soon', 'info' );
                }
                break;
            case 'custom-items':
                if ( function_exists('tt_sync_custom_items') ) {
                    tt_sync_custom_items();
                    tt_ns_fetch_bike_type_info();
                    add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_custom_items' ), 'The sync of custom items/lists started at: ' . $start_time . ' and finished at: ' . date('Y-m-d H:i:s'), 'info' );
                }
                break;
            case 'ns-wc-booking':
                // Manual Sync of guests information from NS to WC.
                if ( function_exists('tt_ns_guest_booking_details') ) {
                    // Last modified guests time range.
                    if( $_REQUEST['time_range'] ) {
                        tt_add_error_log('[Start]', array( 'type'=> 'Manual Sync of guests information from NS to WC for last modified guests.', 'time_range' => $_REQUEST['time_range'] ), array( 'dateTime' => date('Y-m-d H:i:s') ) );
                        tt_ns_guest_booking_details( false, '', '', $_REQUEST['time_range'], true );
                    } else {
                        // Time range defined in DEFAULT_TIME_RANGE.
                        tt_add_error_log('[Start]', array( 'type'=> 'Manual Sync of guests information from NS to WC for last modified guests.', 'time_range' => DEFAULT_TIME_RANGE ), array( 'dateTime' => date('Y-m-d H:i:s') ) );
                        tt_ns_guest_booking_details( false, '', '', DEFAULT_TIME_RANGE, true );
                    }
                    add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_ns_guest_booking_details' ), 'The sync of guest/bookings started at: ' . $start_time . ' and finished at: ' . date('Y-m-d H:i:s'), 'info' );
                }
                break;
            
            default:
                add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_wp_manual_sync_action' ), 'The Manual Sync Action is not recognized!', 'error' );
            
                break;
        }
    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'tt_wp_manual_order_sync_action' && isset( $_REQUEST['order_id'] ) ) {
        do_action('tt_trigger_cron_ns_booking', $_REQUEST['order_id'], null );
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
                tt_add_error_log('[Start]', ['type'=> 'Single Trip (Product) Sync'], ['dateTime' => date('Y-m-d H:i:s')]);
                as_schedule_single_action(time(), 'ns_trips_sync_to_wc_product', array( false, [$ns_tripId] ));
            }else{
                tt_add_error_log('[Sync] - NS Trip to WC (Product)', ['trip_code' => $req_trip_code, 'message' => 'No NS Trip ID found' ], ['dateTime' => date('Y-m-d H:i:s')]);
            }
        }else{
            tt_add_error_log('[Sync] - NS Trip to WC (Product)', ['trip_code' => $req_trip_code, 'message' => 'No Product found' ], ['dateTime' => date('Y-m-d H:i:s')]);
        }
    }
    if ( isset( $_REQUEST['action'] ) && 'tt_wp_manual_trip_details_sync_action' === $_REQUEST['action'] && isset( $_REQUEST['trip_id'] ) ) {
        if( empty( $_REQUEST['trip_id'] ) ) {
            add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_ns_single_trip_details' ), 'Trip ID can\'t be empty!', 'error' );
        } else {
            // Proceed with the single trip details sync.
            tt_sync_ns_single_trip_details( (int) $_REQUEST['trip_id'] );
        }
    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'tt_wp_manual_checklist_sync_action' ) {
        tt_add_error_log('[Start]', ['type'=> 'User Checklist Sync'], ['dateTime' => date('Y-m-d H:i:s')]);

        if( function_exists( 'tt_ns_fetch_registration_ids' ) ) {
            tt_ns_fetch_registration_ids();
        }

        tt_add_error_log('[End]', ['type'=> 'User Checklist Sync'], ['dateTime' => date('Y-m-d H:i:s')]);

    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dx-add-is-cancelled' ) {
        global $wpdb;
        $table_name      = $wpdb->prefix . 'guest_bookings';
        $new_column_name = 'is_guestreg_cancelled';

        $column_exist    = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = '{$new_column_name}'"  );

        if( empty( $column_exist ) ) {
            $sql = "ALTER TABLE $table_name ADD COLUMN $new_column_name INT(11) DEFAULT 0 AFTER guestRegistrationId";

            // Execute the query
            $wpdb->query($sql);
        }
    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dx-sync-is-cancelled' ) {
        global $wpdb;
        $table_name      = $wpdb->prefix . 'guest_bookings';
        $new_column_name = 'is_guestreg_cancelled';

        $column_exist    = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = '{$new_column_name}'"  );

        // Do Sync if the column is_guestreg_cancelled exist in the guest_bookings table.
        if( ! empty( $column_exist ) ) {

            $sql = "SELECT DISTINCT netsuite_guest_registration_id from {$table_name} WHERE guestRegistrationId IS NOT NULL AND guestRegistrationId <> '' AND netsuite_guest_registration_id IS NOT NULL AND netsuite_guest_registration_id <> ''";
            // Get All unique Guest registration IDs, from the guest_bookings table.
            $results = $wpdb->get_results( $sql, ARRAY_A );

            tt_add_error_log('[Start] Sync isCancelled status', array( 'Found NS Users in the guest_bookings table'=> $results ), array( 'dateTime' => date('Y-m-d H:i:s') ) );
            if( ! empty( $results ) ) {
                $ns_guest_reg_ids_arr = array_chunk( $results, 15 );

                foreach( $ns_guest_reg_ids_arr as $ns_guest_reg_ids ) {
                    foreach( $ns_guest_reg_ids as $ns_guest_reg_id ) {
                        if( isset( $ns_guest_reg_id['netsuite_guest_registration_id'] ) ) {
                            tt_ns_guest_booking_details( true, $ns_guest_reg_id['netsuite_guest_registration_id'] );
                        }
                    }
                }
            }
            tt_add_error_log('[End] Sync isCancelled status', array( 'Found NS Users in the guest_bookings table'=> $results ), array( 'dateTime' => date('Y-m-d H:i:s') ) );
        }
    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dx-add-product-line' ) {
        global $wpdb;
        $table_name      = $wpdb->prefix . 'netsuite_trip_detail';
        $new_column_name = 'product_line';

        $column_exist    = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = '{$new_column_name}'"  );

        if ( empty( $column_exist ) ) {
            $sql = "ALTER TABLE $table_name ADD COLUMN $new_column_name text DEFAULT NULL AFTER riderType";

            // Execute the query
            $wpdb->query($sql);
        }
    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dx-add-substyle' ) {
        global $wpdb;
        $table_name      = $wpdb->prefix . 'netsuite_trip_detail';
        $new_column_name = 'subStyle';

        $column_exist    = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = '{$new_column_name}'"  );

        if ( empty( $column_exist ) ) {
            $sql = "ALTER TABLE $table_name ADD COLUMN $new_column_name text DEFAULT NULL AFTER riderType";

            // Execute the query
            $wpdb->query($sql);
        }
    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dx-add-activity-level' ) {
        global $wpdb;
        $table_name      = $wpdb->prefix . 'guest_bookings';
        $new_column_name = 'activity_level';

        $column_exist    = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = '{$new_column_name}'"  );

        if ( empty( $column_exist ) ) {
            $sql = "ALTER TABLE $table_name ADD COLUMN $new_column_name varchar(100) DEFAULT NULL AFTER rider_level";

            // Execute the query
            $wpdb->query($sql);
        }
    }
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dx-add-bike-comments' ) {
        global $wpdb;
        $table_name      = $wpdb->prefix . 'guest_bookings';
        $new_column_name = 'bike_comments';

        $column_exist    = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = '{$new_column_name}'"  );

        if ( empty( $column_exist ) ) {
            $sql = "ALTER TABLE $table_name ADD COLUMN $new_column_name varchar(100) DEFAULT NULL AFTER specialRoomRequests";

            // Execute the query
            $wpdb->query($sql);
        }
    }
    // Keep this here, to can display some admin notifications!
    require_once TTNSW_DIR . 'tt-templates/ttnsw-admin-header.php';

    // Prepare Itinerary Filters.
    $is_itinerary_filter_available = false;
    if ( function_exists('tt_get_custom_item_name') ) {
        $itinerary_codes = tt_get_custom_item_name('syncTripsList');
        if( isset( $itinerary_codes['options'] ) && is_array( $itinerary_codes['options'] ) && ! empty( $itinerary_codes['options'] ) ) {
            $is_itinerary_filter_available = true;
        }
    }
?>
    <div class="tt-admin-page-div tt-pl-40 tt-mt-30 tt-sync-page">
        <div class="tt-wc-ns-admin-wrap">
            <div id="tt-ns-sync-class">
                <span style="padding: 2px 5px;border-radius:4px; background-color:#f00; color: white;">Clear Caches After Manual Trip Sync!</span>
                <h3>Manual Sync for WC<>NS</h3>
                <form class="tt-wp-manual-sync" action="" method="post">
                    <select name="type" required>
                        <option value="">Select Sync Type</option>
                        <option value="trip">Step 1: Get All Trips</option>
                        <option value="trip-details">Step 2: Get Trip Details</option>
                        <option value="bikes">Step 3: Get Bikes</option>
                        <option value="hotels">Step 4: Get Hotels</option>
                        <option value="addons">Step 5: Get Addons</option>
                        <option value="product-sync">Step 6: Create WC Trip Products</option>
                        <option value="product-sync-all">Misc: Create WC Trip Products - [All]</option>
                        <option value="custom-items">Misc: Custom Items/Lists</option>
                        <option value="ns-wc-booking">Misc: NS<>WC Booking Sync</option>
                    </select>
                    <select name="filter_type" style="display: none;" required>
                        <option value="">Filter Type</option>
                        <option value="modifiedAfter">By Last Modified Date</option>
                        <option value="tripYear">By Trip Year</option>
                        <?php if ( $is_itinerary_filter_available ) : ?>
                            <option value="itineraryCode">By Itinerary Code</option>
                            <!-- This option below is very similar to By Itinerary Code. I'm hiding it visually so we have it as a reference and option if we need to use it in the future -->
                            <!-- <option value="itineraryId">By Itinerary ID</option> -->
                        <?php endif; ?>
                    </select>
                    <select name="time_range" style="display: none;" required>
                        <option value="">Time Range</option>
                        <option value="-12 hours">Last 12 Hours</option>
                        <option value="-24 hours">Last 24 Hours</option>
                        <option value="-1 week">Last Week</option>
                        <option value="-1 month">Last Month</option>
                        <option value="-1 year">Last Year</option>
                    </select>
                    <select name="trip_year" style="display: none;" required>
                        <option value="">Trip Year</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                    <?php if ( $is_itinerary_filter_available ) : ?>
                        <select name="itinerary_code" style="display: none;" required>
                            <option value="">Itinerary Code</option>
                            <?php
                                foreach( $itinerary_codes['options'] as $itinerary_code ) {
                                    if( is_array( $itinerary_code ) && ! empty( $itinerary_code ) ) {
                                        ?>
                                            <option value="<?php echo esc_attr( $itinerary_code['optionValue'] ) ?>"><?php echo esc_attr( $itinerary_code['optionValue'] ) ?></option>
                                        <?php
                                    }
                                }
                            ?>
                        </select>
                        <select name="itinerary_id" style="display: none;" required>
                            <option value="">Itinerary ID</option>
                            <?php
                                foreach( $itinerary_codes['options'] as $itinerary_code ) {
                                    if( is_array( $itinerary_code ) && ! empty( $itinerary_code ) ) {
                                        ?>
                                            <option value="<?php echo esc_attr( $itinerary_code['optionId'] ) ?>"><?php echo esc_attr( $itinerary_code['optionId'] ) ?> ( <?php echo esc_attr( $itinerary_code['optionValue'] ); ?> ) </option>
                                        <?php
                                    }
                                }
                            ?>
                        </select>
                    <?php endif; ?>
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
            <div id="tt-trip-details-sync-admin">
                <h3>Manual Trip Details Sync from NS to WC</h3>
                <p>This action will <strong>sync the trip details</strong> for a single trip</p>
                <form action="" class="tt-trip-details-sync" method="post">
                    <input type="text" name="trip_id" placeholder="Enter Trip ID (xxxxx)" required>
                    <input type="hidden" name="action" value="tt_wp_manual_trip_details_sync_action">
                    <input type="submit" name="submit" value="Sync Trip Details" class="button-primary">
                </form>
            </div>
            <div id="tt-order-sync-admin">
                <h3>Manual Trip Sync from NS to WC</h3>
                <p>This action will <strong>sync only product</strong>, not the trip details table in the DB</p>
                <form action="" class="tt-order-sync" method="post">
                    <input type="text" name="trip_code" placeholder="Enter TRIP Code/SKU" required>
                    <input type="hidden" name="action" value="tt_wp_manual_trip_sync_action">
                    <input type="submit" name="submit" value="Sync Trip (Product)" class="button-primary">
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
                    Click to sync the user checklist locking for the past <?php echo DEFAULT_TIME_RANGE_LOCKING_STATUS; ?>
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
            <div id="dx-repair-tools">
                <hr>
                <h3>DX Repair Tools</h3>
                <p>This process below will add the column <code>is_guestreg_cancelled</code> in the <code>guest_bookings</code> table.</p>
                <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'guest_bookings';
                    $row        = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = 'is_guestreg_cancelled'"  );
                ?>
                <form action="" class="tt-locking-status-sync" method="post">
                    <input type="hidden" name="action" value="dx-add-is-cancelled">
                    <input type="submit" name="submit" value="Add is_guestreg_cancelled column" class="button-primary" <?php echo esc_attr( !empty($row) ? 'disabled="true"' : '' ); ?>>
                </form>
                <div id="dx-print_result" style="margin: 2% 0px;">
                    <p><b>Bookings table status: </b>
                        <?php if( empty($row) ) : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#f00; color: white;">The <code>is_guestreg_cancelled</code> column missing yet</span>
                        <?php else : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#0f0; color: blue;">Successfully added <code>is_guestreg_cancelled</code> column</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div style="display:none;">
                    <hr>
                    <p>This process below will take all the Guest Registration IDs from the <code>guest_bookings</code> table and loop through them to set the <code>isCancelled</code> status from NS.</p>
                    <form action="" class="tt-locking-status-sync" method="post">
                        <input type="hidden" name="action" value="dx-sync-is-cancelled">
                        <input type="submit" name="submit" value="Sync isCancelled status" class="button-primary" <?php echo esc_attr( empty($row) ? 'disabled="true"' : '' ); ?>>
                    </form>
                    <div id="dx-print_result" style="margin: 2% 0px;">
                        <p><b>Ready to sync - status: </b>
                            <?php if( empty($row) ) : ?>
                            <span style="padding: 2px 5px;border-radius:4px; background-color:#f00; color: white;">Not ready for sync! The <code>is_guestreg_cancelled</code> column missing yet</span>
                            <?php else : ?>
                            <span style="padding: 2px 5px;border-radius:4px; background-color:#00f; color: white;">Ready to sync <code>isCancelled</code> status</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <hr>
                <!-- Add the product_line column in the netsuite_trip_detail table -->
                <?php
                    global $wpdb;
                    $trip_detail_table_name  = $wpdb->prefix . 'netsuite_trip_detail';
                    $trip_detail_column_name = 'product_line';
                    $trip_detail_row         = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$trip_detail_table_name}' AND column_name = '{$trip_detail_column_name}'"  );
                    $trip_detail_add_action  = 'dx-add-product-line';
                ?>
                <p>This process below will add the column <code><?php echo esc_html( $trip_detail_column_name ); ?></code> in the <code><?php echo esc_html( $trip_detail_table_name ); ?></code> table.</p>
                <form action="" class="tt-locking-status-sync" method="post">
                    <input type="hidden" name="action" value="<?php echo esc_attr( $trip_detail_add_action ) ?>">
                    <input type="submit" name="submit" value="Add <?php echo esc_attr( $trip_detail_column_name ); ?> column" class="button-primary" <?php echo esc_attr( ! empty( $trip_detail_row ) ? 'disabled="true"' : '' ); ?>>
                </form>
                <div id="dx-print_result" style="margin: 2% 0px;">
                    <p><b>Trip Details table status: </b>
                        <?php if( empty( $trip_detail_row ) ) : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#f00; color: white;">The <code><?php echo esc_html( $trip_detail_column_name ); ?></code> column missing yet</span>
                        <?php else : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#0f0; color: blue;">Successfully added <code><?php echo esc_html( $trip_detail_column_name ); ?></code> column</span>
                        <?php endif; ?>
                    </p>
                </div>
                <hr>
                <!-- Add the activity_level column in the guest_bookings table -->
                <?php
                    global $wpdb;
                    $bookings_table_table_name  = $wpdb->prefix . 'guest_bookings';
                    $bookings_table_column_name = 'activity_level';
                    $bookings_table_row         = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$bookings_table_table_name}' AND column_name = '{$bookings_table_column_name}'"  );
                    $bookings_table_add_action  = 'dx-add-activity-level';
                ?>
                <p>This process below will add the column <code><?php echo esc_html( $bookings_table_column_name ); ?></code> in the <code><?php echo esc_html( $bookings_table_table_name ); ?></code> table.</p>
                <form action="" class="tt-locking-status-sync" method="post">
                    <input type="hidden" name="action" value="<?php echo esc_attr( $bookings_table_add_action ) ?>">
                    <input type="submit" name="submit" value="Add <?php echo esc_attr( $bookings_table_column_name ); ?> column" class="button-primary" <?php echo esc_attr( ! empty( $bookings_table_row ) ? 'disabled="true"' : '' ); ?>>
                </form>
                <div id="dx-print_result" style="margin: 2% 0px;">
                    <p><b>Guest Bookings table status: </b>
                        <?php if( empty( $bookings_table_row ) ) : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#f00; color: white;">The <code><?php echo esc_html( $bookings_table_column_name ); ?></code> column missing yet</span>
                        <?php else : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#0f0; color: blue;">Successfully added <code><?php echo esc_html( $bookings_table_column_name ); ?></code> column</span>
                        <?php endif; ?>
                    </p>
                </div>
                <hr>
                <!-- Add the bike_comments column in the guest_bookings table -->
                <?php
                    global $wpdb;
                    $bike_comments_table_name  = $wpdb->prefix . 'guest_bookings';
                    $bike_comments_column_name = 'bike_comments';
                    $bike_comments_row         = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$bike_comments_table_name}' AND column_name = '{$bike_comments_column_name}'"  );
                    $bike_comments_add_action  = 'dx-add-bike-comments';
                ?>
                <p>This process below will add the column <code><?php echo esc_html( $bike_comments_column_name ); ?></code> in the <code><?php echo esc_html( $bike_comments_table_name ); ?></code> table.</p>
                <form action="" class="tt-locking-status-sync" method="post">
                    <input type="hidden" name="action" value="<?php echo esc_attr( $bike_comments_add_action ) ?>">
                    <input type="submit" name="submit" value="Add <?php echo esc_attr( $bike_comments_column_name ); ?> column" class="button-primary" <?php echo esc_attr( ! empty( $bike_comments_row ) ? 'disabled="true"' : '' ); ?>>
                </form>
                <div id="dx-print_result" style="margin: 2% 0px;">
                    <p><b>Trip Details table status: </b>
                        <?php if( empty( $bike_comments_row ) ) : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#f00; color: white;">The <code><?php echo esc_html( $bike_comments_column_name ); ?></code> column missing yet</span>
                        <?php else : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#0f0; color: blue;">Successfully added <code><?php echo esc_html( $bike_comments_column_name ); ?></code> column</span>
                        <?php endif; ?>
                    </p>
                </div>
                <hr>
                <!-- Add the substyle column in the netsuite_trip_detail table -->
                <?php
                    global $wpdb;
                    $trip_detail_table_name  = $wpdb->prefix . 'netsuite_trip_detail';
                    $trip_detail_column_name = 'subStyle';
                    $trip_detail_row         = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$trip_detail_table_name}' AND column_name = '{$trip_detail_column_name}'"  );
                    $trip_detail_add_action  = 'dx-add-substyle';
                ?>
                <p>This process below will add the column <code><?php echo esc_html( $trip_detail_column_name ); ?></code> in the <code><?php echo esc_html( $trip_detail_table_name ); ?></code> table.</p>
                <form action="" class="tt-locking-status-sync" method="post">
                    <input type="hidden" name="action" value="<?php echo esc_attr( $trip_detail_add_action ) ?>">
                    <input type="submit" name="submit" value="Add <?php echo esc_attr( $trip_detail_column_name ); ?> column" class="button-primary" <?php echo esc_attr( ! empty( $trip_detail_row ) ? 'disabled="true"' : '' ); ?>>
                </form>
                <div id="dx-print_result" style="margin: 2% 0px;">
                    <p><b>Trip Details table status: </b>
                        <?php if( empty( $trip_detail_row ) ) : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#f00; color: white;">The <code><?php echo esc_html( $trip_detail_column_name ); ?></code> column missing yet</span>
                        <?php else : ?>
                        <span style="padding: 2px 5px;border-radius:4px; background-color:#0f0; color: blue;">Successfully added <code><?php echo esc_html( $trip_detail_column_name ); ?></code> column</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="print-tax-ids">
                    <hr>
                    <h4>Taxonomy Switcher Helper</h4>
                    <p>Print the terms IDs for the given taxonomy, split by a comma for easy transfer when using the Taxonomy Switcher Plugin</p>
                    <form action="" class="tt-print-tax-ids" method="post">
                        <input type="text" name="tax_name" placeholder="Enter Taxonomy Name" required>
                        <input type="hidden" name="action" value="dx-print-tax-ids">
                        <input type="submit" name="submit" value="Print Taxonomy Terms IDs" class="button-primary">
                    </form>
                    <div class="tax-print-result" style="padding: 1rem;background: #fefefe;margin: 1rem 0;border-radius: 4px;overflow:hidden;">
                        <h4 style="font-size: 1.2rem;text-align: center;background: #28AAE1;margin: 0 0 0.5rem;padding: 0.5rem;border-radius: 4px;color: #000;letter-spacing: 2px;text-transform: uppercase;">Result</h4>
                        <hr>
                        <?php
                            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'dx-print-tax-ids' && isset($_REQUEST['tax_name'])) :
                                $tax_terms = get_terms(
                                    array(
                                        'taxonomy'   => $_REQUEST['tax_name'],
                                        'hide_empty' => false,
                                    )
                                );
                                if( is_wp_error( $tax_terms ) ) {
                                    echo '<span style="background:#f00;color:#fff;padding:0.2rem;border-radius:4px">Something went wrong!</span>';
                                    echo '<div><pre>';
                                    print_r( $tax_terms );
                                    echo '</pre></div>';
                                } else {
                                    if( ! empty( $tax_terms ) ) {
                                        echo '<span style="background:#0f0;color:#000;padding:0.2rem;border-radius:4px">Terms IDs for: <b>' . $_REQUEST['tax_name'] . '</b></span><br>';
                                        echo '<div style="overflow: auto;border: 1px solid #0f0f0f;margin: 1rem 0;"><pre>';
                                        print_r( implode( ',', array_column( $tax_terms, 'term_id' ) ) );
                                        echo '</pre></div>';
                                    } else {
                                        echo '<span style="background:#28AAE1;color:#fff;padding:0.2rem;border-radius:4px">This taxonomy does not have any terms yet!</span>';
                                    }
                                }
                                ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="dx-hidden-section" style="position:relative">
                <button type="button" class="dx-show-hidden" style="position:absolute;right:20px;bottom:0;width:2rem;height:2rem;display:flex;justify-content: center;align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M234.7 42.7L197 56.8c-3 1.1-5 4-5 7.2s2 6.1 5 7.2l37.7 14.1L248.8 123c1.1 3 4 5 7.2 5s6.1-2 7.2-5l14.1-37.7L315 71.2c3-1.1 5-4 5-7.2s-2-6.1-5-7.2L277.3 42.7 263.2 5c-1.1-3-4-5-7.2-5s-6.1 2-7.2 5L234.7 42.7zM46.1 395.4c-18.7 18.7-18.7 49.1 0 67.9l34.6 34.6c18.7 18.7 49.1 18.7 67.9 0L529.9 116.5c18.7-18.7 18.7-49.1 0-67.9L495.3 14.1c-18.7-18.7-49.1-18.7-67.9 0L46.1 395.4zM484.6 82.6l-105 105-23.3-23.3 105-105 23.3 23.3zM7.5 117.2C3 118.9 0 123.2 0 128s3 9.1 7.5 10.8L64 160l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L128 160l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L128 96 106.8 39.5C105.1 35 100.8 32 96 32s-9.1 3-10.8 7.5L64 96 7.5 117.2zm352 256c-4.5 1.7-7.5 6-7.5 10.8s3 9.1 7.5 10.8L416 416l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L480 416l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L480 352l-21.2-56.5c-1.7-4.5-6-7.5-10.8-7.5s-9.1 3-10.8 7.5L416 352l-56.5 21.2z"/></svg>
                </button>
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

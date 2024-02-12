<?php

use TTNetSuite\NetSuiteClient;
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : TT CRON Interval added for every 4 hours
 **/
function tt_custom_cron_schedule( $schedules ) {
    $schedules['every_four_hours'] = array(
        'interval' => 14400,
        'display'  => __( 'Every 4 hours' ),
    );

    //Add every 1 hour to the existing schedules.
    $schedules['every_one_hour'] = array(
        'interval' => 3600,
        'display'  => __( 'Every 1 hour' ),
    );

    return $schedules;
}
add_filter( 'cron_schedules', 'tt_custom_cron_schedule' );
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : TT CRON fire hook every 4 hour
 **/
add_action('tt_wc_ns_sync_hourly_event', 'tt_wc_ns_sync_hourly_event_cb');
function tt_wc_ns_fire_cron_on_wp_init()
{
    if (!wp_next_scheduled('tt_wc_ns_sync_hourly_event')) {
        wp_schedule_event(time(), 'every_four_hours', 'tt_wc_ns_sync_hourly_event');
    }
}


add_action( 'tt_wc_ns_sync_one_hour_event', 'tt_wc_ns_sync_one_hour_event_cb' );

function tt_wc_ns_fire_one_hour_cron() {
    if (!wp_next_scheduled('tt_wc_ns_sync_one_hour_event')) {
        wp_schedule_event(time(), 'every_one_hour', 'tt_wc_ns_sync_one_hour_event');
    }
}
add_action( 'wp', 'tt_wc_ns_fire_one_hour_cron' );

add_action('wp', 'tt_wc_ns_fire_cron_on_wp_init');
function tt_wc_ns_sync_hourly_event_cb()
{
    tt_sync_ns_trips();
    tt_sync_ns_trip_details();
    tt_sync_ns_trip_hotels();
    tt_sync_ns_trip_bikes();
    tt_sync_ns_trip_addons();
    tt_sync_wc_products_from_ns();
}

function tt_wc_ns_sync_one_hour_event_cb() {
    tt_ns_fetch_registration_ids();
}

add_action('tt_trigger_cron_ns_booking', 'tt_trigger_cron_ns_booking_cb', 10, 2);
function tt_trigger_cron_ns_booking_cb($order_id, $user_id = 'null', $is_behalf=false)
{
    if( $is_behalf == false ){
        $is_behalf = get_post_meta($order_id, 'tt_wc_order_ns_is_behalf', true);
        $is_behalf = $is_behalf == true ? true : false; 
    }
    $admin_user_id = get_option( 'admin111' );
    $super_admin = get_user_by('ID', $admin_user_id);
    $super_admin_name = $super_admin->display_name;
    $admin_ns_user_id = get_user_meta(get_current_user_id(), 'ns_customer_internal_id', true);
    $netSuiteClient = new NetSuiteClient();
    $ns_booking_payload = $ns_booking_result = [];
    $user_exist = get_userdata($user_id);
    $is_booking_status = $user_exist ? false : true;
    $wc_booking_result = tt_get_booking_details($order_id, $is_booking_status);
    $guests_count      = count( $wc_booking_result );
    $occupants = tt_get_booking_field('order_id', $order_id, 'trip_room_selection', true);
    $tt_users_indexes = tt_get_booking_guests_indexes($order_id);
    //$dummy_rooms = ["single","single"];
    //Begin: Billing info
    $accepted_p_ids = tt_get_line_items_product_ids();
    $trek_checkoutData = array();
    $order = wc_get_order($order_id);
    $order_items           = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
    foreach ($order_items as $item_id => $item) {
        $product_id = $item['product_id'];
        if (!in_array($product_id, $accepted_p_ids)) {
            $trek_checkoutData = wc_get_order_item_meta($item_id, 'trek_user_checkout_data', true);
        }
    }
    $user_rooms_arr = tt_create_booking_rooms_arr($tt_users_indexes, $trek_checkoutData['occupants']);
    $billing_add_1 = tt_validate($trek_checkoutData['billing_address_1']);
    $billing_add_2 = tt_validate($trek_checkoutData['billing_address_2']);
    $billing_country = tt_validate($trek_checkoutData['billing_country']);
    $billing_fname = tt_validate($trek_checkoutData['billing_first_name']);
    $billing_lname = tt_validate($trek_checkoutData['billing_last_name']);
    $billing_country = tt_validate($trek_checkoutData['billing_country']);
    $billing_state = tt_validate($trek_checkoutData['billing_state']);
    $billing_city = tt_validate($trek_checkoutData['billing_city']);
    $billing_postcode = tt_validate($trek_checkoutData['billing_postcode']);
    if (isset($trek_checkoutData['is_same_billing_as_mailing']) && $trek_checkoutData['is_same_billing_as_mailing'] == 1) {
        $billing_add_1 = tt_validate($trek_checkoutData['shipping_address_1']);
        $billing_add_2 = tt_validate($trek_checkoutData['shipping_address_2']);
        $billing_country = tt_validate($trek_checkoutData['shipping_country']);
        $billing_fname = tt_validate($trek_checkoutData['shipping_first_name']);
        $billing_lname = tt_validate($trek_checkoutData['shipping_last_name']);
        $billing_country = tt_validate($trek_checkoutData['shipping_country']);
        $billing_state = tt_validate($trek_checkoutData['shipping_state']);
        $billing_city = tt_validate($trek_checkoutData['shipping_city']);
        $billing_postcode = tt_validate($trek_checkoutData['shipping_postcode']);
    }
    //End: Billing info
    //Begin: orders extra meta data(Financial Data)
    $order_currency = get_post_meta($order_id, '_order_currency', true);
    $cart_discount = get_post_meta($order_id, '_cart_discount', true);
    $cart_discount = intval( $guests_count ) * floatval( $cart_discount );
    $cart_discount_tax = get_post_meta($order_id, '_cart_discount_tax', true);
    $order_tax = get_post_meta($order_id, '_order_tax', true);
    $order_total = get_post_meta($order_id, '_order_total', true);
    $transaction_id = get_post_meta($order_id, '_wc_cybersource_credit_card_trans_id', true);
    $transaction_date = get_post_meta($order_id, '_wc_cybersource_credit_card_trans_date', true);
    $authorization_amount = get_post_meta($order_id, '_wc_cybersource_credit_card_authorization_amount', true);
    $transaction_deposit = get_post_meta($order_id, '_is_order_transaction_deposit', true);
    /*$posted_payment_token = tt_validate($trek_checkoutData['wc-cybersource-credit-card-payment-token'], '');
    if( $posted_payment_token ){
        $transaction_payment_token = get_post_meta($order_id, '_wc_cybersource_credit_card_payment_token', true);
    }else{
        $transaction_payment_token = get_post_meta($order_id, '_wc_cybersource_credit_card_customer_id', true);
    } */
    $transaction_payment_token = get_post_meta($order_id, '_wc_cybersource_credit_card_customer_id', true);
    $cc_account_four = get_post_meta($order_id, '_wc_cybersource_credit_card_account_four', true);
    $cc_expiry_date = get_post_meta($order_id, '_wc_cybersource_credit_card_card_expiry_date', true);
    $cc_card_type = get_post_meta($order_id, '_wc_cybersource_credit_card_card_type', true);
    $cc_processor_transaction_id = get_post_meta($order_id, '_wc_cybersource_credit_card_processor_transaction_id', true);
    //End: orders extra meta data(Financial Data)
    $depositAmount = tt_get_local_trips_detail('depositAmount', '', $trek_checkoutData['sku'], true);
    $bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', '', $trek_checkoutData['sku'], true);
    $cart_total = $order->get_total();
    if( $transaction_deposit == true ){
        $trip_transaction_amount = $depositAmount;
    }else{
        $trip_transaction_amount = $authorization_amount;
    }
    $booking_index = 0;
    if ($wc_booking_result) {
        foreach ($wc_booking_result as $wc_booking_key => $wc_booking) {
            $ns_trip_ID = NULL;
            if ($wc_booking->product_id) {
                $ns_trip_ID = get_post_meta($wc_booking->product_id, TT_WC_META_PREFIX . 'tripId', true);
            }
            $start_date = date('Y-m-d', $wc_booking->trip_start_date);
            $end_date = date('Y-m-d', $wc_booking->trip_end_date);
            $guest_index_id = $wc_booking->guest_index_id;
            $guest_wp_id = $_SESSION['current_user_ids'];
            $user_rooms_data = $user_rooms_arr['users_in_rooms'];
            $trip_rooms = $user_rooms_arr['rooms'];
            $user_room_index = tt_get_user_room_index_by_user_key($user_rooms_data, $guest_index_id);
            //$ns_trip_ID = 26604;
            $wantPrivate = tt_validate($wc_booking->wantPrivate, 0);
            $referralSourceType = tt_validate($wc_booking->referralSourceType);
            $referralSourceName = tt_validate($wc_booking->referralSourceName);
            //$bikeUpgradePriceDisplayed = tt_validate($wc_booking->bikeUpgradePriceDisplayed, 0);
            //$rooms = tt_validate($wc_booking->trip_room_selection, [] );
            $specialRoomRequests = tt_validate($wc_booking->specialRoomRequests);
            $promoCode = tt_validate($wc_booking->promoCode);
            $wantsInsurance = tt_validate($wc_booking->wantsInsurance, false);
            $insuranceAmount = tt_validate($wc_booking->insuranceAmount, false);
            $wc_coupon_amount = 0;
            if ($promoCode) {
                $wc_coupon = new WC_Coupon($promoCode);
                $wc_coupon_amount = $wc_coupon->amount;
            }
            if ($ns_trip_ID) {
                $fname = tt_validate($wc_booking->guest_first_name);
                $lname = tt_validate($wc_booking->guest_last_name);
                $email = tt_validate($wc_booking->guest_email_address);
                $phone = tt_validate($wc_booking->guest_phone_number);
                $dob = tt_validate($wc_booking->guest_date_of_birth);
                $gender = tt_validate($wc_booking->guest_gender);
                $country = tt_validate($wc_booking->shipping_address_country);
                $address = tt_validate($wc_booking->shipping_address_1) . ' ' . tt_validate($wc_booking->shipping_address_2);
                $city = tt_validate($wc_booking->shipping_address_city);
                $state = tt_validate($wc_booking->shipping_address_state);
                $zipcode = tt_validate($wc_booking->shipping_address_zipcode);
                $rider_height = tt_validate($wc_booking->rider_height);
                $rider_level = tt_validate($wc_booking->rider_level);
                // If $bike_id is with value 0, we need send 0 to NS, that means customer selected "I don't know" option for $bike_size.
                $default_bike_id = '';
                if( 0 == $wc_booking->bike_id ){
                    $default_bike_id = 0;
                }
                $bike_id = tt_validate($wc_booking->bike_id, $default_bike_id);
                $bike_size = tt_validate($wc_booking->bike_size);
                $bike_selection = tt_validate($wc_booking->bike_selection);
                $isBikeUpgrade = tt_validate($wc_booking->isBikeUpgrade, '');
                $saddle_height = tt_validate($wc_booking->saddle_height);
                $pedal_selection = tt_validate($wc_booking->pedal_selection);
                $helmet_selection = tt_validate($wc_booking->helmet_selection);
                $jersey_style = tt_validate($wc_booking->tt_jersey_size);
                $passport_number = tt_validate($wc_booking->passport_number);
                $passport_issue_date = tt_validate($wc_booking->passport_issue_date);
                $passport_expiration_date = tt_validate($wc_booking->passport_expiration_date);
                $passport_place_of_issue = tt_validate($wc_booking->passport_place_of_issue);
                $medications = tt_validate($wc_booking->medications);
                $allergies = tt_validate($wc_booking->allergies);
                $medical_conditions = tt_validate($wc_booking->medical_conditions);
                $allergies = tt_validate($wc_booking->allergies);
                $dietary_restrictions = tt_validate($wc_booking->dietary_restrictions);
                $e_fname = tt_validate($wc_booking->emergency_contact_first_name);
                $e_lname = tt_validate($wc_booking->emergency_contact_last_name);
                $e_phone = tt_validate($wc_booking->emergency_contact_phone);
                $e_relationship = tt_validate($wc_booking->emergency_contact_relationship);
                $ns_user_id = tt_validate($wc_booking->netsuite_guest_registration_id);
                $admin_ns_user_id = 1687333;
                $sales_rep_id = get_user_meta( $guest_wp_id, 'salesrepid', true );
                if ( empty( $sales_rep_id ) ) {
                    $sales_rep_id = '';
                }
                if ( $booking_index == 0 ) {
                    $ns_booking_payload = [
                        "tripDateId" => $ns_trip_ID,
                        "startDate" => $start_date,
                        "endDate" => $end_date,
                        "wantPrivate" => $wantPrivate,
                        //"referralSourceType" => $is_behalf == true ? 19 : '',
                        'salesRepId' => $is_behalf == true ? $sales_rep_id : '',
                        //"referralSourceName" => $is_behalf == true ? $super_admin_name : '',
                        "bikeUpgradePriceDisplayed" => $bikeUpgradePrice,
                        "rooms" => $trip_rooms,
                        "specialRoomRequests" => $specialRoomRequests,
                        "promoCode" => $promoCode,
                        // Financial
                        "paymentInfo" =>
                        [
                            "order_currency" => $order_currency,
                            "cart_discount" => $cart_discount,
                            "cart_discount_tax" => $cart_discount_tax,
                            "order_tax" => $order_tax,
                            "order_total" => $order_total,
                            "transaction_id" => $transaction_id,
                            "transaction_date" => $transaction_date,
                            "transaction_authorization_amount" => $trip_transaction_amount,
                            "transaction_deposit" => ($transaction_deposit ? 1 : 0),
                            "transaction_payment_token" => $transaction_payment_token,
                            "transaction_credit_card_account_four" => $cc_account_four,
                            "transaction_card_card_expiry_date" => $cc_expiry_date,
                            "transaction_credit_card_card_type" => $cc_card_type,
                            "transaction_credit_card_processor_transaction_id" => $cc_processor_transaction_id
                        ],
                        "billingAddress" => [
                            "firstName" => $billing_fname,
                            "lastName" => $billing_lname,
                            "country" => $billing_country,
                            "address" => $billing_add_1,
                            "address2" => $billing_add_2,
                            "city" => $billing_city,
                            "state" => $billing_state,
                            "zip" => $billing_postcode
                        ]
                    ];
                }
                $ns_booking_payload['guestsData'][$wc_booking_key] = [
                    "guestId" => $ns_user_id,
                    "firstName" => $fname,
                    "lastName" => $lname,
                    "email" => $email,
                    "phone" => $phone,
                    "birthDate" => $dob,
                    "genderId" => $gender,
                    "country" => $country,
                    "address" => $address,
                    "city" => $city,
                    "state" => $state,
                    "zip" => $zipcode,
                    "roomIndex" =>  $user_room_index,
                    "riderLevelId" => $rider_level,
                    "bikeId" => $bike_id,
                    'bike_size' => tt_validate($bike_size),
                    "bikeTypeName" => $bike_selection,
                    "isBikeUpgrade" => $isBikeUpgrade,
                    "heightId" => $rider_height,
                    "pedalsId" => $pedal_selection,
                    "addOnIds" => [],
                    "helmetId" => $helmet_selection,
                    "jerseyId" => $jersey_style,
                    "passportNumber" => $passport_number,
                    "passportPlaceOfIssue" => $passport_place_of_issue,
                    "passportCountryOfIssue" => "",
                    "passportExpDate" => $passport_expiration_date,
                    "passportIssueDate" => $passport_issue_date,
                    "placeOfBirth" => $passport_place_of_issue,
                    "medications" => $medications,
                    "allergies" => $allergies,
                    "medicalConditions" => $medical_conditions,
                    "dietaryRestrictions" => $dietary_restrictions,
                    "ecFirstName" => $e_fname,
                    "ecLastName" => $e_lname,
                    "ecPhone" => $e_phone,
                    "ecRelationship" => $e_relationship,
                    "wantsInsurance" => $wantsInsurance,
                    "insuranceAmount" => $insuranceAmount,
                    'tripDiscountAmount' => $wc_coupon_amount
                ];
            }
            $booking_index++;
        }
        if ($ns_booking_payload) {
            $ns_booking_result = $netSuiteClient->post(BOOKING_SCRIPT_ID . ':2', json_encode($ns_booking_payload));
            tt_update_user_booking_info($order_id, $ns_booking_result);
            tt_add_error_log('BOOKING_SCRIPT_ID: ' . BOOKING_SCRIPT_ID, $ns_booking_payload, $ns_booking_result);
        }else{
            tt_add_error_log('BOOKING_SCRIPT_ID: ' . BOOKING_SCRIPT_ID, [], ['message' => 'no Payload found']);
        }
    }else{
        tt_add_error_log('BOOKING_SCRIPT_ID: ' . BOOKING_SCRIPT_ID, ['order_id' => $order_id ], ['message' => 'no booking found for Order ID']);
    }
}
function tt_get_common_logs($limit=10)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'tt_common_error_logs';
    $sql = "SELECT * from {$table_name} ORDER BY id DESC";
    if( $limit ){
        $sql .= " limit {$limit}";
    }
    $logs = $wpdb->get_results($sql, ARRAY_A);
    $trHTML = '';
    if ($logs) {
        foreach ($logs as $log_key => $log) {
            $args = $log['args'];
            $response = $log['response'];
            $type = $log['type'];
            $created_at = $log['created_at'];
            $trHTML .= '<tr>
            <td>' . ($log_key + 1) . '</td>
            <td>' . $type . '</td>
            <td>' . $args . '</td>
            <td>' . $response . '</td>
            <td>' . $created_at . '</td>
        </tr>';
        }
    } else {
        $trHTML .= '<tr><th colspan="5">No logs found!</th></tr>';
    }
    return $trHTML;
}
function tt_get_bookings($limit=10)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_bookings';
    $sql = "SELECT * from {$table_name} ORDER BY id DESC";
    if( $limit ){
        $sql .= " limit {$limit}";
    }
    $bookings = $wpdb->get_results($sql, ARRAY_A);
    $trHTML = '';
    if ($bookings) {
        foreach ($bookings as $booking) {
            $id = $booking['id'];
            $booking_id = $booking['ns_trip_booking_id'];
            $order_id = $booking['order_id'];
            $user_id = $booking['user_id'];
            $ns_user_id = $booking['netsuite_guest_registration_id'];
            $promoCode = $booking['promoCode'];
            $trip_code = $booking['trip_code'];
            $trip_name = $booking['trip_name'];
            $no_guests = $booking['trip_number_of_guests'];
            $rooms = $booking['trip_room_selection'];
            $is_primary = $booking['guest_is_primary'];
            $fname = $booking['guest_first_name'];
            $lname = $booking['guest_last_name'];
            $name = $fname.' '.$lname;
            $wc_meta = get_post_meta($order_id);
            $ns_booking_status = $booking['ns_booking_status'];
            $created_at = $booking['created_at'];
            $trHTML .= '<tr>
            <td>' . $id . '</td>
            <td>' . $booking_id . '</td>
            <td>' . $order_id . '</td>
            <td>' . $user_id . '</td>
            <td>' . $ns_user_id . '</td>
            <td>' . $promoCode . '</td>
            <td>' . $trip_code . '</td>
            <td>' . $trip_name . '</td>
            <td>' . $no_guests . '</td>
            <td>' . $rooms . '</td>
            <td>' . $is_primary . '</td>
            <td>' . $name . '</td>
            <td class="expandable-cell"><code>' . json_encode($wc_meta) . '</code><span class="expand-cell expand-single" title="Expand Cell"></span></td>
            <td>' . $ns_booking_status . '</td>
            <td>' . $created_at . '</td>
        </tr>';
        }
    } else {
        $trHTML .= '<tr><th colspan="14">No bookings found!</th></tr>';
    }
    return $trHTML;
}
function ttnsw_enqueue_custom_admin_style() {
    $currentScreen = get_current_screen();
    if ($currentScreen->base == 'netsuitewc_page_tt-common-logs' || $currentScreen->base == 'toplevel_page_trek-travel-ns-wc' || $currentScreen->base == 'netsuitewc_page_tt-bookings' ) {
        wp_register_style( 'ttnsw-style', TTNSW_URL . '/assets/ttnsw-styles.css', false, time() );
        wp_enqueue_style( 'ttnsw-style' );
        wp_register_script( 'ttnsw-developer', TTNSW_URL.'/assets/ttnsw-developer.js', array(), time(), true );
        wp_enqueue_script( 'ttnsw-developer' );
    }
}
add_action( 'admin_enqueue_scripts', 'ttnsw_enqueue_custom_admin_style' );

function tt_update_bikes_table() {
    global $wpdb;

    if( $_GET['trekupdatebikestable'] == 'enable' && current_user_can( 'administrator' ) ) {

        // Define the table name
        $table_name = $wpdb->prefix . 'netsuite_trip_bikes';

        $after_column_name = 'bikeType';

        // Define the new column name and data type
        $new_column_name = 'bikeModel';
        $data_type = 'TEXT';

        // Check if the column already exists
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE '$new_column_name'");

        if (empty($column_exists)) {
            // Alter the table to add the new column
            $wpdb->query("ALTER TABLE $table_name ADD $new_column_name $data_type AFTER $after_column_name");

            // You can also update the new column with initial values if needed
            $wpdb->query("UPDATE $table_name SET $new_column_name = 'NULL'");

            echo '<div class="notice notice-success is-dismissible">';
            echo '<h2>Updated current DB</h2>';
            echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        } else {
            echo 'There has been an issue :/';
        }
    }
}

add_action( 'admin_init', 'tt_update_bikes_table' );
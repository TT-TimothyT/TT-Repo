<?php

use TTNetSuite\NetSuiteClient;

add_action( 'tt_trigger_cron_ns_booking', 'tt_trigger_cron_ns_booking_cb', 10, 2 );
function tt_trigger_cron_ns_booking_cb( $order_id, $user_id = 'null', $is_behalf = false ) {
    if( $is_behalf == false ){
        $is_behalf = get_post_meta($order_id, 'tt_wc_order_ns_is_behalf', true);
        $is_behalf = $is_behalf == true ? true : false; 
    }
    $admin_user_id          = get_option( 'admin111' );
    $super_admin            = get_user_by('ID', $admin_user_id);
    $super_admin_name       = $super_admin->display_name;
    $admin_ns_user_id       = get_user_meta(get_current_user_id(), 'ns_customer_internal_id', true);
    $netSuiteClient         = new NetSuiteClient();
    $ns_booking_payload = $ns_booking_result = [];
    $guests_email_addresses = [];
    $user_exist             = get_userdata($user_id);
    $is_booking_status      = $user_exist ? false : true;
    $wc_booking_result      = tt_get_booking_details($order_id, $is_booking_status);
    $guests_count           = count( $wc_booking_result );
    $occupants              = tt_get_booking_field('order_id', $order_id, 'trip_room_selection', true);
    $tt_users_indexes       = tt_get_booking_guests_indexes($order_id);
    //$dummy_rooms = ["single","single"];
    //Begin: Billing info
    $accepted_p_ids         = tt_get_line_items_product_ids();
    $trek_checkoutData      = array();
    $order                  = wc_get_order($order_id);
    $order_items            = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
    foreach ( $order_items as $item_id => $item ) {
        $product_id = $item['product_id'];
        if ( ! in_array( $product_id, $accepted_p_ids ) ) {
            $trek_checkoutData = wc_get_order_item_meta( $item_id, 'trek_user_checkout_data', true );
        }
    }

    // RETRIEVE INSTRUMENT IDENTIFIER //
    global $wpdb;
    $token = get_post_meta( $order_id, '_wc_cybersource_credit_card_payment_token', true );

    // Prepare and execute the query
    $token_id = $wpdb->get_var( $wpdb->prepare( "
        SELECT token_id 
        FROM {$wpdb->prefix}woocommerce_payment_tokens 
        WHERE token = %s
    ", $token ) );

    // Prepare and execute the query
    $instrument_identifier = $wpdb->get_var( $wpdb->prepare( "
        SELECT meta_value 
        FROM {$wpdb->prefix}woocommerce_payment_tokenmeta 
        WHERE payment_token_id = %d 
        AND meta_key = 'instrument_identifier'
    ", $token_id ) );

    // Check if an instrument identifier was found
    if ( $instrument_identifier ) {
        // Unserialize the instrument identifier
        $instrument_identifier_array = unserialize( $instrument_identifier );

        // Extract the identifier
        $identifier = isset( $instrument_identifier_array['id'] ) ? $instrument_identifier_array['id'] : '';

    } else {
        $identifier = '';
    }

    $user_rooms_arr   = tt_create_booking_rooms_arr($tt_users_indexes, $trek_checkoutData['occupants']);
    $billing_add_1    = tt_validate($trek_checkoutData['billing_address_1']);
    $billing_add_2    = tt_validate($trek_checkoutData['billing_address_2']);
    $billing_country  = tt_validate($trek_checkoutData['billing_country']);
    $billing_fname    = tt_validate($trek_checkoutData['billing_first_name']);
    $billing_lname    = tt_validate($trek_checkoutData['billing_last_name']);
    $billing_country  = tt_validate($trek_checkoutData['billing_country']);
    $billing_state    = tt_validate($trek_checkoutData['billing_state']);
    $billing_city     = tt_validate($trek_checkoutData['billing_city']);
    $billing_postcode = tt_validate($trek_checkoutData['billing_postcode']);
    if ( isset( $trek_checkoutData['is_same_billing_as_mailing'] ) && $trek_checkoutData['is_same_billing_as_mailing'] == 1 ) {
        $billing_add_1    = tt_validate($trek_checkoutData['shipping_address_1']);
        $billing_add_2    = tt_validate($trek_checkoutData['shipping_address_2']);
        $billing_country  = tt_validate($trek_checkoutData['shipping_country']);
        $billing_fname    = tt_validate($trek_checkoutData['shipping_first_name']);
        $billing_lname    = tt_validate($trek_checkoutData['shipping_last_name']);
        $billing_country  = tt_validate($trek_checkoutData['shipping_country']);
        $billing_state    = tt_validate($trek_checkoutData['shipping_state']);
        $billing_city     = tt_validate($trek_checkoutData['shipping_city']);
        $billing_postcode = tt_validate($trek_checkoutData['shipping_postcode']);
    }
    //End: Billing info
    //Begin: orders extra meta data(Financial Data)
    $order_currency       = get_post_meta($order_id, '_order_currency', true);
    $cart_discount        = get_post_meta($order_id, '_cart_discount', true);
    $cart_discount        = intval( $guests_count ) * floatval( $cart_discount );
    $cart_discount_tax    = get_post_meta($order_id, '_cart_discount_tax', true);
    $order_tax            = get_post_meta($order_id, 'tt_order_tax', true);
    $order_total          = get_post_meta($order_id, '_order_total', true);
    $transaction_id       = get_post_meta($order_id, '_wc_cybersource_credit_card_trans_id', true);
    $transaction_date     = get_post_meta($order_id, '_wc_cybersource_credit_card_trans_date', true);
    $authorization_amount = get_post_meta($order_id, '_wc_cybersource_credit_card_authorization_amount', true);
    $transaction_deposit  = get_post_meta($order_id, '_is_order_transaction_deposit', true);
    /*$posted_payment_token = tt_validate($trek_checkoutData['wc-cybersource-credit-card-payment-token'], '');
    if( $posted_payment_token ){
        $transaction_payment_token = get_post_meta($order_id, '_wc_cybersource_credit_card_payment_token', true);
    }else{
        $transaction_payment_token = get_post_meta($order_id, '_wc_cybersource_credit_card_customer_id', true);
    } */
    $transaction_payment_token   = $identifier;
    $cc_account_four             = get_post_meta($order_id, '_wc_cybersource_credit_card_account_four', true);
    $cc_expiry_date              = get_post_meta($order_id, '_wc_cybersource_credit_card_card_expiry_date', true);
    $cc_card_type                = get_post_meta($order_id, '_wc_cybersource_credit_card_card_type', true);
    $cc_processor_transaction_id = get_post_meta($order_id, '_wc_cybersource_credit_card_processor_transaction_id', true);
    //End: orders extra meta data(Financial Data)
    $depositAmount = tt_get_local_trips_detail('depositAmount', '', $trek_checkoutData['sku'], true);
    $bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', '', $trek_checkoutData['sku'], true);
    $cart_total = $order->get_total();
    if( $transaction_deposit == true ){
        $trip_transaction_amount = intval( $guests_count ) * floatval( $depositAmount );
    }else{
        $trip_transaction_amount = $authorization_amount;
    }
    $booking_index = 0;
    if ($wc_booking_result) {
        $total_insuarance_ammount = 0;
        $trip_info                = tt_get_trip_pid_sku_from_cart($order_id);
        $is_hiking_checkout       = tt_is_product_line( 'Hiking', $trip_info['sku'] );
        foreach ($wc_booking_result as $wc_booking_key => $wc_booking) {
            $ns_trip_ID = NULL;
            if ($wc_booking->product_id) {
                $ns_trip_ID = get_post_meta($wc_booking->product_id, TT_WC_META_PREFIX . 'tripId', true);
            }
            $start_date          = date('Y-m-d', $wc_booking->trip_start_date);
            $end_date            = date('Y-m-d', $wc_booking->trip_end_date);
            $guest_index_id      = $wc_booking->guest_index_id;
            $guest_wp_id         = $_SESSION['current_user_ids'];
            $user_rooms_data     = $user_rooms_arr['users_in_rooms'];
            $trip_rooms          = $user_rooms_arr['rooms'];
            $user_room_index     = tt_get_user_room_index_by_user_key($user_rooms_data, $guest_index_id);
            //$ns_trip_ID = 26604;
            $wantPrivate         = tt_validate($wc_booking->wantPrivate, 0);
            $referralSourceType  = tt_validate($wc_booking->referralSourceType);
            $referralSourceName  = tt_validate($wc_booking->referralSourceName);
            //$bikeUpgradePriceDisplayed = tt_validate($wc_booking->bikeUpgradePriceDisplayed, 0);
            //$rooms = tt_validate($wc_booking->trip_room_selection, [] );
            $specialRoomRequests = tt_validate($wc_booking->specialRoomRequests);
            $promoCode           = tt_validate($wc_booking->promoCode);
            $wantsInsurance      = tt_validate($wc_booking->wantsInsurance, false);
            $insuranceAmount     = tt_validate($wc_booking->insuranceAmount, 0);
            if( $wantsInsurance ) {
                $total_insuarance_ammount += floatval( $insuranceAmount );
            }
            $wc_coupon_amount = 0;
            if ( $promoCode ) {
                $wc_coupon = new WC_Coupon( $promoCode );
                $wc_coupon_amount = $wc_coupon->amount;
            }
            if ( $ns_trip_ID ) {
                $fname          = tt_validate( $wc_booking->guest_first_name );
                $lname          = tt_validate( $wc_booking->guest_last_name );
                $email          = tt_validate( $wc_booking->guest_email_address );
                $phone          = tt_validate( $wc_booking->guest_phone_number );
                $dob            = tt_validate( $wc_booking->guest_date_of_birth );
                $gender         = tt_validate( $wc_booking->guest_gender );
                $country        = tt_validate( $wc_booking->shipping_address_country );
                $address        = tt_validate( $wc_booking->shipping_address_1 ) . ' ' . tt_validate( $wc_booking->shipping_address_2 );
                $city           = tt_validate( $wc_booking->shipping_address_city );
                $state          = tt_validate( $wc_booking->shipping_address_state );
                $zipcode        = tt_validate( $wc_booking->shipping_address_zipcode );
                $rider_height   = tt_validate( $wc_booking->rider_height );
                $rider_level    = tt_validate( $wc_booking->rider_level );
                $activity_level = tt_validate( $wc_booking->activity_level );
                // If $bike_id is with value 0, we need send 0 to NS, that means customer selected "I don't know" option for $bike_size.
                $default_bike_id = '';
                if( 0 == $wc_booking->bike_id ){
                    if ( ! $is_hiking_checkout ) {
                        $default_bike_id = 0;
                    } else {
                        $default_bike_id = HIKER_BIKE_ID;
                    }
                }
                $bike_id                  = tt_validate( $wc_booking->bike_id, $default_bike_id );
                $bike_size                = tt_validate( $wc_booking->bike_size );
                $bike_selection           = tt_validate( $wc_booking->bike_selection );
                $bike_type_name           = tt_validate( $wc_booking->bikeTypeName );
                $bike_comments            = tt_validate( $wc_booking->bike_comments );
                $isBikeUpgrade            = tt_validate( $wc_booking->isBikeUpgrade, '' );
                $saddle_height            = tt_validate( $wc_booking->saddle_height );
                $pedal_selection          = tt_validate( $wc_booking->pedal_selection );
                $helmet_selection         = tt_validate( $wc_booking->helmet_selection );
                $jersey_style             = tt_validate( $wc_booking->tt_jersey_size );
                $tshirt_size              = tt_validate( $wc_booking->tshirt_size );
                $passport_number          = tt_validate( $wc_booking->passport_number );
                $passport_issue_date      = tt_validate( $wc_booking->passport_issue_date );
                $passport_expiration_date = tt_validate( $wc_booking->passport_expiration_date );
                $passport_place_of_issue  = tt_validate( $wc_booking->passport_place_of_issue );
                $medications              = tt_validate( $wc_booking->medications );
                $allergies                = tt_validate( $wc_booking->allergies );
                $medical_conditions       = tt_validate( $wc_booking->medical_conditions );
                $allergies                = tt_validate( $wc_booking->allergies );
                $dietary_restrictions     = tt_validate( $wc_booking->dietary_restrictions );
                $e_fname                  = tt_validate( $wc_booking->emergency_contact_first_name );
                $e_lname                  = tt_validate( $wc_booking->emergency_contact_last_name );
                $e_phone                  = tt_validate( $wc_booking->emergency_contact_phone );
                $e_relationship           = tt_validate( $wc_booking->emergency_contact_relationship );
                $ns_user_id               = tt_validate( $wc_booking->netsuite_guest_registration_id );
                $admin_ns_user_id         = 1687333;
                $sales_rep_id             = get_user_meta( $guest_wp_id, 'salesrepid', true );
                if ( empty( $sales_rep_id ) ) {
                    $sales_rep_id = '';
                }
                if ( $booking_index == 0 ) {
                    $ns_booking_payload = [
                        "tripDateId"                => $ns_trip_ID,
                        "startDate"                 => $start_date,
                        "endDate"                   => $end_date,
                        "wantPrivate"               => $wantPrivate,
                        // "referralSourceType"        => $is_behalf == true ? 19 : '',
                        'salesRepId'                => $is_behalf == true ? $sales_rep_id : '',
                        // "referralSourceName"        => $is_behalf == true ? $super_admin_name : '',
                        "bikeUpgradePriceDisplayed" => $bikeUpgradePrice,
                        "rooms"                     => $trip_rooms,
                        "specialRoomRequests"       => $specialRoomRequests,
                        "promoCode"                 => $promoCode,
                        // Financial
                        "paymentInfo"               =>
                        [
                            "order_currency"                                   => $order_currency,
                            "cart_discount"                                    => $cart_discount,
                            "cart_discount_tax"                                => $cart_discount_tax,
                            "order_tax"                                        => $order_tax,
                            "order_total"                                      => $order_total,
                            "transaction_id"                                   => $transaction_id,
                            "transaction_date"                                 => $transaction_date,
                            "transaction_authorization_amount"                 => $trip_transaction_amount,
                            "transaction_deposit"                              => ($transaction_deposit ? 1 : 0),
                            "transaction_payment_token"                        => $transaction_payment_token,
                            "transaction_credit_card_account_four"             => $cc_account_four,
                            "transaction_card_card_expiry_date"                => $cc_expiry_date,
                            "transaction_credit_card_card_type"                => $cc_card_type,
                            "transaction_credit_card_processor_transaction_id" => $cc_processor_transaction_id
                        ],
                        "billingAddress"            => [
                            "firstName" => $billing_fname,
                            "lastName"  => $billing_lname,
                            "country"   => $billing_country,
                            "address"   => $billing_add_1,
                            "address2"  => $billing_add_2,
                            "city"      => $billing_city,
                            "state"     => $billing_state,
                            "zip"       => $billing_postcode
                        ]
                    ];
                }
                $ns_booking_payload['guestsData'][$wc_booking_key] = [
                    "guestId"                => $ns_user_id,
                    "firstName"              => $fname,
                    "lastName"               => $lname,
                    "email"                  => $email,
                    "phone"                  => $phone,
                    "birthDate"              => $dob,
                    "genderId"               => $gender,
                    "country"                => $country,
                    "address"                => $address,
                    "city"                   => $city,
                    "state"                  => $state,
                    "zip"                    => $zipcode,
                    "roomIndex"              => $user_room_index,
                    "activityLevelId"        => $activity_level,
                    "riderLevelId"           => $rider_level,
                    "bikeId"                 => $bike_id,
                    'bike_size'              => tt_validate($bike_size),
                    "bikeTypeName"           => $bike_type_name,
                    "bikeComments"           => $bike_comments,
                    "isBikeUpgrade"          => $isBikeUpgrade,
                    "heightId"               => $rider_height,
                    "pedalsId"               => $pedal_selection,
                    "addOnIds"               => [],
                    "helmetId"               => $helmet_selection,
                    "jerseyId"               => $jersey_style,
                    "tshirtSizeId"           => $tshirt_size,
                    "passportNumber"         => $passport_number,
                    "passportPlaceOfIssue"   => $passport_place_of_issue,
                    "passportCountryOfIssue" => "",
                    "passportExpDate"        => $passport_expiration_date,
                    "passportIssueDate"      => $passport_issue_date,
                    "placeOfBirth"           => $passport_place_of_issue,
                    "wantsInsurance"         => $wantsInsurance,
                    "insuranceAmount"        => $insuranceAmount,
                    'tripDiscountAmount'     => $wc_coupon_amount
                ];

                /**
                 * Optional Data. Do not send an empty field, to prevent info wiped from NS.
                 * This is most applicable for Secondary Guests, because in the trek-booking-engine.php,
                 * for the secondary guest, we keep empty data, for the optional fields.
                 * (we can upgrade this in the feature, to check for existing secondary guests).
                 * Note: The data for those fields is taken from the user's meta.
                 */

                // Optional Medical Info. Add key to the payload if has info.
                if ( ! empty( $medications ) ) {
                    $ns_booking_payload['guestsData'][$wc_booking_key]['medications'] = $medications;
                }
                if ( ! empty( $allergies ) ) {
                    $ns_booking_payload['guestsData'][$wc_booking_key]['allergies'] = $allergies;
                }
                if ( ! empty( $medical_conditions ) ) {
                    $ns_booking_payload['guestsData'][$wc_booking_key]['medicalConditions'] = $medical_conditions;
                }
                if ( ! empty( $dietary_restrictions ) ) {
                    $ns_booking_payload['guestsData'][$wc_booking_key]['dietaryRestrictions'] = $dietary_restrictions;
                }

                // Optional Emergency Contact Info. Add key to the payload if has info.
                if ( ! empty( $e_fname ) ) {
                    $ns_booking_payload['guestsData'][$wc_booking_key]['ecFirstName'] = $e_fname;
                }
                if ( ! empty( $e_lname ) ) {
                    $ns_booking_payload['guestsData'][$wc_booking_key]['ecLastName'] = $e_lname;
                }
                if ( ! empty( $e_phone ) ) {
                    $ns_booking_payload['guestsData'][$wc_booking_key]['ecPhone'] = $e_phone;
                }
                if ( ! empty( $e_relationship ) ) {
                    $ns_booking_payload['guestsData'][$wc_booking_key]['ecRelationship'] = $e_relationship;
                }

                // Store all guest emails, so can use it in the tt_update_user_booking_info() function, to check if there existing users with these emails in WP and those users don't have ns_customer_internal_id (ns_user_id) can repair this on order creating.
                $guests_email_addresses[ $wc_booking_key ] = $email;
            }
            $booking_index++;
        }
        if ( $transaction_deposit == true ) {
            $ns_booking_payload["paymentInfo"]["transaction_authorization_amount"] = $trip_transaction_amount + $total_insuarance_ammount;
        }
        // Check if the order is with a deposit to overwrite the order total that is sent to NS with the real full order amount.
        $pay_amount = tt_validate( $trek_checkoutData['pay_amount'] );
        if( ! empty( $pay_amount ) ) {
            if( 'deposite' === $pay_amount ) {
                $order_total_full_amount = tt_validate( $trek_checkoutData['cart_total_full_amount'] );
                if( ! empty( $order_total_full_amount ) ) {
                    $ns_booking_payload["paymentInfo"]["order_total"] = $order_total_full_amount;
                }
            }
        }
        // NS Expects The Order Total without Travel Protections!
        $ns_booking_payload["paymentInfo"]["order_total"] = round( $ns_booking_payload["paymentInfo"]["order_total"] - $total_insuarance_ammount, 2 );
        if ( $ns_booking_payload ) {
            $ns_booking_result = $netSuiteClient->post( BOOKING_SCRIPT_ID, json_encode( $ns_booking_payload ) );
            tt_update_user_booking_info( $order_id, $ns_booking_result, $guests_email_addresses );
            tt_add_error_log( 'BOOKING_SCRIPT_ID: ' . BOOKING_SCRIPT_ID, $ns_booking_payload, $ns_booking_result );
        } else {
            tt_add_error_log( 'BOOKING_SCRIPT_ID: ' . BOOKING_SCRIPT_ID, [], ['message' => 'no Payload found'] );
            do_action( 'tt_set_ns_booking_status', $order_id, 'booking_onhold' );
        }
    } else {
        tt_add_error_log( 'BOOKING_SCRIPT_ID: ' . BOOKING_SCRIPT_ID, ['order_id' => $order_id ], ['message' => 'no booking found for Order ID'] );
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

/**
 * Check if we have this booking in guest_bookings table.
 *
 * @param int $booking_id NS Booking ID.
 *
 * @return array|bool
 */
function tt_check_booking_existing( $booking_id ) {
    if( empty( $booking_id ) ) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_bookings';
    $sql = "SELECT DISTINCT gb.order_id from {$table_name} as gb WHERE gb.ns_trip_booking_id = {$booking_id}";

    $result = $wpdb->get_results( $sql );

    return $result;
}

/**
 * Get Booking info from NS.
 * 
 * @param int $booking_id The NS Booking ID.
 * @uses NS Script GET_BOOKING_SCRIPT_ID
 * 
 * @return object Booking data object.
 */
function tt_get_ns_booking_info( $booking_id ) {
    if( empty( $booking_id ) ) {
        return false;
    }
    // 1304&deploy=1&bookingId=893165
    $net_suite_client = new NetSuiteClient();
    $args             = array( 'bookingId' => $booking_id );
    $ns_booking_data  = $net_suite_client->get( GET_BOOKING_SCRIPT_ID, $args );

    // Booking info not found.
    if( ! $ns_booking_data ) {
        return false;
    }

    return $ns_booking_data;
}

/**
 * Get Guest info from NS.
 * This information includes user preferences.
 * If you like can choose to take information for the guest
 * with his bookings or without them.
 * 
 * @param int $ns_user_id The NS Booking ID.
 * @param int $with_booking_info Should include bookings info: 1 for yes or 0 for no.
 * @uses NS Script USER_BOOKINGS_SCRIPT_ID
 * 
 * @return object|false Guest data in NS.
 */
function tt_get_ns_guest_info( $ns_user_id, $with_booking_info = 1 ) {
    if( empty( $ns_user_id ) ) {
        return false;
    }
    // 1305&deploy=1&guestId=2395994&includeBookingInfo=1
    $net_suite_client = new NetSuiteClient();
    $args             = array( 'guestId' => $ns_user_id, 'includeBookingInfo' => $with_booking_info );
    $ns_guest_data    = $net_suite_client->get( USER_BOOKINGS_SCRIPT_ID, $args );

    // Guest info not found.
    if( ! $ns_guest_data ) {
        return false;
    }

    return $ns_guest_data;
}

/**
 * Get Guest Registrations info from NS.
 * 
 * @param int|array $booking_id The NS Booking ID.
 * @param bool      $is_single Whether to return a single object or an array.
 * @uses NS Script GET_REGISTRATIONS_SCRIPT_ID
 * 
 * @return array|object|false Guest registrations data.
 */
function tt_get_ns_guest_registrations_info( $user_reg_ids, $is_single = false ) {
    if( empty( $user_reg_ids ) ) {
        return false;
    }

    $registration_ids = is_array( $user_reg_ids ) ? $user_reg_ids : array( $user_reg_ids );
    // 1294&deploy=2&registrationIds=322700,322699
    $net_suite_client  = new NetSuiteClient();
    $args              = array( 'registrationIds' => implode( ',', $registration_ids ) );
    $ns_guest_reg_data = $net_suite_client->get( GET_REGISTRATIONS_SCRIPT_ID, $args );

    // Guest Registrations not found.
    if( ! $ns_guest_reg_data ) {
        return false;
    }

    if( $is_single ) {
        return $ns_guest_reg_data[0];
    }

    return $ns_guest_reg_data;
}

/**
 * Get Modified Guest Registrations data from NS starting from the given period in the past.
 * 
 * @param string $time_range The period in the past from which to start search modified registrations.
 * @uses NS Script GET_MODIFIED_REGISTRATIONS
 * 
 * @return array Last modified Guest registrations data in array with objects from id, email and tripId.
 */
function tt_get_ns_guest_modified_registrations( $time_range = DEFAULT_TIME_RANGE_LOCKING_STATUS ) {

    // Fire the NS script to fetch all the registration ids for the past 4 hours.
    $modified_after = date( 'Y-m-d H:i:s', strtotime( $time_range ) );

    // Format should be YYYY-MM-DDTHH:mm:SS.
    $modified_after_dt = gmdate( "Y-m-d\TH:i:s", strtotime( $modified_after ) );

    // 1293&modifiedAfter=2024-01-25&deploy=2
    // 1293&modifiedAfter=2024-04-18T10:19:20&deploy=2
    $net_suite_client       = new NetSuiteClient();
    $args                   = array( 'modifiedAfter' => $modified_after_dt );
    $ns_guest_modified_regs = $net_suite_client->get( GET_MODIFIED_REGISTRATIONS, $args );

    // Guest Registrations not found.
    if( ! $ns_guest_modified_regs ) {
        return false;
    }

    if( isset( $ns_guest_modified_regs->status ) && -1 == $ns_guest_modified_regs->status ) {
        /**
         * Possible errors:
         *
         * error_message: "Please provide modifiedAfter date in the format YYYY-MM-DD"
         * error_message: "Please provide modifiedAfter Date"
         */
        return false;
    }

    return $ns_guest_modified_regs;
}

/**
 * Create Orders Programmatically.
 * 
 * @param object $booking_id NS Booking ID.
 * @param bool $print_result Should print the result of order creation.
 * @return object $order The Newly created WC order.
 */
function tt_create_order( $booking_id = null, $print_result = false ) {
    if( ! $booking_id ) {
        return;
    }

    // *** Basic Order Billing Info ***
    $pr_guest_first_name = 'Woo';
    $pr_guest_last_name  = 'Customer';
    
    // *** Create Order ***
    $order = wc_create_order();
    
    // Add billing and shipping addresses.
    $address = array(
        'first_name' => $pr_guest_first_name,
        'last_name'  => $pr_guest_last_name,
    );

    $order->set_address( $address, 'billing' );
    $order->set_status( 'wc-processing', 'Order Created Programmatically' );
    $order->save();

    // *** Update Order Meta ***
    update_post_meta( $order->id, 'tt_meta_guest_booking_id', $booking_id );
    update_post_meta( $order->id, 'tt_wc_order_finished_status', 'not-finished' );
    update_post_meta( $order->id, 'tt_wc_order_type', 'auto-generated' );

    if( $print_result ) {
        echo '<pre>';
        print_r( $order );
        echo '</pre>';
    }

    return $order;
}

/**
 * Take WC order by given booking ID.
 * Since we store the booking ID in the order's meta,
 * we can take the order for a given booking.
 * 
 * @param int $booking_id The NS Booking ID.
 * @param bool $full_order Full order Object or the ID only.
 *
 * @return object|int|bool
 */
function tt_get_order_by_booking( $booking_id, $full_order = true ) {

    $args = array(
        // 'status'       => 'completed', // Accepts a string: one of 'pending', 'processing', 'on-hold', 'completed', 'refunded, 'failed', 'cancelled', or a custom order status.
        'meta_key'     => 'tt_meta_guest_booking_id', // Postmeta key field.
        'meta_value'   => $booking_id, // Postmeta value field.
        'meta_compare' => '=', // Possible values are ‘=’, ‘!=’, ‘>’, ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’, ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ (only in WP >= 3.5), and ‘NOT EXISTS’ (also only in WP >= 3.5). Values ‘REGEXP’, ‘NOT REGEXP’ and ‘RLIKE’ were added in WordPress 3.7. Default value is ‘=’.
        'return'       => $full_order ? 'objects' : 'ids' // Accepts a string: 'ids' or 'objects'. Default: 'objects'.
    );

    $orders = wc_get_orders( $args );

    if( ! empty( $orders ) ) {
        return $orders[0];
    }

    return false;
}

/**
 * Take all bike info from `netsuite_trip_detail` by given trip code/sku and bike ID.
 *
 * @param string     $trip_code The Trip code or SKU. 
 * @param int|string $bike_id The Bike ID.
 *
 * @uses tt_get_local_bike_detail()
 *
 * @return bool|array false on not found result or array with bike info.
 */
function tt_get_bike_attributes_by_bike_id( $trip_id = '', $trip_code = '', $bike_id = 0 ) {
    if ( empty( $trip_code ) || empty( $bike_id ) ) {
        // Missing required parameters.
        return false;
    }

	$bike_arr = tt_get_local_bike_detail( $trip_id, $trip_code, $bike_id );

	if( empty( $bike_arr ) ) {
		return false;
	}

	return $bike_arr[0];
}

/**
 * Finish the migrated order on the first registered guest.
 */
function tt_finalize_migrated_order( $order, $product_id, $wc_user_id, $current_ns_guest_id, $ns_guest_booking_result, $ns_booking_data, $ns_guest_info, $ns_guest_info_from_booking ) {
    $order_status       = 'not-finished';
    $order_guest_index  = 0;
    $guests_count       = 1;
    $order_bike_gears   = array(
        'primary' => array(),
        'guests'  => array(),
    );

    $order_guests       = array();
    $order_meta_emails  = array();
    $order_meta_primary = array();

    $ns_guest_id        = $ns_guest_info_from_booking->guestId; // Ns guest.

    $guest_bike_info    = tt_get_bike_attributes_by_bike_id( $ns_booking_data->tripId, $ns_booking_data->tripCode, $ns_guest_info->bikeId );

    $guest_bike_gears   = array(
        'rider_level'              => tt_validate( $ns_guest_booking_result->riderType->id ),
        'activity_level'           => tt_validate( $ns_guest_booking_result->activityLevel->id ),
        'bikeTypeId'               => tt_validate( json_decode( $guest_bike_info['bikeModel'], true )['id'] ),
        'bikeId'                   => tt_validate( $ns_guest_info->bikeId ),
        'bike_type_id_preferences' => '',
        'bike_size'                => tt_validate( json_decode( $guest_bike_info['bikeSize'], true )['id'] ),
        'rider_height'             => tt_validate( $ns_guest_info->heightId ),
        'bike_pedal'               => tt_validate( $ns_guest_info->pedalsId ),
        'helmet_size'              => tt_validate( $ns_guest_info->helmetId ),
        'jersey_style'             => tt_get_jersey_style( tt_validate( $ns_guest_info->jerseyId ) ),
        'jersey_size'              => tt_validate( $ns_guest_info->jerseyId ),
    );

    // Order info.
    $trip_product_qty    = isset( $ns_booking_data->guests ) ? count( $ns_booking_data->guests ) : 0;
    $trip_total_amount   = isset( $ns_booking_data->totalAmount ) ? $ns_booking_data->totalAmount : '';
    $trip_product_guests = isset( $ns_booking_data->guests ) ? $ns_booking_data->guests : array();
    $trip_product_price  = 0;
    $trip_s_qty          = 0;
    $trip_s_single_price = 0;

    // Here we have two options. Some of the secondary guest is registered first or the primary guest is registered first.
    if( $ns_guest_info->isPrimary ) {
        // Primary guest registers first.
        $guest_index_id              = 0;
        $order_guest_index           = $guest_index_id;

        $order_bike_gears['primary'] = $guest_bike_gears;

        $order_rf_id                 = '';

        foreach( $ns_booking_data->releaseForms as $guest_release_form ) {
            if( $ns_guest_id == $guest_release_form->guestId ) {
                $order_rf_id = $guest_release_form->releaseFormId;
            }
        }

        // Compatibility solution for the old version of the NS Script 1305.
        if( ! isset( $ns_guest_booking_result->addressInfo->shipping ) || ! isset( $ns_guest_booking_result->addressInfo->billing ) ) {
            $address_full   = explode( ' ', tt_validate( $ns_guest_booking_result->addressInfo->address ), 2 );
            $address_line_1 = $address_full[0];
            $address_line_2 = ! empty( $address_full[1] ) ? $address_full[1] : '';
        }

        $order_meta_primary = array(
            'first_name'  => tt_validate( $ns_guest_booking_result->firstname ),
            'last_name'   => tt_validate( $ns_guest_booking_result->lastname ),
            'email'       => tt_validate( $ns_guest_booking_result->email ),
            'phone'       => tt_validate( $ns_guest_booking_result->phone ),
            'address_info' => array(
                'shipping' => array(
                    'first_name' => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->firstname ) : tt_validate( $ns_guest_booking_result->firstname ),
                    'last_name'  => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->lastname ) : tt_validate( $ns_guest_booking_result->lastname ),
                    'address_1'  => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->address_1 ) : $address_line_1,
                    'address_2'  => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->address_2 ) : $address_line_2,
                    'city'       => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->city ) : tt_validate( $ns_guest_booking_result->addressInfo->city ),
                    'state'      => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->state ) : tt_validate( $ns_guest_booking_result->addressInfo->state ),
                    'postcode'   => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->zip ) : tt_validate( $ns_guest_booking_result->addressInfo->zip ),
                    'country'    => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->country ) : tt_validate( $ns_guest_booking_result->addressInfo->country ),
                ),
                'billing' => array(
                    'first_name' => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->firstname ) : tt_validate( $ns_guest_booking_result->firstname ),
                    'last_name'  => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->lastname ) : tt_validate( $ns_guest_booking_result->lastname ),
                    'address_1'  => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->address_1 ) : $address_line_1,
                    'address_2'  => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->address_2 ) : $address_line_2,
                    'city'       => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->city ) : tt_validate( $ns_guest_booking_result->addressInfo->city ),
                    'state'      => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->state ) : tt_validate( $ns_guest_booking_result->addressInfo->state ),
                    'postcode'   => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->zip ) :  tt_validate( $ns_guest_booking_result->addressInfo->zip ),
                    'country'    => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->country ) : tt_validate( $ns_guest_booking_result->addressInfo->country ),
                )
            ),
            'dob'         => ! empty( tt_validate( $ns_guest_booking_result->birthdate ) ) ? date( 'Y-m-d', strtotime( $ns_guest_booking_result->birthdate ) ) : '',
            'gender'      => tt_validate( $ns_guest_booking_result->gender->id ),
            'order_rf_id' => $order_rf_id,
        );
    } else {
        // Secondary guest registers first.
        $guest_index_id    = 1;
        $order_guest_index = $guest_index_id;
        $guests_count += 1;

        $order_bike_gears['guests'][$guest_index_id] = $guest_bike_gears;

        $order_guests[$guest_index_id] = array(
            'guest_fname'  => tt_validate( $ns_guest_booking_result->firstname ),
            'guest_lname'  => tt_validate( $ns_guest_booking_result->lastname ),
            'guest_email'  => tt_validate( $ns_guest_booking_result->email ),
            'guest_phone'  => tt_validate( $ns_guest_booking_result->phone ),
            'guest_gender' => tt_validate( $ns_guest_booking_result->gender->id ),
            'guest_dob'    => ! empty( tt_validate( $ns_guest_booking_result->birthdate ) ) ? date( 'Y-m-d', strtotime( $ns_guest_booking_result->birthdate ) ) : '',
        );

        array_push( $order_meta_emails, tt_validate( $ns_guest_booking_result->email ) );
    }

    $current_guest_rf_id = '';

    foreach( $ns_booking_data->releaseForms as $guest_release_form ) {
        if( $ns_guest_id == $guest_release_form->guestId ) {
            $current_guest_rf_id = $guest_release_form->releaseFormId;
        }
    }

    $bookings_table_order_info = array(
        'order_id'            => $order->id,
        'guest_index_id'      => $order_guest_index,
        'current_guest_rf_id' => $current_guest_rf_id,
    );

    $insert_booking_data = array(
        'ns_guest_booking_result' => $ns_guest_booking_result,
        'ns_booking_data'         => $ns_booking_data,
        'ns_guest_info'           => $ns_guest_info,
        'guest'                   => $ns_guest_info_from_booking,
        'order_info'              => $bookings_table_order_info,
    );

    // New record insertion. This is the first insertion.
    tt_guest_bookings_table_crud( tt_prepare_bookings_table_data( $insert_booking_data, 'insert' ), [], 'insert' );

    foreach( $ns_booking_data->guests as $guest ) {
        // Skip current guest, because we inserted data for him, and has stored in variables for the order already.
        if( $guest->guestId == $current_ns_guest_id ) {
            continue;
        }

        $_ns_guest_id = $guest->guestId; // Ns guest.

        // Make requests to NS to take the info for every guest.
        $_ns_guest_booking_result = tt_get_ns_guest_info( $_ns_guest_id, 0 );
        $_ns_guest_info           = tt_get_ns_guest_registrations_info( $guest->registrationId, true );

        $_guest_bike_info         = tt_get_bike_attributes_by_bike_id( $ns_booking_data->tripId, $ns_booking_data->tripCode, $_ns_guest_info->bikeId );

        $_guest_bike_gears        = array(
            'rider_level'              => tt_validate( $_ns_guest_booking_result->riderType->id ),
            'activity_level'           => tt_validate( $_ns_guest_booking_result->activityLevel->id ),
            'bikeTypeId'               => tt_validate( json_decode( $_guest_bike_info['bikeModel'], true )['id'] ),
            'bikeId'                   => tt_validate( $_ns_guest_info->bikeId ),
            'bike_type_id_preferences' => '',
            'bike_size'                => tt_validate( json_decode( $_guest_bike_info['bikeSize'], true )['id'] ),
            'rider_height'             => tt_validate( $_ns_guest_info->heightId ),
            'bike_pedal'               => tt_validate( $_ns_guest_info->pedalsId ),
            'helmet_size'              => tt_validate( $_ns_guest_info->helmetId ),
            'jersey_style'             => tt_get_jersey_style( tt_validate( $_ns_guest_info->jerseyId ) ),
            'jersey_size'              => tt_validate( $_ns_guest_info->jerseyId ),
        );

        if( $guest->isPrimary ) {
            // Primary guest.
            $guest_index_id              = 0;
            $order_guest_index           = $guest_index_id;

            $order_bike_gears['primary'] = $_guest_bike_gears;

            $order_rf_id                 = '';

            foreach( $ns_booking_data->releaseForms as $guest_release_form ) {
                if( $_ns_guest_id == $guest_release_form->guestId ) {
                    $order_rf_id = $guest_release_form->releaseFormId;
                }
            }

            // Compatibility solution for the old version of the NS Script 1305.
            if( ! isset( $ns_guest_booking_result->addressInfo->shipping ) || ! isset( $ns_guest_booking_result->addressInfo->billing ) ) {
                $address_full   = explode( ' ', tt_validate( $ns_guest_booking_result->addressInfo->address ), 2 );
                $address_line_1 = $address_full[0];
                $address_line_2 = ! empty( $address_full[1] ) ? $address_full[1] : '';
            }

            $order_meta_primary = array(
                'first_name'  => tt_validate( $_ns_guest_booking_result->firstname ),
                'last_name'   => tt_validate( $_ns_guest_booking_result->lastname ),
                'email'       => tt_validate( $_ns_guest_booking_result->email ),
                'phone'       => tt_validate( $_ns_guest_booking_result->phone ),
                'address_info' => array(
                    'shipping' => array(
                        'first_name' => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->firstname ) : tt_validate( $ns_guest_booking_result->firstname ),
                        'last_name'  => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->lastname ) : tt_validate( $ns_guest_booking_result->lastname ),
                        'address_1'  => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->address_1 ) : tt_validate( $address_line_1 ),
                        'address_2'  => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->address_2 ) : tt_validate( $address_line_2 ),
                        'city'       => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->city ) : tt_validate( $ns_guest_booking_result->addressInfo->city ),
                        'state'      => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->state ) : tt_validate( $ns_guest_booking_result->addressInfo->state ),
                        'postcode'   => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->zip ) : tt_validate( $ns_guest_booking_result->addressInfo->zip ),
                        'country'    => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->country ) : tt_validate( $ns_guest_booking_result->addressInfo->country ),
                    ),
                    'billing' => array(
                        'first_name' => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->firstname ) : tt_validate( $ns_guest_booking_result->firstname ),
                        'last_name'  => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->lastname ) : tt_validate( $ns_guest_booking_result->lastname ),
                        'address_1'  => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->address_1 ) : tt_validate( $address_line_1 ),
                        'address_2'  => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->address_2 ) : tt_validate( $address_line_2 ),
                        'city'       => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->city ) : tt_validate( $ns_guest_booking_result->addressInfo->city ),
                        'state'      => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->state ) : tt_validate( $ns_guest_booking_result->addressInfo->state ),
                        'postcode'   => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->zip ) :  tt_validate( $ns_guest_booking_result->addressInfo->zip ),
                        'country'    => isset( $ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $ns_guest_booking_result->addressInfo->billing->country ) : tt_validate( $ns_guest_booking_result->addressInfo->country ),
                    )
                ),
                'dob'         => ! empty( tt_validate( $_ns_guest_booking_result->birthdate ) ) ? date( 'Y-m-d', strtotime( $_ns_guest_booking_result->birthdate ) ) : '',
                'gender'      => tt_validate( $_ns_guest_booking_result->gender->id ),
                'order_rf_id' => $order_rf_id,
            );
        } else {
            // Secondary guest.
            $guest_index_id = $guests_count; // ++
            $order_guest_index = $guest_index_id;
            $guests_count += 1;

            $order_bike_gears['guests'][$guest_index_id] = $_guest_bike_gears;

            $order_guests[$guest_index_id] = array(
                'guest_fname'  => tt_validate( $_ns_guest_booking_result->firstname ),
                'guest_lname'  => tt_validate( $_ns_guest_booking_result->lastname ),
                'guest_email'  => tt_validate( $_ns_guest_booking_result->email ),
                'guest_phone'  => tt_validate( $_ns_guest_booking_result->phone ),
                'guest_gender' => tt_validate( $_ns_guest_booking_result->gender->id ),
                'guest_dob'    => ! empty( tt_validate( $_ns_guest_booking_result->birthdate ) ) ? date( 'Y-m-d', strtotime( $_ns_guest_booking_result->birthdate ) ) : '',
            );

            array_push( $order_meta_emails, tt_validate( $_ns_guest_booking_result->email ) );
        }

        $current_guest_rf_id = '';

        foreach( $ns_booking_data->releaseForms as $guest_release_form ) {
            if( $_ns_guest_id == $guest_release_form->guestId ) {
                $current_guest_rf_id = $guest_release_form->releaseFormId;
            }
        }

        $_bookings_table_order_info = array(
            'order_id'            => $order->id,
            'guest_index_id'      => $order_guest_index,
            'current_guest_rf_id' => $current_guest_rf_id,
        );

        $_insert_booking_data = array(
            'ns_guest_booking_result' => $_ns_guest_booking_result,
            'ns_booking_data'         => $ns_booking_data,
            'ns_guest_info'           => $_ns_guest_info,
            'guest'                   => $guest,
            'order_info'              => $_bookings_table_order_info,
        );

        // New record insertion. Every other guest insertion
        tt_guest_bookings_table_crud( tt_prepare_bookings_table_data( $_insert_booking_data, 'insert' ), [], 'insert' );
    }

    // Finalizing the order needs to be done only one time.
    foreach( $trip_product_guests as $guest ) {
        if( $guest->isPrimary ) {
            $trip_pr_guest_ns_user_id = $guest->guestId;
            $trip_product_price       = $guest->basePrice;
        }
        
        if( 0 != $guest->singleSupplement ) {
            $trip_s_qty++;
            $trip_s_single_price = $guest->singleSupplement;
        }
    }

    $bike_upgrade_price      = get_post_meta( $product_id, TT_WC_META_PREFIX . 'bikeUpgradePrice', true );
    $single_supplement_price = get_post_meta( $product_id, TT_WC_META_PREFIX . 'singleSupplementPrice', true );

    $trek_user_checkout_data = array(
        'no_of_guests'          => count( $ns_booking_data->guests ),
        'shipping_first_name'   => $order_meta_primary['address_info']['shipping']['first_name'],
        'shipping_last_name'    => $order_meta_primary['address_info']['shipping']['last_name'],
        'shipping_phone'        => $order_meta_primary['phone'],
        'custentity_birthdate'  => $order_meta_primary['dob'],
        'custentity_gender'     => $order_meta_primary['gender'],
        'shipping_address_1'    => $order_meta_primary['address_info']['shipping']['address_1'],
        'shipping_address_2'    => $order_meta_primary['address_info']['shipping']['address_2'],
        'shipping_country'      => $order_meta_primary['address_info']['shipping']['country'],
        'shipping_state'        => $order_meta_primary['address_info']['shipping']['state'],
        'shipping_city'         => $order_meta_primary['address_info']['shipping']['city'],
        'shipping_postcode'     => $order_meta_primary['address_info']['shipping']['postcode'],
        'email'                 => $order_meta_primary['email'],
        'guests'                => $order_guests,
        'bike_gears'            => $order_bike_gears,
        'tt_waiver'             => '1',
        'parent_product_id'     => tt_get_parent_trip_id_by_child_sku( $ns_booking_data->tripCode, false ),
        'product_id'            => $product_id,
        'sku'                   => $ns_booking_data->tripCode,
        'bikeUpgradePrice'      => $bike_upgrade_price,
        'singleSupplementPrice' => $single_supplement_price,
    );

    $trip_product = wc_get_product( $product_id );
    if( ! empty( $trip_product_price ) ) {
        $trip_product->set_price( $trip_product_price );
    }
    $product_item_id = $order->add_product( $trip_product, $trip_product_qty );
    wc_add_order_item_meta( $product_item_id, 'trek_user_checkout_data', $trek_user_checkout_data );
    wc_add_order_item_meta( $product_item_id, 'trek_user_checkout_product_id', $product_id );

    // TODO: Travel protection, Bike Upgrade
    // *** Single Supplement Fees ***
    if( ! empty( $trip_s_qty ) ) {
        $s_product_id = tt_create_line_item_product( 'TTWP23SUPP' );
        $s_product    = wc_get_product( $s_product_id );
        if( ! empty( $trip_s_single_price ) ) {
            $s_product->set_price( $trip_s_single_price );
        }
        $s_product_item_id = $order->add_product( $s_product, $trip_s_qty );
    }

    // Add billing and shipping addresses.
    $billing_address = array(
        'first_name' => $order_meta_primary['address_info']['billing']['first_name'],
        'last_name'  => $order_meta_primary['address_info']['billing']['last_name'],
        'email'      => $order_meta_primary['email'],
        'phone'      => $order_meta_primary['phone'],
        'address_1'  => $order_meta_primary['address_info']['billing']['address_1'],
        'address_2'  => $order_meta_primary['address_info']['billing']['address_2'],
        'city'       => $order_meta_primary['address_info']['billing']['city'],
        'state'      => $order_meta_primary['address_info']['billing']['state'],
        'postcode'   => $order_meta_primary['address_info']['billing']['postcode'],
        'country'    => $order_meta_primary['address_info']['billing']['country'],
    );

    $order->set_address( $billing_address, 'billing' );

    $shipping_address = array(
        'first_name' => $order_meta_primary['address_info']['shipping']['first_name'],
        'last_name'  => $order_meta_primary['address_info']['shipping']['last_name'],
        'email'      => $order_meta_primary['email'],
        'phone'      => $order_meta_primary['phone'],
        'address_1'  => $order_meta_primary['address_info']['shipping']['address_1'],
        'address_2'  => $order_meta_primary['address_info']['shipping']['address_2'],
        'city'       => $order_meta_primary['address_info']['shipping']['city'],
        'state'      => $order_meta_primary['address_info']['shipping']['state'],
        'postcode'   => $order_meta_primary['address_info']['shipping']['postcode'],
        'country'    => $order_meta_primary['address_info']['shipping']['country'],
    );

    $order->set_address( $shipping_address, 'shipping' );

    // Take primary guest WP ID.
    $wp_user    = get_user_by( 'email', $order_meta_primary['email'] );
    $wp_user_id = $wp_user->ID;

    // Finish the status if primary guest has registration.
    if( ! empty( $wp_user_id ) ) {
        $wc_user_id = $wp_user_id;
        $order_status = 'finished';
    }

    // Assign the first registered guest as a customer of the order if the primary guest is not registered yet.
    if( ! empty( $wc_user_id ) ) {
        $order->set_customer_id( $wc_user_id );
    }

    if( ! empty( $ns_booking_data->bookingDate ) ) {
        $date_time = date( strtotime( $ns_booking_data->bookingDate ) );
        $order->set_date_created( $date_time );
    }

    $order->calculate_totals();
    $order->save();

    // Update the order meta.
    update_post_meta( $order->id, 'trek_user_checkout_data', $trek_user_checkout_data );
    update_post_meta( $order->id, 'trek_user_checkout_product_id', $product_id );
    update_post_meta( $order->id, 'tt_meta_releaseFormId', $order_meta_primary['order_rf_id'] );
    update_post_meta( $order->id, 'tt_wc_order_ns_status', true );
    update_post_meta( $order->id, 'tt_wc_order_trip_user_emails', $order_meta_emails );
    update_post_meta( $order->id, 'tt_meta_total_amount', $trip_total_amount );
    update_post_meta( $order->id, 'tt_wc_order_finished_status', $order_status );

    // Restore product qty.
    wc_update_product_stock( $product_id, $trip_product_qty, 'increase' );

    tt_add_error_log( 'NS - Finalize migrated order - END.', array( 'booking_id' => $ns_booking_data->bookingId, 'order_id' => $order->id, 'customer_id' => $wc_user_id, 'ns_user_id' => $current_ns_guest_id, 'is_primary' => $ns_guest_info->isPrimary ), array( 'status' => 'true', 'message' => 'End migrated order sync.' ) );
}

/**
 * Prepare the data to insert or update the `guest_bookings` table.
 *
 * @param array $all_data Array with NS Response objects. And order_info is an array with additional data when needs inserting.
 *
 * @return array Array with prepared to insert or update booking data.
 */
function tt_prepare_bookings_table_data( $all_data, $operation_type = 'update' ) {
    $booking_table_data      = array();

    
    $ns_guest_booking_result = $all_data['ns_guest_booking_result']; // Object.
    $ns_booking_data         = $all_data['ns_booking_data']; // Object.
    $ns_guest_info           = $all_data['ns_guest_info']; // Object.
    $guest                   = $all_data['guest']; // Object.
    $order_info              = isset( $all_data['order_info'] ) ? $all_data['order_info'] : array(); // Array.
    
    $ns_guest_id             = $guest->guestId; // Ns guest.
    $ns_guest_email          = tt_validate( $ns_guest_booking_result->email );
    $wp_user                 = get_user_by( 'email', $ns_guest_email );
    $wp_user_id              = $wp_user->ID;
    
    $guest_bike_info         = tt_get_bike_attributes_by_bike_id( $ns_booking_data->tripId, $ns_booking_data->tripCode, $ns_guest_info->bikeId );
    
    $is_ride_camp            = tt_check_is_ride_camp_trip_from_dates( $ns_booking_data->tripStartDate, $ns_booking_data->tripEndDate, $ns_booking_data->wholeTripStartDate, $ns_booking_data->wholeTripEndDate );

    if( $is_ride_camp ) {

        $ride_camp_product_info = tt_take_ride_camp_product_info( $ns_booking_data->tripStartDate, $ns_booking_data->tripEndDate, $ns_booking_data->wholeTripStartDate, $ns_booking_data->wholeTripEndDate, $ns_booking_data->tripCode );

        $product_id             = tt_validate( $ride_camp_product_info['product_id'] );
        $trip_code              = tt_validate( $ride_camp_product_info['trip_code'] );
        $trip_name              = tt_validate( $ride_camp_product_info['trip_name'] );
    } else {

        $trip_code              = tt_validate( $ns_booking_data->tripCode );
        $product_id             = tt_get_product_by_sku( $trip_code, true );
        $trip_name              = tt_validate( $ns_booking_data->tripName );
    }

    // Two types preparing, for insert or an update.
    if( 'insert' === $operation_type ) {
        // Additional data that is needed to insert a row into the table.

        // Collect the insertion booking data.
        $booking_table_data['order_id']                 = $order_info['order_id'];
        $booking_table_data['wantsInsurance']           = tt_validate( $guest->tripInsurancePurchased );
        $booking_table_data['releaseFormId']            = $order_info['current_guest_rf_id'];
        $booking_table_data['guest_index_id']           = $order_info['guest_index_id'];
        $booking_table_data['guest_phone_number']       = tt_validate( $ns_guest_booking_result->phone );
        $booking_table_data['guest_gender']             = tt_validate( $ns_guest_booking_result->gender->id );
        $booking_table_data['guest_date_of_birth']      = ! empty( tt_validate( $ns_guest_booking_result->birthdate ) ) ? date( 'Y-m-d', strtotime( $ns_guest_booking_result->birthdate ) ) : '';

        // Shipping Address.
        if( isset( $ns_guest_booking_result->addressInfo->shipping ) ) {
            $booking_table_data['shipping_address_1']       = tt_validate( $ns_guest_booking_result->addressInfo->shipping->address_1 );
            $booking_table_data['shipping_address_2']       = tt_validate( $ns_guest_booking_result->addressInfo->shipping->address_2 );
            $booking_table_data['shipping_address_city']    = tt_validate( $ns_guest_booking_result->addressInfo->shipping->city );
            $booking_table_data['shipping_address_state']   = tt_validate( $ns_guest_booking_result->addressInfo->shipping->state );
            $booking_table_data['shipping_address_country'] = tt_validate( $ns_guest_booking_result->addressInfo->shipping->country );
            $booking_table_data['shipping_address_zipcode'] = tt_validate( $ns_guest_booking_result->addressInfo->shipping->zip );
        } else {
            // Compatibility solution for the old version of the NS Script 1305.
            $address_full                                   = explode( ' ', tt_validate( $ns_guest_booking_result->addressInfo->address ), 2 );
            $address_line_1                                 = $address_full[0];
            $address_line_2                                 = ! empty( $address_full[1] ) ? $address_full[1] : '';

            $booking_table_data['shipping_address_1']       = $address_line_1;
            $booking_table_data['shipping_address_2']       = $address_line_2;
            $booking_table_data['shipping_address_city']    = tt_validate( $ns_guest_booking_result->addressInfo->city );
            $booking_table_data['shipping_address_state']   = tt_validate( $ns_guest_booking_result->addressInfo->state );
            $booking_table_data['shipping_address_country'] = tt_validate( $ns_guest_booking_result->addressInfo->country );
            $booking_table_data['shipping_address_zipcode'] = tt_validate( $ns_guest_booking_result->addressInfo->zip );
        }

        // Set rider level from Guest. TODO: add check based on bike ID, because Guest data may change for any booking if there more than one.
        $booking_table_data['rider_level']              = tt_validate( $ns_guest_booking_result->riderType->id );
        // Set activity level from Guest.
        $booking_table_data['activity_level']           = tt_validate( $ns_guest_booking_result->activityLevel->id );
    }

    // Bike info from bike ID.
    if( !empty( $guest_bike_info ) ) {
        $booking_table_data['bike_type_id'] = tt_validate( json_decode( $guest_bike_info['bikeModel'], true )['id'] );
        $booking_table_data['bike_size']    = tt_validate( json_decode( $guest_bike_info['bikeSize'], true )['id'] );
    }

    if( ! empty( $product_id ) ) {
        $booking_table_data['product_id'] = $product_id;
    }

    if ( ! empty( $wp_user_id ) ) {
        $booking_table_data['user_id'] = $wp_user_id;
    }

    $booking_table_data['guest_email_address']                 = tt_validate( $ns_guest_booking_result->email );
    $booking_table_data['guest_first_name']                    = tt_validate( $ns_guest_booking_result->firstname );
    $booking_table_data['guest_last_name']                     = tt_validate( $ns_guest_booking_result->lastname );
    $booking_table_data['netsuite_guest_registration_id']      = tt_validate( $guest->guestId );
    $booking_table_data['guestRegistrationId']                 = tt_validate( $guest->registrationId );
    $booking_table_data['ns_trip_booking_id']                  = tt_validate( $ns_booking_data->bookingId );
    $booking_table_data['trip_code']                           = $trip_code;
    $booking_table_data['trip_name']                           = $trip_name;
    $booking_table_data['trip_total_amount']                   = tt_validate( $ns_booking_data->totalAmount );
    $booking_table_data['trip_number_of_guests']               = ! empty( tt_validate( $ns_booking_data->guests ) ) ? count( $ns_booking_data->guests ) : 0;
    $booking_table_data['trip_start_date']                     = ! empty( tt_validate( $ns_booking_data->tripStartDate ) ) ? strtotime( $ns_booking_data->tripStartDate ) : '';
    $booking_table_data['trip_end_date']                       = ! empty( tt_validate( $ns_booking_data->tripEndDate ) ) ? strtotime( $ns_booking_data->tripEndDate ) : '';
    $booking_table_data['guest_is_primary']                    = tt_validate( $ns_guest_info->isPrimary ) == 1 ? 1 : 0;
    $booking_table_data['bike_selection']                      = tt_validate( $ns_guest_info->bikeId );
    $booking_table_data['bike_id']                             = tt_validate( $ns_guest_info->bikeId );
    $booking_table_data['rider_height']                        = tt_validate( $ns_guest_info->heightId );
    $booking_table_data['helmet_selection']                    = tt_validate( $ns_guest_info->helmetId );
    $booking_table_data['pedal_selection']                     = tt_validate( $ns_guest_info->pedalsId );
    $booking_table_data['saddle_height']                       = tt_validate( $ns_guest_info->saddleHeight );
    $booking_table_data['saddle_bar_reach_from_saddle']        = tt_validate( $ns_guest_info->barReachFromSaddle );
    $booking_table_data['saddle_bar_height_from_wheel_center'] = tt_validate( $ns_guest_info->barHeightFromWheelCenter );
    $booking_table_data['jersey_style']                        = tt_get_jersey_style( tt_validate( $ns_guest_info->jerseyId ) );
    $booking_table_data['tt_jersey_size']                      = tt_validate( $ns_guest_info->jerseyId );
    $booking_table_data['tshirt_size']                         = tt_validate( $ns_guest_info->tshirtSizeId );
    $booking_table_data['race_fit_jersey_size']                = tt_validate( $ns_guest_info->raceFitJerseyId );
    $booking_table_data['shorts_bib_size']                     = tt_validate( $ns_guest_info->shortsBibSizeId );
    $booking_table_data['emergency_contact_first_name']        = tt_validate( $ns_guest_info->ecFirstName );
    $booking_table_data['emergency_contact_last_name']         = tt_validate( $ns_guest_info->ecLastName );
    $booking_table_data['emergency_contact_phone']             = tt_validate( $ns_guest_info->ecPhone );
    $booking_table_data['emergency_contact_relationship']      = tt_validate( $ns_guest_info->ecRelationship );
    $booking_table_data['medical_conditions']                  = tt_validate( $ns_guest_info->medicalConditions );
    $booking_table_data['medications']                         = tt_validate( $ns_guest_info->medications );
    $booking_table_data['allergies']                           = tt_validate( $ns_guest_info->allergies );
    $booking_table_data['dietary_restrictions']                = tt_validate( $ns_guest_info->dietaryPreferences );
    $booking_table_data['waiver_signed']                       = tt_validate( $ns_guest_info->waiverAccepted ) == 1 ? 1 : 0;
    $booking_table_data['is_guestreg_cancelled']               = tt_validate( $ns_guest_info->isCancelled ) == 1 ? 1 : 0;

    return $booking_table_data;
}

/**
 * CRUD opeartions for `guest_bookings` table.
 *
 * @param array  $booking_table_data The data to insert or update in the bookings table.
 * @param string $operation_type The type of the operation. Allowed types 'update' and 'insert'.
 * @param array  $where The where arguments for an update operation.
 *
 * @return void
 */
function tt_guest_bookings_table_crud( $booking_table_data = array(), $where = array(), $operation_type = 'update' ) {

    if( empty( $booking_table_data ) || empty( $operation_type ) ) {
        // No data provided or missing type of operation.
        return;
    }

    if( $operation_type === 'update' && empty( $where ) ) {
        // Missing required for an update operation, where parameter.
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_bookings';

    switch ( $operation_type ) {

        case 'insert':
            // Insert booking.
            $insert_booking = $wpdb->insert( $table_name, $booking_table_data );
            tt_add_error_log( '4) NS<>WC - SQL [Insert]', array( 'last_error' => $wpdb->last_error, 'last_query' => $wpdb->last_query ), array( 'insert_booking' => $insert_booking, 'booking_table_data' => $booking_table_data ) );
            break;

        case 'update':
            // Update existing booking.
            $wpdb->update( $table_name, $booking_table_data, $where );
            tt_add_error_log( '5) NS<>WC - SQL [Update]', array( 'last_error' => $wpdb->last_error, 'last_query' => $wpdb->last_query ), $booking_table_data );
            break;

        default:
            // code...
            break;
    }
}

/**
 * Update WP user meta for guest preferences from NetSuite.
 *
 * @param int    $user_id WP User ID / Customer ID.
 * @param object $guest_data NS Response from USER_BOOKINGS_SCRIPT_ID with guest data object.
 *
 * @return void
 */
function tt_sync_guest_preferences( $user_id = 0, $guest_data = [] ) {
    if( empty( $user_id ) || empty( $guest_data ) ) {
        return;
    }

    // Take NS Guest Preferences.
    $ns_guest_preferences = array(
        // Collect Personal Information.
        'first_name'                              => tt_validate( $guest_data->firstname ),
        'last_name'                               => tt_validate( $guest_data->lastname ),
        'billing_phone'                           => tt_validate( $guest_data->phone ),
        'custentity_phone_number'                 => tt_validate( $guest_data->phone ),
        'custentity_birthdate'                    => ! empty( tt_validate( $guest_data->birthdate ) ) ? date( 'm/d/Y', strtotime( $guest_data->birthdate ) ) : '', // Convert date from format Y-m-d ( NS Format ) to format m/d/Y ( Meta Value Format ).
        'custentity_gender'                       => tt_validate( $guest_data->gender->id ),

        // Collect Medical Information.
        'custentity_medications'                  => tt_validate( $guest_data->medications ),
        'custentity_allergies'                    => tt_validate( $guest_data->allergies ),
        'custentity_medicalconditions'            => tt_validate( $guest_data->medicalconditions ),
        'custentity_dietaryrestrictions'          => tt_validate( $guest_data->dietaryrestrictions ),

        // Collect Emergency Contact.
        'custentity_emergencycontactfirstname'    => tt_validate( $guest_data->emergencyContactPrimFirstName ),
        'custentityemergencycontactlastname'      => tt_validate( $guest_data->emergencyContactPrimLastName ),
        'custentity_emergencycontactphonenumber'  => tt_validate( $guest_data->emergencyContactPrimPhone ),
        'custentity_emergencycontactrelationship' => tt_validate( $guest_data->emergencyContactPrimRelationship ),

        // Collect Communication Preferences.
        'custentity_contactmethod'                => tt_validate( $guest_data->preferredContactMethod->id ),

        // These two fields below are not clear for what are they.
        // 'custentity_addtotrektravelmailinglist' => isset( $guest_data->addToMailList->id ) ? $guest_data->addToMailList->id : '', // Example response - "addToEmailList": { "id": "2", "name": "Soft Opt-Out" } or "addToEmailList": { "id": "1", "name": "Soft Opt-In" }
        // 'globalsubscriptionstatus'              => isset( $guest_data->addToEmailList->id ) ? $guest_data->addToEmailList->id : '', // Example response - "addToMailList": { "id": "1", "name": "Yes" },

        // Collect Bike & Gear Preferences
        // TODO: Bike type field mapping. One of the listed bellow.
        // 'gear_preferences_bike'       => isset( $guest_data->bikeSelect ) ? $guest_data->bikeSelect : '', // this is a bike id in user meta.
        // 'gear_preferences_bike_size'  => isset( $guest_data->bikeSelect ) ? $guest_data->bikeSelect : '', // this is a bike size in user meta.
        // 'gear_preferences_bike_type'  => isset( $guest_data->bikeSelect ) ? $guest_data->bikeSelect : '', // this is a bike type id in user meta.
        'gear_preferences_rider_height'           => tt_validate( $guest_data->height->id ),
        'gear_preferences_select_pedals'          => tt_validate( $guest_data->pedalSelect->id ),
        'gear_preferences_helmet_size'            => tt_validate( $guest_data->helmetSize->id ),

        // These fields below not comming from 1305 NS Script.
        // 'gear_preferences_jersey_size' => '',
        // 'gear_preferences_jersey_style' => '',

        // Collect Gear Optional Preferences.
        'gear_preferences_saddle_height'          => tt_validate( $guest_data->saddleHeight ),
        'gear_preferences_bar_height'             => tt_validate( $guest_data->barReachFromSaddle ),
        'gear_preferences_bar_reach'              => tt_validate( $guest_data->barHeightFromWheel ),

        // Collect Passport Preferences.
        'custentity_passport_number'              => tt_validate( $guest_data->passportNumber ),
        'custentity_passport_exp_date'            => ! empty( tt_validate( $guest_data->passportExpirationDate ) ) ? date( 'm/d/Y', strtotime( $guest_data->passportExpirationDate ) ) : '', // Convert date from format Y-m-d ( NS Format ) to format m/d/Y ( Meta Value Format ).
    );

    // Collect Billing Address Preferences.
    if( isset( $guest_data->addressInfo->billing ) ) {
        $ns_guest_preferences['billing_first_name'] = tt_validate( $guest_data->addressInfo->billing->firstname );
        $ns_guest_preferences['billing_last_name']  = tt_validate( $guest_data->addressInfo->billing->lastname );
        $ns_guest_preferences['billing_address_1']  = tt_validate( $guest_data->addressInfo->billing->address_1 );
        $ns_guest_preferences['billing_address_2']  = tt_validate( $guest_data->addressInfo->billing->address_2 );
        $ns_guest_preferences['billing_city']       = tt_validate( $guest_data->addressInfo->billing->city );
        $ns_guest_preferences['billing_state']      = tt_validate( $guest_data->addressInfo->billing->state );
        $ns_guest_preferences['billing_postcode']   = tt_validate( $guest_data->addressInfo->billing->zip );
        $ns_guest_preferences['billing_country']    = tt_validate( $guest_data->addressInfo->billing->country );
    } else {
        // Compatibility solution for the old version of the NS Script 1305.
        $address_full   = explode( ' ', tt_validate( $guest_data->addressInfo->address ), 2 );
        $address_line_1 = $address_full[0];
        $address_line_2 = ! empty( $address_full[1] ) ? $address_full[1] : '';

        // About address from NS billing_address_1 ['World Way'] and billing_address_2 ['1'] comming as one field $guest_data->addressInfo->address ['World Way 1'].
        $ns_guest_preferences['billing_first_name'] = tt_validate( $guest_data->firstname );
        $ns_guest_preferences['billing_last_name']  = tt_validate( $guest_data->lastname );
        $ns_guest_preferences['billing_address_1']  = $address_line_1;
        $ns_guest_preferences['billing_address_2']  = $address_line_2;
        $ns_guest_preferences['billing_city']       = tt_validate( $guest_data->addressInfo->city );
        $ns_guest_preferences['billing_state']      = tt_validate( $guest_data->addressInfo->state );
        $ns_guest_preferences['billing_postcode']   = tt_validate( $guest_data->addressInfo->zip );
        $ns_guest_preferences['billing_country']    = tt_validate( $guest_data->addressInfo->country );
    }

    // Collect Shipping Address Preferences.
    if( isset( $guest_data->addressInfo->shipping ) ) {
        $ns_guest_preferences['shipping_first_name'] = tt_validate( $guest_data->addressInfo->shipping->firstname );
        $ns_guest_preferences['shipping_last_name']  = tt_validate( $guest_data->addressInfo->shipping->lastname );
        $ns_guest_preferences['shipping_address_1']  = tt_validate( $guest_data->addressInfo->shipping->address_1 );
        $ns_guest_preferences['shipping_address_2']  = tt_validate( $guest_data->addressInfo->shipping->address_2 );
        $ns_guest_preferences['shipping_city']       = tt_validate( $guest_data->addressInfo->shipping->city );
        $ns_guest_preferences['shipping_state']      = tt_validate( $guest_data->addressInfo->shipping->state );
        $ns_guest_preferences['shipping_postcode']   = tt_validate( $guest_data->addressInfo->shipping->zip );
        $ns_guest_preferences['shipping_country']    = tt_validate( $guest_data->addressInfo->shipping->country );
    } else {
        // Compatibility solution for the old version of the NS Script 1305.
        $address_full   = explode( ' ', tt_validate( $guest_data->addressInfo->address ), 2 );
        $address_line_1 = $address_full[0];
        $address_line_2 = ! empty( $address_full[1] ) ? $address_full[1] : '';

        // About address from NS shipping_address_1 ['World Way'] and shipping_address_2 ['1'] comming as one field $guest_data->addressInfo->address ['World Way 1'].
        $ns_guest_preferences['shipping_first_name'] = tt_validate( $guest_data->firstname );
        $ns_guest_preferences['shipping_last_name']  = tt_validate( $guest_data->lastname );
        $ns_guest_preferences['shipping_address_1']  = $address_line_1;
        $ns_guest_preferences['shipping_address_2']  = $address_line_2;
        $ns_guest_preferences['shipping_city']       = tt_validate( $guest_data->addressInfo->city );
        $ns_guest_preferences['shipping_state']      = tt_validate( $guest_data->addressInfo->state );
        $ns_guest_preferences['shipping_postcode']   = tt_validate( $guest_data->addressInfo->zip );
        $ns_guest_preferences['shipping_country']    = tt_validate( $guest_data->addressInfo->country );
    }

    foreach( $ns_guest_preferences as $key => $value ) {
        // Update user meta for this key.
        update_user_meta( $user_id, $key, $value );
    }

    tt_add_error_log( 'NS<>WC - Sync guest preferences', array( 'customer_id' => $user_id, 'ns_guest_preferences' => $ns_guest_preferences ), array( 'status' => true, 'message' => 'Update the user metadata with info for the guest preferences from NS.' ) );
}

/**
 * Read file with name `bookings-export.csv` uploaded in `wp-content/uploads/bookings/`
 * and return array with bookings ids.
 *
 * Booking ID need to be numeric and in to first column of the file.
 *
 * @return array
 */
function tt_get_bookings_ids_from_file() {

    if ( class_exists( 'Psr\Log\AbstractLogger' ) ) {
        $upload_dir         = wp_upload_dir();
        $base_dir           = $upload_dir['basedir'] . '/bookings/';
        $json_bookings_path = $base_dir . 'bookings-export.csv';

        $bookings_ids_arr   = array();

        if ( ( $handle = fopen( $json_bookings_path, "r" ) ) !== FALSE ) {
            while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
				if( is_numeric( $data[0] ) ) {
					$bookings_ids_arr[] = $data[0];
				}
            }

            fclose( $handle );
        }
    }

    return $bookings_ids_arr;
}

/**
 * Return bookings ids in array from uploaded file trough input field.
 *
 * @param string $file_name The name of the file
 * @return array
 */
function tt_get_bookings_ids_from_upload_file( $file_name ) {
    $bookings_ids_arr = array();

    if ( ( $handle = fopen( $file_name, "r" ) ) !== FALSE ) {
        while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
            if( is_numeric( $data[0] ) ) {
                $bookings_ids_arr[] = $data[0];
            }
        }

        fclose( $handle );
    }

    return $bookings_ids_arr;
}

/**
 * Return NS User ids in array from uploaded file trough input field.
 *
 * Note: we rely on that in the 5 column are the NS user IDs
 *
 * @param string $file_name The name of the file
 * @return array
 */
function tt_get_ns_user_ids_from_upload_file( $file_name ) {
    $ns_user_ids_arr = array();

    if ( ( $handle = fopen( $file_name, "r" ) ) !== FALSE ) {
        while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
            if( is_numeric( $data[4] ) ) {
                $ns_user_ids_arr[] = $data[4];
            }
        }

        fclose( $handle );
    }

    return $ns_user_ids_arr;
}

/**
 * Create multiple placeholder orders by given array with booking ids.
 *
 * @param array $bookings_ids Array with booking ids to create migrated orders.
 */
function tt_create_multiple_orders( $bookings_ids ) {
    if( empty( $bookings_ids ) ) {
        return;
    }

    foreach( $bookings_ids as $booking_id ) {
        tt_create_order( $booking_id );
    }
}

/**
 * Sync multiple ns user bookings with the guest_bookings table,
 * and create auto-generated orders for that bookings.
 *
 * @param $ns_user_ids Array with the NS User IDs
 *
 * @return bool True on success or false on empty args.
 */
function tt_sync_multiple_guest_bookings_details( $ns_user_ids ) {
    if( empty( $ns_user_ids ) ) {
        return false;
    }

    foreach( $ns_user_ids as $ns_user_id ) {
        // This will take time... Loop through all ns_user_ids, check bookings, take data from NS, create orders, and fill the guest_bookings table.
        tt_ns_guest_booking_details( true, $ns_user_id, '', DEFAULT_TIME_RANGE, true );
    }

    return true;
}

/**
 * Function for the WP CLI command `migrate-bookings`.
 * 
 * @param array $args Array with the args provided from the command line.
 * 
 * @return void
 */
function tt_migrate_bookings_wp_cli( $args ) {
    $filename        = $args[0];
    $ns_user_ids_arr = array();

    if( file_exists( $filename ) ) {
        WP_CLI::success( 'File exist ' . $filename );
        $ns_user_ids_arr = tt_get_ns_user_ids_from_upload_file( $filename );
        
        // Confirm before proceed.
        if( ! empty( $ns_user_ids_arr ) ){
            
            WP_CLI::success( 'Found ' . count( $ns_user_ids_arr ) . ' NS User IDs. Here they are:' . json_encode( $ns_user_ids_arr ) );

            WP_CLI::confirm( __( 'Do you want to proceed?', 'trek-travel' ) );
        } else {
            WP_CLI::error( 'NS User IDs Not Found!' );
        }
    } else {
        WP_CLI::error( "File doesn't exist!" );
    }

    if( ! empty( $ns_user_ids_arr ) ) {

        $import_status = tt_sync_multiple_guest_bookings_details( $ns_user_ids_arr );

        if( 'true' == $import_status ) {
            WP_CLI::success( 'The Orders were created and Bookings were imported for NS User IDs in the file!' );
        } else {
            WP_CLI::error( 'Something failed during the bookings import!' );
        }
    }

}

if ( class_exists( 'WP_CLI' ) ) {
    WP_CLI::add_command( 'migrate-bookings', 'tt_migrate_bookings_wp_cli' );
}

/**
 * Check if the trip is with nested dates or not by given date periods.
 *
 * @param string $start_date      The booked start date in format MM/dd/YYYY.
 * @param string $end_date        The booked end date in format MM/dd/YYYY.
 * @param string $main_start_date The whole trip start date in format MM/dd/YYYY.
 * @param string $main_end_date   The whole trip end date in format MM/dd/YYYY.
 * 
 * @return bool
 */
function tt_check_is_ride_camp_trip_from_dates( $start_date, $end_date, $main_start_date, $main_end_date ) {

    if( $start_date === $main_start_date && $end_date === $main_end_date ) {
        // This is the main trip product for full 7-day period.
        return false;
    }

    // We have a Ride Camp trip for nested days, 4-day period.
    return true;
}

/**
 * Collect and return the Ride Camp/Nested Dates product info
 * by given dates and main product trip code/sku.
 *
 * @param string $start_date      The booked start date in format MM/dd/YYYY.
 * @param string $end_date        The booked end date in format MM/dd/YYYY.
 * @param string $main_start_date The whole trip start date in format MM/dd/YYYY.
 * @param string $main_end_date   The whole trip end date in format MM/dd/YYYY.
 *
 * @param string $main_trip_code  The trip code / SKU of the main product.
 * @param bool   $product_id_only Whether to return the product ID only.
 * 
 * @return array|bool Array with the basic product info or false if product info not found.
 */
function tt_take_ride_camp_product_info( $start_date, $end_date, $main_start_date, $main_end_date, $main_trip_code, $product_id_only = false ) {
    $ride_camp_product_sku = '';

    if( $start_date === $main_start_date && $end_date !== $main_end_date ) {
        // This is the FIRST child product, add a suffix to the SKU.
        $ride_camp_product_sku = $main_trip_code . '-FIRST';

    } elseif ( $start_date !== $main_start_date && $end_date === $main_end_date ) {
        // This is the SECOND child product, add a suffix to the SKU.
        $ride_camp_product_sku = $main_trip_code . '-SECOND';
    }

    $product_info = tt_get_product_by_sku( $ride_camp_product_sku, false );

    if( ! empty( $product_info ) ) {

        if( $product_id_only ) {
            // Return the product ID only.

            return tt_validate( $product_info->id );
        }

        // Collect and return Ride Camp product info.
        $ride_camp_product_info = array(
            'product_id' => tt_validate( $product_info->id ),
            'trip_code'  => $ride_camp_product_sku,
            'trip_name'  => tt_validate( $product_info->get_name() ),

        );

        return $ride_camp_product_info;
    }

    return false;
}

/**
 * Set Lock Trip Checklist and Lock Bike Selection statuses,
 * based on NS Guest Registrations corresponded fields.
 *
 * @param string     $guest_email             User email.
 * @param string|int $guest_reg_id            The ID of the Guest registration in NS.
 * @param object     $ns_registration_details Guest registration details.
 *
 * @uses tt_get_ns_guest_registrations_info() To obtain the info from NetSuite for specific Guest Registration.
 *
 * @return bool Whether the process finished successfully.
 */
function tt_set_bike_record_lock_status( $guest_email = '', $guest_reg_id = 0, $ns_registration_details = array() ) {
    // Check if user exists in WC.
    $user = get_user_by( 'email', $guest_email );

    if( ! $user || empty( $guest_reg_id ) ) {
        return false;
    }

    $wc_user_id = $user->ID;

    if( empty( $ns_registration_details ) ) {
        // If the registration details not provided, fetch them.
        $ns_registration_details = tt_get_ns_guest_registrations_info( $guest_reg_id, true );
    }

    // Get "Lock Record" and "Lock Bike" values.
    $lock_record = tt_validate( $ns_registration_details->lockRecord );
    $lock_bike   = tt_validate( $ns_registration_details->lockBike );

    // Get stored registrations values.
    $lock_record_user_regs = get_user_meta( $wc_user_id, 'lock_record_registration_ids', true );
    $lock_bike_user_regs   = get_user_meta( $wc_user_id, 'lock_bike_registration_ids', true );

    // Set change status flags.
    $lock_record_has_change = false;
    $lock_bike_has_change   = false;

    if( $lock_record ) {
        // Need to store the Guest Registration ID to the user's meta for Record Locking.
        if( empty( $lock_record_user_regs ) ) {
            // If the user meta doesn't exist, create a new one.
            $lock_record_user_regs = array();
        }

        // Store the Guest Registration ID if not stored yet.
        if( is_array( $lock_record_user_regs ) && ! in_array( $guest_reg_id, $lock_record_user_regs ) ) {
            $lock_record_user_regs[] = $guest_reg_id; // So this guest registration is a with lock record status.
            $lock_record_has_change  = true;
            tt_add_error_log('NS - Locked Gear For:', array( 'user_email' => $guest_email, 'guest_registration_id' => $guest_reg_id ), array( 'lock_record_registration_ids' => $lock_record_user_regs ) );
        }
    } else {
        // Check if the guest registration exists in the array, if it does, remove it.
        if( ! empty( $lock_record_user_regs ) ) {
            // Remove all existing instances of the guest registration id, even if it's more than one.
            $key = array_search( $guest_reg_id, $lock_record_user_regs );

            if( $key !== false ) {
                unset( $lock_record_user_regs[ $key ] );
                $lock_record_has_change  = true;
                tt_add_error_log( 'NS - Unlocked Gear For:', array( 'user_email' => $guest_email, 'guest_registration_id' => $guest_reg_id ), array( 'lock_record_registration_ids' => $lock_record_user_regs ) );
            }
        }
    }

    if( $lock_bike ) {
        // Need to store the Guest Registration ID to the user's meta for Bike Locking.
        if( empty( $lock_bike_user_regs ) ) {
            // If the user meta doesn't exist, create a new one.
            $lock_bike_user_regs = array();
        }

        // Store the Guest Registration ID if not stored yet.
        if( is_array( $lock_bike_user_regs ) && ! in_array( $guest_reg_id, $lock_bike_user_regs ) ) {
            $lock_bike_user_regs[] = $guest_reg_id; // So this guest registration is a with lock bike status.
            $lock_bike_has_change  = true;
            tt_add_error_log('NS - Locked Bike For:', array( 'user_email' => $guest_email, 'guest_registration_id' => $guest_reg_id ), array( 'lock_bike_registration_ids' => $lock_bike_user_regs ) );
        }
    } else {
        // Check if the guest registration exists in the array, if it does, remove it.
        if( ! empty( $lock_bike_user_regs ) ) {
            // Remove all existing instances of the guest registration id, even if it's more than one.
            $key = array_search( $guest_reg_id, $lock_bike_user_regs );

            if( $key !== false ) {
                unset( $lock_bike_user_regs[ $key ] );
                $lock_bike_has_change  = true;
                tt_add_error_log( 'NS - Unlocked Bike For:', array( 'user_email' => $guest_email, 'guest_registration_id' => $guest_reg_id ), array( 'lock_bike_registration_ids' => $lock_bike_user_regs ) );
            }
        }
    }

    if( $lock_record_has_change ) {
        // If there is a change in the status of Record Lock, update the user meta.
        update_user_meta( $wc_user_id, 'lock_record_registration_ids', $lock_record_user_regs );
    }

    if( $lock_bike_has_change ) {
        // If there is a change in the status of Bike Lock, update the user meta.
        update_user_meta( $wc_user_id, 'lock_bike_registration_ids', $lock_bike_user_regs );
    }

    return true;
}

// Restore the mising pr() function
if ( ! function_exists( 'pr' ) ) {
	function pr( $data ) {
		echo '<pre>';
		print_r( $data );
		echo '</pre>';
	}
}

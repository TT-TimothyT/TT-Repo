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
    $trip_info        = tt_get_trip_pid_sku_from_cart($order_id);
    $depositAmount    = tt_get_local_trips_detail('depositAmount', $trip_info['ns_trip_Id'], $trek_checkoutData['sku'], true);
    $bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', $trip_info['ns_trip_Id'], $trek_checkoutData['sku'], true);
    if ( $transaction_deposit == true ) {
        $trip_transaction_amount = intval( $guests_count ) * floatval( $depositAmount );
    } else {
        $trip_transaction_amount = $authorization_amount;
    }
    $booking_index = 0;
    if ($wc_booking_result) {
        $total_insuarance_ammount = 0;
        $is_hiking_checkout       = tt_is_product_line( 'Hiking', $trip_info['sku'], $trip_info['ns_trip_Id'] );
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
    if ( $currentScreen->base == 'netsuitewc_page_tt-common-logs' || $currentScreen->base == 'toplevel_page_trek-travel-ns-wc' || $currentScreen->base == 'netsuitewc_page_tt-bookings' || $currentScreen->base == 'netsuitewc_page_tt-dev-tools' ) {
        wp_register_style( 'ttnsw-style', TTNSW_URL . '/assets/ttnsw-styles.css', false, time() );
        wp_enqueue_style( 'ttnsw-style' );
        wp_register_script( 'ttnsw-developer', TTNSW_URL.'/assets/ttnsw-developer.js', array(), time(), true );
        wp_localize_script('ttnsw-developer', 'ttnsw_JS_obj', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( '_ttnsw_nonce' ),
            'i18n'    => array(
                'approximateCountInfo' => __(
                    'This is an approximate count shown for better performance. ' .
                    'The exact count is being calculated in the background and will update automatically. ' .
                    'For large datasets, this helps avoid database slowdowns.',
                    'trek-travel-netsuite-integration'
                ),
            ),
        ));
        wp_enqueue_script( 'ttnsw-developer' );

        // Add Shepherd.js for tour guide
        wp_enqueue_style( 'shepherd-css', TTNSW_URL . '/assets/shepherd.css' );
        wp_enqueue_script( 'shepherd-js', TTNSW_URL . '/assets/shepherd.min.js', array(), null, true );
        
        // Add custom tour guide assets
        wp_enqueue_style('ttnsw-tour-guide', TTNSW_URL . '/assets/ttnsw-tour-guide.css', array(), time());
        wp_enqueue_script('ttnsw-tour-guide', TTNSW_URL . '/assets/ttnsw-tour-guide.js', array('jquery', 'shepherd-js'), time(), true);

        // Check if user has seen the tour
        $user_id = get_current_user_id();

        switch ($currentScreen->base) {
            case 'netsuitewc_page_tt-common-logs':
                $show_tour = ! get_user_meta( $user_id, 'tt_tour_logs_table_seen', true );
                $tour_name = 'logs_table';
                break;
            case 'netsuitewc_page_tt-bookings':
                $show_tour = ! get_user_meta( $user_id, 'tt_tour_bookings_table_seen', true );
                $tour_name = 'bookings_table';
                break;
            case 'toplevel_page_trek-travel-ns-wc':
                $show_tour = ! get_user_meta( $user_id, 'tt_tour_sync_tab_seen', true );
                $tour_name = 'sync_tab';
                break;
            default:
                $show_tour = '';
                $tour_name = 'no_tour';
                break;
        }

        // Get current user info
        $current_user = wp_get_current_user();
        $user_display_name = $current_user->display_name;

        $user_roles = $current_user->roles;

        wp_localize_script('ttnsw-tour-guide', 'ttnsw_tour_JS_obj', array(
            'ajaxurl'        => admin_url('admin-ajax.php'),
            'nonce'          => wp_create_nonce('_ttnsw_tour_nonce'),
            'show_tour'      => $show_tour,
            'user_name'      => $user_display_name, // Add user name
            'current_screen' => $currentScreen->base,
            'tour_name'      => $tour_name,
            'user_roles'     => $user_roles
        ));
    }
}
add_action( 'admin_enqueue_scripts', 'ttnsw_enqueue_custom_admin_style' );

// Add AJAX handler for saving tour status.
function tt_save_tour_status() {
    check_ajax_referer('_ttnsw_tour_nonce', 'nonce');
    
    $tour_name = sanitize_text_field($_POST['tour_name']); 
    $user_id   = get_current_user_id();
    
    update_user_meta($user_id, 'tt_tour_' . $tour_name . '_seen', true);
    
    wp_send_json_success();
}
add_action('wp_ajax_tt_save_tour_status', 'tt_save_tour_status');

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
 * @param bool $is_all Return all orders or the first one.
 *
 * @return object|int|bool
 */
function tt_get_order_by_booking( $booking_id, $full_order = true, $is_all = false ) {

    $args = array(
        // 'status'       => 'completed', // Accepts a string: one of 'pending', 'processing', 'on-hold', 'completed', 'refunded, 'failed', 'cancelled', or a custom order status.
        'meta_key'     => 'tt_meta_guest_booking_id', // Postmeta key field.
        'meta_value'   => $booking_id, // Postmeta value field.
        'meta_compare' => '=', // Possible values are ‘=’, ‘!=’, ‘>’, ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’, ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ (only in WP >= 3.5), and ‘NOT EXISTS’ (also only in WP >= 3.5). Values ‘REGEXP’, ‘NOT REGEXP’ and ‘RLIKE’ were added in WordPress 3.7. Default value is ‘=’.
        'return'       => $full_order ? 'objects' : 'ids' // Accepts a string: 'ids' or 'objects'. Default: 'objects'.
    );

    $orders = wc_get_orders( $args );

    if ( ! empty( $orders ) ) {
        // Return all orders or the first one.
        if ( $is_all ) {
            return $orders;
        }

        // Return the first order.
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
 * Convert a date from m/d/Y format to Y-m-d format.
 *
 * This function takes a date string in the format m/d/Y (e.g., 12/06/2025)
 * and returns it in Y-m-d format (e.g., 2025-12-06).
 *
 * @param string $date_string The date string to convert.
 *
 * @return string|null        The formatted date string, or null if the input is invalid.
 */
function tt_convert_us_date_to_iso( $date_string ) {
    if ( empty( $date_string ) || ! is_string( $date_string ) ) {
        return null;
    }

    $date = DateTime::createFromFormat( 'm/d/Y', $date_string );

    if ( false === $date ) {
        return null;
    }

    return $date->format( 'Y-m-d' );
}

/**
 * Get the accommodation type by guest ID.
 *
 * Searches through an array of rooms to find the accommodation type
 * associated with a given guest ID.
 *
 * @param array      $rooms       An array of rooms, each containing 'accommodationType' and 'guestIds'.
 * @param int|string $guest_id    The guest ID to search for.
 *
 * @return string|null            Returns the accommodation type if found, or null if not found.
 */
function tt_get_accommodation_type_by_guest_id( $rooms, $guest_id ) {
    if ( empty( $rooms ) || ! is_array( $rooms ) ) {
        return null;
    }

    foreach ( $rooms as $room ) {
        if ( isset( $room->guestIds ) && is_array( $room->guestIds ) ) {
            if ( in_array( $guest_id, $room->guestIds ) ) {
                return isset( $room->accommodationType ) ? $room->accommodationType : null;
            }
        }
    }

    return null;
}

/**
 * Remove line items from a WooCommerce order.
 *
 * This function removes specified line items from a given WooCommerce order.
 * It iterates through the provided item IDs and removes each one from the order.
 *
 * @param object $order    The WC Order object.
 * @param array  $item_ids An array of item IDs to remove from the order.
 *
 * @return void
 */
function tt_remove_line_items_from_order( $order, $item_ids ) {
    if ( ! $order || empty( $item_ids ) ) {
        return;
    }

    foreach ( $item_ids as $item_id ) {
        $order->remove_item( $item_id );
    }
}

/**
 * Get float value from a string with dollar sign and commas.
 *
 * This function takes a string that may contain a dollar sign and commas,
 * removes those characters, and converts the remaining string to a float.
 *
 * @param string $value The value to convert.
 *
 * @return float The converted float value.
 */
function tt_get_float_value( $value ) {
    // Remove dollar sign and commas.
    $value_str = str_replace(array('$', ','), '', $value);
    // Convert the string to a float.
    return (float) $value_str;
}

/**
 * Process and finalize order data with NetSuite sync.
 * 
 * Handles comprehensive order processing for both migrated orders from NetSuite 
 * and regular orders created through the web portal. Updates order items, calculates 
 * pricing, processes guest data, and ensures booking records are properly stored 
 * in the guest_bookings table.
 * 
 * @uses tt_get_bike_attributes_by_bike_id()
 * @uses tt_get_jersey_style()
 * @uses tt_ns_get_bike_type_info()
 *
 * @param object $order                      The WC Order object.
 * @param int    $product_id                 The Product ID.
 * @param int    $wc_user_id                 The WC User ID.
 * @param int    $current_ns_guest_id        The current NS Guest ID.
 * @param object $ns_guest_booking_result    The NS Guest Booking Result object. // 1305
 * @param object $ns_booking_data            The NS Booking Data object. // 1304
 * @param object $ns_guest_info              The NS Guest Info object. // 1294
 * @param object $ns_guest_info_from_booking The NS Guest Info from Booking object. // guest object in 1304
 * @param bool   $is_insertion               Whether this is an insertion or an update.
 *
 * @return void
 */
function tt_sync_order_data( $order, $product_id, $wc_user_id, $current_ns_guest_id, $ns_guest_booking_result, $ns_booking_data, $ns_guest_info, $ns_guest_info_from_booking, $is_insertion = false ) {
    $order_status       = 'not-finished';
    $order_guest_index  = 0;
    $guests_count       = 1;
    $bike_upgrade_count = 0;
    $order_bike_gears   = array(
        'primary' => array(),
        'guests'  => array(),
    );

    $order_guests       = array();
    $order_meta_emails  = array();
    $order_meta_primary = array();

    $bike_upgrade_price      = get_post_meta( $product_id, TT_WC_META_PREFIX . 'bikeUpgradePrice', true );
    $bike_upgrade_prices     = array(); // Will be used to store bike upgrade prices for each guest.
    $single_supplement_price = get_post_meta( $product_id, TT_WC_META_PREFIX . 'singleSupplementPrice', true );
    $product_tax_rate        = floatval( get_post_meta( $product_id, TT_WC_META_PREFIX . 'taxRate', true ) );

    // Convert the string to a float
    $single_supplement_price_float = tt_get_float_value( $single_supplement_price );

    $tt_total_insurance_amount = 0;
    $insured_person_count      = 0;
    $guests_with_discount      = 0;

    $ns_guest_id        = $ns_guest_info_from_booking->guestId; // Ns guest.

    $is_hiking_checkout = tt_is_product_line( 'Hiking', $ns_booking_data->tripCode, $ns_booking_data->tripId );

    $guest_bike_info    = tt_get_bike_attributes_by_bike_id( $ns_booking_data->tripId, $ns_booking_data->tripCode, $ns_guest_info->bikeId );

    $bike_type_id       = tt_validate( json_decode( $guest_bike_info['bikeModel'], true )['id'] );
    $guest_bike_gears   = array(
        'rider_level'              => tt_validate( $ns_guest_booking_result->riderType->id ),
        'activity_level'           => tt_validate( $ns_guest_booking_result->activityLevel->id ),
        'bikeTypeId'               => $bike_type_id,
        'bikeId'                   => tt_validate( $ns_guest_info->bikeId ),
        'bike_type_id_preferences' => '',
        'bike_size'                => tt_validate( json_decode( $guest_bike_info['bikeSize'], true )['id'] ),
        'rider_height'             => tt_validate( $ns_guest_info->heightId ),
        'bike_pedal'               => tt_validate( $ns_guest_info->pedalsId ),
        'helmet_size'              => tt_validate( $ns_guest_info->helmetId ),
        'jersey_style'             => tt_get_jersey_style( tt_validate( $ns_guest_info->jerseyId ) ),
        'jersey_size'              => tt_validate( $ns_guest_info->jerseyId ),
        'tshirt_size'              => tt_validate( $ns_guest_info->tshirtSizeId ),
    );

    $current_guest_has_bike_upgrade = false;
    $bike_upgrade_price_ns          = isset( $ns_guest_info_from_booking->wheelUpgrade ) ? tt_get_float_value( $ns_guest_info_from_booking->wheelUpgrade ) : 0;

    if ( 0 < $bike_upgrade_price_ns ) {
        // We have a bike upgrade as line item in the booking.
        $bike_upgrade_count++;

        $current_guest_has_bike_upgrade = true;

        // Add the price from the booking data.
        $bike_upgrade_prices[] = $bike_upgrade_price_ns;
    }

    // Rooms info.
    $room_count_single   = 0;
    $room_count_double   = 0;
    $room_count_roommate = 0;
    $room_count_private  = 0;
    $occupants           = array();
    $guests_occupants    = array();

    // Travel Protection info.
    $trek_guest_insurance = array(
        'primary' => array(
            'is_travel_protection' => 0,
            'basePremium' => 0,
        ),
        'guests' => array(),
    );

    $plan_id = get_field( 'plan_id', 'option' );

    if ( empty( $plan_id ) ) {
        $plan_id = 'TREKTRAVEL24';
    }

    $trek_insurance_args = array(
        "coverage" => array(
            "effective_date"  => tt_convert_us_date_to_iso( $ns_booking_data->tripStartDate ), // Trip Start Date.
            "expiration_date" => tt_convert_us_date_to_iso( $ns_booking_data->tripEndDate ), // Trip End Date.
            "depositDate"     => tt_convert_us_date_to_iso( $ns_booking_data->bookingDate ), // Booking Date.
            "destinations"    => array(
                array(
                    "countryCode" => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->country ) : tt_validate( $ns_guest_booking_result->addressInfo->country ),
                )
            )
        ),
        "language"             => "en-us",
        "planID"               => $plan_id,
        "returnTravelerQuotes" => true,
        "localDateTime"        => tt_convert_us_date_to_iso( $ns_booking_data->bookingDate ), // With this parameter can avoid errors caused by Time Zone differences like: {"success":false,"responseMessage":"The Deposit Date can not occurr in the future","responseCode":"InvalidDepositDate"}.
        "insuredPerson"        => array() // Will be filled with the guests data individually.
    );

    // Order info.
    $trip_product_qty    = isset( $ns_booking_data->guests ) ? count( $ns_booking_data->guests ) : 0;
    $trip_total_amount   = isset( $ns_booking_data->totalAmount ) ? tt_get_float_value( $ns_booking_data->totalAmount ) : 0;
    $trip_balance_paid   = isset( $ns_booking_data->balancePaid ) ? tt_get_float_value( $ns_booking_data->balancePaid ) : 0;
    $trip_amount_due     = isset( $ns_booking_data->amountDue ) ? tt_get_float_value( $ns_booking_data->amountDue ) : 0;
    $trip_sales_tax      = isset( $ns_booking_data->salesTax ) ? tt_get_float_value( $ns_booking_data->salesTax ) : 0;
    $trip_discount_total = isset( $ns_booking_data->discountsTotal ) ? abs( tt_get_float_value( $ns_booking_data->discountsTotal ) ) : 0;
    $trip_product_guests = isset( $ns_booking_data->guests ) ? $ns_booking_data->guests : array();
    $trip_product_price  = 0;
    $trip_s_qty          = 0;
    $trip_s_single_price = 0;

    $base_price_ns      = isset( $ns_guest_info_from_booking->basePrice ) ? tt_get_float_value( $ns_guest_info_from_booking->basePrice ) : 0;
    $insurance_price_ns = isset( $ns_guest_info_from_booking->insurance ) ? tt_get_float_value( $ns_guest_info_from_booking->insurance ) : 0;

    $single_supplement_price_ns = isset( $ns_guest_info_from_booking->singleSupplement ) ? tt_get_float_value( $ns_guest_info_from_booking->singleSupplement ) : 0;

    $individual_trip_cost = $base_price_ns;

    if ( $current_guest_has_bike_upgrade ) {
        $individual_trip_cost += 0 < $bike_upgrade_price_ns ? $bike_upgrade_price_ns : (float) $bike_upgrade_price;
    }

    if ( 0 != $single_supplement_price_ns ) {
        $individual_trip_cost += $single_supplement_price_ns;
    }

    $guest_discounts_total_ns = 0;

    if ( isset( $ns_guest_info_from_booking->discounts ) && is_array( $ns_guest_info_from_booking->discounts ) && ! empty( $ns_guest_info_from_booking->discounts ) ) {
        foreach( $ns_guest_info_from_booking->discounts as $discount ) {
            if ( isset( $discount->amount ) ) {
                $individual_trip_cost -= abs( $discount->amount );
                $guest_discounts_total_ns += abs( $discount->amount );
                $guests_with_discount++;
            }
        }
    }

    $insured_person_single = array();

    // Here we have two options. Some of the secondary guest is registered first or the primary guest is registered first.
    if ( $ns_guest_info->isPrimary ) {
        // Primary guest registers first.
        $guest_index_id              = 0;
        $order_guest_index           = $guest_index_id;

        // Rooms guests indexes map.
        $guests_occupants[$ns_guest_info->guestId] = $order_guest_index;

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

        if ( 0 < $insurance_price_ns ) {
            // If the insurance price is already set, we use it.
            $arcBasePremiumPP = $insurance_price_ns;
        } else {
            // Otherwise, we calculate the insurance fees.

            $insured_person_single[] = array(
                "address" => array(
                    "stateAbbreviation"   => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->state ) : tt_validate( $ns_guest_booking_result->addressInfo->state ),
                    "countryAbbreviation" => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->country ) : tt_validate( $ns_guest_booking_result->addressInfo->country ),
                ),
                "dob"                => $ns_guest_booking_result->birthdate,
                "individualTripCost" => $individual_trip_cost
            );

            $trek_insurance_args["insuredPerson"] = $insured_person_single;

            $archinsuranceResPP = tt_set_calculate_insurance_fees_api( $trek_insurance_args );
            $arcBasePremiumPP   = isset( $archinsuranceResPP['basePremium'] ) ? (float) $archinsuranceResPP['basePremium'] : 0;
        }

        $trek_guest_insurance['primary'] = array(
            'is_travel_protection' => isset( $ns_guest_info_from_booking->tripInsurancePurchased ) ? (int) $ns_guest_info_from_booking->tripInsurancePurchased : 0,
            'basePremium'          => $arcBasePremiumPP,
            'ns_prices'            => array(
                'base_price'        => $base_price_ns,
                'wheel_upgrade'     => $bike_upgrade_price_ns,
                'single_supplement' => $single_supplement_price_ns,
                'discounts_total'   => $guest_discounts_total_ns,
            )
        );

        if ( $ns_guest_info_from_booking->tripInsurancePurchased ) {
            $tt_total_insurance_amount += $arcBasePremiumPP;
            $insured_person_count++;
        }
    } else {
        // Secondary guest registers first.
        $guest_index_id    = 1;
        $order_guest_index = $guest_index_id;
        $guests_count += 1;

        // Rooms guests indexes map.
        $guests_occupants[$ns_guest_info->guestId] = $order_guest_index;

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

        if ( ! isset( $trek_guest_insurance['guests'][$order_guest_index] ) ) {
            $trek_guest_insurance['guests'][$order_guest_index] = array();
        }

        if ( 0 < $insurance_price_ns ) {
            // If the insurance price is already set, we use it.
            $arcBasePremiumPG = $insurance_price_ns;
        } else {
            // Otherwise, we calculate the insurance fees.

            $insured_person_single[] = array(
                "address" => array(
                    "stateAbbreviation"   => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->state ) : tt_validate( $ns_guest_booking_result->addressInfo->state ),
                    "countryAbbreviation" => isset( $ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $ns_guest_booking_result->addressInfo->shipping->country ) : tt_validate( $ns_guest_booking_result->addressInfo->country ),
                ),
                "dob"                => $ns_guest_booking_result->birthdate,
                "individualTripCost" => $individual_trip_cost
            );

            $trek_insurance_args["insuredPerson"] = $insured_person_single;

            $archinsuranceResPG = tt_set_calculate_insurance_fees_api( $trek_insurance_args );
            $arcBasePremiumPG   = isset( $archinsuranceResPG['basePremium'] ) ? (float) $archinsuranceResPG['basePremium'] : 0;
        }


        $trek_guest_insurance['guests'][$order_guest_index] = array(
            'is_travel_protection' => isset( $ns_guest_info_from_booking->tripInsurancePurchased ) ? (int) $ns_guest_info_from_booking->tripInsurancePurchased : 0,
            'basePremium'          => $arcBasePremiumPG,
            'ns_prices'            => array(
                'base_price'        => $base_price_ns,
                'wheel_upgrade'     => $bike_upgrade_price_ns,
                'single_supplement' => $single_supplement_price_ns,
                'discounts_total'   => $guest_discounts_total_ns,
            )
        );

        if ( $ns_guest_info_from_booking->tripInsurancePurchased ) {
            $tt_total_insurance_amount += $arcBasePremiumPG;
            $insured_person_count++;
        }
    }

    // Prepare data for the bookings table.
    $current_guest_rf_id = '';

    foreach ( $ns_booking_data->releaseForms as $guest_release_form ) {
        if ( $ns_guest_id == $guest_release_form->guestId ) {
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

    $check_gb_status = tt_checkbooking_status( $ns_guest_id, $ns_booking_data->bookingId ); // 0 if record not in the bookings table, 1 if record exists.

    // If this is an insertion or the record does not exist, we need to insert the booking data into the bookings table.
    if ( $is_insertion || ( 0 >= $check_gb_status ) ) {
        // New record insertion. This is the first insertion.
        tt_guest_bookings_table_crud( tt_prepare_bookings_table_data( $insert_booking_data, 'insert' ), [], 'insert' );
    } else {
        // Update the bookings table for the current guest. First update.
        $where = array( 'netsuite_guest_registration_id' => $ns_guest_id, 'ns_trip_booking_id' => $ns_booking_data->bookingId );
        tt_guest_bookings_table_crud( tt_prepare_bookings_table_data( $insert_booking_data ), $where );
    }

    foreach ( $ns_booking_data->guests as $guest ) {
        // Skip current guest, because we inserted data for him, and has stored in variables for the order already.
        if ( $guest->guestId == $current_ns_guest_id ) {
            continue;
        }

        $_ns_guest_id = $guest->guestId; // Ns guest.

        // Make requests to NS to take the info for every guest.
        $_ns_guest_booking_result = tt_get_ns_guest_info( $_ns_guest_id, 0 );
        $_ns_guest_info           = tt_get_ns_guest_registrations_info( $guest->registrationId, true );

        $_guest_bike_info         = tt_get_bike_attributes_by_bike_id( $ns_booking_data->tripId, $ns_booking_data->tripCode, $_ns_guest_info->bikeId );

        $_bike_type_id            = tt_validate( json_decode( $_guest_bike_info['bikeModel'], true )['id'] );
        $_guest_bike_gears        = array(
            'rider_level'              => tt_validate( $_ns_guest_booking_result->riderType->id ),
            'activity_level'           => tt_validate( $_ns_guest_booking_result->activityLevel->id ),
            'bikeTypeId'               => $_bike_type_id,
            'bikeId'                   => tt_validate( $_ns_guest_info->bikeId ),
            'bike_type_id_preferences' => '',
            'bike_size'                => tt_validate( json_decode( $_guest_bike_info['bikeSize'], true )['id'] ),
            'rider_height'             => tt_validate( $_ns_guest_info->heightId ),
            'bike_pedal'               => tt_validate( $_ns_guest_info->pedalsId ),
            'helmet_size'              => tt_validate( $_ns_guest_info->helmetId ),
            'jersey_style'             => tt_get_jersey_style( tt_validate( $_ns_guest_info->jerseyId ) ),
            'jersey_size'              => tt_validate( $_ns_guest_info->jerseyId ),
            'tshirt_size'              => tt_validate( $_ns_guest_info->tshirtSizeId ),
        );

        $_current_guest_has_bike_upgrade = false;
        $_bike_upgrade_price_ns          = isset( $guest->wheelUpgrade ) ? tt_get_float_value( $guest->wheelUpgrade ) : 0;

        if ( 0 < $_bike_upgrade_price_ns ) {
            // Selected bike is with upgrade
            $bike_upgrade_count++;

            $_current_guest_has_bike_upgrade = true;

            // Add the price from the booking data.
            $bike_upgrade_prices[] = $_bike_upgrade_price_ns;
        }

        $_base_price_ns      = isset( $guest->basePrice ) ? tt_get_float_value( $guest->basePrice ) : 0;
        $_insurance_price_ns = isset( $guest->insurance ) ? tt_get_float_value( $guest->insurance ) : 0;

        $_single_supplement_price_ns = isset( $guest->singleSupplement ) ? tt_get_float_value( $guest->singleSupplement ) : 0;

        $_individual_trip_cost = $_base_price_ns;

        if ( $_current_guest_has_bike_upgrade ) {
            $_individual_trip_cost += 0 < $_bike_upgrade_price_ns ? $_bike_upgrade_price_ns : (float) $bike_upgrade_price;
        }

        if ( 0 < $_single_supplement_price_ns ) {
            $_individual_trip_cost += $_single_supplement_price_ns;
        }

        $_guest_discounts_total_ns = 0;

        if ( isset( $guest->discounts ) && is_array( $guest->discounts ) && ! empty( $guest->discounts ) ) {
            foreach( $guest->discounts as $discount ) {
                if ( isset( $discount->amount ) ) {
                    $_individual_trip_cost -= abs( $discount->amount );
                    $_guest_discounts_total_ns += abs( $discount->amount );
                    $guests_with_discount++;
                }
            }
        }

        $_insured_person_single = array();

        if ( $guest->isPrimary ) {
            // Primary guest.
            $guest_index_id              = 0;
            $order_guest_index           = $guest_index_id;

            // Rooms guests indexes map.
            $guests_occupants[$_ns_guest_info->guestId] = $order_guest_index;

            $order_bike_gears['primary'] = $_guest_bike_gears;

            $order_rf_id                 = '';

            foreach( $ns_booking_data->releaseForms as $guest_release_form ) {
                if( $_ns_guest_id == $guest_release_form->guestId ) {
                    $order_rf_id = $guest_release_form->releaseFormId;
                }
            }

            // Compatibility solution for the old version of the NS Script 1305.
            if( ! isset( $_ns_guest_booking_result->addressInfo->shipping ) || ! isset( $_ns_guest_booking_result->addressInfo->billing ) ) {
                $address_full   = explode( ' ', tt_validate( $_ns_guest_booking_result->addressInfo->address ), 2 );
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
                        'first_name' => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->firstname ) : tt_validate( $_ns_guest_booking_result->firstname ),
                        'last_name'  => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->lastname ) : tt_validate( $_ns_guest_booking_result->lastname ),
                        'address_1'  => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->address_1 ) : tt_validate( $address_line_1 ),
                        'address_2'  => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->address_2 ) : tt_validate( $address_line_2 ),
                        'city'       => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->city ) : tt_validate( $_ns_guest_booking_result->addressInfo->city ),
                        'state'      => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->state ) : tt_validate( $_ns_guest_booking_result->addressInfo->state ),
                        'postcode'   => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->zip ) : tt_validate( $_ns_guest_booking_result->addressInfo->zip ),
                        'country'    => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->country ) : tt_validate( $_ns_guest_booking_result->addressInfo->country ),
                    ),
                    'billing' => array(
                        'first_name' => isset( $_ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $_ns_guest_booking_result->addressInfo->billing->firstname ) : tt_validate( $_ns_guest_booking_result->firstname ),
                        'last_name'  => isset( $_ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $_ns_guest_booking_result->addressInfo->billing->lastname ) : tt_validate( $_ns_guest_booking_result->lastname ),
                        'address_1'  => isset( $_ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $_ns_guest_booking_result->addressInfo->billing->address_1 ) : tt_validate( $address_line_1 ),
                        'address_2'  => isset( $_ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $_ns_guest_booking_result->addressInfo->billing->address_2 ) : tt_validate( $address_line_2 ),
                        'city'       => isset( $_ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $_ns_guest_booking_result->addressInfo->billing->city ) : tt_validate( $_ns_guest_booking_result->addressInfo->city ),
                        'state'      => isset( $_ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $_ns_guest_booking_result->addressInfo->billing->state ) : tt_validate( $_ns_guest_booking_result->addressInfo->state ),
                        'postcode'   => isset( $_ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $_ns_guest_booking_result->addressInfo->billing->zip ) :  tt_validate( $_ns_guest_booking_result->addressInfo->zip ),
                        'country'    => isset( $_ns_guest_booking_result->addressInfo->billing ) ? tt_validate( $_ns_guest_booking_result->addressInfo->billing->country ) : tt_validate( $_ns_guest_booking_result->addressInfo->country ),
                    )
                ),
                'dob'         => ! empty( tt_validate( $_ns_guest_booking_result->birthdate ) ) ? date( 'Y-m-d', strtotime( $_ns_guest_booking_result->birthdate ) ) : '',
                'gender'      => tt_validate( $_ns_guest_booking_result->gender->id ),
                'order_rf_id' => $order_rf_id,
            );

            if ( 0 < $_insurance_price_ns ) {
                // If the insurance is already purchased, we use the price from the booking.
                $arcBasePremiumPP = $_insurance_price_ns;
            } else {
                // Otherwise, we calculate the insurance fees.
                $_insured_person_single[] = array(
                    "address" => array(
                        "stateAbbreviation"   => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->state ) : tt_validate( $_ns_guest_booking_result->addressInfo->state ),
                        "countryAbbreviation" => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->country ) : tt_validate( $_ns_guest_booking_result->addressInfo->country ),
                    ),
                    "dob"                => $_ns_guest_booking_result->birthdate,
                    "individualTripCost" => $_individual_trip_cost
                );

                $trek_insurance_args["insuredPerson"] = $_insured_person_single;

                $archinsuranceResPP = tt_set_calculate_insurance_fees_api( $trek_insurance_args );
                $arcBasePremiumPP   = isset( $archinsuranceResPP['basePremium'] ) ? (float) $archinsuranceResPP['basePremium'] : 0;
            }

            $trek_guest_insurance['primary'] = array(
                'is_travel_protection' => isset( $guest->tripInsurancePurchased ) ? (int) $guest->tripInsurancePurchased : 0,
                'basePremium'          => $arcBasePremiumPP,
                'ns_prices'            => array(
                    'base_price'        => $_base_price_ns,
                    'wheel_upgrade'     => $_bike_upgrade_price_ns,
                    'single_supplement' => $_single_supplement_price_ns,
                    'discounts_total'   => $_guest_discounts_total_ns,
                )
            );

            if ( $guest->tripInsurancePurchased ) {
                $tt_total_insurance_amount += $arcBasePremiumPP;
                $insured_person_count++;
            }
        } else {
            // Secondary guest.
            $guest_index_id    = $guests_count; // ++
            $order_guest_index = $guest_index_id;
            $guests_count     += 1;

             // Rooms guests indexes map.
            $guests_occupants[$_ns_guest_info->guestId] = $order_guest_index;

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

            if ( ! isset( $trek_guest_insurance['guests'][$order_guest_index] ) ) {
                $trek_guest_insurance['guests'][$order_guest_index] = array();
            }

            if ( 0 < $_insurance_price_ns ) {
                // If the insurance is already purchased, we use the price from the booking.
                $arcBasePremiumPG = $_insurance_price_ns;
            } else {
                // Otherwise, we calculate the insurance fees.
                // !!! Note: the address here is for the current guest, not for the primary one.
                $_insured_person_single[] = array(
                    "address" => array(
                        "stateAbbreviation"   => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->state ) : tt_validate( $_ns_guest_booking_result->addressInfo->state ),
                        "countryAbbreviation" => isset( $_ns_guest_booking_result->addressInfo->shipping ) ? tt_validate( $_ns_guest_booking_result->addressInfo->shipping->country ) : tt_validate( $_ns_guest_booking_result->addressInfo->country ),
                    ),
                    "dob"                => $_ns_guest_booking_result->birthdate,
                    "individualTripCost" => $_individual_trip_cost
                );

                $trek_insurance_args["insuredPerson"] = $_insured_person_single;

                $archinsuranceResPG = tt_set_calculate_insurance_fees_api( $trek_insurance_args );
                $arcBasePremiumPG   = isset( $archinsuranceResPG['basePremium'] ) ? (float) $archinsuranceResPG['basePremium'] : 0;
            }

            $trek_guest_insurance['guests'][$order_guest_index] = array(
                'is_travel_protection' => isset( $guest->tripInsurancePurchased ) ? (int) $guest->tripInsurancePurchased : 0,
                'basePremium'          => $arcBasePremiumPG,
                'ns_prices'            => array(
                    'base_price'        => $_base_price_ns,
                    'wheel_upgrade'     => $_bike_upgrade_price_ns,
                    'single_supplement' => $_single_supplement_price_ns,
                    'discounts_total'   => $_guest_discounts_total_ns,
                )
            );

            if ( $guest->tripInsurancePurchased ) {
                $tt_total_insurance_amount += $arcBasePremiumPG;
                $insured_person_count++;
            }
        }

        // Prepare data for the bookings table.
        $current_guest_rf_id = '';

        foreach ( $ns_booking_data->releaseForms as $guest_release_form ) {
            if ( $_ns_guest_id == $guest_release_form->guestId ) {
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

        $_check_gb_status = tt_checkbooking_status( $_ns_guest_id, $ns_booking_data->bookingId ); // 0 if record not in the bookings table, 1 if record exists.

        // If this is an insertion or the record does not exist, we need to insert the booking data into the bookings table.
        if ( $is_insertion || ( 0 >= $_check_gb_status ) ) {
            // New record insertion. Every other guest insertion
            tt_guest_bookings_table_crud( tt_prepare_bookings_table_data( $_insert_booking_data, 'insert' ), [], 'insert' );
        } else {
            // Update the bookings table for the current guest.
            $_where = array( 'netsuite_guest_registration_id' => $_ns_guest_id, 'ns_trip_booking_id' => $ns_booking_data->bookingId );
            tt_guest_bookings_table_crud( tt_prepare_bookings_table_data( $_insert_booking_data ), $_where );
        }
    }

    // Finalizing the order needs to be done only one time.
    foreach ( $trip_product_guests as $guest ) {
        if ( $guest->isPrimary ) {
            $trip_pr_guest_ns_user_id = $guest->guestId;
            $trip_product_price_ns = isset( $guest->basePrice ) ? tt_get_float_value( $guest->basePrice ) : 0;
            $trip_product_price    = $trip_product_price_ns;
        }

        if ( 0 != $guest->singleSupplement ) {
            $trip_s_qty++;
            $trip_s_single_price_ns = isset( $guest->singleSupplement ) ? tt_get_float_value( $guest->singleSupplement ) : 0;
            $trip_s_single_price    = $trip_s_single_price_ns;
        }
    }

    $accommodation_type_map = array(
        'doubleOneBed'      => 'single',
        'doubleTwoBeds'     => 'double',
        'sharedWithRoommate'=> 'roommate',
        'privateRoom'       => 'private',
    );

    // Assign occupants to the rooms.
    if ( isset( $ns_booking_data->rooms ) && is_array( $ns_booking_data->rooms ) ) {
        foreach ( $ns_booking_data->rooms as $room ) {
            if( 'doubleOneBed' === $room->accommodationType ) { // single
                $room_count_single++;
            } elseif( 'doubleTwoBeds' === $room->accommodationType ) { // double
                $room_count_double++;
            } elseif( 'sharedWithRoommate' === $room->accommodationType ) { // roommate
                $room_count_roommate++;
            } elseif( 'privateRoom' === $room->accommodationType ) { // private
                $room_count_private++;
            }

            // Assign occupants to the rooms.
            if ( ! empty( $room->guestIds ) && is_array( $room->guestIds ) ) {
                foreach ( $room->guestIds as $guest_id ) {
                    if ( isset( $guests_occupants[$guest_id] ) ) {
                        if ( ! isset( $occupants[$accommodation_type_map[$room->accommodationType]] ) ) {
                            $occupants[$accommodation_type_map[$room->accommodationType]] = array();
                        }
                        $occupants[$accommodation_type_map[$room->accommodationType]][] = $guests_occupants[$guest_id];
                    }
                }
            }
        }
    }

    // Check the trip for the deposit payment.
    $pay_amount = 'full'; // Default payment amount is full.
    $is_deposit = '0'; // Default deposit is not set.

    if ( 0 < $trip_amount_due ) {
        $pay_amount = 'deposite'; // If the trip amount due is greater than 0, we set the payment amount to deposit.
        $is_deposit = '1';
    }

    $trek_user_checkout_data = array(
        'no_of_guests'               => count( $ns_booking_data->guests ),
        'first_name'                 => $order_meta_primary['first_name'],
        'last_name'                  => $order_meta_primary['last_name'],
        'shipping_first_name'        => $order_meta_primary['address_info']['shipping']['first_name'],
        'shipping_last_name'         => $order_meta_primary['address_info']['shipping']['last_name'],
        'shipping_phone'             => $order_meta_primary['phone'],
        'custentity_birthdate'       => $order_meta_primary['dob'],
        'custentity_gender'          => $order_meta_primary['gender'],
        'shipping_address_1'         => $order_meta_primary['address_info']['shipping']['address_1'],
        'shipping_address_2'         => $order_meta_primary['address_info']['shipping']['address_2'],
        'shipping_country'           => $order_meta_primary['address_info']['shipping']['country'],
        'shipping_state'             => $order_meta_primary['address_info']['shipping']['state'],
        'shipping_city'              => $order_meta_primary['address_info']['shipping']['city'],
        'shipping_postcode'          => $order_meta_primary['address_info']['shipping']['postcode'],
        'billing_first_name'         => $order_meta_primary['address_info']['billing']['first_name'],
        'billing_last_name'          => $order_meta_primary['address_info']['billing']['last_name'],
        'billing_address_1'          => $order_meta_primary['address_info']['billing']['address_1'],
        'billing_address_2'          => $order_meta_primary['address_info']['billing']['address_2'],
        'billing_country'            => $order_meta_primary['address_info']['billing']['country'],
        'billing_state'              => $order_meta_primary['address_info']['billing']['state'],
        'billing_city'               => $order_meta_primary['address_info']['billing']['city'],
        'billing_postcode'           => $order_meta_primary['address_info']['billing']['postcode'],
        'email'                      => $order_meta_primary['email'],
        'guests'                     => $order_guests,
        'single'                     => $room_count_single,
        'double'                     => $room_count_double,
        'roommate'                   => $room_count_roommate,
        'private'                    => $room_count_private,
        'occupants'                  => $occupants,
        'pay_amount'                 => $pay_amount,
        'bike_gears'                 => $order_bike_gears,
        'trek_guest_insurance'       => $trek_guest_insurance,
        'tt_waiver'                  => '1',
        'is_hiking_checkout'         => $is_hiking_checkout,
        'parent_product_id'          => tt_get_parent_trip_id_by_child_sku( $ns_booking_data->tripCode, false ),
        'product_id'                 => $product_id,
        'sku'                        => $ns_booking_data->tripCode,
        'bikeUpgradePrice'           => $bike_upgrade_price,
        'singleSupplementPrice'      => $single_supplement_price_float,
        'cart_total_full_amount'     => $trip_total_amount,
        'insuredPerson'              => $insured_person_count,
        'tt_insurance_total_charges' => $tt_total_insurance_amount,
    );

    // Get the trip product.
    $trip_product                   = wc_get_product( $product_id );
    $existing_trip_product_item_id  = null;

    // Get the line item product IDs.
    $s_product_id                   = tt_create_line_item_product( 'TTWP23SUPP' );
    $existing_s_item_id             = null;

    $bike_upgrade_product_id        = tt_create_line_item_product( 'TTWP23UPGRADES' );
    $existing_bike_upgrade_item_ids = array();

    $insurance_product_id           = tt_create_line_item_product( 'TTWP23FEES' );
    $existing_insurance_item_id     = null;

    // Get existing item IDs for single supplement, bike upgrade, and insurance.
    foreach ( $order->get_items() as $item_id => $item ) {
        $item_product_id = $item->get_product_id();

        if ( $item_product_id == $s_product_id ) {
            $existing_s_item_id = $item_id;
        }

        if ( $item_product_id == $bike_upgrade_product_id ) {
            $existing_bike_upgrade_item_ids[] = $item_id; // Store all bike upgrade item IDs.
        }

        if ( $item_product_id == $insurance_product_id ) {
            $existing_insurance_item_id = $item_id;
        }

        if ( $item_product_id == $product_id ) {
            // If the trip product is already in the order, we can update it.
            $existing_trip_product_item_id = $item_id;
        }
    }

    if ( ! empty( $trip_product_price ) ) {
        $trip_product->set_price( $trip_product_price );
    }

    // If this is an insertion, we need to insert the line item meta.
    if ( $is_insertion ) {
        $product_item_id = $order->add_product( $trip_product, $trip_product_qty );
        wc_add_order_item_meta( $product_item_id, 'trek_user_checkout_data', $trek_user_checkout_data );
        wc_add_order_item_meta( $product_item_id, 'trek_user_checkout_product_id', $product_id );
    } else {
        // Add or update the trip product item in the order.
        if ( $existing_trip_product_item_id ) {
            $product_item_id = $existing_trip_product_item_id;
            $existing_item   = $order->get_item( $product_item_id );
            $existing_item->set_quantity( $trip_product_qty );
            if ( ! empty( $trip_product_price ) ) {
                $existing_item->set_subtotal( $trip_product_price * $trip_product_qty );
                $existing_item->set_total( $trip_product_price * $trip_product_qty );
            }
            $existing_item->save();
        } else {
            // If the trip product is not in the order, we add it.
            $product_item_id = $order->add_product( $trip_product, $trip_product_qty );
        }

        // Update the order item meta for the trip product.
        wc_update_order_item_meta( $product_item_id, 'trek_user_checkout_data', $trek_user_checkout_data );
        wc_update_order_item_meta( $product_item_id, 'trek_user_checkout_product_id', $product_id );
    }

    // *** Single Supplement Fees ***
    if ( empty( $trip_s_qty ) || $trip_s_qty <= 0 ) {
        // Remove existing single supplement item if trip_s_qty is empty or 0
        if ( $existing_s_item_id ) {
            $order->remove_item( $existing_s_item_id );
            $order->save();
        }
    } else {
        // Add or update single supplement item
        if ( $existing_s_item_id ) {
            // Update existing item quantity
            $existing_item = $order->get_item( $existing_s_item_id );
            $existing_item->set_quantity( $trip_s_qty );
            if ( ! empty( $trip_s_single_price ) ) {
                $existing_item->set_subtotal( $trip_s_single_price * $trip_s_qty );
                $existing_item->set_total( $trip_s_single_price * $trip_s_qty );
            }
            $existing_item->save();
        } else {
            // Add new single supplement item
            $s_product = wc_get_product( $s_product_id );
            if ( ! empty( $trip_s_single_price ) ) {
                $s_product->set_price( $trip_s_single_price );
            }
            $s_product_item_id = $order->add_product( $s_product, $trip_s_qty );
        }
    }

    // *** Bike Upgrade Fees ***
    // Remove existing bike upgrade item if bike_upgrade_count is empty or 0
    if ( ! empty( $existing_bike_upgrade_item_ids ) ) {
        tt_remove_line_items_from_order( $order, $existing_bike_upgrade_item_ids );
    }

    if ( $bike_upgrade_count > 0 ) {

        // Add new bike upgrade items.
        $bike_upgrade_product = wc_get_product( $bike_upgrade_product_id );

        // Check if any price in the bike upgrade prices is different from the others.
        if ( count( array_unique( $bike_upgrade_prices ) ) > 1 ) {
            // If there are different prices, we need to set the price for each item individually.
            foreach ( $bike_upgrade_prices as $price ) {
                $bike_upgrade_product = wc_get_product( $bike_upgrade_product_id );
                $bike_upgrade_product->set_price( $price );
                $bike_upgrade_item_id = $order->add_product( $bike_upgrade_product, 1 );
            }
        } elseif ( count( $bike_upgrade_prices ) > 0 ) {
            // If all prices are the same, we can set the price for the entire item.
            $bike_upgrade_product->set_price( $bike_upgrade_prices[0] );
            $bike_upgrade_item_id = $order->add_product( $bike_upgrade_product, $bike_upgrade_count );
        }
    }

    // *** Travel Protection Fees ***
    if ( empty( $tt_total_insurance_amount ) || $tt_total_insurance_amount <= 0 ) {
        // Remove existing insurance item if tt_total_insurance_amount is empty or 0
        if ( $existing_insurance_item_id ) {
            $order->remove_item( $existing_insurance_item_id );
            $order->save();
        }
    } else {
        // Add or update insurance item
        if ( $existing_insurance_item_id ) {
            // Update existing item quantity
            $existing_item = $order->get_item( $existing_insurance_item_id );
            $existing_item->set_quantity( 1 ); // Insurance is always one item
            if ( ! empty( $tt_total_insurance_amount ) ) {
                $existing_item->set_subtotal( $tt_total_insurance_amount );
                $existing_item->set_total( $tt_total_insurance_amount );
            }
            $existing_item->save();
        } else {
            // Add new insurance item
            $insurance_product = wc_get_product( $insurance_product_id );
            if ( ! empty( $tt_total_insurance_amount ) ) {
                $insurance_product->set_price( $tt_total_insurance_amount );
            }
            $insurance_item_id = $order->add_product( $insurance_product, 1 );
        }
    }

    // *** Taxes ***
    $tax_fee        = null;
    $tax_fee_exists = false;
    $tax_fee_id     = null;
    foreach ( $order->get_items('tax') as $tax_item_id => $tax_item ) {
         /** @var WC_Order_Item_Tax $tax_item */
        if ( $tax_item->get_label() === __( 'Tax', 'trek-travel-theme' ) ) {
            $tax_fee_exists = true;
            $tax_fee        = $tax_item;
            $tax_fee_id     = $tax_item_id;
            break;
        }
    }

    // If there is no tax amount, we can remove it.
    if ( $tax_fee_exists && $tax_fee_id ) {
        $order->remove_item( $tax_fee_id );
    }

    // If there is a tax amount, we need to add it to the order or update the existing one.
    if ( 0 < $trip_sales_tax ) {
        $tax_fee = new WC_Order_Item_Tax();
        $tax_fee->set_rate_id( $product_tax_rate ); // Set the tax rate if needed.
        $tax_fee->set_label( __( 'Tax', 'trek-travel-theme' ) );
        $tax_fee->set_tax_total( $trip_sales_tax );
        $tax_fee->set_shipping_tax_total( 0.00 );

        $order->add_item( $tax_fee );
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

    // *** Discounts ***
    $coupon = null;

    // First backup any existing coupons on the first sync.
    $order_coupons_bk = get_post_meta( $order->id, 'tt_wc_order_coupons_bk', true );
    if ( empty( $order_coupons_bk ) ) {
        $coupons_for_bk   = array();
        $existing_coupons = $order->get_items( 'coupon' );

        foreach ( $existing_coupons as $coupon_item ) {
            /** @var WC_Order_Item_Coupon $coupon_item */
            $coupons_for_bk[] = array(
                'code'     => $coupon_item->get_code(),
                'discount' => $coupon_item->get_discount(),
            );
        }
        update_post_meta( $order->id, 'tt_wc_order_coupons_bk', $coupons_for_bk );
    }

    // If there is a discount, needs to create a coupon.
    if ( 0 < $trip_discount_total ) {
        if ( 0 <= $guests_with_discount ) {
            $guests_with_discount = $guests_count; // Default to total guests count.
        }

        $discount_amount = $trip_discount_total / $guests_with_discount;

        $should_create_coupon = true;

        // Check for coupon with the same discount_amount.
        $coupons = $order->get_items( 'coupon' );
        foreach ( $coupons as $coupon_item ) {
            /** @var WC_Order_Item_Coupon $coupon_item */
            if ( $coupon_item->get_discount() == $discount_amount ) {
                // If a coupon with the same discount amount already exists, no need to create a new one.
                $should_create_coupon = false;
                break;
            }
        }

        // If no coupon with the same discount amount exists, create a new coupon.
        if ( $should_create_coupon ) {
            $coupon_code = 'auto_discount_' . $order->id;

            $coupon = new WC_Coupon();
            $coupon->set_code( $coupon_code );
            $coupon->set_discount_type( 'fixed_cart' );
            $coupon->set_amount( $discount_amount );
            $coupon->set_individual_use( true );
            $coupon->set_usage_limit( 1 );
            $coupon->save();

            $order->apply_coupon( $coupon_code );
        }
    } else {
        // No discount applied, needs to remove any existing coupon.
        $coupons = $order->get_items( 'coupon' );
        foreach ( $coupons as $coupon_item_id => $coupon_item ) {
            /** @var WC_Order_Item_Coupon $coupon_item */
            $order->remove_item( $coupon_item_id );
        }
    }

    $order->calculate_totals();

    if ( '1' === $is_deposit ) {
        // Update the order total with the paid amount.
        $order->set_total( $trip_balance_paid );
    } else {
        if ( 0 < $trip_sales_tax ) {
            $order->set_total( $order->get_total() + $trip_sales_tax );
        }
    }

    $order->save();

    if ( $coupon && is_a( $coupon, 'WC_Coupon' ) ) {
        wp_delete_post( $coupon->get_id(), true );
    }

    // Check for backup of the original order meta.
    $trek_user_checkout_data_bk = get_post_meta( $order->id, 'trek_user_checkout_data_bk', true );

    // If the order backup meta is empty, we can store the original data as a backup.
    if ( empty( $trek_user_checkout_data_bk ) ) {
        $trek_user_checkout_data_original = get_post_meta( $order->id, 'trek_user_checkout_data', true );
        // Backup the original order meta.
        update_post_meta( $order->id, 'trek_user_checkout_data_bk', $trek_user_checkout_data_original );
    }

    // Update the order meta.
    update_post_meta( $order->id, 'trek_user_checkout_data', $trek_user_checkout_data );
    update_post_meta( $order->id, 'trek_user_checkout_product_id', $product_id );
    update_post_meta( $order->id, 'tt_meta_releaseFormId', $order_meta_primary['order_rf_id'] );
    update_post_meta( $order->id, 'tt_wc_order_ns_status', true );
    update_post_meta( $order->id, 'tt_wc_order_trip_user_emails', $order_meta_emails );
    update_post_meta( $order->id, 'tt_meta_total_amount', $trip_total_amount );
    update_post_meta( $order->id, 'tt_wc_order_finished_status', $order_status );

    if ( '1' === $is_deposit ) {
        update_post_meta( $order->id, '_is_order_transaction_deposit', true );
    } else {
        update_post_meta( $order->id, '_is_order_transaction_deposit', false );
    }

    // If this is an insertion, we need to increase the product stock and log the order finalization.
    if ( $is_insertion ) {
        // Restore product qty.
        wc_update_product_stock( $product_id, $trip_product_qty, 'increase' );

        tt_add_error_log( 'NS - Finalize migrated order - END.', array( 'booking_id' => $ns_booking_data->bookingId, 'order_id' => $order->id, 'customer_id' => $wc_user_id, 'ns_user_id' => $current_ns_guest_id, 'is_primary' => $ns_guest_info->isPrimary ), array( 'status' => 'true', 'message' => 'End migrated order sync.' ) );
    }
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
        $booking_table_data['ns_booking_status']        = 1;

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

    if ( isset( $order_info['order_id'] ) ) {
        $booking_table_data['order_id'] = $order_info['order_id'];
    }

    if ( isset( $order_info['current_guest_rf_id'] ) ) {
        $booking_table_data['releaseFormId'] = $order_info['current_guest_rf_id'];
    }

    if ( isset( $order_info['guest_index_id'] ) ) {
        $booking_table_data['guest_index_id'] = $order_info['guest_index_id'];
    }

    $booking_table_data['guest_phone_number']                  = tt_validate( $ns_guest_booking_result->phone );
    $booking_table_data['guest_gender']                        = tt_validate( $ns_guest_booking_result->gender->id );
    $booking_table_data['guest_date_of_birth']                 = ! empty( tt_validate( $ns_guest_booking_result->birthdate ) ) ? date( 'Y-m-d', strtotime( $ns_guest_booking_result->birthdate ) ) : '';
    $booking_table_data['wantsInsurance']                      = tt_validate( $guest->tripInsurancePurchased );
    $booking_table_data['waive_insurance']                     = tt_validate( $guest->waiveInsurance );
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
        'gear_preferences_barheightfromwheel'     => tt_validate( $guest_data->barHeightFromWheel ),
        'gear_preferences_barreachfromsaddle'     => tt_validate( $guest_data->barReachFromSaddle ),

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

/**
 * Save the screen option setting for the per_page option.
 *
 * @link https://humanmade.com/engineering/extend-and-create-screen-options-in-the-wordpress-admin/
 *
 * Modified the source hook from the link above with this set_screen_option_{$option}
 * @see https://developer.wordpress.org/reference/hooks/set_screen_option_option/
 *
 * @param string $screen_option The default value for the filter. Using anything other than false assumes you are handling saving the option.
 * @param string $option The option name.
 * @param array  $value  Whatever option you're setting.
 */
function ttnsw_set_screen_option_per_page( $screen_option, $option, $value ) {
	return $value;
}
add_filter( 'set_screen_option_ttnsw_common_logs_per_page', 'ttnsw_set_screen_option_per_page', 10, 3 );
add_filter( 'set_screen_option_ttnsw_bookings_per_page', 'ttnsw_set_screen_option_per_page', 10, 3 );

/**
 * Remove the Admin notices on the plugin pages.
 */
function ttnsw_remove_third_party_admin_notices() {
    $remove_notices_pages = array( 'toplevel_page_trek-travel-ns-wc', 'netsuitewc_page_tt-common-logs', 'netsuitewc_page_tt-bookings', 'netsuitewc_page_tt-dev-tools' );
    $current_screen       = get_current_screen();
    if( in_array( $current_screen->base, $remove_notices_pages ) ) {
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );
    }
}
add_action( 'in_admin_header', 'ttnsw_remove_third_party_admin_notices', 20 );

/**
 * Save the column group visibility settings.
 */
function ttnsw_save_column_group_visibility_cb() {
    if( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), '_ttnsw_nonce' ) ) {
        wp_send_json_error( 'Invalid nonce.' );
    }
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'You do not have permission to do this.' );
    }
    
    $group   = sanitize_key( $_POST['group'] );
    $visible = $_POST['visible'] === 'true' ? 'true' : 'false'; // Convert to string.
    
    update_user_option( get_current_user_id(), 'bookings_group_' . $group . '_visible', $visible );
    wp_send_json_success();
}
add_action( 'wp_ajax_save_column_group_visibility', 'ttnsw_save_column_group_visibility_cb' );

/**
 * Add the screen options for the bookings table.
 *
 * @param string     $settings
 * @param \WP_Screen $screen
 */
function ttnsw_bookings_table_screen_settings( $settings, \WP_Screen $screen ) {
    global $ttnsw_bookings_page;

    // Return if not on our settings page.
    if( ! is_object( $screen ) || $screen->id !== $ttnsw_bookings_page) {
        return $settings;
    }

    // Add column groups screen options
    $ttnsw_bookings_table = new Guest_Bookings_Table();
    $groups               = $ttnsw_bookings_table->get_column_groups();

    $settings .= '<fieldset class="metabox-prefs column-groups">';
    $settings .= '<legend>' . __('Column Groups', 'trek-travel-netsuite-integration') . '</legend>';

    foreach ( $groups as $group_key => $group ) {
        $checked   = get_user_option( 'bookings_group_' . $group_key . '_visible' ) !== 'false' ? 'checked="checked"' : '';
        $settings .= sprintf(
            '<label><input type="checkbox" class="hide-column-tog" name="bookings_group_%1$s" value="1" %3$s>%2$s</label>',
            esc_attr($group_key),
            esc_html($group['title']),
            $checked
        );
    }

    $settings .= '</fieldset>';

    return $settings;

}
add_filter( 'screen_settings', 'ttnsw_bookings_table_screen_settings', 10, 2 );

/**
 * Helper function to load a template part and return its contents as a string
 *
 * @param string $template The template to load.
 *
 * @return string The template content.
 */
function ttnsw_load_template_part( $template ) {
    ob_start();
    include TTNSW_DIR . $template;
    return ob_get_clean();
}

/**
 * Remove specific menu items for users with the 'manage_guest_data' capability
 * but only if they are not administrators.
 */
function ttnsw_remove_menu_items() {
    // Keep menus for administrators regardless of other capabilities
    if (current_user_can('administrator')) {
        return;
    }

    // Get current user
    $user = wp_get_current_user();

    // Check if user has the manage_guest_data capability OR has the guest_data_manager role
    if (current_user_can('manage_guest_data') || in_array('guest_data_manager', (array) $user->roles)) {
        remove_menu_page('revisionary-archive');
        remove_menu_page('profile.php');
        remove_menu_page('algolia');
    }
}
add_action('admin_menu', 'ttnsw_remove_menu_items', 11);

/**
 * Callback function for the `tt_trigger_ns_booking_update` action.
 * This function is a placeholder and can be extended to perform actions
 * when a booking update is triggered.
 *
 * @param int $order_id The ID of the order being updated.
 */
function tt_trigger_ns_booking_update_cb( $order_id = 0 ) {
    if ( empty( $order_id ) ) {
        return;
    }

    $accepted_p_ids     = tt_get_line_items_product_ids();
    $tt_protection_data = array();
    $order              = wc_get_order($order_id);
    $order_items        = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
    foreach ( $order_items as $item_id => $item ) {
        $product_id = $item['product_id'];
        if ( in_array( $product_id, $accepted_p_ids ) ) {
            $tt_protection_data = wc_get_order_item_meta( $item_id, 'tt_protection_data', true );
        }
    }

    $travelers        = isset( $tt_protection_data['travelers'] ) ? $tt_protection_data['travelers'] : array();
    $booking_order_id = isset( $tt_protection_data['order_id'] ) ? $tt_protection_data['order_id'] : 0;

    // RETRIEVE INSTRUMENT IDENTIFIER //
    global $wpdb;
    $bookings_table_name = $wpdb->prefix . 'guest_bookings';

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

    $transaction_id              = get_post_meta( $order_id, '_wc_cybersource_credit_card_trans_id', true );
    $transaction_date            = get_post_meta( $order_id, '_wc_cybersource_credit_card_trans_date', true );
    $authorization_amount        = get_post_meta( $order_id, '_wc_cybersource_credit_card_authorization_amount', true );
    $transaction_deposit         = get_post_meta( $order_id, '_is_order_transaction_deposit', true );
    $transaction_payment_token   = $identifier;
    $cc_account_four             = get_post_meta( $order_id, '_wc_cybersource_credit_card_account_four', true );
    $cc_expiry_date              = get_post_meta( $order_id, '_wc_cybersource_credit_card_card_expiry_date', true );
    $cc_card_type                = get_post_meta( $order_id, '_wc_cybersource_credit_card_card_type', true );
    $cc_processor_transaction_id = get_post_meta( $order_id, '_wc_cybersource_credit_card_processor_transaction_id', true );


    $ns_booking_update_payload = array();

    foreach ( $travelers as $traveler_key => $traveler_data ) {
        if ( 'primary' === $traveler_key ) {
            $ns_booking_update_payload['paymentInfo'] = array(
                'transaction_id'                                   => $transaction_id,
                'transaction_date'                                 => $transaction_date,
                'transaction_authorization_amount'                 => $authorization_amount,
                'transaction_deposit'                              => ($transaction_deposit ? 1 : 0),
                'transaction_payment_token'                        => $transaction_payment_token,
                'transaction_credit_card_account_four'             => $cc_account_four,
                'transaction_card_card_expiry_date'                => $cc_expiry_date,
                'transaction_cardholder_name'                      => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'transaction_credit_card_card_type'                => $cc_card_type,
                'transaction_credit_card_processor_transaction_id' => $cc_processor_transaction_id
            );

            $ns_booking_update_payload['billingAddress'] = array(
                'country' => $order->get_billing_country(),
                'address' => $order->get_billing_address_1() . ' ' . $order->get_billing_address_2(),
                'city'    => $order->get_billing_city(),
                'state'   => $order->get_billing_state(),
                'zip'     => $order->get_billing_postcode()
            );

            // Update the primary guest booking
            $guest_index = 0; // Primary guest index is always 0

            $booking_order_data = $wpdb->get_row( $wpdb->prepare( "
                SELECT guestRegistrationId, 
                       ns_trip_booking_id, 
                       netsuite_guest_registration_id, 
                       wantsInsurance, 
                       insuranceAmount
                FROM {$bookings_table_name} 
                WHERE order_id = %d AND guest_index_id = %d
            ", $booking_order_id, $guest_index ), ARRAY_A );

            $ns_booking_update_payload['bookingId'] = $booking_order_data['ns_trip_booking_id'];

            $is_tp_purchased = (int) $traveler_data['is_tp_purchased'] === 1 ? true : false;

            // If the guest has purchased the travel protection, skip it.
            if ( $is_tp_purchased ) {
                continue;
            }

            $ns_booking_update_payload['guestsData'][$guest_index] = array(
                'registrationId' => $booking_order_data['guestRegistrationId'],
                'guestId'        => $booking_order_data['netsuite_guest_registration_id'],
                'wantsInsurance' => (int) $booking_order_data['wantsInsurance'], // Convert to boolean.
                'insuranceAmount'=> $booking_order_data['insuranceAmount']
            );

        } else {
            // Update each guest booking
            foreach ( $traveler_data as $guest_index => $guest_data ) {
                $booking_order_data = $wpdb->get_row( $wpdb->prepare( "
                    SELECT guestRegistrationId, 
                           ns_trip_booking_id, 
                           netsuite_guest_registration_id, 
                           wantsInsurance, 
                           insuranceAmount
                    FROM {$bookings_table_name} 
                    WHERE order_id = %d AND guest_index_id = %d
                ", $booking_order_id, $guest_index ), ARRAY_A );

                $is_tp_purchased = (int) $guest_data['is_tp_purchased'] === 1 ? true : false;

                // If the guest has purchased the travel protection, skip it.
                if ( $is_tp_purchased ) {
                    continue;
                }

                $ns_booking_update_payload['guestsData'][$guest_index] = array(
                    'registrationId' => $booking_order_data['guestRegistrationId'],
                    'guestId'        => $booking_order_data['netsuite_guest_registration_id'],
                    'wantsInsurance' => (int) $booking_order_data['wantsInsurance'], // Convert to boolean.
                    'insuranceAmount'=> $booking_order_data['insuranceAmount']
                );
            }
        }
    }

    if ( empty( $ns_booking_update_payload['guestsData'] ) ) {
        do_action( 'tt_set_ns_tpp_status', $order_id, 'tpp_onhold' );
        return;
    }

    $ns_booking_update_payload['guestsData'] = array_values( $ns_booking_update_payload['guestsData'] ); // Re-index the array to ensure it starts from 0.

    $net_suite_client         = new NetSuiteClient();
    $ns_booking_update_result = $net_suite_client->post( TPP_SCRIPT_ID, json_encode( $ns_booking_update_payload ) );
    tt_after_ns_booking_update( $order_id, $ns_booking_update_result );
    tt_add_error_log( 'TPP_SCRIPT_ID: ' . TPP_SCRIPT_ID, $ns_booking_update_payload, $ns_booking_update_result );
}
add_action( 'tt_trigger_ns_booking_update', 'tt_trigger_ns_booking_update_cb', 10, 1 );

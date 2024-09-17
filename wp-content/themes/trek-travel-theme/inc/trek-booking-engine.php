<?php

add_action( 'woocommerce_thankyou', 'insert_records_guest_bookings_cb');
function insert_records_guest_bookings_cb( $order_id, $custom_user = null, $call_from_code = 'false'){
    $is_behalf = false;
    if ( isset( $_SESSION['admin'] ) && isset( $_SESSION['current_user_ids'] ) && $_SESSION['admin'] == 'adminisloggedin' ) {
        $is_behalf = true;
    }
    //$order_id = $order->get_id();
    if( $call_from_code == 'true' ){
        $ns_order_status = 'false';
    } else {
        $ns_order_status = get_post_meta($order_id,'tt_wc_order_ns_status', true);
    }
    $tt_trip_user_emails = [];
    if( $ns_order_status != 'true' ){
        global $wpdb;
        $table_name = $wpdb->prefix . 'guest_bookings';
        $accepted_p_ids = tt_get_line_items_product_ids();
        $currentUser = wp_get_current_user();
        $user_id = $currentUser->ID;
        if( $custom_user ){
            $user_id = $custom_user;
        }
        $currentUser_email = $currentUser->user_email;
        $order = new WC_Order($order_id);
        $items = $order->get_items();
        $checkout_data = $formatted_checkout_data = array();
        $product_id = NULL;
        foreach ( $items as $item_id => $item ) {
            if( !in_array($item['product_id'], $accepted_p_ids) ){
                $formatted_checkout_data = wc_get_order_item_meta( $item_id, 'trek_user_formatted_checkout_data', true ); 
                $checkout_data = wc_get_order_item_meta( $item_id, 'trek_user_checkout_data', true ); 
                $product_id = version_compare( WC_VERSION, '3.0', '<' ) ? $item['product_id'] : $item->get_product_id();
            }
        }
        if (!empty($formatted_checkout_data)) {
            update_post_meta($order_id, 'trek_user_formatted_checkout_data', $formatted_checkout_data);
        }
        if (!empty($checkout_data)) {
            update_post_meta($order_id, 'trek_user_checkout_data', $checkout_data);
        }
        if (!empty($product_id)) {
            update_post_meta($order_id, 'trek_user_checkout_product_id', $product_id);
        }
        $payment_on = isset($checkout_data['pay_amount']) && $checkout_data['pay_amount'] == 'deposite' ? true : false;
        if (!empty($payment_on)) {
            update_post_meta($order_id, '_is_order_transaction_deposit', $payment_on);
        }
        $ns_user_id = get_user_meta($user_id, 'ns_customer_internal_id', true);
        $emergence_cfname = get_user_meta($user_id, 'custentity_emergencycontactfirstname', true);
        $emergence_clname = get_user_meta($user_id, 'custentityemergencycontactlastname', true);
        $emergence_cphone = get_user_meta($user_id, 'custentity_emergencycontactphonenumber', true);
        $emergence_crelationship = get_user_meta($user_id, 'custentity_emergencycontactrelationship', true);
        $medicalconditions = get_user_meta($user_id, 'custentity_medicalconditions', true);
        $medications = get_user_meta($user_id, 'custentity_medications', true);
        $allergies = get_user_meta($user_id, 'custentity_allergies', true);
        $dietaryrestrictions = get_user_meta($user_id, 'custentity_dietaryrestrictions', true);
        $no_of_guests = $checkout_data['no_of_guests'];
        $shipping_address_1 = $checkout_data['shipping_address_1'];
        $shipping_address_2 = $checkout_data['shipping_address_2'];
        $shipping_country = $checkout_data['shipping_country'];
        $shipping_state = $checkout_data['shipping_state'];
        $shipping_city = $checkout_data['shipping_city'];
        $shipping_postcode = $checkout_data['shipping_postcode'];
        $occupants = $checkout_data['occupants'];
        $promoCode = $checkout_data['coupon_code'];
        // Check if the order contains an already applied coupon and fix the missing coupon code.
        if( count( $order->get_coupon_codes() ) > 0  && empty( $promoCode ) ) {
            // There are coupons.
            $applied_coupons = $order->get_coupon_codes();
            $promoCode       = $applied_coupons[0];
        }
        $wantPrivate = isset($occupants['private']) && $occupants['private'] > 0 ? true : false;
        $bikeUpgradePrice = isset( $checkout_data['bikeUpgradePrice'] ) ? $checkout_data['bikeUpgradePrice'] : '';
        $singleSupplementPrice = isset( $checkout_data['singleSupplementPrice'] ) ? $checkout_data['singleSupplementPrice'] : '';
        $special_needs = isset( $checkout_data['special_needs'] ) ? $checkout_data['special_needs'] : '';
        //Trim the special needs to 250 characters max
        if (strlen($special_needs) > 250) {
            $special_needs = substr($special_needs, 0, 250);
        }
        $trek_guest_insurance = isset( $checkout_data['trek_guest_insurance'] ) ? $checkout_data['trek_guest_insurance'] : [];
        $product = wc_get_product( $product_id );
        $trip_name = $trip_sdate = $trip_edate = $trip_sku = '';
        if( isset($checkout_data['is_saved_billing']) && $checkout_data['is_saved_billing'] == 1 ){
            $billing_address_1 = $checkout_data['billing_address_1'];
            $billing_address_2 = $checkout_data['billing_address_2'];
            $billing_country = $checkout_data['billing_country'];
            $billing_state = $checkout_data['billing_state'];
            $billing_city = $checkout_data['billing_city'];
            $billing_postcode = $checkout_data['billing_postcode'];
            $billing_data = array(
                'billing_address_1'          => $billing_address_1,
                'billing_address_2'      => $billing_address_2,
                'billing_country'         => $billing_country,
                'billing_state'         => $billing_state,
                'billing_city'         => $billing_city,
                'billing_postcode'         => $billing_postcode
            );
            foreach ($billing_data as $billing_key => $billing_value ) {
                update_user_meta( $user_id, $billing_key, $billing_value );
            }
        }
        if( isset( $checkout_data['shipping_phone'] ) && ! empty( $checkout_data['shipping_phone'] ) ) {
            // Save guest_phone as billing_phone to prevent overwriting the primary phone from TM NetSuite plugin.
            update_user_meta( $user_id, 'custentity_phone_number', $checkout_data['shipping_phone'] );
            update_user_meta( $user_id, 'billing_phone', $checkout_data['shipping_phone'] );
            update_user_meta( $user_id, 'shipping_phone', $checkout_data['shipping_phone'] );
        }
        if( $product ){
            $trip_status = tt_get_custom_product_tax_value( $product_id, 'trip-status', true );
            $trip_sdate = $product->get_attribute( 'pa_start-date' ); 
            $trip_edate = $product->get_attribute( 'pa_end-date' );
            $trip_name = $product->get_name();
            $trip_sku = $product->get_sku();
            $sdate_obj = explode('/', $trip_sdate);
            $sdate_info = array(
                'd' => $sdate_obj[0],
                'm' => $sdate_obj[1],
                'y' => substr(date('Y'),0,2).$sdate_obj[2]
            );
            $edate_obj = explode('/', $trip_edate);
            $edate_info = array(
                'd' => $edate_obj[0],
                'm' => $edate_obj[1],
                'y' => substr(date('Y'),0,2).$edate_obj[2]
            );
        }
        tt_add_error_log('Before: BOOKING_TABLE', ['1' => $checkout_data, '2' => $formatted_checkout_data ], $checkout_data);
        if( $formatted_checkout_data && isset($formatted_checkout_data[1]) ){
            foreach( $formatted_checkout_data[1]['cart_item_data'] as $iter_key=>$formatted_Data){
                $is_primary        = ( $iter_key === 0 ? true : false );
                $insured_emails    = ( $formatted_checkout_data[2]['cart_item_data']['guest_email'] ? $formatted_checkout_data[2]['cart_item_data']['guest_email'] : array() );
                $wantsInsurance    = false;
                $bikeId            = '';
                $bike_type_name    = '';
                $isBikeUpgrade     = false;
                $own_bike_id       = 5270;
                $non_rider_bike_id = 5257;
                if ( $iter_key == 0 ) {
                    $bike_type_id    = $checkout_data['bike_gears']['primary']['bikeTypeId'];
                    $bikeId          = $checkout_data['bike_gears']['primary']['bikeId'];
                    if ( $bike_type_id ) {
                        $bikeTypeInfo = tt_ns_get_bike_type_info( $bike_type_id );
                        if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
                            if ( ! in_array( (int) $bikeId, array( $own_bike_id, $non_rider_bike_id ) ) ) {
                                // Bike upgrade is not applicable for non-rider and bring own bike.
                                $isBikeUpgrade = true;
                            }
                        }
                        $bike_type_name = tt_ns_get_bike_type_name( $bike_type_id );
                    }
                    $bike_selection  = $checkout_data['bike_gears']['primary']['bike'];
                    $bike_size       = $checkout_data['bike_gears']['primary']['bike_size'];
                    $rider_height    = $checkout_data['bike_gears']['primary']['rider_height'];
                    $bike_pedal      = $checkout_data['bike_gears']['primary']['bike_pedal'];
                    $rider_level     = $checkout_data['bike_gears']['primary']['rider_level'];
                    $activity_level  = tt_validate( $checkout_data['bike_gears']['primary']['activity_level'] );
                    $jersey_style    = $checkout_data['bike_gears']['primary']['jersey_style'];
                    $jersey_size     = $checkout_data['bike_gears']['primary']['jersey_size'];
                    $tshirt_size     = tt_validate( $checkout_data['bike_gears']['primary']['tshirt_size'] );
                    $helmet_size     = $checkout_data['bike_gears']['primary']['helmet_size'];
                    $insuranceAmount = isset($trek_guest_insurance['primary']['basePremium']) ? $trek_guest_insurance['primary']['basePremium'] : 0;
                    $wantsInsurance  = isset($trek_guest_insurance['primary']['is_travel_protection']) && $trek_guest_insurance['primary']['is_travel_protection'] == true ? true : false;
                    $bike_comments   = ! empty( tt_validate( $checkout_data['bike_gears']['primary']['transportation_options'] ) ) || ! empty( tt_validate( $checkout_data['bike_gears']['primary']['type_of_bike'] ) ) ?  tt_validate( $checkout_data['bike_gears']['primary']['transportation_options'] ) . '; ' . tt_validate( $checkout_data['bike_gears']['primary']['type_of_bike'] ) : '';
                } else {
                    $bike_type_id = $checkout_data['bike_gears']['guests'][$iter_key]['bikeTypeId'];
                    $bikeId       = $checkout_data['bike_gears']['guests'][$iter_key]['bikeId'];
                    if ( $bike_type_id ) {
                        $bikeTypeInfo = tt_ns_get_bike_type_info($bike_type_id);
                        if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
                            if ( ! in_array( (int) $bikeId, array( $own_bike_id, $non_rider_bike_id ) ) ) {
                                // Bike upgrade is not applicable for non-rider and bring own bike.
                                $isBikeUpgrade = true;
                            }
                        }
                        $bike_type_name = tt_ns_get_bike_type_name( $bike_type_id );
                    }
                    $bike_selection  = $checkout_data['bike_gears']['guests'][$iter_key]['bike'];
                    $bike_size       = $checkout_data['bike_gears']['guests'][$iter_key]['bike_size'];
                    $rider_height    = $checkout_data['bike_gears']['guests'][$iter_key]['rider_height'];
                    $bike_pedal      = $checkout_data['bike_gears']['guests'][$iter_key]['bike_pedal'];
                    $rider_level     = $checkout_data['bike_gears']['guests'][$iter_key]['rider_level'];
                    $activity_level  = tt_validate( $checkout_data['bike_gears']['guests'][$iter_key]['activity_level'] );
                    $jersey_style    = $checkout_data['bike_gears']['guests'][$iter_key]['jersey_style'];
                    $jersey_size     = $checkout_data['bike_gears']['guests'][$iter_key]['jersey_size'];
                    $tshirt_size     = tt_validate( $checkout_data['bike_gears']['guests'][$iter_key]['tshirt_size'] );
                    $helmet_size     = $checkout_data['bike_gears']['guests'][$iter_key]['helmet_size'];
                    $insuranceAmount = isset($trek_guest_insurance['guests'][$iter_key]['basePremium']) ? $trek_guest_insurance['guests'][$iter_key]['basePremium'] : 0;
                    $wantsInsurance  = isset($trek_guest_insurance['guests'][$iter_key]['is_travel_protection']) && $trek_guest_insurance['guests'][$iter_key]['is_travel_protection'] == true ? true : false;
                    $bike_comments   = ! empty( tt_validate( $checkout_data['bike_gears']['guests'][$iter_key]['transportation_options'] ) ) || ! empty( tt_validate( $checkout_data['bike_gears']['guests'][$iter_key]['type_of_bike'] ) ) ?  tt_validate( $checkout_data['bike_gears']['guests'][$iter_key]['transportation_options'] ) . '; ' . tt_validate( $checkout_data['bike_gears']['guests'][$iter_key]['type_of_bike'] ) : '';
                }
                $tt_trip_user_emails[] = isset($formatted_Data['guest_email']) ? $formatted_Data['guest_email'] : '';
                $bookingData = array(
                    'netsuite_guest_registration_id' => ( $iter_key === 0 ? $ns_user_id : '' ),
                    'user_id'                        => ( $iter_key === 0 ? $user_id : '' ),
                    'guest_index_id'                 => $iter_key,
                    'order_id'                       => $order_id,
                    'product_id'                     => $product_id,
                    'guest_is_primary'               => $is_primary,
                    'guest_first_name'               => $formatted_Data['guest_fname'],
                    'guest_last_name'                => $formatted_Data['guest_lname'],
                    'guest_email_address'            => ( $iter_key === 0 ? $currentUser_email : $formatted_Data['guest_email'] ),
                    'guest_phone_number'             => $formatted_Data['guest_phone'],
                    'guest_gender'                   => $formatted_Data['guest_gender'],
                    'guest_date_of_birth'            => $formatted_Data['guest_dob'],
                    'rider_height'                   => $rider_height,
                    'rider_level'                    => $rider_level,
                    'activity_level'                 => $activity_level,
                    'bike_type_id'                   => $bike_type_id,
                    'bikeTypeName'                   => $bike_type_name,
                    'bike_size'                      => $bike_size,
                    'bike_id'                        => $bikeId,
                    'bike_selection'                 => $bike_selection,
                    'pedal_selection'                => $bike_pedal,
                    'tt_jersey_size'                 => $jersey_size,
                    'tshirt_size'                    => $tshirt_size,
                    'jersey_style'                   => $jersey_style,
                    'helmet_selection'               => $helmet_size,
                    'emergency_contact_first_name'   => ( $iter_key === 0 ? $emergence_cfname : '' ),
                    'emergency_contact_last_name'    => ( $iter_key === 0 ? $emergence_clname : '' ),
                    'emergency_contact_phone'        => ( $iter_key === 0 ? $emergence_cphone : '' ),
                    'emergency_contact_relationship' => ( $iter_key === 0 ? $emergence_crelationship : '' ),
                    'medical_conditions'             => ( $iter_key === 0 ? $medicalconditions : '' ),
                    'medications'                    => ( $iter_key === 0 ? $medications : '' ),
                    'allergies'                      => ( $iter_key === 0 ? $allergies : '' ),
                    'dietary_restrictions'           => ( $iter_key === 0 ? $dietaryrestrictions : '' ),
                    'trip_number_of_guests'          => $no_of_guests,
                    'shipping_address_1'             => $shipping_address_1,
                    'shipping_address_2'             => $shipping_address_2,
                    'shipping_address_city'          => $shipping_city,
                    'shipping_address_state'         => $shipping_state,
                    'shipping_address_country'       => $shipping_country,
                    'shipping_address_zipcode'       => $shipping_postcode,
                    'trip_start_date'                => strtotime(implode('-', $sdate_info)),
                    'trip_end_date'                  => strtotime(implode('-', $edate_info)),
                    'trip_name'                      => $trip_name,
                    'trip_code'                      => $trip_sku,
                    'wantsInsurance'                 => $wantsInsurance,
                    'insuranceAmount'                => $insuranceAmount,
                    'promoCode'                      => $promoCode,
                    'wantPrivate'                    => $wantPrivate,
                    'bikeUpgradePriceDisplayed'      => $bikeUpgradePrice,
                    'specialRoomRequests'            => $special_needs,
                    'bike_comments'                  => $bike_comments,
                    'trip_room_selection'            => json_encode($occupants),
                    'isBikeUpgrade'                  => $isBikeUpgrade,
                    'waiver_signed'                  => ( $iter_key === 0 ? 1 : 0 ),
                );
                $insert_booking = $wpdb->insert($table_name, $bookingData);
                tt_add_error_log('BOOKING_TABLE', ['last_error' => $wpdb->last_error, 'last_query' => $wpdb->last_query ], $bookingData);
            }
        }
        if( $insert_booking && $order_id ){
            tt_add_error_log('[Start] - NS Trip Booking', [$order_id], ['dateTime' => date('Y-m-d H:i:s')]);
            as_enqueue_async_action('tt_trigger_cron_ns_booking', array( $order_id, null, $is_behalf ), '[Sync] - NetSuite Trip');
            do_action( 'tt_set_ns_booking_status', $order_id, 'booking_pending' );
        } else {
            // There is no Order ID, or insertion in the bookings table failed.
            do_action( 'tt_set_ns_booking_status', $order_id, 'booking_onhold' );
        }
        update_post_meta($order_id, 'tt_wc_order_ns_status', 'true');
        update_post_meta($order_id, 'tt_wc_order_ns_is_behalf', $is_behalf);
    }
    if( ! $custom_user ){
        WC()->cart->empty_cart();
    }
}

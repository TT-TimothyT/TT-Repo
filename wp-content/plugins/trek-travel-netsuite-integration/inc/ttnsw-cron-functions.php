<?php

use TTNetSuite\NetSuiteClient;

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get Trips last modified After
 **/
if (!function_exists('tt_get_last_modified_trip_ids')) {
    function tt_get_last_modified_trip_ids( $time_range = DEFAULT_TIME_RANGE ){
        $trips_ids = [];
       
        // $current_year = $time_range;
        $netSuiteClient = new NetSuiteClient();
        $trek_script_args = array(
           'modifiedAfter' => date('Y-m-d\TH:i:s', strtotime($time_range))
        );
        $trek_trips = $netSuiteClient->get(TRIPS_SCRIPT_ID, $trek_script_args);
        if ($trek_trips && !empty($trek_trips)) {
            foreach ($trek_trips as $trek_trip) {
                $tripId = $trek_trip->tripId;
                $trips_ids[] = $tripId;
            }
        }
        return $trips_ids;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : NS Trips sync function
 **/

if (!function_exists('tt_sync_ns_trips')) {
    function tt_sync_ns_trips( $time_range = DEFAULT_TIME_RANGE )
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'netsuite_trips';
        $netSuiteClient = new NetSuiteClient();
        //$trek_script_args = array('tripYear' => intval($current_year)); //modified_at
        $trek_script_args = array(
           'modifiedAfter' => date('Y-m-d\TH:i:s', strtotime($time_range))
        );
        $trek_trips = $netSuiteClient->get(TRIPS_SCRIPT_ID, $trek_script_args);

        //$trek_trips = $netSuiteClient->get(TRIPS_SCRIPT_ID, array('itineraryCode' => 'AC' ));
        if ($trek_trips && !empty($trek_trips)) {
            foreach ($trek_trips as $trek_trip) {
                $tripId = $trek_trip->tripId;
                $tripCode = $trek_trip->tripCode;
                $tripsData = array(
                    'tripName' => $trek_trip->tripName,
                    'itineraryId' => $trek_trip->itineraryId,
                    'itineraryCode' => $trek_trip->itineraryCode,
                    'lastModifiedDate' => $trek_trip->lastModifiedDate
                );
                $check_trip = tt_get_trip_by_idCode($table_name, $tripId, $tripCode);
                if ($check_trip >= 1) {
                    $where = array('tripId' => $tripId, 'tripCode' => $tripCode);
                    $wpdb->update($table_name, $tripsData, $where);
                } else {
                    $tripsData['tripId'] = $tripId;
                    $tripsData['tripCode'] = $tripCode;
                    $inserted_id = $wpdb->insert($table_name, $tripsData);
                }
                //echo 'executed 1'; exit;
            }
        }
        // if( $trek_trips ){
        //     tt_add_error_log('NS_SCRIPT_ID: '.TRIPS_SCRIPT_ID, $trek_script_args, ['status'=>'true']);
        // }else{
        //     tt_add_error_log('NS_SCRIPT_ID: '.TRIPS_SCRIPT_ID, $trek_script_args, $trek_trips);
        // }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : NS Trip Details sync function
 **/
if (!function_exists('tt_sync_ns_trip_details')) {
    function tt_sync_ns_trip_details( $time_range = DEFAULT_TIME_RANGE )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'netsuite_trip_detail';
        $netSuiteClient = new NetSuiteClient();
        //$trek_trips = tt_get_local_trips();
        $last_modified_trip_ids = tt_get_last_modified_trip_ids( $time_range );
        $trip_Ids = tt_get_local_trip_ids($last_modified_trip_ids);
        if ($trip_Ids && !empty($trip_Ids)) {
            foreach ($trip_Ids as $trip_Ids_arr) {
                $trek_script_args = array('tripIds' => implode(',', $trip_Ids_arr) );
                $trek_trip_details = $netSuiteClient->get(TRIP_DETAIL_SCRIPT_ID, $trek_script_args);
                // if( $trek_trip_details ){
                //     tt_add_error_log('NS_SCRIPT_ID: '.TRIP_DETAIL_SCRIPT_ID, $trek_script_args, ['status' => 'true']);
                // }else{
                //     tt_add_error_log('NS_SCRIPT_ID: '.TRIP_DETAIL_SCRIPT_ID, $trek_script_args, $trek_trip_details);
                // }
                if (!empty($trek_trip_details) && isset($trek_trip_details->trips)) {
                    foreach ($trek_trip_details->trips as $trek_trip_detail) {
                        $tripCode = $trek_trip_detail->tripCode;
                        $tripsData = array(
                            'isRideCamp' => $trek_trip_detail->isRideCamp,
                            'isLateDepositAllowed' => $trek_trip_detail->isLateDepositAllowed,
                            'depositBeforeDate' => $trek_trip_detail->depositBeforeDate,
                            'removeFromStella' => $trek_trip_detail->removeFromStella,
                            'capacity' => $trek_trip_detail->capacity,
                            'booked' => $trek_trip_detail->booked,
                            'remaining' => $trek_trip_detail->remaining,
                            'status' => json_encode($trek_trip_detail->status),
                            'startDate' => $trek_trip_detail->startDate,
                            'endDate' => $trek_trip_detail->endDate,
                            'daysToTrip' => $trek_trip_detail->daysToTrip,
                            'tripYear' => $trek_trip_detail->tripYear,
                            'tripSeason' => $trek_trip_detail->tripSeason,
                            'tripMonth' => $trek_trip_detail->tripMonth,
                            'tripContinent' => $trek_trip_detail->tripContinent,
                            'tripCountry' => $trek_trip_detail->tripCountry,
                            'tripRegion' => $trek_trip_detail->tripRegion,
                            'itineraryId' => $trek_trip_detail->itineraryId,
                            'itineraryCode' => $trek_trip_detail->itineraryCode,
                            'lastModifiedDate' => $trek_trip_detail->lastModifiedDate,
                            'riderType' => json_encode($trek_trip_detail->riderType),
                            'basePrice' => $trek_trip_detail->basePrice,
                            'singleSupplementPrice' => $trek_trip_detail->singleSupplementPrice,
                            'depositAmount' => $trek_trip_detail->depositAmount,
                            'bikeUpgradePrice' => $trek_trip_detail->bikeUpgradePrice,
                            'insurancePercentage' => $trek_trip_detail->insurancePercentage,
                            'taxRate' => $trek_trip_detail->taxRate,
                            'ssSoldOut' => $trek_trip_detail->ssSoldOut,
                            'isOpenToRoommateDisabled' => $trek_trip_detail->isOpenToRoommateDisabled,
                            'bookablePeriods' => json_encode($trek_trip_detail->bookablePeriods),
                            'hotels' => json_encode($trek_trip_detail->hotels),
                            'bikes' => json_encode($trek_trip_detail->bikes),
                            'addOns' => json_encode($trek_trip_detail->addOns),
                            'tripSpecificMessage' => $trek_trip_detail->tripSpecificMessage
                        );
                        $check_trip = tt_get_trip_by_idCode($table_name, $trek_trip_detail->tripId, $trek_trip_detail->tripCode);
                        if ($check_trip >= 1) {
                            $where = array('tripId' => $trek_trip_detail->tripId, 'tripCode' => $tripCode);
                            $wpdb->update($table_name, $tripsData, $where);
                        } else {
                            $tripsData['tripId'] = $trek_trip_detail->tripId;
                            $tripsData['tripCode'] = $tripCode;
                            $inserted_id = $wpdb->insert($table_name, $tripsData);
                        }
                    }
                }
            }
        }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : NS Trip Hotels sync function
 **/
if (!function_exists('tt_sync_ns_trip_hotels')) {
    function tt_sync_ns_trip_hotels( $time_range = DEFAULT_TIME_RANGE )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'netsuite_trip_hotels';
        $last_modified_trip_ids = tt_get_last_modified_trip_ids( $time_range );
        $trek_trips = tt_get_local_trips( $last_modified_trip_ids );
        tt_add_error_log('[Hotels Sync]: '.TRIPS_SCRIPT_ID, 'START Hotels Sync for ' . $time_range, '$last_modified_trip_ids-> ' . count($last_modified_trip_ids));
        if ($trek_trips && !empty($trek_trips)) {
            foreach ($trek_trips as $trek_trip) {
                $tripId = $trek_trip->tripId;
                $tripCode = $trek_trip->tripCode;
                $trek_trip_details = tt_get_local_trips_detail('hotels', $tripId, $tripCode);
                if (!empty($trek_trip_details) && isset($trek_trip_details[0]->hotels)) {
                    $hotels = json_decode($trek_trip_details[0]->hotels);
                    if ($hotels) {
                        foreach ($hotels as $hotel) {
                            $tripsData = array(
                                'hotelId' => $hotel->hotelId,
                                'hotelName' => $hotel->hotelName,
                                'rooms' => json_encode($hotel->rooms),
                            );
                            $check_trip = tt_get_field_by_ID($table_name, 'hotelId', $hotel->hotelId, $tripId);
                            if ($check_trip >= 1) {
                                $where = array('hotelId' => $hotel->hotelId, 'tripId' => $tripId, 'tripCode' => $tripCode);
                                $wpdb->update($table_name, $tripsData, $where);
                            } else {
                                $tripsData['tripId'] = $tripId;
                                $tripsData['tripCode'] = $tripCode;
                                $inserted_id = $wpdb->insert($table_name, $tripsData);
                            }
                        }
                    }
                }
            }
        }
        tt_add_error_log('[Hotels Sync]: '.TRIPS_SCRIPT_ID, 'END Hotels Sync', '$last_modified_trip_ids-> ' . count($last_modified_trip_ids));
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : NS Trip bikes sync function
 **/
if (!function_exists('tt_sync_ns_trip_bikes')) {
    function tt_sync_ns_trip_bikes( $time_range = DEFAULT_TIME_RANGE )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'netsuite_trip_bikes';
        $last_modified_trip_ids = tt_get_last_modified_trip_ids( $time_range );
        $trek_trips_bulk = tt_get_local_trips( $last_modified_trip_ids, true );
        tt_add_error_log('[Bikes Sync]: '.TRIPS_SCRIPT_ID, 'START Bikes Sync for ' . $time_range, '$last_modified_trip_ids-> ' . count($last_modified_trip_ids));
        if( $trek_trips_bulk && !empty( $trek_trips_bulk ) ) {
            foreach ( $trek_trips_bulk as $trek_trips ) {
                if ($trek_trips && !empty($trek_trips)) {
                    foreach ($trek_trips as $trek_trip) {
                        $tripId = $trek_trip->tripId;
                        $tripCode = $trek_trip->tripCode;
                        $trek_trip_details = tt_get_local_trips_detail('bikes', $tripId, $tripCode);
                        if (!empty($trek_trip_details) && isset($trek_trip_details[0]->bikes)) {
                            $bikes = json_decode($trek_trip_details[0]->bikes);
                            if ($bikes) {
                                foreach ($bikes as $bike) {
                                    $tripsData = array(
                                        'bikeId' => $bike->bikeId,
                                        'bikeDescr' => $bike->bikeDescr,
                                        'bikeType' => json_encode($bike->bikeType),
                                        'bikeSize' => json_encode($bike->bikeSize),
                                        'bikeModel' => json_encode($bike->bikeModel),
                                        'total' => $bike->total,
                                        'allocated' => $bike->allocated,
                                        'available' => $bike->available,
                                    );
                                    $check_trip = tt_get_field_by_ID($table_name, 'bikeId', $bike->bikeId, $tripId);
                                    if ($check_trip >= 1) {
                                        $where = array('bikeId' => $bike->bikeId, 'tripId' => $tripId, 'tripCode' => $tripCode);
                                        $wpdb->update($table_name, $tripsData, $where);
                                    } else {
                                        $tripsData['tripId'] = $tripId;
                                        $tripsData['tripCode'] = $tripCode;
                                        $inserted_id = $wpdb->insert($table_name, $tripsData);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        tt_add_error_log('[Bikes Sync]: '.TRIPS_SCRIPT_ID, 'END Bikes Sync', '$last_modified_trip_ids-> ' . count($last_modified_trip_ids));
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : NS Trips Addons sync function
 **/
if (!function_exists('tt_sync_ns_trip_addons')) {
    function tt_sync_ns_trip_addons( $time_range = DEFAULT_TIME_RANGE )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'netsuite_trip_addons';
        $last_modified_trip_ids = tt_get_last_modified_trip_ids( $time_range );
        $trek_trips = tt_get_local_trips( $last_modified_trip_ids );
        tt_add_error_log('[Addons Sync]: '.TRIPS_SCRIPT_ID, 'START Addons Sync for ' . $time_range, '$last_modified_trip_ids->' . count($last_modified_trip_ids));
        if ($trek_trips && !empty($trek_trips)) {
            foreach ($trek_trips as $trek_trip) {
                $tripId = $trek_trip->tripId;
                $tripCode = $trek_trip->tripCode;
                $trek_trip_details = tt_get_local_trips_detail('addOns', $tripId, $tripCode);
                if (!empty($trek_trip_details) && isset($trek_trip_details[0]->addOns)) {
                    $addOns = json_decode($trek_trip_details[0]->addOns);
                    if ($addOns) {
                        foreach ($addOns as $addOn) {
                            $tripsData = array(
                                'itemId' => $addOn->itemId,
                                'itemDescr' => $addOn->itemDescr,
                                'itemBasePrice' => $addOn->itemBasePrice,
                                'total' => $addOn->total,
                                'allocated' => $addOn->allocated,
                                'available' => $addOn->available,
                            );
                            $check_trip = tt_get_field_by_ID($table_name, 'itemId', $addOn->itemId, $tripId);
                            if ($check_trip >= 1) {
                                $where = array('itemId' =>  $addOn->itemId);
                                $wpdb->update($table_name, $tripsData, $where);
                            } else {
                                $tripsData['tripId'] = $tripId;
                                $tripsData['tripCode'] = $tripCode;
                                $inserted_id = $wpdb->insert($table_name, $tripsData);
                            }
                        }
                    }
                }
            }
        }
        tt_add_error_log('[Addons Sync]: '.TRIPS_SCRIPT_ID, 'END Addons Sync', '$last_modified_trip_ids->' . count($last_modified_trip_ids));
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get WC Child product Ids
 **/
if (!function_exists('tt_get_wc_child_product_ids')) {
    function tt_get_wc_child_product_ids()
    {
        $linked_products_ids = $linked_products = array();
        global $wpdb;
        $product_res = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_children' AND meta_value !='a:0:{}' ", ARRAY_A);
        $product_res = array_column($product_res, 'post_id');
        if ($product_res) {
            foreach ($product_res as $product_id) {
                $linked_products = get_post_meta($product_id, '_children', true);
                if ($linked_products && is_array($linked_products)) {
                    $linked_products_ids = array_merge($linked_products_ids, $linked_products);
                }
            }
        }
        return array_unique($linked_products_ids);
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : TT get WC trips Tripcode/SKU
 **/
if (!function_exists('tt_get_wc_postId_Tripcode')) {
    function tt_get_wc_postId_by_sku($tripCode)
    {
        global $wpdb;
        //tt_meta_ns_trip_code
        $table_name = $wpdb->prefix . 'postmeta';
        $sql = "SELECT post_id from {$table_name} where meta_key='_sku' AND meta_value='{$tripCode}' ";
        $result = $wpdb->get_results($sql);
        $product_id = 0;
        if ($result) {
            $product_id = $result[0]->post_id;
        }
        return $product_id;
    }
}

/**
 * Create or update a WooComerce product for given NetSuite Trip.
 * Or for shorter, this is a Product Sync Function.
 * 
 * @param object(stdClass) $trek_trip A single trip from NS. 
 */
function tt_sync_wc_product_from_ns( $trek_trip ) {
    $tripCode = $trek_trip->tripCode;
    $tripId = $trek_trip->tripId;
    $capacity = $trek_trip->capacity;
    $basePrice = $trek_trip->basePrice;
    $product_id = tt_get_wc_postId_by_sku($tripCode);
    $product_attr = get_post_meta($product_id, '_product_attributes', true);
    $product_attr = ($product_attr ? $product_attr : array());
    //Begin: Data preparation
    $product_data_args = array(
        'post_title' => $trek_trip->tripName,
        'post_type' => 'product'
    );
    $meta_fields = array(
        'tripId' => $tripId,
        'itineraryId' => $trek_trip->itineraryId,
        'itineraryCode' => $trek_trip->itineraryCode,
        'singleSupplementPrice' => $trek_trip->singleSupplementPrice,
        'depositAmount' => $trek_trip->depositAmount,
        'bikeUpgradePrice' => $trek_trip->bikeUpgradePrice,
        'insurancePercentage' => $trek_trip->insurancePercentage,
        'taxRate' => $trek_trip->taxRate,
        'isPassportRequired' => $trek_trip->isPassportRequired,
        'tripSpecificMessage' => $trek_trip->tripSpecificMessage
    );
    $attr_startDate = ($trek_trip->startDate ? strtotime($trek_trip->startDate) : '');
    $attr_endDate = ($trek_trip->endDate ? strtotime($trek_trip->endDate) : '');
    $status = isset($trek_trip->status) && $trek_trip->status ? $trek_trip->status : '';
    $riderType = isset($trek_trip->riderType) ? $trek_trip->riderType : '';
    $daysToTrip = ($trek_trip->daysToTrip ? $trek_trip->daysToTrip : '');
    $tripYear = ($trek_trip->tripYear ? $trek_trip->tripYear : '');
    $tripSeason = ($trek_trip->tripSeason ? $trek_trip->tripSeason : '');
    $tripMonth = ($trek_trip->tripMonth ? $trek_trip->tripMonth : '');
    $tripContinent = ($trek_trip->tripContinent ? $trek_trip->tripContinent : '');
    $tripCountry = ($trek_trip->tripCountry ? $trek_trip->tripCountry : '');
    $tripRegion = ($trek_trip->tripRegion ? $trek_trip->tripRegion : '');
    //End: Data preparation
    $product = wc_get_product( $product_id );
    // $parent_product_id = tt_get_parent_trip_id_by_child_sku($tripCode);
    //if ($product && in_array($product_id, $wc_child_product_ids)) {
    if ($product) {    
        //update existing product data with new data
        // if ($parent_product_id) {
        //     $product_data_args['post_parent'] = $parent_product_id;
        // }
        $product_data_args['ID'] = $product_id;
        $is_updated = wp_update_post($product_data_args);
        if ($is_updated && $meta_fields) {
            foreach ($meta_fields as $meta_id => $meta_value) {
                $meta_key = TT_WC_META_PREFIX . $meta_id;
                update_post_meta($product_id, $meta_key, $meta_value);
            }
        }
    } else {
        //insert new product in WC product
        // if ($parent_product_id) {
        //     $product_data_args['post_parent'] = $parent_product_id;
        // }
        $product_data_args['post_status'] = 'publish';
        $new_product_id = wp_insert_post($product_data_args);
        $product_id = $new_product_id;
        update_post_meta($product_id, '_sku', $trek_trip->tripCode);
    }
    if ($product_id) {
        if ($meta_fields) {
            foreach ($meta_fields as $meta_id => $meta_value) {
                $meta_key = TT_WC_META_PREFIX . $meta_id;
                update_post_meta($product_id, $meta_key, $meta_value);
            }
        }
        $has_sku = get_post_meta($product_id, '_sku', true);
        if( empty($has_sku) ){
            update_post_meta($product_id, '_sku', $trek_trip->tripCode);
        }
        if( $parent_p_ID ){
            $new_child_ids = [$product_id];
            $get_children_ids = get_post_meta($parent_p_ID, '_children', true);
            if( $get_children_ids){
                $new_child_ids = array_merge($get_children_ids, $new_child_ids);
            }
            $new_child_ids = array_unique($new_child_ids);
        }
        //start saved attribute code
        if( is_object($riderType) ){
            $riderLevelVal = tt_get_custom_item_name('syncRiderLevels',$riderType->id);
        }
        $custom_attributes = [
            'pa_trip-status' => is_object($status) ? $status->name : '',
            'pa_rider-level' => $riderLevelVal,
            'pa_duration' => $daysToTrip,
            'pa_region' => $tripRegion,
            'pa_continent' => $tripContinent,
            'pa_season' => $tripSeason,
            'pa_country' => $tripCountry,
            'pa_start-date' => date('d/m/y', $attr_startDate),
            'pa_end-date' => date('d/m/y', $attr_endDate)
        ];
        if( $custom_attributes ){
            foreach($custom_attributes as $custom_attribute_k=>$custom_attribute_v){
                if( $custom_attribute_v ){
                    tt_set_wc_attribute_value($product_id, $custom_attribute_k, $custom_attribute_v);
                }
            }
        }
        update_post_meta($product_id, '_manage_stock', 'yes');
        update_post_meta($product_id, '_stock', $capacity);
        update_post_meta($product_id, '_regular_price', $basePrice);
        update_post_meta($product_id, '_price', $basePrice);
        //end saved attribute code
        update_post_meta($product_id, 'ns_last_synced_date_time', date('Y-m-d H:i:s'));
    }
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : NS Trips/Hotels/Bike/Rooms sync function
 **/
if (!function_exists('tt_sync_wc_products_from_ns')) {
    function tt_sync_wc_products_from_ns($is_all=false, $sync_trip_Ids=array(), $time_range = DEFAULT_TIME_RANGE)
    {
        $netSuiteClient = new NetSuiteClient();
        $trip_sync_type_msg = '[Trip Sync]: '.TRIP_DETAIL_SCRIPT_ID;

        //Check if we are syncing a single trip or a bunch of such
        if( $sync_trip_Ids && is_array($sync_trip_Ids) && !empty($sync_trip_Ids) ){
            $trip_Ids = $sync_trip_Ids;
            $trip_Ids = array_chunk($trip_Ids, 10);
            $trip_sync_type_msg = '[Single Trip Sync]: '.TRIP_DETAIL_SCRIPT_ID;
        } else {
            $last_modified_trip_ids = tt_get_last_modified_trip_ids( $time_range );
            if ( $is_all == true ){
                $trip_Ids = tt_get_local_trip_ids();
            } else {
                $trip_Ids = tt_get_local_trip_ids( $last_modified_trip_ids );
            }
        }

        //If the trip code is found locally in netsuite_trips proceed.
        if( $trip_Ids ){
            /*if(count($trip_Ids) > 10 ){
                $trip_Ids = array_chunk($trip_Ids, 10);
            }*/
            if ( ! empty( $trip_Ids ) ) {
                foreach ($trip_Ids as $trip_Ids_arr) {
                    if( is_int($trip_Ids_arr) && !is_array($trip_Ids_arr) ){
                        $trip_Ids_arr = [$trip_Ids];
                    }
                    $trek_script_args = array('tripIds' => implode(',', $trip_Ids_arr) );
                    $trek_trips = $netSuiteClient->get(TRIP_DETAIL_SCRIPT_ID, $trek_script_args);
                    if( $trek_trips ){
                        tt_add_error_log($trip_sync_type_msg, $trek_script_args, ['status'=>'true']);
                    }else{
                        tt_add_error_log($trip_sync_type_msg, $trek_script_args, $trek_trips);
                    }
                    if (!empty($trek_trips) && isset($trek_trips->trips)) {
                        $ride_camp_trips = array();
                        foreach ( $trek_trips->trips as $trek_trip ) {
                            // Set is Ride Camp flag.
                            $is_ride_camp = ( $trek_trip->isRideCamp ? $trek_trip->isRideCamp : '' );

                            if( !empty( $is_ride_camp ) && $is_ride_camp ) {
                                // This is a Ride Camp trip, store it into array for laiter usage.
                                array_push( $ride_camp_trips, $trek_trip );
                            }
                            // Continue as normal.
                            tt_sync_wc_product_from_ns( $trek_trip );
                        }
                        if( !empty( $ride_camp_trips ) ) {
                            // Have a Ride Camp Trips.
                            foreach( $ride_camp_trips as $trek_trip ) {

                                // Take the info for the additional trips that need to be made.
                                $bookable_periods = $trek_trip->bookablePeriods;

                                $main_start_date  = $trek_trip->startDate;
                                $main_end_date    = $trek_trip->endDate;

                                // Keep SKU base.
                                $main_trip_code   = $trek_trip->tripCode;
                                $main_trip_name   = $trek_trip->tripName;

                                foreach( $bookable_periods as $period ) {
                                    $start_date = $period->startDate;
                                    $end_date   = $period->endDate;

                                    if( $start_date === $main_start_date && $end_date === $main_end_date ) {
                                        // This is the main trip product, that we already have into WC.
                                        continue;
                                    }

                                    if( $start_date === $main_start_date && $end_date !== $main_end_date ) {
                                        // This is the FIRST child product, add a suffix to the SKU.
                                        $trek_trip->tripCode = $main_trip_code . '-FIRST';
                                        // Add suffix on the name.
                                        $trek_trip->tripName = $main_trip_name . '-FIRST';

                                    } elseif ( $start_date !== $main_start_date && $end_date === $main_end_date ) {
                                        // This is the SECOND child product, add a suffix to the SKU.
                                        $trek_trip->tripCode = $main_trip_code . '-SECOND';
                                        // Add suffix on the name.
                                        $trek_trip->tripName = $main_trip_name . '-SECOND';
                                    }
                                    
                                    $trek_trip->startDate             = $start_date;
                                    $trek_trip->endDate               = $end_date;
                                    $trek_trip->basePrice             = $period->basePrice;
                                    $trek_trip->singleSupplementPrice = $period->singleSupplementPrice;
                                    $trek_trip->depositAmount         = $period->depositAmount;
                                    $trek_trip->bikeUpgradePrice      = $period->bikeUpgradePrice;

                                    // Create or update a Ride Camp half-period product.
                                    tt_sync_wc_product_from_ns( $trek_trip );
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Sync All Custom Attributs from NS
 **/
if (!function_exists('tt_sync_custom_items')) {
    function tt_sync_custom_items()
    {
        $scriptIDs = [
            'customlist_genericbiketype' => 'syncBikeTypes',
            'customlist_genericbikesizes' => 'syncBikeSizes',
            'customlist_height' => 'syncHeights',
            'customlist_helmetselection' => 'syncHelmets',
            'customlist_pedals' => 'syncPedals',
            'customlist_saddlechoice' => 'syncSaddles',
            'customlist_riderlevel' => 'syncRiderLevels',
            'customlist_itemstatus' => 'syncDateStatuses',
            'customlist_contactmethod' => 'syncPreferredContactMethod',
            'customlist_leadsourcepersonal' => 'syncHeardAboutUs',
            'customlist_clothingsize' => 'syncJerseySizes',
            'customlist_itinerarycodes' => 'syncTripsList',
        ];
        $netSuiteClient = new NetSuiteClient();
        if( $scriptIDs ){
            foreach( $scriptIDs as $listId=> $scriptMethod ){
                $response = $netSuiteClient->get(LISTS_SCRIPT_ID, ['listId' => $listId ]);
                if( $response ){
                    update_option(TT_OPTION_PREFIX.$scriptMethod, json_encode($response));
                }
                tt_add_error_log('LISTS_SCRIPT_ID: '.LISTS_SCRIPT_ID, ['listId' => $listId ], $response);
            }
        }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : tt_ns_get_last_modified_gustes Array([0] => 1522426 [1] => 2107076 )
 **/
if (!function_exists('tt_ns_get_last_modified_gustes')) {
    function tt_ns_get_last_modified_gustes()
    {
        $modifiedAfter = date('Y-m-d H:i:s', strtotime(' -24 hours'));
        $modifiedAfterTime = gmdate("Y-m-d\TH:i:s", strtotime($modifiedAfter));
        $netSuiteClient = new NetSuiteClient();
        $modified_guest_ids = $netSuiteClient->get('1306:2', ['modifiedAfter' => $modifiedAfterTime ]);
        //tt_add_error_log('NS - modified_guest_ids', ['modifiedAfter' => $modifiedAfterTime ], $modified_guest_ids);
        return $modified_guest_ids;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : tt_ns_guest_booking_details
 **/
if (!function_exists('tt_ns_guest_booking_details')) {
    function tt_ns_guest_booking_details($single_guest=false,$ns_new_guest_id='',$wc_user_id='')
    {
        if( $single_guest == false ){
            $guest_ids = tt_ns_get_last_modified_gustes();
        }else{
            if( $ns_new_guest_id && is_numeric($ns_new_guest_id) ){
                $guest_ids = [$ns_new_guest_id];
            }
        }
        $netSuiteClient = new NetSuiteClient();
        global $wpdb;
        $table_name = $wpdb->prefix . 'guest_bookings';
        if( $guest_ids ){
            foreach($guest_ids as $guest_id ){
                $args = array('guestId'=> $guest_id, 'includeBookingInfo' => 1 );
                $ns_guest_booking_result = $netSuiteClient->get('1305:2', $args );
                tt_add_error_log('1) NS - Guest includeBookingInfo', $args, $ns_guest_booking_result);
                if( $ns_guest_booking_result && isset($ns_guest_booking_result->bookings) ){
                    foreach($ns_guest_booking_result->bookings as $booking_data){
                        if( $booking_data->bookingId ){
                            $args_2 = array( 'bookingId'=> $booking_data->bookingId );
                            $ns_booking_Data = $netSuiteClient->get('1304:2', $args_2 );
                            tt_add_error_log('2) NS - Guest includeBookingInfo', $args_2, $ns_booking_Data);
                            $booking_table_data = [];
                            if( $ns_booking_Data ){
                                $bookingId = $ns_booking_Data->bookingId;  //NS booking ID
                                $bookingNumber = $ns_booking_Data->bookingNumber;
                                $bookingDate = $ns_booking_Data->bookingDate;
                                $primaryGuestId = $ns_booking_Data->primaryGuestId;
                                $tripId = $ns_booking_Data->tripId;
                                $tripCode = $ns_booking_Data->tripCode;
                                $itineraryCode = $ns_booking_Data->itineraryCode;
                                $tripName = $ns_booking_Data->tripName;
                                $tripYear = $ns_booking_Data->tripYear;
                                $tripStartDate = $ns_booking_Data->tripStartDate;
                                $tripEndDate = $ns_booking_Data->tripEndDate;
                                $totalAmount = $ns_booking_Data->totalAmount;
                                $balancePaid = $ns_booking_Data->balancePaid;
                                $amountDue = $ns_booking_Data->amountDue;
                                $salesTax = $ns_booking_Data->salesTax;
                                $payments = $ns_booking_Data->payments; //Array<>Object
                                $product_id = tt_get_postid_by_meta_key_value('tt_meta_tripId', $tripId);
                                $guests = $ns_booking_Data->guests;
                                if( $guests ){
                                    foreach( $guests as $guest ){
                                        $guestId = $guest->guestId; //Ns guest
                                        $guestName = $guest->guestName;
                                        $guestEmail = '';
                                        if( $ns_new_guest_id == $guestId ){
                                            $userInfo = wp_get_current_user();
                                            $guestEmail = $userInfo->user_email;
                                        }
                                        $tripInsurancePurchased = $guest->tripInsurancePurchased;
                                        $waiveInsurance = $guest->waiveInsurance;
                                        $basePrice = $guest->basePrice;
                                        $insurance = $guest->insurance;
                                        $singleSupplement = $guest->singleSupplement;
                                        $wheelUpgrade = $guest->wheelUpgrade;
                                        $registrationId = $guest->registrationId;
                                        if($registrationId){
                                            $args = array('registrationId'=> $registrationId );
                                            $ns_guest_info = $netSuiteClient->get('1294:2', $args );
                                            tt_add_error_log('NS - Guest includeBookingInfo', $args, $ns_guest_info);
                                            if( $ns_guest_info ){
                                                $isPrimary = isset($ns_guest_info->isPrimary) ? $ns_guest_info->isPrimary : '';
                                                $bikeId = isset($ns_guest_info->bikeId) ? $ns_guest_info->bikeId : '';
                                                $helmetId = isset($ns_guest_info->helmetId) ? $ns_guest_info->helmetId : '';
                                                $pedalsId = isset($ns_guest_info->pedalsId) ? $ns_guest_info->pedalsId : '';
                                                $saddleId = isset($ns_guest_info->saddleId) ? $ns_guest_info->saddleId : '';
                                                $saddleHeight = isset($ns_guest_info->saddleHeight) ? $ns_guest_info->saddleHeight : '';
                                                $barReachFromSaddle = isset($ns_guest_info->barReachFromSaddle) ? $ns_guest_info->barReachFromSaddle : '';
                                                $barHeightFromWheelCenter = isset($ns_guest_info->barHeightFromWheelCenter) ? $ns_guest_info->barHeightFromWheelCenter : '';
                                                $jerseyId = isset($ns_guest_info->jerseyId) ? $ns_guest_info->jerseyId : '';
                                                $tshirtSizeId = isset($ns_guest_info->tshirtSizeId) ? $ns_guest_info->tshirtSizeId : '';
                                                $raceFitJerseyId = isset($ns_guest_info->raceFitJerseyId) ? $ns_guest_info->raceFitJerseyId : '';
                                                $shortsBibSizeId = isset($ns_guest_info->shortsBibSizeId) ? $ns_guest_info->shortsBibSizeId : '';
                                                $ecFirstName = isset($ns_guest_info->ecFirstName) ? $ns_guest_info->ecFirstName : '';
                                                $ecLastName = isset($ns_guest_info->ecLastName) ? $ns_guest_info->ecLastName : '';
                                                $ecPhone = isset($ns_guest_info->ecPhone) ? $ns_guest_info->ecPhone : '';
                                                $ecRelationship = isset($ns_guest_info->ecRelationship) ? $ns_guest_info->ecRelationship : '';
                                                $medicalConditions = isset($ns_guest_info->medicalConditions) ? $ns_guest_info->medicalConditions : '';
                                                $medications = isset($ns_guest_info->medications) ? $ns_guest_info->medications : '';
                                                $allergies = isset($ns_guest_info->allergies) ? $ns_guest_info->allergies : '';
                                                $dietaryRestrictions = isset($ns_guest_info->dietaryRestrictions) ? $ns_guest_info->dietaryRestrictions : '';
                                                $dietaryPreferences = isset($ns_guest_info->dietaryPreferences) ? $ns_guest_info->dietaryPreferences : '';
                                                $lockRecord = isset($ns_guest_info->lockRecord) ? $ns_guest_info->lockRecord : '';
                                                $lockBike = isset($ns_guest_info->lockBike) ? $ns_guest_info->lockBike : '';
                                                $waiverAccepted = isset($ns_guest_info->waiverAccepted) ? $ns_guest_info->waiverAccepted : '';
                                                if( $product_id != null ){
                                                    $booking_table_data['product_id'] = $product_id;
                                                }
                                                $guestName_arr = explode(' ', $guestName);
                                                $booking_table_data['netsuite_guest_registration_id'] = $guestId;
                                                $booking_table_data['guestRegistrationId'] = $registrationId;
                                                $booking_table_data['user_id'] = $wc_user_id;
                                                $booking_table_data['guest_booking_id'] = $bookingId;
                                                $booking_table_data['trip_code'] = $tripCode;
                                                $booking_table_data['trip_name'] = $tripName;
                                                $booking_table_data['trip_total_amount'] = $totalAmount;
                                                $booking_table_data['trip_number_of_guests'] = count($guests);
                                                $booking_table_data['trip_start_date'] = strtotime($tripStartDate);
                                                $booking_table_data['trip_end_date'] = strtotime($tripEndDate);
                                                $booking_table_data['guest_is_primary'] = ( $isPrimary == 1 ? 1 : 0 );
                                                $booking_table_data['guest_first_name'] = ( $guestName_arr[0] ? $guestName_arr[0] : '' );
                                                $booking_table_data['guest_last_name'] = ( $guestName_arr[1] ? $guestName_arr[1] : '' );
                                                $booking_table_data['guest_email_address'] = $guestEmail;
                                                $booking_table_data['bike_selection'] = $bikeId;
                                                $booking_table_data['helmet_selection'] = $helmetId;
                                                $booking_table_data['pedal_selection'] = $pedalsId;
                                                $booking_table_data['saddle_height'] = $saddleHeight;
                                                $booking_table_data['saddle_bar_reach_from_saddle'] = $barReachFromSaddle;
                                                $booking_table_data['saddle_bar_height_from_wheel_center'] = $barHeightFromWheelCenter;
                                                $booking_table_data['jersey_style'] = $jerseyId;
                                                $booking_table_data['tt_jersey_size'] = $jerseyId;
                                                $booking_table_data['shorts_bib_size'] = $shortsBibSizeId;
                                                $booking_table_data['emergency_contact_first_name'] = $ecFirstName;
                                                $booking_table_data['emergency_contact_last_name'] = $ecLastName;
                                                $booking_table_data['emergency_contact_phone'] = $ecPhone;
                                                $booking_table_data['emergency_contact_relationship'] = $ecRelationship;
                                                $booking_table_data['medical_conditions'] = $medicalConditions;
                                                $booking_table_data['medications'] = $medications;
                                                $booking_table_data['allergies'] = $allergies;
                                                $booking_table_data['dietary_restrictions'] = $dietaryRestrictions;
                                                $booking_table_data['waiver_signed'] = $waiverAccepted;
                                                if( $bookingId ){
                                                    $check_booking = tt_checkbooking_status($guestId, $bookingId);
                                                    if( $check_booking == 0 ){
                                                        $insert_booking = $wpdb->insert($table_name, $booking_table_data);
                                                        tt_add_error_log('4) NS<>WC - SQL [Insert]', ['last_error' => $wpdb->last_error, 'last_query' => $wpdb->last_query ], $booking_table_data);
                                                    }
                                                    if( $check_booking && $check_booking > 0 ){
                                                        $where = array('netsuite_guest_registration_id' => $guestId, 'guest_booking_id' => $bookingId);
                                                        $wpdb->update($table_name, $booking_table_data, $where);
                                                        tt_add_error_log('5) NS<>WC - SQL [Update]', ['last_error' => $wpdb->last_error, 'last_query' => $wpdb->last_query ], $booking_table_data);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : tt_ns_fetch_bike_type_info
 **/
if (!function_exists('tt_ns_fetch_bike_type_info')) {
    function tt_ns_fetch_bike_type_info()
    {
        $netSuiteClient = new NetSuiteClient();
        $ns_bikeType_results = $netSuiteClient->get('1301:2');
        if( $ns_bikeType_results ){
            update_option(TT_OPTION_PREFIX.'ns_bikeType_info', json_encode($ns_bikeType_results));
        }
        tt_add_error_log('NS - tt_ns_fetch_bike_type_info', [], $ns_bikeType_results);
    }
}


if( ! function_exists( 'tt_ns_fetch_registration_ids' ) ) {
    function tt_ns_fetch_registration_ids() {
        //Fire the NS script to fetch all the registration ids for the past 24 hours
        $modifiedAfter = date('Y-m-d H:i:s', strtotime(' -2 hours'));

        //Get the 1293 script with the modifiedAfter parameter and deploy 2
        $modifiedAfterTime = gmdate("Y-m-d\TH:i:s", strtotime($modifiedAfter));
        $netSuiteClient = new NetSuiteClient();
        $modified_reg_ids = $netSuiteClient->get('1293:2', ['modifiedAfter' => $modifiedAfterTime ]);

        //Loop through the registration ids and fetch the registration details. That's done to filter out users outside of this WP installation
        foreach( $modified_reg_ids as $modified_reg_id ) {
            $registrationEmail = $modified_reg_id->email;
            $netsuite_trip_id = $modified_reg_id->tripId;

            //Check if user exists in WC
            $user = get_user_by( 'email', $registrationEmail );
            if( $user ) {
                $wc_user_id = $user->ID;
                //Execute the 1294 script with the registration id as parameter and deploy 2
                $ns_registration_id = $modified_reg_id->id;
                $ns_registration_details = $netSuiteClient->get('1294:2', ['registrationId' => $ns_registration_id ]);

                //Get "Lock Record" and "Lock Bike" values
                $lockRecord = isset($ns_registration_details[0]->lockRecord) ? $ns_registration_details[0]->lockRecord : '';
                $lockBike = isset($ns_registration_details[0]->lockBike) ? $ns_registration_details[0]->lockBike : '';

                //Find the product with netsuite_trip_id  as meta value without using tt_get_postid_by_meta_key_value()
                $args = array(
                    'post_type' => 'product',
                    'meta_query' => array(
                        array(
                            'key' => 'tt_meta_tripid',
                            'value' => $netsuite_trip_id,
                            'compare' => '='
                        )
                    )
                );
                $trip_product = get_posts( $args );

                //If the product exists, get the ID
                if( $trip_product ) {
                    $trip_product_id = $trip_product[0]->ID;
                }

                //Update the lock bikes and lock record for the trip as meta values
                if( $trip_product_id ) {
                    update_post_meta( $trip_product_id, 'lock_record', $lockRecord );
                    update_post_meta( $trip_product_id, 'lock_bike', $lockBike );
                }

                if( $lockRecord || $lockBike ) {

                    //Check if such post meta exists, if it does, add the new registration id to the array
                    $existing_registration_ids_bike = get_post_meta( $trip_product_id, 'ns_registration_ids_bike', true );
                    $existing_registration_ids_record = get_post_meta( $trip_product_id, 'ns_registration_ids_record', true );


                    if( $lockBike ) {
                        if( ! empty( $existing_registration_ids_bike ) ) {

                            //Check if the user already exists in the array, if it does, don't add it again
                            if( ! in_array( $wc_user_id, $existing_registration_ids_bike ) ) {
                                $existing_registration_ids_bike[] += $wc_user_id;
                                update_post_meta( $trip_product_id, 'ns_registration_ids_bike', $existing_registration_ids_bike );
                            }

                        } else {
                            //If the post meta doesn't exist, create a new one
                            $new_registration_ids_bike = array();
                            $new_registration_ids_bike[] += $wc_user_id;
                            update_post_meta( $trip_product_id, 'ns_registration_ids_bike', $new_registration_ids_bike );
                        }
                    } else {
                        //Check if the user exists in the array, if it does, remove it
                        if( ! empty( $existing_registration_ids_bike ) ) {
                            $key = array_search( $wc_user_id, $existing_registration_ids_bike );
                            if( $key !== false ) {
                                unset( $existing_registration_ids_bike[$key] );
                                update_post_meta( $trip_product_id, 'ns_registration_ids_bike', $existing_registration_ids_bike );
                            }
                        }
                    }


                    if( $lockRecord ) {
                        if( ! empty( $existing_registration_ids_record ) ) {
                            //Check if the user already exists in the array, if it does, don't add it again
                            if( ! in_array( $wc_user_id, $existing_registration_ids_record ) ) {
                                $existing_registration_ids_record[] += $wc_user_id;
                                update_post_meta( $trip_product_id, 'ns_registration_ids_record', $existing_registration_ids_record );
                            }
                        } else {
                            //If the post meta doesn't exist, create a new one
                            $new_registration_ids_record = array();
                            $new_registration_ids_record[] += $wc_user_id;
                            update_post_meta( $trip_product_id, 'ns_registration_ids_record', $new_registration_ids_record );
                        }
                    } else {
                        //Check if the user exists in the array, if it does, remove it
                        if( ! empty( $existing_registration_ids_record ) ) {
                            //Remove all existing instances of the user id, even if it's more than one
                            $key = array_search( $wc_user_id, $existing_registration_ids_record );
                            if( $key !== false ) {
                                unset( $existing_registration_ids_record[$key] );
                                update_post_meta( $trip_product_id, 'ns_registration_ids_record', $existing_registration_ids_record );
                            }
                        }
                    }
                }

            }
        }
    }
}

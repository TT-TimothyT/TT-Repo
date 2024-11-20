<?php

use TTNetSuite\NetSuiteClient;

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get Trips last modified After
 **/
if (!function_exists('tt_get_last_modified_ns_trips')) {
    /**
     * Function to retrieve basic trips info from NS by the given filters.
     *
     * @param string $time_range The time range for which take the details in the past (modifiedAfter). By default is used DEFAULT_TIME_RANGE defined in the ttnsw-constants.php file.
     * @param string $filter_type The type of filter to use, when searching for trips in NS. Supported types of filter by NS Script 1296: itineraryCode, itineraryId, tripYear, modifiedAfter
     * @param bool   $return_all_trips_data Whether to return all trip data or only IDs
     *
     * @return array Array with objects for the found trips or trip IDs.
     *
     * Example trip object:
     * {
     *    "tripId": "43436",
     *    "tripCode": "24ADRC0226",
     *    "tripName": "Adelaide Ride Camp - 02/26/2024",
     *    "itineraryId": "369",
     *    "itineraryCode": "ADRC",
     *    "lastModifiedDate": "2024-02-26T10:37:00.000Z"
     * }
     */
    function tt_get_last_modified_ns_trips( $time_range = DEFAULT_TIME_RANGE, $filter_type = 'modifiedAfter', $return_all_trips_data = false ) {
        $trip_ids         = array();
        $net_suite_client = new NetSuiteClient();
        $trek_script_args = array();
        switch ( $filter_type ) {
            case 'tripYear':
                $trek_script_args = array(
                    'tripYear' => (int) $time_range
                );
                break;
            case 'modifiedAfter':
                $trek_script_args = array(
                    'modifiedAfter' => date('Y-m-d\TH:i:s', strtotime( $time_range ) )
                );
                break;
            case 'itineraryCode':
                $trek_script_args = array(
                    'itineraryCode' =>  $time_range
                );
                break;
            case 'itineraryId':
                $trek_script_args = array(
                    'itineraryId' => (int) $time_range
                );
                break;
            default:
                // By default modifiedAfter with the default time range.
                $trek_script_args = array(
                    'modifiedAfter' => date('Y-m-d\TH:i:s', strtotime( DEFAULT_TIME_RANGE ) )
                );
                break;
        }
        $trek_trips = $net_suite_client->get( TRIPS_SCRIPT_ID, $trek_script_args );

        // If trek_trips isn't an array, it most probably has an error returned from the NS API.
        if ( $trek_trips && is_array( $trek_trips ) && ! empty( $trek_trips ) ) {

            tt_add_error_log( 'NS_SCRIPT_ID: ' . TRIPS_SCRIPT_ID, array( 'filter_value' => $time_range, 'filter_type' => $filter_type, 'return_all_trips_data' => $return_all_trips_data ), array( 'status' => 'true', 'trips_count' => count( $trek_trips ) ) );

            if( $return_all_trips_data ) {
                // Return all trips data.
                return $trek_trips;
            } else {
                // Return IDs only.
                foreach ( $trek_trips as $trek_trip ) {
                    $trip_id = $trek_trip->tripId;
                    $trip_ids[] = $trip_id;
                }
            }
        } else {
            // Some error or empty array.
            tt_add_error_log( 'NS_SCRIPT_ID: ' . TRIPS_SCRIPT_ID, array( 'filter_value' => $time_range, 'filter_type' => $filter_type, 'return_all_trips_data' => $return_all_trips_data ), array( 'status' => 'false', 'trek_trips' => $trek_trips ) );
        }
        return $trip_ids;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : NS Trips sync function
 **/

if ( ! function_exists( 'tt_sync_ns_trips' ) ) {
    /**
     * Store the basic info for the trips from NS to trips table in DB.
     *
     * @param string $time_range The time range for which take the details in the past (modifiedAfter).
     * By default is used DEFAULT_TIME_RANGE defined in the ttnsw-constants.php file.
     * OR based on the filter type can put another value.
     *
     * @param string $filter_type The type of filter to use, when searching for trips in NS. Supported types of filter by NS Script 1296: itineraryCode, itineraryId, tripYear, modifiedAfter
     *
     * @return void
     */
    function tt_sync_ns_trips( $time_range = DEFAULT_TIME_RANGE, $filter_type = 'modifiedAfter' ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'netsuite_trips';
        $trek_trips = tt_get_last_modified_ns_trips( $time_range, $filter_type, true ); // Pass parameter true, to can return all trip data.

        if ( $trek_trips && ! empty( $trek_trips ) && is_array( $trek_trips ) ) {
            foreach ( $trek_trips as $trek_trip ) {
                $trips_data = array(
                    'tripName'         => $trek_trip->tripName,
                    'itineraryId'      => $trek_trip->itineraryId,
                    'itineraryCode'    => $trek_trip->itineraryCode,
                    'lastModifiedDate' => $trek_trip->lastModifiedDate
                );
                $check_trip = tt_get_trip_by_idCode( $table_name, $trek_trip->tripId, $trek_trip->tripCode );
                if ( $check_trip >= 1 ) {
                    $where = array( 'tripId' => $trek_trip->tripId, 'tripCode' => $trek_trip->tripCode );
                    $wpdb->update( $table_name, $trips_data, $where );
                } else {
                    $trips_data['tripId']   = $trek_trip->tripId;
                    $trips_data['tripCode'] = $trek_trip->tripCode;
                    $inserted_id = $wpdb->insert( $table_name, $trips_data );
                }
                if ( $wpdb->last_error ) {
                    tt_add_error_log( '[Faild] Trips Table', array( 'trip_id' => $trek_trip->tripId, 'trip_code' => $trek_trip->tripCode ), array( 'last_error' => $wpdb->last_error, 'trips_data' => $trips_data ) );
                }
            }
        }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : NS Trip Details sync function
 **/
if ( ! function_exists( 'tt_sync_ns_trip_details' ) ) {
    /**
     * Function to sync the trip details table in the DB.
     *
     * @param string $time_range The time range for which take the details in the past (modifiedAfter).
     * By default is used DEFAULT_TIME_RANGE defined in the ttnsw-constants.php file.
     * OR based on the filter type can put another value.
     *
     * @param string $filter_type The type of filter to use, when searching for trips in NS. Supported types of filter by NS Script 1296: itineraryCode, itineraryId, tripYear, modifiedAfter
     * @param bool $single_trip Whether to sync a single trip or trips for the given time range.
     * @param int $trip_id The ID for the trip in NS.
     *
     * @return void|bool Bool is used for the single trip sync.
     */
    function tt_sync_ns_trip_details( $time_range = DEFAULT_TIME_RANGE, $filter_type = 'modifiedAfter', $single_trip = false, $trip_id = 0 ) {
        global $wpdb;
        $table_name             = $wpdb->prefix . 'netsuite_trip_detail';
        $net_suite_client       = new NetSuiteClient();
        $last_modified_trip_ids = array();
        if( $single_trip && ! empty( $trip_id ) ) {
            $last_modified_trip_ids = array( (int) $trip_id );
        } else {
            $last_modified_trip_ids = tt_get_last_modified_ns_trips( $time_range, $filter_type );
        }
        tt_add_error_log('[Trip Details Sync]: '.TRIPS_SCRIPT_ID, 'START Ttrip Details Sync for ' . $filter_type . ' ' . $time_range . '; single_trip: ' . ( $single_trip ? 'true' : 'false' ), '$last_modified_trip_ids-> ' . count($last_modified_trip_ids));
        // Early exit if there are no last modified trip ids for the given time range.
        if ( empty( $last_modified_trip_ids ) ) {
            tt_add_error_log('[Trip Details Sync]: '.TRIPS_SCRIPT_ID, 'END Ttrip Details Sync for ' . $filter_type . ' ' . $time_range, array( 'last_modified_trip_ids: ' => count( $last_modified_trip_ids ), 'message' => 'There are no new Trip IDs to sync modified in the period ' . $time_range ) );
            return;
        }
        $trip_Ids = tt_get_local_trip_ids( $last_modified_trip_ids ); // This is return all ids if there are empty array as argument. If the trip not found, returns an empty array.
        if ( $single_trip && empty( $trip_Ids ) ) {
            // Trip doesn't exist in the netsuite_trips table. It can't sync the Trip because it does not exist in the Local DB.
            // TODO: For future development, we can find a way to insert the record with the required info.
            tt_add_error_log( '[Trip Details Sync]: '.TRIPS_SCRIPT_ID, 'END Ttrip Details Sync for trip_id ' . $trip_id, array( 'sucess' => false, 'message' => 'It can\'t sync the Trip because it does not exist in the Local DB.' ) );
            return false;
        }
        if ( $trip_Ids && ! empty( $trip_Ids ) ) {
            foreach ($trip_Ids as $trip_Ids_arr) {
                $trek_script_args = array( 'tripIds' => implode(',', $trip_Ids_arr) );
                $trek_trip_details = $net_suite_client->get(TRIP_DETAIL_SCRIPT_ID, $trek_script_args);
                if( $trek_trip_details ){
                    tt_add_error_log( 'NS_SCRIPT_ID: ' . TRIP_DETAIL_SCRIPT_ID, $trek_script_args, array( 'status' => 'true' ) );
                } else {
                    tt_add_error_log( 'NS_SCRIPT_ID: ' . TRIP_DETAIL_SCRIPT_ID, $trek_script_args, $trek_trip_details );
                }
                if ( ! empty( $trek_trip_details ) && isset( $trek_trip_details->trips ) ) {
                    foreach ( $trek_trip_details->trips as $trek_trip_detail ) {
                        $trips_data = array(
                            'isRideCamp'               => $trek_trip_detail->isRideCamp ? $trek_trip_detail->isRideCamp : $trek_trip_detail->supportsNestedDates,
                            'isLateDepositAllowed'     => $trek_trip_detail->isLateDepositAllowed,
                            'depositBeforeDate'        => $trek_trip_detail->depositBeforeDate,
                            'removeFromStella'         => $trek_trip_detail->removeFromStella,
                            'capacity'                 => $trek_trip_detail->capacity,
                            'booked'                   => $trek_trip_detail->booked,
                            'remaining'                => $trek_trip_detail->remaining,
                            'status'                   => json_encode( $trek_trip_detail->status ),
                            'startDate'                => $trek_trip_detail->startDate,
                            'endDate'                  => $trek_trip_detail->endDate,
                            'daysToTrip'               => $trek_trip_detail->daysToTrip,
                            'tripYear'                 => $trek_trip_detail->tripYear,
                            'tripSeason'               => $trek_trip_detail->tripSeason,
                            'tripMonth'                => $trek_trip_detail->tripMonth,
                            'tripContinent'            => $trek_trip_detail->tripContinent,
                            'tripCountry'              => $trek_trip_detail->tripCountry,
                            'tripRegion'               => $trek_trip_detail->tripRegion,
                            'itineraryId'              => $trek_trip_detail->itineraryId,
                            'itineraryCode'            => $trek_trip_detail->itineraryCode,
                            'lastModifiedDate'         => $trek_trip_detail->lastModifiedDate,
                            'riderType'                => json_encode( $trek_trip_detail->riderType ),
                            'product_line'             => json_encode( tt_validate( $trek_trip_detail->productLine, NULL ) ),
                            'subStyle'                 => json_encode( tt_validate( $trek_trip_detail->subStyle, NULL ) ),
                            'basePrice'                => $trek_trip_detail->basePrice,
                            'singleSupplementPrice'    => $trek_trip_detail->singleSupplementPrice,
                            'depositAmount'            => $trek_trip_detail->depositAmount,
                            'bikeUpgradePrice'         => $trek_trip_detail->bikeUpgradePrice,
                            'insurancePercentage'      => $trek_trip_detail->insurancePercentage,
                            'taxRate'                  => $trek_trip_detail->taxRate,
                            'ssSoldOut'                => $trek_trip_detail->ssSoldOut,
                            'isOpenToRoommateDisabled' => $trek_trip_detail->isOpenToRoommateDisabled,
                            'bookablePeriods'          => json_encode( $trek_trip_detail->bookablePeriods ),
                            'hotels'                   => json_encode( $trek_trip_detail->hotels ),
                            'bikes'                    => json_encode( $trek_trip_detail->bikes ),
                            'addOns'                   => json_encode( $trek_trip_detail->addOns ),
                            'tripSpecificMessage'      => $trek_trip_detail->tripSpecificMessage,
                            'SmugMugLink'              => $trek_trip_detail->smugMugLink,
                            'SmugMugPassword'          => $trek_trip_detail->smugMugPasscode
                        );
                        $check_trip = tt_get_trip_by_idCode( $table_name, $trek_trip_detail->tripId, $trek_trip_detail->tripCode );
                        if ( $check_trip >= 1 ) {
                            $where = array('tripId' => $trek_trip_detail->tripId, 'tripCode' => $trek_trip_detail->tripCode );
                            $wpdb->update( $table_name, $trips_data, $where );
                        } else {
                            $trips_data['tripId']   = $trek_trip_detail->tripId;
                            $trips_data['tripCode'] = $trek_trip_detail->tripCode;
                            $inserted_id            = $wpdb->insert( $table_name, $trips_data );
                        }
                        if ( $wpdb->last_error ) {
                            tt_add_error_log( '[Faild] Trip Details Table', array( 'trip_id' => $trek_trip_detail->tripId, 'trip_code' => $trek_trip_detail->tripCode ), array( 'last_error' => $wpdb->last_error, 'trips_data' => $trips_data ) );
                        }
                    }
                }
            }
        }
        tt_add_error_log( '[Trip Details Sync]: '.TRIPS_SCRIPT_ID, 'END Ttrip Details Sync for ' . $filter_type . ' ' . $time_range . '; single_trip: ' . ( $single_trip ? 'true' : 'false' ), '$last_modified_trip_ids-> ' . count( $last_modified_trip_ids ) );
        // If it is a single trip sync, the function awaits some response.
        if ( $single_trip ) {
            return true;
        }
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

        $custom_taxonomies = array(
            // 'activity-level' => $riderLevelVal, // In my opinion this is not really in use, because we use the parent products rider level which are set manually.
            // 'country'        => $tripCountry, // In my opinion this is not really in use, because we use the parent products country which are set manually.
            'trip-status'    => is_object( $status ) ? $status->name : '',
        );

        foreach( $custom_taxonomies as $custom_tax_name => $custom_tax_value ) {
            if( $custom_tax_value ) {
                tt_set_custom_product_tax_value( $product_id, $custom_tax_name, $custom_tax_value );
            }
        }

        $custom_attributes = array(
            'pa_duration'   => $daysToTrip, // This will keep as is it, based on a discussion on call with the client.
            'pa_season'     => $tripSeason,
            'pa_start-date' => ! empty( $attr_startDate ) ? date( 'd/m/y', $attr_startDate ) : '',
            'pa_end-date'   => ! empty( $attr_endDate ) ? date( 'd/m/y', $attr_endDate ) : '',
        );
        if( $custom_attributes ){
            foreach($custom_attributes as $custom_attribute_k=>$custom_attribute_v){
                if( $custom_attribute_v ){
                    tt_set_wc_attribute_value($product_id, $custom_attribute_k, $custom_attribute_v);
                }
            }
        }
        update_post_meta( $product_id, '_manage_stock', 'yes' );
        update_post_meta( $product_id, '_stock', $capacity );
        update_post_meta( $product_id, '_regular_price', $basePrice );
        update_post_meta( $product_id, '_price', $basePrice );
        // End saved attribute code.
        update_post_meta( $product_id, 'ns_last_synced_date_time', date( 'Y-m-d H:i:s' ) );
        // Add 'nofollow' and 'noindex' for the Date Trip / Simple product.
        update_post_meta( $product_id, '_yoast_wpseo_meta-robots-noindex', 1 ); // Update Yoast meta for noindex.
        update_post_meta( $product_id, '_yoast_wpseo_meta-robots-nofollow', 1 ); // Update Yoast meta for nofollow.
    }
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : NS Trips/Hotels/Bike/Rooms sync function
 **/
if (!function_exists('tt_sync_wc_products_from_ns')) {
    /**
     * Create a products in WC from trips details in NetSutie.
     *
     * @param bool   $is_all Whether to create/update all trips from the local DB.
     * @param array  $sink_trip_ids Array with trip ids for wich to create/update products.
     * @param string $time_range The time range for which take the details in the past (modifiedAfter).
     * By default is used DEFAULT_TIME_RANGE defined in the ttnsw-constants.php file.
     * OR based on the filter type can put another value.
     *
     * @param string $filter_type The type of filter to use, when searching for trips in NS. Supported types of filter by NS Script 1296: itineraryCode, itineraryId, tripYear, modifiedAfter
     *
     * @return void
     */
    function tt_sync_wc_products_from_ns( $is_all = false, $sync_trip_ids = array(), $time_range = DEFAULT_TIME_RANGE, $filter_type = 'modifiedAfter' )
    {
        $net_suite_client   = new NetSuiteClient();
        $trip_sync_type_msg = '[Trip/Product Sync]: ' . TRIP_DETAIL_SCRIPT_ID;

        // Check if we are syncing a single trip or a bunch of such.
        if( $sync_trip_ids && is_array( $sync_trip_ids ) && ! empty( $sync_trip_ids ) ) {

            $trip_ids           = $sync_trip_ids;
            $trip_ids           = array_chunk( $trip_ids, 10 );
            $trip_sync_type_msg = '[Single Trip/Product Sync]: ' . TRIP_DETAIL_SCRIPT_ID;
        } else {

            $last_modified_trip_ids = tt_get_last_modified_ns_trips( $time_range, $filter_type );

            if ( $is_all == true ) {

                $trip_ids = tt_get_local_trip_ids();
            } else {

                if ( ! empty( $last_modified_trip_ids ) ) {

                    $trip_ids = tt_get_local_trip_ids( $last_modified_trip_ids );
                } else {

                    $trip_ids = array();
                }
            }
        }
        tt_add_error_log( $trip_sync_type_msg, 'START Trip/Product Sync for ' . $filter_type . ' ' . $time_range, array( 'is_all: ' => $is_all, '$last_modified_trip_ids->' => isset( $last_modified_trip_ids ) && is_countable( $last_modified_trip_ids ) ? count( $last_modified_trip_ids ) : $trip_ids ) );
        // Early exit if there are no trip ids.
        if ( empty( $trip_ids ) ) {
            tt_add_error_log( $trip_sync_type_msg, 'END Trip/Product Sync', array( 'status' => true, 'message' => 'There are no new Trip IDs to sync modified in the period ' . $time_range ) );
            return;
        }
        
        // If the trip code is found locally in netsuite_trips proceed.
        foreach ( $trip_ids as $trip_ids_arr ) {

            if( is_int( $trip_ids_arr ) && ! is_array( $trip_ids_arr ) ) {
                $trip_ids_arr = [ $trip_ids ];
            }

            $trek_script_args = array( 'tripIds' => implode( ',', $trip_ids_arr ) );
            $trek_trips       = $net_suite_client->get( TRIP_DETAIL_SCRIPT_ID, $trek_script_args );

            if( $trek_trips ) {
                tt_add_error_log( $trip_sync_type_msg, $trek_script_args, array( 'status' => 'true' ) );
            } else {
                tt_add_error_log( $trip_sync_type_msg, $trek_script_args, $trek_trips );
            }

            if( empty( $trek_trips ) || ! isset( $trek_trips->trips ) ) {
                continue;
            }

            $ride_camp_trips = array();

            foreach ( $trek_trips->trips as $trek_trip ) {
                // Set is Ride Camp flag.
                $is_ride_camp             = ( $trek_trip->isRideCamp ? $trek_trip->isRideCamp : '' );
                // Set nested dates flag.
                $is_supports_nested_dates = ( $trek_trip->supportsNestedDates ? $trek_trip->supportsNestedDates : '' );

                if( ( ! empty( $is_ride_camp ) && $is_ride_camp ) || ( ! empty( $is_supports_nested_dates ) && $is_supports_nested_dates ) ) {
                    // This is a Ride Camp trip, store it into array for laiter usage.
                    array_push( $ride_camp_trips, $trek_trip );
                }
                // Continue as normal.
                tt_sync_wc_product_from_ns( $trek_trip );
            }

            if( empty( $ride_camp_trips ) ) {
                continue;
            }

            // Have a Ride Camp Trips.
            foreach( $ride_camp_trips as $trek_trip ) {

                // Take the info for the additional trips that need to be made.
                $bookable_periods = $trek_trip->bookablePeriods;

                $main_start_date = $trek_trip->startDate;
                $main_end_date   = $trek_trip->endDate;

                // Keep SKU base.
                $main_trip_code = $trek_trip->tripCode;
                $main_trip_name = $trek_trip->tripName;

                $main_trip_status = $trek_trip->status;

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

                    $period_status = $period->status;
                    if ( ! empty( $period_status->id ) ) {
                        $trek_trip->status = $period_status;
                    } else {
                        $trek_trip->status = $main_trip_status;
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
        tt_add_error_log( $trip_sync_type_msg, 'END Trip/Product Sync', array( '$last_modified_trip_ids->' => isset( $last_modified_trip_ids ) && is_countable( $last_modified_trip_ids ) ? count( $last_modified_trip_ids ) : $trip_ids ) );
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Sync All Custom Attributs from NS
 **/
if ( ! function_exists( 'tt_sync_custom_items' ) ) {
    function tt_sync_custom_items() {
        $script_ids = array(
            'customlist_genericbiketype'      => 'syncBikeTypes',
            'customlist_genericbikesizes'     => 'syncBikeSizes',
            'customlist_height'               => 'syncHeights',
            'customlist_helmetselection'      => 'syncHelmets',
            'customlist_pedals'               => 'syncPedals',
            'customlist_saddlechoice'         => 'syncSaddles',
            'customlist_riderlevel'           => 'syncRiderLevels',
            'customlist_itemstatus'           => 'syncDateStatuses',
            'customlist_contactmethod'        => 'syncPreferredContactMethod',
            'customlist_leadsourcepersonal'   => 'syncHeardAboutUs',
            'customlist_clothingsize'         => 'syncJerseySizes',
            'customlist_itinerarycodes'       => 'syncTripsList',
            'customlist_productline'          => 'syncPoductLine',
            'customlist_tt_it_activity_level' => 'syncActivityLevel',
        );
        $net_suite_client = new NetSuiteClient();
        if ( $script_ids ) {
            foreach( $script_ids as $list_id => $script_method ){
                $response = $net_suite_client->get( LISTS_SCRIPT_ID, array( 'listId' => $list_id ) );
                if ( $response ) {
                    update_option( TT_OPTION_PREFIX . $script_method, json_encode( $response ) );
                }
                tt_add_error_log( 'LISTS_SCRIPT_ID: ' . LISTS_SCRIPT_ID, array( 'listId' => $list_id ), $response );
            }
        }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : tt_ns_get_last_modified_gustes Array([0] => 1522426 [1] => 2107076 )
 **/
if ( ! function_exists( 'tt_ns_get_last_modified_gustes' ) ) {
    function tt_ns_get_last_modified_gustes( $time_range = DEFAULT_TIME_RANGE )
    {
        $modified_after      = date( 'Y-m-d H:i:s', strtotime( $time_range ) );
        $modified_after_time = gmdate( "Y-m-d\TH:i:s", strtotime( $modified_after ) );
        $net_suite_client    = new NetSuiteClient();
        $modified_guest_ids  = $net_suite_client->get( GUESTS_TO_SYNC_SCRIPT_ID, array( 'modifiedAfter' => $modified_after_time ) );
        tt_add_error_log( 'NS - modified_guest_ids: ' . GUESTS_TO_SYNC_SCRIPT_ID, array( 'modified_after' => $modified_after, 'modified_after_time' => $modified_after_time ), $modified_guest_ids );

        return $modified_guest_ids;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : tt_ns_guest_booking_details
 **/
if (!function_exists('tt_ns_guest_booking_details')) {
    function tt_ns_guest_booking_details( $single_guest = false, $ns_new_guest_id = '', $wc_user_id = '', $time_range = DEFAULT_TIME_RANGE, $is_sync_process = true )
    {
        global $wpdb;
        $table_name       = $wpdb->prefix . 'guest_bookings';

        $net_suite_client = new NetSuiteClient();

        if( $single_guest == false ) {
            // This is a manual sync from Dashboard with action - NS<>WC Booking Sync.
            $guest_ids = tt_ns_get_last_modified_gustes( $time_range );
        } else {
            // This is a manual sync from Dashboard with action - Manual Single Guest Bookings/Preferences Sync from NS to WC
            // or during user registration.
            if( $ns_new_guest_id && is_numeric( $ns_new_guest_id ) ) {
                $guest_ids = array( $ns_new_guest_id );
            }
        }

        // Guests IDs not found.
        if( ! $guest_ids ) {
            return;
        }

        foreach( $guest_ids as $guest_id ) {
            $args                    = array( 'guestId' => $guest_id, 'includeBookingInfo' => 1 );
            $ns_guest_booking_result = $net_suite_client->get( USER_BOOKINGS_SCRIPT_ID, $args ); // 1305
            tt_add_error_log( '1) NS - Guest includeBookingInfo', $args, $ns_guest_booking_result );

            // Guest Not Found or doesn't have bookings.
            if( ! $ns_guest_booking_result || ! isset( $ns_guest_booking_result->bookings ) ) {
                continue;
            }

            // Before collect guest preferences, check for wp user existing.
            $ns_guest_email = isset( $ns_guest_booking_result->email ) ? $ns_guest_booking_result->email : '';
            $wp_user        = get_user_by( 'email', $ns_guest_email );
            $wp_user_id     = isset( $wp_user->ID ) ? $wp_user->ID : 0;

            if ( empty( $wp_user_id ) ) {
                // WP User not found by email, try to find WP User by NS User ID.
                $wp_users_by_ns_user_id = get_users(
                    array(
                        'meta_key'   => 'ns_customer_internal_id',
                        'meta_value' => (int) $guest_id
                    )
                );

                if ( ! empty( $wp_users_by_ns_user_id ) && 1 === count( $wp_users_by_ns_user_id ) ) {
                    $wp_user       = $wp_users_by_ns_user_id[0];
                    $wp_user_id    = tt_validate( $wp_user->ID, 0 );
                    $wp_user_email = tt_validate( $wp_user->data->user_email, '' );
                    tt_add_error_log( 'Guest Sync Warning: Found WP User with a different primary email in NetSuite', array( 'ns_user_id' => $guest_id, 'ns_primary_email' => $ns_guest_email ), array( 'wp_user_id' => $wp_user_id, 'wp_user_email' => $wp_user_email ) );
                } elseif ( count( $wp_users_by_ns_user_id ) > 1 ) {
                    // Duplicate WP Users with the same NS User ID.
                    $wp_users_data = array_column( $wp_users_by_ns_user_id, 'data' );
	                $wp_users      = array_column( $wp_users_data, 'user_email', 'ID' );
                    tt_add_error_log( 'Guest Sync Warning: Duplicate WP users with the same NS User ID', array( 'ns_user_id' => $guest_id, 'ns_primary_email' => $ns_guest_email ), array( 'found_wp_users' => $wp_users ) );
                }
            }

            // If we have wp user.
            if ( ! empty( $wp_user_id ) ) {

                if( empty( $wc_user_id ) ) {
                    $wc_user_id = $wp_user_id;
                }

                // The check for $is_sync_process prevents override information during the booking process.
                if( 'true' == $is_sync_process ) {
                    tt_sync_guest_preferences( $wp_user_id, $ns_guest_booking_result );
                }

                // Try to repair the missing NS User ID.
                $ns_user_id = get_user_meta( $wp_user_id, 'ns_customer_internal_id', true );

                if( empty( $ns_user_id ) ) {
                    // Update the NS User ID for WP User.
                    update_user_meta( $wp_user_id, 'ns_customer_internal_id', $guest_id );
                }
            }

            foreach( $ns_guest_booking_result->bookings as $booking_data ) {

                // Booking ID not found.
                if( ! $booking_data->bookingId ) {
                    continue;
                }

                $args_2          = array( 'bookingId' => $booking_data->bookingId );
                $ns_booking_data = $net_suite_client->get( GET_BOOKING_SCRIPT_ID, $args_2 ); // 1304
                tt_add_error_log( '2) NS - Guest includeBookingInfo', $args_2, $ns_booking_data );

                // Booking data not found.
                if( ! $ns_booking_data ) {
                    continue;
                }

                $guests = $ns_booking_data->guests;

                // Guests not found.
                if( ! $guests ) {
                    continue;
                }

                $booking_id = $ns_booking_data->bookingId;  // NS booking ID.

                foreach( $guests as $guest ){

                    $registration_id = $guest->registrationId;
                    $ns_guest_id     = $guest->guestId; // Ns guest.

                    // Guest Registration ID not found or this guest data not for current guest.
                    if( ! $registration_id || $guest_id != $ns_guest_id ) {
                        continue;
                    }

                    $args_3            = array( 'registrationId' => $registration_id );
                    $ns_guest_info_arr = $net_suite_client->get( GET_REGISTRATIONS_SCRIPT_ID, $args_3 ); // 1294
                    tt_add_error_log( '3) NS - Guest includeBookingInfo', $args_3, $ns_guest_info_arr );

                    // Guest Registration not found.
                    if( ! $ns_guest_info_arr || ! is_array( $ns_guest_info_arr ) ) {
                        continue;
                    }

                    $ns_guest_info = $ns_guest_info_arr[0];

                    // Set Bike and Checklist Locking statuses during the sync process.
                    tt_set_bike_record_lock_status( $ns_guest_email, $registration_id, $ns_guest_info );

                    /**
                     * Check for existing records in DB.
                     * The unique record is determined via NetSuite User ID and Booking ID.
                     *
                     * Here, we rely on the fact that during the checkout process,
                     * these functions [ insert_records_guest_bookings_cb() & tt_update_user_booking_info() ] have executed successfully
                     * and the necessary information has been stored in the guest_bookings table for this check.
                     */
                    $check_booking = tt_checkbooking_status( $ns_guest_id, $booking_id );

                    if( ! $check_booking || $check_booking <= 0 ) {

                        $trip_code    = $ns_booking_data->tripCode;
                        $is_ride_camp = tt_check_is_ride_camp_trip_from_dates( $ns_booking_data->tripStartDate, $ns_booking_data->tripEndDate, $ns_booking_data->wholeTripStartDate, $ns_booking_data->wholeTripEndDate );
                        $product_id   = $is_ride_camp ? tt_take_ride_camp_product_info( $ns_booking_data->tripStartDate, $ns_booking_data->tripEndDate, $ns_booking_data->wholeTripStartDate, $ns_booking_data->wholeTripEndDate, $ns_booking_data->tripCode, true ) : tt_get_product_by_sku( $trip_code, true ); // If there is not found product, we have null here.

                        // Need to have a product, because the data for the booking will be stored in line item metadata.
                        if( empty( $product_id ) ) {
                            tt_add_error_log( '[WC] - create auto-generated order', array( 'booking_id' => $booking_id, 'customer_id' => $wc_user_id, 'ns_user_id' => $guest_id, 'is_primary' => $ns_guest_info->isPrimary, 'trip_code' => $trip_code, 'is_ride_camp' => $is_ride_camp ), array( 'status' => 'false', 'message' => 'Attempt to create an order, but product was not found.' ) );
                            // Skip the order creating, booking import, and booking update.
                            continue;
                        }

                        // If booking not found in the table, create an empty order and migrate this booking below.
                        $auto_generated_order = tt_create_order( $booking_id );

                        
                        if ( $auto_generated_order ) {
                            tt_add_error_log( '[WC] - create auto-generated order', array( 'booking_id' => $booking_id, 'customer_id' => $wc_user_id, 'ns_user_id' => $guest_id, 'is_primary' => $ns_guest_info->isPrimary ), array( 'status' => 'true', 'message' => 'New Auto generated Order was created.', 'order_id' => $auto_generated_order->id ) );
                            tt_finalize_migrated_order( $auto_generated_order, $product_id, $wc_user_id, $guest_id, $ns_guest_booking_result, $ns_booking_data, $ns_guest_info, $guest );
                        }

                        // Skip next steps and go to the next guest. By the way, we added a check to sync only the guest registration of the current NS user.
                        continue;
                    }

                    $update_booking_data = array(
                        'ns_guest_booking_result' => $ns_guest_booking_result,
                        'ns_booking_data'         => $ns_booking_data,
                        'ns_guest_info'           => $ns_guest_info,
                        'guest'                   => $guest,
                    );

                    if( $guest->isPrimary ) {
                        // Take the order for this booking.
                        $order = tt_get_order_by_booking( $booking_id );

                        if ( $order ) {
                            $order_finished_status = $order->get_meta( 'tt_wc_order_finished_status' );

                            // If the order is not finished the last step is to set the primary guest as a customer of the order.
                            if( 'not-finished' == $order_finished_status ) {
                                // Finish the order on the primary registered guest.
                                if( ! empty( $wp_user_id ) ) {
                                    $order->set_customer_id( $wp_user_id );
                                    $order->save();
                                    update_post_meta( $order->id, 'tt_wc_order_finished_status', 'finished' );
                                    tt_add_error_log( 'NS - Found migrated order', array( 'booking_id' => $booking_id, 'order_id' => $order->id, 'customer_id' => $wp_user_id, 'ns_user_id' => $ns_guest_id, 'is_primary' => $ns_guest_info->isPrimary ), array( 'status' => 'true', 'message' => 'Assign the primary guest as customer to the order.' ) );
                                }
                            }
                        }
                    }

                    // Update existing booking.
                    $where = array( 'netsuite_guest_registration_id' => $ns_guest_id, 'ns_trip_booking_id' => $booking_id );
                    tt_guest_bookings_table_crud( tt_prepare_bookings_table_data( $update_booking_data ), $where );
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
        $ns_bikeType_results = $netSuiteClient->get( GET_BIKE_MODELS_SCRIPT_ID );
        if( $ns_bikeType_results ){
            update_option(TT_OPTION_PREFIX.'ns_bikeType_info', json_encode($ns_bikeType_results));
        }
        tt_add_error_log('NS - tt_ns_fetch_bike_type_info', [], $ns_bikeType_results);
    }
}


if( ! function_exists( 'tt_ns_fetch_registration_ids' ) ) {
    function tt_ns_fetch_registration_ids( $time_range = DEFAULT_TIME_RANGE_LOCKING_STATUS ) {
        // Fire the NS script to fetch all the registration ids for the past 4 hours.
        $modified_reg_data = tt_get_ns_guest_modified_registrations( $time_range );

        if( ! $modified_reg_data ) {
            return;
        }

        // Loop through the registration ids and fetch the registration details.
        foreach( $modified_reg_data as $modified_reg ) {
            $ns_reg_id    = $modified_reg->id;
            $ns_reg_email = $modified_reg->email;
            $ns_trip_id   = $modified_reg->tripId;

            tt_set_bike_record_lock_status( $ns_reg_email, $ns_reg_id );
        }
    }
}

if ( ! function_exists( 'tt_sync_ns_single_trip_details' ) ) {
    /**
     * Manual sync the details table with info from NS for a single trip by given trip ID.
     *
     * @param int $trip_id The ID for the trip in NS.
     *
     * @return void
     */
    function tt_sync_ns_single_trip_details( $trip_id = 0 ) {
        $start_time  = date('Y-m-d H:i:s'); // For admin notices.
        if( empty( $trip_id ) || ! is_numeric( $trip_id ) ) {
            // Missing the Trip ID param or not a number.
            add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_ns_single_trip_details' ), 'Trip ID can\'t be empty, and should be numeric!', 'error' );
            return;
        }

        tt_add_error_log('[Single Trip Details Sync START]: ' . TRIPS_SCRIPT_ID, array( 'trip_id' => $trip_id ), array( 'time' => date('Y-m-d H:i:s') ) );

        $success = tt_sync_ns_trip_details( DEFAULT_TIME_RANGE, 'modifiedAfter', true, $trip_id );

        if ( ! $success ) {
            add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_ns_single_trip_details' ), 'It can\'t sync the Trip with ID ' . $trip_id . ' because it does not exist in the Local DB.', 'error' );
            return;
        }

        tt_add_error_log('[Single Trip Details Sync END]: ' . TRIPS_SCRIPT_ID, array( 'trip_id' => $trip_id ), array( 'time' => date('Y-m-d H:i:s') ) );
        add_settings_error( 'ttnsw-admin-notice', esc_attr( 'tt_sync_ns_single_trip_details' ), 'The sync of the Trip with ID ' . $trip_id . ' completed! Started at: ' . $start_time . ' and finished at: ' . date('Y-m-d H:i:s'), 'success' );
    }
}

<?php
/**
* @author  : Dharmesh Panchal
* @version : 1.0.0
* @return  : Get NS custom fields data using self object data
**/ 
function get_trek_ns_item_data($res_object, $key, $scriptId = 'scriptId'){
	$item_data = new stdClass();
	if($res_object && $key ){
		$field_index = array_search( $key, array_column($res_object, $scriptId));
		$item_data = $res_object[$field_index];
	}
	return $item_data;
}
/**
* @author  : Dharmesh Panchal
* @version : 1.0.0
* @return  : NetSuite Service items sync to WC Products ACF fields using default NS plugin filter Hook.
**/ 
add_filter('tm_ns_item_prices', 'tm_ns_item_prices_cb', 10, 3 );
function tm_ns_item_prices_cb( $prices, $searchResponse, $product_id) {
	$products = $searchResponse->searchResult->recordList->record;
	$item_cf = $products[0]->customFieldList->customField;
	//[ NS Items <> WP Products ] - updating custom fields
	$cf_list = array(
		'custitem_continent' => 'custitem_continent', 
		'custitem_stateregionhost' => 'custitem_stateregionhost', 
		'custitem_tripcountry' => 'custitem_tripcountry', 
		'custitem_productline' => 'custitem_productline', 
		'custitem_explorerluxury' => 'custitem_explorerluxury', 
		'custitem_totaltripqtyavailable' => 'custitem_totaltripqtyavailable', 
		'custitem_totaltripqtybooked' => 'custitem_totaltripqtybooked', 
		'custitem_totaltripqtyremain' => 'custitem_totaltripqtyremain', 
		'custitem_itemstatus' => 'custitem_itemstatus', 
		'custitem_triplength' => 'custitem_triplength', 
		'custitem_tripstartdate' => 'custitem_tripstartdate', 
		'custitem_tripenddate' => 'custitem_tripenddate', 
		'custitem_daystotrip' => 'custitem_daystotrip', 
		'custitem_year' => 'custitem_year', 
		'custitem_month' => 'custitem_month', 
		'custitem_season' => 'custitem_season', 
		'custitem_ridertype' => 'custitem_ridertype', 
		'custitem_remove_stella' => 'custitem_remove_stella', 
		'custitem_ob_noroommate' => 'custitem_ob_noroommate', 
		'custitem_ob_no_carbon_wheels' => 'custitem_ob_no_carbon_wheels', 
		'trip_code_hierarchy' => 'trip_code_hierarchy', 
		'custitem_minimumage' => 'custitem_minimumage', 
		'custitem_salestaxrate' => 'custitem_salestaxrate', 
		'custitem_taxcodename' => 'custitem_taxcodename', 
		'custitem_singlesupplement' => 'custitem_singlesupplement', 
		'price' => 'custitem_ns_price'
	);
	if( $cf_list ){
		foreach( $cf_list as $cf_item_ns_key => $cf_item_acf_key ){
			$cf_item_data = get_trek_ns_item_data($item_cf, $cf_item_ns_key);
			if( $cf_item_data && isset( $cf_item_data->value ) ){
				if( is_object( $cf_item_data->value ) ){
					update_post_meta($product_id, $cf_item_acf_key, $cf_item_data->value->name );
				}else{
					update_post_meta($product_id, $cf_item_acf_key, $cf_item_data->value );
				}
			}
		}
	}
	return $prices;
}
<?php
$medical_fields = array(
	'custentity_medications' => '1. Are you currently taking any medications?',
	'custentity_medicalconditions' => '2. Do you have any medical conditions?',
	'custentity_allergies' => '3. Do you have any allergies?',
	'custentity_dietaryrestrictions' => '4. Do you have any dietary restrictions?'
);
$order_id = $_REQUEST['order_id'];
$order = wc_get_order($order_id);
$userInfo = wp_get_current_user();
$user_id = $userInfo->ID;
$order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$userInfo = wp_get_current_user();
$accepted_p_ids = tt_get_line_items_product_ids();
$guest_emails_arr = trek_get_guest_emails($user_id, $order_id);
$User_order_info = trek_get_user_order_info($user_id, $order_id);
// $waiver_status = tt_get_waiver_status($order_id);
$guest_is_primary = isset($User_order_info[0]['guest_is_primary']) ? $User_order_info[0]['guest_is_primary'] : 0;
$guest_emails = implode(', ', $guest_emails_arr);
$trek_formatted_checkoutData = $trek_checkoutData = array();
$trip_name = $trip_order_date = '';
$trip_name = $trip_sdate = $trip_edate = $trip_sku = '';
$order_item;
$booked_trip_id = null;
foreach ($order_items as $item_id => $item) {
	$product_id = $item['product_id'];
	if (!in_array($product_id, $accepted_p_ids)) {
		$order_item = $item;
		$booked_trip_id = $product_id;
		$product = $item->get_product();
		$trek_checkoutData = wc_get_order_item_meta($item_id, 'trek_user_checkout_data', true);
		$trek_formatted_checkoutData = wc_get_order_item_meta($item_id, 'trek_user_formatted_checkout_data', true);
		if ($product) {
			$trip_status = $product->get_attribute('pa_trip-status');
			$trip_sdate = $product->get_attribute('pa_start-date');
			$trip_edate = $product->get_attribute('pa_end-date');
			$trip_name = $product->get_name();
			$trip_sku = $product->get_sku();
			$sdate_obj = explode('/', $trip_sdate);
			$sdate_info = array(
				'd' => $sdate_obj[0],
				'm' => $sdate_obj[1],
				'y' => substr(date('Y'), 0, 2) . $sdate_obj[2]
			);
			$edate_obj = explode('/', $trip_edate);
			$edate_info = array(
				'd' => $edate_obj[0],
				'm' => $edate_obj[1],
				'y' => substr(date('Y'), 0, 2) . $edate_obj[2]
			);
			$start_date_text = date('F jS, Y', strtotime(implode('-', $sdate_info)));
			$end_date_text_1 = date('F jS, Y', strtotime(implode('-', $edate_info)));
			$end_date_text_2 = date('jS, Y', strtotime(implode('-', $edate_info)));
			$date_range_1 = $start_date_text . ' - ' . $end_date_text_2;
			$date_range_2 = $start_date_text . ' - ' . $end_date_text_1;
			$date_range = $date_range_1;
			if ($sdate_info['m'] != $edate_info['m']) {
				$date_range = $date_range_2;
			}
			$product_image_url = 'https://via.placeholder.com/150?text=Trek Travel';
			if (has_post_thumbnail($product_id) && $product_id) {
				$product_image_url = get_the_post_thumbnail_url($product_id);
			}
		}
	}
}
$primary_address_1 = $trek_checkoutData['shipping_address_1'];
$primary_address_2 = $trek_checkoutData['shipping_address_2'];
$primary_country = $trek_checkoutData['shipping_country'];
$billing_add_1 = $trek_checkoutData['billing_address_1'];
$billing_add_2 = $trek_checkoutData['billing_address_2'];
$billing_country = $trek_checkoutData['billing_country'];
$billing_name = ($trek_checkoutData['billing_first_name'] ? $trek_checkoutData['billing_first_name'] . ' ' . $trek_checkoutData['billing_last_name'] : '');
$shipping_name = ($trek_checkoutData['shipping_first_name'] ? $trek_checkoutData['shipping_first_name'] . ' ' . $trek_checkoutData['shipping_last_name'] : '');
$biller_name = (!empty($billing_name) ? $billing_name : $shipping_name);
$emergence_cfname = isset($User_order_info[0]['emergency_contact_first_name']) ? $User_order_info[0]['emergency_contact_first_name'] : '';
$emergence_clname = isset($User_order_info[0]['emergency_contact_last_name']) ? $User_order_info[0]['emergency_contact_last_name'] : '';
$emergence_cphone = isset($User_order_info[0]['emergency_contact_phone']) ? $User_order_info[0]['emergency_contact_phone'] : '';
$emergence_crelationship = isset($User_order_info[0]['emergency_contact_relationship']) ? $User_order_info[0]['emergency_contact_relationship'] : '';
$medicalconditions = isset($User_order_info[0]['medical_conditions']) ? $User_order_info[0]['medical_conditions'] : '';
$medications = isset($User_order_info[0]['medications']) ? $User_order_info[0]['medications'] : '';
$allergies = isset($User_order_info[0]['allergies']) ? $User_order_info[0]['allergies'] : '';
$dietaryrestrictions = isset($User_order_info[0]['dietary_restrictions']) ? $User_order_info[0]['dietary_restrictions'] : '';

$waiver_signed = isset($User_order_info[0]['waiver_signed']) ? $User_order_info[0]['waiver_signed'] : false;
$passport_number = isset($User_order_info[0]['passport_number']) ? $User_order_info[0]['passport_number'] : '';
$passport_issue_date = isset($User_order_info[0]['passport_issue_date']) ? $User_order_info[0]['passport_issue_date'] : '';
$passport_expiration_date = isset($User_order_info[0]['passport_expiration_date']) ? $User_order_info[0]['passport_expiration_date'] : '';
$passport_place_of_issue = isset($User_order_info[0]['passport_place_of_issue']) ? $User_order_info[0]['passport_place_of_issue'] : '';
$full_name_on_passport = isset($User_order_info[0]['full_name_on_passport']) ? $User_order_info[0]['full_name_on_passport'] : '';
$rider_height = isset($User_order_info[0]['rider_height']) ? $User_order_info[0]['rider_height'] : '';
$rider_level = isset($User_order_info[0]['rider_level']) ? $User_order_info[0]['rider_level'] : '';
$bike_id = isset($User_order_info[0]['bike_id']) ? $User_order_info[0]['bike_id'] : '';
$bike_size = isset($User_order_info[0]['bike_size']) ? $User_order_info[0]['bike_size'] : '';
$pedal_selection = isset($User_order_info[0]['pedal_selection']) ? $User_order_info[0]['pedal_selection'] : '';
$bikeTypeId = isset($User_order_info[0]['bikeTypeId']) ? $User_order_info[0]['bikeTypeId'] : '';
$helmet_selection = isset($User_order_info[0]['helmet_selection']) ? $User_order_info[0]['helmet_selection'] : '';
$saddle_height = isset($User_order_info[0]['saddle_height']) ? $User_order_info[0]['saddle_height'] : '';
$saddle_bar_reach_from_saddle = isset($User_order_info[0]['saddle_bar_reach_from_saddle']) ? $User_order_info[0]['saddle_bar_reach_from_saddle'] : '';
$saddle_bar_height_from_wheel_center = isset($User_order_info[0]['saddle_bar_height_from_wheel_center']) ? $User_order_info[0]['saddle_bar_height_from_wheel_center'] : '';
$jersey_style = isset($User_order_info[0]['jersey_style']) ? $User_order_info[0]['jersey_style'] : '';
$tt_jersey_size = isset($User_order_info[0]['tt_jersey_size']) ? $User_order_info[0]['tt_jersey_size'] : '';
$tshirt_size = isset($User_order_info[0]['tshirt_size']) ? $User_order_info[0]['tshirt_size'] : '';
$shorts_bib_size = isset($User_order_info[0]['shorts_bib_size']) ? $User_order_info[0]['shorts_bib_size'] : '';
$trip_room_selection = isset($User_order_info[0]['trip_room_selection']) ? $User_order_info[0]['trip_room_selection'] : '';
$guest_is_primary = isset($User_order_info[0]['guest_is_primary']) ? $User_order_info[0]['guest_is_primary'] : '';
$own_bike = isset($User_order_info[0]['own_bike']) ? $User_order_info[0]['own_bike'] : 'no';
//Trek Insurance
$guest_insurance_html = tt_guest_insurance_output($trek_checkoutData);
$public_view_order_url = '';
if ($guest_is_primary == 1) {
	$public_view_order_url = esc_url($order->get_view_order_url());
}
$itinerary_link = tt_get_itinerary_link($trip_name);
$tt_rooms_output = tt_rooms_output($trek_checkoutData, true);
$ns_booking_info = tt_get_ns_booking_details_by_order($order_id);
$waiver_link = $ns_booking_info['waiver_link'];
$locked_user_ids_bike = get_post_meta($booked_trip_id, 'ns_registration_ids_bike', true);
$locked_user_ids_record = get_post_meta($booked_trip_id, 'ns_registration_ids_record', true);

//get current user id
$current_user_id = get_current_user_id();

$lockedUserBike = 0;
$lockedUserRecord = 0;
//Check if current user id is in locked user ids
if ( is_array( $locked_user_ids_bike ) && in_array( $current_user_id, $locked_user_ids_bike ) ) {
	$lockedUserBike = 1;
}

if ( is_array( $locked_user_ids_record ) && in_array($current_user_id, $locked_user_ids_record ) ) {
	$lockedUserRecord = 1;
}

$bikeUpgradePrice = 0;
$bikePriceCurr = '';
if ($trip_sku) {
	$bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', '', $trip_sku, true);
	$bikePriceCurr = get_woocommerce_currency_symbol() . $bikeUpgradePrice;
}
$trip_information = tt_get_trip_pid_sku_from_cart($order_id);
$product_image_url = $trip_information['parent_trip_image'];
$tripRegion = tt_get_local_trips_detail('tripRegion', '', $trip_sku, true);
$pa_city = "";
$parent_product_id = tt_get_parent_trip_id_by_child_sku($trip_sku);
if( $parent_product_id ){
	$p_product = wc_get_product($parent_product_id);
	if($p_product){
        $pa_city = $p_product->get_attribute('pa_city');
    }
}

$isPassportRequired = get_post_meta($booked_trip_id, TT_WC_META_PREFIX . 'isPassportRequired', true);
$ns_booking_id = get_post_meta($order_id, TT_WC_META_PREFIX.'guest_booking_id', true);
$waiver_info = tt_get_waiver_info($ns_booking_id);

$tripProductLine    = wc_get_product_term_ids( $parent_product_id, 'product_cat' );
$hideJerseyForTrips = [ 710, 744, 712, 713 ];
$hideme = "";

$bike_pointer_none = '';
$gear_pointer_none = '';

if ( ! empty( $tripProductLine) && is_array( $tripProductLine ) && ! empty( $hideJerseyForTrips ) && is_array( $hideJerseyForTrips ) ) {
	$product_cat_matches = array_intersect( $tripProductLine, $hideJerseyForTrips );
	if ( 0 < count( $product_cat_matches ) && is_array( $product_cat_matches ) ) {
		if ( in_array( 712, $product_cat_matches ) || in_array( 744, $product_cat_matches ) ) {
			$hideme = "d-none";
		} elseif ( in_array( 710, $product_cat_matches ) && in_array( 713, $product_cat_matches ) ) {
			$hideme = "d-none";
		} else {
			$hideme = "none";
		}
	}
}

// Take user preferences from user postmeta.
$current_user_preferences = dx_get_user_pb_preferences( $user_id );

// Populate medical info user preferences if there is no value confirmed yet.
// If the user confirms 'no' for any medical info field,
// we will have a value 'none' for the given field, which is not empty.
if( empty( $medications ) && ! empty( $current_user_preferences['med_info_medications'] ) ) {
	$medications = $current_user_preferences['med_info_medications'];
}

if( empty( $medicalconditions ) && ! empty( $current_user_preferences['med_info_medical_conditions'] ) ) {
	$medicalconditions = $current_user_preferences['med_info_medical_conditions'];
}

if( empty( $allergies ) && ! empty( $current_user_preferences['med_info_allergies'] ) ) {
	$allergies = $current_user_preferences['med_info_allergies'];
}

if( empty( $dietaryrestrictions ) && ! empty( $current_user_preferences['med_info_dietary_restrictions'] ) ) {
	$dietaryrestrictions = $current_user_preferences['med_info_dietary_restrictions'];
}

// Populate emergency contact info user preferences if no value is confirmed yet.

if( empty( $emergence_cfname ) && ! empty( $current_user_preferences['em_info_em_contact_firstname'] ) ) {
	$emergence_cfname = $current_user_preferences['em_info_em_contact_firstname'];
}

if( empty( $emergence_clname ) && ! empty( $current_user_preferences['em_info_em_contact_lastname'] ) ) {
	$emergence_clname = $current_user_preferences['em_info_em_contact_lastname'];
}

if( empty( $emergence_cphone ) && ! empty( $current_user_preferences['em_info_em_contact_phonenumber'] ) ) {
	$emergence_cphone = $current_user_preferences['em_info_em_contact_phonenumber'];
}

if( empty( $emergence_crelationship ) && ! empty( $current_user_preferences['em_info_em_contact_relationship'] ) ) {
	$emergence_crelationship = $current_user_preferences['em_info_em_contact_relationship'];
}

$confirmed_info_user         = get_user_meta( $user_id, 'pb_checklist_cofirmations', true );
$confirmed_info_unserialized = maybe_unserialize( $confirmed_info_user );

$is_section_confirmed = array(
	'medical_section'       => false,
	'emergency_section'     => false,
	'gear_section'          => false,
	'passport_section'      => false,
	'bike_section'          => false,
	'gear_optional_section' => false,
);

// Assign confirmed sections.
if( !empty( $confirmed_info_unserialized ) ) {
	// Has confirmations for some orders.
	if( !empty( $confirmed_info_unserialized[ $order_id ] ) ) {
		// Has confirmations for current order.
		foreach( $confirmed_info_unserialized[ $order_id ] as $section => $value ) {
			// Loop the section's confirmations.
			$is_section_confirmed[ $section ] = $value;
		}
	}
}

?>
<div class="container my-trips-checklist my-4">
	<div class="row mx-0 flex-column flex-lg-row">
		<div class="col-lg-6 my-trips__back order-1 order-lg-0">
			<a class="text-decoration-none" href="<?php echo site_url('my-account/my-trips'); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to My trips</span></a>
		</div>
		<div class="col-lg-6 d-flex dashboard__log">
			<p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
			<a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
		</div>
	</div>

	<div id="my-trips-responses"></div>

	<div class="row mx-0">
		<div class="col-lg-10">
			<div class="card dashboard__card rounded-1">

				<div class="trips-list-item desktop-hideme">
					<div class="trip-image">
						<img src="<?php echo $product_image_url; ?>" />
						<div class="trip-info">
							<p class="fw-normal fs-sm lh-sm mb-0 mt-4">
								<?php
								$trip_address = [$pa_city, $tripRegion];
								$trip_address = array_filter($trip_address);
								echo implode(', ', $trip_address);
								?>
							</p>
							<h5 class="fw-semibold"><?php echo $trip_name; ?></h5>
							<p class="fw-medium fs-sm lh-sm"><?php echo $date_range; ?></p>

						</div>
					</div>
					<div class="booking-info">
						<div class="trip-confirmation">
							<p class="fw-medium fs-lg lh-lg line-item-title">Confirmation #</p>
							<p class="fw-normal fs-md lh-md"><?php echo $order_id ?></p>
						</div>
						<div class="trip-total">
							<p class="fw-medium fs-lg lh-lg line-item-title">Trip Total</p>
							<p class="fw-normal fs-md lh-md"><?php echo $order->get_formatted_order_total($order_item) ?></p>
						</div>
					</div>
					<hr>
					<div class="guests-info">
						<div class="trip-guests">
							<p class="fw-medium fs-lg lh-lg line-item-title">Guests</p>
							<p class="fw-normal fs-md lh-md"><?php echo $trek_checkoutData['no_of_guests']; ?> Guests Attending</p>
						</div>
						<div class="guests-room">
							<p class="fw-medium fs-lg lh-lg line-item-title">Room Selection</p>
							<?php echo $tt_rooms_output; ?>
						</div>
					</div>
					<div class="trip-details-cta my-4">
						<?php if ($public_view_order_url) : ?>
							<a href="<?php echo $public_view_order_url; ?>" class="btn btn-md w-100 btn-primary rounded-1 mb-3">View order summary</a>
						<?php endif; ?>
						<?php if ($itinerary_link) : ?>
							<a href="<?php echo $itinerary_link; ?>" class="btn btn-md w-100 btn-secondary btn-outline-dark rounded-1">View full itinerary</a>
						<?php endif; ?>
					</div>
				</div>

				<!-- desktop start -->
				<div class="trip-checklist-desktop mobile-hideme">
					<div class="trips-list-item">
						<div class="trip-image">
							<img src="<?php echo $product_image_url; ?>">
						</div>
						<div class="trip-info">
							<p class="fw-normal fs-sm lh-sm mb-0 mt-4 mt-lg-0">
								<?php
								$trip_address = [$pa_city, $tripRegion];
								$trip_address = array_filter($trip_address);
								echo implode(', ', $trip_address);
								?>
							</p>
							<h5 class="fw-semibold"><?php echo $trip_name; ?></h5>
							<p class="fw-medium fs-sm lh-sm"><?php echo $date_range; ?></p>
						</div>
						<div class="trip-details-cta my-4 my-lg-0">
							<?php if ($public_view_order_url) : ?>
								<a href="<?php echo $public_view_order_url; ?>" class="btn btn-md w-100 btn-primary rounded-1 mb-3">View order summary</a>
							<?php endif; ?>
							<?php if ($itinerary_link) : ?>
								<a href="<?php echo $itinerary_link; ?>" class="btn btn-md w-100 btn-secondary btn-outline-dark rounded-1">View full itinerary</a>
							<?php endif; ?>
						</div>
					</div>

					<div class="container">
						<div class="booking-info d-flex">
							<div class="trip-confirmation w-50">
								<p class="fw-medium fs-lg lh-lg line-item-title">Confirmation #</p>
								<p class="fw-normal fs-md lh-md"><?php echo $order_id ?></p>
							</div>
							<div class="trip-total">
								<p class="fw-medium fs-lg lh-lg line-item-title">Trip Total</p>
								<p class="fw-normal fs-md lh-md"><?php echo $order->get_formatted_order_total($order_item) ?></p>
							</div>
						</div>
						<hr>
						<div class="guests-info d-flex">
							<div class="trip-guests w-50">
								<p class="fw-medium fs-lg lh-lg line-item-title">Guests</p>
								<p class="fw-normal fs-md lh-md"><?php echo $trek_checkoutData['no_of_guests']; ?> Guests Attending</p>
							</div>
							<div class="guests-room">
								<p class="fw-medium fs-lg lh-lg line-item-title">Room Selection</p>
								<?php echo $tt_rooms_output; ?>
							</div>
						</div>
					</div>
				</div>
				<!-- desktop end -->

			</div>
		</div>
	</div> <!-- row ends -->
	<div class="row mx-0">
		<div class="col-lg-10">
			<h4 class="fw-semibold">Additional Trip Information</h4>
			<p class="fw-normal fs-lg lh-lg">Please confirm the following items below [30] days before your trip start date.</p>
		</div>
	</div><!-- row ends -->

	<div class="row mx-0">
		<div class="col-lg-10 text-end">
			<a href="javascript:void(0)" class="fw-normal fs-md lh-md checklist-expand-all">Expand all</a>
			<a href="#" class="fw-normal fs-md lh-md checklist-collapse-all">Collapse all</a>
		</div>
		<div class="col-lg-10 checklist-accordion">
			<div class="accordion accordion-flush" id="accordionFlushExample">
				<!-- show for secondary guest only -->
				<?php /* if ($guest_is_primary != 1) { ?>
					<div class="accordion-item woocommerce">
						<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-shippingAddress">
							<button class="accordion-button px-0 collapsed medical_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-shippingAddress" aria-expanded="false" aria-controls="flush-collapse-shippingAddress">
								<img src="/wp-content/themes/trek-travel-theme/assets/images/error2.png">
								Confirm your shipping address
							</button>
						</p>
						<div id="flush-collapse-shippingAddress" class="accordion-collapse collapse checkout woocommerce-checkout" aria-labelledby="flush-heading-shippingAddress">
							<div class="accordion-body px-0">
								<div class="password-reset-form medical_items">
									<?php
									//pr($User_order_info[0]);
									global $woocommerce;
									$fields = $woocommerce->checkout->get_checkout_fields('shipping');
									$iter = 0;
									$field_html = '';
									$fields_size = sizeof($fields);
									$cols = 2;
									$field_includes = array(
										'shipping_address_1',
										'shipping_address_2',
										'shipping_country',
										'shipping_state',
										'shipping_city',
										'shipping_postcode'
									);
									if ($fields) {
										foreach ($fields as $key => $field) {
											if (in_array($key, $field_includes)) {
												if ($iter % $cols == 0) {
													$field_html .= '<div class="row mx-0 guest-checkout__primary-form-row">';
												}
												$field_html .= '<div class="col-md px-0 form-row"><div class="form-floating">';
												$field['placeholder'] = $field['label'];
												$field['required'] = false;
												$field['label'] = '';
												$field['input_class'] = array('form-control');
												$field['return'] = true;
												//if ($key != 'shipping_address_2') {
													//$field['custom_attributes']['required'] = "required";
												//}
												$woo_field_value = $woocommerce->checkout->get_value($key);
												if ($key == 'shipping_address_1') {
													$orderUdataVal = isset($User_order_info[0]['shipping_address_1']) ? $User_order_info[0]['shipping_address_1'] : '';
												}
												if ($key == 'shipping_address_2') {
													$orderUdataVal = isset($User_order_info[0]['shipping_address_2']) ? $User_order_info[0]['shipping_address_2'] : '';
												}
												if ($key == 'shipping_country') {
													$orderUdataVal = isset($User_order_info[0]['shipping_address_country']) ? $User_order_info[0]['shipping_address_country'] : '';
												}
												if ($key == 'shipping_state') {
													$orderUdataVal = isset($User_order_info[0]['shipping_address_state']) ? $User_order_info[0]['shipping_address_state'] : '';
												}
												if ($key == 'shipping_city') {
													$orderUdataVal = isset($User_order_info[0]['shipping_address_city']) ? $User_order_info[0]['shipping_address_city'] : '';
												}
												if ($key == 'shipping_postcode') {
													$orderUdataVal = isset($User_order_info[0]['shipping_address_zipcode']) ? $User_order_info[0]['shipping_address_zipcode'] : '';
												}
												if ($orderUdataVal) {
													$woo_field_value = $orderUdataVal;
												}
												$field_input = woocommerce_form_field($key, $field, $woo_field_value);
												$field_input = str_ireplace('<span class="woocommerce-input-wrapper">', '', $field_input);
												$field_input = str_ireplace('</span>', '', $field_input);
												$sort            = $field['priority'] ? $field['priority'] : '';
												if (isset($field['required'])) {
													$field['class'][] = 'validate-required';
												}
												if (isset($field['validate'])) {
													foreach ($field['validate'] as $validate_name) {
														$field['class'][] = 'validate-' . $validate_name . '';
													}
												}
												$container_class = isset($field['class']) ? esc_attr(implode(' ', $field['class'])) : '';
												$container_id    = esc_attr($key) . '_field';
												$pfield_container = '<p class="form-row ' . $container_class . '" id="' . $container_id . '" data-priority="' . esc_attr($sort) . '">';
												$field_input = str_ireplace($pfield_container, '', $field_input);
												$field_input = str_ireplace('<p class="form-row form-row-wide address-field" id="shipping_address_2_field" data-priority="26">', '', $field_input);
												$field_input = str_ireplace('<p class="form-row form-row-wide address-field validate-postcode" id="shipping_postcode_field" data-priority="90">', '', $field_input);
												$field_input = str_ireplace('</p>', '', $field_input);
												$field_html .= $field_input;
												$field_html .= '<label for="shipping_' . $key . '">' . $field['placeholder'] . '</label>';
												$field_html .= '</div></div>';
												if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
													$field_html .= '</div>';
												}
												$iter++;
											}
										}
									}
									echo $field_html;
									?>
								</div>
								<?php if ($lockRecord != 1) { ?>
									<div class="form-check form-check-inline mb-0">
										<input class="form-check-input" type="checkbox" name="tt_save_shipping_info" id="inlineCheck" value="yes">
										<label class="form-check-label" for="inlineCheck">Save this information for future use. This will override any existing information you have saved on your account. </label>
									</div>
									<div class="form-buttons d-flex medical-information__buttons">
										<div class="form-group align-self-center">
											<button type="submit" class="btn btn-lg btn-primary w-100 medical-information__save rounded-1" name="medical-information"><?php esc_html_e('Confirm', 'trek-travel-theme'); ?></button>
										</div>
										<div class="fs-md lh-md fw-medium text-center align-self-center">
											<a href="javascript;">Cancel</a>
										</div>
									</div>
								<?php } ?>

							</div>
						</div>
					</div> <!-- accordion-item ends -->
				<?php  } */ ?>
				<?php $medical_title_string = "Add any medical information we need to know"; ?>
					<?php if( $lockedUserRecord == 1 ) { ?>
						<?php $medical_title_string = 'Review your medical information'; ?>
						<?php $gray_out = 'disabled style="color: #666666;"'; ?>
					<?php } ?>
				<div class="accordion-item">
					<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-medicalInfo">
						<button class="accordion-button px-0 collapsed medical_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-medicalInfo" aria-expanded="false" aria-controls="flush-collapse-medicalInfo">
							<?php if( $is_section_confirmed['medical_section'] ) : ?>
								<img src="/wp-content/themes/trek-travel-theme/assets/images/success.png" alt="success icon">
							<?php else : ?>
								<img src="/wp-content/themes/trek-travel-theme/assets/images/error2.png" alt="error icon">
							<?php endif; ?>
							<?php echo $medical_title_string ?>
						</button>
					</p>
					<form name="tt-checklist-form-medical-section" method="post" novalidate>
						<div id="flush-collapse-medicalInfo" class="accordion-collapse collapse" aria-labelledby="flush-heading-medicalInfo">
							<div class="accordion-body px-0">
								<?php if( $lockedUserRecord ) { ?>
									<div class="checkout-bikes__notice d-flex flex-column flex-lg-row flex-nowrap">
										<div class="checkout-bikes__notice-icon">
											<img src="/wp-content/themes/trek-travel-theme/assets/images/checkout/checkout-warning.png">
										</div>
										<div class="checkout-bikes__notice-text">
											<p class="fw-normal fs-sm lh-sm">Looks like your trip is starting soon! If you need to make any changes to your information below, give us a call!</p>
										</div>
									</div>
								<?php } ?>
								<div class="password-reset-form medical_items">
									<fieldset>
										<?php
										$medical_field_html = '';
										if ($medical_fields) {
											foreach ($medical_fields as $medical_key => $medical_field) {
												$medical_val = '';
												if ($medical_key == 'custentity_medications') {
													$medical_val = $medications;
												}
												if ($medical_key == 'custentity_medicalconditions') {
													$medical_val = $medicalconditions;
												}
												if ($medical_key == 'custentity_allergies') {
													$medical_val = $allergies;
												}
												if ($medical_key == 'custentity_dietaryrestrictions') {
													$medical_val = $dietaryrestrictions;
												}
												$is_medical = ($medical_val && 'none' != $medical_val ? 'yes' : 'no');
												$toggleTextClass = ($medical_val && 'none' != $medical_val ? 'style="display:block;"' : 'style="display:none;"');
												$medical_field_html .= '<div class="form-group medical-information__item medical_item">
												<div class="flex-grow-1">
													<p class="fw-medium fs-lg lh-lg mb-4 mb-lg-5">' . $medical_field . '</p>
													<div class="form-check form-check-inline mb-0">
													<input ' . $gray_out . ' class="form-check-input medical_validation_checkboxes" type="radio" name="' . $medical_key . '[boolean]" id="inlineRadioYes' . $medical_key . '" value="yes" ' . ($is_medical == 'yes' ? 'checked' : '') . '>
													<label class="form-check-label" for="inlineRadioYes' . $medical_key . '">Yes</label>
													</div>
													<div class="form-check form-check-inline mb-0 ">
													<input ' . $gray_out . ' class="form-check-input medical_validation_checkboxes" type="radio" name="' . $medical_key . '[boolean]" id="inlineRadioNo' . $medical_key . '" value="no" ' . ($is_medical == 'no' ? 'checked' : '') . '>
													<label class="form-check-label" for="inlineRadioNo' . $medical_key . '">No</label>
													</div>
													<textarea name="' . $medical_key . '[value]" placeholder="Please tell us more" class="form-control rounded-1 mt-4" ' . $toggleTextClass . '>' . ( 'none' != $medical_val ? $medical_val : '') . '</textarea>
													<div class="invalid-feedback"><img class="invalid-icon" />This field is required.</div>
												</div>
											</div>';
											}
											echo $medical_field_html;
										}
										?>
										<?php if ( $lockedUserRecord != 1 ) { ?>
											<div class="form-check form-check-inline mb-0">
												<input class="form-check-input" type="checkbox" name="tt_save_medical_info" id="inlineCheck1" value="yes">
												<label class="form-check-label" for="inlineCheck1">Save this information for future use. This will override any existing information you have saved on your account. </label>
											</div>
										<?php } ?>
									</fieldset>
								</div>
								<?php if ( $lockedUserRecord != 1 ) { ?>
									<div class="form-buttons d-flex medical-information__buttons">
										<div class="form-group align-self-center">
											<button type="submit" class="btn btn-lg btn-primary w-100 medical-information__save rounded-1" name="medical-information" data-confirm="medical_section"><?php esc_html_e('Confirm', 'trek-travel-theme'); ?></button>
										</div>
										<div class="fs-md lh-md fw-medium text-center align-self-center">
											<a href="#" data-bs-toggle="collapse" data-bs-target="#flush-collapse-medicalInfo" aria-expanded="false" aria-controls="flush-collapse-medicalInfo" class="pb-checklist-cancel">Cancel</a>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
						<input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
						<input type="hidden" name="ns_booking_id" value="<?php echo $ns_booking_id; ?>" />
						<input type="hidden" name="releaseFormId" value="<?php echo isset($ns_booking_info['releaseFormId']) ? $ns_booking_info['releaseFormId'] : ''; ?>" />
						<?php wp_nonce_field('edit_trip_checklist_medical_section_action', 'edit_trip_checklist_medical_section_nonce'); ?>
					</form>
				</div> <!-- accordion-item ends -->
				<?php $emergency_title_string = "Add your emergency contact"; ?>
				<?php if( $lockedUserRecord == 1 ) { ?>
					<?php $emergency_title_string = 'Review your emergency contact'; ?>
					<?php $gray_out = 'disabled style="color: #666666;"'; ?>
				<?php } ?>
				<div class="accordion-item">
					<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-emergencyInfo">
						<button class="accordion-button px-0 collapsed emergency_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-emergencyInfo" aria-expanded="false" aria-controls="flush-collapse-emergencyInfo">
							<?php if( $is_section_confirmed['emergency_section'] ) : ?>
								<img src="/wp-content/themes/trek-travel-theme/assets/images/success.png" alt="success icon">
							<?php else : ?>
								<img src="/wp-content/themes/trek-travel-theme/assets/images/error2.png" alt="error icon">
							<?php endif; ?>
							<?php echo $emergency_title_string ?>
						</button>
					</p>
					<form name="tt-checklist-form-emergency-section" method="post" novalidate>
						<div id="flush-collapse-emergencyInfo" class="accordion-collapse collapse" aria-labelledby="flush-heading-emergencyInfo">
							<div class="accordion-body px-0">
								<?php if( $lockedUserRecord ) { ?>
									<div class="checkout-bikes__notice d-flex flex-column flex-lg-row flex-nowrap">
										<div class="checkout-bikes__notice-icon">
											<img src="/wp-content/themes/trek-travel-theme/assets/images/checkout/checkout-warning.png">
										</div>
										<div class="checkout-bikes__notice-text">
											<p class="fw-normal fs-sm lh-sm">Looks like your trip is starting soon! If you need to make any changes to your information below, give us a call!</p>
										</div>
									</div>
								<?php } ?>
								<div class="row mx-0 guest-checkout__primary-form-row">
									<div class="col-md px-0">
										<div class="form-floating">
											<input <?php echo $gray_out; ?> type="text" class="form-control emergency_validation_inputs" name="emergency_contact_first_name" id="emergency_contact_first_name" placeholder="First Name" value="<?php echo $emergence_cfname; ?>" autocomplete="given-name" required>
											<label for="emergency_contact_first_name">First Name</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
										</div>
									</div>
									<div class="col-md px-0">
										<div class="form-floating">
											<input <?php echo $gray_out; ?> type="text" class="form-control emergency_validation_inputs" name="emergency_contact_last_name" id="emergency_contact_last_name" placeholder="Last Name" value="<?php echo $emergence_clname; ?>" autocomplete="family-name" required>
											<label for="emergency_contact_last_name">Last Name</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
										</div>
									</div>
								</div>
								<div class="row mx-0 guest-checkout__primary-form-row">
									<div class="col-md px-0">
										<div class="form-floating">
											<input <?php echo $gray_out; ?> type="tel" class="form-control emergency_validation_inputs" name="emergency_contact_phone" id="emergency_contact_phone" placeholder="Phone Number" value="<?php echo $emergence_cphone; ?>" autocomplete="given-name" required>
											<label for="emergency_contact_phone">Phone Number</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
										</div>
									</div>
									<div class="col-md px-0">
										<div class="form-floating">
											<input <?php echo $gray_out; ?> type="text" class="form-control emergency_validation_inputs" name="emergency_contact_relationship" id="emergency_contact_relationship" placeholder="Phone Number" value="<?php echo $emergence_crelationship; ?>" autocomplete="given-name" required>
											<label for="emergency_contact_relationship">Relationship to You</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
											<label for="emergency_contact_address_2">Relationship to You</label>
										</div>
									</div>
								</div>
								<?php if ( $lockedUserRecord != 1 ) { ?>
									<div class="form-check form-check-inline mb-0">
										<input class="form-check-input" type="checkbox" name="tt_save_emergency_info" id="inlineCheck2" value="yes">
										<label class="form-check-label" for="inlineCheck2">Save this information for future use. This will override any existing information you have saved on your account. </label>
									</div>
									<div class="emergency-contact__button d-flex align-items-lg-center">
										<div class="d-flex align-items-center emergency-contact__flex">
											<button type="submit" class="btn btn-lg btn-primary fs-md lh-md emergency-contact__save" data-confirm="emergency_section">Confirm</button>
											<a href="#" data-bs-toggle="collapse" data-bs-target="#flush-collapse-emergencyInfo" aria-expanded="false" aria-controls="flush-collapse-emergencyInfo" class="emergency-contact__cancel pb-checklist-cancel">Cancel</a>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
						<input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
						<input type="hidden" name="ns_booking_id" value="<?php echo $ns_booking_id; ?>" />
						<input type="hidden" name="releaseFormId" value="<?php echo isset($ns_booking_info['releaseFormId']) ? $ns_booking_info['releaseFormId'] : ''; ?>" />
						<?php wp_nonce_field('edit_trip_checklist_emergency_section_action', 'edit_trip_checklist_emergency_section_nonce'); ?>
					</form>
				</div> <!-- accordion-item ends -->
				<?php $gray_out = ''; ?>
					<?php if ($rider_level != 5) { ?>
						<?php $title_string = 'Confirm your gear information'; ?>
					<?php if( $lockedUserRecord == 1 ) { ?>
						<?php $title_string = 'Review your gear information'; ?>
						<?php $gray_out = 'disabled style="color: #666666;"'; ?>
					<?php } ?>
				<div class="accordion-item" >
					<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-gearInfo">
						<button class="accordion-button px-0 collapsed gear_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-gearInfo" aria-expanded="false" aria-controls="flush-collapse-gearInfo">
							<?php if( $is_section_confirmed['gear_section'] ) : ?>
								<img src="/wp-content/themes/trek-travel-theme/assets/images/success.png" alt="success icon">
							<?php else : ?>
								<img src="/wp-content/themes/trek-travel-theme/assets/images/error2.png" alt="error icon">
							<?php endif; ?>
							<?php echo $title_string; ?>
						</button>
					</p>
					<form name="tt-checklist-form-gear-section" method="post" novalidate>
						<div id="flush-collapse-gearInfo" class="accordion-collapse collapse" aria-labelledby="flush-heading-gearInfo">
							<div class="accordion-body px-0">
								<?php if( $lockedUserRecord ) { ?>
									<div class="checkout-bikes__notice d-flex flex-column flex-lg-row flex-nowrap">
										<div class="checkout-bikes__notice-icon">
											<img src="/wp-content/themes/trek-travel-theme/assets/images/checkout/checkout-warning.png">
										</div>
										<div class="checkout-bikes__notice-text">
											<p class="fw-normal fs-sm lh-sm">Looks like your trip is starting soon! If you need to make any changes to your information below, give us a call!</p>
										</div>
									</div>
								<?php } ?>
								<div class="row mx-0 guest-checkout__primary-form-row">
									<div class="col-md px-0">
										<div class="form-floating">
											<select <?php echo $gray_out; ?> name="tt-rider-height" id="tt-rider-height" class="form-select gear_validation_inputs" autocomplete="address-level1" data-input-classes="" data-label="Rider Height" tabindex="-1" aria-hidden="true" required>
												<?php echo tt_items_select_options('syncHeights', $rider_height); ?>
											</select>
											<label for="emergency_contact_address_2">Rider Height</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
										</div>
									</div>
									<div class="col-md px-0">
										<div class="form-floating">
											<select <?php echo $gray_out; ?> name="tt-pedal-selection" id="tt-pedal-selection" class="form-select gear_validation_inputs" autocomplete="address-level1" data-input-classes="" data-label="Select Pedals" tabindex="-1" aria-hidden="true" required>
												<?php echo tt_items_select_options('syncPedals', $pedal_selection); ?>
											</select>
											<label for="emergency_contact_address_2">Select Pedals</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
										</div>
									</div>
								</div>
								<div class="row mx-0 guest-checkout__primary-form-row">
									<div class="col-md px-0">
										<div class="form-floating">
											<select <?php echo $gray_out; ?> name="tt-helmet-size" id="tt-helmet-size" class="form-select gear_validation_inputs" autocomplete="address-level1" data-input-classes="" data-label="Helmet Size" tabindex="-1" aria-hidden="true" required>
												<?php echo tt_items_select_options('syncHelmets', $helmet_selection); ?>
											</select>
											<label for="emergency_contact_address_2">Helmet Size</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
										</div>
									</div>
									<div class="col-md px-0 <?php echo $hideme; ?>">
										<div class="form-floating">
											<select <?php echo $gray_out; ?> name="tt-jerrsey-style" id="tt-jerrsey-style" class="form-select gear_validation_inputs tt_jersey_style_change" autocomplete="address-level1" data-input-classes="" data-label="Jersey Style" tabindex="-1" aria-hidden="true" data-guest-index="00" data-is-required="<?php echo( 'd-none' === $hideme ? 'false' : 'true' ); ?>">
												<option value="">Select Clothing Style</option>
												<?php if ( 'd-none' === $hideme ) : ?>
													<option selected value="">None</option>
												<?php endif; ?>
												<option value="men" <?php echo ($jersey_style == 'men' ? 'selected' : ''); ?>>Men's</option>
												<option value="women" <?php echo ($jersey_style == 'women' ? 'selected' : ''); ?>>Women's</option>
											</select>
											<label for="emergency_contact_address_2">Jersey Style</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
										</div>
									</div>
								</div>
								<div class="row mx-0 guest-checkout__primary-form-row gear-info-last-row <?php echo $hideme; ?>">
									<div class="col-md px-0">
										<div class="form-floating">
											<select <?php echo $gray_out; ?> name="tt-jerrsey-size" id="tt-jerrsey-size" class="form-select gear_validation_inputs" autocomplete="address-level1" data-input-classes="" data-label="Jersey Size" tabindex="-1" aria-hidden="true" data-is-required="<?php echo( 'd-none' === $hideme ? 'false' : 'true' ); ?>">
												<?php if ( 'd-none' === $hideme ) : ?>
													<option selected value="">None</option>
												<?php endif; ?>
												<?php echo tt_get_jersey_sizes($jersey_style, $tt_jersey_size); ?>
											</select>
											<label for="emergency_contact_address_2">Jersey Size</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
										</div>
									</div>
								</div>
								<?php if ( $lockedUserRecord != 1 ) { ?>
									<div class="form-check form-check-inline mb-0">
										<input class="form-check-input" type="checkbox" name="tt_save_gear_info" id="inlineCheck3" value="yes">
										<label class="form-check-label" for="inlineCheck3">Save this information for future use. This will override any existing information you have saved on your account. </label>
									</div>
									<div class="emergency-contact__button d-flex align-items-lg-center">
										<div class="d-flex align-items-center emergency-contact__flex">
											<button type="submit" class="btn btn-lg btn-primary fs-md lh-md emergency-contact__save" data-confirm="gear_section">Confirm</button>
											<a href="#" data-bs-toggle="collapse" data-bs-target="#flush-collapse-gearInfo" aria-expanded="false" aria-controls="flush-collapse-gearInfo" class="emergency-contact__cancel pb-checklist-cancel">Cancel</a>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
						<input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
						<input type="hidden" name="ns_booking_id" value="<?php echo $ns_booking_id; ?>" />
						<input type="hidden" name="releaseFormId" value="<?php echo isset($ns_booking_info['releaseFormId']) ? $ns_booking_info['releaseFormId'] : ''; ?>" />
						<?php wp_nonce_field('edit_trip_checklist_gear_section_action', 'edit_trip_checklist_gear_section_nonce'); ?>
					</form>
				</div> <!-- accordion-item ends -->
				<?php } ?>
				<?php if (isset($isPassportRequired) && $isPassportRequired == true) { ?>
					<?php $passport_title_string = "Add your passport information"; ?>
						<?php if( $lockedUserRecord == 1 ) { ?>
							<?php $passport_title_string = 'Review your passport information'; ?>
							<?php $gray_out = 'disabled style="color: #666666;"'; ?>
						<?php } ?>
					<div class="accordion-item">
						<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-passportInfo">
							<button class="accordion-button px-0 collapsed passport_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-passportInfo" aria-expanded="false" aria-controls="flush-collapse-passportInfo">
								<?php if( $is_section_confirmed['passport_section'] ) : ?>
									<img src="/wp-content/themes/trek-travel-theme/assets/images/success.png" alt="success icon">
								<?php else : ?>
									<img src="/wp-content/themes/trek-travel-theme/assets/images/error2.png" alt="error icon">
								<?php endif; ?>
								<?php echo $passport_title_string ?>
							</button>
						</p>
						<form name="tt-checklist-form-passport-section" method="post" novalidate>
							<div id="flush-collapse-passportInfo" class="accordion-collapse collapse" aria-labelledby="flush-heading-passportInfo">
								<div class="accordion-body px-0">
									<?php if( $lockedUserRecord ) { ?>
										<div class="checkout-bikes__notice d-flex flex-column flex-lg-row flex-nowrap">
											<div class="checkout-bikes__notice-icon">
												<img src="/wp-content/themes/trek-travel-theme/assets/images/checkout/checkout-warning.png">
											</div>
											<div class="checkout-bikes__notice-text">
												<p class="fw-normal fs-sm lh-sm">Looks like your trip is starting soon! If you need to make any changes to your information below, give us a call!</p>
											</div>
										</div>
									<?php } ?>
									<div class="row mx-0 guest-checkout__primary-form-row">
										<div class="col-md px-0">
											<div class="form-floating">
												<input <?php echo $gray_out; ?> type="text" class="form-control passport_validation_inputs" name="full_name_on_passport" id="full_name_on_passport" placeholder="Full name on Passport" value="<?php echo $full_name_on_passport; ?>" autocomplete="given-name" required>
												<label for="full_name_on_passport">Full name on Passport</label>
												<div class="invalid-feedback">
													<img class="invalid-icon" />
													This field is required.
												</div>
											</div>
										</div>
										<div class="col-md px-0">
											<div class="form-floating">
												<input <?php echo $gray_out; ?> type="text" class="form-control passport_validation_inputs" name="passport_number" id="passport_number" placeholder="First Name" value="<?php echo $passport_number; ?>" autocomplete="given-name" required>
												<label for="passport_number">Passport Number</label>
												<div class="invalid-feedback">
													<img class="invalid-icon" />
													This field is required.
												</div>
											</div>
										</div>
									</div>
									<div class="row mx-0 guest-checkout__primary-form-row">

										<div class="col-md px-0">
											<div class="form-floating">
												<input <?php echo $gray_out; ?> type="tel" class="form-control passport_validation_inputs" name="passport_place_of_issue" id="passport_place_of_issue" placeholder="Passport Place of issue" value="<?php echo $passport_place_of_issue; ?>" required>
												<label for="passport_place_of_issue">Passport Place of issue</label>
												<div class="invalid-feedback">
													<img class="invalid-icon" />
													This field is required.
												</div>
											</div>
										</div>
										<div class="col-md px-0">
											<div class="form-floating">
												<input <?php echo $gray_out; ?> type="date" class="form-control passport_validation_inputs" name="passport_expiration_date" id="passport_expiration_date" placeholder="Last Name" value="<?php echo $passport_expiration_date; ?>" required>
												<label for="passport_expiration_date">Passport expiration date</label>
												<div class="invalid-feedback">
													<img class="invalid-icon" />
													This field is required.
												</div>
											</div>
										</div>
									</div>
									<?php if ( $lockedUserRecord != 1 ) { ?>
										<div class="emergency-contact__button d-flex align-items-lg-center">
											<div class="d-flex align-items-center emergency-contact__flex">
												<button type="submit" class="btn btn-lg btn-primary fs-md lh-md emergency-contact__save" data-confirm="passport_section">Confirm</button>
												<a href="#" data-bs-toggle="collapse" data-bs-target="#flush-collapse-passportInfo" aria-expanded="false" aria-controls="flush-collapse-passportInfo" class="emergency-contact__cancel pb-checklist-cancel">Cancel</a>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
							<input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
							<input type="hidden" name="ns_booking_id" value="<?php echo $ns_booking_id; ?>" />
							<input type="hidden" name="releaseFormId" value="<?php echo isset($ns_booking_info['releaseFormId']) ? $ns_booking_info['releaseFormId'] : ''; ?>" />
							<?php wp_nonce_field('edit_trip_checklist_passport_section_action', 'edit_trip_checklist_passport_section_nonce'); ?>
						</form>
					</div> <!-- accordion-item ends -->
				<?php } ?>
					<?php if ($rider_level != 5 && $own_bike != 'yes' && 5270 != $bike_id ) { ?>
						<?php $gray_out = ''; ?>
						<?php $bike_review_string = 'Confirm your bike selection'; ?>
						<?php if( $lockedUserBike ) { ?>
							<?php $bike_pointer_none = 'style="pointer-events: none;"' ?>
							<?php $bike_review_string = 'Review your bike selection'; ?>
							<?php $gray_out = 'disabled style="color: #666666;"'; ?>
						<?php } ?>
					<div class="accordion-item" >
						<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-bikeInfo">
							<button class="accordion-button px-0 collapsed bike_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-bikeInfo" aria-expanded="false" aria-controls="flush-collapse-bikeInfo">
								<?php if( $is_section_confirmed['bike_section'] ) : ?>
									<img src="/wp-content/themes/trek-travel-theme/assets/images/success.png" alt="success icon">
								<?php else : ?>
									<img src="/wp-content/themes/trek-travel-theme/assets/images/error2.png" alt="error icon">
								<?php endif; ?>
								<?php echo $bike_review_string; ?>
							</button>
						</p>
						<form name="tt-checklist-form-bike-section" method="post" novalidate>
							<div  disabled <?php echo $gray_out; ?> id="flush-collapse-bikeInfo" class="accordion-collapse collapse" aria-labelledby="flush-heading-bikeInfo">
								<div  <?php echo $bike_pointer_none; ?> class="accordion-body px-0 checkout-bikes-section">
									<?php if( $lockedUserBike ) { ?>
										<div class="checkout-bikes__notice d-flex flex-column flex-lg-row flex-nowrap">
											<div class="checkout-bikes__notice-icon">
												<img src="/wp-content/themes/trek-travel-theme/assets/images/checkout/checkout-warning.png">
											</div>
											<div class="checkout-bikes__notice-text">
												<p class="fw-normal fs-sm lh-sm">Looks like your trip is starting soon! If you need to make any changes to your information below, give us a call!</p>
											</div>
										</div>
									<?php } ?>
									<div class="checkout-bikes__bike-grid d-flex flex-column flex-lg-row flex-nowrap">
										<?php
										$primary_bikeId = $bike_id;
										$primary_bikeTypeId = isset($User_order_info[0]['bike_type_id']) ? $User_order_info[0]['bike_type_id'] : ''; //$bikeTypeId;
										$primary_available_bike_html = '';
										$bikes_model_id_in = [];
										$available_bikes = tt_get_local_bike_detail($trip_sku);
										$gear_preferences_bike_type = '';
										$selected_bike_type_info = tt_ns_get_bike_type_info($primary_bikeTypeId);
										$is_selected_bike_with_upgrade = false;
										if ($selected_bike_type_info && isset($selected_bike_type_info['isBikeUpgrade']) && $selected_bike_type_info['isBikeUpgrade'] == 1) {
											// Selected bike is with upgrade
											$is_selected_bike_with_upgrade = true;
										}
										if ($available_bikes) {
											foreach ($available_bikes as $available_bike) {
												$bikeId        = $available_bike['bikeId'];
												$bikeDescr     = $available_bike['bikeDescr'];
												$bikeType      = json_decode($available_bike['bikeType'], true);
												$bikeTypeId    = $bikeType['id'];
												$bikeModel     = json_decode($available_bike['bikeModel'], true);
												$bikeModelId   = $bikeModel['id'];
												$bikeModelName = $bikeModel['name'];
												if (!in_array($bikeModelId, $bikes_model_id_in) && $bikeModelId) {
													$bikeTypeName = $bikeType['name'];
													$selected_p_bikeId = '';
													$checkedClass = '';
													$pcheckedClassIcon = 'checkout-bikes__select-bike-icon';
													if( $primary_bikeTypeId == $bikeModelId ) {
														$selected_p_bikeId = 'checked';
														$checkedClass = 'bike-selected';
														$pcheckedClassIcon .= ' checkout-bikes__selected-bike-icon';
														$gear_preferences_bike_type = $bikeTypeId;
													}
													//$bike_post_id = tt_get_postid_by_meta_key_value('netsuite_bike_type_id', $bikeTypeId);
													$bike_post_name = $bikeDescr;
													$bike_post_id = null;
													$bike_post_name_arr = explode(' ', $bike_post_name);
													unset($bike_post_name_arr[0]);
													$bike_post_name = implode(' ', $bike_post_name_arr);
													$posts = get_posts(
														array(
															'post_type' => 'bikes',
															'title'     => $bikeModelName,
														)
													);
													if ( ! empty( $posts ) && is_array( $posts ) ) {
														$bike_post_id = $posts[0]->ID;
														$bike_image_id = get_post_meta( $bike_post_id, 'bike_image', true );   
														$bike_image = wp_get_attachment_image_url( $bike_image_id, 'medium' );
													} else {
														$bike_image = get_template_directory_uri() . "/assets/images/bike-placehoder-image.png";
													}
													// if ($bike_post_id !== NULL && is_numeric($bike_post_id)) {
													// 	$bike_post_name = get_the_title($bike_post_id);
													// }
													$bikeTypeInfo = tt_ns_get_bike_type_info($bikeModelId);
													$bikeUpgradeHtml = '';
													if ($bikeTypeInfo && isset($bikeTypeInfo['isBikeUpgrade']) && $bikeTypeInfo['isBikeUpgrade'] == 1) {
														$bikeUpgradeHtml .= '<div class="checkout-bikes__price-upgrade d-flex ms-4">
													<p class="fw-normal fs-sm lh-sm">Upgrade now </p>
													<p class="fw-bold fs-sm lh-sm"> +' . $bikeUpgradePrice . '</p>
												</div>';
													}
													$is_bike_with_upgrade = ($bikeTypeInfo && isset($bikeTypeInfo['isBikeUpgrade']) && $bikeTypeInfo['isBikeUpgrade'] == 1) ? true : false;
													$disabled_bike_style = '';
													// If the selected bike has an upgrade and this bike is without an upgrade, disable it. OR - If the selected bike is without an upgrade and this bike is with an upgrade, disable it.
													if( ( $is_selected_bike_with_upgrade && ! $is_bike_with_upgrade ) || ( ! $is_selected_bike_with_upgrade && $is_bike_with_upgrade ) ) {
														// Disbale bike.
														$disabled_bike_style = 'style="opacity:0.5;pointer-events:none;"';
													}
													$primary_available_bike_html .= '<div class="checkout-bikes__bike bike_selectionElementchk ' . $checkedClass . '" data-id="' . $bikeModelId . '" data-guest-id="0" data-type-id="' . $bikeTypeId . '" ' . $disabled_bike_style . '>
											<input name="bikeModelId" ' . $selected_p_bikeId . ' type="radio" value="' . $bikeModelId . '" class="bike_validation_inputs" required>
													<div class="checkout-bikes__image d-flex justify-content-center align-content-center">
														<img src="' . $bike_image . '" alt="' . $bikeDescr . '">
														<span class="checkout-bikes__badge checkout-bikes__badge--ebike">' . $bikeTypeName . '</span>
													</div>
													<div class="checkout-bikes__title d-flex justify-content-around">
														<p class="fw-medium fs-lg lh-lg">' . $bikeModelName . '</p>
													<span class="radio-selection ' . $pcheckedClassIcon . '"></span>
												</div>
												' . $bikeUpgradeHtml . '
											</div>';
												}
												$bikes_model_id_in[] = $bikeModelId;
											}
											$primary_available_bike_html .= '<input name="bikeId" type="hidden" value="' . $bike_id . '">';
											$primary_available_bike_html .= '<input name="bikeTypeId" type="hidden" value="' . $primary_bikeTypeId . '">';
											$primary_available_bike_html .= '<input name="bike_type_id_preferences" type="hidden" value="' . esc_attr( $gear_preferences_bike_type ) . '">';
										} else {
											$primary_available_bike_html .= '<strong>No bikes available!</strong>';
										}
										$primary_available_bike_html .= '<input name="wc_order_id" type="hidden" value="' . $order_id . '">';
										echo $primary_available_bike_html;
										?>
									</div>
									<?php if ($available_bikes) : ?>
										<div class="form-floating checkout-bikes__bike-size">
											<select <?php echo $gray_out; ?> name="tt-bike-size" class="form-select tt_chk_bike_size_change bike_validation_select" id="floatingSelect1" aria-label="Floating label select example" required>
												<?php
												$bikeOpt_object = tt_get_bikes_by_trip_info_pbc('', $trip_sku, $primary_bikeTypeId, $bike_size, $bike_id);
												if ($bikeOpt_object && $bikeOpt_object['size_opts']) {
													echo $bikeOpt_object['size_opts'];
												}
												?>
											</select>
											<label for="floatingSelect">Bike size</label>
											<div class="invalid-feedback">
												<img class="invalid-icon" />
												This field is required.
											</div>
										</div>
									<?php endif; ?>
									<?php if ( $lockedUserBike != 1 ) { ?>
										<?php if ($available_bikes) : ?>
											<div class="form-check form-check-inline mb-0">
												<input class="form-check-input" type="checkbox" name="tt_save_bike_info" id="inlineCheck4" value="yes">
												<label class="form-check-label" for="inlineCheck4">Save this information for future use. This will override any existing information you have saved on your account. </label>
											</div>
										<?php endif; ?>
										<div class="emergency-contact__button d-flex align-items-lg-center">
											<div class="d-flex align-items-center emergency-contact__flex">
												<button type="submit" class="btn btn-lg btn-primary fs-md lh-md emergency-contact__save" data-confirm="bike_section">Confirm</button>
												<a href="#" data-bs-toggle="collapse" data-bs-target="#flush-collapse-bikeInfo" aria-expanded="false" aria-controls="flush-collapse-bikeInfo" class="emergency-contact__cancel pb-checklist-cancel">Cancel</a>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
							<input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
							<input type="hidden" name="ns_booking_id" value="<?php echo $ns_booking_id; ?>" />
							<input type="hidden" name="releaseFormId" value="<?php echo isset($ns_booking_info['releaseFormId']) ? $ns_booking_info['releaseFormId'] : ''; ?>" />
							<?php wp_nonce_field('edit_trip_checklist_bike_section_action', 'edit_trip_checklist_bike_section_nonce'); ?>
						</form>
					</div>
					<!-- accordion-item ends -->
					<?php $fit_review_string = 'Tell us your bike fit information'; ?>
						<?php if( $lockedUserBike ) { ?>
							<?php $fit_review_string = 'Review your bike fit information'; ?>
							<?php $gray_out = 'disabled style="color: #666666;"'; ?>
						<?php } ?>
					<div class="accordion-item">
						<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-gearInfo-optional">
							<button class="accordion-button px-0 collapsed gear_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-gearInfo-optional" aria-expanded="false" aria-controls="flush-collapse-gearInfo-optional">
								<!-- <img src="/wp-content/themes/trek-travel-theme/assets/images/error2.png"> -->
								<?php echo $fit_review_string; ?> <span class="fw-normal fs-md lh-md text-muted">(Optional)</span>
							</button>
							<span class="fw-normal fs-sm lh-sm">Comfort matters! Let our team have your bike adjusted ahead of your arrival. </span>
						</p>
						<form name="tt-checklist-form-gear-optional-section" method="post" novalidate>
							<div id="flush-collapse-gearInfo-optional" class="accordion-collapse collapse" aria-labelledby="flush-heading-gearInfo-optional">
								<div class="accordion-body px-0">
									<?php if( $lockedUserBike ) { ?>
										<div class="checkout-bikes__notice d-flex flex-column flex-lg-row flex-nowrap">
											<div class="checkout-bikes__notice-icon">
												<img src="/wp-content/themes/trek-travel-theme/assets/images/checkout/checkout-warning.png">
											</div>
											<div class="checkout-bikes__notice-text">
												<p class="fw-normal fs-sm lh-sm">Looks like your trip is starting soon! If you need to make any changes to your information below, give us a call!</p>
											</div>
										</div>
									<?php } ?>
									<div class="row mx-0 guest-checkout__primary-form-row">
										<div class="col-md px-0">
											<div class="form-floating">
												<input <?php echo $gray_out; ?> name="saddle_height" id="saddle_height" class="form-control gear_optional_validation_inputs" value="<?php echo $saddle_height ?>">
												<label for="saddle_height">Saddle Height (cm)</label>
											</div>
										</div>
										<div class="col-md px-0">
											<div class="form-floating">
												<input <?php echo $gray_out; ?> type="text" name="bar_reach" id="bar_reach" class="form-control gear_optional_validation_inputs" value="<?php echo $saddle_bar_reach_from_saddle ?>">
												<label for="bar_reach">Bar reach (cm)</label>
											</div>
										</div>
									</div>
									<div class="row mx-0 guest-checkout__primary-form-row">
										<div class="col-md px-0">
											<div class="form-floating">
												<input <?php echo $gray_out; ?> type="text" name="bar_height" id="bar_height" class="form-control gear_optional_validation_inputs" value="<?php echo $saddle_bar_height_from_wheel_center; ?>">
												<label for="bar_height">Bar Height (cm)</label>
											</div>
										</div>
										<div class="col-md px-0">
											<div class="form-floating">
											</div>
										</div>
									</div>
									<?php if ( $lockedUserBike != 1 ) { ?>
										<div class="form-check form-check-inline mb-0">
											<input <?php echo $gray_out; ?> class="form-check-input" type="checkbox" name="tt_save_gear_info_optional" id="inlineCheck5" value="yes">
											<label class="form-check-label" for="inlineCheck5">Save this information for future use. This will override any existing information you have saved on your account. </label>
										</div>
										<div class="emergency-contact__button d-flex align-items-lg-center">
											<div class="d-flex align-items-center emergency-contact__flex">
												<button type="submit" class="btn btn-lg btn-primary fs-md lh-md emergency-contact__save" data-confirm="gear_optional_section">Confirm</button>
												<a href="#" data-bs-toggle="collapse" data-bs-target="#flush-collapse-gearInfo-optional" aria-expanded="false" aria-controls="flush-collapse-gearInfo-optional" class="emergency-contact__cancel pb-checklist-cancel">Cancel</a>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
							<input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
							<input type="hidden" name="ns_booking_id" value="<?php echo $ns_booking_id; ?>" />
							<input type="hidden" name="releaseFormId" value="<?php echo isset($ns_booking_info['releaseFormId']) ? $ns_booking_info['releaseFormId'] : ''; ?>" />
							<?php wp_nonce_field('edit_trip_checklist_gear_optional_section_action', 'edit_trip_checklist_gear_optional_section_nonce'); ?>
						</form>
					</div> <!-- accordion-item ends -->
				<?php } ?>
			</div>
		</div>
	</div> <!-- row ends -->
	<?php if ($guest_is_primary != 1 ) { ?>
		<div class="row mx-0 p-0 trip-waiver-info">
			<div class="col-lg-10 waiver-col">
				<div class="card dashboard__card rounded-1">
					<p class="fw-medium fs-xl lh-xl">Trip Waiver Status</p>
					<?php if ( $waiver_info['waiver_accepted'] == 1 ) {  ?>
						<p class="fw-medium fs-lg lh-lg status-signed">Signed</p>
						<p class="fw-normal fs-sm lh-sm">You're all set here!</p>
					<?php } else { ?>
						<div class="waiver-not-signed-ctr">
							<p class="fw-medium fs-lg lh-lg status-not-signed">
								<img src="<?php echo TREK_DIR; ?>/assets/images/error2.png"> Not Signed
							</p>
							<p class="fw-normal fs-sm lh-sm">Please review & sign the waiver below before the start of your trip date.</p>
							<a class="btn btn-primary fs-md lh-md mobile-hideme" href="javascript:" target="_blank" data-bs-toggle="modal" data-bs-target="#waiver_modal">Sign Waiver</a>
							<a class="btn btn-primary fs-md lh-md desktop-hideme" href="javascript:" target="_blank" data-bs-toggle="modal" data-bs-target="#waiver_modal">View Waiver</a>
						</div>
					<?php } ?>
				</div>
			</div>
		</div> <!-- row ends -->
	<?php }else{ ?>
		<div class="row mx-0 p-0 trip-waiver-info">
			<div class="col-lg-10 waiver-col">
				<div class="card dashboard__card rounded-1">
					<p class="fw-medium fs-xl lh-xl">Trip Waiver Status</p>
						<p class="fw-medium fs-lg lh-lg status-signed">Signed</p>
						<p class="fw-normal fs-sm lh-sm">You're all set here!</p>
				</div>
			</div>
		</div> <!-- row ends -->
	<?php }?>

</div>
<!-- Begin: Travel Waiver modal form  -->
<!-- Modal -->
<div class="modal fade modal-search-filter" id="waiver_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-ns-booking-id="<?php echo esc_attr( $ns_booking_id ); ?>">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="fw-semibold modal-body__title">Trip Waiver</h4>
				<span type="button" class="btn-close close-waiver-event" data-bs-dismiss="modal" aria-label="Close">
					<i type="button" class="bi bi-x"></i>
				</span>
			</div>
			<div class="modal-body" style="padding: 0;">
				<?php if( !empty( $waiver_info['waiver_link'] ) ) : ?>
					<iframe src="<?php echo esc_url( $waiver_info['waiver_link'] ); ?>" width="100%" height="350"></iframe>
				<?php else : ?>
					<p class="p-4"><?php echo esc_html('Please check again later!'); ?></p>
				<?php endif; ?>
			</div>
		</div><!-- / .modal-content -->
	</div><!-- / .modal-dialog -->
</div><!-- / .modal -->
<!-- End: Travel Waiver modal form -->
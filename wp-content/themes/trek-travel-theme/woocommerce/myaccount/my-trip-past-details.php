<?php
$order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
$order = wc_get_order($order_id);
$tt_order_type = $order->get_meta( 'tt_wc_order_type' );
$is_order_auto_generated = 'auto-generated' == $tt_order_type ? true : false;
$tt_auto_generated_order_total_amount = $order->get_meta( 'tt_meta_total_amount' );
$userInfo = wp_get_current_user();
$user_id = $userInfo->ID;
$order_items = $order->get_items(array('line_item'));
$userInfo = wp_get_current_user();
$accepted_p_ids = tt_get_line_items_product_ids();
$guest_emails_arr = trek_get_guest_emails($user_id, $order_id);
$guest_emails = implode(', ', $guest_emails_arr);
$trek_formatted_checkoutData = $trek_checkoutData = array();
$trip_name = $trip_order_date = '';
$trip_name = $trip_sdate = $trip_edate = $trip_sku = '';
$order_item;
$User_order_info = trek_get_user_order_info($user_id, $order_id);
$guest_is_primary = isset($User_order_info[0]['guest_is_primary']) ? $User_order_info[0]['guest_is_primary'] : '';
$public_view_order_url = '';
if ($guest_is_primary == 1) {
	$public_view_order_url = esc_url($order->get_view_order_url());
}
if ($order_items) {
	$is_passport_required = '';
	foreach ($order_items as $item_id => $item) {
		$product_id = $item['product_id'];
		if (!in_array($product_id, $accepted_p_ids)) {
			$order_item = $item;
			$product = $item->get_product();
			$trek_checkoutData = wc_get_order_item_meta($item_id, 'trek_user_checkout_data', true);
			$trek_formatted_checkoutData = wc_get_order_item_meta($item_id, 'trek_user_formatted_checkout_data', true);
			if ($product) {
				$is_passport_required = get_post_meta( $product_id, TT_WC_META_PREFIX . 'isPassportRequired', true );
				$p_id = $product->get_id();
				$trip_status = tt_get_custom_product_tax_value( $p_id, 'trip-status', true );
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
				$product_image_url = get_template_directory_uri() . '/assets/images/TT-Logo.png';
				if (has_post_thumbnail($product_id) && $product_id) {
					$product_image_url = get_the_post_thumbnail_url($product_id);
				}
			}
		}
	}
}
$waiver_status = tt_get_waiver_status($order_id);
$primary_address_1 = $trek_checkoutData['shipping_address_1'];
$primary_address_2 = $trek_checkoutData['shipping_address_2'];
$primary_country = $trek_checkoutData['shipping_country'];
$billing_add_1 = $trek_checkoutData['billing_address_1'];
$billing_add_2 = $trek_checkoutData['billing_address_2'];
$billing_country = $trek_checkoutData['billing_country'];
$billing_name = ($trek_checkoutData['billing_first_name'] ? $trek_checkoutData['billing_first_name'] . ' ' . $trek_checkoutData['billing_last_name'] : '');
$shipping_name = ($trek_checkoutData['shipping_first_name'] ? $trek_checkoutData['shipping_first_name'] . ' ' . $trek_checkoutData['shipping_last_name'] : '');
$biller_name = (!empty($billing_name) ? $billing_name : $shipping_name);
$emergence_cfname = get_user_meta($user_id, 'custentity_emergencycontactfirstname', true);
$emergence_clname = get_user_meta($user_id, 'custentityemergencycontactlastname', true);
$emergence_cphone = get_user_meta($user_id, 'custentity_emergencycontactphonenumber', true);
$emergence_crelationship = get_user_meta($user_id, 'custentity_emergencycontactrelationship', true);
$medicalconditions = get_user_meta($user_id, 'custentity_medicalconditions', true);
$medications = get_user_meta($user_id, 'custentity_medications', true);
$allergies = get_user_meta($user_id, 'custentity_allergies', true);
$dietaryrestrictions = get_user_meta($user_id, 'custentity_dietaryrestrictions', true);
$trip_information = tt_get_trip_pid_sku_from_cart($order_id);
$product_image_url = $trip_information['parent_trip_image'];
$rooms_html = tt_rooms_output($trek_checkoutData, true);
//order information
$waiver_signed = isset($User_order_info[0]['waiver_signed']) ? $User_order_info[0]['waiver_signed'] : '';
$ns_booking_info = tt_get_ns_booking_details_by_order($order_id);
$waiver_link = $ns_booking_info['waiver_link'];
// $releaseFormId = isset($User_order_info[0]['releaseFormId']) ? $User_order_info[0]['releaseFormId'] : '';
// $waiver_link = add_query_arg(
// 	array(
// 		'custpage_releaseFormId' => $releaseFormId
// 	),
// 	TT_WAIVER_URL
// );
$passport_number = isset($User_order_info[0]['passport_number']) ? $User_order_info[0]['passport_number'] : '';
$passport_issue_date = isset($User_order_info[0]['passport_issue_date']) ? $User_order_info[0]['passport_issue_date'] : '';
$passport_expiration_date = isset($User_order_info[0]['passport_expiration_date']) ? $User_order_info[0]['passport_expiration_date'] : '';
$passport_place_of_issue = isset($User_order_info[0]['passport_place_of_issue']) ? $User_order_info[0]['passport_place_of_issue'] : '';
$full_name_on_passport = isset($User_order_info[0]['full_name_on_passport']) ? $User_order_info[0]['full_name_on_passport'] : '';
$rider_height = isset($User_order_info[0]['rider_height']) ? $User_order_info[0]['rider_height'] : '';
$rider_levelVal = isset($User_order_info[0]['rider_level']) ? $User_order_info[0]['rider_level'] : '';
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
//Gear information
$rider_level = tt_get_custom_item_name('syncRiderLevels', $rider_levelVal);
$bike_size = tt_get_custom_item_name('syncBikeSizes', $bike_size);
$rider_height = tt_get_custom_item_name('syncHeights', $rider_height);
$helmet_size = tt_get_custom_item_name('syncHelmets', $helmet_selection);
$bikeTypeId = tt_get_custom_item_name('syncBikeTypes', $bikeTypeId);
$bike_pedal = tt_get_custom_item_name('syncPedals', $pedal_selection);
$jersey_size = tt_get_custom_item_name('syncJerseySizes', $tt_jersey_size);
$jersey_style = tt_get_custom_item_name('syncJerseySizes', $jersey_style);
$tripRegion = tt_get_local_trips_detail('tripRegion', '', $trip_sku, true);

$SmugMugLink = tt_get_local_trips_detail( 'SmugMugLink',  '', $trip_sku, true );
$SmugMugPassword = tt_get_local_trips_detail( 'SmugMugPassword',  '', $trip_sku, true );

$wheel_upgrade = 'No';
$bike_type_id = $User_order_info[0]['bike_type_id'];
$bikeTypeInfo = tt_ns_get_bike_type_info( $bike_type_id );
if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
	$wheel_upgrade = 'Yes';
}

// Set the bike name based on bike_id value.
$local_bike_details     = tt_get_local_bike_detail( $trip_information['ns_trip_Id'], $trip_sku );
$local_bike_models_info = array_column( $local_bike_details, 'bikeModel', 'bikeId' );
$bike_name              = '';
if ( ( isset( $bike_id ) && $bike_id ) || 0 == $bike_id ) {
	switch ( $bike_id ) {
		case 5270: // I am bringing my own bike.
			$bike_name = 'Bringing own';
			break;
		case 0: // If set to 0, it means "I don't know" was picked for bike size and the bikeTypeName property will be used.
			$bike_name = $bikeTypeId;
			break;
		default: // Take the name of the bike.
			$bike_name = json_decode( $local_bike_models_info[ $bike_id ], true)[ 'name' ];
			break;
	}
}

$is_nested_dates_trip = false;
$nested_dates_period = explode( '-', $trip_sku )[1];
if( $nested_dates_period ) {
	$is_nested_dates_trip = true;
}
$parent_product_id = tt_get_parent_trip_id_by_child_sku( $trip_sku, $is_nested_dates_trip );

// Look for itineraries realation field on the product.
$itinerary_link = tt_get_itinerary_link_from_trip_itineraries( $trip_sku, $parent_product_id );

$pa_city = "";
if( $parent_product_id ){
	$p_product = wc_get_product($parent_product_id);
	if($p_product){
        $pa_city = $p_product->get_attribute('pa_city');
    }
}

$pay_amount             = isset( $trek_checkoutData['pay_amount'] ) ? $trek_checkoutData['pay_amount'] : 'full';
$cart_total_full_amount = isset( $trek_checkoutData['cart_total_full_amount'] ) ? $trek_checkoutData['cart_total_full_amount'] : '';
$cart_total             = 'deposite' === $pay_amount && ! empty( $cart_total_full_amount ) ? $cart_total_full_amount : $order->get_total( $order_item );
?>
<div class="container my-trips-checklist my-4">
	<div class="row mx-0 flex-column flex-lg-row">
		<div class="col-lg-6 my-trips__back order-1 order-lg-0">
			<a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
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
							<p class="fw-medium fs-lg lh-lg">Confirmation #</p>
							<p class="fw-normal fs-md lh-md"><?php echo $order_id ?></p>
						</div>
						<div class="trip-total">
							<p class="fw-medium fs-lg lh-lg">Trip Total</p>
							<p class="fw-normal fs-md lh-md"><?php echo $is_order_auto_generated ? wc_price( floatval( str_replace( ',', '', $tt_auto_generated_order_total_amount ) ) ) : wc_price( $cart_total ) ?></p>
						</div>
					</div>
					<hr>
					<div class="guests-info">
						<div class="trip-guests">
							<p class="fw-medium fs-lg lh-lg">Guests</p>
							<p class="fw-normal fs-md lh-md"><?php echo $trek_checkoutData['no_of_guests']; ?> Guests Attending</p>
						</div>
						<?php if( ! $is_order_auto_generated ) : ?>
						<div class="guests-room">
							<p class="fw-medium fs-lg lh-lg">Room Selection</p>
							<?php echo $rooms_html; ?>
						</div>
						<?php endif; ?>
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
								<p class="fw-medium fs-lg lh-lg">Confirmation #</p>
								<p class="fw-normal fs-md lh-md"><?php echo $order_id ?></p>
							</div>
							<div class="trip-total">
								<p class="fw-medium fs-lg lh-lg">Trip Total</p>
								<p class="fw-normal fs-md lh-md"><?php echo $is_order_auto_generated ? wc_price( floatval( str_replace( ',', '', $tt_auto_generated_order_total_amount ) ) ) : wc_price( $cart_total ) ?></p>
							</div>
						</div>
						<hr>
						<div class="guests-info d-flex">
							<div class="trip-guests w-50">
								<p class="fw-medium fs-lg lh-lg">Guests</p>
								<p class="fw-normal fs-md lh-md"><?php echo $trek_checkoutData['no_of_guests']; ?> Guests Attending</p>
							</div>
							<?php if( ! $is_order_auto_generated ) : ?>
							<div class="guests-room">
								<p class="fw-medium fs-lg lh-lg">Room Selection</p>
								<?php echo $rooms_html; ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<!-- desktop end -->

			</div>
		</div>
	</div> <!-- row ends -->

	<?php if( ! empty( $SmugMugLink ) ) { ?>
		<div class="row mx-0 p-0 trip-photo-album">
			<div class="col-lg-10">
				<div class="card dashboard__card rounded-1">
					<img src="/wp-content/themes/trek-travel-theme/assets/images/photo-album.png">
					<div class="photo-album-text">
						<h5 class="fw-semibold">Trek Travel Photo Album</h5>
						<p class="fw-normal fs-sm lh-sm">View, download, or purchase photos from your trip.</p>
						<?php if( ! empty( $SmugMugPassword ) ) { ?>
							<p class="fw-normal fs-sm lh-sm">Password to access: <strong><?php echo $SmugMugPassword; ?></strong></p>
						<?php } ?>
					</div>
					<a href=<?php echo $SmugMugLink; ?>><button class="btn btn-md btn-secondary btn-outline-dark rounded-1">View photos</button></a>
				</div>
			</div>
		</div> <!-- row ends -->
	<?php } ?>

	<div class="row mx-0 my-lg-5">
		<div class="col-lg-10">
			<h4 class="fw-semibold">Additional Trip Information</h4>
		</div>
	</div><!-- row ends -->

	<div class="row mx-0 p-0 additional-trip-info">
		<div class="col-lg-10">
			<div class="card dashboard__card rounded-1">
				<div class="medical-info">
					<p class="fw-medium fs-xl lh-xl">Medical Information</p>
					<div class="sub-head">
						<p class="fw-medium fs-md lh-md">Medications</p>
						<p class="fw-normal fs-sm lh-sm"><?php echo ($medications ? $medications : 'None'); ?></p>
					</div>
					<div class="sub-head">
						<p class="fw-medium fs-md lh-md">Medical Conditions</p>
						<p class="fw-normal fs-sm lh-sm"><?php echo ($medicalconditions ? $medicalconditions : 'None'); ?></p>
					</div>
					<div class="sub-head">
						<p class="fw-medium fs-md lh-md">Allergies</p>
						<p class="fw-normal fs-sm lh-sm"><?php echo ($allergies ? $allergies : 'None'); ?></p>
					</div>
				</div> <!-- info ends -->
				<hr>
				<div class="emergency-info">
					<p class="fw-medium fs-xl lh-xl">Emergency Contact</p>
					<div class="sub-head">
						<p class="fw-medium fs-md lh-md">Name: <?php echo ( $emergence_cfname ? $emergence_cfname . ' ' . $emergence_clname : '' ); ?></p>
						<p class="fw-normal fs-sm lh-sm">Phone: <?php echo ( $emergence_cphone ? $emergence_cphone : '' ); ?> </p>
						<p class="fw-normal fs-sm lh-sm">Relationship: <?php echo ( $emergence_crelationship ? $emergence_crelationship : '' ); ?></p>
					</div>
				</div> <!-- info ends -->
				<hr>
				<div class="gear-info">
					<p class="fw-medium fs-xl lh-xl">Gear Information</p>
					<div class="sub-head">
						<p class="fw-normal fs-sm lh-sm">Rider level: <?php echo $rider_level; ?></p>
						<?php if ( 5257 != $bike_id ) { ?>
							<p class="fw-normal fs-sm lh-sm">Bike Size: <?php echo $bike_size . ' ' . $bike_name; ?></p>
							<p class="fw-normal fs-sm lh-sm">Rider Height: <?php echo $rider_height; ?></p>
							<p class="fw-normal fs-sm lh-sm">Pedals: <?php echo $bike_pedal; ?></p>
							<p class="fw-normal fs-sm lh-sm">Helmet Size: <?php echo $helmet_size; ?></p>
							<p class="fw-normal fs-sm lh-sm">Jersey Size: <?php echo $jersey_size; ?></p>
						<?php } ?>
					</div>
				</div> <!-- info ends -->
				<?php if ( '1' === $is_passport_required ) : ?>
					<hr>
					<div class="emergency-info">
						<p class="fw-medium fs-xl lh-xl">Passport Information</p>
						<div class="sub-head">
							<p class="fw-medium fs-md lh-md"><?php echo $full_name_on_passport; ?></p>
							<p class="fw-normal fs-sm lh-sm">Passport number: <?php echo $passport_number; ?></p>
							<p class="fw-normal fs-sm lh-sm">Passport issue date: <?php echo $passport_issue_date; ?></p>
							<p class="fw-normal fs-sm lh-sm">Passport expiration date: <?php echo $passport_expiration_date; ?></p>
						</div>
					</div> <!-- info ends -->
				<?php endif; ?>
				<?php /* ?>
                <hr>
                <div class="gear-info">
                    <p class="fw-medium fs-xl lh-xl">Bike Selection</p>
                    <div class="sub-head">                        
                        <p class="fw-normal fs-sm lh-sm">Rider Height: XXXXXXXXX</p>
                        <p class="fw-normal fs-sm lh-sm">Rider Height: XXXXXXXXX</p>
                    </div>
                </div>
                <hr>
                <div class="gear-info">
                    <p class="fw-medium fs-xl lh-xl">Bike Fit Information</p>
                    <div class="sub-head">                        
                        <p class="fw-normal fs-sm lh-sm">Rider Height: XXXXXXXXX</p>
                        <p class="fw-normal fs-sm lh-sm">Rider Height: XXXXXXXXX</p>
                        <p class="fw-normal fs-sm lh-sm">Rider Height: XXXXXXXXX</p>
                        <p class="fw-normal fs-sm lh-sm">Rider Height: XXXXXXXXX</p>
                    </div>
                </div>	
				<?php */ ?>
				<!-- <hr> -->
			</div>
		</div>
	</div> <!-- row ends -->

	<div class="row mx-0 p-0 trip-waiver-info">
		<div class="col-lg-10 waiver-col">
			<div class="card dashboard__card rounded-1">
				<p class="fw-medium fs-xl lh-xl">Trip Waiver Status</p>
				<?php if ($waiver_status == 1) {  ?>
					<p class="fw-medium fs-lg lh-lg status-signed">Signed</p>
					<p class="fw-normal fs-sm lh-sm">You're all set here!</p>
				<?php } else { ?>
					<p class="fw-medium fs-lg lh-lg status-not-signed">
						<img src="<?php echo TREK_DIR; ?>/assets/images/error2.png"> Not Signed
					</p>
					<!-- <a class="btn btn-primary fs-md lh-md" href="javascript:" target="_blank" data-bs-toggle="modal" data-bs-target="#waiver_modal">Sign Waiver</a> -->
				<?php } ?>
			</div>
		</div>
	</div> <!-- row ends -->
</div>
<!-- Begin: Travel Waiver modal form  -->
<!-- Modal -->
<div class="modal fade modal-search-filter" id="waiver_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="fw-semibold modal-body__title">Trip Waiver</h4>
				<span type="button" class="btn-close close-waiver-event" data-bs-dismiss="modal" aria-label="Close">
					<i type="button" class="bi bi-x"></i>
				</span>
			</div>
			<div class="modal-body" style="padding: 0;">
				<iframe src="<?php echo $waiver_link; ?>" width="100%" height="350"></iframe>
				<!-- </form> -->
			</div>
		</div><!-- / .modal-content -->
	</div><!-- / .modal-dialog -->
</div><!-- / .modal -->
<!-- End: Travel Waiver modal form -->
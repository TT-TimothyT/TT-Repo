<?php
$medical_fields = array(
	'custentity_medications'         => '1. Are you currently taking any medications?',
	'custentity_medicalconditions'   => '2. Do you have any medical conditions?',
	'custentity_allergies'           => '3. Do you have any allergies?',
	'custentity_dietaryrestrictions' => '4. Do you have any dietary restrictions?'
);
$order_id                             = $_REQUEST['order_id'];
$order                                = wc_get_order( $order_id );
$tt_order_type                        = $order->get_meta( 'tt_wc_order_type' );
$is_order_auto_generated              = false; // 'auto-generated' === $tt_order_type ? true : false;
$tt_auto_generated_order_total_amount = $order->get_meta( 'tt_meta_total_amount' );
$userInfo                             = wp_get_current_user();
$user_id                              = $userInfo->ID;
$order_items                          = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$accepted_p_ids                       = tt_get_line_items_product_ids();
$guest_emails_arr                     = trek_get_guest_emails( $user_id, $order_id );
$user_order_info                      = trek_get_user_order_info( $user_id, $order_id );
$guest_is_primary                     = tt_validate( $user_order_info[0]['guest_is_primary'], 0);
if ( $guest_is_primary ) {
	$user_order_info = trek_get_user_order_info(null, $order_id);
}
$guest_emails                         = implode(', ', $guest_emails_arr);
$trek_formatted_checkoutData          = array();
$trek_checkoutData                    = array();
$trip_name                            = '';
$trip_order_date                      = '';
$trip_sdate                           = '';
$trip_edate                           = '';
$trip_sku                             = '';
$order_item                           = null;
$booked_trip_id                       = null;
foreach ( $order_items as $item_id => $item ) {
	$product_id = $item['product_id'];
	if ( ! in_array( $product_id, $accepted_p_ids ) ) {
		$order_item                  = $item;
		$booked_trip_id              = $product_id;
		$product                     = $item->get_product();
		$trek_checkoutData           = wc_get_order_item_meta($item_id, 'trek_user_checkout_data', true);
		$trek_formatted_checkoutData = wc_get_order_item_meta($item_id, 'trek_user_formatted_checkout_data', true);
		if ( $product ) {
			$p_id        = $product->get_id();
			$trip_status = tt_get_custom_product_tax_value( $p_id, 'trip-status', true );
			$trip_sdate  = $product->get_attribute('pa_start-date');
			$trip_edate  = $product->get_attribute('pa_end-date');
			$trip_name   = $product->get_name();
			$trip_sku    = $product->get_sku();
			$parent_trip = tt_get_parent_trip_group($trip_sku);
			// Load parent product if available
			$parent_product = $parent_trip['id'] ? wc_get_product($parent_trip['id']) : null;

			// Set parent product name and link, with fallbacks if parent is unavailable
			$parent_name = $parent_product ? $parent_product->get_name() : $product->get_name();
			$sdate_obj   = explode('/', $trip_sdate);
			$sdate_info  = array(
				'd' => $sdate_obj[0],
				'm' => $sdate_obj[1],
				'y' => substr(date('Y'), 0, 2) . $sdate_obj[2]
			);
			$edate_obj  = explode('/', $trip_edate);
			$edate_info = array(
				'd' => $edate_obj[0],
				'm' => $edate_obj[1],
				'y' => substr(date('Y'), 0, 2) . $edate_obj[2]
			);
			$start_date_text = date('F jS', strtotime(implode('-', $sdate_info)));
			$end_date_text_1 = date('F jS, Y', strtotime(implode('-', $edate_info)));
			$end_date_text_2 = date('jS, Y', strtotime(implode('-', $edate_info)));
			$date_range_1    = $start_date_text . ' - ' . $end_date_text_2;
			$date_range_2    = $start_date_text . ' - ' . $end_date_text_1;
			$date_range      = $date_range_1;
			if ( $sdate_info['m'] != $edate_info['m'] ) {
				$date_range = $date_range_2;
			}
			$product_image_url = get_template_directory_uri() . '/assets/images/TT-Logo.png';
			if ( has_post_thumbnail( $product_id ) && $product_id ) {
				$product_image_url = get_the_post_thumbnail_url( $product_id );
			}

			$sdate_str = $sdate_obj[2] . '-' . $sdate_obj[1] . '-' . $sdate_obj[0]; // YYYY-MM-DD
			$start = new DateTime($sdate_str);

			$today = new DateTime();
			$diff = $today->diff($start);
			$days_left = ($start > $today) ? $diff->days . ' days left' : 'Started';
		}
	}
}
$primary_address_1                   = $trek_checkoutData['shipping_address_1'];
$primary_address_2                   = $trek_checkoutData['shipping_address_2'];
$primary_country                     = $trek_checkoutData['shipping_country'];
$billing_add_1                       = $trek_checkoutData['billing_address_1'];
$billing_add_2                       = $trek_checkoutData['billing_address_2'];
$billing_country                     = $trek_checkoutData['billing_country'];
$billing_name                        = ( $trek_checkoutData['billing_first_name'] ? $trek_checkoutData['billing_first_name'] . ' ' . $trek_checkoutData['billing_last_name'] : '' );
$shipping_name                       = ( $trek_checkoutData['shipping_first_name'] ? $trek_checkoutData['shipping_first_name'] . ' ' . $trek_checkoutData['shipping_last_name'] : '' );
$biller_name                         = ( ! empty( $billing_name ) ? $billing_name : $shipping_name );
$bike_id                             = isset( $user_order_info[0]['bike_id'] ) ? $user_order_info[0]['bike_id'] : ''; // The Bike ID can be 0, so avoid usage of the tt_validate and take the raw value from the DB.
$emergence_cfname                    = tt_validate( $user_order_info[0]['emergency_contact_first_name'] );
$emergence_clname                    = tt_validate( $user_order_info[0]['emergency_contact_last_name'] );
$emergence_cphone                    = tt_validate( $user_order_info[0]['emergency_contact_phone'] );
$emergence_crelationship             = tt_validate( $user_order_info[0]['emergency_contact_relationship'] );
$medicalconditions                   = tt_validate( $user_order_info[0]['medical_conditions'] );
$medications                         = tt_validate( $user_order_info[0]['medications'] );
$allergies                           = tt_validate( $user_order_info[0]['allergies'] );
$dietaryrestrictions                 = tt_validate( $user_order_info[0]['dietary_restrictions'] );
$waiver_signed                       = tt_validate( $user_order_info[0]['waiver_signed'], false );
$passport_number                     = tt_validate( $user_order_info[0]['passport_number'] );
$passport_issue_date                 = tt_validate( $user_order_info[0]['passport_issue_date'] );
$passport_expiration_date            = tt_validate( $user_order_info[0]['passport_expiration_date'] );
$passport_place_of_issue             = tt_validate( $user_order_info[0]['passport_place_of_issue'] );
$full_name_on_passport               = tt_validate( $user_order_info[0]['full_name_on_passport'] );
$rider_height                        = tt_validate( $user_order_info[0]['rider_height'] );
$rider_level                         = tt_validate( $user_order_info[0]['rider_level'] );
$bike_size                           = tt_validate( $user_order_info[0]['bike_size'] );
$pedal_selection                     = tt_validate( $user_order_info[0]['pedal_selection'] );
$bikeTypeId                          = tt_validate( $user_order_info[0]['bikeTypeId'] );
$helmet_selection                    = tt_validate( $user_order_info[0]['helmet_selection'] );
$saddle_height                       = tt_validate( $user_order_info[0]['saddle_height'] );
$saddle_bar_reach_from_saddle        = tt_validate( $user_order_info[0]['saddle_bar_reach_from_saddle'] );
$saddle_bar_height_from_wheel_center = tt_validate( $user_order_info[0]['saddle_bar_height_from_wheel_center'] );
$jersey_style                        = tt_validate( $user_order_info[0]['jersey_style'] );
$tt_jersey_size                      = tt_validate( $user_order_info[0]['tt_jersey_size'] );
$tshirt_size                         = tt_validate( $user_order_info[0]['tshirt_size'] );
$shorts_bib_size                     = tt_validate( $user_order_info[0]['shorts_bib_size'] );
$trip_room_selection                 = tt_validate( $user_order_info[0]['trip_room_selection'] );
$public_view_order_url               = '';
if ( $guest_is_primary == 1 ) {
	$public_view_order_url = esc_url($order->get_view_order_url());
}
$itinerary_link  = '';
$tt_rooms_output = tt_rooms_output($trek_checkoutData, true);
$ns_booking_info = tt_get_ns_booking_details_by_order($order_id);
$waiver_link     = $ns_booking_info['waiver_link'];

// Get current user id.
$current_user_id  = get_current_user_id();
$lockedUserBike   = tt_is_registration_locked( $current_user_id, $user_order_info[0]['guestRegistrationId'], 'bike' );
$lockedUserRecord = tt_is_registration_locked( $current_user_id, $user_order_info[0]['guestRegistrationId'], 'record' );
$bikeUpgradePrice = 0;
$bikePriceCurr    = '';
$trip_id          = '';
if ( $trip_sku ) {
	$trip_id          = get_post_meta( $booked_trip_id, TT_WC_META_PREFIX . 'tripId', true );
	$bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', $trip_id, $trip_sku, true);
	$bikePriceCurr    = get_woocommerce_currency_symbol() . $bikeUpgradePrice;
}
$trip_information  = tt_get_trip_pid_sku_from_cart($order_id);
$product_image_url = $trip_information['parent_trip_image'];
$tripRegion        = tt_get_local_trips_detail('tripRegion', $trip_id, $trip_sku, true);

$is_nested_dates_trip = false;
$nested_dates_period  = explode( '-', $trip_sku )[1];
if ( $nested_dates_period ) {
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

$isPassportRequired = get_post_meta($booked_trip_id, TT_WC_META_PREFIX . 'isPassportRequired', true);
$ns_booking_id      = get_post_meta($order_id, TT_WC_META_PREFIX.'guest_booking_id', true);

$bike_pointer_none = '';
$gear_pointer_none = '';

// Reusing the already built logic here.
$jersey_hideme = '';

// Get the trip style object, if it matches one of the items from the array, then we hide the jersey options.
$trip_style           = json_decode( tt_get_local_trips_detail( 'subStyle', $trip_id, $trip_sku, true ) );

$trip_style_name      = $trip_style ? $trip_style->name : '';

// The trip sub-style includes either "Training", "Discover", or "Self-Guided" = hide jersey options.
$hide_jersey_for_arr  = array( 'Training', 'Discover', 'Self-Guided' );

// Again reusing the logic with the hideme that we have on other parts of the template.
if ( in_array( $trip_style_name, $hide_jersey_for_arr ) ) {
	$jersey_hideme = 'd-none';
} else {
	$jersey_hideme = 'none';
}

// Take user preferences from user postmeta.
$current_user_preferences = dx_get_user_pb_preferences( $user_id );

// Populate medical info user preferences if there is no value confirmed yet.
// If the user confirms 'no' for any medical info field,
// we will have a value 'None' for the given field, which is not empty.
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

// Populate the Bike fit (optional) info user preferences if no value is confirmed yet.

if( empty( $saddle_bar_reach_from_saddle ) && ! empty( $current_user_preferences['gear_preferences_barreachfromsaddle'] ) ) {
	$saddle_bar_reach_from_saddle = $current_user_preferences['gear_preferences_barreachfromsaddle'];
}

if( empty( $saddle_height ) && ! empty( $current_user_preferences['gear_preferences_saddle_height'] ) ) {
	$saddle_height = $current_user_preferences['gear_preferences_saddle_height'];
}

if( empty( $saddle_bar_height_from_wheel_center ) && ! empty( $current_user_preferences['gear_preferences_barheightfromwheel'] ) ) {
	$saddle_bar_height_from_wheel_center = $current_user_preferences['gear_preferences_barheightfromwheel'];
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

$pay_amount             = isset( $trek_checkoutData['pay_amount'] ) ? $trek_checkoutData['pay_amount'] : 'full';
$cart_total_full_amount = isset( $trek_checkoutData['cart_total_full_amount'] ) ? $trek_checkoutData['cart_total_full_amount'] : '';
$cart_total             = 'deposite' === $pay_amount && ! empty( $cart_total_full_amount ) ? $cart_total_full_amount : $order->get_total( $order_item );

$is_hiking_checkout     = tt_is_product_line( 'Hiking', $trip_information['sku'], $trip_information['ns_trip_Id'] );

// Status icons.
$success_icon     = '<img src="' . TREK_DIR . '/assets/images/Tick.svg" class="me-2 icon-16" alt="">';
$error_icon       = '<img src="' . TREK_DIR . '/assets/images/error.svg" class="me-2 icon-16" alt="">';
$lock_icon        = '<img src="' . TREK_DIR . '/assets/images/lock.svg" class="me-2 icon-16" alt="">';
$exclamation_icon = '<img src="' . TREK_DIR . '/assets/images/exclamation.svg" class="me-2 icon-16" alt="">';
$shield_icon      = '<img src="' . TREK_DIR . '/assets/images/shield-icon.svg" class="me-2 icon-16" alt="">';
$tpp_not_accepted = '<img src="' . TREK_DIR . '/assets/images/not-accepted-protection.svg" class="icon-16 me-2" alt="">';
$tpp_accepted     = '<img src="' . TREK_DIR . '/assets/images/accepted-protection.svg" class="icon-16 me-2" alt="">';

$tpp_not_accepted_mobile = '<img src="' . TREK_DIR . '/assets/images/not-accepted-protection.svg" class="icon-22 me-2" alt="">';
$tpp_accepted_mobile     = '<img src="' . TREK_DIR . '/assets/images/accepted-protection.svg" class="icon-22 me-2" alt="">';

// Set the travel protected status.
$travel_protected = isset( $user_order_info[0]['wantsInsurance'] ) && $user_order_info[0]['wantsInsurance'] == 1 ? true : false;
// Set the declined insurance status.
$waive_insurance = isset( $user_order_info[0]['waive_insurance'] ) && $user_order_info[0]['waive_insurance'] == 1 ? true : false;

// Count the protected guests.
$travel_protected_guests_count = 0;
// Count the declined insurance guests.
$declined_insurance_guests_count = 0;
// Count of the guests.
$guest_count = count( $user_order_info );
if ( $guest_count > 0 ) {
	foreach ( $user_order_info as $guest ) {
		if ( isset( $guest['wantsInsurance'] ) && $guest['wantsInsurance'] == 1 ) {
			// If the guest has travel protection, increment the count
			$travel_protected_guests_count++;
		}

		if ( isset( $guest['waive_insurance'] ) && $guest['waive_insurance'] == 1 ) {
			// If the guest has declined travel protection, increment the count
			$declined_insurance_guests_count++;
		}
	}
}

$can_show_travel_protection = $travel_protected_guests_count < $guest_count && ( $diff->days > 14 ) ? true : false;

$can_show_decline_btn = $declined_insurance_guests_count < $guest_count && ( $declined_insurance_guests_count + $travel_protected_guests_count ) < $guest_count ? true : false;

if( $guest_is_primary ) {
	$public_view_order_url = esc_url($order->get_view_order_url());
}

$fees_product_id = tt_create_line_item_product( 'TTWP23FEES' );

// Always lock the bike selection if the user has a bike with upgrade selected.
$primary_bikeTypeId            = isset($user_order_info[0]['bike_type_id']) ? $user_order_info[0]['bike_type_id'] : ''; //$bikeTypeId;
$selected_bike_type_info       = tt_ns_get_bike_type_info($primary_bikeTypeId);
$is_selected_bike_with_upgrade = false;
if ($selected_bike_type_info && isset($selected_bike_type_info['isBikeUpgrade']) && $selected_bike_type_info['isBikeUpgrade'] == 1) {
	// Selected bike is with upgrade
	$is_selected_bike_with_upgrade = true;
	$lockedUserBike = true;
}
?>

<!-- SVG Icons templates -->
<svg class="d-none" style="display: none;">
	<symbol id="icon-error" width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect x="0.5" y="0.5" width="18" height="18" rx="9" stroke="#CE242D"/>
		<path d="M9.375 11.9375C9.16016 11.9375 9.00977 11.873 8.88086 11.7441C8.75195 11.6152 8.6875 11.4434 8.6875 11.2285V5.75C8.6875 5.55664 8.75195 5.38477 8.88086 5.25586C9.00977 5.12695 9.16016 5.0625 9.375 5.0625C9.56836 5.0625 9.74023 5.12695 9.86914 5.25586C9.99805 5.38477 10.0625 5.55664 10.0625 5.75V11.2715C10.0625 11.4648 9.99805 11.6152 9.86914 11.7441C9.74023 11.873 9.56836 11.9375 9.375 11.9375ZM9.375 12.9688C9.61133 12.9902 9.80469 13.0762 9.97656 13.2266C10.1484 13.3984 10.2344 13.6133 10.2344 13.8281C10.2344 14.0645 10.1484 14.2578 9.97656 14.4297C9.80469 14.6016 9.61133 14.666 9.375 14.666C9.11719 14.666 8.92383 14.6016 8.77344 14.4297C8.60156 14.2793 8.51562 14.0859 8.51562 13.8281C8.51562 13.5918 8.60156 13.3984 8.77344 13.2266C8.92383 13.0547 9.11719 12.9688 9.375 12.9688Z" fill="#CE242D"/>
	</symbol>

	<symbol id="icon-success" width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect width="19" height="19" rx="9.5" fill="#6B9214"/>
		<path d="M13.7801 6.22807C13.8498 6.30029 13.9051 6.38606 13.9428 6.48046C13.9806 6.57487 14 6.67606 14 6.77825C14 6.88045 13.9806 6.98164 13.9428 7.07604C13.9051 7.17045 13.8498 7.25621 13.7801 7.32844L8.53065 12.7719C8.461 12.8442 8.37829 12.9016 8.28725 12.9407C8.19622 12.9799 8.09863 13 8.00008 13C7.90153 13 7.80395 12.9799 7.71291 12.9407C7.62187 12.9016 7.53916 12.8442 7.46952 12.7719L5.21977 10.439C5.07905 10.2931 5 10.0952 5 9.88882C5 9.68246 5.07905 9.48456 5.21977 9.33864C5.36048 9.19272 5.55133 9.11075 5.75033 9.11075C5.94933 9.11075 6.14018 9.19272 6.2809 9.33864L8.00008 11.1224L12.7189 6.22807C12.7886 6.15577 12.8713 6.09841 12.9623 6.05928C13.0534 6.02014 13.1509 6 13.2495 6C13.348 6 13.4456 6.02014 13.5367 6.05928C13.6277 6.09841 13.7104 6.15577 13.7801 6.22807Z" fill="white"/>
	</symbol>
</svg>

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

	<div class="row mx-0">
		<div class="col-12">
			<div id="my-trips-responses"></div>
		</div>
	</div>

	<div class="row mx-0">
		<div class="col-12">
			<div class="card mb-5 dashboard__card rounded-1 my-trips-card">
				<div class="card-body pb-0">
					<?php
					
						$trips_html .= '<div class="dashboard__trip mb-4">';

							$trips_html .= '<a href="' . $parent_trip['link'] . '" class="my-upcoming-trips position-relative d-block">';
								$trips_html .= '<img src="' . esc_url($product_image_url) . '" alt="">';
								$trips_html .= '<h6 class="dashboard__trip-badge fs-sm lh-sm fw-medium mb-2 bg-black text-white px-2 py-1 rounded-end">Upcoming</h6>';
							$trips_html .= '</a>';

							$trips_html .= '<div class="w-50">';

								$trips_html .= '<a href="' . $parent_trip['link'] . '" class="text-decoration-none">';
									$trips_html .= '<h6 class="fs-5 lh-lg fw-bold mb-1 text-dark trip-title">' . esc_html($parent_name) . '</h6>';
								$trips_html .= '</a>';

								$trips_html .= '<p class="dashboard__date-range fs-sm pt-1 lh-sm fw-normal mb-5 text-dark">' . esc_html($date_range) . ' <span class="text-muted">(' . esc_html($days_left) . ')</span></p>';

								$is_checklist_completed = tt_is_checklist_completed($userInfo->ID, $order_id, $rider_level, $product_id, $bike_id, $guest_is_primary, $waiver_signed);
								if (!$is_checklist_completed && !$lockedUserRecord && !$lockedUserBike) {
									$trips_html .= '<p class="dashboard__error general-checklist-status-ctr"><img src="' . TREK_DIR . '/assets/images/error.svg"> You have pending items</p>';

								} elseif ( !$lockedUserRecord && !$lockedUserBike ) {
									$trips_html .= '<img class="dashboard__good-to-go-badge__img" src="' . esc_url(get_template_directory_uri() . '/assets/images/Tick.svg') . '" alt="success icon">';
									$trips_html .= '<span class="mb-0 fs-sm lh-sm dashboard__good-to-go-badge__text rounded-1 p-1">You are all set!</span>';
								}

								$trips_html .= '<div class="mt-4">';
									// Add TPP status for mobile view.
									if ($travel_protected) {
										$trips_html .= '<p class="d-flex align-items-center lh-xs show-mobile fs-lg">' . $tpp_accepted_mobile . ' <b> Travel Protection </b></p>';
									} else {
										$trips_html .= '<p class="d-flex align-items-center lh-xs show-mobile fs-lg">' . $tpp_not_accepted_mobile . ' <b> Travel Protection </b></p>';
									}

									if ($lockedUserRecord || $lockedUserBike) {
										$trips_html .= '<p class="dashboard__error locked-text"><i class="fa fa-lock fa-lg" aria-hidden="true"></i> Your Checklist or a checklist item is locked</p>';
									}

									$trips_html .= '<div class="row text-xs fw-normal trip-info-list" data-page="my-trip-checklist" data-order_id="' . esc_attr($order_id) . '">';

									// Column 1
									$trips_html .= '<div class="">';
									$medical_info_status = tt_is_individual_checklist_completed('medical_section', $userInfo->ID, $order_id, $rider_level, $product_id, $bike_id, $guest_is_primary);
									$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="#flush-heading-medicalInfo" class="d-flex align-items-center text-decoration-none">' . $medical_info_status . ' Medical information</a></p>';

									if ( '1' == $isPassportRequired ) {
										$passport_info_status = $lockedUserRecord ? $lock_icon : tt_is_individual_checklist_completed('passport_section', $userInfo->ID, $order_id, $rider_level, $product_id, $bike_id, $guest_is_primary);
										$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="#flush-heading-passportInfo" class="d-flex align-items-center text-decoration-none">' . $passport_info_status . ' Passport details</a></p>';
									}

									if ($bike_id != 5270 && 5257 != $bike_id) {
										$gear_info_status = $lockedUserRecord ? $lock_icon : tt_is_individual_checklist_completed('gear_section', $userInfo->ID, $order_id, $rider_level, $product_id, $bike_id, $guest_is_primary);
										$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="#flush-heading-gearInfo" class="d-flex align-items-center text-decoration-none">' . $gear_info_status . ' Gear information</a></p>';
									}

									$emergency_info_status = tt_is_individual_checklist_completed('emergency_section', $userInfo->ID, $order_id, $rider_level, $product_id, $bike_id, $guest_is_primary);
									$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="#flush-heading-emergencyInfo" class="d-flex align-items-center text-decoration-none">' . $emergency_info_status . ' Emergency contact</a></p>';
									$trips_html .= '</div>';

									// Column 2
									$trips_html .= '<div class="">';
									if ( $bike_id != 5270 && 5257 != $bike_id && ! $is_hiking_checkout ) {
										$bike_info_status = $lockedUserBike ? $lock_icon : tt_is_individual_checklist_completed('bike_section', $userInfo->ID, $order_id, $rider_level, $product_id, $bike_id, $guest_is_primary);
										$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="#flush-heading-bikeInfo" class="d-flex align-items-center text-decoration-none">' . $bike_info_status . ' Bike selection</a></p>';

										$gear_info_optional_status = tt_is_individual_checklist_completed('gear_optional_section', $userInfo->ID, $order_id, $rider_level, $product_id, $bike_id, $guest_is_primary);
										$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="#flush-heading-gearInfo-optional" class="d-flex align-items-center text-decoration-none">' . $gear_info_optional_status . ' Bike fit information (optional)</a></p>';
									}
									if ($waiver_signed) {
										$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="#waiver-section" class="d-flex align-items-center text-decoration-none">' . $success_icon . ' Trip Waiver</a></p>';
									} else {
										$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="#waiver-section" class="d-flex align-items-center text-decoration-none">' . $error_icon . ' Trip Waiver</a></p>';
									}

									if ($travel_protected) {
										$trips_html .= '<p class="d-flex align-items-center lh-xs ">' . $tpp_accepted . ' <b> Travel Protection </b></p>';
									} else {
										$trips_html .= '<p class="d-flex align-items-center lh-xs ">' . $tpp_not_accepted . ' <b> Travel Protection </b></p>';
									}
									$trips_html .= '</div>';

									$trips_html .= '</div>'; // end .row

								$trips_html .= '</div>'; // end checklist block
							$trips_html .= '</div>'; // end trip info

							$trips_html .= '<div class="w-50 guests-booking-info">';

								$trips_html .= '<div class="booking-info d-flex mb-5">';
									$trips_html .= '<div class="trip-confirmation w-50">';
										$trips_html .= '<p class="fw-medium fs-lg lh-lg line-item-title">Confirmation #</p>';
										$trips_html .= '<p class="fw-normal fs-md lh-md">' . $order_id . '</p>';
									$trips_html .= '</div>';
									$trips_html .= '<div class="trip-guests w-50">';
										$trips_html .= '<p class="fw-medium fs-lg lh-lg line-item-title">Guests</p>';
										$trips_html .= '<p class="fw-normal fs-md lh-md">' . $trek_checkoutData['no_of_guests'] . ' Guests Attending</p>';
									$trips_html .= '</div>';
								$trips_html .= '</div>';

								$trips_html .= '<div class="guests-info d-flex">';
									if( ! $is_order_auto_generated ) {
										$trips_html .= '<div class="guests-room w-50">';
											$trips_html .= '<p class="fw-medium fs-lg lh-lg line-item-title">Room Selection</p>';
											$trips_html .= $tt_rooms_output;
										$trips_html .= '</div>';
									}
									$trips_html .= '<div class="trip-total w-50">';
										$trips_html .= '<p class="fw-medium fs-lg lh-lg line-item-title">Trip Total</p>';
										$trips_html .= '<p class="fw-normal fs-md lh-md">' . ($is_order_auto_generated ? wc_price( floatval( str_replace( ',', '', $tt_auto_generated_order_total_amount ) ) ) : wc_price( $cart_total )) . '</p>';
									$trips_html .= '</div>';
								$trips_html .= '</div>';

								// CTA Button
								$trips_html .= '<div class="mt-5 btn-checklist d-flex align-items-center justify-content-between flex-column flex-md-row">';
								if ( $public_view_order_url ) {
									$trips_html .= '<a class="btn btn-link rounded-1 fw-medium text-decoration-underline p-0 ms-auto me-3" href="' . $public_view_order_url . '">Order Details</a>';
								}
								if ($itinerary_link) {
									$trips_html .= '<a href="' . $itinerary_link . '" class="btn btn-link rounded-1 text-decoration-underline p-0">View full itinerary</a>';
								}
								$trips_html .= '</div>';

							$trips_html .= '</div>'; // end .guests-booking-info

						$trips_html .= '</div>'; // end .dashboard__trip

						// Add check for cookie with name hide_trip_insurance_info_${orderId}=true so can hide the section
						if ( ! isset( $_COOKIE[ 'hide_trip_insurance_info_' . $order_id ] ) || $_COOKIE[ 'hide_trip_insurance_info_' . $order_id ] !== 'true' ) {
							$trips_html .= '<div class="trip-insurance-info">';

								$trips_html .= '<div class="fs-5 fw-bold text-dark mb-2 travel-protection-title hide-mobile"><img src="' . TREK_DIR . '/assets/images/accepted-protection.svg" class="icon-22">Travel Protection Benefits</div>';
								$trips_html .= '<p class="fs-sm fw-semibold text-dark travel-protection-subtitle hide-mobile">Because peace of mind is the best travel companion. Protect your trip from the unexpected</p>';
								$trips_html .= '<div class="fs-5 fw-bold text-dark mb-5 travel-protection-title show-mobile"><img src="' . TREK_DIR . '/assets/images/accepted-protection.svg" class="icon-22">Travel Protection Benefits</div>';
								$trips_html .= '<div class="hide-mobile">';
								$trips_html .= '<div class="d-flex align-items-center justify-content-between"><p class="fs-sm fw-semibold text-dark lh-sm mb-0">Here\'s what you get:</p><a href="' . site_url('/travel-protection') . '" class="btn btn-link p-0 view-full-details-link" target="_blank" data-page="my-trip-checklist">View Full Details</a></div>';
								$trips_html .= '<div class="row list-elements">';
								$trips_html .= '<div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Cancel with Confidence:</strong><span>Get up to 100% back if you cancel, 150% if your trip is interrupted.*</span></div>';
								$trips_html .= '<div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Delays Happen, We’ve Got You:</strong><span>We cover meals, hotels, and transport for flight delays or missed connections.</span></div>';
								$trips_html .= '<div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Missing Luggage?:</strong><span>Lost or delayed bags? We’ll help replace essentials and cover your gear.</span></div>';
								$trips_html .= '<div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Medical Coverage Wherever You Ride or Hike</strong><span>Includes up to $50K for care and $150K for evacuation—no deductible.</span></div>';
								$trips_html .= '<div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">In Case of Real Emergencies</strong><span>Get up to $25K for security evacuation in critical situations.</span></div>';
								$trips_html .= '<div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Additional Coverage & Travel Dates</strong><span><a href="' . site_url('/contact-us') . '">Contact us</a> to insure non-refundable costs like airfare, hotels, and additional travel days.</span></div>';
								$trips_html .= '<div class="d-flex justify-content-md-end mt-3">';
								$trips_html .= '</div>';
								$trips_html .= '</div>';
								$trips_html .= '</div>';
								$trips_html .= '<ul class="show-mobile travel-protection-list">';
								$trips_html .= '<li class=""><svg xmlns="http://www.w3.org/2000/svg" width="18" height="15" viewBox="0 0 18 15" fill="none"><path d="M17.5601 0.936124C17.6996 1.08057 17.8102 1.2521 17.8857 1.44091C17.9612 1.62972 18 1.8321 18 2.03649C18 2.24088 17.9612 2.44326 17.8857 2.63207C17.8102 2.82088 17.6996 2.99241 17.5601 3.13685L7.0613 14.0238C6.922 14.1684 6.75659 14.2832 6.57451 14.3614C6.39243 14.4397 6.19726 14.48 6.00016 14.48C5.80306 14.48 5.60789 14.4397 5.42582 14.3614C5.24374 14.2832 5.07833 14.1684 4.93903 14.0238L0.439535 9.35799C0.158105 9.06615 0 8.67034 0 8.25762C0 7.84491 0.158105 7.44909 0.439535 7.15726C0.720964 6.86542 1.10266 6.70147 1.50067 6.70147C1.89867 6.70147 2.28037 6.86542 2.5618 7.15726L6.00016 10.7247L15.4379 0.936124C15.5772 0.79152 15.7426 0.676804 15.9246 0.598535C16.1067 0.520267 16.3019 0.47998 16.499 0.47998C16.6961 0.47998 16.8913 0.520267 17.0733 0.598535C17.2554 0.676804 17.4208 0.79152 17.5601 0.936124Z" fill="black"/></svg><div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Cancel with Confidence:</strong><span>Get up to 100% back if you cancel, 150% if your trip is interrupted.*</span></div></li>';
								$trips_html .= '<li class=""><svg xmlns="http://www.w3.org/2000/svg" width="18" height="15" viewBox="0 0 18 15" fill="none"><path d="M17.5601 0.936124C17.6996 1.08057 17.8102 1.2521 17.8857 1.44091C17.9612 1.62972 18 1.8321 18 2.03649C18 2.24088 17.9612 2.44326 17.8857 2.63207C17.8102 2.82088 17.6996 2.99241 17.5601 3.13685L7.0613 14.0238C6.922 14.1684 6.75659 14.2832 6.57451 14.3614C6.39243 14.4397 6.19726 14.48 6.00016 14.48C5.80306 14.48 5.60789 14.4397 5.42582 14.3614C5.24374 14.2832 5.07833 14.1684 4.93903 14.0238L0.439535 9.35799C0.158105 9.06615 0 8.67034 0 8.25762C0 7.84491 0.158105 7.44909 0.439535 7.15726C0.720964 6.86542 1.10266 6.70147 1.50067 6.70147C1.89867 6.70147 2.28037 6.86542 2.5618 7.15726L6.00016 10.7247L15.4379 0.936124C15.5772 0.79152 15.7426 0.676804 15.9246 0.598535C16.1067 0.520267 16.3019 0.47998 16.499 0.47998C16.6961 0.47998 16.8913 0.520267 17.0733 0.598535C17.2554 0.676804 17.4208 0.79152 17.5601 0.936124Z" fill="black"/></svg><div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Delays Happen, We’ve Got You:</strong><span>We cover meals, hotels, and transport for flight delays or missed connections.</span></div></li>';
								$trips_html .= '<li class=""><svg xmlns="http://www.w3.org/2000/svg" width="18" height="15" viewBox="0 0 18 15" fill="none"><path d="M17.5601 0.936124C17.6996 1.08057 17.8102 1.2521 17.8857 1.44091C17.9612 1.62972 18 1.8321 18 2.03649C18 2.24088 17.9612 2.44326 17.8857 2.63207C17.8102 2.82088 17.6996 2.99241 17.5601 3.13685L7.0613 14.0238C6.922 14.1684 6.75659 14.2832 6.57451 14.3614C6.39243 14.4397 6.19726 14.48 6.00016 14.48C5.80306 14.48 5.60789 14.4397 5.42582 14.3614C5.24374 14.2832 5.07833 14.1684 4.93903 14.0238L0.439535 9.35799C0.158105 9.06615 0 8.67034 0 8.25762C0 7.84491 0.158105 7.44909 0.439535 7.15726C0.720964 6.86542 1.10266 6.70147 1.50067 6.70147C1.89867 6.70147 2.28037 6.86542 2.5618 7.15726L6.00016 10.7247L15.4379 0.936124C15.5772 0.79152 15.7426 0.676804 15.9246 0.598535C16.1067 0.520267 16.3019 0.47998 16.499 0.47998C16.6961 0.47998 16.8913 0.520267 17.0733 0.598535C17.2554 0.676804 17.4208 0.79152 17.5601 0.936124Z" fill="black"/></svg><div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Missing Luggage?:</strong><span>Lost or delayed bags? We’ll help replace essentials and cover your gear.</span></div></li>';
								$trips_html .= '<li class=""><svg xmlns="http://www.w3.org/2000/svg" width="18" height="15" viewBox="0 0 18 15" fill="none"><path d="M17.5601 0.936124C17.6996 1.08057 17.8102 1.2521 17.8857 1.44091C17.9612 1.62972 18 1.8321 18 2.03649C18 2.24088 17.9612 2.44326 17.8857 2.63207C17.8102 2.82088 17.6996 2.99241 17.5601 3.13685L7.0613 14.0238C6.922 14.1684 6.75659 14.2832 6.57451 14.3614C6.39243 14.4397 6.19726 14.48 6.00016 14.48C5.80306 14.48 5.60789 14.4397 5.42582 14.3614C5.24374 14.2832 5.07833 14.1684 4.93903 14.0238L0.439535 9.35799C0.158105 9.06615 0 8.67034 0 8.25762C0 7.84491 0.158105 7.44909 0.439535 7.15726C0.720964 6.86542 1.10266 6.70147 1.50067 6.70147C1.89867 6.70147 2.28037 6.86542 2.5618 7.15726L6.00016 10.7247L15.4379 0.936124C15.5772 0.79152 15.7426 0.676804 15.9246 0.598535C16.1067 0.520267 16.3019 0.47998 16.499 0.47998C16.6961 0.47998 16.8913 0.520267 17.0733 0.598535C17.2554 0.676804 17.4208 0.79152 17.5601 0.936124Z" fill="black"/></svg><div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Medical Coverage Wherever You Ride or Hike</strong><span>Includes up to $50K for care and $150K for evacuation—no deductible.</span></div></li>';
								$trips_html .= '<li class=""><svg xmlns="http://www.w3.org/2000/svg" width="18" height="15" viewBox="0 0 18 15" fill="none"><path d="M17.5601 0.936124C17.6996 1.08057 17.8102 1.2521 17.8857 1.44091C17.9612 1.62972 18 1.8321 18 2.03649C18 2.24088 17.9612 2.44326 17.8857 2.63207C17.8102 2.82088 17.6996 2.99241 17.5601 3.13685L7.0613 14.0238C6.922 14.1684 6.75659 14.2832 6.57451 14.3614C6.39243 14.4397 6.19726 14.48 6.00016 14.48C5.80306 14.48 5.60789 14.4397 5.42582 14.3614C5.24374 14.2832 5.07833 14.1684 4.93903 14.0238L0.439535 9.35799C0.158105 9.06615 0 8.67034 0 8.25762C0 7.84491 0.158105 7.44909 0.439535 7.15726C0.720964 6.86542 1.10266 6.70147 1.50067 6.70147C1.89867 6.70147 2.28037 6.86542 2.5618 7.15726L6.00016 10.7247L15.4379 0.936124C15.5772 0.79152 15.7426 0.676804 15.9246 0.598535C16.1067 0.520267 16.3019 0.47998 16.499 0.47998C16.6961 0.47998 16.8913 0.520267 17.0733 0.598535C17.2554 0.676804 17.4208 0.79152 17.5601 0.936124Z" fill="black"/></svg><div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">In Case of Real Emergencies</strong><span>Get up to $25K for security evacuation in critical situations.</span></div></li>';
								$trips_html .= '<li class=""><svg xmlns="http://www.w3.org/2000/svg" width="18" height="15" viewBox="0 0 18 15" fill="none"><path d="M17.5601 0.936124C17.6996 1.08057 17.8102 1.2521 17.8857 1.44091C17.9612 1.62972 18 1.8321 18 2.03649C18 2.24088 17.9612 2.44326 17.8857 2.63207C17.8102 2.82088 17.6996 2.99241 17.5601 3.13685L7.0613 14.0238C6.922 14.1684 6.75659 14.2832 6.57451 14.3614C6.39243 14.4397 6.19726 14.48 6.00016 14.48C5.80306 14.48 5.60789 14.4397 5.42582 14.3614C5.24374 14.2832 5.07833 14.1684 4.93903 14.0238L0.439535 9.35799C0.158105 9.06615 0 8.67034 0 8.25762C0 7.84491 0.158105 7.44909 0.439535 7.15726C0.720964 6.86542 1.10266 6.70147 1.50067 6.70147C1.89867 6.70147 2.28037 6.86542 2.5618 7.15726L6.00016 10.7247L15.4379 0.936124C15.5772 0.79152 15.7426 0.676804 15.9246 0.598535C16.1067 0.520267 16.3019 0.47998 16.499 0.47998C16.6961 0.47998 16.8913 0.520267 17.0733 0.598535C17.2554 0.676804 17.4208 0.79152 17.5601 0.936124Z" fill="black"/></svg><div class=" ps-4"><span class="fs-sm text-dark"><strong class="d-block">Additional Coverage & Travel Dates</strong><span><a href="' . site_url('/contact-us') . '">Contact us</a> to insure non-refundable costs like airfare, hotels, and additional travel days.</span></div></li>';
								$trips_html .= '</ul>';


								$trips_html .= '<div class="d-flex justify-content-between align-items-center mb-5 travel-protection-footer trip-details-cta">';
									// Left link
									$trips_html .= '<a href="#" class="text-decoration-underline text-dark fw-normal text-xs hide-mobile hide-insurance-btn" data-order_id="' . esc_attr( $order_id ) . '" data-page="my-trip-checklist"><strong>Hide</strong></a>';
									if ( $guest_is_primary && $can_show_travel_protection ) {


										// Decline Travel Protection button (left)
										// Add check for cookie with name hide_travel_protection_button_${orderId}=true so can hide the button
										if ( $can_show_decline_btn ) {
											$trips_html .= '<a href="#"
															data-order_id="' . esc_attr( $order_id ) . '" 
															data-page="my-trip-checklist"
															data-bs-toggle="modal"
															data-bs-target="#tpDeclineWarningModal"
															class="btn btn-sm btn-fixed-width fw-medium rounded-1 text-white ms-auto mb-3 mb-md-0 me-md-3 trek-decline-travel-protection">
															<strong>Decline Travel Protection</strong>
														</a>';
										}
										// Add Travel Protection button (right)
										$add_tp_btn = '<a href="?add-to-cart=' . esc_attr( $fees_product_id ) . '" 
															data-product_id="' . esc_attr( $fees_product_id ) . '" 
															data-order_id="' . esc_attr( $order_id ) . '" 
															data-origin="tt_modal_checkout" 
															data-bs-toggle="modal"
															data-bs-target="#quickLookModalCheckout"
															data-page="my-trip-checklist"
															class="btn btn-sm btn-fixed-width fw-medium rounded-1 text-white ms-auto trek-add-to-cart add-travel-protection-btn" 
															style="background-color: #28AAE1;">
															<svg class="show-mobile" xmlns="http://www.w3.org/2000/svg" width="21" height="23" viewBox="0 0 21 23" fill="none">
																<path d="M19.5664 3.83936C20.3398 4.22607 20.7695 4.82764 20.8125 5.68701C20.7266 9.38232 19.9961 12.4331 18.6211 14.8823C17.2461 17.3745 15.7422 19.1792 14.1523 20.3823C12.5195 21.6284 11.3164 22.23 10.5 22.23C9.68359 22.23 8.48047 21.6284 6.84766 20.4253C5.21484 19.2222 3.75391 17.3745 2.37891 14.9253C1.00391 12.4761 0.273438 9.38232 0.1875 5.68701C0.1875 4.82764 0.617188 4.22607 1.47656 3.83936L9.72656 0.401855C9.98438 0.315918 10.2422 0.22998 10.5 0.22998C10.7578 0.22998 11.0586 0.315918 11.3164 0.401855L19.5664 3.83936ZM14.625 8.82373C14.625 8.65186 14.5391 8.43701 14.4102 8.1792C14.2383 7.96436 13.9805 7.83545 13.5938 7.74951C13.25 7.74951 12.9922 7.87842 12.8203 8.09326L9.42578 12.0894L8.13672 10.8003C7.92188 10.6284 7.66406 10.4995 7.40625 10.4995C6.76172 10.5854 6.41797 10.9292 6.375 11.5308C6.375 11.8745 6.46094 12.0894 6.67578 12.2612L8.73828 14.3237C8.91016 14.5386 9.16797 14.6245 9.46875 14.6245C9.64062 14.7104 9.89844 14.5815 10.2422 14.3237L14.3672 9.51123C14.5391 9.33936 14.625 9.08154 14.625 8.82373Z" fill="white"/>
															</svg>
															Add Travel Protection
														</a>';
										$call_us_tp_btn = '<a href="tel:8664648735"
															class="btn btn-sm btn-fixed-width fw-medium rounded-1 text-white ms-auto trek-add-to-cart add-travel-protection-btn" 
															style="background-color: #28AAE1;">
															<i class="bi bi-telephone"></i> Call Us
														</a>';
										
										$trips_html .= $add_tp_btn;

									}

								$trips_html .= '</div>';

							$trips_html .= '</div>';
						}

						echo $trips_html;

					?>
				</div>
			</div>
		</div>
	</div>

	<div class="row mx-0">
		<div class="col-12">
			<h4 class="fw-semibold">Additional Trip Information</h4>
			<?php if( $lockedUserRecord ) : ?>
				<p class="fw-normal fs-lg lh-lg">
					<?php
						printf(
							wp_kses(
								/* translators: %1$s: Phone number; */
								__( 'Your trip is starting soon! We&apos;ve locked your checklist and are sending your guides the details. Call us to make any changes ahead of your trip <a href="%1$s">866-464-8735</a>', 'trek-travel-theme' ), 
								array(
									'a' => array(
										'class'  => array(),
										'href'   => array(),
										'target' => array()
									)
								)
							),
							esc_url( 'tel:8664648735' ),
						);
						?>
				</p>
			<?php else: ?>
				<p class="fw-normal fs-lg lh-lg">Please confirm the following items below 14 days before your trip start date.</p>
			<?php endif; ?>
		</div>
	</div><!-- row ends -->

	<div class="row mx-0">
		<div class="col-12 text-end">
			<a href="javascript:void(0)" class="fw-normal fs-md lh-md checklist-expand-all">Expand all</a>
			<a href="#" class="fw-normal fs-md lh-md checklist-collapse-all">Collapse all</a>
		</div>
		<div class="col-12 checklist-accordion">
			<div class="accordion accordion-flush" id="accordionFlushExample">
				<!-- show for secondary guest only -->
				<?php /* if ($guest_is_primary != 1) { ?>
					<div class="accordion-item woocommerce">
						<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-shippingAddress">
							<button class="accordion-button px-0 collapsed medical_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-shippingAddress" aria-expanded="false" aria-controls="flush-collapse-shippingAddress">
								<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/error2.png' ); ?>">
								Confirm your shipping address
							</button>
						</p>
						<div id="flush-collapse-shippingAddress" class="accordion-collapse collapse checkout woocommerce-checkout" aria-labelledby="flush-heading-shippingAddress">
							<div class="accordion-body px-0">
								<div class="password-reset-form medical_items">
									<?php
									//pr($user_order_info[0]);
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
													$orderUdataVal = isset($user_order_info[0]['shipping_address_1']) ? $user_order_info[0]['shipping_address_1'] : '';
												}
												if ($key == 'shipping_address_2') {
													$orderUdataVal = isset($user_order_info[0]['shipping_address_2']) ? $user_order_info[0]['shipping_address_2'] : '';
												}
												if ($key == 'shipping_country') {
													$orderUdataVal = isset($user_order_info[0]['shipping_address_country']) ? $user_order_info[0]['shipping_address_country'] : '';
												}
												if ($key == 'shipping_state') {
													$orderUdataVal = isset($user_order_info[0]['shipping_address_state']) ? $user_order_info[0]['shipping_address_state'] : '';
												}
												if ($key == 'shipping_city') {
													$orderUdataVal = isset($user_order_info[0]['shipping_address_city']) ? $user_order_info[0]['shipping_address_city'] : '';
												}
												if ($key == 'shipping_postcode') {
													$orderUdataVal = isset($user_order_info[0]['shipping_address_zipcode']) ? $user_order_info[0]['shipping_address_zipcode'] : '';
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
				<?php $gray_out = ''; ?>
					<?php if( $lockedUserRecord == 1 ) { ?>
						<?php $medical_title_string = 'Review your medical information'; ?>
						<?php $gray_out = 'disabled style="color: #666666;"'; ?>
						<?php $gray_out_text = 'disabled'; ?>
					<?php } ?>
				<div class="accordion-item">
					<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-medicalInfo">
						<button class="accordion-button px-0 collapsed medical_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-medicalInfo" aria-expanded="false" aria-controls="flush-collapse-medicalInfo">
							<?php if ( $lockedUserRecord ) : ?>
								<i class="fa fa-lock fa-2x text-muted" aria-hidden="true"></i>
							<?php else: ?>
								<svg width="19" height="19" class="status-icon">
									<?php if( $is_section_confirmed['medical_section'] ) : ?>
										<use href="#icon-success"></use>
									<?php else : ?>
										<use href="#icon-error"></use>
									<?php endif; ?>
								</svg>
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
											<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-warning.png' ); ?>">
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
												$is_medical = ( $medical_val && 'none' != strtolower( $medical_val ) ? 'yes' : 'no' );

												$toggleTextClass = ( $medical_val && 'none' != strtolower( $medical_val ) ? 'style="display:block;"' : 'style="display:none;"' );

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
													<textarea ' . $gray_out_text . ' name="' . $medical_key . '[value]" placeholder="Please tell us more" class="form-control rounded-1 mt-4" ' . $toggleTextClass . '>' . ( 'none' != strtolower( $medical_val ) ? $medical_val : '') . '</textarea>
													<div class="invalid-feedback"><img class="invalid-icon" />This field is required.</div>
												</div>
											</div>';
											}
											echo $medical_field_html;
										}
										?>
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
				<?php $gray_out = ''; ?>
				<?php if( $lockedUserRecord == 1 ) { ?>
					<?php $emergency_title_string = 'Review your emergency contact'; ?>
					<?php $gray_out = 'disabled style="color: #666666;"'; ?>
				<?php } ?>
				<div class="accordion-item">
					<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-emergencyInfo">
						<button class="accordion-button px-0 collapsed emergency_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-emergencyInfo" aria-expanded="false" aria-controls="flush-collapse-emergencyInfo">
							<?php if ( $lockedUserRecord ) : ?>
								<i class="fa fa-lock fa-2x text-muted" aria-hidden="true"></i>
							<?php else: ?>
								<svg width="19" height="19" class="status-icon">
									<?php if( $is_section_confirmed['emergency_section'] ) : ?>
										<use href="#icon-success"></use>
									<?php else : ?>
										<use href="#icon-error"></use>
									<?php endif; ?>
								</svg>
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
											<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-warning.png' ); ?>">
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
					<?php if ( 5257 != $bike_id ) { ?>
						<?php $title_string = 'Confirm your gear information'; ?>
					<?php if( $lockedUserRecord == 1 ) { ?>
						<?php $title_string = 'Review your gear information'; ?>
						<?php $gray_out = 'disabled style="color: #666666;"'; ?>
					<?php } ?>
				<div class="accordion-item" >
					<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-gearInfo">
						<button class="accordion-button px-0 collapsed gear_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-gearInfo" aria-expanded="false" aria-controls="flush-collapse-gearInfo">
							<?php if ( $lockedUserRecord ) : ?>
								<i class="fa fa-lock fa-2x text-muted" aria-hidden="true"></i>
							<?php else: ?>
								<svg width="19" height="19" class="status-icon">
									<?php if( $is_section_confirmed['gear_section'] ) : ?>
										<use href="#icon-success"></use>
									<?php else : ?>
										<use href="#icon-error"></use>
									<?php endif; ?>
								</svg>
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
											<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-warning.png' ); ?>">
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
									<?php if ( ! $is_hiking_checkout ) : ?>
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
									<?php endif; ?>
								</div>
								<?php if ( ! $is_hiking_checkout ) : ?>
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
										<div class="col-md px-0 <?php echo $jersey_hideme; ?>">
											<div class="form-floating">
												<select <?php echo $gray_out; ?> name="tt-jerrsey-style" id="tt-jerrsey-style" class="form-select gear_validation_inputs tt_jersey_style_change" autocomplete="address-level1" data-input-classes="" data-label="Jersey Style" tabindex="-1" aria-hidden="true" data-guest-index="00" data-is-required="<?php echo( 'd-none' === $jersey_hideme ? 'false' : 'true' ); ?>">
													<option value="">Select Jersey Style</option>
													<?php if ( 'd-none' === $jersey_hideme ) : ?>
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
								<?php endif; ?>
								<?php if ( ! $is_hiking_checkout ) : ?>
									<div class="row mx-0 guest-checkout__primary-form-row gear-info-last-row <?php echo $jersey_hideme; ?>">
										<div class="col-md px-0">
											<div class="form-floating">
												<select <?php echo $gray_out; ?> name="tt-jerrsey-size" id="tt-jerrsey-size" class="form-select gear_validation_inputs" autocomplete="address-level1" data-input-classes="" data-label="Jersey Size" tabindex="-1" aria-hidden="true" data-is-required="<?php echo( 'd-none' === $jersey_hideme ? 'false' : 'true' ); ?>">
													<?php if ( 'd-none' === $jersey_hideme ) : ?>
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
								<?php endif; ?>
								<?php if ( $is_hiking_checkout ) : ?>
									<?php
									$tshirt_size = $user_order_info[0]['tshirt_size'];
									?>
									<div class="row mx-0 guest-checkout__primary-form-row gear-info-last-row">
										<div class="col-md px-0">
											<div class="form-floating">
												<select <?php echo $gray_out; ?> name="tt-jerrsey-size" id="tt-jerrsey-size" class="form-select gear_validation_inputs" autocomplete="address-level1" data-input-classes="" data-label="T-Shirt Size" tabindex="-1" aria-hidden="true" data-is-required="<?php echo( 'd-none' === $jersey_hideme ? 'false' : 'true' ); ?>">
													<?php echo tt_items_select_options( 'syncJerseySizes', tt_validate( $tshirt_size ) ); ?>
												</select>
												<label for="emergency_contact_address_2">T-Shirt Size</label>
												<div class="invalid-feedback">
													<img class="invalid-icon" />
													This field is required.
												</div>
											</div>
										</div>
									</div>
								<?php endif; ?>
								<?php if ( $lockedUserRecord != 1 ) { ?>
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
					<?php $gray_out = ''; ?>
					<?php $passport_title_string = "Add your passport information"; ?>
						<?php if( $lockedUserRecord == 1 ) { ?>
							<?php $passport_title_string = 'Review your passport information'; ?>
							<?php $gray_out = 'disabled style="color: #666666;"'; ?>
						<?php } ?>
					<div class="accordion-item">
						<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-passportInfo">
							<button class="accordion-button px-0 collapsed passport_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-passportInfo" aria-expanded="false" aria-controls="flush-collapse-passportInfo">
								<?php if ( $lockedUserRecord ) : ?>
									<i class="fa fa-lock fa-2x text-muted" aria-hidden="true"></i>
								<?php else: ?>
									<svg width="19" height="19" class="status-icon">
										<?php if( $is_section_confirmed['passport_section'] ) : ?>
											<use href="#icon-success"></use>
										<?php else : ?>
											<use href="#icon-error"></use>
										<?php endif; ?>
									</svg>
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
												<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-warning.png' ); ?>">
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
												<input <?php echo $gray_out; ?> type="tel" class="form-control passport_validation_inputs" name="passport_place_of_issue" id="passport_place_of_issue" placeholder="Passport country of issue" value="<?php echo $passport_place_of_issue; ?>" required>
												<label for="passport_place_of_issue">Passport country of issue</label>
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
					<?php if ( 5270 != $bike_id && 5257 != $bike_id && ! $is_hiking_checkout ) { ?>
						<?php $gray_out = ''; ?>
						<?php $bike_review_string = 'Confirm your bike selection'; ?>
						<?php if( $lockedUserRecord || $lockedUserBike ) { ?>
							<?php $bike_pointer_none = 'style="pointer-events: none;"' ?>
							<?php $bike_review_string = 'Review your bike selection'; ?>
							<?php $gray_out = 'disabled style="color: #666666;"'; ?>
						<?php } ?>
					<div class="accordion-item" >
						<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-bikeInfo">
							<button class="accordion-button px-0 collapsed bike_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-bikeInfo" aria-expanded="false" aria-controls="flush-collapse-bikeInfo">
								<?php if ( $lockedUserRecord || $lockedUserBike ) : ?>
									<i class="fa fa-lock fa-2x text-muted" aria-hidden="true"></i>
								<?php else: ?>
									<svg width="19" height="19" class="status-icon">
										<?php if( $is_section_confirmed['bike_section'] ) : ?>
											<use href="#icon-success"></use>
										<?php else : ?>
											<use href="#icon-error"></use>
										<?php endif; ?>
									</svg>
								<?php endif; ?>
								<?php echo $bike_review_string; ?>
							</button>
						</p>
						<form name="tt-checklist-form-bike-section" method="post" novalidate>
							<div  disabled <?php echo $gray_out; ?> id="flush-collapse-bikeInfo" class="accordion-collapse collapse" aria-labelledby="flush-heading-bikeInfo">
								<div  <?php echo $bike_pointer_none; ?> class="accordion-body px-0 checkout-bikes-section">
									<?php if( $lockedUserRecord || $lockedUserBike ) { ?>
										<div class="checkout-bikes__notice d-flex flex-column flex-lg-row flex-nowrap">
											<div class="checkout-bikes__notice-icon">
												<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-warning.png' ); ?>">
											</div>
											<div class="checkout-bikes__notice-text">
												<p class="fw-normal fs-sm lh-sm">Looks like your trip is starting soon! If you need to make any changes to your information below, give us a call!</p>
											</div>
										</div>
									<?php } ?>
									<div class="checkout-bikes__bike-grid d-flex flex-column flex-lg-row flex-nowrap">
										<?php
										$primary_bikeId = $bike_id;
										$primary_available_bike_html = '';
										$bikes_model_id_in = [];
										$available_bikes = tt_get_local_bike_detail( $trip_information['ns_trip_Id'], $trip_sku );
										$gear_preferences_bike_type = '';

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
												$bikeOpt_object = tt_get_bikes_by_trip_info_pbc( $trip_information['ns_trip_Id'], $trip_sku, $primary_bikeTypeId, $bike_size, '', '' );
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
									<?php if ( $lockedUserRecord !=1 && $lockedUserBike != 1 ) { ?>
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
					<?php $gray_out = ''; ?>
						<?php if( $lockedUserRecord ) { ?>
							<?php $fit_review_string = 'Review your bike fit information'; ?>
							<?php $gray_out = 'disabled style="color: #666666;"'; ?>
						<?php } ?>
					<div class="accordion-item">
						<p class="accordion-header fw-medium fs-md lh-md" id="flush-heading-gearInfo-optional">
							<button class="accordion-button px-0 collapsed gear_checklist-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-gearInfo-optional" aria-expanded="false" aria-controls="flush-collapse-gearInfo-optional">
								<?php echo $fit_review_string; ?> <span class="fw-normal fs-md lh-md text-muted">(Optional)</span>
							</button>
							<span class="fw-normal fs-sm lh-sm">Comfort matters! Let our team have your bike adjusted ahead of your arrival. </span>
						</p>
						<form name="tt-checklist-form-gear-optional-section" method="post" novalidate>
							<div id="flush-collapse-gearInfo-optional" class="accordion-collapse collapse" aria-labelledby="flush-heading-gearInfo-optional">
								<div class="accordion-body px-0">
									<?php if( $lockedUserRecord ) { ?>
										<div class="checkout-bikes__notice d-flex flex-column flex-lg-row flex-nowrap">
											<div class="checkout-bikes__notice-icon">
												<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-warning.png' ); ?>">
											</div>
											<div class="checkout-bikes__notice-text">
												<p class="fw-normal fs-sm lh-sm">Looks like your trip is starting soon! If you need to make any changes to your information below, give us a call!</p>
											</div>
										</div>
									<?php } ?>
									<div class="row mx-0 guest-checkout__primary-form-row">
										<div class="col-md px-0">
											<div class="form-floating">
												<input <?php echo $gray_out; ?> name="saddle_height" id="saddle_height" placeholder="Saddle Height (cm)" class="form-control gear_optional_validation_inputs" value="<?php echo $saddle_height ?>" pattern="^(?!0(\.0+)?$)(\d+(\.\d+)?|\.\d+)$" required>
												<label for="saddle_height">Saddle Height (cm)</label>
												<div class="invalid-feedback">
													<img class="invalid-icon" />
													This field is required.
												</div>
											</div>
										</div>
										<div class="col-md px-0">
											<div class="form-floating">
												<input <?php echo $gray_out; ?> type="text" name="bar_reach" id="bar_reach" placeholder="Bar reach (cm)" class="form-control gear_optional_validation_inputs" value="<?php echo $saddle_bar_reach_from_saddle ?>" pattern="^(?!0(\.0+)?$)(\d+(\.\d+)?|\.\d+)$" required>
												<label for="bar_reach">Bar reach (cm)</label>
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
												<input <?php echo $gray_out; ?> type="text" name="bar_height" id="bar_height" placeholder="Bar Height (cm)" class="form-control gear_optional_validation_inputs" value="<?php echo $saddle_bar_height_from_wheel_center; ?>" pattern="^(?!0(\.0+)?$)(\d+(\.\d+)?|\.\d+)$" required>
												<label for="bar_height">Bar Height (cm)</label>
												<div class="invalid-feedback">
													<img class="invalid-icon" />
													This field is required.
												</div>
											</div>
										</div>
										<div class="col-md px-0">
											<div class="form-floating">
											</div>
										</div>
									</div>
									<?php if ( $lockedUserRecord != 1 ) { ?>
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
	<div class="row mx-0 p-0 trip-waiver-info">
		<div class="col-12 waiver-col">
			<div class="card dashboard__card rounded-1" id="waiver-section">
				<p class="fw-medium fs-xl lh-xl">Trip Waiver Status</p>
				<?php if ( $waiver_signed == 1 ) {  ?>
					<p class="fw-medium fs-lg lh-lg status-signed">Signed</p>
					<p class="fw-normal fs-sm lh-sm">You're all set here!</p>
				<?php } else { ?>
					<div class="waiver-not-signed-ctr">
						<p class="fw-medium fs-lg lh-lg status-not-signed">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/error2.png' ); ?>"> Not Signed
						</p>
						<p class="fw-normal fs-sm lh-sm">Please review & sign the waiver below before the start of your trip date.</p>
						<a class="btn btn-primary fs-md lh-md mobile-hideme" href="javascript:" target="_blank" data-bs-toggle="modal" data-bs-target="#waiver_modal">Sign Waiver</a>
						<a class="btn btn-primary fs-md lh-md desktop-hideme" href="javascript:" target="_blank" data-bs-toggle="modal" data-bs-target="#waiver_modal">View Waiver</a>
					</div>
				<?php } ?>
			</div>
		</div>
	</div> <!-- row ends -->
</div>
<!-- Begin: Travel Waiver modal form  -->
<!-- Modal -->
<div class="modal fade modal-search-filter" id="waiver_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-ns-booking-id="<?php echo esc_attr( $ns_booking_id ); ?>" data-order-id="<?php echo esc_attr( $order_id ); ?>">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="fw-semibold modal-body__title">Trip Waiver</h4>
				<span type="button" class="btn-close close-waiver-event" data-bs-dismiss="modal" aria-label="Close">
					<i type="button" class="bi bi-x"></i>
				</span>
			</div>
			<div class="modal-body" style="padding: 0;">
				<?php if( !empty( $waiver_link ) ) : ?>
					<iframe src="<?php echo esc_url( $waiver_link ); ?>" width="100%" height="350"></iframe>
				<?php else : ?>
					<p class="p-4"><?php echo esc_html('Please check again later!'); ?></p>
				<?php endif; ?>
			</div>
		</div><!-- / .modal-content -->
	</div><!-- / .modal-dialog -->
</div><!-- / .modal -->
<!-- End: Travel Waiver modal form -->

<!-- #tpDeclineWarningModal -->
<?php get_template_part('inc/trek-modal-checkout/templates/modal', 'tp-decline-warning' ); ?>

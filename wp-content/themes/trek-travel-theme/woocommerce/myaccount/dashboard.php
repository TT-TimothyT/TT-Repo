<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Quick Look Modal for Thank You Page
 *
 * Should be included at the top in the dashboard.php file,
 * so that it can trigger the woocommerce_thankyou action
 * and process the flow after successful checkout,
 * before showing the page.
 */
get_template_part('tpl-parts/common/modal', 'quick-look', array( 'id' => 'quickLookModalThankYou', 'additional_class' => 'modal-quick-look-thank-you' ) );

global $woocommerce;
$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);
$userInfo = wp_get_current_user();
$wp_user_email = $userInfo->user_email;
//static fields need to replace with dynamic fields if data is available
$phone = "";
$dob = "";
$ns_user_id = get_user_meta(get_current_user_id(), 'ns_customer_internal_id', true);
$is_log = isset($_REQUEST['log']) && $_REQUEST['log'] == 1 ? true : false;

// Check if user sync is in progress
$sync_in_progress = get_user_meta(get_current_user_id(), 'tt_user_sync_in_progress', true) === 'yes';

// Check when the sync started
$sync_started_at = intval(get_user_meta(get_current_user_id(), 'tt_user_sync_started_at', true));

// If sync started more than 5 minutes ago, consider it timed out
if ($sync_in_progress && $sync_started_at > 0 && (time() - $sync_started_at) > 300) {
    $sync_in_progress = false;
    // Auto-clear the flag after timeout
    delete_user_meta(get_current_user_id(), 'tt_user_sync_in_progress');
    delete_user_meta(get_current_user_id(), 'tt_user_sync_started_at');
}

// Get trips only if sync is not in progress
$trips = ($sync_in_progress) ? array('data' => array(), 'count' => 0) : trek_get_guest_trips(get_current_user_id(), 1, '', $is_log);
$trips_counter = 0;
if (!$sync_in_progress) {
    foreach($trips['data'] as $trip ){
        $order = wc_get_order( $trip['order_id'] );
        if ( $order && ! in_array( $order->get_status(), ['cancelled', 'trash'] ) ) {
            // Get The booking status.
            $booking_status = tt_get_booking_status( $trip['order_id'] );
            if( $booking_status && ! in_array( $booking_status, TT_HIDE_ORDER_BOOKING_STATUSES ) ) {
                $trips_counter++;
            }
        }
    }
}

$shipping_address = "";
$billing_address = "";
$fullname = $userInfo->first_name;
if( isset($userInfo->last_name) && $userInfo->last_name ){
	$fullname .= ' '.$userInfo->last_name;	
}
$shipping_address_1 = get_user_meta($userInfo->ID, 'shipping_address_1', true);
$shipping_address_2 = get_user_meta($userInfo->ID, 'shipping_address_2', true);
$shipping_postcode = get_user_meta($userInfo->ID, 'shipping_postcode', true);
$shipping_country = get_user_meta($userInfo->ID, 'shipping_country', true);
$shipping_state = get_user_meta($userInfo->ID, 'shipping_state', true);
$shipping_city = get_user_meta($userInfo->ID, 'shipping_city', true);
$billing_address_1 = get_user_meta($userInfo->ID, 'billing_address_1', true);
$billing_address_2 = get_user_meta($userInfo->ID, 'billing_address_2', true);
$billing_postcode = get_user_meta($userInfo->ID, 'billing_postcode', true);
$billing_country = get_user_meta($userInfo->ID, 'billing_country', true);
$billing_state = get_user_meta($userInfo->ID, 'billing_state', true);
$billing_city = get_user_meta($userInfo->ID, 'billing_city', true);
$phone = get_user_meta($userInfo->ID, 'custentity_phone_number', true);
$dob = get_user_meta($userInfo->ID, 'custentity_birthdate', true);

// My Account - Resource Center Options
$resource_center_general_information = get_field( 'resource_center_general_information', 'option' );
$resource_center_useful_resources    = get_field( 'resource_center_useful_resources', 'option' );
$resource_center_video_resources     = get_field( 'resource_center_video_resources', 'option' );

// My Account - Travel Advisor Options
$travel_advisor_general_information = get_field( 'travel_advisor_general_information', 'option' );
$travel_advisor_useful_resources    = get_field( 'travel_advisor_useful_resources', 'option' );

$shipping_states       = WC()->countries->get_states( $shipping_country );
$shipping_state_name   = isset( $shipping_states[$shipping_state] ) ? $shipping_states[$shipping_state] : $shipping_state;
$shipping_country_name = WC()->countries->countries[$shipping_country];

$billing_states       = WC()->countries->get_states( $billing_country );
$billing_state_name   = isset( $billing_states[$billing_state] ) ? $billing_states[$billing_state] : $billing_state;
$billing_country_name = WC()->countries->countries[$billing_country];


$success_icon = '<img src="' . TREK_DIR . '/assets/images/Tick.svg" class="me-2 icon-16" alt="">';
$error_icon = '<img src="' . TREK_DIR . '/assets/images/error.svg" class="me-2 icon-16" alt="">';
$lock_icon = '<img src="' . TREK_DIR . '/assets/images/lock.svg" class="me-2 icon-16" alt="">';
$exclamation_icon = '<img src="' . TREK_DIR . '/assets/images/exclamation.svg" class="me-2 icon-16" alt="">';
$shield_icon = '<img src="' . TREK_DIR . '/assets/images/shield-icon.svg" class="me-2 icon-16" alt="">';
$tpp_not_accepted = '<img src="' . TREK_DIR . '/assets/images/not-accepted-protection.svg" class="icon-16 me-2" alt="">';
$tpp_accepted     = '<img src="' . TREK_DIR . '/assets/images/accepted-protection.svg" class="icon-16 me-2" alt="">';

$pending_items = false; // Flag to track if there are any pending items

?>
<div class="container dashboard px-0" id="dashboard">
	<div class="row mx-0">
		<div class="col-lg-12 d-flex dashboard__log">
			<p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
			<a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
		</div>
	</div>
	<div class="row mx-0">
		<div class="col-lg-12">
			<h3 class="dashboard__title fw-semibold">Dashboard</h3>
		</div>
	</div>
	<div class="row mx-0">
		<div class="col-lg-8 accordion">

		<div class="card mb-5 dashboard__card rounded-1 my-profile-mobile accordion-item" data-tour-guide="account-details-mobile">
				<div class="card-body pb-0">
					<div class="mobile-toggle-heading" data-bs-toggle="collapse" data-bs-target="#profileMobileCollapse" aria-expanded="false" aria-controls="profileMobileCollapse">
						<h5 class="card-title fw-bold mb-5 pt-2 pb-1">Profile</h5>
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="8" viewBox="0 0 14 8" fill="none">
							<path d="M7 7.54248C6.73633 7.54248 6.50195 7.45459 6.32617 7.27881L0.701172 1.65381C0.525391 1.47803 0.4375 1.24365 0.4375 0.97998C0.4375 0.745605 0.525391 0.51123 0.701172 0.306152C0.876953 0.130371 1.11133 0.0424805 1.375 0.0424805C1.60938 0.0424805 1.84375 0.130371 2.04883 0.306152L7 5.28662L11.9512 0.306152C12.127 0.130371 12.3613 0.0424805 12.625 0.0424805C12.8594 0.0424805 13.0938 0.130371 13.2988 0.306152C13.4746 0.51123 13.5625 0.745605 13.5625 0.97998C13.5625 1.24365 13.4746 1.47803 13.2988 1.65381L7.67383 7.27881C7.46875 7.45459 7.23438 7.54248 7 7.54248Z" fill="#333333"/>
						</svg>
					</div>
					<div class="collapse" id="profileMobileCollapse">
						<div class="d-flex justify-content-between align-items-baseline mb-2">
							<h6 class="card-subtitle fs-sm lh-sm fw-medium">Personal Information</h6>
							<a class="fs-sm lh-sm fw-medium edit-link" href="<?php echo site_url('my-account/edit-account'); ?>">Edit</a>
						</div>
						<div class="card-text dashboard__info">
							<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $fullname; ?></p>
							<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $userInfo->user_email; ?></p>
							<?php if(!empty($phone)): ?>
								<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $phone;?></p>
							<?php endif; ?>
							<?php if(!empty($dob)): ?>
								<p class="mb-0 fs-sm lh-sm fw-normal">DOB: <?php echo $dob;?></p>
							<?php endif; ?>
						</div>
						
						<div class="card-body pb-0 profile-addresses">
							<h5 class="address-title fw-bold">Addresses</h5>
							<div class="d-flex justify-content-between align-items-baseline mb-2">
								<h6 class="address-subtitle fs-sm lh-sm fw-medium">Shipping Address</h6>
								<a class="fs-sm lh-sm fw-medium edit-link" href="<?php echo site_url('my-account/edit-address/shipping'); ?>">Edit</a>
							</div>
							<div class="card-text">
								<?php if(empty($shipping_address_1)): ?>
									<p class="mb-0 fs-sm lh-sm fw-normal">No shipping address added</p>
								<?php else: ?>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $shipping_address_1; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $shipping_address_2; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $shipping_city; ?>, <?php echo $shipping_state_name; ?>, <?php echo $shipping_postcode; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $shipping_country_name; ?></p>
								<?php endif; ?>
							</div>
							<!-- <hr> -->
							<div class="d-flex justify-content-between align-items-baseline mb-2 billing-address">
								<h6 class="address-subtitle fs-sm lh-sm fw-medium">Billing Address</h6>
								<a class="fs-sm lh-sm fw-medium edit-link" href="<?php echo site_url('my-account/edit-address/billing'); ?>">Edit</a>
							</div>
							<div class="card-text">
								<?php if(empty($billing_address_1)): ?>
									<p class="fs-sm lh-sm fw-normal">No billing address added</p>
								<?php else: ?>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_address_1; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_address_2; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_city; ?>, <?php echo $billing_state_name; ?>, <?php echo $billing_postcode; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_country_name; ?></p>
								<?php endif; ?>
							</div>
						</div>
						<div>
							 <div class="d-flex justify-content-between align-items-baseline mb-2 pt-2">
								 <h6 class="card-subtitle fs-sm lh-sm fw-medium">Password</h6>
								 <a class="fs-sm lh-sm fw-medium edit-link" href="<?php echo site_url('my-account/change-password'); ?>">Edit</a>
							 </div>
							 <p class="fs-sm lh-sm fw-normal pb-2">********</p>
						 </div>
						<div class="d-flex justify-content-between align-items-baseline mb-2 pt-2">
							<h6 class="card-subtitle fs-sm lh-sm fw-medium">Communication Preferences</h6>
							<a class="fs-sm lh-sm fw-medium edit-link" href="<?php echo site_url('my-account/communication-preferences'); ?>">Edit</a>
						</div>
					</div>
				</div>
			</div>

		<div class="card mb-5 dashboard__card rounded-1 my-trips-card accordion-item">
				<div class="card-body pb-0">
					<div class="d-flex justify-content-between align-items-baseline mb-3 mobile-toggle-heading" data-bs-toggle="collapse" data-bs-target="#myTripsCollapse" aria-expanded="true" aria-controls="myTripsCollapse">
						<h5 class="card-title fw-bold mb-2">My Trips <span>(<?php echo $trips_counter; ?>)</span></h5>
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="8" viewBox="0 0 14 8" fill="none">
							<path d="M7 7.54248C6.73633 7.54248 6.50195 7.45459 6.32617 7.27881L0.701172 1.65381C0.525391 1.47803 0.4375 1.24365 0.4375 0.97998C0.4375 0.745605 0.525391 0.51123 0.701172 0.306152C0.876953 0.130371 1.11133 0.0424805 1.375 0.0424805C1.60938 0.0424805 1.84375 0.130371 2.04883 0.306152L7 5.28662L11.9512 0.306152C12.127 0.130371 12.3613 0.0424805 12.625 0.0424805C12.8594 0.0424805 13.0938 0.130371 13.2988 0.306152C13.4746 0.51123 13.5625 0.745605 13.5625 0.97998C13.5625 1.24365 13.4746 1.47803 13.2988 1.65381L7.67383 7.27881C7.46875 7.45459 7.23438 7.54248 7 7.54248Z" fill="#333333"/>
						</svg>
					</div>
					<?php if ($sync_in_progress || empty($trips) || (isset($trips['count']) && $trips['count'] <= 0)) { ?>
						<div class="no-trips-msg-container accordion-collapse collapse show" id="myTripsCollapse" data-tour-guide="nearest-upcoming-trip">
							<?php if ( $sync_in_progress ) { ?>
								<p class="fs-sm lh-sm fw-normal">Your trips are on the way! Please check back in a few minutes to view your trip details.</p>
							<?php } else { ?>
								<p class="fs-sm lh-sm fw-normal">Looks like you have no trips planned yet!</p>
							<?php } ?>
							<a href="<?php echo site_url('tours/all/') ?>" class="btn btn-lg btn-primary dashboard__button rounded-1 mb-4">Book a trip</a>
							<div class="view-all-trips-mobile"><a class="fs-sm lh-sm fw-medium btn-view-all-trip" href="<?php echo site_url("my-account/my-trips"); ?>">View All Trips</a></div>
						</div>
					<?php } else {
						if ($trips && isset($trips['data'])) {
							$trips_html = '<div class="trips-box accordion-collapse collapse show" id="myTripsCollapse">'; // Open .trips-box container


							$show_two_trips_counter = 0;
							$trip_index             = 0;
							foreach ($trips['data'] as $trip) {
								$product_id = $trip['product_id'];
								$order_id   = $trip['order_id'];
								$order      = wc_get_order($order_id);

								if ( ! $order ) continue;

								// Get Order Status
								$wc_order_status = $order->get_status();
								if ( 'cancelled' === $wc_order_status || 'trash' === $wc_order_status ) {
									if ( 0 === $trips_counter ) {
										$trips_html .= '<p class="fs-sm lh-sm fw-normal" data-tour-guide="nearest-upcoming-trip">Looks like you have no trips planned yet!</p>';
									}
									continue;
								}

								// Get The booking status
								$booking_status = tt_get_booking_status($order_id);
								// If the booking status is in the hidden list, skip this trip
								if ( $booking_status && in_array( $booking_status, TT_HIDE_ORDER_BOOKING_STATUSES ) ) {
									// If only one trip is available or all available trips are in not show booking status, show a message
									if ( 0 === $trips_counter ) {
										$trips_html .= '<p class="fs-sm lh-sm fw-normal" data-tour-guide="nearest-upcoming-trip">Your trips are on the way! Please check back in a few minutes to view your trip details.</p>';
									}
									continue;
								}

								$show_two_trips_counter++;
								if ($show_two_trips_counter > 3) break;

								$order_details    = trek_get_user_order_info($userInfo->ID, $order_id);
								$guest_is_primary = isset($order_details[0]['guest_is_primary']) ? $order_details[0]['guest_is_primary'] : 0;
								if ( $guest_is_primary ) {
									$order_details = trek_get_user_order_info(null, $order_id);
								}
								$lockedUserBike   = tt_is_registration_locked( get_current_user_id(), $order_details[0]['guestRegistrationId'], 'bike' );
								$lockedUserRecord = tt_is_registration_locked( get_current_user_id(), $order_details[0]['guestRegistrationId'], 'record' );
								$bike_id          = $order_details[0]['bike_id']; // Assuming this is where you get the bike_id
								$waiver_signed    = isset($order_details[0]['waiver_signed']) ? $order_details[0]['waiver_signed'] : false;
								$product          = wc_get_product($product_id);


								if ( $product ) {
									$trip_link              = esc_url(add_query_arg('order_id', $order_id, get_permalink(TREK_MY_ACCOUNT_PID) . 'my-trip'));
									$trip_sku               = $product->get_sku();
									$trip_sdate             = $product->get_attribute('pa_start-date');
									$trip_edate             = $product->get_attribute('pa_end-date');
									$trip_id                = get_post_meta( $product_id, TT_WC_META_PREFIX . 'tripId', true );
									$tripRegion             = tt_get_local_trips_detail( 'tripRegion', $trip_id, $trip_sku, true );
									$pa_city                = $product->get_attribute('pa_city');
									$is_checklist_completed = tt_is_checklist_completed($userInfo->ID, $order_id, $order_details[0]['rider_level'], $product_id, $order_details[0]['bike_id'], $guest_is_primary, $waiver_signed);

									$checkout_data = get_post_meta( $order_id, 'trek_user_checkout_data', true );
									$public_view_order_url = '';

									if( $guest_is_primary ) {
										$public_view_order_url = esc_url($order->get_view_order_url());
									}

									// Retrieve parent trip details by child SKU
									$parent_trip = tt_get_parent_trip_group($trip_sku); // Returns details including the image

									// Load parent product if available
									$parent_product = $parent_trip['id'] ? wc_get_product($parent_trip['id']) : null;

									// Set parent product name and link, with fallbacks if parent is unavailable
									$parent_name = $parent_product ? $parent_product->get_name() : $product->get_name();
									$parent_trip_link = $parent_trip['link'];;
									$parent_image = $parent_trip['image'];

									// Date formatting
									$sdate_obj = explode('/', $trip_sdate);
									$edate_obj = explode('/', $trip_edate);
									$sdate_info = ['d' => $sdate_obj[0], 'm' => $sdate_obj[1], 'y' => substr(date('Y'), 0, 2) . $sdate_obj[2]];
									$edate_info = ['d' => $edate_obj[0], 'm' => $edate_obj[1], 'y' => substr(date('Y'), 0, 2) . $edate_obj[2]];
									$start_date_text = date('F jS, Y', strtotime(implode('-', $sdate_info)));
									$end_date_text = date('F jS, Y', strtotime(implode('-', $edate_info)));
									$date_range = $start_date_text . ' - ' . date('jS, Y', strtotime(implode('-', $edate_info)));

									$sdate_obj = explode('/', $trip_sdate); // e.g., 16/11/2025
									$sdate_str = $sdate_obj[2] . '-' . $sdate_obj[1] . '-' . $sdate_obj[0]; // YYYY-MM-DD
									$start = new DateTime($sdate_str);

									$today = new DateTime();
									$diff = $today->diff($start);
									$days_left = ($start > $today) ? $diff->days . ' days left' : 'Started';

									$fees_product_id = tt_create_line_item_product( 'TTWP23FEES' );

									$is_hiking_checkout = tt_is_product_line( 'Hiking', $trip_sku, $trip_id );

									$travel_protected = isset( $order_details[0]['wantsInsurance'] ) && $order_details[0]['wantsInsurance'] == 1 ? true : false;

									// Count the protected guests.
									$travel_protected_guests_count = 0;
									// Count the declined insurance guests.
									$declined_insurance_guests_count = 0;
									if ( count( $order_details ) > 0 ) {
										foreach ( $order_details as $guest ) {
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

									$can_show_travel_protection = $travel_protected_guests_count < count( $order_details ) && ( $diff->days > 14 ) ? true : false;

									if( $trip_index == 0 ) {
										$trips_html .= '<div class="dashboard__trip" data-tour-guide="nearest-upcoming-trip">';

											$trips_html .= '<a href="' . $trip_link . '" class="my-upcoming-trips position-relative d-block">';
												$trips_html .= '<img src="' . esc_url($parent_image) . '" alt="">';
												$trips_html .= '<h6 class="dashboard__trip-badge fs-sm lh-sm fw-medium mb-2 bg-black text-white px-2 py-1 rounded-end">Upcoming</h6>';
											$trips_html .= '</a>';

											$trips_html .= '<div class="w-100">';

												$trips_html .= '<a href="' . $trip_link . '" class="text-decoration-none">';
													$trips_html .= '<h6 class="fs-5 lh-lg fw-bold mb-1 text-dark trip-title">' . esc_html($parent_name) . '</h6>';
												$trips_html .= '</a>';

												$trips_html .= '<p class="dashboard__date-range fs-sm pt-1 lh-sm fw-normal mb-5 text-dark">' . esc_html($date_range) . ' <span class="text-muted">(' . esc_html($days_left) . ')</span></p>';
												$trips_html .= '<div class="tour-guide-ctr" data-tour-guide="view-checklist">';
													if (!empty($order_details) && !$is_checklist_completed && !$lockedUserRecord && !$lockedUserBike) {
														$trips_html .= '<p class="dashboard__error"><img src="' . TREK_DIR . '/assets/images/error.svg"> You have pending items</p>';

													} elseif ( !$lockedUserRecord && !$lockedUserBike ) {
														$trips_html .= '<img class="dashboard__good-to-go-badge__img" src="' . esc_url(get_template_directory_uri() . '/assets/images/Tick.svg') . '" alt="success icon">';
														$trips_html .= '<span class="mb-0 fs-sm lh-sm dashboard__good-to-go-badge__text rounded-1 p-1">You are all set!</span>';
													}

													$trips_html .= '<div class="mt-4">';

														if ($lockedUserRecord || $lockedUserBike) {
															$trips_html .= '<p class="dashboard__error locked-text"><i class="fa fa-lock fa-lg" aria-hidden="true"></i> Your Checklist or a checklist item is locked</p>';
														}

														$trips_html .= '<div class="row text-xs fw-normal trip-info-list" data-page="dashboard" data-order_id="' . esc_attr($order_id) . '">';
		
														// Column 1
														$trips_html .= '<div class="">';
														$medical_info_status = tt_is_individual_checklist_completed('medical_section', $userInfo->ID, $order_id, $order_details[0]['rider_level'], $product_id, $bike_id, $guest_is_primary);
														$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="' . site_url('my-trip/?order_id=' . $order_id . '#flush-heading-medicalInfo') . '" class="d-flex align-items-center text-decoration-none">' . $medical_info_status . ' Medical information</a></p>';

														$is_passport_required = get_post_meta( $product_id, TT_WC_META_PREFIX . 'isPassportRequired', true );
														if ( '1' == $is_passport_required ) {
															$passport_info_status = $lockedUserRecord ? $lock_icon : tt_is_individual_checklist_completed('passport_section', $userInfo->ID, $order_id, $order_details[0]['rider_level'], $product_id, $bike_id, $guest_is_primary);
															$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="' . site_url('my-trip/?order_id=' . $order_id . '#flush-heading-passportInfo') . '" class="d-flex align-items-center text-decoration-none">' . $passport_info_status . ' Passport details</a></p>';
														}

														if ($bike_id != 5270 && 5257 != $bike_id) {
															$gear_info_status = $lockedUserRecord ? $lock_icon : tt_is_individual_checklist_completed('gear_section', $userInfo->ID, $order_id, $order_details[0]['rider_level'], $product_id, $bike_id, $guest_is_primary);
															$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="' . site_url('my-trip/?order_id=' . $order_id . '#flush-heading-gearInfo') . '" class="d-flex align-items-center text-decoration-none">' . $gear_info_status . ' Gear information</a></p>';
														}

														$emergency_info_status = tt_is_individual_checklist_completed('emergency_section', $userInfo->ID, $order_id, $order_details[0]['rider_level'], $product_id, $bike_id, $guest_is_primary);
														$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="' . site_url('my-trip/?order_id=' . $order_id . '#flush-heading-emergencyInfo') . '" class="d-flex align-items-center text-decoration-none">' . $emergency_info_status . ' Emergency contact</a></p>';
														$trips_html .= '</div>';
		
														// Column 2
														$trips_html .= '<div class="">';
														if ( $bike_id != 5270 && 5257 != $bike_id &&  ! $is_hiking_checkout ) {
															$bike_info_status = $lockedUserBike ? $lock_icon : tt_is_individual_checklist_completed('bike_section', $userInfo->ID, $order_id, $order_details[0]['rider_level'], $product_id, $order_details[0]['bike_id'], $guest_is_primary);
															$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="' . site_url('my-trip/?order_id=' . $order_id . '#flush-heading-bikeInfo') . '" class="d-flex align-items-center text-decoration-none">' . $bike_info_status . ' Bike selection</a></p>';

															$gear_info_optional_status = tt_is_individual_checklist_completed('gear_optional_section', $userInfo->ID, $order_id, $order_details[0]['rider_level'], $product_id, $order_details[0]['bike_id'], $guest_is_primary);
															$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="' . site_url('my-trip/?order_id=' . $order_id . '#flush-heading-gearInfo-optional') . '" class="d-flex align-items-center text-decoration-none">' . $gear_info_optional_status . ' Bike fit information (optional)</a></p>';
														}
														if ($waiver_signed) {
															$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="' . site_url('my-trip/?order_id=' . $order_id . '#waiver-section') . '" class="d-flex align-items-center text-decoration-none">' . $success_icon . ' Trip Waiver</a></p>';
														} else {
															$trips_html .= '<p class="d-flex align-items-center lh-xs "><a href="' . site_url('my-trip/?order_id=' . $order_id . '#waiver-section') . '" class="d-flex align-items-center text-decoration-none">' . $error_icon . ' Trip Waiver</a></p>';
														}

														if ($travel_protected) {
															$trips_html .= '<p class="d-flex align-items-center lh-xs ">' . $tpp_accepted . ' <b> Travel Protection </b></p>';
														} else {
															$trips_html .= '<p class="d-flex align-items-center lh-xs ">' . $tpp_not_accepted . ' <b> Travel Protection </b></p>';
														}
														$trips_html .= '</div>';
		
														$trips_html .= '</div>'; // end .row
		
														// CTA Button
														$trips_html .= '<div class="mt-5 btn-checklist d-flex align-items-center justify-content-between flex-column flex-md-row">';
														$trips_html .= '<a href="' . esc_url(add_query_arg('order_id', $order_id, home_url() . '/my-trip/')) . '" class="btn btn-primary rounded-1 view-checklist-btn" data-order_id="' . esc_attr( $order_id ) . '" data-page="dashboard">View Checklist</a>';
														if ( $public_view_order_url ) {
															$trips_html .= '<a class="btn btn-link rounded-1 fw-medium text-decoration-underline p-0 order-details-btn" href="' . $public_view_order_url . '" data-order_id="' . esc_attr( $order_id ) . '" data-page="dashboard">Order Details</a>';
														}
														$trips_html .= '</div>';
		
													$trips_html .= '</div>'; // end checklist block
												$trips_html .= '</div>'; // end tour-guide-ctr
											$trips_html .= '</div>'; // end trip info

										$trips_html .= '</div>'; // end .dashboard__trip

										// Add check for cookie with name hide_trip_insurance_info_${orderId}=true so can hide the section
										if ( ! isset( $_COOKIE[ 'hide_trip_insurance_info_' . $order_id ] ) || $_COOKIE[ 'hide_trip_insurance_info_' . $order_id ] !== 'true' ) {
											$trips_html .= '<div class="trip-insurance-info">';

												$trips_html .= '<div class="fs-5 fw-bold text-dark mb-2 travel-protection-title hide-mobile"><img src="' . TREK_DIR . '/assets/images/accepted-protection.svg" class="icon-22">Travel Protection Benefits</div>';
												$trips_html .= '<p class="fs-sm fw-semibold text-dark travel-protection-subtitle hide-mobile">Because peace of mind is the best travel companion. Protect your trip from the unexpected</p>';
												$trips_html .= '<div class="fs-5 fw-bold text-dark mb-5 travel-protection-title show-mobile"><img src="' . TREK_DIR . '/assets/images/accepted-protection.svg" class="icon-22">Protect your vacation!</div>';
												$trips_html .= '<div class="hide-mobile">';
												$trips_html .= '<div class="d-flex align-items-center justify-content-between"><p class="fs-sm fw-semibold text-dark lh-sm mb-0">Here\'s what you get:</p><a href="' . site_url('/travel-protection') . '" class="btn btn-link p-0 view-full-details-link" target="_blank" data-page="dashboard">View Full Details</a></div>';
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


												$trips_html .= '<div class="d-flex justify-content-between align-items-center mb-5 travel-protection-footer">';

													// Left link
													$trips_html .= '<a href="#" class="text-decoration-underline text-dark fw-normal text-xs hide-mobile hide-insurance-btn" data-order_id="' . esc_attr( $order_id ) . '" data-page="dashboard"><strong>Hide</strong></a>';

													// Show if it is a primary guest and ( not protected or any of the guests already not protected ) and not declined.
													if ( $guest_is_primary && $can_show_travel_protection ) {

														// Add Travel Protection button (right)
														$add_tp_btn = '<a href="?add-to-cart=' . esc_attr( $fees_product_id ) . '" 
														data-product_id="' . esc_attr( $fees_product_id ) . '" 
														data-order_id="' . esc_attr( $order_id ) . '" 
														data-origin="tt_modal_checkout" 
														data-bs-toggle="modal"
														data-bs-target="#quickLookModalCheckout"
														data-page="dashboard"
														class="btn btn-sm btn-fixed-width fw-medium rounded-1 text-white trek-add-to-cart add-travel-protection-btn" 
														style="background-color: #28AAE1;"><svg class="show-mobile" xmlns="http://www.w3.org/2000/svg" width="21" height="23" viewBox="0 0 21 23" fill="none">
														<path d="M19.5664 3.83936C20.3398 4.22607 20.7695 4.82764 20.8125 5.68701C20.7266 9.38232 19.9961 12.4331 18.6211 14.8823C17.2461 17.3745 15.7422 19.1792 14.1523 20.3823C12.5195 21.6284 11.3164 22.23 10.5 22.23C9.68359 22.23 8.48047 21.6284 6.84766 20.4253C5.21484 19.2222 3.75391 17.3745 2.37891 14.9253C1.00391 12.4761 0.273438 9.38232 0.1875 5.68701C0.1875 4.82764 0.617188 4.22607 1.47656 3.83936L9.72656 0.401855C9.98438 0.315918 10.2422 0.22998 10.5 0.22998C10.7578 0.22998 11.0586 0.315918 11.3164 0.401855L19.5664 3.83936ZM14.625 8.82373C14.625 8.65186 14.5391 8.43701 14.4102 8.1792C14.2383 7.96436 13.9805 7.83545 13.5938 7.74951C13.25 7.74951 12.9922 7.87842 12.8203 8.09326L9.42578 12.0894L8.13672 10.8003C7.92188 10.6284 7.66406 10.4995 7.40625 10.4995C6.76172 10.5854 6.41797 10.9292 6.375 11.5308C6.375 11.8745 6.46094 12.0894 6.67578 12.2612L8.73828 14.3237C8.91016 14.5386 9.16797 14.6245 9.46875 14.6245C9.64062 14.7104 9.89844 14.5815 10.2422 14.3237L14.3672 9.51123C14.5391 9.33936 14.625 9.08154 14.625 8.82373Z" fill="white"/>
														</svg>Add Travel Protection</a>';
														$trips_html .= '<a href="tel:8664648735"
														class="btn btn-sm btn-fixed-width fw-medium rounded-1 text-white trek-add-to-cart add-travel-protection-btn" 
														style="background-color: #28AAE1;">
														<i class="bi bi-telephone"></i> Call Us
														</a>';

													}

												$trips_html .= '</div>';


											$trips_html .= '</div>';
										} // End of travel protection section

										$trips_html .= '<hr class="blue-divider"/>';

									} else {
										// Code for the rest of the trips
										$trips_html .= '<div class="dashboard__trip rest-trips">';
											$trips_html .= '<a ref="' . $trip_link . '" class="my-upcoming-trips trip-image--small position-relative">';
												$trips_html .= '<img src="' . esc_url($parent_image) . '" alt="">';
											$trips_html .= '</a>';

											$trips_html .= '<div class="w-100">'; // Keep using w-100 to respect Bootstrap column

												$trips_html .= '<a href="' . $trip_link . '" class="text-decoration-none">';
													$trips_html .= '<h6 class="fs-6 lh-sm fw-bold mb-1 text-dark trip-title-rest">' . esc_html($parent_name) . '</h6>';
												$trips_html .= '</a>';

												$trips_html .= '<p class="fs-sm pt-2 lh-sm fw-normal mb-2 text-dark range-date-rest">' . esc_html($date_range)  . ' <span class="text-muted">(' . esc_html($days_left) . ')</span></p>';

												// Checklist messages
												$trips_html .= '<div class="checklist-messages">';
													if (!empty($order_details) && !$is_checklist_completed && !$lockedUserRecord && !$lockedUserBike) {
														$trips_html .= '<p class="dashboard__error"><img src="' . TREK_DIR . '/assets/images/error.svg"> You have pending items</p>';
													} elseif ($lockedUserRecord || $lockedUserBike) {
														$trips_html .= '<p class="dashboard__error locked-text"><i class="fa fa-lock fa-lg" aria-hidden="true"></i> Your Checklist or a checklist item is locked</p>';
													} else {
														$trips_html .= '<img class="dashboard__good-to-go-badge__img" src="' . esc_url(get_template_directory_uri() . '/assets/images/Tick.svg') . '" alt="success icon">';
														$trips_html .= '<span class="mb-0 fs-sm lh-sm dashboard__good-to-go-badge__text rounded-1 p-1">Good to go! Your checklist is complete.</span>';
													}

													if ( $travel_protected ) {
														$trips_html .= '<p class="dashboard__protection">' . $tpp_accepted . 'Travel Protection</p>';
													} else {
														$trips_html .= '<p class="dashboard__protection">' . $tpp_not_accepted . 'Travel Protection</p>';
													}
												$trips_html .= '</div>';
											$trips_html .= '</div>'; // Close .w-100

											$trips_html .= '<div class="d-flex justify-content-between align-items-center mt-3 travel-protection-footer">';
												// View Details button (left)
												$trips_html .= '<a class="btn rounded-1 fw-medium text-white" href="' . $trip_link . '">View Details</a>';
												if ( $guest_is_primary && $can_show_travel_protection ) {
													// Add Travel Protection button (right)
													$add_tp_btn = '<a href="?add-to-cart=' . esc_attr( $fees_product_id ) . '" 
														data-product_id="' . esc_attr( $fees_product_id ) . '" 
														data-order_id="' . esc_attr( $order_id ) . '" 
														data-origin="tt_modal_checkout" 
														data-bs-toggle="modal"
														data-bs-target="#quickLookModalCheckout"
														data-page="dashboard"
														class="btn btn-sm btn-fixed-width fw-medium rounded-1 text-white trek-add-to-cart add-travel-protection-btn" 
														style="background-color: #28AAE1;">Add Travel Protection</a>';
													$trips_html .= '<a href="tel:8664648735"
														class="btn btn-sm btn-fixed-width fw-medium rounded-1 text-white trek-add-to-cart add-travel-protection-btn" 
														style="background-color: #28AAE1;">
														<i class="bi bi-telephone"></i> Call Us
														</a>';
												} 
											$trips_html .= '</div>'; // Close .travel-protection-footer
										$trips_html .= '</div>'; // Close .dashboard__trip
										}
									$trip_index ++;
								}
							}

							$trips_html .= '<div class="view-all-trips-mobile"><a class="fs-sm lh-sm fw-medium btn-view-all-trip" href="' . site_url("my-account/my-trips") . '">View All Trips</a></div>';

							$trips_html .= '</div>'; // Close .trips-box container
						}

						echo $trips_html;
					} ?>
				</div>
			</div>

			<!-- <div class="card mb-3 dashboard__card rounded-1">
				<div class="card-body pb-0">
					<div class="d-flex justify-content-between align-items-baseline mb-2">
						<h5 class="card-title fw-bold mb-2">Bike & Gear Preferences</h5>
						<a class="fs-sm lh-sm fw-medium" href="<?php 
						// echo site_url('my-account/bike-gear-preferences'); 
						?>">Edit</a>
					</div>
					<p class="fs-sm lh-sm fw-normal">Manage your jersey size, helmet size, and more</p>
				</div>
			</div> -->
		</div>
		<div class="col-lg-4 accordion">
			<div class="card mb-5 dashboard__card rounded-1 my-profile-desktop accordion-item" data-tour-guide="account-details">
				<div class="card-body pb-0">
					<div class="d-flex justify-content-between align-items-baseline mb-5 mobile-toggle-heading" data-bs-toggle="collapse" data-bs-target="#profileDesktopCollapse" aria-expanded="true" aria-controls="profileDesktopCollapse">
						<h5 class="card-title fw-bold mb-0 pt-2 pb-1">Profile</h5>
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="8" viewBox="0 0 14 8" fill="none">
							<path d="M7 7.54248C6.73633 7.54248 6.50195 7.45459 6.32617 7.27881L0.701172 1.65381C0.525391 1.47803 0.4375 1.24365 0.4375 0.97998C0.4375 0.745605 0.525391 0.51123 0.701172 0.306152C0.876953 0.130371 1.11133 0.0424805 1.375 0.0424805C1.60938 0.0424805 1.84375 0.130371 2.04883 0.306152L7 5.28662L11.9512 0.306152C12.127 0.130371 12.3613 0.0424805 12.625 0.0424805C12.8594 0.0424805 13.0938 0.130371 13.2988 0.306152C13.4746 0.51123 13.5625 0.745605 13.5625 0.97998C13.5625 1.24365 13.4746 1.47803 13.2988 1.65381L7.67383 7.27881C7.46875 7.45459 7.23438 7.54248 7 7.54248Z" fill="#333333"/>
						</svg>
					</div>
					<div class="accordion-collapse collapse show" id="profileDesktopCollapse">
						<div class="d-flex justify-content-between align-items-baseline mb-2">
							<h6 class="card-subtitle fs-sm lh-sm fw-medium">Personal Information</h6>
							<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/edit-account'); ?>">Edit</a>
						</div>
						<div class="card-text dashboard__info">
							<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $fullname; ?></p>
							<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $userInfo->user_email; ?></p>
							<?php if(!empty($phone)): ?>
								<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $phone;?></p>
							<?php endif; ?>
							<?php if(!empty($dob)): ?>
								<p class="mb-0 fs-sm lh-sm fw-normal">DOB: <?php echo $dob;?></p>
							<?php endif; ?>
						</div>
						<hr>
						<div class="d-flex justify-content-between align-items-baseline mb-2 pt-2">
							<h6 class="card-subtitle fs-sm lh-sm fw-medium">Password</h6>
							<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/change-password'); ?>">Edit</a>
						</div>
						<p class="fs-sm lh-sm fw-normal pb-2">********</p>
						<hr>
						<div class="d-flex justify-content-between align-items-baseline mb-2 pt-2">
							<h6 class="card-subtitle fs-sm lh-sm fw-medium">Communication Preferences</h6>
							<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/communication-preferences'); ?>">Edit</a>
						</div>
						<p class="fs-sm lh-sm fw-normal">Manage your email and mail subscriptions</p>
						<div class="pt-4 pb-0">
							<h5 class="card-title fw-bold">Addresses</h5>
							<div class="d-flex justify-content-between align-items-baseline mb-2">
								<h6 class="card-subtitle fs-sm lh-sm fw-medium">Shipping Address</h6>
								<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/edit-address/shipping'); ?>">Edit</a>
							</div>
							<div class="card-text">
								<?php if(empty($shipping_address_1)): ?>
									<p class="mb-0 fs-sm lh-sm fw-normal">No shipping address added</p>
								<?php else: ?>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $shipping_address_1; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $shipping_address_2; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $shipping_city; ?>, <?php echo $shipping_state_name; ?>, <?php echo $shipping_postcode; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $shipping_country_name; ?></p>
								<?php endif; ?>
							</div>
							<hr>
							<div class="d-flex justify-content-between align-items-baseline mb-2">
								<h6 class="card-subtitle fs-sm lh-sm fw-medium">Billing Address</h6>
								<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/edit-address/billing'); ?>">Edit</a>
							</div>
							<div class="card-text">
								<?php if(empty($billing_address_1)): ?>
									<p class="fs-sm lh-sm fw-normal">No billing address added</p>
								<?php else: ?>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_address_1; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_address_2; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_city; ?>, <?php echo $billing_state_name; ?>, <?php echo $billing_postcode; ?></p>
									<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_country_name; ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php
			// This is a custom capability added to the Travel Advisor custom role
			if ( current_user_can( 'travel_advisor' ) ) : ?>
				<div class="card mb-5 dashboard__card rounded-1 my-travel-advisor accordion-item">
					<div class="card-body pb-0">
						<?php if ( ! empty( $travel_advisor_general_information['section_title'] ) ) : ?>
							<div class="d-flex justify-content-between align-items-baseline mb-2 mobile-toggle-heading" data-bs-toggle="collapse" data-bs-target="#travelAdvisorCollapse" aria-expanded="false" aria-controls="travelAdvisorCollapse">
								<h5 class="card-title fw-bold mb-2"><?php echo esc_attr( $travel_advisor_general_information['section_title'] ); ?></h5>
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="8" viewBox="0 0 14 8" fill="none">
									<path d="M7 7.54248C6.73633 7.54248 6.50195 7.45459 6.32617 7.27881L0.701172 1.65381C0.525391 1.47803 0.4375 1.24365 0.4375 0.97998C0.4375 0.745605 0.525391 0.51123 0.701172 0.306152C0.876953 0.130371 1.11133 0.0424805 1.375 0.0424805C1.60938 0.0424805 1.84375 0.130371 2.04883 0.306152L7 5.28662L11.9512 0.306152C12.127 0.130371 12.3613 0.0424805 12.625 0.0424805C12.8594 0.0424805 13.0938 0.130371 13.2988 0.306152C13.4746 0.51123 13.5625 0.745605 13.5625 0.97998C13.5625 1.24365 13.4746 1.47803 13.2988 1.65381L7.67383 7.27881C7.46875 7.45459 7.23438 7.54248 7 7.54248Z" fill="#333333"/>
								</svg>
							</div>
						<?php endif; ?>
							<div class="accordion-collapse collapse" id="travelAdvisorCollapse">
						<?php if ( ! empty( $travel_advisor_general_information['section_content'] ) ) : ?>
							<div class="fs-sm lh-sm fw-normal"><?php echo $travel_advisor_general_information['section_content']; ?></div>
						<?php endif; ?>

						<hr>

						<?php if ( ! empty( $travel_advisor_useful_resources['section_title'] ) ) : ?>
							<h6 class="card-subtitle fs-sm lh-sm fw-medium"><?php echo $resource_center_useful_resources['section_title']; ?></h6>
						<?php endif; ?>

						<?php if ( ! empty( $travel_advisor_useful_resources['useful_resources'] ) ) : ?>
							<div class="quick-links">
								<?php foreach( $travel_advisor_useful_resources['useful_resources'] as $resource ) : ?>
									<?php $target = ( $resource['new_tab'] == true ) ? 'target="_blank"' : ''; ?>
									<p><a <?php echo $target; ?> href="<?php echo esc_url( $resource['url'] ); ?>"><?php echo esc_attr( $resource['title'] ); ?></a></p>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
							</div>
					</div>
				</div>
			<?php endif; ?>

			<div class="card mb-5 dashboard__card rounded-1 resource-center accordion-item" data-tour-guide="resource-center">
				<div class="card-body pb-0">
					<div class="d-flex justify-content-between align-items-baseline mb-2 mobile-toggle-heading" data-bs-toggle="collapse" data-bs-target="#resourceCenterCollapse" aria-expanded="false" aria-controls="resourceCenterCollapse">
						<?php if ( ! empty( $resource_center_general_information['section_title'] ) ) : ?>
							<h5 class="card-title fw-bold mb-2"><?php echo $resource_center_general_information['section_title'] ?></h5>
						<?php endif; ?>

						<!-- <?php if ( ! empty( $resource_center_general_information['section_url'] ) ) : ?>
							<a class="fs-sm lh-sm fw-medium" href="<?php echo esc_url( $resource_center_general_information['section_url'] ); ?>">View all</a>
						<?php endif; ?> -->
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="8" viewBox="0 0 14 8" fill="none">
							<path d="M7 7.54248C6.73633 7.54248 6.50195 7.45459 6.32617 7.27881L0.701172 1.65381C0.525391 1.47803 0.4375 1.24365 0.4375 0.97998C0.4375 0.745605 0.525391 0.51123 0.701172 0.306152C0.876953 0.130371 1.11133 0.0424805 1.375 0.0424805C1.60938 0.0424805 1.84375 0.130371 2.04883 0.306152L7 5.28662L11.9512 0.306152C12.127 0.130371 12.3613 0.0424805 12.625 0.0424805C12.8594 0.0424805 13.0938 0.130371 13.2988 0.306152C13.4746 0.51123 13.5625 0.745605 13.5625 0.97998C13.5625 1.24365 13.4746 1.47803 13.2988 1.65381L7.67383 7.27881C7.46875 7.45459 7.23438 7.54248 7 7.54248Z" fill="#333333"/>
						</svg>
					</div>
					<div class="accordion-collapse collapse" id="resourceCenterCollapse">
						<?php if ( ! empty( $resource_center_general_information['section_content'] ) ) : ?>
								<div class="fs-sm lh-sm fw-normal hide-mobile"><?php echo $resource_center_general_information['section_content']; ?></div>
							<?php endif; ?>
	
						<!-- <hr> -->
	
						<?php if ( ! empty( $resource_center_useful_resources['section_title'] ) ) : ?>
							<h6 class="card-subtitle fs-sm lh-sm fw-medium"><?php echo $resource_center_useful_resources['section_title']; ?></h6>
						<?php endif; ?>
	
						<?php if ( ! empty( $resource_center_useful_resources['useful_resources'] ) ) : ?>
							<div class="quick-links">
								<?php foreach( $resource_center_useful_resources['useful_resources'] as $resource ) : ?>
									<?php $target = ( $resource['new_tab'] == true ) ? 'target="_blank"' : ''; ?>
									<p><a <?php echo $target; ?> href="<?php echo esc_url( $resource['url'] ); ?>"><?php echo esc_attr( $resource['title'] ); ?></a></p>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
	
						<!-- <hr> -->
	
						<div class="video-link mb-4">
							<?php if ( ! empty( $resource_center_video_resources['section_title'] ) ) : ?>
								<h6 class="card-subtitle fs-sm lh-sm fw-medium"><?php echo $resource_center_video_resources['section_title']; ?></h6>
							<?php endif; ?>
	
							<?php if ( ! empty( $resource_center_video_resources['videos'] ) ) : ?>
								<?php foreach( $resource_center_video_resources['videos'] as $video ) : ?>
									<div class="my-account-video">
										<?php if ( ! empty( $video['video_description'] ) ) : ?>
											<div class="my-account-description"><?php echo $video['video_description']; ?></div>
										<?php endif; ?>
										<?php if ( ! empty( $video['video_url'] ) ) : ?>
											<iframe class="w-100" height="180" src="<?php echo $video['video_url']; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

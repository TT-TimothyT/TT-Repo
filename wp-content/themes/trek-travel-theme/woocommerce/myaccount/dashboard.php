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
$trips = trek_get_guest_trips(get_current_user_id(), 1,'', $is_log);
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
		<div class="col-lg-4">
			<div class="card mb-5 dashboard__card rounded-1">
				<div class="card-body pb-0">
					<h5 class="card-title fw-bold mb-4">Profile</h5>
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
						<h6 class="card-subtitle fs-sm lh-sm fw-medium">Medical Information</h6>
						<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/medical-information'); ?>">Edit</a>
					</div>
					<p class="fs-sm lh-sm fw-normal pb-2"> Manage your medical and emergency contact info</p>
					<hr>
					<div class="d-flex justify-content-between align-items-baseline mb-2 pt-2">
						<h6 class="card-subtitle fs-sm lh-sm fw-medium">Communication Preferences</h6>
						<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/communication-preferences'); ?>">Edit</a>
					</div>
					<p class="fs-sm lh-sm fw-normal">Manage your email and mail subscriptions</p>
				</div>
			</div>
			<div class="card mb-5 dashboard__card rounded-1">
				<div class="card-body pb-0">
					<div class="d-flex justify-content-between align-items-baseline mb-2">
						<h5 class="card-title fw-bold mb-2">Payment Information</h5>
						<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/payment-methods'); ?>">Edit</a>
					</div>
					<p class="fs-sm lh-sm fw-normal">Manage your payment methods</p>
				</div>
			</div>
		</div>
		
		<div class="col-lg-4">
			<?php
			// This is a custom capability added to the Travel Advisor custom role
			if ( current_user_can( 'travel_advisor' ) ) : ?>
				<div class="card mb-5 dashboard__card rounded-1">
					<div class="card-body pb-0">
						<?php if ( ! empty( $travel_advisor_general_information['section_title'] ) ) : ?>
							<div class="d-flex justify-content-between align-items-baseline mb-2">
								<h5 class="card-title fw-bold mb-2"><?php echo esc_attr( $travel_advisor_general_information['section_title'] ); ?></h5>
							</div>
						<?php endif; ?>

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
			<?php endif; ?>

			<div class="card mb-5 dashboard__card rounded-1">
				<div class="card-body pb-0">
					<div class="d-flex justify-content-between align-items-baseline mb-3">
						<h5 class="card-title fw-bold mb-2">My Trips</h5>
						<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/my-trips'); ?>">View all trips (<?php echo $trips['count']; ?>)</a>
					</div>
					<h6 class="card-subtitle fs-sm lh-sm fw-medium mb-2">Upcoming</h6>
					<?php if(empty($trips) || (isset($trips['count']) && $trips['count'] <=0 )) { ?>
					<p class="fs-sm lh-sm fw-normal">Your trips are on the way. To view them, please reload the page after a few seconds.</p>
					<a href="<?php echo site_url('bike-tours/all/') ?>" class="btn btn-lg btn-primary dashboard__button rounded-1 mb-4">Book a trip</a>
					<?php }else{
						$trips_html = '<p class="fs-sm lh-sm fw-normal">Your trips are on the way. To view them, please reload the page after a few seconds.</p>';
						if($trips && isset($trips['data'])){
							$trips_html = '';
							$showTwoTripsCounter = 0;
							foreach($trips['data'] as $trip ){
								if ($showTwoTripsCounter == 2) {
									break;
								}
								$showTwoTripsCounter++;
								$product_id = $trip['product_id'];
								$order_id = $trip['order_id'];
                                $order_details = trek_get_user_order_info($userInfo->ID, $order_id);
								$guest_is_primary = isset( $order_details[0]['guest_is_primary'] ) ? $order_details[0]['guest_is_primary'] : 0;
								$waiver_signed = isset( $order_details[0]['waiver_signed'] ) ? $order_details[0]['waiver_signed'] : false;
								$product = wc_get_product( $product_id );
								$product_name = '';
								if( $product ) {
									$product_name = $product->get_name();
								}
								$trip_name = $trip_sdate = $trip_edate = $trip_sku = '';
								$is_checklist_completed = tt_is_checklist_completed( $userInfo->ID, $order_id, $order_details[0]['rider_level'], $product_id, $order_details[0]['bike_id'], $guest_is_primary, $waiver_signed );
								if( $product ){
									$trip_status = $product->get_attribute( 'pa_trip-status' );
									$trip_sdate = $product->get_attribute( 'pa_start-date' ); 
									$trip_edate = $product->get_attribute( 'pa_end-date' );
									$trip_name = $product->get_name();
									$trip_sku = $product->get_sku();
									$tripRegion = tt_get_local_trips_detail('tripRegion', '', $trip_sku, true);
									$pa_city = $product->get_attribute( 'pa_city' );
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
									$start_date_text = date('F jS, Y', strtotime(implode('-', $sdate_info)));
									$end_date_text_1 = date('F jS, Y', strtotime(implode('-', $edate_info)));
									$end_date_text_2 = date('jS, Y', strtotime(implode('-', $edate_info)));
									$date_range_1 = $start_date_text. ' - '.$end_date_text_2;
									$date_range_2 = $start_date_text. ' - '.$end_date_text_1;
									$date_range = $date_range_1;
									if( $sdate_info['m'] != $edate_info['m'] ){
										$date_range = $date_range_2;
									}
									$product_image_url = 'https://via.placeholder.com/150?text=Trek Travel';
									if( has_post_thumbnail($product) ){
										$product_image_url = wp_get_attachment_url($product->get_image_id());
									}
									$trip_sku = $product->get_sku();
									$parentTrip = tt_get_parent_trip($trip_sku);
									$trip_link = esc_url( add_query_arg( 'order_id', $order_id, get_permalink( TREK_MY_ACCOUNT_PID ).'my-trip' ) );
								}
								$trips_html .= '<div class="dashboard__trip d-flex"><div class="my-upcoming-trips"><img src="'.$parentTrip['image'].'"></div><div class="w-50"><h6 class="fs-sm lh-sm fw-bold mb-1"><a href="'.$trip_link.'">'.$product_name.'</a></h6>';

								// Check if $pa_city exists before adding it
								if ( ! empty( $pa_city ) ) {
									$trips_html .= '<p class="fs-sm lh-sm fw-normal mb-1">'.$pa_city.', '.$tripRegion.'</p>';
								} else {
									$trips_html .= '<p class="fs-sm lh-sm fw-normal mb-1">'.$tripRegion.'</p>';
								}

								$trips_html .= '<p class="fs-sm lh-sm fw-normal mb-2">'.$date_range.'</p>';

								// Check if $order_details is not empty before adding the error message
								if ( ! empty( $order_details ) && ! $is_checklist_completed ) { 
									$trips_html .= '<p class="dashboard__error"><img src="'.TREK_DIR.'/assets/images/error.png"> You have pending items</p>';
								}

								$trips_html .= '</div></div>';
							}
						}
						echo $trips_html;
					} ?>
				</div>
			</div>
			<div class="card mb-3 dashboard__card rounded-1">
				<div class="card-body pb-0">
					<div class="d-flex justify-content-between align-items-baseline mb-2">
						<h5 class="card-title fw-bold mb-2">Bike & Gear Preferences</h5>
						<a class="fs-sm lh-sm fw-medium" href="<?php echo site_url('my-account/bike-gear-preferences'); ?>">Edit</a>
					</div>
					<p class="fs-sm lh-sm fw-normal">Manage your jersey size, helmet size, and more</p>
				</div>
			</div>
		</div>
		<div class="col-lg-4">
			<div class="card mb-5 dashboard__card rounded-1">
				<div class="card-body pb-0">
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
			<div class="card mb-5 dashboard__card rounded-1">
				<div class="card-body pb-0">
					<div class="d-flex justify-content-between align-items-baseline mb-2">
						<?php if ( ! empty( $resource_center_general_information['section_title'] ) ) : ?>
							<h5 class="card-title fw-bold mb-2"><?php echo $resource_center_general_information['section_title'] ?></h5>
						<?php endif; ?>

						<?php if ( ! empty( $resource_center_general_information['section_url'] ) ) : ?>
							<a class="fs-sm lh-sm fw-medium" href="<?php echo esc_url( $resource_center_general_information['section_url'] ); ?>">View all</a>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $resource_center_general_information['section_content'] ) ) : ?>
							<div class="fs-sm lh-sm fw-normal"><?php echo $resource_center_general_information['section_content']; ?></div>
						<?php endif; ?>

					<hr>

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

					<hr>

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

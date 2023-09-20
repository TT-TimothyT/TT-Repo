<?php
$order_id = $_REQUEST['order_id'];
$order = wc_get_order($order_id);
$userInfo = wp_get_current_user();
$user_id = $userInfo->ID;
$order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$userInfo = wp_get_current_user();
$accepted_p_ids = tt_get_line_items_product_ids();
$guest_emails_arr = trek_get_guest_emails($user_id, $order_id);
$User_order_info = trek_get_user_order_info($user_id, $order_id);
$guest_emails = implode(', ', $guest_emails_arr);
$tt_formatted = $tt_posted = array();
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
		$tt_posted = wc_get_order_item_meta($item_id, 'trek_user_checkout_data', true);
		$tt_formatted = wc_get_order_item_meta($item_id, 'trek_user_formatted_checkout_data', true);
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
$primary_address_1 = $tt_posted['shipping_address_1'];
$primary_address_2 = $tt_posted['shipping_address_2'];
$primary_country = $tt_posted['shipping_country'];
$billing_add_1 = $tt_posted['billing_address_1'];
$billing_add_2 = $tt_posted['billing_address_2'];
$billing_country = $tt_posted['billing_country'];
$billing_state = $tt_posted['billing_state'];
$billing_city = $tt_posted['billing_city'];
$billing_postcode = $tt_posted['billing_postcode'];
$billing_name = ($tt_posted['billing_first_name'] ? $tt_posted['billing_first_name'] . ' ' . $tt_posted['billing_last_name'] : '');
$shipping_name = ($tt_posted['shipping_first_name'] ? $tt_posted['shipping_first_name'] . ' ' . $tt_posted['shipping_last_name'] : '');
$biller_name = (!empty($billing_name) ? $billing_name : $shipping_name);
$emergence_cfname = isset($User_order_info[0]['emergency_contact_first_name']) ? $User_order_info[0]['emergency_contact_first_name'] : '';
$emergence_clname = isset($User_order_info[0]['emergency_contact_last_name']) ? $User_order_info[0]['emergency_contact_last_name'] : '';
$emergence_cphone = isset($User_order_info[0]['emergency_contact_phone']) ? $User_order_info[0]['emergency_contact_phone'] : '';
$emergence_crelationship = isset($User_order_info[0]['emergency_contact_relationship']) ? $User_order_info[0]['emergency_contact_relationship'] : '';
$medicalconditions = isset($User_order_info[0]['medical_conditions']) ? $User_order_info[0]['medical_conditions'] : '';
$medications = isset($User_order_info[0]['medications']) ? $User_order_info[0]['medications'] : '';
$allergies = isset($User_order_info[0]['allergies']) ? $User_order_info[0]['allergies'] : '';
$dietaryrestrictions = isset($User_order_info[0]['dietary_restrictions']) ? $User_order_info[0]['dietary_restrictions'] : '';

$waiver_signed = isset($User_order_info[0]['waiver_signed']) ? $User_order_info[0]['waiver_signed'] : '';
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
$guest_insurance_html = tt_guest_insurance_output($tt_posted);
$cc_account_four = get_post_meta($order_id, '_wc_cybersource_credit_card_account_four', true);
$cc_expiry_date = get_post_meta($order_id, '_wc_cybersource_credit_card_expiry_date', true);
$cc_card_type = get_post_meta($order_id, '_wc_cybersource_credit_card_type', true);
$trip_information = tt_get_trip_pid_sku_from_cart($order_id);
$product_image_url = $trip_information['parent_trip_image'];
$rooms_html = tt_rooms_output($tt_posted, true);
$guests_gears_data = tt_guest_details($tt_posted);
//deposite due vars
$depositAmount = tt_get_local_trips_detail('depositAmount', '', $trip_sku, true);
$depositAmount = $depositAmount ? str_ireplace(',', '', $depositAmount) : 0;
$depositAmount = floatval($depositAmount) * intval(isset($tt_posted['no_of_guests']) ? $tt_posted['no_of_guests'] : 1);
$cart_total = $order->get_total();
$remaining_amount = $cart_total - ($depositAmount ? $depositAmount : 0);
$remaining_amountCurr = get_woocommerce_currency_symbol() . $remaining_amount;
$cart_totalCurr = get_woocommerce_currency_symbol() . $cart_total;
$depositAmountCurr  = get_woocommerce_currency_symbol() . $depositAmount;
$tripRegion = tt_get_local_trips_detail('tripRegion', '', $trip_sku, true);
$parent_product_id = tt_get_parent_trip_id_by_child_sku($trip_sku);
$p_product = wc_get_product( $parent_product_id );
$pa_city = $p_product->get_attribute( 'pa_city' );
$singleSupplementQty = $bikeUpgradeQty = 0;
$occupants = isset($tt_posted['occupants']) && $tt_posted['occupants'] ? $tt_posted['occupants'] : [];
$singleSupplementQty += isset($occupants['private']) && $occupants['private'] ? count($occupants['private']) : 0;
$singleSupplementQty += isset($occupants['roommate']) && $occupants['roommate'] ? count($occupants['roommate']) : 0;
$insuredPerson = isset($tt_posted['insuredPerson']) ? $tt_posted['insuredPerson'] : 0;
$tt_insurance_total_charges = isset($tt_posted['tt_insurance_total_charges']) ? $tt_posted['tt_insurance_total_charges'] : 0;
?>
<div class="container my-trip-order-summary my-4">
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
			<div class="card my-trip-order-summary__card rounded-1">

				<div class="trips-list-item">
					<div class="trip-image">
						<img src="<?php echo $product_image_url; ?>" />
					</div>
					<div class="trip-info">
						<p class="fw-normal fs-sm lh-sm mb-0 mt-4">
						<?php
							$trip_address = [$pa_city,$tripRegion];
							$trip_address = array_filter($trip_address);
							echo implode(', ', $trip_address);
						?>
						</p>
						<h5 class="fw-semibold"><?php echo $trip_name; ?></h5>
						<p class="fw-medium fs-sm lh-sm"><?php echo $date_range; ?></p>

					</div>
					<div class="trip-details-cta my-4">
						<a href="" onclick="printThis('order-details-page');" class="btn btn-md w-100 btn-secondary btn-outline-dark rounded-1">Print Summary</a>
					</div>
				</div>

				<div class="container order-details p-0" id="order-details-page">
					<div class="row mx-0">
						<div class="col-12">
							<div class="order-details__summary">
								<div class="order-details__content">
									<p class="fs-xl lh-xl fw-medium">Purchase Summary</p>
									<div class="d-flex">
										<div class="w-50">
											<p class="mb-0 fw-normal order-details__text">Purchase Date</p>
											<p class="mb-0 fw-normal order-details__text">Confirmation #</p>
											<p class="mb-0 fw-normal order-details__text">Guests: <?php echo $tt_posted['no_of_guests']; ?></p>
											<?php if ($tt_posted['bikeUpgradePrice']) { ?>
												<p class="mb-0 fw-normal order-details__text">[Upgrade]</p>
											<?php } ?>
											<?php if ($singleSupplementQty > 0) { ?>
												<p class="mb-0 fw-normal order-details__text">[Single Suppliment]x <?php echo $singleSupplementQty; ?></p>
											<?php } ?>
											<?php if($insuredPerson > 0 && $tt_insurance_total_charges > 0 ) { ?>
												<p class="mb-0 fw-normal order-details__text">[Insurance Fees]x <?php echo $tt_insurance_total_charges; ?></p>
											<?php } ?>
											<p class="mb-0 fw-normal order-details__text">Subtotal</p>
											<p class="mb-0 fw-normal order-details__text">Local Taxes</p>
											<?php if (!empty($dues)) : ?>
												<p class="mb-0 fw-normal order-details__text">Trip Total</p>
												<p class="mb-0 mt-1 mt-lg-2 fw-medium order-details__textbold">Amount Paid</p>
												<p class="mt-2 mb-2 mt-lg-4 fw-medium order-details__textbold">Remaining Due</p>
											<?php else : ?>
												<p class="mt-1 mb-2 mt-lg-2 fw-medium order-details__textbold">Trip Total</p>
											<?php endif; ?>
										</div>
										<div class="w-50">
											<p class="mb-0 fw-normal order-details__text"><?php echo date('M d, Y', strtotime($order->get_date_created())) ?></p>
											<p class="mb-0 fw-normal order-details__text"><?php echo $order_id; ?></p>
											<p class="mb-0 fw-normal order-details__text"><?php echo $order->get_formatted_line_subtotal($order_item) ?></p>
											<?php if ($tt_posted['bikeUpgradePrice']) { ?>
												<p class="mb-0 fw-normal order-details__text"><?php echo get_woocommerce_currency_symbol() . $tt_posted['bikeUpgradePrice']; ?></p>
											<?php } ?>
											<?php if ($singleSupplementQty > 0) { ?>
												<p class="mb-0 fw-normal order-details__text"><?php echo get_woocommerce_currency_symbol() . $singleSupplementPrice; ?></p>
											<?php } ?>
											<p class="mb-0 fw-normal order-details__text"><?php echo get_woocommerce_currency_symbol() . $order->get_subtotal(); ?></p>
											<p class="mb-0 fw-normal order-details__text"><?php echo get_woocommerce_currency_symbol() . $order->get_cart_tax(); ?></p>
											<?php if (!empty($dues)) : ?>
												<p class="mb-0 fw-normal order-details__text"><?php echo $cart_totalCurr; ?></p>
												<p class="mb-0 mt-1 mt-lg-2 fw-medium order-details__textbold"><?php echo $depositAmountCurr; ?></p>
												<p class="mt-2 mb-2 mt-lg-4 fw-medium order-details__textbold"><?php echo $remaining_amountCurr; ?></p>
											<?php else : ?>
												<p class="mt-1 mb-2 mt-lg-2 fw-medium order-details__textbold"><?php echo $cart_totalCurr; ?></p>
											<?php endif; ?>
										</div>
									</div>
									<?php if (!empty($dues)) : ?>
										<p class="mb-0 fs-sm lh-sm fw-normal w-lg-50 order-details__duesp">You will be responsible for paying the remaining amount on your trip before the trip start date. Our team will reach out to collect final payment.</p>
									<?php endif; ?>
								</div>
								<hr>
								<div class="order-details__content">
									<p class="fs-xl lh-xl fw-medium order-details__subheading">Payment Details</p>
									<div class="d-flex order-details__flex">
										<div>
											<p class="mb-2 fs-md lh-md fw-medium">Billing Method</p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $biller_name; ?></p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo ($cc_card_type ? $cc_card_type  : '') ?> <?php echo ($cc_account_four ? '****' . $cc_account_four  : '') ?></p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo ($cc_expiry_date ? 'Exp' . $cc_expiry_date  : '') ?></p>
										</div>
										<div>
											<p class="mb-2 fs-md lh-md fw-medium">Billing Address</p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_add_1; ?></p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_add_2; ?></p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_city; ?>, <?php echo $billing_state; ?>, <?php echo $billing_postcode; ?></p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_country; ?></p>
										</div>
									</div>
								</div>
								<hr>
								<div class="order-details__content">
									<p class="fs-xl lh-xl fw-medium order-details__subheading">Guest Details</p>
									<?php echo $guests_gears_data['guests']; ?>
								</div>
								<hr>
								<div class="order-details__content">
									<p class="fs-xl lh-xl fw-medium order-details__subheading">Room Details</p>
									<div class="d-flex order-details__flex">
										<?php
										echo $rooms_html;
										?>
									</div>
								</div>
								<hr>
								<div class="order-details__content">
									<p class="fs-xl lh-xl fw-medium order-details__subheading">Bikes &amp; Gear Details</p>
									<?php echo $guests_gears_data['bike_gears']; ?>
								</div>
								<hr>
								<div class="order-details__content">
									<p class="fs-xl lh-xl fw-medium order-details__subheading">Travel Protection Details</p>
									<?php echo $guest_insurance_html; ?>
								</div>
							</div>
						</div>
					</div>
				</div>


			</div>
		</div>
	</div>
</div>
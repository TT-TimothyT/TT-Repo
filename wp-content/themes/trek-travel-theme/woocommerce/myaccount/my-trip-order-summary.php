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
			$p_id = $product->get_id();
			$trip_status = tt_get_custom_product_tax_value( $p_id, 'trip-status', true );
			$trip_sdate = $product->get_attribute('pa_start-date');
			$trip_edate = $product->get_attribute('pa_end-date');
			$trip_name = $product->get_name();
			$trip_sku = $product->get_sku();
			$parent_trip = tt_get_parent_trip_group($trip_sku);
			// Load parent product if available
			$parent_product = $parent_trip['id'] ? wc_get_product($parent_trip['id']) : null;

			// Set parent product name and link, with fallbacks if parent is unavailable
			$parent_name = $parent_product ? $parent_product->get_name() : $product->get_name();
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
			$start_date_text = date('F jS', strtotime(implode('-', $sdate_info)));
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
$guest_insurance_html = tt_guest_insurance_output($tt_posted, '', '');
$cc_account_four = get_post_meta($order_id, '_wc_cybersource_credit_card_account_four', true);
$cc_expiry_date = get_post_meta($order_id, '_wc_cybersource_credit_card_expiry_date', true);
$cc_card_type = get_post_meta($order_id, '_wc_cybersource_credit_card_type', true);
$trip_information = tt_get_trip_pid_sku_from_cart($order_id);
$product_image_url = $trip_information['parent_trip_image'];
$rooms_html = tt_rooms_output($tt_posted, true);
$guests_gears_data = tt_guest_details($tt_posted);
//deposite due vars
$depositAmount = tt_get_local_trips_detail('depositAmount', $trip_information['ns_trip_Id'], $trip_sku, true);

//get the supliment fees
$supplementFees = tt_get_local_trips_detail('singleSupplementPrice', $trip_information['ns_trip_Id'], $trip_sku, true);
$supplementFees = wc_format_decimal( $supplementFees );

//Get the products from the order
$products = $order->get_items();

//Loop through the products
foreach( $products as $product ) {
    //Get the product name
    $product_name = $product->get_name();
    if( $product_name == "Single Supplement Fees" ) {
        // Get product quantity
        $product_quantity = $product->get_quantity();
        $supplementFees = $supplementFees * $product_quantity;
    }

    if( $product_name == 'Travel Protection' ) {
        // Get product quantity
        $product_quantity = $product->get_quantity();
        $insuranceFees = $product->get_total();
    }
}

if( ! empty( $supplementFees ) ) {
    $depositAmount = $depositAmount ? str_ireplace(',', '', $depositAmount) : 0;
    $depositAmount = floatval($depositAmount) * intval(isset($trek_checkoutData['no_of_guests']) ? $trek_checkoutData['no_of_guests'] : 1);
    $depositAmount = $depositAmount + floatval( $supplementFees );
    $depositAmount = $depositAmount + floatval( $insuranceFees );
} else {
    $depositAmount = $depositAmount ? str_ireplace(',', '', $depositAmount) : 0;
    $depositAmount = floatval($depositAmount) * intval(isset($trek_checkoutData['no_of_guests']) ? $trek_checkoutData['no_of_guests'] : 1);
    $depositAmount = $depositAmount + floatval( $insuranceFees );
}

$taxes_amount = floatval( $order->get_cart_tax() );

if( ! empty( $taxes_amount ) ) {
    $depositAmount = $depositAmount + floatval( $taxes_amount );
}

$depositAmount = round( $depositAmount, 3 );


$cart_total = $order->get_total();
$remaining_amount = $cart_total - ($depositAmount ? $depositAmount : 0);
$remaining_amountCurr = get_woocommerce_currency_symbol() . $remaining_amount;
$cart_totalCurr = get_woocommerce_currency_symbol() . $cart_total;
$depositAmountCurr  = get_woocommerce_currency_symbol() . $depositAmount;
$tripRegion = tt_get_local_trips_detail('tripRegion', $trip_information['ns_trip_Id'], $trip_sku, true);
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
		<div class="col-12">
			<div class="card my-trip-order-summary__card rounded-1">

				<div class="trips-list-item row">
					<div class="trip-image col-12 col-md-6 col-xl-4">
						<img src="<?php echo $product_image_url; ?>">
					</div>
					<div class="trip-box col-12 col-md-6 col-xl-8 col-xxl-7">
						<div class="trip-info">
						<h5 class="fw-semibold"><a href="<?php echo $parent_trip['link']; ?>" target="_blank"><?php echo $parent_name; ?></a></h5>
							<p class="fw-medium lh-sm"><?php echo $date_range; ?></p>
						</div>
						<div class="trip-details-cta">
							<a href="" onclick="printThis('order-details-page');" class="btn btn-secondary btn-outline-dark rounded-1">Print Summary</a>
						</div>
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
												<p class="mb-0 fw-normal order-details__text">[Single Supplement]x <?php echo $singleSupplementQty; ?></p>
											<?php } ?>
											<?php if($insuredPerson > 0 && $tt_insurance_total_charges > 0 ) { ?>
												<p class="mb-0 fw-normal order-details__text">[Travel Protection]x <?php echo $tt_insurance_total_charges; ?></p>
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
											<?php
											$local_tax = $order->get_cart_tax();
											if ( '0' === $local_tax ) {
												$local_tax = $order->get_total() - $order->get_subtotal();
											}
											?>
											<p class="mb-0 fw-normal order-details__text"><?php echo $local_tax; ?></p>
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
											<?php
                                            $billing_states       = WC()->countries->get_states( $billing_country );
                                            $billing_state_name   = isset( $billing_states[$billing_state] ) ? $billing_states[$billing_state] : $billing_state;
                                            $billing_country_name = WC()->countries->countries[$billing_country];
                                            ?>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_add_1; ?></p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_add_2; ?></p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_city; ?>, <?php echo $billing_state_name; ?>, <?php echo $billing_postcode; ?></p>
											<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_country_name; ?></p>
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
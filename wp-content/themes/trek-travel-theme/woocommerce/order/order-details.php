<?php

/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.6.0
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if (!$order) {
	return;
}
$userInfo = wp_get_current_user();
$order_items           = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$show_purchase_note    = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();

if ($show_downloads) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>


<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action('woocommerce_after_order_details', $order);



//to be removed just for design implementation
$dues = "true";
?>
<?php
$accepted_p_ids = tt_get_line_items_product_ids();
$trek_formatted_checkoutData = $trek_checkoutData = array();
$trip_name = $trip_order_date = '';
$trip_name = $trip_sdate = $trip_edate = $trip_sku = '';
$order_item;
$pr = get_post_meta($order_id);
$cc_account_four = get_post_meta($order_id, '_wc_cybersource_credit_card_account_four', true);
$cc_expiry_date = get_post_meta($order_id, '_wc_cybersource_credit_card_card_expiry_date', true);
$cc_expiry_date_arr = explode('-',$cc_expiry_date);
$cc_expiry_date_arr = array_reverse($cc_expiry_date_arr);
$cc_expiry_date = implode('/',$cc_expiry_date_arr);
$cc_card_type = get_post_meta($order_id, '_wc_cybersource_credit_card_card_type', true);
foreach ($order_items as $item_id => $item) {
	$product_id = $item['product_id'];
    if ( !in_array($product_id, $accepted_p_ids) ) {
		$order_item = $item;
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
			if (has_post_thumbnail($product)) {
				$product_image_url = wp_get_attachment_url($product->get_image_id());
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
$billing_state = $trek_checkoutData['billing_state'];
$billing_city = $trek_checkoutData['billing_city'];
$billing_postcode = $trek_checkoutData['billing_postcode'];
$billing_name = ($trek_checkoutData['billing_first_name'] ? $trek_checkoutData['billing_first_name'] . ' ' . $trek_checkoutData['billing_last_name'] : '');
$shipping_name = ($trek_checkoutData['shipping_first_name'] ? $trek_checkoutData['shipping_first_name'] . ' ' . $trek_checkoutData['shipping_last_name'] : '');
$biller_name = (!empty($billing_name) ? $billing_name : $shipping_name);
$guest_insurance_html = tt_guest_insurance_output($trek_checkoutData);
$singleSupplementQty = $bikeUpgradeQty = 0;
$occupants = isset($trek_checkoutData['occupants']) && $trek_checkoutData['occupants'] ? $trek_checkoutData['occupants'] : [];
$singleSupplementQty += isset($occupants['private']) && $occupants['private'] ? count($occupants['private']) : 0;
$singleSupplementQty += isset($occupants['roommate']) && $occupants['roommate'] ? count($occupants['roommate']) : 0;
$singleSupplementPrice = isset($trek_checkoutData['singleSupplementPrice']) ? $trek_checkoutData['singleSupplementPrice'] : 0;
$singleSupplementPrice = $singleSupplementPrice * $singleSupplementQty;
$rooms_html = tt_rooms_output($trek_checkoutData, true);
$guests_gears_data = tt_guest_details($trek_checkoutData);
//deposite due vars

if( $singleSupplementPrice == 1 ) {
	$supplementFees = tt_get_local_trips_detail('singleSupplementPrice', '', $trip_sku, true);

	//Get the products from the order
	$supplementFees = str_ireplace(',','',$supplementFees);


	//strip the , from the price if there's such
	$calcSupplementFees = floatval( $supplementFees ) * $singleSupplementQty;


	$calcSupplementFees = strval( $calcSupplementFees );


	//Get the , back to the string
	$supplementFees = number_format( $calcSupplementFees, 2 );

} else {
	$supplementFees = $singleSupplementPrice;
}

$depositAmount = tt_get_local_trips_detail('depositAmount', '', $trip_sku, true);
$depositAmount = $depositAmount ? str_ireplace(',','',$depositAmount) : 0;
$depositAmount = floatval($depositAmount) * intval(isset($trek_checkoutData['no_of_guests']) ? $trek_checkoutData['no_of_guests'] : 1);
$cart_total = $order->get_total();
$remaining_amount = $cart_total - ($depositAmount ? $depositAmount : 0);
$remaining_amountCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $remaining_amount .'</span>';
$cart_totalCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $cart_total .'</span>';
$depositAmountCurr  = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $depositAmount .'</span>';
$insuredPerson = isset($trek_checkoutData['insuredPerson']) ? $trek_checkoutData['insuredPerson'] : 0;
$tt_insurance_total_charges = isset($trek_checkoutData['tt_insurance_total_charges']) ? $trek_checkoutData['tt_insurance_total_charges'] : 0;
$insurance_array = isset($trek_checkoutData['trek_guest_insurance']) ? $trek_checkoutData['trek_guest_insurance'] : 0;
$insuredPerson = 0;
$tt_insurance_total_charges = 0;
if( $insurance_array ){
	foreach($insurance_array as $insurance_k=>$insurance_v){
		if( $insurance_k == 'primary' ){
			if( $insurance_v['is_travel_protection'] == 1 ){
				$insuredPerson++;
				$tt_insurance_total_charges += isset($insurance_v['basePremium']) ? $insurance_v['basePremium'] : 0;
			}
		}else{
			foreach ($insurance_v as $guest_key => $guest_insurance_Data) {
				if( $guest_insurance_Data['is_travel_protection'] == 1 ){
					$insuredPerson++;
					$tt_insurance_total_charges += isset($guest_insurance_Data['basePremium']) ? $guest_insurance_Data['basePremium'] : 0;
				}
			}
		}
	}
}
$guest_emails = trek_get_guest_emails($order_id);
$tt_get_upgrade_qty = tt_get_upgrade_qty($trek_checkoutData);
$dues = isset($trek_checkoutData['pay_amount']) && $trek_checkoutData['pay_amount'] == 'full' ? false : true;

?>
<div class="container-fluid order-details__banner d-flex justify-content-end flex-column">
	<h1 class="mb-0 mb-lg-1 order-details__banner-heading">Thank You!</h1>
	<p class="order-details__banner-text">Success Msg - Lorem ipsum dolor sit amet</p>
</div>
<div class="container order-details" id="order-details-page">
	<div class="row">
		<div class="col-12">
			<div class="order-details__number">
				<p class="fs-xl lh-xl">Your confirmation number is <span class="fw-bold"><?php echo $order_id; ?></span></p>
				<p class="fs-xl lh-xl">We will send a confirmation email to <span class="fw-medium" id="wc-order-emails"><?php echo $guest_emails && !is_array($guest_emails) ? $guest_emails : ''; ?></span></p>
			</div>
			<button class="btn btn-lg btn-primary rounded-1 order-details__print" onclick="printThis('order-details-page');">Print summary</button>
			<div class="order-details__quite rounded-1">
				<h5 class="fw-semibold order-details__title mb-3">You’re not quite done yet...</h5>
				<p class="fs-xl lh-xl">Please note that we will need to collect additional information for all guests before the trip starts. <a href="/my-account">View your account</a> now to add your information.</p>
				<p class="fs-xl lh-xl mb-0"><a href="/contact-us">Contact us</a> if you have any questions or concerns.</p>
			</div>
			<div class="order-details__summary">
				<h4 class="order-details__heading"><?php echo $trip_name ?></h4>
				<p class="fs-xl lh-xl fw-medium pb-2"><?php echo $date_range; ?></p>
				<hr>
				<div class="order-details__content">
					<p class="fs-xl lh-xl fw-medium">Purchase Summary</p>
					<div class="d-flex">
						<div class="w-50">
							<p class="mb-0 fw-normal order-details__text">Purchase Date</p>
							<p class="mb-0 fw-normal order-details__text">Confirmation #</p>
							<p class="mb-0 fw-normal order-details__text">Guests: <small>x<?php echo $trek_checkoutData['no_of_guests']; ?></small></p>
							<?php if( $tt_get_upgrade_qty > 0 &&  $trek_checkoutData['bikeUpgradePrice'] ) { ?>
							<p class="mb-0 fw-normal order-details__text">Upgrade <small>x<?php echo $tt_get_upgrade_qty; ?></small></p>
							<?php } ?>
							<?php if($singleSupplementQty > 0) { ?>
							<p class="mb-0 fw-normal order-details__text">Single Suppliment <small>x<?php echo $singleSupplementQty; ?></small></p>
							<?php } ?>
							<?php if($insuredPerson > 0 && $tt_insurance_total_charges > 0 ) { ?>
							<p class="mb-0 fw-normal order-details__text">Travel Protection <small>x<?php echo $insuredPerson; ?></small></p>
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
							<?php if( $tt_get_upgrade_qty > 0 &&  $trek_checkoutData['bikeUpgradePrice'] ) { ?>
							<p class="mb-0 fw-normal order-details__text"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $trek_checkoutData['bikeUpgradePrice']; ?></span></p>
							<?php } ?>
							<?php if($singleSupplementQty > 0) { ?>
							<p class="mb-0 fw-normal order-details__text"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $supplementFees; ?></span></p>
							<?php } ?>
							<?php if($insuredPerson > 0 && $tt_insurance_total_charges > 0 ) { ?>
							<p class="mb-0 fw-normal order-details__text"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $tt_insurance_total_charges; ?></span></p>
							<?php } ?>
							<p class="mb-0 fw-normal order-details__text"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $order->get_subtotal(); ?></span></p>
							<?php
							$local_tax = $order->get_cart_tax();
							if ( '0' === $local_tax ) {
								$local_tax = $order->get_total() - $order->get_subtotal();
							}
							?>
							<p class="mb-0 fw-normal order-details__text"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $local_tax; ?></span></p>
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
							<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo ( $cc_card_type ? ucfirst($cc_card_type)  : '' ) ?> <?php echo ( $cc_account_four ? '****'.$cc_account_four  : '' ) ?></p>
							<p class="mb-0 fs-sm lh-sm fw-normal"><?php echo ( $cc_expiry_date ? 'Exp: '.$cc_expiry_date  : '' ) ?></p>
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
					<?php
					echo $guests_gears_data['guests'];
					?>
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
					<p class="fs-xl lh-xl fw-medium order-details__subheading">Bikes & Gear Details</p>
					<?php echo $guests_gears_data['bike_gears']; ?>
				</div>
				<hr>
				<div class="order-details__content">
					<p class="fs-xl lh-xl fw-medium order-details__subheading">Travel Protection Information</p>
					<?php echo $guest_insurance_html; ?>
				</div>
				<hr>
				<div class="order-details__content">
					<h5 class="fw-semibold order-details__title order-details__formtitle">How did you hear about us?</h5>
					<form>
						<div class="form-group mb-4">
							<select name="find" id="find" class="form-select py-4 px-5" required>
								<option value="">Friend or Family</option>
								<option value="">Travel Agent</option>
								<option value="">Trek Bicycle Store</option>
								<option value="">Trek Travel Guide</option>
								<option value="">Online Advertising</option>
								<option value="">Social Media</option>
								<option value="">Email</option>
								<option value="">Virtual Event</option>
								<option value="">Other</option>
							</select>
						</div>
						<div class="form-group">
							<input type="text" class="form-control py-4 px-5" name="please_specify" id="please_specify" placeholder="Please specify" value="">
						</div>
						<button type="submit" class="btn btn-lg btn-primary rounded-1 order-details__submit">Submit</button>
					</form>
				</div>
				<hr>
				<div class="order-details__accountdiv text-center">
					<a href="/my-account" class="btn btn-outline-primary rounded-1 py-4 px-5 order-details__account">Go to My Account</a>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function () {

		dataLayer.push({ ecommerce: null });
		var productsArr = [{
						'name': "<?php echo preg_replace('/[^A-Za-z0-9]/', '', $trip_name) ?>", // Please remove special characters
						'id': '<?php echo $item['product_id'] ?>', // Parent ID
						'price': '<?php echo $cart_total ?>', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
						'brand': '', //
						'category': '<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>', // populate with the 'country,continent' separating with a comma
						'variant': '<?php echo $trip_sku ?>', //this is the SKU of the product
						'quantity': '1' //the number of products added to the cart
					}];

					<?php if( $tt_get_upgrade_qty > 0 &&  $trek_checkoutData['bikeUpgradePrice'] ) { ?>
						productsArr.push({
							'name': "upgrade", // Please remove special characters
						'id': '<?php echo $item['product_id'] ?>', // Parent ID
						'price': '<?php echo $trek_checkoutData['bikeUpgradePrice'] ?>', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
						'brand': '', //
						'category': '<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>', // populate with the 'country,continent' separating with a comma
						'variant': '<?php echo $trip_sku ?>', //this is the SKU of the product
						'quantity': '1' //the number of products added to the cart
						})

						<?php 
					}
					if($singleSupplementQty > 0) { 
					 ?>
					 productsArr.push({
							'name': "single suppliment fee", // Please remove special characters
						'id': '<?php echo $item['product_id'] ?>', // Parent ID
						'price': '<?php echo $supplementFees ?>', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
						'brand': '', //
						'category': '<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>', // populate with the 'country,continent' separating with a comma
						'variant': '<?php echo $trip_sku ?>', //this is the SKU of the product
						'quantity': '1' //the number of products added to the cart
						})

					 <?php } ?>


		dataLayer.push({ 
			'event':'purchase',
			'ecommerce': {
				'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
				'purchase': {
					'actionField':{
						'id': '<?php echo $order_id; ?>', // populate with the order number
						'revenue': '<?php echo $cart_total; ?>',  // total price the customer paid 
						'product_revenue': '<?php echo $cart_total; ?>',  // product revenue paid                      
						'tax':'<?php echo $item['total_tax'] ?>', // amount of tax paid
						'shipping': '', // amount of shipping paid
						'coupon': '<?php echo $item['coupon_code'] ?>', // order level coupon. Pipe delimit stacked codes.
						'payment_type': 'credit card', // pipe delimit multiple values:ApplePay,GooglePay,Paypal, AfterPay, GiftCard, CreditCard 
						'order_discount': '' // order level discount amount
					},
					'products': productsArr
				}
			}
		})
		jQuery("#currency_switcher").trigger("change")
	})
</script>
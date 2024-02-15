<?php

/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
	exit;
}
if (!isset($_GET['step']) && is_checkout() && !is_admin()) {
	wp_redirect(trek_checkout_step_link(1));
}
/**
 * Make check for unavailable trips in the cart and remove them.
 * For unavailable trips will be considered a trips with status "Remove from Stella" and
 * trips with specific status from woocommerce.
 * 
 * This function is located in /trek-travel-theme/inc/trek-general-function.php
 */
tt_check_and_remove_old_trips_in_persistent_cart();
?>
<?php
// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}
$tt_checkout_data =  get_trek_user_checkout_data();
$tt_posted = isset($tt_checkout_data['posted']) ? $tt_checkout_data['posted'] : array();
$primary_address_1 = isset($tt_posted['shipping_address_1']) ? $tt_posted['shipping_address_1'] : '';
$primary_address_2 = isset($tt_posted['shipping_address_2']) ? $tt_posted['shipping_address_2'] : '';
$primary_country = isset($tt_posted['shipping_country']) ? $tt_posted['shipping_country'] : '';
$tripInfo = tt_get_trip_pid_sku_from_cart();
$parent_trip_link = isset($tripInfo['parent_trip_link']) ? $tripInfo['parent_trip_link'] : 'javascript:';
?>
<div class="card-wizard">
	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

		<div class="container px-0">
			<div class="row mx-0">
				<div class="col-12">
					<div class="checkout-timeline__back">
						<a href="<?php echo $parent_trip_link; ?>" class="d-flex align-items-center">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkout/checkout-arrow.png">
							<p class="fw-medium mb-0">Back to Trip Details</p>
						</a>
					</div>
				</div>
			</div>
			<div class="row mx-0 checkout-trek">
				<div class="col-lg-7">
					<?php wc_get_template('woocommerce/checkout/checkout-timeline.php'); ?>
					<div class="card-body">
						<div class="tab-content">
							<div class="tab-pane <?php if (isset($_GET['step']) && $_GET['step'] == 1) echo 'active show'; ?>" id="guest" data-step="1">
								<?php
								//if (isset($_REQUEST['step']) && $_REQUEST['step'] == 1) {
								$checkout_guest = __DIR__ . '/checkout-guest.php';
								if (is_readable($checkout_guest)) {
									wc_get_template('woocommerce/checkout/checkout-guest.php');
								} else {
									echo '<h3>Step 1</h3><p>Checkout guest form code is missing!</p>';
								}
								//}
								?>
							</div>
							<div class="tab-pane <?php if (isset($_GET['step']) && $_GET['step'] == 2) echo 'active show'; ?>" id="rooms" data-step="2">
								<?php
								//if (isset($_REQUEST['step']) && $_REQUEST['step'] == 2) {
								$checkout_hotel = __DIR__ . '/checkout-hotel.php';
								echo '<div id="tt-hotel-occupant-inner-html">';
								if (is_readable($checkout_hotel)) {
									wc_get_template('woocommerce/checkout/checkout-hotel.php');
								} else {
									echo 'Checkout Hotel form code is missing!';
								}
								echo '</div>';
								$checkout_bikes = __DIR__ . '/checkout-bikes.php';
								if (is_readable($checkout_bikes)) {
									wc_get_template('woocommerce/checkout/checkout-bikes.php');
								} else {
									echo '<h3>Step 2</h3><p>Checkout Bike form code is missing!</p>';
								}
								//}
								?>
							</div>
							<div class="tab-pane <?php if (isset($_GET['step']) && $_GET['step'] == 3) echo 'active show'; ?>" id="trip-payment" data-step="3">
								<?php
								//if (isset($_REQUEST['step']) && $_REQUEST['step'] == 3) {
								$checkout_payment = __DIR__ . '/checkout-payment.php';
								if (is_readable($checkout_payment)) {
									wc_get_template('woocommerce/checkout/checkout-payment.php');
								} else {
									echo '<h3>Step 3</h3><p>Checkout Payment form code is missing!</p>';
								}
								//}
								?>
							</div>
							<div class="tab-pane <?php if (isset($_GET['step']) && $_GET['step'] == 4) echo 'active show'; ?>" id="review" data-step="4">
								<?php

								//if (isset($_REQUEST['step']) && $_REQUEST['step'] == 4) {
								$checkout_review = __DIR__ . '/checkout-reviews.php';
								if (is_readable($checkout_review)) {
									wc_get_template('woocommerce/checkout/checkout-reviews.php');
								} else {
									echo '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
								}
								//}
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div id="tt-review-order">
						<?php do_action('woocommerce_checkout_order_review');?>
					</div>
				</div>
			</div>
			<?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
			<!-- Begin: Travel Protection modal form  -->
			<!-- Modal -->
			<div class="modal fade modal-search-filter" id="protection_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">

						<div class="modal-header">
							<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
								<i type="button" class="bi bi-x"></i>
							</span>
						</div>

						<div class="modal-body">
							<h4 class="fw-semibold modal-body__title">Travel Protection</h4>
							<p class="fs-lg lh-lg fw-bold mb-4">Please tell us who will be covered in this booking</p>
							<p class="modal-body__sub">We can also insure additional trip costs, such as flights and non-refundable hotels. You can learn more on our travel protection page <a href="<?php echo home_url( '/travel-protection/' ); ?>"  target="_blank">here</a> or call us at <a href="tel:866-464-8735">866-464-8735</a>.</p>
							<p class="modal-body__sub">To review full plan details online, please visit: <a href="<?php echo home_url( '/travel-protection/' ); ?>" target="_blank">here</a></p>
							<hr>
							<!-- <form method="post" name="travel-protection" id="travel-protection"> -->
							<?php
							$guest_insurance_html = '';
							$guests = isset($tt_posted['guests']) ? $tt_posted['guests'] : array();
							$guest_insurance = isset($tt_posted['trek_guest_insurance']) ? $tt_posted['trek_guest_insurance'] : array();
							$primary_name  = '';
							if (isset($tt_posted['shipping_first_name']) && $tt_posted['shipping_first_name']) {
								$primary_name .= $tt_posted['shipping_first_name'];
							}
							if (isset($tt_posted['shipping_last_name']) && $tt_posted['shipping_last_name']) {
								$primary_name .= ' ' . $tt_posted['shipping_last_name'];
							}
							$basePremium = (isset($guest_insurance['primary']['basePremium']) ? $guest_insurance['primary']['basePremium'] : 0);
							$basePremium = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>'.$basePremium.'</span>';
							$guest_insurance_html .= '<div class="modal-body__guest">
										<p class="mb-4 fw-medium">Primary Guest: ' . $primary_name . '</p>
										<div class="d-flex align-items-center mb-4">
											<input type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="1" ' . (isset($guest_insurance['primary']['is_travel_protection']) && $guest_insurance['primary']['is_travel_protection'] != 0 ? 'checked' : '') . '>
											<input type="hidden"  name="trek_guest_insurance[primary][basePremium]" value="' . (isset($guest_insurance['primary']['basePremium']) ? $guest_insurance['primary']['basePremium'] : 0) . '">
											<label>Add Travel Protection <span class="fw-bold">(' . $basePremium . ')</span></label>
										</div>
										<div class="d-flex align-items-center">
											<input type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="0" ' . (isset($guest_insurance['primary']['is_travel_protection']) && $guest_insurance['primary']['is_travel_protection'] == 0 ? 'checked' : '') . '>
											<label>Decline Travel Protection</label>
										</div>
									</div>';
							if ($guests && !empty($guests)) {
								foreach ($guests as $guest_k => $guest) {
									$basePremium = (isset($guest_insurance["guests"][$guest_k]['basePremium']) ? $guest_insurance["guests"][$guest_k]['basePremium'] : 0);
									$basePremium = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>'.$basePremium.'</span>';
									$guest_insurance_html .= '<hr><div class="modal-body__guest">
												<p class="mb-4 fw-medium">Guest: ' . $guest['guest_fname'] . ' ' . $guest['guest_lname'] . '</p>
												<div class="d-flex align-items-center mb-4">
													<input type="radio" class="guest_radio" name="trek_guest_insurance[guests][' . $guest_k . '][is_travel_protection]" value="1" ' . (isset($guest_insurance["guests"][$guest_k]["is_travel_protection"]) && $guest_insurance["guests"][$guest_k]["is_travel_protection"] != 0 ? 'checked' : '') . '>
													<input type="hidden" name="trek_guest_insurance[guests][' . $guest_k . '][basePremium]" value="' . (isset($guest_insurance["guests"][$guest_k]["basePremium"]) ? $guest_insurance["guests"][$guest_k]["basePremium"] : 0) . '">
													<label>Add Travel Protection <span class="fw-bold">(' . $basePremium . ')</span></label>
												</div>
												<div class="d-flex align-items-center">
													<input type="radio" class="guest_radio" name="trek_guest_insurance[guests][' . $guest_k . '][is_travel_protection]" value="0" ' . (isset($guest_insurance["guests"][$guest_k]["is_travel_protection"]) && $guest_insurance["guests"][$guest_k]["is_travel_protection"]  == 0 ? 'checked' : '') . '>
													<label>Decline Travel Protection</label>
												</div>
											</div>';
								}
							}
							//echo $guest_insurance_html;
							echo '<div id="tt-popup-insured-form">' . $guest_insurance_html . '</div>';
							?>
							<div class="modal-body__footer d-lg-flex align-items-center">
								<button type="submit" class="btn btn-primary submit_protection" data-bs-dismiss="modal">Submit</button>
								<div>
									<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">Cancel</span>
								</div>
							</div>
							<!-- </form> -->
						</div>
					</div><!-- / .modal-content -->
				</div><!-- / .modal-dialog -->
			</div><!-- / .modal -->
			<!-- End: Travel Protection modal form -->
			<input type="hidden" name="step" value="<?php echo (isset($_REQUEST['step']) ? $_REQUEST['step'] : '1'); ?>">
		</div>
	</form>
</div>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
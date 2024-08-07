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
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_GET['step'] ) && is_checkout() && ! is_admin() ) {
	wp_redirect( trek_checkout_step_link(1) );
}

/**
 * Make check for unavailable trips in the cart and remove them.
 * For unavailable trips will be considered a trips with status "Remove from Stella" and
 * trips with specific status from woocommerce.
 * 
 * This function is located in /trek-travel-theme/inc/trek-general-function.php
 */
tt_check_and_remove_old_trips_in_persistent_cart();

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

$trip_info          = tt_get_trip_pid_sku_from_cart();
$parent_trip_link   = tt_validate( $trip_info['parent_trip_link'], 'javascript:' );
$is_hiking_checkout = tt_is_product_line( 'Hiking', $trip_info['sku'] );
$current_step       = tt_validate( $_REQUEST['step'], '1' );
?>
<div class="card-wizard">
	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
		<div class="container px-0">
			<div class="mx-0 checkout-trek pt-5">
				<div class="checkout-trek-main">
					<?php wc_get_template('woocommerce/checkout/checkout-timeline.php'); ?>
					<div class="card-body">
						<div class="tab-content">
							<div class="tab-pane <?php echo esc_attr( isset( $_GET['step'] ) && $_GET['step'] == 1 ? 'active show' : '' ); ?>" id="guest" data-step="1">
								<?php
									$checkout_guests = __DIR__ . '/checkout-guests.php';

									if ( is_readable( $checkout_guests ) ) {
										wc_get_template('woocommerce/checkout/checkout-guests.php');
									} else {
										?>
											<h3><?php esc_html_e( 'Step 1', 'trek-travel-theme' ); ?></h3>
											<p><?php esc_html_e( 'Checkout guest form code is missing!', 'trek-travel-theme' ); ?></p>
										<?php
									}
									?>
							</div>

							<div class="tab-pane <?php echo esc_attr( isset( $_GET['step'] ) && $_GET['step'] == 2 ? 'active show' : '' ); ?>" id="rooms" data-step="2">
								<div id="tt-hotel-occupant-inner-html">
									<?php
										$checkout_hotel = __DIR__ . '/checkout-hotel.php';

										if( is_readable( $checkout_hotel ) ) {
											wc_get_template('woocommerce/checkout/checkout-hotel.php');
										} else {
											?>
												<h3><?php esc_html_e( 'Step 2', 'trek-travel-theme' ); ?></h3>
												<p><?php esc_html_e( 'Checkout Hotel form code is missing!', 'trek-travel-theme' ); ?></p>
											<?php
										}
										?>
								</div>
							</div>
							<?php if( ! $is_hiking_checkout ) : ?>
								<div class="tab-pane <?php echo esc_attr( isset( $_GET['step'] ) && $_GET['step'] == 3 ? 'active show' : '' ); ?>" id="trip-payment" data-step="3">
									<div id="tt-bikes-selection-inner-html">
										<?php
											$checkout_bikes = __DIR__ . '/checkout-bikes.php';

											if( is_readable( $checkout_bikes ) ) {
												wc_get_template('woocommerce/checkout/checkout-bikes.php');
											} else {
												?>
													<h3><?php esc_html_e( 'Step 2', 'trek-travel-theme' ); ?></h3>
													<p><?php esc_html_e( 'Checkout Bike form code is missing!', 'trek-travel-theme' ); ?></p>
												<?php
											}
											?>
									</div>
								</div>
							<?php endif; ?>
							<div class="tab-pane <?php echo esc_attr( isset( $_GET['step'] ) && ( ( $_GET['step'] == 4 && ! $is_hiking_checkout ) || ( $_GET['step'] == 3 && $is_hiking_checkout ) ) ? 'active show' : '' ); ?>" id="review" data-step="<?php echo esc_attr( $is_hiking_checkout ? 3 : 4 ) ?>">
								<div id="tt-checkout-reviews-inner-html">
									<?php
										$checkout_review = __DIR__ . '/checkout-reviews.php';
										if( is_readable( $checkout_review ) ) {
											wc_get_template('woocommerce/checkout/checkout-reviews.php');
										} else {
											?>
												<h3><?php esc_html_e( 'Step 4', 'trek-travel-theme' ); ?></h3>
												<p><?php esc_html_e( 'Checkout review form code is missing!', 'trek-travel-theme' ); ?></p>
											<?php
										}
										?>
								</div>
								<?php

									$checkout_payment = __DIR__ . '/checkout-payment.php';

									if( is_readable( $checkout_payment ) ) {
										wc_get_template('woocommerce/checkout/checkout-payment.php');
									} else {
										?>
											<h3><?php esc_html_e( 'Step 4', 'trek-travel-theme' ); ?></h3>
											<p><?php esc_html_e( 'Checkout Payment form code is missing!', 'trek-travel-theme' ); ?></p>
										<?php
									}
									?>
							</div>
						</div>
					</div>
				</div>

				<div class="checkout-trek-summary">
					<div id="tt-review-order">
						<?php do_action('woocommerce_checkout_order_review');?>
					</div>
				</div>

			</div>

			<?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>

			<!-- Begin: Travel Protection modal form  -->
			<div class="modal fade modal-search-filter" id="protection_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">

						<div class="modal-header">
							<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
								<i type="button" class="bi bi-x"></i>
							</span>
						</div>

						<div class="modal-body">
							<h4 class="fw-semibold modal-body__title"><?php esc_html_e( 'Travel Protection', 'trek-travel-theme' ); ?></h4>
							<p class="fs-lg lh-lg fw-bold mb-4"><?php esc_html_e( 'Please tell us who will be covered in this booking', 'trek-travel-theme' ); ?></p>
							<p class="modal-body__sub">
								<?php
									printf(
										wp_kses(
											/* translators: %1$s: Travel Protection page URL; %2$s: phone number */
											__( 'We can also insure additional trip costs, such as flights and non-refundable hotels. You can learn more on our travel protection page <a href="%1$s" target="_blank">here</a> or call us at <a href="%2$s">866-464-8735</a>.', 'trek-travel-theme' ),
											array(
												'a' => array(
													'class'  => array(),
													'href'   => array(),
													'target' => array()
												)
											)
										),
										esc_url( home_url( '/travel-protection/' ) ),
										esc_attr( 'tel:866-464-8735' )
									);
									?>
							</p>
							<p class="modal-body__sub">
								<?php
									printf(
										wp_kses(
											/* translators: %1$s: Travel Protection page URL; */
											__( 'To review full plan details online, please visit: <a href="%1$s" target="_blank">here</a>', 'trek-travel-theme' ),
											array(
												'a' => array(
													'class'  => array(),
													'href'   => array(),
													'target' => array()
												)
											)
										),
										esc_url( home_url( '/travel-protection/' ) ),
									);
									?>
							</p>
							<hr>
							<div id="tt-popup-insured-form">
								<?php
									$checkout_insured_users_template = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php';
									if( is_readable( $checkout_insured_users_template ) ) {
										wc_get_template( 'woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php' );
									} else {
										?>
											<h3><?php esc_html_e( 'Step 4', 'trek-travel-theme' ); ?></h3>
											<p><?php esc_html_e( 'checkout-insured-guests-popup.php template is missing!', 'trek-travel-theme' ); ?></p>
										<?php
									}
									?>
							</div>
							<div class="modal-body__footer d-lg-flex align-items-center">
								<button type="submit" class="btn btn-primary submit_protection" data-bs-dismiss="modal"><?php esc_html_e( 'Submit', 'trek-travel-theme' ); ?></button>
								<div><span type="button" class="btn-close cancel-submit-protection" data-tp-dismiss="true" data-bs-dismiss="modal" aria-label="Close"><?php esc_html_e( 'Cancel', 'trek-travel-theme' ); ?></span></div>
							</div>
						</div>
					</div><!-- / .modal-content -->
				</div><!-- / .modal-dialog -->
			</div><!-- / .modal -->
			<!-- End: Travel Protection modal form -->

			<input type="hidden" name="step" value="<?php echo esc_attr( $current_step ); ?>">
			<input type="hidden" name="is_hiking_checkout" value="<?php echo esc_attr( $is_hiking_checkout ); ?>">
		</div>
	</form>
</div>
<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

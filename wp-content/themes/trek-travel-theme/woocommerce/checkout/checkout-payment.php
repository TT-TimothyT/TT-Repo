<?php
/**
 * Template file for the payments, step 4.
 *
 * Travel protection section, Payment Options, Payment Methods, Billing Address and Waiver agreement.
 *
 * @param array $args['tt_posted'] The posted guest data.
 */

$trek_user_checkout_data    = get_trek_user_checkout_data();
$tt_posted                  = $trek_user_checkout_data['posted'];
$no_of_guests               = tt_validate( $tt_posted['no_of_guests'], 1 );
$guest_insurance_html       = tt_guest_insurance_output( $tt_posted, '<div id="travel-protection-div">', '</div>');
$checkout_waiver_heading    = get_field( 'checkout_waiver_heading', 'option' );
$checkout_waiver_copy       = get_field( 'checkout_waiver_copy', 'option' );
$trip_booking_limit         = get_trip_capacity_info();
$is_protection_modal_showed = (bool) tt_validate( $tt_posted['is_protection_modal_showed'], false );
?>
<div class="checkout-payment" id="checkout-payment">
	<div class="checkout-payment__add-travel <?php if( $is_protection_modal_showed ) echo 'd-none'; ?>">
		<h5 class="fs-xl lh-xl fw-medium d-flex align-items-center checkout-payment__title mb-3 title-poppins"><?php esc_html_e( 'Interested in Travel Protection?', 'trek-travel-theme' ); ?> <i class="bi bi-info-circle checkout-travel-protection-tooltip"></i></h5>
		<p class="fs-sm checkout-payment__sublabel mb-0"><?php esc_html_e( 'In order to help protect you, your traveling party, and your trip investment, we recommend that you add travel protection to your reservation. For your convenience, Trek Travel offers this protection with a wide range of benefits through Arch RoamRight comprehensive line of insurance programs.', 'trek-travel-theme' ); ?></p>
		<div class="checkout-payment__checkbox d-flex align-items-lg-center">
			<input id="protection_modal_checkbox" type="checkbox" class="protection_modal protection_modal_ev">
			<label for="protection_modal_checkbox"><?php esc_html_e( "I am interested in Trek Travel's Travel Protection Plan", 'trek-travel-theme' ); ?></label>
		</div>
	</div>
	<div class="checkout-payment__added-travel <?php if( ! $is_protection_modal_showed ) echo 'd-none'; ?>">
		<div class="d-flex travel">
			<h5 class="fs-xl lh-xl fw-medium d-flex align-items-center checkout-payment__title mb-3"><?php esc_html_e( 'Travel Protection Information', 'trek-travel-theme' ); ?></h5>
			<button type="button" class="btn btn-md btn-outline-primary d-lg-block d-none edit-info" data-bs-toggle="modal" data-bs-target="#protection_modal">
				<?php esc_html_e( 'Edit Info', 'trek-travel-theme' ); ?>
			</button>
		</div>
		<?php echo $guest_insurance_html; ?>
		<button type="button" class="btn btn-md btn-outline-primary d-lg-none d-block" data-bs-toggle="modal" data-bs-target="#protection_modal">
			<?php esc_html_e( 'Edit Info', 'trek-travel-theme' ); ?>
		</button>
	</div>
	<hr>
	<div class="checkout-payment__options">
		<?php
			$checkout_payment_options_template = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php';

			if( is_readable( $checkout_payment_options_template ) ) {
				wc_get_template( 'woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php', $template_args );
			} else {
				?>
					<h3><?php esc_html_e( 'Step 4', 'trek-travel-theme' ); ?></h3>
					<p><?php esc_html_e( 'Checkout Payment Options form code is missing!', 'trek-travel-theme' ); ?></p>
				<?php
			}
			?>
	</div>
	<hr>
	<?php
	// Check for plugin Woo Gift Cards is active.
	if ( is_plugin_active( 'woocommerce-gift-cards/woocommerce-gift-cards.php' ) ) : 
		?>
		<div class="checkout-payment__reward">
			<div class="woocommerce-form-coupon-toggle fw-medium checkout-payment__reward-title">
				<?php wc_print_notice(apply_filters('woocommerce_checkout_coupon_message', ' <a href="#" class="showcoupon">' . esc_html__('Redeem a Gift Card', 'woocommerce') . '</a>'), 'notice'); ?>
			</div>

			<div class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none">
				<div class="row mx-0 guest-checkout__primary-form-row mt-3">
					<div class="col-md-7 px-0">
						<div class="form-floating">
							<input type="text" name="gift_card_code" class="input-text form-control" placeholder="<?php esc_attr_e('Gift Card Number', 'woocommerce'); ?>" id="gift_card_code floatingInputGrid" value="" required />
							<label for="floatingInputGrid"><?php esc_html_e('Gift Card Number', 'woocommerce'); ?></label>
							<div class="invalid-feedback">
								<img class="invalid-icon" />
								<?php esc_html_e( 'This field is required.', 'trek-travel-theme' ); ?>
							</div>
						</div>
					</div>
					<div class="col-md-2 px-0">
						<div class="form-floating">
							<input type="text" class="form-control" id="floatingInputGrid" name="pin" placeholder="<?php esc_html_e( 'Pin', 'trek-travel-theme' ); ?>" value="">
							<label for="floatingInputGrid"><?php esc_html_e( 'Pin', 'trek-travel-theme' ); ?></label>
						</div>
					</div>
					<div class="col-md-2 px-0">
						<button type="submit" class="btn btn-lg btn-primary coupon_button w-100" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_html_e('Submit', 'woocommerce'); ?></button>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<hr>
	<?php endif; ?>
	<div class="checkout-payment__method">
		<h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-4"><?php esc_html_e( 'Payment Method', 'trek-travel-theme' ); ?></h5>
		<div class="checkout-payment__paymethod">
			<?php
			$available_gateways  = WC()->payment_gateways->get_available_payment_gateways();
			if( WC()->cart->needs_payment() ) : ?>
				<ul class="wc_payment_methods payment_methods methods">
					<?php
					if( ! empty( $available_gateways ) ) {
						foreach( $available_gateways as $gateway ) {
							wc_get_template( 'checkout/payment-method.php', array('gateway' => $gateway ) );
						}
					} else {
						echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters('woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce') : esc_html__('Please fill in your details above to see available payment methods.', 'woocommerce')) . '</li>'; // @codingStandardsIgnoreLine
					}
					?>
				</ul>
			<?php endif; ?>
		</div>
		<input name="wc-cybersource-credit-card-tokenize-payment-method" type="hidden" value="true">
	</div>
	<hr>
	<div class="checkout-payment__billing">
		<?php
			$checkout_billing_address_template = TREK_PATH . '/woocommerce/checkout/checkout-billing-address.php';

			if( is_readable( $checkout_billing_address_template ) ) {
				wc_get_template( 'woocommerce/checkout/checkout-billing-address.php', array( 'tt_posted' => $tt_posted ) );
			} else {
				?>
					<h3><?php esc_html_e( 'Step 4', 'trek-travel-theme' ); ?></h3>
					<p><?php esc_html_e( 'Checkout Billing Adress form code is missing!', 'trek-travel-theme' ); ?></p>
				<?php
			}
			?>
	</div>
	<hr>
	<div class="checkout-payment__release">
		<h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-4"><?php esc_html_e( 'Release of Liability and Assumption of All Risks', 'trek-travel-theme' ); ?></h5>
		<p class="mb-0 fs-md lh-sm checkout-payment__gray"><?php esc_html_e( 'Please scroll through release form below and check "I Agree" once finished.', 'trek-travel-theme' ); ?></p>
		<?php if( ! empty( $checkout_waiver_copy ) || ! empty( $checkout_waiver_heading ) ) : ?>
			<div class="checkout-payment__iframe rounded-1">
				<?php if ( ! empty( $checkout_waiver_heading ) ) : ?>
					<h5 class="fs-xl lh-lg fw-medium mb-2"><?php echo wp_kses_post( $checkout_waiver_heading ); ?></h5>
				<?php endif; ?>
				<?php if ( ! empty( $checkout_waiver_copy ) ) : ?>
					<?php $checkout_waiver_copy = str_replace( array( '<p>', '</p>' ), '', $checkout_waiver_copy ); ?>
					<p class="fs-sm lh-sm"><?php echo wp_kses_post( $checkout_waiver_copy ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="d-flex checkout-payment__billing-checkboxtwo">
			<input id="tt_waiver_checkbox" class="tt_waiver_check" type="checkbox" name="tt_waiver" value="1" <?php if (isset($tt_posted['tt_waiver']) && $tt_posted['tt_waiver'] == 1) echo 'checked'; ?> required="required">
			<label for="tt_waiver_checkbox"><?php esc_html_e( 'By checking “I Agree” I acknowledge that I have read, understand and agree to this Release Form and Cancellation Policy.', 'trek-travel-theme' ); ?></label>
		</div>
		<div class="invalid-feedback tt_waiver_required">
			<img class="invalid-icon" />
			<?php esc_html_e( 'This field is required.', 'trek-travel-theme' ); ?>
		</div>
	</div>
	<hr>
	<div class="checkout-payment__release">
		<h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-4"><?php esc_html_e( 'Cancellation Policy & Pay ', 'trek-travel-theme' ); ?></h5>
	</div>
	<div class="d-flex checkout-timeline__info rounded-1 mb-20">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-info.svg' ); ?>">
		<p class="mb-0 fs-sm lh-sm"><?php echo esc_html( 'All payment amounts are processed in $USD. Please check your exchange rates if booking outside of the U.S.' ); ?></p>
	</div>
	<hr>
	<div class="checkout-payment__button mb-5">
		<div class="checkout-summary__total">
			<div class="col-md px-0 d-flex align-items-center guest-checkout__checkbox-gap mb-5 d-none">
				<input id="tt-terms-checkbox" type="checkbox" class="guest-checkout__checkbox" value="1" required="required" name="tt_terms"<?php echo esc_attr( 1 == $tt_terms ? ' ' . 'checked' : '' ); ?>>
				<label for="tt-terms-checkbox" class="w-75">
					<?php 
					echo 'I have read and agree to Trek Travel’s <a href="' . esc_url( home_url( '/privacy-policy' ) ) . '" target="_blank">Terms and Conditions</a> and <a href="' . esc_url( TT_CAN_POLICY_PAGE ) . '" target="_blank">Cancellation Policy</a>';
					?>
				</label>
			</div>
			<?php if ( $trip_booking_limit['remaining']  >= $no_of_guests ) : ?>
				<button class="btn btn-lg btn-primary rounded-1 w-100 mt-1 mb-3 checkout-summary__button d-none"<?php echo esc_attr( 1 != $tt_terms ? ' ' . 'disabled': '' ); ?>><?php esc_html_e( 'Pay now', 'trek-travel-theme' ); ?></button>
			<?php endif; ?>
		</div>
	</div>
</div>

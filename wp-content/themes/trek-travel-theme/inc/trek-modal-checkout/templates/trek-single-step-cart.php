<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

$checkout = WC()->checkout();

?>
<form name="checkout" method="post" class="checkout tt-modal-checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
	<div class="container">
		<div class="protection-ctr">
			<h4 class="fw-semibold modal-body__title"><?php esc_html_e( 'Travel Protection', 'trek-travel-theme' ); ?></h4>
			<p class="fs-lg lh-lg fw-bold mb-4 info-text"><?php esc_html_e( 'Please tell us who will be covered in this booking', 'trek-travel-theme' ); ?></p>
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
			<div id="tt-popup-insured-form"></div>
		</div>
		<div class="payment-ctr">
			<div class="checkout-payment__method">
				<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
				<!-- Ins Payment Methood Load -->
				<?PHP
				if ( ! wp_doing_ajax() ) {
					do_action( 'woocommerce_review_order_before_payment' );
				}
				?>
				<h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-4"><?php esc_html_e( 'Payment Method', 'trek-travel-theme' ); ?></h5>
				<div class="checkout-payment checkout-payment__paymethod">
					<?php
					// if( WC()->cart->needs_payment() ) : 
						$available_gateways  = WC()->payment_gateways->get_available_payment_gateways();
						WC()->payment_gateways()->set_current_gateway( $available_gateways );
					?>
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
					<?php // endif; ?>
				</div>
				<input name="wc-cybersource-credit-card-tokenize-payment-method" type="hidden" value="true">

				<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
			</div>
			<div class="checkout-payment__billing">
				<div class="billing-section-title flex-wrap">
					<h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-5"><?php esc_html_e( 'Billing Address', 'trek-travel-theme' ); ?></h5>
					<div class="d-flex align-items-center checkout-payment__billing-checkbox mb-0">
						<input id="pre_billing_address" type="checkbox" class="pre-billing-checkbox" name="pre_billing_address" value="1" <?php checked( ! tt_user_has_valid_billing_address() ); ?>>
						<label for="pre_billing_address"><?php esc_html_e( 'Use a different address', 'trek-travel-theme' ); ?></label>
					</div>
				</div>
				<div class="billing-address-section">
					<?php
						$checkout_billing_address_template = TREK_PATH . '/inc/trek-modal-checkout/templates/billing-address-form.php';

						if ( is_readable( $checkout_billing_address_template ) ) {
							wc_get_template( 'inc/trek-modal-checkout/templates/billing-address-form.php', array( 'tt_posted' => array(), 'is_pre_billing_address' => tt_user_has_valid_billing_address() ) );
						} else {
							?>
								<h3><?php esc_html_e( 'Step 4', 'trek-travel-theme' ); ?></h3>
								<p><?php esc_html_e( 'Checkout Billing Adress form code is missing!', 'trek-travel-theme' ); ?></p>
							<?php
						}
						?>
					</div>
			</div>
			<div class="d-flex checkout-timeline__info rounded-1 mb-20">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-info.svg' ); ?>">
				<p class="mb-0 fs-sm lh-sm"><?php echo esc_html( 'All payment amounts are processed in $USD. Please check your exchange rates if booking outside of the U.S.' ); ?></p>
			</div>
			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
			<div class="checkout-payment__button">
				<div class="modal-checkout__total-price">
					<span class="total-price-label"><?php esc_html_e( 'Total — ', 'trek-travel-theme' ); ?></span>
					<span id="total-price" class="cart-total-price"><?php echo WC()->cart->get_cart_total(); ?></span>
				</div>
				<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '" name="woocommerce_checkout_pay_now" id="pay_now" value="' . esc_attr( 'Pay Now' ) . '" data-value="' . esc_attr( 'Pay Now' ) . '">' . esc_html( __( 'Pay Now', 'trek-travel-theme' ) ) . '</button>' ); // @codingStandardsIgnoreLine  ?>
			</div>
			<?php
				if ( ! wp_doing_ajax() ) {
					do_action( 'woocommerce_review_order_after_payment' );
				}
				do_action( 'woocommerce_review_order_after_submit' );
			?>
			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		</div>
	</div>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
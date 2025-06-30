<?php
/**
 * Template file for the payment option section.
 */

// Collect the necessary data.
$cart_total = WC()->cart->total;

?>
<h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-3"><?php esc_html_e( 'Payment Option', 'trek-travel-theme' ); ?></h5>
<div class="checkout-payment__pay"> 
	<!-- Pay Full Amount Option -->
	<div>
		<input type="radio" name="pay_amount_checkout_modal" required="required" value="full" checked>
		<div class="checkout-payment__paydep rounded-1 d-flex justify-content-between align-items-center">
			<p class="fs-lg lh-lg fw-medium mb-0"><?php esc_html_e( 'Pay Full Amount:', 'trek-travel-theme' ); ?>
				<span>
					<span class="amount">
						<span class="woocommerce-Price-currencySymbol"></span>
						<?php echo esc_attr( $cart_total ); ?>
					</span>
				</span>
			</p>
			<i class="checkout-payment__pay-icon"></i>
		</div>
	</div>
</div>

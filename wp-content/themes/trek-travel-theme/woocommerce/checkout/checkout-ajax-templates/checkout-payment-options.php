<?php
/**
 * Template file for the payment option section.
 */

// Collect the necessary data.
$trek_user_checkout_data = get_trek_user_checkout_data();
$tt_posted               = $trek_user_checkout_data['posted'];
$trip_info               = tt_get_trip_pid_sku_from_cart();
$no_of_guests            = tt_validate( $tt_posted['no_of_guests'], 1 );
$guest_insurance         = tt_validate( $tt_posted['trek_guest_insurance'], array() );
$insurance_amount        = tt_get_full_insurance_amount( $guest_insurance );
$deposit_info            = tt_get_deposit_info( $trip_info['sku'], $no_of_guests, $insurance_amount );
$deposit_amount          = tt_validate( $deposit_info['deposit_amount'], 0 );
$deposit_allowed         = $deposit_info['deposit_allowed'];
$pay_amount_default_mode = $deposit_allowed ? 'deposite' : 'full';
$pay_amount              = isset( $tt_posted['pay_amount'] ) ? $tt_posted['pay_amount'] : $pay_amount_default_mode;
$cart_total_full_amount  = tt_validate( $tt_posted['cart_total_full_amount'] );
$cart_total              = 'deposite' === $pay_amount && ! empty( $cart_total_full_amount ) ? $cart_total_full_amount : WC()->cart->total;
$remaining_amount        = 'full' === $pay_amount ? 0 : $cart_total - $deposit_amount;

?>

<h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-3"><?php esc_html_e( 'Payment Option', 'trek-travel-theme' ); ?></h5>
<p class="fs-md mb-5 checkout-payment__sublabel">
	<?php
		printf(
			wp_kses(
				/* translators: %1$s: Cancellation Policy page URL; */
				__( 'Minimum amount required is trip deposit. <a href="%1$s" target="_blank">Learn more about our cancellation policy.</a>', 'trek-travel-theme' ),
				array(
					'a' => array(
						'class'  => array(),
						'href'   => array(),
						'target' => array()
					)
				)
			),
			esc_url( home_url( '/cancellation-policy/' ) ),
		);
		?>
</p>
<div class="checkout-payment__pay"> 
	<?php if( $deposit_allowed ) : ?>
		<!-- Pay Deposit Option -->
		<div class="mb-4">
			<input type="radio" name="pay_amount" required="required" value="deposite"<?php echo esc_attr( 'deposite' === $pay_amount ?  ' ' . 'checked' : '' ); ?>>
			<div class="checkout-payment__paydep rounded-1 d-flex justify-content-between align-items-center">
				<p class="fs-xl lh-lg fw-normal mb-0"><?php esc_html_e( 'Pay Deposit:', 'trek-travel-theme' ); ?>
					<span>
						<span class="amount fw-medium">
							<span class="woocommerce-Price-currencySymbol"></span>
							<?php echo esc_attr( $deposit_amount ); ?>
						</span>
					</span>
				</p>
				<i class="checkout-payment__pay-icon"></i>
			</div>
		</div>
	<?php endif; ?>
	<!-- Pay Full Amount Option -->
	<div>
		<input type="radio" name="pay_amount" required="required" value="full"<?php echo esc_attr( 'full' === $pay_amount ? ' ' . 'checked' : '' ); ?>>
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
<p class="mb-2"><?php esc_html_e( 'Remaining Amount Owed:', 'trek-travel-theme' ); ?>
	<span class="fw-medium">
		<span class="amount">
			<span class="woocommerce-Price-currencySymbol"></span>
			<?php echo esc_attr( $remaining_amount ); ?>
		</span>
	</span>
</p>
<?php if ( 'full' !== $pay_amount ) : ?>
	<p class="fs-sm lh-sm checkout-payment__gray"><?php esc_html_e( 'Our team will reach out to collect final payment prior to your trip start date.', 'trek-travel-theme' ); ?></p>
<?php endif; ?>

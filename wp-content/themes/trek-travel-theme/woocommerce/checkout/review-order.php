<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;

$trek_user_checkout_data = get_trek_user_checkout_data();
$tt_posted               = $trek_user_checkout_data['posted'];
$tt_coupon_code          = tt_validate( $tt_posted['coupon_code'] );
// Check if the cart contains an already applied coupon and fix the missing coupon code.
if( WC()->cart->get_applied_coupons() && isset( $tt_coupon_code ) && empty( $tt_coupon_code ) ) {
    $applied_coupons = WC()->cart->get_applied_coupons();
    $tt_coupon_code = $applied_coupons[0];
}
$applied_coupon          = false;
if ( ! empty( $tt_coupon_code ) && tt_is_coupon_applied( $tt_coupon_code ) ) {
    $applied_coupon = true;
}
$product_id              = tt_validate( $tt_posted['product_id'] );
$no_of_guests            = tt_validate( $tt_posted['no_of_guests'], 1 );
$accepted_p_ids          = tt_get_line_items_product_ids();
$guest_insurance         = tt_validate( $tt_posted['trek_guest_insurance'], array() );
$trip_info               = tt_get_trip_pid_sku_from_cart();
$parent_id = tt_get_parent_trip_id_by_child_sku( $trip_info['sku'] );
$trip_start_date         = tt_get_local_trips_detail( 'startDate', '', $trip_info['sku'], true ); // The value or empty string.
$trip_end_date           = tt_get_local_trips_detail( 'endDate', '', $trip_info['sku'], true ); // The value or empty string.
$trip_product_line_name  = tt_is_product_line( 'Hiking', $trip_info['sku'] ) ? 'Hiking' : 'Cycling';
$insurance_amount        = tt_get_full_insurance_amount( $guest_insurance );
$deposit_info            = tt_get_deposit_info( $trip_info['sku'], $no_of_guests, $insurance_amount );
$deposit_amount          = tt_validate( $deposit_info['deposit_amount'], 0 );
$deposit_allowed         = $deposit_info['deposit_allowed'];
$pay_amount_default_mode = $deposit_allowed ? 'deposite' : 'full';
$pay_amount              = tt_validate( $tt_posted['pay_amount'], $pay_amount_default_mode );
$cart_total_full_amount  = tt_validate( $tt_posted['cart_total_full_amount'] );
$cart_total              = 'deposite' === $pay_amount && ! empty( $cart_total_full_amount ) ? $cart_total_full_amount : WC()->cart->total;
$cart_total_curr         = get_woocommerce_currency_symbol() . $cart_total;
$trip_booking_limit      = get_trip_capacity_info();
$arch_info               = tt_get_insurance_info( $tt_posted );
$insured_person          = tt_validate( $arch_info['count'], 0 );
$tt_terms                = tt_validate( $tt_posted['tt_terms'], 0 );
$outstanding_payment     = $cart_total_curr;
$remaining_amount_curr   = get_woocommerce_currency_symbol() . '0';
if ( 'deposite' === $pay_amount ) {
    $outstanding_payment   = floatval( $deposit_amount );
    $remaining_amount_curr = get_woocommerce_currency_symbol() . ( $cart_total - $deposit_amount );
}
$product_name = '';
foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    if (
        $_product && $_product->exists() && $cart_item['quantity'] > 0 && !in_array($cart_item['product_id'], $accepted_p_ids)
        && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)
    ) {
        $product_name = $_product->get_name();
    }
}

?>
<div class="checkout-summary__mobile d-block d-lg-none text-center position-sticky" id="checkout-summary-mobile">
    <div class="closed">
		<svg width="20" height="11" viewBox="0 0 20 11" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M1.23282 8.61235L9.13938 1.12193C9.39947 0.913863 9.71157 0.757812 9.97165 0.757812C10.2838 0.757812 10.5959 0.913863 10.8559 1.12193L18.7625 8.61235C19.2827 9.08051 19.2827 9.86076 18.8145 10.3809C18.3464 10.9011 17.5661 10.9011 17.0459 10.4329L9.97165 3.77479L2.94938 10.4329C2.42921 10.9011 1.64896 10.9011 1.18081 10.3809C0.712654 9.86076 0.712654 9.08051 1.23282 8.61235Z" fill="currentColor"/>
		</svg>

        <p class="mb-0"><?php echo esc_html( $trip_product_line_name ); ?> - <?php esc_html_e( 'Trip Summary', 'trek-travel-theme' ); ?></p>
        <p class="mb-0 fs-md lh-md fw-bold"><?php esc_html_e( 'Total:', 'trek-travel-theme' ); ?> <?php wc_cart_totals_subtotal_html(); ?></p>
    </div>
    <div class="open">
        <p class="mb-4"><?php esc_html_e( 'Trip Summary', 'trek-travel-theme' ); ?></p>
		<svg width="20" height="11" viewBox="0 0 20 11" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M18.7672 2.92671L10.8606 10.4171C10.6005 10.6252 10.2884 10.7812 10.0283 10.7812C9.71625 10.7812 9.40415 10.6252 9.14406 10.4171L1.2375 2.92671C0.717335 2.45856 0.717335 1.6783 1.18549 1.15814C1.65364 0.637967 2.43389 0.637967 2.95406 1.10612L10.0283 7.76427L17.0506 1.10612C17.5708 0.637967 18.351 0.637967 18.8192 1.15814C19.2873 1.6783 19.2873 2.45856 18.7672 2.92671Z" fill="currentColor"/>
		</svg>

    </div>
</div>
<div class="checkout-summary d-none d-lg-block" id="checkout-summary">
    <div class="card checkout-summary__card">
		<div class="checkout-summary__card-header">
        	<p class="text-center trip-product-line"><?php echo esc_html( $trip_product_line_name ); ?></p>
			<div class="image-frame">
				<img src="<?php echo esc_url( $trip_info['parent_trip_image'] ); ?>">
			</div>
		</div>
		<div class="checkout-summary__card-body">
			<div class="checkout-summary__card-body-header">
				<h5 class="text-center checkout-summary__title mb-1"><?php echo esc_html( get_the_title($parent_id) ); ?></h5>
				<p class="text-center checkout-summary__date trip-duration mb-0"><?php printf( '%1$s - %2$s', esc_attr( $trip_start_date ), esc_attr( $trip_end_date ) ) ?></p>
			</div>
			<hr>
			<div class="checkout-summary__promo">
				<span class="promo"><?php esc_html_e( 'Promo Code', 'trek-travel-theme' ); ?></span>
				<div class="mt-5 d-flex justify-content-start align-items-start promo-form checkout-summary__promo<?php echo esc_attr( $applied_coupon ? ' ' . 'd-none' : ' ' . 'd-flex' ); ?>">
					<div class="form-group form-floating mb-0 coupon-field">
						<input type="text" class="input-text form-control rounded-1 promo-input coupon-code-input h-100" name="coupon_code" placeholder="<?php esc_html_e( 'Enter promo code', 'trek-travel-theme' ); ?>" value="<?php echo $tt_coupon_code; ?>" required>
						<label><?php esc_html_e( 'Enter promo code', 'trek-travel-theme' ); ?></label>
						<div class="invalid-feedback invalid-code" style="<?php echo esc_attr( true !== $applied_coupon && ! empty( $tt_coupon_code ) ? 'display:block' : 'display:none' ); ?>">
							<img class="invalid-icon" />
							This code is no longer valid. <br> Need help with your promo code? <br> Call us at <a href="tel:8664648735">(866) 464-8735</a>
						</div>
					</div>
					<button type="button" id="promo-checkout" class="btn btn-primary rounded-1 checkout-summary__submit tt_apply_coupan promo-code-submit" data-action="add" name="Submit" value="Submit"><?php esc_html_e( 'Submit', 'trek-travel-theme' ); ?></button>
				</div>
				<div class="checkout-summary__applied d-flex mt-4<?php echo esc_attr( $applied_coupon ? ' ' . 'd-flex' : ' ' . 'd-none' ); ?>">
					<p class="fs-md lh-md mb-0"><?php esc_html_e( 'Promo', 'trek-travel-theme' ); ?> <span class="fw-bold" id="tt-applied-code"><?php echo $tt_coupon_code; ?></span> <?php esc_html_e( 'Applied', 'trek-travel-theme' ); ?></p>
					<a href="javascript:" class="tt_remove_coupan" data-action="remove"><?php esc_html_e( 'Remove', 'trek-travel-theme' ); ?></a>
				</div>
			</div>

			<hr>
			<div class="checkout-summary__wrap">
				<!-- Products line items -->
				<div class="checkout-summary__price">
					<p class="fs-xl fw-medium mt-0 checkout-summary__price-title"><?php esc_html_e( 'Summary', 'trek-travel-theme' ); ?></p>
					<div class="d-flex justify-content-between checkout-summary__table-row">
						<div class="text-start">
							<p class="mb-0">
								<?php esc_html_e( 'Guests', 'trek-travel-theme' ); ?>
							</p>
						</div>
						<div class="text-end">
							<p class="mb-0 fw-bold fs-lg">
								<?php echo $no_of_guests; ?>
							</p>
						</div>
					</div>
					<?php
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
							$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
								$product_name = $_product->get_name();
								$sku          = $_product->get_sku();
								$product_qty  = $cart_item['quantity'];
								if ( ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
									$product_qty = $no_of_guests;
								}
								if ( 'TTWP23FEES' === $sku ) {
									$product_qty    = $insured_person;
									$cart_sub_total = WC()->cart->get_product_subtotal( $_product, 1 );
								} else {
									$cart_sub_total = WC()->cart->get_product_subtotal( $_product, $product_qty );
								}
								$cart_item = wc_get_formatted_cart_item_data( $cart_item );
								?>
									<div class="d-flex justify-content-between checkout-summary__table-row">
										<div class="text-start">
											<p class="col-md  px-0 py-0 my-0 form-row">
												<?php echo esc_html( $product_name ); ?>
												<span class="fs-sm lh-sm checkout-summary__guest d-none">x<?php echo esc_attr( $product_qty ); ?>&nbsp;<?php esc_html_e( 'guest', 'trek-travel-theme' ); ?></span>
											</p>
										</div>
										<div class="text-end">
											<p class="col-md px-0 py-0 my-0 form-row fw-bold fs-lg">
												<?php echo $cart_sub_total; ?>
											</p>
										</div>
									</div>
								<?php
							}
						}
						?>
				</div>

				<!-- End product line items -->
				<div class="checkout-summary__total">
					<div class="d-flex justify-content-between checkout-summary__table-row">
						<div class="text-start">
							<p class="mb-0"><?php esc_html_e( 'Subtotal', 'trek-travel-theme' ); ?></p>
						</div>
						<div class="text-end">
							<p class="mb-0 fw-bold fs-lg"><?php wc_cart_totals_subtotal_html(); ?></p>
						</div>
					</div>

					<div class="d-flex justify-content-between checkout-summary__table-row">
						<div class="text-start">
							<p class="mb-0"><?php esc_html_e( 'Local Taxes', 'trek-travel-theme' ); ?></p>
							<p class="fs-xs lh-xs checkout-summary__small"><?php esc_html_e( 'Taxes are based on your trip destination', 'trek-travel-theme' ); ?></p>
						</div>
						<div class="text-end ">
							<p class="fw-bold fs-lg"><?php do_action( 'woocommerce_review_order_before_shipping' ); ?></p>
						</div>
					</div>
					
					<!-- Begin: Coupan code Dynamic logic -->
					<?php
					if ( WC()->cart->get_coupons() ) {
						?>
						<div class="checkout-summary__coupans">
							<?php
								foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
									if ( is_string( $coupon ) ) {
										$coupon = new WC_Coupon( $coupon );
									}
									$amount          = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax );
									$discount_amount = floatval( $no_of_guests ) * floatval( $amount );
									?>
									<div class="d-flex justify-content-between checkout-summary__table-row">
										<div class="text-start">
											<p class="mb-5" data-coupan="<?php echo esc_attr( sanitize_title( $code ) ); ?>"><?php esc_html_e( 'Discount', 'trek-travel-theme' ); ?></p>
										</div>
										<div class="text-end">
											<p class="fw-bold"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo esc_attr( $discount_amount );?></span></p>
										</div>
									</div>
									<?php
								}
								?>
						</div>
						<?php
					}

					if ( WC()->cart->get_fees() ) {
						foreach ( WC()->cart->get_fees() as $fee ) {
							$cart_totals_fee = WC()->cart->display_prices_including_tax() ? wc_price( $fee->total + $fee->tax ) : wc_price( $fee->total );
							if( !empty( esc_html( $fee->name ) ) ) {
							?>
								<div class="d-flex justify-content-between checkout-summary__fees">
									<div class="text-start">
										<?php echo esc_html( $fee->name ) ?>
										<p class="mb-5"></p>
									</div>
									<div class="text-end">
										<p class="fw-bold"><?php echo $cart_totals_fee; ?></p>
									</div>
								</div>
							<?php
							}
						}
					}

					if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
						?>
							<div class="checkout-summary__fees">
							<?php
								if ( 'itemized' === get_option('woocommerce_tax_total_display') ) {
									foreach ( WC()->cart->get_tax_totals() as $code => $tax ) {
										?>
										<div class="d-flex justify-content-between  checkout-summary__table-row">
											<div class="text-start">
												<p class="mb-0"><?php echo esc_html( $tax->label ); ?></p>
											</div>
											<div class="text-end">
												<p class="fw-bold fs-lg"><?php echo wp_kses_post( $tax->formatted_amount ); ?></p>
											</div>
										</div>
										<?php
									}
								} else {
									?>
									<div class="d-flex justify-content-between checkout-summary__table-row">
										<div class="text-start">
											<p class="mb-5"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></p>
										</div>
										<div class="text-end">
											<p class="fw-bold"><?php wc_price( WC()->cart->get_taxes_total() ); ?></p>
										</div>
									</div>
									<?php
								}
								?>
							</div>
						<?php
					}
					?>
					<!-- End: Coupan code Dynamic logic -->
					<?php if ( ! empty( $outstanding_payment ) ) : ?>
						<div class="checkout-summary__dues">

							<div class="d-flex justify-content-between checkout-summary__table-row">
								<div class="text-start">
									<p class=""><?php esc_html_e( 'Trip Total', 'trek-travel-theme' ); ?></p>
								</div>
								<div class="text-end">
									<p class="fw-bold fs-lg"><span class="amount"><?php echo $cart_total_curr; ?></span></p>
								</div>
							</div>

							<div class="d-flex justify-content-between checkout-summary__table-row">
								<div class="text-start">
								<p><?php esc_html_e( 'Future Payment', 'trek-travel-theme' ); ?></p>
								</div>
								<div class="text-end">
								<p class="fw-bold fs-lg"><span class="amount"><?php echo $remaining_amount_curr; ?></span></p>
								</div>
							</div>

							<div class="d-flex justify-content-between checkout-summary__dues-today  align-items-center checkout-summary__table-row">
								<div class="text-start">
									<p class="fs-lg lh-lg fw-bold "><?php esc_html_e( 'Due Today', 'trek-travel-theme' ); ?></p>
								</div>
								<div class="text-end">
									<p class="h5 fw-semibold checkout-summary__font"><span class="amount"><?php echo $outstanding_payment; ?></span></p>
								</div>
							</div>
							
						</div>
					<?php endif; ?>
				</div>
			</div>
			<hr>
			<p class="mb-1"><?php esc_html_e( 'Secure your adventure today! Trip pricing can change, so don’t miss out—book now and lock in your dream getaway!', 'trek-travel-theme' ); ?></p>
		</div>
    </div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
    document.addEventListener("keydown", function( event ) {
        if ( event.key === "Enter" ) {
            const promoCodeInput = document.querySelector(".coupon-code-input"); // Replace with the correct class
            const promoCodeSubmitButton = document.querySelector(".promo-code-submit"); // Replace with the correct class

            if (promoCodeInput) {
                event.preventDefault(); // Prevent form submission
                promoCodeSubmitButton.click(); // Trigger promo code submission
            }
        }
    });
});
</script>

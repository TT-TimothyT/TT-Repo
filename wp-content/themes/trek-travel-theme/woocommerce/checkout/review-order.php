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
$dues                    = "";
$future_payment          = "null";
$promo                   = null;
$trek_user_checkout_data = get_trek_user_checkout_data();
$tt_posted               = $trek_user_checkout_data['posted'];
$tt_coupan_code          = ( isset( $tt_posted['coupon_code'] ) && $tt_posted['coupon_code'] ? $tt_posted['coupon_code'] : '' );
$applied_coupon          = false;
if ( ! empty( $tt_coupan_code ) && tt_is_coupon_applied( $tt_coupan_code ) ) {
    $applied_coupon = true;
}
$product_id     = isset( $tt_posted['product_id'] ) ? $tt_posted['product_id'] : '';
$no_of_guests   = isset( $tt_posted['no_of_guests'] ) ? $tt_posted['no_of_guests'] : 1;
$accepted_p_ids = tt_get_line_items_product_ids();
$product_name   = $start_date = '';
foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
    if (
        $_product && $_product->exists() && $cart_item['quantity'] > 0 && !in_array($cart_item['product_id'], $accepted_p_ids)
        && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)
    ) {
        $product_name = $_product->get_name();
        $start_date = $_product->get_attribute('start-date');
        $end_date = $_product->get_attribute('end-date');
    }
}
$tripInfo = tt_get_trip_pid_sku_from_cart();
$depositAmount = tt_get_local_trips_detail('depositAmount', '', $tripInfo['sku'], true);
$depositAmount = $depositAmount ? str_ireplace(',', '', $depositAmount) : 0;
$depositAmount = floatval($depositAmount) * intval($no_of_guests);
$cart_total = WC()->cart->total;
$remaining_amount = $cart_total - ($depositAmount ? $depositAmount : 0);
$remaining_amountCurr = get_woocommerce_currency_symbol() . $remaining_amount;
$cart_totalCurr = get_woocommerce_currency_symbol() . $cart_total;
$depositAmountCurr  = get_woocommerce_currency_symbol() . $depositAmount;
$trip_booking_limit = get_trip_capacity_info();
$parent_trip_link = isset($tripInfo['parent_trip_link']) ? $tripInfo['parent_trip_link'] : 'javascript:';
$arch_info = tt_get_insurance_info($tt_posted);
$insuredPerson = isset($arch_info['count']) ? $arch_info['count'] : 0;
//$insuredPerson = isset($tt_posted['insuredPerson']) ? $tt_posted['insuredPerson'] : 0;
$tt_terms = isset($tt_posted['tt_terms']) ? $tt_posted['tt_terms'] : 0;
$depositBeforeDate = '';
if( $tripInfo && isset($tripInfo['sku']) ){
    $depositAmount = tt_get_local_trips_detail('depositAmount', '', $tripInfo['sku'], true);
    $depositBeforeDate = tt_get_local_trips_detail('depositBeforeDate', '', $tripInfo['sku'], true);
    $depositAmount = $depositAmount ? str_ireplace(',','',$depositAmount) : 0;
    if( $depositAmount ){
        $depositAmount = floatval($depositAmount) * intval($no_of_guests);
    }
}
$is_deposited = tt_get_trip_payment_mode($depositBeforeDate);
$pay_amount = isset($tt_posted['pay_amount']) ? $tt_posted['pay_amount'] : 'full';
?>
<div class="checkout-summary__mobile d-block d-lg-none text-center position-sticky" id="checkout-summary-mobile">
    <div class="closed">
        <i class="bi bi-chevron-compact-up mb-2"></i>
        <p class="mb-0">Trip Summary</p>
        <p class="mb-3 fs-md lh-md fw-bold">Total: <?php wc_cart_totals_subtotal_html(); ?></p>
    </div>
    <div class="open">
        <p class="mb-4">Trip Summary</p>
        <i class="bi bi-chevron-compact-down mb-2"></i>
    </div>
</div>
<div class="checkout-summary d-none d-lg-block" id="checkout-summary">
    <div class="card checkout-summary__card">
        <h5 class="fw-semibold checkout-summary__title mb-3"><?php echo $product_name; ?></h5>
        <div class="checkout-summary__date d-flex">
            <!-- <p class="fw-medium"><?php echo $start_date; ?></p> -->
            <!-- <a href="<?php echo $parent_trip_link; ?>">Change date</a> -->
        </div>
        <p class="mb-1">Trip pricing is subject to change. Book your trip now to lock in your price!</p>
        <hr>
        <!-- Products line items -->
        <?php
        $line_item_html = '<div class="checkout-summary__price">
            <p class="fs-lg lh-lg fw-medium mt-1">Price Summary</p>';
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                $product_name = $_product->get_name();
                $sku = $_product->get_sku();
                $product_qty = $cart_item['quantity'];
                if (!in_array($cart_item['product_id'], $accepted_p_ids)) {
                    $product_qty = $no_of_guests;
                }
                if ($sku == 'TTWP23FEES') {
                    $product_qty = $insuredPerson;
                    $cart_sub_total = WC()->cart->get_product_subtotal($_product, 1);
                } else {
                    $cart_sub_total = WC()->cart->get_product_subtotal($_product, $product_qty);
                }
                $cart_item = wc_get_formatted_cart_item_data($cart_item);
                $line_item_html .= '<div class="d-flex justify-content-between"><div><p class="mb-2">' . $product_name . '<span class="fs-sm lh-sm checkout-summary__guest">x' . $product_qty . ' guest</span></p></div>
                    <div><p class="mb-2 fw-medium">' . $cart_sub_total . '</p></div></div>';
            }
        }
        $line_item_html .= '</div>';
        echo $line_item_html;
        ?>
        <!-- End product line items -->
        <hr>
        <div class="checkout-summary__promo">
            <span class="promo">Promo Code</span>
            <div class="mt-5 d-flex justify-content-between align-items-start promo-form checkout-summary__promo <?php echo ( $applied_coupon ? 'd-none' : 'd-flex' ); ?>">
                <div class="form-group form-floating mb-0 w-75">
                    <input type="text" class="input-text form-control rounded-1 promo-input coupon-code-input" name="coupon_code" placeholder="Enter promo code" value="<?php echo $tt_coupan_code; ?>" required>
                    <label>Enter promo code</label>
                    <div class="invalid-feedback invalid-code" <?php echo ( true !== $applied_coupon && ! empty( $tt_coupan_code ) ? 'style="display: block"' : 'style="display: none"' ) ?>>
                        <img class="invalid-icon" />
                        This code is no longer valid.
                    </div>
                </div>
                <button type="button" id="promo-checkout" class="btn btn-primary rounded-1 checkout-summary__submit tt_apply_coupan promo-code-submit" data-action="add" name="Submit" value="Submit">Submit</button>
            </div>
            <div class="checkout-summary__applied d-flex mt-4 <?php echo ( $applied_coupon ? 'd-flex' : 'd-none' ); ?>">
                <p class="fs-md lh-md mb-0">Promo <span class="fw-bold" id="tt-applied-code"><?php echo $tt_coupan_code; ?></span> Applied</p>
                <a href="javascript:" class="tt_remove_coupan" data-action="remove">Remove</a>
            </div>
        </div>
        <!-- Begin: Coupan code Dynamic logic -->

        <?php
        $coupon_html = '';
        if (WC()->cart->get_coupons()) {
            $coupon_html .= '<div class="d-flex justify-content-between checkout-summary__coupans">';
            foreach (WC()->cart->get_coupons() as $code => $coupon) {
                if (is_string($coupon)) {
                    $coupon = new WC_Coupon($coupon);
                }
                $coupan_code     = strtoupper($coupon->get_code());
                $amount          = WC()->cart->get_coupon_discount_amount($coupon->get_code(), WC()->cart->display_cart_ex_tax);
                $discount_amount = floatval( $no_of_guests ) * floatval( $amount );
                $discount_type   = $coupon->get_discount_type();
                $coupon_html    .= '<div>
                                        <p class="mb-5" data-coupan="' . esc_attr( sanitize_title( $code ) ) . '">Discount</p>
                                    </div>
                                    <div class="text-end">
                                        <p class="fw-medium"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $discount_amount . '</span></p>
                                    </div>';
            }
            $coupon_html .= '</div>';
        }
        $tt_fees_html = '';
        if (WC()->cart->get_fees()) {
            foreach (WC()->cart->get_fees() as $fee) {
                $cart_totals_fee = WC()->cart->display_prices_including_tax() ? wc_price($fee->total + $fee->tax) : wc_price($fee->total);
                $tt_fees_html .= '<div class="d-flex justify-content-between checkout-summary__fees">';
                $tt_fees_html .= '<div>
                    ' . $fee->name . '
                                        <p class="mb-5"></p>
                                    </div>
                                    <div class="text-end">
                                        <p class="fw-medium">' . $cart_totals_fee . '</p>
                                    </div>';
                $tt_fees_html .= '</div>';
            }
        }
        ?>

        <?php
        $tt_cart_tax_html = '';
        if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) {
            $tt_cart_tax_html .= '<div class="d-flex justify-content-between checkout-summary__fees">';
            if ('itemized' === get_option('woocommerce_tax_total_display')) {
                foreach (WC()->cart->get_tax_totals() as $code => $tax) {
                    $tt_cart_tax_html .= '<div>
                        <p class="mb-5">' . esc_html($tax->label) . '</p>
                    </div>
                    <div class="text-end">
                        <p class="fw-medium">' . wp_kses_post($tax->formatted_amount) . '</p>
                    </div>';
                }
            } else {
                $tt_cart_tax_html .= '<div>
                        <p class="mb-5">' . esc_html(WC()->countries->tax_or_vat()) . '</p>
                    </div>
                    <div class="text-end">
                        <p class="fw-medium">' . wc_price(WC()->cart->get_taxes_total()) . '</p>
                    </div>';
            }
            $tt_cart_tax_html .= '</div>';
        }
        ?>

        <!-- End: Coupan code Dynamic logic -->
        <hr>
        <div class="checkout-summary__total mt-2">
            <div class="d-flex justify-content-between mb-1">
                <div>
                    <p class="mb-2">Subtotal</p>
                    <p class="mb-0">
                        Local Taxes
                    <p class="fs-xs lh-xs checkout-summary__small">Taxes are based on your trip destination</p>
                    </p>
                </div>
                <div>
                    <p class="mb-2 fw-medium"><?php wc_cart_totals_subtotal_html(); ?></p>
                    <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
                </div>
            </div>
            <?php
            echo $coupon_html;
            echo $tt_fees_html;
            echo $tt_cart_tax_html;
            ?>
            <?php
            $outstanding_payment = $cart_totalCurr;
            if ($pay_amount == 'deposite') {
                $outstanding_payment = $depositAmountCurr;
            } else {
                $remaining_amountCurr = get_woocommerce_currency_symbol() . '0';
            }
            ?>
            <?php if (!empty($outstanding_payment)) : ?>
                <div class="d-flex justify-content-between checkout-summary__dues">
                    <div>
                        <p class="mb-5">Trip Total</p>
                        <p class="fs-lg lh-lg fw-medium mb-3">Due Today</p>
                        <p>Future Payment</p>
                    </div>
                    <div class="text-end">
                        <p class="fw-medium"><span class="amount"><?php echo $cart_totalCurr; ?></span></p>
                        <p class="h5 fw-semibold checkout-summary__font"><span class="amount"><?php echo $outstanding_payment; ?></span></p>
                        <p class="fw-medium"><span class="amount"><?php echo $remaining_amountCurr; ?></span></p>
                    </div>
                </div>
            <?php endif; ?>
            <p class="fs-sm lh-sm fw-normal checkout-summary__p mt-2">All payment amounts are processed in $USD. Please check your exchange rates if booking outside of the U.S.</p>
            <div class="col-md px-0 d-flex align-items-center guest-checkout__checkbox-gap mb-5 d-none">
                <input type="checkbox" class="guest-checkout__checkbox" value="1" required="required" name="tt_terms" <?php if ($tt_terms == 1) echo 'checked'; ?>>
                <label class="w-75">I have read and agree to Trek Travel’s Terms and Conditions and Cancellation Policy</label>
            </div>
            <?php if ( $trip_booking_limit['remaining']  >= $no_of_guests ) : ?>
                <button class="btn btn-lg btn-primary rounded-1 w-100 mt-1 mb-3 checkout-summary__button d-none" <?php if ($tt_terms != 1) echo 'disabled'; ?>>Pay now</button>
            <?php endif; ?>
            <div class="mt-5 text-center"><a href="<?php echo TT_CAN_POLICY_PAGE; ?>" target="_blank">View Cancellation Policy</a></div>
        </div>
    </div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
    document.addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
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

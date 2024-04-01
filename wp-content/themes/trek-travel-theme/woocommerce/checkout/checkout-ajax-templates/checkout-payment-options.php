<?php
$trek_user_checkout_data =  get_trek_user_checkout_data();
$tt_posted               = $trek_user_checkout_data['posted'];
$tripInfo                = tt_get_trip_pid_sku_from_cart();
$no_of_guests            = isset($tt_posted['no_of_guests']) ? $tt_posted['no_of_guests'] : 1;
$guest_insurance         = isset( $tt_posted['trek_guest_insurance'] ) ? $tt_posted['trek_guest_insurance'] : array();
$insurance_amount        = tt_get_full_insurance_amount( $guest_insurance );
$deposit_info            = tt_get_deposit_info( $tripInfo['sku'], $no_of_guests, $insurance_amount );
$deposit_amount          = $deposit_info['deposit_amount'];
$deposit_allowed         = $deposit_info['deposit_allowed'];
$pay_amount_default_mode = $deposit_allowed ? 'deposite' : 'full';
$pay_amount              = isset($tt_posted['pay_amount']) ? $tt_posted['pay_amount'] : $pay_amount_default_mode;
?>
<h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-1">Payment Option</h5>
<p class="fs-sm checkout-payment__sublabel">Minimum amount required is trip deposit. <a href="<?php echo home_url( '/cancellation-policy/' ); ?>" target="_blank">Learn more about our No-Risk Deposit.</a></p>
<div class="checkout-payment__pay">
    <?php
    $cart_total_full_amount = isset( $tt_posted['cart_total_full_amount'] ) ? $tt_posted['cart_total_full_amount'] : '';
    $cart_total             = 'deposite' === $pay_amount && ! empty( $cart_total_full_amount ) ? $cart_total_full_amount : WC()->cart->total;
    $remaining_amount       = $cart_total - ( $deposit_amount ? $deposit_amount : 0 );
    $remaining_amountCurr   = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $remaining_amount . '</span>';
    $cart_totalCurr         = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $cart_total . '</span>';
    $depositAmountCurr      = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $deposit_amount . '</span>';
    if( $pay_amount == 'full' ){
        $remaining_amountCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>0</span>';
    }
    if( $deposit_allowed ) { ?>
        <div class="mb-4">
            <input type="radio" name="pay_amount" required="required" <?php echo ( $pay_amount == 'deposite' ? 'checked' : '' ); ?> value="deposite">
            <div class="checkout-payment__paydep rounded-1 d-flex justify-content-between align-items-center">
                <p class="fs-lg lh-lg fw-medium mb-0">Pay Deposit: <span><?php echo $depositAmountCurr; ?></span></p>
                <i class="checkout-payment__pay-icon"></i>
            </div>
        </div>
    <?php } ?>
    <div>
        <input type="radio" name="pay_amount" required="required" value="full"<?php echo ( $pay_amount == 'full' ? 'checked' : '' ); ?>>
        <div class="checkout-payment__paydep rounded-1 d-flex justify-content-between align-items-center">
            <p class="fs-lg lh-lg fw-medium mb-0">Pay Full Amount: <span><?php echo $cart_totalCurr; ?></span></p>
            <i class="checkout-payment__pay-icon"></i>
        </div>
    </div>
</div>
<p class="mb-2">Remaining Amount Owed: <span class="fw-medium"><?php echo $remaining_amountCurr; ?></span></p>
<p class="fs-sm lh-sm checkout-payment__gray">Our team will reach out to collect final payment prior to your trip start date.</p>
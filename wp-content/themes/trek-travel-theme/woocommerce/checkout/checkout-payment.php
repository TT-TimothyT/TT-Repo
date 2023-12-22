<?php
global $woocommerce;
//demo content for looping till dynamic content is not loaded
$cards = ['Visa ending in 2559', 'Visa ending in 2456'];
$guest = ['Jason Bauer', 'Tim Lasso', 'Steve Bauer', 'Keith Lasso'];
$trek_user_checkout_data =  get_trek_user_checkout_data();
$tt_posted = $trek_user_checkout_data['posted'];
$trek_user_checkout_formatted = $trek_user_checkout_data['formatted'];
$primary_address_1 = isset($tt_posted['shipping_address_1']) ? $tt_posted['shipping_address_1'] : '';
$primary_address_2 = isset($tt_posted['shipping_address_2']) ? $tt_posted['shipping_address_2'] : '';
$primary_country = isset($tt_posted['shipping_country']) ? $tt_posted['shipping_country'] : '';
$guest_insurance = isset($tt_posted['trek_guest_insurance']) ? $tt_posted['trek_guest_insurance'] : array();
$shipping_fname = isset($tt_posted['shipping_first_name']) ? $tt_posted['shipping_first_name']  :'';
$shipping_lname = isset($tt_posted['shipping_last_name']) ? $tt_posted['shipping_last_name']  :'';
$shipping_name = $shipping_fname.' '.$shipping_lname; 
$shipping_postcode = isset($tt_posted['shipping_postcode']) ? $tt_posted['shipping_postcode']  :'';
$shipping_state = isset($tt_posted['shipping_state']) ? $tt_posted['shipping_state']  :'';
$shipping_city = isset($tt_posted['shipping_city']) ? $tt_posted['shipping_city']  :'';
$iter = 0;
$cols = 2;
$guest_insurance_html = '';
if (isset($guest_insurance) && !empty($guest_insurance)) {
    $fields_size = sizeof(isset($tt_posted['trek_guest_insurance']['guests']) ? $tt_posted['trek_guest_insurance']['guests'] : array()) + 1;
    foreach ($guest_insurance as $guest_insurance_k => $guest_insurance_val) {
        if ($guest_insurance_k == 'primary') {
            if ($iter % $cols == 0) {
                $guest_insurance_html .= '<div class="row mx-0">';
            }
            $guest_insurance_html .= '<div class="col-lg-6 px-0 travel-col ' . $guest_insurance_k . ' ' . $iter . '">';
            $guest_insurance_html .= '<p class="fw-medium mb-2">Primary Guest: '.$shipping_name.'</p>
                <p class="fs-sm lh-sm mb-0">' . (isset($guest_insurance_val['is_travel_protection']) && $guest_insurance_val['is_travel_protection'] == 1 ? 'Added Travel Protection' : 'Declined Travel Protection') . '</p>';
            $guest_insurance_html .= '</div>';
            if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                $guest_insurance_html .= '</div>';
            }
            $iter++;
        } else {
            foreach ($guest_insurance_val as $guest_key => $guest_insurance_Data) {
                if ($iter % $cols == 0) {
                    $guest_insurance_html .= '<div class="row mx-0">';
                }
                $guestInfo = $tt_posted['guests'][$guest_key];
                $fullname = $guestInfo['guest_fname'] . ' ' . $guestInfo['guest_lname'];
                $guest_insurance_html .= '<div class="col-lg-6 px-0 travel-col ' . $guest_insurance_k . ' ' . $iter . '">';
                $guest_insurance_html .= '<p class="fw-medium mb-2">Guest ' . $guest_key + 1 . ': ' . $fullname . '</p>
                    <p class="fs-sm lh-sm mb-0">' . ( isset($guest_insurance_Data['is_travel_protection']) && $guest_insurance_Data['is_travel_protection'] == 1 ? 'Added Travel Protection' : 'Declined Travel Protection') . '</p>';
                $guest_insurance_html .= '</div>';
                if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                    $guest_insurance_html .= '</div>';
                }
                $iter++;
            }
        }
    }
}
$tripInfo = tt_get_trip_pid_sku_from_cart();
$depositAmount = 0;
$depositBeforeDate = '';
$isDeposite = '';
$no_of_guests = isset($tt_posted['no_of_guests']) ? $tt_posted['no_of_guests'] : 1;
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
<div class="checkout-payment" id="checkout-payment">
    <div class="checkout-payment__add-travel <?php if (!empty($guest_insurance_html)) echo 'd-none'; ?>">
        <h5 class="fs-xl lh-xl fw-medium d-flex align-items-center checkout-payment__title mb-3">Interested in Travel Protection? <i class="bi bi-info-circle checkout-travel-protection-tooltip"></i></h5>
        <p class="fs-sm checkout-payment__sublabel mb-0">In order to help protect you, your traveling party, and your trip investment, we recommend that you add travel protection to your reservation. For your convenience, Trek Travel offers this protection with a wide range of benefits through Arch RoamRight comprehensive line of insurance programs.</p>
        <div class="checkout-payment__checkbox d-flex align-items-lg-center">
            <input type="checkbox" class="protection_modal protection_modal_ev">
            <label>I am interested in Trek Travel's Travel Protection Plan</label>
        </div>
    </div>
    <div class="checkout-payment__added-travel <?php if (empty($guest_insurance_html)) echo 'd-none'; ?>">
        <div class="d-flex travel">
            <h5 class="fs-xl lh-xl fw-medium d-flex align-items-center checkout-payment__title mb-3">Travel Protection Information</h5>
            <button type="button" class="btn btn-md btn-outline-primary d-lg-block d-none edit-info" data-bs-toggle="modal" data-bs-target="#protection_modal">
                Edit Info
            </button>
        </div>
        <div id="travel-protection-div">
            <?php echo $guest_insurance_html; ?>
        </div>
        <button type="button" class="btn btn-md btn-outline-primary d-lg-none d-block" data-bs-toggle="modal" data-bs-target="#protection_modal">
            Edit Info
        </button>
    </div>
    <hr>
    <div class="checkout-payment__options">
        <h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-1">Payment Option</h5>
        <p class="fs-sm checkout-payment__sublabel">Minimum amount required is trip deposit. <a href="#">Learn more about our No-Risk Deposit.</a></p>
        <div class="checkout-payment__pay">
            <?php
            $cart_total = WC()->cart->total;
            $remaining_amount = $cart_total - ( $depositAmount ? $depositAmount : 0 );
            $remaining_amountCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>'.$remaining_amount.'</span>';
            $cart_totalCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>'.$cart_total.'</span>';
            $depositAmountCurr  = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>'.$depositAmount.'</span>';
            if( $pay_amount == 'full' ){
                $remaining_amountCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>0</span>';
            }
            if( $depositAmount && $depositAmount > 0 && $is_deposited == 1 ) { ?>
            <div class="mb-4">
                <input type="radio" name="pay_amount" required="required"  value="deposite" <?php echo ( $pay_amount == 'deposite' ? 'checked' : '' ); ?>>
                <div class="checkout-payment__paydep rounded-1 d-flex justify-content-between align-items-center">
                    <p class="fs-lg lh-lg fw-medium mb-0">Pay Deposit: <span><?php echo $depositAmountCurr; ?></span></p>
                    <i class="checkout-payment__pay-icon"></i>
                </div>
            </div>
            <?php } ?>
            <div>
                <input type="radio" name="pay_amount" required="required" value="full" <?php echo ( $pay_amount == 'full' ? 'checked' : '' ); ?>>
                <div class="checkout-payment__paydep rounded-1 d-flex justify-content-between align-items-center">
                    <p class="fs-lg lh-lg fw-medium mb-0">Pay Full Amount: <span><?php echo $cart_totalCurr; ?></span></p>
                    <i class="checkout-payment__pay-icon"></i>
                </div>
            </div>
        </div>
        <p class="mb-2">Remaining Amount Owed: <span class="fw-medium"><?php echo $remaining_amountCurr; ?></span></p>
        <p class="fs-sm lh-sm checkout-payment__gray">Our team will reach out to collect final payment prior to your trip start date.</p>
    </div>
    <hr>
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
                            This field is required.
                        </div>
                    </div>
                </div>
                <div class="col-md-2 px-0">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="floatingInputGrid" name="pin" placeholder="Pin" value="">
                        <label for="floatingInputGrid">Pin</label>
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
    <div class="checkout-payment__method">
        <h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-4">Payment Method</h5>
        <div class="checkout-payment__paymethod">
            <?php
            $available_gateways  = WC()->payment_gateways->get_available_payment_gateways();
            if (WC()->cart->needs_payment()) : ?>
                <ul class="wc_payment_methods payment_methods methods">
                    <?php
                    if (!empty($available_gateways)) {
                        foreach ($available_gateways as $gateway) {
                            wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
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
        <h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-4">Billing Address</h5>
        <div class="d-flex align-items-lg-center checkout-payment__billing-checkbox">
            <input type="checkbox" class="billing_checkbox" name="is_same_billing_as_mailing" value="1" <?php if (isset($tt_posted['is_same_billing_as_mailing']) && $tt_posted['is_same_billing_as_mailing'] == 1) echo 'checked'; ?>>
            <label>Same as my mailing address</label>
        </div>
        <?php
        $fields = $woocommerce->checkout->get_checkout_fields('billing');
        $iter = 0;
        $field_html = '';
        $fields_size = sizeof($fields);
        $cols = 2;
        $field_includes = array('billing_first_name','billing_last_name','billing_address_1', 'billing_address_2', 'billing_country', 'billing_state', 'billing_city', 'billing_postcode');
        if( $fields ){
            foreach ($fields as $key => $field) {
                if (in_array($key, $field_includes)) {
                    if ($iter % $cols == 0) {
                        $field_html .= '<div class="row mx-0 guest-checkout__primary-form-row billing_row ' . ( isset($tt_posted['is_same_billing_as_mailing']) && $tt_posted['is_same_billing_as_mailing'] == 1 ? 'd-none' : '') . '">';
                    }
                    $field_html .= '<div class="col-md px-0 form-row"><div class="form-floating">';
                    $field['placeholder'] = $field['label'];
                    $field['required'] = true;
                    $field['label'] = '';
                    $field['input_class'] = array('form-control');
                    $field['return'] = true;
                    if( $key != 'billing_address_2' ){
                        $field['custom_attributes']['required'] = "required";
                    }
                    if( $key == 'billing_state' ){
                        $field['custom_attributes']['required'] = "required";
                    }
                    if( $key == 'billing_country' ){
                        $field['custom_attributes']['required'] = "required";
                    }
                    $woo_field_value = $woocommerce->checkout->get_value($key);
                    if (empty($woo_field_value) && isset($tt_posted[$key]) && $tt_posted[$key]) {
                        $woo_field_value = $tt_posted[$key];
                    }
                    if( $key == 'billing_state'  ){
                        $country_val = $woocommerce->checkout->get_value('billing_country');
                        if (isset($tt_posted['billing_country']) && !empty($tt_posted['billing_country'])) {
                            $country_val = $tt_posted['billing_country'];
                        }
                        $field['country'] = !empty( $country_val ) ? $country_val : '';
                        $woo_field_value = $tt_posted['billing_state'];
                    }
                    $field_input = woocommerce_form_field($key, $field, $woo_field_value);
                    $field_input = str_ireplace('<span class="woocommerce-input-wrapper">', '', $field_input);
                    $field_input = str_ireplace('</span>', '', $field_input);
                    $sort            = $field['priority'] ? $field['priority'] : '';
                    if ( isset($field['required']) ) {
                        $field['class'][] = 'validate-required';
                    }
                    if (isset($field['validate'])) {
                        foreach ($field['validate'] as $validate_name) {
                            $field['class'][] = 'validate-' . $validate_name . '';
                        }
                    }
                    $container_class = isset($field['class']) ? esc_attr(implode(' ', $field['class'])) : '';
                    $container_id    = esc_attr($key) . '_field';
                    $pfield_container = '<p class="form-row ' . $container_class . '" id="' . $container_id . '" data-priority="' . esc_attr($sort) . '">';
                    $field_input = str_ireplace($pfield_container, '', $field_input);
                    $field_input = str_ireplace('<p class="form-row form-row-wide address-field" id="billing_address_2_field" data-priority="26">', '', $field_input);
                    $field_input = str_ireplace('<p class="form-row form-row-wide address-field validate-postcode" id="billing_postcode_field" data-priority="90">', '', $field_input);
                    $field_input = str_ireplace('</p>', '', $field_input);
                    $field_html .= $field_input;
                    $field_html .= '<label for="billing_' . $key . '">' . $field['placeholder'] . '</label>';
                    if ($key == 'billing_state' || $key == 'billing_country') {
                        $field_html .= '<div class="invalid-feedback"><img class="invalid-icon" /> This field is required.</div>';
                    }
                    $field_html .= '</div></div>';
                    if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                        $field_html .= '</div>';
                    }
                    $iter++;
                }
            }
        }
        $field_html .= '<input type="hidden" id="tt_pay_fname" name="first_name" value="'.$woocommerce->checkout->get_value('billing_first_name').'"/>';
        $field_html .= '<input type="hidden" id="tt_pay_lname" name="last_name" value="'.$woocommerce->checkout->get_value('billing_last_name').'"/>';
        echo $field_html;
        ?>
        <div class="checkout-payment__pre-address <?php echo ( isset($tt_posted['is_same_billing_as_mailing']) && $tt_posted['is_same_billing_as_mailing'] == 1 ? '' : 'd-none'); ?>" style="height:120px;">
            <p class="mb-0"><?php echo $shipping_name; ?></p>
            <p class="mb-0"><?php echo $primary_address_1; ?></p>
            <p class="mb-0"><?php echo $primary_address_2; ?></p>
            <p class="mb-0"><?php echo $shipping_city; ?>, <?php echo $shipping_state; ?>, <?php echo $shipping_postcode; ?></p>
            <p class="mb-0"><?php echo $primary_country; ?></p>
            <p class="mb-0"></p>
        </div>
        <div class="d-flex checkout-payment__billing-checkboxtwo">
            <input type="checkbox" name="is_saved_billing" value="1" <?php if ( isset($tt_posted['is_saved_billing']) &&  $tt_posted['is_saved_billing'] == 1) echo 'checked'; ?>>
            <label class="w-75">Save this billing address for future use. This will override your existing billing address saved on your profile.</label>
        </div>
    </div>
    <hr>
    <div class="checkout-payment__release">
        <h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-4">Release of Liability and Assumption of All Risks</h5>
        <p class="mb-0 fs-sm lh-sm checkout-payment__gray">Please scroll through release form below and check "I Agree" once finished.</p>
        <div class="checkout-payment__iframe rounded-1">
            <h5 class="fs-lg lh-lg fw-medium mb-2">Waiver iFrame Headline</h5>
            <p class="fs-sm lh-sm">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ultrices in amet posuere faucibus ultrices pellentesque. Tristique magna lectus dui nulla sed sit elementum. Egestas sapien vitae nunc senectus ac egestas ipsum non elementum. Ullamcorper turpis ultrices orci sit quis malesuada nunc, aliquam et. Eu et sagittis eget nulla nulla sapien justo. Scelerisque tellus mollis ridiculus porta suscipit sed mauris.<br>

                Lacus turpis nunc, blandit odio. Auctor vitae maecenas rhoncus mattis nulla vel amet aenean. Ac augue convallis ullamcorper id blandit. Pharetra pellentesque quam nisl pretium arcu lectus id proin imperdiet.
                Donec pharetra, in morbi rhoncus nullam sed volutpat mi.<br>

                Sit fringilla in eu, dictum sit ut. Adipiscing interdum enim dolor adipiscing morbi aliquam. Metus, est nisi, fermentum scelerisque praesent quisque gravida mauris vestibulum. Morbi nisl viverra nunc nulla odio eget. Sed pulvinar fermentum sed aliquet aliquet. Cras laoreet amet, lorem placerat purus.</p>
        </div>
        <div class="d-flex checkout-payment__billing-checkboxtwo">
            <input class="tt_waiver_check" type="checkbox" name="tt_waiver" value="1" <?php if (isset($tt_posted['tt_waiver']) && $tt_posted['tt_waiver'] == 1) echo 'checked'; ?> required="required">
            <label>By checking “I Agree” I acknowledge that I have read, understand and agree to this Release Form and Cancellation Policy.</label>
        </div>
        <div class="invalid-feedback tt_waiver_required">
            <img class="invalid-icon" />
            This field is required.
        </div>
    </div>
    <hr>
    <div class="checkout-payment__button mb-5">
        <button type="button" class="btn btn-md btn-outline-primary btn-previous tt_change_checkout_step" data-step="2">Go back</button>
        <button type="button" class="btn btn-md btn-primary float-end btn-next">Next: Review & Pay</button>
    </div>
</div>
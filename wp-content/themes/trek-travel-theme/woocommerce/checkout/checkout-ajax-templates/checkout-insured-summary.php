<?php
$tt_checkoutData = get_trek_user_checkout_data();
$tt_posted       = isset( $tt_checkoutData['posted'] ) ? $tt_checkoutData['posted'] : [];
$guests          = isset( $tt_posted['guests'] ) ? $tt_posted['guests'] : [];
$guest_insurance = isset( $tt_posted['trek_guest_insurance'] ) ? $tt_posted['trek_guest_insurance'] : [];
$shipping_fname  = isset( $tt_posted['shipping_first_name'] ) ? $tt_posted['shipping_first_name'] : '';
$shipping_lname  = isset( $tt_posted['shipping_last_name'] ) ? $tt_posted['shipping_last_name'] : '';
$shipping_name   = $shipping_fname . ' ' . $shipping_lname;
//primary user Insured HTML
$iter = 0;
$cols = 2;
$guest_insurance_html = '';
if ( isset( $guest_insurance ) && ! empty( $guest_insurance ) ) {
    $insuredGuests = isset( $guest_insurance['guests'] ) ? $guest_insurance['guests'] : [];
    $fields_size   = sizeof($insuredGuests) + 1;
    foreach ( $guest_insurance as $guest_insurance_k => $guest_insurance_val ) {
        if ( $guest_insurance_k == 'primary' ) {
            $p_insurance_amount      = isset( $guest_insurance_val['basePremium'] ) ? $guest_insurance_val['basePremium'] : 0;
            $p_insurance_amount_curr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $p_insurance_amount . '</span>';
            if ( $iter % $cols == 0 ) {
                $guest_insurance_html .= '<div class="d-flex order-details__flex order-details__flexmulti">';
            }
            $guest_insurance_html .= '<div>';
            $guest_insurance_html .= '<p class="fw-medium mb-2">Primary Guest: ' . $shipping_name . '</p>
                <p class="fs-sm lh-sm mb-0">' . ( isset( $guest_insurance_val['is_travel_protection'] ) && $guest_insurance_val['is_travel_protection'] == 1 ? 'Added Travel Protection ('.$p_insurance_amount_curr . ')' : 'Declined Travel Protection' ) . '</p>';
            $guest_insurance_html .= '</div>';
            if ( ( $iter % $cols == $cols - 1 ) || ( $iter == $fields_size - 1 ) ) {
                $guest_insurance_html .= '</div>';
            }
            $iter++;
        } else {
            foreach ($guest_insurance_val as $guest_key => $guest_insurance_Data) {
                $g_insurance_amount      = isset( $guest_insurance_Data['basePremium'] ) ? $guest_insurance_Data['basePremium'] : 0;
                $g_insurance_amount_curr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $g_insurance_amount . '</span>';
                if ( $iter % $cols == 0 ) {
                    $guest_insurance_html .= '<div class="d-flex order-details__flex order-details__flexmulti">';
                }
                $guestInfo = $tt_posted['guests'][$guest_key];
                $fullname = $guestInfo['guest_fname'] . ' ' . $guestInfo['guest_lname'];
                $guest_insurance_html .= '<div>';
                $guest_insurance_html .= '<p class="fw-medium mb-2">Guest ' . $guest_key + 1 . ': ' . $fullname . '</p>
                    <p class="fs-sm lh-sm mb-0">' . ( isset( $guest_insurance_Data['is_travel_protection'] ) && $guest_insurance_Data['is_travel_protection'] == 1 ? 'Added Travel Protection ('.$g_insurance_amount_curr . ')' : 'Declined Travel Protection' ) . '</p>';
                $guest_insurance_html .= '</div>';
                if ( ( $iter % $cols == $cols - 1 ) || ( $iter == $fields_size - 1 ) ) {
                    $guest_insurance_html .= '</div>';
                }
                $iter++;
            }
        }
    }
}
echo $guest_insurance_html;

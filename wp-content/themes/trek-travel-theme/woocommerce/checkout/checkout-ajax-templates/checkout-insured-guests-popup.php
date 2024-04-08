<?php
$trek_checkoutData =  get_trek_user_checkout_data();
$tt_posted = isset($trek_checkoutData['posted']) ? $trek_checkoutData['posted'] : [];
$guests = isset($tt_posted['guests']) ? $tt_posted['guests'] : [];
$guest_insurance = isset($tt_posted['trek_guest_insurance']) ? $tt_posted['trek_guest_insurance'] : [];
$shipping_fname = isset($tt_posted['shipping_first_name']) ? $tt_posted['shipping_first_name'] : '';
$shipping_lname = isset($tt_posted['shipping_last_name']) ? $tt_posted['shipping_last_name'] : '';
$primary_name  = $shipping_fname . ' ' . $shipping_lname;
//primary user Insured HTML
$basePremium = isset($guest_insurance['primary']['basePremium']) ? $guest_insurance['primary']['basePremium'] : 0;
$basePremium = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $basePremium .'</span>';
$insuredHTML = '';
$insuredHTML .= '<div class="modal-body__guest">
                <p class="mb-4 fw-medium">Primary Guest: ' . $primary_name . '</p>
                <div class="d-flex align-items-center mb-4">
                    <input id="trek_guest_insurance_pr_add" type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="1" ' . ($guest_insurance['primary']['is_travel_protection'] != 0 ? 'checked' : '') . '>
                    <input type="hidden"  name="trek_guest_insurance[primary][basePremium]" value="' . ($guest_insurance['primary']['basePremium'] ? $guest_insurance['primary']['basePremium'] : 0) . '">
                    <label for="trek_guest_insurance_pr_add">Add Travel Protection <span class="fw-bold">(' . $basePremium . ')</span></label>
                </div>
                <div class="d-flex align-items-center">
                    <input id="trek_guest_insurance_pr_decline" type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="0" ' . ($guest_insurance['primary']['is_travel_protection'] == 0 ? 'checked' : '') . '>
                    <label for="trek_guest_insurance_pr_decline">Decline Travel Protection</label>
                </div>
            </div>';
            if( empty( $guest_insurance['primary']['basePremium'] ) ) {
                $insuredHTML .= '<div class="invalid-feedback travel-protection-feedback" style="display:block;">
                    <img class="invalid-icon">
                    Something went wrong during the calculation of the Travel Protection amount. Please double-check date of birth and address from step one to ensure they are entered correctly, and try again.
                </div>';
            }
if ($guests) {
    foreach ($guests as $guest_k => $guest) {
        $basePremium = isset($guest_insurance["guests"][$guest_k]['basePremium']) ? $guest_insurance["guests"][$guest_k]['basePremium'] : 0;
        $basePremium = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $basePremium .'</span>';
        $guest_fname = isset($guest['guest_fname']) ? $guest['guest_fname'] : '';
        $guest_lname = isset($guest['guest_lname']) ? $guest['guest_lname'] : '';
        $guest_full_name = $guest_fname.' '.$guest_lname;
        $insuredHTML .= '<div class="modal-body__guest">
                                <p class="mb-4 fw-medium">Guest: ' . $guest_full_name . '</p>
                                <div class="d-flex align-items-center mb-4">
                                    <input id="trek_guest_insurance_radio_add_' . $guest_k . '" type="radio" class="guest_radio" name="trek_guest_insurance[guests][' . $guest_k . '][is_travel_protection]" value="1" ' . ($guest_insurance["guests"][$guest_k]["is_travel_protection"] != 0 ? 'checked' : '') . '>
                                    <input type="hidden" name="trek_guest_insurance[guests][' . $guest_k . '][basePremium]" value="' . ($guest_insurance["guests"][$guest_k]["basePremium"] ? $guest_insurance["guests"][$guest_k]["basePremium"]  : 0) . '">
                                    <label for="trek_guest_insurance_radio_add_' . $guest_k . '">Add Travel Protection <span class="fw-bold">(' . $basePremium . ')</span></label>
                                </div>
                                <div class="d-flex align-items-center">
                                    <input id="trek_guest_insurance_radio_decline_' . $guest_k . '" type="radio" class="guest_radio" name="trek_guest_insurance[guests][' . $guest_k . '][is_travel_protection]" value="0" ' . ($guest_insurance["guests"][$guest_k]["is_travel_protection"] == 0 ? 'checked' : '') . '>
                                    <label for="trek_guest_insurance_radio_decline_' . $guest_k . '">Decline Travel Protection</label>
                                </div>
                            </div>';
        if( empty( $guest_insurance["guests"][$guest_k]['basePremium'] ) ) {
            $insuredHTML .= '<div class="invalid-feedback travel-protection-feedback" style="display:block;">
                <img class="invalid-icon">
                Something went wrong during the calculation of the Travel Protection amount. Please double-check date of birth and address from step one to ensure they are entered correctly, and try again.
            </div>';
        }
    }
}
echo $insuredHTML;
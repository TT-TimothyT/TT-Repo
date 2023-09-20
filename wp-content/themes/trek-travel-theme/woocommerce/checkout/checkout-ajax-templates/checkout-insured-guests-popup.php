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
                    <input type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="1" ' . ($guest_insurance['primary']['is_travel_protection'] != 0 ? 'checked' : '') . '>
                    <input type="hidden"  name="trek_guest_insurance[primary][basePremium]" value="' . ($guest_insurance['primary']['basePremium'] ? $guest_insurance['primary']['basePremium'] : 0) . '">
                    <label>Add Travel Protection <span class="fw-bold">(' . $basePremium . ')</span></label>
                </div>
                <div class="d-flex align-items-center">
                    <input type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="0" ' . ($guest_insurance['primary']['is_travel_protection'] == 0 ? 'checked' : '') . '>
                    <label>Decline Travel Protection</label>
                </div>
            </div>';
if ($guests) {
    foreach ($guests as $guest_k => $guest) {
        $basePremium = isset($guest_insurance["guests"][$guest_k]['basePremium']) ? $guest_insurance["guests"][$guest_k]['basePremium'] : 0;
        $basePremium = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $basePremium .'</span>';
        $guest_fname = isset($guest['guest_fname']) ? $guest['guest_fname'] : '';
        $guest_lname = isset($guest['guest_lname']) ? $guest['guest_lname'] : '';
        $guest_full_name = $guest_fname.' '.$guest_lname;
        $insuredHTML .= '<div class="modal-body__guest">
                                <p class="mb-4 fw-medium">Guest : ' . $guest_full_name . '</p>
                                <div class="d-flex align-items-center mb-4">
                                    <input type="radio" class="guest_radio" name="trek_guest_insurance[guests][' . $guest_k . '][is_travel_protection]" value="1" ' . ($guest_insurance["guests"][$guest_k]["is_travel_protection"] != 0 ? 'checked' : '') . '>
                                    <input type="hidden" name="trek_guest_insurance[guests][' . $guest_k . '][basePremium]" value="' . ($guest_insurance["guests"][$guest_k]["basePremium"] ? $guest_insurance["guests"][$guest_k]["basePremium"]  : 0) . '">
                                    <label>Add Travel Protection <span class="fw-bold">(' . $basePremium . ')</span></label>
                                </div>
                                <div class="d-flex align-items-center">
                                    <input type="radio" class="guest_radio" name="trek_guest_insurance[guests][' . $guest_k . '][is_travel_protection]" value="0" ' . ($guest_insurance["guests"][$guest_k]["is_travel_protection"] == 0 ? 'checked' : '') . '>
                                    <label>Decline Travel Protection</label>
                                </div>
                            </div>';
    }
}
echo $insuredHTML;
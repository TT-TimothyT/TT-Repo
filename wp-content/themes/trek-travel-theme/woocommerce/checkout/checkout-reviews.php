<?php
global $woocommerce;
$trek_user_checkout_data =  get_trek_user_checkout_data();
$tt_posted = $trek_user_checkout_data['posted'];
$primary_name = $primary_email = $primary_phone = $primary_address_1 = $primary_address_2 = $primary_country = $billing_add1 = $billing_add2 = $billing_country = $primary_dob = $primary_gender = $primary_city = $primary_state = $billing_city = $billing_state = $billing_postcode = $primary_postcode = '';
if ($tt_posted) {
    $shipping_fname = isset($tt_posted['shipping_first_name']) ? $tt_posted['shipping_first_name'] : '';
    $shipping_lname = isset($tt_posted['shipping_last_name']) ? $tt_posted['shipping_last_name'] : '';
    $primary_name  = $shipping_fname . ' ' .$shipping_lname;
    $primary_email = isset($tt_posted['email']) ? $tt_posted['email'] : '';
    $primary_phone = isset($tt_posted['shipping_phone']) ? $tt_posted['shipping_phone'] : '';
    $primary_address_1 = isset($tt_posted['shipping_address_1']) ? $tt_posted['shipping_address_1'] : '';
    $primary_address_2 = isset($tt_posted['shipping_address_2']) ? $tt_posted['shipping_address_2'] : '';
    $primary_country = isset($tt_posted['shipping_country']) ? $tt_posted['shipping_country'] : '';
    $primary_state = isset($tt_posted['shipping_state']) ? $tt_posted['shipping_state'] : '';
    $primary_city = isset($tt_posted['shipping_city']) ? $tt_posted['shipping_city'] : '';
    $primary_postcode = isset($tt_posted['shipping_postcode']) ? $tt_posted['shipping_postcode'] : '';
    $primary_dob = isset($tt_posted['custentity_birthdate']) ? $tt_posted['custentity_birthdate'] : '';
    $primary_gender = isset($tt_posted['custentity_gender']) ? $tt_posted['custentity_gender'] : '';
    $primary_gender = ($primary_gender == 2 ? 'Female' : 'Male');
    $guests = isset($tt_posted['guests']) ? $tt_posted['guests'] : '';
    $billing_add1 = isset($tt_posted['billing_address_1']) ? $tt_posted['billing_address_1'] : '';
    $billing_add2 = isset($tt_posted['billing_address_2']) ? $tt_posted['billing_address_2'] : '';
    $billing_country = isset($tt_posted['billing_country']) ? $tt_posted['billing_country'] : '';
    $billing_state = isset($tt_posted['billing_state']) ? $tt_posted['billing_state'] : '';
    $billing_city = isset($tt_posted['billing_city']) ? $tt_posted['billing_city'] : '';
    $billing_postcode = isset($tt_posted['billing_postcode']) ? $tt_posted['billing_postcode'] : '';
}
$guests = $guests ? $guests : array();
$guest_insurance_html = tt_guest_insurance_output($tt_posted);
$cc_exp_date = isset($tt_posted['wc-cybersource-credit-card-expiry']) ? $tt_posted['wc-cybersource-credit-card-expiry'] : '';
$cc_masked = isset($tt_posted['wc-cybersource-credit-card-masked-pan']) ? $tt_posted['wc-cybersource-credit-card-masked-pan'] : '';
$cc_type = isset($tt_posted['wc-cybersource-credit-card-card-type']) ? $tt_posted['wc-cybersource-credit-card-card-type'] : '';
$tt_rooms_output = tt_rooms_output($tt_posted);

// Take bike names.
$tripInfo = tt_get_trip_pid_sku_from_cart();
$local_bike_details = tt_get_local_bike_detail($tripInfo['sku']);
$local_bike_models_info = array_column( $local_bike_details, 'bikeModel', 'bikeId' );
?>
<div class="checkout-review" id="checkout-review">
    <div class="checkout-review__guest">
        <div class="d-flex checkout-review__title-bar">
            <h5 class="fs-xl lh-xl fw-medium d-flex align-items-center checkout-review__title">Review Guest Information</h5>
            <a href="javascript:" class="btn btn-md btn-outline-primary d-lg-block d-none checkout-review__edit tt_change_checkout_step" data-step="1">Edit Guest Info</a>
        </div>
        <div class="row mx-0">
            <div class="col-lg-6 px-0 checkout-review__col">
                <p class="fw-medium mb-2">Primary Guest: <?php echo $primary_name; ?></p>
                <p class="fs-sm lh-sm mb-0"><?php echo $primary_email; ?></p>
                <p class="fs-sm lh-sm mb-0"><?php echo $primary_phone; ?></p>
                <p class="fs-sm lh-sm mb-0"><?php echo $primary_address_1; ?></p>
                <p class="fs-sm lh-sm mb-0"><?php echo $primary_address_2; ?></p>
                <p class="fs-sm lh-sm mb-0"><?php echo $primary_city; ?>, <?php echo $primary_state; ?>, <?php echo $primary_postcode; ?></p>
                <p class="fs-sm lh-sm mb-0"><?php echo $primary_country; ?></p>
                <p class="fs-sm lh-sm mb-0"><?php echo $primary_dob; ?></p>
                <p class="fs-sm lh-sm mb-0"><?php echo $primary_gender; ?></p>
            </div>
            <?php if ($guests && !empty($guests)) { ?>
                <div class="col-lg-6 px-0 checkout-review__col">
                    <p class="fw-medium mb-2">Guest 2: <?php echo $guests[1]['guest_fname'] . ' ' . $guests[1]['guest_lname']; ?></p>
                    <p class="fs-sm lh-sm mb-0"><?php echo $guests[1]['guest_email']; ?></p>
                    <p class="fs-sm lh-sm mb-0"><?php echo $guests[1]['guest_phone']; ?></p>
                    <p class="fs-sm lh-sm mb-0"><?php echo ($guests[1]['guest_gender'] == 2 ? 'Female' : 'Male'); ?></p>
                </div>
            <?php }  ?>
        </div>
        <?php
        $guest_html = '';
        if ($guests && sizeof($guests) > 1) {
            $iter = 0;
            $cols = 2;
            $fields_size = sizeof($guests) - 1;
            foreach ($guests as $guest_num => $guest) {
                if ($guest_num != 1) {
                    if ($iter % $cols == 0) {
                        $guest_html .= '<div class="row mx-0">';
                    }
                    $guest_html .= '<div class="col-lg-6 px-0 checkout-review__col" data-num="'.$guest_num.'" data-size="'.$fields_size.'">';
                    $guest_html .= '<p class="fw-medium mb-2">Guest ' . $guest_num + 1 . ': ' . $guest['guest_fname'] . ' ' . $guest['guest_lname'] . '</p>
                            <p class="fs-sm lh-sm mb-0">' . $guest['guest_email'] . '</p>
                            <p class="fs-sm lh-sm mb-0">' . $guest['guest_phone'] . '</p>
                            <p class="fs-sm lh-sm mb-0">' . ($guest['guest_gender'] == 2 ? 'Female' : 'Male') . '</p>';
                    $guest_html .= '</div>';
                    if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                        $guest_html .= '</div>';
                    }
                    $iter++;
                }
            }
        }
        echo $guest_html;
        ?>
        <a href="javascript:" class="btn btn-md btn-outline-primary d-lg-none d-block tt_change_checkout_step" data-step="1">Edit Guest Info</a>
    </div>
    <hr>
    <div class="checkout-review__room">
        <div class="d-flex checkout-review__title-bar">
            <h5 class="fs-xl lh-xl fw-medium d-flex align-items-center checkout-review__title">Review Room Information</h5>
            <a href="javascript:" class="btn btn-md btn-outline-primary d-lg-block d-none checkout-review__edit tt_change_checkout_step" data-step="22">Edit Room Info</a>
        </div>
        <div class="row mx-0">
            <div class="col-lg-6 px-0 checkout-review__col">
                <?php echo $tt_rooms_output['single']; ?>
            </div>
            <div class="col-lg-6 px-0 checkout-review__col">
                <?php echo $tt_rooms_output['double']; ?>
            </div>
        </div>
        <div class="row mx-0">
            <div class="col-lg-6 px-0 checkout-review__col">
                <?php echo $tt_rooms_output['roommate']; ?>
            </div>
            <div class="col-lg-6 px-0 checkout-review__col">
                <?php echo $tt_rooms_output['private']; ?>
            </div>
        </div>
        <a href="javascript:" class="btn btn-md btn-outline-primary d-lg-none d-block tt_change_checkout_step" data-step="22">Edit Room Info</a>
    </div>
    <hr>
    <div class="checkout-review__bikes">
        <div class="d-flex checkout-review__title-bar">
            <h5 class="fs-xl lh-xl fw-medium d-flex align-items-center checkout-review__title">Review Bikes & Gear Information</h5>
            <a href="javascript:" class="btn btn-md btn-outline-primary d-lg-block d-none checkout-review__edit tt_change_checkout_step" data-step="2">Edit Bikes & Gear</a>
        </div>
        <?php
        $tt_formatted = $trek_user_checkout_data['formatted'];
        //Preparing insurance HTML
        $review_bikes_arr = isset($tt_formatted[1]) && isset($tt_formatted[1]['cart_item_data']) ? $tt_formatted[1]['cart_item_data'] : [];
        $iter = 0;
        $cols = 2;
        $review_bikes_html = '';
        $fields_size = sizeof($review_bikes_arr);
        if (isset($review_bikes_arr) && !empty($review_bikes_arr)) {
            foreach ($review_bikes_arr as $review_bikes_arr_k => $review_bikes_arr_val) {
                $wheel_upgrade = 'No';
                $bikeTypeInfo = tt_ns_get_bike_type_info( $review_bikes_arr_val['bikeTypeId'] );
                if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
                    $wheel_upgrade = 'Yes';
                }
                if ($iter % $cols == 0) {
                    $review_bikes_html .= '<div class="row mx-0">';
                }
                $ownBike = '';
                $syncRiderLevels = $syncBikeSizes = $syncHeights = $syncHelmets = $syncBikeTypes = $syncPedals = $syncJerseySizes = '';
                if( isset($review_bikes_arr_val['rider_level']) && $review_bikes_arr_val['rider_level'] ){
                    $syncRiderLevels =    tt_get_custom_item_name('syncRiderLevels',$review_bikes_arr_val['rider_level']);
                }
                if( isset($review_bikes_arr_val['bike_size']) && $review_bikes_arr_val['bike_size'] ){
                    $syncBikeSizes =    tt_get_custom_item_name('syncBikeSizes',$review_bikes_arr_val['bike_size']);
                }
                if( isset($review_bikes_arr_val['rider_height']) && $review_bikes_arr_val['rider_height'] ){
                    $syncHeights =    tt_get_custom_item_name('syncHeights',$review_bikes_arr_val['rider_height']);
                }
                if( isset($review_bikes_arr_val['helmet_size']) && $review_bikes_arr_val['helmet_size'] ){
                    $syncHelmets =    tt_get_custom_item_name('syncHelmets',$review_bikes_arr_val['helmet_size']);
                }
                if( isset($review_bikes_arr_val['bikeTypeId']) && $review_bikes_arr_val['bikeTypeId'] ){
                    $syncBikeTypes =    tt_get_custom_item_name('syncBikeTypes',$review_bikes_arr_val['bikeTypeId']);
                }
                if( isset($review_bikes_arr_val['bike_pedal']) && $review_bikes_arr_val['bike_pedal'] ){
                    $syncPedals =    tt_get_custom_item_name('syncPedals',$review_bikes_arr_val['bike_pedal']);
                }
                if( isset($review_bikes_arr_val['jersey_size']) && $review_bikes_arr_val['jersey_size'] ){
                    $syncJerseySizes =    tt_get_custom_item_name('syncJerseySizes',$review_bikes_arr_val['jersey_size']);
                }
                $bike_id   = (int) $review_bikes_arr_val['bikeId'];
                $bike_name = '';
                if( ( isset($bike_id) && $bike_id ) || 0 == $bike_id ){
                    switch ( $bike_id ) {
                        case 5270: // I am bringing my own bike.
                            $bike_name = 'Bringing own';
                            break;
                        case 0: // If set to 0, it means "I don't know" was picked for bike size and the bikeTypeName property will be used.
                            $bike_name = $syncBikeTypes;
                            break;
                        default: // Take the name of the bike.
                            $bike_name = json_decode( $local_bike_models_info[ $bike_id ], true)[ 'name' ];
                            break;
                    }
                }
                if( isset($review_bikes_arr_val['own_bike']) && $review_bikes_arr_val['own_bike'] ){
                    $ownBike = $review_bikes_arr_val['own_bike'];
                }
                $guestLabel = ($review_bikes_arr_k == 0 ? 'Primary Guest' : 'Guest ' . ($review_bikes_arr_k + 1));
                $fullname = $review_bikes_arr_val['guest_fname'] . ' ' . $review_bikes_arr_val['guest_lname'];
                $review_bikes_html .= '<div class="col-lg-6 px-0 checkout-review__col">';
                $review_bikes_html .= '<p class="fw-medium mb-2">' . $guestLabel . ': ' . $fullname . '</p>
                <p class="fs-sm lh-sm mb-0">Rider Level: ' . $syncRiderLevels . '</p>';
                if ( $review_bikes_arr_val['rider_level'] != 5 ) {
                    if( !empty( $bike_name ) ){

                        $review_bikes_html .= '<p class="fs-sm lh-sm mb-0">Bike: ' . $bike_name . '</p>';
                    }
                    if( 'yes' !== $ownBike || 0 == $bike_id ){
                        $review_bikes_html .= '<p class="fs-sm lh-sm mb-0">Bike Size: ' . $syncBikeSizes . '</p>
                            <p class="fs-sm lh-sm mb-0">Rider Height: ' . $syncHeights . '</p>';
                    }
                    $review_bikes_html .= '<p class="fs-sm lh-sm mb-0">Pedals: ' . $syncPedals . '</p>
                    <p class="fs-sm lh-sm mb-0">Helmet Size: ' . $syncHelmets . '</p>';
                    if( !empty( $syncJerseySizes ) && ! is_array( $syncJerseySizes )  && '-' != $syncJerseySizes ) {
                        $review_bikes_html .= '<p class="fs-sm lh-sm mb-0">Jersey: ' . $syncJerseySizes . '</p>';
                    }
                    $review_bikes_html .= '<p class="fs-sm lh-sm mb-0">Wheel Upgrade: ' . $wheel_upgrade . '</p>';
                }
                $review_bikes_html .= '</div>';
                if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                    $review_bikes_html .= '</div>';
                }
                $iter++;
            }
        }
        echo $review_bikes_html;
        ?>
        <a href="javascript:" class="btn btn-md btn-outline-primary d-lg-none d-block tt_change_checkout_step" data-step="2">Edit Bikes & Gear</a>
    </div>
    <hr>
    <div class="checkout-review__travel">
        <div class="d-flex checkout-review__title-bar">
            <h5 class="fs-xl lh-xl fw-medium d-flex align-items-center checkout-review__title">Travel Protection Information</h5>
            <a href="javascript:" class="btn btn-md btn-outline-primary d-lg-block d-none checkout-review__edit tt_change_checkout_step" data-step="3">Edit Info</a>
        </div>
        <?php echo $guest_insurance_html; ?>
        <button type="button" class="btn btn-md btn-outline-primary d-lg-none d-block">Edit Info</button>
    </div>
    <hr>
    <div class="checkout-review__payment">
        <div class="d-flex checkout-review__title-bar">
            <h5 class="fs-xl lh-xl fw-medium d-flex align-items-center checkout-review__title">Review Payment Method</h5>
            <a href="javascript:" class="btn btn-md btn-outline-primary d-lg-block d-none checkout-review__edit tt_change_checkout_step" data-step="3">Edit Payment</a>
        </div>
        <div class="row mx-0">
            <div class="col-lg-6 px-0 checkout-review__col">
                <p class="fw-medium mb-2">Billing Method</p>
                <p class="fs-sm lh-sm mb-0"><?php echo $primary_name; ?></p>
                <p class="fs-sm lh-sm mb-0"><?php echo $cc_type; ?> <?php echo ( $cc_masked ? '****'.$cc_masked : '' ); ?></p>
                <p class="fs-sm lh-sm mb-0">Exp <?php echo $cc_exp_date; ?></p>
            </div>
            <div class="col-lg-6 px-0 checkout-review__col">
                <p class="fw-medium mb-2">Billing Address</p>
                <?php if (isset($tt_posted['is_same_billing_as_mailing']) && $tt_posted['is_same_billing_as_mailing'] == 1) { ?>
                    <p class="fs-sm lh-sm mb-0"><?php echo $primary_address_1; ?></p>
                    <p class="fs-sm lh-sm mb-0"><?php echo $primary_address_2; ?></p>
                    <p class="fs-sm lh-sm mb-0"><?php echo $primary_city; ?>, <?php echo $primary_state; ?>, <?php echo $primary_postcode; ?></p>
                    <p class="fs-sm lh-sm mb-0"><?php echo $primary_country; ?></p>
                <?php } else { ?>
                    <p class="fs-sm lh-sm mb-0"><?php echo $billing_add1; ?></p>
                    <p class="fs-sm lh-sm mb-0"><?php echo $billing_add2; ?></p>
                    <p class="fs-sm lh-sm mb-0"><?php echo $billing_city; ?>, <?php echo $billing_state; ?>, <?php echo $billing_postcode; ?></p>
                    <p class="fs-sm lh-sm mb-0"><?php echo $billing_country; ?></p>
                <?php } ?>
            </div>
        </div>
        <a href="javascript:" class="btn btn-md btn-outline-primary d-lg-none d-block tt_change_checkout_step" data-step="3">Edit Payment</a>
    </div>
</div>
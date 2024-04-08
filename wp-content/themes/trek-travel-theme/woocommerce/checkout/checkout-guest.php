<?php
global $woocommerce;
$trek_user_checkout_data =  get_trek_user_checkout_data();
$trek_user_checkout_posted = $trek_user_checkout_data['posted'];
$userInfo = wp_get_current_user();
?>
<div class="guest-checkout">
    <div class="d-flex align-items-center guest-checkout__guest-number">
        <p class="fw-medium mb-0">Number of Guests</p>
        <div class="d-flex guest-checkout__qty qty rounded-1">
            <div id="minus" class="guestCounterAction"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkout/checkout-minus.png"></div>
            <input name="no_of_guests" class="guest-checkout__guestnumber guestnumber" type="number" value="<?php echo ($trek_user_checkout_posted && isset($trek_user_checkout_posted['no_of_guests']) ? $trek_user_checkout_posted['no_of_guests'] : '1') ?>">
            <div id="plus" class="guestCounterAction"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkout/checkout-add.png"></div>
        </div>
        <div class="invalid-feedback limit-reached-feedback w-auto"><img class="invalid-icon" /> Max guests limit is reached.</div>
    </div>
    <hr>
    <p class="guest-checkout-info fs-xl lh-xl fw-medium mb-1">Primary Guest Information</p>
    <p class="guest-checkout-subinfo fs-sm lh-sm fw-normal">Please be sure to use your mailing address.</p>
    <div class="guest-checkout__primary-form">
        <?php
        $fields = $woocommerce->checkout->get_checkout_fields('shipping');
        $iter = 0;
        $field_html = '';
        $fields_size = sizeof($fields);
        $cols = 2;
        foreach ($fields as $key => $field) {
            if ($iter % $cols == 0) {
                $field_html .= '<div class="row mx-0 guest-checkout__primary-form-row">';
            }
            $field_html .= '<div class="col-md px-0 form-row"><div class="form-floating">';
            $field['placeholder'] = $field['label'];
            //$field['class'] = array('field-main-wrap');
            $field['label'] = '';
            $field['required'] = 'true';
            $field['input_class'] = array('form-control');
            $field['return'] = true;
            $woo_field_value = $woocommerce->checkout->get_value($key);
            if (isset($trek_user_checkout_posted[$key]) && !empty($trek_user_checkout_posted[$key])) {
                $woo_field_value = $trek_user_checkout_posted[$key];
            }
            $check_field_keys = [
                'shipping_first_name', 
                'shipping_last_name', 
                'shipping_email', 
                'custentity_birthdate',
                'custentity_gender',
                'shipping_phone',
                'shipping_state',
                'shipping_country'
            ];
            if ( $key && $check_field_keys && in_array( $key, $check_field_keys ) ) {
                if ( $key == 'custentity_birthdate' ) {
                    $woo_field_value = strtotime( $woo_field_value );
                    $woo_field_value = date( 'Y-m-d', $woo_field_value );
                }
                if ( $key == 'shipping_first_name' ) {
                    $woo_field_value = $userInfo->first_name;
                }
                if ( $key == 'shipping_last_name' ) {
                    $woo_field_value = $userInfo->last_name;
                }
                if ( $key == 'shipping_email' ) {
                    $woo_field_value = $userInfo->user_email;
                }
                if ( $key == 'shipping_phone' ) {
                    $guest_phone_number = get_user_meta( $userInfo->ID, 'custentity_phone_number', true );
                    
                    if ( ! empty( tt_validate( $trek_user_checkout_posted['shipping_phone'] ) ) ) {
                        $guest_phone_number = $trek_user_checkout_posted['shipping_phone'];
                    }

                    $woo_field_value = $guest_phone_number;
                }
                if ( $key == 'shipping_state' ) {
                    $field['custom_attributes']['required'] = "required";
                }
                if ( $key == 'shipping_country' ) {
                    $field['custom_attributes']['required'] = "required";
                }
            }
            if( $key != 'shipping_address_2' ){
                $field['custom_attributes']['required'] = "required";
            }
            if ( $key === 'shipping_state' ) {
                $country_val = get_user_meta( get_current_user_id(), 'shipping_country', true );
                $state_val   = get_user_meta( get_current_user_id(), 'shipping_state', true );

                if ( ! empty( tt_validate( $trek_user_checkout_posted['shipping_country'] ) ) ) {
                    $country_val = $trek_user_checkout_posted['shipping_country'];
                }

                if ( ! empty( tt_validate( $trek_user_checkout_posted['shipping_state'] ) ) ) {
                    $state_val = $trek_user_checkout_posted['shipping_state'];
                }

                $field['country'] = ! empty( $country_val ) ? $country_val : '';
                $woo_field_value  = $state_val;
            }
            if ( $key === 'shipping_country' ) {
                $country_val = get_user_meta( get_current_user_id(), 'shipping_country', true );

                if ( ! empty( tt_validate( $trek_user_checkout_posted['shipping_country'] ) ) ) {
                    $country_val = $trek_user_checkout_posted['shipping_country'];
                }

                $field['country'] = ! empty( $country_val ) ? $country_val : '';
                $woo_field_value  = $country_val;
            }
            $field_input = woocommerce_form_field($key, $field, $woo_field_value);
            $field_input = str_ireplace('<span class="woocommerce-input-wrapper">', '', $field_input);
            $field_input = str_ireplace('</span>', '', $field_input);
            $sort            = $field['priority'] ? $field['priority'] : '';
            if (isset($field['required'])) {
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
            $field_input = str_ireplace('<p class="form-row form-row-wide address-field" id="shipping_address_2_field" data-priority="26">', '', $field_input);
            $field_input = str_ireplace('<p class="form-row form-row-wide address-field validate-postcode" id="shipping_postcode_field" data-priority="90">', '', $field_input);
            $field_input = str_ireplace('</p>', '', $field_input);
            $field_html .= $field_input;
            if ($key == 'custentity_birthdate') {
                $field_html .= '<div class="invalid-feedback invalid-age dob-error"><img class="invalid-icon" /> Age must be 16 years old or above, Please enter correct date of birth.</div>';
                $field_html .= '<div class="invalid-feedback invalid-min-year dob-error"><img class="invalid-icon" /> The year must be greater than 1900, Please enter correct date of birth.</div>';
                $field_html .= '<div class="invalid-feedback invalid-max-year dob-error"><img class="invalid-icon" /> The year cannot be in the future, Please enter the correct date of birth.</div>';
            }
            if ($key != 'custentity_gender') {
                $field_html .= '<label for="shipping_' . $key . '">' . $field['placeholder'] . '</label>';
            }
            if ($key == 'custentity_gender') {
                $field_html .= '<label for="' . $key . '">' . $field['placeholder'] . '</label><div class="invalid-feedback"><img class="invalid-icon" /> Please select gender.</div>';
            }
            if ($key == 'shipping_phone') {
                $field_html .= '<div class="invalid-feedback"><img class="invalid-icon" /> Please enter valid phone number.</div>';
            }
            if ($key == 'shipping_state' || $key == 'shipping_country') {
                $field_html .= '<div class="invalid-feedback"><img class="invalid-icon" /> This field is required.</div>';
            }
            $field_html .= '</div></div>';
            if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                $field_html .= '</div>';
            }
            $iter++;
        }
        $field_html .= '<input type="hidden" name="email" value="'.$userInfo->user_email.'" required>';
        echo $field_html;
        ?>
    </div>
    <hr>
    <p class="guest-checkout-info fs-xl lh-xl fw-medium guest-infoo d-none mb-1">Guest Information</p>
    <p class="guest-checkout-subinfo fs-sm lh-sm fw-normal guest-subinfo d-none">An email will be sent to your guest to complete their info for the trip.</p>
    <div id="qytguest" class="<?php echo ($trek_user_checkout_posted && isset($trek_user_checkout_posted['guests']) ? '' : 'd-none'); ?>">
        <?php if ($trek_user_checkout_posted && isset($trek_user_checkout_posted['guests']) && !empty($trek_user_checkout_posted['guests'])) {
            foreach ($trek_user_checkout_posted['guests'] as $guest_num => $guest) {
        ?>
                <div class="guest-checkout__guests guests">
                    <p class="guest-checkout-info fs-xl lh-xl fw-medium mb-4">Guest <?php echo $guest_num + 1; ?></p>
                    <div class="row mx-0 guest-checkout__primary-form-row">
                        <div class="col-md px-0 form-row">
                            <div class="form-floating"><input type="text" name="guests[<?php echo $guest_num; ?>][guest_fname]" class="form-control tt_guest_inputs" data-validation="text" data-type="input" id="floatingInputGrid" placeholder="First Name" value="<?php echo $guest['guest_fname']; ?>" required="required"><label for="floatingInputGrid">First Name</label></div>
                        </div>
                        <div class="col-md px-0 form-row">
                            <div class="form-floating"><input type="text" name="guests[<?php echo $guest_num; ?>][guest_lname]" class="form-control tt_guest_inputs" data-validation="text" data-type="input" id="floatingInputGrid" placeholder="Last Name" value="<?php echo $guest['guest_lname']; ?>" required="required"><label for="floatingInputGrid">Last Name</label></div>
                        </div>
                    </div>
                    <div class="row mx-0 guest-checkout__primary-form-row">
                        <div class="col-md px-0 form-row">
                            <div class="form-floating">
                                <input type="email" name="guests[<?php echo $guest_num; ?>][guest_email]" class="form-control tt_guest_inputs" data-validation="email" data-type="input" id="floatingInputGrid" placeholder="Email" value="<?php echo $guest['guest_email']; ?>" required="required">
                                <label for="floatingInputGrid">Email</label>
                                <div class="invalid-feedback"><img class="invalid-icon" /> Please enter valid email address.</div>
                            </div>
                        </div>
                        <div class="col-md px-0 form-row">
                            <div class="form-floating"><input type="text" class="form-control tt_guest_inputs" data-validation="phone" data-type="input" id="floatingInputGrid" name="guests[<?php echo $guest_num; ?>][guest_phone]" placeholder="Phone" value="<?php echo $guest['guest_phone']; ?>" required="required"><label for="floatingInputGrid">Phone</label><div class="invalid-feedback"><img class="invalid-icon" /> Please enter valid phone number.</div></div>
                        </div>
                    </div>
                    <div class="row mx-0 guest-checkout__primary-form-row">
                        <div class="col-md px-0 form-row">
                            <div class="form-floating"><select class="form-select tt_guest_inputs" data-validation="text" data-type="select" name="guests[<?php echo $guest_num; ?>][guest_gender]" id="floatingSelectGrid" aria-label="Floating label select example">
                                    <option value="" <?php echo ( empty( $guest['guest_gender'] ) ? 'selected' : '' ); ?>>Select Gender</option>
                                    <option value="1" <?php if ($guest['guest_gender'] == "1") echo "selected"; ?>>Male</option>
                                    <option value="2" <?php if ($guest['guest_gender'] == "2") echo "selected"; ?>>Female</option>
                                </select><label for="floatingInputGrid">Gender</label>
                                <div class="invalid-feedback">
                                    <img class="invalid-icon" />
                                    Please select gender.
                                </div>
                            </div>
                        </div>
                        <div class="col-md px-0 form-row">
                            <div class="form-floating"><input type="date" class="form-control tt_guest_inputs" data-validation="date" data-type="date" name="guests[<?php echo $guest_num; ?>][guest_dob]" class="form-control" id="floatingInputGrid" placeholder="Date of Birth" value="<?php echo $guest['guest_dob']; ?>" required="required"><label for="floatingInputGrid">Date of Birth</label><div class="invalid-feedback"><img class="invalid-icon" /> Age must be 12 years old or above, Please enter correct date of birth.</div></div>
                        </div>
                    </div>
                    <div class="row mx-0 guest-checkout__primary-form-row pt-1">
                        <div class="col-md px-0 d-flex align-items-center guest-checkout__checkbox-gap">
                            <input type="checkbox" <?php if (isset($guest['guest_as_me_mailing']) && $guest['guest_as_me_mailing'] == "on") echo "checked"; ?> name="guests[<?php echo $guest_num; ?>][guest_as_me_mailing]" class="guest-checkout__checkbox"><label>This guest shares the same mailing address as me</label>
                        </div>
                        <hr>
                    </div>
                </div>
        <?php
            }
        }  ?>
    </div>
    <button type="button" class="btn btn-primary fw-medium float-end guest-checkout__button btn-next">Next: Rooms & Gear</button>
</div>
<div class="container">
    <!-- Modal -->
    <div class="modal fade modal-guest-change-warning" id="checkoutGuestChangeModal" tabindex="-1" aria-labelledby="tripBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">                    
                    <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i type="button" class="bi bi-x"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <p class="fw-medium fs-xl lh-xl">Are you sure?</p>
                    <p class="fw-normal fs-md lh-md">This will reset your room selections and occupant assignments. Would you like to proceed?</p>
                </div>
                <div class="modal-footer">
                    <div class="container">
                        <div class="row align-items-center">                                            
                            <div class="col text-end">                                             
                                <a href="" class="fw-medium fs-md lh-md me-4 text-decoration-none" data-bs-dismiss="modal">Cancel</a>
                                <button type="button" class="btn btn-primary reset-room-selections" data-bs-dismiss="modal">Proceed</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- / .modal-content -->
        </div><!-- / .modal-dialog -->
    </div><!-- / .modal -->
</div> <!-- / Modal .container -->
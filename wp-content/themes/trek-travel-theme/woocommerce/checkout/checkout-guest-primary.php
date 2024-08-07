<?php
/**
 * Template file for the primary guest form fields on the checkout page, step 1.
 */

global $woocommerce;
$trek_user_checkout_data   = get_trek_user_checkout_data();
$trek_user_checkout_posted = $trek_user_checkout_data['posted'];
$user_info                 = wp_get_current_user();
$fields                    = $woocommerce->checkout->get_checkout_fields('shipping');
$trip_info                 = tt_get_trip_pid_sku_from_cart();
$is_hiking_checkout        = tt_is_product_line( 'Hiking', $trip_info['sku'] );

if( $is_hiking_checkout ) :
    $bike_gears_fields = array(
        'activity_level' => array(
            'id'           => 'floatingSelectActivityLevelPrimary',
            'name'         => 'bike_gears[primary][activity_level]',
            'data_type'    => 'tt_activity_level_primary',
            'options_html' => tt_items_select_options( 'syncActivityLevel', tt_validate(  $trek_user_checkout_posted['bike_gears']['primary']['activity_level'] ) )
        ),
        'rider_height' => array(
            'id'           => 'riderHeightSelectPrimary',
            'name'         => 'bike_gears[primary][rider_height]',
            'options_html' => tt_items_select_options( 'syncHeights', tt_validate( $trek_user_checkout_posted['bike_gears']['primary']['rider_height'] ) )
        ),
        'tshirt_size' => array(
            'id'           => 'tshirtSizeSelectPrimary',
            'name'         => 'bike_gears[primary][tshirt_size]',
            'options_html' => tt_items_select_options( 'syncJerseySizes', tt_validate( $trek_user_checkout_posted['bike_gears']['primary']['tshirt_size'] ) )
        ),
    );
?>
<!-- Activity Level Select -->
<div class="checkout-bikes__rider-level">
	<p class="fw-medium fs-sm lh-sm"><?php esc_html_e( 'Activity Level', 'trek-travel-theme' ); ?>&nbsp;<i class="bi bi-info-circle pdp-rider-level"></i></p>
    <div class="row mx-0 guest-checkout__primary-form-row">
        <div class="col-md px-0 py-0 my-0 form-row">
            <div class="form-floating mb-3 col-md-6 pe-2">
                <select name="<?php echo esc_attr( $bike_gears_fields['activity_level']['name'] ); ?>" data-type="<?php echo esc_attr( $bike_gears_fields['activity_level']['data_type'] ); ?>" class="form-select tt_activity_level_select select form-control" id="<?php echo esc_attr( $bike_gears_fields['activity_level']['id'] ); ?>" aria-label="<?php esc_html_e( 'Activity Level select', 'trek-travel-theme' ); ?>" required>
                    <?php
                        print wp_kses(
                            $bike_gears_fields['activity_level']['options_html'],
                            array(
                                'option' => array(
                                    'class'      => array(),
                                    'value'      => array(),
                                    'selected'   => array(),
                                    'data-value' => array(),
                                )
                            ),						
                        );
                        ?>
                </select>
                <label for="<?php echo esc_attr( $bike_gears_fields['activity_level']['id'] ); ?>"><?php esc_html_e( 'Activity Level', 'trek-travel-theme' ); ?></label>
                <div class="invalid-feedback activity-select">
                    <img class="invalid-icon" />
                    <?php esc_html_e( 'Please select valid Activity Level.', 'trek-travel-theme' ); ?>
                </div>
            </div>
        </div>
	</div>
</div>

<?php else : ?>

<?php
// Default Cycling checkout style.
$bike_gears_fields = array(
    'rider_level' => array(
        'id'           => 'floatingSelectRiderLevelPrimary',
        'name'         => 'bike_gears[primary][rider_level]',
        'data_type'    => 'tt_rider_level_primary',
        'options_html' => tt_items_select_options( 'syncActivityLevel', tt_validate(  $trek_user_checkout_posted['bike_gears']['primary']['rider_level'] ) )
    ),
);
?>
<!-- Activity Level Select -->
<div class="checkout-bikes__rider-level">
	<p class="fw-medium fs-sm lh-xs"><?php esc_html_e( 'Activity Level', 'trek-travel-theme' ); ?>&nbsp;<i class="bi bi-info-circle pdp-rider-level"></i></p>
    <div class="row mx-0 guest-checkout__primary-form-row">
        <div class="col-md px-0 py-0 my-0 form-row">
            <div class="form-floating mb-4 col-md-6 pe-2">
                <select name="<?php echo esc_attr( $bike_gears_fields['rider_level']['name'] ); ?>" data-type="<?php echo esc_attr( $bike_gears_fields['rider_level']['data_type'] ); ?>" class="form-select tt_rider_level_select select form-control" id="<?php echo esc_attr( $bike_gears_fields['rider_level']['id'] ); ?>" aria-label="<?php esc_html_e( 'Activity level select', 'trek-travel-theme' ); ?>" required>
                    <?php
                        $bike_gears_array = $bike_gears_fields['rider_level']['options_html'];
                        if( ! $is_hiking_checkout ) {
                            $bike_gears_array = str_replace( 'Non-Hiker', 'Non-Rider', $bike_gears_fields['rider_level']['options_html'] );
                        }
                        print wp_kses(
                            $bike_gears_array,
                            array(
                                'option' => array(
                                    'class'      => array(),
                                    'value'      => array(),
                                    'selected'   => array(),
                                    'data-value' => array(),
                                )
                            ),						
                        );
                        ?>
                </select>
                <label for="<?php echo esc_attr( $bike_gears_fields['rider_level']['id'] ); ?>"><?php esc_html_e( 'Activity Level', 'trek-travel-theme' ); ?></label>
                <div class="invalid-feedback rider-select">
                    <img class="invalid-icon" />
                    <?php esc_html_e( 'Please select valid Activity Level.', 'trek-travel-theme' ); ?>
                </div>
            </div>
        </div>
	</div>
</div>

<?php endif; ?>

<p class="fw-medium fs-sm lh-sm"><?php esc_html_e( 'Guest Information', 'trek-travel-theme' ); ?></p>
<div class="guest-checkout__primary-form">
    <?php
        $iter             = 0;
        $field_html       = '';
        $fields_size      = sizeof( $fields );
        $cols             = 2;
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
        foreach( $fields as $key => $field ) :
            if( $iter % $cols == 0 ) :
                ?>
                    <div class="row mx-0 guest-checkout__primary-form-row">
                <?php
            endif;
            ?>
                <div class="col-md px-0 py-0 my-0 form-row">
                    <div class="form-floating">
                        <?php
                            $field['placeholder'] = $field['label'];
                            $field['label']       = '';
                            $field['required']    = 'true';
                            $field['input_class'] = array('form-control');
                            $field['return']      = true;
                            $woo_field_value      = $woocommerce->checkout->get_value($key);

                            if ( ! empty( tt_validate( $trek_user_checkout_posted[$key] ) ) ) {
                                $woo_field_value = $trek_user_checkout_posted[$key];
                            }

                            if ( $key && $check_field_keys && in_array( $key, $check_field_keys ) ) {
                                if ( 'custentity_birthdate' === $key ) {
                                    $woo_field_value = strtotime( $woo_field_value );
                                    $woo_field_value = ! empty( $woo_field_value ) ? date( 'Y-m-d', $woo_field_value ) : '';
                                }
                                if ( 'shipping_first_name' === $key ) {
                                    $woo_field_value = $user_info->first_name;
                                }
                                if ( 'shipping_last_name' === $key ) {
                                    $woo_field_value = $user_info->last_name;
                                }
                                if ( 'shipping_email' === $key ) {
                                    $woo_field_value = $user_info->user_email;
                                }
                                if ( 'shipping_phone' === $key ) {
                                    $guest_phone_number = get_user_meta( $user_info->ID, 'custentity_phone_number', true );
                                    
                                    if ( ! empty( tt_validate( $trek_user_checkout_posted['shipping_phone'] ) ) ) {
                                        $guest_phone_number = $trek_user_checkout_posted['shipping_phone'];
                                    }

                                    $woo_field_value = $guest_phone_number;
                                }
                                if( 'shipping_country' === $key || 'shipping_state' === $key) {
                                    $field['custom_attributes']['required'] = 'required';
                                }
                            }
                            if( 'shipping_address_2' !== $key ) {
                                $field['custom_attributes']['required'] = 'required';
                            }
                            if ( 'shipping_state' === $key ) {
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
                            if ( 'shipping_country' === $key ) {
                                $country_val = get_user_meta( get_current_user_id(), 'shipping_country', true );

                                if ( ! empty( tt_validate( $trek_user_checkout_posted['shipping_country'] ) ) ) {
                                    $country_val = $trek_user_checkout_posted['shipping_country'];
                                }

                                $field['country'] = ! empty( $country_val ) ? $country_val : '';
                                $woo_field_value  = $country_val;
                            }
                            $field_input = woocommerce_form_field( $key, $field, $woo_field_value );
                            $field_input = str_ireplace( '<span class="woocommerce-input-wrapper">', '', $field_input );
                            $field_input = str_ireplace( '</span>', '', $field_input );
                            $sort        = $field['priority'] ? $field['priority'] : '';
                            if ( isset( $field['required'] ) ) {
                                $field['class'][] = 'validate-required';
                            }
                            if ( isset( $field['validate'] ) ) {
                                foreach ( $field['validate'] as $validate_name) {
                                    $field['class'][] = 'validate-' . $validate_name . '';
                                }
                            }
                            $container_class  = isset( $field['class'] ) ? esc_attr( implode( ' ', $field['class'] ) ) : '';
                            $container_id     = esc_attr( $key ) . '_field';
                            $pfield_container = '<p class="form-row ' . $container_class . '" id="' . $container_id . '" data-priority="' . esc_attr( $sort ) . '">';
                            $field_input      = str_ireplace( $pfield_container, '', $field_input );
                            $field_input      = str_ireplace( '<p class="form-row form-row-wide address-field" id="shipping_address_2_field" data-priority="26">', '', $field_input );
                            $field_input      = str_ireplace( '<p class="form-row form-row-wide address-field validate-postcode" id="shipping_postcode_field" data-priority="90">', '', $field_input );
                            $field_input      = str_ireplace( '</p>', '', $field_input );

                            echo $field_input;

                            if( 'custentity_birthdate' === $key ) :
                                ?>
                                    <div class="invalid-feedback invalid-age dob-error">
                                        <img class="invalid-icon" />
                                        <?php esc_html_e( 'Age must be 18 years old or above, Please enter correct date of birth.', 'trek-travel-theme' ); ?>
                                    </div>
                                    <div class="invalid-feedback invalid-min-year dob-error">
                                        <img class="invalid-icon" />
                                        <?php esc_html_e( 'The year must be greater than 1900, Please enter correct date of birth.', 'trek-travel-theme' ); ?>
                                    </div>
                                    <div class="invalid-feedback invalid-max-year dob-error">
                                        <img class="invalid-icon" />
                                        <?php esc_html_e( 'The year cannot be in the future, Please enter the correct date of birth.', 'trek-travel-theme' ); ?>
                                    </div>
                                <?php
                            endif;
                            if( 'custentity_gender' !== $key ) :
                                ?>
                                    <label for="shipping_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['placeholder'] ); ?></label>
                                <?php
                            endif;
                            if( 'custentity_gender' === $key ) :
                                ?>
                                    <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['placeholder'] ); ?></label>
                                    <div class="invalid-feedback">
                                        <img class="invalid-icon" />
                                        <?php esc_html_e( 'Please select gender.', 'trek-travel-theme' ); ?>
                                    </div>
                                <?php
                            elseif( 'shipping_phone' === $key ) :
                                ?>
                                    <div class="invalid-feedback">
                                        <img class="invalid-icon" />
                                        <?php esc_html_e( 'Please enter valid phone number.', 'trek-travel-theme' ); ?>
                                    </div>
                                <?php
                            else :
                                ?>
                                    <div class="invalid-feedback">
                                        <img class="invalid-icon" />
                                        <?php esc_html_e( 'This field is required.', 'trek-travel-theme' ); ?>
                                    </div>
                                <?php
                            endif;
                            ?>
                    </div><!-- / .form-floating -->
                </div><!-- / .form-row -->
            <?php
            if( ( $iter % $cols == $cols - 1 ) || ( $iter == $fields_size - 1 ) ) :
                ?>
                    </div><!-- / .guest-checkout__primary-form-row -->
                <?php
            endif;

            $iter++;
        endforeach;
        if( $is_hiking_checkout ) :
        ?>
        <div class="row mx-0 guest-checkout__primary-form-row">
            <div class="col-md px-0 py-0 my-0 form-row">
                <!-- Guest height -->
                <div class="form-floating checkout-bikes__bike-size">
                    <select name="<?php echo esc_attr( $bike_gears_fields['rider_height']['name'] ); ?>" class="form-select" id="<?php echo esc_attr( $bike_gears_fields['rider_height']['id'] ); ?>" aria-label="<?php esc_html_e( 'Guest height select', 'trek-travel-theme' ); ?>" required >
                        <?php
                            print wp_kses(
                                $bike_gears_fields['rider_height']['options_html'],
                                array(
                                    'option' => array(
                                        'class'    => array(),
                                        'value'    => array(),
                                        'selected' => array(),
                                        'disabled' => array(),
                                    )
                                ),
                            );
                            ?>
                    </select>
                    <label for="<?php echo esc_attr( $bike_gears_fields['rider_height']['id'] ); ?>"><?php esc_html_e( 'Guest Height*', 'trek-travel-theme' ); ?></label>
                    <div class="invalid-feedback">
                        <img class="invalid-icon" />
                        <?php esc_html_e( 'This field is required.', 'trek-travel-theme' ); ?>
                    </div>
                </div>
            </div><!-- / .form-row -->
            <div class="col-md px-0 py-0 my-0 form-row">
                <!-- T-shirt size -->
                <div class="form-floating checkout-bikes__bike-size">
                    <select name="<?php echo esc_attr( $bike_gears_fields['tshirt_size']['name'] ); ?>" class="form-select" id="<?php echo esc_attr( $bike_gears_fields['tshirt_size']['id'] ); ?>" aria-label="<?php esc_html_e( 'T-Shirt size select', 'trek-travel-theme' ); ?>" required >
                        <?php
                            print wp_kses(
                                $bike_gears_fields['tshirt_size']['options_html'],
                                array(
                                    'option' => array(
                                        'class'    => array(),
                                        'value'    => array(),
                                        'selected' => array(),
                                        'disabled' => array(),
                                    )
                                ),
                            );
                            ?>
                    </select>
                    <label for="<?php echo esc_attr( $bike_gears_fields['tshirt_size']['id'] ); ?>"><?php esc_html_e( 'T-Shirt Size*', 'trek-travel-theme' ); ?></label>
                    <div class="invalid-feedback">
                        <img class="invalid-icon" />
                        <?php esc_html_e( 'This field is required.', 'trek-travel-theme' ); ?>
                    </div>
                </div>
            </div><!-- / .form-row -->
        </div><!-- / .guest-checkout__primary-form-row -->
        <?php endif; ?>
        <input type="hidden" name="email" value="<?php echo esc_html( $user_info->user_email ); ?>" required>
</div>
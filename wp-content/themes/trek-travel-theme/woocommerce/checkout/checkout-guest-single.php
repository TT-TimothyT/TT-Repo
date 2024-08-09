<?php
/**
 * Template file for a single guest adding on the checkout page, step 1.
 *
 * The $args comming as arguments from the wc_get_template_html() or wc_get_template() functions.
 */

$guest_num             = 0;
$guest                 = array();
$show_mailing_checkbox = true;
$trip_info             = tt_get_trip_pid_sku_from_cart();
$is_hiking_checkout    = tt_is_product_line( 'Hiking', $trip_info['sku'] );

if ( isset( $args ) && ! empty( $args ) ) {

    if( isset( $args['guest_num'] ) ) {
        $guest_num = esc_attr( $args['guest_num'] );
    }

    if( isset( $args['guest'] ) ) {
        $guest = $args['guest'];
    }

    if( isset( $args['show_mailing_checkbox'] ) ) {
        $show_mailing_checkbox = $args['show_mailing_checkbox'];
    }
}

if( $is_hiking_checkout ) {
    $bike_gears_fields = array(
        'activity_level' => array(
            'id'           => 'floatingSelectActivityLevelGuest' . $guest_num,
            'name'         => 'bike_gears[guests][' . $guest_num . '][activity_level]',
            'data_type'    => 'tt_activity_level_guest_' . $guest_num,
            'options_html' => tt_items_select_options( 'syncActivityLevel', tt_validate( $args['activity_level'] ) )
        ),
        'rider_height' => array(
            'id'           => 'riderHeightSelectGuest' . $guest_num,
            'name'         => 'bike_gears[guests][' . $guest_num . '][rider_height]',
            'options_html' => tt_items_select_options( 'syncHeights', tt_validate( $args['rider_height'] ) )
        ),
        'tshirt_size' => array(
            'id'           => 'tshirtSizeSelectGuest' . $guest_num,
            'name'         => 'bike_gears[guests][' . $guest_num . '][tshirt_size]',
            'options_html' => tt_items_select_options( 'syncJerseySizes', tt_validate( $args['tshirt_size'] ) )
        ),
    );
} else {
    // Default Cycling Checkout style.
    $bike_gears_fields = array(
        'rider_level' => array(
            'id'           => 'floatingSelectRiderLevelGuest' . $guest_num,
            'name'         => 'bike_gears[guests][' . $guest_num . '][rider_level]',
            'data_type'    => 'tt_rider_level_guest_' . $guest_num,
            'options_html' => tt_items_select_options( 'syncActivityLevel', tt_validate( $args['rider_level'] ) )
        ),
    );
}

?>

<div class="guest-checkout__guests guests">
    <p class="guest-checkout-info small"><?php printf( esc_html__( 'Guest %d', 'trek-travel-theme' ), esc_attr( $guest_num + 1 ) ); ?></p>
    <?php if( $is_hiking_checkout ) : ?>
        <!-- Activity Level Select -->
        <div class="checkout-bikes__rider-level">
            <p class="fw-medium fs-md lh-md"><?php esc_html_e( 'Tell us about your activity level', 'trek-travel-theme' ); ?>&nbsp;<i class="bi bi-info-circle pdp-rider-level"></i></p>
            <div class="row mx-0 guest-checkout__primary-form-row">
                <div class="col-md px-0 py-0 my-0  form-row">
                    <div class="form-floating mb-4 col-md-6 pe-2">
                        <select name="<?php echo esc_attr( $bike_gears_fields['activity_level']['name'] ); ?>" data-type="<?php echo esc_attr( $bike_gears_fields['activity_level']['data_type'] ); ?>" class="form-select tt_activity_level_select select form-control" id="<?php echo esc_attr( $bike_gears_fields['activity_level']['id'] ); ?>" aria-label="<?php esc_html_e( 'Activity Level select', 'trek-travel-theme' ); ?>" required>
                            <?php
                                $bike_gears_array = $bike_gears_fields['activity_level']['options_html'];
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
                        <label for="<?php echo esc_attr( $bike_gears_fields['activity_level']['id'] ); ?>"><?php esc_html_e( 'Tell us about your activity level', 'trek-travel-theme' ); ?></label>
                        <div class="invalid-feedback activity-select">
                            <img class="invalid-icon" />
                            <?php esc_html_e( 'Please select valid Activity Level.', 'trek-travel-theme' ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <!-- Activity Level Select -->
        <div class="checkout-bikes__rider-level">
            <p class="fw-medium fs-sm lh-xs"><?php esc_html_e( 'Tell us about your activity level', 'trek-travel-theme' ); ?>&nbsp;<i class="bi bi-info-circle pdp-rider-level"></i></p>
            <div class="row mx-0 guest-checkout__primary-form-row">
                <div class="col-md px-0 py-0 my-0 form-row">
                    <div class="form-floating mb-4 col-md-6 pe-2">
                        <select name="<?php echo esc_attr( $bike_gears_fields['rider_level']['name'] ); ?>" data-type="<?php echo esc_attr( $bike_gears_fields['rider_level']['data_type'] ); ?>" class="form-select tt_rider_level_select select form-control" id="<?php echo esc_attr( $bike_gears_fields['rider_level']['id'] ); ?>" aria-label="<?php esc_html_e( 'Rider level select', 'trek-travel-theme' ); ?>" required>
                            <?php
                                print wp_kses(
                                    str_replace( 'Non-Hiker', 'Non-Rider', $bike_gears_fields['rider_level']['options_html'] ),
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
                        <label for="<?php echo esc_attr( $bike_gears_fields['rider_level']['id'] ); ?>"><?php esc_html_e( 'Tell us about your activity level', 'trek-travel-theme' ); ?></label>
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
    <div class="row mx-0 guest-checkout__primary-form-row">
        <div class="col-md  px-0 py-0 my-0 form-row">
            <div class="form-floating">
                <input type="text" name="guests[<?php echo esc_attr( $guest_num ); ?>][guest_fname]" class="form-control tt_guest_inputs" data-validation="text" data-type="input" id="floatingInputGrid" placeholder="<?php esc_html_e( 'First Name', 'trek-travel-theme' ); ?>" value="<?php echo esc_attr( tt_validate( $guest['guest_fname'] ) ); ?>" required="required">
                <label for="floatingInputGrid"><?php esc_html_e( 'First Name', 'trek-travel-theme' ); ?></label>
                <div class="invalid-feedback">
                    <img class="invalid-icon" />
                    <?php esc_html_e( 'This field is required.', 'trek-travel-theme' ); ?>
                </div>
            </div>
        </div>
        <div class="col-md  px-0 py-0 my-0 form-row">
            <div class="form-floating">
                <input type="text" name="guests[<?php echo esc_attr( $guest_num ); ?>][guest_lname]" class="form-control tt_guest_inputs" data-validation="text" data-type="input" id="floatingInputGrid" placeholder="<?php esc_html_e( 'Last Name', 'trek-travel-theme' ); ?>" value="<?php echo esc_attr( tt_validate( $guest['guest_lname'] ) ); ?>" required="required">
                <label for="floatingInputGrid"><?php esc_html_e( 'Last Name', 'trek-travel-theme' ); ?></label>
                <div class="invalid-feedback">
                    <img class="invalid-icon" />
                    <?php esc_html_e( 'This field is required.', 'trek-travel-theme' ); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row mx-0 guest-checkout__primary-form-row">
        <div class="col-md  px-0 py-0 my-0 form-row">
            <div class="form-floating">
                <input type="email" name="guests[<?php echo esc_attr( $guest_num ); ?>][guest_email]" class="form-control tt_guest_inputs" data-validation="email" data-type="input" id="floatingInputGrid" placeholder="<?php esc_html_e( 'Email', 'trek-travel-theme' ); ?>" value="<?php echo esc_attr( tt_validate( $guest['guest_email'] ) ); ?>" required="required">
                <label for="floatingInputGrid"><?php esc_html_e( 'Email', 'trek-travel-theme' ); ?></label>
                <div class="invalid-feedback">
                    <img class="invalid-icon" /> 
                    <?php esc_html_e( 'Please enter valid email address.', 'trek-travel-theme' ); ?>
                </div>
            </div>
        </div>
        <div class="col-md  px-0 py-0 my-0 form-row">
            <div class="form-floating">
                <input type="text" class="form-control tt_guest_inputs" data-validation="phone" data-type="input" id="floatingInputGrid" name="guests[<?php echo esc_attr( $guest_num ); ?>][guest_phone]" placeholder="<?php esc_html_e( 'Phone', 'trek-travel-theme' ); ?>" value="<?php echo esc_attr( tt_validate( $guest['guest_phone'] ) ); ?>" required="required">
                <label for="floatingInputGrid"><?php esc_html_e( 'Phone', 'trek-travel-theme' ); ?></label>
                <div class="invalid-feedback">
                    <img class="invalid-icon" /> 
                    <?php esc_html_e( 'Please enter valid phone number.', 'trek-travel-theme' ); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row mx-0 guest-checkout__primary-form-row">
        <div class="col-md  px-0 py-0 my-0 form-row">
            <div class="form-floating">
                <select class="form-select tt_guest_inputs" data-validation="text" data-type="select" name="guests[<?php echo $guest_num; ?>][guest_gender]" id="floatingSelectGrid" aria-label="Floating label select example" required="required">
                    <option value="" <?php echo esc_attr( empty( tt_validate( $guest['guest_gender'] ) ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Select Gender', 'trek-travel-theme' ); ?></option>
                    <option value="1" <?php echo esc_attr( tt_validate( $guest['guest_gender'] ) == '1' ? 'selected' : '' ); ?>><?php esc_html_e( 'Male', 'trek-travel-theme' ); ?></option>
                    <option value="2" <?php echo esc_attr( tt_validate( $guest['guest_gender'] ) == '2' ? 'selected' : '' ); ?>><?php esc_html_e( 'Female', 'trek-travel-theme' ); ?></option>
                </select>
                <label for="floatingInputGrid"><?php esc_html_e( 'Gender', 'trek-travel-theme' ); ?></label>
                <div class="invalid-feedback">
                    <img class="invalid-icon" />
                    <?php esc_html_e( 'Please select gender.', 'trek-travel-theme' ); ?>
                </div>
            </div>
        </div>
        <div class="col-md  px-0 py-0 my-0 form-row">
            <div class="form-floating">
                <input type="date" class="form-control tt_guest_inputs" data-validation="date" data-type="date" name="guests[<?php echo esc_attr( $guest_num ); ?>][guest_dob]" class="form-control" id="floatingInputGrid" placeholder="<?php esc_html_e( 'Date of Birth', 'trek-travel-theme' ); ?>" value="<?php echo esc_attr( tt_validate( $guest['guest_dob'] ) ); ?>" required="required">
                <label for="floatingInputGrid"><?php esc_html_e( 'Date of Birth', 'trek-travel-theme' ); ?></label>
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
            </div>
        </div>
    </div>
    <?php if( $is_hiking_checkout ) : ?>
        <div class="row mx-0 guest-checkout__primary-form-row">
            <div class="col-md  px-0 py-0 my-0 form-row">
                <div class="form-floating">
                    <!-- Rider height -->
                    <select name="<?php echo esc_attr( $bike_gears_fields['rider_height']['name'] ); ?>" class="form-select" id="<?php echo esc_attr( $bike_gears_fields['rider_height']['id'] ); ?>" aria-label="<?php esc_html_e( 'Rider height select', 'trek-travel-theme' ); ?>" required >
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
                    <label for="<?php echo esc_attr( $bike_gears_fields['rider_height']['id'] ); ?>"><?php esc_html_e( 'Rider Height*', 'trek-travel-theme' ); ?></label>
                    <div class="invalid-feedback">
                        <img class="invalid-icon" />
                        <?php esc_html_e( 'This field is required.', 'trek-travel-theme' ); ?>
                    </div>
                </div>
            </div>
            <div class="col-md  px-0 py-0 my-0 form-row">
                <div class="form-floating">
                    <!-- T-shirt size -->
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
            </div>
        </div>
    <?php endif; ?>
    <div class="row mx-0 guest-checkout__primary-form-row pt-1">
        <?php if( $show_mailing_checkbox ) : ?>
            <div class="col-md px-0 d-flex align-items-center guest-checkout__checkbox-gap">
                <input type="checkbox" <?php if( isset( $guest['guest_as_me_mailing'] ) && $guest['guest_as_me_mailing'] == 'on' ) echo esc_attr( 'checked' ); ?> name="guests[<?php echo esc_attr( $guest_num ); ?>][guest_as_me_mailing]" class="guest-checkout__checkbox"><label><?php esc_html_e( 'This guest shares the same mailing address as me', 'trek-travel-theme' ); ?></label>
            </div>
        <?php endif; ?>
        <hr>
    </div>
</div>

<?php
/**
 * Template file for hotel rooms assignment on the checkout page, step 2.
 *
 * @uses checkout-hotel-occupant-popup.php template file for the occupant assignment popup.
 */
$trek_user_checkout_data      = get_trek_user_checkout_data();
$trek_user_checkout_posted    = $trek_user_checkout_data['posted'];
$number_of_guests             = intval( tt_validate( $trek_user_checkout_posted['no_of_guests'], 0 ) );
$room_single                  = intval( tt_validate( $trek_user_checkout_posted['single'], 0 ) );
$room_double                  = intval( tt_validate( $trek_user_checkout_posted['double'], 0 ) );
$room_private                 = intval( tt_validate( $trek_user_checkout_posted['private'], 0 ) );
$room_roommate                = intval( tt_validate( $trek_user_checkout_posted['roommate'], 0 ) );
$s_occupant_hide_show_style   = ( $room_single === 0 ? 'style="display:none;"' : 'style="display:flex;"' );
$d_occupant_hide_show_style   = ( $room_double === 0 ? 'style="display:none;"' : 'style="display:flex;"' );
$p_occupant_hide_show_style   = ( $room_private === 0 ? 'style="display:none;"' : 'style="display:flex;"' );
$r_occupant_hide_show_style   = ( $room_roommate === 0 ? 'style="display:none;"' : 'style="display:flex;"' );
$tt_rooms_output              = tt_rooms_output( $trek_user_checkout_posted, false, false );
$trip_sku                     = tt_validate( $trek_user_checkout_posted['sku'] );
$single_supplement_price      = 0;
$single_supplement_price_curr = '';

if( $trek_user_checkout_posted['product_id'] ) {
    $single_supplement_price      = get_post_meta( $trek_user_checkout_posted['product_id'], TT_WC_META_PREFIX . 'singleSupplementPrice', true );
    $single_supplement_price_curr = get_woocommerce_currency_symbol() . $single_supplement_price;
}

$is_open_to_roommate_disabled = tt_get_local_trips_detail( 'isOpenToRoommateDisabled', '', $trip_sku, true );
$is_hiking_checkout           = tt_is_product_line( 'Hiking', $trip_sku );
?>
<div class="checkout-step-two-hotel collapse multi-collapse show" id="multiCollapseExample1">
    <p class="fw-medium fs-xxl lh-xl title-poppins"><?php esc_html_e( 'Select Room & Occupants', 'trek-travel-theme' ); ?></p>
    <div class="checkout-step-two-hotel__guests-left-counter d-flex">
        <p class="fw-medium fs-xl lh-lg"><?php esc_html_e( 'Guests left to assign', 'trek-travel-theme' ); ?></p><span class="badge"><?php echo esc_attr( $number_of_guests ); ?></span>
    </div>
    <input type="hidden" name="is_open_to_roommate_disabled" value="<?php echo esc_attr( $is_open_to_roommate_disabled ); ?>" />
    <hr>
    <div class="checkout-step-two-hotel__room-options">
        <!-- Single Room -->
        <div class="checkout-step-two-hotel__one-bed">
            <p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type"><?php esc_html_e( 'Room with 1 Bed', 'trek-travel-theme' ); ?> <span class="bed-icon"></span></p>
            <div class="checkout-step-two-hotel__room-quantity">
                <p class="fw-normal fs-sm lh-sm"><?php esc_html_e( 'Select Number of Rooms', 'trek-travel-theme' ); ?></p>
                <div class="input-group mb-3">
                    <button class="btn btn-number" type="button" disabled="disabled" data-type="minus" data-field="single"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/room_counter_minus.svg' ); ?>" /></button>
                    <input type="text" name="single" class="form-control border-0 input-number" value="<?php echo esc_attr( tt_validate( $trek_user_checkout_posted['single'], 0 ) ); ?>" min="0" max="<?php echo esc_attr( floor( $number_of_guests / 2 ) ); ?>" aria-label="<?php esc_html_e( 'The Number of rooms', 'trek-travel-theme' ); ?>">
                    <button class="btn btn-number" <?php echo esc_attr(  $number_of_guests === 1  ? 'disabled="disabled"' : '' ); ?> type="button" data-type="plus" data-field="single"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/room_counter_plus.svg' ); ?>" /></button>
                </div>
            </div>
            <p class="fw-normal fs-md lh-sm mb-4 text-gray-600 checkout-step-two-hotel__room-occupancy"><?php esc_html_e( 'Double Occupancy', 'trek-travel-theme' ); ?> <i class="bi bi-info-circle pdp-double-occupancy checkout-double-occupancy"></i></p>
            <div class="checkout-step-two-hotel__add-occupants" data-room="s-assign" <?php echo wp_strip_all_tags( $s_occupant_hide_show_style ); ?>>
                <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn align-items-center mb-4 <?php echo esc_attr( $number_of_guests === 2 ? 'd-none' : '' ); ?>" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" /><?php esc_html_e( 'Assign Occupants', 'trek-travel-theme' ); ?></button>
            </div>
            <div class="checkout-step-two-hotel__edit-occupants" data-room="s" <?php echo wp_strip_all_tags( $s_occupant_hide_show_style ); ?>>
                <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn align-items-center <?php echo esc_attr( $number_of_guests === 2 ? 'd-none' : '' ); ?>" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" /><?php esc_html_e( 'Edit Occupants', 'trek-travel-theme' ); ?></button>
                <a href="javascript:" id="single" class="tt_reset_rooms"><?php esc_html_e( 'Reset', 'trek-travel-theme' ); ?></a>
            </div>
            <div class="checkout-step-two-hotel__assigned-occupants-list" id="tt-single-occupants"><?php echo $tt_rooms_output['single']; ?></div>
        </div>
        <hr>
        <!-- Double Room -->
        <div class="checkout-step-two-hotel__two-bed">
            <p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type"><?php esc_html_e( 'Room with 2 Beds', 'trek-travel-theme' ); ?> <span class="bed-icon"></span><span class="bed-icon ms-1"></span></p>
            <div class="checkout-step-two-hotel__room-quantity">
                <p class="fw-normal fs-sm lh-sm"><?php esc_html_e( 'Select Number of Rooms', 'trek-travel-theme' ); ?></p>
                <div class="input-group mb-3">
                    <button class="btn btn-number" type="button" disabled="disabled" data-type="minus" data-field="double"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_minus.svg" /></button>
                    <input type="text" name="double" class="form-control border-0 input-number" value="<?php echo esc_attr( tt_validate( $trek_user_checkout_posted['double'], 0 ) ); ?>" min="0" max="<?php echo esc_attr( floor( $number_of_guests / 2 ) ); ?>" aria-label="<?php esc_html_e( 'The Number of rooms', 'trek-travel-theme' ); ?>">
                    <button class="btn btn-number" <?php echo ( $number_of_guests === 1 ? 'disabled="disabled"' : ''); ?> type="button" data-type="plus" data-field="double"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_plus.svg" /></button>
                </div>
            </div>
            <p class="fw-normal fs-md lh-sm mb-4 text-gray-600 checkout-step-two-hotel__room-occupancy"><?php esc_html_e( 'Double Occupancy', 'trek-travel-theme' ); ?> <i class="bi bi-info-circle pdp-double-occupancy checkout-double-occupancy"></i></p>
            <div class="checkout-step-two-hotel__add-occupants" data-room="d-assign" <?php echo wp_strip_all_tags( $d_occupant_hide_show_style ); ?>>
                <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn align-items-center mb-4 <?php echo esc_attr( $number_of_guests === 2 ? 'd-none' : '' ); ?>" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/assign_occupants.svg' ); ?>" /><?php esc_html_e( 'Assign Occupants', 'trek-travel-theme' ); ?></button>
            </div>
            <div class="checkout-step-two-hotel__edit-occupants" data-room="d" <?php echo wp_strip_all_tags( $d_occupant_hide_show_style ); ?>>
                <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn align-items-center <?php echo esc_attr( $number_of_guests === 2 ? 'd-none' : '' ); ?>" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/edit_occupants.svg' ); ?>" /><?php esc_html_e( 'Edit Occupants', 'trek-travel-theme' ); ?></button>
                <a href="javascript:" id="double" class="tt_reset_rooms"><?php esc_html_e( 'Reset', 'trek-travel-theme' ); ?></a>
            </div>
            <div class="checkout-step-two-hotel__assigned-occupants-list" id="tt-double1Bed-occupants"><?php echo $tt_rooms_output['double']; ?></div>
        </div>
        <hr>
        <!-- Roommate -->
        <div class="checkout-step-two-hotel__open-to-roommate">
            <p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type"><?php esc_html_e( 'Open to a Roommate', 'trek-travel-theme' ); ?> <span class="bed-icon"></span><span class="bed-icon ms-1"></span></p>
            <div class="checkout-step-two-hotel__room-quantity">
                <p class="fw-normal fs-sm lh-sm"><?php esc_html_e( 'Select Number of Rooms', 'trek-travel-theme' ); ?></p>
                <div class="input-group mb-3">
                    <button class="btn btn-number" type="button" disabled="disabled" data-type="minus" data-field="roommate"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_minus.svg" /></button>
                    <input type="text" name="roommate" class="form-control border-0 input-number" value="<?php echo esc_attr( tt_validate( $trek_user_checkout_posted['roommate'], 0 ) ); ?>" min="0" max="<?php echo esc_attr( $number_of_guests ); ?>" aria-label="<?php esc_html_e( 'The Number of rooms', 'trek-travel-theme' ); ?>">
                    <button class="btn btn-number" <?php echo ( $is_open_to_roommate_disabled === '1' ? 'disabled="disabled"' : '' ); ?> type="button" data-type="plus" data-field="roommate"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/room_counter_plus.svg' ); ?>" /></button>
                </div>
            </div>
            <p class="fw-normal fs-md lh-sm mb-4 text-gray-600 checkout-step-two-hotel__room-occupancy"><?php esc_html_e( 'Double Occupancy', 'trek-travel-theme' ); ?> <i class="bi bi-info-circle pdp-double-occupancy checkout-double-occupancy"></i></p>
            <div class="checkout-step-two-hotel__add-occupants" data-room="r-assign" <?php echo wp_strip_all_tags( $r_occupant_hide_show_style ); ?>>
                <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn align-items-center mb-4 <?php echo esc_attr( $number_of_guests === 1 ? 'd-none' : '' ); ?>" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/assign_occupants.svg' ); ?>" /><?php esc_html_e( 'Assign Occupants', 'trek-travel-theme' ); ?></button>
            </div>
            <div class="checkout-step-two-hotel__edit-occupants" data-room="r" <?php echo wp_strip_all_tags( $r_occupant_hide_show_style ); ?>>
                <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn align-items-center <?php echo esc_attr( $number_of_guests === 1 ? 'd-none' : '' ); ?>" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/edit_occupants.svg' ); ?>" /><?php esc_html_e( 'Edit Occupants', 'trek-travel-theme' ); ?></button>
                <a href="javascript:" id="roommate" class="tt_reset_rooms"><?php esc_html_e( 'Reset', 'trek-travel-theme' ); ?></a>
            </div>
            <div class="checkout-step-two-hotel__assigned-occupants-list" id="tt-roommate-occupants"><?php echo $tt_rooms_output['roommate']; ?></div>
            <p class="fw-medium fs-lg lh-lg price mb-0">+<span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $single_supplement_price_curr; ?></span></p>
            <p class="fw-normal fs-sm lh-sm mb-0 refund-info"><?php esc_html_e( 'This will be refunded if paired with a Roomate', 'trek-travel-theme' ); ?> <i class="bi bi-info-circle open-roommate-popup"></i></p>
        </div>
        <hr>
        <!-- Private -->
        <div class="checkout-step-two-hotel__private">
            <p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type"><?php esc_html_e( 'Enjoy a room all to yourself', 'trek-travel-theme' ); ?> <span class="bed-icon"></span></p>
            <div class="checkout-step-two-hotel__room-quantity">
                <p class="fw-normal fs-sm lh-sm"><?php esc_html_e( 'Select Number of Rooms', 'trek-travel-theme' ); ?></p>
                <div class="input-group mb-3">
                    <button class="btn btn-number" type="button" disabled="disabled" data-type="minus" data-field="private"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_minus.svg" /></button>
                    <input type="text" name="private" class="form-control border-0 input-number" value="<?php echo esc_attr( tt_validate( $trek_user_checkout_posted['private'], 0 ) ); ?>" min="0" max="<?php echo esc_attr( $number_of_guests ); ?>" aria-label="<?php esc_html_e( 'The Number of rooms', 'trek-travel-theme' ); ?>">
                    <button class="btn btn-number" type="button" data-type="plus" data-field="private"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_plus.svg" /></button>
                </div>
            </div>
            <p class="fw-normal fs-md lh-sm mb-4 text-gray-600 checkout-step-two-hotel__room-occupancy"><?php esc_html_e( 'Private', 'trek-travel-theme' ); ?> <i class="bi bi-info-circle checkout-private-popup"></i></p>
            <div class="checkout-step-two-hotel__add-occupants" data-room="p-assign" <?php echo wp_strip_all_tags( $p_occupant_hide_show_style ); ?>>
                <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn align-items-center mb-4 <?php echo esc_attr( $number_of_guests === 1 ? 'd-none' : '' ); ?>" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" /><?php esc_html_e( 'Assign Occupants', 'trek-travel-theme' ); ?></button>
            </div>
            <div class="checkout-step-two-hotel__edit-occupants" data-room="p" <?php echo wp_strip_all_tags( $p_occupant_hide_show_style ); ?>>
                <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn align-items-center <?php echo esc_attr( $number_of_guests === 1 ? 'd-none' : '' ); ?>" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" /><?php esc_html_e( 'Edit Occupants', 'trek-travel-theme' ); ?></button>
                <a href="javascript:" id="private" class="tt_reset_rooms"><?php esc_html_e( 'Reset', 'trek-travel-theme' ); ?></a>
            </div>
            <div class="checkout-step-two-hotel__assigned-occupants-list" id="tt-private-occupants"><?php echo $tt_rooms_output['private']; ?></div>
            <p class="fw-medium fs-lg lh-lg price mb-0">+<span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $single_supplement_price_curr; ?></span></p>
        </div>
    </div>
    <hr>
    <div class="checkout-step-two-hotel__special-requests">
        <!-- Add a div that says "Sorry, there's a maximum limit of 250 characters for this field" -->
        <div class="bi me-3" id="room-request-notice" style="display: none;">
            <p class="fw-medium fs-md lh-md text-danger"><?php esc_html_e( "Sorry, there's a maximum limit of 250 characters for this field.", 'trek-travel-theme' ); ?></p>
        </div>
        <p class="fw-medium fs-xxl lh-xl title-poppins"><?php esc_html_e( 'Any special needs or requests?', 'trek-travel-theme' ); ?></p>
        <div class="mb-3">
            <textarea name="special_needs" class="form-control" placeholder="<?php esc_attr_e( 'Extra pillows, allergic to down, extra towels, etc.', 'trek-travel-theme' ); ?>" id="exampleFormControlTextarea1" rows="3" required><?php echo wp_unslash( esc_html( tt_validate( $trek_user_checkout_posted['special_needs'] ) ) ); ?></textarea>
        </div>
        <p class="fw-normal fs-sm lh-md "><?php esc_html_e( 'We will try our best to accommodate but cannot guarantee.', 'trek-travel-theme' ); ?></p>
    </div>
    <hr>
    <div class="checkout-step-two-hotel__next-step <?php if( $is_hiking_checkout ) : ?> d-flex justify-content-between <?php endif; ?>"  >
        <?php if( $is_hiking_checkout ) : ?>
            <button type="button" class="fw-semibold  btn btn-outline-primary border-1 border-dark btn-lg rounded-1 btn-previous tt_change_checkout_step" data-step="1"><?php esc_html_e( 'Go back', 'trek-travel-theme' ); ?></button>
            <button type="button" class="btn btn-primary btn-lg rounded-1 btn-next"><?php esc_html_e( 'Next: Payment', 'trek-travel-theme' ); ?></button>
        <?php else : ?>
            <p class="fw-medium fs-xl lh-xxl title-poppins"><?php esc_html_e( 'Select Bikes & Gear', 'trek-travel-theme' ); ?></p>
            <button type="button" class="btn btn-primary btn-lg rounded-1 btn-next"><?php esc_html_e( 'Continue to Bikes', 'trek-travel-theme' ); ?></button>
        <?php endif; ?>
    </div>
    <?php if( ! $is_hiking_checkout ) : ?>
        <hr>
        <div class="container checkout-step-two-hotel__footer-step-btn d-flex justify-content-between">
            <button type="button" class="fw-semibold  btn btn-outline-primary border-1 border-dark btn-lg rounded-1 btn-previous tt_change_checkout_step" data-step="1"><?php esc_html_e( 'Go back', 'trek-travel-theme' ); ?></button>
        </div>
    <?php endif; ?>
</div>

<div class="container checkout-hotel-modal">
    <!-- Modal -->
    <div class="modal fade modal-search-filter" id="addOccupantsModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'trek-travel-theme' ); ?>">
                        <i type="button" class="bi bi-x"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <h4 class="fw-semibold"><?php esc_html_e( 'Occupants', 'trek-travel-theme' ); ?></h4>
                    <div id="occupant-popup-inner-html">
                        <?php 
                            $checkout_hotel_occupant_popup = TREK_PATH . '/woocommerce/checkout/checkout-hotel-occupant-popup.php';

                            if( is_readable( $checkout_hotel_occupant_popup ) ) {
                                wc_get_template('woocommerce/checkout/checkout-hotel-occupant-popup.php', $trek_user_checkout_posted );
                            } else {
                                ?>
                                    <h3><?php esc_html_e( 'Step 2', 'trek-travel-theme' ); ?></h3>
                                    <p><?php esc_html_e( 'The checkout-hotel-occupant-popup.php template is missing!', 'trek-travel-theme' ); ?></p>
                                <?php
                            }
                            ?>
                    </div>
                    <div class="checkout-hotel-modal__modal-footer-btn">
                        <button type="button" id="tt-occupants-btn" class="btn btn-primary btn-lg rounded-1 w-100"><?php esc_html_e( 'Done', 'trek-travel-theme' ); ?></button>
                        <button type="button" id="tt-occupants-btn-close" data-bs-dismiss="modal" class="btn btn-secondary btn-lg rounded-1 w-100 checkout-hotel-modal__cancel-btn"><?php esc_html_e( 'Cancel', 'trek-travel-theme' ); ?></button>
                    </div>
                </div>
            </div><!-- / .modal-content -->
        </div><!-- / .modal-dialog -->
    </div><!-- / .modal -->
</div> <!-- / Modal .container -->
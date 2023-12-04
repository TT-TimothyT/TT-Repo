<?php
$trek_user_checkout_data =  get_trek_user_checkout_data();
$trek_user_checkout_posted = $trek_user_checkout_data['posted'];
$guest_left = isset($trek_user_checkout_posted['no_of_guests']) ? $trek_user_checkout_posted['no_of_guests'] : 0;
$guests = isset($trek_user_checkout_posted['guests']) ? $trek_user_checkout_posted['guests'] : array();
$guest_occupants = isset($trek_user_checkout_posted['occupants']) ? $trek_user_checkout_posted['occupants'] : array();
$number_of_guests = intval( $trek_user_checkout_posted['no_of_guests'] );
$bed_one_guest_names = $bed_two_guest_names = array();
$shipping_fname = isset($trek_user_checkout_posted['shipping_first_name']) ? $trek_user_checkout_posted['shipping_first_name'] : '';
$shipping_lname = isset($trek_user_checkout_posted['shipping_last_name']) ? $trek_user_checkout_posted['shipping_last_name'] : '';
$primary_name  = $shipping_fname . ' ' . $shipping_lname;
//$total_rooms_selected = 0;
$room_single = isset($trek_user_checkout_posted['single']) ? $trek_user_checkout_posted['single'] : 0;
$room_double = isset($trek_user_checkout_posted['double']) ? $trek_user_checkout_posted['double'] : 0;
$room_private = isset($trek_user_checkout_posted['private']) ? $trek_user_checkout_posted['private'] : 0;
$room_roommate = isset($trek_user_checkout_posted['roommate']) ? $trek_user_checkout_posted['roommate'] : 0;
$s_occupant_hide_show_style = ($room_single == 0 ? 'style="display:none;"' : 'style="display:flex;"');
$d_occupant_hide_show_style = ($room_double == 0 ? 'style="display:none;"' : 'style="display:flex;"');
$p_occupant_hide_show_style = ($room_private == 0 ? 'style="display:none;"' : 'style="display:flex;"');
$r_occupant_hide_show_style = ($room_roommate == 0 ? 'style="display:none;"' : 'style="display:flex;"');
$shipping_fname = isset($trek_user_checkout_posted['shipping_first_name']) ? $trek_user_checkout_posted['shipping_first_name']  : '';
$shipping_lname = isset($trek_user_checkout_posted['shipping_last_name']) ? $trek_user_checkout_posted['shipping_last_name']  : '';
$shipping_name = $shipping_fname . ' ' . $shipping_lname;
$tt_rooms_output = tt_rooms_output($trek_user_checkout_posted, false, false);
$trip_sku = isset($trek_user_checkout_posted['sku']) ? $trek_user_checkout_posted['sku'] : '';
$singleSupplementPrice  = 0;
$singleSupplementPriceCurr = '';
$cart         = WC()->cart->get_cart();
if ( ! empty( $cart ) ) {
    $first_cart_item = reset( $cart ); // Get the first cart item

    if ( isset( $first_cart_item['quantity'] ) ) {
        $quantity = $first_cart_item['quantity'];
    }
} else {
    $quantity = 0;
}
$trip_guests = intval( $quantity );

$private_button_plus_class  = '';
$single_button_plus_class   = '';
$double_button_plus_class   = '';
$roommate_button_plus_class = '';
if ( 1 === $trip_guests ) {
    $private_button_plus_class  .= ' btn-plus-private';
    $roommate_button_plus_class .= ' btn-plus-roommate';
}
if ( 2 === $trip_guests ) {
    $single_button_plus_class   .= ' btn-plus-single';
    $double_button_plus_class   .= ' btn-plus-double';
}

if ($trip_sku) {
    $singleSupplementPrice = tt_get_local_trips_detail('singleSupplementPrice', '', $trip_sku, true);
    $singleSupplementPriceCurr = get_woocommerce_currency_symbol() . $singleSupplementPrice;
}
?>
<div class="checkout-step-two-hotel collapse multi-collapse show" id="multiCollapseExample1">
    <p class="fw-medium fs-xl lh-xl">1. Select Room & Occupants</p>
    <div class="checkout-step-two-hotel__guests-left-counter d-flex">
        <p class="fw-medium fs-lg lh-lg">Guests left to assign</p><span class="badge"><?php echo $guest_left; ?></span>
    </div>
    <hr>
    <div class="checkout-step-two-hotel__room-options">
        <div class="checkout-step-two-hotel__one-bed">
            <p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type">Room with 1 Bed <span class="bed-icon"></span></p>
            <div class="checkout-step-two-hotel__room-quantity">
                <p class="fw-normal fs-sm lh-sm">Select Number of Rooms</p>
                <div class="input-group mb-3">
                    <button class="btn btn-number" type="button" disabled="disabled" data-type="minus" data-field="single"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_minus.svg" /></button>
                    <input type="text" name="single" class="form-control border-0 input-number" value="<?php echo (isset($trek_user_checkout_posted['single']) ? $trek_user_checkout_posted['single'] : '0'); ?>" min="0" max="<?php echo isset($trek_user_checkout_posted['no_of_guests']) ? $trek_user_checkout_posted['no_of_guests'] : 0; ?>" aria-label="Example text with two button addons">
                    <button class="btn btn-number<?php echo esc_attr( $single_button_plus_class ); ?>" <?php echo ($guest_left == 1) ? 'disabled="disabled"' : ''; ?> type="button" data-type="plus" data-field="single"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_plus.svg" /></button>
                </div>
            </div>
            <p class="fw-normal fs-sm lh-sm mb-4 text-muted checkout-step-two-hotel__room-occupancy">Double Occupancy <i class="bi bi-info-circle pdp-double-occupancy checkout-double-occupancy"></i></p>
            <?php if ( 2 === $trip_guests ) : ?>
                <div class="checkout-step-two-hotel__add-occupants" data-room="s-assign" <?php echo $s_occupant_hide_show_style; ?>>
                    <button type="button" class="d-none plus-button-single btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" />Assign Occupants</button>
                </div>
                <div class="checkout-step-two-hotel__edit-occupants" data-room="s" <?php echo $s_occupant_hide_show_style; ?>>
                    <button type="button" style="pointer-events: none; opacity: 0;" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" />Edit Occupants</button>
                    <a href="javascript:" id="single" class="tt_reset_rooms">Reset</a>
                </div>
            <?php else : ?>
                <div class="checkout-step-two-hotel__add-occupants" data-room="s-assign" <?php echo $s_occupant_hide_show_style; ?>>
                    <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" />Assign Occupants</button>
                </div>
                <div class="checkout-step-two-hotel__edit-occupants" data-room="s" <?php echo $s_occupant_hide_show_style; ?>>
                    <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" />Edit Occupants</button>
                    <a href="javascript:" id="single" class="tt_reset_rooms">Reset</a>
                </div>
            <?php endif; ?>
            <div class="checkout-step-two-hotel__assigned-occupants-list" id="tt-single-occupants"><?php echo $tt_rooms_output['single']; ?></div>
        </div>
        <hr>
        <div class="checkout-step-two-hotel__two-bed">
            <p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type">Room with 2 Beds <span class="bed-icon"></span><span class="bed-icon ms-1"></span></p>
            <div class="checkout-step-two-hotel__room-quantity">
                <p class="fw-normal fs-sm lh-sm">Select Number of Rooms</p>
                <div class="input-group mb-3">
                    <button class="btn btn-number" type="button" disabled="disabled" data-type="minus" data-field="double"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_minus.svg" /></button>
                    <input type="text" name="double" class="form-control border-0 input-number" value="<?php echo (isset($trek_user_checkout_posted['double']) ? $trek_user_checkout_posted['double'] : '0'); ?>" min="0" max="<?php echo isset($trek_user_checkout_posted['no_of_guests']) ? $trek_user_checkout_posted['no_of_guests'] : 0; ?>" aria-label="Example text with two button addons">
                    <button class="btn btn-number<?php echo esc_attr( $double_button_plus_class ); ?>" <?php echo ($guest_left  == 1 ? 'disabled="disabled"' : ''); ?> type="button" data-type="plus" data-field="double"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_plus.svg" /></button>
                </div>
            </div>
            <p class="fw-normal fs-sm lh-sm mb-4 text-muted checkout-step-two-hotel__room-occupancy">Double Occupancy <i class="bi bi-info-circle pdp-double-occupancy checkout-double-occupancy"></i></p>
            <?php if ( 2 === $trip_guests ) : ?>
                <div class="checkout-step-two-hotel__add-occupants" data-room="d-assign" <?php echo $d_occupant_hide_show_style; ?>>
                    <button type="button" class="d-none plus-button-double btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" />Assign Occupants</button>
                </div>
                <div class="checkout-step-two-hotel__edit-occupants" data-room="d" <?php echo $d_occupant_hide_show_style; ?>>
                    <button type="button" style="pointer-events: none; opacity: 0;" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" />Edit Occupants</button>
                    <a href="javascript:" id="double" class="tt_reset_rooms">Reset</a>
                </div>
            <?php else : ?>
                <div class="checkout-step-two-hotel__add-occupants" data-room="d-assign" <?php echo $d_occupant_hide_show_style; ?>>
                    <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" />Assign Occupants</button>
                </div>
                <div class="checkout-step-two-hotel__edit-occupants" data-room="d" <?php echo $d_occupant_hide_show_style; ?>>
                    <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" />Edit Occupants</button>
                    <a href="javascript:" id="double" class="tt_reset_rooms">Reset</a>
                </div>
            <?php endif; ?>
            <div class="checkout-step-two-hotel__assigned-occupants-list" id="tt-double1Bed-occupants"><?php echo $tt_rooms_output['double']; ?></div>
        </div>
        <hr>
        <div class="checkout-step-two-hotel__open-to-roommate">
            <p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type">Open to a Roommate <span class="bed-icon"></span><span class="bed-icon ms-1"></span></p>
            <div class="checkout-step-two-hotel__room-quantity">
                <p class="fw-normal fs-sm lh-sm">Select Number of Rooms</p>
                <div class="input-group mb-3">
                    <button class="btn btn-number" type="button" disabled="disabled" data-type="minus" data-field="roommate"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_minus.svg" /></button>
                    <input type="text" name="roommate" class="form-control border-0 input-number" value="<?php echo (isset($trek_user_checkout_posted['roommate']) ? $trek_user_checkout_posted['roommate'] : '0'); ?>" min="0" max="<?php echo isset($trek_user_checkout_posted['no_of_guests']) ? $trek_user_checkout_posted['no_of_guests'] : 0; ?>" aria-label="Example text with two button addons">
                    <button class="btn btn-number<?php echo esc_attr( $roommate_button_plus_class ); ?>" type="button" data-type="plus" data-field="roommate"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_plus.svg" /></button>
                </div>
            </div>
            <p class="fw-normal fs-sm lh-sm mb-4 text-muted checkout-step-two-hotel__room-occupancy">Double Occupancy <i class="bi bi-info-circle pdp-double-occupancy checkout-double-occupancy"></i></p>
            <?php if ( 1 === $trip_guests ) : ?>
                <div class="checkout-step-two-hotel__add-occupants" data-room="r-assign" <?php echo $r_occupant_hide_show_style; ?>>
                    <button type="button" class="d-none plus-button-roommate btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" />Assign Occupants</button>
                </div>
                <div class="checkout-step-two-hotel__edit-occupants" data-room="r" <?php echo $r_occupant_hide_show_style; ?>>
                    <button type="button" style="pointer-events: none; opacity: 0;" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" />Edit Occupants</button>
                    <a href="javascript:" id="roommate" class="tt_reset_rooms d-none">Reset</a>
                </div>
            <?php else : ?>
                <div class="checkout-step-two-hotel__add-occupants" data-room="r-assign" <?php echo $r_occupant_hide_show_style; ?>>
                    <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" />Assign Occupants</button>
                </div>
                <div class="checkout-step-two-hotel__edit-occupants" data-room="r" <?php echo $r_occupant_hide_show_style; ?>>
                    <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" />Edit Occupants</button>
                    <a href="javascript:" id="roommate" class="tt_reset_rooms">Reset</a>
                </div>
            <?php endif; ?>
            <div class="checkout-step-two-hotel__assigned-occupants-list" id="tt-roommate-occupants"><?php echo $tt_rooms_output['roommate']; ?></div>
            <p class="fw-medium fs-lg lh-lg price mb-0">+<span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $singleSupplementPriceCurr; ?></span></p>
            <p class="fw-normal fs-xs lh-sm mb-0 refund-info">This will be refunded if paired with a Roomate <i class="bi bi-info-circle open-roommate-popup"></i></p>
        </div>
        <hr>
        <div class="checkout-step-two-hotel__private">
            <p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type">Enjoy a room all to yourself <span class="bed-icon"></span></p>
            <div class="checkout-step-two-hotel__room-quantity">
                <p class="fw-normal fs-sm lh-sm">Select Number of Rooms</p>
                <div class="input-group mb-3">
                    <button class="btn btn-number" type="button" disabled="disabled" data-type="minus" data-field="private"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_minus.svg" /></button>
                    <input type="text" name="private" class="form-control border-0 input-number" value="<?php echo (isset($trek_user_checkout_posted['private']) ? $trek_user_checkout_posted['private'] : '0'); ?>" min="0" max="<?php echo isset($trek_user_checkout_posted['no_of_guests']) ? $trek_user_checkout_posted['no_of_guests'] : 0; ?>" aria-label="Example text with two button addons">
                    <button class="btn btn-number<?php echo esc_attr( $private_button_plus_class ); ?>" type="button" data-type="plus" data-field="private"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/room_counter_plus.svg" /></button>
                </div>
            </div>
            <p class="fw-normal fs-sm lh-sm mb-4 text-muted checkout-step-two-hotel__room-occupancy">Private <i class="bi bi-info-circle checkout-private-popup"></i></p>
            <?php if ( 1 === $trip_guests ) : ?>
                <?php $guests_per_trip = $number_of_guests; ?>
                <div class="checkout-step-two-hotel__add-occupants" data-room="p-assign" <?php echo $p_occupant_hide_show_style; ?>>
                    <button type="button" class="d-none plus-button-private btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" />Assign Occupants</button>
                </div>
                <div class="checkout-step-two-hotel__edit-occupants" data-room="p" <?php echo $p_occupant_hide_show_style; ?>>
                    <button type="button" style="pointer-events: none; opacity: 0;" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" />Edit Occupants</button>
                    <a href="javascript:" id="private" class="tt_reset_rooms d-none">Reset</a>
                </div>
            <?php else : ?>
                <div class="checkout-step-two-hotel__add-occupants" data-room="p-assign" <?php echo $p_occupant_hide_show_style; ?>>
                    <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__add-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/assign_occupants.svg" />Assign Occupants</button>
                </div>
                <div class="checkout-step-two-hotel__edit-occupants" data-room="p" <?php echo $p_occupant_hide_show_style; ?>>
                    <button type="button" class="btn btn-md rounded-1 checkout-step-two-hotel__edit-occupants-btn" id="" data-bs-toggle="modal" data-bs-target="#addOccupantsModal"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit_occupants.svg" />Edit Occupants</button>
                    <a href="javascript:" id="private" class="tt_reset_rooms">Reset</a>
                </div>
            <?php endif; ?>
            <div class="checkout-step-two-hotel__assigned-occupants-list" id="tt-private-occupants"><?php echo $tt_rooms_output['private']; ?></div>
            <p class="fw-medium fs-lg lh-lg price mb-0">+<span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $singleSupplementPriceCurr; ?></span></p>
        </div>
    </div>
    <hr>
    <div class="checkout-step-two-hotel__special-requests">
        <p class="fw-medium fs-xl lh-xl">Any special needs or requests?</p>
        <div class="mb-3">
            <textarea name="special_needs" class="form-control" placeholder="Extra pillows, allergic to down, extra towels, etc." id="exampleFormControlTextarea1" rows="3" required><?php echo (isset($trek_user_checkout_posted['special_needs']) ? $trek_user_checkout_posted['special_needs'] : ''); ?></textarea>
        </div>
        <p class="fw-normal fs-md lh-md text-muted">We will try our best to accommodate but cannot guarantee.</p>
    </div>
    <hr>
    <div class="checkout-step-two-hotel__next-step">
        <p class="fw-medium fs-xl lh-xl text-muted">2. Select Bikes & Gear</p>
        <button class="btn btn-primary btn-lg rounded-1 tt_continue_bike_click_btn" type="button">Continue to Bikes</button>
        <button class="btn btn-primary btn-lg rounded-1 tt_continue_bike_click_btn_trigger" type="button" data-bs-toggle="collapse" data-bs-target=".multi-collapse" aria-expanded="false" aria-controls="multiCollapseExample1 multiCollapseExample2" style="display: none;">Continue to Bikes</button>
    </div>
    <hr>
    <div class="container checkout-step-two-hotel__footer-step-btn d-flex justify-content-between">
        <button type="button" class="fw-semibold  btn btn-outline-primary border-1 border-dark btn-lg rounded-1 btn-previous tt_change_checkout_step" data-step="1">Go back</button>
    </div>
</div>

<div class="container checkout-hotel-modal">
    <!-- Modal -->
    <div class="modal fade modal-search-filter" id="addOccupantsModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i type="button" class="bi bi-x"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <h4 class="fw-semibold">Occupants</h4>
                    <div id="occupant-popup-inner-html">
                        <?php echo tt_occupant_selection_popup($trek_user_checkout_posted); ?>
                    </div>
                    <div class="checkout-hotel-modal__modal-footer-btn">
                        <button type="button" id="tt-occupants-btn" class="btn btn-primary btn-lg rounded-1 w-100">Done</button>
                        <button type="button" id="tt-occupants-btn-close" data-bs-dismiss="modal" class="btn btn-secondary btn-lg rounded-1 w-100 checkout-hotel-modal__cancel-btn">Cancel</button>
                    </div>
                </div>
            </div><!-- / .modal-content -->
        </div><!-- / .modal-dialog -->
    </div><!-- / .modal -->
</div> <!-- / Modal .container -->
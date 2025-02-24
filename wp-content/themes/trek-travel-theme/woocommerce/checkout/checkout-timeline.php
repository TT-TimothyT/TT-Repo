<?php
/**
 * Checkout steps, timeline indicator template on the top of the page.
 */

$trip_info             = tt_get_trip_pid_sku_from_cart();
$is_hiking_checkout    = tt_is_product_line( 'Hiking', $trip_info['sku'], $trip_info['ns_trip_Id'] );
$trip_specific_message = '';
if( $trip_info ) {
    $trip_sku   = tt_validate( $trip_info['sku'] );
    $ns_trip_Id = tt_validate( $trip_info['ns_trip_Id'] );
    if( $trip_sku && $ns_trip_Id ) {
        $trip_specific_message = tt_get_local_trips_detail( 'tripSpecificMessage', $ns_trip_Id, $trip_sku, true );
    }
}

$checkout_generic_message = get_field( 'checkout_generic_message', 'option' );

?>
<div class="checkout-timeline">
    <div class="checkout-timeline__progress-bar" id="progress-bar">
        <ul class="d-flex align-items-center justify-content-between">
            <li class="nav-item guest-info <?php echo esc_attr( isset( $_GET['step'] ) && $_GET['step'] == 1 ? 'active' : '' ); ?>" data-step-id="guest" data-step="1">
                <a class="nav-link" href="<?php echo esc_url( trek_checkout_step_link(1) ); ?>">
                    <span>Guest Info</span>
                </a>
            </li>
            <li class="nav-item rooms-gear <?php echo esc_attr( isset( $_GET['step'] ) && $_GET['step'] == 2 ? 'active' : '' ); ?>" data-step-id="rooms" data-step="2">
                <a class="nav-link" href="<?php echo esc_url( trek_checkout_step_link(2) ); ?>">
                    <span>Rooms</span>
                </a>
            </li>
            <?php if( ! $is_hiking_checkout ) : ?>
                <li class="nav-item payment <?php echo esc_attr( isset( $_GET['step'] ) && $_GET['step'] == 3 ? 'active' : '' ); ?>" data-step-id="trip-payment" data-step="3">
                    <a class="nav-link" href="<?php echo esc_url( trek_checkout_step_link(3) ); ?>">
                        <span>Gear</span>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item review <?php echo esc_attr( isset( $_GET['step'] ) && ( ( $_GET['step'] == 4 && ! $is_hiking_checkout ) || ( $_GET['step'] == 3 && $is_hiking_checkout ) ) ? 'active' : '' ); ?>" data-step-id="review" data-step="<?php echo esc_attr( $is_hiking_checkout ? 3 : 4 ) ?>">
                <a class="nav-link" href="<?php echo esc_url( trek_checkout_step_link( $is_hiking_checkout ? 3 : 4 ) ); ?>">
                    <span>Payment</span>
                </a>
            </li>
        </ul>
        <div class="checkout-timeline__progress">
            <div class="checkout-timeline__progress-bar-line d-flex justify-content-center" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
    <?php if( $checkout_generic_message ) : ?>
        <div class="d-flex align-items-center checkout-timeline__info rounded-1 mb-3">
		<svg class="info-img" width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.8125 12.5312C17.3125 13.4062 16.6875 14.5 15.6562 14.5H2.3125C1.28125 14.5 0.65625 13.4062 1.15625 12.5312L7.8125 1.15625C8.34375 0.28125 9.625 0.28125 10.125 1.15625L16.8125 12.5312ZM8.25 4.75V8.75C8.25 9.1875 8.5625 9.5 9 9.5C9.40625 9.5 9.75 9.1875 9.75 8.75V4.75C9.75 4.34375 9.40625 4 9 4C8.5625 4 8.25 4.34375 8.25 4.75ZM9 12.5C9.53125 12.5 9.96875 12.0625 9.96875 11.5312C9.96875 11 9.53125 10.5625 9 10.5625C8.4375 10.5625 8 11 8 11.5312C8 12.0625 8.4375 12.5 9 12.5Z" fill="#28AAE1"></path>
</svg>
            <p class="mb-0 fs-sm lh-sm"><?php echo wp_kses_post( $checkout_generic_message ); ?></p>
        </div>
    <?php endif; ?>
    <?php if( $trip_specific_message ) : ?>
        <div class="d-flex align-items-center checkout-timeline__warning rounded-1">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-warning.png' ); ?>">
            <p class="mb-0 fs-sm lh-sm"><?php echo wp_kses_post( $trip_specific_message ); ?></p>
        </div>
    <?php endif; ?>
</div>
<?php
/**
 * Template file for guests fields on the checkout page, step 1.
 *
 * @uses checkout-guest-primary.php - Template for the Primary Guest.
 * @uses checkout-guest-single.php  - Template for every Secondary Guest.
 */

global $woocommerce;
$trek_user_checkout_data   = get_trek_user_checkout_data();
$trek_user_checkout_posted = $trek_user_checkout_data['posted'];
$user_info                 = wp_get_current_user();
$trip_info                 = tt_get_trip_pid_sku_from_cart();

if (isset($trip_info['sku']) && strpos($trip_info['sku'], '25NC') !== false) {
    echo '<div style="padding: 15px; margin-bottom: 10px; border-radius: 8px; background-color: #28AAE1;">
            <p style="color: black; line-height: normal; font-size: 15px; margin-bottom: 0;"> 
                *Notice <br>
                We are monitoring the current conditions and assessing our Spring 2025 trip departures due to the recent devastations from Hurricane Helene. Please call for more information. Our thoughts are with those as they recover their communities.
            </p>
          </div>';
}

?>
<div class="guest-checkout">
    <div class="d-flex align-items-center guest-checkout__guest-number flex-wrap">
        <p class="fw-medium mb-0 title-poppins"><?php esc_html_e( 'Number of Guests', 'trek-travel-theme' ); ?></p>
        
        <div class="invalid-feedback limit-reached-feedback w-auto">
            <img class="invalid-icon" />
            <?php esc_html_e( 'Max guests limit is reached.', 'trek-travel-theme' ); ?>
        </div>
    </div>
	<div class="d-flex flex-wrap align-items-center guest-checkout__qty-wrap">
		<div class="d-flex guest-checkout__qty qty rounded-1">
            <div id="minus" class="guestCounterAction"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-minus.png' ); ?>"></div>
            <input name="no_of_guests" class="guest-checkout__guestnumber guestnumber" type="number" value="<?php echo esc_attr( $trek_user_checkout_posted && isset( $trek_user_checkout_posted['no_of_guests'] ) ? $trek_user_checkout_posted['no_of_guests'] : '1' ); ?>">
            <div id="plus" class="guestCounterAction"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-add.png' ); ?>"></div>
        </div>

		<div class="d-flex align-items-center checkout-timeline__info rounded-1 mb-0 animated max-online-booking-message">
			<img class="warning-img d-none" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-warning.png' ); ?>">
			<svg class="info-img" width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.8125 12.5312C17.3125 13.4062 16.6875 14.5 15.6562 14.5H2.3125C1.28125 14.5 0.65625 13.4062 1.15625 12.5312L7.8125 1.15625C8.34375 0.28125 9.625 0.28125 10.125 1.15625L16.8125 12.5312ZM8.25 4.75V8.75C8.25 9.1875 8.5625 9.5 9 9.5C9.40625 9.5 9.75 9.1875 9.75 8.75V4.75C9.75 4.34375 9.40625 4 9 4C8.5625 4 8.25 4.34375 8.25 4.75ZM9 12.5C9.53125 12.5 9.96875 12.0625 9.96875 11.5312C9.96875 11 9.53125 10.5625 9 10.5625C8.4375 10.5625 8 11 8 11.5312C8 12.0625 8.4375 12.5 9 12.5Z" fill="#28AAE1"/>
</svg>

			<p class="mb-0 fs-sm lh-sm">
				<?php
					printf(
						wp_kses(
							/* translators: %1$s: Phone number; */
							__( 'For groups larger than 4 please call us at <a href="%1$s">(866) 464-8735</a>', 'trek-travel-theme' ),
							array(
								'a' => array(
									'class'  => array(),
									'href'   => array(),
									'target' => array()
								)
							)
						),
						esc_url( 'tel:8664648735' ),
					);
					?>
			</p>
		</div>
	</div>
    <hr>
    <p class="guest-checkout-info title-poppins"><?php esc_html_e( 'Primary Guest Information', 'trek-travel-theme' ); ?></p>
    <p class="guest-checkout-subinfo"><?php esc_html_e( 'Please be sure to use your mailing address.', 'trek-travel-theme' ); ?></p>
    <?php wc_get_template('woocommerce/checkout/checkout-guest-primary.php'); ?>

    <div id="qytguest" class="<?php echo esc_attr( $trek_user_checkout_posted && isset( $trek_user_checkout_posted['guests'] ) ? '' : 'd-none' ); ?>">
		<hr>
		<p class="guest-checkout-info title-poppins"><?php esc_html_e( 'Guest Information', 'trek-travel-theme' ); ?></p>
		<p class="guest-checkout-subinfo  guest-subinfo d-none"><?php esc_html_e( 'An email will be sent to your guest to complete their info for the trip.', 'trek-travel-theme' ); ?></p>
   
		
		<?php 
            if( $trek_user_checkout_posted && isset( $trek_user_checkout_posted['guests'] ) && ! empty( $trek_user_checkout_posted['guests'] ) ) :
                foreach( $trek_user_checkout_posted['guests'] as $guest_num => $guest ) :
                    $guest_single_args_arr = array(
                        'guest'          => $guest,
                        'guest_num'      => $guest_num,
                        'activity_level' => tt_validate( $trek_user_checkout_posted['bike_gears']['guests'][$guest_num]['activity_level'] ),
                        'rider_level'    => tt_validate( $trek_user_checkout_posted['bike_gears']['guests'][$guest_num]['rider_level'] ),
                        'rider_height'   => tt_validate( $trek_user_checkout_posted['bike_gears']['guests'][$guest_num]['rider_height'] ),
                        'tshirt_size'    => tt_validate( $trek_user_checkout_posted['bike_gears']['guests'][$guest_num]['tshirt_size'] )
                    );
                    wc_get_template( 'woocommerce/checkout/checkout-guest-single.php', $guest_single_args_arr );
                endforeach;
            endif;
            ?>
    </div><!-- / #qytguest -->
    <button type="button" class="btn btn-primary fw-medium float-end guest-checkout__button btn-next"><?php esc_html_e( 'Next Step', 'trek-travel-theme' ); ?></button>
</div><!-- / .guest-checkout -->

<div class="container">
    <!-- Guest change confirmation Modal -->
    <div class="modal fade modal-guest-change-warning" id="checkoutGuestChangeModal" tabindex="-1" aria-labelledby="tripBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">                    
                    <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i type="button" class="bi bi-x"></i></span>
                </div>
                <div class="modal-body">
                    <p class="fw-medium fs-xl lh-xl"><?php esc_html_e( 'Are you sure?', 'trek-travel-theme' ); ?></p>
                    <p class="fw-normal fs-md lh-md"><?php esc_html_e( 'This will reset your room selections and occupant assignments. Would you like to proceed?', 'trek-travel-theme' ); ?></p>
                </div>
                <div class="modal-footer">
                    <div class="container">
                        <div class="row align-items-center">                                            
                            <div class="col text-end">                                             
                                <a href="#" class="fw-medium fs-md lh-md me-4 text-decoration-none" data-bs-dismiss="modal"><?php esc_html_e( 'Cancel', 'trek-travel-theme' ); ?></a>
                                <button type="button" class="btn btn-primary reset-room-selections" data-bs-dismiss="modal"><?php esc_html_e( 'Proceed', 'trek-travel-theme' ); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- / .modal-content -->
        </div><!-- / .modal-dialog -->
    </div><!-- / .modal -->
    <!-- Guest change confirmation Modal END -->
</div><!-- / .container -->

<div class="container">
	<!-- Modal -->
	<div class="modal fade modal-rider-level-warning" id="checkoutRiderLevelModal" tabindex="-1" aria-labelledby="tripBookingModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">                    
					<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						<i type="button" class="bi bi-x"></i>
					</span>
				</div>
				<div class="modal-body">
					<p class="fw-medium fs-xl lh-xl"><?php esc_html_e( 'Wait!', 'trek-travel-theme' ); ?></p>
					<p class="fw-normal fs-md lh-md">
						<?php
							printf(
								wp_kses(
									/* translators: %1$s: Rider level text; %2$s: activity levels page URL; %3$s: contact us page URL */
									__( 'Did you know this is a Level <span>%1$s</span> trip, which may not be recommended for the activity level you selected. We want to ensure this is the right trip for you. <a href="%2$s">Learn more</a> about rider levels or <a href="%3$s">contact a trip consultant</a> for any questions!', 'trek-travel-theme' ),
									array(
										'span' => array(),
										'a'    => array(
											'href' => array()
										)
									)
								),
								esc_html( tt_validate( $trip_info['parent_rider_level'] ) ),
								esc_url( home_url( '/activity-levels' ) ),
								esc_url( home_url( '/contact-us' ) )
							)
						?>
					</p>
				</div>
				<div class="modal-footer">
					<div class="container">
						<div class="row align-items-center">                                            
							<div class="col text-end">                                             
								<button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php esc_html_e( 'Proceed', 'trek-travel-theme' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div><!-- / .modal-content -->
		</div><!-- / .modal-dialog -->
	</div><!-- / .modal -->
</div> <!-- / Modal .container -->

<?php
/**
 * Template file for the bikes selection per guest on the checkout page, step 3.
 *
 * @uses checkout-bikes-single.php template for the bike selecting grid box listing.
 */

$trek_user_checkout_data =  get_trek_user_checkout_data();
$tt_posted               = $trek_user_checkout_data['posted'];
$guests                  = tt_validate( $tt_posted['guests'], array() );
$trip_info               = tt_get_trip_pid_sku_from_cart();
$tt_rooms_output         = tt_rooms_output( $tt_posted, true, true );
$is_hiking_checkout      = tt_is_product_line( 'Hiking', $trip_info['sku'], $trip_info['ns_trip_Id'] );

if( $is_hiking_checkout ) :
?>
<div class="checkout-bikes-section">
	<div class="container checkout-bikes__footer-step-btn d-flex justify-content-between mb-5">
		<button type="button" class="fw-semibold  btn btn-outline-primary border-1 border-dark btn-lg rounded-1 btn-previous tt_change_checkout_step" data-step="2"><?php esc_html_e( 'Go back', 'trek-travel-theme' ); ?></button>
		<button type="button" class="btn btn-primary btn-lg rounded-1 btn-next"><?php esc_html_e( 'Next Step', 'trek-travel-theme' ); ?></button>
	</div>
</div>
<?php
else:
?>
<div class="checkout-bikes-section">
	<div class="checkout-bikes">
		<div class="checkout-bikes__hotel-details">
			<p class="fw-medium fs-xl lh-xl title-poppins"><?php esc_html_e( 'Select Room & Occupants', 'trek-travel-theme' ); ?></p>
			<div id="tt-room-bikes-selection">
				<?php echo $tt_rooms_output; ?>
			</div>
		</div>
		<div class="checkout-bikes__edit-room-info">
			<button type="button" class="fw-semibold  btn btn-outline-primary border-1 border-dark btn-lg rounded-1 btn-previous tt_change_checkout_step" data-step="2"><?php esc_html_e( 'Edit Room Info', 'trek-travel-theme' ); ?></button>
		</div>
	</div>
	<hr>
	<p class="fw-medium fs-xxl lh-xl title-poppins"><?php esc_html_e( 'Select Bikes & Gear', 'trek-travel-theme' ); ?></p>
	<?php
		$checkout_bikes_single_template = TREK_PATH . '/woocommerce/checkout/checkout-bikes-single.php';
		if( is_readable( $checkout_bikes_single_template ) ) :
			?>
			<div class="checkout-bikes__primary-guest">
				<?php

					/**
					 * $selected_bikes_arr will be used to adjust bike size availability during the checkout process,
					 * when reloading the page at some stage.
					 */
					// Collect guests bikeTypeId and bike_size.
					$selected_bikes_arr = array_map(
						function( $guest_bike_gears ) {
							return array_intersect_key( $guest_bike_gears, array_flip( array( 'bike_size', 'bikeTypeId' ) ) );
						},
						tt_validate( $tt_posted['bike_gears']['guests'], array() )
					);

					// Add primary bikeTypeId and bike_size.
					$selected_bikes_arr[] = array(
						'bike_size'  => tt_validate( $tt_posted['bike_gears']['primary']['bike_size'] ),
						'bikeTypeId' => tt_validate( $tt_posted['bike_gears']['primary']['bikeTypeId'] ),
					);

					// Change array keys to match with the required keys in the tt_get_bikes_by_trip_info() function.
					$selected_bikes_arr = array_map( function( $bike_gears ) {
						return array(
							'bike_size_id' => $bike_gears['bike_size'],
							'bike_type_id' => $bike_gears['bikeTypeId']
						);
					}, $selected_bikes_arr );
					
					$checkout_bikes_single_primary_args = array(
						'is_primary'             => true,
						'guest_name'             => tt_validate( $tt_posted['shipping_first_name'] ) . ' ' . tt_validate( $tt_posted['shipping_last_name'] ),
						'sku'                    => $trip_info['sku'],
						'ns_trip_id'             => $trip_info['ns_trip_Id'],
						'product_id'             => $trip_info['product_id'],
						'posted_bike_id'         => isset( $tt_posted['bike_gears']['primary']['bikeId'] ) ? $tt_posted['bike_gears']['primary']['bikeId'] : ( isset( $tt_posted['bike_gears']['primary']['rider_level'] ) && '5' == $tt_posted['bike_gears']['primary']['rider_level'] ? 5257 : '' ),
						'posted_bike_type_id'    => tt_validate( $tt_posted['bike_gears']['primary']['bikeTypeId'] ),
						'rider_level'            => tt_validate( $tt_posted['bike_gears']['primary']['rider_level'] ),
						'own_bike'               => tt_validate( $tt_posted['bike_gears']['primary']['own_bike'] ),
						'bike_size'              => tt_validate( $tt_posted['bike_gears']['primary']['bike_size'] ),
						'bike_pedal'             => tt_validate( $tt_posted['bike_gears']['primary']['bike_pedal'] ),
						'rider_height'           => tt_validate( $tt_posted['bike_gears']['primary']['rider_height'] ),
						'helmet_size'            => tt_validate( $tt_posted['bike_gears']['primary']['helmet_size'] ),
						'jersey_style'           => tt_validate( $tt_posted['bike_gears']['primary']['jersey_style'] ),
						'jersey_size'            => tt_validate( $tt_posted['bike_gears']['primary']['jersey_size'] ),
						'save_preferences'       => tt_validate( $tt_posted['bike_gears']['primary']['save_preferences'] ),
						'transportation_options' => tt_validate( $tt_posted['bike_gears']['primary']['transportation_options'] ),
						'type_of_bike'           => tt_validate( $tt_posted['bike_gears']['primary']['type_of_bike'] ),
						'selected_bikes_arr'     => $selected_bikes_arr,
					);

					wc_get_template( 'woocommerce/checkout/checkout-bikes-single.php', $checkout_bikes_single_primary_args );
					?>
			</div> <!-- primary guest -->
			<hr class="invisible">
			<div class="checkout-bikes__other-guest">
				<?php
					if( $guests ) :
						foreach( $guests as $guest_num => $guest ) :

							$checkout_bikes_single_guest_args = array(
								'is_primary'             => false,
								'guest_num'              => $guest_num,
								'guest_name'             => tt_validate( $guest['guest_fname'] ) . ' ' . tt_validate( $guest['guest_lname'] ),
								'sku'                    => $trip_info['sku'],
								'ns_trip_id'             => $trip_info['ns_trip_Id'],
								'product_id'             => $trip_info['product_id'],
								'posted_bike_id'         => isset( $tt_posted['bike_gears']['guests'][$guest_num]['bikeId'] ) ? $tt_posted['bike_gears']['guests'][$guest_num]['bikeId'] : ( isset( $tt_posted['bike_gears']['guests'][$guest_num]['rider_level'] ) && '5' == $tt_posted['bike_gears']['guests'][$guest_num]['rider_level'] ? 5257 : '' ),
								'posted_bike_type_id'    => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['bikeTypeId'] ),
								'rider_level'            => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['rider_level'] ),
								'own_bike'               => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['own_bike'] ),
								'bike_size'              => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['bike_size'] ),
								'bike_pedal'             => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['bike_pedal'] ),
								'rider_height'           => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['rider_height'] ),
								'helmet_size'            => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['helmet_size'] ),
								'jersey_style'           => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['jersey_style'] ),
								'jersey_size'            => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['jersey_size'] ),
								'transportation_options' => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['transportation_options'] ),
								'type_of_bike'           => tt_validate( $tt_posted['bike_gears']['guests'][$guest_num]['type_of_bike'] ),
								'selected_bikes_arr'     => $selected_bikes_arr,
							);

							wc_get_template( 'woocommerce/checkout/checkout-bikes-single.php', $checkout_bikes_single_guest_args );
						endforeach;
					endif;
					?>
			</div> <!-- other guest -->
	<?php else : ?>
		<h3><?php esc_html_e( 'Step 3', 'trek-travel-theme' ); ?></h3>
		<p><?php esc_html_e( 'checkout-bikes-single.php template is missing!', 'trek-travel-theme' ); ?></p>
	<?php endif; ?>
	<hr>
	<div class="container checkout-bikes__footer-step-btn d-flex justify-content-between mb-5">
		<button type="button" class="fw-semibold  btn btn-outline-primary border-1 border-dark btn-lg rounded-1 btn-previous tt_change_checkout_step" data-step="2"><?php esc_html_e( 'Go back', 'trek-travel-theme' ); ?></button>
		<button type="button" class="btn btn-primary btn-lg rounded-1 btn-next"><?php esc_html_e( 'Next Step', 'trek-travel-theme' ); ?></button>
	</div>
</div>

<div class="container">
	<!-- Modal -->
	<div class="modal fade modal-own-bike-warning" data-bs-backdrop='static' id="checkoutOwnBikeModal" tabindex="-1" aria-labelledby="tripBookingModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">                    
					<span type="button" class="btn-close invisible" data-bs-dismiss="modal" aria-label="Close">
						<i type="button" class="bi bi-x"></i>
					</span>
				</div>
				<div class="modal-body">
					<p class="fw-medium fs-xl lh-xl"><?php esc_html_e( 'Heads up!', 'trek-travel-theme' ); ?></p>
					<p class="fw-normal fs-md lh-md">
						<?php esc_html_e( 'You are welcome to bring your own bike once approved by our team. Please note that we cannot assume responsibility for the safety of or damage to your personal bike. You are responsible for the maintenance of your bike and any spare parts that may be used during the trip. You are also responsible for assembling your bike upon arrival and disassembling it upon departure as well as any and all shipping arrangements needed to get your bike to and from the trip. There is no trip price difference for bringing your own bike.', 'trek-travel-theme' ); ?>
					</p>
					<div class="form-check">
						<input name="bring_own_bike_confirmation" class="form-check-input checkout-bikes__own-bike-confirmation-check shadow-none" type="checkbox" value="yes" id="bringOwnBikeConfirmation">
						<label class="form-check-label fw-medium fs-md lh-md" for="bringOwnBikeConfirmation">
							<?php  esc_html_e( 'I have read and agree', 'trek-travel-theme' ) ?>
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<div class="container">
						<div class="row align-items-center">
							<div class="col text-end">
								<button type="button" class="btn btn-primary proceed-btn" data-bs-dismiss="modal" disabled><?php esc_html_e( 'Proceed', 'trek-travel-theme' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div><!-- / .modal-content -->
		</div><!-- / .modal-dialog -->
	</div><!-- / .modal -->
</div> <!-- / Modal .container -->

<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		const ownBikeCheckbox = document.querySelector('.tt_my_own_bike_checkbox');
		const bikeUpgradeContainers = document.querySelectorAll('.checkout-bikes__price-upgrade');
		const bikeElements = document.querySelectorAll('.checkout-bikes__bike');

		ownBikeCheckbox.addEventListener('change', function() {
			const displayValue = this.checked ? 'none' : 'block';
			bikeUpgradeContainers.forEach(upgradeContainer => {
				upgradeContainer.style.display = displayValue;
			});
		});

		bikeElements.forEach(bikeElement => {
			bikeElement.addEventListener('click', function() {
				this.classList.toggle('bike-selected');
			});
		});
	});
</script>

<?php endif; ?>

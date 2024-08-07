<?php
/**
 * Template file for guests review on the checkout page, step 4.
 */

global $woocommerce;
?>
<div class="checkout-review" id="checkout-review">
	<div class="accordion accordion-flush" id="accordionFlushReviewInfo">
		<div class="accordion-item border rounded p-3">
			<h2 class="accordion-header lh-md" id="flush-heading-reviewInfo">
				<button class="accordion-button px-0 collapsed shadow-none title-poppins" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-reviewInfo" aria-expanded="false" aria-controls="flush-collapse-reviewInfo">
					<?php esc_html_e( 'Review Information', 'trek-travel-theme' ) ?>
				</button>
			</h2>
			<div id="flush-collapse-reviewInfo" class="accordion-collapse collapse" aria-labelledby="flush-heading-reviewInfo">
				<div class="accordion-body px-0 pb-sm-0">
					<?php
					
						$checkout_review_guest = __DIR__ . '/checkout-reviews-guest-single.php';

						if( is_readable( $checkout_review_guest ) ) {
							$trek_user_checkout_data =  get_trek_user_checkout_data();
							$tt_posted               = $trek_user_checkout_data['posted'];
							$tt_formatted            = $trek_user_checkout_data['formatted'];
							$own_bike_id             = 5270;
							$non_rider_bike_id       = 5257;
							$trip_info               = tt_get_trip_pid_sku_from_cart();
							$is_hiking_checkout      = tt_is_product_line( 'Hiking', $trip_info['sku'] );
							?>
								<div class="checkout-review__guest mb-sm-0">
									<?php
										$raw_city_state_code = array(
											tt_validate( $tt_posted['shipping_city'] ),
											tt_validate( WC()->countries->get_states( tt_validate( $tt_posted['shipping_country'] ) )[tt_validate( $tt_posted['shipping_state'] )], tt_validate( $tt_posted['shipping_state'] ) ),
											tt_validate( $tt_posted['shipping_postcode'] ),
										);
										$city_state_code = '';
										foreach( $raw_city_state_code as $index => $value ) {
											if( ! empty( $value ) ) {
												if( 0 < $index && ! empty( $city_state_code ) ) {
													$city_state_code .= ', ';
												}
												$city_state_code .= $value;
											}
										}

										$transportation_options  = array(
											''             => '',
											'hard case'    => 'Hard Case',
											'soft case'    => 'Soft Case',
											'shipping'     => 'Shipping',
											'i am driving' => "I'm driving"
										);
										
										$review_bikes_arr = tt_validate( $tt_formatted[1]['cart_item_data'], [] );

										$review_bikes_arr_primary = $review_bikes_arr[0];

										$checkout_review_guest_primary_args = array(
											'is_non_rider' => $non_rider_bike_id === (int) tt_validate( $review_bikes_arr_primary['bikeId'] ),
											'is_primary'   => true,
											'fullname'     => tt_validate( $review_bikes_arr_primary['guest_fname'] ) . ' ' . tt_validate( $review_bikes_arr_primary['guest_lname'] ),
											'room_type'    => tt_get_room_type_by_guest_index( 0, $tt_posted['occupants'] ),
											'guest_info'   => array(
												'email'           => tt_validate( $tt_posted['email'] ),
												'phone'           => tt_validate( $tt_posted['shipping_phone'] ),
												'addr_1'          => tt_validate( $tt_posted['shipping_address_1'] ),
												'addr_2'          => tt_validate( $tt_posted['shipping_address_2'] ),
												'city_state_code' => $city_state_code,
												'country'         => WC()->countries->countries[tt_validate( $tt_posted['shipping_country'] )],
												'dob'             => tt_validate( $tt_posted['custentity_birthdate'] ),
												'gender'          => 2 === (int) tt_validate( $tt_posted['custentity_gender'] ) ? 'Female' : ( 1 === (int) tt_validate( $tt_posted['custentity_gender'] ) ? 'Male' : '' )

											),
											'bike_info'    => array(
												'Rider Level:'            => tt_get_custom_item_name( 'syncRiderLevels', tt_validate( $review_bikes_arr_primary['rider_level'] ) ),
												'Activity Level:'         => tt_get_custom_item_name( 'syncActivityLevel', tt_validate( $review_bikes_arr_primary['activity_level'] ) ), // H&W.
												'Bike:'                   => ! in_array( (int) tt_validate( $review_bikes_arr_primary['bikeId'] ), array( $own_bike_id, $non_rider_bike_id ) ) ? tt_validate( tt_get_custom_item_name('ns_bikeType_info' )[ tt_validate( array_search( tt_validate( $review_bikes_arr_primary['bikeTypeId'] ), array_column( tt_get_custom_item_name( 'ns_bikeType_info' ), 'id' ) ) ) ]['name'] ) : ( $own_bike_id === (int) tt_validate( $review_bikes_arr_primary['bikeId'] ) ? 'Bringing own' : '' ),
												'Bike Size:'              => tt_get_custom_item_name( 'syncBikeSizes', tt_validate( $review_bikes_arr_primary['bike_size'] ) ),
												'Transportation Options:' => $transportation_options[ tt_validate( $review_bikes_arr_primary['transportation_options'] ) ], // If selected Own Bike.
												'Type of bike:'           => tt_validate( $review_bikes_arr_primary['type_of_bike'] ), // If selected Own Bike.
												'Rider Height:'           => tt_get_custom_item_name( 'syncHeights', tt_validate( $review_bikes_arr_primary['rider_height'] ) ),
												'Pedals:'                 => tt_get_custom_item_name( 'syncPedals', tt_validate( $review_bikes_arr_primary['bike_pedal'] ) ),
												'Helmet Size:'            => tt_get_custom_item_name( 'syncHelmets', tt_validate( $review_bikes_arr_primary['helmet_size'] ) ),
												'Jersey:'                 => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_primary['jersey_size'] ) ),
												'Wheel Upgrade:'          => ! $is_hiking_checkout ? 1 === (int) tt_validate( tt_ns_get_bike_type_info( tt_validate( $review_bikes_arr_primary['bikeTypeId'] ) )['isBikeUpgrade'], 0 ) && ! in_array( (int) tt_validate( $review_bikes_arr_primary['bikeId'] ), array( $own_bike_id, $non_rider_bike_id ) ) ? 'Yes' : 'No' : '', // Bike upgrade is not applicable for Non-rider and Bring own bike!
												'T-Shirt Size:'           => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_primary['tshirt_size'] ) ), // H&W.
											)
										);

										wc_get_template( 'woocommerce/checkout/checkout-reviews-guest-single.php', $checkout_review_guest_primary_args );

										$guests = tt_validate( $tt_posted['guests'], [] );

										if( $guests ) {
											foreach ( $guests as $guest_num => $guest ) {
												$review_bikes_arr_guest = $review_bikes_arr[$guest_num];
												$checkout_review_guest_args = array(
													'is_non_rider' => $non_rider_bike_id === (int) tt_validate( $review_bikes_arr_guest['bikeId'] ),
													'is_primary'   => false,
													'guest_num'    => $guest_num,
													'fullname'     => tt_validate( $review_bikes_arr_guest['guest_fname'] ) . ' ' . tt_validate( $review_bikes_arr_guest['guest_lname'] ),
													'room_type'    => tt_get_room_type_by_guest_index( $guest_num, $tt_posted['occupants'] ),
													'guest_info'   => array(
														'email'           => tt_validate( $review_bikes_arr_guest['guest_email'] ),
														'phone'           => tt_validate( $review_bikes_arr_guest['guest_phone'] ),
														'dob'             => tt_validate( $review_bikes_arr_guest['guest_dob'] ),
														'gender'          => 2 === (int) tt_validate( $review_bikes_arr_guest['guest_gender'] ) ? 'Female' : ( 1 === (int) tt_validate( $review_bikes_arr_guest['guest_gender'] ) ? 'Male' : '' )
													),
													'bike_info'    => array(
														'Rider Level:'            => tt_get_custom_item_name( 'syncRiderLevels', tt_validate( $review_bikes_arr_guest['rider_level'] ) ),
														'Activity Level:'         => tt_get_custom_item_name( 'syncActivityLevel', tt_validate( $review_bikes_arr_guest['activity_level'] ) ), // H&W.
														'Bike:'                   => ! in_array( (int) tt_validate( $review_bikes_arr_guest['bikeId'] ), array( $own_bike_id, $non_rider_bike_id ) ) ? tt_validate( tt_get_custom_item_name('ns_bikeType_info' )[ tt_validate( array_search( tt_validate( $review_bikes_arr_guest['bikeTypeId'] ), array_column( tt_get_custom_item_name( 'ns_bikeType_info' ), 'id' ) ) ) ]['name'] ) : ( $own_bike_id === (int) tt_validate( $review_bikes_arr_guest['bikeId'] ) ? 'Bringing own' : '' ),
														'Bike Size:'              => tt_get_custom_item_name( 'syncBikeSizes', tt_validate( $review_bikes_arr_guest['bike_size'] ) ),
														'Transportation Options:' => $transportation_options[ tt_validate( $review_bikes_arr_guest['transportation_options'] ) ], // If selected Own Bike.
														'Type of bike:'           => tt_validate( $review_bikes_arr_guest['type_of_bike'] ), // If selected Own Bike.
														'Rider Height:'           => tt_get_custom_item_name( 'syncHeights', tt_validate( $review_bikes_arr_guest['rider_height'] ) ),
														'Pedals:'                 => tt_get_custom_item_name( 'syncPedals', tt_validate( $review_bikes_arr_guest['bike_pedal'] ) ),
														'Helmet Size:'            => tt_get_custom_item_name( 'syncHelmets', tt_validate( $review_bikes_arr_guest['helmet_size'] ) ),
														'Jersey:'                 => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_guest['jersey_size'] ) ),
														'Wheel Upgrade:'          => ! $is_hiking_checkout ? 1 === (int) tt_validate( tt_ns_get_bike_type_info( tt_validate( $review_bikes_arr_guest['bikeTypeId'] ) )['isBikeUpgrade'], 0 ) && ! in_array( (int) tt_validate( $review_bikes_arr_guest['bikeId'] ), array( $own_bike_id, $non_rider_bike_id ) ) ? 'Yes' : 'No' : '', // Bike upgrade is not applicable for Non-rider and Bring own bike!
														'T-Shirt Size:'           => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_guest['tshirt_size'] ) ), // H&W.
													)
												);
						
												wc_get_template( 'woocommerce/checkout/checkout-reviews-guest-single.php', $checkout_review_guest_args );
											}
										}
										?>
								</div><!-- .checkout-review__guest -->
							<?php
						} else {
							?>
								<h3><?php esc_html_e( 'Step 4', 'trek-travel-theme' ); ?></h3>
								<p><?php esc_html_e( 'Checkout reviews single guest template is missing!', 'trek-travel-theme' ); ?></p>
							<?php
						}
						?>
				</div>
			</div>
		</div>
	</div>
</div>

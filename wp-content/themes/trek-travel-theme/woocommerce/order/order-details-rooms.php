<?php
/**
 * Template file for a room details review, Thank you page.
 *
 * The $args comming as arguments from the wc_get_template_html() or wc_get_template() functions.
 */

if ( ! empty( $args['occupants'] ) ) {
	$rooms_header_arr = array(
		'single'   => __( 'Room with 1 Beds', 'trek-travel-theme' ),
		'double'   => __( 'Room with 2 Beds', 'trek-travel-theme' ),
		'private'  => __( 'Private room', 'trek-travel-theme' ),
		'roommate' => __( 'Roommate', 'trek-travel-theme' ),
	);

	foreach ( $args['occupants'] as $room_type => $rooms_info ) {
		// Continue with the next iteration if there are no rooms info.
		if( ! $rooms_info || ! is_array( $rooms_info ) ) {
			continue;
		}

		// Populate the guest names.
		$guest_names = array();

		foreach ( $rooms_info as $room_count => $guest_id ) {
			if ( $guest_id == 0 ) {
				// Primary guest name.
				$guest_names[] = $args['primary_guest_name'];
			} else {
				if ( $guest_id && 'none' !== $guest_id ) {
					// Secondary guest name.
					$guest_names[] = trim( tt_validate( $args['guests'][$guest_id]['guest_fname'] ) . ' ' . tt_validate( $args['guests'][$guest_id]['guest_lname'] ) );
				}
			}
		}

		// Continue with the next iteration if there are no guests.
		if( empty( $guest_names ) ) {
			continue;
		}

		?>
			<div class="checkout-bikes__selected-rooms">
				<p class="mb-2 fs-md lh-sm fw-bold"><?php echo esc_html( $rooms_header_arr[$room_type] ); ?></p>
				<?php
					// More than one room of this type was selected.
					if ( $args[$room_type] > 1 ) {
						$rooms_arr = array_chunk( $guest_names, $args[$room_type] );
						if ( $rooms_arr ) :
							foreach( $rooms_arr as $room_index => $single_room ) :
								?>
									<ul class="mb-0">
										<li class="fw-normal order-details__text"><?php esc_html_e( 'Room', 'trek-travel-theme' ); ?> <?php echo esc_attr( $room_index + 1 ); ?> - <?php echo esc_html( ( implode( ', ', $single_room ) ) ); ?></li>
									</ul>
								<?php
							endforeach;
						endif;
					} else {
						// Only one room of this type was selected.
						?>
							<ul class="mb-0">
								<li class="fw-normal order-details__text"><?php esc_html_e( 'Room 1', 'trek-travel-theme' ); ?> - <?php echo esc_html( ( implode( ', ', $guest_names ) ) ); ?></li>
							</ul>
						<?php
					}
					?>
			</div>
		<?php
	}
}
?>

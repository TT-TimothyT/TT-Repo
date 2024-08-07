<?php
/**
 * Template file for Occupancy selection popup, step 2.
 */

$has_occupants = false;
$single        = intval( tt_validate( $args['single'], 0 ) );
$double        = intval( tt_validate( $args['double'], 0 ) );
$private       = intval( tt_validate( $args['private'], 0 ) );
$roommate      = intval( tt_validate( $args['roommate'], 0 ) );
$no_of_guests  = intval( tt_validate( $args['no_of_guests'], 0 ) );

if( $single ) :
	$iter        = 0;
	$cols        = 2;
	$fields_size = $single - 1;
	for( $i = 0; $i < ( $single * 2 ); $i++ ) :
		if( 0 === $iter % $cols) :
			?>
				<div class="checkout-hotel-modal__occupants-selection">
					<p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type"><?php esc_html_e( 'Room with 1 Beds', 'trek-travel-theme' ); ?> <span class="bed-icon"></span></p>
					<p class="fw-medium fs-md lh-md"><?php esc_html_e( 'Who will be in this room?', 'trek-travel-theme' ); ?></p>
			<?php
		endif;
		$single_selected = isset( $args['occupants']['single'][$i] ) ? $args['occupants']['single'][$i] : 'none';
		if( 0 < $no_of_guests && 2 === $no_of_guests && 1 === $single && 'none' === $single_selected ) {
			// Auto assign occupants.
			$single_selected = $i;
		}
		?>
					<div class="form-floating">
						<select required="required" name="occupants[single][<?php echo esc_attr( $i ); ?>]" class="form-select" id="floatingSelectSingle" aria-label="Single room occupants select">
							<?php 
							print wp_kses(
								trek_occupants_options( $single_selected ),
								array(
									'option' => array(
										'class'    => array(),
										'value'    => array(),
										'selected' => array(),
									)
								),						
							);
							?>
						</select>
						<label for="floatingSelectSingle"><?php esc_html_e( 'Select Occupant', 'trek-travel-theme' ); ?></label>
					</div>
		<?php
		if( ( $iter % $cols == $cols - 1 ) || ( $iter == $fields_size - 1 ) ) :
			?>
				</div>
			<?php
		endif;
		$iter++;
	endfor;
	$has_occupants = true;
endif;

if( $double ) :
	$iter        = 0;
	$cols        = 2;
	$fields_size = $double - 1;
	for( $i = 0; $i < ( $double * 2 ); $i++ ) :
		if ( 0 === $iter % $cols ) :
			?>
				<div class="checkout-hotel-modal__occupants-selection">
					<p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type"><?php esc_html_e( 'Room with 2 Beds', 'trek-travel-theme' ); ?> <span class="bed-icon"></span><span class="bed-icon ms-1"></span></p>
					<p class="fw-medium fs-md lh-md"><?php esc_html_e( 'Who will be in this room?', 'trek-travel-theme' ); ?></p>
			<?php
		endif;
		$double_selected = isset( $args['occupants']['double'][$i] ) ? $args['occupants']['double'][$i] : 'none';
		if( 0 < $no_of_guests && 2 === $no_of_guests && 1 === $double && 'none' === $double_selected ) {
			// Auto assign occupants.
			$double_selected = $i;
		}
		?>
					<div class="form-floating">
						<select required="required" name="occupants[double][<?php echo esc_attr( $i ); ?>]" class="form-select" id="floatingSelectdouble" aria-label="Double room occupants select">
							<?php 
							print wp_kses(
								trek_occupants_options( $double_selected ),
								array(
									'option' => array(
										'class'    => array(),
										'value'    => array(),
										'selected' => array(),
									)
								),						
							);
							?>
						</select>
						<label for="floatingSelectdouble"><?php esc_html_e( 'Select Occupant', 'trek-travel-theme' ); ?></label>
					</div>
		<?php
		if( ( $iter % $cols == $cols - 1 ) || ( $iter == $fields_size - 1 ) ) :
			?>
				</div>
			<?php
		endif;
		$iter++;
	endfor;
	$has_occupants = true;
endif;

if( $private ) :
	for( $i = 0; $i < $private ; $i++ ) :
		$private_selected = isset( $args['occupants']['private'][$i] ) ? $args['occupants']['private'][$i] : 'none';
		if( 0 < $no_of_guests && 1 === $no_of_guests && 1 === $private && 'none' === $private_selected ) {
			// Auto assign occupants.
			$private_selected = $i;
		}
		?>
			<div class="checkout-hotel-modal__occupants-selection">
				<p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type"><?php esc_html_e( 'Private', 'trek-travel-theme' ); ?> <span class="bed-icon"></span></p>
				<p class="fw-medium fs-md lh-md"><?php esc_html_e( 'Who will be in this room?', 'trek-travel-theme' ); ?></p>
				<div class="form-floating">
					<select required="required" name="occupants[private][<?php echo esc_attr( $i ); ?>]" class="form-select" id="floatingSelectprivate" aria-label="Private room occupants select">
						<?php 
							print wp_kses(
								trek_occupants_options( $private_selected ),
								array(
									'option' => array(
										'class'    => array(),
										'value'    => array(),
										'selected' => array(),
									)
								),						
							);
							?>
					</select>
					<label for="floatingSelectprivate"><?php esc_html_e( 'Select Occupant', 'trek-travel-theme' ); ?></label>
				</div>
			</div>
		<?php
	endfor;
	$has_occupants = true;
endif;

if( $roommate ) :
	for( $i = 0; $i < $roommate; $i++ ) :
		$roommate_selected = isset( $args['occupants']['roommate'][$i] ) ? $args['occupants']['roommate'][$i] : 'none';
		if( 0 < $no_of_guests && 1 === $no_of_guests && 1 === $roommate && 'none' === $roommate_selected ) {
			// Auto assign occupants.
			$roommate_selected = $i;
		}
		?>
			<div class="checkout-hotel-modal__occupants-selection">
				<p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type"><?php esc_html_e( 'Open to Roommate', 'trek-travel-theme' ); ?> <span class="bed-icon"></span><span class="bed-icon ms-1"></span></p>
				<p class="fw-medium fs-md lh-md"><?php esc_html_e( 'Who will be in this room?', 'trek-travel-theme' ); ?></p>
				<div class="form-floating">
					<select required="required" name="occupants[roommate][<?php echo esc_attr( $i ); ?>]" class="form-select" id="floatingSelectroommate" aria-label="Roommate room occupants select">
						<?php 
							print wp_kses(
								trek_occupants_options( $roommate_selected ),
								array(
									'option' => array(
										'class'    => array(),
										'value'    => array(),
										'selected' => array(),
									)
								),						
							);
							?>
					</select>
					<label for="floatingSelectroommate"><?php esc_html_e( 'Select Occupant', 'trek-travel-theme' ); ?></label>
				</div>
			</div>
		<?php
	endfor;
	$has_occupants = true;
endif;
if( false === $has_occupants) :
	?>
	<h3><?php esc_html_e( 'Please add room first for occupant selection!', 'trek-travel-theme' ); ?></h3>
	<?php
endif;

<?php
/**
 * Template file for the guest insurance pop up modal.
 */

$guest_insurance         = isset( self::$calculations['travelers'] ) ? self::$calculations['travelers'] : [];
$base_premium_pr         = tt_validate( $guest_insurance['primary']['insurance_amount'], 0 );
$is_travel_protection_pr = $guest_insurance['primary']['is_travel_protection'];
$is_tp_purchased_pr      = isset( $guest_insurance['primary']['is_tp_purchased'] ) ? $guest_insurance['primary']['is_tp_purchased'] : 0;
$shipping_fname          = tt_validate( $guest_insurance['primary']['first_name'] );
$shipping_lname          = tt_validate( $guest_insurance['primary']['last_name'] );
$primary_name            = esc_html( $shipping_fname . ' ' . $shipping_lname );

$guests                  = isset( $guest_insurance['guests'] ) ? $guest_insurance['guests'] : [];
// Primary Guest HTML.
if ( $is_tp_purchased_pr == 1 ) :
	?>
	<div class="modal-body__guest">
		<p class="mb-4 fw-medium"><?php printf( esc_html__( 'Primary Guest: %s', 'trek-travel-theme' ), $primary_name ); ?></p>
		<p class="d-flex align-items-center lh-xs "><img src="<?php echo esc_url( TREK_DIR . '/assets/images/accepted-protection.svg' ); ?>" class="icon-22 me-1"><?php esc_html_e( 'Protected', 'trek-travel-theme' ); ?></p>
	</div>
	<?php
else :
	?>

	<div class="modal-body__guest">
		<p class="mb-4 fw-medium"><?php printf( esc_html__( 'Primary Guest: %s', 'trek-travel-theme' ), $primary_name ); ?></p>
		<div class="d-flex align-items-center mb-4">
			<input id="trek_guest_insurance_pr_add" type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="1" <?php echo esc_html( $is_travel_protection_pr != 0 ? 'checked' : '' ) ?>>
			<input type="hidden"  name="trek_guest_insurance[primary][insurance_amount]" value="<?php echo esc_attr( $base_premium_pr ) ?>">
			<label for="trek_guest_insurance_pr_add"><?php esc_html_e( 'Add Travel Protection', 'trek-travel-theme' ); ?> <span class="fw-bold">(<?php echo wc_price( floatval( esc_attr( $base_premium_pr ) ) ); ?>)</span></label>
		</div>
		<div class="d-flex align-items-center">
			<input id="trek_guest_insurance_pr_decline" type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="0" <?php echo esc_html( $is_travel_protection_pr == 0 ? 'checked' : '' ); ?>>
			<label for="trek_guest_insurance_pr_decline"><?php esc_html_e( 'Decline Travel Protection', 'trek-travel-theme' ); ?></label>
		</div>
		<?php if( empty( $guest_insurance['primary']['insurance_amount'] ) ) : ?>
			<div class="invalid-feedback travel-protection-feedback" style="display:block;">
				<img class="invalid-icon">
				<?php esc_html_e( 'Something went wrong during the calculation of the Travel Protection amount. Please double-check date of birth and address from step one to ensure they are entered correctly, and try again.', 'trek-travel-theme' ); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php
endif;

if( $guests ) :
	foreach ( $guests as $guest_k => $guest ) :
		$base_premium_guest         = tt_validate( $guest_insurance['guests'][$guest_k]['insurance_amount'], 0 );
		$guest_fname                = tt_validate( $guest['first_name'] );
		$guest_lname                = tt_validate( $guest['last_name'] );
		$guest_full_name            = esc_html( $guest_fname . ' ' . $guest_lname );
		$is_tp_purchased_guest      = isset( $guest_insurance['guests'][$guest_k]['is_tp_purchased'] ) ? $guest_insurance['guests'][$guest_k]['is_tp_purchased'] : 0;
		$is_travel_protection_guest = $guest_insurance['guests'][$guest_k]['is_travel_protection'];

		// Each Next Guest HTML.
		if ( $is_tp_purchased_guest == 1 ) :
			?>
			<div class="modal-body__guest">
				<p class="mb-4 fw-medium"><?php printf( esc_html__( 'Guest: %s', 'trek-travel-theme' ), $guest_full_name ) ?></p>
				<p class="d-flex align-items-center lh-xs "><img src="<?php echo esc_url( TREK_DIR . '/assets/images/accepted-protection.svg' ); ?>" class="icon-22 me-1"><?php esc_html_e( 'Protected', 'trek-travel-theme' ); ?></p>
			</div>
			<?php
			continue;
		endif;
		?>
			<div class="modal-body__guest">
				<p class="mb-4 fw-medium"><?php printf( esc_html__( 'Guest: %s', 'trek-travel-theme' ), $guest_full_name ) ?></p>
				<div class="d-flex align-items-center mb-4">
					<input id="trek_guest_insurance_radio_add_<?php echo esc_attr( $guest_k ); ?>" type="radio" class="guest_radio" name="trek_guest_insurance[guests][<?php echo esc_attr( $guest_k ); ?>][is_travel_protection]" value="1" <?php echo esc_html( $is_travel_protection_guest != 0 ? 'checked' : '' ); ?>>
					<input type="hidden" name="trek_guest_insurance[guests][<?php echo esc_attr( $guest_k ); ?>][insurance_amount]" value="<?php echo esc_attr( $base_premium_guest ); ?>">
					<label for="trek_guest_insurance_radio_add_<?php echo esc_attr( $guest_k ); ?>"><?php esc_html_e( 'Add Travel Protection', 'trek-travel-theme' ); ?> <span class="fw-bold">(<?php echo wc_price( floatval( esc_html( $base_premium_guest ) ) ); ?>)</span></label>
				</div>
				<div class="d-flex align-items-center">
					<input id="trek_guest_insurance_radio_decline_<?php echo esc_attr( $guest_k ); ?>" type="radio" class="guest_radio" name="trek_guest_insurance[guests][<?php echo esc_attr( $guest_k ); ?>][is_travel_protection]" value="0" <?php echo esc_html( $is_travel_protection_guest == 0 ? 'checked' : '' ); ?>>
					<label for="trek_guest_insurance_radio_decline_<?php echo esc_attr( $guest_k ); ?>"><?php esc_html_e( 'Decline Travel Protection', 'trek-travel-theme' ); ?></label>
				</div>
			</div>
			<?php if( empty( $guest_insurance["guests"][$guest_k]['insurance_amount'] ) ) : ?>
				<div class="invalid-feedback travel-protection-feedback" style="display:block;">
					<img class="invalid-icon">
					<?php esc_html_e( 'Something went wrong during the calculation of the Travel Protection amount. Please double-check date of birth and address from step one to ensure they are entered correctly, and try again.', 'trek-travel-theme' ); ?>
				</div>
			<?php endif; ?>
		<?php
	endforeach;
endif;

?>

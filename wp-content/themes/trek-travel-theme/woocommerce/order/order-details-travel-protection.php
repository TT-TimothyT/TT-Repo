<?php
/**
 * Template file for a travel protection details review, Thank you page.
 *
 * The $args comming as arguments from the wc_get_template_html() or wc_get_template() functions.
 */

if ( ! empty( $args['guest_insurance'] ) ) :
	foreach ( $guest_insurance as $guest_insurance_k => $guest_insurance_val ) :
		if ( 'primary' === $guest_insurance_k ) :
			// Primary guest Insured HTML.
			?>
				<div class="d-flex order-details__flex">
					<div class="mb-2 fs-md lh-sm fw-bold"><?php esc_html_e( 'Primary Guest', 'trek-travel-theme' ); ?></div>
					<div class="mb-2 fs-md lh-sm fw-bold text-end"><?php echo esc_html( $args['primary_guest_name'] ); ?></div>
				</div>
				<div class="d-flex order-details__flex">
				<?php
					if( isset( $guest_insurance_val['is_travel_protection'] ) && 1 == $guest_insurance_val['is_travel_protection'] ) {
						?>
							<div class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Added Travel Protection', 'trek-travel-theme' ) ?></div>
							<div class="fw-medium mb-2 text-end"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo esc_attr( tt_validate( $guest_insurance_val['basePremium'], 0 ) ) ?></span></div>
						<?php
					} else {
						?>
							<div class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Declined Travel Protection', 'trek-travel-theme' ); ?></div>
						<?php
					}
					?>
				</div>
			<?php
		else :
			foreach( $guest_insurance_val as $guest_key => $guest_insurance_data ) :
				// Each Next Guest Insured HTML.
				?>
					<div class="d-flex order-details__flex mt-4">
						<div class="mb-2 fs-md lh-sm fw-bold"><?php esc_html_e( 'Guest', 'trek-travel-theme' ); ?> <?php echo esc_attr( intval( $guest_key + 1 ) ); ?></div>
						<div class="mb-2 fs-md lh-sm fw-bold text-end"><?php echo esc_html( trim( tt_validate( $args['guests'][$guest_key]['guest_fname'] ) . ' ' . tt_validate( $args['guests'][$guest_key]['guest_lname'] ) ) ); ?></div>
					</div>
					<div class="d-flex order-details__flex">
					<?php
						if( isset( $guest_insurance_data['is_travel_protection'] ) && 1 == $guest_insurance_data['is_travel_protection'] ) {
							?>
								<div class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Added Travel Protection', 'trek-travel-theme' ) ?></div>
								<div class="fw-medium mb-2 text-end"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo esc_attr( tt_validate( $guest_insurance_data['basePremium'], 0 ) ) ?></span></div>
							<?php
						} else {
							?>
								<div class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Declined Travel Protection', 'trek-travel-theme' ); ?></div>
							<?php
						}
						?>
					</div>
				<?php
			endforeach;
		endif;
	endforeach;
endif;

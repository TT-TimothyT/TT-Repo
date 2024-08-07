<?php
/**
 * Template file for the guest insurance summary.
 *
 * @param string $args['before_html'] Wrapper opening tag.
 * @param string $args['after_html'] Wrapper closing tag.
 * @param array $args['tt_posted'] The posted guest data.
 *
 */

$tt_posted = array();

if( isset( $args['tt_posted'] ) && ! empty( $args['tt_posted'] ) ) {
	$tt_posted = $args['tt_posted'];
} else {
	$tt_checkout_data = get_trek_user_checkout_data();
	$tt_posted        = tt_validate( $tt_checkout_data['posted'], [] );
}

$guest_insurance = tt_validate( $tt_posted['trek_guest_insurance'], [] );

echo wp_kses_post( tt_validate( $args['before_html'] ) );

if( ! empty( $guest_insurance ) ) :
	$insured_guests = tt_validate( $guest_insurance['guests'], [] );
	$fields_size    = sizeof( $insured_guests ) + 1;
	$iter           = 0;
	$cols           = 2;

	foreach( $guest_insurance as $guest_insurance_k => $guest_insurance_val ) :
		if( 'primary' === $guest_insurance_k ) :
			// Primary guest Insured HTML.
			if ( 0 === $iter % $cols ) :
				?>
					<div class="d-flex order-details__flex order-details__flexmulti">
				<?php
			endif;
			?>
						<div>
							<p class="fw-medium mb-2"><?php esc_html( printf( __( 'Primary Guest: %s', 'trek-travel-theme' ), tt_validate( $tt_posted['shipping_first_name'] ) . ' ' . tt_validate( $tt_posted['shipping_last_name'] ) ) ); ?></p>
							<p class="fs-sm lh-sm mb-0">
								<?php
									if( isset( $guest_insurance_val['is_travel_protection'] ) && 1 == $guest_insurance_val['is_travel_protection'] ) :
										printf(
											wp_kses(
												/* translators: %1$s: Primary guest insurance amount; */
												__( 'Added Travel Protection (<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>%1$s</span>)', 'trek-travel-theme' ),
												array(
													'span' => array(
														'class' => array()
													)
												),
											),
											esc_attr( tt_validate( $guest_insurance_val['basePremium'], 0 ) )
										);
									else :
										esc_html_e( 'Declined Travel Protection', 'trek-travel-theme' );
									endif;
									?>
							</p>
						</div>
			<?php
			if( ( $iter % $cols == $cols - 1 ) || ( $iter == $fields_size - 1 ) ) :
				?>
					</div>
				<?php
			endif;
			$iter++;
		else :
			foreach( $guest_insurance_val as $guest_key => $guest_insurance_data ) :
				// Each Next Guest Insured HTML.
				if( $iter % $cols == 0 ) :
					?>
						<div class="d-flex order-details__flex order-details__flexmulti">
					<?php
				endif;
				?>
							<div>
								<p class="fw-medium mb-2"><?php esc_html( printf( __( 'Guest %d: %s', 'trek-travel-theme' ), intval( $guest_key + 1 ), tt_validate( $tt_posted['guests'][$guest_key]['guest_fname'] ) . ' ' . tt_validate( $tt_posted['guests'][$guest_key]['guest_lname'] ) ) ); ?></p>
								<p class="fs-sm lh-sm mb-0">
									<?php
										if( isset( $guest_insurance_data['is_travel_protection'] ) && 1 == $guest_insurance_data['is_travel_protection'] ) :
											printf(
												wp_kses(
													/* translators: %1$s: Primary guest insurance amount; */
													__( 'Added Travel Protection (<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>%1$s</span>)', 'trek-travel-theme' ),
													array(
														'span' => array(
															'class' => array()
														)
													),
												),
												esc_attr( tt_validate( $guest_insurance_data['basePremium'], 0 ) )
											);
										else :
											esc_html_e( 'Declined Travel Protection', 'trek-travel-theme' );
										endif;
										?>
								</p>
							</div>
				<?php
				if( ( $iter % $cols == $cols - 1 ) || ( $iter == $fields_size - 1 ) ) :
					?>
						</div>
					<?php
				endif;
				$iter++;
			endforeach;
		endif;
	endforeach;
endif;

echo wp_kses_post( tt_validate( $args['after_html'] ) );

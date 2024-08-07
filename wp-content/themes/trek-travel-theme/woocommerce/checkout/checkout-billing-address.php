<?php
/**
 * Template file for the primary guest billing address form fields on the checkout page, step 4.
 *
 * @param array $args['tt_posted'] The posted guest data.
 */

global $woocommerce;

$tt_posted = array();

if( isset( $args['tt_posted'] ) && ! empty( $args['tt_posted'] ) ) {
	$tt_posted = $args['tt_posted'];
} else {
	$tt_checkout_data = get_trek_user_checkout_data();
	$tt_posted        = tt_validate( $tt_checkout_data['posted'], [] );
}
?>
<div class="d-flex align-items-lg-center justify-content-between billing-address-checkbox">
	<h5 class="fs-xl lh-xl fw-medium checkout-payment__title-option mb-5"><?php esc_html_e( 'Billing Address', 'trek-travel-theme' ); ?></h5>
	<div class="d-flex align-items-center checkout-payment__billing-checkbox">
		<input id="is_same_billing_as_mailing_checkbox" type="checkbox" class="billing_checkbox" name="is_same_billing_as_mailing" value="1"<?php echo esc_attr( 1 === intval( tt_validate( $tt_posted['is_same_billing_as_mailing'], 1 ) ) ? ' ' . 'checked' : '' ); ?>>
		<label for="is_same_billing_as_mailing_checkbox"><?php esc_html_e( 'Same as my mailing address', 'trek-travel-theme' ); ?></label>
	</div>
</div>
<?php
$fields = $woocommerce->checkout->get_checkout_fields( 'billing' );

if( $fields ) :
	$iter           = 0;
	$cols           = 2;
	$fields_size    = sizeof( $fields );
	$field_includes = array('billing_first_name','billing_last_name','billing_address_1', 'billing_address_2', 'billing_country', 'billing_state', 'billing_city', 'billing_postcode');

	foreach( $fields as $key => $field ) :
		if( in_array( $key, $field_includes ) ) :
			if( 0 === $iter % $cols ) :
				?>
					<div class="row mx-0 guest-checkout__primary-form-row billing_row<?php echo esc_attr( 1 === intval( tt_validate( $tt_posted['is_same_billing_as_mailing'], 1 ) ) ? ' ' . 'd-none' : '' ); ?>">
				<?php
			endif;
			?>
				<div class="col-md px-0 form-row">
					<div class="form-floating">
						<?php
							// Setup the field attributes.
							$field['placeholder'] = $field['label'];
							$field['required']    = true;
							$field['label']       = '';
							$field['input_class'] = array('form-control');
							$field['return']      = true;

							if( 'billing_address_2' !== $key ) {
								$field['custom_attributes']['required'] = "required";
							}

							$woo_field_value = $woocommerce->checkout->get_value($key);

							if( isset( $tt_posted[$key] ) && ! empty( $tt_posted[$key] ) ) {
								$woo_field_value = $tt_posted[$key];
							}

							if( 'billing_state' === $key ) {
								// Take the billing state and the billing country from the user preferences.
								$country_val = get_user_meta( get_current_user_id(), 'billing_country', true );
								$state_val   = get_user_meta( get_current_user_id(), 'billing_state', true );

								// Take the billing country from the posted data.
								if ( ! empty( tt_validate( $tt_posted['billing_country'] ) ) ) {
									$country_val = $tt_posted['billing_country'];
								}

								// Take the billing state from the posted data.
								if ( ! empty( tt_validate( $tt_posted['billing_state'] ) ) ) {
									$state_val = $tt_posted['billing_state'];
								}

								$field['country'] = tt_validate( $country_val );
								$woo_field_value  = $state_val;
							}

							if ( 'billing_country' === $key ) {
								// Take the billing country from the user preferences.
								$country_val = get_user_meta( get_current_user_id(), 'billing_country', true );

								// Take the billing country from the posted data.
								if ( ! empty( tt_validate( $tt_posted['billing_country'] ) ) ) {
									$country_val = $tt_posted['billing_country'];
								}

								$field['country'] = tt_validate( $country_val );
								$woo_field_value  = $country_val;
							}

							if( 1 === intval( tt_validate( $tt_posted['is_same_billing_as_mailing'], 1 ) ) ) {
								// Use the same address as shipping.
								if( 'billing_first_name' === $key ) {
									$woo_field_value = $tt_posted['shipping_first_name'];
								}

								if( 'billing_last_name' === $key ) {
									$woo_field_value = $tt_posted['shipping_last_name'];
								}

								if( 'billing_address_1' === $key ) {
									$woo_field_value = $tt_posted['shipping_address_1'];
								}

								if( 'billing_address_2' === $key ) {
									$woo_field_value = $tt_posted['shipping_address_2'];
								}
								
								if( 'billing_state' === $key ) {
									// Take the shipping state and the shipping country from the user preferences.
									$country_val = get_user_meta( get_current_user_id(), 'shipping_country', true );
									$state_val   = get_user_meta( get_current_user_id(), 'shipping_state', true );
									
									if ( ! empty( tt_validate( $tt_posted['shipping_country'] ) ) ) {
										$country_val = $tt_posted['shipping_country'];
									}
									
									if ( ! empty( tt_validate( $tt_posted['shipping_state'] ) ) ) {
										$state_val = $tt_posted['shipping_state'];
									}
									
									$field['country'] = tt_validate( $country_val );
									$woo_field_value  = $state_val;
								}
								
								if( 'billing_country' === $key ) {
									$country_val = get_user_meta( get_current_user_id(), 'shipping_country', true );
									
									if ( ! empty( tt_validate( $tt_posted['shipping_country'] ) ) ) {
										$country_val = $tt_posted['shipping_country'];
									}
									
									$field['country'] = tt_validate( $country_val );
									$woo_field_value  = $country_val;
								}
								
								if( 'billing_city' === $key ) {
									$woo_field_value = $tt_posted['shipping_city'];
								}

								if( 'billing_postcode' === $key ) {
									$woo_field_value = $tt_posted['shipping_postcode'];
								}
							}

							$field_input = woocommerce_form_field( $key, $field, $woo_field_value );
							$field_input = str_ireplace('<span class="woocommerce-input-wrapper">', '', $field_input);
							$field_input = str_ireplace('</span>', '', $field_input);
							$sort        = $field['priority'] ? $field['priority'] : '';

							if( isset( $field['required'] ) ) {
								$field['class'][] = 'validate-required';
							}

							if( isset( $field['validate'] ) ) {
								foreach( $field['validate'] as $validate_name ) {
									$field['class'][] = 'validate-' . $validate_name . '';
								}
							}

							$container_class  = isset( $field['class'] ) ? esc_attr( implode(' ', $field['class'] ) ) : '';
							$container_id     = esc_attr( $key ) . '_field';
							$pfield_container = '<p class="form-row ' . $container_class . '" id="' . $container_id . '" data-priority="' . esc_attr( $sort ) . '">';
							$field_input      = str_ireplace( $pfield_container, '', $field_input );
							$field_input      = str_ireplace( '<p class="form-row form-row-wide address-field" id="billing_address_2_field" data-priority="26">', '', $field_input );
							$field_input      = str_ireplace( '<p class="form-row form-row-wide address-field validate-postcode" id="billing_postcode_field" data-priority="90">', '', $field_input );
							$field_input      = str_ireplace( '</p>', '', $field_input );
							// Displays the field.
							echo $field_input;
							?>
								<label for="billing_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['placeholder'] ); ?></label>
							<?php
							if( 'billing_state' === $key || 'billing_country' === $key ) {
								?>
									<div class="invalid-feedback"><img class="invalid-icon" />&nbsp;<?php esc_html_e( 'This field is required.', 'trek-travel-theme' ); ?></div>
								<?php
							}
							?>
					</div>
				</div>
				<?php
				if( ( $iter % $cols == $cols - 1 ) || ( $iter == $fields_size - 1 ) ) {
					?>
						</div><!-- / .guest-checkout__primary-form-row -->
					<?php
				}
			$iter++;
		endif;
	endforeach;
endif;
?>
<input type="hidden" id="tt_pay_fname" name="first_name" value="<?php echo esc_attr( $woocommerce->checkout->get_value('billing_first_name') ); ?>"/>
<input type="hidden" id="tt_pay_lname" name="last_name" value="<?php echo esc_attr( $woocommerce->checkout->get_value('billing_last_name') ); ?>"/>
<?php
$shipping_country      = tt_validate( $tt_posted['shipping_country'] );
$shipping_state        = tt_validate( $tt_posted['shipping_state'] );
$woo_states            = WC()->countries->get_states( $shipping_country );
$shipping_state_name   = isset( $woo_states[$shipping_state] ) ? $woo_states[$shipping_state] : $shipping_state;
$shipping_country_name = WC()->countries->countries[$shipping_country];
?>
<div class="checkout-payment__pre-address<?php echo esc_attr( 1 === intval( tt_validate( $tt_posted['is_same_billing_as_mailing'], 1 ) ) ? '' : ' ' . 'd-none' ); ?>" style="height:120px;">
	<p class="mb-0"><?php echo esc_html( tt_validate( $tt_posted['shipping_first_name'] ) . ' ' . tt_validate( $tt_posted['shipping_last_name'] ) ); ?></p>
	<p class="mb-0"><?php echo esc_html( tt_validate( $tt_posted['shipping_address_1'] ) ); ?></p>
	<p class="mb-0"><?php echo esc_html( tt_validate( $tt_posted['shipping_address_2'] ) ); ?></p>
	<p class="mb-0"><?php echo esc_html( tt_validate( $tt_posted['shipping_city'] ) ); ?>, <?php echo esc_html( $shipping_state_name ); ?>, <?php echo esc_html( tt_validate( $tt_posted['shipping_postcode'] ) ); ?></p>
	<p class="mb-0"><?php echo esc_html( $shipping_country_name ); ?></p>
	<p class="mb-0"></p>
</div>
<div class="d-flex checkout-payment__billing-checkboxtwo">
	<input id="is_saved_billing_checkbox" type="checkbox" name="is_saved_billing" value="1"<?php echo esc_attr( 1 === intval( tt_validate( $tt_posted['is_saved_billing'] ) ) ? ' ' . 'checked' : '' ); ?>>
	<label for="is_saved_billing_checkbox" class="w-75"><?php esc_html_e( 'Save this billing address for future use. This will override your existing billing address saved on your profile.', 'trek-travel-theme' ); ?></label>
</div>

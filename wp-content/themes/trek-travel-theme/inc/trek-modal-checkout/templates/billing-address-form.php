<?php
/**
 * Template file for the primary guest billing address form fields on the checkout page, step 4.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $woocommerce;

$current_user_id      = get_current_user_id();
$billing_address_1    = get_user_meta($current_user_id, 'billing_address_1', true);
$billing_address_2    = get_user_meta($current_user_id, 'billing_address_2', true);
$billing_postcode     = get_user_meta($current_user_id, 'billing_postcode', true);
$billing_country      = get_user_meta($current_user_id, 'billing_country', true);
$billing_state        = get_user_meta($current_user_id, 'billing_state', true);
$billing_city         = get_user_meta($current_user_id, 'billing_city', true);
$billing_states       = WC()->countries->get_states( $billing_country );
$billing_state_name   = isset( $billing_states[$billing_state] ) ? $billing_states[$billing_state] : $billing_state;
$billing_country_name = WC()->countries->countries[$billing_country];

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
					<div class="row mx-0 guest-checkout__primary-form-row billing_row<?php echo esc_attr( true === $args['is_pre_billing_address'] ? ' ' . 'd-none' : '' ); ?>">
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

							if( 'billing_state' === $key ) {
								// Take the billing state and the billing country from the user preferences.
								$country_val = get_user_meta( get_current_user_id(), 'billing_country', true );
								$state_val   = get_user_meta( get_current_user_id(), 'billing_state', true );

								$field['country'] = tt_validate( $country_val );
								$woo_field_value  = $state_val;
							}

							if ( 'billing_country' === $key ) {
								// Take the billing country from the user preferences.
								$country_val = get_user_meta( get_current_user_id(), 'billing_country', true );

								$field['country'] = tt_validate( $country_val );
								$woo_field_value  = $country_val;
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

<div class="checkout-payment__pre-address<?php echo esc_attr( true === $args['is_pre_billing_address'] ? '' : ' ' . 'd-none' ); ?>">
	<p class="mb-0"><?php echo esc_html( $woocommerce->checkout->get_value('billing_first_name') . ' ' . $woocommerce->checkout->get_value('billing_last_name') ); ?></p>
	<p class="mb-0"><?php echo esc_html( $billing_address_1 ); ?></p>
	<p class="mb-0"><?php echo esc_html( $billing_address_2 ); ?></p>
	<?php if ( ! empty( $billing_state_name ) ) : ?>
		<p class="mb-0"><?php echo esc_html( $billing_city ); ?>, <?php echo esc_html( $billing_state_name ); ?>, <?php echo esc_html( $billing_postcode ); ?></p>
	<?php else : ?>
		<p class="mb-0"><?php echo esc_html( $billing_city ); ?>, <?php echo esc_html( $billing_postcode ); ?></p>
	<?php endif; ?>
	<p class="mb-0"><?php echo esc_html( $billing_country_name ); ?></p>
</div>

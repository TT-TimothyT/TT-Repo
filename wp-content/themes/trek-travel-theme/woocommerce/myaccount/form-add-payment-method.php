<?php
/**
 * Add payment method form form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-add-payment-method.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.3.0
 */

defined( 'ABSPATH' ) || exit;

$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
$userInfo = wp_get_current_user();

if ( $available_gateways ) : ?>

<div class="container add-payment-method my-4">
	<div class="row mx-0 flex-column flex-lg-row">
		<div class="col-lg-6 medical-information__back order-1 order-lg-0">
			<a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>payment-methods/"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Payment Information</span></a>
		</div>
		<div class="col-lg-6 d-flex dashboard__log">
			<p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
			<a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
		</div>
	</div>
	<div id="add-payment-method-responses"></div>
	<div class="row mx-0 my-4">
		<div class="col-lg-12">
			<h3 class="add-payment-method__title fw-semibold">Add a new card</h3>
		</div>
	</div>

	<div class="row mx-0">
		<div class="col-lg-10">
			<div class="card add-payment-method__card rounded-1">
				<form id="add_payment_method" method="post">
					<div id="payment" class="woocommerce-Payment">
						<ul class="woocommerce-PaymentMethods payment_methods methods">
							<?php
							// Chosen Method.
							if ( count( $available_gateways ) ) {
								current( $available_gateways )->set_current();
							}

							foreach ( $available_gateways as $gateway ) {
								?>
								<li class="woocommerce-PaymentMethod woocommerce-PaymentMethod--<?php echo esc_attr( $gateway->id ); ?> payment_method_<?php echo esc_attr( $gateway->id ); ?>">
								<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> />
								<label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>"><?php echo wp_kses_post( $gateway->get_title() ); ?> <?php echo wp_kses_post( $gateway->get_icon() ); ?></label>
									<?php
									if ( $gateway->has_fields() || $gateway->get_description() ) {
										echo '<div class="woocommerce-PaymentBox woocommerce-PaymentBox--' . esc_attr( $gateway->id ) . ' payment_box payment_method_' . esc_attr( $gateway->id ) . '" >';
										$gateway->payment_fields();
										echo '</div>';
									}
									?>
								<!-- <div class="payment-method-icons">
									<?php //echo wp_kses_post( $gateway->get_icon() ); ?>
								</div> -->
							</li>
								<?php
							}
							?>
						</ul>

						<?php do_action( 'woocommerce_add_payment_method_form_bottom' ); ?>

						<div class="form-row form-buttons d-flex">
							<div class="form-group align-self-center me-4">
							<?php wp_nonce_field( 'woocommerce-add-payment-method', 'woocommerce-add-payment-method-nonce' ); ?>
								<button type="submit" id="place_order" class="btn btn-lg btn-primary w-100 medical-information__save rounded-1"><?php esc_html_e('Save', 'trek-travel-theme'); ?></button>
								<input type="hidden" name="woocommerce_add_payment_method" id="woocommerce_add_payment_method" value="1" />
							</div>
							<div class="fs-md lh-md fw-medium text-center align-self-center">
								<a href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>">Cancel</a>
							</div>
						</div>

						
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php else : ?>
	<p class="woocommerce-notice woocommerce-notice--info woocommerce-info"><?php esc_html_e( 'New payment methods can only be added during checkout. Please contact us if you require assistance.', 'woocommerce' ); ?></p>
<?php endif; ?>

<?php
/**
 * Payment methods
 *
 * Shows customer payment methods on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/payment-methods.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$saved_methods = wc_get_customer_saved_methods_list( get_current_user_id() );
$has_methods   = (bool) $saved_methods;
$types         = wc_get_account_payment_methods_types();
$userInfo = wp_get_current_user();

do_action( 'woocommerce_before_account_payment_methods', $has_methods ); ?>


<div class="container payment-methods my-4">
	<div class="row mx-0 flex-column flex-lg-row">
		<div class="col-lg-6 medical-information__back order-1 order-lg-0">
			<a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
		</div>
		<div class="col-lg-6 d-flex dashboard__log">
			<p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
			<a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
		</div>
	</div>
	<div id="payment-methods-responses"></div>
	<div class="row mx-0">
		<div class="col-lg-12">
			<h3 class="payment-methods__title fw-semibold mb-5">Payment Information</h3>
		</div>
	</div>
	<div class="row mx-0">
		<div class="col-lg-10">
			<div class="card payment-methods__card rounded-1">
				
				<?php if ( $has_methods ) : ?>
				<table class="table woocommerce-MyAccount-paymentMethods shop_table shop_table_responsive account-payment-methods-table">
					<thead>
						<tr>
							<?php if( $saved_methods ){ foreach ( wc_get_account_payment_methods_columns() as $column_id => $column_name ) :
								  $column_name = str_ireplace('Method', 'Payment Method', $column_name);
								  $column_name = str_ireplace('Default?', 'Defaults?', $column_name);
								  
								 ?>
								<th class="woocommerce-PaymentMethod woocommerce-PaymentMethod--<?php echo esc_attr( $column_id ); ?> payment-method-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
							<?php endforeach; } ?>
						</tr>
					</thead>
					<?php foreach ( $saved_methods as $type => $methods ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
						<?php foreach ( $methods as $method ) : ?>
							<tr class="payment-method<?php echo ! empty( $method['is_default'] ) ? ' default-payment-method' : ''; ?>">
								<?php foreach ( wc_get_account_payment_methods_columns() as $column_id => $column_name ) : ?>
									<td class="woocommerce-PaymentMethod woocommerce-PaymentMethod--<?php echo esc_attr( $column_id ); ?> payment-method-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
										<?php
										if ( has_action( 'woocommerce_account_payment_methods_column_' . $column_id ) ) {
											do_action( 'woocommerce_account_payment_methods_column_' . $column_id, $method );
										} elseif ( 'method' === $column_id ) {
											if ( ! empty( $method['method']['last4'] ) ) {
												/* translators: 1: credit card type 2: last 4 digits */
												echo sprintf( esc_html__( '%1$s ending in %2$s', 'woocommerce' ), esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) ), esc_html( $method['method']['last4'] ) );
											} else {
												echo esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) );
											}
										} elseif ( 'expires' === $column_id ) {
											echo esc_html( $method['expires'] );
										} elseif ( 'actions' === $column_id ) {
											foreach ( $method['actions'] as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
												echo '<a href="' . esc_url( $action['url'] ) . '" class="btn btn-secondary btn-outline-dark btn-sm rounded-1 button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>&nbsp;';
											}
										}
										?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</table>

				<?php else : ?>

					<table class="table">
						<thead>
							<tr>
								<?php if( $saved_methods ) { foreach ( wc_get_account_payment_methods_columns() as $column_id => $column_name ) : ?>
									<th class="woocommerce-PaymentMethod woocommerce-PaymentMethod--<?php echo esc_attr( $column_id ); ?> payment-method-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
								<?php endforeach; } ?>
							</tr>
						</thead>
					</table>
					<p class="fw-medium fs-lg lh-lg my-4"><?php esc_html_e( 'You currently have no payment methods saved. Add a new payment method to get started.', 'woocommerce' ); ?></p>

				<?php endif; ?>

				<?php do_action( 'woocommerce_after_account_payment_methods', $has_methods ); ?>

				<?php if ( WC()->payment_gateways->get_available_payment_gateways() ) : ?>
					<div class="add-payment-cta d-flex my-4">
						<i class="bi bi-plus-circle-fill me-2"></i><a class="text-decoration-none" href="<?php echo esc_url( wc_get_endpoint_url( 'add-payment-method' ) ); ?>"><?php esc_html_e( 'Add payment method', 'woocommerce' ); ?></a>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</div>
</div>

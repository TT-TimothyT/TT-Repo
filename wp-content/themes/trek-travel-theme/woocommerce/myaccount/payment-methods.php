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
				<p>Not available at the moment.</p>
			</div>
		</div>
	</div>
</div>

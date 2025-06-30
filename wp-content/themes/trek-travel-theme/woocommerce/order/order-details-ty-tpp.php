<?php
/**
 * Order details for Travel Protection orders
 *
 * @package TrekTravel
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$insured_travelers = tt_get_insured_travelers( $protection_data );

// Get order details
$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );

$item                  = reset( $order_items );
$item_product_id       = $item->get_product_id();
$item_qty              = $item->get_quantity();
$item_price            = $item->get_total();

$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();

$cc_account_four       = get_post_meta( $order_id, '_wc_cybersource_credit_card_account_four', true );
$cc_expiry_date        = get_post_meta( $order_id, '_wc_cybersource_credit_card_card_expiry_date', true );
$cc_expiry_date_arr    = explode( '-', $cc_expiry_date );
$cc_expiry_date_arr    = array_reverse( $cc_expiry_date_arr );
$cc_expiry_date        = implode( '/',$cc_expiry_date_arr );
$cc_card_type          = get_post_meta( $order_id, '_wc_cybersource_credit_card_card_type', true );

$product_image_url     = get_template_directory_uri() . '/assets/images/Thankyou.jpg';
$related_order_id      = $protection_data['order_id'];
$related_order         = wc_get_order( $related_order_id );
$trip_info             = tt_get_trip_pid_sku_from_cart($related_order_id);
$parent_id             = tt_get_parent_trip_id_by_child_sku( $trip_info['sku'] );
$product_image_url     = $trip_info['parent_trip_image'];

$billing_add_1         = $order->get_billing_address_1();
$billing_add_2         = $order->get_billing_address_2();
$billing_country       = $order->get_billing_country();
$billing_state         = $order->get_billing_state();
$billing_city          = $order->get_billing_city();
$billing_postcode      = $order->get_billing_postcode();
$billing_name          = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
$shipping_name         = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
$biller_name           = !empty($billing_name) ? $billing_name : $shipping_name;

$cart_total            = $order->get_total();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<div class="container-fluid order-details__banner order-details__banner_tpp d-flex flex-column" style="background-repeat: no-repeat;background-size: cover;background-image:linear-gradient(0deg, #000000 -7.63%, rgba(0, 0, 0, 0) 55.25%),url('<?php echo esc_url( $product_image_url ) ?>');">
	<div class="row my-auto">
		<div class="col-12 col-lg-10 mx-auto text-center">
			<div class="d-flex flex-column justify-content-center align-items-center od-box">
				<h1 class="mb-0 mb-lg-1 order-details__banner-heading text-center"><?php esc_html_e( 'Thanks for choosing Trek Travel for your vacation of a lifetime.', 'trek-travel-theme' ); ?></h1>
			</div>
		</div>
	</div>
</div>

<div class="container order-details order-details_tpp" id="order-details-page">
	<div class="row">
		<div class="col-12 col-lg-8 col-xl-6 mx-auto order-details-container">
			<div class="order-details__number border text-center">
				<div class="d-flex flex-column ">
					<h4 class="order-details__heading px-3 mt-5">
						<?php esc_html_e( 'Travel Protection Purchased', 'trek-travel-theme' ); ?>
					</h4>
					<hr>
					<p class="m-msg h5 px-3 pb-5">
						<?php esc_html_e( 'Keep an eye on your inbox — ', 'trek-travel-theme' ); ?>
						<br>
						<?php esc_html_e( 'A copy of your Travel Protection Policy will be sent to you shortly.', 'trek-travel-theme' ); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-12 col-lg-8 col-xl-6 mx-auto">
			<div class="order-details__summary">
				<h4 class="order-details__heading text-center"><?php esc_html_e( 'Order Summary', 'trek-travel-theme' ); ?></h4>
				<hr>
				<!-- Purchase Summary -->
				<div class="order-details__content">
					<p class="fs-lg lh-sm fw-bold fw-medium mb-5"><?php esc_html_e( 'Purchase Summary', 'trek-travel-theme' ); ?></p>
					<div class="d-flex">
						<div class="w-50">
							<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Travel Protection purchased for', 'trek-travel-theme' ); ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Purchase Date', 'trek-travel-theme' ); ?></p>
							<?php foreach ( $insured_travelers as $traveler ) : ?>
								<p class="mb-0 fw-normal order-details__text"><?php echo esc_html( $traveler['name'] ); ?></p>
							<?php endforeach; ?>
							<p class="mt-1 mb-2 mt-lg-2 fw-medium order-details__textbold"><?php esc_html_e( 'Purchase Total', 'trek-travel-theme' ); ?></p>
						</div>
						<div class="w-50 text-end">
							<p class="mb-0 fw-normal order-details__text"><?php echo esc_html( get_the_title( $parent_id ) ); ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo date('M d, Y', strtotime( $order->get_date_created() ) ) ?></p>
							<?php foreach ( $insured_travelers as $traveler ) : ?>
								<?php if ( $traveler['is_tp_purchased'] == 1 ) : ?>
									<p class="mb-0 fw-normal order-details__text"><img src="<?php echo esc_url( TREK_DIR . '/assets/images/accepted-protection.svg' ); ?>" class="icon-22 me-1"><?php esc_html_e( 'Protected', 'trek-travel-theme' ); ?></p>
								<?php elseif ( $traveler['is_protected'] == 1 ) : ?>
									<p class="mb-0 fw-normal order-details__text"><?php echo wc_price( $traveler['amount'] ); ?> USD</p>
								<?php else : ?>
									<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Not Protected', 'trek-travel-theme' ); ?></p>
								<?php endif; ?>
							<?php endforeach; ?>
							<p class="mt-1 mb-2 mt-lg-2 fw-medium order-details__textbold"><?php echo wc_price( floatval( $cart_total ) ); ?> USD</p>
						</div>
					</div>
				</div>
				<hr>
				<!-- Payment Details -->
				<div class="order-details__content">
					<p class="fs-lg lh-sm fw-bold fw-medium mb-5 order-details__subheading"><?php esc_html_e( 'Payment Details', 'trek-travel-theme' ); ?></p>
					<div class="d-flex order-details__flex">
						<div>
							<p class="mb-2 fs-md lh-sm fw-bold"><?php esc_html_e( 'Billing Method', 'trek-travel-theme' ); ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo $biller_name; ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo ( $cc_card_type ? ucfirst( $cc_card_type )  : '' ) ?> <?php echo ( $cc_account_four ? '****'.$cc_account_four  : '' ) ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo ( $cc_expiry_date ? 'Exp: '.$cc_expiry_date  : '' ) ?></p>
						</div>
						<div class="text-end">
							<p class="mb-2 fs-md lh-sm fw-bold"><?php esc_html_e( 'Billing Address', 'trek-travel-theme' ); ?></p>
							<?php
							
							$billing_states       = WC()->countries->get_states( $billing_country );
							$billing_state_name   = isset( $billing_states[$billing_state] ) ? $billing_states[$billing_state] : $billing_state;
							$billing_country_name = WC()->countries->countries[$billing_country];
							?>
							<p class="mb-0 fw-normal order-details__text"><?php echo $billing_add_1; ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo $billing_add_2; ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo $billing_city; ?>, <?php echo $billing_state_name; ?>, <?php echo $billing_postcode; ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo $billing_country_name; ?></p>
						</div>
					</div>
				</div>
			</div><!-- .order-details__summary -->
		</div>
	</div>
</div>

<script>
	// Remove Elementor Kit class from body.
	jQuery('body').removeClass('elementor-kit-14');

	jQuery(document).ready(function () {

		dataLayer.push({ ecommerce: null });
		var productsArr = [{
			'name': "travel protection", // Please remove special characters
			'id': '<?php echo $item_product_id; ?>', // populate with the product ID
			'price': '<?php echo $item_price; ?>', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
			'brand': '', //
			'category': '<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>', // populate with the 'country,continent' separating with a comma
			'variant': '', //this is the SKU of the product
			'quantity': '<?php echo $item_qty; ?>' // the number of products added to the cart
		}];
		dataLayer.push({ 
			'event':'purchase',
			'ecommerce': {
				'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
				'purchase': {
					'actionField':{
						'id': '<?php echo $order_id; ?>', // populate with the order number
						'revenue': '<?php echo $cart_total; ?>',  // total price the customer paid 
						'product_revenue': '<?php echo $cart_total; ?>',  // product revenue paid
						'tax':'', // amount of tax paid
						'shipping': '', // amount of shipping paid
						'coupon': '', // order level coupon. Pipe delimit stacked codes.
						'payment_type': 'credit card', // pipe delimit multiple values:ApplePay,GooglePay,Paypal, AfterPay, GiftCard, CreditCard 
						'order_discount': '', // order level discount amount
						'trip_type' : '',
					},
					'products': productsArr
				}
			}
		})
		jQuery("#currency_switcher").trigger("change")
	})
</script>

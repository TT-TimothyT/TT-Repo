<?php
/**
 * Order details - TT Thank You Page template.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.5.0
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();
$first_item            = reset( $order_items );

if ( $first_item ) {
	$product_id              = $first_item['product_id'];
	$first_product_price     = get_post_meta( $product_id, '_price', true );
	$first_product_price     = str_replace( ',', '', $first_product_price );
	$product_tax_rate        = floatval( get_post_meta( $product_id, 'tt_meta_taxRate', true ) );
	$single_supplement_price = wc_format_decimal( get_post_meta( $product_id, 'tt_meta_singleSupplementPrice', true ) );
	$discount_total          = $order->get_discount_total();
	if ( isset( $discount_total ) && ! empty( $discount_total ) ) {
		$first_product_price = floatval( $first_product_price ) - floatval( $discount_total );
	}
	
	if ( $product_tax_rate ) {
		$total_tax     = 0;
		$first_product = false;
		foreach ( $order_items as $item ) {
			$item_id            = $item->get_product_id();
			$product_tax_status = get_post_meta( $item_id, '_tax_status', true );
			if ( 'taxable' === $product_tax_status ) {
				$product_price = get_post_meta( $item_id, '_price', true );
				if ( 73798 === $item_id ) {
					$product_price = $single_supplement_price;
				}
				if ( $product_id === $item_id & $first_product === false ) {
					$first_product = true;
					$product_price = $first_product_price;
				}
				$cleaned_price    = str_replace( ',', '', $product_price );
				$float_price      = floatval( $cleaned_price );
				$product_quantity = $item['quantity'];
				$product_tax      = ( $product_tax_rate / 100 ) * $float_price * $product_quantity;
				$total_tax       += $product_tax;
			}
		}
	}
}

$accepted_p_ids               = tt_get_line_items_product_ids();
$trek_checkout_data           = array();
$trek_checkout_data_formatted = array();
$trip_name                    = '';
$trip_sdate                   = '';
$trip_edate                   = '';
$trip_sku                     = '';
$order_item                   = '';
$cc_account_four              = get_post_meta( $order_id, '_wc_cybersource_credit_card_account_four', true );
$cc_expiry_date               = get_post_meta( $order_id, '_wc_cybersource_credit_card_card_expiry_date', true );
$cc_expiry_date_arr           = explode( '-', $cc_expiry_date );
$cc_expiry_date_arr           = array_reverse( $cc_expiry_date_arr );
$cc_expiry_date               = implode( '/',$cc_expiry_date_arr );
$cc_card_type                 = get_post_meta( $order_id, '_wc_cybersource_credit_card_card_type', true );
$product_image_url            = get_template_directory_uri() . '/assets/images/Thankyou.jpg';
$trip_info                    = tt_get_trip_pid_sku_from_cart($order_id);
$parent_id                    = tt_get_parent_trip_id_by_child_sku( $trip_info['sku'] );
$product_image_url            = $trip_info['parent_trip_image'];
$trip_product_line_name       = tt_is_product_line( 'Hiking', $trip_info['sku'], $trip_info['ns_trip_Id'] ) ? 'Hiking' : 'Cycling';
$trip_start_date              = tt_get_local_trips_detail( 'startDate', $trip_info['ns_trip_Id'], $trip_info['sku'], true ); // The value or empty string.
$trip_end_date                = tt_get_local_trips_detail( 'endDate', $trip_info['ns_trip_Id'], $trip_info['sku'], true ); // The value or empty string.
$is_hiking_checkout           = tt_is_product_line( 'Hiking', $trip_info['sku'], $trip_info['ns_trip_Id'] );

foreach ( $order_items as $item_id => $item ) {
	$product_id = $item['product_id'];
    if ( ! in_array( $product_id, $accepted_p_ids ) ) {
		$order_item = $item;
		$product = $item->get_product();
		$trek_checkout_data = wc_get_order_item_meta( $item_id, 'trek_user_checkout_data', true );
		$trek_checkout_data_formatted = wc_get_order_item_meta( $item_id, 'trek_user_formatted_checkout_data', true );
		if ( $product ) {
			$p_id = $product->get_id();
			$trip_status = tt_get_custom_product_tax_value( $p_id, 'trip-status', true );
			$trip_sdate  = $product->get_attribute('pa_start-date');
			$trip_edate  = $product->get_attribute('pa_end-date');
			$trip_name   = $product->get_name();
			$trip_sku    = $product->get_sku();
			$sdate_obj   = explode('/', $trip_sdate);
			$edate_obj   = explode('/', $trip_edate);
			
			$start_date = sprintf('%02d/%02d/%04d', $sdate_obj[1], $sdate_obj[0], substr(date('Y'), 0, 2) . $sdate_obj[2]);
			$end_date   = sprintf('%02d/%02d/%04d', $edate_obj[1], $edate_obj[0], substr(date('Y'), 0, 2) . $edate_obj[2]);
			
			$date_range = $start_date . ' - ' . $end_date;
		}
	}
}

$guests                   = tt_validate( $trek_checkout_data['guests'], [] );
$review_bikes_arr         = tt_validate( $trek_checkout_data_formatted[1]['cart_item_data'], [] );
$review_bikes_arr_primary = $review_bikes_arr[0];
$billing_add_1            = $trek_checkout_data['billing_address_1'];
$billing_add_2            = $trek_checkout_data['billing_address_2'];
$billing_country          = $trek_checkout_data['billing_country'];
$billing_state            = $trek_checkout_data['billing_state'];
$billing_city             = $trek_checkout_data['billing_city'];
$billing_postcode         = $trek_checkout_data['billing_postcode'];
$billing_name             = ($trek_checkout_data['billing_first_name'] ? $trek_checkout_data['billing_first_name'] . ' ' . $trek_checkout_data['billing_last_name'] : '');
$shipping_name            = ($trek_checkout_data['shipping_first_name'] ? $trek_checkout_data['shipping_first_name'] . ' ' . $trek_checkout_data['shipping_last_name'] : '');
$biller_name              = (!empty($billing_name) ? $billing_name : $shipping_name);
$single_supplement_qty    = 0;
$occupants                = isset($trek_checkout_data['occupants']) && $trek_checkout_data['occupants'] ? $trek_checkout_data['occupants'] : [];
$single_supplement_qty   += isset($occupants['private']) && $occupants['private'] ? count($occupants['private']) : 0;
$single_supplement_qty   += isset($occupants['roommate']) && $occupants['roommate'] ? count($occupants['roommate']) : 0;
$singleSupplementPrice    = isset($trek_checkout_data['singleSupplementPrice']) ? $trek_checkout_data['singleSupplementPrice'] : 0;
// Fix older orders, that don't have singleSupplementPrice in the trek_user_checkout_data cart item meta.
if ( empty( $singleSupplementPrice ) ) {
    $singleSupplementPrice = tt_validate( tt_get_local_trips_detail( 'singleSupplementPrice', $trip_info['ns_trip_Id'], $trip_sku, true ), 0 );
}

// Calculate the price depends on guest number.
$supplement_fees          = str_ireplace(',','',$singleSupplementPrice); // Strip the , from the price if there's such.
$calc_supplement_fees     = floatval( $supplement_fees ) * $single_supplement_qty; // Calculate the full price.
$calc_supplement_fees     = strval( $calc_supplement_fees ); // Get the , back to the string.
$supplement_fees          = number_format( $calc_supplement_fees, 2 );
// Deposite due vars.
$deposit_amount           = tt_get_local_trips_detail( 'depositAmount', $trip_info['ns_trip_Id'], $trip_sku, true );
$deposit_amount           = $deposit_amount ? str_ireplace( ',','',$deposit_amount ) : 0;
$deposit_amount           = floatval( $deposit_amount ) * intval( tt_validate( $trek_checkout_data['no_of_guests'], 1 ) );
$pay_amount               = isset( $trek_checkout_data['pay_amount'] ) ? $trek_checkout_data['pay_amount'] : 'full';
$cart_total_full_amount   = isset( $trek_checkout_data['cart_total_full_amount'] ) ? $trek_checkout_data['cart_total_full_amount'] : '';
$cart_total               = 'deposite' === $pay_amount && ! empty( $cart_total_full_amount ) ? $cart_total_full_amount : $order->get_total();

$insurance_array          = tt_validate( $trek_checkout_data['trek_guest_insurance'], 0 );
$insured_person           = 0;
$tt_insurance_total_charges = 0;

if ( $insurance_array ) {
	foreach ( $insurance_array as $insurance_k=>$insurance_v ) {
		if ( $insurance_k == 'primary' ) {
			if ( $insurance_v['is_travel_protection'] == 1 ) {
				$insured_person++;
				$tt_insurance_total_charges += isset($insurance_v['basePremium']) ? $insurance_v['basePremium'] : 0;
			}
		} else {
			foreach ( $insurance_v as $guest_key => $guest_insurance_data ) {
				if ( $guest_insurance_data['is_travel_protection'] == 1 ) {
					$insured_person++;
					$tt_insurance_total_charges += isset($guest_insurance_data['basePremium']) ? $guest_insurance_data['basePremium'] : 0;
				}
			}
		}
	}
}

$guest_emails         = trek_get_guest_emails($order_id);
$tt_get_upgrade_qty   = tt_get_upgrade_qty($trek_checkout_data);
$dues                 = isset($trek_checkout_data['pay_amount']) && $trek_checkout_data['pay_amount'] == 'full' ? false : true;
$discount_order       = floatval( $discount_total ) * $trek_checkout_data['no_of_guests'];
$deposit_amount      += $tt_insurance_total_charges;
$remaining_amount     = $cart_total - ( $deposit_amount ? $deposit_amount : 0 );

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
<div class="container-fluid order-details__banner d-flex flex-column" style="background-repeat: no-repeat;background-size: cover;background-image:linear-gradient(0deg, #000000 -7.63%, rgba(0, 0, 0, 0) 55.25%),url('<?php echo esc_url( $product_image_url ) ?>');">
	<div class="row my-auto">
		<div class="col-12 col-lg-10 mx-auto text-center">
			<div class="d-flex flex-column justify-content-center align-items-center od-box">
				<h1 class="mb-0 mb-lg-1 order-details__banner-heading text-center"><?php esc_html_e( 'Thanks for choosing Trek Travel for your vacation of a lifetime.', 'trek-travel-theme' ); ?></h1>
				<h3 class="order-details__banner-text text-center"><?php esc_html_e( 'We’re thrilled to adventure with you!', 'trek-travel-theme' ); ?></h3>
			</div>
		</div>
	
	
	</div>
</div>
<div class="container order-details" id="order-details-page">
	<div class="row">
		<div class="col-12 col-lg-8 col-xl-6 mx-auto order-details-container">
			<div class="order-details__number border text-center">
				<div class="d-flex flex-column ">
					<p class="trip-product-line"><?php echo esc_html( $trip_product_line_name ); ?></p>
					<h4 class="order-details__heading px-3"><?php echo esc_html( get_the_title( $parent_id ) ); ?></h4>
					<p class="trip-duration mb-0 px-3"><?php echo $date_range; ?></p>
					<hr>
					<p class="m-msg h5 px-3 pb-5"><?php esc_html_e( 'Keep an eye on your inbox— ', 'trek-travel-theme' ); ?> <span class="fw-medium" id="wc-order-emails"><?php echo $guest_emails && !is_array($guest_emails) ? $guest_emails : ''; ?></span><br><?php esc_html_e( 'we’ll confirm your trip within 1 business day.', 'trek-travel-theme' ); ?> </p>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-12 col-lg-8 col-xl-6 mx-auto">
			<div class="order-details__quite rounded-1">
			<svg class="info-img" width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M16.8125 12.5312C17.3125 13.4062 16.6875 14.5 15.6562 14.5H2.3125C1.28125 14.5 0.65625 13.4062 1.15625 12.5312L7.8125 1.15625C8.34375 0.28125 9.625 0.28125 10.125 1.15625L16.8125 12.5312ZM8.25 4.75V8.75C8.25 9.1875 8.5625 9.5 9 9.5C9.40625 9.5 9.75 9.1875 9.75 8.75V4.75C9.75 4.34375 9.40625 4 9 4C8.5625 4 8.25 4.34375 8.25 4.75ZM9 12.5C9.53125 12.5 9.96875 12.0625 9.96875 11.5312C9.96875 11 9.53125 10.5625 9 10.5625C8.4375 10.5625 8 11 8 11.5312C8 12.0625 8.4375 12.5 9 12.5Z" fill="#28AAE1"/>
			</svg>
			<div>
				<p class="">
					<?php
						printf(
							wp_kses(
								/* translators: %1$s: My Account page URL; */
								__( 'Please note that we will need to collect additional information for all guests before the trip starts. <a href="%1$s">View your account</a> now to add your information.', 'trek-travel-theme' ),
								array(
									'a' => array(
										'class'  => array(),
										'href'   => array(),
										'target' => array()
									)
								)
							),
							esc_url( home_url( '/my-account/' ) ),
						);
						?>
				</p>
				<p class="lh-sm mb-0">
					<?php
						printf(
							wp_kses(
								/* translators: %1$s: Contact Us page URL; */
								__( '<a href="%1$s">Contact us</a> if you have any questions or concerns.', 'trek-travel-theme' ),
								array(
									'a' => array(
										'class'  => array(),
										'href'   => array(),
										'target' => array()
									)
								)
							),
							esc_url( home_url( '/contact-us/' ) ),
						);
						?>	
				</p>
			</div>
			</div>
			<div class="order-details__form">
				<?php 
					// If we have GF shortcode proceed.
					if ( shortcode_exists( 'gravityform' ) ) {
						// Take Form ID from ACF Field in Options page.
						$form_id = get_field( 'order_details_page_form_id', 'option' );
						
						// If we have ID proceed.
						if( ! empty( $form_id ) ) {
							?>
							<div class="order-details__content">
								<h5 class="fw-medium lh-sm order-details__title order-details__formtitle"><?php esc_html_e( 'How did you hear about us?', 'trek-travel-theme' ); ?></h5>
								<?php
								// Add additional classes to the fields.
								add_filter( 'gform_field_content_' . $form_id, function ( $field_content, $field ) {
										// Add classes to input type text fields.
										if ( $field->type == 'text' ) {
											return str_replace( "class='large'", "class='large form-control py-4 px-5'", $field_content );
										}
										// Add classes to select fields.
										if ( $field->type == 'select' ) {
											// This search for specific classname in select/dropdown field
											return str_replace( "gfield_select", "gfield_select form-select py-4 px-5", $field_content );
										}
									
										return $field_content;
								}, 10, 2 );
								
								// Add additional classes to the submit button.
								add_filter( 'gform_submit_button_' . $form_id, function ( $button, $form ) {
										// Return without changes for the admin back-end.
										if ( is_admin() ){
											return $button;
										}
										return str_replace( "gform_button button", "btn btn-lg btn-primary rounded-1 order-details__submit", $button );
								}, 10, 2 );
								
								// Display GF.
								echo do_shortcode('[gravityform id="' . $form_id . '" title="false" description="false"]');
								?>
							</div>
							<hr>
						<?php
						}
					}
				?>
			</div>
		
			<div class="order-details__summary">
				<h4 class="order-details__heading text-center"><?php esc_html_e( 'Order Summary', 'trek-travel-theme' ); ?></h4>
				<hr>
				<!-- Purchase Summary -->
				<div class="order-details__content">
					<p class="fs-lg lh-sm fw-bold fw-medium mb-5"><?php esc_html_e( 'Purchase Summary', 'trek-travel-theme' ); ?></p>
					<div class="d-flex">
						<div class="w-50">
							<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Purchase Date', 'trek-travel-theme' ); ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Confirmation #', 'trek-travel-theme' ); ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Guests', 'trek-travel-theme' ); ?> <small>x<?php echo esc_html( $trek_checkout_data['no_of_guests'] ); ?></small></p>
							<?php if( $tt_get_upgrade_qty > 0 &&  $trek_checkout_data['bikeUpgradePrice'] ) { ?>
							<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Upgrade', 'trek-travel-theme' ); ?> <small>x<?php echo esc_html( $tt_get_upgrade_qty ); ?></small></p>
							<?php } ?>
							<?php if( $single_supplement_qty > 0) { ?>
								<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Single Supplement', 'trek-travel-theme' ); ?> <small>x<?php echo esc_html( $single_supplement_qty ); ?></small></p>
							<?php } ?>
							<?php if( $insured_person > 0 && $tt_insurance_total_charges > 0 ) { ?>
								<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Travel Protection', 'trek-travel-theme' ); ?> <small>x<?php echo esc_html( $insured_person ); ?></small></p>
							<?php } ?>
							<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Subtotal', 'trek-travel-theme' ); ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Local Taxes', 'trek-travel-theme' ); ?></p>
							<?php if ( 0 < $discount_order ) : ?>
								<p class="mb-0 fw-normal order-details__text"><?php esc_html_e( 'Discount', 'trek-travel-theme' ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $dues ) ) : ?>
								<p class="mb-0 pt-4 fw-medium fs-xl lh-lg order-details__text"><?php esc_html_e( 'Trip Total', 'trek-travel-theme' ); ?></p>
								<p class="mb-0 mt-1 fs-md mt-lg-2 fw-medium order-details__textbold"><?php esc_html_e( 'Amount Paid', 'trek-travel-theme' ); ?></p>
								<p class="mb-2 fw-medium fs-md order-details__textbold"><?php esc_html_e( 'Remaining Due', 'trek-travel-theme' ); ?></p>
							<?php else : ?>
								<p class="mt-1 mb-2 mt-lg-2 fw-medium order-details__textbold"><?php esc_html_e( 'Trip Total', 'trek-travel-theme' ); ?></p>
							<?php endif; ?>
						</div>
						<div class="w-50 text-end">
							<p class="mb-0 fw-normal order-details__text"><?php echo date('M d, Y', strtotime( $order->get_date_created() ) ) ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo esc_html( $order_id ); ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo $order->get_formatted_line_subtotal( $order_item ) ?></p>
							<?php if ( $tt_get_upgrade_qty > 0 &&  $trek_checkout_data['bikeUpgradePrice'] ) { ?>
								<p class="mb-0 fw-normal order-details__text"><?php echo wc_price( $tt_get_upgrade_qty * $trek_checkout_data['bikeUpgradePrice'] ); ?></p>
							<?php } ?>
							<?php if($single_supplement_qty > 0) { ?>
								<p class="mb-0 fw-normal order-details__text"><?php echo wc_price( wc_format_decimal( $supplement_fees ) ); ?></p>
							<?php } ?>
							<?php if($insured_person > 0 && $tt_insurance_total_charges > 0 ) { ?>
								<p class="mb-0 fw-normal order-details__text"><?php echo wc_price( floatval( $tt_insurance_total_charges ) ); ?></p>
							<?php } ?>
							<p class="mb-0 fw-normal order-details__text"><?php echo wc_price( $order->get_subtotal() ); ?></p>
							<p class="mb-0 fw-normal order-details__text"><?php echo wc_price( $total_tax ); ?></p>
							<?php if ( 0 < $discount_order ) : ?>
								<p class="mb-0 fw-normal order-details__text"><?php echo wc_price( $discount_order ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $dues ) ) : ?>
								<p class="mb-0 pt-4 fw-medium fs-xl lh-lg order-details__text"><?php echo wc_price( floatval( $cart_total ) ); ?> USD</p>
								<p class="mb-0 mt-1 mt-lg-2 fs-md fw-medium order-details__textbold"><?php echo wc_price( $deposit_amount ); ?> USD</p>
								<p class="mt-2 mb-2 fs-md fw-medium order-details__textbold"><?php echo wc_price( $remaining_amount ); ?> USD</p>
							<?php else : ?>
								<p class="mt-1 mb-2 mt-lg-2 fw-medium order-details__textbold"><?php echo wc_price( floatval( $cart_total ) ); ?></p>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( ! empty( $dues ) ) : ?>
						<p class="mb-0 fs-xs lh-xs fw-normal w-75 w-lg-50 order-details__duesp"><?php esc_html_e( 'You will be responsible for paying the remaining amount on your trip before the trip start date. Our team will reach out to collect final payment.', 'trek-travel-theme' ); ?></p>
					<?php endif; ?>
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
				<hr>
				<!-- Guest Details -->
				<div class="order-details__content">
					<p class="fs-lg lh-sm fw-bold fw-medium mb-5 order-details__subheading"><?php esc_html_e( 'Guest Details', 'trek-travel-theme' ); ?></p>
					<?php
					
					$checkout_review_guest = __DIR__ . '/order-details-guest.php';

					if( is_readable( $checkout_review_guest ) ) {
						?>
							<div class="checkout-review__guest">
								<?php
									$raw_city_state_code = array(
										tt_validate( $trek_checkout_data['shipping_city'] ),
										tt_validate( WC()->countries->get_states( tt_validate( $trek_checkout_data['shipping_country'] ) )[tt_validate( $trek_checkout_data['shipping_state'] )], tt_validate( $trek_checkout_data['shipping_state'] ) ),
										tt_validate( $trek_checkout_data['shipping_postcode'] ),
									);
									$city_state_code = '';
									foreach( $raw_city_state_code as $index => $value ) {
										if( ! empty( $value ) ) {
											if( 0 < $index && ! empty( $city_state_code ) ) {
												$city_state_code .= ', ';
											}
											$city_state_code .= $value;
										}
									}

									$checkout_review_guest_primary_args = array(
										'is_primary'   => true,
										'fullname'     => tt_validate( $review_bikes_arr_primary['guest_fname'] ) . ' ' . tt_validate( $review_bikes_arr_primary['guest_lname'] ),
										'guest_info'   => array(
											'email'           => tt_validate( $trek_checkout_data['email'] ),
											'phone'           => tt_validate( $trek_checkout_data['shipping_phone'] ),
											'addr_1'          => tt_validate( $trek_checkout_data['shipping_address_1'] ),
											'addr_2'          => tt_validate( $trek_checkout_data['shipping_address_2'] ),
											'city_state_code' => $city_state_code,
											'country'         => WC()->countries->countries[tt_validate( $trek_checkout_data['shipping_country'] )],
											'dob'             => tt_validate( $trek_checkout_data['custentity_birthdate'] ),
											'gender'          => 2 === (int) tt_validate( $trek_checkout_data['custentity_gender'] ) ? 'Female' : ( 1 === (int) tt_validate( $trek_checkout_data['custentity_gender'] ) ? 'Male' : '' ),
										),
										'hiking_info'  => $is_hiking_checkout ? array(
											'Activity Level:' => tt_get_custom_item_name( 'syncActivityLevel', tt_validate( $review_bikes_arr_primary['activity_level'] ) ), // H&W.
											'Guest Height:'   => tt_get_custom_item_name( 'syncHeights', tt_validate( $review_bikes_arr_primary['rider_height'] ) ),
											'T-Shirt Size:'   => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_primary['tshirt_size'] ) ), // H&W.
										) : array()
									);

									wc_get_template( 'woocommerce/order/order-details-guest.php', $checkout_review_guest_primary_args );

									if( $guests ) {
										foreach ( $guests as $guest_num => $guest ) {
											$review_bikes_arr_guest = $review_bikes_arr[$guest_num];
											$checkout_review_guest_args = array(
												'is_primary'   => false,
												'guest_num'    => $guest_num,
												'fullname'     => tt_validate( $review_bikes_arr_guest['guest_fname'] ) . ' ' . tt_validate( $review_bikes_arr_guest['guest_lname'] ),
												'guest_info'   => array(
													'email'           => tt_validate( $review_bikes_arr_guest['guest_email'] ),
													'phone'           => tt_validate( $review_bikes_arr_guest['guest_phone'] ),
													'dob'             => tt_validate( $review_bikes_arr_guest['guest_dob'] ),
													'gender'          => 2 === (int) tt_validate( $review_bikes_arr_guest['guest_gender'] ) ? 'Female' : ( 1 === (int) tt_validate( $review_bikes_arr_guest['guest_gender'] ) ? 'Male' : '' ),
												),
												'hiking_info'  => $is_hiking_checkout ? array(
													'Activity Level:' => tt_get_custom_item_name( 'syncActivityLevel', tt_validate( $review_bikes_arr_guest['activity_level'] ) ), // H&W.
													'Guest Height:'   => tt_get_custom_item_name( 'syncHeights', tt_validate( $review_bikes_arr_guest['rider_height'] ) ),
													'T-Shirt Size:'   => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_guest['tshirt_size'] ) ), // H&W.
												) : array()
											);
					
											wc_get_template( 'woocommerce/order/order-details-guest.php', $checkout_review_guest_args );
										}
									}
									?>
							</div><!-- .checkout-review__guest -->
						<?php
					} else {
						?>
							<h3><?php esc_html_e( 'Thank you page', 'trek-travel-theme' ); ?></h3>
							<p><?php esc_html_e( 'Checkout reviews single guest template is missing!', 'trek-travel-theme' ); ?></p>
						<?php
					}
					?>
				</div>
				<hr>
				<!-- Room Details -->
				<div class="order-details__content">
					<p class="fs-lg lh-sm fw-bold fw-medium mb-5 order-details__subheading"><?php esc_html_e( 'Room Details', 'trek-travel-theme' ); ?></p>
					<div>
						<?php
						$checkout_review_rooms = __DIR__ . '/order-details-rooms.php';
						if( is_readable( $checkout_review_rooms ) ) {
							?>
								<div class="checkout-review__guest">
									<?php
										$checkout_review_rooms_args = array(
											'primary_guest_name' => trim( tt_validate( $trek_checkout_data['shipping_first_name'] ) . ' ' . tt_validate( $trek_checkout_data['shipping_last_name'] ) ),
											'guests'             => $guests,
											'occupants'          => tt_validate( $trek_checkout_data['occupants'], [] ),
											'single'             => tt_validate( $trek_checkout_data['single'], 0 ),
											'double'             => tt_validate( $trek_checkout_data['double'], 0 ),
											'private'            => tt_validate( $trek_checkout_data['private'], 0 ),
											'roommate'           => tt_validate( $trek_checkout_data['roommate'], 0 ),
										);

										wc_get_template( 'woocommerce/order/order-details-rooms.php', $checkout_review_rooms_args );
										?>
								</div><!-- .checkout-review__guest -->
							<?php
						} else {
							?>
								<h3><?php esc_html_e( 'Thank you page', 'trek-travel-theme' ); ?></h3>
								<p><?php esc_html_e( 'Checkout reviews rooms template is missing!', 'trek-travel-theme' ); ?></p>
							<?php
						}
						?>
					</div>
				</div>
				<?php if( ! $is_hiking_checkout ) : ?>
					<hr>
					<!-- Bikes & Gear Details -->
					<div class="order-details__content">
						<p class="fs-lg lh-sm fw-bold fw-medium mb-5 order-details__subheading"><?php esc_html_e( 'Bikes & Gear Details', 'trek-travel-theme' ); ?></p>
						<?php
							$checkout_review_bike = __DIR__ . '/order-details-bike.php';

							if( is_readable( $checkout_review_bike ) ) {
								$own_bike_id       = 5270;
								$non_rider_bike_id = 5257;

								?>
									<div class="checkout-review__guest">
										<?php

											$transportation_options  = array(
												''             => '',
												'hard case'    => 'Hard Case',
												'soft case'    => 'Soft Case',
												'shipping'     => 'Shipping',
												'i am driving' => "I'm driving"
											);

											$bike_type_info_primary    = tt_ns_get_bike_type_info( $review_bikes_arr_primary['bikeTypeId'] );
											$is_bike_upgrade_primary   = $bike_type_info && isset( $bike_type_info_primary['isBikeUpgrade'] ) && $bike_type_info_primary['isBikeUpgrade'] == 1 ? true : false;
											$bike_arr_primary          = tt_get_local_bike_detail( $trip_info['ns_trip_Id'], $trip_info['sku'], $review_bikes_arr_primary['bikeId'] );
											$is_bike_available_primary = $bike_arr_primary && isset( $bike_arr_primary[0]['available'] ) && (int) $bike_arr_primary[0]['available'] > 0 ? true : false;
											
											$requested_label_primary   = $review_bikes_arr_primary['bikeId'] && ! $is_bike_upgrade_primary && ! $is_bike_available_primary ? ' (<span class="fw-bold">' . __( 'request pending', 'trek-travel-theme' ) . '</span>)' : '';
		
											$checkout_review_bike_primary_args = array(
												'is_non_rider' => $non_rider_bike_id === (int) tt_validate( $review_bikes_arr_primary['bikeId'] ),
												'is_primary'   => true,
												'fullname'     => tt_validate( $review_bikes_arr_primary['guest_fname'] ) . ' ' . tt_validate( $review_bikes_arr_primary['guest_lname'] ),
												'bike_info'    => array(
													'Activity Level:'         => ! $is_hiking_checkout ? str_replace( 'Non-Hiker', 'Non-Rider', tt_get_custom_item_name( 'syncActivityLevel', tt_validate( $review_bikes_arr_primary['rider_level'] ) ) ) : tt_get_custom_item_name( 'syncActivityLevel', tt_validate( $review_bikes_arr_primary['activity_level'] ) ),
													'Bike:'                   => ! in_array( (int) tt_validate( $review_bikes_arr_primary['bikeId'] ), array( $own_bike_id, $non_rider_bike_id ) ) ? tt_validate( tt_get_custom_item_name('ns_bikeType_info' )[ tt_validate( array_search( tt_validate( $review_bikes_arr_primary['bikeTypeId'] ), array_column( tt_get_custom_item_name( 'ns_bikeType_info' ), 'id' ) ) ) ]['name'] ) : ( $own_bike_id === (int) tt_validate( $review_bikes_arr_primary['bikeId'] ) ? 'Bringing own' : '' ),
													'Bike Size:'              => ! in_array( (int) tt_validate( $review_bikes_arr_primary['bikeId'] ), array( $own_bike_id, $non_rider_bike_id ) ) ? tt_get_custom_item_name( 'syncBikeSizes', tt_validate( $review_bikes_arr_primary['bike_size'] ) ) . $requested_label_primary : '',
													'Transportation Options:' => $transportation_options[ tt_validate( $review_bikes_arr_primary['transportation_options'] ) ], // If selected Own Bike.
													'Type of bike:'           => tt_validate( $review_bikes_arr_primary['type_of_bike'] ), // If selected Own Bike.
													'Rider Height:'           => tt_get_custom_item_name( 'syncHeights', tt_validate( $review_bikes_arr_primary['rider_height'] ) ),
													'Pedals:'                 => tt_get_custom_item_name( 'syncPedals', tt_validate( $review_bikes_arr_primary['bike_pedal'] ) ),
													'Helmet Size:'            => tt_get_custom_item_name( 'syncHelmets', tt_validate( $review_bikes_arr_primary['helmet_size'] ) ),
													'Jersey:'                 => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_primary['jersey_size'] ) ),
													'T-Shirt Size:'           => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_primary['tshirt_size'] ) ), // H&W.
												)
											);
		
											wc_get_template( 'woocommerce/order/order-details-bike.php', $checkout_review_bike_primary_args );
		
											if( $guests ) {
												foreach ( $guests as $guest_num => $guest ) {
													$review_bikes_arr_guest = $review_bikes_arr[$guest_num];

													$bike_type_info_guest    = tt_ns_get_bike_type_info( $review_bikes_arr_guest['bikeTypeId'] );
													$is_bike_upgrade_guest   = $bike_type_info && isset( $bike_type_info_guest['isBikeUpgrade'] ) && $bike_type_info_guest['isBikeUpgrade'] == 1 ? true : false;
													$bike_arr_guest          = tt_get_local_bike_detail( $trip_info['ns_trip_Id'], $trip_info['sku'], $review_bikes_arr_guest['bikeId'] );
													$is_bike_available_guest = $bike_arr_guest && isset( $bike_arr_guest[0]['available'] ) && (int) $bike_arr_guest[0]['available'] > 0 ? true : false;

													$requested_label_guest   = $review_bikes_arr_guest['bikeId'] && ! $is_bike_upgrade_guest && ! $is_bike_available_guest ? ' (<span class="fw-bold">' . __( 'request pending', 'trek-travel-theme' ) . '</span>)' : '';
													
													$checkout_review_bike_guest_args = array(
														'is_non_rider' => $non_rider_bike_id === (int) tt_validate( $review_bikes_arr_guest['bikeId'] ),
														'is_primary'   => false,
														'guest_num'    => $guest_num,
														'fullname'     => tt_validate( $review_bikes_arr_guest['guest_fname'] ) . ' ' . tt_validate( $review_bikes_arr_guest['guest_lname'] ),
														'guest_info'   => array(
															'email'           => tt_validate( $review_bikes_arr_guest['guest_email'] ),
															'phone'           => tt_validate( $review_bikes_arr_guest['guest_phone'] ),
															'dob'             => tt_validate( $review_bikes_arr_guest['guest_dob'] ),
															'gender'          => 2 === (int) tt_validate( $review_bikes_arr_guest['guest_gender'] ) ? 'Female' : ( 1 === (int) tt_validate( $review_bikes_arr_guest['guest_gender'] ) ? 'Male' : '' )
														),
														'bike_info'    => array(
															'Activity Level:'         => ! $is_hiking_checkout ? str_replace( 'Non-Hiker', 'Non-Rider', tt_get_custom_item_name( 'syncActivityLevel', tt_validate( $review_bikes_arr_guest['rider_level'] ) ) ) : tt_get_custom_item_name( 'syncActivityLevel', tt_validate( $review_bikes_arr_guest['activity_level'] ) ),
															'Bike:'                   => ! in_array( (int) tt_validate( $review_bikes_arr_guest['bikeId'] ), array( $own_bike_id, $non_rider_bike_id ) ) ? tt_validate( tt_get_custom_item_name('ns_bikeType_info' )[ tt_validate( array_search( tt_validate( $review_bikes_arr_guest['bikeTypeId'] ), array_column( tt_get_custom_item_name( 'ns_bikeType_info' ), 'id' ) ) ) ]['name'] ) : ( $own_bike_id === (int) tt_validate( $review_bikes_arr_guest['bikeId'] ) ? 'Bringing own' : '' ),
															'Bike Size:'              => ! in_array( (int) tt_validate( $review_bikes_arr_guest['bikeId'] ), array( $own_bike_id, $non_rider_bike_id ) ) ? tt_get_custom_item_name( 'syncBikeSizes', tt_validate( $review_bikes_arr_guest['bike_size'] ) ) . $requested_label_guest : '',
															'Transportation Options:' => $transportation_options[ tt_validate( $review_bikes_arr_guest['transportation_options'] ) ], // If selected Own Bike.
															'Type of bike:'           => tt_validate( $review_bikes_arr_guest['type_of_bike'] ), // If selected Own Bike.
															'Rider Height:'           => tt_get_custom_item_name( 'syncHeights', tt_validate( $review_bikes_arr_guest['rider_height'] ) ),
															'Pedals:'                 => tt_get_custom_item_name( 'syncPedals', tt_validate( $review_bikes_arr_guest['bike_pedal'] ) ),
															'Helmet Size:'            => tt_get_custom_item_name( 'syncHelmets', tt_validate( $review_bikes_arr_guest['helmet_size'] ) ),
															'Jersey:'                 => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_guest['jersey_size'] ) ),
															'T-Shirt Size:'           => tt_get_custom_item_name( 'syncJerseySizes', tt_validate( $review_bikes_arr_guest['tshirt_size'] ) ), // H&W.
														)
													);
							
													wc_get_template( 'woocommerce/order/order-details-bike.php', $checkout_review_bike_guest_args );
												}
											}
											?>
									</div><!-- .checkout-review__guest -->
								<?php
							} else {
								?>
									<h3><?php esc_html_e( 'Thank you page', 'trek-travel-theme' ); ?></h3>
									<p><?php esc_html_e( 'Checkout reviews single bike guest template is missing!', 'trek-travel-theme' ); ?></p>
								<?php
							}
						?>
					</div>
				<?php endif; ?>
				<hr>
				<!-- Travel Protection Information -->
				<div class="order-details__content">
					<p class="fs-lg lh-sm fw-bold fw-medium mb-5 order-details__subheading"><?php esc_html_e( 'Travel Protection Information', 'trek-travel-theme' ); ?></p>
					<?php
						$checkout_review_travel_protection = __DIR__ . '/order-details-travel-protection.php';
						if( is_readable( $checkout_review_travel_protection ) ) {
							?>
								<div class="checkout-review__guest">
									<?php
										$checkout_review_travel_protection_args = array(
											'primary_guest_name' => trim( tt_validate( $trek_checkout_data['shipping_first_name'] ) . ' ' . tt_validate( $trek_checkout_data['shipping_last_name'] ) ),
											'guest_insurance'    => tt_validate( $trek_checkout_data['trek_guest_insurance'], [] ),
											'guests'             => $guests
										);

										wc_get_template( 'woocommerce/order/order-details-travel-protection.php', $checkout_review_travel_protection_args );
										?>
								</div><!-- .checkout-review__guest -->
							<?php
						} else {
							?>
								<h3><?php esc_html_e( 'Thank you page', 'trek-travel-theme' ); ?></h3>
								<p><?php esc_html_e( 'Checkout reviews travel protection template is missing!', 'trek-travel-theme' ); ?></p>
							<?php
						}
					?>
				</div>
			</div>
			</div>
		</div>
		</div>
	</div>
</div>

<script>

jQuery('body').removeClass('elementor-kit-14');

	jQuery(document).ready(function () {

		dataLayer.push({ ecommerce: null });
		var productsArr = [{
						'name': "<?php echo preg_replace('/[^A-Za-z0-9]/', '', $trip_name) ?>", // Please remove special characters
						'id': '<?php echo $item['product_id'] ?>', // Parent ID
						'price': '<?php echo $cart_total ?>', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
						'brand': '', //
						'category': '<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>', // populate with the 'country,continent' separating with a comma
						'variant': '<?php echo $trip_sku ?>', //this is the SKU of the product
						'quantity': '1' //the number of products added to the cart
					}];

					<?php if( $tt_get_upgrade_qty > 0 &&  $trek_checkout_data['bikeUpgradePrice'] ) { ?>
						productsArr.push({
							'name': "upgrade", // Please remove special characters
						'id': '<?php echo $item['product_id'] ?>', // Parent ID
						'price': '<?php echo $trek_checkout_data['bikeUpgradePrice'] ?>', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
						'brand': '', //
						'category': '<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>', // populate with the 'country,continent' separating with a comma
						'variant': '<?php echo $trip_sku ?>', //this is the SKU of the product
						'quantity': '1' //the number of products added to the cart
						})

						<?php 
					}
					if( $single_supplement_qty > 0 ) { 
					 ?>
					 productsArr.push({
							'name': "single supplement fee", // Please remove special characters
						'id': '<?php echo $item['product_id'] ?>', // Parent ID
						'price': '<?php echo $supplement_fees ?>', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
						'brand': '', //
						'category': '<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>', // populate with the 'country,continent' separating with a comma
						'variant': '<?php echo $trip_sku ?>', //this is the SKU of the product
						'quantity': '1' //the number of products added to the cart
						})

					 <?php } ?>


		dataLayer.push({ 
			'event':'purchase',
			'ecommerce': {
				'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
				'purchase': {
					'actionField':{
						'id': '<?php echo $order_id; ?>', // populate with the order number
						'revenue': '<?php echo $cart_total; ?>',  // total price the customer paid 
						'product_revenue': '<?php echo $cart_total; ?>',  // product revenue paid                      
						'tax':'<?php echo $item['total_tax'] ?>', // amount of tax paid
						'shipping': '', // amount of shipping paid
						'coupon': '<?php echo $item['coupon_code'] ?>', // order level coupon. Pipe delimit stacked codes.
						'payment_type': 'credit card', // pipe delimit multiple values:ApplePay,GooglePay,Paypal, AfterPay, GiftCard, CreditCard 
						'order_discount': '', // order level discount amount
						'trip_type' : '<?php echo $is_hiking_checkout ? "Hiking & Walking" : "Cycling" ?>',
					},
					'products': productsArr
				}
			}
		})
		jQuery("#currency_switcher").trigger("change")
	})
</script>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_order_details', $order );

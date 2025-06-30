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

// Check if there is protection data
$protection_data = tt_get_protection_data_by_order_id( $order_id );

if ( ! empty( $protection_data ) ) {
	// Load the Travel Protection thank you page.
	wc_get_template( 'order/order-details-ty-tpp.php', array( 'order_id' => $order_id, 'order' => $order, 'protection_data' => $protection_data ) );
} else {
	// Load the regular thank you page.
	wc_get_template( 'order/order-details-ty-regular.php', array( 'order_id' => $order_id, 'order' => $order ) );
}

/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_order_details', $order );

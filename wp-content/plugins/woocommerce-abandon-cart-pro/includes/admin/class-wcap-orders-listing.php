<?php
/**
 * It will display the Order Status for Recovered Orders
 *
 * @author  Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Report
 * @since 8.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wcap_Orders_Listing' ) ) {

	/**
	 * It will display the order status for orders which have been recovered.
	 *
	 * @since 8.4
	 */
	class Wcap_Orders_Listing {

		/**
		 * Construct.
		 */
		public function __construct() {
			add_action( 'manage_shop_order_posts_custom_column', array( &$this, 'wcap_cpt_orders_status' ), 99, 1 );
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( &$this, 'wcap_hpos_orders_status' ), 99, 2 );
		}

		/**
		 * Called to display the Recovered status in WC Orders - CPT.
		 *
		 * @param string - Column Name.
		 */
		public static function wcap_cpt_orders_status( $column ) {

			if ( in_array( $column, array( 'order_status', 'wc_actions' ), true ) ) {
				global $post;
				$order_id = $post->ID;
				self::wcap_add_recovered_status( $order_id, $column );
			}
		}

		/**
		 * Called to display the Recovered status in WC Orders - HPOS.
		 *
		 * @param string $column - Column Name.
		 * @param string $order  - WC Order object.
		 */
		public static function wcap_hpos_orders_status( $column, $order ) {

			if ( in_array( $column, array( 'order_status', 'wc_actions' ), true ) ) {
				$order_id = $order->get_id();
				self::wcap_add_recovered_status( $order_id, $column );
			}
		}
		/**
		 * This function will display 'Recovered' status for recovered orders in WC->Orders listing page.
		 *
		 * @param string $column - ID of column in WC->Orders.
		 * @since 8.4
		 */
		public static function wcap_add_recovered_status( $order_id, $column ) {

			if ( 'order_status' === $column ) {

				global $wpdb;

				$get_cart = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE recovered_cart = %d", $order_id ) ); //phpcs:ignore

				if ( isset( $get_cart ) && is_array( $get_cart ) && count( $get_cart ) > 0 ) {
					$status = esc_html__( 'Recovered', 'woocommerce-ac' );
					echo "<span id='wcap_recovered_cart' class='wcap_recovered_cart' style='margin-left: 10%; background-color: green; color: white; padding: 4px 9px 4px 9px; border-radius: 3px; margin-top: 8px; display:inline-block;'>$status</span>";
				}
			}
			if ( 'wc_actions' === $column ) {
				$order = wc_get_order( $order_id );
				// Display ATC Coupon Used label for orders which have been placed using Cupons generated via ATC.
				$coupon_code = $order->get_meta( 'wcap_atc_coupon' );
				if ( isset( $coupon_code ) && '' !== $coupon_code ) {
					$coupon_status = __( 'ATC Coupon Used', 'woocommerce-ac' );
					echo wp_kses_post( "<span id='wcap_atc_coupon' class='wcap_atc_coupon' style='font-weight: 600;font-style: italic;'>$coupon_status</span>" );
				}
			}
		}
	} // class.
} // if class exists.
$wcap_orders_listing = new Wcap_Orders_Listing();

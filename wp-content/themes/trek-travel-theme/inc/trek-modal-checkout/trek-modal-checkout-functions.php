<?php
/**
 * Trek Modal Checkout Functions
 *
 * Functions related to the Trek Travel Protection Modal Checkout
 *
 * @package TrekTravel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get travel protection data by order ID
 *
 * Retrieves the travel protection data stored in an order, either from order meta
 * or from line items if meta doesn't exist
 *
 * @param int $order_id The order ID to retrieve data for
 * @return array|false Travel protection data or false if not found
 */
function tt_get_protection_data_by_order_id( $order_id ) {
	if ( empty( $order_id ) ) {
		return false;
	}

	// Try to get data from order meta first (most reliable)
	$protection_data = get_post_meta( $order_id, 'tt_protection_data', true );
	
	// If we have data in the order meta, return it
	if ( ! empty( $protection_data ) ) {
		return $protection_data;
	}
	
	// If no data in order meta, try to get from line items
	$order = wc_get_order( $order_id );
	
	if ( ! $order ) {
		return false;
	}
	
	$accepted_p_ids = tt_get_line_items_product_ids();
	$items = $order->get_items();
	$protection_data = array();
	
	// Check each line item for travel protection data
	foreach ( $items as $item_id => $item ) {
		$product_id = $item->get_product_id();
		
		if ( in_array( $product_id, $accepted_p_ids ) ) {
			$item_protection_data = wc_get_order_item_meta( $item_id, 'tt_protection_data', true );
			
			if ( ! empty( $item_protection_data ) ) {
				$protection_data = $item_protection_data;
				break;
			}
		}
	}
	
	return ! empty( $protection_data ) ? $protection_data : false;
}

/**
 * Get travel protection data from cart
 *
 * Retrieves the travel protection data stored in the current cart
 *
 * @return array|false Travel protection data or false if not found
 */
function tt_get_protection_data_from_cart() {
	// Check if WooCommerce is active and cart is available
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}

	$accepted_p_ids = tt_get_line_items_product_ids();
	$cart           = WC()->cart->get_cart();

	foreach ( $cart as $cart_item_key => $cart_item ) {
		$product_id = isset( $cart_item['product_id'] ) ? $cart_item['product_id'] : '';

		if ( in_array( $product_id, $accepted_p_ids ) ) {
			if ( isset( $cart_item['tt_protection_data'] ) && ! empty( $cart_item['tt_protection_data'] ) ) {
				return $cart_item['tt_protection_data'];
			}
		}
	}

	return false;
}

/**
 * Check if order has travel protection
 *
 * @param int $order_id The order ID to check
 * @return bool True if order has travel protection, false otherwise
 */
function tt_has_travel_protection( $order_id ) {
	$protection_data = tt_get_protection_data_by_order_id( $order_id );
	
	if ( ! $protection_data ) {
		return false;
	}
	
	// Check if we have a modal checkout flag
	$is_modal_checkout = get_post_meta( $order_id, 'tt_modal_checkout', true );
	if ( ! empty( $is_modal_checkout ) ) {
		return true;
	}
	
	// Check if we have travelers with protection
	if ( ! empty( $protection_data['travelers'] ) ) {
		foreach ( $protection_data['travelers'] as $guest_type => $traveler_data ) {
			if ( $guest_type === 'primary' ) {
				if ( isset( $traveler_data['is_travel_protection'] ) && $traveler_data['is_travel_protection'] == 1 ) {
					return true;
				}
			} else {
				foreach ( $traveler_data as $traveler ) {
					if ( isset( $traveler['is_travel_protection'] ) && $traveler['is_travel_protection'] == 1 ) {
						return true;
					}
				}
			}
		}
	}
	
	// Check if we have a total insurance amount
	if ( isset( $protection_data['total_insurance_amount'] ) && $protection_data['total_insurance_amount'] > 0 ) {
		return true;
	}
	
	// Check if we have a tp_price
	if ( isset( $protection_data['tp_price'] ) && $protection_data['tp_price'] > 0 ) {
		return true;
	}
	
	return false;
}

/**
 * Get insured travelers from protection data
 *
 * @param array $protection_data The protection data
 * @return array List of insured travelers
 */
function tt_get_insured_travelers( $protection_data ) {
	$insured_travelers = array();

	if ( empty( $protection_data ) || empty( $protection_data['travelers'] ) ) {
		return $insured_travelers;
	}

	foreach ( $protection_data['travelers'] as $guest_type => $traveler_data ) {
		if ( $guest_type === 'primary' ) {
			$insured_travelers[] = array(
				'type'            => 'primary',
				'name'            => $traveler_data['first_name'] . ' ' . $traveler_data['last_name'],
				'is_tp_purchased' => isset( $traveler_data['is_tp_purchased'] ) ? $traveler_data['is_tp_purchased'] : 0,
				'amount'          => isset( $traveler_data['insurance_amount'] ) ? $traveler_data['insurance_amount'] : 0,
				'is_protected'    => isset( $traveler_data['is_travel_protection'] ) ? $traveler_data['is_travel_protection'] : 0
			);
		} else {
			foreach ( $traveler_data as $guest_idx => $guest_data ) {
				$insured_travelers[] = array(
					'type'            => 'guest',
					'index'           => $guest_idx,
					'name'            => $guest_data['first_name'] . ' ' . $guest_data['last_name'],
					'is_tp_purchased' => isset( $guest_data['is_tp_purchased'] ) ? $guest_data['is_tp_purchased'] : 0,
					'amount'          => isset( $guest_data['insurance_amount'] ) ? $guest_data['insurance_amount'] : 0,
					'is_protected'    => isset( $guest_data['is_travel_protection'] ) ? $guest_data['is_travel_protection'] : 0
				);
			}
		}
	}

	return $insured_travelers;
}

/**
 * Get related orders for a given order ID
 *
 * Retrieves all orders that are related to the specified order,
 * including travel protection orders linked to a trip order and vice versa.
 *
 * @param int $order_id The order ID to get related orders for
 * @return array Array of related order IDs or empty array if none found
 */
function tt_get_related_orders( $order_id ) {
	if ( empty( $order_id ) ) {
		return array();
	}

	// Get related orders from order meta
	$related_orders = get_post_meta( $order_id, 'tt_related_orders', true );

	if ( empty( $related_orders ) || ! is_array( $related_orders ) ) {
		return array();
	}

	// Make sure we return an array of integers
	$related_order_ids = array_map( 'intval', $related_orders );

	// Ensure we don't have duplicates
	$related_order_ids = array_unique( $related_order_ids );

	// Remove any zero or invalid values
	$related_order_ids = array_filter( $related_order_ids );

	return $related_order_ids;
}

/**
 * Check if current user has a valid billing address
 *
 * This function checks if all required billing address fields have values.
 * It's used to determine whether to show the billing form or use the saved address.
 *
 * @return bool True if user has valid billing address, false otherwise
 */
function tt_user_has_valid_billing_address() {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	
	$current_user_id = get_current_user_id();
	$required_fields = array(
		'billing_address_1',
		'billing_postcode',
		'billing_country',
		'billing_state',
		'billing_city',
	);
	
	foreach ( $required_fields as $field ) {
		$value = get_user_meta( $current_user_id, $field, true );
		if ( empty( $value ) ) {
			return false;
		}
	}
	
	return true;
}

/**
 * Get travel protection status for a booking order
 *
 * This function checks if the booking order has travel protection purchased,
 * for a specific guest registration ID.
 *
 * @param int $booking_order_id The booking order ID to check
 * @param int|null $guest_reg_id The guest registration ID to check, defaults to null
 * @return bool True if travel protection is purchased, false otherwise
 */
function tt_get_protected_status( $booking_order_id = 0, $guest_reg_id = null ) {
	if ( empty( $booking_order_id ) ) {
		return false;
	}

	global $wpdb;
	$bookings_table_name = $wpdb->prefix . 'guest_bookings';

	$guest_data = $wpdb->get_row( $wpdb->prepare( "
		SELECT *
		FROM $bookings_table_name
		WHERE order_id = %d
		AND guestRegistrationId = %d
	", $booking_order_id, $guest_reg_id ), ARRAY_A );

	if ( ! $guest_data ) {
		return false;
	}

	if ( isset( $guest_data['wantsInsurance'] ) && $guest_data['wantsInsurance'] == 1 ) {
		return true;
	}

	return false;
}

<?php

use Automattic\WooCommerce\Utilities\OrderUtil;


function tm_ns_get_post_meta( $post_id, $meta_key ) {
	$meta_value = '';
	if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
		// HPOS usage is enabled.
		$order = wc_get_order($post_id);
		$meta_value = $order ? $order->get_meta($meta_key, true) : null;        
	} else {
		$meta_value =  get_post_meta($post_id, $meta_key, true);    
	}

	return $meta_value; 
}



function tm_ns_update_post_meta( $post_id, $meta_key, $meta_value ) {
	if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
		$order = wc_get_order($post_id);
		$order->update_meta_data($meta_key, $meta_value );
		$order->save();
	} else {
		update_post_meta($post_id, $meta_key, $meta_value);    
	}
}

function tm_ns_delete_post_meta( $post_id, $meta_key, $meta_value ) {
	if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
		$order = wc_get_order($post_id);
		$order->delete_meta_data($meta_key, $meta_value);
		$order->save();
	} else {
		delete_post_meta($post_id, $meta_key);    
	}
}

function tm_ns_get_post_type( $post_id ) {
	if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
		$post_type = OrderUtil::get_order_type( $post_id );
	} else {
		$post_type = get_post_type($post_id);    
	}


	return $post_type; 
}

function tm_ns_get_order_data( $order, $mapping ) {
	$saved_value = '';
	if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
		$meta_key = '_' . $mapping['wc_field_key'];

		if ('id' == $mapping['wc_field_key']) {
			$saved_value = $order->get_id();
		}

		if ('shipping_method' == $mapping['wc_field_key']) {
			$saved_value = $order->get_shipping_method();
		}

		if ('cart_discount' == $mapping['wc_field_key']) {
			$saved_value = $order->get_discount_total();
		}

		if ('cart_discount_tax' == $mapping['wc_field_key']) {
			$saved_value = $order->get_discount_tax();
		}


		if ('customer_user' == $mapping['wc_field_key']) {
			$saved_value = $order->get_user_id();
		}

		if ('order_tax' == $mapping['wc_field_key']) {
			$saved_value = $order->get_cart_tax();
		}

		if ('order_shipping_tax' == $mapping['wc_field_key']) {
			$saved_value = $order->get_shipping_tax();
		}

		if ('order_shipping' == $mapping['wc_field_key']) {
			$saved_value = $order->get_shipping_total();
		}

		if ('order_total' == $mapping['wc_field_key']) {
			$saved_value = $order->get_subtotal();
		}

		if ('memo' == $mapping['wc_field_key'] || 'customer_note' == $mapping['wc_field_key']) {
			$saved_value = $order->get_customer_note();
		}


		if (empty($saved_value) && !empty($order->get_meta($meta_key, true))) {
			$saved_value = $order->get_meta($meta_key, true);
		}

	} else {
		if ('memo' == $mapping['wc_field_key'] || 'customer_note' == $mapping['wc_field_key']) {
			$saved_value = $order->get_customer_note();
		}

		if ('id' == $mapping['wc_field_key']) {
			$saved_value = $order->get_id();
		}

		if ('shipping_method' == $mapping['wc_field_key']) {
			$saved_value = $order->get_shipping_method();
		}

		
		if (empty($saved_value)) {
			if (!empty(get_post_meta($order->get_id(), '_' . $mapping['wc_field_key'], true))) {
				$saved_value = get_post_meta($order->get_id(), '_' . $mapping['wc_field_key'], true);
			} 

		}




	}


	return $saved_value; 
}

if (!function_exists('tmns_hpos_add_meta_box')) {
	function tmns_hpos_add_meta_box( $id, $title, $callback, $screen, $context = 'advanced', $priority = 'default' ) {
		if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$screen = wc_get_page_screen_id( $screen );
		} else {
			$screen = $screen;
		}
		add_meta_box(
			$id,
			$title,
			$callback,
			$screen,
			$context,
			$priority
		);
	}
}

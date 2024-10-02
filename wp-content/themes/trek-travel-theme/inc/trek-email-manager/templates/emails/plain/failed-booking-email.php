<?php
/**
 * Admin failed booking email
 */
$order = new WC_order( $item_data->order_id );

echo "= " . $email_heading . " =\n\n";

$opening_paragraph = __( 'A new order has been made by %s and the Booking creation in NetSuite failed. The details are as follows:', 'trek-travel-theme' );

$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->billing_first_name : $order->get_billing_first_name();
$billing_last_name = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->billing_last_name : $order->get_billing_last_name(); 
if ( $order && $billing_first_name && $billing_last_name ) {
	echo sprintf( $opening_paragraph, $billing_first_name . ' ' . $billing_last_name ) . "\n\n";
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf( __( 'Booked Trip: %s', 'trek-travel-theme' ), $item_data->product_title ) . "\n";

echo sprintf( __( 'Quantity: %s', 'trek-travel-theme' ), $item_data->qty ) . "\n";

echo sprintf( __( 'SKU: %s', 'trek-travel-theme' ), $item_data->sku ) . "\n";

echo sprintf( __( 'Date Product ID: %s', 'trek-travel-theme' ), $item_data->product_id ) . "\n";

echo sprintf( __( 'Trip Status: %s', 'trek-travel-theme' ), $item_data->trip_status ) . "\n";

echo sprintf( __( 'Order ID: %s', 'trek-travel-theme' ), $item_data->order_id ) . "\n";

echo sprintf( __( 'Order Date: %s', 'trek-travel-theme' ), $item_data->order_date ) . "\n";

echo sprintf( __( 'Order Status: %s', 'trek-travel-theme' ), $item_data->order_status ) . "\n";

echo sprintf( __( 'Order Total (including fees): %s', 'trek-travel-theme' ), $item_data->order_total ) . "\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo __( 'The details from the NetSuite Response are as follows:', 'trek-travel-theme' ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf( __( 'NetSuite Success: %s', 'trek-travel-theme' ), is_bool( $item_data->ns_response->success ) ? ( $item_data->ns_response->success ? 'true' : 'false' ) : $item_data->ns_response->success ) . "\n";

echo sprintf( __( 'NetSuite Errors: %s', 'trek-travel-theme' ), implode( ';', $item_data->ns_response->errors ) ) . "\n";

echo sprintf( __( 'NetSuite Saved Data: %s', 'trek-travel-theme' ), json_encode( $item_data->ns_response->saved_data ) ) . "\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo __( 'This is a custom email sent when a booking creation in NetSuite fails.', 'trek-travel-theme' ) . "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
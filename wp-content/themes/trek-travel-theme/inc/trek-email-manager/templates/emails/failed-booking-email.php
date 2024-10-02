<?php
/**
 * Admin failed booking email
 */
$order = new WC_order( $item_data->order_id );
$opening_paragraph = __( 'A new order has been made by %s and the Booking creation in NetSuite failed. The details are as follows:', 'trek-travel-theme' );

?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<?php
$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->billing_first_name : $order->get_billing_first_name();
$billing_last_name = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->billing_last_name : $order->get_billing_last_name(); 
if ( $order && $billing_first_name && $billing_last_name ) : ?>
	<p><?php printf( $opening_paragraph, $billing_first_name . ' ' . $billing_last_name ); ?></p>
<?php endif; ?>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Booked Trip', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->product_title; ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->qty; ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'SKU', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->sku; ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Date Product ID', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->product_id; ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Trip Status', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->trip_status; ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Order ID', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->order_id; ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Order Date', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->order_date; ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Order Status', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->order_status; ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Order Total (including fees)', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->order_total; ?></td>
		</tr>
	</tbody>
</table>

<?php echo "\n\n"; ?>

<p><?php _e( 'The details from the NetSuite Response are as follows:', 'trek-travel-theme' ); ?></p>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'NetSuite Success', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo is_bool( $item_data->ns_response->success ) ? ( $item_data->ns_response->success ? 'true' : 'false' ) : esc_html( $item_data->ns_response->success ); ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'NetSuite Errors', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;">
				<?php
					foreach( $item_data->ns_response->errors as $ns_error ) {
						echo esc_html( $ns_error ) . "\n\n";
					}
					?>
			</td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'NetSuite Saved Data', 'trek-travel-theme' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo json_encode( $item_data->ns_response->saved_data, JSON_PRETTY_PRINT ); ?></td>
		</tr>
	</tbody>
</table>

<p><?php _e( 'This is a custom email sent when a booking creation in NetSuite fails.', 'trek-travel-theme' ); ?></p>

<p><?php echo make_clickable( sprintf( __( 'You can view and edit this order in the dashboard here: %s', 'trek-travel-theme' ), admin_url( 'post.php?post=' . $item_data->order_id . '&action=edit' ) ) ); ?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>
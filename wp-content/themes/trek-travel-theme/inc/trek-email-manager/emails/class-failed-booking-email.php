<?php 
/**
 * Failed Booking Email
 *
 * An email sent to the admin when a booking creation in NetSuite fails.
 * 
 * @class       Failed_Booking_Email
 * @extends     WC_Email
 *
 */

class Failed_Booking_Email extends WC_Email {
	
	function __construct() {
		
		// Add email ID, title, description, heading, subject.
		$this->id                   = 'failed_booking_email';
		$this->title                = __( 'Failed Booking Email', 'trek-travel-theme' );
		$this->description          = __( 'This email is received when a booking creation in NetSuite fails.', 'trek-travel-theme' );
		
		$this->heading              = __( 'Failed Booking Email', 'trek-travel-theme' );
		$this->subject              = __( '[{blogname}] Booking Creation Failed: {product_title} (Order {order_number}) - {order_date}', 'trek-travel-theme' );
		
		// Email template path.
		$this->template_html    = 'emails/failed-booking-email.php';
		$this->template_plain   = 'emails/plain/failed-booking-email.php';
		
		// Triggers for this email.
		add_action( 'failed_booking_pending_email_notification', array( $this, 'queue_notification' ), 10, 2 );
		add_action( 'failed_booking_email_notification', array( $this, 'trigger' ), 10, 3 );
		
		// Call parent constructor.
		parent::__construct();
		
		// Other settings.
		$this->template_base = TREK_TEMPLATE_PATH;
		// Default the email recipient to the admin's email address.
		$this->recipient     = $this->get_option( 'recipient', get_option( 'admin_email' ) );
		
	}
	
	public function queue_notification( $order_id, $ns_response ) {

		$order           = null;
		$items           = array();
		$accepted_p_ids  = tt_get_line_items_product_ids();
		$item_id         = null;
		$order_item      = null;

		if ( isset( $order_id ) && (int) $order_id > 0 ) {
			$order = new WC_order( $order_id );
			$items = $order->get_items();
		}
		// Foreach item in the order.
		foreach ( $items as $item_key => $item_value ) {
			$product_id = $item_value['product_id'];
			if ( ! in_array( $product_id, $accepted_p_ids ) ) {
				// This is the Trip Date product.
				$item_id    = $item_key;
				$order_item = $item_value;
			}
		}

		// Add an event for the item email, pass the item ID so other details can be collected as needed
		wp_schedule_single_event( time(), 'failed_booking_email', array( $item_id, $order_item, $ns_response ) );
	}
	
	// This function collects the data and sends the email.
	function trigger( $item_id, $order_item, $ns_response ) {
		
		$send_email = true;
		// Validations.
		if ( $item_id && $send_email ) {
			// Create an object with item details like name, quantity etc.
			$this->object = $this->create_object( $item_id, $order_item, $ns_response );
			
			// Replace the merge tags with valid data.
			$key = array_search( '{product_title}', array_keys( $this->placeholders ) );
			if ( false !== $key ) {
				unset( $this->placeholders[ $key ] );
			}

			$this->placeholders = array_merge( array( '{product_title}' => $this->object->product_title ), $this->placeholders );
		
			if ( $this->object->order_id ) {

				$this->placeholders = array_merge( array( '{order_date}' => date_i18n( wc_date_format(), strtotime( $this->object->order_date ) ) ), $this->placeholders );
		
				$this->placeholders = array_merge( array( '{order_number}' =>  $this->object->order_id ), $this->placeholders );
			} else {

				$this->placeholders = array_merge( array( '{order_date}' =>  __( 'N/A', 'trek-travel-theme' ) ), $this->placeholders );
		
				$this->placeholders = array_merge( array( '{order_number}' =>  __( 'N/A', 'trek-travel-theme' ) ), $this->placeholders );
			}
	
			// If no recipient is set, do not send the email.
			if ( ! $this->get_recipient() ) {
				return;
			}
			// Send the email.
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), array() );

		}
	}
	
	// Create an object with the data to be passed to the templates
	public static function create_object( $item_id, $order_item, $ns_response = array() ) {
	
		global $wpdb;

		$email_data_object = new stdClass();

		/**
		 * Collect Order details data.
		 */
		
		// Order ID.
		$query_order_id = "SELECT order_id FROM `". $wpdb->prefix."woocommerce_order_items` WHERE order_item_id = %d";
		$get_order_id = $wpdb->get_results( $wpdb->prepare( $query_order_id, $item_id ) );
	
		$order_id = 0;
		if ( isset( $get_order_id ) && is_array( $get_order_id ) && count( $get_order_id ) > 0 ) {
			$order_id = $get_order_id[0]->order_id;
		} 
		$email_data_object->order_id = $order_id;
	
		$order = new WC_order( $order_id );
		// Order status.
		$email_data_object->order_status = $order->get_status();
	
		// Order date.
		$post_data = get_post( $order_id );
		$email_data_object->order_date = $post_data->post_date;
	
		// Product ID.
		$email_data_object->product_id = wc_get_order_item_meta( $item_id, '_product_id' );

		// Trek checkout data
		$trek_checkout_data = wc_get_order_item_meta( $item_id, 'trek_user_checkout_data', true );
		$pay_amount         = isset( $trek_checkout_data['pay_amount'] ) ? $trek_checkout_data['pay_amount'] : 'full';
		$ct_full_amount     = isset( $trek_checkout_data['cart_total_full_amount'] ) ? $trek_checkout_data['cart_total_full_amount'] : '';
		$cart_total         = 'deposite' === $pay_amount && ! empty( $ct_full_amount ) ? $ct_full_amount : $order->get_total( $order_item );

		// Order total.
		$email_data_object->order_total = wc_price( $cart_total );
	
		// Product name.
		$_product = wc_get_product( $email_data_object->product_id );
		$email_data_object->product_title = $_product->get_title();

		// Product SKU.
		$email_data_object->sku = $_product->get_sku();

		// Trip Status.
		$email_data_object->trip_status = tt_get_custom_product_tax_value( $email_data_object->product_id, 'trip-status', true );

		// Qty.
		$email_data_object->qty = wc_get_order_item_meta( $item_id, '_qty' );

		// Email adress.
		$email_data_object->billing_email = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->billing_email : $order->get_billing_email();
	
		// Customer ID.
		$email_data_object->customer_id = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->user_id : $order->get_user_id();

		/**
		 * Collect NS Response data.
		 */

		$email_data_object->ns_response = new stdClass();

		if ( isset( $ns_response['success'] ) ) {
			$email_data_object->ns_response->success = $ns_response['success'];
		}

		if ( isset( $ns_response['errors'] ) ) {
			
			if( is_array( $ns_response['errors'] ) ) {
				$email_data_object->ns_response->errors = $ns_response['errors'];
			} else {
				$email_data_object->ns_response->errors = array( $ns_response['errors'] );
			}
		}

		if ( isset( $ns_response['savedData'] ) ) {
			$email_data_object->ns_response->saved_data = $ns_response['savedData'];
		}

		return $email_data_object;
	
	}
	
	// Return the html content.
	function get_content_html() {
		ob_start();
		wc_get_template( $this->template_html, array(
		'item_data'       => $this->object,
		'email_heading' => $this->get_heading()
		), 'dx-trek-email/', $this->template_base );
		return ob_get_clean();
	}

	// Return the plain content.
	function get_content_plain() {
		ob_start();
		wc_get_template( $this->template_plain, array(
			'item_data'       => $this->object,
			'email_heading' => $this->get_heading()
			), 'dx-trek-email/', $this->template_base );
		return ob_get_clean();
	}
	
	// Return the subject.
	function get_subject() {
		
		$order = new WC_order( $this->object->order_id );
		return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->subject ), $this->object );
		
	}
	
	// Return the email heading.
	public function get_heading() {
		
		$order = new WC_order( $this->object->order_id );
		return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
		
	}
	
	// Form fields that are displayed in WooCommerce->Settings->Emails.
	function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' 		=> __( 'Enable/Disable', 'trek-travel-theme' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enable this email notification', 'trek-travel-theme' ),
				'default' 		=> 'yes'
			),
			'recipient' => array(
				'title'         => __( 'Recipient', 'trek-travel-theme' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s', 'trek-travel-theme' ), get_option( 'admin_email' ) ),
				'default'       => get_option( 'admin_email' )
			),
			'subject' => array(
				'title' 		=> __( 'Subject', 'trek-travel-theme' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'trek-travel-theme' ), $this->subject ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'heading' => array(
				'title' 		=> __( 'Email Heading', 'trek-travel-theme' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'trek-travel-theme' ), $this->heading ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'email_type' => array(
				'title' 		=> __( 'Email type', 'trek-travel-theme' ),
				'type' 			=> 'select',
				'description' 	=> __( 'Choose which format of email to send.', 'trek-travel-theme' ),
				'default' 		=> 'html',
				'class'			=> 'email_type',
				'options'		=> array(
					'plain'		 	=> __( 'Plain text', 'trek-travel-theme' ),
					'html' 			=> __( 'HTML', 'trek-travel-theme' ),
					'multipart' 	=> __( 'Multipart', 'trek-travel-theme' ),
				)
			)
		);
	}
	
}
return new Failed_Booking_Email();
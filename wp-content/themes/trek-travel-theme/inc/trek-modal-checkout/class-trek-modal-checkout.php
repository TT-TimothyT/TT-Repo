<?php
/**
 * Trek Modal Checkout Handler
 *
 * Class responsible for handling travel protection purchases through AJAX
 *
 * @package TrekTravel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Trek Modal Checkout class
 */
class Trek_Modal_Checkout {

	/**
	 * Flag to track origin of the request
	 *
	 * @var string
	 */
	private static $request_origin = '';

	/**
	 * Flag for purchase status
	 *
	 * @var bool
	 */
	private static $is_protection_purchase = false;

	/**
	 * Calculations data
	 *
	 * @var array
	 */
	private static $calculations = array();

	/**
	 * Initialize the class
	 */
	public static function init() {
		// Load the modal HTML
		add_action( 'wp_body_open', array( __CLASS__, 'tt_layout_three' ), 10 );

		add_action( 'tt_quick_look_modal_body', array( __CLASS__, 'tt_quick_look_modal_body_checkout' ), 10, 1 );

		// Add modal body for Thank You page
		add_action( 'tt_quick_look_modal_body', array( __CLASS__, 'tt_quick_look_modal_body_thank_you' ), 10, 1 );

		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		// Register AJAX handlers
		add_action( 'wp_ajax_tt_add_travel_protection', array( __CLASS__, 'process_add_travel_protection' ) );
		add_action( 'wp_ajax_nopriv_tt_add_travel_protection', array( __CLASS__, 'process_add_travel_protection' ) );

		add_action( 'wp_ajax_tt_modal_checkout_update_cart', array( __CLASS__, 'process_modal_checkout_update_cart' ) );
		add_action( 'wp_ajax_nopriv_tt_modal_checkout_update_cart', array( __CLASS__, 'process_modal_checkout_update_cart' ) );

		add_action( 'wp_ajax_tt_pre_billing_address', array( __CLASS__, 'pre_billing_address_handler' ) );
		add_action( 'wp_ajax_nopriv_tt_pre_billing_address', array( __CLASS__, 'pre_billing_address_handler' ) );

		add_action( 'wp_ajax_tt_decline_travel_protection', array( __CLASS__, 'process_decline_travel_protection' ) );
		add_action( 'wp_ajax_nopriv_tt_decline_travel_protection', array( __CLASS__, 'process_decline_travel_protection' ) );

		// Filter to intercept cart addition
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'pre_add_to_cart_validation' ), 10, 3 );
		
		// Actions after adding to cart
		add_action( 'woocommerce_add_to_cart', array( __CLASS__, 'after_add_to_cart' ), 10, 6 );
		
		// Add custom cart item data
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 3 );

		// Add custom data to cart item
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'before_calculate_totals' ), 17 );

		// This is where we can add custom validation for the checkout process.
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'after_checkout_validation' ), 10000, 2 );

		// After successful checkout, we can perform any actions needed. Set priority to 9 to ensure it runs before the default WooCommerce actions.
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'after_successful_checkout' ), 9, 1 );

		// Add custom data to order item meta.
		add_action('woocommerce_checkout_create_order_line_item', array( __CLASS__, 'add_product_custom_field_to_order_item_meta' ), 10, 4 );

		// Custom redirect to My Account page after checkout
		add_filter( 'woocommerce_get_return_url', array( __CLASS__, 'custom_wc_redirect_to_my_account' ), 10, 2 );
	}

	/**
	 * Load the quick look modal for checkout.
	 */
	public static function tt_layout_three() {
		// Get the checkout page's ID
		$checkout_page_id = wc_get_page_id( 'checkout' );

		// Get the post object for the checkout page
		$checkout_page = get_post( $checkout_page_id );

		// Get the slug of the checkout page
		$checkout_page_slug = $checkout_page->post_name;

		// Return if WooCommerce not active
		if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}

		// Return if checkout page
		if ( class_exists( 'woocommerce' ) ) {
			if ( is_page( $checkout_page_slug ) || is_page( 'checkout' ) ) {
				return;
			}
		}

		// Return if cart page
		if ( class_exists( 'woocommerce' ) ) {
			if ( is_page( 'cart' ) || is_cart() ) {
				return;
			}
		}

		get_template_part('tpl-parts/common/modal', 'quick-look', array( 'id' => 'quickLookModalCheckout', 'additional_class' => 'modal-quick-look-checkout' ) );
	}

	/**
	 * Load the modal body for checkout
	 *
	 * @param array $args Arguments passed to the template.
	 */
	public static function tt_quick_look_modal_body_checkout( $args ) {
		if ( ! isset( $args['id'] ) || $args['id'] !== 'quickLookModalCheckout' ) {
			return;
		}

		require_once get_template_directory() . '/inc/trek-modal-checkout/templates/trek-single-step-cart.php';
	}

	/**
	 * Load the modal body for Thank You page
	 *
	 * @param array $args Arguments passed to the template.
	 */
	public static function tt_quick_look_modal_body_thank_you( $args ) {
		if ( ! isset( $args['id'] ) || $args['id'] !== 'quickLookModalThankYou' ) {
			return;
		}

		if ( ! isset( $_GET['tpp_thankyou'], $_GET['order_id'], $_GET['order_key'] ) || '1' !== $_GET['tpp_thankyou'] ) {
			return;
		}

		// Get order ID from query params
		$order_id  = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
		$order_key = isset($_GET['order_key']) ? wc_clean($_GET['order_key']) : '';
		
		// Verify order exists and belongs to user
		if ($order_id && $order_key) {
			$order = wc_get_order($order_id);

			if ( $order && $order->get_order_key() === $order_key && ( $order->get_user_id() === get_current_user_id() ) ) {
				// Trigger actions for thank you page.
				do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
				do_action( 'woocommerce_thankyou', $order->get_id() );
				// Show the modal
				echo '<script>jQuery(document).ready(function($) { $("#quickLookModalThankYou").modal("show"); });</script>';
				// Remove the query parameters to prevent reloading the page with them
				echo '<script>history.replaceState(null, null, window.location.pathname + window.location.search.replace(/([&?]tpp_thankyou=1|[&?]order_id=' . $order_id . '|[&?]order_key=' . $order_key . ')/g, ""));</script>';
			} else {
				echo '<p class="error-message text-center">' . esc_html__( 'Invalid order or access denied.', 'trek-travel-theme' ) . '</p>';
			}
		} else {
			echo '<p class="error-message text-center">' . esc_html__( 'Order information not found.', 'trek-travel-theme' ) . '</p>';
		}
	}

	/**
	 * Enqueue scripts and styles
	 */
	public static function enqueue_scripts() {
		global $woocommerce;

		if ( class_exists( 'woocommerce' ) ) {
			wp_enqueue_script( 'wc-checkout' );
			// wp_enqueue_script( 'ins-wc-checkout' );
			wp_enqueue_style( 'select2' );
			wp_enqueue_script( 'select2' );
			// wp_dequeue_script( 'selectWoo' );
			wp_enqueue_script( 'wc-country-select' );
		}

		// Enqueue custom scripts and styles for the modal checkout
		wp_enqueue_script( 'tt-modal-checkout', get_template_directory_uri() . '/assets/js/trek-modal-checkout.js', array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'tt-modal-checkout', 'tt_modal_checkout_params', array(
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'nonce'               => wp_create_nonce( 'tt_modal_checkout_nonce' ),
			'is_tt_loader'        => 'true',
			'tt_checkout_load_wc' => $woocommerce->plugin_url() . '/assets/js/frontend/checkout.js',
			'tt_cart_count'       => WC()->cart->get_cart_contents_count(),
			'i18n'                => array(
				'pay_now' => __( 'Pay %%amount%% Now', 'trek-travel-theme' ),
			),
		) );
	}

	/**
	 * Process AJAX request to add travel protection
	 */
	public static function process_add_travel_protection() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'tt_modal_checkout_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'trek-travel-theme' ) ) );
		}

		self::$request_origin = isset( $_POST['request_origin'] ) ? sanitize_text_field( $_POST['request_origin'] ) : '';
		self::$is_protection_purchase = true;
		
		// Get product ID to add
		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		
		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product', 'trek-travel-theme' ) ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

		if ( ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order', 'trek-travel-theme' ) ) );
		}
		
		// Perform pre-calculations
		self::perform_calculations( $order_id );
		
		$quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
		$variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : 0;
		$variation = isset( $_POST['variation'] ) ? (array) $_POST['variation'] : array();
		$cart_item_data = array( 'tt_protection_data' => self::$calculations, 'tt_modal_checkout' => true, 'tt_related_orders' => array( $order_id ) );

		$accepted_p_ids = tt_get_line_items_product_ids();
		
		// Prevent adding the same product multiple times.
		$travel_protection_product_cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data ); // !!! Keep in mind that this will work when the arguments are the same passed during add_to_cart.
		if ( ! WC()->cart->find_product_in_cart( $travel_protection_product_cart_id ) ) {

			// Keep only one product in the cart
			$cart = WC()->cart->get_cart();
			foreach ( $cart as $cart_item_key => $cart_item ) {
				WC()->cart->remove_cart_item( $cart_item_key );
			}

			// Add to cart
			$added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );
		} else {
			$added = true;
			// Update the existing cart item data
			$cart = WC()->cart->get_cart_contents();

			foreach ($cart as $cart_item_id => $cart_item) {
				$_product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
				if ( in_array( $_product_id, $accepted_p_ids ) ) {

					if ( isset( $cart_item['tt_protection_data'] ) && ! empty( $cart_item['tt_protection_data'] ) ) {

						$cart_item['tt_protection_data'] = self::$calculations;
						$cart[$cart_item_id]             = $cart_item;

					}
				}
			}

			// Store the updated cart.
			WC()->cart->set_cart_contents( $cart );
			// Recalculate the totals after modifying the cart.
			WC()->cart->calculate_totals();
			// Save the updated cart to the session.
			WC()->cart->set_session();
			// Update persistent_cart.
			WC()->cart->persistent_cart_update();
		}

		ob_start();
		require_once get_template_directory() . '/inc/trek-modal-checkout/templates/trek-single-step-cart.php';
		$data = ob_get_clean();

		ob_start();
		require_once get_template_directory() . '/inc/trek-modal-checkout/templates/insured-guests.php';
		$insured_guests_html = ob_get_clean();

		// Override the checkout template
		if ( ! is_user_logged_in() ) {
			// Display for non-logged-in users
			ob_start();
			add_action( 'woocommerce_checkout_shipping', array( WC()->checkout(), 'checkout_form_shipping' ) );
			do_action( 'woocommerce_checkout_shipping' );
			$tt_shipping_additional = ob_get_clean();
		} else {
			// Use the default WooCommerce action for logged-in users
			ob_start();
			do_action( 'woocommerce_checkout_shipping' );
			$tt_shipping_additional = ob_get_clean();
		}

		$tt_cart_total = WC()->cart->get_cart_contents_count();

		if ( $added ) {
			wp_send_json_success( array( 
				'message'                => __( 'Travel protection added to cart', 'trek-travel-theme' ),
				'calculations'           => self::$calculations,
				'cart_hash'              => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
				'modal_checkout_html'    => $data,
				'cart_total'             => WC()->cart->get_cart_total(),
				'tt_cart_count'          => $tt_cart_total,
				'tt_shipping_additional' => $tt_shipping_additional,
				'insured_guests_html'    => $insured_guests_html,
				'total_price'            => WC()->cart->get_cart_contents_total(),
				'fragments'              => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
				'cart_hash'              => apply_filters( 'woocommerce_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() )
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to add travel protection', 'trek-travel-theme' ) ) );
		}
		
		wp_die();
	}

	/**
	 * Process modal checkout add to cart
	 */
	public static function process_modal_checkout_update_cart() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'tt_modal_checkout_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'trek-travel-theme' ) ) );
		}

		$guest_type           = isset( $_POST['guest_type'] ) ? sanitize_text_field( $_POST['guest_type'] ) : '';
		$guest_idx            = isset( $_POST['guest_index'] ) ? sanitize_text_field( $_POST['guest_index'] ) : '';
		$is_travel_protection = isset( $_POST['insurance_option'] ) ? (bool) $_POST['insurance_option'] : false;

		$accepted_p_ids = tt_get_line_items_product_ids();
		$cart = WC()->cart->get_cart_contents();
		foreach ($cart as $cart_item_id => $cart_item) {
			$product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
			if (in_array($product_id, $accepted_p_ids)) {
				// Check if the product is a travel protection product
				if ( isset( $cart_item['tt_protection_data'] ) && ! empty( $cart_item['tt_protection_data'] ) ) {
					// Update the cart item data with the new calculations
					$tt_protection_data = $cart_item['tt_protection_data'];

					if ( $is_travel_protection ) {

						if ( 'primary' === $guest_type ) {
							$tt_protection_data['travelers'][$guest_type]['is_travel_protection'] = 1;
						} else {
							$tt_protection_data['travelers'][$guest_type][$guest_idx]['is_travel_protection'] = 1;
						}

					} else {
						if ( 'primary' === $guest_type ) {
							$tt_protection_data['travelers'][$guest_type]['is_travel_protection'] = 0;
						} else {
							$tt_protection_data['travelers'][$guest_type][$guest_idx]['is_travel_protection'] = 0;
						}
					}
					// Update the cart item data
					$cart_item['tt_protection_data'] = $tt_protection_data;
					$cart[$cart_item_id]             = $cart_item;
				}
			}
		}

		// Store the updated cart.
		WC()->cart->set_cart_contents( $cart );
		// Recalculate the totals after modifying the cart.
		WC()->cart->calculate_totals();
		// Save the updated cart to the session.
		WC()->cart->set_session();
		// Update persistent_cart.
		WC()->cart->persistent_cart_update();

		// Get the updated cart contents
		$updated_cart_contents = WC()->cart->get_cart_contents();
		wp_send_json_success(
			array( 
				'cart_contents' => $updated_cart_contents,
				'total_price' => WC()->cart->get_cart_contents_total(),
			) 
		);
		wp_die();
	}

	/**
	 * Process decline travel protection.
	 */
	public static function process_decline_travel_protection() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'tt_modal_checkout_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'trek-travel-theme' ) ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

		if ( ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order', 'trek-travel-theme' ) ) );
		}

		$guests_booking_info = trek_get_user_order_info( null, $order_id );

		foreach ( $guests_booking_info as $guest_index => $guest_info ) {
			// If travel protection is purchased, skip this guest
			if ( self::is_trip_insurance_purchased( $order_id, $guest_index ) ) {
				continue;
			}

			$ns_user_reg_id = isset( $guest_info['guestRegistrationId'] ) ? $guest_info['guestRegistrationId'] : '';

			if ( empty( $ns_user_reg_id ) ) {
				tt_add_error_log( '[SuiteScript:' . CHECKLIST_SCRIPT_ID . '] - Decline Travel Protection Failed', array( 'order_id' => $order_id, 'ns_reg_id' => $ns_user_reg_id, 'guest_index' => $guest_index ), array( 'status' => 'error', 'message' => __( 'No registration ID found for this order', 'trek-travel-theme' ) ) );
			} else {
				$ns_user_reg_args = array(
					'registrationId' => $ns_user_reg_id,
					'waiveInsurance' => 1,
				);

				// Store the travel protection declined status in the user order info
				self::update_bookings_table_with_waive_insurance( $order_id, $guest_index, 1 );

				// Update guest booking information in NetSuite.
				as_schedule_single_action( time() + $guest_index, 'tt_decline_travel_protection_ns', array( $ns_user_reg_args ) );
			}
		}

		wp_send_json_success( array( 'message' => __( 'Travel protection decline processed', 'trek-travel-theme' ) ) );
		wp_die();
	}

	/**
	 * Validate before adding to cart
	 *
	 * @param bool $passed
	 * @param int $product_id
	 * @param int $quantity
	 * @return bool
	 */
	public static function pre_add_to_cart_validation( $passed, $product_id, $quantity ) {
		if ( self::$is_protection_purchase ) {
			// Perform additional validation if needed
			// For example, check if the user has a trip in cart already
			
			// Log information about the request
			// tt_add_error_log(
			// 	'Travel Protection Purchase Validation', 
			// 	array(
			// 		'product_id' => $product_id,
			// 		'request_origin' => self::$request_origin,
			// 		'calculations' => self::$calculations
			// 	), 
			// 	array('status' => $passed)
			// );
		}
		
		return $passed;
	}

	/**
	 * After adding to cart action
	 *
	 * @param string $cart_item_key
	 * @param int $product_id
	 * @param int $quantity
	 * @param int $variation_id
	 * @param array $variation
	 * @param array $cart_item_data
	 */
	public static function after_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( self::$is_protection_purchase ) {
			// Perform any post-add actions
			do_action( 'tt_after_protection_add_to_cart', $cart_item_key, $product_id, self::$request_origin, self::$calculations );
			
			// Reset flags
			self::$is_protection_purchase = false;
		}
	}

	/**
	 * Add custom data to cart item
	 *
	 * @param array $cart_item_data
	 * @param int $product_id
	 * @param int $variation_id
	 * @return array
	 */
	public static function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		if ( self::$is_protection_purchase ) {
			// Store our custom data in the cart item
			// $cart_item_data['tt_protection_data'] = array(
			// 	'request_origin' => self::$request_origin,
			// 	'calculations' => self::$calculations,
			// 	'timestamp' => time(),
			// );
			// $accepted_p_ids = tt_get_line_items_product_ids();

			// $cart = WC()->cart->get_cart_contents();

			// foreach ($cart as $cart_item_id => $cart_item) {
			// 	$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_id);
			// 	$_product_name = $_product->get_name();
			// 	$product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
			// 	if (in_array($product_id, $accepted_p_ids)) {
			// 		$cart_item['tt_protection_data'] = $cart_item_data['tt_protection_data'];
			// 	}
			// }

			// // Store the updated cart.
			// WC()->cart->set_cart_contents( $cart );
			// // Recalculate the totals after modifying the cart.
			// WC()->cart->calculate_totals();
			// // Save the updated cart to the session.
			// WC()->cart->set_session();
			// // Update persistent_cart.
			// WC()->cart->persistent_cart_update();
		}

		return $cart_item_data;
	}

	/**
	 * Check if a guest wants insurance.
	 *
	 * @param int $order_id
	 * @param int $guest_index
	 * @return bool Whether the trip insurance is purchased by the guest.
	 */
	private static function is_trip_insurance_purchased( $order_id, $guest_index = 0 ) {
		$guests_booking_info = trek_get_user_order_info( null, $order_id );
		$guest_booking_info  = isset( $guests_booking_info[$guest_index] ) ? $guests_booking_info[$guest_index] : array();

		return ( isset( $guest_booking_info['wantsInsurance'] ) && $guest_booking_info['wantsInsurance'] == 1 );
	}

	/**
	 * Perform calculations before adding to cart
	 *
	 * @param int $order_id
	 */
	private static function perform_calculations( $order_id ) {
		// Get trip information from cart
		$trip_info = tt_get_trip_pid_sku_from_cart( $order_id );

		$tt_posted = isset( $trip_info['tt_posted'] ) ? $trip_info['tt_posted'] : array();

		$coupon_code     = strtolower( $tt_posted['coupon_code'] );
		$coupon          = new WC_Coupon( $coupon_code );

		$trip_product_id = isset( $trip_info['product_id'] ) ? $trip_info['product_id'] : 0;

		$trip_product = wc_get_product( $trip_product_id );
		$individualTripCost = 0;
		$sdate_info = $edate_info = '';

		if( $trip_product ){
			$individualTripCost = $trip_product->get_price();

			$trip_sdate = $trip_product->get_attribute('pa_start-date');
			$sdate_obj = explode('/', $trip_sdate);
			$sdate_info = array(
				'd' => $sdate_obj[0],
				'm' => $sdate_obj[1],
				'y' => substr(date('Y'),0,2).$sdate_obj[2]
			);
			$trip_edate = $trip_product->get_attribute('pa_end-date');
			$edate_obj = explode('/', $trip_edate);
			$edate_info = array(
				'd' => $edate_obj[0],
				'm' => $edate_obj[1],
				'y' => substr(date('Y'),0,2).$edate_obj[2]
			);
		}

		$insuredPerson = $insuredPerson_single = array();
		$effective_date = $expiration_date = '';
		if( $sdate_info && is_array($sdate_info) ){
			$effective_date = date('Y-m-d', strtotime(implode('-', $sdate_info)));
		}
		if( $edate_info && is_array($edate_info) ){
			$expiration_date = date('Y-m-d', strtotime(implode('-', $edate_info)));
		}

		$plan_id = get_field( 'plan_id', 'option' );

		if ( empty( $plan_id ) ) {
			$plan_id = 'TREKTRAVEL24';
		}

		$current_date = date( 'Y-m-d' );

		$trek_insurance_args = array(
			"coverage" => array(
				"effective_date"  => $effective_date,
				"expiration_date" => $expiration_date,
				"depositDate"     => $current_date,
				"destinations"    => array(
					array(
						"countryCode" => $tt_posted['shipping_country']
					)
				)
			),
			"language"             => "en-us",
			"planID"               => $plan_id,
			"returnTravelerQuotes" => true,
			"localDateTime"        => $current_date // With this parameter can avoid errors caused by Time Zone differences like: {"success":false,"responseMessage":"The Deposit Date can not occurr in the future","responseCode":"InvalidDepositDate"}.
		);

		$guest_insurance = isset( $tt_posted['trek_guest_insurance'] ) ? $tt_posted['trek_guest_insurance'] : array();
		$tt_total_insurance_amount  = 0;
		$is_travel_protection_count = 0;
		if ( isset( $guest_insurance ) && ! empty( $guest_insurance ) ) {
			$single_supplement_price = isset( $tt_posted['singleSupplementPrice'] ) ? $tt_posted['singleSupplementPrice'] : 0;
			// Remove dollar sign and commas
			$single_supplement_price_str = str_replace(array('$', ','), '', $single_supplement_price);
			// Convert the string to a float
			$single_supplement_price_float = (float) $single_supplement_price_str;
			// Convert to an integer (removing the decimal part)
			$single_supplement_price_int = (int) $single_supplement_price_float;

			foreach ( $guest_insurance as $guest_insurance_k => $guest_insurance_val ) {
				$individualTripCost = $trip_product->get_price();
				$occupants          = $tt_posted['occupants'];
				$trek_insurance_args["insuredPerson"] = array();
				$bike_gears         = $tt_posted['bike_gears'];
				if ( isset( $tt_posted['bikeUpgradePrice'] ) ) {
					$bike_upgrade_price = (int) $tt_posted['bikeUpgradePrice'];
				} else {
					$bike_upgrade_price =  0;
				}
				if ($guest_insurance_k == 'primary') {
					// TODO: Add logic to handle primary guest insurance
					// We should check if the primary guest has travel protection. The status should be taken from the bookings table.
					$guest_insurance_val['is_travel_protection'] = self::is_trip_insurance_purchased( $order_id, 0 ) ? 0 : 1;

					$primary_guest_bike = $bike_gears['primary'];
					$bike_type_info     = tt_ns_get_bike_type_info( $primary_guest_bike['bikeTypeId'] );
					if ($guest_insurance_val['is_travel_protection'] == 1) {
						$is_travel_protection_count++;
					}
					if ( ( isset( $occupants['private'] ) && is_array( $occupants['private'] ) && in_array( 0, $occupants['private'] ) )
					|| ( isset( $occupants['roommate'] ) && is_array( $occupants['roommate'] ) && in_array( 0, $occupants['roommate'] ) ) ) {
						$individualTripCost = $individualTripCost + $single_supplement_price_int;
					}
					if ( $bike_type_info && isset( $bike_type_info['isBikeUpgrade'] ) && $bike_type_info['isBikeUpgrade'] == 1 ) {
						$individualTripCost = $individualTripCost + $bike_upgrade_price;
					}
					if ( $coupon && tt_is_coupon_applied( $coupon_code ) ) {
						// Check if the coupon exists
						if ( $coupon->get_id() > 0 ) {
							// Coupon exists, retrieve its details
							$coupon_amount = $coupon->get_amount();
						}
				
						$individualTripCost = $individualTripCost - $coupon_amount;
					}
					//if ($guest_insurance_val['is_travel_protection'] == 1) {
					$insuredPerson[] = array(
						"address" => [
							"stateAbbreviation" => $tt_posted['shipping_state'],
							"countryAbbreviation" => $tt_posted['shipping_country']
						],
						"dob" => $tt_posted['custentity_birthdate'],
						"individualTripCost" => $individualTripCost
					);
					$insuredPerson_single = [];
					$insuredPerson_single[] = array(
						"address" => [
							"stateAbbreviation" => $tt_posted['shipping_state'],
							"countryAbbreviation" => $tt_posted['shipping_country']
						],
						"dob" => $tt_posted['custentity_birthdate'],
						"individualTripCost" => $individualTripCost
					);
					$trek_insurance_args["insuredPerson"]      = $insuredPerson_single;
					$archinsuranceResPP                        = tt_set_calculate_insurance_fees_api( $trek_insurance_args );
					$arcBasePremiumPP                          = isset( $archinsuranceResPP['basePremium'] ) ? $archinsuranceResPP['basePremium'] : 0;
					$guest_insurance['primary']['basePremium'] = $arcBasePremiumPP;
					if ( $guest_insurance_val['is_travel_protection'] == 1 ) {
						$tt_total_insurance_amount += $arcBasePremiumPP;
					}
				} else {
					foreach ($guest_insurance_val as $guest_key => $guest_insurance_Data) {
						// TODO: Add logic to handle guest insurance
						$guest_insurance_Data['is_travel_protection'] = self::is_trip_insurance_purchased( $order_id, $guest_key ) ? 0 : 1;
						$individualTripCost                           = $trip_product->get_price();
						$guestInfo                                    = $tt_posted['guests'][$guest_key];
						$guest_bike                                   = $bike_gears['guests'][$guest_key];
						$bike_type_info                               = tt_ns_get_bike_type_info( $guest_bike['bikeTypeId'] );
						if ($guest_insurance_Data['is_travel_protection'] == 1) {
							$is_travel_protection_count++;
						}
						if ( ( isset( $occupants['private'] ) && is_array( $occupants['private'] ) && in_array( $guest_key, $occupants['private'] ) )
						|| ( isset( $occupants['roommate'] ) && is_array( $occupants['roommate'] ) && in_array( $guest_key, $occupants['roommate'] ) ) ) {
							$individualTripCost = $individualTripCost + $single_supplement_price_int;
						}
						if ( $bike_type_info && isset( $bike_type_info['isBikeUpgrade'] ) && $bike_type_info['isBikeUpgrade'] == 1 ) {
							$individualTripCost = $individualTripCost + $bike_upgrade_price;
						}
						if ( $coupon && tt_is_coupon_applied( $coupon_code ) ) {
							// Check if the coupon exists
							if ( $coupon->get_id() > 0 ) {
								// Coupon exists, retrieve its details
								$coupon_amount = $coupon->get_amount();
							}
					
							$individualTripCost = $individualTripCost - $coupon_amount;
						}
						$insuredPerson[] = array(
							"address" => [
								"stateAbbreviation" => $tt_posted['shipping_state'],
								"countryAbbreviation" => $tt_posted['shipping_country']
							],
							"dob" => $guestInfo['guest_dob'],
							"individualTripCost" => $individualTripCost
						);
						$insuredPerson_single = [];
						$insuredPerson_single[] = array(
							"address" => [
								"stateAbbreviation" => $tt_posted['shipping_state'],
								"countryAbbreviation" => $tt_posted['shipping_country']
							],
							"dob" => $guestInfo['guest_dob'],
							"individualTripCost" => $individualTripCost
						);
						$trek_insurance_args["insuredPerson"]                 = $insuredPerson_single;
						$archinsuranceResPG                                   = tt_set_calculate_insurance_fees_api( $trek_insurance_args );
						$arcBasePremiumPG                                     = isset( $archinsuranceResPG['basePremium'] ) ? $archinsuranceResPG['basePremium'] : 0;
						$guest_insurance['guests'][$guest_key]['basePremium'] = $arcBasePremiumPG;
						if ( $guest_insurance_Data['is_travel_protection'] == 1 ) {
							$tt_total_insurance_amount += $arcBasePremiumPG;
						}
					}
				}
			}
		}
		$trek_insurance_args["insuredPerson"] = $insuredPerson;
		$arcBasePremium                       = $tt_total_insurance_amount && $tt_total_insurance_amount > 0 ? $tt_total_insurance_amount : 0;
		
		// Initialize calculations array
		$calculations = array(
			'order_id'               => $order_id,
			// 'travelers'              => array(),
			// 'total_insurance_amount' => 0,
			'trip_cost'              => 0,
			'tp_price'               => $arcBasePremium,
		);

		// Process travelers info
		if ( ! empty( $guest_insurance ) ) {
			foreach ( $guest_insurance as $guest_key => $guest_data ) {
				// Get traveler information
				if( 'primary' === $guest_key ) {
					$primary_is_tp_purchased = self::is_trip_insurance_purchased( $order_id, 0 );
					$traveler_info = array(
						'first_name'           => isset($tt_posted['first_name']) ? $tt_posted['first_name'] : '',
						'last_name'            => isset($tt_posted['last_name']) ? $tt_posted['last_name'] : '',
						'email'                => isset($tt_posted['email']) ? $tt_posted['email'] : '',
						'dob'                  => isset($tt_posted['custentity_birthdate']) ? $tt_posted['custentity_birthdate'] : '',
						'insurance_amount'     => isset($guest_data['basePremium']) ? $guest_data['basePremium'] : 0,
						'is_tp_purchased'      => $primary_is_tp_purchased ? 1 : 0, // 1 if purchased, 0 if not purchased.
						'is_travel_protection' => $primary_is_tp_purchased ? 0 : 1, // 0 if not wants insurance, 1 if wants insurance.
					);
					$calculations['travelers'][$guest_key] = $traveler_info;
					$calculations['total_insurance_amount'] += floatval($traveler_info['insurance_amount']);
				} else {
					$guests_traveler_info = array();
					foreach ( $guest_data as $_guest_key => $guest_data ) {
						$guest_is_tp_purchased = self::is_trip_insurance_purchased( $order_id, $_guest_key );
						$guests_traveler_info[$_guest_key] = array(
							'first_name'           => isset($tt_posted['guests'][$_guest_key]['guest_fname']) ? $tt_posted['guests'][$_guest_key]['guest_fname'] : '',
							'last_name'            => isset($tt_posted['guests'][$_guest_key]['guest_lname']) ? $tt_posted['guests'][$_guest_key]['guest_lname'] : '',
							'email'                => isset($tt_posted['guests'][$_guest_key]['guest_email']) ? $tt_posted['guests'][$_guest_key]['guest_email'] : '',
							'dob'                  => isset($tt_posted['guests'][$_guest_key]['guest_dob']) ? $tt_posted['guests'][$_guest_key]['guest_dob'] : '',
							'insurance_amount'     => isset($guest_data['basePremium']) ? $guest_data['basePremium'] : 0,
							'is_tp_purchased'      => $guest_is_tp_purchased ? 1 : 0, // 1 if purchased, 0 if not purchased.
							'is_travel_protection' => $guest_is_tp_purchased ? 0 : 1, // 0 if not wants insurance, 1 if wants insurance.
						);
						$calculations['travelers'][$guest_key] = $guests_traveler_info;
						$calculations['total_insurance_amount'] += floatval($guests_traveler_info[$_guest_key]['insurance_amount']);
					}
				}
			}
		}
		
		// Get trip cost from the product in cart
		if (isset($trip_info['product_id'])) {
			$product = wc_get_product($trip_info['product_id']);
			if ($product) {
				$calculations['trip_cost'] = $product->get_price();
			}
		}
		
		// Store the calculations
		self::$calculations = $calculations;
	}

	/**
	 * Before calculate totals action
	 *
	 * @param WC_Cart $cart
	 */
	public static function before_calculate_totals( $cart ) {
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['tt_protection_data'] ) && is_array( $cart_item['tt_protection_data'] ) ) {
				$tp_price = 0;

				foreach ( $cart_item['tt_protection_data']['travelers'] as $guest_idx => $traveler_data ) {
					if( 'primary' === $guest_idx ) {
						if( isset( $traveler_data['is_travel_protection'] ) && $traveler_data['is_travel_protection'] == 1 ) {
							if (self::is_devrix_email( $traveler_data['email'] )) {
								// If the email is a DevriX email, set the price to 2.
								$tp_price += 2;
							} else {
								// Add the insurance amount for the primary guest
								$tp_price += floatval( $traveler_data['insurance_amount'] );
							}
						}
					} else {
						// Loop through each guest's insurance data
						foreach ( $traveler_data as $traveler ) {
							if( isset( $traveler['is_travel_protection'] ) && $traveler['is_travel_protection'] == 1 ) {
								if (self::is_devrix_email( $traveler['email'] )) {
									// If the email is a DevriX email, set the price to 2.
									$tp_price += 2;
								} else {
									// Add the insurance amount for each guest
									$tp_price += floatval( $traveler['insurance_amount'] );
								}
							}
						}
					} 
				}

				$cart_item['tt_protection_data']['tp_price'] = $tp_price;
				$cart_item['data']->set_price( $tp_price );
			}
		}
	}

	/**
	 * Get the request origin
	 *
	 * @return string
	 */
	public static function get_request_origin() {
		return self::$request_origin;
	}

	/**
	 * Check if current process is a protection purchase
	 *
	 * @return bool
	 */
	public static function is_protection_purchase() {
		return self::$is_protection_purchase;
	}

	/**
	 * Get the calculations
	 *
	 * @return array
	 */
	public static function get_calculations() {
		return self::$calculations;
	}

	/**
	 * After checkout validation
	 *
	 * @param array $fields An array of posted data.
	 * @param WP_Error $errors Validation errors.
	 */
	public static function after_checkout_validation( $fields, $errors ) {

		// if any validation errors
		if( ! empty( $errors->get_error_codes() ) ) {

			// remove all of them
			foreach( $errors->get_error_codes() as $code ) {
				$errors->remove( $code );
			}

		}

		// Perform TPP Specific Validation if needed.
	}

	/**
	 * After successful checkout
	 *
	 * @param int $order_id Order ID.
	 */
	public static function after_successful_checkout( $order_id ) {

		$accepted_p_ids = tt_get_line_items_product_ids();

		$order              = new WC_Order($order_id);
		$items              = $order->get_items();
		$tt_protection_data = array();
		$tt_modal_checkout  = array();
		$tt_related_orders  = array();
		foreach ( $items as $item_id => $item ) {
			if( in_array( $item['product_id'], $accepted_p_ids ) ){
				$tt_protection_data = wc_get_order_item_meta( $item_id, 'tt_protection_data', true );
				$tt_modal_checkout  = wc_get_order_item_meta( $item_id, 'tt_modal_checkout', true );
				$tt_related_orders  = wc_get_order_item_meta( $item_id, 'tt_related_orders', true );
			}
		}

		if (!empty($tt_protection_data)) {
			update_post_meta($order_id, 'tt_protection_data', $tt_protection_data);
		} else {
			return; // If no protection data, exit early and continue with the booking flow.
		}

		if (!empty($tt_modal_checkout)) {
			update_post_meta($order_id, 'tt_modal_checkout', $tt_modal_checkout);
		}

		if (!empty($tt_related_orders)) {
			// Set the Trip Order ID.
			update_post_meta($order_id, 'tt_related_orders', $tt_related_orders);
			$trip_order_id = $tt_related_orders[0];
			$trip_order_related_orders = get_post_meta($trip_order_id, 'tt_related_orders', true);
			if ( ! empty( $trip_order_related_orders ) ) {
				$trip_order_related_orders[] = $order_id;
			} else {
				$trip_order_related_orders = array( $order_id );
			}
			// Update the trip order with the related orders.
			update_post_meta($trip_order_id, 'tt_related_orders', $trip_order_related_orders);
		}

		// Perform any actions needed after successful checkout
		// For example, send to NetSuite, update order status/meta, etc.
		
		// First needs to update the bookings table.
		$ns_order_status  = get_post_meta( $order_id,'tt_wc_order_ns_status', true ); // Indicate if the order is sent for sync to NetSuite.
		if ( $ns_order_status !== 'true' ) {
			$travelers        = isset( $tt_protection_data['travelers'] ) ? $tt_protection_data['travelers'] : array();
			$booking_order_id = isset( $tt_protection_data['order_id'] ) ? $tt_protection_data['order_id'] : 0;

			foreach ( $travelers as $traveler_key => $traveler_data ) {
				if ( 'primary' === $traveler_key ) {
					// Update the primary guest booking
					$guest_index = 0; // Primary guest index is always 0
					self::update_bookings_table( $booking_order_id, $guest_index, $traveler_data );
				} else {
					// Update each guest booking
					foreach ( $traveler_data as $guest_index => $guest_data ) {
						self::update_bookings_table( $booking_order_id, $guest_index, $guest_data );
					}
				}
			}
			// Trigger the async action to send to NS. The function will be implemented in the plugin.

			tt_add_error_log('[Start] - NS Booking Update', array( $order_id ), array( 'dateTime' => date('Y-m-d H:i:s') ) );
			as_enqueue_async_action( 'tt_trigger_ns_booking_update', array( $order_id ), '[Sync] - NetSuite Booking Update' );

			update_post_meta( $order_id, 'tt_wc_order_ns_status', 'true' ); // Mark the current order as sent to NetSuite.
		}

		// Clear the cart after successful checkout
		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}
	}

	/**
	 * Add custom field to order item meta
	 *
	 * @param WC_Order_Item_Product $item Order item.
	 * @param string|int $cart_item_key Cart item key.
	 * @param mixed $values Cart item data.
	 * @param WC_Order $order Order instance.
	 */
	public static function add_product_custom_field_to_order_item_meta( $item, $cart_item_key, $values, $order ) {
		if ( array_key_exists( 'tt_protection_data', $values ) ) {
			$item->add_meta_data( 'tt_protection_data', $values['tt_protection_data'] );
		}

		if ( array_key_exists( 'tt_modal_checkout', $values ) ) {
			$item->add_meta_data( 'tt_modal_checkout', $values['tt_modal_checkout'] );
		}

		if ( array_key_exists( 'tt_related_orders', $values ) ) {
			$item->add_meta_data( 'tt_related_orders', $values['tt_related_orders'] );
		}
	}

	/**
	 * Get the cart item data
	 *
	 * @param string $cart_item_key Cart item key.
	 * @return array
	 */
	public static function get_cart_item_data( $cart_item_key ) {
		$cart_item = WC()->cart->get_cart_item( $cart_item_key );
		if ( isset( $cart_item['tt_protection_data'] ) ) {
			return $cart_item['tt_protection_data'];
		}

		return array();
	}

	/**
	 * Pre-billing address handler
	 */
	public static function pre_billing_address_handler() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'tt_modal_checkout_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'trek-travel-theme' ) ) );
		}
		$is_pre_billing_address = isset( $_POST['is_pre_billing_address'] ) ? sanitize_text_field( $_POST['is_pre_billing_address'] ) : true;
		$is_pre_billing_address = $is_pre_billing_address === 'true' ? false : true;

		$output = wc_get_template_html( 'inc/trek-modal-checkout/templates/billing-address-form.php', array( 'is_pre_billing_address' => (bool) $is_pre_billing_address ) );
		wp_send_json_success( array(
			'billing_address' => $output,
		) );
	}

	/**
	 * Update bookings table with traveler data.
	 *
	 * @param int $booking_order_id Order ID of the Booking for this traveler.
	 * @param int $guest_index Guest index.
	 * @param array $traveler_data Traveler data.
	 *
	 * @return bool True if the update was successful, false otherwise.
	 */
	private static function update_bookings_table( $booking_order_id = 0, $guest_index = 0, $traveler_data = array() ) {
		// Check if the order ID, guest index, and traveler data are valid
		if ( empty( $booking_order_id ) || ! is_numeric( $guest_index ) || empty( $traveler_data ) || ! is_array( $traveler_data ) ) {
			return false;
		}

		$is_tp_purchased = isset( $traveler_data['is_tp_purchased'] ) ? $traveler_data['is_tp_purchased'] : 0;
		if ( $is_tp_purchased == 1 ) {
			// If travel protection is purchased, we don't need to update the bookings table.
			return true;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'guest_bookings';
		$where      = array( 'order_id' => $booking_order_id, 'guest_index_id' => $guest_index );

		$booking_data = array(
			'wantsInsurance'  => isset( $traveler_data['is_travel_protection'] ) ? (int) $traveler_data['is_travel_protection'] : 0,
			'waive_insurance' => isset( $traveler_data['is_travel_protection'] ) && $traveler_data['is_travel_protection'] == 1 ? 0 : 1, // 1 if waived insurance, 0 if not waived.
			'insuranceAmount' => isset( $traveler_data['insurance_amount'] ) ? $traveler_data['insurance_amount'] : 0,
		);

		$is_updated = $wpdb->update( $table_name, $booking_data, $where );

		if ( $wpdb->last_error ) {
			tt_add_error_log( '[Failed] Update Booking', array( 'order_id' => $booking_order_id, 'guest_index_id' => $guest_index, 'traveler_data' => $traveler_data ), array( 'last_error' => $wpdb->last_error ) );
		}

		if ( $is_updated ) {
			return true;
		}

		return false;
	}

	/**
	 * Update bookings table with waive insurance status.
	 *
	 * @param int $booking_order_id Order ID of the Booking for this traveler.
	 * @param int $guest_index Guest index.
	 * @param int $decline_status Waive insurance status (1 if waived, 0 if not waived).
	 *
	 * @return bool True if the update was successful, false otherwise.
	 */
	private static function update_bookings_table_with_waive_insurance( $booking_order_id, $guest_index = 0, $decline_status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'guest_bookings';
		$where      = array( 'order_id' => $booking_order_id, 'guest_index_id' => $guest_index );

		$booking_data = array(
			'waive_insurance' => $decline_status, // 1 if waived insurance, 0 if not waived.
		);

		$is_updated = $wpdb->update( $table_name, $booking_data, $where );

		if ( $wpdb->last_error ) {
			tt_add_error_log( '[Failed] Update Booking Waive Insurance', array( 'order_id' => $booking_order_id, 'guest_index_id' => $guest_index, 'decline_status' => $decline_status ), array( 'last_error' => $wpdb->last_error ) );
		}

		if ( ! $is_updated ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if an email belongs to the devrix.com domain
	 *
	 * @param string $email Email address to check.
	 * @return bool True if the email belongs to devrix.com domain, false otherwise.
	 */
	private static function is_devrix_email( $email ) {
		// Add filter to disable the check for devrix.com emails.
		if ( apply_filters( 'tt_disable_devrix_email_check', true ) ) {
			return false;
		}

		// Check if the email is empty or not a string
		if ( empty( $email ) || ! is_string( $email ) ) {
			return false;
		}

		// Validate email format first
		if ( ! is_email( $email ) ) {
			return false;
		}

		// Extract the domain part from the email
		$email_parts = explode( '@', $email );
		$domain      = isset( $email_parts[1] ) ? strtolower( $email_parts[1] ) : '';

		// Check if the domain is devrix.com
		return 'devrix.com' === $domain;
	}

	/**
	 * Filter the return URL after a successful WooCommerce checkout.
	 *
	 * Redirects the user to My Account page with custom query parameters
	 * instead of the default Thank You page.
	 *
	 * @param string          $return_url Default return URL.
	 * @param WC_Order|string $order      Order object or order ID.
	 * @return string Modified return URL.
	 */
	public static function custom_wc_redirect_to_my_account( $return_url, $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order instanceof WC_Order ) {
			return $return_url;
		}

		$accepted_p_ids = tt_get_line_items_product_ids();

		$items             = $order->get_items();
		$tt_protection_data = array();
		foreach ( $items as $item_id => $item ) {
			if ( in_array( $item['product_id'], $accepted_p_ids ) ) {
				$tt_protection_data = wc_get_order_item_meta( $item_id, 'tt_protection_data', true );
			}
		}

		if ( empty( $tt_protection_data ) ) {
			return $return_url; // If no protection data, exit early and continue with the booking flow.
		}

		$order_id  = $order->get_id();
		$order_key = $order->get_order_key();

		$custom_url = wc_get_page_permalink( 'myaccount' );
		$custom_url = add_query_arg(
			array(
				'tpp_thankyou' => '1',
				'order_id'     => $order_id,
				'order_key'    => $order_key,
			),
			$custom_url
		);

		return $custom_url;
	}
}

// Initialize the class
Trek_Modal_Checkout::init();

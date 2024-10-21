<?php
/**
 * Trek Travel Woocommerce Persistent Cart Controller
 *
 * Add date of the first add to cart, validate date expiration of the persistent cart, clear the cart.
 */

defined( 'ABSPATH' ) || exit;

class TT_WC_Persistent_Cart_Controller {

	/**
	 * The class instance.
	 */
	private static $instance      = null;

	/**
	 * The time to clear the persistent cart.
	 */
	private static $clear_pc_time = '- 10 days';

	/**
	 * Date format used to store the add_to_cart date.
	 */
	private static $date_format   = 'm/d/Y';

	/**
	 * The persistent cart user meta key name.
	 */
	private static $tt_pc_user_mk = 'tt_wc_pc_add_to_cart_date';

	/**
	 * Get class instance.
	 */
	public static function ttnsw_get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new TT_WC_Persistent_Cart_Controller();
		}
		return self::$instance;
	}

	/**
	 * Consturct Function.
	 */
	public function __construct() {

		add_action( 'tt_set_add_to_cart_date', array( $this, 'set_add_to_cart_date' ) );
		add_action( 'tt_clear_persistent_cart', array( $this, 'clear_persistent_cart' ) );
		add_filter( 'tt_is_persistent_cart', array( $this, 'is_persistent_cart' ) );
		add_filter( 'tt_is_persistent_cart_valid', array( $this, 'is_persistent_cart_valid' ) );
	}

	/**
	 * Get teh add_to_cart date.
	 *
	 * @return mixed The value of meta data field. False for an invalid $user_id (non-numeric, zero, or negative value). An empty string if a valid but non-existing user ID is passed.
	 */
	private function get_tt_add_to_cart_date() {
		return get_user_meta( get_current_user_id(), self::$tt_pc_user_mk, true );
	}

	/**
	 * Update the add_to_cart date.
	 *
	 * @param array $date_now The current date with format m/d/Y.
	 *
	 * @return void
	 */
	private function update_add_to_cart_date( $date_now ) {
		update_user_meta( get_current_user_id(), self::$tt_pc_user_mk, $date_now );
	}

	/**
	 * Delete the add_to_cart date.
	 *
	 * @return void
	 */
	private function delete_add_to_cart_date() {
		delete_user_meta( get_current_user_id(), self::$tt_pc_user_mk );
	}

	/**
	 * Destroy cart session data.
	 *
	 * @link https://woocommerce.github.io/code-reference/classes/WC-Cart-Session.html
	 *
	 * @return void
	 */
	private function tt_destroy_cart_session() {
		WC()->session->set( 'cart', null );
		WC()->session->set( 'cart_totals', null );
		WC()->session->set( 'applied_coupons', null );
		WC()->session->set( 'coupon_discount_totals', null );
		WC()->session->set( 'coupon_discount_tax_totals', null );
		WC()->session->set( 'removed_cart_contents', null );
		WC()->session->set( 'order_awaiting_payment', null );
	}

	/**
	 * Set the add_to_cart date in the user's meta if is not set.
	 *
	 * @return void
	 */
	public function set_add_to_cart_date() {
		$add_to_cart_date = self::get_tt_add_to_cart_date();

		if( ! empty( $add_to_cart_date ) ) {
			// The date has been set already. Do not rewrite it.
			return;
		}

		$date_now = date( self::$date_format );
		self::update_add_to_cart_date( $date_now );
	}

	/**
	 * Check if the persistent cart exists.
	 *
	 * @param bool $is_pc Is the persistent cart exists.
	 *
	 * @return bool
	 */
	public function is_persistent_cart( $is_pc = true ) {
		$cart_result           = get_user_meta( get_current_user_id(), '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
		$cart                  = WC()->session->get( 'cart', null );
		$persistent_cart_count = isset( $cart_result['cart'] ) && $cart_result['cart'] ? count( $cart_result['cart'] ) : 0;

		if ( ! is_null( $cart ) && $persistent_cart_count > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the cart is valid based on the date of expiration.
	 *
	 * @param bool $is_valid Is the persistent cart valid.
	 *
	 * @return bool
	 */
	public function is_persistent_cart_valid( $is_valid = true ) {

		$add_to_cart_date = self::get_tt_add_to_cart_date();

		if ( empty( $add_to_cart_date ) ) {
			// Backward compatibility for existing carts that still don't have a date.
			if ( self::is_persistent_cart() ) {
				// Check if there really is a cart before adding the date.
				self::update_add_to_cart_date( date( self::$date_format ) );
			}
			return true;
		}

		$atc_date_time   = new DateTime( $add_to_cart_date );
		$check_date_time = new DateTime( date( self::$date_format, strtotime( self::$clear_pc_time ) ) );

		// "<" - clear the cart on the day after the check date; "<=" - clear the cart on the check day.
		if ( $atc_date_time < $check_date_time ) {
			// The cart is not valid; it should clear it.
			return false;
		}

		return $is_valid;
	}

	/**
	 * Clears the whole cart, cart session, and persistent cart.
	 * Clears the user's add_to_cart date meta.
	 *
	 * @return void
	 */
	public function clear_persistent_cart() {
		// Clear the cart.
		WC()->cart->empty_cart();

		// Cleanup old session.
		WC()->session->destroy_session();
		self::tt_destroy_cart_session();

		// Clear the add_to_cart_date from the user's meta.
		self::delete_add_to_cart_date();
		
		/**
		 * Let it rest for a bit, clear the session peacefully, and then continue with adding the new product.
		 * I tried everything I could think of, but this was the last option, and it seems to be working (>_<)
		 * I suppose the issue comes from the fact that the checkout opens in a new tab.
		 */
		sleep(1);
	}
}

/**
 * Make an instance of TT WC Persistent Cart Controller.
 */
function TT_WC_Persistent_Cart_Controller() {
	return TT_WC_Persistent_Cart_Controller::ttnsw_get_instance();
}

TT_WC_Persistent_Cart_Controller();

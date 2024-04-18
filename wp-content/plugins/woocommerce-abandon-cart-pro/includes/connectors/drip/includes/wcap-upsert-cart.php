<?php
/**
 * Insert/Update Cart in Drip.
 *
 * @package Connectors/Drip/Actions
 */

/**
 * Insert/Update Carts in Drip.
 */
class Wcap_Drip_Upsert_Cart_Action {

	/**
	 * Class Instance.
	 *
	 * @var $ins
	 */
	private static $ins = null;

	/**
	 * Construct.
	 */
	public function __construct() {
		$connector_settings = Wcap_Connectors_Common::wcap_get_connectors_data( 'wcap_drip' );
		if ( empty( $connector_settings ) || 'active' !== $connector_settings['status'] ) {
			return false;
		}
		// Guest email address captured.
		add_action( 'wcap_guest_cart_history_after_insert', array( &$this, 'wcap_guest_inserted' ), 101, 1 );
		// Guest Details updated.
		add_action( 'wcap_after_update_guest_cart_history', array( &$this, 'wcap_guest_updated' ), 101, 2 );
		// Cart Updated.
		add_action( 'wcap_after_update_cart_history', array( &$this, 'wcap_cart_updated' ), 101, 2 );
		// Cart Inserted.
		add_action( 'wcap_cart_history_after_insert', array( &$this, 'wcap_cart_inserted' ), 101, 1 );
	}

	/**
	 * Get instance.
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Guest record inserted.
	 *
	 * @param int $user_id - User ID.
	 */
	public function wcap_guest_inserted( $user_id = 0 ) {

		if ( $user_id > 0 ) {

			$abandoned_id = wcap_get_abandoned_id_from_user_id( $user_id );
			if ( $abandoned_id > 0 ) {
				$this->wcap_prepare_cart_details( $abandoned_id );
			}
		}
	}

	/**
	 * Guest record updated.
	 *
	 * @param array $value - Updated columns and their values.
	 * @param array $where - Where condition for update.
	 */
	public function wcap_guest_updated( $value = array(), $where = array() ) {
		$user_id = 0;
		if ( is_array( $where ) && array_key_exists( 'id', $where ) ) {
			$user_id = $where['id'];
		} elseif ( is_array( $value ) && array_key_exists( 'id', $value ) ) {
			$user_id = $value['id'];
		}

		$abandoned_id = wcap_get_abandoned_id_from_user_id( $user_id );
		if ( $abandoned_id > 0 ) {
			$this->wcap_prepare_cart_details( $abandoned_id );
		}
	}

	/**
	 * Abandoned Cart record inserted.
	 *
	 * @param int $abandoned_id - Abandoned Cart ID.
	 */
	public function wcap_cart_inserted( $abandoned_id = 0 ) {

		if ( $abandoned_id > 0 ) {
			$this->wcap_prepare_cart_details( $abandoned_id );
		}
	}

	/**
	 * Abandoned Cart record updated.
	 *
	 * @param array $value - Updated columns and their values.
	 * @param array $where - Where condition for update.
	 */
	public function wcap_cart_updated( $value = array(), $where = array() ) {
		$abandoned_id = 0;
		if ( is_array( $where ) && array_key_exists( 'id', $where ) ) {
			$abandoned_id = $where['id'];
		} elseif ( is_array( $value ) && array_key_exists( 'id', $value ) ) {
			$abandoned_id = $value['id'];
		}

		if ( $abandoned_id > 0 ) {
			$this->wcap_prepare_cart_details( $abandoned_id, false, $value );
		}
	}

	/**
	 * Prepare cart details for MC.
	 *
	 * @param int   $abandoned_id - Abandoned Cart ID.
	 * @param bool  $return_status - true for manual sync.
	 * @param array $value - Columns Updated.
	 */
	public function wcap_prepare_cart_details( $abandoned_id, $return_status = false, $value = array() ) {
		// Fetch the contact data.
		$common_inst      = Wcap_Connectors_Common::get_instance();
		$customer_details = $common_inst->wcap_get_contact_data( $abandoned_id );
		if ( isset( $customer_details['email'] ) && '' === $customer_details['email'] ) {
			return;
		}

		// Depending on the cart status, the record in MC will be inserted or updated.
		$cart_sync_id = $common_inst->wcap_get_cart_status( $abandoned_id, 'drip' );
		$cart_details = $this->wcap_get_cart( $abandoned_id );

		if ( is_array( $customer_details ) && count( $customer_details ) > 0 && is_array( $cart_details ) && count( $cart_details ) > 0 ) {
			$email  = isset( $customer_details['email'] ) ? $customer_details['email'] : '';
			$action = $cart_sync_id > 0 ? 'update' : 'insert';
			$status = $this->wcap_create_cart_in_dp( $abandoned_id, $email, $cart_details, $action, $cart_sync_id );
			if ( $return_status ) {
				return $status;
			}
		}
	}

	/**
	 * Raise the request for API call to MC.
	 *
	 * @param int    $abandoned_id - Abandoned ID.
	 * @param string $email - Email Address.
	 * @param array  $cart_details - Cart Details, URL, products etc.
	 * @param string $action - Insert/Update cart in MC.
	 * @param int    $cart_sync_id - Cart Sync ID in Connectors Sync Table.
	 * @param array  $merge_fields - Merge fields.
	 * @param array  $interests - Interests in MC.
	 */
	public function wcap_create_cart_in_dp( $abandoned_id, $email, $cart_details, $action = 'insert', $cart_sync_id = 0, $merge_fields = array(), $interests = array() ) {
		$connector_dp = Wcap_Drip::get_instance();
		$call         = $connector_dp->registered_calls['wcap_drip_upsert_cart'];
		// Fetch the connector data.
		$connector_settings = Wcap_Connectors_Common::wcap_get_connectors_data( 'wcap_drip' );
		$api_token          = isset( $connector_settings['api_token'] ) ? $connector_settings['api_token'] : '';
		$account_id         = isset( $connector_settings['account_id'] ) ? $connector_settings['account_id'] : '';
		$status             = isset( $connector_settings['status'] ) ? $connector_settings['status'] : '';

		if ( 'active' === $status && '' !== $email && $abandoned_id > 0 && '' !== $cart_details['checkout_url'] ) {
			$params = array(
				'api_token'      => $api_token,
				'account_id'     => $account_id,
				'email'          => $email,
				'cart_id'        => $abandoned_id,
				'abandoned_time' => $cart_details['abandoned_time'],
				'cart_url'       => $cart_details['checkout_url'],
				'cart_items'     => $cart_details['cart_items'],
				'cart_total'     => $cart_details['cart_total'],
			);

			$params['action'] = 'update' === $action ? 'updated' : 'created';
			if ( is_array( $merge_fields ) && ! empty( $merge_fields ) ) {
				$params['merge_fields'] = $merge_fields;
			}

			if ( is_array( $interests ) && ! empty( $interests ) ) {
				$params['interests'] = $interests;
			}

			$call->set_data( $params );
			$result = $call->process();

			// Update the status of the integration for the cart in the table.
			$message = $result['response'];
			if ( 200 != absint( $result['response'] ) ) {
				$error  = __( 'Error Response Code: ', 'woocommerce-ac' ) . $result['response'] . __( '. Drip Error: ', 'woocommerce-ac' );
				$error .= is_array( $result['response'] ) && isset( $result['response']['message'] ) ? $result['response']['message'] : __( 'No Response from Drip. ', 'woocommerce-ac' );
				$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Wcap Error: ', 'woocommerce-ac' ) . $result['body'][0] : '';

				$connector_cart_id = '';
				$status            = 'failed';
				if ( isset( $result['response']['message'] ) ) {
					$message .= ': ' . $result['response']['message'];
				}
			} else {
				// Update the details in the plugin table.
				$connector_cart_id = $result['body']['request_ids'][0];
				$status            = 'complete';
				$message          .= 'insert' === $action ? ' Created a cart.' : ' Updated a cart';
			}
			global $wpdb;
			if ( 'update' === $action ) {
				$wpdb->update( // phpcs:ignore
					$wpdb->prefix . 'ac_connector_sync',
					array(
						'sync_date'         => current_time( 'timestamp' ), // phpcs:ignore
						'status'            => $status,
						'sync_data'         => wp_json_encode( $params ),
						'message'           => $message,
					),
					array(
						'id' => $cart_sync_id
					)
				);
			} else {
				$wpdb->insert( // phpcs:ignore
					$wpdb->prefix . 'ac_connector_sync',
					array(
						'cart_id'           => $abandoned_id,
						'connector_cart_id' => $connector_cart_id,
						'connector_name'    => 'drip',
						'sync_date'         => current_time( 'timestamp' ), // phpcs:ignore
						'status'            => $status,
						'sync_data'         => wp_json_encode( $params ),
						'message'           => $message,
					)
				);
			}
			return $status;
		}
	}

	/**
	 * Get Cart Data.
	 *
	 * @param int   $abandoned_id - Abandoned Cart ID.
	 * @param array $customer_details - Customer Details.
	 * @return array $cart_details - Cart Details.
	 */
	public function wcap_get_cart( $abandoned_id ) {
		$cart_history = wcap_get_data_cart_history( $abandoned_id );
		$cart_details = array();
		if ( $cart_history ) {
			$cart_details['abandoned_time'] = $cart_history->abandoned_cart_time;
			$cart_details['checkout_url']   = $cart_history->checkout_link;

			$abandoned_cart_info = json_decode( $cart_history->abandoned_cart_info, true );
			$cart_items          = array();
			$cart_total          = 0;
			foreach ( $abandoned_cart_info['cart'] as $c_key => $c_value ) {
				$line_total           = $c_value['line_total'];
				$cart_items[ $c_key ] = array(
					'product_id'   => $c_value['product_id'],
					'variation_id' => $c_value['variation_id'],
					'quantity'     => $c_value['quantity'],
					'line_total'   => $line_total,
				);
				$cart_total          += $line_total;
			}
			$cart_details['cart_items'] = $cart_items;
			$cart_details['cart_total'] = $cart_total;
		}
		return $cart_details;
	}

}
new Wcap_Drip_Upsert_Cart_Action();

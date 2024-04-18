<?php
/**
 * AC insert and update file
 *
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Connectors/klaviyo
 */

/**
 * Class for ActiveCapmaign Connector
 */
class Wcap_Klaviyo extends Wcap_Connector {
	/**
	 * Connector Name
	 *
	 * @var $connector_name
	 */
	public $connector_name = 'klaviyo';
	/**
	 * Slug Name
	 *
	 * @var $slug
	 */
	public $slug = 'wcap_klaviyo';
	/**
	 * Name
	 *
	 * @var $name
	 */
	public $name = 'Klaviyo';
	/**
	 * Description
	 *
	 * @var $desc
	 */
	public $desc = 'Send emails and abandoned carts collected from the plugin to Klaviyo.';
	/**
	 * Signle instance of the class
	 *
	 * @var $ins
	 */
	private static $ins = null;
	/**
	 * Headers for Curl calls
	 *
	 * @var $headers
	 */
	public static $headers = null;

	/**
	 * Array of events
	 *
	 *  @var array All calls with object
	 */
	public $events = array( 'Created Cart', 'Modifed Cart', 'Ignored Cart', 'Recovered Cart', 'Deleted Cart' );
	/**
	 * Array of registered calls for function
	 *
	 *  @var array All calls with object
	 */
	public $registered_calls = array();
	/**
	 * Construct. Add hooks and filters.
	 *
	 * @var array All calls with object.
	 */
	public function __construct() {
		$this->wcap_define_plugin_properties();
		$this->connector_url = WCAP_KLAVIYO_PLUGIN_URL;
		add_filter( 'wcap_connectors_loaded', array( $this, 'add_card' ) );
		add_action( 'wp_ajax_wcap_get_klaviyo_lists', array( $this, 'wcap_get_klaviyo_lists' ) );
		add_action( 'wp_ajax_wcap_get_existing_settings_klaviyo', array( &$this, 'wcap_get_existing_settings' ) );
		add_action( 'init', array( &$this, 'wcap_klaviyo_include_files' ) );
		// Add cart status in the Abandoned Orders tab.
		add_filter( 'wcap_abandoned_orders_table_data', array( &$this, 'wcap_add_klaviyo_cart_status' ), 10, 1 );
		$this->wcap_register_calls();
	}

	/**
	 * Function to define constans
	 */
	public function wcap_define_plugin_properties() {
		if ( ! defined( 'WCAP_KLAVIYO_VERSION' ) ) {
			define( 'WCAP_KLAVIYO_VERSION', '1.0.0' );
		}
		if ( ! defined( 'WCAP_KLAVIYO_FULL_NAME' ) ) {
			define( 'WCAP_KLAVIYO_FULL_NAME', 'Abandoned Carts Automations Connectors: KLAVIYO' );
		}
		if ( ! defined( 'WCAP_KLAVIYO_PLUGIN_FILE' ) ) {
			define( 'WCAP_KLAVIYO_PLUGIN_FILE', __FILE__ );
		}
		if ( ! defined( 'WCAP_KLAVIYO_PLUGIN_DIR' ) ) {
			define( 'WCAP_KLAVIYO_PLUGIN_DIR', __DIR__ );
		}
		if ( ! defined( 'WCAP_KLAVIYO_PLUGIN_URL' ) ) {
			define( 'WCAP_KLAVIYO_PLUGIN_URL', untrailingslashit( plugin_dir_url( WCAP_KLAVIYO_PLUGIN_FILE ) ) );
		}
		if ( ! defined( 'WCAP_KLAVIYO_PLUGIN_BASENAME' ) ) {
			define( 'WCAP_KLAVIYO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}
	}

	/**
	 * Function to Add card in connector's main page.
	 *
	 * @param array $available_connectors - Avaialble connector for display in main connector page.
	 */
	public function add_card( $available_connectors ) {
		$available_connectors['wcap']['connectors']['wcap_klaviyo'] = array(
			'name'            => $this->name,
			'desc'            => __( $this->desc, 'woocommerce-ac' ), //phpcs:ignore
			'connector_class' => 'Wcap_Klaviyo',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

	/**
	 * Function to get instance.
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Function to register connector calls by pulling class files from the directory.
	 */
	public function wcap_register_calls() {
		$resource_dir = WCAP_KLAVIYO_PLUGIN_DIR . '/calls';
		if ( file_exists( $resource_dir ) ) {
			foreach ( glob( $resource_dir . '/class-*.php' ) as $filename ) {
				$call_class = require_once $filename;
				if ( ( is_object( $call_class ) || is_string( $call_class ) ) && method_exists( $call_class, 'get_instance' ) ) {
					$call_obj                                       = $call_class::get_instance();
					$this->registered_calls[ $call_obj->call_slug ] = $call_obj;
				}
			}
		}
		do_action( 'wcap_' . $this->get_slug() . '_actions_loaded' );

	}

	/**
	 * Function to get headers for curl/http calls.
	 */
	public static function get_headers() {
		return self::$headers;
	}

	/**
	 * Function to set headers for curl/http calls.
	 *
	 * @var array $api_key - API key created in the settings, integration at klaviyo.
	 */
	public static function set_headers() {
		$headers = array(
			'Content-Type' => 'application/json',
		);

		self::$headers = $headers;
	}

	/**
	 * Function to include files
	 */
	public function wcap_klaviyo_include_files() {
		include_once WCAP_KLAVIYO_PLUGIN_DIR . '/includes/wcap-upsert-contact.php';
		include_once WCAP_KLAVIYO_PLUGIN_DIR . '/includes/wcap-upsert-cart.php';
	}

	/**
	 * Function to include files.
	 */
	public function wcap_get_klaviyo_lists() {
		$private_key = isset( $_POST['private_key'] ) ? sanitize_text_field( wp_unslash( $_POST['private_key' ] ) ) : ''; // phpcs:ignore

		if ( empty( $private_key ) ) {
			wp_send_json(
				array(
					'response' => __( 'Private Key is not provided', 'woocommerce-ac' ),
				)
			);
		}

		$lists_result = $this->fetch_lists(
			array(
				'private_key' => $private_key,
			)
		);

		$this->wcap_add_default_events();

		if ( ! is_array( $lists_result ) || ! count( $lists_result ) > 0 ) {
			wp_send_json( array( 'status' => false ) );
		}

		wp_send_json( $lists_result );

		die();
	}

	/**
	 * Function to fetch lists available in klaviyo.
	 *
	 * @param array $params - API key created in the settings, integration at klaviyo.
	 * @param array $captured_items - Array of lists.
	 */
	public function fetch_lists( $params, $captured_items = array() ) {
		$call = $this->registered_calls['wcap_klaviyo_get_lists'];
		$call->set_data( $params );
		$result = $call->process();

		if ( 200 !== absint( $result['response'] ) ) {
			$error  = __( 'Error Response Code: ', 'woocommerce-ac' ) . $result['response'] . __( '. Klaviyo Error: ', 'woocommerce-ac' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Klaviyo. ', 'woocommerce-ac' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'WCAP Error: ', 'woocommerce-ac' ) . $result['body'][0] : '';

			wp_send_json(
				array(
					'status'  => 'failed',
					'message' => $error,
				)
			);
		}

		$total_items_count = count( $result['body'] );
		if ( ! $total_items_count ) {
			$call = $this->registered_calls['wcap_klaviyo_add_default_list'];
			$call->set_data( $params );
			$result = $call->process();

			$call = $this->registered_calls['wcap_klaviyo_get_lists'];
			$call->set_data( $params );
			$result = $call->process();
			$data   = $result['body'];

		} else {
			$data = $result['body'];
		}
		foreach ( $data as $row ) {
			$id                    = $row['list_id'];
			$captured_items[ $id ] = $row['list_name'];
		}

		$offset = '';
		if ( $total_items_count > count( $captured_items ) ) {
			$offset = count( $captured_items );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_lists( $params, $captured_items );
		}

		return $captured_items;
	}
	/**
	 * Function to add default events to klaviyo.
	 */
	public function wcap_add_default_events() {
		$connector_mc = self::get_instance();
		$call         = $connector_mc->registered_calls['wcap_klaviyo_upsert_cart'];
		// Fetch the connector data.
		$connector_settings = Wcap_Connectors_Common::wcap_get_connectors_data( $this->slug );
		if ( empty( $connector_settings ) ) {
			return false;
		}
		$public_key = isset( $connector_settings['public_key'] ) ? $connector_settings['public_key'] : '';
		$status     = isset( $connector_settings['status'] ) ? $connector_settings['status'] : '';
		$params     = array(
			'token'               => $public_key,
			'customer_properties' => array( '$email' => 'test@test.com' ),
		);

		$events = $connector_mc->events;
		foreach ( $events as $event ) {
			$params['event'] = $event;
			$call->set_data( $params );
			$result = $call->process();
		}

	}
	/**
	 * Sync Carts manually.
	 */
	public function wcap_sync_manually() {

		$connector_settings = Wcap_Connectors_Common::wcap_get_connectors_data( 'wcap_klaviyo' );
		$activated_time     = isset( $connector_settings['activated'] ) ? $connector_settings['activated'] : '';
		$status             = isset( $connector_settings['status'] ) ? $connector_settings['status'] : '';

		global $wpdb;
		// Get the list of carts which have not yet been synced.
		$cart_list = $wpdb->get_col( // phpcs:ignore
			$wpdb->prepare( 'SELECT wcap.id FROM ' . WCAP_ABANDONED_CART_HISTORY_TABLE . ' as wcap LEFT JOIN ' . $wpdb->prefix . 'ac_connector_sync as csync ON (wcap.id=csync.cart_id AND connector_name="klaviyo" ) where ( connector_cart_id IS NULL OR connector_cart_id =""  ) AND wcap.user_id > 0 AND wcap.abandoned_cart_time > %d', $activated_time ) // phpcs:ignore 
		);
		if ( is_array( $cart_list ) && count( $cart_list ) > 0 ) { // Start the process one at a time.
			$sync_count = 0;
			$cart_count = count( $cart_list );
			foreach ( $cart_list as $cart_id ) {
				$cart_sync_status = $this->wcap_sync_single_cart( $cart_id );
				if ( $cart_sync_status ) { // failed, catch the data and move on.
					$sync_count++;
				}
			}

			if ( $sync_count === $cart_count ) {
				$status  = 'success';
				$message = sprintf(
					/* translators: %1$s,%2$s are replaced with number of carts synced and number of total carts respectively */
					__( '%1$d of %2$d carts were synced successfully.', 'woocommerce-ac' ), //phpcs:ignore
					esc_html( $sync_count ),
					esc_html( $cart_count )
				);
			} else {
				$status = 'error';
				if ( 0 === $sync_count ) {
					$message = sprintf(
						/* translators: %1$s,%2$s are replaced with number of carts synced and number of total carts respectively */
						__( '%1$d of %2$d carts were synced. Please check for network connectivity issues and try again.', 'woocommerce-ac' ), //phpcs:ignore
						esc_html( $sync_count ),
						esc_html( $cart_count )
					);
				} else {
					$message = sprintf(
						/* translators: %1$s,%2$s,%3$s and %4$s is replaced with string */
						__( '%1$d of %2$d carts were synced successfully. Please try again to sync the remaining carts.', 'woocommerce-ac' ), //phpcs:ignore
						esc_html( $sync_count ),
						esc_html( $cart_count )
					);
				}
			}
			wp_send_json(
				array(
					'status'  => $status,
					'message' => $message,
				)
			);

		} else { // If none found, send a message saying data has already been synced.
			wp_send_json(
				array(
					'status'  => 'success',
					'message' => __( 'All carts have been synced.', 'woocommerce-ac' ),
				)
			);
		}
	}

	/**
	 * Sync individual cart.
	 *
	 * @param int $cart_id - Cart ID.
	 * @return bool true | false - success | failure.
	 * @since 8.16.0
	 */
	public function wcap_sync_single_cart( $cart_id = 0 ) {
		if ( $cart_id > 0 ) {
			if ( ! WC()->session ) {
				WC()->initialize_session();
			}
			wcap_set_cart_session( 'wcap_klaviyo_sync_cart_id', '' );
			wcap_set_cart_session( 'klaviyo_customer_details', '' );
			$wcap_ct_instance = Wcap_Klaviyo_Upsert_Contact_Action::get_instance();
			$wcap_add_contact = $wcap_ct_instance->wcap_prepare_contact( $cart_id );
			$wcap_add_cart    = Wcap_Klaviyo_Upsert_Cart_Action::get_instance();
			$res              = $wcap_add_cart->wcap_prepare_cart_details( $cart_id, true );
			if ( 'complete' === $res ) { // failed, catch the data and move on.
				return true;
			}
		}
		return false;
	}

	/**
	 * Get existing Klaviyo settings.
	 */
	public function wcap_get_existing_settings() {
		$connector_settings = Wcap_Connectors_Common::wcap_get_connectors_data( 'wcap_klaviyo' );

		$private_key = isset( $connector_settings['private_key'] ) ? $connector_settings['private_key'] : '';
		$public_key  = isset( $connector_settings['public_key'] ) ? $connector_settings['public_key'] : '';
		$list_id     = isset( $connector_settings['list_id'] ) ? $connector_settings['list_id'] : '';
		if ( empty( $private_key ) ) {
			wp_send_json(
				array(
					'response' => __( 'Private Key is not provided', 'woocommerce-ac' ),
				)
			);
		}
		if ( empty( $public_key ) ) {
			wp_send_json(
				array(
					'response' => __( 'Public URL is not provided', 'woocommerce-ac' ),
				)
			);
		}
		$lists_result = $this->fetch_lists(
			array(
				'private_key' => $private_key,
			)
		);

		if ( is_array( $lists_result ) && count( $lists_result ) > 0 ) {
			wp_send_json(
				array(
					'status'  => 'success',
					'lists'   => $lists_result,
					'list_id' => $list_id,
				)
			);
		} else {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => __( 'We were unable to connect to your store. Please disconnect and try connecting again.', 'woocommerce-ac' ),
				)
			);
		}
		die();
	}

	/**
	 * Display Cart Sync status.
	 *
	 * @param object $wcap_abandoned_orders - Abandoned Orders data.
	 */
	public function wcap_add_klaviyo_cart_status( $wcap_abandoned_orders ) {

		$connector_settings = Wcap_Connectors_Common::wcap_get_connectors_data( $this->slug );
		$connector_status   = isset( $connector_settings['status'] ) ? $connector_settings['status'] : '';

		if ( 'active' === $connector_status ) {
			foreach ( $wcap_abandoned_orders as $k => $col_data ) {
				$cart_id = 0;
				foreach ( $col_data as $col_name => $col_value ) {
					$cart_id = 'id' === $col_name ? $col_value : $cart_id;
					if ( (int) $cart_id > 0 ) {
						$connector_common = Wcap_Connectors_Common::get_instance();
						$cart_status      = $connector_common->wcap_get_cart_sync_status( $cart_id, $this->connector_name );

						if ( $cart_status ) {
							$wcap_abandoned_orders[ $k ]->connector_status .= "<span><img src='" . WCAP_KLAVIYO_PLUGIN_URL . "/views/icon.png' class='wcap-wpf-success wcap-success wcap-connector-status' /></span>";
						} else {
							$wcap_abandoned_orders[ $k ]->connector_status .= "<span><img src='" . WCAP_KLAVIYO_PLUGIN_URL . "/views/icon.png' class='wcap-wpf-failed wcap-failed wcap-connector-status' /></span>";
						}
					}
					break;
				}
			}
		}
		return $wcap_abandoned_orders;
	}
}

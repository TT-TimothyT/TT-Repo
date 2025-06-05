<?php
require_once TMWNI_DIR . 'inc/background-process/class-add-netsuite-order.php';
require_once TMWNI_DIR . 'inc/background-process/class-add-netsuite-customer.php';
require_once TMWNI_DIR . 'inc/NS_Restlet/netsuiteRestAPI.php';


use Automattic\WooCommerce\Utilities\OrderUtil;


use NetSuite\NetSuiteService;
use NetSuite\Classes\CustomerAddressbookList;
use NetSuite\Classes\CustomerAddressbook;
use NetSuite\Classes\Address;
use NetSuite\Classes\Customer;
use NetSuite\Classes\GetRequest;
use NetSuite\Classes\GetResponse;
use NetSuite\Classes\RecordRef;
use NetSuite\Classes\GetAllRequest;
use NetSuite\Classes\GetAllRecord;
use NetSuite\Classes\GetListRequest;
use NetSuite\Classes\ListOrRecordRef;
use NetSuite\Classes\Record;
use NetSuite\Classes\BaseRef;
use NetSuite\Classes\PriceLevel;
use NetSuite\Classes\GetSelectValueFieldDescription;
use NetSuite\Classes\getSelectValueRequest;
use NetSuite\Classes\RecordType;
use NetSuite\Classes\SearchMultiSelectField;
use NetSuite\Classes\SEARCHENUMMULTISELECTFIELDOPERATOR;
use NetSuite\Classes\TransactionSearchBasic;
use NetSuite\Classes\SearchRequest;
use NetSuite\Classes\AccountSearchBasic;
use NetSuite\Classes\SearchStringField;
use NetSuite\Classes\SearchStringFieldOperator;
use NetSuite\Classes\TermSearchBasic;
use NetSuite\Classes\SearchBooleanField;
use NetSuite\Classes\PriceLevelSearchBasic;

class TMWNI_Loader {


	private static $instance = null;
	public static function getInstance() {
		if (null === self::$instance) {
			self::$instance = new TMWNI_Loader();
		}
		return self::$instance;
	}

	public $netsuiteOrderClient = '';
	public  $add_netsuite_order;
	public  $netsuiteService;
	public $add_netsuite_customer;
	/**
	 * Construct
	 *
	 */
	public function __construct() {

		global $TMWNI_OPTIONS;
		if (class_exists('SOAPClient')) {
			if (TMWNI_Settings::areCredentialsDefined()) {
				require_once TMWNI_DIR . 'inc/order.php';
				require_once TMWNI_DIR . 'inc/customer.php';
				
				$this->netsuiteOrderClient = new OrderClient();

				$this->netsuiteService = new NetSuiteService(null, array(
					'exceptions' => true,
				));
				$this->add_netsuite_order = new Add_Netsuite_Order();
				$this->add_netsuite_customer = new Add_Netsuite_Customer();
				add_action('init', array(
					$this,
					'remove_background_process',
				));
				require_once TMWNI_DIR . 'inc/inventory.php';
				require_once TMWNI_DIR . 'inc/orderRefund.php';
				require_once TMWNI_DIR . 'inc/common.php';

				if (( isset($TMWNI_OPTIONS['enableDisplayOrderTrackingNumber']) && 'on' == $TMWNI_OPTIONS['enableDisplayOrderTrackingNumber'] )) {

					add_filter('woocommerce_my_account_my_orders_columns', array(
						$this,
						'tm_ns_add_my_account_orders_column',
					) , 10, 1);

					add_action('woocommerce_my_account_my_orders_column_tracking-number', array(
						$this,
						'tm_ns_my_orders_tracking_number',
					));

				}
				if (isset($TMWNI_OPTIONS['enableCustomerSync']) && 'on' == $TMWNI_OPTIONS['enableCustomerSync']) {

					add_action('woocommerce_created_customer', array(
						$this,
						'addUpdateNetsuiteCustomer',
					) , 999);
					//wordpress user register
					add_action('user_register', array(
						$this,
						'addUpdateNetsuiteUser',
					) , 999);

					add_action('profile_update', array(
						$this,
						'profileUpdateNetSuiteUser',
					) , 999);
				}
				if (isset($TMWNI_OPTIONS['enableOrderSync']) && 'on' == $TMWNI_OPTIONS['enableOrderSync']) {

					add_action('wp_ajax_manual_order_sync', array(
						$this,
						'ManualOrderSync',
					));

					add_action('woocommerce_order_actions', array(
						$this,
						'sync_to_netsuite_action',
					));

					add_action('woocommerce_order_action_sync_to_netsuite', array(
						$this,
						'sync_to_netsuite',
					));

					if (isset($TMWNI_OPTIONS['syncDeletedOrders']) && 'on' == $TMWNI_OPTIONS['syncDeletedOrders']) {
						if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
							add_action('woocommerce_trash_order', array(
								$this,
								'deleteNetsuiteOrder',
							));
						} else {

							add_action('wp_trash_post', array(
								$this,
								'deleteNetsuiteOrder',
							));
						}
					}

					if (isset($TMWNI_OPTIONS['ns_order_autosync_status']) && !empty($TMWNI_OPTIONS['ns_order_autosync_status'])) {

						if ('pending' == $TMWNI_OPTIONS['ns_order_autosync_status']) {
							add_action('woocommerce_checkout_order_processed', array(
								$this,
								'syncNetSuiteOrder',
							));
						} else {
							add_action('woocommerce_order_status_' . $TMWNI_OPTIONS['ns_order_autosync_status'], array(
								$this,
								'syncNetSuiteOrder',
							));
						}
					} else {
						add_action('woocommerce_order_status_processing', array(
							$this,
							'syncNetSuiteOrder',
						));
					}

					if (isset($TMWNI_OPTIONS['ns_auto_invoice_sync']) && !empty($TMWNI_OPTIONS['ns_auto_invoice_sync'])) {
						add_action('woocommerce_order_status_' . $TMWNI_OPTIONS['ns_auto_invoice_status'], array(
							$this,
							'syncSOInvoice',
						));

					}


					// Process all queued order sync to netsuite
					add_action('tm_ns_process_order_queue', array(
						$this,
						'addNetsuiteOrder',
					) , 10, 1);
				}

				if (( isset($TMWNI_OPTIONS['enableFulfilmentSync']) && 'on' == $TMWNI_OPTIONS['enableFulfilmentSync'] ) || ( isset($TMWNI_OPTIONS['ns_order_tracking_email']) && 'on' == $TMWNI_OPTIONS['ns_order_tracking_email'] ) || ( isset($TMWNI_OPTIONS['ns_order_auto_complete']) && 'on' == $TMWNI_OPTIONS['ns_order_auto_complete'] )) {

					if (!wp_next_scheduled('tm_ns_fetch_order_tracking_info')) {
						wp_schedule_event(time() , 'hourly', 'tm_ns_fetch_order_tracking_info');
					}

					//Fetching Order tracking info
					add_action('tm_ns_fetch_order_tracking_info', array(
						$this,
						'fetchOrderTrackingInfo',
					));

					//Custom woo order tracking email
					add_filter('woocommerce_email_classes', array(
						$this,
						'ns_order_tracking_woocommerce_email',
					));

				}
				if (isset($TMWNI_OPTIONS['refund_order_ns_to_woo']) && 'on' == $TMWNI_OPTIONS['refund_order_ns_to_woo']) {

					if (!wp_next_scheduled('tm_ns_fetch_refund_order')) {
						wp_schedule_event(time() , 'hourly', 'tm_ns_fetch_refund_order');
					}

					//Fetching Order tracking info
					add_action('tm_ns_fetch_refund_order', array(
						$this,
						'fetchNSRefundOrder',
					));

				}
				//Order Refund
				if (isset($TMWNI_OPTIONS['refund_order_woo_to_ns']) && 'on' == $TMWNI_OPTIONS['refund_order_woo_to_ns']) {
					add_action('woocommerce_order_refunded', array(
						$this,
						'create_netsuite_refund',
					) , 10, 2);
				}
			}

		} else {
			add_action('admin_notices', array(
				$this,
				'soap_notice',
			));
		}
	}

	public function remove_background_process() {
		if (isset($_GET['tm_ns_order_queue']) && 1 == $_GET['tm_ns_order_queue']) {
			$this
			->add_netsuite_order
			->cancel_process();
		}
	}
	public function soap_notice() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>PHP SOAP Extension is not enabled on your server. <a target="_blank" href="https://www.php.net/manual/en/soap.setup.php">Know more</a></p>
		</div>
		<?php
	}

	public function addUpdateNetsuiteUser( $customer_id ) {
		$user_meta = get_userdata($customer_id);
		$user_roles = $user_meta->roles;

		if ('customer' == $user_roles[0]) {
			return;
		} else {
			$this->addUpdateNetsuiteCustomer($customer_id);
		}
	}

	/**
	 * User Update
	 *
	 */
	public function profileUpdateNetSuiteCustomer( $customer_id ) {
		$customer_internal_id = get_user_meta($customer_id, TMWNI_Settings::$ns_customer_id, true);
		if (!empty($customer_internal_id)) {
			if (!empty($_GET['wc-ajax']) && 'checkout' == $_GET['wc-ajax']) {
				$var = 'checkout';
			} else {
				$this->addUpdateNetsuiteCustomer($customer_id);
			}
		}
	}

	public function profileUpdateNetSuiteUser( $customer_id ) {

		$user_meta = get_userdata($customer_id);
		$user_roles = $user_meta->roles;

		if (!empty($_GET['wc-ajax']) && 'checkout' ==$_GET['wc-ajax']) {
			$var = 'checkout';
		} elseif (!$this->is_post_in_queue($customer_id)) {
				$this->add_netsuite_customer->push_to_queue($customer_id);
				$this->add_netsuite_customer->save()->dispatch();
				return false;
		}
	}

	public function syncSOInvoice( $order_id ) {
		$status = true;

		/**
		 * Order Add status hook.
		 *
		 * @since 1.0.0
		 */
		$status = apply_filters('tm_netsuite_order_autosync_invoice', $status, $order_id);

		if (true == $status) {
			$order_internal_id = tm_ns_get_post_meta($order_id, TMWNI_Settings::$ns_order_id, true);
			$invoice_internal_id = tm_ns_get_post_meta($order_id, TMWNI_Settings::$ns_invoice_id, true);
			if (!empty($order_internal_id) && empty($invoice_internal_id)) {
				$order = wc_get_order($order_id);
				$user_id = $order->get_user_id();
				if (0 == $user_id) {
					$customer_internal_id = tm_ns_get_post_meta($order_id, TMWNI_Settings::$ns_guest_customer_id, true);
				} else {
					$customer_internal_id = get_user_meta($user_id, TMWNI_Settings::$ns_customer_id, true);
				}    
				$this->netsuiteOrderClient->createSOInvoice($order_id, $customer_internal_id, $order_internal_id);
			}
		}
	}

	public function syncNetSuiteOrder( $order_id ) {
		$status = true;
		/** 
			*Order Add status hook.
		
			* @since 1.0.0
 
			**/
			$status = apply_filters('tm_netsuite_order_autosync_status', $status, $order_id);
		if (true == $status) {
			if (!$this->is_post_in_queue($order_id)) {
				$this->push_orders_to_queue($order_id);
			}
		}
	}

	public function is_post_in_queue( $id ) {
		global $wpdb;
		$ids = array();
		$ids[0] = $id;
		$wpdb->netsuite_queue = $wpdb->prefix . 'options';

		$value = serialize($ids);

		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->netsuite_queue WHERE option_value=%s", $value));

		if (is_multisite()) {
			$wpdb->netsuite_queue_site_meta_table = $wpdb->prefix . 'sitemeta';
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->netsuite_queue_site_meta_table WHERE meta_value=%s", $value));

		}

		return ( $count > 0 );
	}

	/**
	 * Sync Order
	 *
	 */
	public function addNetsuiteOrder( $order_id ) {
		global $TMWNI_OPTIONS;
		$order = wc_get_order($order_id);
		// get user id associated with the order
		$user_id = $order->get_user_id();

		$check_if_sent = $this->getOrderInternalIdFromDB($order_id);

		if (empty($check_if_sent)) {
			if (0 == $user_id) {
				$customer_internal_id = $this->addUpdateNetsuiteGuestCustomer($order);
				if (!empty($customer_internal_id)) {
					tm_ns_update_post_meta($order_id, TMWNI_Settings::$ns_guest_customer_id, $customer_internal_id);
				}
			} else {
				$customer_internal_id = $this->addUpdateNetsuiteCustomer($user_id, $order_id);
			}
			/**
				* Filter to modify customer internal id.
				*
				* @since 1.0.0
			*/
			$customer_internal_id = apply_filters('tm_netsuite_order_customer_internal_id', $customer_internal_id, $order_id, $order, $user_id);

			$order_data = $this->getOrderData($order_id, TMWNI_Settings::$ns_rec_type_order);
			/**
			 * Order data hook.
			 *
			 * @since 1.0.0
			 */
			$order_data = apply_filters('tm_netsuite_order_data', $order_data, $order_id);

			$this
			->netsuiteOrderClient->order_id = $order_id;

			if (0 != $customer_internal_id) {
				tm_ns_update_post_meta($order_id, TMWNI_Settings::$ns_customer_id, $customer_internal_id);

				$order_netsuite_internal_id = $this
				->netsuiteOrderClient
				->addOrder($order_data, $customer_internal_id);

				if (!empty($order_netsuite_internal_id)) {
					$this->updateOrderInternalIdDB($order_id, $order_netsuite_internal_id);
				}
			}

			return $order_netsuite_internal_id;
		} else {
			if (0 == $user_id) {
				$customer_internal_id = $this->addUpdateNetsuiteGuestCustomer($order);
			} else {
				$customer_internal_id = $this->addUpdateNetsuiteCustomer($user_id, $order_id);
			}
			/**
				* Filter to modify customer internal id.
				*
				* @since 1.0.0
			*/
			$customer_internal_id = apply_filters('tm_netsuite_order_customer_internal_id', $customer_internal_id, $order_id, $order, $user_id);

			// get required order data
			$order_data = $this->getOrderData($order_id, TMWNI_Settings::$ns_rec_type_order);
			/**
			 * Order data hook.
			 *
			 * @since 1.0.0
			 */
			$order_data = apply_filters('tm_netsuite_order_data', $order_data, $order_id);

			$order_netsuite_internal_id = $this
			->netsuiteOrderClient
			->updateOrder($order_data, $customer_internal_id, $check_if_sent);

			return $order_netsuite_internal_id;
		}
	}
	/**
	 * Order Tracking Woo email
	 *
	 */
	public function ns_order_tracking_woocommerce_email( $email_classes ) {
		$email_classes['WC_NetSuite_Order_Tracking_No'] = require TMWNI_DIR . 'inc/woo-email-class/class-wc-netsuite-order-tracking-email.php';
		return $email_classes;
	}
	/**
	 * Sync to NS
	 *
	 */
	public function sync_to_netsuite_action( $actions ) {
		$actions['sync_to_netsuite'] = __('Sync to NetSuite', 'songlify');
		return $actions;
	}

	public function ManualOrderSync() {
		global $TMWNI_OPTIONS;

		if (isset($_POST['nonce']) && !empty($_POST['nonce']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']) , 'security_nonce')) {
			if (isset($_POST['order_id']) && !empty($_POST['order_id'])) {
				if (isset($TMWNI_OPTIONS['enableOrderSync']) && 'on' == $TMWNI_OPTIONS['enableOrderSync']) {
					$order_id = intval($_POST['order_id']);
					$response = $this->addNetsuiteOrder($order_id);
					esc_attr_e($response);
				}
			}
		} else {
			die('Order Logs Nonce Error');
		}
	}
	/**
	 * Sync Order to Netsuite
	 *
	 */
	public function sync_to_netsuite( $order ) {
		global $TMWNI_OPTIONS;
		$status = true;

		if (isset($TMWNI_OPTIONS['enableOrderSync']) && 'on' == $TMWNI_OPTIONS['enableOrderSync']) {
			/**
				* Filter to modify order data.
				*
				* @since 1.0.0
			*/
			$status = apply_filters('tm_add_netsuite_order', $status, $order);
			if (true == $status) {
				$this->addNetsuiteOrder($order->get_id());
			}   
		}
		return;
	}

	/**
	 * Hook function which will receive customer id and pass it to net update_user_meta($customer_id, TMWNI_Settings::$ns_customer_id, $customer_internal_id);
	 */
	/**
	 * Hook function which will recieve customer id and pass it to net  update_user_meta($customer_id, TMWNI_Settings::$ns_customer_id, $customer_internal_id);
	 */
	public function addUpdateNetsuiteCustomer( $customer_id, $order_id = 0 ) {
		$customer_sync = true;
		/** 
		 *Add customer status hook.
		 *
		 * @since 1.0.0
		 *
		 */
		$customer_sync = apply_filters('tm_ns_customer_sync_status_check', $customer_sync, $customer_id);
		if ($order_id) {
			/** 
			 *Before Add Update customer hook.
			 *
			 * @since 1.0.0
			 *
			 */
			do_action('before_add_update_netsuite_customer', $customer_id, $order_id);
		}
		if (true == $customer_sync) {
			$customer_internal_id = $this->syncNetSuiteCustomerData($customer_id, $order_id);
			return $customer_internal_id;

		}
	}

	public function syncNetSuiteCustomerData( $customer_id, $order_id ) {

		global $TMWNI_OPTIONS;
		$customer_internal_id = 0;
		$customerSearchResponseByEmail = '';

			//get and set customer data
		$woo_customer_data = get_userdata($customer_id);

		$user_roles = $woo_customer_data->roles;
		$customerRoles = $TMWNI_OPTIONS['customer_roles'] ? $TMWNI_OPTIONS['customer_roles'] : array();
		$find_common_role = !empty($customerRoles) ? array_intersect($user_roles, $customerRoles) : array();

			//check if passed user is a customer
		if (!empty($find_common_role)) {
			require_once TMWNI_DIR . 'inc/customer.php';

			//instance of API client
			$netsuiteCustomerClient = new CustomerClient();
			$email = $woo_customer_data
			->data->user_email;

			$get_customer_internalid_from_db_status = true;
			/** 
			 *Hook for get status customer internal id from database.
			 *
			 * @since 1.0.0
			 *
			 */
			$get_customer_internalid_from_db_status = apply_filters('tm_netsuite_get_customer_db_status', $get_customer_internalid_from_db_status, $customer_id);

			if (true == $get_customer_internalid_from_db_status) {
				$customer_internal_id = get_user_meta($customer_id, TMWNI_Settings::$ns_customer_id, true);
			}

			if (empty($customer_internal_id)) {
				/** 
				 *Hook for email by search customer on NetSuite.
				 *
				 * @since 1.3.7
				 *
				 */
				$email = apply_filters('tm_ns_search_customer_email', $email, $customer_id);
				//check if customer already registered on netsuite
				if (!empty($email)) {
					$customerSearchResponseByEmail = $netsuiteCustomerClient->searchCustomer($email, $customer_id);
				}

				if (isset($customerSearchResponseByEmail
					->searchResult
					->recordList
					->record[0]
					->internalId) && !empty($customerSearchResponseByEmail
						->searchResult
						->recordList
						->record[0]
						->internalId)) {
					$customer_internal_id = $customerSearchResponseByEmail
					->searchResult
					->recordList
					->record[0]->internalId;
				}
			}

			if (!empty($woo_customer_data->first_name) && !empty($woo_customer_data->last_name)) {
				$first_name = $woo_customer_data->first_name;
				$last_name = $woo_customer_data->last_name;
			} else {
				$first_name = get_user_meta($customer_id, 'billing_first_name', true);
				$last_name = get_user_meta($customer_id, 'billing_last_name', true);
			}

			$company_name = get_user_meta($customer_id, 'billing_company', true);
			$phone = get_user_meta($customer_id, 'billing_phone', true);

			$customer_data = array(
				'customer_id' => $customer_id,
				'firstName' => $first_name,
				'lastName' => $last_name,
				'email' => $email,
				'companyName' => $company_name,
				'phone' => $phone,
			);

			update_user_meta($customer_id, TMWNI_Settings::$ns_customer_id, $customer_internal_id);
			if (empty($customer_internal_id)) {
				$address_type = array(
					'billing',
					'shipping',
				);
				$addresses = array();

				$addresses = $this->getRegisterUserAddress($address_type, $customer_id);

				$alb = '';
				$als = '';

				foreach ($addresses as $key => $address) {
					if (!empty($address['postcode'])) {
						if (isset($address['country']) && !empty($address['country'])) {
							$ns_country = TMWNI_Settings::$netsuite_country[$address['country']];
						} else {
							$ns_country = '';
						}

						if ('billing' == $key) {
							$alb = new CustomerAddressbook();
							$alb->internalId = $customer_internal_id;
							$alb->defaultShipping = false;
							$alb->defaultBilling = true;
							$alb->isResidential = true;
							$alb->label = 'Customer Address Billing';
							$alb->addressbookAddress = new Address();
							$alb
							->addressbookAddress->addr1 = $address['address1'];
							$alb
							->addressbookAddress->addr2 = $address['address2'];
							$alb
							->addressbookAddress->addr3 = '';

							$alb
							->addressbookAddress->addrPhone = $phone;
							$alb
							->addressbookAddress->addrText = $address['address1'];

							$alb
							->addressbookAddress->city = $address['city'];
							$alb
							->addressbookAddress->country = $ns_country;
							$alb
							->addressbookAddress->internalId = $customer_internal_id;
							$alb
							->addressbookAddress->override = false;
							$alb
							->addressbookAddress->state = $address['state'];
							$alb
							->addressbookAddress->zip = $address['postcode'];

							if (!empty($address['companyName'])) {
								$alb
								->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];
								$alb
								->addressbookAddress->addressee = $address['companyName'];

							} else {
								// $alb->addressbookAddress->addressee = $address['firstName'] . ' ' . $address['lastName'];
								$alb
								->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];

							}
						} elseif ('shipping' == $key) {
							if (isset($address['country']) && !empty($address['country'])) {
								$ns_shipping_country = TMWNI_Settings::$netsuite_country[$address['country']];
							} else {
								$ns_shipping_country = '';
							}
							$als = new CustomerAddressbook();
							$als->internalId = $customer_internal_id;
							$als->defaultShipping = true;
							$als->defaultBilling = false;
							$als->isResidential = false;
							$als->label = 'Customer Address Shipping';
							$als->addressbookAddress = new Address();
							$als
							->addressbookAddress->addr1 = $address['address1'];
							$als
							->addressbookAddress->addr2 = $address['address2'];
							$als
							->addressbookAddress->addr3 = '';
							$als
							->addressbookAddress->addrPhone = '';
							$als
							->addressbookAddress->addrText = $address['address1'];
							$als
							->addressbookAddress->city = $address['city'];
							$als
							->addressbookAddress->country = $ns_shipping_country;
							$als
							->addressbookAddress->internalId = $customer_internal_id;
							$als
							->addressbookAddress->override = false;
							$als
							->addressbookAddress->state = $address['state'];
							$als
							->addressbookAddress->zip = $address['postcode'];

							if (!empty($address['companyName'])) {
								$als
								->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];
								$als
								->addressbookAddress->addressee = $address['companyName'];

							} else {
								// $als->addressbookAddress->addressee = $address['firstName'] . ' ' . $address['lastName'];
								$als
								->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];

							}
						}

						$address_data = array( $alb, $als );
					}

				}

			} else {
				if (isset($customerSearchResponseByEmail
					->searchResult
					->recordList
					->record[0]
					->addressbookList) && !empty($customerSearchResponseByEmail
						->searchResult
						->recordList
						->record[0]
						->addressbookList)) {

					$existing_customer_address = $customerSearchResponseByEmail
					->searchResult
					->recordList
					->record[0]->addressbookList;
				} else {
					$existing_customer_address = $netsuiteCustomerClient->searchCustomerByInternalId($customer_internal_id, $customer_id);
				}
				$address_data = $this->existingRegisterCustomerAddressData($existing_customer_address, $customer_internal_id, $customer_id);
			}
			/** 
			 *Custome Address data hook.
			 *
			 * @since 1.0.0
			 *
			 */
			$address_data = apply_filters('tm_netsuite_customer_address_data', $address_data, $customer_id);
			$al = new CustomerAddressbookList();
			$al->addressbook = $address_data;
			$al->replaceAll = false;

			if (!empty($customer_internal_id)) {
				/** 
				 *Custome data hook for update customer.
				 *
				 * @since 1.3.7
				 *
				 */
				$customer_data = apply_filters('tm_netsuite_customer_data', $customer_data, $customer_id, $customerSearchResponseByEmail);

				$netsuiteCustomerClient->updateCustomer($customer_data, $customer_internal_id, $al, $order_id);
			} else {
				//add customer to netsuite
				$customer_internal_id = $netsuiteCustomerClient->addCustomer($customer_data, $al, $order_id);
				if ($customer_internal_id) {
					update_user_meta($customer_id, TMWNI_Settings::$ns_customer_id, $customer_internal_id);
				}
			}

			return $customer_internal_id;

		} else {
			require_once TMWNI_DIR . 'inc/common.php';
			if (isset($woo_customer_data->roles[0]) && !empty($woo_customer_data->roles[0])) {
				$netsuiteCommonIntegrationFunctions = new CommonIntegrationFunctions();
				$error_msg = 'Please select ' . $woo_customer_data->roles[0] . ' role in customer tab';
				$netsuiteCommonIntegrationFunctions->handleLog(1, $customer_id, 'Customer', $error_msg);
			}

			return 0;

		}
	}

	public function getRegisterUserAddress( $address_type, $customer_id ) {

		$addresses = array();
		foreach ($address_type as $single_address) {
			$address['firstName'] = get_user_meta($customer_id, $single_address . '_first_name', true);
			$address['lastName'] = get_user_meta($customer_id, $single_address . '_last_name', true);
			$address['companyName'] = get_user_meta($customer_id, $single_address . '_company', true);
			$address['address1'] = get_user_meta($customer_id, $single_address . '_address_1', true);
			$address['address2'] = get_user_meta($customer_id, $single_address . '_address_2', true);
			$address['city'] = get_user_meta($customer_id, $single_address . '_city', true);
			$address['state'] = get_user_meta($customer_id, $single_address . '_state', true);
			$address['postcode'] = get_user_meta($customer_id, $single_address . '_postcode', true);
			$address['country'] = get_user_meta($customer_id, $single_address . '_country', true);
			$address['phone'] = get_user_meta($customer_id, $single_address . '_phone', true);
			$addresses[$single_address] = $address;
		}
		return $addresses;
	}

	public function addUpdateNetsuiteGuestCustomer( $order ) {
		$guest_customer_sync = true;
		/**
			* Filter to modify the status of the Guest Customer sync process.
			*
			* @since 1.0.0
		*/
		$guest_customer_sync = apply_filters('tm_ns_guest_customer_sync_status_check', $guest_customer_sync, $order);
		if (true == $guest_customer_sync) {
			$customer_internal_id = $this->syncNetSuiteGuestCustomerData($order);
			return $customer_internal_id;
		}
	}

	/**
	 * Sync Guest Customer
	 *
	 */
	public function syncNetSuiteGuestCustomerData( $order ) {
		if ($order) {

			$customerSearchResponseByEmail = '';

			/** 
			 *Before Add Update Customer  hook.
			 *
			 * @since 1.0.0
			 *
			 */
			$customer_internal_id = apply_filters('before_add_update_guest_netsuite_customer', 0, $order);
			// do_action("before_add_update_guest_netsuite_customer",$order);
			
		}
		require_once TMWNI_DIR . 'inc/customer.php';
		$netsuiteCustomerClient = new CustomerClient();

		$email = $order->get_billing_email();

		$customer_id = 0;
		$this->object_id = $customer_id;

		if (empty($customer_internal_id) && !empty($email)) {
			$customer_internal_id = 0;

			$customerSearchResponseByEmail = $netsuiteCustomerClient->searchCustomer($email, $customer_id);

			if (isset($customerSearchResponseByEmail
				->searchResult
				->recordList
				->record[0]
				->internalId) && !empty($customerSearchResponseByEmail
					->searchResult
					->recordList
					->record[0]
					->internalId)) {
				$customer_internal_id = $customerSearchResponseByEmail
				->searchResult
				->recordList
				->record[0]->internalId;
			}

		}
		$first_name = $order->get_billing_first_name();
		$last_name = $order->get_billing_last_name();
		$company_name = $order->get_billing_company();
		$cust_address = $order->get_Address();
		$phone = $order->get_billing_phone();
		$cust_order_id = $order->get_id();

		$customer_data = array(
			'customer_id' => $customer_id,
			'firstName' => $first_name,
			'lastName' => $last_name,
			'email' => $email,
			'companyName' => $company_name,
			'phone' => $phone,
		);

		if (empty($customer_internal_id)) {
			$address_type = array(
				'billing',
				'shipping',
			);
			$addresses = array();

			$alb = '';
			$als = '';
			foreach ($address_type as $key => $type) {
				if ('billing' == $type) {
					if (!empty($order->get_billing_country())) {
						$ns_country = TMWNI_Settings::$netsuite_country[$order->get_billing_country() ];
					} else {
						$ns_country = '';
					}
					$alb = new CustomerAddressbook();
					$alb->internalId = $customer_internal_id;
					$alb->defaultShipping = false;
					$alb->defaultBilling = true;
					$alb->isResidential = true;
					$alb->label = 'Customer Address Billing';
					$alb->addressbookAddress = new Address();
					$alb
					->addressbookAddress->addr1 = $order->get_billing_address_1();
					$alb
					->addressbookAddress->addr2 = $order->get_billing_address_2();
					$alb
					->addressbookAddress->addr3 = '';
					// $alb->addressbookAddress->attention = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
					$alb
					->addressbookAddress->addrPhone = $order->get_billing_phone();
					$alb
					->addressbookAddress->addrText = $order->get_billing_address_1();
					// $alb->addressbookAddress->addressee = $order->get_billing_company();
					$alb
					->addressbookAddress->city = $order->get_billing_city();
					$alb
					->addressbookAddress->country = $ns_country;
					$alb
					->addressbookAddress->internalId = $customer_internal_id;
					$alb
					->addressbookAddress->override = false;
					$alb
					->addressbookAddress->state = $order->get_billing_state();
					$alb
					->addressbookAddress->zip = $order->get_billing_postcode();

					if (!empty($order->get_billing_company())) {

						$alb
						->addressbookAddress->attention = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
						$alb
						->addressbookAddress->addressee = $order->get_billing_company();

					} else {
						$alb
						->addressbookAddress->addressee = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
						$alb
						->addressbookAddress->attention = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

					}
				}
				if ('shipping' == $type) {
					if (!empty($order->get_shipping_country())) {
						$ns_country = TMWNI_Settings::$netsuite_country[$order->get_shipping_country() ];
					} else {
						$ns_country = '';
					}
					$als = new CustomerAddressbook();
					$als->internalId = $customer_internal_id;
					$als->defaultShipping = true;
					$als->defaultBilling = false;
					$als->isResidential = false;
					$als->label = 'Customer Address Shipping';
					$als->addressbookAddress = new Address();
					$als
					->addressbookAddress->addr1 = $order->get_shipping_address_1();
					$als
					->addressbookAddress->addr2 = $order->get_shipping_address_2();
					$als
					->addressbookAddress->addr3 = '';
					// $als->addressbookAddress->attention = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
					$als
					->addressbookAddress->addrPhone = '';
					$als
					->addressbookAddress->addrText = $order->get_shipping_address_1();
					// $als->addressbookAddress->addressee = $order->get_shipping_company();
					$als
					->addressbookAddress->city = $order->get_shipping_city();
					$als
					->addressbookAddress->country = $ns_country;
					$als
					->addressbookAddress->internalId = $customer_internal_id;
					$als
					->addressbookAddress->override = false;
					$als
					->addressbookAddress->state = $order->get_shipping_state();
					$als
					->addressbookAddress->zip = $order->get_shipping_postcode();

					if (!empty($order->get_shipping_company())) {

						$als
						->addressbookAddress->attention = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
						$als
						->addressbookAddress->addressee = $order->get_shipping_company();

					} else {
						$als
						->addressbookAddress->addressee = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
						$als
						->addressbookAddress->attention = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();

					}
				}

			}

			$address_data = array( $alb, $als );

			/** 
			 *Customer Address data hook.
			 *
			 * @since 1.0.0
			 *
			 */
			$address_data = apply_filters('tm_netsuite_customer_address_data', $address_data, $customer_id);
			$al = new CustomerAddressbookList();
			$al->addressbook = $address_data;
			$al->replaceAll = false;

		} else {

			if (isset($customerSearchResponseByEmail
				->searchResult
				->recordList
				->record[0]
				->addressbookList) && !empty($customerSearchResponseByEmail
					->searchResult
					->recordList
					->record[0]
					->addressbookList)) {

				$existing_customer_address = $customerSearchResponseByEmail
				->searchResult
				->recordList
				->record[0]->addressbookList;
			} else {
				$existing_customer_address = $netsuiteCustomerClient->searchCustomerByInternalId($customer_internal_id, $customer_id);
			}

			$address_data = $this->existingGuestCustomerAddressData($existing_customer_address, $order, $customer_internal_id);

			$al = new CustomerAddressbookList();
			$al->addressbook = $address_data;
			$al->replaceAll = false;

		}

		if (!empty($customer_internal_id)) {
			/** 
			 *Custome data hook for update customer.
			 *
			 * @since 1.3.7
			 *
			 */
			$customer_data = apply_filters('tm_netsuite_customer_data', $customer_data, $customer_id, $customerSearchResponseByEmail);
			$netsuiteCustomerClient->updateCustomer($customer_data, $customer_internal_id, $al, $cust_order_id);
		} else {
			$customer_internal_id = $netsuiteCustomerClient->addCustomer($customer_data, $al, $cust_order_id);
		}

		return $customer_internal_id;
	}
	public function existingGuestCustomerAddressData( $existing_customer_address, $order, $customer_internal_id ) {
		global $TMWNI_OPTIONS;

		// $existing_customer_address = $response->searchResult->recordList->record[0]->addressbookList;
		

		$billing_address = $order->get_Address();
		if (isset($billing_address['country']) && !empty($billing_address['country'])) {
			$ns_country = TMWNI_Settings::$netsuite_country[$billing_address['country']];
		} else {
			$ns_country = '';
		}
		$address_data = array();

		$alb = '';
		$als = '';
		$alb = new CustomerAddressbook();
		// $alb->internalId = $customer_internal_id;
		$alb->defaultShipping = false;
		$alb->defaultBilling = false;
		$alb->isResidential = false;
		$alb->label = 'Customer Address Billing';
		$alb->addressbookAddress = new Address();
		$alb
		->addressbookAddress->addr1 = $order->get_billing_address_1();
		$alb
		->addressbookAddress->addr2 = $order->get_billing_address_2();
		$alb
		->addressbookAddress->addr3 = '';
		// $alb->addressbookAddress->attention = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		$alb
		->addressbookAddress->addrPhone = $order->get_billing_phone();
		$alb
		->addressbookAddress->addrText = $order->get_billing_address_1();
		// $alb->addressbookAddress->addressee = $order->get_billing_company();
		$alb
		->addressbookAddress->city = $order->get_billing_city();
		$alb
		->addressbookAddress->country = $ns_country;
		$alb
		->addressbookAddress->internalId = $customer_internal_id;
		$alb
		->addressbookAddress->override = false;
		$alb
		->addressbookAddress->state = $order->get_billing_state();
		$alb
		->addressbookAddress->zip = $order->get_billing_postcode();
		if (!empty($order->get_billing_company())) {

			$alb
			->addressbookAddress->attention = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			$alb
			->addressbookAddress->addressee = $order->get_billing_company();

		} else {
			$alb
			->addressbookAddress->addressee = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			$alb
			->addressbookAddress->attention = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

		}

		$als = new CustomerAddressbook();
		// $als->internalId = $customer_internal_id;
		$als->defaultShipping = false;
		$als->defaultBilling = false;
		$als->isResidential = false;
		$als->label = 'Customer Address Shipping';
		$als->addressbookAddress = new Address();
		$als
		->addressbookAddress->addr1 = $order->get_shipping_address_1();
		$als
		->addressbookAddress->addr2 = $order->get_shipping_address_2();
		$als
		->addressbookAddress->addr3 = '';
		// $als->addressbookAddress->attention = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
		$als
		->addressbookAddress->addrPhone = '';
		$als
		->addressbookAddress->addrText = $order->get_shipping_address_1();
		// $als->addressbookAddress->addressee = $order->get_shipping_company();
		$als
		->addressbookAddress->city = $order->get_shipping_city();
		$als
		->addressbookAddress->country = $ns_country;
		$als
		->addressbookAddress->internalId = $customer_internal_id;
		$als
		->addressbookAddress->override = false;
		$als
		->addressbookAddress->state = $order->get_shipping_state();
		$als
		->addressbookAddress->zip = $order->get_shipping_postcode();

		if (!empty($order->get_shipping_company())) {

			$als
			->addressbookAddress->attention = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
			$als
			->addressbookAddress->addressee = $order->get_shipping_company();

		} else {
			$als
			->addressbookAddress->addressee = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
			$als
			->addressbookAddress->attention = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();

		}

		$check_billing_address_is_existing = $this->checkCustomerAddressAlreadyExistOnNetSuite($existing_customer_address, $alb);

		if ('false' == $check_billing_address_is_existing) {
			$address_data[] = $alb;
		}

		$check_shipping_address_is_existing = $this->checkCustomerAddressAlreadyExistOnNetSuite($existing_customer_address, $als);

		if ('false' == $check_shipping_address_is_existing) {
			$address_data[] = $als;
		}

		return $address_data;
	}

	public function createRegisterCustomerAddressRequest( $customer_id, $customer_internal_id ) {
		$address_data = array();
		$alb = '';
		$als = '';
		$address_type = array(
			'billing',
			'shipping',
		);
		$addresses = $this->getRegisterUserAddress($address_type, $customer_id);
		foreach ($addresses as $key => $address) {
			if (isset($address['country']) && !empty($address['country'])) {
				$ns_country = TMWNI_Settings::$netsuite_country[$address['country']];
			} else {
				$ns_country = '';
			}
			if ('billing' == $key) {
				$alb = new CustomerAddressbook();
				$alb->internalId = $customer_internal_id;
				$alb->defaultShipping = false;
				$alb->defaultBilling = false;
				$alb->isResidential = false;
				$alb->label = 'Customer Address Billing';
				$alb->addressbookAddress = new Address();
				$alb
				->addressbookAddress->addr1 = $address['address1'];
				$alb
				->addressbookAddress->addr2 = $address['address2'];
				$alb
				->addressbookAddress->addr3 = '';
				$alb
				->addressbookAddress->addrPhone = $address['phone'];
				$alb
				->addressbookAddress->city = $address['city'];
				$alb
				->addressbookAddress->country = $ns_country;
				$alb
				->addressbookAddress->internalId = $customer_internal_id;
				$alb
				->addressbookAddress->override = false;
				$alb
				->addressbookAddress->state = $address['state'];
				$alb
				->addressbookAddress->zip = $address['postcode'];
				$alb
				->addressbookAddress->addrText = $address['address1'];
				if (!empty($address['companyName'])) {
					$alb
					->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];
					$alb
					->addressbookAddress->addressee = $address['companyName'];
					$addressee = $address['companyName'];

				} else {
					$addressee = $address['firstName'] . ' ' . $address['lastName'];
					$alb
					->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];

				}
				$alb
				->addressbookAddress->addressee = $addressee;
			} elseif ('shipping' == $key) {
				$als = new CustomerAddressbook();
				$als->internalId = $customer_internal_id;
				$als->defaultShipping = false;
				$als->defaultBilling = false;
				$als->isResidential = false;
				$als->label = 'Customer Address Shipping';
				$als->addressbookAddress = new Address();
				$als
				->addressbookAddress->addr1 = $address['address1'];
				$als
				->addressbookAddress->addr2 = $address['address2'];
				$als
				->addressbookAddress->addr3 = '';
				$als
				->addressbookAddress->addrPhone = $address['phone'];
				$als
				->addressbookAddress->addrText = $address['address1'];
				$als
				->addressbookAddress->city = $address['city'];
				$als
				->addressbookAddress->country = $ns_country;
				$als
				->addressbookAddress->internalId = $customer_internal_id;
				$als
				->addressbookAddress->override = false;
				$als
				->addressbookAddress->state = $address['state'];
				$als
				->addressbookAddress->zip = $address['postcode'];
				if (!empty($address['companyName'])) {
					$als
					->addressbookAddress->addressee = $address['firstName'] . ' ' . $address['lastName'];
					$als
					->addressbookAddress->attention = $address['companyName'];
				} else {
					$als
					->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];

				}
			}
		}

		$address_data = array(
			'als' => $als,
			'alb' => $alb,
		);

		/**
		 * Custom Address data hook.
		 *
		 * @since 1.0.0
		 */
		$address_data = apply_filters('tm_netsuite_customer_address_data', $address_data, $customer_id);

		$al = new CustomerAddressbookList();
		$al->addressbook = $address_data;
		$al->replaceAll = false;

		return $address_data;
	}

	public function existingRegisterCustomerAddressData( $existing_customer_address, $customer_internal_id, $customer_id ) {
		global $TMWNI_OPTIONS;

		$address_data = array();
		$company_name = get_user_meta($customer_id, 'billing_company', true);
		$phone = get_user_meta($customer_id, 'billing_phone', true);

		$alb = '';
		$als = '';
		$address_type = array(
			'billing',
			'shipping',
		);

		$addresses = $this->getRegisterUserAddress($address_type, $customer_id);
		foreach ($addresses as $key => $address) {
			if (isset($address['country']) && !empty($address['country'])) {
				$ns_country = TMWNI_Settings::$netsuite_country[$address['country']];
			} else {
				$ns_country = '';
			}

			if ('billing' == $key) {
				$alb = new CustomerAddressbook();
				$alb->internalId = $customer_internal_id;
				$alb->defaultShipping = false;
				$alb->defaultBilling = false;
				$alb->isResidential = false;
				$alb->label = 'Customer Address Billing';
				$alb->addressbookAddress = new Address();
				$alb
				->addressbookAddress->addr1 = $address['address1'];
				$alb
				->addressbookAddress->addr2 = $address['address2'];
				$alb
				->addressbookAddress->addr3 = '';

				$alb
				->addressbookAddress->addrPhone = $phone;

				$alb
				->addressbookAddress->city = $address['city'];
				$alb
				->addressbookAddress->country = $ns_country;
				$alb
				->addressbookAddress->internalId = $customer_internal_id;
				$alb
				->addressbookAddress->override = false;
				$alb
				->addressbookAddress->state = $address['state'];
				$alb
				->addressbookAddress->zip = $address['postcode'];
				$alb
				->addressbookAddress->addrText = $address['address1'];

				if (!empty($address['companyName'])) {
					$alb
					->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];
					$alb
					->addressbookAddress->addressee = $address['companyName'];
					$addressee = $address['companyName'];

				} else {
					$addressee = $address['firstName'] . ' ' . $address['lastName'];
					$alb
					->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];

				}
				$alb
				->addressbookAddress->addressee = $addressee;
			} elseif ('shipping' == $key) {
				$als = new CustomerAddressbook();
				$als->internalId = $customer_internal_id;
				$als->defaultShipping = false;
				$als->defaultBilling = false;
				$als->isResidential = false;
				$als->label = 'Customer Address Shipping';
				$als->addressbookAddress = new Address();
				$als
				->addressbookAddress->addr1 = $address['address1'];
				$als
				->addressbookAddress->addr2 = $address['address2'];
				$als
				->addressbookAddress->addr3 = '';
				// $als->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];
				// ;
				$als
				->addressbookAddress->addrPhone = $address['phone'];
				$als
				->addressbookAddress->addrText = $address['address1'];
				// $als->addressbookAddress->addressee = $address['companyName'];
				$als
				->addressbookAddress->city = $address['city'];
				$als
				->addressbookAddress->country = $ns_country;
				$als
				->addressbookAddress->internalId = $customer_internal_id;
				$als
				->addressbookAddress->override = false;
				$als
				->addressbookAddress->state = $address['state'];
				$als
				->addressbookAddress->zip = $address['postcode'];

				if (!empty($address['companyName'])) {
					$als
					->addressbookAddress->addressee = $address['firstName'] . ' ' . $address['lastName'];
					$als
					->addressbookAddress->attention = $address['companyName'];

				} else {
					// $als->addressbookAddress->addressee = $address['firstName'] . ' ' . $address['lastName'];
					$als
					->addressbookAddress->attention = $address['firstName'] . ' ' . $address['lastName'];

				}
			}

		}

		$check_billing_address_is_existing = $this->checkCustomerAddressAlreadyExistOnNetSuite($existing_customer_address, $alb);
		if ('false' == $check_billing_address_is_existing) {
			$address_data[] = $alb;
		}

		$check_shipping_address_is_existing = $this->checkCustomerAddressAlreadyExistOnNetSuite($existing_customer_address, $als);
		if ('false' == $check_shipping_address_is_existing) {
			$address_data[] = $als;
		}

		return $address_data;
	}

	public function checkCustomerAddressAlreadyExistOnNetSuite( $existing_customer_address_on_ns, $newWooAddress ) {
		$exist_check = 'false';
		if (isset($existing_customer_address_on_ns->addressbook) && !empty($existing_customer_address_on_ns->addressbook)) {
			foreach ($existing_customer_address_on_ns->addressbook as $key => $ns_address) {
				$ExistingNSaddr1 = strtolower(trim($ns_address
					->addressbookAddress
					->addr1));
				$newAddr1 = strtolower(trim($newWooAddress
					->addressbookAddress
					->addr1));
				if ($ExistingNSaddr1 == $newAddr1) {
					$exist_check = 'true';
				}
			}
		}
		return $exist_check;
	}
	/**
	 * Get Order Data
	 *
	 */
	public function getOrderData( $order_id, $rec_type ) {

		global $TMWNI_OPTIONS;
		$data = array();
		$order = new WC_Order($order_id);
		$data['order_id'] = $order_id;
		$data['order'] = $order;
		$user_id = $order->get_user_id();
		if (!$user_id) { //if customer is not a registered woocommerce user then use billing email
			$data['customer_email'] = $order->get_billing_email();
		} else { //else fetch user email from woocommerce
			$user_data = get_userdata($user_id);
			$data['customer_email'] = $user_data
			->data->user_email;
		}
		$data['order_status'] = $order->get_status();
		$data['order_currency'] = $order->get_currency();
		$data['total_shipping'] = $order->get_total_shipping();
		$billing_address = $order->get_Address();
		if (isset($billing_address['country']) && !empty($billing_address['country'])) {
			$ns_country = TMWNI_Settings::$netsuite_country[$billing_address['country']];
		} else {
			$ns_country = '';
		}
		$billing_address['country'] = $ns_country;
		$data['billing_address'] = $billing_address;
		$data['shipping_address'] = $order->get_Address('shipping');
		if (isset($data['shipping_address']['country']) && !empty($data['shipping_address']['country'])) {
			$ns_shipping_country = TMWNI_Settings::$netsuite_country[$data['shipping_address']['country']];
		} else {
			$ns_shipping_country = '';
		}
		//breaking shipping address
		$data['shipping_address']['country'] = $ns_shipping_country;
		$order_payment_method_id = $order->get_payment_method();
		$data['order_payment_method'] = $order_payment_method_id;
		$shipping_method = array_values($order->get_shipping_methods());
		$data['order_shipping_method'] = $shipping_method;
		$order_items = array_values($order->get_items());
		$data['items'] = array();
		$data['items'] = $this->checkOrderItems($order_items, $order_id, $rec_type);
		return $data;
	}
	/**
	 * Check Order Items
	 *
	 */
	public function checkOrderItems( $order_items, $order_id, $rec_type ) {
		$items = array();
		$count = 0;
		foreach ($order_items as $key => $order_item) {
			$product = new WC_Product($order_item['product_id']);
			$order_item_id = $order_item->get_id();
			$product_sku = $product->get_sku(); // MANISH : CHANGE THIS TO CUSTOM FIELD
			if (isset($order_item['variation_id']) && !empty($order_item['variation_id'])) {
				$variation_obj = new WC_Product_Variation($order_item['variation_id']);
				if ($variation_obj->variation_has_sku) {
					$product_sku = $variation_obj->get_sku();
				}
				$unit_price = $variation_obj->get_price();

				$netsuite_internal_id = get_post_meta($order_item['variation_id'], TMWNI_Settings::$ns_product_id, true);
			} else {
				$unit_price = $product->get_price();
				$netsuite_internal_id = get_post_meta($order_item['product_id'], TMWNI_Settings::$ns_product_id, true);

			}
			if (empty($netsuite_internal_id)) {
				if (TMWNI_Settings::$ns_rec_type_order == $rec_type) {
					$netsuite_internal_id = $this
					->netsuiteOrderClient
					->searchItem($product_sku, $order_item['product_id'], $order_item['variation_id']);
				}
				if (0 == $netsuite_internal_id) {
					continue;
				}
			}
			if (isset($order_item['variation_id']) && !empty($order_item['variation_id'])) {
				$location_id = get_post_meta($order_item['variation_id'], 'ns_item_location_id', true);
			} else {
				$location_id = get_post_meta($order_item['product_id'], 'ns_item_location_id', true);
			}
			$items[$key]['internalId'] = $netsuite_internal_id;
			$items[$key]['total'] = $order_item['total'];
			$items[$key]['unit_price'] = $unit_price;
			$items[$key]['qty'] = $order_item['qty'];
			$items[$key]['total_tax'] = $order_item['total_tax'];
			$items[$key]['locationId'] = $location_id;
			$items[$key]['productId'] = $order_item['product_id'];
			$items[$key]['subtotal'] = $order_item['subtotal'];
			$items[$key]['order_item_id'] = $order_item_id;
		}
		$items = array_reverse($items);

		return $items;
	}
	/**
	 * Order tracking file include
	 *
	 */
	public function fetchOrderTrackingInfo() {
		global $TMWNI_OPTIONS;
		if (isset($TMWNI_OPTIONS['enableFulfilmentSync']) && 'on' == $TMWNI_OPTIONS['enableFulfilmentSync']) {
			require_once TMWNI_DIR . 'inc/orderTracking.php';
		} elseif (( isset($TMWNI_OPTIONS['ns_order_tracking_email']) && 'on' == $TMWNI_OPTIONS['ns_order_tracking_email'] ) || ( isset($TMWNI_OPTIONS['ns_order_auto_complete']) && 'on' == $TMWNI_OPTIONS['ns_order_auto_complete'] )) {
			require_once TMWNI_DIR . 'inc/orderTracking.php';
		}
	}

	public function updateOrderInternalIdDB( $order_id, $order_netsuite_internal_id ) {
		global $TMWNI_OPTIONS;
		if (isset($TMWNI_OPTIONS['ns_order_record_type']) && 'cashsale' == $TMWNI_OPTIONS['ns_order_record_type']) {
			tm_ns_update_post_meta($order_id, TMWNI_Settings::$ns_cash_sale_id, $order_netsuite_internal_id);
		}
		tm_ns_update_post_meta($order_id, TMWNI_Settings::$ns_order_id, $order_netsuite_internal_id);
	}

	public function getOrderInternalIdFromDB( $order_id ) {
		global $TMWNI_OPTIONS;

		$record_type = tm_ns_get_post_meta($order_id, 'ns_record_type', true);
		if ('cashsale.nl' == $record_type) {
			$nsOrderInternalId = tm_ns_get_post_meta($order_id, esc_attr(TMWNI_Settings::$ns_cash_sale_id));
		} else {
			$nsOrderInternalId = tm_ns_get_post_meta($order_id, esc_attr(TMWNI_Settings::$ns_order_id));
		}

		return $nsOrderInternalId;
	}
	/**
	 * Delete Order
	 *
	 */
	public function deleteNetsuiteOrder( $post_id ) {
		global $TMWNI_OPTIONS;
		global $wpdb;
		$post_type = tm_ns_get_post_type($post_id);
		if ('shop_order' == $post_type) {
			$nsOrderInternalId = $this->getOrderInternalIdFromDB($post_id);
			$record_type = tm_ns_get_post_meta($post_id, 'ns_record_type', true);
			if (!empty($nsOrderInternalId)) {
				$this->netsuiteOrderClient->deleteOrder($nsOrderInternalId, $post_id);
				if ('cashsale.nl' == $record_type) {
					tm_ns_delete_post_meta($post_id, TMWNI_Settings::$ns_cash_sale_id, $nsOrderInternalId);
				} 
				tm_ns_delete_post_meta($post_id, TMWNI_Settings::$ns_order_id, $nsOrderInternalId);
				$wpdb->netsuite_order_logs = $wpdb->prefix . 'tm_woo_netsuite_auto_sync_order_status';
				$order_data_logs_delete = $wpdb->query($wpdb->prepare(" DELETE  FROM {$wpdb->netsuite_order_logs} WHERE ns_order_internal_id = %d ", $nsOrderInternalId, OBJECT));

			}

		}
		return;
	}
	public function push_orders_to_queue( $order_id ) {
		$this
		->add_netsuite_order
		->push_to_queue($order_id);
		$this
		->add_netsuite_order
		->save()
		->dispatch();

		return false;
	}
	public function create_netsuite_refund( $order_id, $refund_id ) {
		$order_ns_id = $this->getOrderInternalIdFromDB($order_id);
		if (!empty($order_ns_id)) {
			$orderRefund = new OrderRefund();
			$ns_order_refund_id = $orderRefund->create_nd_refund($order_id, $refund_id, $order_ns_id);
			if (!empty($ns_order_refund_id)) {
				tm_ns_update_post_meta($order_id, TMWNI_Settings::$ns_order_refund_internal_id, $ns_order_refund_id);

			}
		}
	}
	public static function fetchNSRefundOrder() {
		$orderRefund = new OrderRefund();
		$ns_order_refund_id = $orderRefund->get_refund_order();
	}
	/**
	 * Adds data to the custom "new-data" column in "My Account > Orders".
	 *
	 * WC_Order $order the order object for the row
	 */
	public function tm_ns_my_orders_tracking_number( $order ) {
		$tracking_number = tm_ns_get_post_meta($order->get_id() , 'ywot_tracking_code');
		$tracking_number = ( !empty($tracking_number) ) ? $tracking_number : 'N/A';
		echo esc_html($tracking_number);
	}
	public function tm_ns_add_my_account_orders_column( $columns ) {
		$new_columns = array();
		foreach ($columns as $key => $name) {
			$new_columns[$key] = $name;
			if ('order-status' === $key) {
				$new_columns['tracking-number'] = __('Tracking Number', 'textdomain');
			}
		}

		return $new_columns;
	}
}




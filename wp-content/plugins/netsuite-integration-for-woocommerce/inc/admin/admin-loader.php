<?php

use NetSuite\Classes\GetServerTimeRequest;
use NetSuite\NetSuiteService;
use NetSuite\Classes\Customer;
use NetSuite\Classes\SalesOrder;
use NetSuite\Classes\CashSale;
use NetSuite\Classes\Invoice;
use NetSuite\Classes\LocationSearchBasic;
use NetSuite\Classes\SearchEnumMultiSelectField;
use NetSuite\Classes\SearchRequest;
use NetSuite\Classes\SearchBooleanField;
use NetSuite\Classes\GetSelectValueFieldDescription;
use NetSuite\Classes\getSelectValueRequest;
use NetSuite\Classes\RecordType;
use NetSuite\Classes\PriceLevelSearchBasic;
use NetSuite\Classes\SalesTaxItemSearchBasic;
use Automattic\WooCommerce\Utilities\OrderUtil;

class TMWNI_Admin_Loader extends CommonIntegrationFunctions {
	private $netsuiteParameters;
	private $cust_woo_order_fields = array(
		'id',
		'customer_note', // The billing address first name.
		'billing_first_name', // The billing address first name.
		'billing_last_name', // The billing address last name.
		'billing_company', // The billing address company.
		'billing_address_1', // The first line of the billing address.
		'billing_address_2', // The second line of the billing address.
		'billing_city', // The city of the billing address.
		'billing_state', // The state of the billing address.
		'billing_postcode', // The postcode of the billing address.
		'billing_country', // The country of the billing address.
		'billing_phone', // The billing phone number.
		'billing_email', // The billing email.
		'shipping_first_name', // The shipping address first name.
		'shipping_last_name', // The shipping address last name.
		'shipping_company', // The shipping address company.
		'shipping_address_1', // The first line of the shipping address.
		'shipping_address_2', // The second line of the shipping address.
		'shipping_city', // The city of the shipping address.
		'shipping_state', // The state of the shipping address.
		'shipping_postcode', // The postcode of the shipping address.
		'shipping_country', // The country of the shipping address.
		'shipping_method', // Order Shipping Method
		'cart_discount', // Total amount of discount.
		'cart_discount_tax', // Total amount of discount applied to taxes.
		'customer_user', // User ID who the order belongs to. 0 for guests.
		'order_key', // Random key/password unqique to each order.
		// 'order_discount', // Stored after tax discounts pre-2.3. Now @deprecated.
		'order_tax', // Stores order tax total.
		'order_shipping_tax', // Stores shipping tax total.
		'order_shipping', // Stores shipping total.
		'order_total', // Stores order total.
		'order_currency', // Stores currency code used for the order.
		'payment_method', // method ID.
		'payment_method_title', // Name of the payment method used.
	);
	// user_id,email custom fields and woo customer class magic variables
	private $cust_woo_customer_fields = array(
		'user_id',
		'country',
		'state',
		'email',
		'phone',
		'postcode',
		'city',
		'address_1',
		'address_2',
		'company',
		'shipping_country',
		'shipping_state',
		'shipping_postcode',
		'shipping_city',
		'shipping_address_1',
		'shipping_address_2',
		'is_vat_exempt',
		'calculated_shipping',
	);
	// This array contains all required conditional mappings
	private $cust_prefinedCondMapping = array(
		array(
			'type'                  => 1,
			'operator'              => 3,
			'ns_field_key'          => 'entityid',
			'wc_field_key'          => 'email',
			'wc_field_value_prefix' => '',
			'required'              => 1,
		),
	);

	private static $instance = null;
	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new TMWNI_Admin_loader();
		}
		return self::$instance;
	}
	// Consturct Function
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'TMWNIAdminMenu' ) );

		add_filter( 'plugin_action_links_' . TMWNI_BASEURL, array( $this, 'tm_plugin_settings' ) );

		add_filter( 'plugin_row_meta', array( $this, 'tm_custom_plugin_row_meta' ) , 10, 2);

		add_action( 'admin_enqueue_scripts', array( $this, 'TMWNIAdminScript' ) );

		add_action( 'admin_post_save_tm_ns_settings', array( $this, 'tmwniHanldeActions' ) );

		add_action( 'wp_ajax_load_tmwni_logs', array( $this, 'getLogs' ) );

		add_action( 'wp_ajax_tm_clear_customer_ns_id', array( $this, 'tmwniDeleteValue' ) );

		add_action( 'admin_post_import_export_tm_ns', array( $this, 'tmwniHanldeExportActions' ) );

		add_action( 'wp_ajax_import_netsuite_settings', array( $this, 'ImportNetsuiteSettings' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		add_action( 'wp_ajax_tm_clear_logs', array( $this, 'clearAllApiLogs' ) );

		add_action( 'wp_ajax_tm_clear_dashboard_logs', array( $this, 'clearAllDashboardLogs' ) );

		add_action( 'wp_ajax_get_conditional_mapping_template', array( $this, 'getConditionalMappingTemplate' ) );

		add_action( 'wp_ajax_tm_netsuite_cm_save', array( $this, 'saveConditionalMappingForm' ) );

		add_action( 'show_user_profile', array( $this, 'extra_user_profile_fields' ) );

		add_action( 'edit_user_profile', array( $this, 'extra_user_profile_fields' ) );

		add_action( 'wp_ajax_order_logs', array( $this, 'getOrderLogs' ) );

		if ( TMWNI_Settings::areCredentialsDefined() ) {

			add_action( 'wp_ajax_tm_validate_ns_credentials', array( $this, 'validateCredentials' ) );

			add_action( 'wp_ajax_tm_load_ns_promo_feilds_value', array( $this, 'loadPromoFields' ) );

			add_action( 'wp_ajax_tm_load_ns_locations', array( $this, 'loadNsLocations' ) );

			add_action( 'wp_ajax_tm_load_ns_price_levels', array( $this, 'loadNsPriceLevels' ) );

			add_action( 'wp_ajax_tm_load_ns_tax_code', array( $this, 'saveNetsuiteTaxCodes' ) );

			add_action( 'wp_ajax_tm_load_ns_price_currency', array( $this, 'loadNsPriceCurrency' ) );

			add_action( 'admin_notices', array( $this, 'inventory_custom_notices' ) );

		}
	}

	public function inventory_custom_notices() {
		$permission_enable = get_option('tm_rest_web_service_enable');
		if ('no' == $permission_enable) {
			?>
	<div class="notice notice-success is-dismissible">
		<h2><?php esc_html_e('TM WooCommerce NetSuite Integration', 'my-text-domain'); ?></h2>
		<p><?php esc_html_e('We have made an important update to the Inventory Sync settings. To ensure continued operation, you will need to update the user role permissions in NetSuite accordingly.', 'my-text-domain'); ?></p>
		<p>
			<a href="https://techmarbles.com/docs/woocommerce-netsuite-integration/general-settings/" target="_blank" class="button button-primary">
				<?php esc_html_e('Follow Instructions', 'my-text-domain'); ?>
			</a>
		</p>
	</div>
	<?php
		}
	}

	public function tm_plugin_settings( $settings ) {
		array_unshift($settings, '<a href="admin.php?page=tmwni">Settings</a>');
		return $settings;
	}

	public function tm_custom_plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( 'netsuite-integration-for-woocommerce/netsuite-integration-for-woocommerce.php' === $plugin_file ) {
			$new_plugin_meta = array(
				'docs' => '<a href="' . esc_url( 'https://techmarbles.com/docs/woocommerce-netsuite-integration/' ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr__( 'View plugin documentation', 'your-text-domain' ) . '">' . esc_html__( 'Docs' ) . '</a>',
			);
			return array_merge( $plugin_meta, $new_plugin_meta );
		}

		return $plugin_meta;
	}

	public function getOrderLogs() {
		global $wpdb;
		global $TMWNI_OPTIONS;
		if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {

			if ( ! empty( $_POST ) ) {
				$request = $_POST;

				require_once TMWNI_DIR . '/inc/datatables.php';

				$datatables = new Datatables();

				$columns = array(
					array(
						'db'     => 'orderlog.id as id',
						'dt'     => 0,
						'db_ref' => 'id',
					),

					array(
						'db'     => 'log.woo_object_id as woo_object_id',
						'dt'     => 1,
						'db_ref' => 'woo_object_id',
					),
					array(
						'db'     => 'orderlog.created_at as created_at',
						'dt'     => 2,
						'db_ref' => 'created_at',
					),
					array(
						'db'     => 'orderlog.status as status',
						'dt'     => 3,
						'db_ref' => 'status',
					),
					array(
						'db'     => 'orderlog.ns_order_status as ns_order_status',
						'dt'     => 4,
						'db_ref' => 'ns_order_status',
					),
					array(
						'db'     => 'orderlog.ns_order_internal_id as ns_order_internal_id',
						'dt'     => 5,
						'db_ref' => 'ns_order_internal_id',
					),
					array(
						'db'     => 'orderlog.notes as notes',
						'dt'     => 6,
						'db_ref' => 'notes',
					),

				);

				$limit                     = $datatables->limit( $request, $columns );
				$order                     = $datatables->order( $request, $columns );
				$where                     = $datatables->filter( $request, $columns, $binding );
				$wpdb->netsuite_order_logs = $wpdb->prefix . 'tm_woo_netsuite_auto_sync_order_status';

				$limit_arr = explode( ' ', $limit );
				$order_arr = explode( ' ', $order );

				if ( 'id' == $order_arr[2] && 'ASC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog WHERE (orderlog.woo_object_id  LIKE %d) ORDER BY id ASC limit %d, %d", $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog  ORDER BY id ASC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'id' == $order_arr[2] && 'DESC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );

						$data = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT SQL_CALC_FOUND_ROWS 
								orderlog.id as id,
								orderlog.created_at as created_at,
								orderlog.operation as operation,
								orderlog.ns_order_status as ns_order_status,
								orderlog.notes as notes,
								orderlog.woo_object_id as woo_object_id,
								orderlog.ns_order_internal_id as ns_order_internal_id 
								FROM {$wpdb->netsuite_order_logs} as orderlog 
								WHERE (orderlog.woo_object_id LIKE %s) 
								ORDER BY id DESC 
								LIMIT %d, %d",
								'%' . $where . '%', // This adds the wildcards for the LIKE clause
								$limit_arr[1], 
								$limit_arr[2]
							),
							ARRAY_A
						);
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog  ORDER BY id DESC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}

				if ( 'woo_object_id' == $order_arr[2] && 'ASC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog WHERE (orderlog.status  LIKE %s OR orderlog.notes  LIKE %s) ORDER BY woo_object_id ASC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog  ORDER BY woo_object_id ASC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'woo_object_id' == $order_arr[2] && 'DESC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog WHERE (orderlog.status  LIKE %s OR orderlog.notes  LIKE %s) ORDER BY woo_object_id DESC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog  ORDER BY woo_object_id DESC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}

				if ( 'created_at' == $order_arr[2] && 'ASC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog WHERE (orderlog.status  LIKE %s OR orderlog.notes  LIKE %s) ORDER BY created_at ASC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog  ORDER BY created_at ASC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'created_at' == $order_arr[2] && 'DESC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog WHERE (orderlog.status  LIKE %s OR orderlog.notes  LIKE %s) ORDER BY created_at DESC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog  ORDER BY created_at DESC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'ns_order_status' == $order_arr[2] && 'ASC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog WHERE (orderlog.status  LIKE %s OR orderlog.notes  LIKE %s) ORDER BY ns_order_status ASC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog  ORDER BY ns_order_status ASC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'ns_order_status' == $order_arr[2] && 'DESC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog WHERE (orderlog.status  LIKE %s OR orderlog.notes  LIKE %s) ORDER BY ns_order_status DESC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS orderlog.id as id,orderlog.created_at as created_at,orderlog.operation as operation,orderlog.ns_order_status as ns_order_status,orderlog.notes as notes,orderlog.woo_object_id as woo_object_id,orderlog.ns_order_internal_id as ns_order_internal_id FROM {$wpdb->netsuite_order_logs} as orderlog  ORDER BY ns_order_status DESC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( isset( $TMWNI_OPTIONS['ns_account'] ) && ! empty( $TMWNI_OPTIONS['ns_account'] ) ) {
					$ns_account_id = $TMWNI_OPTIONS['ns_account'];
				}

				$data_filter = $wpdb->get_results( 'SELECT FOUND_ROWS() as filtered_rows' );
				// total filtered records
				$recordsFiltered = $data_filter[0]->filtered_rows;

				$recordsTotal = $recordsFiltered;

				$site_url = get_site_url();

				$records = array();

				foreach ( $data as $key => $record ) {
					$record_type = tm_ns_get_post_meta( $record['woo_object_id'], 'ns_record_type', true );
					if ( empty( $record_type ) ) {
						$record_type = 'salesord.nl';
					}

					$order_link            = $site_url . '/wp-admin/post.php?post=' . $record['woo_object_id'] . '&amp;action=edit';
					$ns_explode_account_id = explode( '_', ( $ns_account_id ) );
					$ns_emplode_account_id = implode( '-', $ns_explode_account_id );

					// $ns_url = "https://' . $ns_emplode_account_id . '.app.netsuite.com/app/accounting/transactions/salesord.nl?id=' . $record['ns_order_internal_id'] . '&amp;whence=";
					$ns_url = 'https://' . $ns_emplode_account_id . '.app.netsuite.com/app/accounting/transactions/' . $record_type . '?id=' . $record['ns_order_internal_id'] . '&whence=';

					$rows   = array();
					$rows[] = $record['id'];
					$rows[] = $record['woo_object_id'];
					$rows[] = $record['created_at'];
					$rows[] = ( ! empty( $record['ns_order_internal_id'] ) ) ? '<a target="_blank" href="' . $ns_url . '" class="btn btn-success">View</a>' : '';

					if ( ! empty( $record['ns_order_internal_id'] ) ) {
						$rows[] = $record['ns_order_status'];
					} else {
						$rows[] = $record['ns_order_status'] . '&nbsp;&nbsp;
						<a style="color:#95bf47" data-toggle="collapse" href="#collapsable-msg-' . $key . '" role="button" aria-expanded="false" aria-controls="collapsable-msg-' . $key . '">Know More</a><div class="row">
						<div class="col">
						<div class="collapse multi-collapse" id="collapsable-msg-' . $key . '">
						<div class="card card-body">' . $record['notes'] . '</div>
						</div>
						</div>
						</div>';
					}

					if ( isset( $TMWNI_OPTIONS['enableOrderSync'] ) && 'on' == $TMWNI_OPTIONS['enableOrderSync'] ) {
						$rows[] = '<div class="manually_order_sync_btn">
						<a target="_blank" href="' . $order_link . '"  class="btn btn-success">View</a>&nbsp;
						<button type="button" class="btn btn-success manual_order_sync"  data-id="' . $record['woo_object_id'] . '">Re-Submit</button>
						<span class="loaderSpiner"></span>
						</div>';
					} else {
						$rows[] = '<div class="manually_order_sync_btn">
						<a target="_blank" href="' . $order_link . '"  class="btn btn-success">View</a>
						</div>';
					}

					$records[] = $rows;
					// code...
				}
				// json to be returned
				echo json_encode(
					array(
						'draw'            => intval( $request['draw'] ),
						'recordsTotal'    => intval( $recordsTotal ),
						'recordsFiltered' => intval( $recordsFiltered ),
						'data'            => $records,
					)
				);
				die;
			}die;
		} else {
			echo json_encode(
				array(
					'draw'            => 0,
					'recordsTotal'    => 0,
					'recordsFiltered' => 0,
					'data'            => 0,
				)
			);
		}
	}

	public function validateCredentials() {
		$return           = array();
		$return['status'] = 0;
		$return           = $this->checkNSCreds();

		if ( sanitize_text_field( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) && '' !== sanitize_text_field( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'xmlhttprequest' == strtolower( sanitize_text_field( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) ) {
			echo json_encode( $return );
			die;
		} else {
			return $return;
		}
	}

	public function checkNSCreds( $settings = array() ) {
		$return = array();
		if ( TMWNI_Settings::areCredentialsDefined() || ! empty( $settings ) ) {
			$ns_service = new NetSuiteService( null, array(), $settings );

			$GetServerTimeRequest = new GetServerTimeRequest();
			// pr($GetServerTimeRequest);
			try {
				$rtn_data = $ns_service->getServerTime( $GetServerTimeRequest );

				if ( isset( $rtn_data->getServerTimeResult->status->isSuccess ) && 1 == $rtn_data->getServerTimeResult->status->isSuccess ) {
					$return['status']  = 1;
					$return['message'] = 'Congrats. API connection is successful.';
				} elseif ( isset( $rtn_data->detail->invalidCredentialsFault->message ) && ! empty( $rtn_data->detail->invalidCredentialsFault->message ) ) {
					$return['status']  = 0;
					$error_msg         = $rtn_data->detail->invalidCredentialsFault->message;
					$return['message'] = 'Something wrong with API Credentials. Please check logs tab for more help';
					$this->handleLog( 0, 0, 'validate creds', $error_msg );

				}
			} catch ( SoapFault $e ) {
				$return['status']  = 0;
				$return['message'] = 'Something wrong with API Credentials. Please check logs tab for more help';
				$this->handleLog( 0, 0, 'validate creds', $e->getMessage() );
			}
		} else {
			$return['message'] = "API Credentials are not defined.Please 'enter & save' API credentials first";
		}
		return $return;
	}

	public function loadNsPriceCurrency() {

		$priceCurrency = self::getSettingsFromNS( 'currency', 'inventoryItem' );
		if ( ! empty( $priceCurrency ) ) {
			update_option( 'netstuite_price_currency', $priceCurrency );
		}
	}

	public function getNetsuitePromoSettings() {
		// For Promotion Custom Form
		$Promo_custom_form = get_option( 'netsuite_promo_customForm' );
		if ( empty( $Promo_custom_form ) ) {
			$Promo_custom_form = self::getSettingsFromNS( 'customform', 'promotionCode' );
			update_option( 'netsuite_promo_customForm', $Promo_custom_form, false );
		}
		// For Promotion Discount Item
		$Promo_DiscountItem = get_option( 'netsuite_promo_discountItem' );
		if ( empty( $Promo_DiscountItem ) ) {
			$Promo_DiscountItem = self::getSettingsFromNS( 'discount', 'promotionCode' );
			update_option( 'netsuite_promo_discountItem', $Promo_DiscountItem, false );
		}
	}

	public function saveNetsuiteTaxCodes() {
		$ns_tax_codes = $this->getNetsuiteTaxCodes();
		if (!empty( $ns_tax_codes ) ) {
			update_option( 'netsuite_tax_codes', $ns_tax_codes, false );
		}
	}

	public function getNetsuiteTaxCodes() {

		$ns_service = new NetSuiteService();
		$ns_service->setSearchPreferences( false, 1000, true );

		$selectedField              = new SearchBooleanField();
		$selectedField->searchValue = false;

		$salesTaxItemSearch             = new SalesTaxItemSearchBasic();
		$salesTaxItemSearch->isInactive = $selectedField;

		$request               = new SearchRequest();
		$request->searchRecord = $salesTaxItemSearch;

		try {
			$searchResponse = $ns_service->search( $request );
			$data           = array();
			if ( $searchResponse->searchResult->totalRecords > 0 ) {
				foreach ( $searchResponse->searchResult->recordList->record as $key => $value ) {
					$data[ $value->internalId ] = $value->itemId;
				}
				return $data;
			} else {
				return 0;
			}
		} catch ( SoapFault $e ) {
			return 0;
		}
	}

	public static function getNetsuiteInventoryLocationPriceSettings() {

		$locations = get_option( 'netstuite_locations' );
		if ( empty( $locations ) ) {
			$locations = self::getSettingsFromNS( 'location', 'inventoryItem' );
			update_option( 'netstuite_locations', $locations, false );
		}

		$price_levels = get_option( 'netstuite_price_levels' );
		if ( empty( $price_levels ) ) {
			$price_levels = self::getPriceLevels();
			update_option( 'netstuite_price_levels', $price_levels, false );
		}
	}

	public static function getPriceLevels() {

		$ns_service = new NetSuiteService();

		$selectedField              = new SearchBooleanField();
		$selectedField->searchValue = false;

		$priceLevelSearch             = new PriceLevelSearchBasic();
		$priceLevelSearch->isInactive = $selectedField;

		$request               = new SearchRequest();
		$request->searchRecord = $priceLevelSearch;

		try {
			$searchResponse = $ns_service->search( $request );
			$data           = array();
			if ( $searchResponse->searchResult->totalRecords > 0 ) {
				foreach ( $searchResponse->searchResult->recordList->record as $key => $value ) {
					$data[ $value->internalId ] = $value->name;
				}

				return $data;

			} else {
				return 0;
			}
		} catch ( SoapFault $e ) {
			return 0;
		}
	}

	public function loadPromoFields() {
		if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {
			$fields = array( 'customform', 'discount' );

			foreach ( $fields as  $value ) {
				$data = $this->getSettingsFromNS( $value, 'promotionCode' );
				if ( ! empty( $data ) && 'customform' == $value ) {
					update_option( 'netsuite_promo_customForm', $data, false );
				}

				if ( ! empty( $data ) && 'discount' == $value ) {
					update_option( 'netsuite_promo_discountItem', $data, false );
				}
			}

			echo json_encode(
				array(
					'status' => true,
				)
			);

		} else {
			die( 'Nonce Error' );
		}
	}

	public function loadNsLocations() {
		if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {
			$locations = self::getLocationsFromNS();
			if ( ! empty( $locations ) ) {
				update_option( 'netstuite_locations', $locations, false );
				die(
					json_encode(
						array(
							'status' => true,
						)
					)
				);
			} else {
				die(
					json_encode(
						array(
							'type'   => 'Error',
							'msg'    => 'No data found',
							'status' => 0,
						)
					)
				);
			}
		} else {
			die( 'Nonce Error' );
		}
	}

	public function getLocationsFromNS() {

		$object     = 'location';
		$ns_service = new NetSuiteService();

		$SearchField              = new SearchBooleanField();
		$SearchField->searchValue = false;
		$search                   = new LocationSearchBasic();
		$search->isInactive       = $SearchField;

		$request               = new SearchRequest();
		$request->searchRecord = $search;

		try {
			$searchResponse = $ns_service->search( $request );
			if ( 1 == $searchResponse->searchResult->status->isSuccess ) {
				// die('one');
				$record = $searchResponse->searchResult->recordList->record;
				if ( ! empty( $record ) ) {
					$data = self::getLocationsFromNSResponse( $record );
					return $data;
				} else {
					return 0;
				}
			} else {

				$error_msg .= 'Error Message : ' . $searchResponse->searchResult->status->statusDetail[0]->message;
				$this->handleLog( 0, 0, $object, $error_msg );
				return 0;
			}
		} catch ( SoapFault $e ) {

			$error_msg  = "SOAP API Error occured on '" . ucfirst( $object ) . " Search' operation failed for WooCommerce " . $object;
			$error_msg .= 'Error Message: ' . $e->getMessage();

			$this->handleLog( 0, 0, $object, $error_msg );

			return 0;
		}
	}

	public static function getLocationsFromNSResponse( $records ) {
		$data = array();
		foreach ( $records as $key => $record ) {
			$data[ $record->internalId ] = $record->name;

		}
		return $data;
	}

	public static function getSettingsFromNS( $field, $type ) {
		$ns_service = new NetSuiteService();

		$SearchField        = new GetSelectValueFieldDescription();
		$SearchField->field = $field;

		$SearchField->recordType          = $type;
		$SearchField->recordTypeSpecified = true;

		$requestValue                   = new getSelectValueRequest();
		$requestValue->fieldDescription = $SearchField;
		$requestValue->pageIndex        = 0;

		try {
			$getSelectValueResult = $ns_service->getSelectValue( $requestValue );
			$data                 = array();
			if ( $getSelectValueResult->getSelectValueResult->totalRecords > 0 ) {
				foreach ( $getSelectValueResult->getSelectValueResult->baseRefList->baseRef as $key => $value ) {
					$data[ $value->internalId ] = $value->name;
				}
				return $data;

			} else {
				return 0;
			}
		} catch ( SoapFault $e ) {
			return 0;
		}
	}

	public static function loadNsPriceLevels() {
		if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {
			$pricelevels = self::getPriceLevels();
			if ( ! empty( $pricelevels ) ) {
				update_option( 'netstuite_price_levels', $pricelevels, false );
				die(
					json_encode(
						array(
							'status' => true,
						)
					)
				);
			} else {
				die(
					json_encode(
						array(
							'type'   => 'Error',
							'msg'    => 'No data found',
							'status' => 0,
						)
					)
				);
			}
		} else {
			die( 'Nonce Error' );
		}
	}

	public function extra_user_profile_fields( $user ) {
		$ns_customer_internal_id = get_the_author_meta( 'ns_customer_internal_id', $user->ID );
		?>

		<table class="form-table">
			<tr>
				<th><label for="ns_id"><?php esc_attr_e( 'NetSuite Internal ID' ); ?></label></th>
				<td width="20%">
					<input name="ns_customer_internal_id" id="ns_id" value="<?php ! empty( $ns_customer_internal_id ) ? esc_attr_e( trim( $ns_customer_internal_id ) ) : ''; ?>" disabled>
					<br />
				</td>

				<td>
					<input type="submit" class="button-primary" name="delete" value="Delete" id="delete_user_ns_id" user-id="<?php echo esc_attr( $user->ID ); ?>" <?php echo ! empty( $ns_customer_internal_id ) ? ' ' : "disabled='disabled'"; ?>/>

				</td> 
			</tr>
		</table>
		<?php
	}

	public static function saveConditionalMappingForm() {
		$request = $_REQUEST;

		if ( is_array( $request['cm'] ) ) {
			$request = array_values( $request['cm'] );
			// validate data
			foreach ( $request as $key => $mapping ) {
				switch ( $mapping['operator'] ) {
					case 0:
						if ( count( $request ) > 1 ) {
							unset( $request[ $key ] );
							die(
							json_encode(
								array(
									'type' => 'blankfield',
									'msg'  => 'Required fields cannot be left blank',
								)
							)
							);
						}
						break;
					case 1:
						if ( ! isset( $mapping['wc_field_key'] ) || '' == $mapping['wc_field_key'] || ! isset( $mapping['wc_field_value'] ) || '' == $mapping['wc_field_value'] || ! isset( $mapping['ns_field_key'] ) || '' == $mapping['ns_field_key'] || ! isset( $mapping['ns_field_value'] ) || '' == $mapping['ns_field_value'] ) {
							unset( $request[ $key ] );
							die(
							json_encode(
								array(
									'type' => 'blankfield',
									'msg'  => 'Required fields cannot be left blank',
								)
							)
							);
						}
						break;
					case 2:
						if ( ! isset( $mapping['ns_field_key'] ) || '' == $mapping['ns_field_key'] || ! isset( $mapping['ns_field_value'] ) || '' == $mapping['ns_field_value'] ) {
							unset( $request[ $key ] );
							die(
							json_encode(
								array(
									'type' => 'blankfield',
									'msg'  => 'Required fields cannot be left blank',
								)
							)
							);
						}
						break;
					case 3:
						if ( ( '0' == $mapping['type'] ) || ( '0' == $mapping['wc_field_key'] ) || '' == $mapping['wc_field_key'] || ! isset( $mapping['ns_field_key'] ) || '' == $mapping['ns_field_key'] ) {
							unset( $request[ $key ] );
							die(
							json_encode(
								array(
									'type' => 'blankfield',
									'msg'  => 'Required fields cannot be left blank',
								)
							)
							);
						}
						break;
					default:
					unset( $request[ $key ] );
						break;
				}
			}
			$option_tag = isset( $_REQUEST['cm_type'] ) ? sanitize_text_field( $_REQUEST['cm_type'] ) . '_cm_options' : '';// customer_cm_options,order_cm_options
			update_option( $option_tag, $request, false );

			die(
				json_encode(
					array(
						'type' => 'success',
					)
				)
			);
		}
		die(
			json_encode(
				array(
					'type' => 'Error',
					'msg'  => 'Invalid Request',
				)
			)
		);
	}


	private function getTemplate( $type, $operator, $index = 1, $mapping = array(), $tab = '', $attr_type = '', $cm_wc_field_key = '', $cm_wc_field_value = '', $cm_wc_where_op = '' ) {
		if ( 'customer_settings' == $tab ) {
			$this->netsuiteParameters = new Customer();
		} elseif ( 'order_settings' == $tab ) {
			if ( isset( $TMWNI_OPTIONS['ns_order_record_type'] ) && 'cashsale' == $TMWNI_OPTIONS['ns_order_record_type'] ) {
				$this->netsuiteParameters = new CashSale();
			} else {
				$this->netsuiteParameters = new SalesOrder();
			}
		}

		$template = '';

		switch ( $operator ) {
			case 1:
				if ( empty( $mapping ) ) {
					$mapping['wc_field_key']        = '';
					$mapping['wc_field_value']      = '';
					$mapping['ns_field_key']        = '';
					$mapping['ns_field_value']      = '';
					$mapping['ns_field_type_value'] = '';
					$mapping['wc_where_op']         = '';
				}

				if ( 2 == $type ) {
					$template  = '<tr><td><span class="h6 required">Customer Meta Field</span><br/>';
					$template .= '<input class="form-control input-sm" type="text" name="cm[' . $index . '][wc_field_key]" value="' . ( '' != $cm_wc_field_key ? $cm_wc_field_key : $mapping['wc_field_key'] ) . '"></div>';
				} elseif ( 1 == $type ) {
					$template  = '<tr><td><span class="h6 required">Customer Field</span><br/>';
					$template .= $this->getWooCustomerFieldsTemplate( $index, $mapping, $cm_wc_field_key );

					$template .= '</div>';
				} elseif ( 3 == $type ) {
					$template  = '<tr><td><span class="h6 required">Order Field</span><br/>';
					$template .= $this->getWooOrderFieldsTemplate( $index, $mapping, $cm_wc_field_key );
					$template .= '</div>';
				} elseif ( 4 == $type ) {
					$template  = '<tr><td><span class="h6 required">Order Meta Field</span><br/>';
					$template .= '<input class="form-control input-sm" type="text" name="cm[' . $index . '][wc_field_key]" value="' . ( '' != $cm_wc_field_key ? $cm_wc_field_key : $mapping['wc_field_key'] ) . '"></div>';
				}

			$template .= $this->getWCFieldCompOperatorTemplate( $index, $mapping, $cm_wc_where_op );

			$template .= '<td><span class="h6 required">WC Field Value</span><br/><input class="form-control input-sm" type="text" name="cm[' . $index . '][wc_field_value]" value="' . ( '' != $cm_wc_field_value ? $cm_wc_field_value : $mapping['wc_field_value'] ) . '"></td></tr>';
			$template .= '<tr>';

			$template .= $this->getNetSuiteAttributeTypeTemplate( $index, $attr_type, $mapping );

				if ( ( 1 == $attr_type ) || ( isset( $mapping['ns_attr_type'] ) && 1 == $mapping['ns_attr_type'] ) ) {
					$template .= $this->getNetSuiteDefaultFieldTemplate( $index, $mapping );
				} elseif ( ( 2 == $attr_type ) || ( isset( $mapping['ns_attr_type'] ) && 2 == $mapping['ns_attr_type'] ) ) {
					$template .= $this->getNetSuiteCustomFieldTemplate( $index, $mapping );
				}
			$template .= '</tr>';
				break;
			case 2:
				if ( empty( $mapping ) ) {
					$mapping['ns_field_key']        = '';
					$mapping['ns_field_value']      = '';
					$mapping['ns_field_type_value'] = '';
					$mapping['ns_attr_type']        = '';
				}
			$template .= '<tr>';

			$template .= $this->getNetSuiteAttributeTypeTemplate( $index, $attr_type, $mapping );

				if ( ( 1 == $attr_type ) || ( isset( $mapping['ns_attr_type'] ) && 1 == $mapping['ns_attr_type'] ) ) {
					$template .= $this->getNetSuiteDefaultFieldTemplate( $index, $mapping );
				} elseif ( ( 2 == $attr_type ) || ( isset( $mapping['ns_attr_type'] ) && 2 == $mapping['ns_attr_type'] ) ) {
					$template .= $this->getNetSuiteCustomFieldTemplate( $index, $mapping );
				}
			$template .= '</tr>';

				break;
			case 3:
				if ( empty( $mapping ) ) {
					$mapping['wc_field_key']          = '';
					$mapping['ns_field_key']          = '';
					$mapping['wc_field_value_prefix'] = '';
					$mapping['ns_field_type_value']   = '';
				}

			$template .= '<tr>';

			$template .= $this->getNetSuiteAttributeTypeTemplate( $index, $attr_type, $mapping );

				// NETSUITE Default Field
				if ( ( 1 == $attr_type ) || ( isset( $mapping['ns_attr_type'] ) && 1 == $mapping['ns_attr_type'] ) ) {
					$template .= '<tr><td><span class="h6 required">NS Field</span><br/><select class="form-control input-sm ns-field-key" name="cm[' . $index . '][ns_field_key]" style="width:190px;" >';

					foreach ( $this->netsuiteParameters::$paramtypesmap as $key => $value ) {
						if ( 'RecordRef' == $value || 'string' == $value || 'boolean' == $value || 'dateTime' == $value || 'float' == $value || 'integer' == $value ) {
							if ( $key == $mapping['ns_field_key'] ) {
								$template .= '<option data-type="' . $value . '" value="' . $key . '" selected>' . $key . '(' . $value . ')</option>';
							} else {
								$template .= '<option data-type="' . $value . '" value="' . $key . '">' . $key . '(' . $value . ')</option>';

							}
						}
					}
					$template .= '</select></td>';
					$template .= $this->getWooFieldTypeTemplate( $type, $index, $mapping );
					$template .= '<td><input class="ns-field-type" type="hidden" name="cm[' . $index . '][ns_field_type_value]" value="' . ( isset( $mapping['ns_field_type_value'] ) ? $mapping['ns_field_type_value'] : '' ) . '"></td>';
					$template .= '</tr>';
				} elseif ( ( 2 == $attr_type ) || ( isset( $mapping['ns_attr_type'] ) && 2 == $mapping['ns_attr_type'] ) ) { // NETSUITE Custom Fields
					$template .= '<tr><td><span class="h6 required">NS Field</span><br/><input class="form-control input-sm ns-field-key" name="cm[' . $index . '][ns_field_key]" style="width:190px;" value="' . $mapping['ns_field_key'] . '"></td>';
					$template .= $this->getWooFieldTypeTemplate( $type, $index, $mapping );
					$template .= $this->getNetSuiteFieldTypeTemplate( $index, $mapping );
					$template .= '</tr>';
				}

				break;
			default:
				break;
		}

			return $template;
	}
	private function getNetSuiteDefaultFieldTemplate( $index, $mapping ) {
		$template = '<td><span class="h6 required">NS Field</span><br/><select class="form-control input-sm ns-field-key" name="cm[' . $index . '][ns_field_key]">';

		$template .= '<option value="">Please Select</option>';

		foreach ( $this->netsuiteParameters::$paramtypesmap as $key => $value ) {

			if ( 'RecordRef' == $value || 'string' == $value || 'boolean' == $value || 'dateTime' == $value || 'float' == $value || 'integer' == $value || 'GlobalSubscriptionStatus' == $value  || 'SalesOrderOrderStatus' == $value) {

				if ( $key == $mapping['ns_field_key'] ) {
					$template .= '<option data-type="' . $value . '" value="' . $key . '" selected>' . $key . ' (' . $value . ')</option>';
				} else {
					$template .= '<option data-type="' . $value . '" value="' . $key . '">' . $key . ' (' . $value . ')</option>';
				}
			}
		}
		$template .= '</select></td><td><span class="h6 required">NS Field Value</span><br/><input class="form-control input-sm ns-field-value" type="text" name="cm[' . $index . '][ns_field_value]" value="' . $mapping['ns_field_value'] . '"></td>';

		// $template.= $this->getNetSuiteFieldTypeTemplate($index,$mapping);
		$template .= '<td><input class="ns-field-type" type="hidden" name="cm[' . $index . '][ns_field_type_value]" value="' . ( isset( $mapping['ns_field_type_value'] ) ? $mapping['ns_field_type_value'] : '' ) . '"></td>';

		return $template;
	}

	private function getNetSuiteCustomFieldTemplate( $index, $mapping ) {

		$template = $this->getNetSuiteFieldTypeTemplate( $index, $mapping );

		$template .= '<td><span class="h6 required">NS Field</span><br/><input class="form-control input-sm ns-field-key" name="cm[' . $index . '][ns_field_key]" value="' . $mapping['ns_field_key'] . '"></td><td><span class="h6 required">NS Field Value</span><br/><input class="form-control input-sm ns-field-value" type="text" name="cm[' . $index . '][ns_field_value]" value="' . $mapping['ns_field_value'] . '"></td>';

		return $template;
	}


	private function getNetSuiteFieldTypeTemplate( $index, $mapping ) {
		return "<td><span class='h6 required'>NS Field Type</span><br/>
			<select class='form-control input-sm ns-field-type' name='cm[" . $index . "][ns_field_type_value]'>
			<option value='customcurrdatefield' " . ( ( isset( $mapping['ns_field_type_value'] ) && 'customcurrdatefield' == $mapping['ns_field_type_value'] ) ? 'selected' : '' ) . ">Custom CurrDate Field</option>
			<option value='customdateTime' " . ( ( isset( $mapping['ns_field_type_value'] ) && 'customdateTime' == $mapping['ns_field_type_value'] ) ? 'selected' : '' ) . ">Custom Date Time</option>
			<option value='customboolean' " . ( ( isset( $mapping['ns_field_type_value'] ) && 'customboolean' == $mapping['ns_field_type_value'] ) ? 'selected' : '' ) . ">Custom Boolean</option>
			<option value='customstringfield' " . ( ( isset( $mapping['ns_field_type_value'] ) && 'customstringfield' == $mapping['ns_field_type_value'] ) ? 'selected' : '' ) . " >Custom String Field</option>
			<option value='customselectfield' " . ( ( isset( $mapping['ns_field_type_value'] ) && 'customselectfield' == $mapping['ns_field_type_value'] ) ? 'selected' : '' ) . " >Custom Select Field</option>
			<option value='custommultiselectfield' " . ( ( isset( $mapping['ns_field_type_value'] ) && 'custommultiselectfield' == $mapping['ns_field_type_value'] ) ? 'selected' : '' ) . " >Custom Multi-Select Field</option>
			<option value='customrecordref' " . ( ( isset( $mapping['ns_field_type_value'] ) && 'customrecordref' == $mapping['ns_field_type_value'] ) ? 'selected' : '' ) . ' >Custom Record Ref.</option>
			</select>
			</td>';
	}

	private function getWCFieldCompOperatorTemplate( $index, $mapping, $cm_wc_where_op ) {
		return "</td><td><span class='h6 required'>Where</span><br/>
			<select class='form-control input-sm ns-field-type' name='cm[" . $index . "][wc_where_op]'>
			<option value=''>Please Select</option>
			<option value='is' " . ( ( isset( $mapping['wc_where_op'] ) && 'is' == $mapping['wc_where_op'] ) ? 'selected' : ( ( 'is' == $cm_wc_where_op ) ? 'selected' : '' ) ) . ">IS</option>
			<option value='isnot' " . ( ( isset( $mapping['wc_where_op'] ) && 'isnot' == $mapping['wc_where_op'] ) ? 'selected' : ( ( 'isnot' == $cm_wc_where_op ) ? 'selected' : '' ) ) . " >IS NOT</option>
			<option value='contains' " . ( ( isset( $mapping['wc_where_op'] ) && 'contains' == $mapping['wc_where_op'] ) ? 'selected' : ( ( 'contains' == $cm_wc_where_op ) ? 'selected' : '' ) ) . ">CONTAINS</option>
			<option value='doesnotcontain' " . ( ( isset( $mapping['wc_where_op'] ) && 'doesnotcontain' == $mapping['wc_where_op'] ) ? 'selected' : ( ( 'doesnotcontain' == $cm_wc_where_op ) ? 'selected' : '' ) ) . '>DOES NOT CONTAINS</option>
			</select>
			</td>';
	}

	private function getWooCustomerFieldsTemplate( $index, $mapping, $cm_wc_field_key = '' ) {

		$template  = '<select class="wc_fieldkey" name="cm[' . $index . '][wc_field_key]">';
		$template .= "<option value='0'>Select a customer field</option>";
		foreach ( $this->cust_woo_customer_fields as $value ) {
			if ( ( '' != $cm_wc_field_key ? $cm_wc_field_key : $mapping['wc_field_key'] ) == $value ) {
				$template .= "<option value='" . $value . "' selected>" . $value . '</option>';
			} else {
				$template .= "<option value='" . $value . "'>" . $value . '</option>';
			}
		}
		$template .= '</select></div>';

		return $template;
	}

	private function getWooOrderFieldsTemplate( $index, $mapping, $cm_wc_field_key = '' ) {

		$template  = '<select class="wc_fieldkey" name="cm[' . $index . '][wc_field_key]">';
		$template .= "<option value='0'>Select a order field</option>";
		foreach ( $this->cust_woo_order_fields as $value ) {
			if ( ( '' != $cm_wc_field_key ? $cm_wc_field_key : $mapping['wc_field_key'] ) == $value ) {
				$template .= "<option value='" . $value . "' selected>" . $value . '</option>';
			} else {
				$template .= "<option value='" . $value . "'>" . $value . '</option>';
			}
		}
		$template .= '</select>';

		return $template;
	}

	private function getNetSuiteAttributeTypeTemplate( $index, $attr_type, $mapping ) {
		$template = '<td width="20%"><span class="required">Attribute type</span><br/><select name="cm[' . $index . '][ns_attr_type]" class="form-control input-sm attr_type"><option value="0">Please Select</option>';

		$template .= ( ( isset( $mapping['ns_attr_type'] ) && 1 == $mapping['ns_attr_type'] ) || ( 1 == $attr_type ) ) ? '<option value="1" selected>Default NetSuite Fields</option>' : '<option value="1">Default NetSuite Fields</option>';
		$template .= ( ( isset( $mapping['ns_attr_type'] ) && 2 == $mapping['ns_attr_type'] ) || ( 2 == $attr_type ) ) ? '<option value="2" selected>Custom NetSuite Fields</option></select></td>' : '<option value="2">Custom NetSuite Fields</option></select></td>';

		return $template;
	}

	private function getWooFieldTypeTemplate( $type, $index, $mapping, $cm_wc_field_key = '' ) {

		$template = '';

		if ( 2 == $type ) {
			$template .= '<td><span class="h6">Prefix to append - </span><br/>';
			$template .= '<input class="col-xs-4 input-sm" type="text" name="cm[' . $index . '][wc_field_value_prefix]" value="' . $mapping['wc_field_value_prefix'] . '"></td><td><span class="h6 required">Customer Meta Field</span><br/><input class="col-xs-8 input-sm" type="text" name="cm[' . $index . '][wc_field_key]" value="' . ( '' != $cm_wc_field_key ? $cm_wc_field_key : $mapping['wc_field_key'] ) . '">';
		} elseif ( 1 == $type ) {
			$template .= '<td><span class="h6">Prefix to append - </span><br/>';
			$template .= '<input class="col-xs-4 input-sm " type="text" name="cm[' . $index . '][wc_field_value_prefix]" value="' . $mapping['wc_field_value_prefix'] . '"></td><td><span class="h6 required">Customer Field</span><br/>';

			$template .= $this->getWooCustomerFieldsTemplate( $index, $mapping, $cm_wc_field_key );

			$template .= '</div>';
		} elseif ( 3 == $type ) {
			$template .= '<td><span class="h6">Prefix to append - </span><br/>';
			$template .= '<input class="col-xs-4 input-sm" type="text" name="cm[' . $index . '][wc_field_value_prefix]" value="' . $mapping['wc_field_value_prefix'] . '"></td><td><span class="h6 required">Order Field</span><br/>';
			$template .= $this->getWooOrderFieldsTemplate( $index, $mapping, $cm_wc_field_key );

			$template .= '</div>';
		} elseif ( 4 == $type ) {
			$template .= '<td><span class="h6">Prefix to append - </span><br/>';
			$template .= '<input class="col-xs-4 input-sm" type="text" name="cm[' . $index . '][wc_field_value_prefix]" value="' . $mapping['wc_field_value_prefix'] . '"></td><td><span class="h6 required">Order Meta Field</span><br/><input class="col-xs-8 input-sm" type="text" name="cm[' . $index . '][wc_field_key]" value="' . ( '' != $cm_wc_field_key ? $cm_wc_field_key : $mapping['wc_field_key'] ) . '"></div>';
		}
		$template .= '</td>';

		return $template;
	}
	/**
	 * Ajax function for getting conditional mapping template
	 */
	public function getConditionalMappingTemplate() {
		if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {
			$request          = $_POST;
			$return           = array();
			$return['status'] = 0;

			if ( ! empty( $request ) &&
				! empty( $request['operator'] ) &&
				! empty( $request['index'] ) ) {
				$template = $this->getTemplate( $request['type'], $request['operator'], $request['index'], array(), $request['tab'], $request['attr_type'], $request['cm_wc_field_key'], $request['cm_wc_field_value'], $request['cm_wc_where_op'] );
				if ( ! empty( $template ) ) {
					$return['status']   = 1;
					$return['template'] = $template;
				}
			}

		echo json_encode( $return );
		die;
		} else {
			die( 'Nonce Error' );
		}
	}

	public function add_meta_box() {
		tmns_hpos_add_meta_box(
		'woocommerce-netsuite-order-sync',
		__( 'NetSuite Sales Order Synchronization', 'woocommerce-netsuite-order-sync' ),
		array( $this, 'meta_box_sales_order' ),
		'shop_order',
		'side',
		'high'
		);
	}

	/**
	 * Show the meta box for shipment info on the order page
	 *
	 * Access public
	 */
	public function meta_box_sales_order( $post_or_order_object ) {
		global $post;
		if ( class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$post_id = $post_or_order_object->get_id();
		} else {
			$post_id = $post->ID;
		}

		$record_type = tm_ns_get_post_meta( $post_id, 'ns_record_type', true );
		if ( 'cashsale.nl' == $record_type ) {
			$sales_order_internalID = tm_ns_get_post_meta( $post_id, esc_attr( TMWNI_Settings::$ns_cash_sale_id ) );
		} else {
			$sales_order_internalID = tm_ns_get_post_meta( $post_id, esc_attr( TMWNI_Settings::$ns_order_id ) );
		}
		if ( empty( $sales_order_internalID ) ) {
			echo "<p><span class='replace-content'>This Sales Order for this order has not been synced with NetSuite.</span><br/> Use order action 'Sync to NetSuite' to sync it</p>";
		} else {
			echo "<p><span class='replace-content'>This Sales Order for this order has been successfully transferred to NetSuite. </span><br/><br/><b>NS Internal Order ID : </b>" . esc_attr( $sales_order_internalID );
			$invoice_internal_id = tm_ns_get_post_meta( $post_id, esc_attr( TMWNI_Settings::$ns_invoice_id ) );
			if ( ! empty( $invoice_internal_id ) ) {
				echo '<br><b>NS Invoice ID : </b>' . esc_attr( $invoice_internal_id );
			}
			$sales_order_externalID = tm_ns_get_post_meta( $post_id, esc_attr( TMWNI_Settings::$ns_external_order_id ) );
			if ( ! empty( $sales_order_externalID ) ) {
				echo '<br><b>NS Order ID : </b>' . esc_attr( $sales_order_externalID );
			}
		}

		/**
					* Order Custom meta box
					*
					* @since 1.0.0
		*/
					do_action( 'tm_ns_after_meta_box_sales_order', $post_or_order_object );
	}

	public function tmwniHanldeExportActions() {

		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ) ) ) {
			if ( isset( $_POST['Export'] ) && 'Export Settings' == $_POST['Export'] ) {
				self::downloadSettings();
			}
		} else {
			die( 'Nonce Error' );
		}
	}


	public function downloadSettings() {
		$all_setting = $this->getNetSuiteSaveSettings();
		if ( empty( $all_setting ) ) {
			die( 'All settings fields are blank' );
		}

		$fileName = 'netsuite_setting';

		$data = json_encode( $all_setting );
		file_put_contents( TMWNI_DIR . '/inc/' . $fileName, $data );
		$file = TMWNI_DIR . '/inc/' . $fileName;

		if ( ! file_exists( $file ) ) {
			die( "I'm sorry, the file doesn't seem to exist." );
		}

		$type  = filetype( $file );
		$today = gmdate( 'F j, Y, g:i a' );
		$time  = time();
		// Send file headers
		header( "Content-type: $type" );

		header( "Content-Disposition: attachment;filename={$fileName}.json" );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		set_time_limit( 0 );
		ob_clean();
		flush();

		readfile( $file );
	}

	public function ImportNetsuiteSettings() {

		if ( ! isset( $_POST['nonce'] ) || empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {
			wp_send_json_error( 'Nonce error' );
		}

	// Sanitize and validate the file input
		if ( isset($_FILES['importfile']['tmp_name'])) {
			$data = $_FILES;
			self::importJSONFile( $data );
			wp_send_json_success( 'File imported successfully' );
		} else {
			wp_send_json_error( 'Missing or invalid file' );

		}

	wp_die(); // Ensure script terminates gracefully
	}


	public function importJSONFile( $data ) {
		$fileData = json_decode( file_get_contents( $data['importfile']['tmp_name'] ) );
		foreach ( $fileData as $key => $settings ) {
			if ( 'tmwni_general_settings_options' == $settings->option_name ) {

				$settings_array = unserialize( $settings->option_value );

				if ( ! empty( $settings_array['ns_host'] ) && ! empty( $settings_array['ns_account'] ) && ! empty( $settings_array['ns_consumer_key'] ) && ! empty( $settings_array['ns_token_id'] ) && ! empty( $settings_array['ns_token_secret'] ) ) {
					$validate_credentials = $this->validateCredsJSONFile( $settings_array );
				} else {
					die(
					json_encode(
						array(
							'status' => 'false',
							'msg'    => 'All required credentials are not defined',
						)
					)
					);

				}
				if ( isset( $validate_credentials ) && ! empty( $validate_credentials ) && 0 == $validate_credentials['status'] ) {
					die(
					json_encode(
						array(
							'status' => 'false',
							'msg'    => 'Something wrong with API Credentials',
						)
					)
					);

				} else {
					foreach ( $fileData as $key => $settings ) {
						$option_key     = $settings->option_name;
						$settings_array = unserialize( $settings->option_value );
						update_option( $option_key, $settings_array, 'no' );
					}
					die(
					json_encode(
						array(
							'status' => 'true',
							'msg'    => 'Settings successfully imported!',
						)
					)
					);
				}
			}
		}
	}

	public function validateCredsJSONFile( $settings ) {
		$settings_array = array();

		$settings_array['endpoint']       = NS_ENDPOINT;
		$settings_array['host']           = $settings['ns_host'];
		$settings_array['account']        = $settings['ns_account'];
		$settings_array['role']           = 3;
		$settings_array['consumerKey']    = $settings['ns_consumer_key'];
		$settings_array['consumerSecret'] = $settings['ns_consumer_secret'];
		$settings_array['token']          = $settings['ns_token_id'];
		$settings_array['tokenSecret']    = $settings['ns_token_secret'];
		if ( isset( $settings['hma_algorithm_method'] ) && 'HMAC-SHA1' == $settings['hma_algorithm_method'] ) {
			$settings_array['signatureAlgorithm'] = 'sha1';
		} else {
			$settings_array['signatureAlgorithm'] = 'sha256';
		}

		$validate_credentials = $this->checkNSCreds( $settings_array );
		return $validate_credentials;
	}

	public function tmwniHanldeActions() {
		global $wpdb;
		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ) ) ) {
			if ( ! empty( $_POST['save_post'] ) ) {
				$tm_netsuite_settings = $_POST;

				unset( $tm_netsuite_settings['current_tab_id'] );
				unset( $tm_netsuite_settings['action'] );
				unset( $tm_netsuite_settings['save_post'] );

				if ( ! isset( $_POST['ns_order_shiping_line_item_enable'] ) ) {
					unset( $_POST['ns_order_shiping_line_item'] );
				}

				if ( ! isset( $_POST['ns_coupon_netsuite_sync'] ) ) {
					if ( isset( $_POST['ns_promo_custform_id'] ) ) {
						unset( $_POST['ns_promo_custform_id'] );
					}
					if ( isset( $_POST['ns_promo_discount_id'] ) ) {
						unset( $_POST['ns_promo_custform_id'] );
					}
				}

				if ( isset( $_POST['current_tab_id'] ) && 'inventory_settings' == $_POST['current_tab_id'] ) {
					if ( ! isset( $tm_netsuite_settings['enableInventorySync'] ) && ! isset( $tm_netsuite_settings['enablePriceSync'] ) ) {
						wp_clear_scheduled_hook( 'wp_tm_ns_manual_process_inventories_cron' );
						wp_clear_scheduled_hook( 'tm_ns_process_inventories' );
					}

					if ( ! isset( $tm_netsuite_settings['updateStockStatus'] ) ) {
						$tm_netsuite_settings['updateStockStatus'] = 'no';
					}
				}

				if ( isset( $_POST['current_tab_id'] ) ) {
					TMWNI_Settings::saveOption( $tm_netsuite_settings, sanitize_text_field( $_POST['current_tab_id'] ) );

				}

				$url = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';

				wp_safe_redirect( $url );
				exit();
			}
		} else {
			die( 'nonce_error' );
		}
	}

	public function getLogs() {
		global $wpdb;
		require_once TMWNI_DIR . '/inc/datatables.php';
		if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {
			$request = $_POST;
			$binding = array();
			if ( ! empty( $request ) ) {

				$datatables = new Datatables();

				$columns = array(
				array(
					'db'     => 'log.id as id',
					'dt'     => 0,
					'db_ref' => 'id',
				),
				array(
					'db'     => 'log.created_at as created_at',
					'dt'     => 1,
					'db_ref' => 'created_at',
				),
				array(
					'db'     => 'log.operation as operation',
					'dt'     => 2,
					'db_ref' => 'operation',
				),
				array(
					'db'     => 'log.status as status',
					'dt'     => 3,
					'db_ref' => 'status',
				),
				array(
					'db'     => 'log.notes as notes',
					'dt'     => 4,
					'db_ref' => 'notes',
				),
				array(
					'db'     => 'log.woo_object_id as woo_object_id',
					'dt'     => 5,
					'db_ref' => 'woo_object_id',
				),

				);

				$limit               = $datatables->limit( $request, $columns );
				$order               = $datatables->order( $request, $columns );
				$where               = $datatables->filter( $request, $columns, $binding );
				$table_name          = $wpdb->prefix . 'tm_woo_netsuite_logs';
				$wpdb->netsuite_logs = $wpdb->prefix . 'tm_woo_netsuite_logs';

				$limit_arr = explode( ' ', $limit );
				$order_arr = explode( ' ', $order );

				// By id
				if ( 'id' == $order_arr[2] && 'ASC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY id ASC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY id ASC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'id' == $order_arr[2] && 'DESC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY id DESC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY id DESC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}

				// By Created Date
				if ( 'created_at' == $order_arr[2] && 'ASC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY created_at ASC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY created_at ASC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'created_at' == $order_arr[2] && 'DESC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY created_at DESC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY created_at DESC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}

				// By Operation Condition
				if ( 'operation' == $order_arr[2] && 'ASC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY operation ASC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY operation ASC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'operation' == $order_arr[2] && 'DESC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY operation DESC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY operation DESC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}

				// By Status
				if ( 'status' == $order_arr[2] && 'ASC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY status ASC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY status ASC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'status' == $order_arr[2] && 'DESC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY status DESC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY status DESC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}

				// By woo_object_id
				if ( 'woo_object_id' == $order_arr[2] && 'ASC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY woo_object_id ASC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY woo_object_id ASC limit %d, %d", $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					}
				}
				if ( 'woo_object_id' == $order_arr[2] && 'DESC' == $order_arr[3] ) {
					if ( ! empty( $where ) ) {
						$where_arr = explode( ' ', $where );
						$where     = str_replace( "'", '', $where_arr[4] );
						$data      = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log WHERE (log.status  LIKE %s OR log.notes  LIKE %s) ORDER BY woo_object_id DESC limit %d, %d", $where, $where, $limit_arr[1], $limit_arr[2] ), ARRAY_A );
					} else {
						$data = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT SQL_CALC_FOUND_ROWS log.id as id,log.created_at as created_at,log.operation as operation,log.status as status,log.notes as notes,log.woo
							_object_id as woo_object_id FROM {$wpdb->netsuite_logs} as log  ORDER BY woo_object_id DESC limit %d, %d",
							$limit_arr[1],
							$limit_arr[2]
						),
						ARRAY_A
						);
					}
				}

				$data_filter     = $wpdb->get_results( 'SELECT FOUND_ROWS() as filtered_rows' );
				$recordsFiltered = $data_filter[0]->filtered_rows;

				$recordsTotal = $recordsFiltered;

				if ( empty( $data ) ) {
					$unprocessed_rows = array();
					if ( $recordsTotal > 0 ) {
						$unprocessed_rows = $datatables->data_output( $columns, (array) $data );
					}
				} else {
					// total filtered records
					$order_link = 0;

					$uploads_dir        = wp_upload_dir();
					$logs_plugin_folder = $uploads_dir['basedir'] . '/TM-NetSuite';
					$plugin_folder      = $uploads_dir['baseurl'] . '/TM-NetSuite';
					foreach ( $data as $key => $record ) {
						$rows          = array();
						$rows[]        = $record['id'];
						$rows[]        = $record['created_at'];
						$rows[]        = $record['operation'];
						$rows[]        = $record['status'];
						$rows[]        = $record['notes'];
						$rows[]        = $record['woo_object_id'];
						$operation     = $record['operation'];
						$woo_object_id = $record['woo_object_id'];

						$request_file_path  = "{$logs_plugin_folder}/{$operation}-request-{$woo_object_id}-request.xml";
						$response_file_path = "{$logs_plugin_folder}/{$operation}-request-{$woo_object_id}-response.xml";

						$request_file_path_url  = "{$plugin_folder}/{$operation}-request-{$woo_object_id}-request.xml";
						$response_file_path_url = "{$plugin_folder}/{$operation}-request-{$woo_object_id}-response.xml";

						$request_link  = '';
						$response_link = '';

						if ( is_file( $request_file_path ) ) {
							$request_link = '<div class="manually_order_sync_btn">
						<a target="_blank" href="' . $request_file_path_url . '" class="btn btn-success" download>Download</a>';
						}

						if ( is_file( $response_file_path ) ) {
							$response_link = '<div>
						<a target="_blank" href="' . $response_file_path_url . '" class="btn btn-success" download>Download</a>';
						}

						$rows[] = $request_link;
						$rows[] = $response_link;

						$unprocessed_rows[] = $rows;
					}
				}

				// json to be returned
				// echo json_encode(array(
				// 'draw' => 7,
				// 'recordsTotal' => 7,
				// 'recordsFiltered' => 7,
				// 'data' => $unprocessed_rows,
				// ));
				echo json_encode(
				array(
					'draw'            => intval( $request['draw'] ),
					'recordsTotal'    => intval( $recordsTotal ),
					'recordsFiltered' => intval( $recordsFiltered ),
					'data'            => $unprocessed_rows,
				)
				);
				die;
			}
			die;
		} else {
			echo json_encode(
			array(
				'draw'            => 0,
				'recordsTotal'    => 0,
				'recordsFiltered' => 0,
				'data'            => 0,
			)
			);
			die;

		}
	}

	public function getServerTime() {
		echo esc_html( gmdate( 'Y-m-d h:i' ) );
		die;
	}
	// Add setting page link to menu
	public function TMWNIAdminMenu() {
		$menu = add_menu_page( 'TM NetSuite', 'TM NetSuite', 'manage_options', 'tmwni', array( $this, 'TMWNISettingsTabs' ) );
		add_action( 'admin_print_styles-' . $menu, array( $this, 'tm_woo_ns_css' ) );

		add_action( 'admin_print_scripts-' . $menu, array( $this, 'tm_woo_ns_js' ) );
	}

	public function TMWNIAdminScript() {
		wp_enqueue_script( 'tmwni-common-js', TMWNI_URL . '/assets/js/common.js', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );

		wp_localize_script(
		'tmwni-common-js',
		'tmwni_common_js',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'security_nonce' ),
		)
		);
	}

	public function tmwniDeleteValue() {
		if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {
			if ( ! empty( $_POST['user_id'] ) ) {
				delete_user_meta( sanitize_text_field( $_POST['user_id'] ), TMWNI_Settings::$ns_customer_id );
			}
		} else {
			die( 'Nonce Error' );
		}
	}

	public function TMWNISettingsTabs() {

		global $TMWNI_OPTIONS;

		if ( TMWNI_Settings::areCredentialsDefined() ) {
			$validate_credentials = $this->validateCredentials();
		}

		$intented_tab = ( ! empty( $_GET['tab'] ) ) ? sanitize_text_field( $_GET['tab'] ) : TMWNI_Settings::$default_tab;

		switch ( $intented_tab ) {
			case 'customer_settings':
			$current_tab_id = 'customer_settings';
				break;
			case 'general_settings':
			$current_tab_id = 'general_settings';
				break;
			case 'help':
			$current_tab_id = 'help';
				break;
			case 'import_export_settings':
			$current_tab_id = 'import_export_settings';
				break;
			case 'inventory_settings':
			$current_tab_id = 'inventory_settings';
				break;
			case 'logs':
			$current_tab_id = 'logs';
				break;
			case 'order_settings':
			$current_tab_id = 'order_settings';
				break;
			default:
			$current_tab_id = TMWNI_Settings::$default_tab;
		}
		if ( strpos( $current_tab_id, 'settings' ) ) {
			if ( 'inventory_settings' == $current_tab_id ) {
				if ( isset( $validate_credentials['status'] ) && 1 == $validate_credentials['status'] ) {
					$this->getNetsuiteInventoryLocationPriceSettings();
				}
			}

			if ( 'order_settings' == $current_tab_id ) {
				if ( isset( $validate_credentials['status'] ) && 1 == $validate_credentials['status'] ) {
					if ( isset( $TMWNI_OPTIONS['ns_coupon_netsuite_sync'] ) && ! empty( $TMWNI_OPTIONS['ns_coupon_netsuite_sync'] ) ) {
						$this->getNetsuitePromoSettings();
					}
				}

				$order_general_settings     = TMWNI_Settings::getTabSettings( $current_tab_id . '_general_settings' );
				$order_fulfillment_settings = TMWNI_Settings::getTabSettings( $current_tab_id . '_fulfillment_settings' );
				$order_refund_settings      = TMWNI_Settings::getTabSettings( $current_tab_id . '_refund_settings' );
				$order_line_item_settings   = TMWNI_Settings::getTabSettings( $current_tab_id . '_line_item_settings' );

				if ( ! empty( $order_general_settings ) && ! empty( $order_fulfillment_settings ) && ! empty( $order_refund_settings ) ) {
					$options = array_merge( $order_general_settings, $order_refund_settings, $order_fulfillment_settings );
				} elseif ( ! empty( $order_general_settings ) && ! empty( $order_refund_settings ) ) {
					$options = array_merge( $order_general_settings, $order_refund_settings );
				} elseif ( ! empty( $order_general_settings ) && ! empty( $order_fulfillment_settings ) ) {
					$options = array_merge( $order_general_settings, $order_fulfillment_settings );

				} elseif ( ! empty( $order_general_settings ) ) {
					$options = $order_general_settings;
				} else {
					$options = TMWNI_Settings::getTabSettings( $current_tab_id );
				}
			} else {
				$options = TMWNI_Settings::getTabSettings( $current_tab_id );
			}

			if ( ! empty( $order_line_item_settings ) && !empty($options)) {
				$options = array_merge( $options, $order_line_item_settings );
			}

			$option_tag = '';

			if ( 'order_settings' == $current_tab_id ) {
				$woo_order_statuses = wc_get_order_statuses();
				$option_tag         = 'order_cm_options';

			}

			if ( 'customer_settings' == $current_tab_id ) {
				$option_tag = 'customer_cm_options';
			}

			$cm_options = get_option( $option_tag, null );

			if ( ! empty( $cm_options ) ) {
				foreach ( $cm_options as $key => $mapping ) {

					$index = $key + 1;
					if ( ! empty( $mapping['operator'] ) ) {
						if ( ! empty( self::$prefinedCondMapping ) ) {
							foreach ( self::$prefinedCondMapping as $pre_mapping_key => $pre_mapping ) {
								if ( $mapping['operator'] == $pre_mapping['operator'] && $mapping['type'] == $pre_mapping['type'] && $mapping['ns_field_key'] == $pre_mapping['ns_field_key'] ) { // check for required mapping
									$required_key_present[]         = $pre_mapping_key;
									$cm_options[ $key ]['required'] = 1;
								}
							}
						}

						if ( isset( $mapping['type'] ) ) {
							$cm_options[ $key ]['template'] = $this->getTemplate( $mapping['type'], $mapping['operator'], $index, $mapping, $current_tab_id );
						} else {
							$cm_options[ $key ]['template'] = $this->getTemplate( 2, $mapping['operator'], $index, $mapping, $current_tab_id );
						}
					} else {
						unset( $cm_options[ $key ] );
					}
				}
			}

			if ( 'inventory_settings' == $current_tab_id ) {
				$inventory_sync_frequencies = TMWNI_Settings::$inventory_sync_frequency;
			}
			if ( 'general_settings' == $current_tab_id ) {
				$hma_methods = TMWNI_Settings::$hma_method;
			}
		}
		// GET TAB CONTENT
		$tab_content_file = TMWNI_DIR . 'inc/admin/tab-templates/admin-tab-template-' . $current_tab_id . '.php';
		// pr($tab_content_file); die;
		if ( file_exists( $tab_content_file ) ) {
			ob_start();
			include $tab_content_file;
			$tab_content = ob_get_contents();
			ob_end_clean();
		} else {
			$tab_content = 'No Content :)';
		}

		require_once TMWNI_DIR . 'inc/admin/admin-page.php';
		return;
	}

	public function tm_woo_ns_css() {
		if ( empty( $_GET['tab'] ) ) {
			$current_tab_id = TMWNI_Settings::$default_tab;
		} else {
			$current_tab_id = sanitize_text_field( $_GET['tab'] );
		}
		// if($current_tab_id=='order_settings'){
		wp_enqueue_style( 'tmwni_admin_bootstrap_settings_css', TMWNI_URL . '/assets/css/bootstrap.min.css', false, '1.1', 'all' );
		// }
		wp_enqueue_style( 'tmwni_admin_settings_css', TMWNI_URL . 'assets/css/admin-settings.css', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );

		// Select2 CSS
		wp_enqueue_style( 'tmwni_admin_settings_select2_css', TMWNI_URL . 'assets/css/select2.min.css', false, '1.1', 'all' );

		// Notify/Prettify CSS
		wp_enqueue_style( 'tmwni_admin_settings_notifycss', TMWNI_URL . 'assets/css/notify.css', false, '1.1', 'all' );
		wp_enqueue_style( 'tmwni_admin_settings_prettifycss', TMWNI_URL . 'assets/css/prettify.css', false, '1.1', 'all' );

		if ( 'logs' == $current_tab_id || 'dashboard' == $current_tab_id ) {
			wp_enqueue_style( 'tmwni-admin-log-css', TMWNI_URL . '/assets/css/jquery.dataTables.min.css', false, '1.1', 'all' );
		}
	}

	public function tm_woo_ns_js() {
		if ( empty( $_GET['tab'] ) ) {
			$current_tab_id = TMWNI_Settings::$default_tab;
		} else {
			$current_tab_id = sanitize_text_field( $_GET['tab'] );
		}

		wp_enqueue_script( 'tmwni-bootstrap-js', TMWNI_URL . '/assets/js/bootstrap3.3.7.min.js', array( 'jquery' ), '1.0', 'all' );

		// Notify/Prettify JS
		wp_enqueue_script( 'tmwni-admin-settings-notifyjs', TMWNI_URL . '/assets/js/notify.js', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );

		wp_enqueue_script( 'tmwni-admin-settings-prettifyjs', TMWNI_URL . '/assets/js/prettify.js', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );

		wp_enqueue_script( 'tmwni-common-js', TMWNI_URL . '/assets/js/common.js', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );

		if ( strpos( $current_tab_id, 'settings' ) ) {

			wp_enqueue_script( 'tmwni-admin-settings-js', TMWNI_URL . '/assets/js/admin-settings.js', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );

			// Select2 JS
			wp_enqueue_script( 'tmwni-admin-settings-select2-js', TMWNI_URL . '/assets/js/select2.min.js', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );

			wp_localize_script(
				'tmwni-admin-settings-js',
				'tmwni_admin_settings_js',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'security_nonce' ),
				)
			);

		}
		if ( 'logs' == $current_tab_id ) {

			wp_enqueue_script( 'tmwni-jquery-dataTables-js', TMWNI_URL . '/assets/js/jquery.dataTables.min.js', false, '1.1', 'all' );
			wp_enqueue_script( 'tmwni-admin-log', TMWNI_URL . '/assets/js/admin-logs.js', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );
				// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
			wp_localize_script(
				'tmwni-admin-log',
				'tmwni_admin_log',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'security_nonce' ),
				)
			);
		}

		if ( 'dashboard' == $current_tab_id ) {

			wp_enqueue_script( 'tmwni-jquery-dataTables-js', TMWNI_URL . '/assets/js/jquery.dataTables.min.js', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );
			wp_enqueue_script( 'tmwni-admin-dashboard', TMWNI_URL . '/assets/js/admin-dashboard.js', false, WC_TM_NETSUITE_INTEGRATION_INIT_VERSION, 'all' );
				// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
			wp_localize_script(
				'tmwni-admin-dashboard',
				'tmwni_admin_dashboard',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'security_nonce' ),
				)
			);
		}
	}
	public function clearAllDashboardLogs() {
		if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {
			if ( ! empty( $_POST['form_data'] ) && 'clearDashboardLogs' == $_POST['form_data'] ) {
				global $wpdb;
				$result = $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'tm_woo_netsuite_auto_sync_order_status' );
				if ( empty( $result ) ) {
					exit( 'success' );
				} else {
					exit( 'failure' );
				}
			}
		} else {
			die( 'Nonce Error' );
		}
	}
	public function clearAllApiLogs() {
		if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'security_nonce' ) ) {
			if ( ! empty( $_POST['form_data'] ) && 'clearLogs' == $_POST['form_data'] ) {
				global $wpdb;
				$table  = $wpdb->prefix . 'tm_woo_netsuite_logs';
				$result = $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'tm_woo_netsuite_logs' );
				if ( empty( $result ) ) {
					exit( 'success' );
				} else {
					exit( 'failure' );
				}
			}
		} else {
			die( 'Nonce Error' );
		}
	}
}


TMWNI_Admin_Loader::getInstance();

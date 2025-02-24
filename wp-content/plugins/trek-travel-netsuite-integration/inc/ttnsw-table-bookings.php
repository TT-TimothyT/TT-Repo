<?php
/**
 * Generate Bookings table preview in the admin panel.
 * Extends WP_List_Table.
 *
 * @link https://www.webtrickshome.com/forum/how-to-add-custom-data-table-in-wordpress-dashboard
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

define( 'TT_NS_BOOKINGS_PATH', 'app/accounting/transactions/salesord.nl' );
define( 'TT_NS_CUST_REC_ENTRY_PATH', 'app/common/custom/custrecordentry.nl' );
define( 'TT_NS_GUEST_PATH', 'app/common/entity/custjob.nl' );
define( 'TT_NS_SERVICE_ITEM_PATH', 'app/common/item/item.nl' );

if ( ! defined( 'DX_DEV' ) ) {
	// Live NetSuite
	define( 'TT_NS_BASE_URL', 'https://661527.app.netsuite.com/' );
} else {
	// Sandbox NetSuite
	define( 'TT_NS_BASE_URL', 'https://661527-sb2.app.netsuite.com/' );
}

class Guest_Bookings_Table extends WP_List_Table {

	public function __construct( $args = array() ) {

		if ( empty( $args ) ) {
			$args = array(
				'singular' => 'Booking',
				'plural'   => 'Bookings',
				'ajax'     => false
			);
		}

		parent::__construct( $args );	
	}

	/**
	 * Get the where conditions for the query
	 *
	 * @return array Array of where conditions and values
	 */
	private static function get_where_conditions() {
		global $wpdb;
		$where_conditions = array();
		$where_values    = array();

		if ( ! isset( $_REQUEST['s'] ) || empty( $_REQUEST['s'] ) ) {
			return array( $where_conditions, $where_values );
		}

		$search_request = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		$search_column  = isset( $_REQUEST['search_column'] ) ? sanitize_text_field( $_REQUEST['search_column'] ) : 'all';

		// Define the allowed search columns
		$allowed_columns = array(
			'ns_trip_booking_id',
			'order_id',
			'guestRegistrationId',
			'netsuite_guest_registration_id',
			'guest_email_address',
			'guest_first_name',
			'guest_last_name',
			'trip_code'
		);

		if ( 'all' === $search_column ) {
			$search_conditions = array();
			foreach ( $allowed_columns as $column ) {
				$search_conditions[] = $column . ' LIKE %s';
				$where_values[]     = '%' . $wpdb->esc_like( $search_request ) . '%';
			}
			$where_conditions[] = '(' . implode( ' OR ', $search_conditions ) . ')';
		} elseif ( in_array( $search_column, $allowed_columns ) ) {
			$where_conditions[] = $search_column . ' LIKE %s';
			$where_values[]     = '%' . $wpdb->esc_like( $search_request ) . '%';
		}

		return array( $where_conditions, $where_values );
	}

	/**
	 * Get paginated and filtered records
	 *
	 * @param int $per_page Number of records per page
	 * @param int $page_number Current page number
	 * @return array
	 */
	private static function get_records( $per_page = 20, $page_number = 1 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'guest_bookings';

		// Get where conditions
		list( $where_conditions, $where_values ) = self::get_where_conditions();

		// Build base query
		$sql = "SELECT * FROM {$table_name}";

		// Add where conditions
		if ( ! empty( $where_conditions ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
		} else {
			// Load the first page faster by smart date limiting
			if ( 1 === (int) $page_number ) {
				$order = ( isset( $_REQUEST['order'] ) && strtoupper( $_REQUEST['order'] ) === 'ASC' ) ? 'ASC' : 'DESC';
				if ( 'ASC' === $order ) {
					$earliest_date = $wpdb->get_var( "SELECT MIN(created_at) FROM {$table_name}" );
					if ( $earliest_date ) {
						$sql .= $wpdb->prepare( " WHERE created_at <= DATE_ADD(%s, INTERVAL 30 DAY)", $earliest_date );
					}
				} else {
					// Get the latest date
					$latest_date = $wpdb->get_var( "SELECT MAX(created_at) FROM {$table_name}" );
					if ( $latest_date ) {
						$sql .= $wpdb->prepare(
							" WHERE created_at >= DATE_SUB(%s, INTERVAL 30 DAY)",
							$latest_date
						);
					}
				}
			}
		}

		// Handle ordering
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$allowed_orderby = array( 'id', 'ns_trip_booking_id', 'order_id', 'created_at', 'modified_at' );
			$orderby = in_array( $_REQUEST['orderby'], $allowed_orderby ) ? $_REQUEST['orderby'] : 'id';
			$order = ( isset( $_REQUEST['order'] ) && strtoupper( $_REQUEST['order'] ) === 'DESC' ) ? 'DESC' : 'ASC';
			$sql .= ' ORDER BY ' . esc_sql( $orderby ) . ' ' . esc_sql( $order );
		} else {
			$sql .= ' ORDER BY id DESC';
		}

		// Add pagination
		$sql .= ' LIMIT %d OFFSET %d';
		$where_values[] = $per_page;
		$where_values[] = ( $page_number - 1 ) * $per_page;

		// Prepare and execute query
		$sql = $wpdb->prepare( $sql, $where_values );

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		if ( $wpdb->last_error ) {
			add_settings_error( 'ttnsw-admin-notice', 'ttnsw_logs_error', $wpdb->last_error, 'error' );
		}

		return $result;
	}

	/**
	 * Get filtered count of records
	 *
	 * @return int Total number of records
	 */
	private static function get_records_count() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'guest_bookings';
		
		 // Build cache key
		 $cache_key = 'ttnsw_bookings_count';
		 if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
			 $search_request = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
			 $search_column = isset( $_REQUEST['search_column'] ) ? sanitize_text_field( $_REQUEST['search_column'] ) : 'all';
			 $cache_key .= '_' . $search_column . '_' . md5( $search_request );
		 }
	 
		 // Try to get cached results
		 $cached_count = get_transient( $cache_key );
		 if ( false !== $cached_count ) {
			 return (int) $cached_count;
		 }
	 
		 // Get where conditions
		 list( $where_conditions, $where_values ) = self::get_where_conditions();
	 
		 // Build the query
		 $sql = "SELECT COUNT(id) FROM {$table_name}";
		 
		 if ( ! empty( $where_conditions ) ) {
			 $sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
		 }
	 
		 if ( ! empty( $where_values ) ) {
			 $sql = $wpdb->prepare( $sql, $where_values );
		 }
	 
		 $total = $wpdb->get_var( $sql );
		 
		 // Cache the result for 5 minutes
		 set_transient( $cache_key, $total, 300 );
	 
		 return (int) $total;
	}

	public static function record_count($data = null) {
		return self::get_records_count();
	}

	public function get_column_groups() {
		return array(
			'basic' => array(
				'title' => __('Basic Info', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'id', 
					'ns_trip_booking_id', 
					'order_id', 
					'web_order'
				)
			),
			'trip' => array(
				'title' => __('Trip Info', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'product_id',
					'trip_code',
					'trip_name', 
					'trip_type',
					'trip_total_amount',
					'trip_number_of_guests',
					'trip_start_date',
					'trip_end_date'
				)
			),
			'guest' => array(
				'title' => __('Guest Info', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'guestRegistrationId',
					'netsuite_guest_registration_id',
					'user_id',
					'guest_email_address',
					'guest_first_name',
					'guest_last_name',
					'guest_phone_number',
					'guest_gender',
					'guest_date_of_birth',
					'guest_is_primary'
				)
			),
			'address' => array(
				'title' => __('Address Info', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'shipping_address_1',
					'shipping_address_2', 
					'shipping_address_city',
					'shipping_address_state',
					'shipping_address_country',
					'shipping_address_zipcode'
				)
			),
			'bike' => array(
				'title' => __('Bike Info', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'bike_id',
					'bike_type_id',
					'bike_size',
					'bike_selection',
					'rider_height',
					'rider_level',
					'activity_level',
					'bike_comments',
					'saddle_height',
					'saddle_bar_reach_from_saddle',
					'saddle_bar_height_from_wheel_center'
				)
			),
			'equipment' => array(
				'title' => __('Equipment & Gear', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'helmet_selection',
					'pedal_selection',
					'jersey_style',
					'tt_jersey_size',
					'tshirt_size',
					'race_fit_jersey_size',
					'shorts_bib_size'
				)
			),
			'medical' => array(
				'title' => __('Medical Info', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'medical_conditions',
					'medications',
					'allergies',
					'dietary_restrictions'
				)
			),
			'emergency' => array(
				'title' => __('Emergency Contact', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'emergency_contact_first_name',
					'emergency_contact_last_name',
					'emergency_contact_phone',
					'emergency_contact_relationship'
				)
			),
			'passport' => array(
				'title' => __('Passport Info', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'passport_number',
					'passport_issue_date',
					'passport_expiration_date',
					'passport_place_of_issue',
					'passport_country_of_issue',
					'place_of_birth'
				)
			),
			'status' => array(
				'title' => __('Status & Documents', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'releaseFormId',
					'waiver_signed',
					'is_guestreg_cancelled',
					'ns_booking_status',
					'ns_booking_response',
					'wc_order_meta',
				)
			),
			'meta' => array(
				'title' => __('Additional Data', 'trek-travel-netsuite-integration'),
				'columns' => array(
					'created_at',
					'modified_at'
				)
			)
		);
	}

	public function get_columns() {
		$columns = array();
		$all_columns = array(
			'id' => __('ID', 'trek-travel-netsuite-integration'),
			'ns_trip_booking_id' => __('Booking ID', 'trek-travel-netsuite-integration'),
			'order_id' => __('WC Order ID', 'trek-travel-netsuite-integration'),
			'web_order' => __('Web Order', 'trek-travel-netsuite-integration'),
			'product_id' => __('Product', 'trek-travel-netsuite-integration'),
			'trip_code' => __('Trip Code/SKU', 'trek-travel-netsuite-integration'),
			'trip_type' => __('Trip Type', 'trek-travel-netsuite-integration'),
			'guestRegistrationId' => __('Guest Reg ID', 'trek-travel-netsuite-integration'),
			'netsuite_guest_registration_id' => __('NS User ID', 'trek-travel-netsuite-integration'),
			'user_id' => __('WP User ID', 'trek-travel-netsuite-integration'),
			'guest_email_address' => __('Email', 'trek-travel-netsuite-integration'),
			'guest_first_name' => __('First Name', 'trek-travel-netsuite-integration'),
			'guest_last_name' => __('Last Name', 'trek-travel-netsuite-integration'),
			'guest_is_primary' => __('Primary Guest', 'trek-travel-netsuite-integration'),
			'trip_number_of_guests' => __('Guests Count', 'trek-travel-netsuite-integration'),
			// 'bike' => __( 'Bike', 'trek-travel-netsuite-integration'), // This is an experimental custom column that does not exist in the table.
			'medical_conditions' => __('Medical Conditions', 'trek-travel-netsuite-integration'),
			'medications' => __('Medications', 'trek-travel-netsuite-integration'),
			'allergies' => __('Allergies', 'trek-travel-netsuite-integration'),
			'dietary_restrictions' => __('Dietary Restrictions', 'trek-travel-netsuite-integration'),
			'bike_comments' => __('Bike Comments', 'trek-travel-netsuite-integration'),
			'wc_order_meta' => __('WC Order Meta', 'trek-travel-netsuite-integration'),
			'ns_booking_response' => __('NS Booking Response', 'trek-travel-netsuite-integration'),
			'releaseFormId' => __('Release Form', 'trek-travel-netsuite-integration'),
			'waiver_signed' => __('Waiver Signed', 'trek-travel-netsuite-integration'),
			'created_at' => __('Created At', 'trek-travel-netsuite-integration'),
			'modified_at' => __('Modified At', 'trek-travel-netsuite-integration'),
			'trip_name' => __('Trip Name', 'trek-travel-netsuite-integration'),
			'trip_total_amount' => __('Total Amount', 'trek-travel-netsuite-integration'),
			'trip_start_date' => __('Start Date', 'trek-travel-netsuite-integration'),
			'trip_end_date' => __('End Date', 'trek-travel-netsuite-integration'),
			'guest_phone_number' => __('Phone', 'trek-travel-netsuite-integration'),
			'guest_gender' => __('Gender', 'trek-travel-netsuite-integration'),
			'guest_date_of_birth' => __('Date of Birth', 'trek-travel-netsuite-integration'),
			'shipping_address_1' => __('Address 1', 'trek-travel-netsuite-integration'),
			'shipping_address_2' => __('Address 2', 'trek-travel-netsuite-integration'),
			'shipping_address_city' => __('City', 'trek-travel-netsuite-integration'),
			'shipping_address_state' => __('State', 'trek-travel-netsuite-integration'),
			'shipping_address_country' => __('Country', 'trek-travel-netsuite-integration'),
			'shipping_address_zipcode' => __('ZIP', 'trek-travel-netsuite-integration'),
			'bike_id' => __('Bike ID', 'trek-travel-netsuite-integration'),
			'bike_type_id' => __('Bike Type', 'trek-travel-netsuite-integration'),
			'bike_size' => __('Bike Size', 'trek-travel-netsuite-integration'),
			'bike_selection' => __('Bike Selection', 'trek-travel-netsuite-integration'),
			'rider_height' => __('Rider Height', 'trek-travel-netsuite-integration'),
			'rider_level' => __('Rider Level', 'trek-travel-netsuite-integration'),
			'activity_level' => __('Activity Level', 'trek-travel-netsuite-integration'),
			'saddle_height' => __('Saddle Height', 'trek-travel-netsuite-integration'),
			'saddle_bar_reach_from_saddle' => __('Bar Reach', 'trek-travel-netsuite-integration'),
			'saddle_bar_height_from_wheel_center' => __('Bar Height', 'trek-travel-netsuite-integration'),
			'helmet_selection' => __('Helmet', 'trek-travel-netsuite-integration'),
			'pedal_selection' => __('Pedals', 'trek-travel-netsuite-integration'),
			'jersey_style' => __('Jersey Style', 'trek-travel-netsuite-integration'),
			'tt_jersey_size' => __('Jersey Size', 'trek-travel-netsuite-integration'),
			'tshirt_size' => __('T-Shirt Size', 'trek-travel-netsuite-integration'),
			'race_fit_jersey_size' => __('Race Fit Size', 'trek-travel-netsuite-integration'),
			'shorts_bib_size' => __('Shorts/Bib Size', 'trek-travel-netsuite-integration'),
			'emergency_contact_first_name' => __('EC First Name', 'trek-travel-netsuite-integration'),
			'emergency_contact_last_name' => __('EC Last Name', 'trek-travel-netsuite-integration'),
			'emergency_contact_phone' => __('EC Phone', 'trek-travel-netsuite-integration'),
			'emergency_contact_relationship' => __('EC Relationship', 'trek-travel-netsuite-integration'),
			'is_guestreg_cancelled' => __('Cancelled', 'trek-travel-netsuite-integration'),
			'ns_booking_status' => __('Booking Status', 'trek-travel-netsuite-integration'),
			'passport_number' => __('Passport Number', 'trek-travel-netsuite-integration'),
			'passport_issue_date' => __('Issue Date', 'trek-travel-netsuite-integration'),
			'passport_expiration_date' => __('Expiration Date', 'trek-travel-netsuite-integration'), 
			'passport_place_of_issue' => __('Place of Issue', 'trek-travel-netsuite-integration'),
			'passport_country_of_issue' => __('Country of Issue', 'trek-travel-netsuite-integration'),
			'place_of_birth' => __('Place of Birth', 'trek-travel-netsuite-integration')
		);

		// Get visible groups
		$groups = $this->get_column_groups();
		foreach($groups as $group_key => $group) {
			$visible = get_user_option('bookings_group_' . $group_key . '_visible') !== 'false';
			if($visible) {
				foreach($group['columns'] as $column) {
					if(isset($all_columns[$column])) {
						$columns[$column] = $all_columns[$column];
					}
				}
			}
		}

		return $columns;
	}

	public function get_hidden_columns() {
		$meta_key = 'managenetsuitewc_page_' . $_REQUEST['page'] . 'columnshidden';
		// Retrieves preferences related to hidden columns from usermeta column of database.
		$hidden = ( is_array( get_user_meta( get_current_user_id(), $meta_key, true ) ) ) ? get_user_meta( get_current_user_id(), $meta_key, true ) : array();
		return $hidden;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'                 => array( 'id',true ),
			'ns_trip_booking_id' => array( 'ns_trip_booking_id',true ),
			'order_id'           => array( 'order_id',true ),
			'created_at'         => array( 'created_at',true ),
			'modified_at'        => array( 'modified_at',true ),
		);

		return $sortable_columns;
	}

	// New method to wrap long content
	private function wrap_long_content( $content, $max_length = 100 ) {
		if (empty($content)) {
			return '';
		}

		// Check if content is JSON
		$is_json = is_string($content) && is_array(json_decode($content, true)) && (json_last_error() == JSON_ERROR_NONE);
		
		if ($is_json) {
			// For JSON content, encode to base64 to preserve structure
			$encoded = base64_encode($content);
			$preview = substr(wp_strip_all_tags($content), 0, $max_length);
			
			return '<div class="truncated-content">' . 
				$preview . '...' .
				'<span class="expand-modal dashicons dashicons-editor-expand" data-is-json="1" data-full-content="' . esc_attr($encoded) . '"></span>' .
				'</div>';
		}

		if (strlen($content) > $max_length) {
			// For regular content use normal truncation
			return '<div class="truncated-content">' . 
				substr($content, 0, $max_length) . '...' .
				'<span class="expand-modal dashicons dashicons-editor-expand" data-is-json="0" data-full-content="' . esc_attr($content) . '"></span>' .
				'</div>';
		}

		return $content;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'guest_email_address':
			case 'guest_first_name':
			case 'guest_last_name':
			case 'user_id':
			case 'trip_number_of_guests':
			case 'created_at':
			case 'modified_at':
			case 'trip_name':
			case 'trip_total_amount':
			case 'trip_start_date':
			case 'trip_end_date':
			case 'guest_phone_number':
			case 'guest_date_of_birth':
			case 'shipping_address_1':
			case 'shipping_address_2':
			case 'shipping_address_city':
			case 'shipping_address_state':
			case 'shipping_address_country':
			case 'shipping_address_zipcode':
			case 'bike_id':
			case 'bike_type_id':
			case 'bike_size':
			case 'bike_selection':
			case 'rider_height':
			case 'rider_level':
			case 'activity_level':
			case 'saddle_height':
			case 'saddle_bar_reach_from_saddle':
			case 'saddle_bar_height_from_wheel_center':
			case 'helmet_selection':
			case 'pedal_selection':
			case 'jersey_style':
			case 'tt_jersey_size':
			case 'tshirt_size':
			case 'race_fit_jersey_size':
			case 'shorts_bib_size':
			case 'emergency_contact_first_name':
			case 'emergency_contact_last_name':
			case 'emergency_contact_phone':
			case 'emergency_contact_relationship':
			case 'ns_booking_status':
			case 'passport_number':
			case 'passport_issue_date':
			case 'passport_expiration_date':
			case 'passport_place_of_issue':
			case 'passport_country_of_issue':
			case 'place_of_birth':
				// Display raw data.
				return esc_html($item[ $column_name ]);
			case 'trip_code':
				$trip_info     = tt_get_trip_pid_sku_by_orderId( $item[ 'order_id' ] );
				$trip_id       = $trip_info['ns_trip_Id'];
				$trip_url_args = array( 'id' => (int) $trip_id );
				$trip_url      = add_query_arg( $trip_url_args, TT_NS_BASE_URL . TT_NS_SERVICE_ITEM_PATH );
				return '<a href="' . esc_url( $trip_url ) . '" target="_blank">' . esc_html($item[ $column_name ]) . '</a>';
			case 'ns_trip_booking_id':
				$booking_url_args = array( 'id' => (int) $item[ $column_name ], 'whence' => '' );
				$booking_url      = add_query_arg( $booking_url_args, TT_NS_BASE_URL . TT_NS_BOOKINGS_PATH );
				return '<a href="' . esc_url( $booking_url ) . '" target="_blank">' . esc_html($item[ $column_name ]) . '</a>';
			case 'guestRegistrationId':
				$guest_reg_url_args = array( 'rectype' => 246, 'id' => (int) $item[ $column_name ], 'whence' => '' );
				$guest_reg_url      = add_query_arg( $guest_reg_url_args, TT_NS_BASE_URL . TT_NS_CUST_REC_ENTRY_PATH );
				return '<a href="' . esc_url( $guest_reg_url ) . '" target="_blank">' . esc_html($item[ $column_name ]) . '</a>';
			case 'netsuite_guest_registration_id':
				$guest_url_args = array( 'id' => (int) $item[ $column_name ], 'whence' => '' );
				$guest_url      = add_query_arg( $guest_url_args, TT_NS_BASE_URL . TT_NS_GUEST_PATH );
				return '<a href="' . esc_url( $guest_url ) . '" target="_blank">' . esc_html($item[ $column_name ]) . '</a>';
			case 'releaseFormId':
				$rf_url_args = array( 'rectype' => 162, 'id' => (int) $item[ $column_name ] );
				$rf_url      = add_query_arg( $rf_url_args, TT_NS_BASE_URL . TT_NS_CUST_REC_ENTRY_PATH );
				return '<a href="' . esc_url( $rf_url ) . '" target="_blank">' . esc_html($item[ $column_name ]) . '</a>';
			case 'guest_is_primary':
			case 'waiver_signed':
			case 'is_guestreg_cancelled':
				return 1 == $item[ $column_name ] ? 'yes' : 'no';
			case 'guest_gender':
				switch ( $item[ $column_name ] ) {
					case 1:
						return __( 'Male', 'trek-travel-netsuite-integration' );
					case 2:
						return __( 'Female', 'trek-travel-netsuite-integration' );
					default:
						return esc_html($item[ $column_name ]);
				}
			case 'product_id':
			case 'order_id':
				$order_url_args = array( 'post' => (int) $item[ $column_name ], 'action' => 'edit' );
				$order_url      = add_query_arg($order_url_args, admin_url( 'post.php') );
				return '<a href="' . esc_url( $order_url ) . '" target="_blank">' . esc_html($item[ $column_name ]) . '</a>';
			case 'web_order':
				$order         = wc_get_order( $item[ 'order_id' ] );
				$tt_order_type = $order->get_meta( 'tt_wc_order_type' );
				return 'auto-generated' === $tt_order_type ? 'no' : 'yes';
			case 'trip_type':
				$trip_info          = tt_get_trip_pid_sku_by_orderId( $item[ 'order_id' ] );
				$trip_id            = $trip_info['ns_trip_Id'];
				$is_hiking_checkout = tt_is_product_line( 'Hiking', $item[ 'trip_code' ], $trip_id );
				return $is_hiking_checkout ? 'Hiking' : 'Cycling';
			case 'bike':
				$own_bike_id       = 5270;
				$non_rider_bike_id = 5257;
				return '<span data-bike-id="' . $item[ 'bike_id' ] . '" >' . ( ( $non_rider_bike_id === (int) $item[ 'bike_id' ] ? 'Non-Rider' : ( ! in_array( (int) tt_validate( $item[ 'bike_id' ] ), array( $own_bike_id, $non_rider_bike_id ) ) ? tt_validate( tt_get_custom_item_name('ns_bikeType_info' )[ tt_validate( array_search( tt_validate( $item[ 'bike_type_id' ] ), array_column( tt_get_custom_item_name( 'ns_bikeType_info' ), 'id' ) ) ) ]['name'] ) : ( $own_bike_id === (int) tt_validate( $item[ 'bike_id' ] ) ? 'Bringing own' : '' ) ) ) . ' ' . ( tt_get_custom_item_name( 'syncBikeSizes', tt_validate( $item['bike_size'] ) )  === '-' ? '' : tt_get_custom_item_name( 'syncBikeSizes', tt_validate( $item['bike_size'] ) ) ) ) . '</span>';
			case 'medical_conditions':
			case 'medications':
			case 'allergies':
			case 'dietary_restrictions':
			case 'bike_comments':
			case 'ns_booking_response':
				return $this->wrap_long_content($item[$column_name]);
			case 'wc_order_meta':
				$wc_meta = get_post_meta($item['order_id']);
				$processed_meta = array();

				// Process all meta fields
				foreach ($wc_meta as $meta_key => $meta_values) {
					// Skip internal WordPress meta
					// if (strpos($meta_key, '_wp_') === 0 && $meta_key !== '_wp_travel_engine') {
					// 	continue;
					// }

					// Get first value since WP stores meta as arrays
					$value = $meta_values[0];

					// Try to unserialize if it's serialized
					$unserialized = maybe_unserialize($value);
					
					// If unserialization worked and produced a different value, use it
					if ($unserialized !== false && $unserialized != $value) {
						$processed_meta[$meta_key] = $unserialized;
					} else {
						// Try to decode JSON if it looks like JSON
						$json_decoded = json_decode($value, true);
						if ($json_decoded !== null && json_last_error() == JSON_ERROR_NONE) {
							$processed_meta[$meta_key] = $json_decoded;
						} else {
							$processed_meta[$meta_key] = $value;
						}
					}
				}

				return $this->wrap_long_content(json_encode($processed_meta));
			default:
				return print_r( $item, true );
		}
	}

	public function no_items() {
		_e('No Bookings found in the database.', 'trek-travel-netsuite-integration');

		// Check if we're on a paginated page
		if ( isset( $_GET['paged']) && $_GET['paged'] > 1 ) {
			$current_url = remove_query_arg('paged', $_SERVER['REQUEST_URI']);
			echo '<p class="description"><strong>';
			_e('It seems you were browsing further into the list of results, which might be why no items are shown.', 'trek-travel-netsuite-integration');
			echo ' <a href="' . esc_url($current_url) . '">' . __('Click here to reset pagination', 'trek-travel-netsuite-integration') . '</a>';
			echo '</strong></p>';
		}
	}

	/**
	 * Generates the table navigation above or bellow the table and removes the
	 * _wp_http_referrer and _wpnonce because it generates a error about URL too large
	 * 
	 * @param string $which 
	 * @return void
	 */
	function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft actions">
				<?php $this->bulk_actions(); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
		<?php
	}

	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = $this->get_hidden_columns();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$per_page              = $this->get_items_per_page( 'ttnsw_bookings_per_page' );
		$current_page          = $this->get_pagenum();
		$data                  = self::get_records($per_page, $current_page);
		$total_items           = self::record_count( $data );
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
		]);
		$this->items = $data;
	}

	public function search_box($text, $input_id) {
		if (empty($_REQUEST['s']) && !$this->has_items()) {
			return;
		}

		$input_id = $input_id . '-search-input';
		$search_column = isset($_REQUEST['search_column']) ? $_REQUEST['search_column'] : 'all';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>"><?php echo $text; ?>:</label>
			
			<select name="search_column" style="float: left; margin-right: 6px;">
				<option value="all" <?php selected($search_column, 'all'); ?>>All Columns</option>
				<option value="ns_trip_booking_id" <?php selected($search_column, 'ns_trip_booking_id'); ?>>Booking ID</option>
				<option value="order_id" <?php selected($search_column, 'order_id'); ?>>WC Order ID</option>
				<option value="guest_email_address" <?php selected($search_column, 'guest_email_address'); ?>>Email</option>
				<option value="guest_first_name" <?php selected($search_column, 'guest_first_name'); ?>>First Name</option>
				<option value="guest_last_name" <?php selected($search_column, 'guest_last_name'); ?>>Last Name</option>
				<option value="trip_code" <?php selected($search_column, 'trip_code'); ?>>Trip Code</option>
			</select>
			
			<input type="search" id="<?php echo esc_attr($input_id); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button($text, '', '', false, array('id' => 'search-submit')); ?>
		</p>
		<?php
	}

	public function display() {
		// Add modal HTML before displaying the table
		?>
		<div id="content-modal" class="ttnsw-modal">
			<div class="ttnsw-modal-content">
				<span class="ttnsw-modal-close">&times;</span>
				<h3 class="ttnsw-modal-title"></h3>
				<div class="ttnsw-modal-body"></div>
			</div>
		</div>
		<?php
		
		parent::display();
	}
}
<?php
/**
 * Generate TT Common Logs table preview in the admin panel.
 * Extends WP_List_Table.
 *
 * @link https://www.webtrickshome.com/forum/how-to-add-custom-data-table-in-wordpress-dashboard
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TT_Common_Logs extends WP_List_Table {

	/**
	 * Database table name.
	 * @var string
	 */
	private static $table_name = 'tt_common_error_logs';

	public function __construct( $args = array() ) {

		if ( empty( $args ) ) {
			$args = array(
				'singular' => 'tt-common-log',
				'plural'   => 'tt-common-logs',
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
		$where_values     = array();

		if ( ! isset( $_REQUEST['s'] ) || empty( $_REQUEST['s'] ) ) {
			return array( $where_conditions, $where_values );
		}

		$search_request = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		
		// Prepare search term for exact matching
		$exact_search_term = str_replace(
			array( '"', "'" ),     // Search for both single and double quotes
			array( '\"', "\'" ),   // Replace with escaped versions
			$search_request
		);
		$exact_search_term = '"' . $exact_search_term . '"';

		$search_column  = isset( $_REQUEST['search_column'] ) ? sanitize_text_field( $_REQUEST['search_column'] ) : 'all';

		// Get table indexes for FULLTEXT search capabilities
		$table_name = $wpdb->prefix . self::$table_name;
		$indexes    = $wpdb->get_results( "SHOW INDEX FROM {$table_name}", ARRAY_A );
		
		 // Map columns to their index types
		$column_index_types = array();
		if ( $indexes ) {
			foreach ( $indexes as $index ) {
				$column_index_types[ $index['Column_name'] ] = strtoupper( $index['Index_type'] );
			}
		}

		if ( 'all' === $search_column ) {
			$fulltext_conditions = array();
			$like_conditions    = array();

			$searchable_columns = array( 'type', 'args', 'response', 'created_at' );
			
			foreach ( $searchable_columns as $column ) {
				if ( isset( $column_index_types[ $column ] ) && 'FULLTEXT' === $column_index_types[ $column ] ) {
					 // Add both exact and natural language search for each FULLTEXT column
					$fulltext_conditions[] = "MATCH({$column}) AGAINST(%s IN NATURAL LANGUAGE MODE)";
					$where_values[]        = $exact_search_term;
				} else {
					// Use LIKE for columns without FULLTEXT index
					$like_conditions[] = "{$column} LIKE %s";
					$where_values[]    = '%' . $wpdb->esc_like( $search_request ) . '%';
				}
			}

			// Combine all conditions
			$all_conditions = array_merge( $fulltext_conditions, $like_conditions );
			if ( ! empty( $all_conditions ) ) {
				$where_conditions[] = '(' . implode( ' OR ', $all_conditions ) . ')';
			}

			return array( $where_conditions, $where_values );
		}

		// Single column search logic
		$valid_columns = array( 'type', 'args', 'response', 'created_at' );
		if ( ! in_array( $search_column, $valid_columns ) ) {
			return array( $where_conditions, $where_values );
		}

		// Use MATCH AGAINST if column has FULLTEXT index
		if ( isset( $column_index_types[ $search_column ] ) && 'FULLTEXT' === $column_index_types[ $search_column ] ) {
			// Try exact phrase search first, fallback to natural language if no results
			$where_conditions[] = "MATCH({$search_column}) AGAINST(%s IN NATURAL LANGUAGE MODE)";
			$where_values[]     = $exact_search_term;
		} else {
			$where_conditions[] = "{$search_column} LIKE %s";
			$where_values[]     = '%' . $wpdb->esc_like( $search_request ) . '%';
		}

		return array( $where_conditions, $where_values );
	}

	/**
	 * Get filtered count of records using information_schema for better performance
	 *
	 * @return int Total number of records
	 */
	private static function get_records_count() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;

		// Get where conditions
		list( $where_conditions, $where_values ) = self::get_where_conditions();

		// If we have search conditions, use regular COUNT with cache
		if ( ! empty( $where_conditions ) ) {
			// Build cache key for filtered results
			$cache_key = 'ttnsw_logs_count_' . md5( implode( '', $where_conditions ) . implode( '', $where_values ) );

			// Try to get cached count first
			$cached_count = get_transient( $cache_key );
			if ( false !== $cached_count ) {
				return (int) $cached_count;
			}

			// Build filtered count query
			$sql = "SELECT COUNT(id) FROM {$table_name}";
			$sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
			$count = (int) $wpdb->get_var( $wpdb->prepare( $sql, $where_values ) );

			// Cache filtered count for 5 minutes
			set_transient( $cache_key, $count, 300 );
			
			return $count;
		}

		$total_count = get_transient('ttnsw_logs_exact_count');
		if ( $total_count ) {
			return (int) $total_count;
		}

		return self::get_approximate_count();
	}

	/**
	 * Get paginated and filtered records
	 *
	 * @param int $per_page Number of records per page
	 * @param int $page_number Current page number
	 * @return array
	 */
	private static function get_records($per_page = 20, $page_number = 1) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;

		// Get where conditions
		list( $where_conditions, $where_values ) = self::get_where_conditions();

		// Build base query
		$sql = "SELECT id, type, args, response, created_at FROM {$table_name}";

		// Add where conditions if we have any, otherwise add date filtering for first page
		if ( ! empty( $where_conditions ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
		} else {
			// Load the first page faster by limiting the results to the last 30 days
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

		// Add ordering
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$allowed_orderby = array( 'id', 'created_at' );
			$orderby = in_array( $_REQUEST['orderby'], $allowed_orderby ) ? $_REQUEST['orderby'] : 'id';
			$order = ( isset( $_REQUEST['order'] ) && strtoupper( $_REQUEST['order'] ) === 'ASC' ) ? 'ASC' : 'DESC';
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

		// Debug query execution time
		$start = microtime( true );
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		$end = microtime( true );
		$time = $end - $start;

		add_settings_error( 'ttnsw-admin-notice', 'ttnsw_logs_error', 'Database query time: ' . self::format_execution_time( $time ), 'info' );
		if ( $wpdb->last_error ) {
			add_settings_error( 'ttnsw-admin-notice', 'ttnsw_logs_error', $wpdb->last_error, 'error' );
		}

		return $result;
	}

	/**
	 * Get total number of records
	 *
	 * @param array $data Not used anymore, kept for backward compatibility
	 * @return int
	 */
	public static function record_count($data = null) {
		return self::get_records_count();
	}

	public function get_columns() {
		$columns = [
			'id'         => __( 'ID', 'trek-travel-netsuite-integration'),
			'type'       => __( 'Type', 'trek-travel-netsuite-integration'),
			'args'       => __( 'Args', 'trek-travel-netsuite-integration'),
			'response'   => __( 'Response', 'trek-travel-netsuite-integration'),
			'created_at' => __( 'Created At', 'trek-travel-netsuite-integration'),
		];
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
			'created_at'         => array( 'created_at',true ),
		);

		return $sortable_columns;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'type':
			case 'created_at':
				return esc_html($item[ $column_name ]);
			case 'args':
			case 'response':
				$content = $item[ $column_name ];
				// Check if content is JSON
				$is_json = is_string($content) && is_array(json_decode($content, true)) && (json_last_error() == JSON_ERROR_NONE);
				
				if ($is_json) {
					// For JSON content, encode to base64 to preserve structure
					$encoded = base64_encode($content);
					
					return '<div class="full-content">' . 
						$content .
						'<span class="expand-modal dashicons dashicons-editor-expand" data-is-json="1" data-full-content="' . esc_attr($encoded) . '"></span>' .
						'</div>';
				}

				return '<div class="full-content">' . 
					$content .
					'<span class="expand-modal dashicons dashicons-editor-expand" data-is-json="0" data-full-content="' . esc_attr($content) . '"></span>' .
					'</div>';

			default:
				return print_r( $item, true );
		}
	}

	public function no_items() {
		_e('No Logs found in the database.', 'trek-travel-netsuite-integration');
		
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
		$per_page              = $this->get_items_per_page( 'ttnsw_common_logs_per_page' );
		$current_page          = $this->get_pagenum();
		$data                  = self::get_records( $per_page, $current_page );
		$total_items           = self::record_count( $data );
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => (int) ceil($total_items / $per_page),
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
				<option value="type" <?php selected($search_column, 'type'); ?>>Type</option>
				<option value="args" <?php selected($search_column, 'args'); ?>>Arguments</option>
				<option value="response" <?php selected($search_column, 'response'); ?>>Response</option>
				<option value="created_at" <?php selected($search_column, 'created_at'); ?>>Created At</option>
			</select>
			
			<input type="search" id="<?php echo esc_attr($input_id); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button($text, '', '', false, array('id' => 'search-submit')); ?>
		</p>
		<?php
	}

	/**
	 * Formats the execution time in seconds to a human-readable format.
	 *
	 * @param float $time Time in seconds
	 * @return string Formatted time
	 */
	private static function format_execution_time($time) {
		if ($time < 0.001) {
			return number_format($time * 1000, 2) . ' ms';
		} elseif ($time < 60) {
			return number_format($time, 3) . ' s';
		} else {
			$minutes = floor($time / 60);
			$seconds = $time % 60;
			return $minutes . 'm ' . number_format($seconds, 2) . 's';
		}
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

			// Add flag for approximate count
			list( $where_conditions ) = self::get_where_conditions();
			$total_count              = get_transient('ttnsw_logs_exact_count');
			$is_approximate           = empty($where_conditions) && ! $total_count;
		?>
			<span class="ttnsw-is-approximate" data-is-approximate="<?php echo esc_attr($is_approximate ? 'true' : 'false'); ?>" hidden></span>
			<span class="ttnsw-is-exact-count" data-is-exact-count="<?php echo esc_attr($total_count ? 'true' : 'false'); ?>" hidden></span>
		<?php
	}

	/**
	 * Add extra tablenav elements
	 * 
	 * @param string $which top or bottom
	 */
	public function extra_tablenav($which) {
		if ( $which === 'top' ) {
			// Add date range filter
			$this->render_total_count();
		}
	}

	/**
	 * Render total count of records with loading state and explanation
	 * 
	 * Displays an approximate count initially to provide fast page load times,
	 * then triggers a background process to calculate the exact count.
	 */
	private function render_total_count() {
		$exact_count  = get_transient('ttnsw_logs_exact_count');
		$approx_count = self::get_approximate_count();
		
		?>
		<div class="alignleft actions ttnsw-count-wrapper">
			<span class="ttnsw-logs-total-count" 
				  data-nonce="<?php echo wp_create_nonce('ttnsw_calculate_exact_count'); ?>"
				  data-approx="<?php echo esc_attr($approx_count); ?>">
				<?php
				if ( ! $exact_count ) {
					printf(
						/* translators: %s: approximate number of records */
						__('Total (refreshes every 5min): ~%s records <span class="calculating-indicator spinner is-active"></span>', 'trek-travel-netsuite-integration'),
						number_format_i18n($approx_count)
					);
					?>
					<?php
				} else {
					printf(
						/* translators: %s: exact number of records */
						__('Total (refreshes every 5min): %s records', 'trek-travel-netsuite-integration'),
						number_format_i18n($exact_count)
					);
				}
				?>
			</span>
		</div>
		<?php
	}

	/**
	 * Get approximate count from information schema
	 *
	 * @return int Approximate number of records.
	 */
	private static function get_approximate_count() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;

		// For unfiltered counts, use information_schema
		static $total_rows = null;

		// Check if we already calculated total rows in this request
		if ( null === $total_rows ) {
			// Try to get cached total first
			$total_rows = get_transient( 'ttnsw_logs_total_count' );

			if ( false === $total_rows ) {
				// Get approximate row count from information_schema
				$total_rows = $wpdb->get_var( $wpdb->prepare(
					"SELECT TABLE_ROWS 
					FROM information_schema.TABLES 
					WHERE TABLE_SCHEMA = DATABASE()
					AND TABLE_NAME = %s",
					$table_name
				) );

				if ( empty( $total_rows ) ) {
					// Fallback to regular COUNT if information_schema is not available
					$total_rows = $wpdb->get_var("SELECT COUNT(id) FROM {$table_name}");
				}

				if ( $wpdb->last_error ) {
					add_settings_error( 'ttnsw-admin-notice', 'ttnsw_logs_error', $wpdb->last_error, 'error' );
				}

				// Cache for 5 minutes
				set_transient( 'ttnsw_logs_total_count', $total_rows, 300 );
			}
		}

		return (int) $total_rows;
	}
}
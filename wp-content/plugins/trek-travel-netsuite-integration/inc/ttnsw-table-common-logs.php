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

	private static function get_records( $per_page = 20, $page_number = 1 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'tt_common_error_logs';
		$sql        = "SELECT *, COUNT(*) OVER() as cnt FROM {$table_name}";
		$where_values = array();
		
		if ( isset( $_REQUEST['s'] ) && !empty($_REQUEST['s']) ) {
			$search_request = sanitize_text_field(wp_unslash($_REQUEST['s']));
			$search_column = isset($_REQUEST['search_column']) ? sanitize_text_field($_REQUEST['search_column']) : 'all';

			if ($search_column === 'all') {
				$sql .= ' WHERE (type LIKE %s OR args LIKE %s OR response LIKE %s OR created_at LIKE %s)';
				$search_pattern = '%' . $wpdb->esc_like($search_request) . '%';
				$where_values = array_merge($where_values, array($search_pattern, $search_pattern, $search_pattern, $search_pattern));
			} else {
				$valid_columns = array('type', 'args', 'response', 'created_at');
				if (in_array($search_column, $valid_columns)) {
					$sql .= ' WHERE ' . $search_column . ' LIKE %s';
					$where_values[] = '%' . $wpdb->esc_like($search_request) . '%';
				}
			}
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$allowed_orderby = array('id', 'created_at');
			$orderby = in_array($_REQUEST['orderby'], $allowed_orderby) ? $_REQUEST['orderby'] : 'id';
			$order = (isset($_REQUEST['order']) && strtoupper($_REQUEST['order']) === 'DESC') ? 'DESC' : 'ASC';
			$sql .= ' ORDER BY ' . $orderby . ' ' . $order;
		} else {
			$sql .= ' ORDER BY id DESC';
		}

		$sql .= ' LIMIT %d OFFSET %d';
		$where_values[] = $per_page;
		$where_values[] = ($page_number - 1) * $per_page;

		$sql = $wpdb->prepare($sql, $where_values);
		$result = $wpdb->get_results($sql, 'ARRAY_A');
		return $result;
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
				return $item[ $column_name ];
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

	public static function record_count( $data ) {
		$total_items = 0;

		if( ! empty( $data ) && is_array( $data ) ) {
			$total_items = isset( $data[0]['cnt'] ) ? ( int ) $data[0]['cnt'] : 0;
		}

		return $total_items;
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
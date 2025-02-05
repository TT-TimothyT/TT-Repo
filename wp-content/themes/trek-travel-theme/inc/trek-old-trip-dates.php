<?php

// Main class to handle custom product type "old_trip_date" and admin list table
class TT_Old_Trip_Date_Product_Type {

	public function __construct() {
		// Register hooks
		add_action( 'init', array( $this, 'tt_register_old_trip_date_product_type' ) );
		add_filter( 'product_type_selector', array( $this, 'tt_add_old_trip_date_product_type' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'tt_add_old_trip_date_custom_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'tt_save_old_trip_date_custom_fields' ) );
		add_action( 'pre_get_posts', array( $this, 'tt_exclude_old_trip_date_from_admin_products' ) );
		add_filter( 'woocommerce_json_search_found_products', array( $this, 'tt_exclude_old_trip_date_from_grouped_products' ) );
		add_action( 'woocommerce_product_options_related', array( $this, 'tt_old_trip_date_grouped_products_admin' ) );

		// Add custom admin menu page
		add_action( 'admin_menu', array( $this, 'tt_add_old_trip_date_admin_menu' ) );
	}

	// Register the new product type "old_trip_date"
	public function tt_register_old_trip_date_product_type() {
		if ( class_exists( 'WC_Product' ) ) {
			include_once 'trek-old-trip-dates-type.php'; // Load the custom product class
		}
	}

	// Function to get all simple products
	public function tt_get_all_simple_products_ids() {
		$products_query_args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'tt_line_item_fees_product',
					'compare' => 'NOT EXISTS'
				)
			),
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'simple',
					'operator' => 'IN'
				),
			)
		);
		$products_query = new WP_Query( $products_query_args );
		return $products_query->posts;
	}

	// Function to update product type based on the end dates
	public function tt_update_old_trip_date_products_type() {
		// Get all simple products
		$simple_products_ids = $this->tt_get_all_simple_products_ids();
	
		foreach ( $simple_products_ids as $product_id ) {	
			// Get the product attributes
			$attributes = get_post_meta( $product_id, '_product_attributes', true );
			$original_attributes = $attributes; // Save a copy of the original attributes

			if ( isset( $attributes['pa_end-date'] ) ) {
				$end_date = $attributes['pa_end-date']['value'];

				// Parse the date
				$end_date_time = DateTime::createFromFormat('d/m/y', $end_date);

				if ( $end_date_time ) {
					$current_date = new DateTime();
					$interval = $current_date->diff( $end_date_time );

					// If the end date is 30 days or more in the past
					if ( $end_date_time < $current_date && $interval->days >= 30 ) {
						// Change the product type to 'old_trip_date'
						wp_set_object_terms( $product_id, 'old_trip_date', 'product_type' );

						// Explicitly restore the product attributes to prevent reset
						update_post_meta( $product_id, '_product_attributes', $original_attributes );

						// Get parent products of the current product
						$parent_ids = get_parent_products( $product_id );

						foreach ( $parent_ids as $parent_id ) {
							// Get existing grouped products
							$grouped_products = get_post_meta( $parent_id, '_children', true );
							if ( is_array( $grouped_products ) ) {
								// Remove the current product from the "Grouped Products" list
								$grouped_products = array_diff( $grouped_products, [$product_id] );
								update_post_meta( $parent_id, '_children', $grouped_products );
							}

							// Add the product to the "Grouped Old Trip Dates" custom field
							$grouped_old_trip_dates = get_post_meta( $parent_id, '_grouped_old_trip_dates', true );
							if ( ! is_array( $grouped_old_trip_dates ) ) {
								$grouped_old_trip_dates = [];
							}
							if ( ! in_array( $product_id, $grouped_old_trip_dates ) ) {
								$grouped_old_trip_dates[] = $product_id;
								update_post_meta( $parent_id, '_grouped_old_trip_dates', $grouped_old_trip_dates );
							}
						}
					}
				}
			}
		}
	}

	// Add "old_trip_date" to the product type dropdown in the admin
	public function tt_add_old_trip_date_product_type($types) {
		$types['old_trip_date'] = __('Old Trip Dates', 'woocommerce');
		return $types;
	}

	// Custom fields for "old_trip_date" products (optional, you can add more here)
	public function tt_add_old_trip_date_custom_fields() {
		global $woocommerce, $post;

		// Display a checkbox for marking as a old trip dates
		echo '<div class="options_group">';
		woocommerce_wp_checkbox(
			array(
				'id'            => '_tt_old_trip_date',
				'wrapper_class' => '',
				'label'         => __('Is this a Old Trip Date?', 'woocommerce'),
				'description'   => __('Check if this is a old trip date product.', 'woocommerce'),
			)
		);
		echo '</div>';
	}

	// Save custom fields for "old_trip_date" products
	public function tt_save_old_trip_date_custom_fields($post_id) {
		$is_old_trip_date = isset($_POST['_tt_old_trip_date']) ? 'yes' : 'no';
		update_post_meta($post_id, '_tt_old_trip_date', $is_old_trip_date);
	}

	// Exclude "old_trip_date" from the main product listing in the admin
	public function tt_exclude_old_trip_date_from_admin_products( $query ) {
		global $pagenow, $typenow;

		if ( is_admin() && $pagenow == 'edit.php' && $typenow == 'product' && ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'tt-old-trip-date-products' ) ) {
			$tax_query = $query->get('tax_query') ? $query->get('tax_query') : array();
			$tax_query[] = array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => 'old_trip_date',
				'operator' => 'NOT IN',
			);
			$query->set( 'tax_query', $tax_query );
		}
	}

	// Exclude "old_trip_date" from being selectable in Grouped Products
	public function tt_exclude_old_trip_date_from_grouped_products($products) {
		foreach ($products as $product_id => $product_name) {
			$product = wc_get_product($product_id);
			if ($product && $product->get_type() === 'old_trip_date') {
				unset($products[$product_id]);
			}
		}
		return $products;
	}

	// Add a custom admin menu page for listing old_trip_date products
	public function tt_add_old_trip_date_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=product', // Parent menu (Products)
			__('Old Trip Dates', 'woocommerce'), // Page title
			__('Old Trip Dates', 'woocommerce'), // Menu title
			'manage_woocommerce', // Capability
			'tt-old-trip-date-products', // Menu slug
			array( $this, 'tt_render_old_trip_date_products_page' ) // Callback function to render the page
		);
	}

	// Render the Old Trip Products page in the admin
	public function tt_render_old_trip_date_products_page() {
		echo '<div class="wrap"><h1>' . __( 'Old Trip Dates', 'woocommerce' ) . '</h1>';
		
		// Create an instance of the custom list table.
		$old_trip_date_list_table = new TT_Old_Trip_Date_List_Table();
		echo '<form method="get">';
		echo '<input type="hidden" name="post_type" value="product">';
		echo '<input type="hidden" name="page" value="tt-old-trip-date-products">';

		// Prepare and display items.
		$old_trip_date_list_table->prepare_items();
		// Search box (before displaying items).
		$old_trip_date_list_table->search_box( __( 'Search Old Trip Dates', 'woocommerce' ), 'search_id' );
		$old_trip_date_list_table->display();

		echo '</form>';

		echo '</div>';
	}

	/**
	 * Under Linked product options.
	 */
	public function tt_old_trip_date_grouped_products_admin() {
		global $post, $product_object;
		?>
		<div class="options_group show_if_grouped">
			<p class="form-field">
				<label for="grouped_old_trip_dates"><?php esc_html_e('Grouped Old Trip Dates', 'woocommerce'); ?></label>
				<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="grouped_old_trip_dates" name="grouped_old_trip_dates[]" data-sortable="true" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'woocommerce'); ?>" data-action="woocommerce_json_search_products" data-exclude="<?php echo intval($post->ID); ?>">
					<?php
					$grouped_old_trip_dates = get_post_meta($post->ID, '_grouped_old_trip_dates', true);
					if (!is_array($grouped_old_trip_dates)) {
						$grouped_old_trip_dates = [];
					}
	
					foreach ($grouped_old_trip_dates as $product_id) {
						$product = wc_get_product($product_id);
						if (is_object($product)) {
							echo '<option value="' . esc_attr($product_id) . '" selected="selected">' . esc_html(wp_strip_all_tags($product->get_formatted_name())) . '</option>';
						}
					}
					?>
				</select>
				<?php echo wc_help_tip(__('This lets you choose which products are part of this "Grouped Old Trip Dates" list.', 'woocommerce')); ?>
			</p>
		</div>
		<?php
	}	
}

// Custom list table class for displaying old_trip_date products
if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class TT_Old_Trip_Date_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct(array(
			'singular' => __('Old Trip Date', 'woocommerce'),
			'plural'   => __('Old Trip Dates', 'woocommerce'),
			'ajax'     => false,
		));
	}

	// Retrieve old_trip_date products from the database
	public function tt_get_old_trip_date_products( $search = '' ) {
		$args = array(
			'post_type'   => 'product',
			'post_status' => 'publish',
			'tax_query'   => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => array( 'old_trip_date' ),
					'operator' => 'IN'
				),
			),
			'posts_per_page' => -1,
		);

		// If there is a search term, add it to the query.
		if ( ! empty( $search ) ) {
			$args['s'] = sanitize_text_field( $search ); // Ensure the search term is safe.
		}

		$query = new WP_Query($args);
		return $query->posts;
	}

	// Define table columns
	public function get_columns() {
		$columns = array(
			'title'          => __( 'Product Title', 'woocommerce' ),
			'id'             => __( 'Product ID', 'woocommerce' ),
			'sku'            => __( 'SKU', 'woocommerce' ),
			'last_synced'    => __( 'Last Synced', 'woocommerce' ),
			'parent_product' => __( 'Parent Product', 'woocommerce' ),
		);
		return $columns;
	}

	// Sortabale columns.
	public function get_sortable_columns() {
		$sortable_columns = array(
			'sku' => array( 'sku', true ),
			'id'  => array( 'id', true ),
		);

		return $sortable_columns;
	}

	// Prepare the items for the table
	public function prepare_items() {
		// Number of items to display per page.
		$per_page = 20;
	
		// Handle search request.
		$search = ( isset( $_REQUEST['s'] ) ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$this->items = $this->tt_get_old_trip_date_products( $search );
	
		// Set columns and column headers.
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
	
		// Handle sorting.
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'id';
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'asc';
	
		// Sort the items.
		usort( $this->items, function ( $a, $b ) use ( $orderby, $order ) {
			$result = 0;
	
			if ( $orderby === 'id' ) {
				$result = ( $a->ID < $b->ID ) ? -1 : 1;
			} elseif ( $orderby === 'sku' ) {
				$sku_a = get_post_meta( $a->ID, '_sku', true );
				$sku_b = get_post_meta( $b->ID, '_sku', true );
				$result = strcmp( $sku_a, $sku_b );
			}
	
			return ( $order === 'asc' ) ? $result : -$result;
		});
	
		// Pagination parameters.
		$total_items   = count( $this->items );
		$current_page  = $this->get_pagenum();
		$offset        = ( $current_page - 1 ) * $per_page;
	
		// Paginate the items (use array_slice for pagination).
		$this->items = array_slice( $this->items, $offset, $per_page );
	
		// Set pagination arguments.
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}	

	// Render individual columns for each row
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'sku':
				return get_post_meta( $item->ID, '_sku', true );
			case 'id':
				return $item->ID;
			case 'stock':
				return wc_stock_amount( get_post_meta( $item->ID, '_stock', true ) );
			case 'last_synced':
				return get_post_meta( $item->ID , 'ns_last_synced_date_time' , true );
			case 'parent_product':
				$parents_output = '';
				global $wpdb;
				
				// Modified query with DISTINCT to avoid duplicate parent IDs
				$query = $wpdb->prepare(
					"SELECT DISTINCT post_id 
						FROM {$wpdb->prefix}postmeta 
						WHERE meta_key = '_grouped_old_trip_dates' 
						AND meta_value LIKE %s",
					'%:' . $item->ID . ';%'
				);
				$parent_ids = $wpdb->get_col( $query );
			
				if ( ! empty( $parent_ids ) ) {
					foreach ( $parent_ids as $parent_id ) {
						$parent_title = get_the_title( $parent_id );
						$parents_output .= '<a href="' . get_edit_post_link( $parent_id ) . '">' . $parent_title . '</a><br>';
					}
				} else {
					$parents_output .= __('None', 'woocommerce');
				}
				return $parents_output;
			default:
				return print_r( $item, true ); // Show the whole array for debugging purposes
		}
	}

	// Render the title column
	public function column_title( $item ) {
		$edit_link = get_edit_post_link( $item->ID );
		return '<a href="' . $edit_link . '">' . $item->post_title . '</a>';
	}
}

// Instantiate the main class
new TT_Old_Trip_Date_Product_Type();

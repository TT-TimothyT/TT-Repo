<?php

class NS_Inventory {

	private $logger;
	public $Manual_update_inventory;

	/**
	 * Construct function
	 */
	public function __construct() {
		global $TMWNI_OPTIONS;


		require_once 'background-process/class-manual-update-inventory.php' ;	
		$this->Manual_update_inventory = new Manually_Update_Inventory();

		if (( isset($TMWNI_OPTIONS['enableInventorySync']) && 'on' === $TMWNI_OPTIONS['enableInventorySync'] ) || 
			( isset($TMWNI_OPTIONS['enablePriceSync']) && 'on' === $TMWNI_OPTIONS['enablePriceSync'] )) {
			add_action('wp_ajax_update_woo_inventory', array($this, 'tmNsUpdateWooInventory'));
		add_action('wp_ajax_fetch_price_progress', array($this, 'fetchPriceUpdateStatus'));
		add_action('wp_ajax_fetch_inventory_progress', array($this, 'fetchInventoryUpdateStatus'));
		add_action('init', array($this, 'register_inventory_cron'));
		add_action('tm_ns_process_inventories', array($this, 'updateWooInventory'));		
		}
	add_filter('cron_schedules', array($this, 'custom_cron_schedules'));
	}

	/**
	 * Handle manual WooCommerce inventory update
	 */
	public function tmNsUpdateWooInventory() {
		global $TMWNI_OPTIONS;

		if (( isset($TMWNI_OPTIONS['enableInventorySync']) && 'on' === $TMWNI_OPTIONS['enableInventorySync'] ) || 
			( isset($TMWNI_OPTIONS['enablePriceSync']) && 'on' === $TMWNI_OPTIONS['enablePriceSync'] )) {

			if (TMWNI_Settings::areCredentialsDefined()) {
				$this->updateMannualWooInventory();
			} else {
				wp_die('Please setup API credentials first');
			}
		} else {
			wp_die('Please enable inventory sync first');
		}
	}

	/**
	 * Define custom cron schedules
	 */
	public function custom_cron_schedules($schedules) {
		if (!isset($schedules['10min'])) {
			$schedules['10min'] = array(
				'interval' => 600,
				'display' => __('Once every 10 minutes'),
			);
		}

		return $schedules;
	}

	/**
	 * Register inventory cron
	 */
	public function register_inventory_cron() {
		global $TMWNI_OPTIONS;
		$inventorySyncFrequency = $TMWNI_OPTIONS['inventorySyncFrequency'];
		if (!wp_next_scheduled('tm_ns_process_inventories')) {
			wp_schedule_event(time(), $inventorySyncFrequency, 'tm_ns_process_inventories');
		}

		// For removing inventory background process
		if (isset($_GET['inventory']) && 1 == $_GET['inventory']) {
			$this->updateWooInventory();
		}
	}


	public function updateWooInventory() {

		global $TMWNI_OPTIONS, $wpdb;
		$by_queue = false;
		$sku_lot = $this->getProductSKULot();
		$sku_lot = apply_filters('tm_woo_products_skus', $sku_lot);
		$updateInventoryDateTime = gmdate('Y-m-d H:i:s a');
		update_option('ns_woo_inventory_update', $updateInventoryDateTime, false);
		if (!empty($sku_lot)) {
			$batch_size = 1000;
			$batches = array_chunk($sku_lot, $batch_size);
			foreach ($batches as $batch) {
				$sku_lot = implode(', ', $batch);
				$this->updateData($sku_lot, $by_queue);
			}	
		}
	}

	/**
	 * Update WooCommerce inventory
	 */
	public function updateMannualWooInventory() {
		global $TMWNI_OPTIONS, $wpdb;

		if (isset($TMWNI_OPTIONS['enablePriceSync']) && 'on' == $TMWNI_OPTIONS['enablePriceSync'] ) {
			$price_sync = 'on';
		} else {
			$price_sync = 'off';
		}

		$total_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE (post_type='product' OR post_type='product_variation') AND post_status='publish'");

		$update_total = update_option('total_product_count', $total_count, false);
		$update_processed = update_option('processed_products', 0, false);
		$update_updated = update_option('updated_products_count', 0, false);
		$update_not_found = update_option('not_found_skus', array(), false);
		$update_skipped = update_option('skipped_count', array(), false);
		$file_dir = wp_upload_dir();
		$log_file = $file_dir['basedir'] . '/' . TMWNI_Settings::$ns_price_log_file;
		file_put_contents($log_file, '');
		$inventory_log_file = $file_dir['basedir'] . '/' . TMWNI_Settings::$ns_inventory_log_file;
		file_put_contents($inventory_log_file, '');


		
		$sku_lot = $this->getProductSKULot();

			/**
				* Filter for get woocommerce product sku.
				*
				* @since 1.0.0

			*/
				$sku_lot = apply_filters('tm_woo_products_skus', $sku_lot);
			

		if (!empty($sku_lot)) {
			// $this->processedData($sku_lot);
			$this->Manual_update_inventory->push_to_queue(array('product_sku' => $sku_lot));
		}
				$this->Manual_update_inventory->save()->dispatch();
				wp_send_json(array( 'success' => true, 'total_count' => $total_count,'price_sync' => $price_sync));
				wp_die();
	}

	public function processedData($sku_array) {
		global $TMWNI_OPTIONS;
		$batch_size = 1000;
		$batches = array_chunk($sku_array, $batch_size);
		$by_queue = true;
				
		foreach ($batches as $batch) {
			$batch_count = count($batch);
			$sku_lot = implode(', ', $batch);
			$old_count = get_option('processed_products');
			$new_count = $old_count + $batch_count;
			update_option('processed_products', $new_count, false);
			$this->updateData($sku_lot, $by_queue);

		}	
		return true;
	}

	public function updateData($sku_lot,$by_queue) {
		global $TMWNI_OPTIONS;
		$urlAPIEndPoint = '/suiteql';
		if ('customFieldList' == $TMWNI_OPTIONS['sku_mapping_field']) {
			$map_field_name = $TMWNI_OPTIONS['sku_mapping_custom_field'];
			$map_field_name = str_replace('Field ID: ', '', $map_field_name);
		} else {
			$map_field_name =  $TMWNI_OPTIONS['sku_mapping_field'];
		}

		require_once TMWNI_DIR . 'inc/item.php';
		$netsuiteClient = new ItemClient();
				
		if (isset($TMWNI_OPTIONS['enablePriceSync']) && 'on' == $TMWNI_OPTIONS['enablePriceSync'] ) {
			$netsuiteClient->getProductPriceData($sku_lot, $map_field_name, $TMWNI_OPTIONS['price_level_name'], $TMWNI_OPTIONS['price_currency'], $urlAPIEndPoint, $by_queue);
		}

		if (isset($TMWNI_OPTIONS['enableInventorySync']) && 'on' == $TMWNI_OPTIONS['enableInventorySync'] ) {

			if (1 == $TMWNI_OPTIONS['inventoryDefaultLocation']  || 3 == $TMWNI_OPTIONS['inventoryDefaultLocation'] ) {
				$netsuiteClient->getInventoryFromNetsuite($sku_lot, $map_field_name, $urlAPIEndPoint, $by_queue);
			}

			if (2 == $TMWNI_OPTIONS['inventoryDefaultLocation']) {
				$netsuiteClient->getDefaultLocationInventory($sku_lot, $map_field_name, $urlAPIEndPoint, $by_queue);
			}

		}

	}

	/**
	 * Get product SKUs in a batch
	 */
	public function getProductSKULot() {
		global $wpdb;

		$products = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts}  WHERE (post_type='product' OR post_type='product_variation') AND post_status='publish'"));

		/**
			* Filter for get woocommerce all products.
			*
			* @since 1.0.0

			**/     
			$products = apply_filters('tm_netsuite_get_all_woo_product', $products);

			$sku_lot = array();

		foreach ($products as $key=>$product) {
			$sku = get_post_meta($product->ID, '_sku', true);
			if (!empty($sku)) {
				$sku_lot[] = "'" . esc_sql($sku) . "'";
			}
		}

			return $sku_lot; 
			// return implode(', ', $sku_lot);
	}

	public function fetchPriceUpdateStatus() {

		if (isset($_POST['nonce']) && !empty($_POST['nonce']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'security_nonce') ) {
			if (!empty($_POST['action'])) {
				$processed_count = get_option('processed_products');
				$not_found_skus = get_option('not_found_skus');
				$skipped_count = get_option('skipped_count');
				$updated_count = get_option('updated_products_count');
				$file_dir = wp_upload_dir();
				$price_log_file = $file_dir['basedir'] . '/' . TMWNI_Settings::$ns_price_log_file;
				$price_logs = file_get_contents($price_log_file);
				wp_send_json(array( 'success' => true, 'processed_count' => $processed_count, 'skus' => $not_found_skus, 'updated_count' => $updated_count, 'price_logs' => $price_logs,'skipped_count' => $skipped_count ));
				die();
			} 
		} else {
			die('Nonce Error');
		}
	}

	public function fetchInventoryUpdateStatus() {
		if (isset($_POST['nonce']) && !empty($_POST['nonce']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'security_nonce') ) {
			if (!empty($_POST['action'])) {
				$processed_count = get_option('processed_products');
				$not_found_skus = get_option('not_found_skus');
				$skipped_count = get_option('skipped_count');

				$updated_count = get_option('updated_products_count');
				$file_dir = wp_upload_dir();
				$log_file = $file_dir['basedir'] . '/' . TMWNI_Settings::$ns_inventory_log_file;
				$logs = file_get_contents($log_file);
				wp_send_json(array( 'success' => true, 'processed_count' => $processed_count, 'skus' => $not_found_skus, 'updated_count' => $updated_count, 'logs' => $logs, 'skipped_count' => $skipped_count ));
				die();
			} 
		} else {
			die('Nonce Error');
		}
	}

}

	new NS_Inventory();

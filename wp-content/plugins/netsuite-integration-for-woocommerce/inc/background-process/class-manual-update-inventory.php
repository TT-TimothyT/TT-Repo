<?php

// require_once TMWNI_DIR . 'inc/wp-background-processing/classes/wp-background-process.php';

class Manually_Update_Inventory extends WP_Background_Process {

	
	protected $action = 'tm_ns_manual_process_inventories';

	
	protected function task( $data ) {
		// Actions to perform
		require_once(TMWNI_DIR . 'inc/item.php');
		$netsuiteClient = new ItemClient();
		$netsuiteClient->searchItemUpdateInventory($data['product_sku'], $data['product_id']);
		$old_count = get_option('processed_products');
		error_log(print_r($old_count), true);
		$new_count = $old_count + 1;
		error_log(print_r($new_count), true);
		update_option('processed_products', $new_count, false);
		return false;
	}

	
	protected function complete() {
		parent::complete();

		// Show notice to user or perform some other arbitrary task...
	}


	public function cancel_process() {
		if ( ! $this->is_queue_empty() ) {
			$batch = $this->get_batch();
			pr($batch);

			$this->delete( $batch->key );

			wp_clear_scheduled_hook( $this->cron_hook_identifier );
		}

	}


	public function delete( $key ) {
		delete_site_option( $key );
		die('delete_inventory_queue');

		return $this;
	}



}

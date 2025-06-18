<?php

class Manually_Update_Inventory extends WP_Background_Process {


	protected $action = 'tm_ns_manual_process_inventories';


	protected function task( $data ) {
		// Actions to perform
		require_once TMWNI_DIR . 'inc/inventory.php';
		$netsuiteClient = new NS_Inventory();
		$netsuiteClient->processedData( $data['product_sku'] );
		return false;
	}


	protected function complete() {
		parent::complete();
	}


	public function cancel_process() {
		if ( ! $this->is_queue_empty() ) {
			$batch = $this->get_batch();
			$this->delete( $batch->key );
			wp_clear_scheduled_hook( $this->cron_hook_identifier );
		}
	}


	public function delete( $key ) {
		delete_site_option( $key );
		die( 'delete_inventory_queue' );

		return $this;
	}
}

<?php

// require_once TMWNI_DIR . 'inc/wp-background-processing/classes/wp-background-process.php';

class Add_Netsuite_Order extends WP_Background_Process {

	
	protected $action = 'tm_ns_process_orders';

	
	protected function task( $order_id ) {
		// Actions to perform
		require_once(TMWNI_DIR . 'inc/loader.php');
		$this->netsuiteOrderClient = new TMWNI_Loader();
		$order_netsuite_internal_id = $this->netsuiteOrderClient->addNetsuiteOrder($order_id);


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
		die('delete_order_queue');

		return $this;
	}

}

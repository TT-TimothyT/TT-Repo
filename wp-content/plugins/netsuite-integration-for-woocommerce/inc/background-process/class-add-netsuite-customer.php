<?php

// require_once TMWNI_DIR . 'inc/wp-background-processing/classes/wp-background-process.php';

class Add_Netsuite_Customer extends WP_Background_Process {


	protected $action = 'tm_ns_process_customers';


	protected function task( $customer_id ) {
		// Actions to perform
		require_once TMWNI_DIR . 'inc/loader.php';
		$this->deleteQueueFromDataBase( $customer_id );
		$this->netsuiteLoaderClient = new TMWNI_Loader();
		$order_netsuite_internal_id = $this->netsuiteLoaderClient->addUpdateNetsuiteCustomer( $customer_id );

		return false;
	}


	protected function complete() {
		parent::complete();

		// Show notice to user or perform some other arbitrary task...
	}


	public function cancel_process() {
		if ( ! $this->is_queue_empty() ) {
			$batch = $this->get_batch();
			pr( $batch );

			$this->delete( $batch->key );

			wp_clear_scheduled_hook( $this->cron_hook_identifier );
		}
	}


	public function delete( $key ) {
		delete_site_option( $key );
		die( 'delete_order_queue' );

		return $this;
	}


	public function deleteQueueFromDataBase( $order_id ) {
		global $wpdb;
		$ids = array();
		$ids[0] = $order_id;
		$wpdb->netsuite_order_queue = $wpdb->prefix . 'options';

		$value = serialize( $ids );

		$removefromdb = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->netsuite_order_queue WHERE option_value=%s", $value ) );

		if ( is_multisite() ) {
			$wpdb->netsuite_order_queue_site_meta_table = $wpdb->prefix . 'sitemeta';
			$removefromdb = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->netsuite_order_queue_site_meta_table WHERE meta_value=%s", $value ) );

		}
	}
}

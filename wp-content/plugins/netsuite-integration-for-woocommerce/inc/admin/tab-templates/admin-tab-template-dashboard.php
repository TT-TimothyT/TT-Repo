<?php
require_once TMWNI_DIR . 'inc/common.php';


class TMWNI_Admin_Dashboard {
	public $netsuiteCommonClient;
	public $total_customer_sync;
	public $order_sync;
	public $inventory_update_time;

	public function __construct() {
		$this->netsuiteCommonClient = new CommonIntegrationFunctions();
		if ( TMWNI_Settings::areCredentialsDefined() ) {
			$customer_sync = $this->netsuiteCommonClient->getNetsuiteCustomerSync();
			$customer_sync_count = count( $customer_sync );
			$guest_customer_sync = $this->netsuiteCommonClient->getNetsuiteGuestCustomerSync();
			$guest_customer_sync_count = count( $guest_customer_sync );
			$this->total_customer_sync = $customer_sync_count + $guest_customer_sync_count;
			$order_syncc = $this->netsuiteCommonClient->getNetsuiteOrderSync();
			$this->order_sync = count( $order_syncc );
			$this->inventory_update_time = get_option( 'ns_woo_inventory_update' );
		} else {
			$this->total_customer_sync = '';
			$this->order_sync = '';
			$this->inventory_update_time = '';
		}
	}

	// Getter method for total_customer_sync
	public function get_total_customer_sync() {
		return $this->total_customer_sync;
	}

	// Getter method for order_sync
	public function get_order_sync() {
		return $this->order_sync;
	}

	// Getter method for inventory_update_time
	public function get_inventory_update_time() {
		return $this->inventory_update_time;
	}
}
$dashboard = new TMWNI_Admin_Dashboard();

?>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-4">
			<div class="dashboard-view-boxs">
				<div class="inner-box">
					<i class="glyphicon glyphicon-user" aria-hidden="true"></i>
					<p><span><?php esc_attr_e( $dashboard->get_total_customer_sync() ); ?></span> Customer(s) Synced </p>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="dashboard-view-boxs">
				<div class="inner-box">
					<i class="glyphicon glyphicon-folder-close" aria-hidden="true"></i>
					<p><span><?php esc_attr_e( $dashboard->get_order_sync() ); ?></span> Order(s) Synced </p>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="dashboard-view-boxs">
				<div class="inner-box">
					<i class="glyphicon glyphicon-th-list" aria-hidden="true"></i>
					<p>Inventory last updated on <?php esc_attr_e( $dashboard->get_inventory_update_time() ); ?></p>

				</div>
			</div>
		</div>
	</div>
	<div class="row" style="display: block;">
		<div class="col-md-12">
			<div id="autoSyncTabledcantainer">
			</div>
		</div>
	</div>
</div>
<button id="cleardashboardlogs" class="btn btn-danger" value="clearDashboardLogs">Clear All Logs</button>

<table class="dashboard-form-table dataTable" id="dashboardList" style="width: 1200px;">
	<thead>
		<tr role="row">
			<th>ID</th>
			<th>Order Id</th>
			<th>Order Date</th>
			<th>View Order On Netsuite</th>
			<th>Order Status</th>
			<th>Action</th>
		</tr>
	</thead>
</table>


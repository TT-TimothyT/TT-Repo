<?php
/**
 * Plugin Name: NetSuite Integration for WooCommerce
 * Plugin URI: https://woocommerce.com/products/netsuite-integration-for-woocommerce/
 * Description: Plugin will be used to add/update WooCommerce orders and customers to NetSuite .
 * Version: 1.6.3
 * Author: Techmarbles
 * Author URI: https://techmarbles.com/
 * Text Domain: TMWNI
 * WC requires at least: 2.2
 * WC tested up to: 8.8
 * Woo: 8277071:774402b21708c84be5bee56906b069f1

 */

// Debugging function
if (!function_exists('pr')) {
	function pr($data) {
		echo '<pre>';
		print_r($data);
		echo '</pre>';
	}
}

defined('ABSPATH') || exit();

// Include required functions
if (!function_exists('woothemes_queue_update')) {
	require_once plugin_dir_path(__FILE__) . 'woo-includes/woo-functions.php';
}

// Plugin updates
woothemes_queue_update(plugin_basename(__FILE__), '774402b21708c84be5bee56906b069f1', '8277071');

// Check if WooCommerce is active
if (!is_woocommerce_active()) {
	// Deactivate the plugin if WooCommerce is not active
	add_action('admin_init', 'deactivate_tm_netsuite_integration_plugin');
	return;
}

add_action('admin_init', 'tm_netsuite_integration_redirect_after_activation');

function tm_netsuite_integration_redirect_after_activation() {
	if (get_transient('_tm_netsuite_integration_redirect_after_activation')) {
		delete_transient('_tm_netsuite_integration_redirect_after_activation');

		if (is_network_admin() || wp_doing_ajax() || wp_doing_cron()) {
			return;
		}

		wp_safe_redirect(admin_url('admin.php?page=tmwni&tab=general_settings'));
		exit;
	}
}


// Initialize the plugin
add_action('plugins_loaded', 'init_tm_netsuite_integration', 1);

function init_tm_netsuite_integration() {
	// Define the plugin version
	$plugin_data = get_file_data(__FILE__, ['Version' => 'Version'], false);
	$plugin_version = isset($plugin_data['Version'])
		? $plugin_data['Version']
		: '1.6.0';
	define('WC_TM_NETSUITE_INTEGRATION_INIT_VERSION', $plugin_version);

	// Check if WooCommerce is active in multisite
	if (is_multisite()) {
		$active_plugins = get_site_option('active_sitewide_plugins');
	} else {
		/**
		 * Active plugin hook.
		 *
		 * @since 1.0.0
		 */
		$active_plugins = get_option('active_plugins');
	}
	/**
	 * Check if WooCommerce is not activated and deactivate the plugin
	 *
	 * @since 1.0.0
	 */
	if (
		!isset($active_plugins['woocommerce/woocommerce.php']) &&
		!in_array(
			'woocommerce/woocommerce.php',
			apply_filters('active_plugins', get_option('active_plugins'))
		)
	) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		deactivate_plugins(plugin_basename(__FILE__), true);
		return;
	}

	// Define plugin constants
	define('TMWNI_DIR', plugin_dir_path(__FILE__));
	define('TMWNI_URL', plugin_dir_url(__FILE__));
	define('TMWNI_BASEURL', plugin_basename(__FILE__));

	// Include settings
	require_once TMWNI_DIR . 'inc/tmwni-settings.php';
	$TMWNI_OPTIONS = TMWNI_Settings::getTabSettings();
	$GLOBALS['TMWNI_OPTIONS'] = $TMWNI_OPTIONS;
	require_once plugin_dir_path(__FILE__) . 'inc/tmAutoloader.php';

	// Include admin functionality if in the admin area
	if (is_admin()) {
		require_once TMWNI_DIR . 'inc/admin/admin-loader.php';
	}

	// Load the main file
	if (!empty($TMWNI_OPTIONS)) {
		require_once plugin_dir_path(__FILE__) . 'inc/loader.php';
		$GLOBALS['TMWNI_Loader'] = TMWNI_Loader(); // Global for backwards compatibility

	}
	/**
	 * Hook for Netsuite loaded
	 *
	 * @since 1.0.0
	 */
	do_action('tm_netsuite_loaded');
}

// Deactivate the plugin if WooCommerce is not active
function deactivate_tm_netsuite_integration_plugin() {
	deactivate_plugins(plugin_basename(__FILE__));
}

// Run the install scripts upon plugin activation
register_activation_hook(__FILE__, 'install_tm_netsuite_integration_plugin');

function install_tm_netsuite_integration_plugin() {

	// Check if SOAPClient class exists
	if (!class_exists('SOAPClient')) { ?>
		<div class="notice notice-warning is-dismissible">
			<p>PHP SOAP Extension is not enabled on your server. <a target="_blank" href="https://www.php.net/manual/en/soap.setup.php">Know more</a></p>
		</div>
		<?php 
		die();}

	set_transient('_tm_netsuite_integration_redirect_after_activation', true, 30);

	if (is_multisite()) {
		$active_plugins = get_site_option('active_sitewide_plugins');
		$site_ids = get_sites(['fields' => 'ids']);

		// Loop through each site and create the directory
		foreach ($site_ids as $site_id) {
			switch_to_blog($site_id);

			$uploads_dir = wp_upload_dir();
			$plugin_folder = $uploads_dir['basedir'] . 'TM-NetSuite';

			// Check if the folder already exists
			if (!is_dir($plugin_folder)) {
				wp_mkdir_p($plugin_folder);
			}

			restore_current_blog();
		}
	} else {
		/**
		 * Check if WooCommerce is active in multisite.
		 *
		 * @since 1.0.0
		 */
		$active_plugins = get_option('active_plugins');
		$uploads_dir = wp_upload_dir();
		$plugin_folder = trailingslashit($uploads_dir['basedir']) . 'TM-NetSuite';

		// Check if the folder already exists
		if (!is_dir($plugin_folder)) {
			wp_mkdir_p($plugin_folder);
		}
	}
	/**
	 * Check if WooCommerce is activated and create logs table
	 *
	 * @since 1.0.0
	 */
	if (
		isset($active_plugins['woocommerce/woocommerce.php']) ||
		in_array(
			'woocommerce/woocommerce.php',
			apply_filters('active_plugins', get_option('active_plugins'))
		)
	) {
		global $wpdb;
		$charset_collate = '';

		if (!empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if (!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		// Create log database table
		$table_name = $wpdb->prefix . 'tm_woo_netsuite_logs';
		if (
			$wpdb->get_var(
				$wpdb->prepare('show tables like %s', $table_name)
			) != $table_name
		) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$sql = "CREATE TABLE `$table_name` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `operation` varchar(100) NOT NULL,
                `status` tinyint(4) NOT NULL,
                `notes` text NOT NULL,
                `woo_object_id` int(11) NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;";
			dbDelta($sql);
		}

		$dashboard_table_name =
			$wpdb->prefix . 'tm_woo_netsuite_auto_sync_order_status';

		// Check if the table is empty
		if (
			$wpdb->get_var(
				$wpdb->prepare('show tables like %s', $dashboard_table_name)
			) != $dashboard_table_name
		) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$sql = "CREATE TABLE `$dashboard_table_name` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `operation` varchar(100) NOT NULL,
                `status` tinyint(4) NOT NULL,
                `notes` text NULL,
                `woo_object_id` int(11) NOT NULL,
                `ns_order_internal_id` int(11) NULL,
                `ns_order_status` varchar(100) NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;";
			dbDelta($sql);
		}

		//create folder for logs file
		$uploads_dir = wp_upload_dir();
		$plugin_folder = $uploads_dir['basedir'] . '/TM-NetSuite';
		// Check if the folder already exists
		if (!is_dir($plugin_folder)) {
			wp_mkdir_p($plugin_folder);
		}
	} else {
		die(
			'Your store needs to be WooCommerce-ready to activate this plugin.'
		);
	}


}

// Declare compatibility with custom order tables if the class
add_action('before_woocommerce_init', function () {
	if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
});

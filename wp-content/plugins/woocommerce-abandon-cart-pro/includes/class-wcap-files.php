<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * Class for Including plugin files.
 *
 * @author   Tyche Softwares
 * @package  WCAP/Files
 * @category Classes
 */

/**
 * Class WCAP_Files.
 *
 * @since 5.0
 * @since Updated 8.23.0
 */
class WCAP_Files {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->include_files();
		$this->include_admin_files();
	}

	/**
	 * Include the plugin files for Admin.
	 *
	 * @since 5.0
	 */
	public function include_admin_files() {
		self::include_file( WCAP_PLUGIN_PATH . '/includes/admin/class-wcap-admin-files.php' );
		new WCAP_Admin_Files();
	}

	/**
	 * Include the dependent plugin files.
	 *
	 * @since 5.0
	 * @since Updated 8.23.0
	 */
	public static function include_files() {

		$files = array(
			'/includes/connectors/class-wcap-connector.php',
			'/includes/connectors/class-wcap-connectors-common.php',
			'/includes/connectors/class-wcap-display-connectors.php',
			'/includes/connectors/class-wcap-call.php',
			'/includes/class-wcap-integrations.php',
			'/includes/admin/wcap_edd.php',
			'/includes/wcap_load_hooks.php',
			'/includes/admin/class-wcap-print-and-csv.php',
			'/includes/wcap_load_scripts.php',
			'/includes/admin/class-wcap-orders-listing.php',
		);

		foreach ( $files as $file ) {
			self::include_file( WCAP_PLUGIN_PATH . $file );
		}

		// This condition confirm that the lite plugin active, so we need to perform further action.
		if ( in_array( 'woocommerce-abandoned-cart/woocommerce-ac.php', (array) get_option( 'active_plugins', array() ) ) ||
				( isset( $_GET ['wcap_plugin_link'] ) && 'wcap-update' == $_GET ['wcap_plugin_link'] ) ) {
					self::include_file( WCAP_PLUGIN_PATH . '/includes/admin/wcap_import_lite_to_pro.php' );
		}

		self::include_file( WCAP_PLUGIN_PATH . '/includes/wcap_common.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/admin/wcap_add_settings.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/admin/wcap_dashboard_advanced.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/classes/class_wcap_dashboard_adv_report_action.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/wcap_functions.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/wcap-email-functions.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/wcap_ajax.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/wcap_process_base.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/admin/wcap_actions_handler.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/class-wcap-webhooks.php' );

		self::include_file( WCAP_PLUGIN_PATH . '/includes/admin/wcap_sms_reminders.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/admin/wcap_sms_settings.php' );
		$is_admin = is_admin();

		/**
		 * Load front side files.
		 */
		if ( false === $is_admin ) {
			self::wcap_load_front_side_files();
		}

		if ( true === $is_admin ) {
			self::include_file( WCAP_PLUGIN_PATH . '/includes/admin/class-wcap-email-template-list.php' );
		}

		self::include_file( WCAP_PLUGIN_PATH . '/includes/classes/class_wcap_aes.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/classes/class_wcap_aes_ctr.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/classes/class-wcap-send-manual-email.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/admin/wcap_admin_recovery.php' );

		/**
		 * Load cart details file.
		 */
		$wcap_auto_cron = get_option( 'wcap_use_auto_cron' );
		if ( isset( $wcap_auto_cron ) && $wcap_auto_cron != false && '' != $wcap_auto_cron ) {
			self::include_file( WCAP_PLUGIN_PATH . '/cron/class-wcap-send-email-using-cron.php' );
			self::include_file( WCAP_PLUGIN_PATH . '/includes/fb-recovery/fb-recovery.php' );
		}

		// Follow-up emails file.
		self::include_file( WCAP_PLUGIN_PATH . '/cron/class-wcap-follow-up-emails.php' );

		// Admin Notifications.
		self::include_file( WCAP_PLUGIN_PATH . '/cron/class-wcap-admin-notification.php' );
		/**
		 * Load FB Messenger Files
		 */
		self::include_file( WCAP_PLUGIN_PATH . '/includes/fb-recovery/fb-recovery.php' );
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php' ) ) {
			self::include_file( WCAP_PLUGIN_PATH . '/includes/aelia-currency-switcher/wcap_aelia_currency_switcher.php' );
		}
		/**
		* Files needed for Components folder
		*/
		self::include_file( WCAP_PLUGIN_PATH . '/includes/component/license-active-notice/ts-active-license-notice.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/component/woocommerce-check/ts-woo-active.php' );

		self::include_file( WCAP_PLUGIN_PATH . '/includes/component/plugin-deactivation/class-tyche-plugin-deactivation.php' );
		new Tyche_Plugin_Deactivation(
			array(
				'plugin_name'       => 'Abandoned Cart Pro for WooCommerce',
				'plugin_base'       => 'woocommerce-abandon-cart-pro/woocommerce-ac.php',
				'script_file'       => WCAP_PLUGIN_URL . '/assets/js/admin/plugin-deactivation.js',
				'plugin_short_name' => 'acp_pro',
				'version'           => WCAP_PLUGIN_VERSION,
				'plugin_locale'     => 'woocommerce-ac',
			)
		);
		self::include_file( WCAP_PLUGIN_PATH . '/includes/component/plugin-tracking/class-tyche-plugin-tracking.php' );
		new Tyche_Plugin_Tracking(
			array(
				'plugin_name'       => 'Abandoned Cart Pro for WooCommerce',
				'plugin_locale'     => 'woocommerce-ac',
				'plugin_short_name' => 'wcap',
				'version'           => WCAP_PLUGIN_VERSION,
				'blog_link'         => 'https://www.tychesoftwares.com/docs/docs/abandoned-cart-pro-for-woocommerce-new/abandoned-cart-pro-for-woocommerce-usage-tracking-feature/',
			)
		);
		self::include_file( WCAP_PLUGIN_PATH . '/includes/class-wcap-data-tracking.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/component/faq-support/ts-faq-support.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/wcap_all_component.php' );

		// Load file for Email Verification service.
		self::include_file( WCAP_PLUGIN_PATH . '/includes/class-wcap-email_verification.php' );
		do_action( 'wcap_after_load_files' );
	}

	/**
	 * Include File.
	 *
	 * @param string $file File to be included.
	 * @since 5.0
	 */
	public static function include_file( $file ) {
		if ( file_exists( $file ) ) {
			include_once $file;
		}
	}

	/**
	 * Load files needed for the front end.
	 *
	 * @since 5.0
	 */
	public static function wcap_load_front_side_files() {
		self::include_file( WCAP_PLUGIN_PATH . '/includes/frontend/wcap_cart_updated.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/frontend/wcap_order_received.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/frontend/wcap_populate_cart_of_user.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/frontend/wcap_on_placed_order.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/frontend/wcap_coupon_code.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/frontend/class-wcap-tracking-msg.php' );
		self::include_file( WCAP_PLUGIN_PATH . '/includes/wcap_tiny_url.php' );
	}
}

<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * @package      Abandoned-Cart-Pro-for-WooCommerce
 * @copyright    Copyright (C) 2012-2022, Tyche Softwares - https://support.tychesoftwares.com/help/2285384554
 * @link         https://www.tychesoftwares.com
 * @since        1.0
 * @since        Updated 9.7.1
 *
 * @wordpress-plugin
 * Plugin Name:  Abandoned Cart Pro for WooCommerce
 * Plugin URI:   https://www.tychesoftwares.com/products/woocommerce-abandoned-cart-pro-plugin/
 * Description:  This plugin captures abandoned carts by logged-in users and guest users. It allows to create multiple email templates to be sent at fixed intervals. Thereby reminding customers about their abandoned orders & resulting in increased sales by completing those orders. Go to <strong>WooCommerce -> <a href="admin.php?page=woocommerce_ac_page">Abandoned Carts</a> </strong>to get started.
 * Version:      9.7.1
 * Author:       Tyche Softwares
 * Author URI:   http://www.tychesoftwares.com
 * Text Domain:  woocommerce-ac
 * Requires PHP: 7.4 or higher
 * WC requires at least: 4.0.0
 * WC tested up to: 8.6.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * This is the URL our updater / license checker pings. This should be the URL of the site with EDD installed.
 * IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system.
 */
define( 'EDD_SL_STORE_URL_AC_WOO', 'https://www.tychesoftwares.com/' );

/**
 * The name of your product. This is the title of your product in EDD and should match the download title in EDD exactly.
 * IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system.
 */
define( 'EDD_SL_ITEM_NAME_AC_WOO', 'Abandoned Cart Pro for WooCommerce' );

if ( ! class_exists( 'EDD_AC_WOO_Plugin_Updater' ) ) {
	// load our custom updater if it doesn't already exist.
	include dirname( __FILE__ ) . '/plugin-updates/EDD_AC_WOO_Plugin_Updater.php';
}
/**
 * Retrieve our license key from the DB
 */
$license_key = trim( get_option( 'edd_sample_license_key_ac_woo' ) );
/**
 * Setup the updater
 */
$edd_updater = new EDD_AC_WOO_Plugin_Updater(
	EDD_SL_STORE_URL_AC_WOO,
	__FILE__,
	array(
		'version'   => '9.7.1',                     // current version number.
		'license'   => $license_key,                // license key (used get_option above to retrieve from DB).
		'item_name' => EDD_SL_ITEM_NAME_AC_WOO,     // name of this plugin.
		'author'    => 'Ashok Rane',                // author of this plugin.
	)
);

// Deactivate the Lite plugin if active. This is needed to avoid conflicts.
if ( in_array( 'woocommerce-abandoned-cart/woocommerce-ac.php', (array) get_option( 'active_plugins', array() ) ) ) { //phpcs:ignore
	add_option( 'wcap_migrated_from_lite', true );
	deactivate_plugins( 'woocommerce-abandoned-cart/woocommerce-ac.php' );
}

require_once 'includes/wcap_activate_plugin.php';
require_once 'includes/wcap_update_check.php';
require_once 'includes/wcap_default_settings.php';

/**
 * Woocommerce_Abandon_Cart class
 *
 * It will call all the functions for the file inclusion, global variable and action & filter
 *
 * @since 1.0
 */
final class Woocommerce_Abandon_Cart {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected static $plugin_version = '9.7.1';

	/**
	 * Plugin Name.
	 *
	 * @var string
	 */
	protected static $plugin_name = 'Abandoned Cart Pro for WooCommerce';

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	protected static $plugin_url = 'https://www.tychesoftwares.com/';

	/**
	 * DEV MODE.
	 *
	 * Sets the plugin base to either development ( TRUE ) or production ( FALSE ).
	 *
	 * @var boolean
	 */
	protected static $dev_mode = true;

	/**
	 * The single instance of the class.
	 *
	 * @var Woocommerce_Booking
	 */
	protected static $instance = null;

	/**
	 * Default constructor
	 *
	 * @since 5.17.0
	 */
	private static function setup() {

		if ( ! self::check_is_woo_active() ) {

			add_action(
				'admin_notices',
				function() {
					$class   = 'notice notice-error';
					$message = __( 'We have noticed that WooCommerce plugin is not active. Please activate it to use ' . EDD_SL_ITEM_NAME_AC_WOO. " plugin" ); //phpcs:ignore
					printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); //phpcs:ignore
				}
			);
			if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
				unset( $_GET['activate'] ); //phpcs:ignore
			}
			add_action(
				'plugins_loaded',
				function() {
					deactivate_plugins( 'woocommerce-abandon-cart-pro/woocommerce-ac.php' );
				}
			);

		} else {
			add_action(
				'before_woocommerce_init',
				function() {
					if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'orders_cache', __FILE__, true );
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', __FILE__, true );
					}
				},
				999
			);
				self::wcap_declare_variable();
				// Load the Files.
				add_action( 'plugins_loaded', array( __CLASS__, 'wcap_load_files' ), 5 );
				// Initialize settings.
				register_activation_hook( __FILE__, array( 'Wcap_Activate_Plugin', 'wcap_activate' ) );
				add_action( 'plugins_loaded', array( __CLASS__, 'wcap_load_hooks' ), 6 );
				spl_autoload_register( array( __CLASS__, 'loadclass' ) );
				// Register deactivation hook.
				register_deactivation_hook( __FILE__, array( 'Woocommerce_Abandon_Cart', 'wcap_deactivate' ) );
		}
	}

	/**
	 * Retrieve the instance of the class and ensures only one instance is loaded or can be loaded.
	 *
	 * @return Woocommerce_Abandon_Cart
	 */
	public static function instance() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Woocommerce_Abandon_Cart ) ) {
			self::$instance = new Woocommerce_Abandon_Cart();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Declare the common variables and the constants needed for the plugin.
	 *
	 * @since 5.0
	 */
	public static function wcap_declare_variable() {
		global $one_hour, $three_hours, $six_hours, $twelve_hours, $one_day, $one_week, $duration_range_select, $start_end_dates, $ACUpdateChecker; //phpcs:ignore

		$one_hour        = 60 * 60;
		$three_hours     = 3 * $one_hour;
		$six_hours       = 6 * $one_hour;
		$twelve_hours    = 12 * $one_hour;
		$one_day         = 24 * $one_hour;
		$one_week        = 7 * $one_day;
		$ACUpdateChecker = '9.7.1'; //phpcs:ignore

		$duration_range_select = array(
			'yesterday'      => __( 'Yesterday', 'woocommerce-ac' ),
			'today'          => __( 'Today', 'woocommerce-ac' ),
			'last_seven'     => __( 'Last 7 days', 'woocommerce-ac' ),
			'last_fifteen'   => __( 'Last 15 days', 'woocommerce-ac' ),
			'last_thirty'    => __( 'Last 30 days', 'woocommerce-ac' ),
			'last_ninety'    => __( 'Last 90 days', 'woocommerce-ac' ),
			'last_year_days' => __( 'Last 365', 'woocommerce-ac' ),
		);

		$start_end_dates = array(
			'yesterday'      => array(
				'start_date' => date( 'd M Y', ( current_time( 'timestamp' ) - 24 * 60 * 60 ) ), //phpcs:ignore
				'end_date'   => date( 'd M Y', ( current_time( 'timestamp' ) - 7 * 24 * 60 * 60 ) ), //phpcs:ignore
			),
			'today'          => array(
				'start_date' => date( 'd M Y', ( current_time( 'timestamp' ) ) ), //phpcs:ignore
				'end_date'   => date( 'd M Y', ( current_time( 'timestamp' ) ) ), //phpcs:ignore
			),
			'last_seven'     => array(
				'start_date' => date( 'd M Y', ( current_time( 'timestamp' ) - 7 * 24 * 60 * 60 ) ), //phpcs:ignore
				'end_date'   => date( 'd M Y', ( current_time( 'timestamp' ) ) ), //phpcs:ignore
			),
			'last_fifteen'   => array(
				'start_date' => date( 'd M Y', ( current_time( 'timestamp' ) - 15 * 24 * 60 * 60 ) ), //phpcs:ignore
				'end_date'   => date( 'd M Y', ( current_time( 'timestamp' ) ) ), //phpcs:ignore
			),
			'last_thirty'    => array(
				'start_date' => date( 'd M Y', ( current_time( 'timestamp' ) - 30 * 24 * 60 * 60 ) ), //phpcs:ignore
				'end_date'   => date( 'd M Y', ( current_time( 'timestamp' ) ) ), //phpcs:ignore
			),
			'last_ninety'    => array(
				'start_date' => date( 'd M Y', ( current_time( 'timestamp' ) - 90 * 24 * 60 * 60 ) ), //phpcs:ignore
				'end_date'   => date( 'd M Y', ( current_time( 'timestamp' ) ) ), //phpcs:ignore
			),
			'last_year_days' => array(
				'start_date' => date( 'd M Y', ( current_time( 'timestamp' ) - 365 * 24 * 60 * 60 ) ), //phpcs:ignore
				'end_date'   => date( 'd M Y', ( current_time( 'timestamp' ) ) ), //phpcs:ignore
			),
		);

			/**
			 * Define The constants for plugin table names and other constants.
			 */
			self::wcap_define_constants_for_table_and_other();
	}

		/**
		 * Function for definining WKAP constants.
		 *
		 * @param string $variable Constant which is to be defined.
		 * @param string $value Valueof the Constant.
		 *
		 * @since 8.23.0
		 */
	public static function define( $variable, $value ) {
		if ( ! defined( $variable ) ) {
			define( $variable, $value );
		}
	}

		/**
		 * Define The constants for plugin table names and other constants.
		 *
		 * @globals mixed $wpdb
		 * @since 5.0
		 */
	public static function wcap_define_constants_for_table_and_other() {

			global $wpdb;
			self::define( 'WCAP_PLUGIN_VERSION', self::$plugin_version );
			self::define( 'WCAP_ABANDONED_CART_HISTORY_TABLE', $wpdb->prefix . 'ac_abandoned_cart_history' );
			self::define( 'WCAP_GUEST_CART_HISTORY_TABLE', $wpdb->prefix . 'ac_guest_abandoned_cart_history' );
			self::define( 'WCAP_EMAIL_TEMPLATE_TABLE', $wpdb->prefix . 'ac_email_templates' );
			self::define( 'WCAP_NOTIFICATION_TEMPLATES_TABLE', $wpdb->prefix . 'ac_notification_templates' );
			self::define( 'WCAP_EMAIL_CLICKED_TABLE', $wpdb->prefix . 'ac_link_clicked_email' );
			self::define( 'WCAP_EMAIL_OPENED_TABLE', $wpdb->prefix . 'ac_opened_emails' );
			self::define( 'WCAP_NOTIFICATION_OPENED_TABLE', $wpdb->prefix . 'ac_opened_notifications' );
			self::define( 'WCAP_LINK_CLICKED_TABLE', $wpdb->prefix . 'ac_link_clicked_notifications' );
			self::define( 'WCAP_EMAIL_SENT_HISTORY_TABLE', $wpdb->prefix . 'ac_sent_history' );
			self::define( 'WCAP_PLUGIN_FILE', __FILE__ );
			self::define( 'WCAP_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
			self::define( 'WCAP_ADMIN_URL', admin_url( 'admin.php' ) );
			self::define( 'WCAP_ADMIN_AJAX_URL', admin_url( 'admin-ajax.php' ) );
			self::define( 'WCAP_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			self::define( 'WCAP_NOTIFICATIONS', $wpdb->prefix . 'ac_notifications' );
			self::define( 'WCAP_NOTIFICATIONS_META', $wpdb->prefix . 'ac_notifications_meta' );
			self::define( 'WCAP_TINY_URLS', $wpdb->prefix . 'ac_tiny_urls' );
			self::define( 'WCAP_ATC_RULES_TABLE', $wpdb->prefix . 'ac_atc_rules' );
			self::define( 'WCAP_AC_STATS', $wpdb->prefix . 'ac_statistics' );
			self::define( 'WCAP_INCLUDE_PATH', WCAP_PLUGIN_PATH . '/includes/' );
	}

		/**
		 * Load all files needed for the plugin.
		 *
		 * @globals string $pagenow Name of the current page
		 * @since 5.0
		 */
	public static function wcap_load_files() {
		require_once WCAP_INCLUDE_PATH . 'class-wcap-files.php';
		new WCAP_Files();
	}

		/**
		 * It will load all the hooks needed for the plugin.
		 *
		 * @since 5.0
		 */
	public static function wcap_load_hooks() {
		Wcap_Load_Hooks::wcap_load_hooks_and_filters();
		do_action( 'wcap_after_load_hooks' );
	}

		/**
		 * Actions to be run when deactivating the plugin.
		 *
		 * @since 9.0
		 */
	public static function wcap_deactivate() {
		// Send reminders.
		if ( false !== as_next_scheduled_action( 'woocommerce_ac_send_email_action' ) ) {
			as_unschedule_action( 'woocommerce_ac_send_email_action' );
		}
		$next_scheduled = wp_next_scheduled( 'wcap_ts_tracker_send_event' );
		if ( $next_scheduled ) {
			wp_unschedule_event( $next_scheduled, 'wcap_ts_tracker_send_event' );
		}
	}

		/**
		 * Autoloader for classes
		 *
		 * @param class $class_name Name of the class.
		 *
		 * @since 9.0
		 */
	public static function loadclass( $class_name ) {
			$ds          = DIRECTORY_SEPARATOR;
			$plugin_dir  = realpath( plugin_dir_path( __FILE__ ) ) . $ds;
			$directories = array(
				'includes' . $ds,
				'includes' . $ds . 'admin' . $ds,
				'includes' . $ds . 'classes' . $ds,
				'includes' . $ds . 'component' . $ds,
				'includes' . $ds . 'frontend' . $ds,
				'includes' . $ds . 'libraries' . $ds,
				'includes' . $ds . 'models' . $ds,
			);
			$classpath   = trim( $class_name, 'AC\\' );
			$folderarray = explode( '\\', $classpath );
			$filename    = strtolower( str_replace( '_', '-', array_pop( $folderarray ) ) ) . '.php';
			$filefolder  = strtolower( implode( $ds, $folderarray ) ) . $ds;

			foreach ( $directories as $directory ) {
				$fullpath = $plugin_dir . $directory . $filefolder . $filename;
				if ( file_exists( $fullpath ) ) {
					require_once $fullpath;
					// return.
				}
			}
	}
		/**
		 * Check if woocommerce is active for the site or network
		 */
	public static function check_is_woo_active() {
		$is_active = false;
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$network_check = true;
		$network_check = apply_filters( 'wcap_do_network_check', $network_check );
		if ( $network_check && is_multisite() ) {
			$is_active = is_plugin_active_for_network( 'woocommerce/woocommerce.php' );
		} else {
			$is_active = is_plugin_active( 'woocommerce/woocommerce.php' );
		}
		return $is_active;
	}
}

/**
 * Returns the main instance of the class.
 *
 * @return Woocommerce_Booking
 */
function WCAP() { //phpcs:ignore
	return Woocommerce_Abandon_Cart::instance();
}

WCAP();

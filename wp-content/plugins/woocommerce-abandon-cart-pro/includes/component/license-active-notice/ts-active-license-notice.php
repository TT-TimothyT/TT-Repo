<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Active License Notice class
 *
 * @class wcap_active_license_notice
 * @version	1.0
 */

class wcap_active_license_notice {
	
	/**
	 * @var string The name of the plugin
	 * @access public
	 */
	public $plugin_name = '';

	/**
	 * @var string The option name where the license key is stored
	 * @access public
	 */
	public $plugin_license_option = '';

	/**
	 * Store the path of the license page.
	 * @var string Path of the license page.
	 * @access public
	 */
	public $ts_license_page_url = '';

	/**
	 * Store the plguin locale.
	 * @var string Used Plugin locale.
	 * @access public
	 */
	public $ts_locale = '';

	/**
	 * Check if license expires.
	 *
	 * @var bool if license expires.
	 * @access public
	 */
	public $plugin_license_expires = '';
	
	/**
	 *  Default Constructor
	 * 
	 * @access public
	 * @since  7.7
	 */
	public function __construct( $ts_plugin_name = '', $ts_license_option_name = '', $ts_license_page_url = '', $ts_locale = '', $ts_license_expires = '') {
		$this->plugin_name           = $ts_plugin_name;
		$this->plugin_license_option = $ts_license_option_name;
		$this->ts_license_page_url   = $ts_license_page_url;
		$this->ts_locale             = $ts_locale;

		$this->plugin_license_expires = $ts_license_expires;

		if ( '' != $this->plugin_license_option ) {
			add_action( 'admin_init', array( &$this, 'ts_check_if_license_active' ) );
			add_action( 'admin_init', array( &$this, 'ts_check_if_license_expired' ) );
		}

		
	}

	/* Check if the license key is active for the plugin. If not active a notice will be displayed
 	* 
 	* @access public
 	* @since 7.7
 	*/
	public function ts_check_if_license_active() {
	 	if ( ! $this->ts_check_active_license() ) {
            add_action( 'admin_notices', array( &$this, 'ts_license_active_notice' ) );
	    }
	}

	/** 
	 * Returns the result of the license key
 	 * 
 	 * @access public
 	 * @return bool
 	 * @since  7.7
 	 */
	public function ts_check_active_license() {
		$status = get_option( $this->plugin_license_option );
	    if( false !== $status && 'valid' == $status ) {
	        return true;
	    } else {
	        return false;
	    }
	}
	/**
	 *  Display the notice if the license key is not active
 	 * 
 	 * @access public
 	 * @since 7.7
 	 */

	public function ts_license_active_notice() {
		global $current_screen;
		$current_screen = get_current_screen();
		if ( ( method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor() )
			|| ( function_exists('is_gutenberg_page') && is_gutenberg_page() ) ) {
			return;
		}
		$class = 'notice notice-error';
	    $message = __( 'We have noticed that the license for <b>' . $this->plugin_name . '</b> plugin is not active. To receive automatic updates & support, please activate the license <a href= "'. $this->ts_license_page_url .'">here</a>.', "'. $this->ts_locale .'"  );
	    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}

	/**
	 * Check if the license has expired
	 *
	* @access public
	* @return bool
	* @since  7.7
	*/

	public function ts_check_if_license_expired() {
		if ( $this->ts_check_active_license() && $this->ts_check_license_expired() ) {
			add_action( 'admin_notices', array( &$this, 'ts_license_expiry_notice' ) );
		}
	}

	/**
	 * Returns the result of the license expiry
	 *
	* @access public
	* @return bool
	* @since  7.7
	*/


	public function ts_check_license_expired() {
		$plugin_license_expires = get_option( $this->plugin_license_expires );

		if ( 'lifetime' === $plugin_license_expires ) {
			return false;
		}

		$expiry_date = new DateTime( $plugin_license_expires );
		$today = new DateTime( date( 'Y-m-d' ) );

		if ( $today > $expiry_date ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Display the notice if the license key is expired
	 * 
	* @access public
	* @since 7.7
	*/

	public function ts_license_expiry_notice() {
		$current_screen = get_current_screen();
		if ( ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() )
			|| ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) ) {
			return;
		}
		$class = 'notice notice-error';
		$message = __( 'We have noticed that the license for <b>' . $this->plugin_name . '</b> plugin is expired. To receive automatic updates & support, please renew the license  <a href="https://www.tychesoftwares.com/store/premium-plugins/woocommerce-abandoned-cart-pro/" target="_blank">here</a> and update the license <a href= "'. $this->ts_license_page_url .'">here</a>.', "'. $this->ts_locale .'"  );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}

}




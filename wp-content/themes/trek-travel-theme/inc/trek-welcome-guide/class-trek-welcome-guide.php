<?php
/**
 * Trek Welcome Guide
 *
 * Handles the welcome guide functionality using Shepherd.js
 *
 * @package Trek_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Trek_Welcome_Guide class.
 */
class Trek_Welcome_Guide {

	/**
	 * Singleton instance
	 *
	 * @var Trek_Welcome_Guide
	 */
	private static $instance = null;

	/**
	 * Get instance of the class
	 *
	 * @return Trek_Welcome_Guide
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function hooks() {
		// Front-end scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// AJAX handler for saving tour status
		add_action( 'wp_ajax_tt_save_welcome_tour_status', array( $this, 'save_tour_status' ) );
		add_action( 'wp_ajax_nopriv_tt_save_welcome_tour_status', array( $this, 'save_tour_status' ) );
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		// Only load on specific pages
		if ( $this->should_load_tour() ) {
			// Register Shepherd base library
			wp_enqueue_style( 'shepherd-css', get_template_directory_uri() . '/assets/css/shepherd.css' );
			wp_enqueue_script( 'shepherd-js', get_template_directory_uri() . '/assets/js/shepherd.min.js', array(), null, true );
			
			// Register our custom tour guide
			wp_enqueue_style( 
				'trek-tour-guide', 
				get_template_directory_uri() . '/inc/trek-welcome-guide/assets/trek-tour-guide.css', 
				array(), 
				filemtime( get_template_directory() . '/inc/trek-welcome-guide/assets/trek-tour-guide.css' ) 
			);
			
			// Register page-specific tour scripts
			if ( is_account_page() ) {
				wp_enqueue_script( 
					'trek-account-tour', 
					get_template_directory_uri() . '/inc/trek-welcome-guide/assets/trek-account-tour.js', 
					array( 'jquery', 'shepherd-js' ), 
					filemtime( get_template_directory() . '/inc/trek-welcome-guide/assets/trek-account-tour.js' ), 
					true 
				);
				
				// Pass variables to the script
				$current_user = wp_get_current_user();
				wp_localize_script( 'trek-account-tour', 'trek_tour_JS_obj', array(
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'nonce'          => wp_create_nonce( '_trek_tour_nonce' ),
					'is_logged_in'   => is_user_logged_in(),
					'show_tour'      => ! get_user_meta( get_current_user_id(), 'tt_tour_account_page_seen', true ),
					'user_name'      => $current_user->first_name ? $current_user->first_name : $current_user->display_name,
					'tour_name'      => 'account_page',
					'i18n'           => array(
						'start_tour' => __( 'Start Tour', 'trek-travel-theme' ),
						'not_now'    => __( 'Not Now', 'trek-travel-theme' ),
						'next'       => __( 'Next', 'trek-travel-theme' ),
						'back'       => __( 'Back', 'trek-travel-theme' ),
						'finish'     => __( 'Finish', 'trek-travel-theme' ),
					),
				));
			}
		}
	}

	/**
	 * Check if we should load the tour on current page
	 *
	 * @return bool
	 */
	private function should_load_tour() {
		// Add conditions for tour loading
		if ( is_account_page() ) {
			return true;
		}

		return false;
	}

	/**
	 * Save tour status via AJAX
	 */
	public function save_tour_status() {
		check_ajax_referer( '_trek_tour_nonce', 'nonce' );
		
		$tour_name = sanitize_text_field( $_POST['tour_name'] );
		$user_id   = get_current_user_id();
		
		if ( $user_id > 0 ) {
			update_user_meta( $user_id, 'tt_tour_' . $tour_name . '_seen', true );
		}
		
		wp_send_json_success();
	}
}

// Initialize the class
Trek_Welcome_Guide::get_instance();

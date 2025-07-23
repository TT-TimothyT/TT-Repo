<?php
/**
 * Handles email sending.
 *
 * Creating an email Notification system
 * to Handle orders that don't successfully post to NetSuite.
 *
 * @uses WC_Emails and WC_Email
 *
 * @link https://www.tychesoftwares.com/create-custom-email-templates-woocommerce/
 * @link https://github.com/saranyagokula/notify-pending-emails/
 */

class Trek_Email_Manager {

	/**
	 * Constructor sets up actions
	 */
	public function __construct() {
		
		// Template path.
		define( 'TREK_TEMPLATE_PATH', untrailingslashit( get_template_directory() ) . '/inc/trek-email-manager/templates/' );
		// Hook for when booking creation in NetSuite fails.
		add_action( 'netsuite_booking_failed', array( &$this, 'failed_booking_trigger_email_action' ), 10, 2 );
		// Hook for when booking update in NetSuite fails. TPP failure.
		add_action( 'netsuite_tpp_failed', array( &$this, 'failed_tpp_trigger_email_action' ), 10, 2 );
		// Include the email class files.
		add_filter( 'woocommerce_email_classes', array( &$this, 'trek_init_emails' ) );
		
		// Email Actions - Triggers
		$email_actions = array(
			
			'failed_booking_pending_email',
			'failed_booking_email',
			'failed_tpp_pending_email',
			'failed_tpp_email',
		);

		foreach ( $email_actions as $action ) {
			add_action( $action, array( 'WC_Emails', 'send_transactional_email' ), 10, 10 );
		}
		
		add_filter( 'woocommerce_template_directory', array( $this, 'trek_template_directory' ), 10, 2 );
		
	}
	
	public function trek_init_emails( $emails ) {
		// Include the email class file if it's not included already.
		if ( ! isset( $emails[ 'Failed_Booking_Email' ] ) ) {
			$emails[ 'Failed_Booking_Email' ] = include_once( 'emails/class-failed-booking-email.php' );
		}
		if ( ! isset( $emails[ 'Failed_TPP_Email' ] ) ) {
			$emails[ 'Failed_TPP_Email' ] = include_once( 'emails/class-failed-tpp-email.php' );
		}
		// Include Admin On-hold Email.
		if ( ! isset( $emails[ 'TT_WC_Email_On_Hold_Order' ] ) ) {
			$emails[ 'TT_WC_Email_On_Hold_Order' ] = include_once( 'emails/class-wc-email-on-hold-order.php' );
		}
	
		return $emails;
	}
	
	public function failed_booking_trigger_email_action( $order_id, $ns_response ) {
		// Add an action for our email trigger.
		new WC_Emails();
		do_action( 'failed_booking_pending_email_notification', $order_id, $ns_response );
	}

	public function failed_tpp_trigger_email_action( $order_id, $ns_response ) {
		// Add an action for our email trigger.
		new WC_Emails();
		do_action( 'failed_tpp_pending_email_notification', $order_id, $ns_response );
	}
	
	public function trek_template_directory( $directory, $template ) {
		// Ensure the directory name is correct.
		if ( false !== strpos( $template, '-trek' ) ) {
			return 'dx-trek-email';
		}
	
		return $directory;
	}
	
}

new Trek_Email_Manager();

<?php

/**
 * Send notifications when an entry is updated
 *
 * @since 1.4.0
 * 
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
class GFPGFU_Send_Notification_Entry_Update {

	/**
	 * Notifications that have been triggered
	 *
	 * @since 2.4.2
	 *
	 * @var bool
	 */
	private $notifications_triggered = false;

	/**
	 * GFPGFU_Send_Notification_Entry_Update constructor.
	 *
	 * @since 1.4.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function __construct() {
			
		$this->add_notification_event();

		$this->listen_for_entry_update();

	}

	/**
	 * Add notification events filter
	 *
	 * @since 1.4.0
	 * 
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function add_notification_event() {

		add_filter( 'gform_notification_events', array( $this, 'gform_notification_events' ) );

	}

	/**
	 * Listen for entry update actions
	 *
	 * @since 1.4.0
	 * 
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function listen_for_entry_update() {

		add_action( 'gform_after_update_entry', array( $this, 'gform_after_update_entry' ), 10, 2 );

		add_action( 'gform_post_update_entry', array( $this, 'gform_post_update_entry' ), 10, 2 );


	}

	/**
	 * Add entry updated event to Gravity Forms notification actions
	 *
	 * @since  1.4.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param array $events
	 *
	 * @return array
	 */
	public function gform_notification_events( $events ) {

		$events[ 'entry_update' ] = __( 'Entry is updated', 'gravityplus-gf-utility' );

		return $events;
	}

	/**
	 * Fires when entry is updated in the admin or with GravityView on the frontend
	 *
	 * @since 1.4.0
	 * 
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $form
	 * @param $entry_id
	 */
	public function gform_after_update_entry( $form, $entry_id ) {

		GFP_Utility_AddOn::get_instance()->log_debug( __METHOD__  );

		if ( ! class_exists( 'GFCommon' ) || ! class_exists( 'GFAPI' ) ) {

			return;

		}

		if ( ! $this->notifications_triggered ) {

			$entry = GFAPI::get_entry( $entry_id );

			$this->notifications_triggered = true;

			$this->trigger_notifications( 'entry_update', $form, $entry );

		}
		else {

			GFP_Utility_AddOn::get_instance()->log_debug( 'Entry Update notifications already triggered.' );

		}

	}

	/**
	 * Fires when entry is updated through the API
	 *
	 * @since  1.4.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $entry
	 * @param $original_entry
	 */
	public function gform_post_update_entry( $entry, $original_entry ) {

		GFP_Utility_AddOn::get_instance()->log_debug( __METHOD__  );

		if ( ! class_exists( 'GFCommon' ) || ! class_exists( 'GFAPI' ) ) {

			return;

		}

		if ( ! $this->notifications_triggered ) {

			if ( ! empty( $original_entry ) ) { //this is indicative of partial entry, but not stable

				$form = GFAPI::get_form( $entry[ 'form_id' ] );

				$this->notifications_triggered = true;

				$this->trigger_notifications( 'entry_update', $form, $entry );

			}

		}
		else {

			GFP_Utility_AddOn::get_instance()->log_debug( 'Entry Update notifications already triggered.' );

		}

	}

	/**
	 * Trigger Gravity Forms notifications for an event
	 *
	 * @since  1.4.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param string $id Notification ID
	 * @param array  $form
	 * @param array  $entry
	 */
	function trigger_notifications( $id, $form, $entry ) {

		GFP_Utility_AddOn::get_instance()->log_debug( __METHOD__ . " Sending {$id} notification" );

		$notifications         = GFCommon::get_notifications_to_send( $id, $form, $entry );

		GFP_Utility_AddOn::get_instance()->log_debug( "Notifications: " . print_r( $notifications, true ) );

		$notifications_to_send = array();

		//running through filters that disable notifications
		foreach ( $notifications as $notification ) {

			if ( apply_filters( "gform_disable_notification_{$form['id']}", apply_filters( 'gform_disable_notification', false, $notification, $form, $entry ), $notification, $form, $entry ) ) {
				//skip notifications if it has been disabled by a hook
				continue;
			}

			$notifications_to_send[ ] = $notification[ 'id' ];
		}

		GFCommon::send_notifications( $notifications_to_send, $form, $entry, true, $id );

	}

}

global $gfpgfu_send_notification_entry_update;

$gfpgfu_send_notification_entry_update = new GFPGFU_Send_Notification_Entry_Update();
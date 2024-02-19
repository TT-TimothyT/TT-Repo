<?php
/**
 * Record the notifications that have been sent for an entry, in the entry notes
 *
 * @since 2.5.0
 *
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
class GFPGFU_Record_Sent_Notifications {

	/**
	 * Current notification being sent
	 *
	 * @since 2.5.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @var bool|array
	 */
	private $_current_notification = false;

	/**
	 * Let's get it started!
	 *
	 * @since 2.5.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function run () {

		add_filter( 'gform_pre_send_email', array( $this, 'gform_pre_send_email' ), 10, 4 );

		if ( 'gf_entries' == GFForms::get( 'page' ) ) {

			add_filter( 'esc_html', array( $this, 'esc_html' ), 10, 2 );

		}

	}

	/**
	 * Trigger notification recording
	 *
	 * @since  2.5.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $email
	 * @param $message_format
	 * @param $notification
	 * @param $entry
	 *
	 * @return mixed
	 */
	public function gform_pre_send_email( $email, $message_format, $notification, $entry ) {

		if ( empty( $notification ) || empty( $entry ) ) {

			return $email;
		}

		$this->_current_notification = array( 'id' => $notification['id'], 'name' => $notification['name'] );

		add_action( 'gform_after_email', array( $this, 'gform_after_email' ), 10, 13 );


		return $email;
	}

	/**
	 * Add a note to entry if an email is successfully sent for entry
	 *
	 * @since  2.5.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param bool|WP_Error $is_success
	 * @param $to
	 * @param $subject
	 * @param $message
	 * @param $headers
	 * @param $attachments
	 * @param $message_format
	 * @param $from
	 * @param $from_name
	 * @param $bcc
	 * @param $reply_to
	 * @param array $entry
	 * @param $cc
	 */
	public function gform_after_email( $is_success, $to, $subject, $message, $headers, $attachments, $message_format, $from, $from_name, $bcc, $reply_to, $entry, $cc ) {

		if ( $is_success && ! is_wp_error( $is_success ) && ! empty( $entry ) && ! empty( $this->_current_notification ) ) {

			$notification_edit_url = admin_url( "admin.php?page=gf_edit_forms&view=settings&subview=notification&id={$entry['form_id']}&nid={$this->_current_notification['id']}" );

			$note = "Notification <a href='{$notification_edit_url}'>{$this->_current_notification['name']}</a> sent.<br /><br />To: $to<br />Subject: $subject<br />From: $from_name $from<br />";

			if ( ! empty( $cc ) ){

				$note .= "CC: $cc <br />";
			}

			if ( ! empty( $bcc ) ){

				$note .= "BCC: $bcc <br />";
			}

			GFPGFU_Helper::add_note( $entry[ 'id' ], $note, 'success' );

			$this->reset();
		}

	}

	/**
	 * Reset
	 *
	 * @since  2.5.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function reset() {

		$this->_current_notification = array();

		remove_action( 'gform_after_email', array( $this, 'gform_after_email' ), 10 );

	}

	/**
	 * Preserve HTML formatting in notification
	 *
	 * @since  2.5.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param string $safe_text
	 * @param string $text
	 *
	 * @return mixed
	 */
	public function esc_html( $safe_text, $text ) {

		if ( $this->is_notification_note( $text ) ) {

			$safe_text = $text;

		}

		return $safe_text;
	}

	/**
	 * Make sure this is our note and not someone else's
	 *
	 * @since  2.5.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param string $text
	 *
	 * @return bool
	 */
	private function is_notification_note( $text ) {

		$is_notification_note = false;

		$trace = debug_backtrace();
		$level = $trace[ 5 ];

		if ( 'notes_grid' !== $level[ 'function' ] ) {

			return $is_notification_note;

		}


			foreach ( $level[ 'args' ][ 0 ] as $arg ) {

				if ( $text == $arg->value && 'Utility' == $arg->user_name ) {

					$is_notification_note = true;

					break;
				}

			}


		return $is_notification_note;
	}

}

$gfpgfu_record_sent_notifications = new GFPGFU_Record_Sent_Notifications();

$gfpgfu_record_sent_notifications->run();
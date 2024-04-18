<?php
/**
 * It will add all the settings needed for the plugin.
 *
 * @author  Tyche Softwares
 *
 * @since 2.3.8
 *
 * @package woocommerce-ac
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wcap_Add_Settings' ) ) {
	/**
	 * It will add all the settings needed for the plugin.
	 *
	 * @since 5.0
	 */
	class Wcap_Add_Settings {

		/**
		 * Mark old carts complete.
		 *
		 * @param string $old_value - Old Value of the setting.
		 * @param string $new_value - New Value of the setting.
		 *
		 * @since 8.11.0
		 */
		public static function wcap_update_ac_enable_cart_emails( $old_value, $new_value ) {
			if ( 'on' === $new_value && 'on' !== $old_value ) {
				$old_carts_time = current_time( 'timestamp' ) - 7 * 86400; // (older than 1 week).
				// Mark carts older than 7 days as abandoned.
				global $wpdb;
				$wpdb->query( // phpcs:ignore
					'UPDATE ' . $wpdb->prefix . "ac_abandoned_cart_history SET email_reminder_status = 'complete' WHERE abandoned_cart_time < $old_carts_time AND email_reminder_status = ''" // phpcs:ignore
				);
			}
		}
		/**
		 * Ajax function which will save the notice state in database.
		 *
		 * @param str $new_value - new value.
		 * @param str $old_value - old value.
		 * @since 9.3.0
		 */
		public static function wcap_update_admin_notice_value( $new_value, $old_value ) {
			// Check test mode.
			if ( $new_value !== $old_value && ! empty( $new_value ) && 'on' === $new_value ) {
				update_option( 'wcap_auto_login_notice_dismiss', false );
			}
			return $new_value;
		}
		/**
		 * Enabling this setting will allow users registered on the website to access it via reminder email links without needing to login
		 *
		 * @since 9.3.0
		 *
		 * @hook admin_notices
		 */
		public static function wcap_show_auto_loggin_notice() {
			if ( isset( $_GET['page'] ) && 'woocommerce_ac_page' === $_GET['page'] && isset( $_GET['action'] ) && 'emailsettings' === $_GET['action'] && ! get_option( 'wcap_auto_login_notice_dismiss', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				if ( 'on' === get_option( 'wcap_auto_login_users', 'on' ) ) {
					?>
					<div id='wcap_auto_login_notice' class='is-dismissible notice notice-info wcal-cron-notice'>
						<p>
							<?php
							printf(
								// Translators: Plugin Name and URL.
								esc_html__( 'Enabling this setting will allow users registered on the website to access it via reminder email links without needing to login, which may be a security vulnerability.', 'woocommerce-ac' ),
								wp_kses_post( '<b>Abandoned Cart Pro for WooCommerce</b>' )
							);
							?>
						</p>
					</div>
					<?php
				}
			}
		}

		/**
		 * Gets all settings
		 *
		 * @param bool $return - Return values.
		 *
		 * @since 8.21.0
		 */
		public static function wcap_get_settings( $return = false ) {
			global $wpdb;

			$results  = $wpdb->get_results( // phpcs:ignore
				'SELECT * FROM `' . $wpdb->prefix . "options` WHERE option_name LIKE 'ac_%' OR option_name LIKE 'wcap_%' OR option_name LIKE 'wcac_%'"
			);
			$settings = array();
			foreach ( $results as $value ) {
				$key              = $value->option_name;
				$settings[ $key ] = isset( $value->option_value ) && maybe_unserialize( $value->option_value ) !== false ? maybe_unserialize( $value->option_value ) : $value->option_value;
			}

			$settings['edd_sample_license_status_ac_woo'] = get_option( 'edd_sample_license_status_ac_woo', '' );
			$settings['edd_sample_license_key_ac_woo']    = get_option( 'edd_sample_license_key_ac_woo', '' );

			if ( '' === $settings['ac_delete_abandoned_order_days'] || ! isset( $settings['ac_delete_abandoned_order_days'] ) ) {
				$settings['ac_delete_abandoned_order_days'] = '365';
			}
			$settings_to_check = array(
				'ac_cart_abandoned_after_x_days_order_placed',
				'ac_email_admin_on_recovery',
				'ac_disable_guest_cart_email',
				'ac_disable_logged_in_cart_email',
				'ac_capture_email_from_forms',
				'wcac_delete_plugin_data',
				'wcap_atc_close_icon_add_product_to_cart',
				'wcap_delete_coupon_data',
				'wcap_use_auto_cron',
			);

			foreach ( $settings_to_check as $key ) {
				if ( isset( $settings[ $key ] ) && '' === $settings[ $key ] ) {
					$settings[ $key ] = 'off';
				}
			}

			$wcap_enable_debounce             = isset( $settings['wcap_enable_debounce'] ) ? $settings['wcap_enable_debounce'] : '';
			$settings['wcap_enable_debounce'] = 'on' === $wcap_enable_debounce ? 'on' : '';

			$settings['wcap_enable_gdpr_consent'] = isset( $settings['wcap_enable_gdpr_consent'] ) ? $settings['wcap_enable_gdpr_consent'] : '';
			if ( '' === $settings['wcap_enable_gdpr_consent'] ) {
				$wcap_guest_cart_capture_msg  = isset( $settings['wcap_guest_cart_capture_msg'] ) ? $settings['wcap_guest_cart_capture_msg'] : '';
				$wcap_logged_cart_capture_msg = isset( $settings['wcap_logged_cart_capture_msg'] ) ? $settings['wcap_logged_cart_capture_msg'] : '';
				if ( '' !== $wcap_guest_cart_capture_msg || '' !== $wcap_logged_cart_capture_msg ) {
					$settings['wcap_enable_gdpr_consent'] = 'on';
				}
			}

			if ( ! isset( $settings['wcap_product_name_redirect'] ) || '' === $settings['wcap_product_name_redirect'] ) {
				$settings['wcap_product_name_redirect'] = 'checkout';
			}

			$settings['wcap_product_name_redirect'] = isset( $settings['wcap_product_name_redirect'] ) ? $settings['wcap_product_name_redirect'] : 'checkout';
			$settings['wcap_add_utm_to_links']      = isset( $settings['wcap_add_utm_to_links'] ) ? $settings['wcap_add_utm_to_links'] : '';

			if ( ! isset( $settings['wcap_unsubscribe_landing_page'] ) || '' === $settings['wcap_unsubscribe_landing_page'] ) {
				$settings['wcap_unsubscribe_landing_page'] = 'default_page';
			}

			if ( ! isset( $settings['wcap_unsubscribe_custom_content'] ) || '' === $settings['wcap_unsubscribe_custom_content'] ) {
				$settings['wcap_unsubscribe_custom_content'] = __( 'You have successfully unsubscribed and will not receive any further reminders for abandoned carts.', 'woocommerce-ac' );
			}

			if ( ! isset( $settings['wcap_unsubscribe_custom_wp_page'] ) || '' === $settings['wcap_unsubscribe_custom_wp_page'] ) {
				$settings['wcap_unsubscribe_custom_wp_page'] = '';
			}

			if ( ! isset( $settings['wcap_enable_sms_consent'] ) || '' === $settings['wcap_enable_sms_consent'] ) {
				$settings['wcap_enable_sms_consent'] = '';
			}

			if ( $return ) {
				return $settings;
			}

			wp_send_json( $settings );
		}

		/**
		 * Save all settings.
		 *
		 * @param bool $return - Return values.
		 *
		 * @since 8.21.0
		 */
		public static function wcap_save_settings( $return = false ) {
			$settings = (array) self::wcap_get_settings( true );
			foreach ( $_POST as $key => $value ) { // phpcs:ignore
				$val = sanitize_text_field( wp_unslash( $value ) );
				if ( 'wcap_email_admin_cart_source' === $key ) {
					$val = json_decode( stripslashes( $val ), true );
				}
				update_option( $key, $val );
			}
			wp_send_json( 'success' );
		}

		/**
		 * Ensure setting is not left blanks.
		 *
		 * @param string $new_value - New Value of the setting.
		 * @param string $old_value - Old Value of the setting.
		 * @return string $new_value - New Value.
		 *
		 * @since 9.3.0
		 */
		public static function wcap_update_email_admin_custom_addresses( $new_value, $old_value ) {

			if ( '' === $new_value ) {
				$new_value = get_option( 'admin_email' );
			}
			return $new_value;
		}
	}
}

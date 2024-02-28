<?php
/**
 * AJAX Event Handlers.
 *
 * @since 2.2.2
 */

namespace KoiLab\WC_Currency_Converter;

use KoiLab\WC_Currency_Converter\Utilities\Currency_Utils;
use KoiLab\WC_Currency_Converter\Utilities\Widget_Utils;

/**
 * Class AJAX.
 */
class AJAX {

	/**
	 * Init.
	 *
	 * @since 2.2.2
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in AJAX events.
	 *
	 * @since 2.2.2
	 */
	public static function add_ajax_events() {
		$ajax_events_nopriv = array(
			'fetch_initial_currency',
		);

		foreach ( $ajax_events_nopriv as $ajax_event ) {
			add_action( 'wp_ajax_wc_currency_converter_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			add_action( 'wp_ajax_nopriv_wc_currency_converter_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}
	}

	/**
	 * Fetches the initial currency.
	 *
	 * @since 2.2.2
	 */
	public static function fetch_initial_currency() {
		$widget_id = ( isset( $_POST['widget_id'] ) ? sanitize_text_field( wp_unslash( $_POST['widget_id'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification

		$currency = ( $widget_id ? Currency_Utils::get_by_widget( $widget_id ) : get_woocommerce_currency() );

		wp_send_json_success(
			array(
				'currency' => $currency,
			)
		);
	}
}

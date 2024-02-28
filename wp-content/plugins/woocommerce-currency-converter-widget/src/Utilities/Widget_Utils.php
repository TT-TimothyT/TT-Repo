<?php
/**
 * Widget utilities.
 *
 * @since 2.2.2
 */

namespace KoiLab\WC_Currency_Converter\Utilities;

/**
 * Class Widget_Utils.
 */
class Widget_Utils {

	/**
	 * Gets the widget data for the specified ID.
	 *
	 * @since 2.2.2
	 *
	 * @param string $widget_id The widget ID.
	 * @return array|false An array with the widget date. False if not found.
	 */
	public static function get_data( string $widget_id ) {
		$widget_parts = self::parse_id( $widget_id );

		if ( is_array( $widget_parts ) ) {
			$widgets = get_option( 'widget_' . $widget_parts[0], array() );

			return ( isset( $widgets[ $widget_parts[1] ] ) ? $widgets[ $widget_parts[1] ] : false );
		}

		return false;
	}

	/**
	 * Extracts the widget info from its ID.
	 *
	 * Provided the widget ID `woocommerce_currency_converter-2`,
	 * the function will return `array( 'woocommerce_currency_converter', 2 )`.
	 *
	 * @since 2.2.2
	 *
	 * @param string $widget_id The widget ID.
	 * @return array|false An array with the widget id_base and number. False if the widget ID is not valid.
	 */
	public static function parse_id( string $widget_id ) {
		$pattern = '/^(.*)-(\d+)$/';

		preg_match( $pattern, $widget_id, $matches );

		if ( 3 === count( $matches ) && is_numeric( $matches[2] ) ) {
			return array( $matches[1], (int) $matches[2] );
		}

		return false;
	}
}

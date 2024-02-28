<?php
/**
 * Currency utilities.
 *
 * @since 1.9.0
 */

namespace KoiLab\WC_Currency_Converter\Utilities;

/**
 * Class Currency_Utils.
 */
class Currency_Utils {

	/**
	 * Gets the currency by country.
	 *
	 * @since 1.9.0
	 *
	 * @param string $country_code The country code.
	 * @return string|false The currency code. False if not found.
	 */
	public static function get_by_country( $country_code ) {
		$locale_info = L10n_Utils::get_locale( $country_code );

		return ( $locale_info ? $locale_info['currency_code'] : false );
	}

	/**
	 * Gets the currency by IP address.
	 *
	 * @since 2.2.2
	 *
	 * @param string $ip_address The IP address.
	 * @return false|string The currency code. False if not found.
	 */
	public static function get_by_ip( string $ip_address ) {
		$location = \WC_Geolocation::geolocate_ip( $ip_address );

		if ( isset( $location['country'] ) ) {
			return self::get_by_country( $location['country'] );
		}

		return false;
	}

	/**
	 * Gets the currency for the user location.
	 *
	 * @since 2.2.2
	 *
	 * @return string|false The currency code. False if not found.
	 */
	public static function get_geolocated() {
		return self::get_by_ip( '' );
	}

	/**
	 * Gets the currency based on the widget config.
	 *
	 * @since 2.2.2
	 *
	 * @param string $widget_id The widget ID.
	 * @return string
	 */
	public static function get_by_widget( string $widget_id ): string {
		$wc_currency = get_woocommerce_currency();

		// Currency converter widgets only.
		if ( 0 !== strpos( $widget_id, 'woocommerce_currency_converter' ) ) {
			return $wc_currency;
		}

		$widget = Widget_Utils::get_data( $widget_id );

		if ( ! $widget ) {
			return $wc_currency;
		}

		$disable_location = ( isset( $widget['disable_location'] ) && wc_string_to_bool( $widget['disable_location'] ) );

		/**
		 * Filter the 'disable_location' settings value.
		 *
		 * @since 1.6.0
		 *
		 * @param bool $disable_location The 'disable_location' settings value.
		 */
		$disable_location = apply_filters( 'woocommerce_disable_location_based_currency', $disable_location );

		// Get the currency based on the customer's location.
		if ( ! $disable_location ) {
			$local_currency = self::get_geolocated();

			if ( $local_currency ) {
				$currencies = ( isset( $widget['currency_codes'] ) ? explode( "\n", $widget['currency_codes'] ) : array() );

				// It's an allowed currency.
				if ( is_array( $currencies ) && in_array( $local_currency, $currencies, true ) ) {
					return $local_currency;
				}
			}
		}

		return $wc_currency;
	}
}

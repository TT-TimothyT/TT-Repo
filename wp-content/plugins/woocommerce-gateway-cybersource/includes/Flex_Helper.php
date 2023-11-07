<?php
/**
 * WooCommerce CyberSource
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce CyberSource to newer
 * versions in the future. If you wish to customize WooCommerce CyberSource for your
 * needs please refer to http://docs.woocommerce.com/document/cybersource-payment-gateway/
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2023, SkyVerge, Inc. (info@skyverge.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Cybersource;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use SkyVerge\WooCommerce\PluginFramework\v5_11_4 as Framework;

class Flex_Helper {


	/**
	 * Decodes a Flex Microform token.
	 *
	 * @since 2.3.0-dev.1
	 *
	 * @param string $form_jwt JWT value created by the Microform JS
	 * @param string $api_jwt JWT value returned by the Flex Keys API
	 * @return array
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	public static function decode_flex_token( $form_jwt, $api_jwt ) {

		$payload = JWT::decode( $form_jwt, self::get_public_key_set( $api_jwt ), [ 'RS256' ] );

		return json_decode( json_encode( $payload ), true );
	}


	/**
	 * Gets the public key set from the given Flex API JWT.
	 *
	 * @since 2.3.0-dev.1
	 *
	 * @param string $jwt encoded JWT
	 * @return array
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	private static function get_public_key_set( $jwt ) {

		$payload = self::get_jwt_payload( $jwt );

		if ( empty( $payload['flx']['jwk'] ) ) {
			throw new Framework\SV_WC_Plugin_Exception( 'JWK claim is missing' );
		}

		return JWK::parseKeySet( [
			'keys' => [
				$payload['flx']['jwk'],
			],
		] );
	}


	/**
	 * Gets the payload of a JWT as an array.
	 *
	 * Note: this does not validate the JWT. self::decode_flex_token() or JWT::decode() should be used when validation
	 * is required.
	 *
	 * @since 2.3.0-dev.1
	 *
	 * @param string $jwt JWT value
	 * @return array
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	private static function get_jwt_payload( $jwt ) {

		list( $headers, $payload, $sig ) = explode( '.', $jwt );

		$payload = json_decode( base64_decode( $payload ), true );

		if ( ! is_array( $payload ) ) {
			throw new Framework\SV_WC_Plugin_Exception( 'JWT is invalid' );
		}

		return $payload;
	}


}

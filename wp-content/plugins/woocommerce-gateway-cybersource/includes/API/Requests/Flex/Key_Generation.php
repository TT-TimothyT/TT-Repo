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
 * @copyright   Copyright (c) 2012-2022, SkyVerge, Inc. (info@skyverge.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Cybersource\API\Requests\Flex;

use SkyVerge\WooCommerce\Cybersource\API\Request;

defined( 'ABSPATH' ) or exit;

/**
 * CyberSource API Key Generation Request Class
 *
 * Handles key generation requests
 *
 * @since 2.0.0
 */
class Key_Generation extends Request {


	/**
	 * Key_Generation constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->path = '/flex/v1/keys';

		$this->params = [
			'format' => 'JWT',
		];
	}


	/**
	 * Creates a Flex microform public key request.
	 *
	 * @since 2.0.0
	 *
	 * @param string $encryption_type type of encryption to use
	 */
	public function set_generate_public_key_data( $encryption_type ) {

		$this->method = self::REQUEST_METHOD_POST;

		$complete_url = parse_url( get_site_url() );
		// use the base URL to prevent issues for subfolder installations (single or multisite)
		$base_url = $complete_url['scheme'] . "://" . $complete_url['host'];

		$this->data = [
			'encryptionType' => $encryption_type,
			'targetOrigin'   => $base_url,
		];
	}


}

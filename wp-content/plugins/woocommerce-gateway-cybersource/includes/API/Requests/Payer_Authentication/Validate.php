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

namespace SkyVerge\WooCommerce\Cybersource\API\Requests\Payer_Authentication;

use SkyVerge\WooCommerce\Cybersource\API\Requests\Payments;
use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * CyberSource API validate request.
 *
 * @since 2.0.0
 */
class Validate extends Payments {


	/**
	 * Validate constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->path   = '/risk/v1/authentication-results/';
		$this->method = self::REQUEST_METHOD_POST;
	}


	/**
	 * Sets the order data.
	 *
	 * @since 2.3.0
	 *
	 * @param \WC_Order $order WooCommerce order
	 */
	public function set_order_data( \WC_Order $order ) {

		$this->order = $order;

		$this->data = [
			'clientReferenceInformation' => [
				'code' => Framework\SV_WC_Helper::str_truncate( $order->get_order_number(), 50, '' ),
			],
			'orderInformation' => [
				'amountDetails' => $this->get_amount_details( $order->get_total() ),
			],
			'paymentInformation' => [
				'customer' => [
					'customerId' => ! empty( $order->payment->token ) ? $order->payment->token : $order->payment->js_token,
				],
			],
			'consumerAuthenticationInformation' => [
				'authenticationTransactionId' => $order->threed_secure->transaction_id,
			],
		];
	}


}

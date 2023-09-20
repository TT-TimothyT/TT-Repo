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

namespace SkyVerge\WooCommerce\Cybersource\API\Requests\Payer_Authentication;

use SkyVerge\WooCommerce\Cybersource\API\Requests\Payments\Payment;
use SkyVerge\WooCommerce\PluginFramework\v5_10_12 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * CyberSource API check enrollment request.
 *
 * @since 2.3.0
 */
class Check_Enrollment extends Payment {


	/**
	 * Check_Enrollment constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {

		$this->path   = '/risk/v1/authentications/';
		$this->method = self::REQUEST_METHOD_POST;
	}


	/**
	 * Sets the order data.
	 *
	 * @since 2.3.0
	 *
	 * @param \WC_Order $order
	 */
	public function set_order_data( \WC_Order $order ) {

		$this->order = $order;

		$this->data = [
			'clientReferenceInformation' => $this->get_client_reference_information(),
			'orderInformation'           => $this->get_order_information(),
			'tokenInformation' => [
				'transientToken' => $order->payment->token,
			],
			'buyerInformation' => $this->get_buyer_information(),
		];

		// include the reference ID if there is one
		if ( ! empty( $order->payment->reference_id ) ) {

			$this->data['consumerAuthenticationInformation'] = [
				'referenceId' => $order->payment->reference_id,
			];
		}
	}


	/**
	 * Gets the payment data.
	 *
	 * Purposefully empty.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	protected function get_payment_data() {

		return [];
	}


}

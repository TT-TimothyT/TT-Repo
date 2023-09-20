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

namespace SkyVerge\WooCommerce\Cybersource\API\Requests\Payments;

use SkyVerge\WooCommerce\Cybersource\API\Visa_Checkout\Traits\Can_Add_Visa_Checkout_Request_Data;

defined( 'ABSPATH' ) or exit;

class Credit_Card_Payment extends Payment {


	use Can_Add_Visa_Checkout_Request_Data;


	/** auth and capture transaction type */
	const AUTHORIZE_AND_CAPTURE = true;

	/** authorize-only transaction type */
	const AUTHORIZE_ONLY = false;


	/**
	 * Creates a credit card charge request.
	 *
	 * @since 2.0.0
	 *
	 * @param \WC_Order $order order object
	 */
	public function create_credit_card_charge( \WC_Order $order ) {

		$this->create_payment( $order, self::AUTHORIZE_AND_CAPTURE );
	}


	/**
	 * Creates a credit card auth request.
	 *
	 * @since 2.0.0
	 *
	 * @param \WC_Order $order order object
	 */
	public function create_credit_card_auth( \WC_Order $order ) {

		$this->create_payment( $order, self::AUTHORIZE_ONLY );
	}


	/**
	 * Sets data to create a payment.
	 *
	 * @since 2.3.0
	 *
	 * @param \WC_Order $order WooCommerce order
	 * @param bool $settlement_type settlement type
	 */
	public function create_payment( \WC_Order $order, $settlement_type = true ) {

		parent::create_payment( $order, $settlement_type );

		$threed_secure_data = $this->get_consumer_authentication_information( $order );

		if ( ! empty( $threed_secure_data ) ) {
			$this->data['consumerAuthenticationInformation'] = $threed_secure_data;
		}
	}


	/**
	 * Gets the customer authentication information.
	 *
	 * @since 2.3.0
	 *
	 * @param \WC_Order $order WooCommerce order object
	 * @return array
	 */
	private function get_consumer_authentication_information( \WC_Order $order ) {

		$data = [];

		if ( ! empty( $order->threed_secure->transaction_id ) ) {
			$data['authenticationTransactionId'] = $order->threed_secure->transaction_id;
		}

		// add the reference ID if available
		if ( ! empty( $order->threed_secure->reference_id ) ) {
			$data['referenceId'] = $order->threed_secure->reference_id;
		}

		return array_filter( $data );
	}


	/**
	 * Gets the payment information.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_payment_information() {

		$data = parent::get_payment_information();

		if ( ! empty( $data['customer']['customerId'] ) && ! empty( $this->get_order()->payment->csc ) ) {

			$data['card'] = [
				'securityCode' => $this->get_order()->payment->csc,
			];
		}

		return $data;
	}


	/**
	 * Gets the payment method data.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_payment_data() {

		$payment = $this->get_order()->payment;

		if ( $fluid_data = $this->get_visa_checkout_fluid_data( $this->get_order(), 'payment' ) ) {

			$data = [ 'fluidData' => $fluid_data ];

		} elseif ( ! empty( $this->get_order()->payment->apple_pay ) ) {

			$data = [
				'fluidData' => [
					'value' => $this->get_order()->payment->apple_pay,
				],
			];

		} else if ( ! empty( $this->get_order()->payment->google_pay ) ) {

			$data = [
				'fluidData' => [
					'value' => $this->get_order()->payment->google_pay,
				],
			];

		} else {

			$data = [
				'card' => [
					'expirationYear'  => $payment->exp_year,
					'number'          => $payment->account_number,
					'securityCode'    => ! empty( $payment->csc ) ? $payment->csc : '',
					'expirationMonth' => $payment->exp_month,
				],
			];
		}

		return $data;
	}


	/**
	 * Gets the processing information.
	 *
	 * Sets the Apple Pay payment solution if paying with Apple Pay.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $settlement_type settlement type
	 * @return array
	 */
	protected function get_processing_information( $settlement_type = false ) {

		$data = parent::get_processing_information( $settlement_type );

		if ( ! empty( $this->get_order()->payment->apple_pay ) ) {

			$data['paymentSolution'] = self::PAYMENT_SOLUTION_APPLE_PAY;

		} elseif ( ! empty( $this->get_order()->payment->google_pay ) ) {

			$data['paymentSolution'] = self::PAYMENT_SOLUTION_GOOGLE_PAY;
		}

		// tweak values for 3D Secure
		if ( ! empty( $this->get_order()->threed_secure->transaction_id ) ) {
			$data['actionList'][] = $this->get_order()->threed_secure->jwt ? 'VALIDATE_CONSUMER_AUTHENTICATION' : 'CONSUMER_AUTHENTICATION';
		}

		if ( $info = $this->get_visa_checkout_processing_information( $this->get_order(), 'payment' ) ) {
			$data = array_merge( $data, $info );
		}

		return $data;
	}


	/**
	 * Gets the string representation of this request with all sensitive information masked.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function to_string_safe() {

		$string = $this->to_string();
		$data   = $this->get_data();

		// card number
		if ( isset( $data['paymentInformation']['card']['number'] ) ) {

			$number = $data['paymentInformation']['card']['number'];

			$string = str_replace( $number, str_repeat( '*', strlen( $number ) - 4 ) . substr( $number, -4 ), $string );
		}

		// csc
		if ( isset( $data['paymentInformation']['card']['securityCode'] ) ) {

			$csc = $data['paymentInformation']['card']['securityCode'];

			$string = str_replace( $csc, str_repeat( '*', strlen( $csc ) ), $string );
		}

		// fluid data to keep the logs small
		if ( isset( $data['paymentInformation']['fluidData']['value'] ) ) {
			$string = $this->replace_fluid_data( $data['paymentInformation']['fluidData']['value'], str_repeat( '*', 10 ), $string );
		}

		if ( isset( $data['paymentInformation']['fluidData']['key'] ) ) {
			$string = $this->replace_fluid_data( $data['paymentInformation']['fluidData']['key'], str_repeat( '*', 10 ), $string );
		}

		if ( isset( $data['consumerAuthenticationInformation']['authenticationTransactionId'] ) ) {
			$string = str_replace( $data['consumerAuthenticationInformation']['authenticationTransactionId'], str_repeat( '*', 10 ), $string );
		}

		return $string;
	}


	/**
	 * Replaces fluid data values in the given string.
	 *
	 * JSON encoded strings include '/' characters as '\/' making them different from the value
	 * stored in the data array. As a result, str_replace() is unable to find a match.
	 *
	 * @since 2.3.0
	 *
	 * @param string $value value to replace
	 * @param string $replacement replacement string
	 * @param string $string original string
	 * @return string
	 */
	protected function replace_fluid_data( $value, $replacement, $string ) {

		// ensure / characters are escaped so that str_replace() can find a match
		$encoded_value = json_encode( $value );

		if ( JSON_ERROR_NONE === json_last_error() ) {
			// remove double quotes from encoded value to replace the content of the field only
			$encoded_value = trim( $encoded_value, '"' );
		} else {
			// attempt to replace the original value if an encoding error occurred
			$encoded_value = $value;
		}

		return str_replace( $encoded_value, $replacement, $string );
	}


}

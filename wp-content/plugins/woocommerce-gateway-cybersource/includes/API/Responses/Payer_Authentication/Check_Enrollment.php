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

namespace SkyVerge\WooCommerce\Cybersource\API\Responses\Payer_Authentication;

use SkyVerge\WooCommerce\Cybersource\API\Response;
use SkyVerge\WooCommerce\PluginFramework\v5_11_4 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * CyberSource API payer authentication enrollment response.
 *
 * @since 2.3.0
 */
class Check_Enrollment extends Response {


	/**
	 * Gets the JSON Web Token.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_status() {

		return ! empty( $this->response_data->status ) ? $this->response_data->status : '';
	}


	/**
	 * Gets the JSON Web Token.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_acs_url() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->acsUrl ) ? $this->response_data->consumerAuthenticationInformation->acsUrl : '';
	}


	/**
	 * Gets the JSON Web Token.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_payload() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->pareq ) ? $this->response_data->consumerAuthenticationInformation->pareq : '';
	}


	/**
	 * Gets the authentication transaction ID.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_transaction_id() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->authenticationTransactionId ) ? $this->response_data->consumerAuthenticationInformation->authenticationTransactionId : '';
	}


	/**
	 * Gets the directory server transaction ID.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_directory_server_transaction_id() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->directoryServerTransactionId ) ? $this->response_data->consumerAuthenticationInformation->directoryServerTransactionId : '';
	}


	/**
	 * Gets the CAVV value.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_cavv() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->cavv ) ? $this->response_data->consumerAuthenticationInformation->cavv : '';
	}


	/**
	 * Gets the AAV value.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_aav() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->ucafAuthenticationData ) ? $this->response_data->consumerAuthenticationInformation->ucafAuthenticationData : '';
	}


	/**
	 * Gets the AAV value indicator.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_aav_indicator() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->ucafCollectionIndicator ) ? $this->response_data->consumerAuthenticationInformation->ucafCollectionIndicator : '';
	}


	/**
	 * Gets the enrolled value.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_enrolled() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->veresEnrolled ) ? $this->response_data->consumerAuthenticationInformation->veresEnrolled : '';
	}


	/**
	 * Gets the CAVV algorithm.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_cavv_algorithm() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->cavvAlgorithm ) ? $this->response_data->consumerAuthenticationInformation->cavvAlgorithm : '';
	}


	/**
	 * Gets the ECI value
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_eci() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->eci ) ? $this->response_data->consumerAuthenticationInformation->eci : '';
	}


	/**
	 * Gets the raw ECI value
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_eci_raw() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->eciRaw ) ? $this->response_data->consumerAuthenticationInformation->eciRaw : '';
	}


	/**
	 * Gets the PARes status.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_pares_status() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->paresStatus ) ? $this->response_data->consumerAuthenticationInformation->paresStatus : '';
	}


	/**
	 * Gets the XID.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_xid() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->xid ) ? $this->response_data->consumerAuthenticationInformation->xid : '';
	}


	/**
	 * Gets the 3D Secure specification version.
	 *
	 * This represents either v1 or v2.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_specification_version() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->specificationVersion ) ? $this->response_data->consumerAuthenticationInformation->specificationVersion : '';
	}


	/**
	 * Gets the 3D Secure commerce indicator.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_commerce_indicator() {

		return ! empty( $this->response_data->consumerAuthenticationInformation->ecommerceIndicator ) ? $this->response_data->consumerAuthenticationInformation->ecommerceIndicator : '';
	}


	/**
	 * Get the card type
	 *
	 * @since 2.7.1
	 *
	 * @return 'JCB'|'AMERICAN EXPRESS'|'VISA'|'DINERS'|'DINERS CLUB'|'MASTERCARD'|'DISCOVER'|''
	 */
	public function get_card_type(): string {

		return ! empty( $this->paymentInformation->card->type ) ? $this->paymentInformation->card->type : '';
	}


	/**
	 * Gets the string representation of this response with all sensitive information masked.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function to_string_safe() {

		$string = $this->to_string();

		if ( $value = $this->get_payload() ) {
			$string = str_replace( $value, str_repeat( '*', strlen( $value ) ), $string );
		}

		if ( $value = $this->get_transaction_id() ) {
			$string = str_replace( $value, str_repeat( '*', strlen( $value ) ), $string );
		}

		return $string;
	}


}

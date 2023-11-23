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

namespace SkyVerge\WooCommerce\Cybersource\Gateway\ThreeD_Secure;

use SkyVerge\WooCommerce\Cybersource\API\Response;
use SkyVerge\WooCommerce\Cybersource\Gateway\ThreeD_Secure;
use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * AJAX 3D Secure handler.
 *
 * @since 2.3.0
 */
class AJAX {


	/** @var string the AJAX action for initial setup */
	const ACTION_SETUP = 'wc_cybersource_threed_secure_setup';

	/** @var string the AJAX action for enrollment check */
	const ACTION_CHECK_ENROLLMENT = 'wc_cybersource_threed_secure_check_enrollment';


	/** @var ThreeD_Secure 3D Secure handler */
	private $handler;


	/**
	 * AJAX constructor.
	 *
	 * @since 2.3.0
	 *
	 * @param ThreeD_Secure $handler 3D Secure handler
	 */
	public function __construct( ThreeD_Secure $handler ) {

		$this->handler = $handler;

		// add the action & filter hooks
		$this->add_hooks();
	}


	/**
	 * Adds the action & filter hooks.
	 *
	 * @since 2.3.0
	 */
	private function add_hooks() {

		// handle the AJAX action for enrollment check
		add_action( 'wp_ajax_' . self::ACTION_SETUP,        [ $this, 'setup' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION_SETUP, [ $this, 'setup' ] );

		// handle the AJAX action for enrollment check
		add_action( 'wp_ajax_' . self::ACTION_CHECK_ENROLLMENT,        [ $this, 'check_enrollment' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION_CHECK_ENROLLMENT, [ $this, 'check_enrollment' ] );
	}


	/**
	 * Handles the AJAX action for initial setup.
	 *
	 * @internal
	 *
	 * @since 2.3.0
	 */
	public function setup() {

		try {

			$this->validate_request( self::ACTION_SETUP );

			$order = wc_get_order( Framework\SV_WC_Helper::get_posted_value( 'order_id' ) );

			if ( ! $order instanceof \WC_Order ) {
				throw new Framework\SV_WC_Plugin_Exception( 'Invalid order ID', 400 );
			}

			$order = $this->handler->get_gateway()->get_order( $order );

			$order->payment->token        = Framework\SV_WC_Helper::get_posted_value( 'token' );
			$order->payment->is_transient = Framework\SV_WC_Helper::get_posted_value( 'is_transient' ) == 'true';

			$response = $this->handler->get_gateway()->get_api()->threed_secure_setup( $order );

			wp_send_json_success( [
				'jwt'          => $response->get_jwt(),
				'reference_id' => $response->get_reference_id(),
			] );

		} catch ( Framework\SV_WC_Plugin_Exception $exception ) {

			wp_send_json_error( $exception->getMessage(), $exception->getCode() );
		}
	}


	/**
	 * Handles the AJAX action for enrollment check.
	 *
	 * @internal
	 *
	 * @since 2.3.0
	 */
	public function check_enrollment() {

		try {

			$this->validate_request( self::ACTION_CHECK_ENROLLMENT );

			$order = wc_get_order( Framework\SV_WC_Helper::get_posted_value( 'order_id' ) );

			if ( ! $order instanceof \WC_Order ) {
				throw new Framework\SV_WC_Plugin_Exception( 'Invalid order ID', 400 );
			}

			$order = $this->handler->get_gateway()->get_order( $order );

			$order->payment->token        = Framework\SV_WC_Helper::get_posted_value( 'token' );
			$order->payment->is_transient = Framework\SV_WC_Helper::get_posted_value( 'is_transient' ) == 'true';
			$order->payment->reference_id = Framework\SV_WC_Helper::get_posted_value( 'reference_id' );

			$response = $this->handler->get_gateway()->get_api()->threed_secure_check_enrollment( $order );

			if ( in_array( $response->get_status(), [ 'AUTHENTICATION_FAILED', 'INVALID_REQUEST' ] ) ) {
				throw new Framework\SV_WC_Plugin_Exception( 'Enrollment check failed' );
			}

			wp_send_json_success( [
				'continue' => [
					'AcsUrl'  => $response->get_acs_url(),
					'Payload' => $response->get_payload(),
				],
				'order' => [
					'OrderDetails' => [
						'TransactionId'                => $response->get_transaction_id(),
						'ecommerceIndicator'           => $response->get_commerce_indicator(),
						'ucafCollectionIndicator'      => $response->get_aav_indicator(),
						'cavv'                         => $response->get_cavv(),
						'ucafAuthenticationData'       => $response->get_aav(),
						'xid'                          => $response->get_xid(),
						'veresEnrolled'                => $response->get_enrolled(),
						'specificationVersion'         => $response->get_specification_version(),
						'directoryServerTransactionId' => $response->get_directory_server_transaction_id(),
						'cardType'                     => $response->get_card_type(),
					],
				]
			] );

		} catch ( Framework\SV_WC_Plugin_Exception $exception ) {

			wp_send_json_error( $exception->getMessage(), $exception->getCode() );
		}
	}


	/** Utility methods ***********************************************************************************************/


	/**
	 * Validates the nonce for a given AJAX action.
	 *
	 * @since 2.3.0
	 *
	 * @param string $action AJAX action
	 * @return bool
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	private function validate_request( $action ) {

		if ( ! wp_verify_nonce( Framework\SV_WC_Helper::get_posted_value( 'nonce' ), $action ) ) {
			throw new Framework\SV_WC_Plugin_Exception( 'Invalid nonce', 400 );
		}

		return true;
	}


}

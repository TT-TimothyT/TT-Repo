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

use SkyVerge\WooCommerce\Cybersource\Gateway\ThreeD_Secure;
use SkyVerge\WooCommerce\Cybersource\Plugin;
use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Frontend 3D Secure handler.
 *
 * @since 2.3.0
 */
class Frontend {


	/** @var string the staging JS URL */
	const URL_STAGING = 'https://songbirdstag.cardinalcommerce.com/edge/v1/songbird.js';

	/** @var string the production JS URL */
	const URL_PRODUCTION = 'https://songbird.cardinalcommerce.com/edge/v1/songbird.js';


	/** @var ThreeD_Secure 3D Secure handler */
	private $handler;


	/**
	 * Frontend constructor.
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

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		add_action( 'wc_' . Plugin::CREDIT_CARD_GATEWAY_ID . '_payment_form_end',   array( $this, 'render_js' ), 5 );

		add_action( 'wc_' . Plugin::CREDIT_CARD_GATEWAY_ID . '_payment_form_payment_method_html', array( $this, 'add_token_data' ), 10, 2 );
	}


	/**
	 * Adds the card type and card bin for saved payment methods.
	 *
	 * @internal
	 *
	 * @since 2.7.0
	 */
	public function add_token_data(string $html, Framework\SV_WC_Payment_Gateway_Payment_Token $token) : string {

		$html = str_replace('type="radio"', 'type="radio" data-card-type="' . $token->get_card_type() . '"', $html );

		$token_data = $token->to_datastore_format();

		if ( isset($token_data['first_six']) && $token_data['first_six'] ) {
			$html = str_replace('type="radio"', 'type="radio" data-card-bin="' . $token_data['first_six'] . '"', $html );
		}

		return $html;
	}


	/**
	 * Enqueues the 3D Secure assets.
	 *
	 * @internal
	 *
	 * @since 2.3.0
	 */
	public function enqueue_assets() {

		// only render on the pay page or Add Payment Method pages
		if ( ! is_checkout_pay_page() ) {
			return;
		}

		wp_enqueue_script( 'wc-cybersource-threed-secure-songbird', $this->handler->is_test_mode() ? self::URL_STAGING : self::URL_PRODUCTION, [ 'jquery' ], Plugin::VERSION );

		wp_enqueue_script( 'wc-cybersource-threed-secure', wc_cybersource()->get_plugin_url() . '/assets/js/frontend/wc-cybersource-threed-secure.min.js', [ 'jquery', 'wc-cybersource-threed-secure-songbird' ], Plugin::VERSION );
	}


	/**
	 * Renders the JS.
	 *
	 * @since 2.3.0
	 */
	public function render_js() {

		?>
		<input id="wc_cybersource_threed_secure_transaction_id" name="wc_cybersource_threed_secure_transaction_id" type="hidden" />
		<input id="wc_cybersource_threed_secure_reference_id" name="wc_cybersource_threed_secure_reference_id" type="hidden" />
		<input id="wc_cybersource_threed_secure_jwt" name="wc_cybersource_threed_secure_jwt" type="hidden" />
		<input id="wc_cybersource_threed_secure_ecommerce_indicator" name="wc_cybersource_threed_secure_ecommerce_indicator" type="hidden" />
		<input id="wc_cybersource_threed_secure_ucaf_collection_indicator" name="wc_cybersource_threed_secure_ucaf_collection_indicator" type="hidden" />
		<input id="wc_cybersource_threed_secure_cavv" name="wc_cybersource_threed_secure_cavv" type="hidden" />
		<input id="wc_cybersource_threed_secure_ucaf_authentication_data" name="wc_cybersource_threed_secure_ucaf_authentication_data" type="hidden" />
		<input id="wc_cybersource_threed_secure_xid" name="wc_cybersource_threed_secure_xid" type="hidden" />
		<input id="wc_cybersource_threed_secure_veres_enrolled" name="wc_cybersource_threed_secure_veres_enrolled" type="hidden" />
		<input id="wc_cybersource_threed_secure_specification_version" name="wc_cybersource_threed_secure_specification_version" type="hidden" />
		<input id="wc_cybersource_threed_secure_directory_server_transaction_id" name="wc_cybersource_threed_secure_directory_server_transaction_id" type="hidden" />
		<input id="wc_cybersource_threed_secure_card_type" name="wc_cybersource_threed_secure_card_type" type="hidden" />
		<input id="wc_cybersource_threed_secure_eci_flag" name="wc_cybersource_threed_secure_eci_flag" type="hidden" />
		<?php

		wc_enqueue_js( sprintf( 'window.wc_cybersource_threed_secure = new WC_Cybersource_ThreeD_Secure_Handler( %s );', json_encode( [
			'order_id'                => $this->handler->get_gateway()->get_checkout_pay_page_order_id(),
			'ajax_url'                => admin_url( 'admin-ajax.php' ),
			'logging_enabled'         => $this->handler->get_gateway()->debug_log(),
			'setup_action'            => AJAX::ACTION_SETUP,
			'setup_nonce'             => wp_create_nonce( AJAX::ACTION_SETUP ),
			'check_enrollment_action' => AJAX::ACTION_CHECK_ENROLLMENT,
			'check_enrollment_nonce'  => wp_create_nonce( AJAX::ACTION_CHECK_ENROLLMENT ),
			'enabled_card_types'      => array_map( 'SkyVerge\WooCommerce\Cybersource\API\Helper::convert_card_type_to_code', $this->handler->get_enabled_card_types() ),
			'enabled_card_type_names' => $this->handler->get_enabled_card_types(),
			'i18n' => [
				'error_general' => __( 'An error occurred, please try again or try an alternate form of payment', 'woocommerce-gateway-cybersource' )
			],
		] ) ) );
	}


}

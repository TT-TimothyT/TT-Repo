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

namespace SkyVerge\WooCommerce\Cybersource\Gateway;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Cybersource\Gateway;
use SkyVerge\WooCommerce\Cybersource\Gateway\ThreeD_Secure\Frontend;
use SkyVerge\WooCommerce\Cybersource\Gateway\ThreeD_Secure\AJAX;
use SkyVerge\WooCommerce\Cybersource\Plugin;
use SkyVerge\WooCommerce\PluginFramework\v5_10_12 as Framework;

/**
 * 3D Secure handler.
 *
 * @since 2.3.0
 */
class ThreeD_Secure {


	/** @var Gateway gateway instance */
	private $gateway;

	/** @var bool whether 3D Secure is enabled */
	private $is_enabled = false;

	/** @var bool whether test mode is enabled */
	private $is_test_mode = false;

	/** @var string[] enabled card types */
	private $enabled_card_types = [];

	/** @var Frontend frontend handler instance */
	private $frontend_handler;

	/** @var AJAX AJAX handler instance */
	private $ajax_handler;


	/**
	 * Initializes the handler.
	 *
	 * @since 2.3.0
	 *
	 * @return ThreeD_Secure
	 */
	public function init() {

		if ( ! $this->is_enabled() ) {
			return $this;
		}

		if ( wp_doing_ajax() ) {
			$this->ajax_handler = new AJAX( $this );
		} elseif ( ! is_admin() ) {
			$this->frontend_handler = new Frontend( $this );
		}

		return $this;
	}


	/** Setter methods ************************************************************************************************/


	/**
	 * @param Gateway $gateway
	 * @return $this
	 */
	public function set_gateway( Gateway $gateway ) {

		$this->gateway = $gateway;

		return $this;
	}


	/**
	 * Set whether 3D Secure is enabled.
	 *
	 * @since 2.3.0
	 *
	 * @param bool $is_enabled whether 3D Secure is enabled
	 * @return ThreeD_Secure
	 */
	public function set_enabled( $is_enabled ) {

		$this->is_enabled = (bool) $is_enabled;

		return $this;
	}


	/**
	 * Sets the enabled card types.
	 *
	 * @since 2.3.0
	 *
	 * @param string[] $card_types enabled card types
	 * @return ThreeD_Secure
	 */
	public function set_enabled_card_types( array $card_types ) {

		$this->enabled_card_types = array_intersect( $card_types, array_keys( self::get_supported_card_types() ) );

		return $this;
	}


	/**
	 * Set whether test mode is enabled.
	 *
	 * @since 2.3.0
	 *
	 * @param bool $is_test_mode whether test mode is enabled
	 * @return ThreeD_Secure
	 */
	public function set_test_mode( $is_test_mode ) {

		$this->is_test_mode = (bool) $is_test_mode;

		return $this;
	}


	/** Conditional methods *******************************************************************************************/


	/**
	 * Determines whether 3D Secure is enabled.
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	public function is_enabled() {

		/**
		 * Filters whether 3D Secure is enabled.
		 *
		 * @since 2.3.0
		 *
		 * @param bool $is_enabled whether 3D Secure is enabled
		 */
		return (bool) apply_filters( 'wc_' . Plugin::CREDIT_CARD_GATEWAY_ID . '_3d_secure_is_enabled', $this->is_enabled );
	}


	/**
	 * Determines whether test mode is enabled.
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	public function is_test_mode() {

		return (bool) $this->is_test_mode;
	}


	/** Getter methods ************************************************************************************************/


	/**
	 * Gets the enabled card types.
	 *
	 * @since 2.3.0
	 *
	 * @return string[]
	 */
	public function get_enabled_card_types() {

		/**
		 * Filters the card types enabled for 3D Secure.
		 *
		 * @since 2.3.0
		 *
		 * @param string[] $card_types card types enabled for 3D Secure
		 */
		return (array) apply_filters( 'wc_' . Plugin::CREDIT_CARD_GATEWAY_ID . '_3d_secure_enabled_card_types', $this->enabled_card_types );
	}


	/**
	 * Gets the card types supported by 3D Secure.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	public static function get_supported_card_types() {

		return [
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_AMEX       => __( 'American Express SafeKey', 'woocommerce-gateway-cybersource' ),
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_DINERSCLUB => __( 'Diners International', 'woocommerce-gateway-cybersource' ),
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_DISCOVER   => __( 'Discover ProtectBuy', 'woocommerce-gateway-cybersource' ),
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_JCB        => __( 'JCB J-Secure', 'woocommerce-gateway-cybersource' ),
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_MASTERCARD => __( 'MasterCard SecureCode and Identity Check', 'woocommerce-gateway-cybersource' ),
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_VISA       => __( 'Verified by Visa', 'woocommerce-gateway-cybersource' ),
		];
	}


	/**
	 * Gets the gateway instance.
	 *
	 * @since 2.3.0
	 *
	 * @return Gateway
	 */
	public function get_gateway() {

		return $this->gateway;
	}


}

<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\Internal\Admin\Settings\PaymentsProviders;

use Automattic\WooCommerce\Internal\Logging\SafeGlobalFunctionProxy;
use Throwable;
use WC_Payment_Gateway;

defined( 'ABSPATH' ) || exit;

/**
 * Paystack payment gateway provider class.
 *
 * This class handles all the custom logic for the Paystack payment gateway provider.
 */
class Paystack extends PaymentGateway {

	/**
	 * Check if the payment gateway needs setup.
	 *
	 * @param WC_Payment_Gateway $payment_gateway The payment gateway object.
	 *
	 * @return bool True if the payment gateway needs setup, false otherwise.
	 */
	public function needs_setup( WC_Payment_Gateway $payment_gateway ): bool {
		try {
			$is_valid_for_use = true;
			if ( is_callable( array( $payment_gateway, 'is_valid_for_use' ) ) ) {
				$is_valid_for_use = wc_string_to_bool( $payment_gateway->is_valid_for_use() );
			}

			return ! $is_valid_for_use || ! $this->is_account_connected( $payment_gateway );
		} catch ( Throwable $e ) {
			// Do nothing but log so we can investigate.
			SafeGlobalFunctionProxy::wc_get_logger()->debug(
				'Failed to determine if gateway needs setup: ' . $e->getMessage(),
				array(
					'gateway'   => $payment_gateway->id,
					'source'    => 'settings-payments',
					'exception' => $e,
				)
			);
		}

		return parent::needs_setup( $payment_gateway );
	}

	/**
	 * Check if the payment gateway has a payments processor account connected.
	 *
	 * @param WC_Payment_Gateway $payment_gateway The payment gateway object.
	 *
	 * @return bool True if the payment gateway account is connected, false otherwise.
	 *              If the payment gateway does not provide the information, it will return true.
	 */
	public function is_account_connected( WC_Payment_Gateway $payment_gateway ): bool {
		try {
			return property_exists( $payment_gateway, 'public_key' ) && ! empty( $payment_gateway->public_key ) &&
				property_exists( $payment_gateway, 'secret_key' ) && ! empty( $payment_gateway->secret_key );
		} catch ( Throwable $e ) {
			// Do nothing but log so we can investigate.
			SafeGlobalFunctionProxy::wc_get_logger()->debug(
				'Failed to determine if gateway has an account connected: ' . $e->getMessage(),
				array(
					'gateway'   => $payment_gateway->id,
					'source'    => 'settings-payments',
					'exception' => $e,
				)
			);
		}

		return parent::is_account_connected( $payment_gateway );
	}

	/**
	 * Try to determine if the payment gateway is in test mode onboarding (aka sandbox or test-drive).
	 *
	 * This is a best-effort attempt, as there is no standard way to determine this.
	 * Trust the true value, but don't consider a false value as definitive.
	 *
	 * @param WC_Payment_Gateway $payment_gateway The payment gateway object.
	 *
	 * @return bool True if the payment gateway is in test mode onboarding, false otherwise.
	 */
	public function is_in_test_mode_onboarding( WC_Payment_Gateway $payment_gateway ): bool {
		// Test mode is actually sandbox mode for Paystack, affecting the used API keys.
		return $this->is_in_test_mode( $payment_gateway );
	}
}

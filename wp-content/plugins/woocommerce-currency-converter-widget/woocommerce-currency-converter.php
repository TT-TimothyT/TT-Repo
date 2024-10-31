<?php
/**
 * Plugin Name: Currency Converter for WooCommerce
 * Plugin URI: https://woo.com/products/currency-converter-widget/
 * Description: Provides a currency selection widget for displaying product prices and totals in different currencies. Conversions are estimated based on data from the Open Exchange Rates API with no guarantee whatsoever of accuracy.
 * Version: 2.2.4
 * Author: Kestrel
 * Author URI: https://kestrelwp.com
 * Text Domain: woocommerce-currency-converter-widget
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.6
 * Tested up to: 6.6
 * Requires Plugins: woocommerce
 *
 * WC requires at least: 7.0
 * WC tested up to: 9.1.2
 * Woo: 18651:0b2ec7cb103c9c102d37f8183924b271
 *
 * Copyright: (c) 2012-2024 Kestrel [hey@kestrelwp.com]
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) or exit;

// Load the class autoloader
require __DIR__ . '/src/Autoloader.php';

if ( ! KoiLab\WC_Currency_Converter\Autoloader::init() ) {
	return;
}

// Plugin requirements
KoiLab\WC_Currency_Converter\Requirements::init();

if ( ! KoiLab\WC_Currency_Converter\Requirements::are_satisfied() ) {
	return;
}

// Define plugin file constant
if ( ! defined( 'WC_CURRENCY_CONVERTER_FILE' ) ) {
	define( 'WC_CURRENCY_CONVERTER_FILE', __FILE__ );
}

/**
 * Initializes the plugin.
 *
 * @since 1.0.0
 * @internal
 *
 * @return void
 */
function wc_currency_converter_init() {

	require_once __DIR__ . '/includes/class-wc-currency-converter.php';

	WC_Currency_Converter::instance();
}

wc_currency_converter_init();

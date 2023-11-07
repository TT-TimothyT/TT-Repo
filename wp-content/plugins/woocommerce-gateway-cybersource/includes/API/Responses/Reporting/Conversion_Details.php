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

namespace SkyVerge\WooCommerce\Cybersource\API\Responses\Reporting;

use SkyVerge\WooCommerce\Cybersource\API\Response;

defined( 'ABSPATH' ) or exit;

/**
 * CyberSource API reporting conversion details response.
 *
 * @since 2.3.0
 */
class Conversion_Details extends Response {


	/**
	 * Gets the returned conversion details.
	 *
	 * @since 2.3.0
	 *
	 * @return Conversion_Detail[]
	 */
	public function get_conversion_details() {

		$details = isset( $this->response_data->conversionDetails ) && is_array( $this->response_data->conversionDetails ) ? $this->response_data->conversionDetails : [];
		$objects = [];

		foreach ( $details as $detail ) {
			$objects[] = new Conversion_Detail( $detail );
		}

		return $objects;
	}


}

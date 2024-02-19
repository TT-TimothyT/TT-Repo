<?php
/**
 * @wordpress-plugin
 * Plugin Name: Gravity Forms Utility
 * Plugin URI: https://gravityplus.pro/gravity-forms-utility
 * Description: A collection of tools to make your life easier when working with Gravity Forms. Have an idea for a new tool? Email support@gravityplus.pro.
 * Version: 3.0.0
 * Author: gravity+
 * Author URI: https://gravityplus.pro
 * Text Domain: gravityformsutility
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package   GFP_Utility
 * @version   3.0.0
 * @author    gravity+ <support@gravityplus.pro>
 * @license   GPL-2.0+
 * @link      https://gravityplus.pro
 * @copyright 2014-2021 gravity+
 *
 * last updated: April 27, 2021
 *
 */

/**
 * Class GFP_GF_Utility
 *
 * Little tools to make life easier when using Gravity Forms
 *
 * @since 1.0.0
 *
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
class GFP_GF_Utility {

	/**
	 * Instance of this class.
	 *
	 * @since    1.2.0
	 *
	 * @var      object
	 */
	private static $_this = null;


	/**
	 * GFP_GF_Utility constructor.
	 *
	 * @since
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function __construct () {

		self::$_this = $this;
	}

	/**
	 * Let's get it started!
	 *
	 * @since
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function run () {

		add_action( 'gform_loaded', array( $this, 'gform_loaded' ) );

	}

	/**
	 * @since 3.0.0
	 */
	public function gform_loaded() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {

			return;

		}

		GFForms::include_addon_framework();

		require_once(  GFP_GF_UTILITY_PATH . '/class-addon.php' );

		GFAddOn::register( 'GFP_Utility_AddOn' );
	}

	/**
	 * Return GF Add-On object
	 *
	 * @return GFP_Utility_AddOn
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @since  3.0.0
	 *
	 */
	public function get_addon_object() {

		return GFP_Utility_AddOn::get_instance();

	}

}

/**
 * Plugin version, used for cache-busting of style and script file references.
 *
 * @since   2.0.0
 */
define( 'GFP_GF_UTILITY_CURRENT_VERSION', '3.0.0' );

define( 'GFP_GF_UTILITY_FILE', __FILE__ );

define( 'GFP_GF_UTILITY_PATH', plugin_dir_path( __FILE__ ) );

define( 'GFP_GF_UTILITY_URL', plugin_dir_url( __FILE__ ) );

$gfutility = new GFP_GF_Utility();

$gfutility->run();
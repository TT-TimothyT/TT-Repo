<?php
/**
 * This is the main bootstrap file for the form manager
 *
 * @author            arichard <arichard@nerdery.com>
 * @package           TrekTravel
 * @subpackage        Plugins
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Trek Travel Form Manager
 * Plugin URI:        http://trektravel.com/
 * Description:       This plugin is used to map froms from Gravity Form to NetSuite
 * Version:           1.0.0
 * Author:            arichard
 * Author URI:        http://nerdery.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       trek-form-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// activate
function activate_trek_form_manager() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-trek-form-manager-activator.php';
}

// deactivate
function deactivate_trek_form_manager() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-trek-form-manager-activator.php';
}

// somewhat important hooks
register_activation_hook( __FILE__, 'activate_trek_form_manager' );
register_deactivation_hook( __FILE__, 'deactivate_trek_form_manager' );

// required files
// main plugin file
require_once plugin_dir_path( __FILE__ ) . 'includes/class-trek-form-manager.php';

// admin only files
//if ( is_admin() ) {
//    require_once plugin_dir_path( __FILE__ ) . '/admin/admin_bootstrap.php' ;
//}

/**
 * kick off the plugin
 *
 * @since 1.0.0
 */
function run_trek_form_manager() {
    $trekFormManager = new Trek_Form_Manager();
    $trekFormManager->run();
}
run_trek_form_manager();
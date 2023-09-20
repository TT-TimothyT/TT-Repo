<?php
/*
 * Plugin name: DX ACF Synchronize
 * Description: The plugin load ACF groups from JSON files which are located in certain folder
 * Author: DevriX
 * Author URI: https://devrix.com
 * Version: 1.0.0
 */

class DX_ACF_Sync {

	private static $acf_exports = WP_CONTENT_DIR . '/acf-exports/';

	public static function hooks() {
		add_filter( 'acf/settings/load_json', array( __CLASS__, 'acf_json_load_point' ) );
		add_filter( 'acf/settings/save_json', array( __CLASS__, 'acf_json_save_point' ) );
	}

	public static function acf_json_load_point( $paths ) {
		unset( $paths[0] );

		$paths[] = self::$acf_exports;

		return $paths;
	}

	public static function acf_json_save_point( $path ) {
		return self::$acf_exports;
	}

}

if ( class_exists( 'acf' ) ) {
	add_action( 'plugins_loaded', function() {
		DX_ACF_Sync::hooks();
	} );
}
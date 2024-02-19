<?php

/**
 * @since 3.0.0
 *
 * @author
 */
class GFPGFU_Create_Page_For_Form {

	/**
	 * GFPGFU_Create_Page_For_Form constructor.
	 *
	 * @since
	 *
	 * @author
	 */
	public function __construct () {

		add_filter( 'gfp_utility_addon_scripts', array( $this, 'gfp_utility_addon_scripts' ) );
		
		add_filter( 'gfp_utility_addon_styles', array( $this, 'gfp_utility_addon_styles' ) );

		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'in_admin_header', array( $this, 'in_admin_header' ) );

	}

	/**
	 *
	 */
	public function admin_init () {

		add_filter( 'gfp_gf_utility_menu', array( $this, 'gfp_gf_utility_menu' ), 10, 2 );

		add_filter( 'gform_noconflict_scripts', array( $this, 'gform_noconflict_scripts' ) );

	}


	public function gfp_utility_addon_styles( $styles ) {

		$styles[] =
			array(
				'handle'  	=> 'gfp_gf_create_page',
				'src'     	=> GFCommon::get_base_url() . '/css/admin.css',
				'enqueue'   => array(
					array(
						'admin_page' => array( 'form_editor', 'form_settings', 'confirmation', 'notification_edit', 'notification_list', 'entry_list', 'entry_detail' ),
					),
				),
			);

		$styles[] =
			array(
				'handle'  	=> 'gfp_gf_create_page-style',
				'src'     	=> GFP_GF_UTILITY_URL . 'tools/create-page-for-form/css/create-page-for-form.css',
				'version' 	=> GFP_GF_UTILITY_CURRENT_VERSION,
				'deps'      => array( 'gfp_gf_create_page' ),
				'enqueue'   => array(
					array(
						'admin_page' => array( 'form_editor', 'form_settings', 'confirmation', 'notification_edit', 'notification_list', 'entry_list', 'entry_detail' ),
					),
				),
			);


		return  $styles;
	}


	public function gfp_utility_addon_scripts( $scripts ) {

		global $gfutility;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$scripts[] =
				array(
					'handle'    => 'gfp_gfutil_create_page_for_form',
					'src'       => trailingslashit( GFP_GF_UTILITY_URL ) . "tools/create-page-for-form/js/create-page-for-form{$suffix}.js",
					'version'   => GFP_GF_UTILITY_CURRENT_VERSION,
					'deps'      => array( 'jquery', 'thickbox' ),
					'in_footer' => false,
					'enqueue'   => array(
						array(
							'admin_page' => array( 'form_editor', 'form_settings', 'confirmation', 'notification_edit', 'notification_list', 'entry_list', 'entry_detail' ),
						),
					),
					'strings'   => array(
						'nonce'   => wp_create_nonce( 'wp_rest' ),
						'spinner' => GFCommon::get_base_url() . '/images/spinner.svg',
						'root' => esc_url_raw( rest_url() ),
						'editor_support' => 'no',
						'form_id' => rgar( $gfutility->get_addon_object()->get_current_form(), 'id' )
					)
			);

		return $scripts;

	}


	public function gfp_gf_utility_menu ( $tools, $form_id ) {

		$tools[] = array( 'name' => 'create-page-for-form', 'label' => __( 'Create Page for Form', 'gravityformsutility' ), 'url' => '#' );


		return $tools;

	}


	public static function gform_noconflict_scripts ( $noconflict_scripts ) {

		$noconflict_scripts = array_merge( $noconflict_scripts, array( 'gfp_gfutil_create_page_for_form' ) );


		return $noconflict_scripts;

	}

	public function in_admin_header() {

		include( trailingslashit( GFP_GF_UTILITY_PATH ) . 'tools/create-page-for-form/views/modal.php' );

	}

}

$gfpgfu_create_page_for_form = new GFPGFU_Create_Page_For_Form();
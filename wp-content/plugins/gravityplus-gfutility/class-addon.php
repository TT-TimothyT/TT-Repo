<?php
/* @package   GFP_GF_Utility\GFP_Utility_Addon
 * @author    Naomi C. Bush for gravity+ <support@gravityplus.pro>
 * @copyright 2021 gravity+
 * @license   GPL-2.0+
 * @since     3.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class GFP_Utility_AddOn
 *
 * @since 3.0.0
 */
class GFP_Utility_AddOn extends GFAddOn {

	/**
	 * @var string Version number of the Add-On
	 */
	protected $_version;
	/**
	 * @var string Gravity Forms minimum version requirement
	 */
	protected $_min_gravityforms_version;
	/**
	 * @var string URL-friendly identifier used for form settings, add-on settings, text domain localization...
	 */
	protected $_slug;
	/**
	 * @var string Relative path to the plugin from the plugins folder
	 */
	protected $_path;
	/**
	 * @var string Full path to the plugin. Example: __FILE__
	 */
	protected $_full_path;
	/**
	 * @var string URL to the App website.
	 */
	protected $_url;
	/**
	 * @var string Title of the plugin to be used on the settings page, form settings and plugins page.
	 */
	protected $_title;
	/**
	 * @var string Short version of the plugin title to be used on menus and other places where a less verbose string
	 *      is useful.
	 */
	protected $_short_title;
	/**
	 * @var array Members plugin integration. List of capabilities to add to roles.
	 */
	protected $_capabilities = array();

	// ------------ Permissions -----------
	/**
	 * @var string|array A string or an array of capabilities or roles that have access to the settings page
	 */
	protected $_capabilities_settings_page = array();

	/**
	 * @var string|array A string or an array of capabilities or roles that have access to the plugin page
	 */
	protected $_capabilities_plugin_page = array();
	/**
	 * @var string|array A string or an array of capabilities or roles that can uninstall the plugin
	 */
	protected $_capabilities_uninstall = array();

	/**
	 * @var GFP_Utility_Addon
	 */
	private static $_instance = null;


	/**
	 * @param $args
	 *
	 * @since  3.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @see    parent
	 *
	 */
	function __construct( $args ) {

		$this->_version                    = $args[ 'version' ];
		$this->_slug                       = $args[ 'plugin_slug' ];
		$this->_min_gravityforms_version   = $args[ 'min_gf_version' ];
		$this->_path                       = $args[ 'path' ];
		$this->_full_path                  = $args[ 'full_path' ];
		$this->_url                        = $args[ 'url' ];
		$this->_title                      = $args[ 'title' ];
		$this->_short_title                = $args[ 'short_title' ];
		$this->_capabilities               = $args[ 'capabilities' ];
		$this->_capabilities_settings_page = $args[ 'capabilities_settings_page' ];
		$this->_capabilities_form_settings = $args[ 'capabilities_form_settings' ];
		$this->_capabilities_uninstall     = $args[ 'capabilities_uninstall' ];

		parent::__construct();

	}

	/**
	 * Needed for GF Add-On Framework functions
	 *
	 * @return GFP_Utility_Addon|null
	 * @since 3.0.0
	 *
	 */
	public static function get_instance() {

		if ( self::$_instance == null ) {

			self::$_instance = new self(
				array(
					'version'                    => GFP_GF_UTILITY_CURRENT_VERSION,
					'min_gf_version'             => '2.5.0',
					'plugin_slug'                => 'gravityformsutility',
					'path'                       => plugin_basename( GFP_GF_UTILITY_FILE ),
					'full_path'                  => GFP_GF_UTILITY_FILE,
					'title'                      => 'Gravity Forms Utility',
					'short_title'                => 'Utility',
					'url'                        => 'https://gravityplus.pro',
					'capabilities'               => array(
						'gravityforms_utility_plugin_settings',
						'gravityforms_utility_form_settings',
						'gravityforms_utility_uninstall'
					),
					'capabilities_settings_page' => array( 'gravityforms_utility_plugin_settings' ),
					'capabilities_form_settings' => array( 'gravityforms_utility_form_settings' ),
					'capabilities_uninstall'     => array( 'gravityforms_utility_uninstall' )
				) );

		}

		return self::$_instance;

	}

	/**
	 * @since 3.0.0
	 *
	 * @see   parent
	 */
	public function pre_init() {

		parent::pre_init();

		$this->load_tools();

	}

	/**
	 * Load all of the tools
	 *
	 * @since  3.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function load_tools() {

		require_once( GFP_GF_UTILITY_PATH . '/class-helper.php' );

		require_once( GFP_GF_UTILITY_PATH . '/class-toolbar-menu.php' );

		$settings = $this->get_plugin_settings();

		if ( empty( $settings ) ) {

			$settings = [
				'toggle_all_fields_required'     => '1',
				'show_page_on_toolbar'           => '1',
				'prevent_entry_creation'         => '1',
				'show_feed_on_form_list'         => '1',
				'show_page_on_form_list'         => '1',
				'redact'                         => '1',
				'manually_process_feeds'         => '1',
				'process_feed_entry_update'      => '1',
				'hide_form_user_submitted'       => '1',
				'record_sent_notifications'      => '1',
				'send_notification_entry_update' => '1',
				'create_page_for_form'           => '1',
			];

			update_option( 'gravityformsaddon_' . $this->_slug . '_settings', $settings );

		}

		foreach ( $settings as $tool_name => $value ) {

			$this->load_tool_files( $settings, $tool_name );
		}

	}

	/**
	 * Load the file for each tool
	 *
	 * @param $settings
	 * @param $tool_name
	 *
	 * @since 3.0.0
	 */
	private function load_tool_files( $settings, $tool_name ) {

		if ( '1' === rgar( $settings, $tool_name ) ) {

			require_once( GFP_GF_UTILITY_PATH . '/tools/' . str_replace( '_', '-', $tool_name ) . '.php' );

		}

	}

	/**
	 * @return array|array[]
	 *
	 * @since 3.0.0
	 *
	 * @see   parent
	 */
	public function styles() {

		$styles = array(
			array(
				'handle'  => 'gfutility_plugin_settings',
				'src'     => GFP_GF_UTILITY_URL . "/css/plugin_settings.css",
				'version' => $this->_version,
				'enqueue' => array(
					array( 'admin_page' => array( 'plugin_settings' ) ),
				)
			),
		);

		$styles = apply_filters( 'gfp_utility_addon_styles' , $styles );

		return array_merge( parent::styles(), $styles );
	}

	/**
	 * @since 3.0.0
	 *
	 * @return array|array[]
	 *
	 * @see parent
	 */
	public function scripts() {

		$scripts = apply_filters( 'gfp_utility_addon_scripts' , array() );


		return array_merge( parent::scripts() , $scripts );

	}

	/**
	 *
	 * @return string
	 * @see   parent
	 *
	 * @since 3.0.0
	 *
	 */
	public function get_menu_icon() {

		return file_get_contents( GFP_GF_UTILITY_PATH . 'images/gravity_forms_utility.svg' );

	}

	/**
	 * @return array[]
	 * @see   parent
	 *
	 * @since 3.0.0
	 *
	 */
	public function plugin_settings_fields() {

		return array(
			array(
				'title'  => esc_html__( 'Utility Settings', 'gravityformsutility' ),
				'fields' => array(
					array(
						'type' => 'hidden'
					)
				)
			),
			array(
				'fields' => array(
					array(
						'name'    => 'toggle_all_fields_required',
						'id'      => 'toggle_all_fields_required',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Toggle all fields required', 'gravityformsutility' ),
						'tooltip' => __( 'Add option to toggle all fields required (or unrequired) on a form.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),
			array(
				'fields' => array(
					array(
						'name'    => 'show_page_on_toolbar',
						'id'      => 'show_page_on_toolbar',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Show page on toolbar', 'gravityformsutility' ),
						'tooltip' => __( 'Quickly navigate to the page where a form is embedded, from the Gravity Forms toolbar.', 'gravityformsutility' ),

					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"

			),

			array(
				'fields' => array(
					array(
						'name'    => 'prevent_entry_creation',
						'id'      => 'prevent_entry_creation',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Prevent entry creation', 'gravityformsutility' ),
						'tooltip' => __( 'Add form setting to prevent entries from being created for a form.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"

			),

			array(
				'fields' => array(
					array(
						'name'    => 'show_feed_on_form_list',
						'id'      => 'show_feed_on_form_list',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Show feed on form list', 'gravityformsutility' ),
						'tooltip' => __( 'On the form list, show all feeds connected to a form.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),

			array(
				'fields' => array(
					array(
						'name'    => 'show_page_on_form_list',
						'id'      => 'show_page_on_form_list',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Show page on form list', 'gravityformsutility' ),
						'tooltip' => __( 'On the form list, show all pages where a form is embedded.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),
			array(
				'fields' => array(
					array(
						'name'    => 'redact',
						'id'      => 'redact',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Redact', 'gravityformsutility' ),
						'tooltip' => __( 'Add field option to redact data for form field.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),
			array(
				'fields' => array(
					array(
						'name'    => 'manually_process_feeds',
						'id'      => 'manually_process_feeds',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Manually process feeds', 'gravityformsutility' ),
						'tooltip' => __( 'Add option to manually process a feed for an entry.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),
			array(
				'fields' => array(
					array(
						'name'    => 'process_feed_entry_update',
						'id'      => 'process_feed_entry_update',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Process feed on entry update', 'gravityformsutility' ),
						'tooltip' => __( 'Add feed option to automatically process a feed when an entry is updated.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),

			array(
				'fields' => array(
					array(
						'name'    => 'hide_form_user_submitted',
						'id'      => 'hide_form_user_submitted',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Hide user-submitted form', 'gravityformsutility' ),
						'tooltip' => __( 'Add form setting to hide a form if a user has already submitted it.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),

			array(
				'fields' => array(
					array(
						'name'    => 'record_sent_notifications',
						'id'      => 'record_sent_notifications',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Record sent notifications', 'gravityformsutility' ),
						'tooltip' => __( 'Add an entry note when a notification is sent for an entry.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),
			array(
				'fields' => array(
					array(
						'name'    => 'send_notification_entry_update',
						'id'      => 'send_notification_entry_update',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Send notification on entry update', 'gravityformsutility' ),
						'tooltip' => __( 'Add `Entry is updated` notification event to send notification when an entry is updated.', 'gravityformsutility' ),
					),
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),
			array(
				'fields' => array(
					array(
						'name'    => 'create_page_for_form',
						'id'    => 'create_page_for_form',
						'type'    => 'toggle',
						'toggle_label'   => __( 'Create page for form', 'gravityformsutility' ),
						'tooltip' => __( 'Quickly create page and embed form, from the Gravity Forms toolbar.', 'gravityformsutility' ),
					)
				),
				'style' => 'grid-column: span 1;',
				'class'=> "utility-settings"
			),

		);
	}

	/**
	 * Toggle button markup
	 *
	 * @param $field
	 * @param $is_false
	 *
	 * @return string
	 *@since 3.0.0
	 *
	 */
	public function settings_toggle( $field, $is_false ){

		$html='		<div class="addon-logo dashicons"><i class="dashicons dashicons-admin-tools"></i></div>
					<div class="utility-setting-text">
						<h4 class="gform-settings-panel__title">'. sprintf( esc_html__( '%s', 'gravityformsutility' ), $field->toggle_label ) .'</h4>
						<div>'. sprintf( esc_html__( ' %s', 'gravityformsutility' ), $field->tooltip ) . '</div>
					</div>
					<div class="utility-toggle">
						'. $field->prepare_markup().'
					</div>
				';

		return $html;
	}

	/**
	 * @since 3.0.0
	 *
	 * @see parent
	 * 
	 */
	public function render_uninstall() {

		do_action( "gform_{$this->_slug}_render_uninstall", $this );

		echo "<span class='render-{$this->_slug}-uninstall'>";

		parent::render_uninstall();
		
		echo "</span>";

		echo "<script>

				jQuery(document).ready(function(){

					jQuery('span.render-{$this->_slug}-uninstall div.alert.error').addClass('inline');
					
				})
				
			</script>";

		
	}

}
<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Trek_Form_Manager
 * @subpackage Trek_Form_Manager/admin
 *
 * @link  http://trektravel.com
 * @since 1.0.0
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Trek_Form_Manager
 * @subpackage Trek_Form_Manager/admin
 * @author     arichard <arichard@nerdery.com>
 */
class Trek_Form_Manager_Settings
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $trek_form_manager       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $trek_form_manager, $version )
    {

        $this->plugin_name = $trek_form_manager;
        $this->version = $version;

    }

    /**
     * Add a menu to that admin section for this plugin
     */
    public function setup_plugin_options_menu()
    {
        add_menu_page(
            __( 'Trek Travel Form Manager', 'trek-form-manager' ),
            __( 'TT Form Manager', 'trek-form-manager' ),
            'manage_options',
            'trek_form_manager_options',
            array( $this, 'render_settings_page_content')
        );

        add_submenu_page(
            'trek_form_manager_options',
            __( 'General Settings', 'trek-form-manager' ),
            __( 'General Settings', 'trek-form-manager' ),
            'manage_options',
            'trek_form_manager_options',
            array( $this, 'render_settings_page_content')
        );

        add_submenu_page(
            'trek_form_manager_options',
            __( 'Add Form Mapping', 'trek-form-manager' ),
            __( 'Add Form Mapping', 'trek-form-manager' ),
            'manage_options',
            'trek_form_manager_add_form_mapping',
            array( $this, 'render_add_mapping_page_content')
        );

    }

    /**
     * Renders a simple page to display for the theme menu defined above.
     * 
     * @param string $active_tab the active tab, if any
     * 
     * @return void nothing to return
     */
    public function render_settings_page_content( $active_tab = '' )
    {
        ?>
        <!-- Create a header in the default WordPress 'wrap' container -->
        <div class="wrap">

            <h2><?php _e( 'Trek Form Manager Settings', 'trek-form-manager' ); ?></h2>
            <?php

            settings_errors();

            if ( isset( $_GET['tab'] ) ) {
                $active_tab = $_GET['tab'];
            } else {
                switch( $active_tab ) {
                    case 'form_list';
                        $active_tab = 'form_list';
                        break;
                    case 'general_settings':
                    default:
                        $active_tab = 'general_settings';
                        break;
                }
            }

            ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=trek_form_manager_options&tab=general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'General Settings', 'trek-form-manager' ); ?></a>
                <a href="?page=trek_form_manager_options&tab=form_list" class="nav-tab <?php echo $active_tab == 'form_list' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Managed Forms', 'trek-form-manager' ); ?></a>
            </h2>

            <form method="post" action="options.php">
                <?php

                $showSubmitButton = true;
                switch ( $active_tab ) {
                    case 'form_list':
                        settings_fields( 'trek_form_manager_form_list' );
                        do_settings_sections( 'trek_form_manager_form_list' );

                        $showSubmitButton = false;
                        break;
                    case 'general_settings':
                    default:
                        settings_fields( 'trek_form_manager_general_settings' );
                        do_settings_sections( 'trek_form_manager_general_settings' );
                        break;
                }

                if ( $showSubmitButton ) {
                    submit_button();
                }

                echo '<br/><br/><strong><a href="?page=trek_form_manager_options&tab=' . $active_tab . '&do_export_ttfm_data=all">Export Mapped Forms</a></strong>';

                ?>
            </form>

        </div>
        <?php
    }

    /**
     * Render the Mappings form page, this is the main beast of this plugin
     *
     * @param string $active_tab active tab if needed
     *
     * @return string error message if any
     */
    public function render_add_mapping_page_content( $active_tab = '' ) {
        $error = null;
        $success = null;
        $warning = null;

        // process form submission
        if ( ! empty( $_POST['action'] ) && 'save_tt_form_map' === $_POST['action'] ) {
            if ( wp_verify_nonce( $_POST['_wpnonce'], 'save_tt_form_map_nonce') ) {
                if ( ! empty( $_POST['form_mapping'] ) ) {

                    // proceed to save form mapping
                    $results = $this->process_add_form_mapping( $_POST['form_mapping'] );

                    // in case we didn't get an error message back
                    $error = 'There was a general issue processing the form, please make sure the data you entered is correct';
                    if ( empty( $results['status'] ) || 'error' == $results['status'] ) {
                        $error   = ( ! empty( $results['message'] ) ? $results['message'] : $error );
                        $success = '';
                        $warning = '';
                    } elseif ( 'warning' == $results['status'] ) {
                        $error   = '';
                        $success = '';
                        $warning = ( ! empty( $results['message'] ) ? $results['message'] : '' );
                    } else {
                        $error   = '';
                        $success = 'The form mapping was saved successfully!';
                        $warning = '';
                    }
                } else {
                    return 'Nothing to save. No data was submitted.';
                }
            } else {
                // Yikes! The user needs to resubmit the form, we should try to restore as much
                // data as possible to the form in case the form was really big (I'm looking at
                // you Guide Application)
                $error = 'WPNONCE Expired - Please re-submit the form';
            }
        }

        // the list of forms in gravity forms
        $forms = $this->getGravityFormsList();

        // in case we want to pre-select a form
        $gfFormId = '';

        if ( ! empty( $_POST['form_mapping']['tt_form_add_gf_form_id'] ) ) {
            $gfFormId = $_POST['form_mapping']['tt_form_add_gf_form_id'];
        } elseif ( ! empty( $_GET['tt_form_map_form_id'] ) ) {
            $gfFormId = $_GET['tt_form_map_form_id'];
        }

        // only passed when editing an existing mapping
        $formMapId = ( ! empty( $_GET['tt_form_map_id'] ) ? $_GET['tt_form_map_id'] : '' );

        ?>
        <div class="wrap">

            <h2><?php _e( 'Trek Form Manager Add Form Mapping', 'trek-form-manager' ); ?></h2>

            <p>Use this page to map a Gravity Form to a NetSuite form. To get started, select a Gravity Form below.</p>

            <?php if ( ! empty( $success ) ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html( $success ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $warning ) ) : ?>
                <div class="notice notice-warning is-dismissible">
                    <p><?php echo esc_html( $warning ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $error ) ) : ?>
                <div class="notice notice-error">
                    <p><?php echo esc_html( $error ); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="?page=trek_form_manager_add_form_mapping">
                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('save_tt_form_map_nonce'); ?>" />
                <input type="hidden" name="action" value="save_tt_form_map" />
                <input type="hidden" name="map_option_id" id="tt_form_map_option_id" value="<?php echo esc_attr( $formMapId ); ?>" />
                <div id="gravity-form-list">
                    <?php if ( ! empty( $forms ) ) : ?>
                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="tt_form_add_form_id">Gravity Form (local)</label></th>
                                    <td>
                                        <select id="tt_form_add_form_id" name="form_mapping[tt_form_add_gf_form_id]">
                                            <option></option>
                                            <?php foreach ( $forms as $form ) : ?>
                                                <?php
                                                $selected = '';
                                                if ( ! empty( $gfFormId ) && $form['id'] == $gfFormId ) {
                                                    $selected = ' selected="selected"';
                                                }
                                                ?>
                                                <option value="<?php echo esc_attr( $form['id'] ); ?>"<?php echo $selected; ?>><?php echo esc_html( $form['title'] ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="tt_form_add_form_ns_id">Netsuite Form ID (remote)</label></th>
                                    <td>
                                        <input type="text" id="tt_form_add_form_ns_id" name="form_mapping[tt_form_add_ns_form_id]" value="<?php echo ( ! empty( $nsFormId) ? esc_attr( $nsFormId ) : '' ); ?>" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div id="form-field-mapping-list"></div>

                <?php

                submit_button( 'Save' );

                ?>
            </form>
        </div>
        <?php
        return '';
    }
    /**
     * Initializes the theme's display options page by registering the Sections,
     * Fields, and Settings.
     *
     * This function is registered with the 'admin_init' hook.
     */
    public function initialize_general_settings()
    {

        // If the theme options don't exist, create them.
        if( false === get_option( 'trek_form_manager_general_settings' ) ) {
            $default_array = $this->default_general_settings();
            add_option( 'trek_form_manager_general_settings', $default_array );
        }

        $this->checkAndDoExport();

        add_settings_section(
            'general_settings_section',                   // ID used to identify this section and with which to register options
            __( 'Display Options', 'trek-form-manager' ), // Title to be displayed on the administration page
            array( $this, 'general_settings_callback'),   // Callback used to render the description of the section
            'trek_form_manager_general_settings'          // Page on which to add this section of options
        );

        // Next, we'll introduce the fields for toggling the visibility of content elements.
        // NetSuite URL
        add_settings_field(
            'netsuite_url',                            // ID used to identify the field throughout the theme
            __( 'NetSuite URL', 'trek-form-manager' ), // The label to the left of the option interface element
            array( $this, 'netsuite_url_callback'),    // The name of the function responsible for rendering the option interface
            'trek_form_manager_general_settings',      // The page on which this option will be displayed
            'general_settings_section',                // The name of the section to which this field belongs
            array(                                     // The array of arguments to pass to the callback. In this case, just a description.
                __( 'Enter your NetSuite instance URL.', 'trek-form-manager' ),
            )
        );

        // NetSuite API Key
        add_settings_field(
            'netsuite_api_key',
            __( 'NetSuite API Key', 'trek-form-manager' ),
            array( $this, 'netsuite_api_key_callback'),
            'trek_form_manager_general_settings',
            'general_settings_section',
            array(
                __( 'Enter your NetSuite API Key.', 'trek-form-manager' ),
            )
        );

        // NetSuite API Key
        add_settings_field(
            'netsuite_compid',
            __( 'NetSuite Compid', 'trek-form-manager' ),
            array( $this, 'netsuite_compid_callback'),
            'trek_form_manager_general_settings',
            'general_settings_section',
            array(
                __( 'Enter your NetSuite compid (found in form urls).', 'trek-form-manager' ),
            )
        );

        // Finally, we register the fields with WordPress
        register_setting(
            'trek_form_manager_general_settings',
            'trek_form_manager_general_settings'
        );

    }

    /**
     * Initialize the form list page/tab
     */
    public function initialize_form_list()
    {
        if( false === get_option( 'trek_form_manager_form_list' ) ) {
            $default_array = $this->default_form_list_settings();
            add_option( 'trek_form_manager_form_list', $default_array );
        }

        add_settings_section(
            'form_list_section',
            __('Current Forms Being Managed', 'trek-form-manager'),
            array( $this, 'form_list_callback'),
            'trek_form_manager_form_list'
        );

        register_setting(
            'trek_form_manager_form_list',
            'trek_form_manager_form_list'
        );
    }

    /**
     * get the default "general" settings
     *
     * @return array default settings
     */
    public function default_general_settings()
    {
        return array();
    }

    /**
     * get the default "form_list" settings
     *
     * @return array default settings
     */
    public function default_form_list_settings()
    {
        return array();
    }

    /**
     * setup the general settings page
     */
    public function general_settings_callback()
    {
        $options = get_option('trek_form_manager_general_settings');
        echo '<p>' . __( 'Configure the general settings', 'trek-form-manager' ) . '</p>';
    }

    /**
     * renders input box for netsuite url
     *
     * @param mixed $args used to configure input
     */
    public function netsuite_url_callback( $args )
    {
        $options = get_option('trek_form_manager_general_settings');

        $html = '<input type="text" id="netsuite_url" name="trek_form_manager_general_settings[netsuite_url]" value="' . ( ! empty( $options['netsuite_url'] ) ? esc_attr( $options['netsuite_url'] ) : '' ) . '" />';
        $html .= '<label for="netsuite_url">&nbsp;'  . $args[0] . '</label>';

        echo $html;
    }

    /**
     * renders input box for netsuite api key
     *
     * @param mixed $args used to configure input
     */
    public function netsuite_api_key_callback( $args )
    {
        $options = get_option('trek_form_manager_general_settings');

        $html = '<input type="text" id="netsuite_api_key" name="trek_form_manager_general_settings[netsuite_api_key]" value="' . ( ! empty( $options['netsuite_api_key'] ) ? esc_attr( $options['netsuite_api_key'] ) : '' ) . '" />';
        $html .= '<label for="netsuite_api_key">&nbsp;'  . $args[0] . '</label>';

        echo $html;
    }

    /**
     * renders input box for netsuite compid
     *
     * @param mixed $args used to configure input
     */
    public function netsuite_compid_callback( $args )
    {
        $options = get_option('trek_form_manager_general_settings');

        $html = '<input type="text" id="netsuite_compid" name="trek_form_manager_general_settings[netsuite_compid]" value="' . ( ! empty( $options['netsuite_compid'] ) ? esc_attr( $options['netsuite_compid'] ) : '' ) . '" />';
        $html .= '<label for="netsuite_compid">&nbsp;'  . $args[0] . '</label>';

        echo $html;
    }

    /**
     * get a list of gravity forms
     *
     * @return mixed list of gravity forms
     */
    public function getGravityFormsList()
    {
        return Trek_Form_Manager_Mapper::getGravityFormsList();
    }

    /**
     * render the form list page/tab
     */
    public function form_list_callback()
    {
        $html = '<p>These are forms currently mapped in the system. <a href="?page=trek_form_manager_add_form_mapping">Add a Form</a></p>';
        $html .= '<input type="hidden" name="page" value="' . $_REQUEST['page'] .'" />';
        echo $html;

        $table = new Trek_Form_Manager_Mapper_List();
        $table->prepare_items();
        $table->display();
    }

    /**
     * process the form submission and save the form map to the database
     *
     * expected form data structure:
     *
     * array(
     *     tt_form_add_ns_form_id = int (netsuite form id),
     *     tt_form_add_gf_form_id = int (gravity form id),
     *     // repeated for each form field {#} is the gravity form field ID
     *     form_mapping[gff_{#}][gf_lbl] = string (gravity form field label),
     *     form_mapping[gff_{#}][ns_name] = string (netsuite field name),
     *
     *     // repeatable for each desired operation {#} is an arbitrary value
     *     // used for unique identification of the row (default consecutive numbers)
     *     form_mapping[ext_r{#}][lbl] = string (extra field name)
     *     form_mapping[ext_r{#}][data] = string (extra field value)
     * )
     *
     * return structure if array:
     *
     * array(
     *    'status'  => ['error'|'success'|'warning'] (required),
     *    'message' => String (optional)
     * )
     *
     * @param mixed $mapping the post data containg the form mapping
     * @return array|string the status of the same and optional messaging
     */
    public function process_add_form_mapping( $mapping )
    {
        $extra_field_prefix = 'ext_r';
        $form_field_prefix  = 'gff_';
        $nsFormId           = ( ! empty( $mapping['tt_form_add_ns_form_id'] ) ? $mapping['tt_form_add_ns_form_id'] : '' );
        $gfFormId           = ( ! empty( $mapping['tt_form_add_gf_form_id'] ) ? $mapping['tt_form_add_gf_form_id'] : '' );

        if ( empty( $nsFormId ) ) {
            return 'Please specify a NetSuite form ID';
        }

        if ( empty( $gfFormId ) ) {
            return 'Please select a Gravity Form from the list';
        }

        $saveData = array(
            'gravity_form_id'  => $gfFormId,
            'netsuite_form_id' => $nsFormId,
            'fields'           => array(),
            'extrafields'      => array(),
        );

        $error = array();
        foreach ( $mapping as $field => $value ) {

            // basic form mapping
            if ( 0 === strpos( $field, $form_field_prefix ) ) {
                $gfFieldId = substr( $field, strlen( $form_field_prefix ) );

                if ( empty( $value ) ) {
                    $error[] = 'Field Mapping for field ID ' . $gfFieldId . ' is required.';
                    continue;
                }

                if ( is_scalar( $value ) || empty( $value['gf_lbl'] ) ) {
                    $error[] = 'Field Mapping for field ID ' . $gfFieldId . ' is invalid.';
                    continue;
                }

                $saveData['fields']['gf_field_id_' . $gfFieldId] = array(
                    'gf_field_id'    => $gfFieldId,
                    'gf_field_label' => $value['gf_lbl'],
                    'ns_field_name'  => $value['ns_name'],
                );

                continue;
            }

            // handle "extra" fields
            if ( 0 === strpos( $field, $extra_field_prefix ) ) {
                if ( !is_array( $value ) || empty( $value['lbl'] ) || empty( $value['data'] ) ) {
                    continue;
                }

                $rowId = substr( $field, strlen( $extra_field_prefix ) );

                $saveData['extrafields'][] = array(
                    'id' => $rowId,
                    'fieldName' => $value['lbl'],
                    'fieldData' => $value['data'],
                );

                continue;
            }
        }

        if ( ! empty( $error ) ) {
            return array( 'status' => 'error', 'message' => implode( '</p><p>', $error ) );
        }

        if ( ! empty( $saveData ) ) {
            $result = Trek_Form_Manager_Mapper::saveMap( $saveData );
            if ( $result ) {
                return array( 'status' => 'success' );
            }
            return array( 'status' => 'warning', 'message' => 'There are no changes to save.');
        }

        return array( 'status' => 'error' );
    }

    /**
     * Check if the correct params exist then exports the form mapping configurations
     *
     * @return bool false if called but not supposed to export
     */
    protected function checkAndDoExport()
    {
        if ( empty( $_GET['do_export_ttfm_data'] ) ) {
            return false;
        }

        $ids = array();
        if ( 'all' !== $_GET['do_export_ttfm_data'] ) {
            $id_list = explode( ',', $_GET['do_export_ttfm_data'] );
            $ids = array_map( 'intval', $id_list );
        }

        Trek_Form_Manager_Mapper::exportMaps( $ids );
        return true;
    }
}
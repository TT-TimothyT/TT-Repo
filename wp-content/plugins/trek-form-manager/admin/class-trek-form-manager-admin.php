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
class Trek_Form_Manager_Admin {

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $trek_form_manager The name of this plugin.
     * @param string $version The version of this plugin.
     *
     * @since 1.0.0
     */
    public function __construct( $trek_form_manager, $version )
    {
        $this->plugin_name = $trek_form_manager;
        $this->version     = $version;

        $this->load_dependencies();
        $this->register_ajax();

    }

    /**
     * Load the required dependencies for the Admin facing functionality.
     *
     * Include the following files that make up the plugin:
     *
     * - Trek_Form_Manager_Admin_Settings. Registers the admin settings and page.
     *
     * @since 1.0.0
     *
     * @access private
     *
     * @return void
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-trek-form-manager-settings.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-trek-form-manager-mapper-table.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Trek_Form_Manager_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Trek_Form_Manager_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/trek-form-manager-admin.css',
            array(),
            $this->version, 'all'
        );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Trek_Form_Manager_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Trek_Form_Manager_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/trek-form-manager-admin.js',
            array('jquery'),
            $this->version,
            false
        );

    }

    /**
     * Register ajax methods.
     *
     * @return void
     */
    public function register_ajax()
    {
        add_action('wp_ajax_tt_form_add_fetch_fields', array($this, 'callback_fetch_form_fields'));
        add_action('wp_ajax_nopriv_tt_form_add_fetch_fields', array($this, 'callback_fetch_form_fields'));

        add_action('wp_ajax_tt_form_add_fetch_mapped_fields', array($this,'callback_fetch_mapped_form_fields'));
        add_action('wp_ajax_nopriv_tt_form_add_fetch_mapped_fields',array($this, 'callback_fetch_mapped_form_fields'));
    }

    /**
     * Ajax callback to fetch form fields for form based on data.
     *
     * @param mixed $data data passed to callback
     *
     * @return void
     */
    public function callback_fetch_form_fields($data)
    {
        $gfpath = str_replace(
            'trek-form-manager/admin',
            'gravityforms',
            plugin_dir_path(__FILE__)
        );

        require_once $gfpath . 'gravityforms.php';

        // proper header
        header('Content-type: application/json');

        // default to an error state, this will be updated as needed later on
        $response = $this->get_ajax_error_status();

        if (empty($_POST['formid'])) {
            echo json_encode($response);
            exit;
        }

        $formId     = intval($_POST['formid']);
        $mappedData = Trek_Form_Manager_Mapper::getMappingForGravityForm($formId);

        $form_meta = GFFormsModel::get_form_meta_by_id($formId);

        $fields = array();

        // make sure we got some meta data
        if (empty($form_meta)
            || ! is_array($form_meta)
            || empty($form_meta[0])
            || ! is_array($form_meta[0])
            || empty($form_meta[0]['fields'])
        ) {
            echo json_encode($response);
            exit;
        }

        foreach ( $form_meta[0]['fields'] as $field ) {
            $ns_field_name = '';

            if (!empty($mappedData)) {
                $ns_field_name = $mappedData->fields->{'gf_field_id_' . $field->id}->ns_field_name;
            }

            $fields['gf_field_id_' . $field->id] = array(
                'gf_field_id'    => $field->id,
                'gf_field_label' => $field->label,
                'ns_field_name'  => $ns_field_name,
            );
        }

        $extraFields = array();
        if (!empty($mappedData->extrafields)) {
            foreach ( $mappedData->extrafields as $field ) {
                $extraFields[] = array(
                    'fieldName' => $field->fieldName,
                    'fieldData' => $field->fieldData,
                    'id'        => $field->id,
                );
            }
        }

        if (!empty($fields)) {
            $response           = $this->get_ajax_success_status();
            $response['fields'] = $fields;

            if (!empty($extraFields)) {
                $response['extrafields'] = $extraFields;
            }

            if (!empty($mappedData)) {
                $response['gravity_form_id']  = $mappedData->gravity_form_id;
                $response['netsuite_form_id'] = $mappedData->netsuite_form_id;
            }
        }

        echo json_encode($response);

        // if you don't do this your response will have a fun to debug 0
        exit;
    }

    /**
     * Callback to fetch a known mapped set of field data.
     *
     * @return void
     */
    public function callback_fetch_mapped_form_fields()
    {
        // proper header
        header('Content-type: application/json');

        $response = array();

        if (empty($_POST['mapid'])) {
            echo json_encode($this->get_ajax_error_status());
            exit;
        }


        $mapId      = intval($_POST['mapid']);
        $mappedData = Trek_Form_Manager_Mapper::getMappingByOptionId($mapId);

        if (empty($mappedData)) {
            echo json_encode($this->get_ajax_error_status());
            exit;
        }

        $unpackedMapData = json_decode($mappedData);

        $successMessage = $this->get_ajax_success_status();

        $unpackedMapData->status = $successMessage['status'];

        echo json_encode($unpackedMapData);
        exit;
    }

    /**
     * Get a standardized error status.
     *
     * @return array error status
     */
    protected function get_ajax_error_status()
    {
        return array( 'status' => 'error' );
    }

    /**
     * Get a standardized success status.
     *
     * @return array success status
     */
    protected function get_ajax_success_status()
    {
        return array( 'status' => 'success' );
    }
}

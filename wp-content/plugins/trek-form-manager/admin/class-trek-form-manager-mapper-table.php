<?php
/**
 * This file contains a WP_List_Table for form maps
 *
 * @package    Trek_Form_Manager
 * @subpackage Trek_Form_Manager/admin
 *
 * @author Adam Richards <arichard@nerdery.com>
 *
 * @link  http://trektravel.com
 * @since 1.0.0
 */

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 * Class Trek_Form_Manager_Mapper_List
 *
 * @author Adam Richards <arichard@nerdery.com>
 */
class Trek_Form_Manager_Mapper_List extends WP_List_Table
{
    /**
     * Trek_Form_Manager_Mapper_List constructor.
     */
    public function __construct()
    {
        parent::__construct(
            [
                'singular' => __('Form Map', 'trek-form-manager'),
                'plural'   => __('Form Maps', 'trek-form-manager'),
                'ajax'     => false,
            ]
        );
    }

    /**
     * Message to display if no results are found.
     */
    public function no_items()
    {
        _e('No form maps found', 'trek-form-manager');
    }

    /**
     * Handle column "cb".
     *
     * @param object $item the current row being worked with
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            'tt_form_map',
            $item['option_id']
        );
    }

    /**
     * Default way to handle columns.
     *
     * @param mixed $item current row
     * @param string $column_name name of the current column
     *
     * @return string configured cell
     */
    public function column_default($item, $column_name)
    {
        if ('edit' === $column_name) {
            return '<a href="?page=trek_form_manager_add_form_mapping&tt_form_map_id=' . $item['option_id'] . '">Edit</a>';
        }

        return $item[$column_name];
    }

    /**
     * Handle "gf_form_name" column.
     *
     * @param array $item current row
     *
     * @return mixed configured cell
     */
    public function column_gf_form_name($item)
    {
        return $item['gf_form_name'];
    }

    /**
     * Handle "gf_form_id" column.
     *
     * @param array $item current row
     *
     * @return mixed configured cell
     */
    public function column_gf_form_id($item)
    {
        return intval($item['gf_form_id']);
    }

    /**
     * Handle "ns_form_id" column.
     *
     * @param array $item current row
     *
     * @return mixed configured cell
     */
    public function column_ns_form_id($item)
    {
        return intval($item['ns_form_id']);
    }

    /**
     * Handle "option_id" column.
     *
     * @param array $item current row
     *
     * @return mixed configured cell
     */
    public function column_option_id($item)
    {
        return intval($item['option_id']);
    }

    /**
     * Get list of columms to display.
     *
     * @return array list of columns to display
     */
    public function get_columns()
    {
        return array(
            // 'cb' => '<input type="checkbox" />',
            'gf_form_id'   => 'GF ID',
            'gf_form_name' => 'Gravity Form Name',
            'ns_form_id'   => 'NetSuite Form ID',
            'edit'         => '',
        );
    }

    /**
     * Get list of hidden columns.
     *
     * @return array list of hidden columns
     */
    public function get_hidden_columns()
    {
        return array(
            'option_id' => 'Map ID',
        );
    }

    /**
     * Get list of bulk actions.
     *
     * @return array list of bulk actions
     */
    public function get_bulk_actions()
    {
        return array();
    }

    /**
     * Process the bulk actions.
     *
     * @return void
     */
    public function process_bulk_actions() {
        return;
    }

    /**
     * Prepares data for display, configures table.
     *
     * @return void
     */
    public function prepare_items()
    {
        $this->_column_headers = array(
            $this->get_columns(),
            $this->get_hidden_columns(),
        );

        $this->process_bulk_actions();

        $perPage     = 25;
        $currentPage = $this->get_pagenum();
        $totalItems  = Trek_Form_Manager_Mapper::getMapCount();

        $this->set_pagination_args(
            array(
                'total_items' => $totalItems,
                'pre_page'    => $perPage,
                'total_pages' => ceil(($totalItems / $perPage )),
            )
        );

        $this->items = Trek_Form_Manager_Mapper::getMapsWithNames($perPage, $currentPage);

        return;
    }
}
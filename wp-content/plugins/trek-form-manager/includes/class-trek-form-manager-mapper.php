<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link       http://trektravel.com
 * @since      1.0.0
 *
 * @package    Trek_Form_Manager
 * @subpackage Trek_Form_Manager/includes
 */

/**
 * Classed used for working with the mappings (storage, retrieval, etc.)
 *
 * Basically the Mapper Model
 *
 * @package    Trek_Form_Manager
 * @subpackage Trek_Form_Manager/includes
 * @author     arichard <arichard@nerdery.com>
 */
class Trek_Form_Manager_Mapper {

    /**
     * this is the key prefix used to save and retrieve the form mapping
     */
    const OPTION_PREFIX = 'tt_form_map_';

    /**
     * Trek_Form_Manager_Mapper constructor. unused
     */
    public function __construct() {}

    /**
     * get the mapping of a form by the form ID
     *
     * @param int $formId the id of the form to get the mapping for
     * @return mixed the mapping or empty
     */
    public static function getMappingForGravityForm( $formId )
    {
        $out = get_option( self::OPTION_PREFIX . $formId, null );

        if ( ! empty( $out ) ) {
            $out = json_decode( $out );
        }

        return $out;
    }

    /**
     * get the mapping for a form by the option table id
     *
     * @param int $optionId the id of the option holding the map
     * @return string the mapped values (as json)
     */
    public static function getMappingByOptionId( $optionId ) {
        global $wpdb;

        $map = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_id = %d",
                $optionId
            ),
            ARRAY_A
        );

        if ( empty($map) || empty($map[0]) || empty( $map[0]['option_value'] ) ) {
            return '';
        }

        return $map[0]['option_value'];
    }

    /**
     * save a form map as json in the options table
     *
     * @param mixed $map an array of mappings
     * @return bool was the setting saved or not
     */
    public static function saveMap( $map ) {
        if ( empty( $map['gravity_form_id'] )
             || empty( $map['netsuite_form_id'] )
             || empty( $map['fields'] )
             || ! is_array( $map['fields'] )
        ) {
            return false;
        }

        $key = self::OPTION_PREFIX . $map['gravity_form_id'];

        return update_option( $key, json_encode( $map ), false );
    }

    /**
     * @param int $perPage items per page
     * @param int $page page of results
     * @param array $ids list of maps to pull
     * @return array
     */
    public static function getMaps( $perPage = 25, $page = 1, $ids = array() ) {
        global $wpdb;

        $perPage = intval( $perPage );
        $page    = intval( $page );

        $query = self::getBaseQuery( $wpdb->prefix );

        if ( ! empty( $ids ) ) {
            $query = self::addIdsToQuery( $query, $ids );
        }

        // for pagination, if $page or $perPage are not more than zero get everything
        if ( $page > 0 && $perPage > 0 ) {
            $query = self::addPaginationToQuery( $query, $perPage, $page );
        }

        $results = $wpdb->get_results( $query, ARRAY_A );

        return $results;
    }
    
    public static function getMapsWithNames( $perPage = 25, $page = 1, $ids = array() ) {
        $results = self::getMaps( $perPage, $page, $ids );
        $forms   = self::getGravityFormsList();

        if ( ! empty( $forms ) ) {
            $remappedForms = array();

            foreach ( $forms as $form ) {
                $remappedForms[ $form['id'] ] = $form['title'];
            }

            $forms = $remappedForms;
        }

        $namedResults = array();

        foreach ( $results as $result ) {
            $values = json_decode( $result['option_value'] );

            if ( ! empty( $values ) ) {
                $namedResults[] = array(
                    'gf_form_name' => ( ! empty( $forms[ $values->gravity_form_id ] ) ? $forms[ $values->gravity_form_id ] : '-' ),
                    'gf_form_id'   => ( ! empty( $values->gravity_form_id ) ? $values->gravity_form_id : '' ),
                    'ns_form_id'   => ( ! empty( $values->netsuite_form_id ) ? $values->netsuite_form_id : '-' ),
                    'option_id'    => $result['option_id'],
                );
            }
        }

        return $namedResults;
    }

    public static function getMapCount( $ids = array() ) {
        global $wpdb;

        $query = self::addIdsToQuery( self::getCountBaseQuery( $wpdb->prefix ), $ids );
        return $wpdb->get_var( $query );
    }

    /**
     * get a list of gravity forms
     *
     * @return mixed list of gravity forms
     */
    public static function getGravityFormsList() {
        global $wpdb;
        $forms = $wpdb->get_results("SELECT id, title FROM " . $wpdb->prefix . "gf_form WHERE is_active = 1 ORDER BY title ASC", ARRAY_A );
        return $forms;
    }

    private static function getBaseQuery( $prefix = 'wp_' ) {
        return 'SELECT option_id, option_name, option_value FROM ' . $prefix . 'options WHERE option_name LIKE "tt_form_map%"';
    }

    private static function getCountBaseQuery( $prefix = 'wp_' ) {
        return 'SELECT count(*) FROM ' . $prefix . 'options WHERE option_name LIKE "tt_form_map%"';
    }

    private static function addPaginationToQuery( $query, $perPage, $page ) {

        if ( empty( $query ) ) {
            return $query;
        }

        $perPage = intval( $perPage );
        $page    = intval( $page );

        // for pagination, if $page or $perPage are not more than zero get everything
        if ( $page > 0 && $perPage > 0 ) {
            $query .= ' LIMIT ' . $perPage;
            $query .= ' OFFSET ' . ( $page - 1 ) * $perPage;
        }

        return $query;
    }

    private static function addIdsToQuery( $query, $ids = array() ) {
        if ( ! empty( $ids ) ) {
            $ids = array_map( 'intval', $ids );

            $query .= ' AND option_id in (' . implode( ',', $ids ) . ')';
        }

        return $query;
    }

    public static function deleteMap( $id ) {
        global $wpdb;

        if ( empty( $id ) ) {
            return false;
        }

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM " . $wpdb->prefix . "options WHERE option_id = %d AND option_name LIKE 'tt_form_map%' LIMIT 1",
                $id
            )
        );

        return true;
    }

    /**
     * export the specified list of maps
     *
     * @param array $mapList list of maps to export, empty array will export everything*
     * @return bool true if successful otherwise false
     */
    public static function exportMaps( $mapList = array() ) {

        $maps = self::getMaps( 0, 0, $mapList );

        if ( empty( $maps ) ) {
            return false;
        }

        $stamp = date( 'YmdHis', time() );

        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header( "Cache-Control: private", false );
        header( "Content-Type: text/csv; charset=utf-8" );
        header( "Content-Disposition: attachment; filename=\"trek-form-manager-maps_{$stamp}.json\";" );

        $oh = fopen( 'php://output', 'w' );

        $output = array(
            'export_date'   => $stamp,
            'maps_exported' => count( $maps ),
        );

        foreach ( $maps as $map ) {
            $output[ $map['option_name'] ] = json_decode( $map['option_value'] );
        }
        
        fputs( $oh , json_encode( $output ) );
        
        fclose( $oh );

        exit;
    }

    /**
     * called when appropriate hook is triggered to push form data into NetSuite
     *
     * @param mixed $entry the form entry data
     * @param mixed $form the form meta data
     */
    public static function hookFormProcessorSendToNetSuite( $entry, $form ) {
        // force the script to keep running if the user navigates away - timeout after 30 seconds if there's an issue
        ignore_user_abort( true );
        set_time_limit( 30 );
        $generalSettings = get_option( 'trek_form_manager_general_settings' );
        $formMappingJSON = get_option( self::OPTION_PREFIX . $form['id'] );

        if ( empty( $generalSettings ) || empty( $formMappingJSON ) ) {
            return;
        }

        $generalSettings = maybe_unserialize( $generalSettings );
        $formMapping = json_decode( $formMappingJSON );

        $params = array(
            'compid' => $generalSettings['netsuite_compid'],
            'formid' => $formMapping->netsuite_form_id,
        );

        // this is the list of fields that will be sent to NetSuite
        $netSuiteData = array();

        // map standard fields
        foreach ( $entry as $id => $fieldValue ) {

            if ( empty( $formMapping->fields->{'gf_field_id_' . $id} ) ) {
                continue;
            }

            $nsField = $formMapping->fields->{'gf_field_id_' . $id}->ns_field_name;

            if ( 'custentity_birthdate' == $nsField ) {
                $dateParts = explode( '-', $fieldValue );
                if ( count( $dateParts ) === 3 ) {
                    if ( strlen( $dateParts[0] ) == 4 ) {
                        // assume Year, Month, Day
                        $fieldValue = intval( $dateParts[1], 10) . '/' . intval( $dateParts[2], 10 ) . '/' . $dateParts[0];
                    } else {
                        // assume Month, Day, Year
                        $fieldValue = intval( $dateParts[0], 10) . '/' . intval( $dateParts[1], 10 ) . '/' . $dateParts[2];
                    }

                }
            }

            // make sure only something numeric gets passed to NetSuite
            if ( 'custentity_trippriceperperson' == $nsField ) {
                $fieldValue = preg_replace( '/[^0-9.]/', '', $fieldValue );
            }

            $netSuiteData[ $nsField ] = $fieldValue;
        }

        // map extra fields
        if ( ! empty( $formMapping->extrafields ) ) {
            foreach ( $formMapping->extrafields as $extra ) {
                // these need to be URL params
                // @todo update this one param vs hidden support is added
                if ( $extra->fieldName === 'h' ) {
                    // decode to prevent double encoding
                    $params[ $extra->fieldName ] = urldecode( $extra->fieldData );
                    continue;
                }

                $netSuiteData[ $extra->fieldName ] = $extra->fieldData;
            }
        }

        $netsuiteUrl = $generalSettings['netsuite_url'] . '?' . http_build_query( $params );

        if ( WP_DEBUG ) {
            error_log( 'Trek Form Mapper - Debugging Output' );
            error_log( print_r( $netsuiteUrl, true ) );
            error_log( print_r( $params, true ) );
            error_log( print_r( $netSuiteData, true ) );
        }

        // curl submission backup attempt
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $netsuiteUrl );
        curl_setopt( $ch, CURLOPT_POST, count($_POST) );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $netSuiteData );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);

        // set referrer and user agent in attempts to make NS think we submit via browser
        curl_setopt( $ch, CURLOPT_REFERER, $generalSettings['netsuite_url'] );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );

        @ob_start();
        $response = curl_exec( $ch );
        $buffer_contents = @ob_get_flush();

        if ( WP_DEBUG ) {
            error_log( 'Curl Response' );
            if ( curl_error( $ch ) === '' ) {
                error_log( 'Call succeeded' );

                error_log( 'Curl Response: ' );
                error_log( print_r( $response, true ) );

                if ( ! empty( $buffer_contents ) ) {
                    error_log( 'Buffer Contents:' );
                    error_log( print_r( $buffer_contents, true ) );
                }
            } else {
                error_log( curl_errno( $ch ) . ': ' . curl_error( $ch ) );
            }
        }

        // close up shop before we leave
        curl_close( $ch );
    }
}

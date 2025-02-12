<?php
defined( 'ABSPATH' ) || exit;

/**
 * Constants for index names and status option
 */
define( 'TTNSW_IDX_CREATED_AT', 'idx_created_at' );
define( 'TTNSW_IDX_ARGS', 'idx_args' );
define( 'TTNSW_IDX_RESPONSE', 'idx_response' );
define( 'TTNSW_IDX_TYPE', 'idx_type' );
define( 'TTNSW_INDEX_STATUS_OPTION', 'ttnsw_index_status' );

/**
 * Check if any index creation is in progress
 *
 * @return bool|string False if no index is in progress, or the index name that is in progress
 */
function ttnsw_get_index_in_progress() {
    $status = get_option( TTNSW_INDEX_STATUS_OPTION, array() );
    return isset( $status['in_progress'] ) ? $status['in_progress'] : false;
}

/**
 * Set index creation status
 *
 * @param string|false $index_name The index being created or false to clear
 * @return void
 */
function ttnsw_set_index_status( $index_name ) {
    if ( $index_name ) {
        update_option( TTNSW_INDEX_STATUS_OPTION, array(
            'in_progress' => $index_name,
            'started_at' => current_time( 'mysql' )
        ) );
    } else {
        delete_option( TTNSW_INDEX_STATUS_OPTION );
    }
}

/**
 * Create index asynchronously using WP Background Processing
 */
function ttnsw_create_index_async( $index_name ) {
    if ( ttnsw_get_index_in_progress() ) {
        return false;
    }

    ttnsw_set_index_status( $index_name );

    as_schedule_single_action( time(), 'ttnsw_create_index', array( $index_name ) );

    return true;
}

/**
 * Handle index creation
 */
function ttnsw_handle_create_index( $index_name ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tt_common_error_logs';

    try {
        switch ( $index_name ) {
            case TTNSW_IDX_CREATED_AT:
                $sql = "ALTER TABLE {$table_name} ADD INDEX {$index_name} (created_at)";
                break;
            case TTNSW_IDX_ARGS:
                $sql = "ALTER TABLE {$table_name} ADD FULLTEXT {$index_name} (args)";
                break;
            case TTNSW_IDX_RESPONSE:
                $sql = "ALTER TABLE {$table_name} ADD FULLTEXT {$index_name} (response)";
                break;
            case TTNSW_IDX_TYPE:
                $sql = "ALTER TABLE {$table_name} ADD FULLTEXT {$index_name} (type)";
                break;
            default:
                throw new Exception( 'Invalid index name' );
        }

        $wpdb->query( $sql );

        if ( $wpdb->last_error ) {
            throw new Exception( 'WPDB Failure during index creation: ' . $wpdb->last_error );
        }

    } catch ( Exception $e ) {
        tt_add_error_log( '[Index Creation Error]', array( 'index' => $index_name ), $e->getMessage() );
    }

    ttnsw_set_index_status( false );
}

add_action( 'ttnsw_create_index', 'ttnsw_handle_create_index' );

/**
 * Check if an index exists on the table
 */
function ttnsw_check_index_exists( $index_name ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tt_common_error_logs';

    $result = $wpdb->get_row( $wpdb->prepare(
        "SHOW INDEX FROM {$table_name} WHERE Key_name = %s",
        $index_name
    ) );

    return ! empty( $result );
}

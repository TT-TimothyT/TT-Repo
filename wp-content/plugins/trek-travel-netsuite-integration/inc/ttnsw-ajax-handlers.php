<?php
/**
 * AJAX handlers
 *
 * @package Trek_Travel_Netsuite_Integration
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle exact count calculation in background
 */
function ttnsw_calculate_exact_count() {
	check_ajax_referer('ttnsw_calculate_exact_count', 'nonce');

	global $wpdb;
	$table_name = $wpdb->prefix . 'tt_common_error_logs';

	// Set calculating flag
	set_transient('ttnsw_logs_is_calculating', true, 600); // Cache for 10 minutes

	// Calculate exact count (non-blocking)
	$count = $wpdb->get_var("SELECT COUNT(id) FROM {$table_name}");
	
	// Store result
	set_transient('ttnsw_logs_exact_count', $count, 300); // Cache for 5 minutes
	delete_transient('ttnsw_logs_is_calculating');

	wp_send_json_success(['count' => number_format_i18n($count)]);
}
add_action('wp_ajax_ttnsw_calculate_exact_count', 'ttnsw_calculate_exact_count');

/**
 * Get current exact count value
 */
function ttnsw_get_exact_count() {
	check_ajax_referer('ttnsw_calculate_exact_count', 'nonce');

	$count = get_transient('ttnsw_logs_exact_count');

	wp_send_json_success([
		'count' => number_format_i18n($count),
		'is_calculating' => (bool) get_transient('ttnsw_logs_is_calculating')
	]);
}
add_action('wp_ajax_ttnsw_get_exact_count', 'ttnsw_get_exact_count');

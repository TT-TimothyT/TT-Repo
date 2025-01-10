<?php
/**
 * Custom ordering for taxonomies.
 *
 * @uses order_number ACF field with a numeric value for the order.
 */

$trip_style_terms_args = array(
	'taxonomy'   => 'trip-style',
	'hide_empty' => false,
	'field'      => 'name',
	'meta_query' => array(
		array(
			'key'     => 'order_number',
			'type'    => 'NUMERIC',
			'value'   => 0,
			'compare' => '>'
		)
	),
	'orderby' => 'meta_value_num',
	'order'   => 'ASC',
);

$trip_style_terms = get_terms( $trip_style_terms_args );

$trip_style_names = wp_list_pluck( $trip_style_terms, 'name' );

$trip_class_terms_args = array(
	'taxonomy'   => 'trip-class',
	'hide_empty' => false,
	'field'      => 'name',
	'meta_query' => array(
		array(
			'key'     => 'order_number',
			'type'    => 'NUMERIC',
			'value'   => 0,
			'compare' => '>'
		)
	),
	'orderby' => 'meta_value_num',
	'order'   => 'ASC',
);

$trip_class_terms = get_terms( $trip_class_terms_args );

$trip_class_names = wp_list_pluck( $trip_class_terms, 'name' );

?>
	<script>
		var tt_normalize_amp = (str) => str.replace(/&amp;/g, '&');
		/**
		 * Create a custom order map for the Trip Styles.
		 *
		 * If any taxonomy term doesn't have an order_number, it will be listed at the bottom of the filters, ordered by name ASC.
		 */
		var tsSortOrder = <?php echo json_encode( $trip_style_names ) ?>; // Sorted array with taxonomy terms names.
		var tsOrderMap  = new Map( tsSortOrder.map( ( item, index ) => [tt_normalize_amp( item ), index] ) );

		/**
		 * Create a custom order map for the Trip Classes.
		 *
		 * If any taxonomy term doesn't have an order_number, it will be listed at the bottom of the filters, ordered by name ASC.
		 */
		var tcSortOrder = <?php echo json_encode( $trip_class_names ) ?>; // Sorted array with taxonomy terms names.
		var tcOrderMap  = new Map( tcSortOrder.map( ( item, index ) => [tt_normalize_amp( item ), index] ) );
	</script>

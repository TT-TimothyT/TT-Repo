<?php
/**
 * Make the hierarchical taxonomy checkbox checklist with radio buttons.
 *
 * To can transform taxonomy checkbox list to list with radio buttons,
 * needs to add argument 'meta_box_cb' => 'tt_product_taxonomy_meta_box',
 * in the place where registers the taxonomy
 * and to add the taxonomy key in the array of TT_TAXONOMIES_WITH_RADIO_BUTTONS constant
 */

defined( 'ABSPATH' ) || exit;

define( 'TT_TAXONOMIES_WITH_RADIO_BUTTONS', array( 'trip-status' ) );

/**
 * Function to hide most-used tab from the meta box for Trip Status on Edit Product page,
 * because they use another HTML generation function [wp_popular_terms_checklist],
 * that needs to be further refactored in the function below.
 *
 * @see https://developer.wordpress.org/reference/functions/wp_popular_terms_checklist/
 *
 * Copied the original function that shows the meta box, and removed the li from the nav bar.
 *
 * @see https://developer.wordpress.org/reference/functions/post_categories_meta_box/
 *
 * @param WP_Post $post Current post object.
 * @param array   $box Categories meta box arguments.
 */
function tt_product_taxonomy_meta_box( $post, $box ) {
	$defaults = array( 'taxonomy' => 'category' );
	if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
		$args = array();
	} else {
		$args = $box['args'];
	}
	$parsed_args = wp_parse_args( $args, $defaults );
	$tax_name    = esc_attr( $parsed_args['taxonomy'] );
	$taxonomy    = get_taxonomy( $parsed_args['taxonomy'] );
	?>
	<div id="taxonomy-<?php echo $tax_name; ?>" class="categorydiv">
		<ul id="<?php echo $tax_name; ?>-tabs" class="category-tabs">
			<li class="tabs"><a href="#<?php echo $tax_name; ?>-all"><?php echo $taxonomy->labels->all_items; ?></a></li>
		</ul>

		<div id="<?php echo $tax_name; ?>-pop" class="tabs-panel" style="display: none;">
			<ul id="<?php echo $tax_name; ?>checklist-pop" class="categorychecklist form-no-clear" >
				<?php $popular_ids = wp_popular_terms_checklist( $tax_name ); ?>
			</ul>
		</div>

		<div id="<?php echo $tax_name; ?>-all" class="tabs-panel">
			<?php
			$name = ( 'category' === $tax_name ) ? 'post_category' : 'tax_input[' . $tax_name . ']';
			// Allows for an empty term set to be sent. 0 is an invalid term ID and will be ignored by empty() checks.
			echo "<input type='hidden' name='{$name}[]' value='0' />";
			?>
			<ul id="<?php echo $tax_name; ?>checklist" data-wp-lists="list:<?php echo $tax_name; ?>" class="categorychecklist form-no-clear">
				<?php
				wp_terms_checklist(
					$post->ID,
					array(
						'taxonomy'     => $tax_name,
						'popular_cats' => $popular_ids,
					)
				);
				?>
			</ul>
		</div>
		<?php if ( current_user_can( $taxonomy->cap->edit_terms ) ) : ?>
			<div id="<?php echo $tax_name; ?>-adder" class="wp-hidden-children">
				<a id="<?php echo $tax_name; ?>-add-toggle" href="#<?php echo $tax_name; ?>-add" class="hide-if-no-js taxonomy-add-new">
					<?php
						/* translators: %s: Add New taxonomy label. */
						printf( __( '+ %s' ), $taxonomy->labels->add_new_item );
					?>
				</a>
				<p id="<?php echo $tax_name; ?>-add" class="category-add wp-hidden-child">
					<label class="screen-reader-text" for="new<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_new_item; ?></label>
					<input type="text" name="new<?php echo $tax_name; ?>" id="new<?php echo $tax_name; ?>" class="form-required form-input-tip" value="<?php echo esc_attr( $taxonomy->labels->new_item_name ); ?>" aria-required="true" />
					<label class="screen-reader-text" for="new<?php echo $tax_name; ?>_parent">
						<?php echo $taxonomy->labels->parent_item_colon; ?>
					</label>
					<?php
					$parent_dropdown_args = array(
						'taxonomy'         => $tax_name,
						'hide_empty'       => 0,
						'name'             => 'new' . $tax_name . '_parent',
						'orderby'          => 'name',
						'hierarchical'     => 1,
						'show_option_none' => '&mdash; ' . $taxonomy->labels->parent_item . ' &mdash;',
					);

					/**
					 * Filters the arguments for the taxonomy parent dropdown on the Post Edit page.
					 *
					 * @since 4.4.0
					 *
					 * @param array $parent_dropdown_args {
					 *     Optional. Array of arguments to generate parent dropdown.
					 *
					 *     @type string   $taxonomy         Name of the taxonomy to retrieve.
					 *     @type bool     $hide_if_empty    True to skip generating markup if no
					 *                                      categories are found. Default 0.
					 *     @type string   $name             Value for the 'name' attribute
					 *                                      of the select element.
					 *                                      Default "new{$tax_name}_parent".
					 *     @type string   $orderby          Which column to use for ordering
					 *                                      terms. Default 'name'.
					 *     @type bool|int $hierarchical     Whether to traverse the taxonomy
					 *                                      hierarchy. Default 1.
					 *     @type string   $show_option_none Text to display for the "none" option.
					 *                                      Default "&mdash; {$parent} &mdash;",
					 *                                      where `$parent` is 'parent_item'
					 *                                      taxonomy label.
					 * }
					 */
					$parent_dropdown_args = apply_filters( 'post_edit_category_parent_dropdown_args', $parent_dropdown_args );

					wp_dropdown_categories( $parent_dropdown_args );
					?>
					<input type="button" id="<?php echo $tax_name; ?>-add-submit" data-wp-lists="add:<?php echo $tax_name; ?>checklist:<?php echo $tax_name; ?>-add" class="button category-add-submit" value="<?php echo esc_attr( $taxonomy->labels->add_new_item ); ?>" />
					<?php wp_nonce_field( 'add-' . $tax_name, '_ajax_nonce-add-' . $tax_name, false ); ?>
					<span id="<?php echo $tax_name; ?>-ajax-response"></span>
				</p>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Use radio inputs instead of checkboxes for term checklists in specified taxonomies.
 *
 * @param array|string $args An array or string of arguments.
 *
 * @link https://developer.wordpress.org/reference/hooks/wp_terms_checklist_args/
 * @link https://wordpress.stackexchange.com/questions/139269/wordpress-taxonomy-radio-buttons
 *
 * @return array
 */
function tt_term_radio_buttons_checklist( $args ) {
	if ( ! empty( $args['taxonomy'] ) && in_array( $args['taxonomy'], TT_TAXONOMIES_WITH_RADIO_BUTTONS, true ) ) {
		// Don't override 3rd party walkers.
		if ( empty( $args['walker'] ) || is_a( $args['walker'], 'Walker' ) ) {
			if ( ! class_exists( 'TT_Walker_Radio_Checklist' ) ) {
				/**
				 * Custom walker for switching checkbox inputs to radio.
				 *
				 * @see Walker_Category_Checklist
				 * @link https://developer.wordpress.org/reference/classes/walker_category_checklist/
				 */
				class TT_Walker_Radio_Checklist extends Walker_Category_Checklist {
					/**
					 * Displays array of elements hierarchically.
					 *
					 * Does not assume any existing order of elements.
					 *
					 * $max_depth = -1 means flatly display every element.
					 * $max_depth = 0 means display all levels.
					 * $max_depth > 0 specifies the number of display levels.
					 *
					 * @since 2.1.0
					 * @since 5.3.0 Formalized the existing `...$args` parameter by adding it
					 *              to the function signature.
					 *
					 * @param array $elements  An array of elements.
					 * @param int   $max_depth The maximum hierarchical depth.
					 * @param mixed ...$args   Optional additional arguments.
					 * @return string The hierarchical item output.
					 */
					public function walk( $elements, $max_depth, ...$args ) {
						$output = parent::walk( $elements, $max_depth, ...$args );

						$output = str_replace(
							array( 'type="checkbox"', "type='checkbox'" ),
							array( 'type="radio"', "type='radio'" ),
							$output
						);

						return $output;
					}
				}
			}

			// Attach custom walker to transform checkboxes in to radio buttons.
			$args['walker'] = new TT_Walker_Radio_Checklist;
		}
	}

	return $args;
}
add_filter( 'wp_terms_checklist_args', 'tt_term_radio_buttons_checklist' );

/**
 * Quick Edit mode AJAX handler callback.
 * Take the selected taxonomy term for the given taxonomy and post ID.
 *
 * @see https://developer.wordpress.org/reference/functions/wp_get_object_terms/
 *
 * @return object In the result is the ID of the selected taxonomy term.
 */
function tt_trips_status_inline_edit_radio_checked() {
	// Security check.
	if ( ! isset( $_POST['tt_edit_nonce'] ) ) {
		wp_send_json_error( array( 'status' => false, 'message' => 'Verification not available!' ) );
		exit;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tt_edit_nonce'] ) ), '_tt_inline_edit_nonce' ) ) {
		wp_send_json_error( array( 'status' => false, 'message' => 'Verification fail!' ) );
		exit;
	}

	// Check for data exist.
	if ( ! isset( $_POST['tt_edit_taxonomies'] ) || empty( $_POST['tt_edit_taxonomies'] ) || ! is_array( $_POST['tt_edit_taxonomies'] ) ) {
		wp_send_json_error( array( 'status' => false, 'message' => 'Taxonomies not found!' ) );
		exit;
	}

	if ( ! isset( $_POST['tt_edit_post_id'] ) || empty( $_POST['tt_edit_post_id'] ) ) {
		wp_send_json_error( array( 'status' => false, 'message' => 'Post ID not found!' )  );
		exit;
	}

	$edit_taxonomies  = array_map( 'sanitize_key', wp_unslash( $_POST['tt_edit_taxonomies'] ) );

	$edit_post_id     = sanitize_text_field( wp_unslash( $_POST['tt_edit_post_id'] ) );

	$serch_taxonomies = array_keys( $edit_taxonomies );

	$terms_objects    = wp_get_object_terms( (int) $edit_post_id, $serch_taxonomies );

	if( empty( $terms_objects ) ) {
		wp_send_json_error( array( 'status' => false, 'message' => 'Not found selected terms!' )  );
		exit;
	}

	if ( is_wp_error( $terms_objects ) ) {
		$error_string = $terms_objects->get_error_message();
		wp_send_json_error( array( 'status' => false, 'message' => $error_string )  );
		exit;
	}

	// TODO Multiple
	foreach( $terms_objects as $wp_term ) {
		if( ! is_array( $edit_taxonomies[$wp_term->taxonomy] ) ) {
			$edit_taxonomies[$wp_term->taxonomy] = array();
		}
		$edit_taxonomies[$wp_term->taxonomy][] = $wp_term->term_id;
	}

	$result = $edit_taxonomies;

	$success_response = array(
		'status' => true,
		'result' => $result
	);

	wp_send_json_success( $success_response );
	exit;
}
add_action( 'wp_ajax_tt_trips_status_inline_edit_radio_checked', 'tt_trips_status_inline_edit_radio_checked' );
add_action( 'wp_ajax_nopriv_tt_trips_status_inline_edit_radio_checked', 'tt_trips_status_inline_edit_radio_checked' );

/**
 * Load scripts on the all products page.
 *
 * Enqueue the js file to select the radio button for the selected taxonomy term,
 * when the taxonomy checklist is with radio buttons in the Quick Edit mode.
 *
 * @param string $hook_suffix The current admin page.
 * @link https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
 */
function tt_trips_status_inline_edit_radio_checked_enqueue_script( $hook_suffix ) {
	if( empty( TT_TAXONOMIES_WITH_RADIO_BUTTONS ) ) {
		return;
	}
	$should_load_script   = false;
	$current_admin_screen = get_current_screen();
	if( ! empty( $current_admin_screen ) && isset( $current_admin_screen->id ) && ! empty( $current_admin_screen->id ) ) {
		$should_load_script = 'edit.php' === $hook_suffix && 'edit-product' === $current_admin_screen->id;
	} else {
		$should_load_script = 'edit.php' === $hook_suffix;
	}
	if( $should_load_script ) {

		wp_enqueue_script( 'tt-inline-edit-tax-radio', get_theme_file_uri( '/assets/js/tt-inline-edit-tax-radio.js' ), array( 'jquery' ) );

		wp_localize_script( 'tt-inline-edit-tax-radio', 'tt_inline_edit_assets',
			array(
				'ajaxurl'            => admin_url( 'admin-ajax.php' ),
				'nonce'              => wp_create_nonce( '_tt_inline_edit_nonce' ),
				'allowed_taxonomies' => TT_TAXONOMIES_WITH_RADIO_BUTTONS
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'tt_trips_status_inline_edit_radio_checked_enqueue_script' );

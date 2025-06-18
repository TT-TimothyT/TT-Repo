<?php

/**
 * This is disabling the NetSuite user sync upon user registration. Most of the time it should stay commented out. However,
 * if you have a SPAM flow from user reistration, just enable it and this will block the comments
 *
 * More information on this commit https://github.com/DevriX/Trek-Travel/commit/9ddf212cbb6a573dc7276f5e14796c793497ab04
 */
// define( 'TT_DISABLE_USER_SYNC', true ); // Most of the time it should stay commented out


/**
 * Include Theme Constants.
 */
$theme_constants = __DIR__ . '/inc/trek-constants.php';
if ( is_readable( $theme_constants ) ) {
	require_once $theme_constants;
}

/**
 * Include Theme Customizer.
 *
 * @since v1.0
 */
$theme_customizer = __DIR__ . '/inc/customizer.php';
if ( is_readable( $theme_customizer ) ) {
	require_once $theme_customizer;
}


/**
 * Include Support for wordpress.com-specific functions.
 * 
 * @since v1.0
 */
$theme_wordpresscom = __DIR__ . '/inc/wordpresscom.php';
if ( is_readable( $theme_wordpresscom ) ) {
	require_once $theme_wordpresscom;
}


/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since v1.0
 */
if ( ! isset( $content_width ) ) {
	$content_width = 800;
}


/**
 * General Theme Settings.
 *
 * @since v1.0
 */
if ( ! function_exists( 'trek_travel_theme_setup_theme' ) ) :
	function trek_travel_theme_setup_theme() {
		// Make theme available for translation: Translations can be filed in the /languages/ directory.
		load_theme_textdomain( 'trek-travel-theme', __DIR__ . '/languages' );

		// Theme Support.
		add_theme_support( 'title-tag' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
				'navigation-widgets',
			)
		);

		add_image_size( 'featured-archive', 886, 664, true );
		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );
		// Add support for full and wide alignment.
		add_theme_support( 'align-wide' );
		// Add support for editor styles.
		add_theme_support( 'editor-styles' );
		// Enqueue editor styles.
		add_editor_style( 'style-editor.css' );

		// Default Attachment Display Settings.
		update_option( 'image_default_align', 'none' );
		update_option( 'image_default_link_type', 'none' );
		update_option( 'image_default_size', 'large' );

		// Custom CSS-Styles of Wordpress Gallery.
		add_filter( 'use_default_gallery_style', '__return_false' );
	}
	add_action( 'after_setup_theme', 'trek_travel_theme_setup_theme' );

	// Disable Block Directory: https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/filters/editor-filters.md#block-directory
	remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
	remove_action( 'enqueue_block_editor_assets', 'gutenberg_enqueue_block_editor_assets_block_directory' );
endif;


/**
 * Fire the wp_body_open action.
 *
 * Added for backwards compatibility to support pre 5.2.0 WordPress versions.
 *
 * @since v2.2
 */
if ( ! function_exists( 'wp_body_open' ) ) :
	function wp_body_open() {
		/**
		 * Triggered after the opening <body> tag.
		 *
		 * @since v2.2
		 */
		do_action( 'wp_body_open' );
	}
endif;


/**
 * Add new User fields to Userprofile.
 *
 * @since v1.0
 */
if ( ! function_exists( 'trek_travel_theme_add_user_fields' ) ) :
	function trek_travel_theme_add_user_fields( $fields ) {
		// Add new fields.
		$fields['facebook_profile'] = 'Facebook URL';
		$fields['twitter_profile']  = 'Twitter URL';
		$fields['linkedin_profile'] = 'LinkedIn URL';
		$fields['xing_profile']     = 'Xing URL';
		$fields['github_profile']   = 'GitHub URL';

		return $fields;
	}
	add_filter( 'user_contactmethods', 'trek_travel_theme_add_user_fields' ); // get_user_meta( $user->ID, 'facebook_profile', true );
endif;


/**
 * Test if a page is a blog page.
 * if ( is_blog() ) { ... }
 *
 * @since v1.0
 */
function is_blog() {
	global $post;
	$posttype = get_post_type( $post );

	return ( ( is_archive() || is_author() || is_category() || is_home() || is_single() || ( is_tag() && ( 'post' === $posttype ) ) ) ? true : false );
}


/**
 * Disable comments for Media (Image-Post, Jetpack-Carousel, etc.)
 *
 * @since v1.0
 */
function trek_travel_theme_filter_media_comment_status( $open, $post_id = null ) {
	$media_post = get_post( $post_id );
	if ( 'attachment' === $media_post->post_type ) {
		return false;
	}
	return $open;
}
add_filter( 'comments_open', 'trek_travel_theme_filter_media_comment_status', 10, 2 );


/**
 * Style Edit buttons as badges: https://getbootstrap.com/docs/5.0/components/badge
 *
 * @since v1.0
 */
function trek_travel_theme_custom_edit_post_link( $output ) {
	return str_replace( 'class="post-edit-link"', 'class="post-edit-link badge badge-secondary"', $output );
}
add_filter( 'edit_post_link', 'trek_travel_theme_custom_edit_post_link' );

function trek_travel_theme_custom_edit_comment_link( $output ) {
	return str_replace( 'class="comment-edit-link"', 'class="comment-edit-link badge badge-secondary"', $output );
}
add_filter( 'edit_comment_link', 'trek_travel_theme_custom_edit_comment_link' );


/**
 * Responsive oEmbed filter: https://getbootstrap.com/docs/5.0/helpers/ratio
 *
 * @since v1.0
 */
function trek_travel_theme_oembed_filter( $html ) {
	return '<div class="ratio ratio-16x9">' . $html . '</div>';
}
add_filter( 'embed_oembed_html', 'trek_travel_theme_oembed_filter', 10, 4 );


if ( ! function_exists( 'trek_travel_theme_content_nav' ) ) :
	/**
	 * Display a navigation to next/previous pages when applicable.
	 *
	 * @since v1.0
	 */
	function trek_travel_theme_content_nav( $nav_id ) {
		global $wp_query;

		if ( $wp_query->max_num_pages > 1 ) :
	?>
			<div id="<?php echo esc_attr( $nav_id ); ?>" class="d-flex mb-4 justify-content-between">
				<div><?php next_posts_link( '<span aria-hidden="true">&larr;</span> ' . esc_html__( 'Older posts', 'trek-travel-theme' ) ); ?></div>
				<div><?php previous_posts_link( esc_html__( 'Newer posts', 'trek-travel-theme' ) . ' <span aria-hidden="true">&rarr;</span>' ); ?></div>
			</div><!-- /.d-flex -->
	<?php
		else :
			echo '<div class="clearfix"></div>';
		endif;
	}

	// Add Class.
	function posts_link_attributes() {
		return 'class="btn btn-secondary btn-lg"';
	}
	add_filter( 'next_posts_link_attributes', 'posts_link_attributes' );
	add_filter( 'previous_posts_link_attributes', 'posts_link_attributes' );
endif;


/**
 * Init Widget areas in Sidebar.
 *
 * @since v1.0
 */
function trek_travel_theme_widgets_init() {
	// Area 1.
	register_sidebar(
		array(
			'name'          => 'Primary Widget Area (Sidebar)',
			'id'            => 'primary_widget_area',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	// Area 2.
	register_sidebar(
		array(
			'name'          => 'Secondary Widget Area (Header Navigation)',
			'id'            => 'secondary_widget_area',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	// Area 3.
	register_sidebar(
		array(
			'name'          => 'Third Widget Area (Footer)',
			'id'            => 'third_widget_area',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	// Area 4.
	register_sidebar(
		array(
			'name'          => 'Fix Currency Converter Widget Geolocation (Hidden)',
			'id'            => 'tt_currency_converter_widget_area',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'trek_travel_theme_widgets_init' );


if ( ! function_exists( 'trek_travel_theme_article_posted_on' ) ) :
	/**
	 * "Theme posted on" pattern.
	 *
	 * @since v1.0
	 */
	function trek_travel_theme_article_posted_on() {
		printf(
			wp_kses_post( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author-meta vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'trek-travel-theme' ) ),
			esc_url( get_the_permalink() ),
			esc_attr( get_the_date() . ' - ' . get_the_time() ),
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() . ' - ' . get_the_time() ),
			esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'trek-travel-theme' ), get_the_author() ),
			get_the_author()
		);
	}
endif;


/**
 * Template for Password protected post form.
 *
 * @since v1.0
 */
function trek_travel_theme_password_form() {
	global $post;
	$label = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );

	$output = '<div class="row">';
		$output .= '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">';
		$output .= '<h4 class="col-md-12 alert alert-warning">' . esc_html__( 'This content is password protected. To view it please enter your password below.', 'trek-travel-theme' ) . '</h4>';
			$output .= '<div class="col-md-6">';
				$output .= '<div class="input-group">';
					$output .= '<input type="password" name="post_password" id="' . esc_attr( $label ) . '" placeholder="' . esc_attr__( 'Password', 'trek-travel-theme' ) . '" class="form-control" />';
					$output .= '<div class="input-group-append"><input type="submit" name="submit" class="btn btn-primary" value="' . esc_attr__( 'Submit', 'trek-travel-theme' ) . '" /></div>';
				$output .= '</div><!-- /.input-group -->';
			$output .= '</div><!-- /.col -->';
		$output .= '</form>';
	$output .= '</div><!-- /.row -->';
	return $output;
}
add_filter( 'the_password_form', 'trek_travel_theme_password_form' );


if ( ! function_exists( 'trek_travel_theme_comment' ) ) :
	/**
	 * Style Reply link.
	 *
	 * @since v1.0
	 */
	function trek_travel_theme_replace_reply_link_class( $class ) {
		return str_replace( "class='comment-reply-link", "class='comment-reply-link btn btn-outline-secondary", $class );
	}
	add_filter( 'comment_reply_link', 'trek_travel_theme_replace_reply_link_class' );

	/**
	 * Template for comments and pingbacks:
	 * add function to comments.php ... wp_list_comments( array( 'callback' => 'trek_travel_theme_comment' ) );
	 *
	 * @since v1.0
	 */
	function trek_travel_theme_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback':
			case 'trackback':
	?>
		<li class="post pingback">
			<p><?php esc_html_e( 'Pingback:', 'trek-travel-theme' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( esc_html__( 'Edit', 'trek-travel-theme' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
				break;
			default:
	?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" class="comment">
				<footer class="comment-meta">
					<div class="comment-author vcard">
						<?php
							$avatar_size = ( '0' !== $comment->comment_parent ? 68 : 136 );
							echo get_avatar( $comment, $avatar_size );

							/* translators: 1: comment author, 2: date and time */
							printf(
								wp_kses_post( __( '%1$s, %2$s', 'trek-travel-theme' ) ),
								sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
								sprintf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
									esc_url( get_comment_link( $comment->comment_ID ) ),
									get_comment_time( 'c' ),
									/* translators: 1: date, 2: time */
									sprintf( esc_html__( '%1$s ago', 'trek-travel-theme' ), human_time_diff( (int) get_comment_time( 'U' ), current_time( 'timestamp' ) ) )
								)
							);

							edit_comment_link( esc_html__( 'Edit', 'trek-travel-theme' ), '<span class="edit-link">', '</span>' );
						?>
					</div><!-- .comment-author .vcard -->

					<?php if ( '0' === $comment->comment_approved ) : ?>
						<em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'trek-travel-theme' ); ?></em>
						<br />
					<?php endif; ?>
				</footer>

				<div class="comment-content"><?php comment_text(); ?></div>

				<div class="reply">
					<?php
						comment_reply_link(
							array_merge(
								$args,
								array(
									'reply_text' => esc_html__( 'Reply', 'trek-travel-theme' ) . ' <span>&darr;</span>',
									'depth'      => $depth,
									'max_depth'  => $args['max_depth'],
								)
							)
						);
					?>
				</div><!-- /.reply -->
			</article><!-- /#comment-## -->
		<?php
				break;
		endswitch;
	}

	/**
	 * Custom Comment form.
	 *
	 * @since v1.0
	 * @since v1.1: Added 'submit_button' and 'submit_field'
	 * @since v2.0.2: Added '$consent' and 'cookies'
	 */
	function trek_travel_theme_custom_commentform( $args = array(), $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		$commenter     = wp_get_current_commenter();
		$user          = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';

		$args = wp_parse_args( $args );

		$req      = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true' required" : '' );
		$consent  = ( empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"' );
		$fields   = array(
			'author'  => '<div class="form-floating mb-3">
							<input type="text" id="author" name="author" class="form-control" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_html__( 'Name', 'trek-travel-theme' ) . ( $req ? '*' : '' ) . '"' . $aria_req . ' />
							<label for="author">' . esc_html__( 'Name', 'trek-travel-theme' ) . ( $req ? '*' : '' ) . '</label>
						</div>',
			'email'   => '<div class="form-floating mb-3">
							<input type="email" id="email" name="email" class="form-control" value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_html__( 'Email', 'trek-travel-theme' ) . ( $req ? '*' : '' ) . '"' . $aria_req . ' />
							<label for="email">' . esc_html__( 'Email', 'trek-travel-theme' ) . ( $req ? '*' : '' ) . '</label>
						</div>',
			'url'     => '',
			'cookies' => '<p class="form-check mb-3 comment-form-cookies-consent">
							<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" class="form-check-input" type="checkbox" value="yes"' . $consent . ' />
							<label class="form-check-label" for="wp-comment-cookies-consent">' . esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'trek-travel-theme' ) . '</label>
						</p>',
		);

		$defaults = array(
			'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
			'comment_field'        => '<div class="form-floating mb-3">
											<textarea id="comment" name="comment" class="form-control" aria-required="true" required placeholder="' . esc_attr__( 'Comment', 'trek-travel-theme' ) . ( $req ? '*' : '' ) . '"></textarea>
											<label for="comment">' . esc_html__( 'Comment', 'trek-travel-theme' ) . '</label>
										</div>',
			/** This filter is documented in wp-includes/link-template.php */
			'must_log_in'          => '<p class="must-log-in">' . sprintf( wp_kses_post( __( 'You must be <a href="%s">logged in</a> to post a comment.', 'trek-travel-theme' ) ), wp_login_url( apply_filters( 'the_permalink', get_the_permalink( get_the_ID() ) ) ) ) . '</p>',
			/** This filter is documented in wp-includes/link-template.php */
			'logged_in_as'         => '<p class="logged-in-as">' . sprintf( wp_kses_post( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'trek-travel-theme' ) ), get_edit_user_link(), $user->display_name, wp_logout_url( apply_filters( 'the_permalink', get_the_permalink( get_the_ID() ) ) ) ) . '</p>',
			'comment_notes_before' => '<p class="small comment-notes">' . esc_html__( 'Your Email address will not be published.', 'trek-travel-theme' ) . '</p>',
			'comment_notes_after'  => '',
			'id_form'              => 'commentform',
			'id_submit'            => 'submit',
			'class_submit'         => 'btn btn-primary',
			'name_submit'          => 'submit',
			'title_reply'          => '',
			'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'trek-travel-theme' ),
			'cancel_reply_link'    => esc_html__( 'Cancel reply', 'trek-travel-theme' ),
			'label_submit'         => esc_html__( 'Post Comment', 'trek-travel-theme' ),
			'submit_button'        => '<input type="submit" id="%2$s" name="%1$s" class="%3$s" value="%4$s" />',
			'submit_field'         => '<div class="form-submit">%1$s %2$s</div>',
			'format'               => 'html5',
		);

		return $defaults;
	}
	add_filter( 'comment_form_defaults', 'trek_travel_theme_custom_commentform' );

endif;


/**
 * Nav menus.
 *
 * @since v1.0
 */
if ( function_exists( 'register_nav_menus' ) ) {
	register_nav_menus(
		array(
			'main-menu'   => 'Main Navigation Menu',
			'footer-menu' => 'Footer Menu',
		)
	);
}

// Custom Nav Walker: wp_bootstrap_navwalker().
$custom_walker = __DIR__ . '/inc/wp_bootstrap_navwalker.php';
if ( is_readable( $custom_walker ) ) {
	require_once $custom_walker;
}

$custom_walker_footer = __DIR__ . '/inc/wp_bootstrap_navwalker_footer.php';
if ( is_readable( $custom_walker_footer ) ) {
	require_once $custom_walker_footer;
}

// Trek custom Shortcode.
$trek_general_func = __DIR__ . '/inc/trek-general-functions.php';
if ( is_readable( $trek_general_func ) ) {
	require_once $trek_general_func;
}

// Hierarchical taxonomy checkbox checklist with radio buttons.
$trek_taxonomy_radio_buttons = __DIR__ . '/inc/trek-taxonomy-radio-buttons.php';
if ( is_readable( $trek_taxonomy_radio_buttons ) ) {
	require_once $trek_taxonomy_radio_buttons;
}

// Trek custom Shortcode.
$trek_shortcodes = __DIR__ . '/inc/trek-shortcodes.php';
if ( is_readable( $trek_shortcodes ) ) {
	require_once $trek_shortcodes;
}

// Trek NetSuite Sync integration code.
$trek_ns_sync = __DIR__ . '/inc/netsuite-sync/trek-netsuite-sync.php';
if ( is_readable( $trek_ns_sync ) ) {
	require_once $trek_ns_sync;
}

// Trek NetSuite Sync integration code.
$trek_booking_engine = __DIR__ . '/inc/trek-booking-engine.php';
if ( is_readable( $trek_booking_engine ) ) {
	require_once $trek_booking_engine;
}

// Trek Email integration code.
$trek_email_manager = __DIR__ . '/inc/trek-email-manager/trek-email-manager.php';
if ( is_readable( $trek_email_manager ) ) {
	require_once $trek_email_manager;
}

// Trek Booking status integration.
$trek_booking_status = __DIR__ . '/inc/trek-booking-status.php';
if ( is_readable( $trek_booking_status ) ) {
	require_once $trek_booking_status;
}

// Trek WC Persistent Cart Controller integration.
$trek_wc_persistent_cart = __DIR__ . '/inc/trek-wc-persistent-cart.php';
if ( is_readable( $trek_wc_persistent_cart ) ) {
	require_once $trek_wc_persistent_cart;
}

// Trek Old Trip Dates integration.
$trek_old_trip_dates = __DIR__ . '/inc/trek-old-trip-dates.php';
if ( is_readable( $trek_old_trip_dates ) ) {
	require_once $trek_old_trip_dates;
}


/**
 * Loading All CSS Stylesheets and Javascript Files.
 *
 * @since v1.0
 */
function trek_travel_theme_scripts_loader() {
	$theme_version = wp_get_theme()->get( 'Version' );

	// 1. Styles.
	wp_enqueue_style( 'style', get_theme_file_uri( 'style.css' ), array(), $theme_version, 'all' );
	wp_enqueue_style( 'main', get_theme_file_uri( 'assets/css/main.css' ), array(), time(), 'all' ); // main.scss: Compiled Framework source + custom styles.

	if ( is_rtl() ) {
		wp_enqueue_style( 'rtl', get_theme_file_uri( 'assets/css/rtl.css' ), array(), $theme_version, 'all' );
	}

	// 2. Scripts.
	wp_enqueue_script( 'mainjs', get_theme_file_uri( 'assets/main.js' ), array(), time(), true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_archive() || is_search() ) {
		wp_enqueue_script( 'tt-bs-carousel-lazy-load', get_theme_file_uri( 'assets/js/tt-bs-carousel-lazy-load.js' ), array(), time(), true );
	}
}
add_action( 'wp_enqueue_scripts', 'trek_travel_theme_scripts_loader' );

/**
 * Enqueue a script in the WordPress admin on edit.php.
 *
 * @param string $hook_suffix Hook suffix for the current admin page.
 */
function trek_travel_theme_admin_assets_loader( $hook_suffix ) {
	// Return if this is not edit.php
	if ( 'edit.php' !== $hook_suffix ) {
		return;
	}

	wp_register_style( 'trek-admin', get_theme_file_uri() . '/assets/css/trek-admin.css', false, '1.0.0' );
	wp_enqueue_style( 'trek-admin' );
}
add_action( 'admin_enqueue_scripts', 'trek_travel_theme_admin_assets_loader' );

/**

 * Load custom Mega menu implementation.

 */

require get_template_directory() . '/inc/trek-mega-menu.php';

// Promo Banner - above header
function promo_banner_post_type() {
	register_post_type('promo_banner', array(
		'public' => true,
		'labels' => array(
			'name' => 'Promo Banners',
			'singular_name' => 'Promo Banner'
		),
		'description' => 'Promotional banner above the header. Banner post must be published to show. Unpublish all to remove from header.',
		'show_in_nav_menus' => false,
		'menu_icon' => 'dashicons-megaphone'
	));
}

add_action('init', 'promo_banner_post_type');

// Hotels Post Type
function hotels_post_type() {
	register_post_type('hotels', array(
		'public' => true,
		'labels' => array(
			'name' => __( 'Hotels' ),
			'singular_name' => __( 'Hotel' ),
			'all_items' => __( 'All Hotels')
		),
		'menu_icon' => 'dashicons-admin-multisite',
		'has_archive' => false,
		'can_export' => true
	));
}
add_action('init', 'hotels_post_type');

// Bikes Post Type
function bikes_post_type() {
	register_post_type('bikes', array(
		'public' => true,
		'labels' => array(
			'name' => __( 'Bikes' ),
			'singular_name' => __( 'Bike' ),
			'all_items' => __( 'All Bikes')
		),
		'menu_icon' => 'dashicons-star-filled',
		'has_archive' => false,
		'can_export' => true
	));
}
add_action('init', 'bikes_post_type');

/**
 * Registers the `bike_type` taxonomy,
 * for use with 'bikes'.
 */
function tt_bike_type_init() {
	register_taxonomy( 'bike-type', [ 'bikes' ], [
			'hierarchical'          => false,
			'public'                => true,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'show_admin_column'     => false,
			'query_var'             => true,
			'rewrite'               => true,
			'capabilities'          => [
					'manage_terms' => 'edit_posts',
					'edit_terms'   => 'edit_posts',
					'delete_terms' => 'edit_posts',
					'assign_terms' => 'edit_posts',
			],
			'labels'                => [
					'name'                       => __( 'Bike types', 'YOUR-TEXTDOMAIN' ),
					'singular_name'              => _x( 'Bike type', 'taxonomy general name', 'YOUR-TEXTDOMAIN' ),
					'search_items'               => __( 'Search Bike types', 'YOUR-TEXTDOMAIN' ),
					'popular_items'              => __( 'Popular Bike types', 'YOUR-TEXTDOMAIN' ),
					'all_items'                  => __( 'All Bike types', 'YOUR-TEXTDOMAIN' ),
					'parent_item'                => __( 'Parent Bike type', 'YOUR-TEXTDOMAIN' ),
					'parent_item_colon'          => __( 'Parent Bike type:', 'YOUR-TEXTDOMAIN' ),
					'edit_item'                  => __( 'Edit Bike type', 'YOUR-TEXTDOMAIN' ),
					'update_item'                => __( 'Update Bike type', 'YOUR-TEXTDOMAIN' ),
					'view_item'                  => __( 'View Bike type', 'YOUR-TEXTDOMAIN' ),
					'add_new_item'               => __( 'Add New Bike type', 'YOUR-TEXTDOMAIN' ),
					'new_item_name'              => __( 'New Bike type', 'YOUR-TEXTDOMAIN' ),
					'separate_items_with_commas' => __( 'Separate bike types with commas', 'YOUR-TEXTDOMAIN' ),
					'add_or_remove_items'        => __( 'Add or remove bike types', 'YOUR-TEXTDOMAIN' ),
					'choose_from_most_used'      => __( 'Choose from the most used bike types', 'YOUR-TEXTDOMAIN' ),
					'not_found'                  => __( 'No bike types found.', 'YOUR-TEXTDOMAIN' ),
					'no_terms'                   => __( 'No bike types', 'YOUR-TEXTDOMAIN' ),
					'menu_name'                  => __( 'Bike types', 'YOUR-TEXTDOMAIN' ),
					'items_list_navigation'      => __( 'Bike types list navigation', 'YOUR-TEXTDOMAIN' ),
					'items_list'                 => __( 'Bike types list', 'YOUR-TEXTDOMAIN' ),
					'most_used'                  => _x( 'Most Used', 'bike-type', 'YOUR-TEXTDOMAIN' ),
					'back_to_items'              => __( '&larr; Back to Bike types', 'YOUR-TEXTDOMAIN' ),
			],
			'show_in_rest'          => true,
			'rest_base'             => 'bike-type',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
	] );

}
add_action( 'init', 'tt_bike_type_init' );

/**
* Sets the post updated messages for the `bike_type` taxonomy.
*
* @param  array $messages Post updated messages.
* @return array Messages for the `bike_type` taxonomy.
*/
function tt_bike_type_updated_messages( $messages ) {

	$messages['bike-type'] = [
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Bike type added.', 'YOUR-TEXTDOMAIN' ),
			2 => __( 'Bike type deleted.', 'YOUR-TEXTDOMAIN' ),
			3 => __( 'Bike type updated.', 'YOUR-TEXTDOMAIN' ),
			4 => __( 'Bike type not added.', 'YOUR-TEXTDOMAIN' ),
			5 => __( 'Bike type not updated.', 'YOUR-TEXTDOMAIN' ),
			6 => __( 'Bike types deleted.', 'YOUR-TEXTDOMAIN' ),
	];

	return $messages;
}
add_filter( 'term_updated_messages', 'tt_bike_type_updated_messages' );

// Itineraries Post Type
function itineraries_post_type() {
	register_post_type('itinerary', array(
		'public' => true,
		'labels' => array(
			'name' => __( 'Itineraries' ),
			'singular_name' => __( 'Itinerary' ),
			'all_items' => __( 'All Itineraries')
		),
		'menu_icon' => 'dashicons-location-alt',
		'has_archive' => true,
		'can_export' => true
	));
}
add_action('init', 'itineraries_post_type');

// Resource Center Post Type
function resourcecenter_post_type() {
	register_post_type('resourcecenter', array(
		'public' => true,
		'labels' => array(
			'name' => __( 'Resource Center' ),
			'singular_name' => __( 'Resource' ),
			'all_items' => __( 'All Resources')
		),
		'menu_icon' => 'dashicons-welcome-learn-more',
		'has_archive' => true,
		'can_export' => true
	));
}
add_action('init', 'resourcecenter_post_type');

// Register Team CPT
function register_team_cpt() {
    $labels = array(
        'name'               => 'Team',
        'singular_name'      => 'Team Member',
        'menu_name'          => 'Team',
        'add_new'            => 'Add New Member',
        'add_new_item'       => 'Add New Team Member',
        'edit_item'          => 'Edit Team Member',
        'new_item'           => 'New Team Member',
        'view_item'          => 'View Team Member',
        'search_items'       => 'Search Team Members',
        'not_found'          => 'No team members found',
        'not_found_in_trash' => 'No team members found in trash'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'show_in_rest'       => true, // Enable Gutenberg
        'menu_position'      => 8,
        'menu_icon'          => 'dashicons-groups', // Team icon
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'rewrite'            => array('slug' => 'team'),
    );

    register_post_type('team', $args);
}
add_action('init', 'register_team_cpt');

// Register Team Departments Taxonomy
function register_team_departments_taxonomy() {
    $labels = array(
        'name'              => 'Departments',
        'singular_name'     => 'Department',
        'search_items'      => 'Search Departments',
        'all_items'         => 'All Departments',
        'parent_item'       => 'Parent Department',
        'parent_item_colon' => 'Parent Department:',
        'edit_item'         => 'Edit Department',
        'update_item'       => 'Update Department',
        'add_new_item'      => 'Add New Department',
        'new_item_name'     => 'New Department Name',
        'menu_name'         => 'Departments'
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'team-department'),
        'show_in_rest'      => true,
    );

    register_taxonomy('team_department', array('team'), $args);
}
add_action('init', 'register_team_departments_taxonomy');

function modify_guide_search($query) {
    if (!is_admin() && $query->is_main_query() && is_page('our-guides') && isset($_GET['s'])) {
        $query->set('post_type', 'guide'); // Only search Guide CPT
        $query->set('orderby', 'title'); // Sort by title
        $query->set('order', 'ASC'); // Alphabetical order
        $query->set('meta_query', array()); // Ensure clean search
    }
}
add_action('pre_get_posts', 'modify_guide_search');

function get_years_guided($post_id) {
    $start_year = get_field('guide_years', $post_id);

    if (!$start_year || !is_numeric($start_year)) {
        return 'Years Guided: N/A';
    }

    $current_year = date("Y");
    $years_guided = max(1, $current_year - intval($start_year));

    return $years_guided === 1 ? '1 Year Guiding' : $years_guided . ' Years Guiding';
}


// Register "Testimonials" CPT
function register_testimonials_cpt() {
    $labels = array(
        'name'               => 'Testimonials',
        'singular_name'      => 'Testimonial',
        'menu_name'          => 'Testimonials',
        'add_new'            => 'Add New Testimonial',
        'add_new_item'       => 'Add New Testimonial',
        'edit_item'          => 'Edit Testimonial',
        'new_item'           => 'New Testimonial',
        'view_item'          => 'View Testimonial',
        'search_items'       => 'Search Testimonials',
        'not_found'          => 'No testimonials found',
        'not_found_in_trash' => 'No testimonials found in trash'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'show_in_rest'       => true, // Enable Gutenberg
        'menu_position'      => 7,
        'menu_icon'          => 'dashicons-testimonial',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'rewrite'            => array('slug' => 'testimonials'),
    );

    register_post_type('testimonial', $args);
}
add_action('init', 'register_testimonials_cpt');



//  FOR FRONT-END DEV ONLY  - TO BE REMOVED PRE PRODUCTION ////////////////////
/*function dev_components_post_type() {
	register_post_type('components', array(
		'public' => true,
		'labels' => array(
			'name' => 'FED Components'
		),
		'menu_icon' => 'dashicons-align-wide'
	));
}

add_action('init', 'dev_components_post_type');
*/

add_theme_support('woocommerce');


$trek_datalayer = __DIR__ . '/trek-datalayer.php';
if ( is_readable( $trek_datalayer ) ) {
	require_once $trek_datalayer;
}

// Define a function that will remove the action
function remove_session_message_action() {
    global $WC_Wishlist_Compatibility;
    if (!is_singular('/create-a-list/')) {
        if (is_object($WC_Wishlist_Compatibility) && method_exists($WC_Wishlist_Compatibility, 'add_session_message')) {
            remove_action('template_redirect', array($WC_Wishlist_Compatibility, 'add_session_message'));
        }
    }
}
add_action('init', 'remove_session_message_action');
//Delete shiiping billing address
add_action('wp_ajax_delete_user_address', 'delete_user_address_callback');
add_action('wp_ajax_nopriv_delete_user_address', 'delete_user_address_callback');
function delete_user_address_callback() {
    // Get the current user ID
    $user_id = get_current_user_id();
    $address_type = isset($_POST['addressType']) ? sanitize_text_field($_POST['addressType']) : '';
    if ($address_type === 'billing') {
        // Delete the user's billing address meta data
        delete_user_meta($user_id, 'billing_first_name');
        delete_user_meta($user_id, 'billing_last_name');
        delete_user_meta($user_id, 'billing_address_1');
        delete_user_meta($user_id, 'billing_address_2');
        delete_user_meta($user_id, 'billing_city');
        delete_user_meta($user_id, 'billing_state');
        delete_user_meta($user_id, 'billing_postcode');
        delete_user_meta($user_id, 'billing_country');
    } elseif($address_type === 'shipping'){
        // Delete the user's shipping address meta data
        delete_user_meta($user_id, 'shipping_first_name');
        delete_user_meta($user_id, 'shipping_last_name');
        delete_user_meta($user_id, 'shipping_address_1');
        delete_user_meta($user_id, 'shipping_address_2');
        delete_user_meta($user_id, 'shipping_city');
        delete_user_meta($user_id, 'shipping_state');
        delete_user_meta($user_id, 'shipping_postcode');
        delete_user_meta($user_id, 'shipping_country');
    }

    // Return a success response to the AJAX request
    wp_send_json_success();
}
add_filter('woocommerce_customer_save_address', 'custom_address_update_message_and_redirect', 10, 2);

function custom_address_update_message_and_redirect( $user_id, $load_address ) {
	// Add a check to prevent issues when updating users through the admin panel.
	if( ! is_admin() ) {
		$current_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
    	$relative_url = rtrim( home_url(), '/') . $current_path;

    	// Redirect to the relative URL
    	wp_redirect( $relative_url );
    	exit;
	}
}
/**
 * Redirect users to the My Account page after sign-up.
 *
 * @param int $user_id The ID of the newly registered user.
 */
add_filter( 'woocommerce_registration_redirect', function($redirect){
	$redirect = home_url( '/register-success/' );
	return $redirect;
});
//chnaged password metered strenght
 function iconic_min_password_strength( $strength ) {
     return 2;
 }
 
 add_filter( 'woocommerce_min_password_strength', 'iconic_min_password_strength', 10, 1 );


/**
 * @TODO: we have to update the location and most probably naming + capabilities
 * Most likely this is going to be moved in a separate plugin, as functions.php is not the best place, but we have to progress
 */
 if( function_exists( 'acf_add_options_page' ) ) {

	acf_add_options_page( array(
        'page_title'    => 'Trek Travel Settings',
        'menu_title'    => 'Trek Travel Settings',
        'menu_slug'     => 'trek-travel-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));

    // My Account - Resources Center
	acf_add_options_sub_page( array(
        'page_title'    => 'My Account - Resources Center',
        'menu_title'    => 'My Account - Resources Center',
		'parent_slug'   => 'trek-travel-settings',
		'capability'    => 'edit_posts',
    ));

	 // My Account - Travel Advisor
	 acf_add_options_sub_page( array(
        'page_title'    => 'My Account - Travel Advisor',
        'menu_title'    => 'My Account - Travel Advisor',
		'parent_slug'   => 'trek-travel-settings',
		'capability'    => 'edit_posts',
    ));
}

function dx_strip_text($htmlText){

	// Get the length of the HTML text
	$htmlLength = strlen($htmlText);

	// Initialize variables
	$charCount = 0;
	$insideTag = false;

	// Loop through each character in the HTML text
	for ($i = 0; $i < $htmlLength; $i++) {
		// Check if the character is within an HTML tag
		if ($htmlText[$i] === '<') {
			$insideTag = true;
		} elseif ($htmlText[$i] === '>') {
			$insideTag = false;
		}
		// Increment the character count if not inside an HTML tag
		if (!$insideTag) {
			$charCount++;
		}
		// Check if the character count is greater than or equal to 140
		if ($charCount >= 140) {
			// Extract the substring up to the current position, including the markup
			$result = substr($htmlText, 0, $i + 1);
			break;
		}
	}
	// If the character count is less than 140, use the original HTML text
	if ($charCount < 140) {
		$result = $htmlText;
	}
	// Output the result
	return $result;
}

// CUSTOM WOOCOMMERCE TAXONOMY

add_action( 'init', 'product_tax_activity' );

// Register Custom Taxonomy
function product_tax_activity()  {

$labels = array(
    'name'                       => 'Activities',
    'singular_name'              => 'Activity',
    'menu_name'                  => 'Activity',
    'all_items'                  => 'All Activities',
    'parent_item'                => 'Parent Activity',
    'parent_item_colon'          => 'Parent Activity:',
    'new_item_name'              => 'New Activity Name',
    'add_new_item'               => 'Add New Activity',
    'edit_item'                  => 'Edit Activity',
    'update_item'                => 'Update Activity',
    'separate_items_with_commas' => 'Separate Activity with commas',
    'search_items'               => 'Search Activities',
    'add_or_remove_items'        => 'Add or remove Activities',
    'choose_from_most_used'      => 'Choose from the most used Activities',
);
$args = array(
    'labels'                     => $labels,
    'hierarchical'               => true,
    'public'                     => true,
    'show_ui'                    => true,
    'show_admin_column'          => true,
    'show_in_nav_menus'          => true,
    'show_tagcloud'              => true,
);
register_taxonomy( 'activity', 'product', $args );

}

/**
 * Register a Product taxonomies.
 */
function tt_product_taxonomies()  {
	// Add new taxonomy, make it hierarchical (like categories).
	$destinations_tax_labels = array(
		'name'                       => _x( 'Destinations', 'taxonomy general name', 'trek-travel-theme' ), 
		'singular_name'              => _x( 'Destination', 'taxonomy singular name', 'trek-travel-theme' ),
		'menu_name'                  => __( 'Destinations', 'trek-travel-theme' ),
		'all_items'                  => __( 'All Destinations', 'trek-travel-theme' ),
		'parent_item'                => __( 'Parent Destination', 'trek-travel-theme' ),
		'parent_item_colon'          => __( 'Parent Destination:', 'trek-travel-theme' ),
		'new_item_name'              => __( 'New Destination Name', 'trek-travel-theme' ),
		'add_new_item'               => __( 'Add New Destination', 'trek-travel-theme' ),
		'edit_item'                  => __( 'Edit Destination', 'trek-travel-theme' ),
		'update_item'                => __( 'Update Destination', 'trek-travel-theme' ),
		'separate_items_with_commas' => __( 'Separate Destinations with commas', 'trek-travel-theme' ),
		'search_items'               => __( 'Search Destinations', 'trek-travel-theme' ),
		'add_or_remove_items'        => __( 'Add or remove Destinations', 'trek-travel-theme' ),
		'choose_from_most_used'      => __( 'Choose from the most used Destinations', 'trek-travel-theme' ),
		'not_found'                  => __( 'No Destinations found.', 'textdomain' ),
	);

	$destinations_tax_args = array(
		'labels'            => $destinations_tax_labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => false,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
		'query_var'         => true,
		'has_archive'       => true,
		'rewrite'           => array( 'hierarchical' => true, 'slug' => 'tours/destinations' ),
		'capabilities'          => [
			'manage_terms' => 'edit_posts',
			'edit_terms'   => 'edit_posts',
			'delete_terms' => 'edit_posts',
			'assign_terms' => 'edit_posts',
	],
	);

	register_taxonomy( 'destination', array( 'product' ), $destinations_tax_args );

	/**
	 * Registers the `activity-level` taxonomy,
	 * for use with 'product'.
	 *
	 * Change "Rider Levels" to "Activity Levels".
	 */
	register_taxonomy( 'activity-level', [ 'product' ], [
		'hierarchical'          => true,
		'public'                => true,
		'show_in_nav_menus'     => true,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'tours/activity-level' ),
		'capabilities'          => [
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
		],
		'labels'                => [
				'name'                       => __( 'Activity levels', 'trek-travel-theme' ),
				'singular_name'              => _x( 'Activity level', 'taxonomy general name', 'trek-travel-theme' ),
				'search_items'               => __( 'Search Activity levels', 'trek-travel-theme' ),
				'popular_items'              => __( 'Popular Activity levels', 'trek-travel-theme' ),
				'all_items'                  => __( 'All Activity levels', 'trek-travel-theme' ),
				'parent_item'                => __( 'Parent Activity level', 'trek-travel-theme' ),
				'parent_item_colon'          => __( 'Parent Activity level:', 'trek-travel-theme' ),
				'edit_item'                  => __( 'Edit Activity level', 'trek-travel-theme' ),
				'update_item'                => __( 'Update Activity level', 'trek-travel-theme' ),
				'view_item'                  => __( 'View Activity level', 'trek-travel-theme' ),
				'add_new_item'               => __( 'Add New Activity level', 'trek-travel-theme' ),
				'new_item_name'              => __( 'New Activity level', 'trek-travel-theme' ),
				'separate_items_with_commas' => __( 'Separate activity levels with commas', 'trek-travel-theme' ),
				'add_or_remove_items'        => __( 'Add or remove activity levels', 'trek-travel-theme' ),
				'choose_from_most_used'      => __( 'Choose from the most used activity levels', 'trek-travel-theme' ),
				'not_found'                  => __( 'No activity levels found.', 'trek-travel-theme' ),
				'no_terms'                   => __( 'No activity levels', 'trek-travel-theme' ),
				'menu_name'                  => __( 'Activity levels', 'trek-travel-theme' ),
				'items_list_navigation'      => __( 'Activity levels list navigation', 'trek-travel-theme' ),
				'items_list'                 => __( 'Activity levels list', 'trek-travel-theme' ),
				'most_used'                  => _x( 'Most Used', 'activity-level', 'trek-travel-theme' ),
				'back_to_items'              => __( '&larr; Back to Activity levels', 'trek-travel-theme' ),
		],
		'show_in_rest'          => true,
		'rest_base'             => 'activity-level',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	] );

	/**
	 * Registers the `hotel-level` taxonomy,
	 * for use with 'product'.
	 */
	register_taxonomy( 'hotel-level', [ 'product' ], [
		'hierarchical'          => true,
		'public'                => true,
		'show_in_nav_menus'     => true,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'tours/hotel-level' ),
		'capabilities'          => [
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
		],
		'labels'                => [
				'name'                       => __( 'Hotel levels', 'trek-travel-theme' ),
				'singular_name'              => _x( 'Hotel level', 'taxonomy general name', 'trek-travel-theme' ),
				'search_items'               => __( 'Search Hotel levels', 'trek-travel-theme' ),
				'popular_items'              => __( 'Popular Hotel levels', 'trek-travel-theme' ),
				'all_items'                  => __( 'All Hotel levels', 'trek-travel-theme' ),
				'parent_item'                => __( 'Parent Hotel level', 'trek-travel-theme' ),
				'parent_item_colon'          => __( 'Parent Hotel level:', 'trek-travel-theme' ),
				'edit_item'                  => __( 'Edit Hotel level', 'trek-travel-theme' ),
				'update_item'                => __( 'Update Hotel level', 'trek-travel-theme' ),
				'view_item'                  => __( 'View Hotel level', 'trek-travel-theme' ),
				'add_new_item'               => __( 'Add New Hotel level', 'trek-travel-theme' ),
				'new_item_name'              => __( 'New Hotel level', 'trek-travel-theme' ),
				'separate_items_with_commas' => __( 'Separate hotel levels with commas', 'trek-travel-theme' ),
				'add_or_remove_items'        => __( 'Add or remove hotel levels', 'trek-travel-theme' ),
				'choose_from_most_used'      => __( 'Choose from the most used hotel levels', 'trek-travel-theme' ),
				'not_found'                  => __( 'No hotel levels found.', 'trek-travel-theme' ),
				'no_terms'                   => __( 'No hotel levels', 'trek-travel-theme' ),
				'menu_name'                  => __( 'Hotel levels', 'trek-travel-theme' ),
				'items_list_navigation'      => __( 'Hotel levels list navigation', 'trek-travel-theme' ),
				'items_list'                 => __( 'Hotel levels list', 'trek-travel-theme' ),
				'most_used'                  => _x( 'Most Used', 'hotel-level', 'trek-travel-theme' ),
				'back_to_items'              => __( '&larr; Back to Hotel levels', 'trek-travel-theme' ),
		],
		'show_in_rest'          => true,
		'rest_base'             => 'hotel-level',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	] );

	/**
	 * Registers the `trip-class` taxonomy,
	 * for use with 'product'.
	 */
	register_taxonomy( 'trip-class', [ 'product' ], [
		'hierarchical'          => true,
		'public'                => true,
		'show_in_nav_menus'     => true,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'tours/trip-class' ),
		'capabilities'          => [
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
		],
		'labels'                => [
				'name'                       => __( 'Trip classes', 'trek-travel-theme' ),
				'singular_name'              => _x( 'Trip class', 'taxonomy general name', 'trek-travel-theme' ),
				'search_items'               => __( 'Search Trip classes', 'trek-travel-theme' ),
				'popular_items'              => __( 'Popular Trip classes', 'trek-travel-theme' ),
				'all_items'                  => __( 'All Trip classes', 'trek-travel-theme' ),
				'parent_item'                => __( 'Parent Trip class', 'trek-travel-theme' ),
				'parent_item_colon'          => __( 'Parent Trip class:', 'trek-travel-theme' ),
				'edit_item'                  => __( 'Edit Trip class', 'trek-travel-theme' ),
				'update_item'                => __( 'Update Trip class', 'trek-travel-theme' ),
				'view_item'                  => __( 'View Trip class', 'trek-travel-theme' ),
				'add_new_item'               => __( 'Add New Trip class', 'trek-travel-theme' ),
				'new_item_name'              => __( 'New Trip class', 'trek-travel-theme' ),
				'separate_items_with_commas' => __( 'Separate trip classes with commas', 'trek-travel-theme' ),
				'add_or_remove_items'        => __( 'Add or remove trip classes', 'trek-travel-theme' ),
				'choose_from_most_used'      => __( 'Choose from the most used trip classes', 'trek-travel-theme' ),
				'not_found'                  => __( 'No trip classes found.', 'trek-travel-theme' ),
				'no_terms'                   => __( 'No trip classes', 'trek-travel-theme' ),
				'menu_name'                  => __( 'Trip classes', 'trek-travel-theme' ),
				'items_list_navigation'      => __( 'Trip classes list navigation', 'trek-travel-theme' ),
				'items_list'                 => __( 'Trip classes list', 'trek-travel-theme' ),
				'most_used'                  => _x( 'Most Used', 'trip-class', 'trek-travel-theme' ),
				'back_to_items'              => __( '&larr; Back to Trip classes', 'trek-travel-theme' ),
		],
		'show_in_rest'          => true,
		'rest_base'             => 'trip-class',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	] );

	/**
	 * Registers the `trip-duration` taxonomy,
	 * for use with 'product'.
	 */
	register_taxonomy( 'trip-duration', [ 'product' ], [
		'hierarchical'          => true,
		'public'                => true,
		'show_in_nav_menus'     => true,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'tours/trip-duration' ),
		'capabilities'          => [
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
		],
		'labels'                => [
				'name'                       => __( 'Trip durations', 'trek-travel-theme' ),
				'singular_name'              => _x( 'Trip duration', 'taxonomy general name', 'trek-travel-theme' ),
				'search_items'               => __( 'Search Trip durations', 'trek-travel-theme' ),
				'popular_items'              => __( 'Popular Trip durations', 'trek-travel-theme' ),
				'all_items'                  => __( 'All Trip durations', 'trek-travel-theme' ),
				'parent_item'                => __( 'Parent Trip duration', 'trek-travel-theme' ),
				'parent_item_colon'          => __( 'Parent Trip duration:', 'trek-travel-theme' ),
				'edit_item'                  => __( 'Edit Trip duration', 'trek-travel-theme' ),
				'update_item'                => __( 'Update Trip duration', 'trek-travel-theme' ),
				'view_item'                  => __( 'View Trip duration', 'trek-travel-theme' ),
				'add_new_item'               => __( 'Add New Trip duration', 'trek-travel-theme' ),
				'new_item_name'              => __( 'New Trip duration', 'trek-travel-theme' ),
				'separate_items_with_commas' => __( 'Separate trip durations with commas', 'trek-travel-theme' ),
				'add_or_remove_items'        => __( 'Add or remove trip durations', 'trek-travel-theme' ),
				'choose_from_most_used'      => __( 'Choose from the most used trip durations', 'trek-travel-theme' ),
				'not_found'                  => __( 'No trip durations found.', 'trek-travel-theme' ),
				'no_terms'                   => __( 'No trip durations', 'trek-travel-theme' ),
				'menu_name'                  => __( 'Trip durations', 'trek-travel-theme' ),
				'items_list_navigation'      => __( 'Trip durations list navigation', 'trek-travel-theme' ),
				'items_list'                 => __( 'Trip durations list', 'trek-travel-theme' ),
				'most_used'                  => _x( 'Most Used', 'trip-duration', 'trek-travel-theme' ),
				'back_to_items'              => __( '&larr; Back to Trip durations', 'trek-travel-theme' ),
		],
		'show_in_rest'          => true,
		'rest_base'             => 'trip-duration',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	] );

	/**
	 * Registers the `trip-status` taxonomy,
	 * for use with 'product'.
	 */
	register_taxonomy( 'trip-status', [ 'product' ], [
		'hierarchical'          => true,
		'public'                => true,
		'show_in_nav_menus'     => true,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'tours/trip-status' ),
		'capabilities'          => [
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
		],
		'labels'                => [
				'name'                       => __( 'Trip statuses', 'trek-travel-theme' ),
				'singular_name'              => _x( 'Trip status', 'taxonomy general name', 'trek-travel-theme' ),
				'search_items'               => __( 'Search Trip statuses', 'trek-travel-theme' ),
				'popular_items'              => __( 'Popular Trip statuses', 'trek-travel-theme' ),
				'all_items'                  => __( 'All Trip statuses', 'trek-travel-theme' ),
				'parent_item'                => __( 'Parent Trip status', 'trek-travel-theme' ),
				'parent_item_colon'          => __( 'Parent Trip status:', 'trek-travel-theme' ),
				'edit_item'                  => __( 'Edit Trip status', 'trek-travel-theme' ),
				'update_item'                => __( 'Update Trip status', 'trek-travel-theme' ),
				'view_item'                  => __( 'View Trip status', 'trek-travel-theme' ),
				'add_new_item'               => __( 'Add New Trip status', 'trek-travel-theme' ),
				'new_item_name'              => __( 'New Trip status', 'trek-travel-theme' ),
				'separate_items_with_commas' => __( 'Separate trip statuses with commas', 'trek-travel-theme' ),
				'add_or_remove_items'        => __( 'Add or remove trip statuses', 'trek-travel-theme' ),
				'choose_from_most_used'      => __( 'Choose from the most used trip statuses', 'trek-travel-theme' ),
				'not_found'                  => __( 'No trip statuses found.', 'trek-travel-theme' ),
				'no_terms'                   => __( 'No trip statuses', 'trek-travel-theme' ),
				'menu_name'                  => __( 'Trip statuses', 'trek-travel-theme' ),
				'items_list_navigation'      => __( 'Trip statuses list navigation', 'trek-travel-theme' ),
				'items_list'                 => __( 'Trip statuses list', 'trek-travel-theme' ),
				'most_used'                  => _x( 'Most Used', 'trip-status', 'trek-travel-theme' ),
				'back_to_items'              => __( '&larr; Back to Trip statuses', 'trek-travel-theme' ),
		],
		'show_in_rest'          => true,
		'rest_base'             => 'trip-status',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'meta_box_cb'           => 'tt_product_taxonomy_meta_box'
	] );

	/**
	 * Registers the `trip-style` taxonomy,
	 * for use with 'product'.
	 */
	register_taxonomy( 'trip-style', [ 'product' ], [
		'hierarchical'          => true,
		'public'                => true,
		'show_in_nav_menus'     => true,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'tours/trip-style' ),
		'capabilities'          => [
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
		],
		'labels'                => [
				'name'                       => __( 'Trip styles', 'trek-travel-theme' ),
				'singular_name'              => _x( 'Trip style', 'taxonomy general name', 'trek-travel-theme' ),
				'search_items'               => __( 'Search Trip styles', 'trek-travel-theme' ),
				'popular_items'              => __( 'Popular Trip styles', 'trek-travel-theme' ),
				'all_items'                  => __( 'All Trip styles', 'trek-travel-theme' ),
				'parent_item'                => __( 'Parent Trip style', 'trek-travel-theme' ),
				'parent_item_colon'          => __( 'Parent Trip style:', 'trek-travel-theme' ),
				'edit_item'                  => __( 'Edit Trip style', 'trek-travel-theme' ),
				'update_item'                => __( 'Update Trip style', 'trek-travel-theme' ),
				'view_item'                  => __( 'View Trip style', 'trek-travel-theme' ),
				'add_new_item'               => __( 'Add New Trip style', 'trek-travel-theme' ),
				'new_item_name'              => __( 'New Trip style', 'trek-travel-theme' ),
				'separate_items_with_commas' => __( 'Separate trip styles with commas', 'trek-travel-theme' ),
				'add_or_remove_items'        => __( 'Add or remove trip styles', 'trek-travel-theme' ),
				'choose_from_most_used'      => __( 'Choose from the most used trip styles', 'trek-travel-theme' ),
				'not_found'                  => __( 'No trip styles found.', 'trek-travel-theme' ),
				'no_terms'                   => __( 'No trip styles', 'trek-travel-theme' ),
				'menu_name'                  => __( 'Trip styles', 'trek-travel-theme' ),
				'items_list_navigation'      => __( 'Trip styles list navigation', 'trek-travel-theme' ),
				'items_list'                 => __( 'Trip styles list', 'trek-travel-theme' ),
				'most_used'                  => _x( 'Most Used', 'trip-style', 'trek-travel-theme' ),
				'back_to_items'              => __( '&larr; Back to Trip styles', 'trek-travel-theme' ),
		],
		'show_in_rest'          => true,
		'rest_base'             => 'trip-style',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	] );

}
add_action( 'init', 'tt_product_taxonomies' );

/**
 * DX Keyword in the head
 */
function dx_keyword_in_head() {
	echo '<meta property="trektravel:online" content="true">';
}
add_action( 'wp_head', 'dx_keyword_in_head' );

// CUSTOM BREADCRUMBS

function custom_breadcrumbs() {
    // Bail if home to avoid unnecessary breadcrumb
    if (is_front_page()) return;

    echo '<nav class="container" aria-label="breadcrumb">';
    echo '<ol class="breadcrumb mb-1">';

    // Home link
    echo '<li class="breadcrumb-item fs-sm"><a href="'.esc_url(get_site_url()).'">Home</a></li>';

    // Current page or post
    if (is_single() || is_page()) {
        echo '<li class="breadcrumb-item active fs-sm" aria-current="page">'.esc_html(get_the_title()).'</li>';
    } elseif (is_category()) {
        $category = get_queried_object();
        echo '<li class="breadcrumb-item active fs-sm" aria-current="page">'.esc_html($category->name).'</li>';
    }
    // Add more conditions as needed for custom post types, archives, etc.

    echo '</ol>';
    echo '</nav>';
}

/**
 * Set permalinks for Tours by including the name of the activity type in the URL.
 *
 * @param string  $post_link The post's permalink.
 * @param WP_Post $post      The post in question.
 *
 * @link https://developer.wordpress.org/reference/hooks/post_type_link/
 */
function tt_activity_product_permalink( $post_link, $post ) {
    if( is_object( $post ) && 'product' === $post->post_type && ! empty( $post_link ) && ! in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ), true ) ) { 
        $terms = wp_get_object_terms( $post->ID, 'activity' );
        if( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            return str_replace( '%activity%', $terms[0]->slug, $post_link );
        } else {
			return str_replace( '%activity%', 'none', $post_link );
		}
    }
    return $post_link;
}
add_filter( 'post_type_link', 'tt_activity_product_permalink', 10, 2 );

/**
 * Add rewrite rules for each available activity terms dynamically.
 */
function tt_dynamic_rewrite_rules() {

	// Activity Terms.
    $activity_terms = get_terms(
		array(
			'taxonomy'   => 'activity',
			'hide_empty' => false,
    	)
	);

    if( ! empty( $activity_terms ) ) {
        foreach( $activity_terms as $term ) {
            add_rewrite_rule(
				'^tours/(' . $term->slug . ')/?$',
				'index.php?activity=$matches[1]',
				'top'
            );
        }
    }

	// Hotel Level Terms.
	$hotel_level_terms = get_terms(
		array(
			'taxonomy'   => 'hotel-level',
			'hide_empty' => false,
		)
	);

	if( ! empty( $hotel_level_terms ) ) {
		foreach( $hotel_level_terms as $term ) {
			add_rewrite_rule(
				'^tours/hotel-level/(' . $term->slug . ')/?$',
				'index.php?hotel-level=$matches[1]',
				'top'
			);
		}
	}

	// Activity Level Terms.
	$activity_level_terms = get_terms(
		array(
			'taxonomy'   => 'activity-level',
			'hide_empty' => false,
		)
	);

	if( ! empty( $activity_level_terms ) ) {
		foreach( $activity_level_terms as $term ) {
			add_rewrite_rule(
				'^tours/activity-level/(' . $term->slug . ')/?$',
				'index.php?activity-level=$matches[1]',
				'top'
			);
		}
	}

	// Trip Class Terms.
	$trip_class_terms = get_terms(
		array(
			'taxonomy'   => 'trip-class',
			'hide_empty' => false,
		)
	);

	if( ! empty( $trip_class_terms ) ) {
		foreach( $trip_class_terms as $term ) {
			add_rewrite_rule(
				'^tours/trip-class/(' . $term->slug . ')/?$',
				'index.php?trip-class=$matches[1]',
				'top'
			);
		}
	}

	// Trip Duration Terms.
	$trip_duration_terms = get_terms(
		array(
			'taxonomy'   => 'trip-duration',
			'hide_empty' => false,
		)
	);

	if( ! empty( $trip_duration_terms ) ) {
		foreach( $trip_duration_terms as $term ) {
			add_rewrite_rule(
				'^tours/trip-duration/(' . $term->slug . ')/?$',
				'index.php?trip-duration=$matches[1]',
				'top'
			);
		}
	}

	// Trip status Terms.
	$trip_status_terms = get_terms(
		array(
			'taxonomy'   => 'trip-status',
			'hide_empty' => false,
		)
	);

	if( ! empty( $trip_status_terms ) ) {
		foreach( $trip_status_terms as $term ) {
			add_rewrite_rule(
				'^tours/trip-status/(' . $term->slug . ')/?$',
				'index.php?trip-status=$matches[1]',
				'top'
			);
		}
	}

	// Trip style Terms.
	$trip_style_terms = get_terms(
		array(
			'taxonomy'   => 'trip-style',
			'hide_empty' => false,
		)
	);

	if( ! empty( $trip_style_terms ) ) {
		foreach( $trip_style_terms as $term ) {
			add_rewrite_rule(
				'^tours/trip-style/(' . $term->slug . ')/?$',
				'index.php?trip-style=$matches[1]',
				'top'
			);
		}
	}
	
	// Destination Terms.
	add_rewrite_rule(
		'^tours/destinations/(.+?)/?$',
		'index.php?destination=$matches[1]',
		'top'
	);
	
}
add_action( 'init', 'tt_dynamic_rewrite_rules' );

/**
 * Hooking into create and edit term and if the taxonomy is activity
 * then we're firing flush_rewrite_rules() to refresh permalinks.
 * 
 * @param int    $term_id  Term ID.
 * @param int    $tt_id    Term taxonomy ID.
 * @param string $taxonomy Taxonomy slug.
 *
 * @link https://developer.wordpress.org/reference/hooks/edit_term/
 * @link https://developer.wordpress.org/reference/hooks/create_term/
 */
function tt_flush_rewrite_rules( $term_id, $tt_id, $taxonomy ) {
    if( 'activity' === $taxonomy ) {
        $term = get_term_by( 'term_taxonomy_id', $tt_id );
        add_rewrite_rule(
            '^tours/(' . $term->slug . ')/?$',
            'index.php?activity=$matches[1]',
            'top'
        );
        if( ! function_exists( 'flush_rewrite_rules' ) ) {
            require_once WPINC . '/rewrite.php';
        }
        flush_rewrite_rules();
    }

	if( 'hotel-level' === $taxonomy ) {
		$term = get_term_by( 'term_taxonomy_id', $tt_id );
		add_rewrite_rule(
			'^tours/hotel-level/(' . $term->slug . ')/?$',
			'index.php?hotel-level=$matches[1]',
			'top'
		);
		if( ! function_exists( 'flush_rewrite_rules' ) ) {
			require_once WPINC . '/rewrite.php';
		}
		flush_rewrite_rules();
	}

	if( 'activity-level' === $taxonomy ) {
		$term = get_term_by( 'term_taxonomy_id', $tt_id );
		add_rewrite_rule(
			'^tours/activity-level/(' . $term->slug . ')/?$',
			'index.php?activity-level=$matches[1]',
			'top'
		);
		if( ! function_exists( 'flush_rewrite_rules' ) ) {
			require_once WPINC . '/rewrite.php';
		}
		flush_rewrite_rules();
	}

	if( 'trip-class' === $taxonomy ) {
		$term = get_term_by( 'term_taxonomy_id', $tt_id );
		add_rewrite_rule(
			'^tours/trip-class/(' . $term->slug . ')/?$',
			'index.php?trip-class=$matches[1]',
			'top'
		);
		if( ! function_exists( 'flush_rewrite_rules' ) ) {
			require_once WPINC . '/rewrite.php';
		}
		flush_rewrite_rules();
	}

	if( 'trip-duration' === $taxonomy ) {
		$term = get_term_by( 'term_taxonomy_id', $tt_id );
		add_rewrite_rule(
			'^tours/trip-duration/(' . $term->slug . ')/?$',
			'index.php?trip-duration=$matches[1]',
			'top'
		);
		if( ! function_exists( 'flush_rewrite_rules' ) ) {
			require_once WPINC . '/rewrite.php';
		}
		flush_rewrite_rules();
	}

	if( 'trip-status' === $taxonomy ) {
		$term = get_term_by( 'term_taxonomy_id', $tt_id );
		add_rewrite_rule(
			'^tours/trip-status/(' . $term->slug . ')/?$',
			'index.php?trip-status=$matches[1]',
			'top'
		);
		if( ! function_exists( 'flush_rewrite_rules' ) ) {
			require_once WPINC . '/rewrite.php';
		}
		flush_rewrite_rules();
	}

	if( 'trip-style' === $taxonomy ) {
		$term = get_term_by( 'term_taxonomy_id', $tt_id );
		add_rewrite_rule(
			'^tours/trip-style/(' . $term->slug . ')/?$',
			'index.php?trip-style=$matches[1]',
			'top'
		);
		if( ! function_exists( 'flush_rewrite_rules' ) ) {
			require_once WPINC . '/rewrite.php';
		}
		flush_rewrite_rules();
	}
}
add_action( 'edit_term', 'tt_flush_rewrite_rules', 10, 3 );
add_action( 'create_term', 'tt_flush_rewrite_rules', 10, 3 );

/**
 * Sets the post updated messages for the `activity_level` taxonomy.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `activity_level` taxonomy.
 */
function tt_activity_level_updated_messages( $messages ) {

	$messages['activity-level'] = [
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Activity level added.', 'trek-travel-theme' ),
			2 => __( 'Activity level deleted.', 'trek-travel-theme' ),
			3 => __( 'Activity level updated.', 'trek-travel-theme' ),
			4 => __( 'Activity level not added.', 'trek-travel-theme' ),
			5 => __( 'Activity level not updated.', 'trek-travel-theme' ),
			6 => __( 'Activity levels deleted.', 'trek-travel-theme' ),
	];

	return $messages;
}
add_filter( 'term_updated_messages', 'tt_activity_level_updated_messages' );

/**
 * Sets the post updated messages for the `trip_style` taxonomy.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `trip_style` taxonomy.
 */
function tt_trip_style_updated_messages( $messages ) {

	$messages['trip-style'] = [
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Trip style added.', 'trek-travel-theme' ),
			2 => __( 'Trip style deleted.', 'trek-travel-theme' ),
			3 => __( 'Trip style updated.', 'trek-travel-theme' ),
			4 => __( 'Trip style not added.', 'trek-travel-theme' ),
			5 => __( 'Trip style not updated.', 'trek-travel-theme' ),
			6 => __( 'Trip styles deleted.', 'trek-travel-theme' ),
	];

	return $messages;
}
add_filter( 'term_updated_messages', 'tt_trip_style_updated_messages' );

/**
 * Sets the post updated messages for the `hotel_level` taxonomy.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `hotel_level` taxonomy.
 */
function tt_hotel_level_updated_messages( $messages ) {

	$messages['hotel-level'] = [
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Hotel level added.', 'trek-travel-theme' ),
			2 => __( 'Hotel level deleted.', 'trek-travel-theme' ),
			3 => __( 'Hotel level updated.', 'trek-travel-theme' ),
			4 => __( 'Hotel level not added.', 'trek-travel-theme' ),
			5 => __( 'Hotel level not updated.', 'trek-travel-theme' ),
			6 => __( 'Hotel levels deleted.', 'trek-travel-theme' ),
	];

	return $messages;
}
add_filter( 'term_updated_messages', 'tt_hotel_level_updated_messages' );

/**
 * Sets the post updated messages for the `trip_duration` taxonomy.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `trip_duration` taxonomy.
 */
function tt_trip_duration_updated_messages( $messages ) {

	$messages['trip-duration'] = [
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Trip duration added.', 'trek-travel-theme' ),
			2 => __( 'Trip duration deleted.', 'trek-travel-theme' ),
			3 => __( 'Trip duration updated.', 'trek-travel-theme' ),
			4 => __( 'Trip duration not added.', 'trek-travel-theme' ),
			5 => __( 'Trip duration not updated.', 'trek-travel-theme' ),
			6 => __( 'Trip durations deleted.', 'trek-travel-theme' ),
	];

	return $messages;
}
add_filter( 'term_updated_messages', 'tt_trip_duration_updated_messages' );

/**
 * Sets the post updated messages for the `trip_status` taxonomy.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `trip_status` taxonomy.
 */
function tt_trip_status_updated_messages( $messages ) {

	$messages['trip-status'] = [
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Trip status added.', 'trek-travel-theme' ),
			2 => __( 'Trip status deleted.', 'trek-travel-theme' ),
			3 => __( 'Trip status updated.', 'trek-travel-theme' ),
			4 => __( 'Trip status not added.', 'trek-travel-theme' ),
			5 => __( 'Trip status not updated.', 'trek-travel-theme' ),
			6 => __( 'Trip statuses deleted.', 'trek-travel-theme' ),
	];

	return $messages;
}
add_filter( 'term_updated_messages', 'tt_trip_status_updated_messages' );

/**
 * Sets the post updated messages for the `trip_class` taxonomy.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `trip_class` taxonomy.
 */
function tt_trip_class_updated_messages( $messages ) {

	$messages['trip-class'] = [
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Trip class added.', 'trek-travel-theme' ),
			2 => __( 'Trip class deleted.', 'trek-travel-theme' ),
			3 => __( 'Trip class updated.', 'trek-travel-theme' ),
			4 => __( 'Trip class not added.', 'trek-travel-theme' ),
			5 => __( 'Trip class not updated.', 'trek-travel-theme' ),
			6 => __( 'Trip classes deleted.', 'trek-travel-theme' ),
	];

	return $messages;
}
add_filter( 'term_updated_messages', 'tt_trip_class_updated_messages' );

/**
 * Add info to the new trip duration columns.
 *
 * @param string $string Custom column output. Default empty.
 * @param string $column_name Name of the column.
 * @param int    $term_id Term ID.
 *
 * @return void
 */
function tt_show_trip_duration_info_in_columns( $string, $column_name, $term_id ) {
    switch ( $column_name ) {
        case 'trip-duration-pdp-name' :
            echo esc_html( get_term_meta( $term_id, 'pdp_name', true ) );
        break;
    }
}
add_action( 'manage_trip-duration_custom_column', 'tt_show_trip_duration_info_in_columns', 10, 3 );

/**
 * Adding the new column titles in the trip duration taxonomy.
 *
 * @param string[] $columns The column header labels keyed by column ID.
 *
 * @return string[] $columns The column header label keyed by column ID with added new columns.
 */
function tt_add_trip_duration_columns( $columns ) {
	$columns['trip-duration-pdp-name'] = __( 'PDP Name', 'trek-travel-theme' );
    return $columns;
}
add_filter( 'manage_edit-trip-duration_columns', 'tt_add_trip_duration_columns' );

/**
 * Add info to the new trip style columns.
 *
 * @param string $string Custom column output. Default empty.
 * @param string $column_name Name of the column.
 * @param int    $term_id Term ID.
 *
 * @return void
 */
function tt_show_trip_style_info_in_columns( $string, $column_name, $term_id ) {
    switch ( $column_name ) {
        case 'trip-style-order-num' :
            echo esc_html( get_term_meta( $term_id, 'order_number', true ) );
        break;
    }
}
add_action( 'manage_trip-style_custom_column', 'tt_show_trip_style_info_in_columns', 10, 3 );

/**
 * Adding the new column titles in the trip style taxonomy.
 *
 * @param string[] $columns The column header labels keyed by column ID.
 *
 * @return string[] $columns The column header label keyed by column ID with added new columns.
 */
function tt_add_trip_style_columns( $columns ) {
	$columns['trip-style-order-num'] = __( 'Filters - Order Number', 'trek-travel-theme' );
    return $columns;
}
add_filter( 'manage_edit-trip-style_columns', 'tt_add_trip_style_columns' );

/**
 * Add info to the new trip class columns.
 *
 * @param string $string Custom column output. Default empty.
 * @param string $column_name Name of the column.
 * @param int    $term_id Term ID.
 *
 * @return void
 */
function tt_show_trip_class_info_in_columns( $string, $column_name, $term_id ) {
    switch ( $column_name ) {
        case 'trip-class-order-num' :
            echo esc_html( get_term_meta( $term_id, 'order_number', true ) );
        break;
    }
}
add_action( 'manage_trip-class_custom_column', 'tt_show_trip_class_info_in_columns', 10, 3 );

/**
 * Adding the new column titles in the trip class taxonomy.
 *
 * @param string[] $columns The column header labels keyed by column ID.
 *
 * @return string[] $columns The column header label keyed by column ID with added new columns.
 */
function tt_add_trip_class_columns( $columns ) {
	$columns['trip-class-order-num'] = __( 'Filters - Order Number', 'trek-travel-theme' );
    return $columns;
}
add_filter( 'manage_edit-trip-class_columns', 'tt_add_trip_class_columns' );

// Woocommerce Simple Products NOINDEX

function yoast_noindex_nofollow_simple_products($robots) {
    if (is_singular('product')) {
        global $post;
        $product = wc_get_product($post->ID);

        // Debugging: Log the type and value of $robots
        error_log('Type of $robots: ' . gettype($robots));
        error_log('Value of $robots: ' . print_r($robots, true));

        if ($product && $product->is_type('simple')) {
            if (!is_array($robots)) {
                $robots = array();
            }

            $robots['index'] = 'noindex';
            $robots['follow'] = 'nofollow';
        }
    }

    return $robots;
}

add_filter('wpseo_robots', 'yoast_noindex_nofollow_simple_products');

// Post Type NOINDEX NOFOLLOW

function yoast_seo_noindex_non_published($robots) {
    if (is_single() && !is_preview() && get_post_status() !== 'publish') {
        $robots['index'] = 'noindex';
        $robots['follow'] = 'nofollow';
    }
    return $robots;
}
add_filter('wpseo_robots', 'yoast_seo_noindex_non_published');


// SEND USER PASSWORD CHANGE

add_action('password_reset', 'notify_user_password_reset', 10, 2);
function notify_user_password_reset($user, $new_pass) {
    send_password_changed_email($user);
}

function send_password_changed_email($user) {
    $to = $user->user_email;
    $subject = __('Your Password Has Been Changed', 'textdomain');
    $message = get_password_changed_email_content($user);
    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($to, $subject, $message, $headers);
}

function get_password_changed_email_content($user) {
    ob_start();
    include get_template_directory() . '/woocommerce/emails/customer-password-changed.php';
    return ob_get_clean();
}


// GF Country Values at Country Code

add_filter( 'gform_countries', function () {
    $countries = GF_Fields::get( 'address' )->get_default_countries();
    asort( $countries );
 
    return $countries;
} );

// Add Linked Parent Product Column to Products

// Add custom column to product list
add_filter('manage_edit-product_columns', 'add_parent_product_column', 15);
function add_parent_product_column($columns) {
	$date = $columns['date'];
	unset( $columns['date'] );
	$columns['parent_product'] = __('Parent Product', 'woocommerce');
	$columns['date']           = $date;
    return $columns;
}

// Populate the custom column
add_action('manage_product_posts_custom_column', 'add_parent_product_column_content', 10, 2);
function add_parent_product_column_content($column, $post_id) {
    if ($column == 'parent_product') {
        $parent_ids = get_parent_products($post_id);
        if (!empty($parent_ids)) {
            foreach ($parent_ids as $parent_id) {
                $parent_title = get_the_title($parent_id);
                echo '<a href="' . get_edit_post_link($parent_id) . '">' . $parent_title . '</a><br>';
            }
        } else {
            echo __('None', 'woocommerce');
        }
    }
}

// Retrieve parent products for a simple product
function get_parent_products($product_id) {
    global $wpdb;
    $parent_ids = [];

    // Query to find grouped products that include the current product
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID 
        FROM $wpdb->posts p
        INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
        WHERE p.post_type = 'product'
        AND pm.meta_key = '_children'
        AND pm.meta_value LIKE %s
    ", '%' . $wpdb->esc_like($product_id) . '%'));

    foreach ($results as $result) {
        $parent_ids[] = $result->ID;
    }

    return $parent_ids;
}

/**
 * Get All defined statuses Orders IDs for a defined product ID (or variation ID)
 *
 * @param int $product_id The product ID.
 *
 * @link https://stackoverflow.com/questions/43664819/get-all-orders-ids-from-a-product-id-in-woocommerce-hpos
 *
 * @return array The Array with Order IDs for this product.
 */
function get_orders_ids_by_product_id( $product_id ) {
    global $wpdb;

    // HERE Define the orders status to include IN (each order status always starts with "wc-")
    $orders_statuses = array('wc-completed', 'wc-processing', 'wc-on-hold');

    // Convert order statuses array to a string for the query
    $orders_statuses = "'" . implode("', '", $orders_statuses) . "'";

    // The query
    return $wpdb->get_col( $wpdb->prepare("
        SELECT DISTINCT woi.order_id
        FROM {$wpdb->prefix}woocommerce_order_itemmeta woim
        JOIN {$wpdb->prefix}woocommerce_order_items woi
            ON woi.order_item_id = woim.order_item_id
        JOIN {$wpdb->prefix}posts p
            ON woi.order_id = p.ID
        WHERE p.post_status IN ( {$orders_statuses} )
        AND woim.meta_key IN ( '_product_id', '_variation_id' )
        AND woim.meta_value = %d
        ORDER BY woi.order_item_id DESC;", intval($product_id) ) 
    );
}

// // LOCALIZE START DATE
function startdob_localize_trek_script() {
    if (is_checkout() && class_exists('WooCommerce')) {
        $start_dates = [];

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $_product = wc_get_product($cart_item['product_id']);
            if ($_product && $_product->exists()) {
                $s_date = $_product->get_attribute('start-date');
                if (!empty($s_date)) {
                    $date_parts = explode('/', $s_date);
                    if (count($date_parts) == 3) {
                        $year = intval($date_parts[2]) + 2000;
                        $formatted_date = $year . '-' . $date_parts[1] . '-' . $date_parts[0];
                        $start_dates[] = $formatted_date;
                    }
                }
            }
        }

        wp_register_script('trek-developer', get_template_directory_uri() . '/assets/js/developer.js', array('jquery'), time(), true);
        wp_localize_script('trek-developer', 'trekData', array('startDates' => $start_dates));
        wp_enqueue_script('trek-developer');
    }
}
add_action('wp_enqueue_scripts', 'startdob_localize_trek_script');


// Step 1: Add a new address type called 'Continental US'
add_filter('gform_address_types', 'add_continental_us_address_type');
function add_continental_us_address_type($address_types) {
    $address_types['continental_us'] = array(
        'label'       => 'Continental US',  // The label for the new address type
        'country'     => 'US',              // The country that this address type is for
        'zip_label'   => 'ZIP Code',        // The label for ZIP code
        'state_label' => 'State',           // The label for the state field
        'states'      => array(  // Enable state dropdown
							'AL' => 'Alabama',
							'AZ' => 'Arizona',
							'AR' => 'Arkansas',
							'CA' => 'California',
							'CO' => 'Colorado',
							'CT' => 'Connecticut',
							'DE' => 'Delaware',
							'FL' => 'Florida',
							'GA' => 'Georgia',
							'ID' => 'Idaho',
							'IL' => 'Illinois',
							'IN' => 'Indiana',
							'IA' => 'Iowa',
							'KS' => 'Kansas',
							'KY' => 'Kentucky',
							'LA' => 'Louisiana',
							'ME' => 'Maine',
							'MD' => 'Maryland',
							'MA' => 'Massachusetts',
							'MI' => 'Michigan',
							'MN' => 'Minnesota',
							'MS' => 'Mississippi',
							'MO' => 'Missouri',
							'MT' => 'Montana',
							'NE' => 'Nebraska',
							'NV' => 'Nevada',
							'NH' => 'New Hampshire',
							'NJ' => 'New Jersey',
							'NM' => 'New Mexico',
							'NY' => 'New York',
							'NC' => 'North Carolina',
							'ND' => 'North Dakota',
							'OH' => 'Ohio',
							'OK' => 'Oklahoma',
							'OR' => 'Oregon',
							'PA' => 'Pennsylvania',
							'RI' => 'Rhode Island',
							'SC' => 'South Carolina',
							'SD' => 'South Dakota',
							'TN' => 'Tennessee',
							'TX' => 'Texas',
							'UT' => 'Utah',
							'VT' => 'Vermont',
							'VA' => 'Virginia',
							'WA' => 'Washington',
							'WV' => 'West Virginia',
							'WI' => 'Wisconsin',
							'WY' => 'Wyoming',
						)             
    );
    return $address_types;
}


// Gravity Forms Trips Dropdown

add_filter( 'gform_pre_render_27', 'populate_grouped_products');
add_filter( 'gform_pre_validation_27', 'populate_grouped_products');
add_filter( 'gform_pre_submission_filter_27', 'populate_grouped_products');
add_filter( 'gform_admin_pre_render_27', 'populate_grouped_products');

function populate_grouped_products($form) {
    foreach ($form['fields'] as &$field) {
        // Ensure the field is a dropdown and has the correct CSS class
        if ($field->type !== 'select' || strpos($field->cssClass, 'populate-grouped-products') === false) {
            continue;
        }

        $field->choices = []; // Clear existing choices

        // Query to get WooCommerce Grouped Products
        $args = array(
            'post_type'      => 'product',
			'post_status'    => 'publish',
            'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'grouped'
                )
			),
			'meta_query'     => array(
				array(
					'key'     => 'is_private_custom_trip',
					'value'   => '0',
					'compare' => '='
			),
    ),
        );

        $products = get_posts($args);
        $choices = [];

        foreach ($products as $product) {
            $choices[] = array(
                'text'  => get_the_title($product->ID),
                'value' => $product->ID
            );
        }

        $field->choices = $choices;
    }
    return $form;
}



// Gravity Forms Guides Dropdown

add_filter('gform_pre_render_27', 'populate_guides');
add_filter('gform_pre_validation_27', 'populate_guides');
add_filter('gform_pre_submission_filter_27', 'populate_guides');
add_filter('gform_admin_pre_render_27', 'populate_guides');

function populate_guides($form) {
    foreach ($form['fields'] as &$field) {
        // Ensure the field is a dropdown or multiselect and has the correct CSS class
        if (($field->type !== 'select' && $field->type !== 'multiselect') || strpos($field->cssClass, 'populate-guides') === false) {
            continue;
        }

        $field->choices = []; // Clear existing choices

        // Query to get Guides from Custom Post Type 'team'
        $args = array(
            'post_type'      => 'team',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'team_department', 
                    'field'    => 'slug',
                    'terms'    => 'guide',
                    'operator' => 'IN',
                ),
            ),
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $guides = get_posts($args);
        $choices = [];

        foreach ($guides as $guide) {
            $choices[] = array(
                'text'  => get_the_title($guide->ID),
                'value' => $guide->ID,
                'isSelected' => false // Ensures it's correctly formatted for multi-select fields
            );
        }

        // Apply choices to dropdown and multi-select fields
        $field->choices = $choices;

        // Add a placeholder (only works for single dropdowns)
        if ($field->type === 'select') {
            $field->placeholder = 'Select a Guide';
        }
    }
    return $form;
}

// Gravity Forms Dropbox path
// add_filter('gform_dropbox_folder_path', function ($path, $entry, $form) {
//     $first_name = rgar($entry, '1.3'); // First Name
//     $last_name = rgar($entry, '1.6');  // Last Name

//     // Combine first & last name, if both exist
//     $full_name = trim($first_name . ' ' . $last_name);

//     // Sanitize the name to remove spaces & special characters
//     $safe_name = preg_replace('/[^A-Za-z0-9_-]/', '_', sanitize_text_field($full_name));

//     // Ensure the full Dropbox path is returned
//     return "/Trek Travel/Apps/Gravity Forms Add-On/TT-Testimonials/{$safe_name}/";
// }, 10, 3);

function trek_enqueue_guide_search_script() {
    // Check for the specific page template
    if (is_page_template('tpl-landing-guides.php')) {
        wp_enqueue_script(
            'guide-search',
            get_template_directory_uri() . '/assets/js/tpl/team.js',
            ['jquery'],
            filemtime(get_template_directory() . '/assets/js/tpl/team.js'),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'trek_enqueue_guide_search_script');

// function trek_enqueue_select2() {
//     // Check for the specific page template
//     if (is_page_template('tpl-landing-testimonial.php')) {
// 		wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
//         wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);

//         // Init script
//         wp_add_inline_script('select2-js', "
//             jQuery(document).ready(function($) {
//                 $('.use-select2 select[multiple]').select2({
//                     placeholder: 'Select Guides',
//                     allowClear: true
//                 });
//             });
//         ");
//     }
// }
// add_action('wp_enqueue_scripts', 'trek_enqueue_select2');

// function trek_enqueue_select2() {
//     wp_enqueue_style(
//         'select2-css',
//         'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
//     );
//     wp_enqueue_script(
//         'select2-js',
//         'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
//         ['jquery'],
//         null,
//         true
//     );

//     $inline_js = "
//     function initSelect2Default(context) {
//         jQuery('.use-select2 select', context).each(function () {
//             jQuery(this).select2({
//                 placeholder: 'Search here',
//                 allowClear: true,
//                 width: 'resolve'
//             });
//         });
//     }

//     jQuery(document).ready(function($) {
//         initSelect2Default(document);
//         $(document).on('gform_post_render', function() {
//             initSelect2Default(document);
//         });
//     });
//     ";

//     wp_add_inline_script('select2-js', $inline_js);
// }
// add_action('wp_enqueue_scripts', 'trek_enqueue_select2');

// Tom Select Enqueue

function trek_enqueue_tomselect() {
    wp_enqueue_style(
        'tom-select-css',
        'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css'
    );

    wp_enqueue_script(
        'tom-select-js',
        'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js',
        [],
        null,
        true
    );

    $inline_js = <<<JS
    function initTomSelect(context) {
        context = context || document;
        const selects = context.querySelectorAll('.use-select2 select:not(.ts-initialized)');

        selects.forEach(function(select) {
            if (!select.options || select.options.length === 0) return;

            new TomSelect(select, {
                // allowEmptyOption: true,
                placeholder: 'Search here',
                // maxOptions: 1000,
                plugins: ['remove_button']
            });

            select.classList.add('ts-initialized');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTomSelect(document);
    });

    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('gform_post_render', function(event, formId, currentPage) {
            initTomSelect(document);
        });
    }
JS;

    wp_add_inline_script('tom-select-js', $inline_js);
}
add_action('wp_enqueue_scripts', 'trek_enqueue_tomselect');






// Lightbox JS
function enqueue_lity_scripts() {
    // if (is_page_template('tpl-landing-guides.php')) { 
        wp_enqueue_style('lity-css', 'https://cdnjs.cloudflare.com/ajax/libs/lity/2.4.1/lity.min.css', array(), '2.4.1');
        wp_enqueue_script('lity-js', 'https://cdnjs.cloudflare.com/ajax/libs/lity/2.4.1/lity.min.js', array('jquery'), '2.4.1', true);
    // }
}
add_action('wp_enqueue_scripts', 'enqueue_lity_scripts');


// REMOVE COMMENTS
function remove_comments_admin() {
    // Remove Comments from Admin Menu
    remove_menu_page('edit-comments.php');  

    // Remove Comments from Admin Bar
    add_action('wp_before_admin_bar_render', function() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    });
}
add_action('admin_menu', 'remove_comments_admin');



add_filter( 'template_include', function( $template ) {
    if ( false !== strpos( $template, 'header-footer' ) && is_front_page() ) {
        $custom_template = get_stylesheet_directory() . '/elementor/modules/page-templates/templates/header-footer.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
    return $template;
}, 12 );

// Function to clear the Elementor content cache
function dx_tt_clear_homepage_elementor_cache( $post_id ) {
    if ( (int) get_option( 'page_on_front' ) === (int) $post_id ) {
        delete_transient( 'homepage_elementor_template' );
    }
}

// Clear cache when saving/updating posts/pages/products
add_action( 'save_post', 'dx_tt_clear_homepage_elementor_cache' ); // Clears cache on post/page update


// Add custom field to the product variations
// function acf_location_rule_woocommerce_product_type($match, $rule, $options) {
//     if (isset($options['post_id'])) {
//         $post_id = $options['post_id'];
        
//         if (get_post_type($post_id) !== 'product') {
//             return false;
//         }

//         $product = wc_get_product($post_id);

//         if ($product) {
//             $product_type = $product->get_type();

//             if ($rule['operator'] === '==' && $product_type === $rule['value']) {
//                 $match = true;
//             } elseif ($rule['operator'] === '!=' && $product_type !== $rule['value']) {
//                 $match = true;
//             } else {
//                 $match = false;
//             }
//         }
//     }

//     return $match;
// }
// add_filter('acf/location/rule_match/post_type', 'acf_location_rule_woocommerce_product_type', 10, 3);

// function acf_location_rule_woocommerce_product_type_choices($choices) {
//     $choices['simple'] = 'Simple Product';
//     $choices['variable'] = 'Variable Product';
//     $choices['grouped'] = 'Grouped Product';
//     $choices['external'] = 'External/Affiliate Product';
    
//     return $choices;
// }
// add_filter('acf/location/rule_values/post_type', 'acf_location_rule_woocommerce_product_type_choices');

// Turnstile Server-side Token Validation
add_action('woocommerce_register_post', 'trek_turnstile_validate_registration', 5, 3);

function trek_turnstile_validate_registration($username, $email, $validation_errors) {
    if (!isset($_POST['cf-turnstile-response'])) {
        $validation_errors->add('turnstile_missing', __('Human verification failed. Please try again.', 'your-theme'));
        return $validation_errors;
    }

    $response = sanitize_text_field($_POST['cf-turnstile-response']);
    $secret = '0x4AAAAAABWi6WFZMJqiGUSGPKqPKzQ9EBg';

    $verify = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'body' => [
            'secret' => $secret,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]
    ]);

    $success = false;

    if (!is_wp_error($verify)) {
        $data = json_decode(wp_remote_retrieve_body($verify), true);
        $success = !empty($data['success']);
    }

    if (!$success) {
        $validation_errors->add('turnstile_failed', __('Human verification failed. Please try again.', 'trek-travel-theme'));
    }

    return $validation_errors;
}

// Login Form Turnstile Validation
add_filter('woocommerce_process_login_errors', function($validation_error, $user_login, $user_password) {
    if (!empty($_POST['login'])) {
        if (empty($_POST['cf-turnstile-response'])) {
            $validation_error->add('turnstile_missing', __('Please verify you are human.', 'trek-travel-theme'));
            return $validation_error;
        }

        $token = sanitize_text_field($_POST['cf-turnstile-response']);
        $secret = '0x4AAAAAABWi6WFZMJqiGUSGPKqPKzQ9EBg';

        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ]
        ]);

        if (is_wp_error($response)) {
            $validation_error->add('turnstile_failed', __('Could not verify Turnstile. Please try again.', 'trek-travel-theme'));
            return $validation_error;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($data['success'])) {
            $validation_error->add('turnstile_failed', __('Turnstile verification failed. Please try again.', 'trek-travel-theme'));
        }
    }

    return $validation_error;
}, 10, 3);

// Honeypot check
add_action('woocommerce_register_post', 'tt_check_honeypot', 11, 3);
function tt_check_honeypot($username, $email, $validation_errors) {
    if (!empty($_POST['tt-ba'])) {
        $validation_errors->add('bot_detected', __('Bot detected. Registration blocked.', 'woocommerce'));
    }
}

// Registration time check
add_action('woocommerce_register_post', 'trek_validate_form_time_check', 10, 3);

function trek_validate_form_time_check($username, $email, $validation_errors) {
    // How many seconds minimum it should take to submit
    $minimum_form_time = 9;

    if (isset($_POST['form_start_time'])) {
        $form_start_time = intval($_POST['form_start_time']);
        $current_time = time();

        if (($current_time - $form_start_time) < $minimum_form_time) {
            $validation_errors->add('form_time_check', __('Form submitted too quickly. Please try again.', 'trek-travel-theme'));
        }
    } else {
        // No timestamp? Probably suspicious
        $validation_errors->add('form_time_check_missing', __('Form error detected. Please reload the page.', 'trek-travel-theme'));
    }

    return $validation_errors;
}

// IP Rate limiter
add_action('woocommerce_register_post', 'trek_registration_rate_limiter', 20, 3);

function trek_registration_rate_limiter($username, $email, $validation_errors) {
    // Settings
    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    $max_attempts = 5;
    $time_window = 20 * MINUTE_IN_SECONDS; // 10 minutes
    $now = time();

    // Validate IP format
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return $validation_errors;
    }

    // Create unique transient key per IP
    $transient_key = 'trek_reg_limit_' . wp_hash('register_' . $ip);

    // Get current attempts
    $attempt_data = get_transient($transient_key);

    if ($attempt_data === false || ($now - $attempt_data['start_time']) > $time_window) {
        $attempt_data = [
            'count' => 1,
            'start_time' => $now
        ];
    } else {
        $attempt_data['count']++;
    }

    // Save attempt data
    set_transient($transient_key, $attempt_data, $time_window);

    // Block if limit exceeded
    if ($attempt_data['count'] > $max_attempts) {
        $validation_errors->add('too_many_attempts', __('Too many registration attempts from your IP. Please try again in a few minutes.', 'trek-travel-theme'));
    }

    return $validation_errors;
}



/**
 * User Page -add Registration and Ordering by columns
 */
add_filter( 'manage_users_columns', 'tt_user_registration_column' );
function tt_user_registration_column( $columns ) {
    $columns['registered'] = 'Registration Date';
    return $columns;
}

add_action( 'manage_users_custom_column', 'tt_show_user_registration_column_content', 10, 3 );
function tt_show_user_registration_column_content( $value, $column_name, $user_id ) {
    if ( 'registered' === $column_name ) {
        $user = get_userdata( $user_id );
        return date( 'Y-m-d H:i', strtotime( $user->user_registered ) );
    }
    return $value;
}

add_filter( 'manage_users_sortable_columns', 'tt_make_user_registration_column_sortable' );
function tt_make_user_registration_column_sortable( $columns ) {
    $columns['registered'] = 'user_registered';
    return $columns;
}


// Login Register Modal Link Function

function trek_login_register_modal_link( $args = [] ) {
    $defaults = [
        'text'        => '',
        'class'       => 'btn btn-primary',
        'icon'        => '', // e.g., <i class="bi bi-person"></i>
        'return_url'  => '', // if you want to override default
        'wrapper'     => '', // span, div, etc. if needed
    ];
    $args = wp_parse_args( $args, $defaults );

    $url = '#login-register-modal';
    $return_url = $args['return_url'] ?: esc_url( add_query_arg( 'return_url', rawurlencode( get_permalink() ), home_url() ) );

    $html = sprintf(
        '<a href="%s" data-lity class="%s open-login-modal" data-return-url="%s">%s %s</a>',
        esc_attr( $url ),
        esc_attr( $args['class'] ),
        esc_attr( $return_url ),
        $args['icon'],
        esc_html( $args['text'] )
    );

    if ( $args['wrapper'] ) {
        return sprintf( '<%1$s>%2$s</%1$s>', $args['wrapper'], $html );
    }

    return $html;
}


function redirect_my_account_to_modal() {
    if ( is_account_page() && ! is_user_logged_in() && ! is_ajax() ) {
        ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                if (!document.body.classList.contains('woocommerce-checkout')) {
                    lity('#login-register-modal');
                }
            });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'redirect_my_account_to_modal' );

function trek_prevent_wc_login_template() {
    if ( is_account_page() && ! is_user_logged_in() && ! is_checkout() ) {
        wp_redirect( home_url() );
        exit;
    }
}
add_action( 'template_redirect', 'trek_prevent_wc_login_template', 1 );

function trek_my_account_link() {
    if ( ! is_user_logged_in() ) {
        $return_url = esc_url( home_url( $_SERVER['REQUEST_URI'] ) );
        return '<a href="#login-register-modal" data-lity class="open-login-modal" data-return-url="' . $return_url . '">My Account</a>';
    } else {
        return '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">My Account</a>';
    }
}

add_filter('wp_nav_menu_objects', 'trek_mmm_modify_my_account_menu_link', 10, 2);
function trek_mmm_modify_my_account_menu_link($items, $args) {
    foreach ($items as &$item) {
        // Replace with your actual menu location or condition
        if (strpos($item->url, '/my-account/') !== false && $item->ID == 83692) {
            if (!is_user_logged_in()) {
                $item->url = '#login-register-modal';
                $item->title = 'My Account';
                $item->classes[] = 'open-login-modal';
                $item->classes[] = 'data-lity'; // we’ll convert this to data-lity in the output fix below
                $item->classes[] = 'data-return-url-' . urlencode(home_url(add_query_arg([]))); // custom encoding
            }
        }
    }
    return $items;
}





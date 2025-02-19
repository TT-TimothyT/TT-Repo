<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

\Elementor\Plugin::$instance->frontend->add_body_class( 'elementor-template-full-width' );

get_header();

/**
 * Before Header-Footer page template content.
 */
do_action( 'elementor/page_templates/header-footer/before_content' );

// Cache key for homepage
$cache_key = 'homepage_elementor_template';
$cached_content = get_transient( $cache_key );

if ( false === $cached_content ) {
    // Start output buffering
    ob_start();

    // Generate Elementor content
    \Elementor\Plugin::$instance->modules_manager->get_modules( 'page-templates' )->print_content();

    // Store in transient for 12 hours
    $cached_content = ob_get_clean();
    set_transient( $cache_key, $cached_content, 12 * HOUR_IN_SECONDS );
}

// Output cached content
echo $cached_content;

/**
 * After Header-Footer page template content.
 */
do_action( 'elementor/page_templates/header-footer/after_content' );

get_footer();

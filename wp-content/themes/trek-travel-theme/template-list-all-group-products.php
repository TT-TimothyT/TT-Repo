<?php
/*
* Template Name: Woo All Group Products
*/

get_header();

function only_grouped_products( ){

    $tax_query[] = array(
        'taxonomy'  => 'product_type',
        'field'     => 'name',
        'terms'     => array( 'grouped' ),
    );

    $args = array(
        'post_type'         => 'product',
        'post_status'       => 'publish',
        'posts_per_page'    => '2000',
        'tax_query' => $tax_query,
    );


    $grouped_products = new WP_Query( $args );

    foreach( $grouped_products->posts as $key => $gproduct  ) {
        echo $gproduct->ID . ',';
    }
}

function only_simple_products( ){

    $tax_query[] = array(
        'taxonomy'  => 'product_type',
        'field'     => 'name',
        'terms'     => array( 'simple' ),
    );

    $args = array(
        'post_type'         => 'product',
        'post_status'       => 'publish',
        'posts_per_page'    => '2000',
        'tax_query' => $tax_query,
    );


    $grouped_products = new WP_Query( $args );

    foreach( $grouped_products->posts as $key => $gproduct  ) {
        echo $gproduct->ID . ' ';
    }
}

echo '<h1>Grouped Posts</h1>';
only_grouped_products();

echo '<h1>Simple Products</h1>';

only_simple_products();
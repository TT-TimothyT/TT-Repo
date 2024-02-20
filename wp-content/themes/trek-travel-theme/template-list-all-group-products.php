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
        var_dump( $gproduct->ID );
        wp_delete_post( $gproduct->ID ); // just trash them
    }
}

only_grouped_products();
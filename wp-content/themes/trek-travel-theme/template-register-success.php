<?php
/* Template Name: Register Success */

get_header();

// If user is logged in check if there has session variable with redirect url, and use it to redirect the user.
if( is_user_logged_in() ){
    // Start Session
    session_start();

    // Redirect to checkout page
    if( isset( $_SESSION["return_url"] ) && !empty( $_SESSION["return_url"]) ) {
        // Store the value from session.
        $redirect_to =  $_SESSION["return_url"];

        // Clear session variable.
        unset( $_SESSION["return_url"] );

        // Redirect user to the checkout page.
        wp_redirect( home_url( $redirect_to ) );
        exit;

    } else {

        echo '<div class="row create-account-success">';
        echo '<h4 class="fw-semibold">' . esc_html__( 'Success!', 'woocommerce' ) . '</h4>';
        echo '<p class="fw-normal fs-lg lh-lg">' . esc_html__( 'Your account is activated! You can now sign in using your email and password.', 'trek-travel-theme' ) . '</p>';
        echo '<a class="btn btn-primary rounded-1 w-100" href="' . esc_url( site_url( 'login' ) ) . '">' . esc_html__( 'Sign In', 'trek-travel-theme' ) . '</a>';
        echo '</div>';
    }
}

get_footer();
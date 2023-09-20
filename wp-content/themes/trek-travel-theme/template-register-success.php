<?php
/* Template Name: Register Success */

get_header();

echo '<div class="row create-account-success">';
echo '<h4 class="fw-semibold">' . esc_html__( 'Success!', 'woocommerce' ) . '</h4>';
echo '<p class="fw-normal fs-lg lh-lg">' . esc_html__( 'Your account is activated! You can now sign in using your email and password.', 'trek-travel-theme' ) . '</p>';
echo '<a class="btn btn-primary rounded-1 w-100" href="' . esc_url( site_url( 'login' ) ) . '">' . esc_html__( 'Sign In', 'trek-travel-theme' ) . '</a>';
echo '</div>';

get_footer();
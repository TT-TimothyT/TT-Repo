<?php
/**
 * Template Name: Landing Testimonial
 */

get_header(); 

?>

<main class="landing-page-container">

    <?php get_template_part('tpl-parts/common/hero', 'full'); ?>
    <?php get_template_part('tpl-parts/landing/testimonial', 'main'); ?>

</main>

<script>

jQuery('body').removeClass('elementor-kit-14');

</script>


<?php get_footer();


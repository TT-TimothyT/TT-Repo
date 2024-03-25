<?php
/**
 * Template Name: Landing H+W
 */

get_header(); 

?>

<main class="landing-page-container">

    <?php get_template_part('tpl-parts/common/hero', 'full'); ?>
    <?php get_template_part('tpl-parts/landing/intro', 'section'); ?>
    <?php get_template_part('tpl-parts/common/video', 'section'); ?>
    <?php get_template_part('tpl-parts/landing/main', 'section'); ?>
    <?php get_template_part('tpl-parts/landing/details', 'section'); ?>
    <?php get_template_part('tpl-parts/landing/levels', 'section'); ?>
    <?php get_template_part('tpl-parts/landing/inclusion', 'section'); ?>
    <?php get_template_part('tpl-parts/common/cta', 'blog'); ?>

</main>

<script>

jQuery('body').removeClass('elementor-kit-14');

</script>


<?php get_footer();


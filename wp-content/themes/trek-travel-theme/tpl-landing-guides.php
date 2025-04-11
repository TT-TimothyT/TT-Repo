<?php
/**
 * Template Name: Landing Guides
 */

get_header(); 

?>

<main class="landing-page-container">

    <?php get_template_part('tpl-parts/common/hero', 'full'); ?>
    <div class="guides-content">
        <?php get_template_part('tpl-parts/landing/guides', 'main'); ?>
    </div>
    

</main>

<script>

jQuery('body').removeClass('elementor-kit-14');

</script>


<?php get_footer();


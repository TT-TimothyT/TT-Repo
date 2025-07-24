<?php
/**
 * Template Name: Sustainability Page
 */

get_header(); ?>

<main id="" class="sustainability-page">
 
        <?php get_template_part('tpl-parts/common/hero-full'); ?>
        
        <!-- Intro Section -->
        <?php get_template_part('tpl-parts/common/intro-text'); ?>
        
        <!-- Measuring Section -->
        <?php get_template_part('tpl-parts/common/text-block-overlay'); ?>
        
        <!-- Scopes Section -->
        <?php get_template_part('tpl-parts/common/text-cta-overlay'); ?>
         
        <!-- Timeline Section -->
        <?php get_template_part('tpl-parts/sustainability/timeline'); ?>

        <!-- Partners Section -->
        <?php get_template_part('tpl-parts/common/feature-3-up'); ?>
        
        <!-- Impact Section -->
        <?php get_template_part('tpl-parts/common/cta-50-repeater'); ?>

        <!-- Support Section -->
        <?php get_template_part('tpl-parts/common/text-gallery-block'); ?>
        
        <!-- CTA Section -->
        <?php get_template_part('tpl-parts/common/cta-block'); ?>
        
        <!-- Follow Section -->
        <?php get_template_part('tpl-parts/common/blog-cat-slider'); ?>

</main>

<script>

jQuery('body').removeClass('elementor-kit-14');

</script>

<?php get_footer(); ?>
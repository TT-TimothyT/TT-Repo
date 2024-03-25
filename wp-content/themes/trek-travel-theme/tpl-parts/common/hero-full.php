<?php
// Hero Full Template Part

$featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

?>

<?php if ($featured_img_url): ?>
    <section class="banner-hero-full" style="background-image: url('<?php echo esc_url($featured_img_url); ?>');">
        <div class="container text-center">
        </div>
    </section>
<?php endif; ?>

<?php custom_breadcrumbs(); ?>
<?php
// Hero Full Template Part

$featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
$title = get_the_title();

?>

<?php if (!empty($featured_img_url)): ?>
    <section class="banner-hero-full" style="background-image: url('<?php echo esc_url($featured_img_url); ?>');">
    <?php if (!empty($title)){ ?>
        <div class="container text-center">
            <h1 class="fw-semibold"><?php echo $title; ?></h1>
        </div>
        <?php } ?>
    </section>
<?php endif; ?>

<?php 
// custom_breadcrumbs(); 
?>
<?php
/**
 * Template part for displaying the follow section
 * 
 */
do_action('enqueue_swiper_js');


$header = get_field('blog_header');
$background_image = get_field('blog_background');
$blog_category = get_field('blog_category');




$bg_style = '';
if ($background_image) {
    $bg_style = 'style="background-image: url(' . esc_url($background_image['url']) . ');"';
}

if (!$header) return;

// Query for sustainability blog posts
$blog_query = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 6,
    'cat' => $blog_category,
    'post_status' => 'publish'
]);
?>

<section class="blog-category-slider" <?php echo $bg_style; ?>>
    <div class="container">
        <?php if ($header): ?>
            <div class="row">
                <div class="col-12 col-xl-6 mx-auto text-center">
                    <div class="section-header h3">
                    <?php echo esc_html($header); 
                    
                    print_r($blog_category);?>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
        
        <?php if ($blog_query->have_posts()): ?>
            <div class="swiper blog-swiper">
                <div class="swiper-wrapper">
                    <?php while ($blog_query->have_posts()): $blog_query->the_post(); ?>
                        <div class="swiper-slide">
                            <a class="blog-card bg-overlay" href="<?php the_permalink(); ?> style="background-image: url(<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium')); ?>)" alt="<?php echo esc_attr($background_image['alt']); ?>">

                                <div class="blog-content">
                                    <?php the_title(); ?>
                                </div>

                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Navigation -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>

                <!-- Pagination (optional) -->
                <div class="swiper-pagination mt-3"></div>
            </div>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.blog-swiper', {
        // slidesPerView: 3,
        centeredSlides: true,
        spaceBetween: 30,
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev'
        },
        breakpoints: {
            768: { slidesPerView: 1 },
            992: { slidesPerView: 3 }
        }
    });
});

</script>
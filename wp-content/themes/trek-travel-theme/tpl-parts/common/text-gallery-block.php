<?php
/**
 * Template Part: Additional Support
 * 
 * Displays header, content, and partner gallerys in a gallery layout
 * Uses ACF fields: header, content, image_gallery
 */

// Get ACF fields
$header = get_field('header');
$content = get_field('content');
$image_gallery = get_field('image_gallery');

// Exit if no content
if (!$header && !$content && !$image_gallery) {
    return;
}
?>

<section class="text-gallery-block">
    <div class="container">
        <?php if ($header || $content): ?>
            <div class="row">
                <div class="col-12 col-lg-10 col-xl-8 mx-auto">
                    <div class="block-content text-center mb-5">
                        <?php if ($header): ?>
                            <h2 class="block-header mb-4"><?php echo esc_html($header); ?></h2>
                        <?php endif; ?>
                        
                        <?php if ($content): ?>
                            <div class="block-text mx-auto">
                                <?php echo wp_kses_post($content); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($image_gallery): ?>
            <div class="row">
                <div class="col-12">
                    <div class="gallery">
                            <?php 
                            
                            foreach ($image_gallery as $image): ?>
                                    <div class="gallery-img">
                                        <img src="<?php echo esc_url($image['sizes']['medium'] ?: $image['url']); ?>" 
                                             alt="<?php echo esc_attr($image['alt'] ?: 'Partner gallery'); ?>" 
                                             class="img-fluid">
                                    </div>
                            <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
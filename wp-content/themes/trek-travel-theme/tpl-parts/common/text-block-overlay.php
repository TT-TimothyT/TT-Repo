<?php
/**
 * Template part for displaying the measuring section
 * 
 */



$header = get_field('overlay_text');
$content = get_field('text_block');
$background_image = get_field('text_block_background');


$bg_style = '';
if ($background_image) {
    $bg_style = 'style="background-image: url(' . esc_url($background_image['url']) . ');"';
}
?>

<section class="text-block-overlay" <?php echo $bg_style; ?>>
    <div class="container">
        <div class="content-wrapper row">
            <?php if ($header): ?>
                <div class="col-11 col-lg-10 col-xl-9 mx-auto text-center">
                    <div class="block-header h2">
                        <?php echo esc_html($header); ?>
                    </div>

                </div>
            <?php endif; ?>
            
            <?php if ($content): ?>
                <div class="col-11 col-lg-10 col-xl-9 mx-auto block-box">
                    <div class="block-content">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
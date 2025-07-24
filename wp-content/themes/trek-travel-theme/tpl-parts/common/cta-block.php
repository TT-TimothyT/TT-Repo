<?php
/**
 * Template part for displaying the CTA section
 * 
 */

$background_image = get_field('cta_block_background');
$header = get_field('cta_block_header');
$content = get_field('cta_block_text');
$link = get_field('cta_block_button');

$bg_style = '';
if ($background_image) {
    $bg_style = 'style="background-image: url(' . esc_url($background_image['url']) . ');"';
}

if (!$header && !$content && !$link) return;
?>

<section class="cta-block" <?php echo $bg_style; ?>>
    <div class="container">
        <div class="row">
            <div class="col-12 col-xl-10 mx-auto text-center">
                
            
        <div class="cta-content">
            <?php if ($header): ?>
                <div class="section-header h2"><?php echo esc_html($header); ?></div>
            <?php endif; ?>
            
            <?php if ($content): ?>
                <div class="cta-text">
                    <?php echo wp_kses_post($content); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($link): ?>
                <?php 
                    tt_button($link, 'btn btn-secondary');
                ?>
            <?php endif; ?>
        </div>
        </div>
        </div>
    </div>
</section>
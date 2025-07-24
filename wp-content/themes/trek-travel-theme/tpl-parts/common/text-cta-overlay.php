<?php
/**
 * Template part for displaying the text cta overlay section
 * 
 * @package Theme_Name
 */

$header = get_field('overlay_header');
$ctas = get_field('content_cta_repeater');
$background_image = get_field('overlay_background');

if (!$header && !$ctas) return;

$bg_style = '';
if ($background_image) {
    $bg_style = 'style="background-image: url(' . esc_url($background_image['url']) . ');"';
}
?>

<section class="text-cta-overlay bg-overlay has-parallax" <?php echo $bg_style; ?>>
    <div class="container">
        <?php if ($header): ?>
            <div class="row">
                <div class="col-10 mx-auto text-center">
                    <div class="h2 section-header">
                        <?php echo esc_html($header); ?>
                    </div>
                </div>
        <?php endif; ?>
        
        <?php if ($ctas): ?>
            <div class="ctas row">
                <?php foreach ($ctas as $cta): 
                    $content = preg_replace('/[\x{00a0}\x{2028}\x{202f}]/u', ' ', $cta['content']);
                    ?>
                    <div class="cta-card col-10 mx-auto">
                        <?php if ($cta['content']): ?>
                            <div class="cta-content">
                                <?php echo wp_kses_post($content); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($cta['overlay_cta']): ?>
                            <?php 
                            tt_button($cta['overlay_cta'], 'btn btn-secondary');
                            ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
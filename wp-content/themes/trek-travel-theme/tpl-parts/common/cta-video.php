<?php
/**
 * Template part for displaying the details section
 * 
 * @package Theme_Name
 */

$header = get_field('cta_video_header');
$subheader = get_field('cta_video_subheader');
$cta_links = get_field('cta_repeater');
$video_url = get_field('video_url');
$background_image = get_field('timeline_background_image'); // Extends timeline background

if (!$header && !$cta_links && !$video_url) return;

$bg_style = '';
if ($background_image) {
    $bg_style = 'style="background-image: url(' . esc_url($background_image['url']) . ');"';
}


?>


<?php if (is_page('sustainability')): ?>
<section class="cta-video-section">
<?php else: ?>
    <section class="cta-video-section" <?php echo $bg_style; ?>>has-parallax d
<?php endif; ?>

    <div class="container">
        <div class="d-row row">
            <div class="col-12 col-xl-10 mx-auto">
            <?php if ($header): ?>
                <div class="section-header h2">
                    <?php echo esc_html($header); ?>
                </div>
            <?php endif; ?>
                
            <?php if ($subheader): ?>
                <div class="subheader h4 text-center"><?php echo esc_html($subheader); ?></div>
            <?php endif; ?>
            </div>
        </div>           
        <div class="cta-v-content">
            <?php 
            if ($cta_links): ?>
                <div class="cta-v-links row">
                    <?php foreach ($cta_links as $link): ?>
                        <?php if (!empty($link)): ?>
                            <?php 
                            tt_button($link, 'btn btn-primary','tt-button-wrapper col');
                            ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
          
            <?php if ($video_url): ?>
            <div class="row">
                <div class="col-12 col-xl-10 mx-auto text-center">
                    <div class="video-wrapper">
                    <?php echo get_video_embed($video_url); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

</section>

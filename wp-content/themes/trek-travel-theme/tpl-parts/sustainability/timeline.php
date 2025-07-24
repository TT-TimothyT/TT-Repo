<?php
/**
 * Template part for displaying the timeline section
 * 
 * @package Theme_Name
 */

$header = get_field('timeline_header');
$subheader = get_field('timeline_subheader');
$timeline_items = get_field('timeline_repeater');
$background_image = get_field('timeline_background_image');

if (!$header && !$timeline_items) return;

$bg_style = '';
if ($background_image) {
    $bg_style = 'style="background-image: url(' . esc_url($background_image['url']) . ');"';
}
?>

<section class="timeline-section bg-overlay" <?php echo $bg_style; ?>>
    <div class="container">
        <?php if ($header || $subheader): ?>
            <div class="section-headers">
                <?php if (!empty($header)): ?>
                    <div class="section-header h1"><?php echo esc_html($header); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($subheader)): ?>
                    <div class="subheader"><?php echo esc_html($subheader); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($timeline_items): ?>
            <div class="timeline-wrapper">
                <div class="timeline-track"></div>

                <?php foreach ($timeline_items as $i => $item): ?>
                    <div class="timeline-item">
                        
                        <div class="timeline-box">
                            <?php if ($item['year']): ?>
                                <div class="timeline-year"><?php echo esc_html($item['year']); ?></div>
                            <?php endif; ?>
                            
                            <?php if ($item['description']): ?>
                                <div class="timeline-content">
                                    <p><?php echo esc_html($item['description']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>

    <!-- Details Section -->
        <?php get_template_part('tpl-parts/common/cta-video'); ?>
</section>
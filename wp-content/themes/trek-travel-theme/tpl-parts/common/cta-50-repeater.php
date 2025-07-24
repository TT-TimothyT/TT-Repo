<?php
/**
 * Template part for displaying the cta section with breakout images
 * 
 */

$header = get_field('cta_header');
$cta_items = get_field('cta_contents');

if (!$header && !$cta_items) return;
?>

<section class="cta-repeater-section">
    <div class="container">
        <?php if ($header): ?>
            <div class="section-header-b h1">
                <?php echo esc_html($header); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($cta_items): ?>
        <div class="cta-grid">
            <?php foreach ($cta_items as $item): 
                $image = $item['image'];
                ?>
                <div class="cta-item">
                    <div class="cta-content-wrapper">
                        <div class="container">
                            <div class="row">
                                <div class="col-12 col-lg-6">
                                    <div class="cta-text">
                                        <?php if ($item['header']): ?>
                                            <div class="cta-header h2"><?php echo esc_html($item['header']); ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['content']): ?>
                                            <div class="cta-content">
                                                <?php echo wp_kses_post($item['content']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <!-- Space for image positioning -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($image): ?>
                        <div class="cta-image-breakout col-12 col-lg-6" style="background-image: url(<?php echo $image['url']; ?>)" alt="<?php echo esc_attr($image['alt']); ?>">
                </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

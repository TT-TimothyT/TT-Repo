<?php
/**
 * Template Part: Feature 3-Up
 * 
 * Displays features in a 3-column Bootstrap grid layout
 * Uses ACF fields: features_background, features, features_header
 */

// Get ACF fields
$features_background = get_field('features_background');
$features = get_field('features');
$features_header = get_field('features_header');

// Exit if no features
if (!$features) {
    return;
}
?>

<section class="features-section " <?php if ($features_background): ?>style="background-image: url('<?php echo esc_url($features_background['url']); ?>'); "<?php endif; ?>>
    <div class="container">
        <?php if ($features_header): ?>
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <div class="section-header h2"><?php echo esc_html($features_header); ?></div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="f-row row">
            <?php foreach ($features as $index => $feature): 
                $feature_header = $feature['feature_header'];
                $feature_image = $feature['feature_image'];
                $feature_content = $feature['feature_content'];
                $featured_items = $feature['featured'];
                
                
            ?>
                <div class="col-12">
                    <div class="feature-item">
                        <?php if ($feature_image): ?>
                            <div class="feature-image mb-3">
                                <img src="<?php echo esc_url($feature_image['url']); ?>" 
                                     alt="<?php echo esc_attr($feature_image['alt']); ?>" 
                                     class="img-fluid rounded">
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($feature_header): ?>
                            <h3 class="feature-title h4 mb-3"><?php echo esc_html($feature_header); ?></h3>
                        <?php endif; ?>
                        
                        <?php if ($feature_content): ?>
                            <div class="feature-content mb-3">
                                <?php echo wp_kses_post($feature_content); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($featured_items): ?>
                            <div class="row featured-items">
                                <?php foreach ($featured_items as $featured): 
                                    $featured_header = $featured['featured_header'];
                                    $featured_image = $featured['featured_image'];
                                    $featured_content = $featured['featured_content'];

                                    // Calculate column classes for responsive design
                                    if (count($featured_items) == 0) {
                                        $col_class = 'col-12 mb-4';
                                    } elseif (count($features) == 1) {
                                        $col_class = 'col-lg-6 col-md-6 col-12 mb-4';
                                    } else {
                                        $col_class = 'col-lg-4 col-md-6 col-12 mb-4';
                                    }
                                ?>
                                    <div class="featured-item pt-3 mt-3 <?php echo $col_class; ?>">
                                        <div class="f-item">
                                            <?php if ($featured_image): ?>
                                                <div class="f-img">
                                                    <img src="<?php echo esc_url($featured_image['url']); ?>" 
                                                         alt="<?php echo esc_attr($featured_image['alt']); ?>" 
                                                         class="featured-thumbnail">
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="f-content">
                                                <?php if ($featured_header): ?>
                                                    <div class="featured-title h3"><?php echo esc_html($featured_header); ?></div>
                                                <?php endif; ?>
                                                
                                                <?php if ($featured_content): ?>
                                                    <div class="featured-content">
                                                        <?php echo wp_kses_post($featured_content); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
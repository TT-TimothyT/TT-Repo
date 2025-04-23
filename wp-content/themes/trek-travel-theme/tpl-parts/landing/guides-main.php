<?php
// Guides Main
$guide_args = array(
    'post_type'      => 'team',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'tax_query'      => array(
        array(
            'taxonomy' => 'team_department', 
            'field'    => 'slug',
            'terms'    => 'guide',
            'operator' => 'IN',
        ),
    ),
    'orderby'        => 'title',
    'order'          => 'ASC',
);

$guides = get_posts($guide_args);

$guide_intro = get_field('guide_intro', get_the_ID());

if (!empty($guides)): ?>
    <section class="container py-5">
        <div class="row">
            <div class="col-12 text-center my-5 pb-5">
                <?php
                if (!empty($guide_intro)) {
                    echo wp_kses_post($guide_intro);
                }
                ?>                
            </div>
            <div class="col-12 col-lg-6 col-xl-4 mx-auto">
            
                <div class="guide-search-container">
                    <i class="fal fa-search search-icon"></i>
                    <input type="text" id="guide-search" class="guide-search-input" placeholder="Search Guides">
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <?php foreach ($guides as $guide):
                $guide_id = $guide->ID;
                $full_name = get_the_title($guide_id); // Get name from ACF
                $name_parts = explode(" ", trim($full_name)); // Split by spaces
                $nickname = get_field('guide_nickname', $guide_id);
                $years    = get_years_guided($guide_id);
                $hometown = get_field('guide_hometown', $guide_id);
                $what     = get_field('guide_what', $guide_id);
                $where    = get_field('guide_where', $guide_id);
                $photo    = get_the_post_thumbnail_url($guide_id, 'large');
                $pic_position = get_field('guide_pic_position', $guide_id);
            ?>

            <a href="#guideModal-<?php echo $guide_id; ?>" data-lity class="g-search col-12 col-md-6 col-xl-4 col-xxl-3 my-5">
                <div class="guide-card">
                <div class="guide-img" style="background-image: url('<?php echo esc_url($photo); ?>');<?php if (!empty($pic_position)) echo 'background-position: ' . esc_attr($pic_position) . ';'; ?>"></div>
                    <div class="guide-info">
                        <h4 class="guide-name"><?php foreach ($name_parts as $part): ?>
                                <span><?php echo esc_html($part); ?></span>
                            <?php endforeach; ?>
                        </h4>
                        <div class="guide-details">
                            <?php if (!empty($hometown)): ?>
                                <span><?php echo esc_html($hometown); ?></span><br>
                            <?php endif; ?>

                            <?php if (!empty($years)): ?>
                                <span><?php echo esc_html($years); ?></span>
                            <?php endif; ?>
                        </div>

                    </div>

                </div>
            </a>

            <div class="g-modal lity-hide" id="guideModal-<?php echo $guide_id; ?>">
                <button class="g-close" type="button" aria-label="Close (Press escape to close)" data-lity-close="" style="">×</button>
                    <div class="modal-content">
                    
                        <div class="g-header">
                            <img class="g-img" src="<?php echo esc_url($photo); ?>" style="<?php if (!empty($pic_position)) echo 'object-position: ' . esc_attr($pic_position) . ';'; ?>" alt="">
                            <div class="g-info">
                                <div class="g-names">
                                    <div class="g-title h2" id="guideModalLabel-<?php echo $guide_id; ?>"><?php echo esc_html(get_the_title($guide_id)); ?></div>
                                    <span class="g-nick h5"><?php echo esc_html($nickname); ?></span>
                                </div>
                                <div class="g-details">
                                    <?php if (!empty($hometown)): ?>
                                        <span><?php echo esc_html($hometown); ?></span><br>
                                    <?php endif; ?>

                                    <?php if (!empty($years)): ?>
                                        <span><?php echo esc_html($years); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="g-body">
                            <div class="g-what row">
                                <div class="col-12 mx-auto my-auto">
                                    <div class="what-content">
                                        <div class="h3">What I Love</div>
                                        <div><?php echo wp_kses_post($what); ?></div>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="g-place row">
                                <div class="col-12 mx-auto my-auto">
                                    <div class="place-content">
                                        <div class="p-text h3">My Happy Place</div>
                                        <div><?php echo wp_kses_post($where); ?></div>
                                    </div>
                                </div>
                            </div>
                        
                        </div> 
                    </div>
            </div>

            <?php endforeach; ?>
            <p id="no-results-message" style="display:none; text-align: center; font-weight: bold; margin-top: 20px;margin-bottom: 50px;">
                No guides found.
            </p>
        </div>
</section>


<?php else: ?>
    <p class="text-center">No guides found.</p>
<?php endif; ?>
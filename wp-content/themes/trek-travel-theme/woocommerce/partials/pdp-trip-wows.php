<!-- PDP trip wows start TREK-261 -->
<?php
    if( have_rows('trip_wows_list') ):
        $activity_tax = get_field('Activity');
        $activity = $activity_tax->name;
?>
<div class="container pdp-section trip-wows-container">
    <div class="row">
    	<h5 class="fw-semibold pdp-section__title fw-bold">Trip Wows</h5>

        <div class="col-12 col-lg-7 left-wow-details">
            <ul class="wow-list">
            <?php							
                // Loop through rows.
                while( have_rows('trip_wows_list') ) : the_row();
                    // Load sub field value.
                    $wow_point = get_sub_field('wow_point');                    
            ?>
                <li class="<?php if (!empty($activity) && $activity == 'Biking'):?>tt-blue<?php endif;?>"><p class="fw-normal"><?php echo $wow_point; ?></p></li>
            <?php endwhile; ?>		
            </ul>
        </div>

        <div class="col-12 col-lg-5 image-details">

			<?php 
			$image = get_field('wow_image');
			if( !empty( $image ) ): ?>
				<img class="wow-image" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
			<?php endif; ?>

            <p class="fw-medium fs-sm lh-sm text-muted"><?php the_field('wow_image_description'); ?></p>
            <p class="fw-bold fs-lg lh-lg"><?php the_field('wow_title'); ?></p>
            <p class="fw-normal fs-sm lh-sm mb-0"><?php the_field('wow_summary'); ?></p>
        </div>
    </div>

	<hr class="pdp-section__divider">
</div>
<?php
    endif;
?>
<!-- PDP trip wows end TREK-261 -->
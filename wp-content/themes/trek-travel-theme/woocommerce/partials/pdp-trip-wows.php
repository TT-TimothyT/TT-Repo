<!-- PDP trip wows start TREK-261 -->
<?php
    if( have_rows('trip_wows_list') ):
        $product = wc_get_product( get_the_ID() );
        $product_id = $product->get_id();
        
        $activity_terms = get_the_terms( $product_id, 'activity' );
        $is_event = has_term('Event Access', 'product_tag');
        
        foreach ( $activity_terms as $activity_term) {
            $activity = $activity_term->name;   
        }
?>
<div class="container pdp-section trip-wows-container">
    <div class="row">
    	<h5 class="fw-semibold pdp-section__title fw-bold"><?php echo $is_event ? 'Featured Experiences' : 'Trip Wows'; ?></h5>

        <div class="col-12 col-lg-7 left-wow-details">
            <ul class="wow-list">
            <?php							
                // Loop through rows.
                while( have_rows('trip_wows_list') ) : the_row();
                    // Load sub field value.
                    $wow_point = get_sub_field('wow_point');                    
            ?>
                <li class="<?php if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING):?>tt-blue<?php endif;?>"><p class="fw-normal"><?php echo $wow_point; ?></p></li>
            <?php endwhile; ?>		
            </ul>
        </div>

        <div class="col-12 col-lg-5 image-details">

			<?php 
			$image = get_field('wow_image');
			if( !empty( $image ) ): ?>
				<img class="wow-image" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
			<?php endif; ?>

            <p class="fw-medium fs-sm lh-sm text-muted"><?php echo wp_kses_post( get_field( 'wow_image_description' ) ); ?></p>
            <p class="fw-bold fs-lg lh-lg"><?php echo wp_kses_post( get_field( 'wow_title' ) ); ?></p>
            <p class="fw-normal fs-sm lh-sm mb-0"><?php echo wp_kses_post( get_field( 'wow_summary' ) ); ?></p>
        </div>
    </div>

	<hr class="pdp-section__divider">
</div>
<?php
    endif;
?>
<!-- PDP trip wows end TREK-261 -->
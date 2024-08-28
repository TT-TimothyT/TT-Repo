<?php
defined('ABSPATH') || exit;
global $post;
$pdp_itineraries = [$post->ID];
$book_now_link = get_trip_link_by_itinerary_id($post->ID);

$products = get_posts(array(
    'post_type' => 'product',
    'meta_query' => array(
        array(
            'key' => 'itineraries', // name of custom field
            'value' => '"' . get_the_ID() . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
            'compare' => 'LIKE'
        )
    )
));

foreach ($products as $product) {
    $product_id = $product->ID;
}


$activity_terms = get_the_terms( $product_id, 'activity' );

foreach ( $activity_terms as $activity_term) {
	$activity_type = $activity_term->name;   
}


if ( $pdp_itineraries ) :


?>
    <div class="container itinerary-container itinerary-details <?php if ( !empty($activity_type) && $activity_type != TT_ACTIVITY_DASHBOARD_NAME_BIKING ):?>hw<?php endif;?>" id="itinerary">
        <div class="row">
            <div class="col-12">
                <div class="tour-info">
                    <p class="fw-normal fs-lg lh-lg mb-1">Trip Itinerary</p>
                    <h3 class="fw-semibold"><?php echo $post->post_title; ?></h3>
                    <?php if($post->post_content): ?>
                        <p class="fw-normal fs-lg lh-lg"><?php echo $post->post_content; ?></p>
                    <?php endif; ?>
                    <div class="tour-actions">
                        <a href="#" id="itinerary-print-button" class="btn btn-white btn-md rounded-1 me-4">Print Itinerary</a>

                        <a href="<?php echo $book_now_link; ?>" class="btn btn-primary btn-md rounded-1">Book now</a>
                        <div class="mt-5">
                            <p><em>Print Tip: For wider content, set Margins to 'Minimum' in the print window settings.</em></p>
                        </div>
                    </div>
                </div>
                <div class="pdp-itinerary">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <?php 
                        $yi = 0;
                        foreach( $pdp_itineraries as $post ):
                            $yi++;

                            // To get ACF
                            setup_postdata($post); ?>

                            <button class="nav-link <?php if ($yi == 1) echo 'active';?>" id="nav-<?php echo esc_attr( get_field( 'year' ) ); ?>-tab" data-bs-toggle="tab" data-bs-target="#nav-<?php echo esc_attr( get_field( 'year' ) ); ?>" type="button" role="tab" aria-controls="nav-<?php echo esc_attr( get_field( 'year' ) ); ?>" aria-selected="true"><?php echo esc_attr( get_field( 'year' ) ); ?> Tours</button>
                        <?php 
                        endforeach;
                        wp_reset_postdata(); ?>

                        </div>
                    </nav>
                    <?php 
                    $yj = 0;
                    foreach( $pdp_itineraries as $post ):
                        $yj++;

                        // To get ACF
                        setup_postdata($post);?>

                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade <?php if($yj == 1) echo 'show active';?>" id="nav-<?php echo esc_attr( get_field( 'year' ) ); ?>" role="tabpanel" aria-labelledby="nav-<?php echo esc_attr( get_field( 'year' ) ); ?>-tab">
                            <?php 
                                $map_shortcode = get_field('map_shortcode'); 
                                $map_shortcode_with_callback = str_replace(']', ' loaded_callback="centerMapCallback"]', $map_shortcode);
                                echo do_shortcode($map_shortcode_with_callback); 
                            ?>
                                
                                                                
                                <div class="collapse-header d-md-flex justify-content-between align-items-center">
                                    <h5 class="fw-semibold pdp-section__title pdp-itinerary__title"><?php echo esc_attr( get_field( 'year' ) ); ?> Day-to-Day</h5>
                                    <div class="accordion-actions d-flex gap-4 my-4">
                                        <a href="javascript:void(0)" class="fw-normal fs-md lh-md expand-all">Expand All</a> |
                                        <a href="javascript:void(0)" class="fw-normal fs-md lh-md collapse-all">Collapse All</a>
                                    </div>
                                </div>
                                <div class="accordion" id="accordionFlushExample">
                                    <?php $i = 0; 
                                    $days = get_field( 'day_' );
                                    $arrivalArray = get_field('arrival');
                                    $departureArray = get_field('departure');
                                    ?>

                                    <div class="accordion-item">
                                        <h6 class="accordion-header" id="flush-headingOne">
                                            <button class="accordion-button mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapsearrival" aria-expanded="true" aria-controls="flush-collapsearrival">
                                                <span class="fw-medium fs-lg lh-lg">Arrival / Departure</span>
                                            </button>
                                        </h6>
                                        <div id="flush-collapsearrival" class="accordion-collapse collapse show" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                            <hr>
                                            <div class="accordion-body">
                                                <div class="d-flex justify-content-between accordion-item__collapsearrival">
                                                    <div class="accordion-item-ad__main">
                                                        <p class="fw-medium">Where to Arrive</p>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                        <i class="align-self-start fa-solid fa-plane-arrival"></i>
                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Airport<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block arrival-details"><?php echo $arrivalArray['arrival_airport']; ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                        <i class="align-self-start fa-solid fa-location-dot"></i>
                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Pick-up location<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block arrival-details"><?php echo $arrivalArray['arrival_pickup_location']; ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                        <i class="align-self-start fa-solid fa-clock"></i>
                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Pick-up time<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block"><?php echo $arrivalArray['arrival_pickup_time']; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item-ad__main">
                                                        <p class="fw-medium">Where to Depart</p>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                            <i class="align-self-start fa-solid fa-plane-departure"></i>
                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Airport<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block departure-details"><?php echo $departureArray['departure_airport']; ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                        <i class="align-self-start fa-solid fa-location-dot"></i>
                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Drop-off location<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block departure-details"><?php echo $departureArray['departure_pickup_location']; ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                        <i class="align-self-start fa-solid fa-clock"></i>
                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Drop-off time<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block"><?php echo $departureArray['departure_dropoff_time']; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion-item-ad__a d-lg-flex">
                                                    <div class="d-flex mb-4 accordion-item-ad__submain add-info">
                                                        <div class="accordion-item-ad__sub">
                                                            <p class="d-lg-block fw-medium">Additional Arrival Information</p>
                                                            <p class="mb-0 d-inline d-lg-block color-gray-6"><?php echo $arrivalArray['arrival_additional_information']; ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex mb-4 accordion-item-ad__submain add-info">
                                                        <div class="accordion-item-ad__sub">
                                                            <p class="d-lg-block fw-medium">Additional Departure Information</p>
                                                            <p class="mb-0 d-inline d-lg-block color-gray-6"><?php echo $departureArray['departure_additional_information']; ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    foreach( $days as $day ): 
                                        $i++; 
                                    ?>                                        
                                            <div class="accordion-item">
                                                <h6 class="accordion-header" id="flush-headingOne">
                                                    <button class="accordion-button mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo $i;?>" aria-expanded="true" aria-controls="flush-collapse<?php echo $i;?>">
                                                        <span class="fw-medium fs-lg lh-lg accordion-item__day">Day <?php echo $i;?></span>
                                                        <span class="fw-medium d-none d-lg-block accordion-item__day_text"><?php echo $day['day_title']; ?></span>
                                                    </button>
                                                </h6>
                                                <div id="flush-collapse<?php echo $i;?>" class="accordion-collapse collapse show" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                                    <hr>
                                                    <div class="accordion-body">
                                                        <p class="accordion-item__day_text d-block d-lg-none fw-medium fs-xl lh-xl mt-2"><?php echo $day['day_title']; ?></p>
                                                        <div class="pdp-itinerary-day__accordion row">
                                                            <div class="pdp-itinerary-day__accordion-left col-12 col-lg-7">
                                                                <p class="fw-medium pdp-itinerary-day__accordion-overview">Overview</p>
                                                                <p class="pdp-itinerary-day__accordion-clamp_main color-gray-6">
                                                                    <?php if (strlen($day['day_overview']) > 470) { ?>                                                                    
                                                                        <span class="less-text"><?php echo substr($day['day_overview'], 0, 470); ?>...</span>
                                                                        <span class="more-text d-none"><?php echo $day['day_overview']; ?></span>
                                                                        <a href="#" class="read-more-action">Read More</a>
                                                                    <?php } else { ?>
                                                                        <span><?php echo $day['day_overview']; ?></span>
                                                                    <?php } ?>
                                                                </p>
                                                                <div class="d-flex align-items-center pdp-itinerary-day__accordion-hotels">
                                                                    <?php if (isset($day['day_hotel']) && !empty($day['day_hotel'])) { ?>
                                                                        <div class="w-25">
                                                                        <i class="mb-3 fa-solid fa-hotel"></i>
                                                                            <p class="fs-md lh-md fw-medium mb-1 pdp-itinerary-day__accordion-heading">Hotel</p>
                                                                            <p class="mb-0 pdp-itinerary-day__accordion-text"><?php echo $day['day_hotel']; ?></p>
                                                                        </div>
                                                                    <?php } ?>
                                                                    <div class="w-25">
                                                                        <i class="mb-3 fa-solid fa-plate-utensils"></i>
                                                                        <p class="fs-md lh-md fw-medium mb-1 pdp-itinerary-day__accordion-heading">Meals included</p>
                                                                        <p class="mb-0 pdp-itinerary-day__accordion-text"><?php echo $day['day_meals_included']; ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="pdp-itinerary-day__accordion-right col-12 col-lg-4">
                                                                <?php if (!empty($day['day_highlight_image']['url'])) {
                                                                ?>
                                                                    <img class="pdp-itinerary-day__accordion-image" src="<?php echo $day['day_highlight_image']['url']; ?>" alt="<?php echo $day['day_highlight_title']; ?>">
                                                                <?php } ?>
                                                                    <p class="fs-sm lh-sm fw-medium mb-1 pdp-itinerary-day__accordion-highlight">Highlight of the Day</p>
                                                                <?php if (!empty($day['day_highlight_title']) ) {
                                                                ?>
                                                                    <p class="fs-md lh-md fw-medium mb-1"><?php echo $day['day_highlight_title']; ?></p>
                                                                <?php } ?>
                                                                <?php if (!empty($day['day_highlight_body'])) {
                                                                ?>
                                                                    <p class="fs-sm lh-sm mb-0 pdp-itinerary-day__accordion-right-clamp">                                                                
                                                                    <?php if (strlen($day['day_highlight_body']) > 140) { ?>    
                                                                        <span class="less-text"><?php echo dx_strip_text($day['day_highlight_body']); ?>...</span>
                                                                        <span class="more-text d-none"><?php echo $day['day_highlight_body']; ?></span>
                                                                        <a href="#" class="read-more-action-right">Read More</a>
                                                                    <?php } else { ?>
                                                                        <span><?php echo $day['day_highlight_body']; ?></span>
                                                                    <?php } ?>                                                                   
                                                                    </p>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                        <?php if (isset($day['ride_option_#']) && !empty($day['ride_option_#'])) { ?>
                                                            <hr class="w-100">
                                                            <p class="fw-medium pdp-itinerary-heading">
                                                            <?php
                                                            if (!empty($activity_type) && $activity_type == TT_ACTIVITY_DASHBOARD_NAME_BIKING):?>
                                                                Ride Options
                                                                <?php else: ?>
                                                                    Hiking Options
                                                            <?php endif;?>
                                                                </p>
                                                            <div class="pdp-itinerary-rides d-flex">
                                                                <?php 
                                                                $ii = 0; 
                                                                foreach( $day['ride_option_#'] as $ride_option ): 
                                                                $ii++ ?>
                                                                <div class="ride-column">
                                                                <?php if (!empty($activity_type) && $activity_type == TT_ACTIVITY_DASHBOARD_NAME_BIKING):?>
                                                                    <i class="mb-3 fa-solid fa-person-biking"></i>
                                                                    
                                                                    <?php else: ?>
                                                                        <i class="fa-solid fa-person-hiking"></i>
                                                                    <?php endif;?>
                                                                    <p class="fs-sm lh-sm fw-medium pdp-itinerary-rides__title mb-1"><?php if (!empty($activity_type) && $activity_type == TT_ACTIVITY_DASHBOARD_NAME_BIKING):?>
                                                                Ride Option
                                                                <?php else: ?>
                                                                    Hike Option
                                                            <?php endif;?><?php echo $ii;?></p>
                                                                    <p class="fs-md lh-md fw-medium pdp-itinerary-rides__subtitle mb-1"><?php echo $ride_option['ride_option_title'];?></p>
                                                                    <p class="fs-sm lh-sm pdp-itinerary-rides__distance mb-0"><?php echo $ride_option['ride_option_body'];?></p>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php } ?>
                                                        <?php if (isset($day['activity_#']) && !empty($day['activity_#'])) { ?>
                                                            <hr class="w-100">
                                                            <p class="fw-medium pdp-itinerary-heading">Activities</p>
                                                            <div class="pdp-itinerary-activities d-flex">
                                                            <?php 
                                                                $iii = 0; 
                                                                foreach( $day['activity_#'] as $activity ): 
                                                                $iii++ ?>
                                                                <div class="activity-column">
                                                                    <p class="fs-sm lh-sm fw-medium pdp-itinerary-activities__title mb-1">Activity <?php echo $iii;?></p>
                                                                    <p class="fs-md lh-md fw-medium pdp-itinerary-activities__subtitle mb-1"><?php echo $activity['activity_title'];?></p>
                                                                    <p class="fs-sm lh-sm pdp-itinerary-activities__distance mb-0"><?php echo $activity['activity_body'];?></p>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>                                        
                                    <?php 
                                    endforeach;                                    
                                    ?>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach;
		            wp_reset_postdata();
                ?>
            </div>
        </div>
    </div>
<?php endif; ?>
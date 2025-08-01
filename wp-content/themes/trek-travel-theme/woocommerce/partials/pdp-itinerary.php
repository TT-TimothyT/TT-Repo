<?php
defined('ABSPATH') || exit;

global $product;

// look for itineraries realation field on the product
$pdp_itineraries = get_field('itineraries');

$product = wc_get_product( get_the_ID() );
$product_id = $product->get_id();

$activity_terms = get_the_terms( $product_id, 'activity' );
$is_event = has_term('Event Access', 'product_tag');

foreach ( $activity_terms as $activity_term) {
	$activity_type = $activity_term->name;   
}


if ( $pdp_itineraries ) :
?>
    <a class="pdp-anchor" id="itinerary"></a>
    <div class="container pdp-section itinerary-container <?php if (!empty($activity_type) && $activity_type != TT_ACTIVITY_DASHBOARD_NAME_BIKING):?>hw<?php endif;?>" id="itinerary">
        <div class="row">
            <div class="col-12">
                <h5 class="fw-semibold pdp-section__title">
                    <?php echo $is_event ? 'Details' : 'Itinerary'; ?>
                </h5>
                <div class="pdp-itinerary">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <?php 
                        $yi = 0;
                        foreach( $pdp_itineraries as $itinerary ):
                            $yi++;
                        
                            setup_postdata($itinerary);
                        
                            $year_raw = get_field('year', $itinerary->ID);
                            $navKey = sanitize_title($year_raw); // safe for IDs
                        ?>
                        
                            <button class="nav-link <?php if ($yi == 1) echo 'active';?>" 
                                id="nav-<?php echo esc_attr($navKey); ?>-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#nav-<?php echo esc_attr($navKey); ?>" 
                                type="button" role="tab" 
                                aria-controls="nav-<?php echo esc_attr($navKey); ?>" 
                                aria-selected="true">
                                <?php echo esc_html($year_raw); ?>
                            </button>
                        
                        <?php 
                        endforeach;
                        wp_reset_postdata(); 
                         ?>

                        </div>
                    </nav>
                    <?php 
                    $yj = 0;
                    foreach( $pdp_itineraries as $itinerary ):
                        $yj++;

                        // To get ACF
                        setup_postdata($itinerary);
                        
                        $arrivalArray = get_field('arrival', $itinerary->ID);
                        $departureArray = get_field('departure', $itinerary->ID);

                        // Cleaning up the year value to be used as the navKey for IDs of the tabs 
                        $year_raw = get_field('year', $itinerary->ID);
                        $navKey = sanitize_title($year_raw);
                        ?>

                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade <?php if($yj == 1) echo 'show active';?>" id="nav-<?php echo $navKey;?>" role="tabpanel" aria-labelledby="nav-<?php echo $navKey;?>-tab">
                            <?php
                                $map_shortcode = get_field('map_shortcode', $itinerary->ID);

                                if (strpos($map_shortcode, 'map_id') !== false) {
                                    $map_shortcode = str_replace(']', ' loaded_callback="waymark_refresh"]', $map_shortcode);
                                }
                                echo do_shortcode($map_shortcode);
                            ?>
                            <?php if (empty($is_event)) { ?>
                                <div class="d-md-flex justify-content-between align-items-center">
                                    <h5 class="fw-semibold pdp-section__title pdp-itinerary__title"><?php echo esc_attr( get_field( 'year' ) ); ?> Day-to-Day</h5>
                                    <a href="<?php the_permalink( $itinerary->ID ); ?>" target="_blank" class="btn btn-md btn-outline-dark align-self-start pdp-itinerary__button">View full itinerary</a>
                                </div>
                                <?php } ?>
                                <div class="accordion" id="accordionFlushExample">
                                   <?php if (!empty(array_filter($arrivalArray)) && !empty(array_filter($departureArray))) { 
                                    ?>
                                    <div class="accordion-item">
                                        
                                            <button class="accordion-button collapsed mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapsearrival" aria-expanded="false" aria-controls="flush-collapsearrival">
                                                <h6 class="accordion-header" id="flush-headingOne">
                                                    <span class="fw-medium fs-lg lh-lg">Arrival / Departure</span>
                                                </h6>
                                            </button>
                                        
                                        <div id="flush-collapsearrival" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                            <hr>
                                            <div class="accordion-body">
                                                <div class="d-flex justify-content-between accordion-item__collapsearrival">
                                                <?php if(!empty(array_filter($arrivalArray))) {?>
                                                    <div class="accordion-item-ad__main align-self-md-start">
                                                        <p class="fw-medium">Where to Arrive</p>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                        <i class="align-self-start fa-solid fa-plane-arrival"></i>
                                                            
                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Airport<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block"><?php echo $arrivalArray['arrival_airport']; ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                        <i class="align-self-start fa-solid fa-location-dot"></i>
                                                        
                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Pick-up location<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block"><?php echo $arrivalArray['arrival_pickup_location']; ?></p>
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
                                                    <?php } 
                                                    if(!empty(array_filter($departureArray))) { 
                                                    ?>
                                                    <div class="accordion-item-ad__main align-self-md-start">
                                                        <p class="fw-medium">Where to Depart</p>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                        <i class="align-self-start fa-solid fa-plane-departure"></i>

                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Airport<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block"><?php echo $departureArray['departure_airport']; ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex mb-4 accordion-item-ad__submain">
                                                        <i class="align-self-start fa-solid fa-location-dot"></i>
                                                            <div class="accordion-item-ad__sub">
                                                                <p class="mb-0 d-inline d-lg-block fw-medium">Drop-off location<span class="d-inline d-lg-none">: </span></p>
                                                                <p class="mb-0 d-inline d-lg-block"><?php echo $departureArray['departure_pickup_location']; ?></p>
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
                                                    <?php } ?>
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
                                    <?php } ?>
                                    <?php $i = 0;
				 
                                    $days = get_field( 'day_', $itinerary->ID );
                                    foreach( $days as $day ): 
                                        $i++;
                                        if ( $i <= 13 ): 
                                    ?>
                                        
                                            <div class="accordion-item">
                                                <h6 class="accordion-header" id="flush-headingOne">
                                                    <button class="accordion-button collapsed mb-0" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo $i;?>" aria-expanded="false" aria-controls="flush-collapse<?php echo $i;?>">
                                                        <?php if(empty($is_event)) { ?>
                                                        <span class="fw-medium fs-lg lh-lg accordion-item__day">Day <?php echo $i;?></span>
                                                        <?php } ?>
                                                        <span class="fw-medium d-none d-lg-block"><?php echo $day['day_title']; ?></span>
                                                    </button>
                                                </h6>
                                                <div id="flush-collapse<?php echo $i;?>" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                                    <hr>
                                                    <div class="accordion-body">
                                                        <p class="d-block d-lg-none fw-medium fs-xl lh-xl mt-2"><?php echo $day['day_title']; ?></p>
                                                        <div class="pdp-itinerary-day__accordion row">
                                                            <div class="pdp-itinerary-day__accordion-left  col-12 col-xl-8 col-xxl-9">
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
                                                                        <div class="w-25 align-self-md-start">
                                                                        <i class="mb-3 fa-solid fa-hotel"></i>
                                                                            <p class="fs-md lh-md fw-medium mb-1 pdp-itinerary-day__accordion-heading">Hotel</p>
                                                                            <p class="mb-0 pdp-itinerary-day__accordion-text"><?php echo $day['day_hotel']; ?></p>
                                                                        </div>
                                                                    <?php } ?>
                                                                    <div class="w-25 align-self-md-start">
                                                                    <i class="mb-3 fa-solid fa-plate-utensils"></i>
                                                                        <p class="fs-md lh-md fw-medium mb-1 pdp-itinerary-day__accordion-heading">Meals included</p>
                                                                        <p class="mb-0 pdp-itinerary-day__accordion-text"><?php echo $day['day_meals_included']; ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php if (!empty($day['day_highlight_title']) && !empty($day['day_highlight_body'])) {
                                                            ?>
                                                            <div class="pdp-itinerary-day__accordion-right col-12 col-xl-4 col-xxl-3">
                                                                <?php if (!empty($day['day_highlight_image']) ) { ?>
                                                                <img class="pdp-itinerary-day__accordion-image" src="<?php echo $day['day_highlight_image']['url']; ?>" alt="<?php echo $day['day_highlight_title']; ?>">
                                                                <?php } ?>
                                                                <p class="fs-sm lh-sm fw-medium mb-1 pdp-itinerary-day__accordion-highlight">Highlight of the Day</p>
                                                                <p class="fs-md lh-md fw-medium mb-1"><?php echo $day['day_highlight_title']; ?></p>
                                                                <p class="fs-sm lh-sm mb-0 pdp-itinerary-day__accordion-right-clamp">
                                                                
                                                        

                                                                <?php if (strlen($day['day_highlight_body']) > 140) { ?>    
                                                                    <span class="less-text"><?php echo dx_strip_text($day['day_highlight_body']); ?>...</span>
                                                                    <span class="more-text d-none"><?php echo $day['day_highlight_body']; ?></span>
                                                                    <a href="#" class="read-more-action-right">Read More</a>
                                                                <?php } else { ?>
                                                                    <span><?php echo $day['day_highlight_body']; ?></span>
                                                                <?php } ?>                                                                   
                                                                </p>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                        <?php if (isset($day['ride_option_#']) && !empty($day['ride_option_#'])) { ?>
                                                            <hr class="w-100 mt-5">
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
                                        endif;
                                    endforeach;
                                        ?>
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
    </div>
<?php endif; ?>
<script>

    jQuery('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        tabDivId = jQuery(this).attr("aria-controls")
        if (jQuery("div#"+tabDivId+" div.waymark-map-container").length < 1) {            
            workingMapContainerClone = jQuery("div.waymark-map-container").clone();
            jQuery("div#"+tabDivId+" div.waymark-map").replaceWith(workingMapContainerClone)
        }
	});
    
</script>

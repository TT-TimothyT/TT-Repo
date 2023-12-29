<!-- overview bar section TREK-235 -->
<?php
defined('ABSPATH') || exit;

global $product;

$product_overview = get_field('product_overview');
$pdp_bikes = get_field('bikes');

if ($product_overview) :
    $product_subtitle = $product_overview['product_subtitle'];
?>

    <div class="container my-5 pdp-overview-bar">
        <div class="row">
            <div class="col-lg-5 product-main-info">
                <nav class="mb-0" aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item fs-sm"><a href="<?php echo get_site_url(); ?>">Home</a></li>
                        <li class="breadcrumb-item active fs-sm" aria-current="page"><?php echo get_the_title(); ?></li>
                    </ol>
                </nav>
                <h1 class="fw-semibold"><?php echo get_the_title(); ?></h1>
                <?php if ($product_subtitle) : ?>
                    <p class="fw-normal fs-md lh-lg mb-0"><?php echo $product_subtitle; ?></p>
                <?php endif; ?>
                <ul class="list-inline mb-0 star-rating-custom">
                <?php function_exists('wc_yotpo_show_buttomline') ? wc_yotpo_show_buttomline() : ''; ?>
                    <!--<li class="list-inline-item me-0"><i class="bi bi-star-fill"></i></li>
                <li class="list-inline-item me-0"><i class="bi bi-star-fill"></i></li>
                <li class="list-inline-item me-0"><i class="bi bi-star-fill"></i></li>
                <li class="list-inline-item me-0"><i class="bi bi-star-fill"></i></li>
                <li class="list-inline-item me-0"><i class="bi bi-star-half"></i></li>
                <li class="list-inline-item fw-bold fs-sm lh-sm me-0">4.5</li>
                <li class="list-inline-item fs-sm lh-sm me-0"><a href="#">(66) Reviews</a></li>
                -->
                </ul>
            </div>
            <div class="overview-details col-lg-5">
                <div class="tour-duration">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Tour Duration</p>
                    <p class="fw-medium fs-md lh-md"><?php echo str_replace([' Days', ' Nights', '&amp;'], [' D', ' N', '/'], $product->get_attribute('pa_duration'))  ?></p>
                </div>
                <div class="trip-style">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Trip Style <i class="bi bi-info-circle pdp-trip-styles"></i></p>
                    <p class="fw-medium fs-md lh-md mb-0"><?php echo $product->get_attribute('pa_trip-style') ?></p>
                </div>
                <div class="rider-level">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Rider Level <i class="bi bi-info-circle pdp-rider-level"></i></p>
                    <p class="fw-medium fs-md lh-md"><?php echo $product->get_attribute('pa_rider-level') ?></p>
                </div>
                <div class="hotel-level">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Hotel Level <i class="bi bi-info-circle pdp-hotel-levels"></i></p>
                    <p class="fw-medium fs-md lh-md mb-0"><?php echo $product->get_attribute('pa_hotel-level') ?></p>
                </div>
                <div class="bikes">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Bikes</p>
                    <p class="fw-medium fs-sm lh-sm mb-0">
                        <?php 
                        if ($pdp_bikes) {
                            foreach ($pdp_bikes as $key => $bike) {
                                if ($key < 4) {
                                    setup_postdata($bike);
                                    echo $bike->post_title.'<br>';
                                }
                            }
                        }
                        wp_reset_postdata();
                        ?>
                    </p>
                    <!-- <p class="view-more">
                    <a href="#">+ more</a>
                </p> -->
                    <a class="fs-sm view-details" href="#bikes-guides">View details</a>
                </div>
            </div>
            <?php

            $linked_products  = $product->get_children();
            $child_products   = get_child_products($linked_products);
            $start_price      = '0.00';
            $available_prices = array();

            if( $child_products ){
                foreach( $child_products as $year => $child_product ){
                    ksort( $child_product, 1 );
                    if( $child_product ){
                        foreach( $child_product as $month => $child_product_data ){
                            ksort( $child_product_data, 1 );
                            $current_month = date( 'm', strtotime( date( 'Y-m-d H:i:s' ) ) );
                            $current_year  = date( 'Y', strtotime( date( 'Y-m-d H:i:s' ) ) );

                            // Check for year to skip the trip is in the past.
                            if ( (int) $month < (int) $current_month && ( int ) $year <= (int)  $current_year ) {
                                continue;
                            }

                            if($child_product_data){
                                foreach($child_product_data as $index => $child_product_details){
                                    ksort( $child_product_details, 1 );
                                    $today_date = new DateTime('now');

                                    // 'start_date' => string '11/12/23' d/m/y
                                    $trip_start_date = DateTime::createFromFormat('d/m/y', $child_product_details['start_date']);

                                    // If the date of the trip is today or in the past, skip the trip;
                                    if( $trip_start_date && $trip_start_date <= $today_date ) {

                                        continue;
                                    }

                                    // Store the prices of all available trips.
                                    array_push( $available_prices, $child_product_details['price'] );
                                }
                            }
                        }
                    }
                }
            }

            // If we have any price in the available prices array, take the lowest.
            if( !empty( $available_prices ) ) {
                $start_price = min( $available_prices );
            }

            ?>
            <div class="col-lg-2 pricing">
                <p class="fw-normal fs-sm lh-sm mb-0 text-muted starting-from">Starting from</p>
                <p class="fw-bold fs-xl lh-xl">
                    <span class="amount">
                        <span class="woocommerce-Price-currencySymbol"></span><?php
                            //echo !$product->sale_price ? $product->price : $product->sale_price;
                            
                            if( !empty( $start_price ) ){
                                echo( wc_price( $start_price ) );
                            }
                            ?>
                    </span>
                    <span class="fw-normal fs-sm">per person</span>
                </p>
                <a class="btn btn-primary btn-lg rounded-1 w-100" href="#dates-pricing">See dates</a>
            </div>
        </div>
    </div>

    <hr class="overview-divider m-0">
<?php endif; ?>
<!-- overview bar section end -->

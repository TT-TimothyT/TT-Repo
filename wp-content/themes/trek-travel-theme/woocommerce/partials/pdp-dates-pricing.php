<?php
$product = wc_get_product( get_the_ID() );
$linked_products = $product->get_children();
$get_child_products = get_child_products($linked_products);
$nav_year_tab = $nav_year_tab_content = '';
$trip_style = $product->get_attribute( 'pa_trip-style' );

function getWebDispalyStatus($status){
    $webDispalyArr = [
        "Hold" => "Limited Availability", 
        "Sales Hold" => "Limited Availability",
        "Group Hold" => "Limited Availability",
        "Limited Availability" => "Limited Availability",
        "Sold Out" => "Join Waitlist"
    ];
    if (array_key_exists($status,$webDispalyArr)){
        return $webDispalyArr[$status];
    }
    return $status;
}
$requestTripFormArr = ["Limited Availability" => "book-this-trip", "Join Waitlist" => "book-this-trip"];
$in_status = [
    "Limited Availability",
    "Sold Out",
    "Group Hold",
    "Sales Hold",
    "Hold"
];
$contentFlag = false;
if( $get_child_products ){
    $iter = 1;
    foreach( $get_child_products as $year=>$get_child_product ){
        ksort($get_child_product,1);
        //nav year tabs & button HTML creation
        $nav_year_tab .= '<button class="nav-link '.($iter == 1 ? 'active' : '').'" id="nav-year'.$year.'-tab" data-bs-toggle="tab" data-bs-target="#nav-year'.$year.'" type="button" role="tab" aria-controls="nav-year'.$year.'" aria-selected="true">'.$year.' Tours</button>';
        //nav year tab content HTML creation
        $nav_year_tab_content .= '<div class="tab-pane fade show '.($iter == 1 ? 'active' : '').'" id="nav-year'.$year.'" role="tabpanel" aria-labelledby="nav-year'.$year.'-tab" tabindex="0">';
        // <!-- months nav desktop -->
        $month_nav_desktop_btn_output = $month_nav_mobile_btn_output = $month_content_output =  '';
        if( $get_child_product ){
            $m_iter = 1;
            foreach($get_child_product as $month=>$get_child_product_data){
                $currentMonth = date('m', strtotime(date('Y-m-d H:i:s')));
                if ($month < $currentMonth) {
                    continue;
                }
                $my = $month.$year;
                $monthInfo = trek_get_month_info($month);
                $month_nav_desktop_btn_output .= '<button class="nav-link '.($m_iter == 1 ? 'active' : '').'" id="nav-'.$my.'-tab" data-bs-toggle="tab" data-bs-target="#nav-'.$my.'" type="button" role="tab" aria-controls="nav-'.$my.'" aria-selected="true">'.$monthInfo[$month][0].'</button>';
                $month_nav_mobile_btn_output .= '<option value="nav-'.$my.'-tab">'.$monthInfo[$month][0].'</option>';
                $month_content_output .= '<div class="tab-pane fade show '.($m_iter == 1 ? 'active' : '').'" id="nav-'.$my.'" role="tabpanel" aria-labelledby="nav-'.$my.'-tab" tabindex="0"><div class="accordion accordion-flush" id="accordionFlushExample-'.$my.'">';
                if($get_child_product_data){
                    foreach($get_child_product_data as $child_product_data){
                        $today = date('d', strtotime(date('Y-m-d H:i:s')));
                        $dateParts = explode('/', $child_product_data['start_date']);
                        if (isset($dateParts) && !empty($dateParts)) {
                            $startDay = $dateParts[0];
                            $startMonth = $dateParts[1];
                            if ((int)$today < (int)$startDay) {
                                if ((int)$startMonth < (int)$currentMonth) {
                                    $month_nav_desktop_btn_output = $month_nav_mobile_btn_output = $month_content_output =  '';
                                    continue;
                                }
                            }
                        }
                        $contentFlag = true;
                        $accordina_id = $my.$child_product_data['product_id'];
                        $date_range = $child_product_data['start_date'].' - '.$child_product_data['end_date'];
                        $date_range = $child_product_data['date_range'];
                        $trip_status = $child_product_data['trip_status'];
                        $bike_hotels = tt_get_hotel_bike_list($child_product_data['sku']);
                        $removeFromStella = tt_get_local_trips_detail('removeFromStella', '', $child_product_data['sku'], true);
                        $singleSupplementPrice = isset($child_product_data['singleSupplementPrice']) ? $child_product_data['singleSupplementPrice'] : 0;
                        $singleSupplementPriceCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>'.$singleSupplementPrice.'<span>';
                        // $status_class = strtolower($trip_status);
                        // $status_class = str_ireplace(' ', '-', $status_class);
                        $tripWebStatus = getWebDispalyStatus($trip_status);
                        $tripWebStatusClass = strtolower(str_ireplace(" ","-",getWebDispalyStatus($trip_status)));
                        $month_content_output .= '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                        <h6 class="accordion-header" id="flush-headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-'.$accordina_id.'" aria-expanded="false" aria-controls="flush-collapse-'.$accordina_id.'">
                                <span class="fw-medium w-25 fs-lg lh-lg">'.$date_range.'<!-- January 24-30, 2022 --></span>
                                <span class="fw-normal fs-sm lh-sm '.$tripWebStatusClass.'">'.$tripWebStatus.'</span>
                            </button>
                        </h6>
                        <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                            <hr>
                            <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex">
                                <div class="accordion-hotels">
                                    <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                    '.$bike_hotels['hotels'].'
                                    <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                </div>
                                <div class="accordion-bikes">
                                    <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                    '.$bike_hotels['bikes'].'
                                    <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                    </div>
                                <div class="accordion-book-now">';
                                $formUrl = '';
                                if( in_array($trip_status, $in_status) || $removeFromStella == true ){
                                    $formUrl = "reserve-a-trip";
                                }
                                $cart_result = get_user_meta(get_current_user_id(),'_woocommerce_persistent_cart_' . get_current_blog_id(), true); 
                                $cart = WC()->session->get( 'cart', null );
                                $persistent_cart_count = isset($cart_result['cart']) && $cart_result['cart'] ? count($cart_result['cart']) : 0;
                                if ( !is_null($cart) && $persistent_cart_count > 0 ) {
                                    $button = '<button type="button" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now" id="trip-booking-modal" data-bs-toggle="modal" data-bs-target="#tripBookingModal" data-form-id="'.$accordina_id.'">Book now</button>';
                                }else{
                                    if (isset($formUrl) && !empty($formUrl)) {
                                        $button = '<a href="/'.$formUrl.'?trip='.$product->name.'" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now" target="_blank">Book now</a>';
                                    }else{
                                        $button = '<button type="submit" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now">Book now</button>';
                                    }
                                }
                                
                                    $month_content_output .= '<form class="cart grouped_form" action="'.esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ).'" method="post" enctype="multipart/form-data" target="_blank">
                                    <h5 class="fw-semibold"><span class="amount"><span class="woocommerce-Price-currencySymbol">$</span>'.$child_product_data['price'].' </span> <span class="fw-normal fs-md lh-md">per person</span></h5>
                                    <p class="fw-normal fs-xs lh-xs text-muted">Double Occupancy</p>
                                    '.$button.'                                    
                                    <p class="fw-normal fs-sm lh-sm text-muted">Single Occupancy from: +'.$singleSupplementPriceCurr.' <i class="bi bi-info-circle pdp-single-occupancy"></i></p>
                                    <input type="hidden" name="' . esc_attr( 'quantity[' . $child_product_data['product_id'] . ']' ) . '" value="1" class="wc-grouped-product-add-to-cart-checkbox" />
                                    <input type="hidden" name="add-to-cart" value="'.$child_product_data['product_id'].'" />
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>';
                    }
                }
                $month_content_output .= '</div></div>';

                $m_iter++;
            }
        }
        if ($contentFlag) {
            $month_nav_desktop_output = '<nav class="nav-months-desktop"><div class="nav nav-tabs-months" id="nav-tab-month" role="tablist">';
            $month_nav_desktop_output .= $month_nav_desktop_btn_output;
            $month_nav_desktop_output .='</div></nav>';
            // Months nav mobile
            $month_nav_mobile_output = '<select class="form-select" id="select-month">';
            $month_nav_mobile_output .= $month_nav_mobile_btn_output;
            $month_nav_mobile_output .='</select>';
            
            $nav_year_tab_content .= $month_nav_desktop_output;
            $nav_year_tab_content .= $month_nav_mobile_output;
            
            $nav_year_tab_content .= '<div class="tab-content" id="nav-tabContent-months">';
            $nav_year_tab_content .= $month_content_output;
            $nav_year_tab_content .= '</div>';
            
            $nav_year_tab_content .= '</div>';
            $iter++;
        } else {
            $nav_year_tab = $nav_year_tab_content = '';
        }
    }

}
?>
<a class="pdp-anchor" id="dates-pricing"></a>
<div class="container dates-pricing-container" id="dates-pricing">
<div class="container pdp-section dates-pricing-container">
    <h3 class="fw-semibold">Dates & Pricing</h3>
    <p class="fw-normal fs-xl lh-xl">Book early for the best price</p>
    <div class="trip-status">
        <ul>
            <li class=" guaranteed">
                <p class="fw-normal fs-sm lh-sm">Guaranteed <i class="bi bi-info-circle pdp-guaranteed"></i></p>
            </li>
            <li class=" join-waitlist">
                <p class="fw-normal fs-sm lh-sm">Join Waitlist <i class="bi bi-info-circle pdp-sold-out"></i></p>
            </li>
            <li class=" private">
                <p class="fw-normal fs-sm lh-sm">Private <i class="bi bi-info-circle pdp-private"></i></p>
            </li>
            <li class=" limited-availability">
                <p class="fw-normal fs-sm lh-sm">Limited Availability <i class="bi bi-info-circle pdp-limited-availability"></i></p>
            </li>
        </ul>
    </div>
    <div class="dates-pricing">
        <!-- main nav year/private/custom tour -->
        <nav>
            <div class="nav nav-tabs" id="nav-tab-year" role="tablist">
                <?php
                if ($contentFlag) { echo $nav_year_tab; } 
                ?>
                <!-- <button class="nav-link active" id="nav-year-tab" data-bs-toggle="tab" data-bs-target="#nav-year" type="button" role="tab" aria-controls="nav-year" aria-selected="true">2022 Tours</button> -->
                <?php if (strtolower($trip_style) != "self-guided") { ?>
                    <button class="nav-link fs-lg lh-lg <?php echo $contentFlag ? '' : 'active'; ?>" id="nav-private-tab" data-bs-toggle="tab" data-bs-target="#nav-private" type="button" role="tab" aria-controls="nav-private" aria-selected="false">Private Tour</button>
                    <button class="nav-link fs-lg lh-lg" id="nav-custom-tab" data-bs-toggle="tab" data-bs-target="#nav-custom" type="button" role="tab" aria-controls="nav-custom" aria-selected="false">Custom Tour</button>
                <?php } ?>
                </div>
        </nav>
        <!-- year/private/custom tour tab content -->
        <div class="tab-content" id="nav-tabContent">
            <!-- year tour tab content -->
            <?php if ($contentFlag) { echo $nav_year_tab_content; } ?>
            <?php if (strtolower($trip_style) != "self-guided") { ?>
            <!-- private tour tab content -->
            <div class="tab-pane fade <?php echo $contentFlag ? '' : 'active'; ?>" id="nav-private" role="tabpanel" aria-labelledby="nav-private-tab" tabindex="0">
                <h5 class="fw-semibold">Looking for a Private Tour with us?</h5>
                <p class="fw-normal fs-md lh-md">Private bike tours can range in cost based on your group size. See below for specific pricing based on your group size.</p>
                
                <?php 
                // Get Private Tour Pricing from the ACF Repeater
                $private_tour_price_groups = get_field('private_tour_price_group');
                if ( $private_tour_price_groups ) : ?>
                <dl class="row">
                    <?php foreach( $private_tour_price_groups as $item ): ?>
                    <dt class="col-4 col-md-3"><?php echo $item['private_tour_group_quantity']; ?></dt>
                    <dd class="col-8 col-md-9"><?php echo $item['private_tour_group_price']; ?></dd>
                    <hr>
                    <?php endforeach; ?>
                </dl>
                <?php endif; ?>

                <a href="/ways-to-travel/private?trip=<?php echo $product->name; ?>" target="_blank" class="btn btn-primary btn-md rounded-1 my-4">Book a Private Tour</a>
                <?php if (get_field('private_tour_note_title')): ?>
                    <p class="fw-bold fs-xl lh-xl"><?php the_field('private_tour_note_title')?></p>
                    <p class="fw-normal fs-md lh-md"><?php the_field('private_tour_note_subtitle')?></p>
                    <p class="fw-normal fs-xs lh-xs w-75"><?php the_field('private_tour_note_content')?></p>
                <?php endif;?>
            </div>
            <!-- custom tour tab content -->
            <div class="tab-pane fade" id="nav-custom" role="tabpanel" aria-labelledby="nav-custom-tab" tabindex="0">
                <h5 class="fw-semibold">Looking for a date that you don't see?</h5>
                <p class="fw-normal fs-md lh-md">Look no further. Simply tell us your preferred travel dates and we’ll work together to deliver the same great trip on your custom schedule. Want to make a few changes to your itinerary, no problem. We will work with you to make sure your custom vacation is the ultimate vacation of a lifetime for your group.</p>
                <a href="/trip-styles/custom-bike-tours?trip=<?php echo $product->name; ?>" target="_blank" class="btn btn-primary btn-md rounded-1 my-4">Book a Custom Tour</a>
                <p class="fw-normal fs-xs lh-xs w-75">*Pricing, availability and guest minimums are all subject to change at any time. Certain dates have a minimum number of guests required, please contact us for details. Private pricing is not available on Ride Camp, Race, Special Edition or Cross Country style trips.</p>
            </div>
            <?php } ?>
        </div>
    </div>
    <hr class="pdp-section__divider" >
</div> 

<div class="container">
    <!-- Modal -->
    <div class="modal fade modal-trip-booking-warning" id="tripBookingModal" tabindex="-1" aria-labelledby="tripBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="tripBookingModalLabel">Filters</h5>
                    <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i type="button" class="bi bi-x"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <p class="fw-medium fs-xl lh-xl">You have another booking already in progress</p>
                    <p class="fw-normal fs-md lh-md">Booking a new trip will cancel all of your previous booking progress. Continue your previous booking or proceed with your new booking. </p>
                    <input type="hidden" id="bookId">
                </div>
                <div class="modal-footer">
                    <div class="container">
                        <div class="row align-items-center">                                            
                            <div class="col text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                
                                <button type="button" class="btn btn-primary proceed-booking-btn" data-bs-dismiss="modal">Proceed</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- / .modal-content -->
        </div><!-- / .modal-dialog -->
    </div><!-- / .modal -->
</div> <!-- / Modal .container -->

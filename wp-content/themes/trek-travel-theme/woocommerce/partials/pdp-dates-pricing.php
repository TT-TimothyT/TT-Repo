<?php
$product = wc_get_product( get_the_ID() );
$p_id = $product->get_id();
$linked_products = $product->get_children();
$get_child_products = get_child_products($linked_products);
$nav_year_tab = $nav_year_tab_content = '';
$trip_style = tt_get_custom_product_tax_value( $p_id, 'trip-style', true );

$activity_terms = get_the_terms( $p_id, 'activity' );

foreach ( $activity_terms as $activity_term) {
	$activity = $activity_term->name;   
}

$is_pc_trip = get_field( 'is_private_custom_trip');

// Product Cats
$pcats = array();

$terms = get_the_terms( $p_id, 'product_cat' );
foreach ($terms as $term) {
$product_cat = $term->name;
$pcats[] = $product_cat;
}

// Hide Custom and Private tabs for these trip styles.
$in_trip_style = [
    'self-guided',
    'race',
    'cross country',
    'ride camp'
];

$trip_styles = explode( ', ', strtolower( $trip_style ) );

$is_tabs_visible = true;

foreach ($in_trip_style as $ts) {
    if ( in_array( $ts, $trip_styles ) ) {
        // Need to hide tabs.
        $is_tabs_visible = false;
        break;
    }
}

function getWebDispalyStatus($status){
    $webDispalyArr = [
        "Hold" => "Limited Availability", 
        "Sales Hold" => "Limited Availability",
        "Group Hold" => "Limited Availability",
        "Limited Availability" => "Limited Availability",
        "SOLD OUT" => "Join Waitlist"
    ];
    if (array_key_exists($status,$webDispalyArr)){
        return $webDispalyArr[$status];
    }
    return $status;
}

// $requestTripFormArr = ["Limited Availability" => "book-this-trip", "Join Waitlist" => "book-this-trip"];

$res_status = [
    // "Limited Availability",
    "Group Hold",
    "Sales Hold",
    "Hold"
];

$wait_status = ["SOLD OUT"];

/**
 * Function that sorts two dates, ascending.
 *
 * @param array $a Array with objects, we need ['start_date'] in this format dd/mm/yy.
 * @param array $b Array with objects.
 * @param string $d String with the delimeter.
 */
function date_sort( $a, $b, $d = "/" ) {

    if ($a == $b) {

        return 0;
    } else {

        // Convert into dates and compare.
        list( $ad, $am, $ay ) = explode( $d, $a['start_date'] );

        list( $bd, $bm, $by ) = explode( $d, $b['start_date'] );

        if ( mktime( 0, 0, 0, $am, $ad, $ay ) < mktime( 0, 0, 0, $bm, $bd, $by ) ) {

            return -1;
        } else {

            return 1;
        }
    }
}

$available_child_products = array();


// Sort the trips and store only available trips into a new array.
foreach( $get_child_products as $year => $get_child_product ) {

    // Sort trips by year ascending.
    ksort( $get_child_product, 1 );

    foreach( $get_child_product as $month => $get_child_product_data) {

        // Sort trips by date ascending.
        usort( $get_child_product_data, 'date_sort' );

        foreach( $get_child_product_data as $index => $child_product_data ) {
            $today_date = new DateTime( 'now' );

            // 'start_date' => string '11/12/23' dd/mm/yy.
            $trip_start_date = DateTime::createFromFormat('d/m/y', $child_product_data['start_date']);

            if( $trip_start_date && $trip_start_date > $today_date ) {

                // Check child product is marked as Private/Custom trip.
                $is_private_custom_trip = get_field( 'is_private_custom_trip', $child_product_data['product_id'] );

                // If the child product is marked as a private/custom trip, continue to the next one.
                if( true == $is_private_custom_trip ) {
                    continue;
                }

                if( ! isset( $available_child_products[ $year ] ) ) {
                    // Make a new array for every year.
                    $available_child_products[ $year ] = array();
                }


                if( ! isset( $available_child_products[ $year ][ $month ] ) ) {
                    // Make a new array for every month.
                    $available_child_products[ $year ][ $month ] = array();
                }

                // Store the available trip into the new array.
                array_push( $available_child_products[ $year ][ $month ], $child_product_data );

            }
        }


    }
}


$contentFlag = false;

if( $available_child_products ) {

    ksort($available_child_products);
    
    $iter = 1;
    foreach( $available_child_products as $year=>$get_child_product ){

        //nav year tabs & button HTML creation
        $nav_year_tab .= '<button class="nav-link '.($iter == 1 ? 'active' : '').'" id="nav-year'.$year.'-tab" data-bs-toggle="tab" data-bs-target="#nav-year'.$year.'" type="button" role="tab" aria-controls="nav-year'.$year.'" aria-selected="true"><span>'.$year.' Tours</span></button>';
        //nav year tab content HTML creation
        $nav_year_tab_content .= '<div class="tab-pane fade show '.($iter == 1 ? 'active' : '').'" id="nav-year'.$year.'" role="tabpanel" aria-labelledby="nav-year'.$year.'-tab" tabindex="0">';

        $all_month_content_output = '';

        // <!-- months nav desktop -->
        $month_nav_desktop_btn_output = $month_nav_mobile_btn_output = $month_content_output =  '';
        if( $get_child_product ){
            $m_iter = 1;

            $month_nav_desktop_btn_output .= '<button class="nav-link '.$m_iter.' '.($m_iter == 1 ? 'active' : '').'" id="nav-all-'.$year.'-tab" data-bs-toggle="tab" data-bs-target="#nav-all-'.$year.'" type="button" role="tab" aria-controls="nav-all-'.$year.'" aria-selected="false">All</button>';

            $month_nav_mobile_btn_output .= '<option value="nav-all-'.$year.'">All '.$year.'</option>';

            $all_month_content_output .= '<div class="tab-pane fade show '.($m_iter == 1 ? 'active' : '').'" id="nav-all-'.$year.'" role="tabpanel" aria-labelledby="nav-all-'.$year.'-tab" tabindex="0"><div class="accordion accordion-flush" id="accordionFlushExample-'.$year.'-a">';

            foreach($get_child_product as $month=>$get_child_product_data){
                $m_iter++;

                $my = $month.$year;
                $monthInfo = trek_get_month_info($month);
                
                $month_nav_desktop_btn_output .= '<button class="nav-link '.$m_iter.'  '.($m_iter == 1 ? 'active' : '').'" id="nav-'.$my.'-tab" data-bs-toggle="tab" data-bs-target="#nav-'.$my.'" type="button" role="tab" aria-controls="nav-'.$my.'" aria-selected="true">'.$monthInfo[$month][0].'</button>';

                $month_nav_mobile_btn_output .= '<option value="nav-'.$my.'">'.$monthInfo[$month][0].'</option>';

                $month_content_output .= '<div class="tab-pane fade show '.($m_iter == 1 ? 'active' : '').'" id="nav-'.$my.'" role="tabpanel" aria-labelledby="nav-'.$my.'-tab" tabindex="0"><div class="accordion accordion-flush" id="accordionFlushExample-'.$my.'">';

                if($get_child_product_data){
                    foreach($get_child_product_data as $index => $child_product_data){

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
                        // Check child product is marked as Private/Custom trip.
                        $is_pc_trip = get_field( 'is_private_custom_trip');                        
                        // Check child product is marked as Private/Custom trip.
                        $is_pc_trip = get_field( 'is_private_custom_trip');                   

                        if ($tripWebStatus == 'Private') {
                            if( true == $is_pc_trip ) {
                                $month_content_output .= 
                        '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                            <h6 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-'.$accordina_id.'" aria-expanded="false" aria-controls="flush-collapse-'.$accordina_id.'">
                                <div class="d-box">
                                    <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'</span>
                                    <span class="fw-normal fs-sm lh-sm '.$tripWebStatusClass.'">'.$tripWebStatus.'</span>
                                </div>
                                </button>
                            </h6>
                        <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                            <hr>
                            <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                <div class="accordion-hotels">
                                    <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                    '.$bike_hotels['hotels'].'
                                    <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                    </div>';

                                    if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                        $month_content_output .= 
                                    '<div class="accordion-bikes">
                                        <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                        '.$bike_hotels['bikes'].'
                                        <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                        </div>';
                                    }
                                    $month_content_output .= 
                                    '<div class="accordion-book-now">';

                                $all_month_content_output .= 
                        '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                            <h6 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-'.$accordina_id.'" aria-expanded="false" aria-controls="flush-collapse-'.$accordina_id.'">
                                <div class="d-box">
                                    <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'<!-- January 24-30, 2022 --></span>
                                    <span class="fw-normal fs-sm lh-sm '.$tripWebStatusClass.'">'.$tripWebStatus.'</span>
                                </div>
                                </button>
                            </h6>
                        <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                            <hr>
                            <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                <div class="accordion-hotels">
                                    <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                    '.$bike_hotels['hotels'].'
                                    <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                    </div>';

                                    if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                        $all_month_content_output .= 
                                    '<div class="accordion-bikes">
                                        <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                        '.$bike_hotels['bikes'].'
                                        <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                        </div>';
                                    }
                                    $all_month_content_output .= 
                                    '<div class="accordion-book-now">';

                            } else {

                            if( true == $is_pc_trip ) {
                                $month_content_output .= 
                        '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                            <h6 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-'.$accordina_id.'" aria-expanded="false" aria-controls="flush-collapse-'.$accordina_id.'">
                                <div class="d-box">
                                    <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'</span>
                                    <span class="fw-normal fs-sm lh-sm '.$tripWebStatusClass.'">'.$tripWebStatus.'</span>
                                </div>
                                </button>
                            </h6>
                        <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                            <hr>
                            <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                <div class="accordion-hotels">
                                    <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                    '.$bike_hotels['hotels'].'
                                    <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                    </div>';

                                    if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                        $month_content_output .= 
                                    '<div class="accordion-bikes">
                                        <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                        '.$bike_hotels['bikes'].'
                                        <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                        </div>';
                                    }
                                    $month_content_output .= 
                                    '<div class="accordion-book-now">';

                                $all_month_content_output .= 
                        '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                            <h6 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-'.$accordina_id.'" aria-expanded="false" aria-controls="flush-collapse-'.$accordina_id.'">
                                <div class="d-box">
                                    <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'<!-- January 24-30, 2022 --></span>
                                    <span class="fw-normal fs-sm lh-sm '.$tripWebStatusClass.'">'.$tripWebStatus.'</span>
                                </div>
                                </button>
                            </h6>
                        <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                            <hr>
                            <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                <div class="accordion-hotels">
                                    <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                    '.$bike_hotels['hotels'].'
                                    <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                    </div>';

                                    if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                        $all_month_content_output .= 
                                    '<div class="accordion-bikes">
                                        <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                        '.$bike_hotels['bikes'].'
                                        <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                        </div>';
                                    }
                                    $all_month_content_output .= 
                                    '<div class="accordion-book-now">';

                            } else {

                            $month_content_output .= 
                            '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                                <h6 class="accordion-header" id="flush-headingThree">
                                    <div class="pvt-box">
                                    <div class="d-box">
                                        <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'</span>
                                        <span class="fw-normal fs-sm lh-sm '.$tripWebStatusClass.'">'.$tripWebStatus.'</span>
                                    </div>
                                        <span class="ms-auto fw-medium fs-sm lh-sm d-rsv">Reserved</span>
                                    </div>
                                </h6>
                            <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                                <hr>
                                <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                    <div class="accordion-hotels">
                                        <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                        '.$bike_hotels['hotels'].'
                                        <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                        </div>';

                                        if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                            $month_content_output .= 
                                        '<div class="accordion-bikes">
                                            <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                            '.$bike_hotels['bikes'].'
                                            <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                            </div>';
                                        }
                                        $month_content_output .= 
                                        '<div class="accordion-book-now">';

                                    $all_month_content_output .= 
                            '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                                <h6 class="accordion-header" id="flush-headingThree">
                                    <div class="pvt-box">
                                    <div class="d-box">
                                        <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'</span>
                                        <span class="fw-normal fs-sm lh-sm '.$tripWebStatusClass.'">'.$tripWebStatus.'</span>
                                    </div>
                                        <span class="ms-auto fw-medium fs-sm lh-sm d-rsv">Reserved</span>
                                    </div>
                                </h6>
                            <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                                <hr>
                                <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                    <div class="accordion-hotels">
                                        <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                        '.$bike_hotels['hotels'].'
                                        <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                        </div>';

                                        if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                            $all_month_content_output .= 
                                        '<div class="accordion-bikes">
                                            <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                            '.$bike_hotels['bikes'].'
                                            <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                            </div>';
                                        }
                                        $all_month_content_output .= 
                                        '<div class="accordion-book-now">';
                                }
                                }
                        } else if ($tripWebStatus == 'Not Guaranteed') {
                            $month_content_output .= 
                        '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                            <h6 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-'.$accordina_id.'" aria-expanded="false" aria-controls="flush-collapse-'.$accordina_id.'">
                                    <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'</span>
                                </button>
                            </h6>
                        <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                            <hr>
                            <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                <div class="accordion-hotels">
                                    <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                    '.$bike_hotels['hotels'].'
                                    <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                    </div>';

                                    if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                        $month_content_output .= 
                                    '<div class="accordion-bikes">
                                        <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                        '.$bike_hotels['bikes'].'
                                        <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                        </div>';
                                    }
                                    $month_content_output .= 
                                    '<div class="accordion-book-now">';

                                $all_month_content_output .= 
                        '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                            <h6 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-'.$accordina_id.'" aria-expanded="false" aria-controls="flush-collapse-'.$accordina_id.'">
                                    <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'</span>
                                </button>
                            </h6>
                        <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                            <hr>
                            <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                <div class="accordion-hotels">
                                    <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                    '.$bike_hotels['hotels'].'
                                    <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                    </div>';

                                    if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                        $all_month_content_output .= 
                                    '<div class="accordion-bikes">
                                        <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                        '.$bike_hotels['bikes'].'
                                        <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                        </div>';
                                    }
                                    $all_month_content_output .= 
                                    '<div class="accordion-book-now">';

                        } else {
                        $month_content_output .= 
                        '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                            <h6 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-'.$accordina_id.'" aria-expanded="false" aria-controls="flush-collapse-'.$accordina_id.'">
                                <div class="d-box">
                                    <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'</span>
                                    <span class="fw-normal fs-sm lh-sm '.$tripWebStatusClass.'">'.$tripWebStatus.'</span>
                                </div>
                                </button>
                            </h6>
                        <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                            <hr>
                            <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                <div class="accordion-hotels">
                                    <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                    '.$bike_hotels['hotels'].'
                                    <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                    </div>';

                                    if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                        $month_content_output .= 
                                    '<div class="accordion-bikes">
                                        <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                        '.$bike_hotels['bikes'].'
                                        <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                        </div>';
                                    }
                                    $month_content_output .= 
                                    '<div class="accordion-book-now">';

                                $all_month_content_output .= 
                        '<div class="accordion-item" data-sku="'.$child_product_data['sku'].'" data-stella="'.$removeFromStella.'" data-status="'.$trip_status.'">
                            <h6 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-'.$accordina_id.'" aria-expanded="false" aria-controls="flush-collapse-'.$accordina_id.'">
                                <div class="d-box">
                                    <span class="fw-medium w-40 fs-lg lh-lg">'.$date_range.'<!-- January 24-30, 2022 --></span>
                                    <span class="fw-normal fs-sm lh-sm '.$tripWebStatusClass.'">'.$tripWebStatus.'</span>
                                </div>
                                </button>
                            </h6>
                        <div id="flush-collapse-'.$accordina_id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample-'.$my.'">
                            <hr>
                            <div class="accordion-body '.strtolower($child_product_data['trip_status']).' d-flex justify-content-between">
                                <div class="accordion-hotels">
                                    <p class="fw-medium fs-sm lh-sm">Hotels you`ll stay at on this date:</p>
                                    '.$bike_hotels['hotels'].'
                                    <a class="fs-sm view-details" href="#hotels">View hotels</a>
                                    </div>';

                                    if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING) {
                                        $all_month_content_output .= 
                                    '<div class="accordion-bikes">
                                        <p class="fw-medium fs-sm lh-sm">Available bikes:</p>
                                        '.$bike_hotels['bikes'].'
                                        <a class="fs-sm view-details" href="#bikes-guides">View bikes</a>
                                        </div>';
                                    }
                                    $all_month_content_output .= 
                                    '<div class="accordion-book-now">';
                        }
                                $formUrl = '';
                                if( in_array($trip_status, $res_status) || $removeFromStella == true ){
                                    switch ( $activity ) {
                                        case TT_ACTIVITY_DASHBOARD_NAME_HW:
                                            $formUrl = "reserve-a-trip-hw";
                                            break;
                                        case TT_ACTIVITY_DASHBOARD_NAME_BIKING:
                                        default:
                                            // By default the URL will be this.
                                            $formUrl = "reserve-a-trip";
                                            break;
                                    }
                                }
                                if( in_array($trip_status, $wait_status) ){
                                    $formUrl = "waitlist";
                                }                              
                                
                                $cart_result = get_user_meta(get_current_user_id(),'_woocommerce_persistent_cart_' . get_current_blog_id(), true); 
                                $cart = WC()->session->get( 'cart', null );
                                $persistent_cart_count = isset($cart_result['cart']) && $cart_result['cart'] ? count($cart_result['cart']) : 0;
                                
                                if ( !is_null($cart) && $persistent_cart_count > 0 ) {
                                    if ( isset( $formUrl ) && !empty( $formUrl ) ) {
                                        $button = '<a href="/'.$formUrl.'?tripname='.$product->name.'&tripdate='.$date_range.'" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now">Book now</a>';
                                    } else {
                                        $button = '<button type="button" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now" id="trip-booking-modal" data-bs-toggle="modal" data-bs-target="#tripBookingModal" data-form-id="'.$accordina_id.'" data-return-url="/?trip='.$product->name.'">Book now</button>';
                                    }
                                }else{
                                    if (isset($formUrl) && !empty($formUrl)) {
                                        $button = '<a href="/'.$formUrl.'?tripname='.$product->name.'&tripdate='.$date_range.'" class="btn btn-primary btn-md rounded-1 mb-1 dates-pricing-book-now">Book Now</a>';
                                    }else{
                                        $button = '<button type="submit" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now" data-return-url="/?trip='.$product->name.'">Book now</button>';
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

                    $all_month_content_output .= '<form class="cart grouped_form" action="'.esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ).'" method="post" enctype="multipart/form-data" target="_blank">
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
               
            }
        }
        if ($contentFlag) {
            $month_nav_desktop_output = '<nav class="nav-months-desktop"><div class="nav nav-tabs-months" id="nav-tab-month" role="tablist">';

            

            $month_nav_desktop_output .= $month_nav_desktop_btn_output;
            $month_nav_desktop_output .='</div></nav>';
            // Months nav mobile
            $month_nav_mobile_output = '<form><select class="form-select select-months" id="select-month-'.$year.'">';
            $month_nav_mobile_output .= $month_nav_mobile_btn_output;
            $month_nav_mobile_output .='</select></form>';
            
            $nav_year_tab_content .= $month_nav_desktop_output;
            $nav_year_tab_content .= $month_nav_mobile_output;

            
            
            $nav_year_tab_content .= '<div class="tab-content nav-tabContent-months" id="nav-tabContent-months-'.$year.'">';
                $nav_year_tab_content .= $all_month_content_output;
                    $nav_year_tab_content .= '</div></div>';
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
<!-- <a class="pdp-anchor" id="dates-pricing"></a> -->
<div class="container pdp-section dates-pricing-container" id="dates-pricing">
<!-- <div class="container pdp-section dates-pricing-container"> -->
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
        <?php 
        if ( empty($available_child_products)) { ?>
        <hr class="pdp-section__divider mt-3 mb-3">
        
            <div class="pdp-no-date pt-5">
            <?php
            if (in_array( 'Pro Race Bike Tours', $pcats)) {
            ?>
                <span class="h5 text-center">Place a deposit to hold your spot for next year's <?php echo get_the_title(); ?></span>
                <a href="/pro-race-difference/pro-race-reservations/" class="btn btn-primary" target="_blank">Place Deposit</a>
            <?php } else { ?>
                <span class="h5 text-center">Dates are coming soon for <?php echo get_the_title(); ?>. Please submit your trip inquiry below.</span>
                <a href="/reserve-a-trip?tripname=<?php echo $product->name; ?>&tripdate=To Be Determined" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now" target="_blank">Inquire Now</a>
            <?php } ?>   
            </div>
           <?php
        } else {
        ?>
        <!-- main nav year/private/custom tour -->
        <nav>
            <div class="nav nav-tabs" id="nav-tab-year" role="tablist">
                <?php
                if ($contentFlag) { echo $nav_year_tab; } 
                ?>
                <?php if ( $is_tabs_visible ) { ?>
                    <button class="nav-link fs-lg lh-lg <?php echo $contentFlag ? '' : 'active'; ?>" id="nav-private-tab" data-bs-toggle="tab" data-bs-target="#nav-private" type="button" role="tab" aria-controls="nav-private" aria-selected="false"><span>Private Tour</span></button>
                    <button class="nav-link fs-lg lh-lg" id="nav-custom-tab" data-bs-toggle="tab" data-bs-target="#nav-custom" type="button" role="tab" aria-controls="nav-custom" aria-selected="false"><span>Custom Tour</span></button>
                <?php } ?>
            </div>
        </nav>
        <?php } ?>
        <!-- year/private/custom tour tab content -->
        <div class="tab-content" id="nav-tabContent">
            <!-- year tour tab content -->
            <?php if ($contentFlag) { echo $nav_year_tab_content; } ?>
            <?php if ( $is_tabs_visible && !empty($available_child_products)) { ?>
            <!-- private tour tab content -->
            <div class="tab-pane fade <?php echo $contentFlag ? '' : 'active show'; ?>" id="nav-private" role="tabpanel" aria-labelledby="nav-private-tab" tabindex="0">
                <h5 class="fw-semibold">Looking for a Private Tour with us?</h5>
                <p class="fw-normal fs-md lh-md">Private tours can range in cost based on your group size. See below for specific pricing based on your group size.</p>
                
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
                    <p class="fw-normal fs-xs lh-xs w-75">
                        <?php 
                        $content = get_field('private_tour_note_content');
                        echo !empty($content) ? esc_html($content) : 'Not all scheduled dates are convertible to private groups – a small number of trips and peak season dates may be excluded or may require “buy outs” equivalent to a larger group size. Pricing, availability, and guest minimums are all subject to change at any time.';
                        ?>
                    </p>

                <?php endif;?>
            </div>
            <!-- custom tour tab content -->
            <div class="tab-pane fade" id="nav-custom" role="tabpanel" aria-labelledby="nav-custom-tab" tabindex="0">
                <h5 class="fw-semibold">Looking for a date that you don't see?</h5>
                <p class="fw-normal fs-md lh-md">Look no further. Simply tell us your preferred travel dates and we’ll work together to deliver the same great trip on your custom schedule. Want to make a few changes to your itinerary, no problem. We will work with you to make sure your custom vacation is the ultimate vacation of a lifetime for your group.</p>
                <a href="/trip-styles/custom-bike-tours?trip=<?php echo $product->name; ?>" target="_blank" class="btn btn-primary btn-md rounded-1 my-4">Book a Custom Tour</a>
            </div>
            <?php } ?>
        </div>
    </div>
    <hr class="pdp-section__divider">
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
<?php 
global $woocommerce;
$cartContentsArr = $woocommerce->cart->cart_contents;
$data = $cartContentsArr[array_key_first($cartContentsArr)]['data'];
?>
<script>
    jQuery('form.cart.grouped_form').on('submit', function () {        
        var childSku = jQuery(this).closest(".accordion-item").data("sku")
        dataLayer.push({ 'ecommerce': null });  // Clear the previous ecommerce object.
        dataLayer.push({
            'event':'add_to_cart',
            'ecommerce': {
                'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
                'add': {
                    'products': [{
                        'name': "<?php echo $product->name; ?>", // Please remove special characters
                        'id': '<?php echo $product->id; ?>', // Parent ID
                        'price': jQuery( this ).find("span.amount").data("price"), // per unit price displayed to the user - no format is ####.## (no '$' or ',')
                        'brand': '', //
                        'category': '<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>', // populate with the 'country,continent' separating with a comma
                        'variant': childSku, //this is the SKU of the product
                        'quantity': '1' //the number of products added to the cart
                    }]
                }
            }
        })
    })

    function removeCartAnalytics() {
        dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
        dataLayer.push({
            'event':'remove_from_cart',
            'ecommerce': {
                'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
                'remove': {
                    'products': [{
                    'name': "<?php echo preg_replace('/[^\w\s]/', '', $data->name); ?>", // Please remove special characters
                    'id': '<?php echo $data->id; ?>', // Parent ID
                    'price': '<?php echo number_format((float)$data->price, 2, '.', ''); ?>', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
                    'brand': '', //
                    'category': '', // populate with the 'country,continent' separating with a comma
                    'variant': '<?php echo $data->sku; ?>', //this is the SKU of the product
                    'quantity': '1' //the number of products added to the cart
                    }]
                }
            }
        })
    }

    /**
     * Run AJAX Request on Submit to check if you are not logged in,
     * to store flag in session for later redirect to checkout, after login/register
     */
    jQuery('form.cart.grouped_form').on('submit', function () {
        let action = 'tt_redirect_after_signin_signup_action';
        jQuery.ajax({
            type: 'POST',
            url: trek_JS_obj.ajaxURL,
            data: "action=" + action,
            dataType: 'json'
        });
    })

    document.addEventListener("DOMContentLoaded", function() {
    // Get all select dropdown elements that start with "select-month-"
    var monthSelects = document.querySelectorAll('[id^="select-month-"]');

    // Iterate over each select dropdown
    monthSelects.forEach(function(monthSelect) {
        console.log(monthSelect);

        // Add a change event listener to each select dropdown
        monthSelect.addEventListener('change', function() {
            // Get the value of the selected option
            var selectedMonthId = this.value;
            console.log('selected: ' + selectedMonthId);

            // Assuming each dropdown's corresponding tab content is grouped within a parent
            // with a matching pattern in ID, like "nav-tabContent-months-123" for "select-month-123"
            // First, find this dropdown's corresponding tab content container
            var pattern = this.id.replace('select-month-', ''); // Get the unique part of ID
            var tabContentId = 'nav-tabContent-months-' + pattern;
            var tabContentContainer = document.getElementById(tabContentId);

            if (tabContentContainer) {
                // Hide all tab panes within this specific tab content container
                tabContentContainer.querySelectorAll('.tab-pane').forEach(function(pane) {
                    pane.classList.remove('show', 'active');
                });

                // Show the selected tab pane within this container
                var selectedPane = tabContentContainer.querySelector('#' + selectedMonthId);
                if (selectedPane) {
                    console.log(selectedPane);
                    selectedPane.classList.add('show', 'active');
                    console.log('shown');
                }
            } else {
                console.log("Corresponding tab content container not found for " + this.id);
            }
        });
    });

    if (monthSelects.length === 0) {
        console.log("No dropdown select elements found.");
    }
});

</script>
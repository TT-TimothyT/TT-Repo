<?php
$trek_user_checkout_data =  get_trek_user_checkout_data();
$tt_posted = $trek_user_checkout_data['posted'];
$primary_name = '';
$guests = array();
if ($tt_posted) {
    $primary_name  = $tt_posted['shipping_first_name'] . ' ' . $tt_posted['shipping_last_name'];
    $guests = isset($tt_posted['guests']) ? $tt_posted['guests'] : array();
}
$tripInfo = tt_get_trip_pid_sku_from_cart();
$product = new WC_product($tripInfo['parent_product_id']);
$tripStyle = $product->get_attribute( 'pa_style' );
$tripProductLine = wc_get_product_term_ids( $product->id, 'product_cat' );
$hideJerseyForTrips = [ 710, 744, 712, 713 ];
$hideme = "";

if ( ! empty( $tripProductLine) && is_array( $tripProductLine ) && ! empty( $hideJerseyForTrips ) && is_array( $hideJerseyForTrips ) ) {
	$product_cat_matches = array_intersect( $tripProductLine, $hideJerseyForTrips );
	if ( 0 < count( $product_cat_matches ) && is_array( $product_cat_matches ) ) {
		if ( in_array( 712, $product_cat_matches ) || in_array( 744, $product_cat_matches ) ) {
			$hideme = "d-none";
		} elseif ( in_array( 710, $product_cat_matches ) && in_array( 713, $product_cat_matches ) ) {
			$hideme = "d-none";
		} else {
			$hideme = "none";
		}
	}
}

$singleSupplementPrice = $bikeUpgradePrice = 0;
$bikePriceCurr = $singleSupplementPrice = '';
if ($tripInfo['sku']) {
    $bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', '', $tripInfo['sku'], true);
    $bikePriceCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $bikeUpgradePrice . '</span>';
    $singleSupplementPrice = tt_get_local_trips_detail('singleSupplementPrice', '', $tripInfo['sku'], true);
    $singleSupplementPriceCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $singleSupplementPrice . '</span>';
}
$shipping_name = $primary_name;
$tt_rooms_output = tt_rooms_output($tt_posted, true, true);
$available_bikes = tt_get_local_bike_detail($tripInfo['sku']);
$primary_bikeId = isset($tt_posted['bike_gears']['primary']['bikeId']) ? $tt_posted['bike_gears']['primary']['bikeId'] : '';
$primary_bikeTypeId = isset($tt_posted['bike_gears']['primary']['bikeTypeId']) ? $tt_posted['bike_gears']['primary']['bikeTypeId'] : '';
$primary_bike_size = isset($tt_posted['bike_gears']['primary']['bike_size']) ? $tt_posted['bike_gears']['primary']['bike_size'] : '';
$p_rider_level = isset($tt_posted['bike_gears']['primary']['rider_level']) ? $tt_posted['bike_gears']['primary']['rider_level'] : '';
$p_own_bike = isset($tt_posted['bike_gears']['primary']['own_bike']) ? $tt_posted['bike_gears']['primary']['own_bike'] : '';
$primary_required = "required='required'";
$p_all_hide = $p_own_hide = "style='display:block;'";
if ($p_rider_level == 5) {
    $primary_required = "";
    $p_all_hide = "style='display:none;'";
}
if ($p_own_bike == 'yes') {
    $p_own_hide = "style='display:none;'";
}
?>
<div class="checkout-bikes-section collapse multi-collapse" id="multiCollapseExample2">
    <div class="checkout-bikes">
        <div class="checkout-bikes__hotel-details">
            <p class="fw-medium fs-xl lh-xl">1. Select Room & Occupants</p>
            <div id="tt-room-bikes-selection">
                <?php echo $tt_rooms_output; ?>
            </div>
        </div>
        <div class="checkout-bikes__edit-room-info">
            <button type="button" class="btn btn-md rounded-1 checkout-bikes__edit-room-info-btn" data-bs-toggle="collapse" data-bs-target=".multi-collapse" aria-expanded="true" aria-controls="multiCollapseExample2 multiCollapseExample1">Edit Room Info</button>
        </div>
    </div>
    <hr>
    <p class="fw-medium fs-xl lh-xl">2. Select Bikes & Gear</p>
    <div class="checkout-bikes__primary-guest">
        <p class="fw-medium fs-lg lh-lg">Primary Guest: <?php echo $primary_name; ?></p>
        <div class="checkout-bikes__rider-level">
            <p class="fw-medium fs-md lh-md">Guest Rider Level <i class="bi bi-info-circle pdp-rider-level"></i></p>
            <div class="form-floating mb-4">
                <select name="bike_gears[primary][rider_level]" data-type="tt_rider_level_primary" class="form-select tt_rider_level_select" id="floatingSelect" aria-label="Floating label select example" <?php echo ( $p_rider_level != 5 && $p_own_bike == 'yes' ? '' : $primary_required); ?>>
                    <?php echo tt_items_select_options('syncRiderLevels', isset($tt_posted['bike_gears']['primary']['rider_level']) ? $tt_posted['bike_gears']['primary']['rider_level'] : ''); ?>
                </select>
                <label for="floatingSelect">Rider Level</label>
                <div class="invalid-feedback rider-select">
                    <img class="invalid-icon" />
                    Please select valid Rider Level.
                </div>
            </div>
        </div>
        <div class="checkout-bikes__bike-selection" id="tt_rider_level_primary" <?php echo $p_all_hide; ?>>
            <p class="fw-medium fs-md lh-md">Select Your Bike of Choice</p>
            <div class="form-check">
                <input name="bike_gears[primary][own_bike]" class="form-check-input checkout-bikes__own-bike-check tt_my_own_bike_checkbox" data-type="tt_my_own_bike_primary" type="checkbox" value="yes" id="flexCheckDefault" <?php echo (isset($tt_posted['bike_gears']['primary']['own_bike']) && $tt_posted['bike_gears']['primary']['own_bike'] == 'yes' ? 'checked' : ''); ?>>
                <label class="form-check-label fw-medium fs-md lh-md" for="flexCheckDefault">
                    I am bringing my own bike
                </label>
            </div>
            <div class="container checkout-bikes__grid-list my-5" data-id="tt_my_own_bike_primary" <?php echo $p_own_hide; ?> >

                <div class="checkout-bikes__bike-grid-guests checkout-bikes__bike-grid d-flex flex-column flex-lg-row flex-nowrap flex-lg-wrap">
                    <?php
                    $primary_available_bike_html = '';
                    $bikes_model_id_in           = [];
                    if ( $available_bikes ) {
                        foreach ( $available_bikes as $available_bike ) {
                            $bikeId        = $available_bike['bikeId'];
                            $bikeDescr     = $available_bike['bikeDescr'];
                            $bikeType      = json_decode($available_bike['bikeType'], true);
                            $bikeTypeId    = $bikeType['id'];
                            $bikeModel     = json_decode($available_bike['bikeModel'], true);
                            $bikeModelId   = $bikeModel['id'];
                            $bikeModelName = $bikeModel['name'];
                            if ( ! in_array( $bikeModelId, $bikes_model_id_in ) && $bikeModelId ) {
                                $bikeTypeName = $bikeType['name'];
                                $selected_p_bikeId = ($primary_bikeTypeId == $bikeModelId ? 'checked' : '');
                                $checkedClass = ($primary_bikeTypeId == $bikeModelId ? 'bike-selected' : '');
                                $pcheckedClassIcon = ($primary_bikeTypeId == $bikeModelId ? 'checkout-bikes__selected-bike-icon' : 'checkout-bikes__select-bike-icon');
                                //$bike_post_id = tt_get_postid_by_meta_key_value('netsuite_bike_type_id', $bikeTypeId);
                                $bike_post_name_arr = explode(' ', $bikeDescr);
                                unset($bike_post_name_arr[0]);
                                $bike_post_name = implode(' ', $bike_post_name_arr);
                                $posts = get_posts(
                                    array(
                                        'post_type' => 'bikes',
                                        'title'     => $bikeModelName,
                                    )
                                );
                                if ( ! empty( $posts ) && is_array( $posts ) ) {
                                    $bike_post_id = $posts[0]->ID;
                                    $bike_image_id = get_post_meta( $bike_post_id, 'bike_image', true );   
                                    $bike_image = wp_get_attachment_image_url( $bike_image_id, 'medium' );
                                } else {
                                    $bike_image = get_template_directory_uri() . "/assets/images/bike-placehoder-image.png";
                                }
                                // if( $bike_post_id !== NULL && is_numeric($bike_post_id) ){
                                //     $bike_post_name = get_the_title($bike_post_id);
                                // }
                                $bikeTypeInfo = tt_ns_get_bike_type_info($bikeModelId);
                                $bikeUpgradeHtml = '';
                                if ($bikeTypeInfo && isset($bikeTypeInfo['isBikeUpgrade']) && $bikeTypeInfo['isBikeUpgrade'] == 1) {
                                    $bikeUpgradeHtml .= '<div class="checkout-bikes__price-upgrade d-flex ms-4">
                                        <p class="fw-normal fs-sm lh-sm">Upgrade now </p>
                                        <p class="fw-bold fs-sm lh-sm"> +' . $bikeUpgradePrice . '</p>
                                    </div>';
                                }
                                $primary_available_bike_html .= '<div class="checkout-bikes__bike bike_selectionElement ' . $checkedClass . '" data-selector="tt_bike_selection_primary" data-id="' . $bikeModelId . '" data-guest-id="0">
                                <input name="bike_gears[primary][bikeTypeId]" ' . $selected_p_bikeId . ' type="radio" value="' . $bikeModelId . '" ' . ( $p_rider_level != 5 && $p_own_bike == 'yes' ? '' : $primary_required) . '>
                                        <div class="checkout-bikes__image d-flex justify-content-center align-content-center">
                                            <img src="' . $bike_image . '" alt="' . $bikeDescr . '">
                                            <span class="checkout-bikes__badge checkout-bikes__badge--ebike">' . $bikeTypeName . '</span>
                                        </div>
                                        <div class="checkout-bikes__title d-flex justify-content-around">
                                            <p class="fw-medium fs-lg lh-lg">' . $bikeModelName . '</p>
                                        <span class="radio-selection ' . $pcheckedClassIcon . '"></span>
                                    </div>
                                    ' . $bikeUpgradeHtml . '
                                </div>';
                            }
                            $bikes_model_id_in[] = $bikeModelId;
                        }
                        $primary_available_bike_html .= '<input name="bike_gears[primary][bikeId]" type="hidden" value="' . $bikeId . '" ' . ( $p_rider_level != 5 && $p_own_bike == 'yes' ? '' : $primary_required) . '>';
                    } else {
                        $primary_available_bike_html .= '<strong>No bikes available!</strong>';
                    }
                    echo $primary_available_bike_html;
                    ?>
                </div>
            </div>
            <div class="container checkout-bikes__additional-bike-info my-4">
                <p class="fw-medium fs-md lh-md">Additional Bike & Gear Information</p>
                <div class="form-floating checkout-bikes__bike-size" data-id="tt_my_own_bike_primary" <?php echo $p_own_hide; ?> >
                    <select name="bike_gears[primary][bike_size]" class="form-select tt_bike_size_change" data-guest-index="0" id="floatingSelect1" aria-label="Floating label select example" <?php echo ( $p_rider_level != 5 && $p_own_bike == 'yes' ? '' : $primary_required) ?>>
                        <?php
                        $bikeOpt_object = tt_get_bikes_by_trip_info($tripInfo['ns_trip_Id'], $tripInfo['sku'], $primary_bikeTypeId, $primary_bike_size, isset($tt_posted['bike_gears']['primary']['bike_size']) ? $tt_posted['bike_gears']['primary']['bike_size'] : '');
                        if ($bikeOpt_object && $bikeOpt_object['size_opts']) {
                            echo $bikeOpt_object['size_opts'];
                        }
                        ?>
                    </select>
                    <label for="floatingSelect">Bike size</label>
                </div>
                
                <div class="form-floating checkout-bikes__bike-size" data-id="tt_my_own_bike_primary" <?php echo $p_own_hide; ?> >
                    <select <?php echo ( $p_rider_level != 5 && $p_own_bike == 'yes' ? '' : $primary_required) ?> name="bike_gears[primary][rider_height]" class="form-select" id="floatingSelect1" aria-label="Floating label select example">
                        <?php echo tt_items_select_options('syncHeights', isset($tt_posted['bike_gears']['primary']['rider_height']) ? $tt_posted['bike_gears']['primary']['rider_height'] : ''); ?>
                    </select>
                    <label for="floatingSelect">Rider Height</label>
                </div>
                <div class="form-floating checkout-bikes__bike-size">
                    <select <?php echo ( $p_rider_level != 5 && $p_own_bike == 'yes' ? '' : $primary_required) ?> name="bike_gears[primary][bike_pedal]" class="form-select" id="floatingSelect1" aria-label="Floating label select example">
                        <?php echo tt_items_select_options('syncPedals', isset($tt_posted['bike_gears']['primary']['bike_pedal']) ? $tt_posted['bike_gears']['primary']['bike_pedal'] : ''); ?>
                    </select>
                    <label for="floatingSelect">Select Pedals</label>
                </div>
                <div class="form-floating checkout-bikes__bike-size">
                    <select name="bike_gears[primary][helmet_size]" class="form-select" id="floatingSelect1" aria-label="Floating label select example">
                        <?php echo tt_items_select_options('syncHelmets', isset($tt_posted['bike_gears']['primary']['helmet_size']) ? $tt_posted['bike_gears']['primary']['helmet_size'] : ''); ?>
                    </select>
                    <label for="floatingSelect">Helmet Size</label>
                </div>
                <div class="form-floating checkout-bikes__bike-size <?php echo $hideme; ?>">
                    <select <?php echo ( $p_rider_level != 5 && $p_own_bike == 'yes' ? '' : $primary_required) ?> name="bike_gears[primary][jersey_style]" class="form-select tt_jersey_style_change" id="floatingSelect1" data-guest-index="0">
                        <?php
                            $clothing_style = isset($tt_posted['bike_gears']['primary']['jersey_style']) ? $tt_posted['bike_gears']['primary']['jersey_style'] : '';
                        ?>
                        <option value="">Select Clothing Style</option>
						<?php if ( 'd-none' === $hideme ) : ?>
							<option selected value="none">None</option>
						<?php endif; ?>
                        <option value="men" <?php echo ( $clothing_style == 'men' ? 'selected' : '' ); ?>>Men's</option>
                        <option value="women" <?php echo ( $clothing_style == 'women' ? 'selected' : '' ); ?>>Women's</option>
                    </select>
                    <label for="floatingSelect">Jersey Style</label>
                </div>
                <div class="form-floating checkout-bikes__bike-size <?php echo $hideme; ?>">
                    <select <?php echo ( $p_rider_level != 5 && $p_own_bike == 'yes' ? '' : $primary_required) ?> name="bike_gears[primary][jersey_size]" class="form-select" id="floatingSelect1" aria-label="Floating label select example">
					<?php if ( 'd-none' === $hideme ) : ?>
						<option selected value="none">None</option>
					<?php endif; ?>
					<?php
                        $clothing_size = isset($tt_posted['bike_gears']['primary']['jersey_size']) ? $tt_posted['bike_gears']['primary']['jersey_size'] : '';
                        echo tt_get_jersey_sizes($clothing_style, $clothing_size); ?>
                    </select>
                    <label for="floatingSelect">Jersey Size</label>
                </div>
            </div>

            <div class="container checkout-bikes__save-preferences my-4 mx-0">
                <div class="form-check">
                    <input name="bike_gears[primary][save_preferences]" class="form-check-input checkout-bikes__own-bike-check" type="checkbox" value="yes" id="flexCheckDefault" <?php echo (isset($tt_posted['bike_gears']['primary']['save_preferences']) && $tt_posted['bike_gears']['primary']['save_preferences'] == 'yes' ? 'checked' : ''); ?>>
                    <label class="form-check-label fw-medium fs-md lh-md" for="flexCheckDefault">
                        Save my bike & gear preferences for future use. This will override your existing preferences saved on your profile.
                    </label>
                </div>
            </div>
        </div>
    </div> <!-- primary guest -->
    <hr class="invisible">
    <div class="checkout-bikes__other-guest">
        <?php if ($guests) {
            foreach ($guests as $guest_num => $guest) {
                $guest_bikeId = isset($tt_posted['bike_gears']['guests'][$guest_num]['bikeId']) ? $tt_posted['bike_gears']['guests'][$guest_num]['bikeId'] : '';
                $guest_bikeTypeId = isset($tt_posted['bike_gears']['guests'][$guest_num]['bikeTypeId']) ? $tt_posted['bike_gears']['guests'][$guest_num]['bikeTypeId'] : '';
                $g_rider_level = isset($tt_posted['bike_gears']['guests'][$guest_num]['rider_level']) ? $tt_posted['bike_gears']['guests'][$guest_num]['rider_level'] : '';
                $g_own_bike = isset($tt_posted['bike_gears']['guests'][$guest_num]['own_bike']) ? $tt_posted['bike_gears']['guests'][$guest_num]['own_bike'] : '';
                $guest_required = "required='required'";
                $g_all_hide = $g_own_hide = "style='display:block;'";
                if ($g_rider_level == 5) {
                    $guest_required = "";
                    $g_all_hide = "style='display:none;'";
                }
                if ($g_own_bike == 'yes') {
                    $g_own_hide = "style='display:none;'";
                }
        ?>
                <p class="fw-medium fs-lg lh-lg">Guest <?php echo $guest_num + 1 . ': ' . $guest['guest_fname'] . ' ' . $guest['guest_lname']; ?></p>
                <div class="checkout-bikes__rider-level">
                    <p class="fw-medium fs-md lh-md">Guest Rider Level <i class="bi bi-info-circle pdp-rider-level"></i></p>
                    <div class="form-floating">
                        <select <?php echo ( $g_rider_level != 5 && $g_own_bike == 'yes' ? '' : $guest_required); ?> name="bike_gears[guests][<?php echo $guest_num; ?>][rider_level]" data-type="tt_rider_level_guest_<?php echo $guest_num; ?>" class="form-select tt_rider_level_select" id="floatingSelect" aria-label="Floating label select example">
                            <?php echo tt_items_select_options('syncRiderLevels', isset($tt_posted['bike_gears']['guests'][$guest_num]['rider_level']) ? $tt_posted['bike_gears']['guests'][$guest_num]['rider_level'] : ''); ?>
                        </select>
                        <label for="floatingSelect">Rider Level</label>
                        <div class="invalid-feedback rider-select">
                            <img class="invalid-icon" />
                            Please select valid Rider Level.
                        </div>
                    </div>
                </div>
                <div class="checkout-bikes__bike-selection" id="tt_rider_level_guest_<?php echo $guest_num; ?>" <?php echo $g_all_hide; ?>>
                    <p class="fw-medium fs-md lh-md">Select Your Bike of Choice</p>
                    <div class="form-check">
                        <input class="form-check-input checkout-bikes__own-bike-check tt_my_own_bike_checkbox" name="bike_gears[guests][<?php echo $guest_num; ?>][own_bike]" data-type="tt_my_own_bike_guest_<?php echo $guest_num; ?>" type="checkbox" value="yes" id="flexCheckDefault" <?php echo $g_own_bike == "yes" ? "checked" : ""; ?>>
                        <label class="form-check-label fw-medium fs-md lh-md" for="flexCheckDefault">
                            I am bringing my own bike
                        </label>
                    </div>
                    <div class="container checkout-bikes__grid-list my-5" data-id="tt_my_own_bike_guest_<?php echo $guest_num; ?>" <?php echo $g_own_hide; ?>>
                        <div class="checkout-bikes__bike-grid-guests checkout-bikes__bike-grid d-flex flex-column flex-lg-row flex-nowrap flex-lg-wrap">
                            <?php
                            $guest_available_bike_html = '';
                            $bikes_model_id_in         = [];
                            if ( $available_bikes ) {
                                foreach ( $available_bikes as $available_bike ) {
                                    $bikeId            = $available_bike['bikeId'];
                                    $bikeDescr         = $available_bike['bikeDescr'];
                                    $bikeType          = json_decode($available_bike['bikeType'], true);
                                    $bikeTypeId        = $bikeType['id'];
                                    $bikeModel         = json_decode($available_bike['bikeModel'], true);
                                    $bikeModelId       = $bikeModel['id'];
                                    $bikeModelName     = $bikeModel['name'];
                                    $selected_g_bikeId = ( $bikeModelId == $guest_bikeTypeId ? 'checked' : '' );
                                    $checkedClass      = ( $bikeModelId == $guest_bikeTypeId ? 'bike-selected' : '' );
                                    $gcheckedClassIcon = ( $bikeModelId == $guest_bikeTypeId ? 'checkout-bikes__selected-bike-icon' : 'checkout-bikes__select-bike-icon' );
                                    if ( ! in_array( $bikeModelId, $bikes_model_id_in ) && $bikeModelId ) {
                                        $bikeTypeName = $bikeType['name'];
                                        //$bike_post_id = tt_get_postid_by_meta_key_value('netsuite_bike_type_id', $bikeTypeId);
                                        $bike_post_name = $bikeDescr;
                                        $bike_post_name_arr = explode(' ', $bike_post_name);
                                        unset($bike_post_name_arr[0]);
                                        $bike_post_name = implode(' ', $bike_post_name_arr);
                                        $posts = get_posts(
                                            array(
                                                'post_type' => 'bikes',
                                                'title'     => $bikeModelName,
                                            )
                                        );
                                        if ( ! empty( $posts ) && is_array( $posts ) ) {
                                            $bike_post_id = $posts[0]->ID;
                                            $bike_image_id = get_post_meta( $bike_post_id, 'bike_image', true );   
                                            $bike_image = wp_get_attachment_image_url( $bike_image_id, 'medium' );
                                        } else {
                                            $bike_image = get_template_directory_uri() . "/assets/images/bike-placehoder-image.png";
                                        }
                                        // if( $bike_post_id !== NULL && is_numeric($bike_post_id) ){
                                        //     $bike_post_name = get_the_title($bike_post_id);
                                        // }
                                        $bikeTypeInfo = tt_ns_get_bike_type_info($bikeTypeId);
                                        $bikeUpgradeHtml = '';
                                        if ($bikeTypeInfo && isset($bikeTypeInfo['isBikeUpgrade']) && $bikeTypeInfo['isBikeUpgrade'] == 1) {
                                            $bikeUpgradeHtml .= '<div class="checkout-bikes__price-upgrade d-flex ms-4">
                                                <p class="fw-normal fs-sm lh-sm">Upgrade now </p>
                                                <p class="fw-bold fs-sm lh-sm"> +' . $bikeUpgradePrice . '</p>
                                            </div>';
                                        }
                                        $guest_available_bike_html .= '<div class="checkout-bikes__bike bike_selectionElement ' . $checkedClass . ' tt_bike_selection_guest_' . $guest_num . '" data-selector="tt_bike_selection_guest_' . $guest_num . '" data-id="' . $bikeModelId . '" data-guest-id="' . $guest_num . '">
                                        <input id="tt_bike_selection_guest_' . $guest_num . $bikeModelId . '" name="bike_gears[guests][' . $guest_num . '][bikeTypeId]" ' . $selected_g_bikeId . ' type="radio" value="' . $bikeModelId . '" '.( $g_rider_level != 5 && $g_own_bike == 'yes' ? '' : $guest_required).'>
                                        <label for="tt_bike_selection_guest_' . $guest_num . $bikeModelId . '">
                                            <div class="checkout-bikes__image d-flex justify-content-center align-content-center">
                                                <img src="' . $bike_image . '" alt="' . $bikeDescr . '">
                                                <span class="checkout-bikes__badge checkout-bikes__badge--ebike">' . $bikeTypeName . '</span>
                                            </div>
                                            <div class="checkout-bikes__title d-flex justify-content-around">
                                                <p class="fw-medium fs-lg lh-lg">' . $bikeModelName . '</p>
                                                <div class="radio-selection ' . $gcheckedClassIcon . '"></div>
                                            </div>
                                            ' . $bikeUpgradeHtml . '
                                            </label>
                                        </div>';
                                    }
                                    $bikes_model_id_in[] = $bikeModelId;
                                }
                                $guest_available_bike_html .= '<input name="bike_gears[guests][' . $guest_num . '][bikeId]" type="hidden" value="' . $guest_bikeId . '" '.( $g_rider_level != 5 && $g_own_bike == 'yes' ? '' : $guest_required).'>';
                            } else {
                                $guest_available_bike_html .= '<strong>No bikes available!</strong>';
                            }
                            echo $guest_available_bike_html;
                            ?>
                        </div>
                    </div>
                    <div class="container checkout-bikes__additional-bike-info my-4">
                        <p class="fw-medium fs-md lh-md">Additional Bike & Gear Information</p>
                        <div class="form-floating checkout-bikes__bike-size" data-id="tt_my_own_bike_guest_<?php echo $guest_num; ?>" <?php echo $g_own_hide; ?>>
                            <select <?php echo ( $g_rider_level != 5 && $g_own_bike == 'yes' ? '' : $guest_required); ?> name="bike_gears[guests][<?php echo $guest_num; ?>][bike_size]" class="form-select tt_bike_size_change" id="floatingSelect1" aria-label="Floating label select example" data-guest-index="<?php echo $guest_num; ?>">
                                <?php
                                $guest_bike_size = isset($tt_posted['bike_gears']['guests'][$guest_num]['bike_size']) ? $tt_posted['bike_gears']['guests'][$guest_num]['bike_size'] : '';
                                $bikeOpt_object = tt_get_bikes_by_trip_info($tripInfo['ns_trip_Id'], $tripInfo['sku'], $guest_bikeTypeId, $guest_bike_size, '');
                                if ($bikeOpt_object && $bikeOpt_object['size_opts']) {
                                    echo $bikeOpt_object['size_opts'];
                                }
                                ?>
                            </select>
                            <label for="floatingSelect">Bike size</label>
                        </div>
                        
                        <div class="form-floating checkout-bikes__bike-size" data-id="tt_my_own_bike_guest_<?php echo $guest_num; ?>" <?php echo $g_own_hide; ?>>
                            <select <?php echo ( $g_rider_level != 5 && $g_own_bike == 'yes' ? '' : $guest_required); ?> name="bike_gears[guests][<?php echo $guest_num; ?>][rider_height]" class="form-select" id="floatingSelect1" aria-label="Floating label select example">
                                <?php echo tt_items_select_options('syncHeights', isset($tt_posted['bike_gears']['guests'][$guest_num]['rider_height']) ? $tt_posted['bike_gears']['guests'][$guest_num]['rider_height'] : ''); ?>
                            </select>
                            <label for="floatingSelect">Rider Height</label>
                        </div>
                        <div class="form-floating checkout-bikes__bike-size">
                            <select <?php echo ( $g_rider_level != 5 && $g_own_bike == 'yes' ? '' : $guest_required); ?> name="bike_gears[guests][<?php echo $guest_num; ?>][bike_pedal]" class="form-select" id="floatingSelect1" aria-label="Floating label select example">
                                <?php echo tt_items_select_options('syncPedals', isset($tt_posted['bike_gears']['guests'][$guest_num]['bike_pedal']) ? $tt_posted['bike_gears']['guests'][$guest_num]['bike_pedal'] : ''); ?>
                            </select>
                            <label for="floatingSelect">Select Pedals</label>
                        </div>
                        <div class="form-floating checkout-bikes__bike-size">
                            <select  name="bike_gears[guests][<?php echo $guest_num; ?>][helmet_size]" class="form-select" id="floatingSelect1" aria-label="Floating label select example">
                                <?php echo tt_items_select_options('syncHelmets', isset($tt_posted['bike_gears']['guests'][$guest_num]['helmet_size']) ? $tt_posted['bike_gears']['guests'][$guest_num]['helmet_size'] : ''); ?>
                            </select>
                            <label for="floatingSelect">Helmet Size</label>
                        </div>
                        <div class="form-floating checkout-bikes__bike-size <?php echo $hideme; ?>">
                            <select <?php echo ( $g_rider_level != 5 && $g_own_bike == 'yes' ? '' : $guest_required); ?> name="bike_gears[guests][<?php echo $guest_num; ?>][jersey_style]" class="form-select tt_jersey_style_change" id="floatingSelect1" aria-label="Floating label select example" data-guest-index="<?php echo $guest_num; ?>">
                                <?php if ( 'd-none' === $hideme ) : ?>
					            	<option selected value="none">None</option>
					            <?php endif; ?>
                                <?php 
                                    $g_clothing_style = isset($tt_posted['bike_gears']['guests'][$guest_num]['jersey_style']) ? $tt_posted['bike_gears']['guests'][$guest_num]['jersey_style'] : '';
                                ?>
                                <option value="">Select Clothing Style</option>
                                <option value="men" <?php echo ( $g_clothing_style == 'men' ? 'selected' : '' ); ?>>Men's</option>
                                <option value="women" <?php echo ( $g_clothing_style == 'women' ? 'selected' : '' ); ?>>Women's</option>
                            </select>
                            <label for="floatingSelect">Jersey Style</label>
                        </div>
                        <div class="form-floating checkout-bikes__bike-size <?php echo $hideme; ?>">
                            <select <?php echo ( $g_rider_level != 5 && $g_own_bike == 'yes' ? '' : $guest_required); ?> name="bike_gears[guests][<?php echo $guest_num; ?>][jersey_size]" class="form-select" id="floatingSelect1" aria-label="Floating label select example">
                                <?php if ( 'd-none' === $hideme ) : ?>
					            	<option selected value="none">None</option>
					            <?php endif; ?>
                                <?php 
                                    $g_clothing_size = isset($tt_posted['bike_gears']['guests'][$guest_num]['jersey_size']) ? $tt_posted['bike_gears']['guests'][$guest_num]['jersey_size'] : '';
                                    echo tt_get_jersey_sizes($g_clothing_style, $g_clothing_size); 
                                ?>
                            </select>
                            <label for="floatingSelect">Jersey Size</label>
                        </div>
                    </div>
                </div>
        <?php
            }
        } ?>
    </div> <!-- other guest -->
    <hr>
    <div class="container checkout-bikes__footer-step-btn d-flex justify-content-between mb-5">
        <button type="button" class="btn border-1 border-dark btn-lg rounded-1 btn-previous" data-bs-toggle="collapse" data-bs-target=".multi-collapse" aria-expanded="false" aria-controls="multiCollapseExample1 multiCollapseExample2">Go back</button>
        <button type="button" class="btn btn-primary btn-lg rounded-1 btn-next">Next: Payment</button>
    </div>
</div>
<div class="container">
    <!-- Modal -->
    <div class="modal fade modal-rider-level-warning" id="checkoutRiderLevelModal" tabindex="-1" aria-labelledby="tripBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">                    
                    <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i type="button" class="bi bi-x"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <p class="fw-medium fs-xl lh-xl">Did you know?</p>
                    <p class="fw-normal fs-md lh-md">Did you know this is a Level <span id="rider_level_text"></span> trip, which may not be recommended for the rider level you selected. We want to ensure this is the right trip for you. <a href="/rider-levels">Learn more</a> about rider levels or <a href="/contact-us">contact a trip consultant</a> for any questions!</p>
                </div>
                <div class="modal-footer">
                    <div class="container">
                        <div class="row align-items-center">                                            
                            <div class="col text-end">                                             
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Proceed</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- / .modal-content -->
        </div><!-- / .modal-dialog -->
    </div><!-- / .modal -->
</div> <!-- / Modal .container -->
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        const ownBikeCheckbox = document.querySelector('.tt_my_own_bike_checkbox');
        const bikeUpgradeContainers = document.querySelectorAll('.checkout-bikes__price-upgrade');
        const bikeElements = document.querySelectorAll('.checkout-bikes__bike');

        ownBikeCheckbox.addEventListener('change', function() {
            const displayValue = this.checked ? 'none' : 'block';
            bikeUpgradeContainers.forEach(upgradeContainer => {
                upgradeContainer.style.display = displayValue;
            });
        });

        bikeElements.forEach(bikeElement => {
            bikeElement.addEventListener('click', function() {
                this.classList.toggle('bike-selected');
            });
        });
    });
</script>

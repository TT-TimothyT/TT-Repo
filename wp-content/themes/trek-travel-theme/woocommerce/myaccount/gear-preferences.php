<?php
$userInfo      = wp_get_current_user();
$bike_type     = get_user_meta($userInfo->ID, 'gear_preferences_bike_type', true);
$rider_height  = get_user_meta($userInfo->ID, 'gear_preferences_rider_height', true);
$select_pedals = get_user_meta($userInfo->ID, 'gear_preferences_select_pedals', true);
$helmet_size   = get_user_meta($userInfo->ID, 'gear_preferences_helmet_size', true);
$jersey_style  = get_user_meta($userInfo->ID, 'gear_preferences_jersey_style', true);
$jersey_size   = get_user_meta($userInfo->ID, 'gear_preferences_jersey_size', true);
$saddle_height = get_user_meta($userInfo->ID, 'gear_preferences_saddle_height', true);
$bar_reach     = get_user_meta($userInfo->ID, 'gear_preferences_barreachfromsaddle', true);
$bar_height    = get_user_meta($userInfo->ID, 'gear_preferences_barheightfromwheel', true);
?>
<div class="container gear-preferences px-0 my-4">
    <div class="row mx-0 flex-column flex-lg-row">
        <div class="col-lg-6 medical-information__back order-1 order-lg-0">
            <a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
        </div>
        <div class="col-lg-6 d-flex dashboard__log">
            <p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
            <a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
        </div>
    </div>
    <div id="bike-gear-preferences-responses"></div>
    <div class="row mx-0">
        <div class="col-lg-12">
            <h3 class="dashboard__title fw-semibold">Bike & Gear Preferences</h3>
        </div>
    </div>
    <div class="row mx-0">
        <div class="col-12 px-0">
            <form name="trek-bike-gear-preferences" method="post">
                <div class="gear-preferences__card rounded-1">
                    <div class="row mx-0 primary-form-row">
                        <div class="col-md px-3">
                            <div class="form-floating">
                                <select name="gear_preferences_bike_type" id="gear_preferences_bike-type" class="form-select" autocomplete="address-level1" data-input-classes="" data-label="Bike Type" tabindex="-1" aria-hidden="true">
                                    <?php echo tt_items_select_options('syncBikeTypes', $bike_type); ?>
                                </select>
                                <label for="gear_preferences_bike-type">Bike Type</label>
                            </div>
                        </div>
                        <div class="col-md px-3">
                            <div class="form-floating">
                                <select name="gear_preferences_rider_height" id="gear_preferences_rider-height" class="form-select" autocomplete="address-level1" data-input-classes="" data-label="Rider Height" tabindex="-1" aria-hidden="true">
                                    <?php echo tt_items_select_options('syncHeights', $rider_height); ?>
                                </select>
                                <label for="gear_preferences_rider-height">Rider Height</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mx-0 primary-form-row">
                        <div class="col-md px-3">
                            <div class="form-floating">
                                <select name="gear_preferences_select_pedals" id="gear_preferences_select-pedals" class="form-select" autocomplete="address-level1" data-input-classes="" data-label="Select Pedals" tabindex="-1" aria-hidden="true">
                                    <?php echo tt_items_select_options('syncPedals', $select_pedals); ?>
                                </select>
                                <label for="gear_preferences_select-pedals">Select Pedals</label>
                            </div>
                        </div>
                        <div class="col-md px-3">
                            <div class="form-floating">
                                <select name="gear_preferences_helmet_size" id="gear_preferences_helmet-size" class="form-select" autocomplete="address-level1" data-input-classes="" data-label="Helmet Size" tabindex="-1" aria-hidden="true">
                                    <?php echo tt_items_select_options('syncHelmets', $helmet_size); ?>
                                </select>
                                <label for="gear_preferences_helmet-size">Helmet Size</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mx-0 primary-form-row">
                        <div class="col-md px-3">
                            <div class="form-floating">
                                <select name="gear_preferences_jersey_style" id="gear_preferences_jersey-style" class="form-select tt_jersey_style_change" autocomplete="address-level1" data-input-classes="" data-label="Jersey Style" tabindex="-1" aria-hidden="true" data-guest-index="01">
                                    <option value="">Select Clothing Style</option>
                                    <option value="men" <?php echo ($jersey_style == 'men' ? 'selected' : ''); ?>>Men's</option>
                                    <option value="women" <?php echo ($jersey_style == 'women' ? 'selected' : ''); ?>>Women's</option>
                                </select>
                                <label for="gear_preferences_jersey-style">Jersey Style</label>
                            </div>
                        </div>
                        <div class="col-md px-3">
                            <div class="form-floating">
                                <select name="gear_preferences_jersey_size" id="gear_preferences_jersey-size" class="form-select" autocomplete="address-level1" data-input-classes="" data-label="Jersey Size" tabindex="-1" aria-hidden="true">
                                    <?php echo tt_get_jersey_sizes($jersey_style, $jersey_size); ?>
                                </select>
                                <label for="gear_preferences_jersey-size">Jersey Size</label>
                            </div>
                        </div>
                    </div>
                    <div class="gear-preferences__button d-flex align-items-lg-center">
                        <div class="d-flex align-items-center gear-preferences__flex">
                            <div class="disclaimer-text">
                                <p>Updating these preferences will help you save time at check-out. These preferences will not update your bike and gear selections for any upcoming trips. To update your bike and gear selections for upcoming trips please visit <a href="<?php echo home_url( '/my-account/my-trips/' ); ?>">upcoming trips</a> in your dashboard or call us at <a href="tel:8664648735"> 866-464-8735 </a></p>
                            </div>
                            <button type="submit" class="btn btn-lg btn-primary fs-md lh-md gear-preferences__save">Save</button>
                            <a href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>" class="gear-preferences__cancel">Cancel</a>
                        </div>
                    </div>
                </div>
                <?php wp_nonce_field('edit_bike_gear_form_action', 'edit_bike_gear_info_nonce'); ?>
            </form>
        </div>
    </div>
</div>
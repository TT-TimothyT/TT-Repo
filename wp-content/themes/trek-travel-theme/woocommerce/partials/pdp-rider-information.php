<?php
// PDP - Rider Information

global $post;
$product_id = $post->ID;

$activity_terms = get_the_terms( $product_id, 'activity' );

foreach ( $activity_terms as $activity_term) {
	$activity = $activity_term->name;   
}

$activity_level = tt_get_custom_product_tax_value( $product_id, 'activity-level', true );

if(!empty($activity_level)) {
?>
<div class="container pdp-section <?php if (!empty($activity) && $activity != TT_ACTIVITY_DASHBOARD_NAME_BIKING):?>hw<?php endif;?>" id="rider-information">
    <div class="row">
        <div class="col-12">

            <h5 class="fw-semibold pdp-section__title">Activity Information</h5>
                 
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-rider-tab" data-bs-toggle="tab" data-bs-target="#nav-rider" type="button" role="tab" aria-controls="nav-rider" aria-selected="true">
                    <?php
                    switch ($activity) {
                        case TT_ACTIVITY_DASHBOARD_NAME_BIKING:
                            echo "Riders";
                            break;
                        default:
                            echo "Hiking & Walking";
                            break;
                      }
                ?>
                    </button>
                    <button class="nav-link" id="nav-non-rider-tab" data-bs-toggle="tab" data-bs-target="#nav-non-rider" type="button" role="tab" aria-controls="nav-non-rider" aria-selected="false">
                    <?php
                        switch ($activity) {
                            case TT_ACTIVITY_DASHBOARD_NAME_BIKING:
                                echo "Non-Riders";
                                break;
                            default:
                                echo "Other Activities";
                                break;
                        }
                    ?>
                    </button>
                    <button class="nav-link" id="nav-support-tab" data-bs-toggle="tab" data-bs-target="#nav-support" type="button" role="tab" aria-controls="nav-support" aria-selected="false">Support</button>
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-rider" role="tabpanel" aria-labelledby="nav-rider-tab">
                    <?php $rider = get_field('rider'); ?>
                    <p class="rider-main-heading fw-bold">Activity Level: <?php echo $activity_level ? esc_html( $activity_level ) : ''; ?> <i class="bi bi-info-circle pdp-rider-level"></i></p>
                    <p class="rider-sub-heading fw-medium">Terrain: <?php echo isset($rider['terrain_title']) ? $rider['terrain_title'] : ''; ?></p>
                    <p class="rider-description fw-normal"><?php echo isset($rider['terrain_description']) ? $rider['terrain_description'] : ''; ?></p>
                    <div class="row-miles d-flex">

                        <?php if (!empty($rider['miles_total'])) : ?>
                            <div class="miles">
                                <p class="rider-heading fw-bold"><i class="miles-img fa-sharp fa-solid fa-compass"></i> Distance</p>
                                <div class="m-flex">
                                    <div class="m-col">
                                        <p class="miles-h fw-medium">Daily Average</p>
                                        <p class="miles-p fw-normal"><?php echo isset($rider['miles_daily_average']) ? $rider['miles_daily_average'] : ''; ?></p>
                                    </div>
                                    <div class="m-col">
                                        <p class="miles-h fw-medium">Total</p>
                                        <p class="miles-p fw-normal"><?php echo isset($rider['miles_total']) ? $rider['miles_total'] : ''; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endif;
                        if (!empty($rider['walking_activity_level']) && !empty($rider['average_walking_time'])) : ?>
                            <div class="miles">
                                <p class="rider-heading fw-bold"><i class="miles-img fa-solid fa-person-hiking"></i>Details</p>
                                <div class="m-flex">
                                    <div class="m-col">
                                        <p class="miles-h fw-medium">Activity Level</p>
                                        <p class="miles-p fw-normal"><?php echo isset($rider['walking_activity_level']) ? $rider['walking_activity_level'] : ''; ?></p>
                                    </div>
                                    <div class="m-col">
                                        <p class="miles-h fw-medium">Average Time</p>
                                        <p class="miles-p fw-normal"><?php echo isset($rider['average_walking_time']) ? $rider['average_walking_time'] : ''; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endif;
                        if (isset($rider['elevation_total']) && !empty($rider['elevation_total'])) :
                        ?>
                            <div class="miles">
                                <p class="rider-heading fw-bold"><i class="miles-img fa-solid fa-mountains"></i> Elevation</p>
                                <div class="m-flex">
                                    <div class="m-col">
                                        <p class="miles-h fw-medium">Daily Average</p>
                                        <p class="miles-p fw-normal"><?php echo isset($rider['elevation_daily_average']) ? $rider['elevation_daily_average'] : ''; ?></p>
                                    </div>
                                    <div class="m-col">
                                        <p class="miles-h fw-medium">Total</p>
                                        <p class="miles-p fw-normal"><?php echo isset($rider['elevation_total']) ? $rider['elevation_total'] : ''; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="nav-non-rider" role="tabpanel" aria-labelledby="nav-non-rider-tab">
                    <?php $nonrider = get_field('non-rider'); ?>
                    <div class="d-flex">
                        <div class="left-div">
                            <p><?php echo isset($nonrider['non-rider_description']) ? $nonrider['non-rider_description'] : ''; ?></p>
                            <ul>
                                <?php
                                if ($nonrider['non-rider_list']) {
                                    foreach ($nonrider['non-rider_list'] as $data) { ?>
                                        <li class="fw-normal"><?php echo $data['list_item'] ?></li>
                                <?php }
                                } ?>
                            </ul>
                        </div>
                        <div class="right-div">
                            <img src="<?php echo wp_get_attachment_url($nonrider['non-rider_image']); ?>" alt="non rider">
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="nav-support" role="tabpanel" aria-labelledby="nav-support-tab">
                    <?php $support = get_field('support'); ?>
                    <div class="d-flex">
                        <div class="left-div">
                            <p><?php echo $support['support_description']; ?></p>
                            <ul>
                                <?php if ($support['support_list']) {
                                    foreach ($support['support_list'] as $data) { ?>
                                        <li class="fw-normal"><?php echo $data['list_item'] ?></li>
                                <?php }
                                } ?>
                            </ul>
                        </div>
                        <div class="right-div">
                            <img src="<?php echo isset($support['support_image']['url']) ? $support['support_image']['url'] : "javascript:"; ?>" alt="<?php echo isset($support['support_image']['alt']) ? $support['support_image']['alt'] : ''; ?>">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <hr class="pdp-section__divider">
</div>
<?php } ?>
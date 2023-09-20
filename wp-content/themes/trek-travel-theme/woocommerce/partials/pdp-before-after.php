<?php 
// PDP - Before After
$pdp_before = get_field('before');
$pdp_before_after_identical = get_field('before_after_identical');
$pdp_after = get_field('after');

if (!empty($pdp_before) || !empty($pdp_after)) {
    $pdp_before_image_url = $pdp_before['before_image'] ? wp_get_attachment_url($pdp_before['before_image']) : '';
    $pdp_after_image_url = $pdp_after['after_image'] ? wp_get_attachment_url($pdp_after['after_image']) : '';

?>
<div class="container pdp-section" id="before-after">
    <div class="row">
        <div class="col-12">
            <h5 class="fw-semibold pdp-section__title">Before and After Your Trip</h5>

            <?php if (!$pdp_before_after_identical): ?>
            <nav>
                <div class="nav nav-tabs" id="nav-tab-ba" role="tablist">
                    <?php if (isset($pdp_before) && !empty($pdp_before)) { ?>
                        <button class="nav-link active" id="nav-before-tab" data-bs-toggle="tab" data-bs-target="#nav-before" type="button" role="tab" aria-controls="nav-before" aria-selected="true">Before Your Trip</button>
                    <?php } ?>
                    <?php if (isset($pdp_after) && !empty($pdp_after)) { ?>
                        <button class="nav-link" id="nav-after-tab" data-bs-toggle="tab" data-bs-target="#nav-after" type="button" role="tab" aria-controls="nav-after" aria-selected="false">After Your Trip</button>
                    <?php } ?>
                </div>
            </nav>
            <?php endif; ?>
            
            <div class="tab-content" id="nav-tabContent-ba">
                <div class="tab-pane fade show active" id="nav-before" role="tabpanel" aria-labelledby="nav-before-tab">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="left-div">
                            <p class="heading-before fw-bold"><?php echo $pdp_before['before_title']; ?></p>
                            <p><?php echo $pdp_before['before_text']; ?></p>
                        </div>
                        <?php if (isset($pdp_before_image_url)) { ?>
                            <div class="right-div">
                                <img src="<?php echo $pdp_before_image_url; ?>">
                            </div>
                        <?php } ?>
                    </div>
                    <?php if(!empty($pdp_before['before_hotels'])):  ?>
                        <div class="where-to-stay">
                            <p class="heading-before fw-bold">Where to Stay</p>
                            <div class="row">
                                <?php foreach($pdp_before['before_hotels'] as $item): ?>
                                    <div class="col-lg-4 hotel-details">
                                        <p class="hotel-heading fw-medium"><?php echo $item['before_hotel_title'];?></p>
                                        <p>
                                        <?php echo $item['before_hotel_text'];?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif;?>
                </div>
                <div class="tab-pane fade" id="nav-after" role="tabpanel" aria-labelledby="nav-after-tab">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="left-div">
                            <p class="heading-before fw-bold"><?php echo $pdp_after['after_title']; ?></p>
                            <p><?php echo $pdp_after['after_text']; ?></p>
                        </div>
                        <?php if (isset($pdp_after_image_url)) { ?>
                            <div class="right-div">
                                <img src="<?php echo $pdp_after_image_url; ?>">
                            </div>
                        <?php } ?>
                    </div>
                    <?php if(!empty($pdp_after['after_hotels'])): ?>
                        <div class="where-to-stay">
                            <p class="heading-before fw-bold">Where to Stay</p>
                            <div class="row">
                                <?php foreach($pdp_after['after_hotels'] as $item): ?>
                                    <div class="col-lg-4 hotel-details">
                                        <p class="hotel-heading fw-medium"><?php echo $item['after_hotel_title'];?></p>
                                        <p>
                                        <?php echo $item['after_hotel_text'];?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
	<hr class="pdp-section__divider" >
</div>
<?php
}
?>
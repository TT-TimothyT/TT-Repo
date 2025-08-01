<?php 
// PDP - Trip Inclusions

$product = wc_get_product( get_the_ID() );
$product_id = $product->get_id();

$activity_terms = get_the_terms( $product_id, 'activity' );

foreach ( $activity_terms as $activity_term) {
	$activity = $activity_term->name;   
}

$whats_included = get_field('whats_included');
$whats_not_included = get_field('whats_not_included');

if(!empty($whats_included['included_list']) || !empty($whats_not_included['not_included_list'])) {

?>
<div class="container pdp-section <?php if (!empty($activity) && $activity != TT_ACTIVITY_DASHBOARD_NAME_BIKING):?>hw<?php endif;?>" id="inclusions">
    <div class="row">
        <div class="col-12">

            <h5 class="fw-semibold pdp-section__title">Trip Inclusions</h5>

            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <?php if(!empty($whats_included['included_list'])){ ?>
                    <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">What’s Included</button>
                    <?php } ?>
                    <?php if(!empty($whats_not_included['not_included_list'])){ ?>
                    <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">What’s Not Included</button>
                    <?php } ?>
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <?php if(!empty($whats_included['included_list'])) { ?> 
                <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                    
                    <div class="d-flex">
                        <div class="left-div">
                            <ul>
                                <?php if($whats_included['included_list']) {
                                        foreach( $whats_included['included_list'] as $data ) { ?>
                                            <li class="fw-normal"><?php echo $data['list_item']?></li>
                                <?php } }?>
                            </ul>
                        </div>
                        <div class="right-div">
                            <div class="gallery" id="gallery">
                                <?php if( $whats_included['included_gallary'] ){
                                     foreach( $whats_included['included_gallary'] as $img ) { ?>
                                    <div class="pics">
                                        <img src="<?php echo $img['url']?>" alt="<?php echo $img['alt']?>" width="150"/>
                                    </div>
                                <?php } } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <?php if(!empty($whats_not_included['not_included_list'])) { ?>
                <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                    
                    <div class="d-flex">
                        <div class="left-div">
                            <ul>
                                <?php if($whats_not_included['not_included_list']) {
                                    foreach( $whats_not_included['not_included_list'] as $data ) { ?>
                                    <li class="fw-normal"><?php echo $data['list_item']?></li>
                                <?php } }?>
                            </ul>
                        </div>
                        <?php if(!empty($whats_not_included['gratuities_description'])) { ?>
                        <div class="right-div">
                            <div class="gratuities_description">
                                <i class="mx-auto mb-5 d-flex justify-content-center fa-regular fa-hand-holding-circle-dollar"></i>
                                <p class="gratuities_heading fw-bold text-center">Guide Gratuities</p>
                                <p class="text-center description"><?php echo $whats_not_included['gratuities_description'];?></p>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

	<hr class="pdp-section__divider">
</div>
<?php } ?>
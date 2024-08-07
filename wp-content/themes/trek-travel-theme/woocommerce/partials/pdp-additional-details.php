<?php 
// PDP - Additional Details
$details = get_field('aditional_details');
$product = wc_get_product( get_the_ID() );
$product_id = $product->get_id();

$activity_terms = get_the_terms( $product_id, 'activity' );

foreach ( $activity_terms as $activity_term) {
	$activity = $activity_term->name;   
}

if(!empty($details)):
?>
<a class="pdp-anchor" id="additional-details"></a>
<div class="container pdp-section <?php if (!empty($activity) && $activity != TT_ACTIVITY_DASHBOARD_NAME_BIKING):?>hw<?php endif;?>" id="additional-details">
    <div class="row">
        <div class="col-md-12">

		<h5 class="fw-semibold pdp-section__title">Additional Details</h5>
            <div class="additional-details">
                <?php
                    echo $details;
                ?>
            </div>
        </div>
    </div>

	<hr class="pdp-section__divider">
</div>
<?php endif; ?>
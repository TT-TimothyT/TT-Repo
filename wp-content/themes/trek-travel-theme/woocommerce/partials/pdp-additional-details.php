<?php 
// PDP - Additional Details
$details = get_field('aditional_details');

$activity_tax = get_field('Activity');
$activity = $activity_tax->name;

if(!empty($details)):
?>
<a class="pdp-anchor" id="additional-details"></a>
<div class="container pdp-section <?php if (!empty($activity) && $activity != 'Biking'):?>hw<?php endif;?>" id="additional-details">
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
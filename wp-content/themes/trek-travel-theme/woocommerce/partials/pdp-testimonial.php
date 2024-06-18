<?php 
// PDP - Testimonial
$testimonial_details = get_field('testimonials');
if(!empty($testimonial_details)):

    $activity_tax = get_field('Activity');
    $activity = $activity_tax->name;
?>
    <div class="container pdp-section <?php if (!empty($activity) && $activity != 'Biking'):?>hw<?php endif;?>" id="testimonials">
        <div class="row">
            <div class="col-12">
                <h5 class="fw-semibold pdp-section__title">What Guests are Saying</h5>

                <div class="pdp_testimonial-slider">
                        <?php foreach($testimonial_details as $item): ?>
                        <div class="card">
                            <img src="<?php echo $item['testimonial_image']['url']?>" class="card-img-top" alt="<?php echo $item['testimonial_image']['alt']?>">
                            <div class="card-body">
                                <div>
                                    <p class="card-text text text-center"><?php echo $item['testimonial_text']; ?></p>
                                    <span class="read-more">Read more</span>
                                </div>
                            <p class="card-text text-author text-center fw-medium"><?php echo $item['testimonial_author']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

        <hr class="pdp-section__divider">
    </div>
<?php endif; ?>

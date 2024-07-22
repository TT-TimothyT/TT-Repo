<!-- faqs start TREK-251 -->
<?php
    if( have_rows('faqs') ):
?>
    <!-- <a class="pdp-anchor" id="faqs"></a> -->
    <div class="container pdp-section faq-container" id="faqs">
        <div class="row">
            <div class="col-12">

                <h5 class="fw-semibold pdp-section__title">FAQs</h5>

                <div class="accordion accordion-flush" id="accordionFlushExample">

                    <?php							
                        // Loop through rows.
                        $counter = 1;
                        $hideMe = "";
                        while( have_rows('faqs') ) : the_row();
                            if($counter > 12){
                                $hideMe = "d-none";
                            }                            
                            // Load sub field value.
                            $question = get_sub_field('question');
                            $answer = get_sub_field('answer');
                            ?>
                            <div class="accordion-item <?php echo $hideMe; ?>">
                                <h4 class="accordion-header fw-medium fs-lg lh-lg" id="flush-heading-faq<?php echo $counter; ?>">
                                    <button class="accordion-button collapsed px-0 fw-medium fs-lg lh-lg" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-faq<?php echo $counter; ?>" aria-expanded="false" aria-controls="flush-collapse-faq<?php echo $counter; ?>">
                                        <?php echo $question; ?>
                                    </button>
                                </h4>
                                <div id="flush-collapse-faq<?php echo $counter; ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading-faq<?php echo $counter; ?>" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body px-0">
                                        <?php echo $answer; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $counter++;
                            // End loop.
                        endwhile;
                    ?>

                </div>
                <?php if($counter > 13){ ?>
                    <div class="view-all-faqs text-center m-4">
                        <button class="btn btn-primary rounded-1">View all FAQs</button>
                    </div>
                <?php } ?>

            </div>
        </div>

		<hr class="pdp-section__divider">
    </div>

<?php
    endif;
?>
<!-- faqs end TREK-251 -->
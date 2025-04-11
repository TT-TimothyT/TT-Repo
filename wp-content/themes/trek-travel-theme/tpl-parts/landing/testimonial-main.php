<?php

$page_contents = get_field('page_content');
$form_id = get_field('form_selection');

?>

<main class="testimonial-main">
<?php if (!empty($page_contents)): ?>
    <?php foreach ($page_contents as $content): ?>
    <section class="page-content">
        <div class="container py-5">
            <div class="row">
                <div class="col-12 col-lg-8 mx-auto mb-4">
                    <div class="content-box">
                        <?php echo wp_kses_post($content['text_content']); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endforeach; ?>
    <?php endif; ?>


    <section class="main-content" id="t-form">
        <div class="container py-5">
            <?php if (!empty($form_id)) { ?>
            <div class="row">
                <div class="col-12 col-xl-8 mx-auto">
                <div class="f-header h3 text-center">Your Story Starts Here</div>
                    <div class="f-box" >
                        

                        <?php echo do_shortcode('[gravityform id="' . esc_attr($form_id) . '" title="false" description="false" ajax="true"]'); ?>
                    </div>
                </div>           
            </div>
            <?php } else {
                echo '<div class="row h3 text-center mx-auto">Please select a form in the page settings.</div>';
            } ?>
        </div>
    </section>
</main>

<?php

if ($form_id): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        jQuery(document).on("submit", "form[gformid='<?php echo esc_js($form_id); ?>']", function (event) {
            let pickYourTrip = jQuery("#input_<?php echo esc_js($form_id); ?>_6").val(); // Pick Your Trip dropdown
            let manualTripChecked = jQuery("#input_<?php echo esc_js($form_id); ?>_7").is(":checked"); // Checkbox
            let enterYourTrip = jQuery("#input_<?php echo esc_js($form_id); ?>_8").val(); // Manual Trip Text Field
            let errorMessage = "Please select a trip from the dropdown OR enter a trip manually.";

            // If neither option is filled, show an error
            if ((!pickYourTrip || pickYourTrip === "") && !manualTripChecked) {
                alert(errorMessage);
                event.preventDefault();
            }

            // If "Manual Trip Entry" is checked but no text is entered, show an error
            if (manualTripChecked && (!enterYourTrip || enterYourTrip.trim() === "")) {
                alert(errorMessage);
                event.preventDefault();
            }
        });
    });
    </script>
<?php endif; ?>

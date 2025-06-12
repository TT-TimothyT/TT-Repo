<?php
$form_id = get_field('form_selection');

?>

<main class="testimonial-main">

    <?php get_template_part('tpl-parts/common/wysiwyg-repeater'); ?>
    <?php get_template_part('tpl-parts/common/media-repeater'); ?>


    <section class="main-content" id="t-form">
        <div class="container">
            <?php if (!empty($form_id)) { ?>
            <div class="row">
                <div class="col-12 col-xl-8 mx-auto">
                <div class="f-header h3 text-center">Your story starts here</div>
                    <div class="f-box" >
                        <?php gravity_form($form_id,false,true,false,'',true)?>
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
<<<<<<< HEAD
=======

    document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form.open-on-submit');

    forms.forEach(function (form) {
        form.addEventListener('submit', function () {
        const redirectField = form.querySelector('.upload-link input');
        const url = redirectField?.value;
        if (url) {
            window.open(url, '_blank');
        }
        });
    });
    });

    document.addEventListener("DOMContentLoaded", function () {
        jQuery(document).on("submit", "form[gformid='<?php echo esc_js($form_id); ?>']", function (event) {
            let pickYourTrip = jQuery("#input_<?php echo esc_js($form_id); ?>_6").val(); // Pick Your Trip dropdown
            let manualTripChecked = jQuery("#input_<?php echo esc_js($form_id); ?>_7").is(":checked"); // Checkbox
            let enterYourTrip = jQuery("#input_<?php echo esc_js($form_id); ?>_8").val(); // Manual Trip Text Field
            let errorMessage = "Please select a trip from the dropdown OR enter a trip manually.";
>>>>>>> b09dc4edd2329a81bc84f8c0febe19b37554c100

    document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.open-on-submit');

    forms.forEach(function (form) {
        form.addEventListener('submit', function () {
        const redirectField = form.querySelector('.upload-link input');
        const url = redirectField?.value;
        if (url) {
            window.open(url, '_blank');
        }
        });
    });
    });




    </script>
<?php endif; ?>

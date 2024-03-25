<?php
// Details Section Template Part

// Details Section

$details_contents = get_field('content_section');

?>

<?php 
    ?>
    <section class="details-sect">
        <?php foreach($details_contents as $details_content) {
            $section_header = $details_content['section_header'];
            $section_image = $details_content['section_image'];
            $section_text = $details_content['section_text'];

         ?>
        <div class="details-block">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="details-content">
                            <h4 class="h2"><?php echo esc_html($section_header); ?></h2>
                            <div class="wysiwyg h5 fw-semibold"><?php echo $section_text; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($section_image)): ?>
                <div class="details-img col-12 col-md-5" style="background-image: url(<?php echo $section_image['url']; ?>)" alt="<?php echo esc_attr($section_image['alt']); ?>">
                </div>
            <?php endif; ?>
        </div>
    <?php  }?>
    </section>

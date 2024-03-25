<?php
// CTA Blog Template Part


    $cta_header = get_field('cta_header');
    $cta_image = get_field('cta_image');
    $cta_text = get_field('cta_text');

    $cta_button = get_field('cta_button');
    $btn_text = $cta_button['text'];
    $btn_post = $cta_button['link'];
    $btn_link = get_permalink($btn_post);


?>

<?php 
    ?>
    <section class="cta-sect">
            
        <div class="cta-block">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-lg-4 offset-lg-7">
                        <div class="cta-content">
                            <h4 class="h2"><?php echo esc_html($cta_header); ?></h2>
                            <div class="wysiwyg h5 fw-semibold"><?php echo $cta_text; ?></div>
                            <?php if (!empty($btn_text) && !empty($btn_link)) { ?>
                            <a class="btn btn-secondary" href="<?php echo esc_url($btn_link); ?>"><?php echo $btn_text; ?></a>
                        <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($cta_image)): ?>
                <div class="cta-img col-12 col-lg-6" style="background-image: url(<?php echo $cta_image['url']; ?>)" alt="<?php echo esc_attr($cta_image['alt']); ?>">
                </div>
            <?php endif; ?>
        </div>
    </section>

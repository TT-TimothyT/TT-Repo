<?php
/**
 * Template Part: ACF Media Gallery (images + videos)
 * Usage: get_template_part('tpl-parts/common/wysiwyg-repeater');
 */
$page_contents = get_field('page_content');

 if (!empty($page_contents)): ?>
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

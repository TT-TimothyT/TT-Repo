<?php
// Intro Section Template Part

$intro_header = get_field('intro_header');
$intro_content = get_field('intro_content');

?>

<?php if (!empty($intro_header) || !empty($intro_content)): ?>
    <section class="intro-sect">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-9">
                    <div class="intro-content">
                        <h2 class="text-center"><?php echo esc_html($intro_header); ?></h2>
                        <div class="wysiwyg text-center"><?php echo $intro_content; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

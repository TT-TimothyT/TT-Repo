<?php
// Intro Section Template Part

// Main Section
$main_header = get_field('main_header');
$main_content = get_field('main_content');

$main_button = get_field('main_button');
$btn_text = $main_button['text'];
$btn_tf = $main_button['manual'];
$btn_post = $main_button['link'];
$btn_mlink = $main_button['manual_link'];
$btn_link = get_permalink($btn_post);

print_r($btn_tf);
?>

<?php if (!empty($main_header) || !empty($main_content)): ?>
    <section class="main-sect">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-9">
                    <div class="main-content">
                        <h2 class=""><?php echo esc_html($main_header); ?></h2>
                        <div class="wysiwyg text-center"><?php echo $main_content; ?></div>
                        
                        <?php if (!empty($btn_tf) && ($btn_tf == true)) { ?>
                            <?php if (!empty($btn_text) && !empty($btn_mlink)) { ?>
                                <a class="btn btn-primary" href="<?php echo esc_url($btn_mlink); ?>"><?php echo $btn_text; ?></a>
                            <?php } ?>
                        <?php } else { ?>
                            <?php if (!empty($btn_text) && !empty($btn_link)) { ?>
                                <a class="btn btn-primary" href="<?php echo esc_url($btn_link); ?>"><?php echo $btn_text; ?></a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
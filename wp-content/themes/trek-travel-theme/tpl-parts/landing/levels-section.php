<?php
/**
 * Levels Section Template Part
 */

$levels_header = get_field('levels_header');
$averages = get_field('averages');

$levels_button = get_field('levels_button');
$btn_text = $levels_button['text'] ?? '';
$btn_post = $levels_button['link'] ?? '';
$btn_link = !empty($btn_post) ? get_permalink($btn_post) : '';

$lvls_subtext = get_field('levels_subtext');
$lvls_img = get_field('levels_image');


if (!empty($levels_header)): ?>
    <section class="levels-sect">
        <div class="container">
        <?php if ($levels_header): ?>
            <div class="lvl-header">
                <div class="row">
                    <div class="col-12">
                        <h3 class="h2 fw-semibold text-center"><?php echo esc_html($levels_header); ?></h3>
                    </div>
                </div>
            </div>
        <?php endif; ?>

            <div class="lvl-container">
                <div class="row">
                    <div class="col-12">
                        <?php if (!empty($lvls_img) && is_array($lvls_img)): ?>
                            <div class="lvls-image text-center">
                                <?php
                                tt_image('levels_image');
                                ?>
                            
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- <div class="container"> -->
            <div class="row">
                <div class="col-12">
                    <div class="lvls-find">
                        <?php 
                        tt_button('levels_button');
                        ?>

                        <?php if (!empty($lvls_subtext)): ?>
                            <span class="fst-italic text-center"><?php echo wp_kses_post($lvls_subtext); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

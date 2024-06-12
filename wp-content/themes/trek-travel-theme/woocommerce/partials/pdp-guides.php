<?php 
// PDP - Guides
if(get_field('guide_image')):
?>
<div class="container pdp-section" id="bikes-guide">
    <div class="row">
        <div class="col-md-12">
            <div class="row great-riders">

                <div class="col-lg-6 great-rider-img">
                    <?php
                        $guide_image = get_field('guide_image');
                    ?>
                    <img src="<?php echo esc_url( $guide_image['url'] ); ?>" alt="<?php echo esc_attr( $guide_image['alt'] ); ?>">
                </div>

                <div class="col-lg-6 great-rider align-middle">
                    <p><?php the_field('guide_title'); ?></p>
                    <h5><?php the_field('guide_subtitle'); ?></h5>
                    <p class="great-p"><?php echo wp_kses_post( get_field('guide_text') ); ?></p>
                    <?php
                    $link = get_field('guide_url');
                    if (!empty($link)):
                    ?>
                    <a class="btn btn-md" target="_blank" href="<?php echo esc_url( $link['url'] ); ?>" title="<?php echo esc_attr( $link['title'] ); ?>"><?php echo esc_html( $link['title'] ); ?></a>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>

	<hr class="pdp-section__divider" >
</div>
<?php endif; ?>

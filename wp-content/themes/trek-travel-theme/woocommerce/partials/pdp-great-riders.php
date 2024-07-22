<?php 
// PDP - Great rides
if(get_field('gr_image')):
?>
<div class="container pdp-section" id="great-ride">
    <div class="row">
        <div class="col-md-12">
            <div class="row great-rides">

                <div class="col-12 col-lg-9 overlap-img">
                    <?php
                        $gr_image = get_field('gr_image');
                    ?>
                    <img src="<?php echo esc_url( $gr_image['url'] ); ?>" alt="<?php echo esc_attr( $gr_image['alt'] ); ?>">
                </div>

                <div class="col-lg-6 overlap-text align-middle">
                    <p><?php echo wp_kses_post( get_field( 'gr_title' ) ); ?></p>
                    <h5><?php echo wp_kses_post( get_field( 'gr_subtitle' ) ); ?></h5>
                    <p class="overlap-p"><?php echo wp_kses_post( get_field( 'gr_text' ) ); ?></p>
                    <?php
                    $link = get_field('gr_url');
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

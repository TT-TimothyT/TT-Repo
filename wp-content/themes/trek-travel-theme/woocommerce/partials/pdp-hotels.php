<?php
defined('ABSPATH') || exit;

global $product;

// look for hotel realation field on the product
$pdp_hotels = get_field('hotels');

if ( $pdp_hotels ) :
?>
<!-- <a class="pdp-anchor" id="hotels"></a> -->
<div class="container pdp-section" id="hotels">

	<h5 class="fw-semibold pdp-section__title">Hotels</h5>

	<div class="pdp-hotels container p-0 m-0">

		<?php foreach( $pdp_hotels as $post ):

		// To get ACF
		setup_postdata($post); 
		$hotel_url = get_field('hotel_url');
		?>
		<div class="row p-0 pdp-hotels__hotel">
			<div class="pdp-hotels__slider col-12 col-lg-6 p-0">
				<?php

				$hotel_images = get_field('hotel_gallery');

				foreach( $hotel_images as $image ) : ?>
					<div class="pdp-hotels__image-slide">
						<img src="<?php echo esc_url($image['sizes']['large']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
					</div>
				<?php endforeach; ?>
			</div>

			<div class="pdp-hotels__info col-12 col-lg-6">
				<span class="pdp-hotels__region d-block"><?php echo wp_kses_post( get_field( 'city' ) ); ?>, <?php the_field( 'region' ); ?></span>
				<h5 class="pdp-hotels__name"><?php the_title(); ?></h5>
				<span class="pdp-hotels__level d-block"><?php the_field( 'hotel_level' ); ?> <i class="bi bi-info-circle pdp-hotel-levels"></i></span>
				<span class="pdp-hotels__available d-block"><?php echo wp_kses_post( get_field( 'hotel_note' ) ); ?></span>
				<p class="pdp-hotels__text"><?php substr(the_content(), 0, 10); ?>...</p>
				<?php if ($hotel_url) : ?>
					<a href="<?php echo $hotel_url; ?>" class="pdp-hotels__link" target="_blank">Learn more</a>
				<?php endif;?>
			</div>
		</div> <!-- .row -->
		<?php 
		endforeach;
		wp_reset_postdata();
		?>

	</div>

	<hr class="pdp-section__divider">

</div>
<?php endif; ?>
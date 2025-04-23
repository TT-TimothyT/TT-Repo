<?php
defined('ABSPATH') || exit;

global $product;

// look for bike realation field on the product
$pdp_bikes = get_field('bikes');

if ( $pdp_bikes ) :
?>

<div class="container pdp-section" id="bikes-guides">

	<h5 class="fw-semibold pdp-section__title">Bikes & Gear</h5>

	<div class="container pdp-bikes p-0">

		<span class="pdp-bikes__subtitle d-block">Ride the Best Bikes in the Industry</span>
		

		<div class="pdp-bikes__bike-grid d-flex flex-column flex-lg-row flex-nowrap flex-lg-wrap">
			<?php foreach( $pdp_bikes as $post ):

			// To get ACF
			setup_postdata($post); 
			$bike_image = get_field('bike_image');
			$bike_url = get_field('bike_url');
			?>
			<div class="pdp-bikes__bike">
				<div class="pdp-bikes__image d-flex justify-content-center align-content-center">
					<img src="<?php echo esc_url($bike_image['sizes']['large']); ?>" alt="<?php echo esc_attr($bike_image['alt']); ?>">
					<span class="pdp-bikes__badge pdp-bikes__badge--ebike"><?php the_field( 'bike_badge' ); ?></span>
				</div>
				<h5 class="pdp-bikes__name"><?php the_title(); ?></h5>
				<p class="pdp-bikes__text"><?php the_content() ?></p>
				<?php if ($bike_url) : ?>
					<a href="<?php echo $bike_url; ?>" class="pdp-bikes__link" target="_blank">Learn more</a>
				<?php endif;?>
			</div> 
			<?php 
			endforeach;
			wp_reset_postdata();
			?>
		</div>

	</div>

	<hr class="pdp-section__divider">

</div>
<?php endif; ?>
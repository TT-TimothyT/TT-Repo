<?php
defined('ABSPATH') || exit;

global $product;

$additional_gear_list = get_field('additional_gear_list');
$additional_gear_image = get_field('additional_gear_image');

if(!empty($additional_gear_list)):
?>
<div class="container pdp-section" id="bikes">

	<h5 class="fw-semibold pdp-section__title">Additional Gear</h5>

	<div class="container pdp-additional-gear p-0">

		<div class="pdp-bikes__bike-grid d-flex flex-column flex-lg-row">
			<ul class="pdp-additional-gear__list">
				<?php foreach( $additional_gear_list as $item ): ?>
					<li><?php echo $item['list_item']; ?></li>
				<?php endforeach; ?>
			</ul> 

			<div class="pdp-additional-gear__image">
				<?php if(!empty($additional_gear_image)):?>
					<img src="<?php echo esc_url( $additional_gear_image['url'] ); ?>" alt="<?php echo esc_attr( $additional_gear_image['alt'] ); ?>">
				<?php else:?>
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/additional-gear-placeholder-image.png" alt="Bike Name">
				<?php endif;?>
			</div>
		</div>

	</div>

	<hr class="pdp-section__divider">

</div>
<?php endif; ?>
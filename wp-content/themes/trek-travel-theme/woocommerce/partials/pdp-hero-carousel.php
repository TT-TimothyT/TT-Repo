<?php 
// PDP - Overvew Navigation - Mobile
global $post;
$product_id = $post->ID;

$product = new WC_product($product_id);
$attachment_ids = $product->get_gallery_image_ids();
if(count($attachment_ids) > 4){
	$attachment_ids = array_slice($attachment_ids, 0, 4);
}

$product_overview = get_field('product_overview');
if($product_overview){
	$videoUrl = isset($product_overview['hero_video']) ? $product_overview['hero_video'] : '';
}

if(!empty($attachment_ids)) : ?>

<div class="product-slider">

	<!-- Carousel with autoplay -->
	<!-- <div id="demo" class="carousel slide" data-bs-ride="carousel"> -->
	<!-- Carousel -->
	<div id="demo" class="carousel slide" data-bs-interval="false">

		<!-- Indicators/dots -->
		<div class="carousel-indicators">

		<?php 
			$j=0;
			if (isset($videoUrl) && !empty($videoUrl)) { 
				$j=1;
		?>
				<!-- # code... -->
				<button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
			<?php } ?>
			<?php 
			foreach( $attachment_ids as $attachment_id ) : ?>

				<button type="button" data-bs-target="#demo" data-bs-slide-to="<?php echo $j;?>" class="<?php echo ($j == 0 ? 'active' : ''); ?>"></button>

			<?php 
			$j++;
			endforeach; ?>
		</div>

		<!-- The slideshow/carousel -->
		<div class="carousel-inner">

		<?php 
			$i=0;
			if (isset($videoUrl) && !empty($videoUrl)) { 
				$i=1;
				$videoId = substr($videoUrl, strrpos($videoUrl, '/') + 1);
		?>
		<div class="carousel-item active hero-video">
			<iframe src="<?php echo $videoUrl; ?>?mute=1&autoplay=1&controls=0&loop=1&playlist=<?php echo $videoId; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
		<?php } ?>

			<?php 
			
			foreach( $attachment_ids as $attachment_id ) :
				$image_link =wp_get_attachment_url( $attachment_id );
			?>

				<div class="carousel-item <?php echo ($i == 0 ? 'active' : ''); ?>">
					<img src="<?php echo $image_link;?>" alt="slide 2" class="d-block" style="width:100%">
				</div>

			<?php 
			$i++;
			endforeach; 
			?>
		</div>
	</div>

	<!-- Left and right controls/icons -->
	<button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
		<span class="carousel-control-prev-icon"></span>
	</button>
	<button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
		<span class="carousel-control-next-icon"></span>
	</button>
</div>

<?php endif; ?>
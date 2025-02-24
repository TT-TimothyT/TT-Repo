<?php
/**
 * The compare page template file
 *
 * @version 1.0.8
 * @since 1.0.0
 * @package WC_Products_Compare
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' ); ?>

<noscript><?php esc_html_e( 'Sorry, you must have Javascript enabled in your browser to use compare products', 'woocommerce-products-compare' ); ?></noscript>

<div class="woocommerce-products-compare-content woocommerce">
	<?php

	$products = WC_Products_Compare_Frontend::get_compared_products();
	if ( $products ) {
		global $product;

		// Get all row headers.
		$headers = WC_Products_Compare_Frontend::get_product_meta_headers( $products );
		?>

	<div class="container">
		<h4 class="fw-semibold text-center my-5">Compare Trips</h4>

		<?php do_action( 'woocommerce_before_shop_loop' ); ?>
		<div class="row row-cols-3 g-4">
		

			<?php
				$productCounter = 0;
				$visibilityClass = "";
				foreach ( $products as $product ) {

					if ($productCounter > 0) {
						$visibilityClass = "invisible"	;							
					}
					$product           = wc_get_product( $product );
					$trip_id           = get_post_meta( $product->get_id(), TT_WC_META_PREFIX . 'tripId', true );
					$tripRegion        = tt_get_local_trips_detail('tripRegion', $trip_id, $product->get_sku(), true);
					$parent_product_id = tt_get_parent_trip_id_by_child_sku($product->get_sku());
					$pa_city = '';
					if( $parent_product_id ){
						$p_product = wc_get_product( $parent_product_id );
						$pa_city = $p_product->get_attribute( 'pa_city' );
					}
					if ( ! WC_Products_Compare::is_product( $product ) ) {
						continue;
					}

					$product_overview = get_field('product_overview', $product->get_id());
					$product_subtitle = $product_overview['product_subtitle'];
					$pdp_bikes = get_field('bikes', $product->get_id());
			?>
					<div class="col product-card-<?php echo esc_attr( $product->get_id() ); ?>">
						<div class="card h-100" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
							<a href="<?php echo esc_attr( get_permalink( $product->get_id() ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>" class="product-link">
								<?php woocommerce_show_product_loop_sale_flash(); ?>
								<?php echo $product->get_image( 'shop_single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span class="badge text-bg-dark">Featured</span>
							</a>
							<!-- <img src="..." class="card-img-top" alt="..."> -->
							<div class="card-body px-0">
								<div class="product-info-mobile desktop-hideme">
									<p class="fw-medium fs-lg lh-lg "><?php echo $product->get_title(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
									<p class="fw-normal fs-sm lh-sm"><?php echo $product->get_attribute( 'pa_city' ); ?>, <?php echo $product->get_attribute( 'pa_region' ); ?></p>
									<a class="btn btn-primary rounded-1" href="<?php echo get_permalink( $product->get_id() ); ?>#dates-pricing">Book now</a>
								</div>
								<div class="product-info-desktop mobile-hideme">
									<div class="primary-left w-75">
										<p class="fw-normal fs-sm lh-sm"><?php echo $product->get_attribute( 'pa_city' ); ?>, <?php echo $product->get_attribute( 'pa_region' ); ?></p>
										<p class="fw-medium fs-xl lh-xl "><?php echo $product->get_title(); ?></p>
									</div>
                                    
									<div class="primary-right">
										<p class="fw-normal fs-sm lh-sm">Starting from</p>
										<p class="fw-medium fs-xl lh-xl"><?php echo $product->get_price_html(); ?><span class="fw-normal fs-sm lh-sm">pp</span></p>
									</div>
								</div>
								<p class="fw-normal fs-sm lh-sm line-clamp-short-description w-75 mobile-hideme">
                               		<?php $text = $product_subtitle;
									      $text = substr($text, 0, 35);
										  echo $text . '...';
									?>  
                                </p>
								<a class="btn btn-primary rounded-1 mb-4 mobile-hideme" href="<?php echo get_permalink( $product->get_id() ); ?>#dates-pricing">Book this trip</a>
								<div style="display: none;" class="yotpo yotpo-main-widget fw-semibold" data-product-id="<?php echo $product->get_id() ?>"></div>
                                <p class="fw-normal fs-md lh-md mobile-hideme">
									<span class="fw-semibold"><i class="bi bi-star"></i>
									<span id="displayed-average-score"></span>
									</span>
									<span id="percentage-value" class="text-muted review-text"></span>

								</p>
								<hr>

								<div class="compare-pricing">
									<p class="desktop-hideme fw-bold fs-lg lh-lg <?php echo $visibilityClass; ?>"><?php esc_html_e( 'Starting from', 'woocommerce-products-compare' ); ?></p>
									<p class="desktop-hideme fw-bold fs-lg lh-lg"><?php echo $product->get_price_html(); ?></p>
								</div>
								<hr class="desktop-hideme">
								
								<div class="compare-description">
									<p class="fw-bold fs-lg lh-lg <?php echo $visibilityClass; ?>"><?php esc_html_e( 'Description', 'woocommerce-products-compare' ); ?></p>
									<p class="fw-normal fs-sm lh-sm line-clamp-description">
									<?php $text = $product->post->post_excerpt; 
											$text = substr($text, 0, 250);
											echo $text . '...'; 
									?>  
									</p>
								</div>
								<hr>

								<div class="compare-rider">
									<p class="fw-bold fs-lg lh-lg <?php echo $visibilityClass; ?>"><?php esc_html_e( 'Rider Level', 'woocommerce-products-compare' ); ?></p>
									<p class="fw-normal fs-sm lh-sm"><?php echo $product->get_attribute( 'pa_rider-level' ); ?></p>
								</div>
								<hr>

								<div class="compare-duration">
									<p class="fw-bold fs-lg lh-lg <?php echo $visibilityClass; ?>"><?php esc_html_e( 'Duration', 'woocommerce-products-compare' ); ?></p>
									<p class="fw-normal fs-sm lh-sm"><?php echo $product->get_attribute( 'pa_duration' ); ?> </p>
								</div>
								<hr>

								<div class="compare-style">
									<p class="fw-bold fs-lg lh-lg <?php echo $visibilityClass; ?>"><?php esc_html_e( 'Trip Style', 'woocommerce-products-compare' ); ?></p>
									<p class="fw-normal fs-sm lh-sm"><?php echo $product->get_attribute( 'pa_trip-style' ); ?></p>
								</div>
								<hr>

								<div class="compare-hotel">
									<p class="fw-bold fs-lg lh-lg <?php echo $visibilityClass; ?>"><?php esc_html_e( 'Hotel Level', 'woocommerce-products-compare' ); ?></p>
									<p class="fw-normal fs-sm lh-sm"><?php echo $product->get_attribute( 'pa_hotel-level' ); ?></p>
								</div>
								<hr>

								<div class="compare-bikes">
									<p class="fw-bold fs-lg lh-lg <?php echo $visibilityClass; ?>"><?php esc_html_e( 'Bikes Available', 'woocommerce-products-compare' ); ?></p>
									<p class="fw-normal fs-sm lh-sm">
									<?php 
									if ($pdp_bikes) {
										foreach ($pdp_bikes as $key => $bike) {
											if ($key < 4) {
												echo $bike->post_title.'<br>';
											}
										}
									}
									?>
									</p>
								</div>
								<hr>

							</div>
						</div>
					</div>


			<?php
				$productCounter++;
			} 
			?>


		</div>

	</div>
		<?php
	} else {

		echo WC_Products_Compare_Frontend::empty_message(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

</div><!--.woocommerce-products-compare-content-->
<script type="text/javascript">
    yotpo.initWidgets();
</script>
<?php get_footer( 'shop' ); ?>
<script type="text/javascript">
    // Function to update average score for a specific product ID
    function updateAverageScoreForProduct(productId) {
        var averageScoreElement = document.querySelector(".product-card-" + productId + " .yotpo-regular-box .avg-score");
        var averageScoreValue = averageScoreElement ? parseFloat(averageScoreElement.textContent.trim()) : null;

        var displayedAverageScore = document.querySelector(".product-card-" + productId + " #displayed-average-score");
        var percentageDiv = document.querySelector(".product-card-" + productId + " #percentage-value");
        
        if (displayedAverageScore && percentageDiv) {
            var percentage = calculatePercentage(averageScoreValue);
            var formattedAverageScore = averageScoreValue ? averageScoreValue.toFixed(1) : '0';
            displayedAverageScore.textContent = " " + formattedAverageScore + " / 5";
            percentageDiv.textContent = " " + percentage + "% Would Recommend";
        }
    }

    // Calculate percentage function ...
    function calculatePercentage(score) {
        if (score === null || score === ' ') {
            return '0';
        }

        // Calculate the percentage based on your scoring system
        var maxScore = 5; // Change this to the maximum score in your system
        var percentage = (score / maxScore) * 100;
        return percentage.toFixed(1);
    }

    // Attach the update function to the window.onload event
    window.onload = function() {
        <?php foreach ( $products as $product ) { ?>
            updateAverageScoreForProduct(<?php echo esc_js( $product ); ?>);
        <?php } ?>
    };
</script>

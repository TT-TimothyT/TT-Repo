<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product;
$productData = new WC_Product($product->get_id());
$upsells = $product->get_upsell_ids();
$args = array(
	'post_type' => 'product',
	'ignore_sticky_posts' => 1,
	'posts_per_page' => 6,
	'post__in' => $upsells,
	'post__not_in' => array_merge([$product->get_id()], tt_get_line_items_product_ids() ),
	'meta_query' => array(
		array(
			'key' => '_children',
			'compare' => 'EXISTS'
		)
	)
);
$similar_product = new WP_Query($args);
if( $similar_product->have_posts() ) { ?>
	<section class="related products">
		<div id="similar-trips">
			<div class="row">
				<div class="col-12">
					<?php
					$heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Similar Trips', 'woocommerce' ) );
					if ( $heading ) :
						?>
						<h3 class="fw-semibold my-4 similar-trips-title"><?php echo esc_html( $heading ); ?></h3>
					<?php endif; ?>
					<div class="pdp_similar_trips_slider my-4">
						<?php while($similar_product->have_posts()){
							$similar_product->the_post();
							wc_get_template_part( 'content', 'product' );
						}
						wp_reset_postdata();
						?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php } ?>
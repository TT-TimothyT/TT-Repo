<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// For now this code needs to live here for the carousel otherwise the page
// will not display - need to find a better way to do this.
global $post;
$product_id = $post->ID;

$product = new WC_product($product_id);
$attachment_ids = $product->get_gallery_image_ids();
if(count($attachment_ids) > 4){
	$attachment_ids = array_slice($attachment_ids, 0, 4);
}

$activity_type = get_field('activity_type');

get_header( 'shop' );
?>
<div class="pdp-hero">
<?php
// Hero Carousel //////////////////////////////////////////
wc_get_template_part( 'partials/pdp', 'hero-carousel' );

// overview bar //////////////////////////////////////////
wc_get_template_part( 'partials/pdp', 'overview-bar' );

// Overview navigation - mobile ///////////////////////////
wc_get_template_part( 'partials/pdp', 'overview-navigation-mobile' );

?>
</div>
<div class="container-fluid p-0 pdp-container">
	<div class="row">
		<div class="col-md-3 overview-sidebar-desktop">

			<?php
			// Overview Navigation - Desktop //////////////////
			wc_get_template_part( 'partials/pdp', 'overview-navigation' );
			?>
			
		</div>

		<div class="col-md-9 p-0 pdp-content-section <?php if (!empty ($activity_type) && ($activity_type == 'H&W') ): ?>hw<?php endif; ?>">
			<!-- pdp right side content goes here -->
			<!-- similar trips section goes outside div.row container -->

			<?php 
				while ( have_posts() ) : 
				the_post(); 
				
				/** READ ME ////////////////////////////////////////////////
				 * 
				 * UPDATED - 10/06/2022
				 * 
				 * TEMPLATES -----------------------------------------------
				 * From this point forward all PDP sections will be put into
				 * template parts. The timplate parts should be added to the
				 * /partials directory
				 * 
				 * Naming convention of template parts files:
				 * - 'pdp-section-name.php'
				 * 
				 * Most template template parts for pdp should be called from 
				 * here in this while loop and are called like so:
				 * - wc_get_template_part( 'partials/pdp', 'overview-navigation' );
				 * 
				 * WOOCOMMERCE HOOKS ---------------------------------------
				 * If you need to reference a woocommerce hook you can do it 
				 * like so:
				 * - echo woocommerce_template_single_title()
				 * or just
				 * - woocommerce_breadcrumb();
				 * 
				 * ** When using either test to make sure it works like
				 * ** it should
				 * 
				 * SCSS STYLES ---------------------------------------------
				 * When styling sections create a new SCSS for that section
				 * in the src/scss/PDP folder. Then import them at the bottom
				 * of the src/scss/_pdp.scss file instead of main.scss
				 * - @import "PDP/pdp-overview-section";
				 * 
				 * ADVANCED CUSTOM FIELDS ----------------------------------
				 * When referencing a ACF atribute follow the documentation
				 * here - https://www.advancedcustomfields.com/resources/
				 * 
				 * This workflow should prevent conflicts for this file and 
				 * the _pdp.scss file
				 */

				//  Overview section ///////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'overview-section' );

				//  Trip Wows section ///////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'trip-wows' );

				// Great Riders  ///////////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'testimonial' );

				// Great Riders  ///////////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'great-riders' );

				// Dates & Pricing  ///////////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'dates-pricing' );

				// Itinerarey /////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'itinerary' );

				// Rider Information  ////////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'rider-information' );

				// hotels  ////////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'hotels' );

				// hotels  ////////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'bikes' );

				// Guides  ///////////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'guides' );

				// Additional Gear  ////////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'additional-gear' );

				// Trip Inclusions  ///////////////////////////////////////////
				wc_get_template_part( 'partials/pdp', 'trip-inclusions' );

				// Additional Details //////////////////////
				wc_get_template_part( 'partials/pdp', 'additional-details' ); 

				// Before After //////////////////////
				wc_get_template_part( 'partials/pdp', 'before-after' );

				// Weather //////////////////
				wc_get_template_part( 'partials/pdp', 'weather' );

				// FAQ //////////////////
				wc_get_template_part( 'partials/pdp', 'faq' );
				
				// Reviews //////////////////
				wc_get_template_part( 'partials/pdp', 'reviews' );

				// This Should be removed once all new sections
				//wc_get_template_part( 'content', 'single-product' ); 
				
				endwhile; // end of the loop.

				/**
				 * woocommerce_after_main_content hook.
				 *
				 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
				 */
				do_action( 'woocommerce_after_main_content' );
			?>
		</div> <!-- pdp-content-section -->
		
	</div> <!-- row -->

	<div class="col-12">
		<?php woocommerce_output_related_products(); ?>
	</div><!-- col-12 -->

</div> <!-- container-fluid -->


<?php
get_footer( 'shop' );
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
?>



<script>
	jQuery(document).ready(function () {
		dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
		dataLayer.push({
			'event':'view_item',
			'ecommerce': {
				'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
				'detail': { 
					'products': [{
						'name': "<?php echo $product->name; ?>", // Please remove special characters
						'id': '<?php echo $product->id; ?>', // Parent ID
						'price': "<?php echo number_format((float)$product->price, 2, '.', ''); ?>", // per unit price displayed to the user - no format is ####.## (no '$' or ',')
						'brand': '', //
						'category': "<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>", // populate with the 'country,continent' separating with a comma
					}]
				}
			}
		})
		jQuery('body').removeClass('elementor-kit-14');
	})
</script>

<script>
	document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('*');
    elements.forEach(element => {
        const bounding = element.getBoundingClientRect();
        if (bounding.right > window.innerWidth || bounding.left < 0) {
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const threshold = 10; // Set the overflow threshold

    const checkOverflow = () => {
        const docWidth = document.documentElement.offsetWidth;
        const winWidth = window.innerWidth;

        if ((docWidth - winWidth) > threshold) {
            document.body.style.overflowX = 'scroll';
        } else {
            document.body.style.overflowX = 'hidden';
        }
    };

    // Initial check
    checkOverflow();

    // Re-check on window resize
    window.addEventListener('resize', checkOverflow);
});

</script>
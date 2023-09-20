<?php 
// PDP - Overvew Navigation
global $product, $post;
$product_id = $post->ID;
?>

<!-- PDP - Overview Navigation -->
<div class="navigation-sticky position-sticky">
	<nav class="nav flex-column">
		<a class="nav-link active" aria-current="page" href="#overview">Overview</a>
		<a class="nav-link" href="#dates-pricing">Dates & Pricing</a>
		<a class="nav-link" href="#itinerary">Itinerary</a>
		<a class="nav-link" href="#hotels">Hotels</a>
		<a class="nav-link" href="#bikes-guides">Bikes & Guides</a>
		<a class="nav-link" href="#inclusions">Inclusions</a>
		<a class="nav-link" href="#additional-details">Additional Details</a>
		<a class="nav-link" href="#faqs">FAQs</a>
		<a class="nav-link" href="#reviews">Reviews</a>
	</nav>

	<div class="overview-info">
		<div class="book-trip-cta">
			<a href="#dates-pricing" class="btn btn-primary btn-md rounded-1">Book this trip</a>
		</div>
		<p class="fw-semibold mt-4 mb-0">Have a question?</p>
		<p class="fw-normal"><a href="/contact-us" target="_blank">Contact us</a></p>
		<div class="overview-icons d-flex">
			<button type="button" class="btn btn-outline-secondary share-link">
				<i class="bi bi-link-45deg"></i>
			</button>
			<form class="cart" action="" method="post" enctype="multipart/form-data">
			<?php if( is_user_logged_in() ) { ?>
				<input type="hidden" name="wlid" id="wlid"/>
				<input type="hidden" name="add-to-wishlist-type" value="<?php echo $product->get_type(); ?>"/>
				<input type="hidden" name="wl_from_single_product" value="<?php echo is_product() ? '1' : '0'; ?>"/>	
				<input type="hidden" name="quantity[<?php echo $product->get_id(); ?>]" value="1"/>		
				<a rel="nofollow" href="" data-productid="<?php echo $product->get_id(); ?>" data-listid="<?php echo $add_to_wishlist_args['single_id']; ?>" class="wl-add-to btn btn-outline-secondary add-wishlist ">
					<i class="bi bi-heart"></i><i class="bi bi-heart-fill"></i>
				</a>
				<?php } else { ?>
					<a class="btn btn-outline-secondary add-wishlist" href="<?php echo site_url('login'); ?>">
						<i class="bi bi-heart"></i><i class="bi bi-heart-fill"></i>
					</a>
				<?php } ?>							
			</form>
		</div>
	</div>

	<!-- toasts message -->
	<div class="toast bg-white link-copied align-items-center w-auto start-0" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="d-flex">
			<div class="toast-body">
				Link copied
			<i class="bi bi-x-lg align-self-center" data-bs-dismiss="toast" aria-label="Close"></i>
			</div>
		</div>
	</div>
</div>
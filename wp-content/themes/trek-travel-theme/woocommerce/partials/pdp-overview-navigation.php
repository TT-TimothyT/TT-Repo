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
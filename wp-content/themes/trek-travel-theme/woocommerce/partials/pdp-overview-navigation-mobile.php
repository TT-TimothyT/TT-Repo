<?php 
// PDP - Overvew Navigation - Mobile

$is_event = has_term('Event Access', 'product_tag');
?>

<!-- PDP - Overview Navigation - Mobile  -->
<div class="overview-menu-mobile position-sticky h-100">
	<div class="accordion accordion-flush" id="accordionFlushExample">
		<div class="accordion-item">
			<span class="accordion-header" id="flush-headingOne">
				<button class="accordion-button collapsed fs-lg fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
					Overview
				</button>
			</span>
			<div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
				<div class="accordion-body">
					<nav class="nav flex-column">
						<a class="nav-link" aria-current="page" href="#overview">Overview</a>
						<a class="nav-link" href="#dates-pricing">Dates & Pricing</a>
						<a class="nav-link" href="#itinerary">
							<?php echo $is_event ? 'Details' : 'Itinerary'; ?>
						</a>
						<a class="nav-link" href="#hotels">Hotels</a>
						<a class="nav-link" href="#bikes-guides">Bikes & Guides</a>
						<a class="nav-link" href="#inclusions">Inclusions</a>
						<a class="nav-link" href="#additional-details">Know Before You Go</a>
						<a class="nav-link" href="#faqs">FAQs</a>
						<a class="nav-link" href="#reviews">Reviews</a>
					</nav>
				</div>
			</div>
		</div>
	</div>
</div>
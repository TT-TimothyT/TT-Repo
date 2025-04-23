<?php 
// PDP - Overview Navigation
global $product, $post;
$product_id = $post->ID;

$activity_terms = get_the_terms( $product_id, 'activity' );
$is_event = has_term('Event Access', 'product_tag');

foreach ( $activity_terms as $activity_term) {
	$activity = $activity_term->name;   
}
?>

<!-- PDP - Overview Navigation -->
<div class="navigation-sticky position-sticky">
	<nav class="nav flex-column">
		<a class="nav-link active" aria-current="page" href="#overview">Overview</a>
		<a class="nav-link" href="#dates-pricing">Dates & Pricing</a>
		<a class="nav-link" href="#itinerary">
            <?php echo $is_event ? 'Details' : 'Itinerary'; ?>
        </a>
		<a class="nav-link" href="#hotels">Hotels</a>
		<?php if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING): ?>
			<a class="nav-link" href="#bikes-guides">Bikes & Gear</a>
		<?php endif; ?>
		<a class="nav-link" href="#inclusions">Inclusions</a>
		<a class="nav-link" href="#additional-details">Know Before You Go</a>
		<a class="nav-link" href="#faqs">FAQs</a>
		<a class="nav-link" href="#reviews">Reviews</a>
	</nav>

    <div class="overview-info">
        <div class="book-trip-cta">
            <a href="#dates-pricing" class="btn btn-primary btn-md rounded-1">Book this trip</a>
        </div>
        <p class="fw-semibold">Have a question?</p>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
	const header = document.querySelector('header');
	const navigationSticky = document.querySelector('.navigation-sticky');

	if (header && navigationSticky) {
		const headerHeight = header.offsetHeight;
		document.documentElement.style.setProperty('--header-height', `${headerHeight}px`);

		const adjustNavigationHeight = () => {
			const viewportHeight = window.innerHeight;
			const maxHeight = viewportHeight - headerHeight;
			navigationSticky.style.maxHeight = `${maxHeight}px`;
		};

		adjustNavigationHeight();
		window.addEventListener('resize', adjustNavigationHeight);
	}

	const navLinks = document.querySelectorAll('.nav-link');
	const observerOptions = {
		root: null,
		rootMargin: '0px',
		threshold: Array.from({ length: 101 }, (_, i) => i / 100)
	};

	const observer = new IntersectionObserver((entries) => {
		entries.forEach(entry => {
			const activeSectionId = entry.target.getAttribute('id');
			if (entry.intersectionRatio > 0.5) {
				navLinks.forEach(link => {
					const targetHref = link.getAttribute('href');
					if (targetHref) {
						link.classList.toggle('active', targetHref.substring(1) === activeSectionId);
					}
				});
			}
		});
	}, observerOptions);

	navLinks.forEach(link => {
		const targetHref = link.getAttribute('href');
		const targetId = targetHref ? targetHref.substring(1) : null;

		if (targetId) {
			const targetElement = document.getElementById(targetId);
			if (targetElement) {
				observer.observe(targetElement);
			} else {
				// 🔹 Hide the nav link if the target section doesn't exist
				link.style.display = 'none';
			}
		}
	});
});

</script>

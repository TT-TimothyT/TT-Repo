<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<script>
		window.dataLayer = window.dataLayer || [];
	</script>
	<!-- Google Tag Manager -->
	<script>
		(function(w, d, s, l, i) {
			w[l] = w[l] || [];
			w[l].push({
				'gtm.start': new Date().getTime(),
				event: 'gtm.js'
			});
			var f = d.getElementsByTagName(s)[0],
				j = d.createElement(s),
				dl = l != 'dataLayer' ? '&l=' + l : '';
			j.async = true;
			j.src =
				'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
			f.parentNode.insertBefore(j, f);
		})(window, document, 'script', 'dataLayer', 'GTM-KZZB2GJ');
	</script>
	<!-- End Google Tag Manager -->
<script type="text/javascript">
(function e(){var e=document.createElement("script");e.type="text/javascript",e.async=true,e.src="//staticw2.yotpo.com/4488jd7QVtY0HrLS8BYsAC3fel6zpMyyxIyl9wLW/widget.js";var t=document.getElementsByTagName("script")[0];t.parentNode.insertBefore(e,t)})();
</script>
<script>
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<script type="text/javascript" defer>
    (function e(){
        var e = document.createElement("script");
        e.type = "text/javascript";
        e.async = true;
        e.src = "https://staticw2.yotpo.com/4488jd7QVtY0HrLS8BYsAC3fel6zpMyyxIyl9wLW/widget.js";
        var t = document.getElementsByTagName("script")[0];
        t.parentNode.insertBefore(e, t);
    })();
</script>
	<?php wp_head(); ?>

</head>

<?php
$search_enabled  = get_theme_mod('search_enabled', '1'); // Get custom meta-value.
?>

<body <?php body_class(); ?>>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KZZB2GJ" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	<?php wp_body_open(); ?>

	<a href="#main" class="visually-hidden-focusable"><?php esc_html_e('Skip to main content', 'trek-travel-theme'); ?></a>

	<div id="wrapper">

		<?php
		// Header Promo Banner /////////////////

		$promo_banners = new WP_Query(array(
			'post_type' => 'promo_banner',
			'post_status' => 'publish'
		));

		if ($promo_banners->have_posts()) :
		?>
			<div class="promo-banner bg-primary text-secondary">
				<div class="container">
					<div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
						<div class="carousel-inner">

							<?php
							$slide_index = 0;
							while ($promo_banners->have_posts()) : $promo_banners->the_post();
							?>
								<div class="carousel-item text-center promo-banner__content<?php echo ($slide_index == 0 ? ' active' : ''); ?> ">
									<?php the_content(); ?>
								</div>
							<?php
								$slide_index++;
								wp_reset_postdata();
							endwhile;
							?>

						</div>
						<button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
							<span class="carousel-control-prev-icon promo-banner__control-icon" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
							<span class="visually-hidden">Previous</span>
						</button>
						<button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
							<span class="carousel-control-next-icon promo-banner__control-icon" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
							<span class="visually-hidden">Next</span>
						</button>
					</div>
				</div>
			</div>
		<?php
		endif;
		// End Promo Banners
		?>

		<header class="sticky-top bg-white header-main">
			<?php
			// Contact Nav Menu /////////////////
			?>
			<div class="header-main__contact-nav">
				<div class="container">
					<ul class="nav justify-content-end">
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center" href="<?php echo site_url('catalog') ?>" onclick="dataLayer.push({'event': 'catalog_request'});"><i class="bi bi-book"></i>Request a Catalog</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center" href="<?php echo site_url('contact-us') ?>"><i class="bi bi-envelope"></i>Contact Us</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center" href="tel:8864648735" onclick="dataLayer.push({'event': 'click_to_call'});"><i class="bi bi-telephone"></i>886-464-8735</a>
						</li>
					</ul>
				</div>
			</div>

			<?php
			// Main Nav Menu ////////////////////
			?>
			<!-- search bar -->
			
<!-- search bar end -->

<nav id="header" class="navbar <?php if (!WC()->cart->is_empty()) : echo 'pb-0';
								endif; ?> mb-0 desktop-nav navbar-expand-md<?php if (is_home() || is_front_page()) : echo ' home';
																			endif; ?>">
	<div class="container">
		<a class="navbar-brand" href="<?php echo esc_url(home_url()); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">
			<img class="navbar-brand__logo" src="<?php echo get_template_directory_uri(); ?>/assets/images/desktop_logo.svg" alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" />
		</a>

		<div id="navbar" class="collapse navbar-collapse">
			<?php
			// Loading WordPress Custom Menu (theme_location).
			wp_nav_menu(
				array(
					'theme_location' => 'main-menu',
				)
			);


			?>


		</div><!-- /.navbar-collapse -->

		<?php
		// Search and Account ////////////////////
		?>
		<div class="header-main__search-account d-flex align-items-center gap-5">
			<?php if ('1' === $search_enabled) : ?>
				<div data-bs-toggle="modal" data-bs-target="#globalSearchModal" class="search-in-header">
					<a class="d-flex align-items-center" href="#"><i class="bi bi-search"></i></a>
				</div>
			<?php endif; ?>
			<div class="account-in-header">
				<a class="d-flex align-items-center" href="<?php echo is_user_logged_in() ? site_url('my-account') : site_url('login'); ?>"><i class="bi bi-person"></i></a>
			</div>
			<a href="<?php echo site_url('bike-tours/all/') ?>" class="btn btn-primary find-a-trip">Find a trip</a>
		</div>

	</div><!-- /.container -->

	<!-- saved cart bar -->
	<?php
	$cart_result = get_user_meta(get_current_user_id(),'_woocommerce_persistent_cart_' . get_current_blog_id(), true); 
	$cart = WC()->session->get( 'cart', null );
	$persistent_cart_count = isset($cart_result['cart']) && $cart_result['cart'] ? count($cart_result['cart']) : 0;
	if ( !is_null($cart) && $persistent_cart_count > 0 ) {
	?>
		<div class="container-fluid saved-cart d-flex justify-content-md-center align-items-center p-lg-0 p-3">
			<p class="fw-normal fs-md lh-md mb-0">
				Hey, you have a booking already started for a trip. Pick up where you left off and <br>
				<a href="<?php echo trek_checkout_step_link(1); ?>" class="fw-semibold">Complete your booking</a>
			</p>
		</div>
	<?php } ?>
	<!-- saved cart bar end -->
</nav><!-- /#header -->

<nav id="header" class="navbar mb-0 mobile-nav<?php if (is_home() || is_front_page()) : echo ' home';
												endif; ?>">

	<div class="row">
		<!-- <div class="col-2">
			<a class="nav-link" href="tel:8864648735" onclick="dataLayer.push({'event': 'click_to_call'});">
				<i class="bi bi-telephone"></i>
			</a>
		</div>
		<div class="col-2">
			<a class="nav-link" href="" id="mobile-header-calendar">
				<i class="bi bi-calendar"></i>
			</a>
		</div> -->
		<div class="col-8 mobile-logo">
			<a class="navbar-brand" href="<?php echo esc_url(home_url()); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">
				<img class="navbar-brand__logo" src="<?php echo get_template_directory_uri(); ?>/assets/images/mobile_logo.svg" alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" />
			</a>
		</div>
		<div class="col-2 search-icon">
			<?php
			// Search and Account ////////////////////
			?>
			<div class="header-main__search-account">
				<?php if ('1' === $search_enabled) : ?>
					<div data-bs-toggle="modal" data-bs-target="#globalSearchModal" class="search-in-header">
						<a class="" href="#"><i class="bi bi-search"></i></a>
					</div>
				<?php endif; ?>

			</div>
		</div>
		<div class="col-2 mobile-menu-toggle">
			<div id="navbar" class="collapse show">
				<?php
				// Loading WordPress Custom Menu (theme_location).
				wp_nav_menu(
					array(
						'theme_location' => 'main-menu',
					)
				);
				?>
			</div><!-- /.navbar-collapse -->
		</div>

	</div><!-- /.container -->

	<!-- saved cart bar -->
	<?php
	$cart_result = get_user_meta(get_current_user_id(),'_woocommerce_persistent_cart_' . get_current_blog_id(), true); 
	$cart = WC()->session->get( 'cart', null );
	$persistent_cart_count = isset($cart_result['cart']) && $cart_result['cart'] ? count($cart_result['cart']) : 0;
	if ( !is_null($cart) && $persistent_cart_count > 0 ) {
	?>
		<div class="container-fluid saved-cart d-flex justify-content-md-center align-items-center p-lg-0 p-3">
			<p class="fw-normal fs-md lh-md mb-0">
				Hey, you have a booking already started for a trip. Pick up where you left off and <br>
				<a href="<?php echo trek_checkout_step_link(1); ?>" class="fw-semibold">Complete your booking</a>
			</p>
		</div>
	<?php } ?>
	<!-- saved cart bar end -->

</nav><!-- /#header -->
<div id="autocomplete"></div>
</header>

<div class="modal modal-search-filter fade" id="homeHeaderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog h-100 m-0 top-0" role="document">
    <div class="modal-content">
      <div class="modal-header">
		  <h4 class="modal-title position-relative text-center" id="myModalLabel">Select Date Range</h4>
			<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
				<i type="button" class="bi bi-x"></i>
			</span>
      </div>
      <div class="modal-body">
        <span id="dateRangePickerHeader"></span>
		<div id="headerCTrigger"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary">Apply</button>
      </div>
    </div>
  </div>
</div>

<div class="modal modal-search-filter fade" id="mobileCalendarModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog h-100 m-0 top-0" role="document">
    <div class="modal-content">
      <div class="modal-header">
		  <h4 class="modal-title position-relative text-center" id="myModalLabel">Select Date Range</h4>
			<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
				<i type="button" class="bi bi-x"></i>
			</span>
      </div>
      <div class="modal-body">
        <span id="dateRangePickerMobileCalendar"></span>
		<div id="mobileCalendarTrigger"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary mobile-calendar-apply">Apply</button>
      </div>
    </div>
  </div>
</div>
<form action="/bike-tours/all" method="get" class="trek-trip-finder-form header-calendar-form mobile-calendar-form" id="trek-trip-finder-form">
	<input type="hidden" id="start_time" name="start_time">
	<input type="hidden" id="end_time" name="end_time">
</form>

<!-- Modal -->
<div class="modal fade" id="globalSearchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-fullscreen-md-down">
    <div class="modal-content">
      <!-- <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div> -->
      <div class="modal-body">
	  <?php
			if ('1' == $search_enabled) :
			?>

				<div class="search-container p-0" id="">
					<form class="search-form mb-4" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">

						<div class="input-group mb-4 mx-auto search-input-wrapper">
							<i class="bi bi-search"></i>
							<input class="search-form__input form-control bg-gray-100 border-0" type="text" id="search-input" name="s" placeholder="<?php esc_attr_e('Search', 'trek-travel-theme'); ?>" title="<?php esc_attr_e('Search', 'trek-travel-theme'); ?>" >
							<a class="clear-input" onclick="document.getElementById('search-input').value = ''">Clear</a>
							<span id="search-close" class="search-from__icon search-form__icon--close input-group-text bg-transparent border-0" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></span>
						</div>
					</form>

					<?php
					$popular1 = $popular2 = $popular3 = $popular4 = new stdClass();
					$results = array();
					$algolia_results = trek_algolia_popular_search();
					if ($algolia_results) {
						$results = $algolia_results['results'];
						$popular1 = $algolia_results['popular_1'];
						$popular2 = $algolia_results['popular_2'];
						$popular3 = $algolia_results['popular_3'];
						$popular4 = $algolia_results['popular_4'];
					}
					// 
					?>
					<div class="result-container">
						<div class="container">
							<div class="row">
								<div class="col-md-3 border-end">
								<?php if (isset($results) && $results && isset($results['hits'])) { ?>
									<h6 class="fw-bold ps-4">Popular Searches</h6>
									<div class="list-group list-group-flush">
										<?php
										$popular_search_output = '';
										for ($iter = 0; $iter <= 4; $iter++) {
											$hits = $results["hits"][$iter]["query"];
											$popular_search_output .= '<a href="/?s=algolia&wp_searchable_posts[query]=' . $hits . '&wp_searchable_posts[menu][post_type_label]=Trips" class="list-group-item border-0">' . $hits . '</a>';
										}
										echo $popular_search_output;
										?>
									</div>
								</div>
						<?php } ?>

						<!-- </div> -->
						<div class="col-md-9">
							<div class="row">
								<span class="fw-bold mb-2">Popular Trips</span>
								<?php if ($popular1 != new stdClass()) { ?>
									<div class="col-3">
										<div class="card border-0">
											<a href="<?php echo ($popular1 ? $popular1->Permalink : '');  ?>">
												<img src="<?php echo ($popular1 ? $popular1->gallery_images[0] : DEFAULT_IMG); ?>" class="card-img-top rounded-1" alt="...">
												<div class="card-body ps-0">
													<p class="card-text text-start fw-semibold"><?php echo ($popular1 ? $popular1->Title : ''); ?></p>
												</div>
											</a>
										</div>
									</div>
								<?php } ?>
								<?php if ($popular2 != new stdClass()) { ?>
									<div class="col-3">
										<div class="card border-0">
											<a href="<?php echo ($popular2 ? $popular2->Permalink : ''); ?>">
												<img src="<?php echo ($popular2 ? $popular2->gallery_images[0] : DEFAULT_IMG); ?>" class="card-img-top rounded-1" alt="...">
												<div class="card-body ps-0">
													<p class="card-text text-start fw-semibold"><?php echo ($popular2 ? $popular2->Title : ''); ?></p>
												</div>
											</a>
										</div>
									</div>
								<?php } ?>
								<?php if ($popular3 != new stdClass()) { ?>
									<div class="col-3">
										<div class="card border-0">
											<a href="<?php echo ($popular3 ? $popular3->Permalink : ''); ?>">
												<img src="<?php echo ($popular3 ? $popular3->gallery_images[0] : DEFAULT_IMG); ?>" class="card-img-top rounded-1" alt="...">
												<div class="card-body ps-0">
													<p class="card-text text-start fw-semibold"><?php echo ($popular3 ?  $popular3->Title : ''); ?></p>
												</div>
											</a>
										</div>
									</div>
								<?php } ?>
								<?php if ($popular4 != new stdClass()) { ?>
									<div class="col-3">
										<div class="card border-0">
											<a href="<?php echo ($popular4 ? $popular4->Permalink : ''); ?>">
												<img src="<?php echo ($popular4 ? $popular4->gallery_images[0] : DEFAULT_IMG); ?>" class="card-img-top rounded-1" alt="...">
												<div class="card-body ps-0">
													<p class="card-text text-start fw-semibold"><?php echo ($popular4 ? $popular4->Title : ''); ?></p>
												</div>
											</a>
										</div>
									</div>
								<?php } ?>
								<?php
								if ($popular1 == new stdClass() && $popular2 == new stdClass() && $popular3 == new stdClass() && $popular4 == new stdClass()) {
									echo '<p class="no-results">No popular post found!</p>';
								}
								?>
							</div>
							</div>
						</div>
						</div>
					</div>
				</div>
<?php
			endif;
?>


      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> -->
    </div>
  </div>
</div>
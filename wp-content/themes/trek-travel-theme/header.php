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

<script src="https://kit.fontawesome.com/e4636bfea5.js" crossorigin="anonymous"></script>

<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/favicon-16x16.png">
<link rel="manifest" href="<?php echo esc_url( home_url( '/site.webmanifest' ) ); ?>">
<link rel="mask-icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">

	<?php wp_head(); ?>

</head>

<?php
$search_enabled = get_theme_mod('search_enabled', '1'); // Get custom meta-value.

// Run the cart check once.
$is_cart_check = apply_filters( 'tt_is_persistent_cart', true ) && apply_filters( 'tt_is_persistent_cart_valid', true );
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
							<a class="nav-link d-flex align-items-center" href="tel:8664648735" onclick="dataLayer.push({'event': 'click_to_call'});"><i class="bi bi-telephone"></i>866-464-8735</a>
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
					<a class="d-flex align-items-center" href="javascript:void(0)"><i class="bi bi-search"></i></a>
				</div>
			<?php endif; ?>
			<div class="account-in-header">
				<a class="d-flex align-items-center" href="<?php echo is_user_logged_in() ? site_url('my-account') : site_url('login'); ?>"><i class="bi bi-person"></i></a>
			</div>
			<a href="<?php echo site_url('tours/all/') ?>" class="btn btn-primary find-a-trip">Find a trip</a>
		</div>

	</div><!-- /.container -->

	<!-- saved cart bar -->
	<?php if ( $is_cart_check ) { ?>
		<div class="container-fluid saved-cart d-flex justify-content-md-center align-items-center p-lg-0 p-3">
		<p class="fw-normal fs-md lh-md mb-0">
			Almost there! <a href="<?php echo trek_checkout_step_link(1); ?>" class="fw-semibold">Complete your booking</a> and get ready for your vacation of a lifetime
			</p>
		</div>
	<?php } ?>
	<!-- saved cart bar end -->
</nav><!-- /#header -->

<nav id="header" class="navbar mb-0 mobile-nav<?php if (is_home() || is_front_page()) : echo ' home';
												endif; ?>">

	<div class="row">
		<div class="col-2 phone-icon">
			<a class="nav-link" href="tel:8664648735" onclick="dataLayer.push({'event': 'click_to_call'});">
				<i class="bi bi-telephone"></i>
			</a>
		</div>
		<div class="col-1 calendar-icon">
			<a class="nav-link" href="" id="mobile-header-calendar">
				<i class="bi bi-calendar"></i>
			</a>
		</div>
		<div class="col-6 mobile-logo">
			<a class="navbar-brand" href="<?php echo esc_url(home_url()); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">
				<img class="navbar-brand__logo" src="<?php echo get_template_directory_uri(); ?>/assets/images/mobile_logo.svg" alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" />
			</a>
		</div>
		<div class="col-1 search-icon">
			<?php
			// Search and Account ////////////////////
			?>
			<div class="header-main__search-account">
				<?php if ('1' === $search_enabled) : ?>
					<div data-bs-toggle="modal" data-bs-target="#globalSearchModal" class="search-in-header">
						<a class="" href="javascript:void(0)"><i class="bi bi-search"></i></a>
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



</nav><!-- /#header -->

	<!-- saved cart bar -->
	<?php if ( $is_cart_check ) { ?>
		<div class="container-fluid saved-cart mobile d-md-none d-flex justify-content-center text-center p-1">
			<p class="fw-normal fs-md lh-md mb-0">
			Almost there! <a href="<?php echo trek_checkout_step_link(1); ?>" class="fw-semibold">Complete your booking</a> and get ready for your vacation of a lifetime
			</p>
		</div>
	<?php } ?>
	<!-- saved cart bar end -->

<div id="autocomplete"></div>
</header>

<!-- Modal -->
<div class="modal fade" id="globalSearchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-xl modal-fullscreen-md-down">
    	<div class="modal-content">
      		<div class="modal-body">
	  			<?php if ('1' == $search_enabled) : ?>
					<div class="search-container p-0" id="">
						<form class="container search-form" role="search" method="get" action="<?php echo esc_url( home_url('/') ); ?>">
							<div class="row">
								<div class="d-flex col-12 col-xl-8 mx-auto search-input-wrapper">
									
									<div class="s-input">
										<i class="bi bi-search"></i>
										<input class="search-form__input form-control bg-gray-100 border-0" type="text" id="search-input" name="s" placeholder="<?php esc_attr_e('Search', 'trek-travel-theme'); ?>" title="<?php esc_attr_e('Search', 'trek-travel-theme'); ?>" >
										<a class="clear-input" onclick="document.getElementById('search-input').value = ''">Clear</a>
									</div>
									
									<span id="search-close" class="search-from__icon search-form__icon--close input-group-text bg-transparent border-0" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></span>
								</div>
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
						?>

						<div class="result-container">
							<div class="container">
								<div class="row">
									<div class="ps-box col-md-3 col-xl-2">
										<?php if (isset($results) && $results && isset($results['hits'])) { ?>
											<span class="fw-bold">Popular Searches</span>
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
										<?php } ?>
									</div>
									<div class="col-md-9 col-xl-10">
										<div class="row">
											<span class="fw-bold mb-2">Popular Trips</span>
											<?php if ( $popular1 != new stdClass() && ! empty( $popular1 ) ) { ?>
												<div class="col-6 col-xl-3">
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
											<?php if ( $popular2 != new stdClass() && ! empty( $popular2 ) ) { ?>
												<div class="col-6 col-xl-3">
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
											<?php if ( $popular3 != new stdClass() && ! empty( $popular3 ) ) { ?>
												<div class="col-6 col-xl-3">
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
											<?php if ( $popular4 != new stdClass() && ! empty( $popular4 ) ) { ?>
												<div class="col-6 col-xl-3">
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
											if ( ( $popular1 == new stdClass() || empty( $popular1 ) ) && ( $popular2 == new stdClass() || empty( $popular2 ) ) && ( $popular3 == new stdClass() || empty( $popular3 ) ) && ( $popular4 == new stdClass() || empty( $popular4 ) ) ) {
												echo '<p class="no-results">No popular post found!</p>';
											}
											?>
										</div>
									</div>
								</div><!-- .row -->
							</div><!-- .container -->
						</div><!-- .result-container -->
					</div><!-- .search-container -->
				<?php endif; ?>
			</div><!-- .modal-body -->
    	</div><!-- .modal-content -->
  	</div><!-- .modal-dialog -->
</div><!-- .modal -->

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
<form action="<?php echo site_url('tours/all/') ?>" method="get" class="trek-trip-finder-form header-calendar-form mobile-calendar-form" id="trek-trip-finder-form">
	<input type="hidden" id="start_time" name="start_time">
	<input type="hidden" id="end_time" name="end_time">
</form>


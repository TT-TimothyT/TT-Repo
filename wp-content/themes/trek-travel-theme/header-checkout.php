<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<script>
		window.dataLayer = window.dataLayer || [];
	</script>
<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-KZZB2GJ');</script>
	<!-- End Google Tag Manager -->

	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/favicon-16x16.png">
<link rel="manifest" href="<?php echo esc_url( home_url( '/site.webmanifest' ) ); ?>">
<link rel="mask-icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

	<?php wp_body_open(); ?>

	<a href="#main" class="visually-hidden-focusable"><?php esc_html_e('Skip to main content', 'trek-travel-theme'); ?></a>

	<div id="wrapper">

		<header class="sticky-top bg-black header-main checkout-header">
			

			<nav id="header" class="navbar mb-0 navbar-expand-md<?php if (is_home() || is_front_page()) : echo ' home';
															endif; ?>">
				<div class="container">
					<a class="navbar-brand text-white" href="<?php echo esc_url(home_url()); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">

					<?php
						if(trek_isMobile()){
						?>
							<img class="navbar-brand__logo" src="<?php echo get_template_directory_uri(); ?>/assets/images/checkout/checkout-logo-mobile.svg" alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" />
						<?php
						} else {
						?>
							<img class="navbar-brand__logo" src="<?php echo get_template_directory_uri(); ?>/assets/images/checkout/checkout-logo-desktop.svg" alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" />
						<?php
						}
						?>						
					</a>


					

					<?php
					// Search and Account ////////////////////
					?>
					<div class="header-main__search-account d-flex align-items-center gap-5">
						
						<div class="contact-in-header bg-white rounded-circle d-block d-lg-none">
							<a class="d-flex align-items-center" href="tel:"><i class="bi bi-telephone m-auto"></i></a>
						</div>

						<div class="question-in-header bg-white rounded-circle d-block d-lg-none">
							<a class="d-flex align-items-center" target="_blank" href="<?php echo site_url('contact-us') ?>"><i class="bi bi-question m-auto"></i></a>
						</div>
						<a href="<?php echo site_url('contact-us') ?>" target="_blank" class="btn btn-md btn-light rounded-1 d-none d-lg-block btn-question">Questions?</a>
					</div>

				</div><!-- /.container -->
			</nav><!-- /#header -->
			<div id="autocomplete"></div>
		</header>
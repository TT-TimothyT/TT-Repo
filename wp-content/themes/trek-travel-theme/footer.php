<?php 
// echo tt_compare_trips_html(); 
?>
	<footer id="footer" class="text-white">
	<?php
		if (!is_page('register')) {
			get_template_part('tpl-parts/newsletter', 'signup');
		}
	?>

		<div class="divider"></div>
		<div class="container">
			<div class="row">
				<?php
				if (has_nav_menu('footer-menu')) : // See function register_nav_menus() in functions.php
					/*
							Loading WordPress Custom Menu (theme_location) ... remove <div> <ul> containers and show only <li> items!!!
							Menu name taken from functions.php!!! ... register_nav_menu( 'footer-menu', 'Footer Menu' );
							!!! IMPORTANT: After adding all pages to the menu, don't forget to assign this menu to the Footer menu of "Theme locations" /wp-admin/nav-menus.php (on left side) ... Otherwise the themes will not know, which menu to use!!!
						*/
					wp_nav_menu(
						array(
							'theme_location'  => 'footer-menu',
							'container'       => 'nav',
							'container_class' => 'col-lg-9',
							'fallback_cb'     => '',
							'items_wrap'      => '<ul class="menu d-lg-flex">%3$s</ul><div class="footerCurrencySelector"><label class="currency-label">Currency</label>'.do_shortcode('[woocommerce_currency_converter currency_display="select" currency_codes="USD, EUR, CAD, GBP, AUD"]').'</div>',
							//'fallback_cb'    => 'WP_Bootstrap4_Navwalker_Footer::fallback',
							'walker'          => new WP_Bootstrap4_Navwalker_Footer(),
						)
					);
				endif; ?>
				<div class="col-12 col-lg-3 recognised">
					<div class="row mx-0">
						<p class="menu-title">As Recognized By</p>
						<div class="row mx-0 recognised-by">
							<div class="col-2 px-0">
								<img class="img-item" src="<?php echo get_template_directory_uri(); ?>/assets/images/travel.png" />
							</div>
							<div class="col-2 px-0">
								<img class="img-item" src="<?php echo get_template_directory_uri(); ?>/assets/images/shape-travel.png" />
							</div>
							<div class="col-2 px-0">
								<img class="img-item" src="<?php echo get_template_directory_uri(); ?>/assets/images/bicycle.png" />
							</div>
							<div class="col-2 px-0">
								<img class="img-item" src="<?php echo get_template_directory_uri(); ?>/assets/images/tours.png" />
							</div>
							<div class="col-2 px-0">
								<img class="img-item" src="<?php echo get_template_directory_uri(); ?>/assets/images/50-tours.png" />
							</div>
						</div>
					</div>
					<div class="row mx-0 follow-us">
						<p class="menu-title">Follow Us</p>
						<div class="social-icons d-flex">
							<a href="https://www.facebook.com/trektravel" target="_blank"><img alt="facebook" src="<?php echo get_template_directory_uri(); ?>/assets/images/social/facebook.png" /></a>
							<a href="https://www.instagram.com/trektravel/" target="_blank"><img alt="instagram" src="<?php echo get_template_directory_uri(); ?>/assets/images/social/IG.png" /></a>
							<a href="https://twitter.com/TrekTravel" target="_blank"><img alt="twitter" src="<?php echo get_template_directory_uri(); ?>/assets/images/social/X-logo-white.png" /></a>
							<a href="https://www.youtube.com/@TrekTravel-Cycling-Vacations" target="_blank"><img alt="youtube" src="<?php echo get_template_directory_uri(); ?>/assets/images/social/youtube.png" /></a>
							<a href="https://www.pinterest.com/trektravel/" target="_blank"><img alt="pinterest" src="<?php echo get_template_directory_uri(); ?>/assets/images/social/pinterest.png" /></a>
						</div>
					</div>
				</div>
				<?php if (is_user_logged_in()) :
					if (is_active_sidebar('third_widget_area')) : ?>

						<div class="footer-content col-md-12">
							<?php
							dynamic_sidebar('third_widget_area');

							if (current_user_can('manage_options')) :
							?>
								<span class="edit-link"><a href="<?php echo esc_url(admin_url('widgets.php')); ?>" class="badge badge-secondary"><?php esc_html_e('Edit', 'trek-travel-theme'); ?></a></span><!-- Show Edit Widget link -->
						<?php
							endif;
						endif;
						?>
						</div>
					<?php
				endif;
					?>

			</div><!-- /.row -->
		</div><!-- /.container -->
		<div class="container-fluid copyright text-black">
			<!-- copyright -->
			<div class="container">
				<div class="row">
					<div class="col-12 col-lg-3">
						<p class="mb-1"><?php printf(esc_html__('&copy; %1$s %2$s. All rights reserved.', 'trek-travel-theme'), date_i18n('Y'), get_bloginfo('name', 'display')); ?></p>
					</div>
					<div class="col-12 col-lg-3">
						<a href="/privacy-policy/" class="terms-condition">Terms & Conditions</a> |
						<a href="/privacy-policy/" class="privacy-policy">Privacy Policy</a>
					</div>
				</div>
			</div>
		</div><!-- /copyright -->
		<?php if ( is_active_sidebar('tt_currency_converter_widget_area') ) : ?>
			<div id="fix-currency-converter-geolocation" style="display:none;">
				<?php dynamic_sidebar('tt_currency_converter_widget_area'); ?>
			</div>
		<?php endif; ?>
	</footer><!-- /#footer -->
	</div><!-- /#wrapper - open in header.php -->
	<?php echo trek_login_register_modal(); ?>

	<?php
	wp_footer();
	?>
	<!-- <script src='https://www.google.com/recaptcha/api.js'></script> -->
	<!-- <script src="https://www.google.com/recaptcha/api.js?render=6LfNqogpAAAAAEoQ66tbnh01t0o_2YXgHVSde0zV"></script> -->

	</body>

	</html>
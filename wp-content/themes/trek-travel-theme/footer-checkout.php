<footer id="checkout-footer" class="text-white">
		<div class="container-fluid copyright text-white">
			<!-- copyright -->
			<div class="container">
				<div class="row">
					<div class="col-12 col-md-3 checkout-footer-left">
						<p class="fw-normal fs-sm lh-sm"><?php printf(esc_html__('&copy; %1$s %2$s. All rights reserved.', 'trek-travel-theme'), get_bloginfo('name', 'display'), date_i18n('Y')); ?></p>
					</div>
					<div class="col-12 col-md-9 checkout-footer-right">
						<div class="checkout-footer-menu">
							<a class="fw-normal fs-sm lh-sm" href="/contact-us/">Contact Us</a>
							<a class="fw-normal fs-sm lh-sm" href="/cancellation-policy/">Cancellation Policy</a>
							<a class="fw-normal fs-sm lh-sm" href="/faq/">FAQs</a>
							<a class="fw-normal fs-sm lh-sm" href="/travel-protection/">Travel Protection</a>
						</div>
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

	<?php wc_get_template_part( 'checkout/roommate', 'popup' ); ?>

	<?php wc_get_template_part( 'checkout/private', 'popup' ); ?>

	<?php wc_get_template_part( 'checkout/travel', 'protection-popup' ); ?>

	</div><!-- /#wrapper - open in header.php -->
	<?php
	wp_footer();
	?>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<script src="https://www.google.com/recaptcha/api.js?render=6LfNqogpAAAAAEoQ66tbnh01t0o_2YXgHVSde0zV"></script>
	<script>
		const togglePassword = document
			.querySelector('#togglePassword');

		const password = document.querySelector('#InputPassword');

		togglePassword.addEventListener('click', () => {

			// Toggle the type attribute using
			// getAttribure() method
			const type = password
				.getAttribute('type') === 'password' ?
				'text' : 'password';

			password.setAttribute('type', type);

			// Toggle the eye and bi-eye icon
			togglePassword.classList.toggle('bi-eye');
		});

		// Example starter JavaScript for disabling form submissions if there are invalid fields
		(function() {
			'use strict'

			// Fetch all the forms we want to apply custom Bootstrap validation styles to
			var forms = document.querySelectorAll('.needs-validation')

			// Loop over them and prevent submission
			Array.prototype.slice.call(forms)
				.forEach(function(form) {
					form.addEventListener('submit', function(event) {
						if (!form.checkValidity()) {
							event.preventDefault()
							event.stopPropagation()
						}

						form.classList.add('was-validated')
					}, false)
				})
		})()
	</script>


	</body>

	</html>
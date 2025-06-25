document.addEventListener('DOMContentLoaded', function () {
    console.log('Trek Travel Theme Global Common JS Loaded');
        // Inject return URL into login and registration form
        jQuery(document).on('lity:open', function (event, instance) {
                const $opener = instance.opener();
                const returnUrl = $opener.data('return-url');

                if ($opener.attr('href') === '#login-register-modal') {
                        document.body.classList.add('lity-login-modal');

                        if (returnUrl) {
                                jQuery('#login-form input[name="http_referer"]').val(returnUrl);
                                jQuery('#login-register-modal .woocommerce-form-register input[name="http_referer"]').val(returnUrl);
                        }
                }
        });

	jQuery(document).on('lity:close', function () {
		document.body.classList.remove('lity-login-modal');
	});
});


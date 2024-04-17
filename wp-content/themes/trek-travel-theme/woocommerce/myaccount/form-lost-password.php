<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.2
 */

defined( 'ABSPATH' ) || exit;

$google_api_key = ( G_CAPTCHA_SITEKEY ? G_CAPTCHA_SITEKEY : '6LcwloIpAAAAAMR526emPgUfi-IxtNbdIT0eB0dP' );


do_action( 'woocommerce_before_lost_password_form' );
?>
<div class="row">
	<div class="offset-lg-4 col-lg-4 reset-form">
		<h2 class="reset-title"><?php _e( 'Reset password', 'trek-travel-theme' ); ?></h2>
		<form method="post" class="woocommerce-ResetPassword lost_reset_password needs-validation" novalidate>
		<p class="reset-p"><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Enter email address associated with your account and we will email you a link to reset your password.', 'trek-travel-theme' ) ); ?></p>
		<div class="form-group form-floating">
			<input type="email" class="input-text form-control reset-email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" name="user_login" placeholder="Email" id="InputEmail" value="<?php echo ( ! empty( $_POST['user_login'] ) ) ? esc_attr( wp_unslash( $_POST['user_login'] ) ) : ''; ?>" required />
			<label for="InputEmail" class="label-for">Email*</label>
			<div class="invalid-feedback">
				<img class="invalid-icon" />
				Please enter valid email address.
			</div>
        <div class="form-group my-4">
            <div class="g-recaptcha" data-sitekey="<?php echo $google_api_key; ?>"></div>
        </div>
		</div>
		<?php do_action( 'woocommerce_lostpassword_form' ); ?>
		<div class="form-group">
			<input type="hidden" name="wc_reset_password" value="true" />
			<button type="submit" class="btn btn-primary reset-submit" value="<?php esc_attr_e( 'Send reset link', 'trek-travel-theme' ); ?>"><?php esc_html_e( 'Send reset link', 'trek-travel-theme' ); ?></button>
		</div>
		<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
		<div class="form-group text-center register-link">
			<span>Don't have an account? <a href="<?php echo esc_url( site_url( 'register' ) ); ?>"><?php esc_html_e( 'Sign Up', 'trek-travel-theme' ); ?></a></span>
		</div>
		</form>
	</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Check if the current page URL contains the specific path
    if (window.location.pathname === '/my-account/lost-password/') {
        // Get the email parameter from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get('user_email');

        // Set the value of the email input field if the email parameter exists
        if (email) {
            document.getElementById('InputEmail').value = email;
        }
    }
});
document.addEventListener("DOMContentLoaded", function() {
    // Get the form element
    var lostPasswordForm = document.querySelector(".woocommerce-ResetPassword");
    // Get the current domain
    var currentDomain = window.location.hostname;
    // Add an event listener for form submission
    lostPasswordForm.addEventListener("submit", function(event) {
        var userEmailField = document.querySelector(".reset-email");
        var userEmail = userEmailField.value;
        // Set the email value in a cookie with the current domain
        setCookie("userEmail", userEmail, 1, currentDomain); // Use the current domain
    });
    // Function to set a cookie with domain
    function setCookie(name, value, days, domain) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        // Include the current domain when setting the cookie
        document.cookie = name + "=" + value + expires + "; path=/; domain=" + domain;
    }
});
document.addEventListener("DOMContentLoaded", function() {
    jQuery(document).ready(function() {
    setInterval(function() {
        var form = document.querySelector('.woocommerce-ResetPassword');
        if (form.classList.contains('was-validated')) {
            var submit = document.querySelector('.reset-submit');
            submit.removeAttribute('disabled');
        }
    }, 200);
    });
});
</script>
<?php
do_action( 'woocommerce_after_lost_password_form' );

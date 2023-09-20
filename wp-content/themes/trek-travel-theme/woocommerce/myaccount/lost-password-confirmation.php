<?php
/**
 * Lost password confirmation text.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/lost-password-confirmation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.9.0
 */

defined( 'ABSPATH' ) || exit;

wc_print_notice( esc_html__( 'Password reset email has been sent.', 'woocommerce' ) );
$email_cookie = isset($_COOKIE['userEmail']) ? $_COOKIE['userEmail'] : '';

?>

<?php do_action( 'woocommerce_before_lost_password_confirmation_message' ); ?>

<div class="row">
	<div class="offset-lg-4 col-lg-4 reset-form">
        <div id="resendLinkToast" class="toast align-items-center hide mb-4" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                Link sent
            </div>
        </div>
		<h2 class="reset-title"><?php _e( 'Check your inbox', 'trek-travel-theme' ); ?></h2>
		 <p class="reset-p">
            <?php
            $reset_message = esc_html__( 'An email has been sent to', 'woocommerce' );
            $reset_message .= ' ' . esc_html($email_cookie) . '.';
            $reset_message .= ' ' . esc_html__( 'Please check your email for a link to reset your password.', 'woocommerce' );
            echo $reset_message;
            ?>
        </p>
		
		<div class="form-group">
			<input type="hidden" name="wc_reset_password" value="true" />
			<a class="btn btn-primary reset-submit" href="<?php echo esc_url( site_url( 'login' ) ); ?>"><?php esc_html_e( 'Sign In', 'trek-travel-theme' ); ?></a>
		</div>
		<div class="form-group text-center register-link">
			<span>Didn't get an email? Check your spam folder or <a href="javascript:void(0)" id="resendLink"><?php esc_html_e( 'resend a link.', 'trek-travel-theme' ); ?></a></span>
		</div>		
	</div>
</div>
<?php do_action( 'woocommerce_after_lost_password_confirmation_message' ); ?>
<script>
    const resendClick = document.querySelector('#resendLink');    
    const toastMessage = document.querySelector('#resendLinkToast');
    resendClick.addEventListener('click', () => {
        toastMessage.classList.toggle('show');
            setTimeout(function () {
                toastMessage.classList.toggle('show');            
            }, 3000);
        });
	document.addEventListener("DOMContentLoaded", function() {
    // Get the stored email cookie
    var storedEmail = getCookie("userEmail");

    // Display the email in the confirmation message
    var emailConfirmationMessage = document.querySelector(".reset-p");
    if (storedEmail) {
        emailConfirmationMessage.innerHTML = emailConfirmationMessage.innerHTML.replace("marywilson@gmail.com", storedEmail);
    }

    // Remove the stored email cookie
    deleteCookie("userEmail");

    // Function to delete a cookie
    function deleteCookie(name) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;';
    }

    // Function to get a cookie's value
    function getCookie(name) {
        var nameEQ = name + "=";
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            while (cookie.charAt(0) === ' ') {
                cookie = cookie.substring(1, cookie.length);
            }
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length, cookie.length);
            }
        }
        return null;
    }
});
</script>

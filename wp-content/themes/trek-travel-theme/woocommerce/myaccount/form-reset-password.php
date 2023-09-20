<?php

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
$google_api_key = ( G_CAPTCHA_SITEKEY ? G_CAPTCHA_SITEKEY : '6LfJg7MiAAAAAITw-hl0U0r2E8gSGUimzUh8-9Q0' );
?>

<div class="row">
      
    <div class="offset-lg-4 col-lg-4 form_lost_reset_password">
    	<h4><?php esc_html_e('Create a new password', 'trek-travel-theme'); ?></h4>
			<form method="post" name="email_password_reset" class="woocommerce-ResetPassword lost_reset_password needs-validation" novalidate>

				<p class="fw-normal fs-lg lh-lg"><?php echo apply_filters( 'woocommerce_reset_password_message', esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit ipsum dolor sit amet.', 'woocommerce' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>


				<div class="form-group my-4">
					<div class="form-floating flex-grow-1">
						<input type="password" class="input-text form-control" name="password_1" placeholder="Password" id="password_1" pattern="^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?!.*\s).{8,}$"  required />
						<label for="password_1" class="label-for">New Password</label>
						<span class="password-eye px-2"><i class="bi bi-eye-slash" id="togglePassword2"></i></span>
						<div class="invalid-feedback">
							<img class="invalid-icon" />
							Please enter valid password.
						</div>
					</div>

					<div id="passwordHelpBlock" class="form-text fs-xs lh-xs">
						Password must be at least 8 characters long, no spaces, and must contain one each of the following: one digit(0-9), one lowercase letter(a-z), and one uppercase letter (A-Z).
					</div>
				</div>

				<div class="form-group my-4">
					<div class="form-floating flex-grow-1">
						<input type="password" class="input-text form-control" name="password_2" placeholder="Password" id="password_2" pattern="^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?!.*\s).{8,}$" required />
						<label for="password_2" class="label-for">Confirm New Password</label>
						<span class="password-eye px-2"><i class="bi bi-eye-slash" id="togglePassword3"></i></span>
						<div class="invalid-feedback">
							<img class="invalid-icon" />
							Please enter valid and same password as above.
						</div>
					</div>
				</div>

				<div class="form-group my-4">
					<div class="g-recaptcha" data-sitekey="<?php echo $google_api_key; ?>"></div>
				</div>

				<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
				<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

				<div class="clear"></div>

				<?php do_action( 'woocommerce_resetpassword_form' ); ?>

				<p class="woocommerce-form-row form-row">
					<input type="hidden" name="wc_reset_password" value="true" />
					<button type="submit" class="btn btn-primary reset-submit w-100" value="<?php esc_attr_e( 'Reset password', 'woocommerce' ); ?>"><?php esc_html_e( 'Reset password', 'woocommerce' ); ?></button>
				</p>

				<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>

			</form>
    </div>
</div>
<?php
do_action( 'woocommerce_after_reset_password_form' );


<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

<div class="u-columns col2-set" id="customer_login">

	<div class="u-column1 col-1">

<?php endif; ?>

<div class="row">
      <div class="offset-lg-4 col-lg-4 login-form">
         <h2 class="login-title"><?php esc_html_e( 'Sign In', 'trek-travel-theme' ); ?></h2>
         <form class="woocommerce-form woocommerce-form-login login needs-validation" method="post" novalidate>
            <?php do_action( 'woocommerce_login_form_start' ); ?>
            
            <div class="form-group form-floating">
               <input type="text" class="input-text form-control" name="username" placeholder="Email" id="InputEmail" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required />
               <label for="InputEmail" class="label-for">Email*</label>
               <div class="invalid-feedback">
                  <img class="invalid-icon" />
                  Please enter valid email address.
               </div>
            </div>
            
            <div class="form-group">
               <div class="form-floating password-div flex-grow-1">
                  <input type="password" class="input-text form-control" name="password" placeholder="Password" id="InputPassword" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" value="<?php echo ( ! empty( $_POST['password'] ) ) ? esc_attr( wp_unslash( $_POST['password'] ) ) : ''; ?>" required />
                  <label for="password" class="label-for">Password*</label>
                  <span class="password-eye px-2"><i class="bi bi-eye-slash" id="togglePassword"></i></span>
                  <div class="invalid-feedback">
                     <img class="invalid-icon" />
                     Please enter valid password.
                  </div>
               </div>   
            </div>
            <div class="form-group forgot-pwd">
               <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'trek-travel-theme' ); ?></a>
            </div>
            <?php do_action( 'woocommerce_login_form' ); ?>
            <div class="form-group">
               <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
               <button type="submit" class="woocommerce-button button woocommerce-form-login__submit login-submit btn btn-primary" name="login" value="<?php esc_attr_e( 'Sign in', 'trek-travel-theme' ); ?>"><?php esc_html_e( 'Sign in', 'trek-travel-theme' ); ?></button>
            </div>
            <div class="form-group register-link">
               <span>Don't have an account? <a href="<?php echo esc_url( site_url( 'register' ) ); ?>"><?php esc_html_e( 'Sign Up', 'trek-travel-theme' ); ?></a></span>
            </div>
            <?php do_action( 'woocommerce_login_form_end' ); ?>
         </form>
      </div>
   </div>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

	</div>

	<div class="u-column2 col-2">

		<h2><?php esc_html_e( 'Register', 'woocommerce' ); ?></h2>

		<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
				</p>

			<?php endif; ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
				</p>

			<?php else : ?>

				<p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>

			<?php endif; ?>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<p class="woocommerce-form-row form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
			</p>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>

	</div>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

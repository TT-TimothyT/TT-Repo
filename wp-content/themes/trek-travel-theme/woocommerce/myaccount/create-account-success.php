<!-- TREK-311 -->
<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="row create-account-success">	
		<h4 class="fw-semibold"><?php _e( 'Success!', 'trek-travel-theme' ); ?></h4>		
		<p class="fw-normal fs-lg lh-lg"><?php echo apply_filters( 'woocommerce_create_account_success_message', esc_html__( 'Your account is activated! You can now sign in using your email and password.', 'trek-travel-theme' ) ); ?></p>
		<a class="btn btn-primary rounded-1 w-100" href="<?php echo esc_url(site_url('login')); ?>"><?php esc_html_e('Sign In', 'trek-travel-theme'); ?></a>	
</div>
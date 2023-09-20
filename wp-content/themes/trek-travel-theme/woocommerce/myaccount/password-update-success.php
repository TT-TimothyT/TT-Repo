<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="row password-update-success">	
		<h4 class="fw-semibold"><?php _e( 'Password updated', 'trek-travel-theme' ); ?></h4>		
		<p class="fw-normal fs-lg lh-lg"><?php echo apply_filters( 'woocommerce_password_update_success_message', esc_html__( 'Your password has been successfully changed. You can now sign into your account.', 'trek-travel-theme' ) ); ?></p>
		<a class="btn btn-primary rounded-1 w-100" href="<?php echo esc_url(site_url('login')); ?>"><?php esc_html_e('Sign In', 'trek-travel-theme'); ?></a>	
</div>
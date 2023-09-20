<?php
$userInfo = wp_get_current_user();
$subscriptions = get_user_meta($userInfo->ID, 'globalsubscriptionstatus', true);
$catalog = get_user_meta($userInfo->ID, 'custentity_addtotrektravelmailinglist', true);
$contactmethod = get_user_meta($userInfo->ID, 'custentity_contactmethod', true);
?>
<div class="container communication-preferences my-4">
	<div class="row mx-0 flex-column flex-lg-row">
		<div class="col-lg-6 medical-information__back order-1 order-lg-0">
			<a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
		</div>
		<div class="col-lg-6 d-flex dashboard__log">
			<p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
			<a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
		</div>
	</div>
	<div id="communication-preferences-responses"></div>
	<div class="row mx-0">
		<div class="col-lg-12">
			<h3 class="communication-preferences__title fw-semibold">Communication Preferences</h3>
		</div>
	</div>
	<div class="row mx-0">
		<div class="col-lg-10">
			<div class="card communication-preferences__card rounded-1">
				<h5 class="fw-semibold">Subscriptions</h5>
				<form name="trek-communication-preferences" method="post">
					<div class="communication-preferences__newsletter">
						<p class="fw-bold fs-lg lh-lg">Trek e-Newsletter</p>
						<p class="fw-normal fs-md lh-md">Subscribe to receive the latest Trek Travel news and updates right into your inbox.</p>
						<div class="form-check form-switch my-4">
							<!-- default unchecked switch below -->
							<input name="globalsubscriptionstatus" <?php if( $subscriptions == 1 ) echo "checked"; ?> class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" value="Yes">
							<!-- checked switch below -->
							<!-- <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" checked> -->
							<label class="form-check-label fw-normal fs-md lh-md" for="flexSwitchCheckDefault"><?php echo $subscriptions == 1 ? "Subscribed" : " Not subscribed"; ?></label>
						</div>
						<p class="fw-normal fs-xs lh-xs info">By subscribing to our eNewsletter, you agree to receive marketing materials from Trek Travel and its affiliate Trek Bicycles. Your data is stored in the United States. View our <a target="_blank" href="<?php echo site_url('privacy-policy/'); ?>">privacy policy</a>.</p>
					</div>
					<hr>
					<div class="communication-preferences__mailing-list">
						<p class="fw-bold fs-lg lh-lg">Mailing List</p>
						<p class="fw-normal fs-md lh-md">Subscribe to get a printed copy of our latest Trek Travel catalog. It's FREE!</p>
						<div class="form-check form-switch my-4">
							<!-- default checked switch below -->
							<input name="custentity_addtotrektravelmailinglist" <?php if( $catalog == 1 ) echo "checked"; ?> class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" value="1">
							<label class="form-check-label fw-normal fs-md lh-md" for="flexSwitchCheckChecked"><?php echo $catalog == 1 ? "Subscribed" : " Not subscribed"; ?></label>
						</div>
						<!-- <div class="add-mailing-address">
							<div class="form-check form-switch my-4">
								<input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled>
								<label class="form-check-label" for="flexSwitchCheckDisabled">Not subscribed</label>
							</div>
							<p class="fw-normal fs-xs lh-xs">Please add a <a href="<?php echo site_url('my-account/edit-address/shipping'); ?>">mailing address</a> to your account in order to subscribe.</p>
						</div> -->
					</div>
					<div class="communication-preferences__contact-method my-4">
						<h5 class="fw-semibold">How can we contact you?</h5>
						<div class="col-md px-0">
							<div class="form-floating">
								<select name="custentity_contactmethod" id="communication-preferences_method" class="form-select" autocomplete="address-level1" data-input-classes="" data-label="Contact Method" tabindex="-1" aria-hidden="true">
									<option value="1" <?php if( $contactmethod == 1 ) echo "selected"; ?>>Phone</option>
									<option value="2" <?php if( $contactmethod == 2 ) echo "selected"; ?>>Email</option>
									<option value="6" <?php if( $contactmethod == 6 ) echo "selected"; ?>>Text</option>
								</select>
								<label for="communication-preferences_address_2">Preferred Method of Contact</label>
							</div>
						</div>
					</div>

					<div class="communication-preferences__button d-flex align-items-lg-center">
						<div class="d-flex align-items-center communication-preferences__flex">
							<button type="submit" class="btn btn-lg btn-primary fs-md lh-md communication-preferences__save">Save</button>
							<a href="<?php echo site_url('my-account/'); ?>" class="communication-preferences__cancel">Cancel</a>
						</div>
					</div>
					<?php wp_nonce_field( 'edit_communication_preferences_action', 'edit_communication_preferences_nonce' ); ?>
				</form>
			</div>
		</div>
	</div>
</div>
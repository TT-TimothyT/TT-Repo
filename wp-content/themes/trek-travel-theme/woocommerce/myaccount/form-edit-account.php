<?php
defined('ABSPATH') || exit;
do_action('woocommerce_before_edit_account_form');
$userInfo = wp_get_current_user();
if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'save_account_details_new' ){
	$fname = isset($_REQUEST['account_first_name']) ? $_REQUEST['account_first_name'] : '';
	$lname = isset($_REQUEST['account_last_name']) ? $_REQUEST['account_last_name'] : '';
	$email = isset($_REQUEST['account_email']) ? $_REQUEST['account_email'] : '';
	$phone = isset($_REQUEST['account_phone']) ? $_REQUEST['account_phone'] : '';
	$dob = isset($_REQUEST['account_dob']) ? $_REQUEST['account_dob'] : '';
	$gender = isset($_REQUEST['account_gender']) ? $_REQUEST['account_gender'] : '';
	ob_start();
	wp_update_user(
		array(
			'ID' => $userInfo->ID,
			'first_name' => $fname,
			'last_name' => $lname
		)
	);
	ob_get_clean();
	if( $dob ){
		update_user_meta($userInfo->ID, 'custentity_birthdate', $dob);
	}
	if( $gender ){
		update_user_meta($userInfo->ID, 'custentity_gender', $gender);
	}
	if( $phone ){
		update_user_meta($userInfo->ID, 'custentity_phone_number', $phone);
		update_user_meta($userInfo->ID, 'billing_phone', $phone);
	}
 	ob_start();
    echo '<div class="woocommerce-message woocommerce-message--success alert-success">Your information has been successfully updated.</div>';
    $success_message = ob_get_clean();
	as_schedule_single_action(time(), 'tt_cron_syn_usermeta_ns', array( $userInfo->ID, '[Save account details]' ));
}
// $first_name = get_user_meta($userInfo->Id, 'shipping_first_name', true);
// $last_name = get_user_meta($userInfo->Id, 'shipping_last_name', true);
$dob    = get_user_meta($userInfo->ID, 'custentity_birthdate', true);
$gender = get_user_meta($userInfo->ID, 'custentity_gender', true);
$phone  = get_user_meta($userInfo->ID, 'custentity_phone_number', true);

?>

<div class="container my-account-edit px-0">
	<div class="row mx-0 flex-column flex-lg-row">
        <div class="col-lg-6 personal-information__back order-1 order-lg-0">
            <a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
        </div>
        <div class="col-lg-6 d-flex dashboard__log">
            <p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
            <a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
        </div>
    </div>

	<div id="personal-information-responses"></div>
    <div id="success-message-placeholder"><?php echo $success_message; ?>
	</div>
    <div class="row mx-0">
		<div class="col-lg-12">
			<h3 class="my-account-edit__title fw-semibold">Personal Information</h3>
		</div>
	</div>
	<div class="row mx-0">
		<div class="col-lg-10">
            <div class="card my-account-edit__card rounded-1">
				<form class="woocommerce-EditAccountForm edit-account needs-validation" novalidate action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>
					<?php do_action('woocommerce_edit_account_form_start'); ?>
					<div class="edit-account-form">
						<fieldset>
							<div class="row mx-0">
								<div class="col-md px-3">
									<div class="form-floating">
										<input type="text" class="input-text form-control" name="account_first_name" placeholder="First name" id="account_first_name" value="<?php echo esc_attr($userInfo->first_name); ?>" required />
										<label for="account_first_name" class="label-for">First name</label>
									</div>
								</div>
								<div class="col-md px-3">
									<div class="form-floating">
										<input type="text" class="input-text form-control" name="account_last_name" placeholder="Last name" id="account_last_name" value="<?php echo esc_attr($userInfo->last_name); ?>" required />
										<label for="account_last_name" class="label-for">Last name</label>
									</div>
								</div>
							</div>

							<div class="row mx-0">
								<div class="col-md px-3">
									<div class="form-floating">
										<input type="email" class="input-text form-control" name="account_email" placeholder="Email" id="account_email" value="<?php echo esc_attr($userInfo->user_email); ?>" required />
										<label for="account_email" class="label-for">Email</label>
									</div>
								</div>
								<div class="col-md px-3">
									<div class="form-floating">
										<input type="tel" class="input-text form-control" name="account_phone" placeholder="Phone" id="account_phone" value="<?php echo $phone; ?>" pattern="^\d{10}$" required />
										<label for="account_phone" class="label-for">Phone</label>
										<div class="invalid-feedback">
											<img class="invalid-icon" />
											Please enter valid phone number.
										</div>
									</div>
								</div>
								
							</div>

							<div class="row mx-0">
								<div class="col-md px-3">
									<div class="form-floating">
										<input type="text" name="account_dob" class="input-text form-control" id="account_dob" placeholder="Date of Birth" value="<?php echo $dob; ?>">
										<label for="account_dob">Date of Birth</label>
										<div class="invalid-feedback invalid-age dob-error"><img class="invalid-icon" /> Age must be 16 years old or above, Please enter correct date of birth.</div>
										<div class="invalid-feedback invalid-min-year dob-error"><img class="invalid-icon" /> The year must be greater than 1900, Please enter correct date of birth.</div>
										<div class="invalid-feedback invalid-max-year dob-error"><img class="invalid-icon" /> The year cannot be in the future, Please enter the correct date of birth.</div>
									</div>
								</div>
								
								<div class="col-md px-3">
									<div class="form-floating">
										<select name="account_gender" id="account_gender" class="form-select" autocomplete="address-level1" data-input-classes="" data-label="Gender" tabindex="-1" aria-hidden="true" required>
											<option value="" <?php echo ( empty( $gender ) ? 'selected' : '' ); ?>>Select Gender</option>
											<option value="1" <?php echo ($gender == 1 ? 'selected' : ''); ?>>Male</option>
											<option value="2" <?php echo ($gender == 2 ? 'selected' : ''); ?>>Female</option>
										</select>
										<label for="account_gender">Gender</label>
										<div class="invalid-feedback">
											<img class="invalid-icon" />
											Please select gender.
										</div>
									</div>
								</div>
							</div>							
						</fieldset>
					</div>
					<?php do_action('woocommerce_edit_account_form'); ?>
					<div class="form-buttons d-flex medical-information__buttons">
                        <div class="form-group align-self-center">
							<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
                            <button type="submit" class="btn btn-lg btn-primary w-100 medical-information__save rounded-1" name="save_account_details"><?php esc_html_e('Save', 'trek-travel-theme'); ?></button>
							<input type="hidden" name="action" value="save_account_details_new">
                        </div>
                        <div class="fs-md lh-md fw-medium text-center align-self-center">
                            <a href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>">Cancel</a>
                        </div>
                    </div>
					
					<?php do_action('woocommerce_edit_account_form_end'); ?>
				</form>
			</div>
		</div>
	</div>
</div>
<?php do_action('woocommerce_after_edit_account_form'); ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Check if the success message was displayed in the current session
        const successMessageDisplayed = sessionStorage.getItem("successMessageDisplayed");

        // Display the success message only if it wasn't displayed in this session
        if (!successMessageDisplayed) {
            const successMessage = document.querySelector("#success-message-placeholder .alert-success");
            if (successMessage) {
                successMessage.style.display = "block";
                
                // Mark the success message as displayed in this session
                sessionStorage.setItem("successMessageDisplayed", "true");
            }
        }
    });
</script>

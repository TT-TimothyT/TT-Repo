<?php 
$userInfo = wp_get_current_user();
?>
<div class="container my-account-password-reset my-4">
    <div class="row mx-0 flex-column flex-lg-row">
        <div class="col-lg-6 medical-information__back order-1 order-lg-0">
            <a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
        </div>
		<div class="col-lg-6 d-flex dashboard__log">
			<p class="fs-lg lh-lg fw-bold">Hi <?php echo $userInfo->first_name; ?>!</p>
			<a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
		</div>
	</div>
    <div id="change-password-responses"></div>
    <div class="row mx-0">
		<div class="col-lg-12">
			<h3 class="dashboard__title fw-semibold">Password</h3>
		</div>
	</div>
    <div class="row mx-0">
		<div class="col-lg-10">
            <div class="card dashboard__card rounded-1">
                <form name="trek-change-password" method="post" class="needs-validation" novalidate>
                    <div class="password-reset-form">
                        <fieldset>				
                            <div class="form-group my-4">
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="input-text form-control" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" name="password_current" placeholder="Password" id="password_current" value="<?php echo (!empty($_POST['password'])) ? esc_attr(wp_unslash($_POST['password'])) : ''; ?>" required />
                                <label for="password_current" class="label-for">Enter Current Password</label>
                                <span class="password-eye px-2"><i class="bi bi-eye-slash" id="togglePassword1"></i></span>
                                <div class="invalid-feedback">
                                    <img class="invalid-icon" />
                                    Please enter valid password.
                                </div>
                            </div>
                            </div>

                            <div class="form-group my-4">
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="input-text form-control" name="password_1" placeholder="Password" id="password_1" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"  required />
                                <label for="password_1" class="label-for">Enter New Password</label>
                                <span class="password-eye px-2"><i class="bi bi-eye-slash" id="togglePassword2"></i></span>
                                <div class="invalid-feedback">
                                    <img class="invalid-icon" />
                                    Please enter valid password.
                                </div>
                            </div>
                            <p class="fw-normal fs-xs lh-xs">Password must be at least 8 characters long, no spaces, and must contain one each of the following: one digit(0-9), one lowercase letter(a-z), and one uppercase letter (A-Z).</p>
                            </div>

                            <div class="form-group my-4">
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="input-text form-control" name="password_2" placeholder="Password" id="password_2" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required />
                                <label for="password_2" class="label-for">Confirm New Password</label>
                                <span class="password-eye px-2"><i class="bi bi-eye-slash" id="togglePassword3"></i></span>
                                <div class="invalid-feedback">
                                    <img class="invalid-icon" />
                                    Please enter same as new password.
                                </div>
                            </div>
                            <p class="fw-normal fs-xs lh-xs">Both passwords must match</p>
                            </div>
                        </fieldset>
                    </div>
                    <div class="form-buttons d-flex">
                        <div class="form-group my-4 align-self-center me-4">
                            <button type="submit" class="btn btn-primary w-100" name="change-password"><?php esc_html_e('Change password', 'trek-travel-theme'); ?></button>
                        </div>
                        <div class="fs-md my-4 lh-md fw-normal text-center align-self-center">
                            <span><a href="/login">Cancel</a></span>
                        </div>
                    </div>
                    <div id="passwordUpdatedToast" class="toast align-items-center hide mb-4" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-body">
                            <i class="bi bi-check-circle me-2"></i>
                            Password successfully updated!
                        </div>
                    </div>
                    <?php wp_nonce_field( 'reset_password_action', 'reset_password_nonce' ); ?>
                </form>
            </div>
        </div>
    </div>
</div>
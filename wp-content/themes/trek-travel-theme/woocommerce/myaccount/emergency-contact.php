<?php
$user = wp_get_current_user();
$contact_fname = get_user_meta($user->ID, 'custentity_emergencycontactfirstname', true);
$contact_lname = get_user_meta($user->ID, 'custentityemergencycontactlastname', true);
$contact_phone = get_user_meta($user->ID, 'custentity_emergencycontactphonenumber', true);
$contact_rel = get_user_meta($user->ID, 'custentity_emergencycontactrelationship', true);
?>
<div class="container emergency-contact px-0">
    <div id="contact-information-responses"></div>
    <div class="row mx-0">
        <div class="col-lg-12">
            <h3 class="dashboard__title fw-semibold">Emergency Contact</h3>
        </div>
    </div>
    <form name="trek-contact-information" method="post">
        <div class="row mx-0">
            <div class="col-lg-10">
                <div class="card emergency-contact__card rounded-1">
                    <div class="row mx-0 guest-checkout__primary-form-row">
                        <div class="col-md px-0">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="emergency_contact_first_name" id="emergency_contact_first_name" placeholder="First Name" value="<?php echo $contact_fname; ?>" autocomplete="given-name" required>
                                <label for="emergency_contact_first_name">First Name</label>
                                <div class="invalid-feedback">
                                    <img class="invalid-icon" />
                                    This field is required.
                                </div>
                            </div>
                        </div>
                        <div class="col-md px-0">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="emergency_contact_last_name" id="emergency_contact_last_name" placeholder="Last Name" value="<?php echo $contact_lname; ?>" autocomplete="family-name" required>
                                <label for="emergency_contact_last_name">Last Name</label>
                                <div class="invalid-feedback">
                                    <img class="invalid-icon" />
                                    This field is required.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mx-0 guest-checkout__primary-form-row">
                        <div class="col-md px-0">
                            <div class="form-floating">
                                <input type="tel" class="form-control" name="emergency_contact_phone" id="emergency_contact_phone" placeholder="Phone Number" value="<?php echo $contact_phone; ?>" autocomplete="given-name" required>
                                <label for="emergency_contact_phone">Phone Number</label>
                                <div class="invalid-feedback">
                                    <img class="invalid-icon" />
                                    This field is required.
                                </div>
                            </div>
                        </div>
                        <div class="col-md px-0">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="emergency_contact_relationship" id="emergency_contact_relationship" placeholder="Relationship to You" value="<?php echo $contact_rel; ?>" required>
                                <label for="emergency_contact_address_2">Relationship to You</label>
                            </div>
                        </div>
                    </div>
                    <div class="emergency-contact__button d-flex align-items-lg-center">
                        <div class="d-flex align-items-center emergency-contact__flex">
                            <button type="submit" class="btn btn-lg btn-primary fs-md lh-md emergency-contact__save">Save</button>
                            <a href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>">Cancel</a>
                        </div>
                    </div>
                    <?php wp_nonce_field( 'edit_contact_info_action', 'edit_user_contact_info_nonce' ); ?>
                </div>
            </div>
        </div>
    </form>
</div>
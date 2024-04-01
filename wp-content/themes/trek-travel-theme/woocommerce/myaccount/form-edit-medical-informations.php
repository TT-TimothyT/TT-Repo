<?php 
$medical_fields = array(
    'custentity_medications' => '1. Are you currently taking any medications?',
    'custentity_medicalconditions' => '2. Do you have any medical conditions?',
    'custentity_allergies' => '3. Do you have any allergies?',
    'custentity_dietaryrestrictions' => '4. Do you have any dietary restrictions?'
);
$userInfo = wp_get_current_user();
?>
<div class="container medical-information my-4">
    <div class="row mx-0 flex-column flex-lg-row">
        <div class="col-lg-6 medical-information__back order-1 order-lg-0">
            <a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
        </div>
		<div class="col-lg-6 d-flex dashboard__log">
			<p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
			<a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
		</div>
	</div>
    
    <div id="medical-information-responses"></div>
    <div class="row mx-0">
		<div class="col-lg-12">
			<h3 class="dashboard__title fw-semibold">Medical Information</h3>
		</div>
	</div>
    <div class="row mx-0">
		<div class="col-lg-10">
            <div class="card dashboard__card rounded-1">
                <form name="trek-medical-information" method="post">
                    <div class="password-reset-form medical_items">
                        <fieldset>	
                            <?php
                            $medical_field_html = '';
                            if( $medical_fields ){
                                foreach( $medical_fields as $medical_key=>$medical_field){
                                    $medical_val = get_user_meta( get_current_user_id() , $medical_key, true );
                                    $is_medical = ( $medical_val && 'none' != strtolower( $medical_val ) ? 'yes' : 'no' );
                                    $toggleTextClass = ( $medical_val && 'none' != strtolower( $medical_val ) ? 'style="display:block;"' : 'style="display:none;"' );
                                    $medical_field_html .= '<div class="form-group medical-information__item medical_item">
                                        <div class="flex-grow-1">
                                            <p class="fw-medium fs-lg lh-lg mb-4 mb-lg-5">'.$medical_field.'</p>
                                            <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input" type="radio" name="'.$medical_key.'[boolean]" id="inlineRadioYes'.$medical_key.'" value="yes" '.( $is_medical == 'yes' ? 'checked' : '' ).'>
                                            <label class="form-check-label" for="inlineRadioYes'.$medical_key.'">Yes</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-0 ">
                                            <input class="form-check-input" type="radio" name="'.$medical_key.'[boolean]" id="inlineRadioNo'.$medical_key.'" value="no" '.( $is_medical == 'no' ? 'checked' : '' ).'>
                                            <label class="form-check-label" for="inlineRadioNo'.$medical_key.'">No</label>
                                            </div>
                                            <textarea name="'.$medical_key.'[value]" placeholder="Please tell us more" maxlength="450" class="form-control rounded-1 mt-4" '.$toggleTextClass.'>' . ( 'none' != strtolower( $medical_val ) ? $medical_val : '') . '</textarea>
                                        </div>
                                    </div>';
                                }
                                echo $medical_field_html;
                            } 
                            ?>
                        </fieldset>
                    </div>
                    <div class="form-buttons d-flex medical-information__buttons">
                        <div class="form-group align-self-center">
                            <button type="submit" class="btn btn-lg btn-primary w-100 medical-information__save rounded-1" name="medical-information"><?php esc_html_e('Save', 'trek-travel-theme'); ?></button>
                        </div>
                        <div class="fs-md lh-md fw-medium text-center align-self-center">
                            <a href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>">Cancel</a>
                        </div>
                    </div>
                    <?php wp_nonce_field( 'edit_medical_info_action', 'edit_user_medical_info_nonce' ); ?>
                </form>
                <div class="toast align-items-center my-4 medical-info-toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body"></div>
                        <button type="button" class="btn-close me-2 m-auto bg-transparent" data-bs-dismiss="toast" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once("emergency-contact.php"); ?>
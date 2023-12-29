// Define a custom event "occupant_popup_html_ready" that will indicate whether the html of addOccupantsModal is generated and ready for use.
const occupantPopUpHtmlReadyEvent = new Event("occupant.popup.html.ready");

function tt_fetch_display_order_emails() {
  if (trek_JS_obj && trek_JS_obj.order_id && trek_JS_obj.is_order_received == true) {
    var action = 'tt_display_order_emails_action';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: "action=" + action+"&order_id="+trek_JS_obj.order_id,
      dataType: 'json',
      beforeSend : function(){
      },
      success: function (response) {
        jQuery('#wc-order-emails').html(response.guest_emails);
      },
      complete: function(){
        //jQuery('.checkout-payment__pre-address').unblock();
      }
    });
  }
}
function tt_change_checkout_step(targetStep = '') {
  var is_bikeForm = true;
  if( targetStep == 22 ){
    is_bikeForm = false;
    targetStep = 2;
  }
  if (targetStep) {
    jQuery(`div.tab-pane`).removeClass('active show');
    jQuery(`.checkout-timeline__progress-bar li.nav-item`).removeClass('active');
    jQuery(`.checkout-timeline__progress-bar li.nav-item[data-step="${targetStep}"]`).addClass('active');
    jQuery(`div.tab-pane[data-step="${targetStep}"]`).addClass('active show');
    jQuery('input[name="step"]').val(targetStep);
    var redirectURL = new window.URL(document.location);
    redirectURL.searchParams.set("step", targetStep);
    window.history.replaceState(null, null, redirectURL.toString());
    handleProgressBar(targetStep);
    if( is_bikeForm == false ){
      jQuery('.checkout-bikes__edit-room-info-btn').trigger('click');
    }
  }
}
/**
 * Function that set complete state for sections in Post Booking Checklist page.
 * (my-trip-checklist.php)
 */
function prebookingChecklistValidate() {
  var isFirstMedicalLoad = jQuery('input[name="custentity_medical_info_first_load"]').length > 0;
  var isMedicalInfoSectionComplete = true;
  var totalEmerFields = jQuery('.emergency_validation_inputs').length;
  var totalGearFields = jQuery('.gear_validation_inputs').length;
  var totalPassFields = jQuery('.passport_validation_inputs').length;
  var emergency_itemCount = 0;
  var gear_itemCount = 0;
  var passport_itemCount = 0;
  // Check medical information section for complete state.
  jQuery('.medical_validation_checkboxes').each(function () {
    if (jQuery(this).is(':checked')) {
      var medicalInfoValue = jQuery(this).val();
      var medicalInfoMoreInfoField = jQuery(this).parents('.medical-information__item').find('textarea');
      // If Has a flag for first load, mark medical info section as uncomplete.
      if(isFirstMedicalLoad){
        isMedicalInfoSectionComplete = false;
      }

      // Change textarea value, based on radio value selection.
      if( 'yes' == medicalInfoValue ){
        // Clear 'none' value and show the placeholder.
        medicalInfoMoreInfoField.val('');
      } else if( 'no' == medicalInfoValue ) {
        // Set default 'none' value for 'no' option.
        medicalInfoMoreInfoField.val('none');
      }
    }
  });
  jQuery('.emergency_validation_inputs').each(function () {
    if (jQuery(this).val() != '') {
      emergency_itemCount++;
    }
  });
  jQuery('.gear_validation_inputs').each(function () {
    if (jQuery(this).val() != '') {
      gear_itemCount++;
    }
  });
  jQuery('.passport_validation_inputs').each(function () {
    if (jQuery(this).val() != '') {
      passport_itemCount++;
    }
  });
  if (isMedicalInfoSectionComplete) {
    jQuery('.medical_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
  } else {
    jQuery('.medical_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/error2.png');
  }
  if (totalEmerFields == emergency_itemCount) {
    jQuery('.emergency_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
  } else {
    jQuery('.emergency_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/error2.png');
  }
  if (totalGearFields == gear_itemCount) {
    jQuery('.gear_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
  } else {
    jQuery('.gear_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/error2.png');
  }
  if (totalPassFields == passport_itemCount) {
    jQuery('.passport_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
  } else {
    jQuery('.passport_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/error2.png');
  }

  // Check Bike Section for complete state.
  let bikeModelSelected = false;

  // Loop all bike models.
  jQuery( '.checkout-bikes-section .bike_validation_inputs' ).each( function() {
    if( jQuery( this ).is( ':checked' ) ) {
      // Has selected bike.
      bikeModelSelected = true;
      return false; // breaks.
    }
  })

  let bikeSizeSelected = false;

  // Check bike size select/options index if gt from 0, we has selected size. The 0 option is 'Select bike size'.
  if( jQuery( '.checkout-bikes-section .bike_validation_select' ).prop( 'selectedIndex' ) > 0 ) {
    bikeSizeSelected = true;
  }

  // Set Bike Section complete state.
  if ( bikeModelSelected && bikeSizeSelected ) {
    jQuery( '.bike_checklist-btn img' ).attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
  } else {
    // Set Bike Section as not completed yet.
    jQuery( '.bike_checklist-btn img' ).attr('src', trek_JS_obj.temp_dir + '/assets/images/error2.png');
  }
}
function tripCapacityValidation(is_return = true) {
  jQuery('input#wc-cybersource-credit-card-tokenize-payment-method').prop('checked', true);
  if (trek_JS_obj && trek_JS_obj.is_checkout == true) {
    var no_of_guests = jQuery('input[name="no_of_guests"]').val();
    if (trek_JS_obj.trip_booking_limit.remaining <= 0 || parseInt(no_of_guests) >= parseInt(trek_JS_obj.trip_booking_limit.remaining)) {
      jQuery('div[id="plus"]').css('pointer-events', 'none');
      jQuery('.limit-reached-feedback').css('display', 'block');
      if (is_return == false) {
        return false;
      }
    } else {
      jQuery('div[id="plus"]').css('pointer-events', 'auto');
      jQuery('.limit-reached-feedback').css('display', 'none');
    }
  }
}
function tt_validate_email(email = '') {
  var isValid = true;
  if (email) {
    let regex = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
    isValid = regex.test(email);
  }
  return isValid;
}
function tt_validate_name(name = '') {
  var isValid = false;
  if (name) {
    let regex = /^[A-Za-z-' ]+$/i;
    isValid = regex.test(name);
  }
  return isValid;
}
function tt_validate_phone(phone = '') {
  var isValid = true;
  if (phone) {
    let regex = /^(\+\d{1,2}\s?)?(\(\d{3}\)|\d{3})([-.\s]?)\d{3}([-.\s]?)\d{4}$/;
    isValid = regex.test(phone);
  }
  if ( phone.length === 0 ) {
    isValid = false;
  }
  return isValid;
}
function tt_validate_age(dob = null) {
  var isValid = true;
  if (dob) {
    var dob = new Date(dob);
    var month_diff = Date.now() - dob.getTime();
    var age_dt = new Date(month_diff);
    var year = age_dt.getUTCFullYear();
    var age = Math.abs(year - 1970);
    if (age < 16) {
      isValid = false;
    }
  }
  return isValid;
}
function tt_validate_duplicate_email() {
  var emailValidate = false;
  var guestEmailsArr = [];
  var emailStatus = [];
  jQuery('div[data-step="1"] input[type=email]').each(function () {
    emailValue = jQuery(this).val()
    jQuery(this).closest('div.form-row').find('.duplicateEmailError').remove()
    if (emailValue) {
      if (jQuery.inArray(emailValue.toLowerCase(), guestEmailsArr) === -1) {
        guestEmailsArr.push(emailValue);
        emailStatus.push(true);
      }
      else {
        jQuery(this).closest('div.form-row').addClass('woocommerce-invalid')
        jQuery(this).closest('div.form-row').removeClass('woocommerce-validated')
        jQuery(this).closest('div.form-row').append('<p class="duplicateEmailError mb-0 mt-2 text-danger">This email address is already being used.</p>')
        emailStatus.push(false);
      }
    }
  });
  if( emailStatus.includes(false) == true ){
    emailValidate = true;
  }
}
function checkout_steps_validations(step = 1) {
  var isValidated = false;
  var notAllowedNames = [
    'undefined',
    'gift_card_code',
    'pin',
    'bike_gears[primary][save_preferences]',
    'wc-cybersource-credit-card-expiry',
    'wc-cybersource-credit-card-test-amount',
    'wc-cybersource-credit-card-flex-token',
    'wc-cybersource-credit-card-flex-key',
    'wc-cybersource-credit-card-masked-pan',
    'wc-cybersource-credit-card-card-type',
    'wc-cybersource-credit-card-instrument-identifier-id',
    'wc-cybersource-credit-card-instrument-identifier-new',
    'wc-cybersource-credit-card-instrument-identifier-state',
    'is_saved_billing'
  ];
  var sameMailingInputs = [
    'billing_first_name', 'billing_last_name', 'billing_address_1', 'billing_country', 'billing_state',
    'billing_city', 'billing_postcode'
  ];
  var gearsArr = [];
  var validationMessages = [];
  for (var i = 0; i < 32; i++) {
    gearsArr.push(i);
  }
  if (step == 1) {
    jQuery(`div[data-step="${step}"] input`).each(function () {
      var isRequired = jQuery(this).attr('required');
      var CurrentName = jQuery(this).attr('name');
      var CurrentVal = jQuery(this).val();
      var validationType = jQuery(this).attr('data-validation');
      var inputType = jQuery(this).attr('type');
      if (inputType == 'radio') {
        CurrentVal = jQuery(`input[name="${CurrentName}"]:checked`).val();
      }
      if (isRequired == 'required') {
        if (CurrentVal == '' || CurrentVal == undefined || CurrentVal == 'undefined') {
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-row').addClass('woocommerce-invalid');
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-row').removeClass('woocommerce-validated');
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-floating').find(".rider-select").css("display", "block")
          jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
          isValidated = true;
          validationMessages.push(`Step 1: Field [name: ${CurrentName}, Value: ${CurrentVal}]`);
        } else {
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-row').removeClass('woocommerce-invalid');
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-row').addClass('woocommerce-validated');
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-floating').find(".rider-select").css("display", "none")
          jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
        }
        validationMessages.push(`Step 1: Field [name: ${CurrentName}, Value: ${CurrentVal}]`);
      }
    });
    /*Validation on County selections [Select inputs]*/
    jQuery(`div[data-step="${step}"] select`).each(function () {
      var isRequired = jQuery(this).attr('required');
      var CurrentName = jQuery(this).attr('name');
      var CurrentVal = jQuery(this).val();
      if (isRequired == 'required') {
        if (CurrentVal == '' || CurrentVal == undefined || CurrentVal == "0") {
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-row').addClass('woocommerce-invalid');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-row').removeClass('woocommerce-validated');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').find(".rider-select").css("display", "block")
          jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
          isValidated = true;
          validationMessages.push(`Step 1: Field [name: ${CurrentName}, Value: ${CurrentVal}]`);
        } else {
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-row').removeClass('woocommerce-invalid');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-row').addClass('woocommerce-validated');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').find(".rider-select").css("display", "none")
          jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
        }
      }
    });
    tt_validate_duplicate_email();
  }
  if (step == 2) {
    checkout_steps_validations(1);
    var selectedGuests = 0;
    var no_of_guests = jQuery('input[name="no_of_guests"]').val();
    if (jQuery('select[name^="occupants["]').length > 0) {
      jQuery('select[name^="occupants["]').each(function () {
        var CurrentVal = jQuery(this).val();
        if (CurrentVal != 'none' && CurrentVal >= 0) {
          selectedGuests++;
        }
      });
    }

    if (selectedGuests != no_of_guests) {
      isValidated = true;
    }
    /*Validation on Bikes selections [Select inputs]*/
    jQuery(`div[data-step="${step}"] select`).each(function () {
      var isRequired = jQuery(this).attr('required');
      var CurrentName = jQuery(this).attr('name');
      var CurrentVal = jQuery(this).val();
      if (isRequired == 'required') {
        if (CurrentVal == '' || CurrentVal == undefined) {
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').addClass('woocommerce-invalid');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').removeClass('woocommerce-validated');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').find(".rider-select").css("display", "block")
          isValidated = true;
          validationMessages.push(`Step 2: Field [name: ${CurrentName}, Value: ${CurrentVal}]`);
        } else {
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').removeClass('woocommerce-invalid');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').addClass('woocommerce-validated');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').find(".rider-select").css("display", "none")
        }
      }
    });
    /*Validation on Bikes selections [input,checkox,radio]*/
    jQuery(`div[data-step="${step}"] input`).each(function () {
      var isRequired = jQuery(this).attr('required');
      var CurrentName = jQuery(this).attr('name');
      var CurrentVal = jQuery(this).val();
      var inputType = jQuery(this).attr('type');
      if (inputType == 'radio' || inputType == 'checkbox') {
        CurrentVal = jQuery(`input[name="${CurrentName}"]:checked`).val();
      }
      if (isRequired == 'required') {
        if (CurrentVal == '' || CurrentVal == undefined) {
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-floating').addClass('woocommerce-invalid');
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-floating').removeClass('woocommerce-validated');
          isValidated = true;
          validationMessages.push(`Step 2: Field [name: ${CurrentName}, Value: ${CurrentVal}]`);
        } else {
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-floating').removeClass('woocommerce-invalid');
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-floating').addClass('woocommerce-validated');
        }
      }
    });
  }
  if (step == 3) {
    /*Validation on Bikes selections [Select inputs]*/
    jQuery(`div[data-step="${step}"] select`).each(function () {
      var isRequired = jQuery(this).attr('required');
      var CurrentName = jQuery(this).attr('name');
      var CurrentVal = jQuery(this).val();
      if (isRequired == 'required' && notAllowedNames.includes(CurrentName) == false) {
        if (CurrentVal == '' || CurrentVal == undefined) {
          jQuery(`select[name="${CurrentName}"]`).closest('div').addClass('woocommerce-invalid');
          jQuery(`select[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-validated');
          jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
          isValidated = true;
          validationMessages.push(`Step 3: Field [name: ${CurrentName}, Value: ${CurrentVal}]`);
        } else {
          jQuery(`select[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-invalid');
          jQuery(`select[name="${CurrentName}"]`).closest('div').addClass('woocommerce-validated');
          jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
        }
      }
    });
    /*Validation on Bikes selections [input,checkox,radio]*/
    jQuery(`div[data-step="${step}"] input`).each(function () {
      //sameMailingInputs
      var isMailingChecked = jQuery('input[name="is_same_billing_as_mailing"]').is(':checked');
      var isRequired = jQuery(this).attr('required');
      var CurrentName = jQuery(this).attr('name');
      var CurrentVal = jQuery(this).val();
      var inputType = jQuery(this).attr('type');
      if (inputType == 'radio' || inputType == 'checkbox') {
        CurrentVal = jQuery(`input[name="${CurrentName}"]:checked`).val();
      }
      if (isRequired == 'required' && notAllowedNames.includes(CurrentName) == false) {
        if( isMailingChecked == true && sameMailingInputs.includes(CurrentName) == true ){
            jQuery(`input[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-invalid');
            jQuery(`input[name="${CurrentName}"]`).closest('div').addClass('woocommerce-validated');
        }else{
          if (CurrentVal == '' || CurrentVal == undefined) {
            jQuery(`input[name="${CurrentName}"]`).closest('div').addClass('woocommerce-invalid');
            jQuery(`input[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-validated');
            isValidated = true;
            if (CurrentName == "tt_waiver") {
              jQuery('.tt_waiver_required').css('display', "block")
            }
            validationMessages.push(`Step 3: Field [name: ${CurrentName}, Value: ${CurrentVal}]`);
          } else {
            jQuery(`input[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-invalid');
            jQuery(`input[name="${CurrentName}"]`).closest('div').addClass('woocommerce-validated');
            if (CurrentName == "tt_waiver") {
              jQuery('.tt_waiver_required').css('display', "none")
            }
          }
        }
      }
    });
  }
  console.log(validationMessages);
  return isValidated;
}
var headerMargin = parseInt(jQuery('#header .container').css("marginLeft").replace('px', ''))
  if(jQuery(window).width() > 768 && jQuery(window).width() <= 1440) {
    jQuery('#similar-trips').css('padding-left', headerMargin + 10 + 'px');
    jQuery('.navigation-sticky').css('padding-left', headerMargin + 10 + 'px');
    
  } else if(jQuery(window).width() > 1440) {
    jQuery('#similar-trips').css('padding-left', headerMargin + 'px');
    jQuery('.navigation-sticky').css('padding-left', headerMargin + 'px');
  }
jQuery(document).ready(function () {
  tt_fetch_display_order_emails();
  jQuery.validator.addMethod("pwcheck",
    function (value, element) {
      var regPass = /^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
      return regPass.test(value);
    });
  // jQuery('.menu-item-has-children > .nav-link').attr('href','#');
  jQuery('.menu-item-has-children:first').prepend('<input type="checkbox"><i></i>');
  jQuery('.menu-item-has-children:not(:first)').prepend('<input type="checkbox" checked><i></i>');
  // pdp-hotels mini caro slider
  jQuery('.pdp-hotels__slider').slick({
    infinite: true,
    speed: 300,
    slidesToShow: 1,
    arrows: true,
    dots: true,
    responsive: [
      {
        breakpoint: 992,
        settings: {
          dots: true,
          arrows: false,
        }
      }
    ]
  });
  let counter = jQuery('.guestnumber').val();
  jQuery('#minus').addClass('qtydisable');
  function addfields(countnum) {
    var add = countnum;
    var modifiedCountnum = add + 1;
    jQuery('#qytguest').append('<div class="guest-checkout__guests guests"><p class="guest-checkout-info fs-xl lh-xl fw-medium mb-4">Guest ' + modifiedCountnum + '</p><div class="row mx-0 guest-checkout__primary-form-row"><div class="col-md px-0 form-row"><div class="form-floating"><input type="text" name="guests[' + add + '][guest_fname]" class="form-control tt_guest_inputs" required="required" data-validation="text" data-type="input" id="floatingInputGrid" placeholder="First Name" value=""><label for="floatingInputGrid">First Name</label></div></div><div class="col-md px-0 form-row"><div class="form-floating"><input type="text" name="guests[' + add + '][guest_lname]" class="form-control tt_guest_inputs" required="required" data-validation="text" data-type="input" id="floatingInputGrid" placeholder="Last Name" value=""><label for="floatingInputGrid">Last Name</label></div></div></div><div class="row mx-0 guest-checkout__primary-form-row"><div class="col-md px-0 form-row"><div class="form-floating"><input type="email" name="guests[' + add + '][guest_email]" class="form-control tt_guest_inputs" required="required" data-validation="email" data-type="input" id="floatingInputGrid" placeholder="Email" value=""><label for="floatingInputGrid">Email</label></div></div><div class="col-md px-0 form-row"><div class="form-floating"><input type="text" class="form-control tt_guest_inputs" required="required" data-validation="phone" data-type="input" id="floatingInputGrid" name="guests[' + add + '][guest_phone]" placeholder="Phone" value=""><label for="floatingInputGrid">Phone</label><div class="invalid-feedback"><img class="invalid-icon" /> Please enter valid phone number.</div></div></div></div><div class="row mx-0 guest-checkout__primary-form-row"><div class="col-md px-0 form-row"><div class=""><select required="required" class="form-select py-4 tt_guest_inputs" required="required" data-validation="text" data-type="input" name="guests[' + add + '][guest_gender]" id="floatingSelectGrid" aria-label="Floating label select example"><option value="1">Male</option><option value="2">Female</option></select><div class="invalid-feedback"><img class="invalid-icon" /> Please select gender.</div></div></div><div class="col-md px-0 form-row"><div class="form-floating"><input type="date" name="guests[' + add + '][guest_dob]" class="form-control tt_guest_inputs" required="required" data-validation="date" data-type="date" id="floatingInputGrid" placeholder="Date of Birth" value=""><label for="floatingInputGrid">Date of Birth</label><div class="invalid-feedback"><img class="invalid-icon" /> Age must be 16 years old or above, Please enter correct date of birth.</div></div></div></div><div class="row mx-0 guest-checkout__primary-form-row pt-1"><div class="col-md px-0 d-flex align-items-center guest-checkout__checkbox-gap"><input type="checkbox" name="guests[' + add + '][guest_as_me_mailing]" class="guest-checkout__checkbox"><label>This guest shares the same mailing address as me</label></div><hr></div>');
  }
  //jQuery('.guestnumber').keyup(function () {
  jQuery('.guestnumber').on('keyup', function () {
    counter = jQuery(this).val();
    var tripLimit = parseInt(trek_JS_obj.trip_booking_limit.remaining)
    jQuery('#qytguest').empty()
    if (parseInt(counter) > tripLimit) {
      counter = tripLimit
    }
    var glen = jQuery('#qytguest .guests').length;
    if (counter == 1) {
      jQuery('#minus').addClass('qtydisable');
      jQuery('.guest-infoo , .guest-subinfo , #qytguest').addClass('d-none');
      jQuery('#qytguest').empty();
    } else if (counter <= glen && counter !== '') {
      var num = glen - counter;
      for (var j = -1; j < num; j++) {
        jQuery("#qytguest").find(".guests:last").remove();
      }
    } else if (counter > 1) {
      jQuery('#minus').removeClass('qtydisable');
      var guest = counter - jQuery('#qytguest .guests').length;
      jQuery('.guest-infoo , .guest-subinfo , #qytguest').removeClass('d-none');
      for (var i = 1; i < guest; i++) {
        var count = 1 + jQuery('#qytguest .guests').length;
        addfields(count);
      }
    }
  });
  jQuery('#plus').on('click', function () {
    counter = jQuery('.guestnumber').val();
    //jQuery('#plus').click(function () {
    jQuery('.guestnumber').val(++counter);
    if (counter > 1) {
      jQuery('#minus').removeClass('qtydisable');
    }
    var guest = counter - jQuery('#qytguest .guests').length;
    jQuery('.guest-infoo , .guest-subinfo , #qytguest').removeClass('d-none');
    tripCapacityValidation(false);
    for (var i = 1; i < guest; i++) {
      var count = i + jQuery('#qytguest .guests').length;
      addfields(count);
    }
  });
  jQuery('#minus').on('click', function () {
    counter = jQuery('.guestnumber').val();
    //jQuery('#minus').click(function () {
    jQuery('.guestnumber').val((counter - 1 < 1) ? counter : --counter);
    if (counter == 1) {
      jQuery('#minus').addClass('qtydisable');
      jQuery('.guest-infoo , .guest-subinfo , #qytguest').addClass('d-none');
      jQuery('#qytguest').empty();
    }
    jQuery("#qytguest").find(".guests:last").remove();
    tripCapacityValidation(false);
  });

  jQuery('.pdp_slider').slick({
    dots: false,
    infinite: true,
    speed: 300,
    slidesToShow: 3.15,
    slidesToScroll: 1,
    autoplay: false,
    responsive: [
      {
        breakpoint: 992,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2,
          dots: true,
          arrows: false,
        }
      },
      {
        breakpoint: 480,
        settings: {
          dots: true,
          arrows: false,
          slidesToShow: 1,
          slidesToScroll: 1,
        }
      }
    ]
  });
  // jQuery('.checkout-bikes__own-bike-check').on('change', function () {
  //   if (jQuery(this).is(':checked')) {
  //     jQuery(this).parent().next().addClass('d-none');
  //     jQuery(this).parent().next().next().find('.checkout-bikes__bike-size:nth-child(2) , .checkout-bikes__bike-size:nth-child(3)').addClass('d-none');
  //   } else {
  //     jQuery(this).parent().next().removeClass('d-none');
  //     jQuery(this).parent().next().next().find('.checkout-bikes__bike-size:nth-child(2) , .checkout-bikes__bike-size:nth-child(3)').removeClass('d-none');
  //   }
  // });

  jQuery('#account_dob').datepicker({
    autoclose: true
  });

  // var headerMargin = parseInt(jQuery('#header .container').css("marginLeft").replace('px', ''))
  // if(jQuery(window).width() > 768 && jQuery(window).width() <= 1440) {
  //   jQuery('#similar-trips').css('padding-left', headerMargin + 10 + 'px');
  //   jQuery('.navigation-sticky').css('padding-left', headerMargin + 10 + 'px');
    
  // } else if(jQuery(window).width() > 1440) {
  //   jQuery('#similar-trips').css('padding-left', headerMargin + 'px');
  //   jQuery('.navigation-sticky').css('padding-left', headerMargin + 'px');
  // }

  
  jQuery('.pdp_similar_trips_slider').slick({
    dots: false,
    infinite: false,
    speed: 300,
    slidesToShow: 4.2,
    slidesToScroll: 1,
    autoplay: false,
    centerMode: false,
    responsive: [
      {
        breakpoint: 1640,
        settings: {
          dots: false,
          infinite: false,
          speed: 300,
          slidesToShow: 3.2,
          slidesToScroll: 1,
          autoplay: false,
          centerMode: false,
        }
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 2.2,
          slidesToScroll: 2,
          dots: true,
          arrows: false,
        }
      },
      {
        breakpoint: 480,
        settings: {
          dots: true,
          arrows: false,
          slidesToShow: 1.07,
          slidesToScroll: 1,
        }
      }
    ]
  });

});
jQuery('.protection_modal').on('change', function (e) {
  if (e.target.checked) {
    jQuery('#protection_modal').modal('toggle');
  }
});
jQuery('.btn-close').on('click', function () {
  jQuery('.protection_modal').prop('checked', false);
});
jQuery('.billing_checkbox').on('change', function (e) {
  var action = 'tt_ajax_mailing_address_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: "action=" + action,
    dataType: 'json',
    beforeSend : function(){
      jQuery('.checkout-payment__pre-address').html('');
      jQuery('.checkout-payment__pre-address').block({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      jQuery('.checkout-payment__pre-address').html(response.address);
    },
    complete: function(){
      jQuery('.checkout-payment__pre-address').unblock();
    }
  });
  if (e.target.checked) {
    jQuery('.checkout-payment__pre-address').removeClass('d-none');
    jQuery('.billing_row').addClass('d-none');
  } else {
    jQuery('.checkout-payment__pre-address').addClass('d-none');
    jQuery('.billing_row').removeClass('d-none');
  }
});
jQuery('.submit_protection').on('click', function () {
  jQuery('.checkout-payment__add-travel').addClass('d-none');
  jQuery('.checkout-payment__added-travel').removeClass('d-none');
  
});
jQuery('.checkout-payment__newcard').on('click', function (e) {
  if (jQuery('.checkout-payment__card-details').hasClass('active')) {
    jQuery('.checkout-payment__card-details').removeClass('active');
  } else {
    jQuery('.checkout-payment__card-details').addClass('active');
  }
});
(function () {
  'use strict'
  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')
  // Loop over them and prevent submission
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        var googleCaptcha = jQuery("#g-recaptcha-response").length
        if(googleCaptcha){
          var captchaVal = jQuery("#g-recaptcha-response").val()
          console.log("captchaVal")
          console.log(captchaVal)
          if (captchaVal == '') {
            jQuery(".invalid-captcha").css("display", "block")
          } else {
            jQuery(".invalid-captcha").css("display", "none")
          }
        }
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})();

/*Common js functions */
trek_script_func = {
  initNowUiWizard: function () {
    // Code for the Validator
    // var $validator = $('.card-wizard form').validate({
    //   rules: {
    //     firstname: {
    //       required: true,
    //       minlength: 3
    //     },
    //     lastname: {
    //       required: true,
    //       minlength: 3
    //     },
    //     email: {
    //       required: true,
    //       minlength: 3,
    //     },
    //     number: {
    //       required: true,
    //       minlength: 3,
    //     }

    //   },
    //   highlight: function (element) {
    //     $(element).closest('.input-group').removeClass('has-success').addClass('has-danger');
    //   },
    //   success: function (element) {
    //     $(element).closest('.input-group').removeClass('has-danger').addClass('has-success');
    //   }
    // });

    // Wizard Initialization
    // jQuery('.card-wizard').bootstrapWizard({
    //   'tabClass': 'nav nav-pills',
    //   'nextSelector': '.btn-next',
    //   'previousSelector': '.btn-previous',

    //   onNext: function (tab, navigation, index) {
    //     //var $valid = jQuery('.card-wizard form').valid();
    //     var $valid = true;
    //     if (!$valid) {
    //       $validator.focusInvalid();
    //       return false;
    //     }
    //   },
    //   onInit: function (tab, navigation, index) {
    //     //check number of tabs and fill the entire row
    //     var $total = navigation.find('li').length;
    //     $width = 100 / $total;
    //     //navigation.find('li').css('width', $width + '%');
    //   },
    //   onTabClick: function (tab, navigation, index) {
    //     //var $valid = jQuery('.card-wizard form').valid();
    //     var $valid = true;
    //     if (!$valid) {
    //       return false;
    //     } else {
    //       return true;
    //     }
    //   },
    //   onTabShow: function (tab, navigation, index) {
    //     var $total = navigation.find('li').length;
    //     var $current = index + 1;
    //     var $wizard = navigation.closest('.card-wizard');
    //     // If it's the last tab then hide the last button and show the finish instead
    //     if ($current >= $total) {
    //       jQuery($wizard).find('.btn-next').hide();
    //       jQuery($wizard).find('.btn-finish').show();
    //     } else {
    //       jQuery($wizard).find('.btn-next').show();
    //       jQuery($wizard).find('.btn-finish').hide();
    //     }
    //     //update progress
    //     var move_distance = 100 / $total;
    //     move_distance = move_distance * (index) + move_distance / 2;
    //     $wizard.find(jQuery('.progress-bar')).css({
    //       width: move_distance + '%'
    //     });
    //     //e.relatedTarget // previous tab
    //     $wizard.find(jQuery('.card-wizard .nav-pills li .nav-link.active')).addClass('checked');
    //     if(jQuery('.review .active').hasClass('active')) {
    //       jQuery('.guest-info , .rooms-gear , .payment, .review').addClass('active');
    //       jQuery('.checkout-timeline__progress-bar-line').css('width','100%');
    //     } else if(jQuery('.payment .active').hasClass('active')) {
    //       jQuery('.guest-info , .rooms-gear , .payment').addClass('active');
    //       jQuery('.review').removeClass('active');
    //       jQuery('.checkout-timeline__progress-bar-line').css('width','66%');
    //     } else if(jQuery('.rooms-gear .active').hasClass('active')) {
    //       jQuery('.guest-info , .rooms-gear').addClass('active');
    //       jQuery('.payment , .review').removeClass('active');
    //       jQuery('.checkout-timeline__progress-bar-line').css('width','33%');
    //     } else {
    //       jQuery('.rooms-gear , .payment , .review').removeClass('active');
    //       jQuery('.checkout-timeline__progress-bar-line').css('width','0%');
    //     }
    //   }
    // });
    // Prepare the preview for profile picture
    jQuery("#wizard-picture").change(function () {
      readURL(this);
    });
    jQuery('[data-toggle="wizard-radio"]').click(function () {
      wizard = jQuery(this).closest('.card-wizard');
      wizard.find('[data-toggle="wizard-radio"]').removeClass('active');
      jQuery(this).addClass('active');
      jQuery(wizard).find('[type="radio"]').removeAttr('checked');
      jQuery(this).find('[type="radio"]').attr('checked', 'true');
    });
    jQuery('[data-toggle="wizard-checkbox"]').click(function () {
      if (jQuery(this).hasClass('active')) {
        jQuery(this).removeClass('active');
        jQuery(this).find('[type="checkbox"]').removeAttr('checked');
      } else {
        jQuery(this).addClass('active');
        jQuery(this).find('[type="checkbox"]').attr('checked', 'true');
      }
    });
    jQuery('.set-full-height').css('height', 'auto');
    //Function to show image before upload
    function readURL(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
          jQuery('#wizardPicturePreview').attr('src', e.target.result).fadeIn('slow');
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
  }
};
/*Checkout steps code*/
jQuery(document).ready(function () {
  // Initialise the wizard
  trek_script_func.initNowUiWizard();
  setTimeout(function () {
    jQuery('.card.card-wizard').addClass('active');
  }, 600);
  var maxSteps = 4;
  var minSteps = 1;
  var currentStep = jQuery('input[name="step"]').val();
  handleProgressBar(currentStep)
  if(parseInt(currentStep) == 1){
    initialDatalayerCall()
  }
  if(parseInt(currentStep) == 2){
    checkoutShippingAnalytics()
  }
  if(parseInt(currentStep) == 4){
    checkoutPaymentAnalytics()
  }
  jQuery('body').on('click', 'form.checkout.woocommerce-checkout .btn-next', function () {
    tripCapacityValidation(false);
    var currentStep = jQuery('input[name="step"]').val();
    var targetStep = parseInt(currentStep) + parseInt(1);
    var targetStepId = jQuery('li.nav-item[data-step="' + targetStep + '"]').attr('data-step-id');
    var validationStatus = checkout_steps_validations(currentStep);
    if (validationStatus == true) {
      return false;
    }
    jQuery.blockUI({
      css: {
        border: 'none',
        padding: '15px',
        backgroundColor: '#000',
        '-webkit-border-radius': '10px',
        '-moz-border-radius': '10px',
        opacity: .5,
        color: '#fff'
      }
    });
    var formData = jQuery('form.checkout.woocommerce-checkout').serialize();
    var action = 'save_checkout_steps_action';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: formData + "&targetStep=" + targetStep + "&action=" + action,
      dataType: 'json',
      success: function (response) {
        jQuery(`div.tab-pane`).removeClass('active show');
        jQuery(`.checkout-timeline__progress-bar li.nav-item`).removeClass('active');
        jQuery(`.checkout-timeline__progress-bar li.nav-item[data-step="${targetStep}"]`).addClass('active');
        jQuery(`div.tab-pane[data-step="${targetStep}"]`).addClass('active show');
        jQuery('input[name="step"]').val(targetStep);
        if ((targetStep == 2 || targetStep == 4) && response.stepHTML) {
          jQuery(`div.tab-pane[data-step="${targetStep}"]`).html(response.stepHTML);
        }
        if (response.insuredHTMLPopup) {
          jQuery(`#tt-popup-insured-form`).html(response.insuredHTMLPopup);
        }
        if (response.insuredHTML) {
          jQuery(`#travel-protection-div`).html(response.insuredHTML);
        }
        if (response.review_order) {
          jQuery('#tt-review-order').html(response.review_order);
        }
        setTimeout(jQuery.unblockUI, 2000);
        window.history.replaceState(null, null, response.redirectURL);
        //window.location.href = response.redirectURL
        handleProgressBar(targetStep)

        jQuery('body').on('click', '.open-roommate-popup', function() {
          jQuery('.open-to-roommate-popup-container').css('display', 'flex');
          jQuery('header').css('z-index','0');
          jQuery('body').css('overflow','hidden');
          jQuery('html').addClass('no-scroll');
        })
        
        jQuery('.open-to-roommate-popup-container .close-btn').on('click', function() {
          jQuery('.open-to-roommate-popup-container').fadeOut();
          jQuery('header').css('z-index','1020');
          jQuery('html').removeClass('no-scroll');
        })
        
        jQuery('.open-to-roommate-popup-container').on('click', function(e) {
          if(jQuery(e.target).hasClass('open-to-roommate-popup-container')) {
            jQuery('.open-to-roommate-popup-container').fadeOut();
            jQuery('header').css('z-index','1020');
            jQuery('html').removeClass('no-scroll');
          }
        })

        jQuery('body').on('click', '.checkout-private-popup', function() {
          jQuery('.private-popup-container').css('display', 'flex');
          jQuery('header').css('z-index','0');
          jQuery('body').css('overflow','hidden');
          jQuery('html').addClass('no-scroll');
        })
        
        jQuery('.private-popup-container .close-btn').on('click', function() {
          jQuery('.private-popup-container').fadeOut();
          jQuery('header').css('z-index','1020');
          jQuery('html').removeClass('no-scroll');
        })
        
        jQuery('.private-popup-container').on('click', function(e) {
          if(jQuery(e.target).hasClass('private-popup-container')) {
            jQuery('.private-popup-container').fadeOut();
            jQuery('header').css('z-index','1020');
            jQuery('html').removeClass('no-scroll');
          }
        })

        jQuery('body').on('click', '.checkout-double-occupancy', function() {
          jQuery('.private-popup-container').css('display', 'flex');
          jQuery('header').css('z-index','0');
          jQuery('body').css('overflow','hidden');
          jQuery('html').addClass('no-scroll');
        })

        jQuery('body').on('click', '.checkout-travel-protection-tooltip', function() {
          jQuery('.travel-protection-tooltip-container').css('display', 'flex');
          jQuery('header').css('z-index','0');
          jQuery('body').css('overflow','hidden');
          jQuery('html').addClass('no-scroll');
        })
        
        jQuery('.travel-protection-tooltip-container .close-btn').on('click', function() {
          jQuery('.travel-protection-tooltip-container').fadeOut();
          jQuery('header').css('z-index','1020');
          jQuery('html').removeClass('no-scroll');
        })
        
        jQuery('.travel-protection-tooltip-container').on('click', function(e) {
          if(jQuery(e.target).hasClass('travel-protection-tooltip-container')) {
            jQuery('.travel-protection-tooltip-container').fadeOut();
            jQuery('header').css('z-index','1020');
            jQuery('html').removeClass('no-scroll');
          }
        })
      }
    });
  });
});
jQuery(document).ready(function () {
  if (jQuery('#togglePassword').length > 0) {
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#InputPassword');
    togglePassword.addEventListener('click', () => {
      const type = password
        .getAttribute('type') === 'password' ?
        'text' : 'password';
      password.setAttribute('type', type);
      togglePassword.classList.toggle('bi-eye');
    });
  }
  

  jQuery('body').on('submit', 'form[name="trek-login-form"]', function () {   
    console.log(1);
      var formData = jQuery('form.woocommerce-form-login').serialize();
      var action = 'trek_login_action';
      jQuery.ajax({
        type: 'POST',
        url: trek_JS_obj.ajaxURL,
        data: formData + "&action=" + action,
        dataType: 'json',
        beforeSend: function () {
          console.log(2);
          jQuery('#trek-login-responses').html('');
          jQuery('form.woocommerce-form-login').removeClass('was-validated')
          jQuery.blockUI({
            css: {
              border: 'none',
              padding: '15px',
              backgroundColor: '#000',
              '-webkit-border-radius': '10px',
              '-moz-border-radius': '10px',
              opacity: .5,
              color: '#fff'
            }
          });
        },
        success: function (response) {
          console.log(3);
          var resMessage = '';
          if (response.status == true) {
            jQuery('form.woocommerce-form-login')[0].reset();
            resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`;
            // jQuery('#trek-login-responses').html(resMessage);
            setTimeout(jQuery.unblockUI, 1000);
            window.location.href = response.redirect;
          } else {
            resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`;
            jQuery('#trek-login-responses').html(resMessage);
          }
          setTimeout(jQuery.unblockUI, 500);
          return false;
        }
      });
      console.log(5);
      return false;
    });
    
    jQuery('body').on('submit', 'form[name="trek-change-password"]', function () {
  
      var formData = jQuery('form[name="trek-change-password"]').serialize();
      var action = 'change_password_action';
      jQuery.ajax({
        type: 'POST',
        url: trek_JS_obj.ajaxURL,
        data: formData + "&action=" + action,
        dataType: 'json',
        beforeSend: function () {
          jQuery('#change-password-responses').html('');
          jQuery.blockUI({
            css: {
              border: 'none',
              padding: '15px',
              backgroundColor: '#000',
              '-webkit-border-radius': '10px',
              '-moz-border-radius': '10px',
              opacity: .5,
              color: '#fff'
            }
          });
        },
        success: function (response) {
          var resMessage = '';
          if (response.status == true) {
            const toastMessage = document.querySelector('#passwordUpdatedToast');
            toastMessage.classList.toggle('show');
            setTimeout(function () {
              toastMessage.classList.toggle('show');
            }, 4000);
            jQuery('form[name="trek-change-password"]')[0].reset();
            resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
          } else {
            resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
          }
          jQuery('#change-password-responses').html(resMessage);
          setTimeout(jQuery.unblockUI, 500);
          return false;
        }
      });
    
  });
  jQuery('body').on('submit', 'form[name="trek-trip-checklist-form"]', function () {
    var formData = jQuery('form[name="trek-trip-checklist-form"]').serialize();
    var action = 'update_trip_checklist_action';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: formData + "&action=" + action,
      dataType: 'json',
      beforeSend: function () {
        jQuery('#my-trips-responses').html('');
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        var resMessage = '';
        if (response.status == true) {
          resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
          // Set Medical info section as complete.
          jQuery('input[name="custentity_medical_info_first_load"]').remove();
        } else {
          resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
        }
        // Revalidate checklist completion.
        prebookingChecklistValidate();
        jQuery('#my-trips-responses').html(resMessage);
        if (response.status == false) {
          alert(response.message);
        }
        setTimeout(jQuery.unblockUI, 500);
        return false;
      }
    });
    return false;
  });
  jQuery('body').on('submit', 'form[name="trek-medical-information"]', function () {
    var formData = jQuery('form[name="trek-medical-information"]').serialize();
    var action = 'update_medical_information_action';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: formData + "&action=" + action,
      dataType: 'json',
      beforeSend: function () {
        jQuery('#medical-information-responses').html('');
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {        
        jQuery('.medical-info-toast .toast-body').html(response.message);
        const toastMessage = document.querySelector('.medical-info-toast');
        toastMessage.classList.toggle('show');
        setTimeout(jQuery.unblockUI, 500);
        return false;
      }
    });
    return false;
  });
  jQuery('body').on('submit', 'form[name="trek-contact-information"]', function () {
    var formData = jQuery('form[name="trek-contact-information"]').serialize();
    var action = 'update_emergency_contact_action';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: formData + "&action=" + action,
      dataType: 'json',
      beforeSend: function () {
        jQuery('#contact-information-responses').html('');
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        var resMessage = '';
        if (response.status == true) {
          resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
        } else {
          resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
        }
        jQuery('#contact-information-responses').html(resMessage);
        setTimeout(jQuery.unblockUI, 500);
        return false;
      }
    });
    return false;
  });
  jQuery('body').on('change', '.medical_item input[type="radio"]', function () {
    var is_checked = jQuery(this).val();
    var textArea = jQuery(this).closest('.medical_item').find('textarea');
    (is_checked == 'yes' ? textArea.show() : textArea.hide());
    return false;
  });
  jQuery('body').on('click', '#checkout-summary-mobile', function () {
    jQuery('.checkout-summary').toggleClass('d-none');
    if (jQuery('.checkout-summary').hasClass('d-none')) {
      jQuery(this).removeClass('checkout-summary__mobile-open');
      jQuery(this).addClass('checkout-summary__mobile');
      jQuery('.checkout-summary').removeClass('checkout-summary__toggle');
    } else {
      jQuery(this).addClass('checkout-summary__mobile-open');
      jQuery(this).removeClass('checkout-summary__mobile');
      jQuery('.checkout-summary').addClass('checkout-summary__toggle');
    }
  });

  jQuery('.book-trip-cta a').on('click', function () {
    dataLayer.push({
      'event': 'book_this_trip'
    });
  });
});
jQuery(window).load(function () {

  var isSafari = navigator.vendor && navigator.vendor.indexOf('Apple') > -1 &&
               navigator.userAgent &&
               navigator.userAgent.indexOf('CriOS') == -1 &&
               navigator.userAgent.indexOf('FxiOS') == -1;
  if( isSafari && jQuery(document).width() < 768){
    jQuery('.destination-option').css('padding-bottom', '80px');
  }
  tripCapacityValidation(true);
  prebookingChecklistValidate();
  validateGuestSelectionAdds();
  jQuery('input[name="trek_destination"]').on('click', function() {
    if (window.matchMedia('(max-width: 768px)').matches) {
      jQuery('header').css('z-index','0');
      jQuery('.destination-option').css('display', 'flex');
      jQuery('body').css('overflow','hidden');
    } else {
      jQuery('.destination-option').fadeToggle();
      jQuery('body').css('overflow','scroll');
    }
  })

  jQuery('.destination-option .close-popup').on('click', function() {
    jQuery('.destination-option').fadeOut();
    jQuery('header').css('z-index','1020');
    jQuery('body').css('overflow','scroll');
  })

  jQuery('.destination-option .option').on('click', function() {
    jQuery('.mobile-datepicker button.close-popup').removeClass('disabled');
    var selected_destination = jQuery(this).find('.category-name').text();
    jQuery('#trip-finder-destination .placeholder .selected-destination').addClass('active');
    jQuery('#trip-finder-destination .placeholder .placeholder-text').addClass('active');
    jQuery('#trip-finder-destination .placeholder .selected-destination').text(selected_destination);
  })
  jQuery('input[name="trek_destination"]').change(function () {
    var destination = jQuery('.destination-option input[name="trek_destination"]:checked').attr('id');
    jQuery('.trek-trip-finder-form').attr('action', `${destination}`);
    dataLayer.push({
      'event': 'trip_finder',
      'finder_step': 'select a destination' //find a trip, select a date, select a destination, show trips
    });
  });
});
jQuery('body').on('submit', 'form[name="trek-trip-finder-form"]', function () {
  dataLayer.push({
    'event': 'trip_finder',
    'finder_step': 'show trips' //find a trip, select a date, select a destination, show trips
  });

})
jQuery('body').on('submit', 'form[name="trek-bike-gear-preferences"]', function () {
  var formData = jQuery('form[name="trek-bike-gear-preferences"]').serialize();
  var action = 'update_bike_gear_info_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: formData + "&action=" + action,
    dataType: 'json',
    beforeSend: function () {
      jQuery('#bike-gear-preferences-responses').html('');
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      var resMessage = '';
      if (response.status == true) {
        resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
      } else {
        resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
      }
      jQuery('#bike-gear-preferences-responses').html(resMessage);
      setTimeout(jQuery.unblockUI, 500);
      return false;
    }
  });
  return false;
})
jQuery('body').on('submit', 'form[name="trek-communication-preferences"]', function () {
  var formData = jQuery('form[name="trek-communication-preferences"]').serialize();
  var action = 'update_communication_preferences_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: formData + "&action=" + action,
    dataType: 'json',
    beforeSend: function () {
      jQuery('#communication-preferences-responses').html('');
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      var resMessage = '';
      if (response.status == true) {
        resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
      } else {
        resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
      }
      jQuery('#communication-preferences-responses').html(resMessage);
      setTimeout(jQuery.unblockUI, 500);
      return false;
    }
  });
  return false;
})


async function tt_get_product_Data(product_id = '') {
  var productData = new Object();
  productData['id'] = '';
  productData['image'] = '';
  if (product_id) {
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: { action: 'tt_get_product_data_ajax_action', 'product_id': product_id },
      dataType: 'json',
      success: function (response) {
        productData['id'] = response.product_id;
        productData['image'] = response.image;
      }
    });
  }
  return productData;
}


// my account checklist expand/collapse
jQuery('.my-trips-checklist .checklist-expand-all').on('click', function () {
  currentText = jQuery(this).text()
  if (currentText == 'Expand all') {
    newText = 'Collapse all'
  }
  else {
    newText = 'Expand all'
  }
  jQuery(this).text(newText)
  jQuery('.checklist-accordion .accordion-item .accordion-collapse').collapse('toggle');
});

jQuery('select[id="shipping_country"]').change(function () {
  jQuery('body').trigger('update_checkout');
});

jQuery('body').on('click', '.submit_protection', function () {
  var formData = jQuery('form.checkout.woocommerce-checkout').serialize();
  var action = 'get_quote_travel_protection_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: formData + "&action=" + action,
    dataType: 'json',
    beforeSend: function () {
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      var resMessage = '';
      if (response.status == true) {
        resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
      } else {
        resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
      }
      if (response.review_order) {
        jQuery('#tt-review-order').html(response.review_order);
      }
      setTimeout(jQuery.unblockUI, 500);
      jQuery('.checkout-payment__add-travel').addClass('d-none');
      jQuery('.checkout-payment__added-travel').removeClass('d-none');
      if (response.guest_insurance_html) {
        jQuery('#travel-protection-div').html(response.guest_insurance_html);
      }
      if (response.insuredHTMLPopup) {
        jQuery(`#tt-popup-insured-form`).html(response.insuredHTMLPopup);
      }
      if (jQuery('.checkout-payment__options').length > 0 && response.payment_option) {
        jQuery('.checkout-payment__options').html(response.payment_option);
      }
    },
    complete: function(){
      jQuery("#currency_switcher").trigger("change");
    }
  });
  return false;
})

// Data Layer functions

function gtm_newsletter_signup(event, event_location) {
  dataLayer.push({
    'event': event,
    'event_location': event_location
  });
}
if (jQuery('#billing_first_name').length > 0 && jQuery('#billing_last_name').length > 0) {
  jQuery(document).on('input keyup', '#billing_first_name, #billing_last_name', function () {
    jQuery('#tt_pay_fname').val(jQuery('#billing_first_name').val());
    jQuery('#tt_pay_lname').val(jQuery('#billing_last_name').val());
  });
}
if (jQuery('.tt_apply_coupan').length > 0 || jQuery('.tt_remove_coupan').length > 0) {
  jQuery(document).on('click', '.tt_apply_coupan, .tt_remove_coupan', function () {
    var actionType = jQuery(this).attr('data-action');
    var coupon_code = jQuery('input[name="coupon_code"]').val();
    var dataString = { 'action': 'tt_apply_remove_coupan_action', 'type': actionType, 'coupon_code': coupon_code };
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: dataString,
      dataType: 'json',
      beforeSend: function () {
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        var step = jQuery(".tab-pane.active.show").attr('data-step');
        setTimeout(jQuery.unblockUI, 500);
        if (dataString.type == 'add' && response.is_applied == true && response.status == true) {
          jQuery('div.promo-form').addClass('d-none');
          jQuery('.checkout-summary__applied').removeClass('d-none');
        } else {
          jQuery('div.promo-form').removeClass('d-none');
          jQuery('.checkout-summary__applied').addClass('d-none');
        }
        if (jQuery('.checkout-payment__options').length > 0 && response.payment_option) {
          jQuery('.checkout-payment__options').html(response.payment_option);
        }
        if (jQuery('#tt-review-order').length > 0 && response.html) {
          jQuery('#tt-review-order').html(response.html);
        }
        if (response.status == false && dataString.type == 'add' ) {
          console.log( coupon_code );
          
         if ( coupon_code !== '') {
            // jQuery(".promo-input").val(coupon_code)
            jQuery(".invalid-code").css("display", "block")
          }
        }
        jQuery("#currency_switcher").trigger("change")
        if (parseInt(step) != 4) {
          jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').addClass("d-none")
        }
        // Show tearms and conditiones checkbox and pay now button on step 4.
        if( parseInt( step ) === 4 ) {
          jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').removeClass("d-none");
        }
      }
    });
  });
}
if (jQuery('#load-more').length > 0) {
  let currentPage = 1;
  jQuery('body').on('click', '#load-more', function () {
    var actionName = 'tt_load_more_blog_action';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: {
        action: actionName,
        paged: currentPage
      },
      dataType: 'json',
      beforeSend: function () {
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        if (currentPage >= response.max) {
          jQuery('#load-more').hide();
        }
        jQuery('.blog-list-appendTo').append(response.html);
        setTimeout(jQuery.unblockUI, 500);
      }
    });
    return false;
  });
}
// if (jQuery('button[data-field="private"]').length > 0) {
//   jQuery('body').on('click', 'button[data-field="private"]', function () {
//     var actionName = 'tt_single_suppliment_fees_action';
//     var private = jQuery('input[name="private"]').val();
//     jQuery.ajax({
//       type: 'POST',
//       url: trek_JS_obj.ajaxURL,
//       data: {
//         action: actionName,
//         private: private
//       },
//       dataType: 'json',
//       beforeSend: function () {
//         jQuery.blockUI({
//           css: {
//             border: 'none',
//             padding: '15px',
//             backgroundColor: '#000',
//             '-webkit-border-radius': '10px',
//             '-moz-border-radius': '10px',
//             opacity: .5,
//             color: '#fff'
//           }
//         });
//       },
//       success: function (response) {
//         if( response.review_order ){
//           jQuery('#tt-review-order').html(response.review_order);
//         }
//         setTimeout(jQuery.unblockUI, 500);
//       }
//     });
//     return false;
//   });
// }
if (jQuery('#tt-occupants-btn').length > 0) {
  jQuery('body').on('click', '#tt-occupants-btn', function () {
    // Take the number of all Select/Options fields in the Modal.
    const allSelectOptionsLength = jQuery('select[name^="occupants["]').length;
    // Define the variable to store number of all valid Select/Options.
    let validSelectOptionsLength = 0;
    jQuery('select[name^="occupants["]').each(function () {
      var isRequired = jQuery(this).attr('required');
      var CurrentName = jQuery(this).attr('name');
      var CurrentVal = jQuery(this).val();
      if (isRequired == 'required') {
        if (CurrentVal == '' || CurrentVal == 'none') {
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').addClass('woocommerce-invalid');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').removeClass('woocommerce-validated');
        } else {
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').removeClass('woocommerce-invalid');
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').addClass('woocommerce-validated');
          // If Select/Option field is valid, increase the flag variable.
          validSelectOptionsLength++
        }
      }
    });

    // Check if all Select/Options fields are valid to continue, otherwise return.
    if ( validSelectOptionsLength !== allSelectOptionsLength ) {
      return false;
    }
    var actionName = 'tt_save_occupants_ajax_action';
    var formData = jQuery('form.checkout.woocommerce-checkout').serialize();
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: formData + "&action=" + actionName,
      dataType: 'json',
      beforeSend: function () {
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        jQuery('#tt-occupants-btn-close').trigger('click');
        if (response.review_order) {
          jQuery('#tt-review-order').html(response.review_order);
        }
        if (response.step == 2 && response.stepHTML) {
          jQuery(`div.tab-pane[data-step="${response.step}"]`).html(response.stepHTML);
        }
        validateGuestSelectionAdds();
        jQuery.unblockUI();
        jQuery("#currency_switcher").trigger("change")
        jQuery('div[data-room="p-assign"]').hide();
        jQuery('div[data-room="s-assign"]').hide();
        jQuery('div[data-room="d-assign"]').hide();
        jQuery('div[data-room="r-assign"]').hide();
      }
    });
    //return false;
  });
}
jQuery('body').on('click', '.dates-pricing-book-now', function () {
  dataLayer.push({
    'event': 'book_now'
  });
})

jQuery('.itinerary-details .accordion-actions a.expand-all').on('click', function () {
  jQuery('.itinerary-details .accordion-item .accordion-button').removeClass('collapsed');
  jQuery('.itinerary-details .accordion-item .accordion-button').attr('aria-expanded', true);
  jQuery('.itinerary-details .accordion-item .accordion-collapse').addClass('show');
});

jQuery('.itinerary-details .accordion-actions a.collapse-all').on('click', function () {
  jQuery('.itinerary-details .accordion-item .accordion-button').addClass('collapsed');
  jQuery('.itinerary-details .accordion-item .accordion-button').attr('aria-expanded', false);
  jQuery('.itinerary-details .accordion-item .accordion-collapse').removeClass('show');
});

function printThis(containerId) {
  var divToPrint = document.getElementById(containerId);
  var newWin = window.open('', 'Print-Window');
  newWin.document.open();
  newWin.document.write(divToPrint.innerHTML + '<script>window.print()</script>');
  newWin.document.close();
  setTimeout(function () { newWin.close(); }, 10);
}
jQuery('body').on('click', 'nav #mega-menu-wrap-main-menu a.mega-menu-link', function (e) {
  let category = jQuery(this).parents("li.mega-menu-item").eq(2).find("a").eq(0).text()
  let subcategory = jQuery(this).parents("li.mega-menu-item").eq(1).find("a").eq(0).text()
  let selection = jQuery(this).text()

  let nav_selection = ""
  if (selection.trim() !== "") {
    nav_selection = selection
  }
  if (subcategory.trim() !== "") {
    nav_selection = subcategory + "|" + nav_selection
  }
  if (category.trim() !== "") {
    nav_selection = category + "|" + nav_selection
  }
  dataLayer.push({
    'event': 'navigation_click',
    'navigation_selection': nav_selection.toLowerCase(), //menu category|menu subcategory|menu selection
  });

  jQuery('.mega-menu-item.mega-toggle-on').first().children('ul').show();
})
function handleProgressBar(currentStep) {
  if (currentStep) {
    document.body.scrollTop = document.documentElement.scrollTop = 0
    jQuery('#progress-bar li.active').prevAll().css({ "background-color": "#28AAE1", "border": "2px solid #28AAE1" })
    jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').addClass("d-none")
    jQuery('body').removeClass('elementor-kit-14');
    jQuery("#currency_switcher").trigger("change")
    jQuery(".guest-checkout__primary-form input#email").prop("disabled", true)
    jQuery("#guest #shipping_state").attr("required", "required")
    switch (parseInt(currentStep)) {
      case 2:
        jQuery('#progress-bar .checkout-timeline__progress-bar-line').css("width", "33%")
        jQuery('.checkout-summary').addClass('d-none');
        jQuery('#checkout-summary-mobile').removeClass('checkout-summary__mobile-open');
        jQuery('#checkout-summary-mobile').addClass('checkout-summary__mobile');
        jQuery('.checkout-summary').removeClass('checkout-summary__toggle');
        assignEditOccupantsCtaShowHide()
        var totalOccupatnsAssigned = jQuery('select[name^="occupants["]').length;
        var totalNoOfGuests = jQuery('input[name="no_of_guests"]').val();
        var remainingGuests = totalNoOfGuests - totalOccupatnsAssigned;
        jQuery(".checkout-step-two-hotel__guests-left-counter span.badge").html(remainingGuests)
        break;

      case 3:
        jQuery('#progress-bar .checkout-timeline__progress-bar-line').css("width", "66%")
        jQuery('.checkout-summary').addClass('d-none');
        jQuery('#checkout-summary-mobile').removeClass('checkout-summary__mobile-open');
        jQuery('#checkout-summary-mobile').addClass('checkout-summary__mobile');
        jQuery('.checkout-summary').removeClass('checkout-summary__toggle');
        break;

      case 4:
        jQuery('#progress-bar .checkout-timeline__progress-bar-line').css("width", "99%")
        jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').removeClass("d-none")
        // jQuery('#checkout-summary-mobile').trigger("click")
        jQuery('.checkout-summary').removeClass('d-none');
        jQuery('#checkout-summary-mobile').addClass('checkout-summary__mobile-open');
        jQuery('#checkout-summary-mobile').removeClass('checkout-summary__mobile');
        jQuery('.checkout-summary').addClass('checkout-summary__toggle');

        break;

      default:
        jQuery('#progress-bar .checkout-timeline__progress-bar-line').css("width", "1%")
        jQuery('.checkout-summary').addClass('d-none');
        jQuery('#checkout-summary-mobile').removeClass('checkout-summary__mobile-open');
        jQuery('#checkout-summary-mobile').addClass('checkout-summary__mobile');
        jQuery('.checkout-summary').removeClass('checkout-summary__toggle');
        break;
    }
  }
}

jQuery('body').on('change', '.checkout-summary__total .guest-checkout__checkbox', function () {
  if (jQuery(this).is(':checked')) {
    jQuery('.checkout-summary__button').prop('disabled', false)
  }
  else {
    jQuery('.checkout-summary__button').prop('disabled', true)
  }
});
jQuery(document).on('click keyup keydown change', '.tt_rider_level_select', function () {
//jQuery(document).on('change', '.tt_rider_level_select', function () {
  var rider_level_id = jQuery(this).val(); //no-rider ID : 5
  var rider_level_text = jQuery(this).val(); //no-rider Text : Non-Rider
  var divID = `#${jQuery(this).attr('data-type')}`;
  var dataType = jQuery(this).attr('data-type');
  if (dataType.includes("tt_rider_level_guest_") == true) {
    var guest_idArr = dataType.split('tt_rider_level_guest_');
    var guest_id = guest_idArr[1];
  } else {
    var guest_id = 'primary';
  }
  if (rider_level_id == 5 || rider_level_text == 'Non-Rider') {
    jQuery(divID).find('input').prop('required', false);
    jQuery(divID).find('select').prop('required', false);
    //empty/reset all Fields without bike type id fields.
    jQuery(divID).find('input:not([name$="[bikeTypeId]"])').val('');
    jQuery(divID).find('select').prop('selectedIndex',0);
    if (guest_id != 'primary') {
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').val(5257);
    }else{
      jQuery('input[name="bike_gears[primary][bikeId]"]').val(5257)
    }
    jQuery(divID).hide();
    console.log('rider_level_id', rider_level_id);
  } else {
    if (guest_id != 'primary') {
      jQuery('input[name="bike_gears[guests][' + guest_id + '][own_bike]"]').trigger('change');
    }else{
      jQuery('input[name="bike_gears[primary][own_bike]"]').trigger('change');
    }
    console.log('rider_level_id', rider_level_id);
    jQuery(divID).show();
    if (guest_id != 'primary') {
      if(jQuery('select[name="bike_gears[' + guest_id + '][bike_size]"] option:selected').index() <= 0) {
        jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').val('');
      }
    }else{
      if(jQuery('select[name="bike_gears[primary][bike_size]"] option:selected').index() <= 0) {
        jQuery('input[name="bike_gears[primary][bikeId]"]').val('');
      }
    }
    if (guest_id != 'primary') {
      jQuery(divID).find('select').not('select[name="bike_gears[guests][' + guest_id + '][helmet_size]"], select[data-is-required="false"]').prop('required', true);
      jQuery(divID).find('input').not('input[name="bike_gears[guests][' + guest_id + '][own_bike]"]').prop('required', true);
    } else {
      jQuery(divID).find('select').not('select[name="bike_gears[primary][helmet_size]"], select[data-is-required="false"]').prop('required', true);
      jQuery(divID).find('input').not('input[name="bike_gears[primary][own_bike]"]').not('input[name="bike_gears[primary][save_preferences]"]').not('input[name="bike_gears[primary][bike_type_id_preferences]"]').prop('required', true);
    }
    if (jQuery(divID).find('.tt_my_own_bike_checkbox').is(':checked')) {
      if (guest_id != 'primary') {
        jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_size]"]').prop('required', false);
        jQuery('select[name="bike_gears[guests][' + guest_id + '][rider_height]"]').prop('required', false);
        jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeTypeId]"]').prop('required', false);
        jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').prop('required', false);
      } else {
        jQuery('select[name="bike_gears[primary][bike_size]"]').prop('required', false);
        jQuery('select[name="bike_gears[primary][rider_height]"]').prop('required', false);
        jQuery('input[name="bike_gears[primary][bikeTypeId]"]').prop('required', false);
        jQuery('input[name="bike_gears[primary][bikeId]"]').prop('required', false);
      }
    } else {
      if (guest_id != 'primary') {
        jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_size]"]').prop('required', true);
        jQuery('select[name="bike_gears[guests][' + guest_id + '][rider_height]"]').prop('required', true);
        jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeTypeId]"]').prop('required', true);
        jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').prop('required', true);
      } else {
        jQuery('select[name="bike_gears[primary][bike_size]"]').prop('required', true);
        jQuery('select[name="bike_gears[primary][rider_height]"]').prop('required', true);
        jQuery('input[name="bike_gears[primary][bikeTypeId]"]').prop('required', true);
        jQuery('input[name="bike_gears[primary][bikeId]"]').prop('required', true);
      }
    }
    if (jQuery(divID).find('.tt_my_own_bike_checkbox').is(':checked')) {
      if (guest_id != 'primary') {
          jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').val(5270);
        }else{
          jQuery('input[name="bike_gears[primary][bikeId]"]').val(5270)
        }
    }
  }
});
jQuery(document).on('change', '.tt_my_own_bike_checkbox', function () {
  var divID = `div[data-id="${jQuery(this).attr('data-type')}"]`;
  var dataType = jQuery(this).attr('data-type');
  if (dataType.includes("tt_my_own_bike_guest_") == true) {
    var guest_idArr = dataType.split('tt_my_own_bike_guest_');
    var guest_id = guest_idArr[1];
  } else {
    var guest_id = 'primary';
  }
  if (jQuery(this).is(':checked')) {
    jQuery(divID).find('input').prop('required', false);
    jQuery(divID).find('select').prop('required', false);
    //empty/reset all Fields without bike type id fields.
    jQuery(divID).find('input:not([name$="[bikeTypeId]"])').val('');
    jQuery(divID).find('select').prop('selectedIndex',0);
    if (guest_id != 'primary') {
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').val(5270);
    }else{
      jQuery('input[name="bike_gears[primary][bikeId]"]').val(5270)
    }
    jQuery(divID).hide();
    if (guest_id != 'primary') {
      jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_size]"]').prop('required', false);
      jQuery('select[name="bike_gears[guests][' + guest_id + '][rider_height]"]').prop('required', false);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeTypeId]"]').prop('required', false);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').prop('required', false);
    } else {
      jQuery('select[name="bike_gears[primary][bike_size]"]').prop('required', false);
      jQuery('select[name="bike_gears[primary][rider_height]"]').prop('required', false);
      jQuery('input[name="bike_gears[primary][bikeTypeId]"]').prop('required', false);
      jQuery('input[name="bike_gears[primary][bikeId]"]').prop('required', false);
    }
  } else {
    if (guest_id != 'primary') {
      if(jQuery('select[name="bike_gears[' + guest_id + '][bike_size]"] option:selected').index() <= 0) {
        jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').val('');
      }
    }else{
      if(jQuery('select[name="bike_gears[primary][bike_size]"] option:selected').index() <= 0) {
        jQuery('input[name="bike_gears[primary][bikeId]"]').val('')
      }
    }
    jQuery(divID).show();
    if (guest_id != 'primary') {
      jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_size]"]').prop('required', true);
      jQuery('select[name="bike_gears[guests][' + guest_id + '][rider_height]"]').prop('required', true);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeTypeId]"]').prop('required', true);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').prop('required', true);
    } else {
      jQuery('select[name="bike_gears[primary][bike_size]"]').prop('required', true);
      jQuery('select[name="bike_gears[primary][rider_height]"]').prop('required', true);
      jQuery('input[name="bike_gears[primary][bikeTypeId]"]').prop('required', true);
      jQuery('input[name="bike_gears[primary][bikeId]"]').prop('required', true);
    }
  }
});
jQuery(document).on('click', '.tt_change_checkout_step', function () {
  var step = jQuery(this).attr('data-step');
  tt_change_checkout_step(step);
});

// jQuery('body').on('change', '.checkout-bikes-section input[type="radio"]', function () {
//   jQuery('span.radio-selection').removeClass("checkout-bikes__selected-bike-icon");
//   jQuery('.checkout-bikes__bike').removeClass("bike-selected")
//   jQuery(this).closest('.checkout-bikes__bike').find('span.radio-selection').toggleClass("checkout-bikes__selected-bike-icon");
//   jQuery(this).closest('.checkout-bikes__bike').toggleClass("bike-selected")
// });
jQuery('body').on('click', '.bike_selectionElement', function () {
  var guest_num;
  var bikeTypeId = jQuery(this).attr('data-id');
  var targetElement = `${jQuery(this).attr('data-selector')}`;
  var guest_number = `${jQuery(this).attr('data-guest-id')}`;
  var actionName = 'tt_bike_selection_ajax_action';
  var p_isbikeUpgrade = jQuery('input[name="bike_gears[primary][bikeTypeId]"]:checked').length;
  var no_of_guests = jQuery('input[name="no_of_guests"]').val();
  var bikeUpgradeTotal = (p_isbikeUpgrade ? 1 : 0);
  var bikeUpgradeTotalCount = 0;
  for (var index = 1; index < no_of_guests; index++) {
    var g_isbikeUpgrade = jQuery(`input[name="bike_gears[guests][${index}][bikeTypeId]"]:checked`).length;
    if (g_isbikeUpgrade == '1') {
      bikeUpgradeTotalCount++;
    }
  }
  bikeUpgradeQty = bikeUpgradeTotalCount + bikeUpgradeTotal;
  var parentDiv = jQuery(this).closest('.checkout-bikes__bike-grid-guests');
  parentDiv.find('.bike_selectionElement .radio-selection').removeClass("checkout-bikes__selected-bike-icon");
  parentDiv.find('.bike_selectionElement .radio-selection').addClass("checkout-bikes__select-bike-icon");
  parentDiv.find('.bike_selectionElement').removeClass("bike-selected");
  jQuery(this).find('.radio-selection').addClass("checkout-bikes__selected-bike-icon");
  jQuery(this).addClass("bike-selected");
  // Set bike type id for Bike & Gear Preferences.
  var bikeTypeIdPreferences = jQuery(this).attr('data-type-id');
  jQuery('[name="bike_gears[primary][bike_type_id_preferences]"]').val(bikeTypeIdPreferences);
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: { 'action': actionName, 'bikeTypeId': bikeTypeId, guest_number: guest_number, bikeUpgradeQty: bikeUpgradeQty },
    dataType: 'json',
    beforeSend: function () {
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      if (targetElement == 'tt_bike_selection_primary' && response) {
        // jQuery("div[data-selector='tt_bike_selection_primary']").removeClass("bike-selected");
        // jQuery("div[data-selector='tt_bike_selection_primary'] span.radio-selection").removeClass("checkout-bikes__selected-bike-icon");
        jQuery('input[name="bike_gears[primary][bikeId]"]').val('')
        if (response.size_opts) {
          jQuery('select[name="bike_gears[primary][bike_size]"]').html(response.size_opts);
        }
        // jQuery(this).addClass("bike-selected");
        // jQuery(this).find('span.radio-selection').addClass("checkout-bikes__selected-bike-icon");
      }
      if (targetElement != 'tt_bike_selection_primary' && response) {
        guest_num = targetElement.split('tt_bike_selection_guest_');
        if (guest_num[1] && guest_num[1] != 0) {
          // jQuery("div[class^='tt_bike_selection_guest_']").removeClass("bike-selected");
          // jQuery("div[class^='tt_bike_selection_guest_'] span.radio-selection").removeClass("checkout-bikes__selected-bike-icon");
          jQuery('input[name="bike_gears[guests][' + guest_num[1] + '][bikeId]"]').val('');
          if (response.size_opts) {
            jQuery(`select[name="bike_gears[guests][${guest_num[1]}][bike_size]"]`).html(response.size_opts);
          }
          // jQuery(this).addClass("bike-selected");
          // jQuery(this).find('span.radio-selection').addClass("checkout-bikes__selected-bike-icon");
        }
      }
      if (response.review_order) {
        jQuery('#tt-review-order').html(response.review_order);
      }
      jQuery.unblockUI();
      jQuery("#currency_switcher").trigger("change")
    }
  });
});

jQuery('body').on('click', '#trip-booking-modal', function () {
  var myBookId = jQuery(this).data('form-id');
  jQuery("#tripBookingModal #bookId").val(myBookId);
});

jQuery('body').on('click', '.proceed-booking-btn', function () {
  removeCartAnalytics()
  var actionName = 'tt_clear_cart_ajax_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: { action: actionName },
    dataType: 'json',
    beforeSend: function () {
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      jQuery.unblockUI();
    }
  });
  var myBookId = jQuery("#bookId").val();
  jQuery("#flush-collapse-" + myBookId + " .accordion-book-now form").submit()
});

jQuery('body').on('click', '.view-all-faqs button', function () {
  jQuery("#faqs .accordion-item.d-none").toggleClass("d-none")
  jQuery("#faqs .view-all-faqs").toggleClass("d-none")
});
function validateGuestSelectionAdds(){
  var no_of_guests = jQuery('input[name="no_of_guests"]').val();
  var totalOccupatns = jQuery('select[name^="occupants["]').length;
  var buttonEle = jQuery('.checkout-step-two-hotel__room-options button.btn-number');
  if( buttonEle.length > 0 ){
    jQuery(buttonEle).each(function(){
      var CurrentName = jQuery(this).attr('data-field');
      var CurrentVal = jQuery(`input[name="${CurrentName}"]`).val();
      var remainingRooms = no_of_guests - totalOccupatns;
      jQuery(".checkout-step-two-hotel__guests-left-counter span.badge").html(remainingRooms)
      if( no_of_guests == 1 || remainingRooms == 1 ){
        if( CurrentName == 'double' || CurrentName == 'single' ){
          if( CurrentVal > 0 ){
            jQuery(`button[data-type="minus"][data-field="${CurrentName}"]`).attr('disabled', false);
          }else{
            jQuery(`button[data-type="minus"][data-field="${CurrentName}"]`).attr('disabled', true);
          }
          jQuery(`button[data-type="plus"][data-field="${CurrentName}"]`).attr('disabled', true);
        }else{
          if( CurrentVal == 0 ){
            jQuery(`button[data-type="minus"][data-field="${CurrentName}"]`).attr('disabled', true);
          }else{
            jQuery(`button[data-type="minus"][data-field="${CurrentName}"]`).attr('disabled', false);
          }
          if( no_of_guests == totalOccupatns ){
            jQuery(`button[data-type="plus"][data-field="${CurrentName}"]`).attr('disabled', true);
          }else{
            jQuery(`button[data-type="plus"][data-field="${CurrentName}"]`).attr('disabled', false);
          }
        }
      }else{
        if( CurrentVal > 0 ){
          jQuery(`button[data-type="minus"][data-field="${CurrentName}"]`).attr('disabled', false);
        }else{
          jQuery(`button[data-type="minus"][data-field="${CurrentName}"]`).attr('disabled', true);
        }
        if( no_of_guests == totalOccupatns ){
          jQuery(`button[data-type="plus"][data-field="${CurrentName}"]`).attr('disabled', true);
        }else{
          jQuery(`button[data-type="plus"][data-field="${CurrentName}"]`).attr('disabled', false);
        }
      }
    });
  }
}
if (jQuery('.checkout-step-two-hotel__room-options button.btn-number').length > 0) {
  jQuery('body').on('click', '.checkout-step-two-hotel__room-options button.btn-number', function () {
    var no_of_guests = jQuery('input[name="no_of_guests"]').val();
    var totalOccupatns = jQuery('select[name^="occupants["]').length;
    // checkout hotel room counter
    var fieldName = jQuery(this).attr('data-field');
    var type = jQuery(this).attr('data-type');
    var input = jQuery("input[name='" + fieldName + "']");
    var currentVal = parseInt(input.val());
    if (!isNaN(currentVal)) {
      if (type == 'minus') {
        if (input.val() == 1 || input.val() == 0) {
          if (jQuery('#' + fieldName).length) {
            jQuery('#' + fieldName).click();
          }
        } else {
          if (currentVal > input.attr('min')) {
            input.val(currentVal - 1).change();
          }
          if (parseInt(input.val()) == input.attr('min')) {
            jQuery(this).attr('disabled', true);
          }
        }
      } else if (type == 'plus') {

        if (currentVal < input.attr('max')) {
          input.val(currentVal + 1).change();
        }
        if (parseInt(input.val()) == input.attr('max')) {
          jQuery(this).attr('disabled', true);
        }

      }
    } else {
      input.val(0);
    }
    var actionName = 'tt_update_occupant_popup_html_ajax_action';
    var single = jQuery('input[name="single"]').val();
    var double = jQuery('input[name="double"]').val();
    var private = jQuery('input[name="private"]').val();
    var roommate = jQuery('input[name="roommate"]').val();
    var allroomsTotal = (parseInt(single) * 2) + (parseInt(double) * 2) + parseInt(private) + parseInt(roommate);
    // if( allroomsTotal == no_of_guests ){
    //   return false;
    // }
    var formData = {
      action: actionName,
      single: single,
      double: double,
      private: private,
      roommate: roommate
    };
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: formData,
      dataType: 'json',
      beforeSend: function () {
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        if (response.status == true && response.html) {
          if( no_of_guests == 1 && ( fieldName == 'double' || fieldName == 'single' ) ){
            console.log(`[TT warning] - You can't choose ${fieldName} type for single guest...`);
          }else if( totalOccupatns == no_of_guests && type == 'plus' ){
            console.log(`[TT warning] - You can't add more occupant more than total guests...`);
            if( currentVal > 0 ){
              input.val(currentVal - 1).change();
            }
          }else{
            jQuery('#occupant-popup-inner-html').html(response.html);
          }
          validateGuestSelectionAdds();
        }
        if (response.status == true) {
          if (single > 0) {
            if (jQuery("#tt-single-occupants:empty").length > 0) {
              jQuery('div[data-room="s-assign"]').show();
              jQuery('div[data-room="s"]').hide();
            }
            else{
              jQuery('div[data-room="s"]').show();
              jQuery('div[data-room="s-assign"]').hide();
            }
          } else {
            jQuery('div[data-room="s"]').hide();
            jQuery('div[data-room="s-assign"]').hide();
          }
          if (private > 0) {
            if (jQuery("#tt-private-occupants:empty").length > 0) {
              jQuery('div[data-room="p-assign"]').show();
              jQuery('div[data-room="p"]').hide();
            }
            else{
              jQuery('div[data-room="p"]').show();
              jQuery('div[data-room="p-assign"]').hide();
            }
          } else {
            jQuery('div[data-room="p"]').hide();
            jQuery('div[data-room="p-assign"]').hide();
          }
          if (roommate > 0) {
            if (jQuery("#tt-roommate-occupants:empty").length > 0) {
              jQuery('div[data-room="r-assign"]').show();
              jQuery('div[data-room="r"]').hide();
            }
            else{
              jQuery('div[data-room="r"]').show();
              jQuery('div[data-room="r-assign"]').hide();
            }
          } else {
            jQuery('div[data-room="r"]').hide();
            jQuery('div[data-room="r-assign"]').hide();
          }
          if (double > 0) {
            if (jQuery("#tt-double1Bed-occupants:empty").length > 0) {
              jQuery('div[data-room="d-assign"]').show();
              jQuery('div[data-room="d"]').hide();
            }
            else{
              jQuery('div[data-room="d"]').show();
              jQuery('div[data-room="d-assign"]').hide();
            }
          } else {
            jQuery('div[data-room="d"]').hide();
            jQuery('div[data-room="d-assign"]').hide();
          }
        }
        jQuery.unblockUI();
        jQuery("#currency_switcher").trigger("change")
      },
      complete: function() {
        // Trigger an event that shows that the "#addOccupantsModal" modal html is ready.
        let occupantsModal = document.querySelector('#addOccupantsModal');
        occupantsModal && occupantsModal.dispatchEvent(occupantPopUpHtmlReadyEvent);
      }
    });
  });
}
if (jQuery('.tt_reset_rooms').length > 0) {
  jQuery('body').on('click', '.tt_reset_rooms', function () {
    var actionName = 'tt_update_occupant_popup_html_ajax_action';
    var roomType = jQuery(this).attr('id');
    jQuery(`input[name="${roomType}"]`).val(0);
    var single = jQuery('input[name="single"]').val();
    var double = jQuery('input[name="double"]').val();
    var private = jQuery('input[name="private"]').val();
    var roommate = jQuery('input[name="roommate"]').val();
    var formData = {
      action: actionName,
      single: single,
      double: double,
      private: private,
      roommate: roommate
    };
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: formData,
      dataType: 'json',
      beforeSend: function () {
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        if (response.status == true ) {
          if( response.html ){
            jQuery('#occupant-popup-inner-html').html(response.html);
          }
          if( response.hotel_html ){
            jQuery('#tt-hotel-occupant-inner-html').html(response.hotel_html);
          }
        }
        if (response.status == true) {
          if (single > 0) {
            if (jQuery("#tt-single-occupants:empty").length > 0) {
              jQuery('div[data-room="s-assign"]').show();
              jQuery('div[data-room="s"]').hide();
            }
            else{
              jQuery('div[data-room="s"]').show();
              jQuery('div[data-room="s-assign"]').hide();
            }
          } else {
            jQuery('div[data-room="s"]').hide();
            jQuery('div[data-room="s-assign"]').hide();
          }
          if (private > 0) {
            if (jQuery("#tt-private-occupants:empty").length > 0) {
              jQuery('div[data-room="p-assign"]').show();
              jQuery('div[data-room="p"]').hide();
            }
            else{
              jQuery('div[data-room="p"]').show();
              jQuery('div[data-room="p-assign"]').hide();
            }
          } else {
            jQuery('div[data-room="p"]').hide();
            jQuery('div[data-room="p-assign"]').hide();
          }
          if (roommate > 0) {
            if (jQuery("#tt-roommate-occupants:empty").length > 0) {
              jQuery('div[data-room="r-assign"]').show();
              jQuery('div[data-room="r"]').hide();
            }
            else{
              jQuery('div[data-room="r"]').show();
              jQuery('div[data-room="r-assign"]').hide();
            }
          } else {
            jQuery('div[data-room="r"]').hide();
            jQuery('div[data-room="r-assign"]').hide();
          }
          if (double > 0) {
            if (jQuery("#tt-double1Bed-occupants:empty").length > 0) {
              jQuery('div[data-room="d-assign"]').show();
              jQuery('div[data-room="d"]').hide();
            }
            else{
              jQuery('div[data-room="d"]').show();
              jQuery('div[data-room="d-assign"]').hide();
            }
          } else {
            jQuery('div[data-room="d"]').hide();
            jQuery('div[data-room="d-assign"]').hide();
          }
        }
        jQuery.unblockUI();
        validateGuestSelectionAdds();
        jQuery("#currency_switcher").trigger("change")
      },
      complete: function(){
        if(trek_JS_obj.review_order){
          jQuery('#tt-review-order').html(trek_JS_obj.review_order);
          jQuery("#currency_switcher").trigger("change")
        }
        // Trigger an event that shows that the "#addOccupantsModal" modal html is ready.
        let occupantsModal = document.querySelector('#addOccupantsModal');
        occupantsModal && occupantsModal.dispatchEvent(occupantPopUpHtmlReadyEvent);
      }
    });
    //return false;
  });
  if( jQuery( '.modal-guest-change-warning' ).length > 0 ){
    // Reset Rooms selections, when change number of guests on step one from checkout.
    jQuery( '.modal-guest-change-warning' ).on( 'hidden.bs.modal', function () {
      jQuery('.tt_reset_rooms').click();
    })
  }
}
jQuery('body').on('change', '.tt_bike_size_change', function () {
  var guest_index = jQuery(this).attr('data-guest-index');
  var bikeidInput = (guest_index == 0 ? 'input[name="bike_gears[primary][bikeId]"]' : `input[name="bike_gears[guests][${guest_index}][bikeId]"]`);
  var bikeTypeId;
  if (guest_index == 0) {
    bikeTypeId = jQuery('div[data-selector="tt_bike_selection_primary"] input[type="radio"]:checked').val();
  } else {
    bikeTypeId = jQuery(`div[data-selector="tt_bike_selection_guest_${guest_index}"] input[type="radio"]:checked`).val();
  }
  var bike_size = jQuery(this).val();
  var targetInput = jQuery(bikeidInput);
  var actionName = 'tt_bike_size_change_ajax_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: { 'action': actionName, 'bike_size': bike_size, 'bikeTypeId': bikeTypeId },
    dataType: 'json',
    beforeSend: function () {
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      if (response.status == true) {
        jQuery(targetInput).val(response.bike_id);
      }
      jQuery.unblockUI();
      jQuery("#currency_switcher").trigger("change")
    }
  });
});
if (jQuery('.tt_bike_upgrade_click_ev').length > 0) {
  jQuery('body').on('change', '.tt_bike_upgrade_click_ev', function () {
    var guest_index = jQuery(this).attr('data-guest-index');
    var upgrade_count = 0;
    jQuery('.tt_bike_upgrade_click_ev:checked').each(function () {
      if (this.value == 'yes') {
        upgrade_count++;
      }
    });
    var actionName = 'tt_bike_upgrade_fees_ajax_action';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: { 'action': actionName, 'guest_index': guest_index, 'upgrade_count': upgrade_count },
      dataType: 'json',
      beforeSend: function () {
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        if (response.status == true && response.review_order) {
          jQuery('#tt-review-order').html(response.review_order);
        }
        jQuery.unblockUI();
      }
    });
  });
}
if (jQuery('.tt_continue_bike_click_btn').length > 0) {
  jQuery('body').on('click', '.tt_continue_bike_click_btn', function () {
    var selectedGuests = 0;
    if (jQuery('select[name^="occupants["]').length > 0) {
      var no_of_guests = jQuery('input[name="no_of_guests"]').val();
      jQuery('select[name^="occupants["]').each(function () {
        var CurrentVal = jQuery(this).val();
        if (CurrentVal != 'none' && CurrentVal >= 0) {
          selectedGuests++;
        }
      });
    }
    if (selectedGuests != no_of_guests) {
      return false;
    }else{
      jQuery('.tt_continue_bike_click_btn_trigger').trigger('click');
    }
    var single = jQuery('input[name="single"').val();
    var double = jQuery('input[name="double"').val();
    var roommate = jQuery('input[name="roommate"').val();
    var private = jQuery('input[name="private"').val();
    var special_needs = jQuery('textarea[name="special_needs"').val();
    var actionName = 'tt_guest_rooms_selection_ajax_action';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: {
        'action': actionName,
        'single': single,
        'double': double,
        'roommate': roommate,
        'private': private,
        'special_needs': special_needs
      },
      dataType: 'json',
      beforeSend: function () {
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        if (response.status == true && response.html) {
          jQuery('#tt-room-bikes-selection').html(response.html);
        }
        jQuery.unblockUI();
        jQuery('html, body').animate({
          scrollTop: jQuery('.checkout-bikes__rider-level').offset().top - 150
        }, 'fast');
      }
    });

    var action = 'dx_get_current_user_bike_preferences';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: "action=" + action,
      dataType: 'json',
      success: function (response) {
        // Check if the response is valid
        if (response) {
          // Refill fields with retrieved data
          if (response.gear_preferences_bike_type !== "") {
            var has_selected_bike = false;
            jQuery('[data-selector="tt_bike_selection_primary"] [name="bike_gears[primary][bikeTypeId]"]').each(function () {
              if(jQuery(this).is(':checked')){
                has_selected_bike = true;
              }
            })
            var can_not_select = false;
            if(jQuery(`[data-selector="tt_bike_selection_primary"][data-type-id="${response.gear_preferences_bike_type}"]`).length >= 2 ){
              // Has more than one bike from the same type, can not select any of them.
              can_not_select = true;
            }
            // Check if we have selected already.
            if( !has_selected_bike && !can_not_select ){
              // Click on input inside bike to take available sizes.
              jQuery(`[data-selector="tt_bike_selection_primary"][data-type-id="${response.gear_preferences_bike_type}"] input`).click();
            }
          }
          if (response.gear_preferences_rider_height !== "") {
            // Check if we have selected already.
            if( jQuery( '[name="bike_gears[primary][rider_height]"]' ).prop( 'selectedIndex' ) <= 0 ) {
              jQuery('[name="bike_gears[primary][rider_height]"]').val(response.gear_preferences_rider_height);
            }
          }
          if (response.gear_preferences_select_pedals !== "") {
            // Check if we have selected already.
            if( jQuery( '[name="bike_gears[primary][bike_pedal]"]' ).prop( 'selectedIndex' ) <= 0 ) {
              jQuery('[name="bike_gears[primary][bike_pedal]"]').val(response.gear_preferences_select_pedals);
            }
          }
          if (response.gear_preferences_helmet_size !== "") {
            // Check if we have selected already.
            if( jQuery( '[name="bike_gears[primary][helmet_size]"]' ).prop( 'selectedIndex' ) <= 0 ) {
              jQuery('[name="bike_gears[primary][helmet_size]"]').val(response.gear_preferences_helmet_size);
            }
          }
          if (response.gear_preferences_jersey_style !== "") {
            if (!jQuery('[name="bike_gears[primary][jersey_style]"]').parent().hasClass('d-none')) {
              // Check if we have selected already.
              if( jQuery( '[name="bike_gears[primary][jersey_style]"]' ).prop( 'selectedIndex' ) <= 0 ) {
                jQuery('[name="bike_gears[primary][jersey_style]"]').val(response.gear_preferences_jersey_style);
              }
            }
          }
          if (response.gear_preferences_jersey_size !== "" && response.gear_preferences_jersey_style !== "" ) {
            if (!jQuery('[name="bike_gears[primary][jersey_size]"]').parent().hasClass('d-none')) {
              // Check if we have selected already.
              if( jQuery( '[name="bike_gears[primary][jersey_size]"]' ).prop( 'selectedIndex' ) <= 0 ) {
                // Take Size options before set the size option value.
                var actionName   = 'tt_jersey_change_action';
                var jersey_style = response.gear_preferences_jersey_style;
                jQuery.ajax({
                  type: 'POST',
                  url: trek_JS_obj.ajaxURL,
                  data: { 'action': actionName, 'jersey_style': jersey_style },
                  dataType: 'json',
                  success: function (res) {
                    if (res.status == true) {
                      // Append options to select/option field.
                      jQuery('[name="bike_gears[primary][jersey_size]"]').html(res.opts);

                      // Set selected size.
                      jQuery('[name="bike_gears[primary][jersey_size]"]').val(response.gear_preferences_jersey_size);
                    }
                  }
                });
             }
            }
          }
        } else {
          // Handle the case when the AJAX request is not successful
          console.error('Error fetching user post meta:', response.message);
        }
      }
    });
  });
}
if (jQuery('input[name="pay_amount"]').length > 0) {
  jQuery('body').on('click', 'input[name="pay_amount"]', function () {
    var actionName = 'tt_pay_amount_change_ajax_action';
    var paymentType = jQuery(this).val();
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: { 'action': actionName, 'paymentType': paymentType },
      dataType: 'json',
      beforeSend: function () {
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        if (response.status == true && response.review_order) {
          jQuery('#tt-review-order').html(response.review_order);
        }
        if (jQuery('.checkout-payment__options').length > 0 && response.payment_option) {
          jQuery('.checkout-payment__options').html(response.payment_option);
        }
        jQuery.unblockUI();
        jQuery("#currency_switcher").trigger("change")
      }
    });
  });
}
jQuery('body').on('click', function () {
  var selector = jQuery('nav.mobile-nav div#navbar button.mega-toggle-animated')
  var isExpanded = jQuery(selector).attr("aria-expanded")
  if (isExpanded == "true") {
    // jQuery(".mobile-menu-toggle").addClass("position-absolute w-100 p-0")
    jQuery("nav.mobile-nav div#navbar").addClass("w-100");
    jQuery('html, body').css('overflow', 'hidden');
    if (jQuery("header.header-main").hasClass("add-shadow")) {
      var screenHeight = jQuery(window).outerHeight() - 90
    } else {
      var screenHeight = jQuery(window).outerHeight() - 170
    }   
    jQuery("div#navbar ul#mega-menu-main-menu").height(screenHeight)
    // jQuery("div#navbar .mega-menu-toggle.mega-menu-open").css("background", "#000")
  }
  else{
    jQuery('html, body').css('overflow', 'scroll');
    // jQuery(".mobile-menu-toggle").removeClass("position-absolute w-100 p-0")
    jQuery("nav.mobile-nav div#navbar").removeClass("w-100")
    resetMobileMenu()
    // jQuery("div#navbar .mega-menu-toggle.mega-menu-open").css("background", "#fff")
  }
  var pdpNav = jQuery(".overview-menu-mobile .accordion-button")
  var isPdpNavExpanded = jQuery(pdpNav).attr("aria-expanded")
  if (isPdpNavExpanded == "true") {
    var screenHeightPdp = jQuery(window).outerHeight() - 110    
    jQuery(".overview-menu-mobile").height(screenHeightPdp)
    jQuery(".overview-menu-mobile").css("overflow", "scroll")
  } else {
    jQuery(".overview-menu-mobile").css("height", "auto")
  }

  setTimeout(function () {    
    var searchContainer = jQuery('nav.mobile-nav .search-in-header')
    var isSearchExpanded = jQuery(searchContainer).attr("aria-expanded")
    if (isSearchExpanded == "true") {
      var screenHeightSearch = jQuery(window).outerHeight() - 60   
      jQuery(".search-container").css("overflow", "scroll")
      jQuery("#collapseExampleSearch").height(screenHeightSearch)
    }
  },500) 
});
jQuery('body').on('input, click, oninput, onpaste, change', '.medical_validation_checkboxes, .emergency_validation_inputs, .gear_validation_inputs, .passport_validation_inputs, .bike_validation_select', function () {
  prebookingChecklistValidate();
});
jQuery(document).on('change', 'select[name^="occupants["]', function () {
  var Currentname = jQuery(this).attr('name');
  var CurrentVal = jQuery(this).val();
  jQuery('select[name^="occupants["]').each(function () {
    var name = jQuery(this).attr('name');
    var val = jQuery(this).val();
    if (CurrentVal == val && Currentname != name) {
      jQuery(`select[name^="${name}"`).val('none');
    }
  });
});
jQuery(document).on('click', 'input[name="is_same_billing_as_mailing"]', function () {
  var isMailingChecked = jQuery('input[name="is_same_billing_as_mailing"]').is(':checked');
  var shipping_fname = jQuery('input[name="shipping_first_name"]').val();
  var shipping_lname = jQuery('input[name="shipping_last_name"]').val();
  var shipping_add1 = jQuery('input[name="shipping_address_1"]').val();
  var shipping_add2 = jQuery('input[name="shipping_address_2"]').val();
  var shipping_country = jQuery('select[name="shipping_country"]').val();
  var shipping_city = jQuery('input[name="shipping_city"]').val();
  var shipping_state = jQuery('select[name="shipping_state"]').val();
  var shipping_postcode = jQuery('input[name="shipping_postcode"]').val();
  
  var billingCountry = jQuery('select[name="billing_country"]').val()
  var billingState = jQuery('select[name="billing_state"]').val()
  
  if( isMailingChecked == true ){
    jQuery('input[name="billing_first_name"]').val(shipping_fname);
    jQuery('input[name="billing_last_name"]').val(shipping_lname);
    jQuery('input[name="billing_address_1"]').val(shipping_add1);
    jQuery('input[name="billing_address_2"]').val(shipping_add2);
    jQuery('select[name="billing_country"]').val(shipping_country);
    jQuery('input[name="billing_city"]').val(shipping_city);
    if( jQuery('select[name="billing_state"]').length > 0  ){
      jQuery('select[name="billing_state"]').val(shipping_state);
    }
    if( jQuery('input[name="billing_state"]').length > 0  ){
      jQuery('input[name="billing_state"]').val(shipping_state);
    }
    jQuery('input[name="billing_postcode"]').val(shipping_postcode);
  }else{
    if (billingCountry == '') {
      jQuery('select[name="billing_country"]').val('').trigger('change');
      jQuery('select[name="billing_state"]').val('').trigger('change');
      jQuery('input[name="billing_state"]').val('').trigger('change');
    }
    // jQuery('input[name="billing_first_name"]').val('');
    // jQuery('input[name="billing_last_name"]').val('');
    // jQuery('input[name="billing_address_1"]').val('');
    // jQuery('input[name="billing_address_2"]').val('');
    // jQuery('select[name="billing_country"]').trigger('change');
    // jQuery('input[name="billing_city"]').val('');
    // jQuery('select[name="billing_state"]').trigger('change');
    // jQuery('input[name="billing_postcode"]').val('');
  }
});
jQuery('body').on('click', '.bike_selectionElementchk', function () {
  var bikeTypeId = jQuery(this).attr('data-id');
  var order_id = jQuery('input[name="wc_order_id"]').val();
  var actionName = 'tt_chk_bike_selection_ajax_action';
  var parentDiv = jQuery(this).closest('.checkout-bikes__bike-grid');
  parentDiv.find('.bike_selectionElementchk .radio-selection').removeClass("checkout-bikes__selected-bike-icon");
  parentDiv.find('.bike_selectionElementchk .radio-selection').addClass("checkout-bikes__select-bike-icon");
  parentDiv.find('.bike_selectionElementchk').removeClass("bike-selected");
  jQuery(this).find('.radio-selection').addClass("checkout-bikes__selected-bike-icon");
  jQuery(this).addClass("bike-selected");
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: { 'action': actionName, 'bikeTypeId': bikeTypeId, order_id: order_id },
    dataType: 'json',
    beforeSend: function () {
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      if (response.size_opts) {
        jQuery('select[name="tt-bike-size"]').html(response.size_opts);
      }
      jQuery('input[name="bikeTypeId"]').val(bikeTypeId);
      // Trigger complete status check after bike model changed.
      prebookingChecklistValidate();
      jQuery.unblockUI();
      jQuery("#currency_switcher").trigger("change")
    }
  });
});
jQuery('body').on('change', '.tt_chk_bike_size_change', function () {
  var bikeTypeId = jQuery('input[name="bikeModelId"]:checked').val();
  var bike_size = jQuery(this).val();
  var order_id = jQuery('input[name="wc_order_id"]').val();
  var actionName = 'tt_chk_bike_size_change_ajax_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: { 'action': actionName, 'bike_size': bike_size, 'bikeTypeId': bikeTypeId, order_id: order_id },
    dataType: 'json',
    beforeSend: function () {
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      if (response.status == true) {
        jQuery('input[name="bikeId"]').val(response.bike_id);
        ///jQuery('input[name="bikeId"]').val(bikeTypeId);
      }
      jQuery.unblockUI();
    }
  });
});

jQuery('.read-more-action').on('click', function (e) {
  e.preventDefault();
  jQuery(this).closest('.pdp-itinerary-day__accordion-clamp_main').find('span.less-text').toggleClass("d-none")
  jQuery(this).closest('.pdp-itinerary-day__accordion-clamp_main').find('span.more-text').toggleClass("d-none")
  jQuery(this).toggleClass("d-none")
})
jQuery('.read-more-action-right').on('click', function (e) {
  e.preventDefault();
  jQuery(this).closest('.pdp-itinerary-day__accordion-right-clamp').find('span.less-text').toggleClass("d-none")
  jQuery(this).closest('.pdp-itinerary-day__accordion-right-clamp').find('span.more-text').toggleClass("d-none")
  jQuery(this).toggleClass("d-none")
})

// Listen for clicks on the document and check if the target is the #itinerary-print-button
jQuery(document).on('click', '#itinerary-print-button', function (e) {
  e.preventDefault();

  // Select elements with the specified classes
  var collapseButtons = jQuery('.accordion-button.mb-0.collapsed');

  // Check if any elements were found
  if (collapseButtons.length > 0) {
      // Iterate through the found elements and click each one
      collapseButtons.each(function() {
          jQuery(this).click(); // Trigger a click event on each element
      });
  }
  // Click on all <a> elements with the class "read-more-action"
  jQuery('a.read-more-action').each(function() {
    if (! jQuery(this).hasClass('d-none')) {
      jQuery(this).click();
    }
  });

  // Click on all <a> elements with the class "read-more-action"
  jQuery('a.read-more-action-right').each(function() {
    if (! jQuery(this).hasClass('d-none')) {
      jQuery(this).click();
    }
  });

  // Check if a button with classes "olark-launch-button" and "olark-size-md" exists
  var checkOlarkButton = function () {
    var olarkButton = jQuery('.olark-launch-button.olark-size-md');
    if (olarkButton.length > 0) {
      // Check if the olarkButton has the class "olark-text-button"
      if (!olarkButton.hasClass('olark-text-button') && !olarkButton.parent().parent().parent().hasClass('olark-hidden') ) {
        // Trigger a click event on the button if it doesn't have the class
        olarkButton.click();
      }
    } else {
      // If olarkButton is not found, check again after a delay
      setTimeout(checkOlarkButton, 1000); // You can adjust the delay as needed
    }
  };
  checkOlarkButton(); // Start checking for the olarkButton


  

  window.print();
});


window.addEventListener("beforeprint", (event) => {
  jQuery("header").css("display", "none")
  jQuery("footer").css("display", "none")
  jQuery("div.tour-actions").css("display", "none")
  jQuery(".elementor-popup-modal").css("display", "none")
  jQuery(".copyright").css("display", "none")
  jQuery(".promo-banner").css("display", "none")
  jQuery(".accordion-actions a.expand-all").trigger("click")
  jQuery(".accordion-actions").css("display", "none")
});

window.addEventListener("afterprint", (event) => {
  location.reload();
})


jQuery('body').on('change', '.tt_rider_level_select', function () {
  var selectedRiderLevel = jQuery(this).val()
  if (selectedRiderLevel && selectedRiderLevel > 0) {    
    var tripRiderLevel = trek_JS_obj.rider_level
    var riderLevelText = trek_JS_obj.rider_level_text
    if (selectedRiderLevel < tripRiderLevel) {
      jQuery(".modal-rider-level-warning #rider_level_text").text(riderLevelText)
      jQuery('#checkoutRiderLevelModal').modal('toggle');    
    }
    jQuery(this).closest('div.form-floating').find(".rider-select").css("display", "none")
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').addClass('woocommerce-validated');
  } else {
    jQuery(this).closest('div.form-floating').find(".rider-select").css("display", "none")
    jQuery(this).closest('div.form-floating').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-validated');

  }
})

jQuery('body').on('change', '.form-select', function () {
  var selectValue = jQuery(this).val()
  if (selectValue == 'none' || selectValue == '' || selectValue == null) {
    jQuery(this).closest('div.form-floating').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-validated');
  } else if ((selectValue) || selectValue == 'men' || selectValue == 'woman' ) {
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').addClass('woocommerce-validated');
  } else {
    jQuery(this).closest('div.form-floating').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-validated');
  }
})

jQuery('body').on('change', '.form-control', function () {
  var selectValue = jQuery(this).val()
  if ((selectValue) ) {
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').addClass('woocommerce-validated');
  } else {
    jQuery(this).closest('div.form-floating').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-validated');
  }
})

jQuery('body').on('keyup', '.lost_reset_password #password_2', function () {
    if (
      jQuery("#password_1").val() != "" &&
      jQuery("#password_2").val() != "" &&
      jQuery("#password_1").val() == jQuery("#password_2").val()
    ) {
      document.getElementById("password_2").setCustomValidity("");
    }
    else{
      document.getElementById("password_2").setCustomValidity("Invalid field.");
      return false;
    }
})

jQuery('body').on('keyup', '.my-account-password-reset #password_2', function () {
  if (
    jQuery("#password_1").val() != "" &&
    jQuery("#password_2").val() != "" &&
    jQuery("#password_1").val() == jQuery("#password_2").val()
  ) {
    document.getElementById("password_2").setCustomValidity("");
  }
  else{
    document.getElementById("password_2").setCustomValidity("Invalid field.");
    return false;
  }
})

jQuery('.communication-preferences .form-switch input').on('change', function (e) {
  if (e.target.checked) {
    jQuery(this).closest(".form-switch").find("label").text("Subscribed")
  }
  else{
    jQuery(this).closest(".form-switch").find("label").text("Not subscribed")
  }
});
jQuery('button[data-bs-target="#protection_modal"], input.protection_modal_ev').on('click', function (e) {
  var action = 'tt_generate_save_insurance_quote';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: "&action=" + action,
    dataType: 'json',
    beforeSend: function () {
      jQuery('#protection_modal .modal-content').block({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      var resMessage = '';
      if (response.status == true) {
        resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
      } else {
        resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
      }
      if (response.review_order) {
        jQuery('#tt-review-order').html(response.review_order);
      }
      setTimeout( function(){
        jQuery('#protection_modal .modal-content').unblock()
        jQuery("#currency_switcher").trigger("change")
      }, 500);
      if (response.guest_insurance_html) {
        jQuery('#travel-protection-div').html(response.guest_insurance_html);
      }
      if (response.insuredHTMLPopup) {
        jQuery(`#tt-popup-insured-form`).html(response.insuredHTMLPopup);
      }
      if (jQuery('.checkout-payment__options').length > 0 && response.payment_option) {
        jQuery('.checkout-payment__options').html(response.payment_option);
      }
    }
  });
  jQuery("#currency_switcher").trigger("change")
});

function isMobileDevice() {
  var isMobile = false; //initiate as false
  // device detection
  if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
      || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) { 
      isMobile = true;
  }
  return isMobile
}
function checkguestNumberChangeStatus(){
    var single = jQuery('input[name="single"]').val() || 0;
    var double = jQuery('input[name="double"]').val() || 0;
    var private = jQuery('input[name="private"]').val() || 0;
    var roommate = jQuery('input[name="roommate"]').val() || 0;
    var no_of_guests = jQuery('input[name="no_of_guests"]').val() || 1;
    var allroomsTotal = (parseInt(single) * 2) + (parseInt(double) * 2) + parseInt(private) + parseInt(roommate);
    if( allroomsTotal > 0 ){
      return true
    }else{
      return false;
    }
}

jQuery('body').on('show.bs.collapse', '#rooms #multiCollapseExample1', function () {
  document.body.scrollTop = document.documentElement.scrollTop = 0
})

jQuery('body').on('click', '.guest-checkout .guestCounterAction', function () {
  if (checkguestNumberChangeStatus()) {
    jQuery('#checkoutGuestChangeModal').modal('toggle');
  }
})

function removeParamFromUrl(paramName, url) {
  // Split the URL into its components
  var urlParts = url.split("?");

  // If there is a query string
  if (urlParts.length > 1) {
    // Get the query string parameters
    var queryString = urlParts[1];

    // Split the query string parameters into an array
    var params = queryString.split("&");

    // Loop through the parameters
    for (var i = 0; i < params.length; i++) {
      // Check if the current parameter matches the parameter we want to remove
      if (params[i].split("=")[0] === paramName) {
        // Remove the parameter from the array
        params.splice(i, 1);
      }
    }

    // Join the parameters back together
    queryString = params.join("&");

    // Reconstruct the URL
    url = urlParts[0] + "?" + queryString;
  }

  // Return the new URL
  return url;
}
jQuery('body').on('change', '.tt_jersey_style_change', function () {
  var guest_index = jQuery(this).attr('data-guest-index');
  var jerseySizeElement;
  if( guest_index == 0 ){
    jerseySizeElement = 'select[name="bike_gears[primary][jersey_size]"]';
  }
  if( guest_index != 0 && guest_index != '00' && guest_index != '01' ){
    jerseySizeElement = `select[name="bike_gears[guests][${guest_index}][jersey_size]"]`;
  }
  if( guest_index == '00' ){
    jerseySizeElement = `select[name="tt-jerrsey-size"]`;
  }
  if( guest_index == '01' ){
    jerseySizeElement = `select[name="gear_preferences_jersey_size"]`;
  }
  var jersey_style = jQuery(this).val();
  var actionName = 'tt_jersey_change_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: { 'action': actionName, 'jersey_style': jersey_style },
    dataType: 'json',
    beforeSend: function () {
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      if (response.status == true) {
        jQuery(jerseySizeElement).html(response.opts);
      }
      jQuery.unblockUI();
    }
  });
});

function assignEditOccupantsCtaShowHide() {

  var single = jQuery('input[name="single"]').val();
  var double = jQuery('input[name="double"]').val();
  var private = jQuery('input[name="private"]').val();
  var roommate = jQuery('input[name="roommate"]').val();
  
  if (single > 0) {
    if (jQuery("#tt-single-occupants:empty").length > 0) {
      jQuery('div[data-room="s-assign"]').show();
      jQuery('div[data-room="s"]').hide();
    }
    else{
      jQuery('div[data-room="s"]').show();
      jQuery('div[data-room="s-assign"]').hide();
    }
  } else {
    jQuery('div[data-room="s"]').hide();
    jQuery('div[data-room="s-assign"]').hide();
  }
  if (private > 0) {
    if (jQuery("#tt-private-occupants:empty").length > 0) {
      jQuery('div[data-room="p-assign"]').show();
      jQuery('div[data-room="p"]').hide();
    }
    else{
      jQuery('div[data-room="p"]').show();
      jQuery('div[data-room="p-assign"]').hide();
    }
  } else {
    jQuery('div[data-room="p"]').hide();
    jQuery('div[data-room="p-assign"]').hide();
  }
  if (roommate > 0) {
    if (jQuery("#tt-roommate-occupants:empty").length > 0) {
      jQuery('div[data-room="r-assign"]').show();
      jQuery('div[data-room="r"]').hide();
    }
    else{
      jQuery('div[data-room="r"]').show();
      jQuery('div[data-room="r-assign"]').hide();
    }
  } else {
    jQuery('div[data-room="r"]').hide();
    jQuery('div[data-room="r-assign"]').hide();
  }
  if (double > 0) {
    if (jQuery("#tt-double1Bed-occupants:empty").length > 0) {
      jQuery('div[data-room="d-assign"]').show();
      jQuery('div[data-room="d"]').hide();
    }
    else{
      jQuery('div[data-room="d"]').show();
      jQuery('div[data-room="d-assign"]').hide();
    }
  } else {
    jQuery('div[data-room="d"]').hide();
    jQuery('div[data-room="d-assign"]').hide();
  }
}

jQuery(document).ready(function() {
  var maxHeightPricing = 0;
  jQuery(".compare-pricing").each(function() {
    maxHeightPricing = Math.max(maxHeightPricing, jQuery(this).height());
  });  
  jQuery(".compare-pricing").height(maxHeightPricing);
  
  var maxHeightProduct = 0;
  jQuery(".product-info-mobile").each(function() {
    maxHeightProduct = Math.max(maxHeightProduct, jQuery(this).height());
  });  
  jQuery(".product-info-mobile").height(maxHeightProduct);
  jQuery(".product-info-mobile").addClass("position-relative");
  jQuery(".product-info-mobile a").addClass("position-absolute bottom-0");

  var maxHeightDesc = 0;
  jQuery(".compare-description").each(function() {
    maxHeightDesc = Math.max(maxHeightDesc, jQuery(this).height());
  });  
  jQuery(".compare-description").height(maxHeightDesc);

  var maxHeightRider = 0;
  jQuery(".compare-rider").each(function() {
    maxHeightRider = Math.max(maxHeightRider, jQuery(this).height());
  });  
  jQuery(".compare-rider").height(maxHeightRider);
  
  var maxHeightDur = 0;
  jQuery(".compare-duration").each(function() {
    maxHeightDur = Math.max(maxHeightDur, jQuery(this).height());
  });  
  jQuery(".compare-duration").height(maxHeightDur);

  var maxHeightStyle = 0;
  jQuery(".compare-style").each(function() {
    maxHeightStyle = Math.max(maxHeightStyle, jQuery(this).height());
  });  
  jQuery(".compare-style").height(maxHeightStyle);

  var maxHeightHotel = 0;
  jQuery(".compare-hotel").each(function() {
    maxHeightHotel = Math.max(maxHeightHotel, jQuery(this).height());
  });  
  jQuery(".compare-hotel").height(maxHeightHotel);

  var maxHeightBikes = 0;
  jQuery(".compare-bikes").each(function() {
    maxHeightBikes = Math.max(maxHeightBikes, jQuery(this).height());
  });  
  jQuery(".compare-bikes").height(maxHeightBikes);

  var maxHeightProduct1 = 0;
  jQuery(".product-info-desktop").each(function() {
    maxHeightProduct1 = Math.max(maxHeightProduct1, jQuery(this).height());
  });  
  jQuery(".product-info-desktop").height(maxHeightProduct1);

  jQuery('#testimonials .card-text').each(function() {
    var $text = jQuery(this);
    var $button = $text.siblings('.read-more');
    var lineHeight = parseFloat($text.css('line-height'));
    var textHeight = $text.height();
    var numberOfLines = Math.round(textHeight / lineHeight);
    if(numberOfLines > 3) {
      $text.addClass('long-text');
      $button.css('display','block');
    }
  })

  var maxHeight = 0;
  jQuery('#testimonials .card-body > div').each(function() {
    var thisH = jQuery(this).height();
    if (thisH > maxHeight) { maxHeight = thisH; }
  })
  jQuery('#testimonials .card-body > div').height(maxHeight + 16);

  var maxHeightAuthor = 0;
  jQuery('#testimonials .card-body .text-author').each(function() {
    var thisH = jQuery(this).height();
    if (thisH > maxHeightAuthor) { maxHeightAuthor = thisH; }
  })
  jQuery('#testimonials .card-body .text-author').height(maxHeightAuthor + 16);

  jQuery('#testimonials .read-more').on("click", function(){
      var $this = jQuery(this);
      $this.siblings('.card-text').toggleClass("is-expanded");
      if($this.siblings('.card-text').hasClass("is-expanded")){
          jQuery(this).text("Show less");
          jQuery(this).parent().height('auto');
      } else {
        jQuery(this).text("Read more");
        jQuery(this).parent().height(maxHeight + 16)
      }
  });

})

jQuery('#search-input').on('keyup', function() {
  var value = jQuery(this).val();
  if (value !== '') {
    jQuery(".clear-input").css("display", "block")
    // The input value is not empty.
  } else {
    jQuery(".clear-input").css("display", "none")
    // The input value is empty.
  }
});

jQuery('.medical-info-toast .btn-close').on('click', function() {
  const toastMessage = jQuery('.medical-info-toast');
  toastMessage.toggle('show');
})
window.addEventListener('load', () => {
  const recaptcha = document.querySelector('#g-recaptcha-response');
  if (recaptcha) {
    recaptcha.setAttribute('required', 'required');
  }
})

// jQuery('.search-container').mouseleave(function() {
//   // Allow the background page to scroll again
//   var isSearchExpanded = jQuery("#search-input").attr("aria-expanded")
//   if (isSearchExpanded == "true") {
//     jQuery('html, body').css('overflow', 'hidden');
//   } else {
//     jQuery('html, body').css('overflow', 'auto');
//   }
//   // jQuery('html, body').css('overflow', 'auto');
// });
// jQuery('.search-container').mouseenter(function() {
//   // Allow the background page to scroll again
//   jQuery('html, body').css('overflow', 'hidden');
// });
// jQuery('.search-container').touchend(function() {
//   // Allow the background page to scroll again
//   jQuery('html, body').css('overflow', 'auto');
// });
// jQuery('.search-container').touchstart(function() {
//   // Allow the background page to scroll again
//   jQuery('html, body').css('overflow', 'hidden');
// });
function updateCompareLink() {
  // Check the number of trips in the compare list
  var tripCount = jQuery('.compare-product').length;

  // Disable or enable the "Compare" link based on the trip count
  if (tripCount < 2) {
    jQuery('.woocommerce-products-compare-compare-link').on('click', function(event) {
      event.preventDefault(); // Prevent the default click action
      return false; // Stop further propagation of the event
    }).addClass('disabled');
  } else {
    jQuery('.woocommerce-products-compare-compare-link').off('click').removeClass('disabled');
  }
}

// Call the function to update the "Compare" link state when the page loads
jQuery(document).ready(function() {
  updateCompareLink();
});
jQuery('body').on("change", 'input[name="no_of_guests"]', function (){
  var inputValue = jQuery(this).val()
  var tripLimit = trek_JS_obj.trip_booking_limit.remaining
  if (!Number.isInteger(parseInt(inputValue)) || parseInt(inputValue) <= 0) {
    jQuery(this).val(1)
  }
  if (parseInt(inputValue) > parseInt(tripLimit)) {
    jQuery(this).val(tripLimit)
    jQuery('div[id="plus"]').css('pointer-events', 'none');
    jQuery('.limit-reached-feedback').css('display', 'block');
  } else{
    jQuery('div[id="plus"]').css('pointer-events', 'auto');
    jQuery('.limit-reached-feedback').css('display', 'none');
  }
  if (checkguestNumberChangeStatus()) {
    jQuery('#checkoutGuestChangeModal').modal('toggle');
  }
})
jQuery("body").on("keyup", ".promo-input", function(e){
  if (e.key === 'Enter' || e.keyCode === 13) {
    e.preventDefault();
    jQuery(".tt_apply_coupan").trigger("click")
  }
});

jQuery('.mega-toggle-blocks-left').on('click', function(e) {
  if(jQuery('.mega-toggle-on').length == 1 || jQuery('.mega-toggle-on').length == 0 ) {
    jQuery('.mobile-nav #mega-menu-main-menu').children('li').removeClass('d-none');
    jQuery('.mega-menu-toggle.mega-menu-open').removeClass('active');
    jQuery('.mega-menu-open .mega-toggle-blocks-center').hide();
  } else {
    var menuTitle = jQuery('.mega-menu-item.mega-toggle-on').first().children('a').text();
    jQuery('.mega-toggle-blocks-center').text(menuTitle);
    jQuery('.mega-menu-toggle.mega-menu-open').addClass('active');
    jQuery('.mega-menu-open .mega-toggle-blocks-center').show();
  }

  var LastOpenMenu = jQuery('.mega-toggle-on').last();
  LastOpenMenu.removeClass('mega-toggle-on');
  LastOpenMenu.children('ul').hide();
  LastOpenMenu.children('a').show();
  LastOpenMenu.parent().parent().siblings('li').removeClass('d-none');
  e.preventDefault();
  e.stopPropagation();

  // i finished with Mobile UX menu, trip finder menu, mburger menu on tablet and product price. Tomorrow i will finish the mobile filters.
})

jQuery('body').on('click', 'nav #mega-menu-wrap-main-menu li.mega-menu-item a', function (e) {
  if(jQuery(this).attr('href') === undefined) {
    jQuery(".mega-toggle-blocks-center").text(jQuery(this).text())
    jQuery(this).parent().siblings().addClass("d-none");
    jQuery(".mega-toggle-blocks-left a.mega-icon").attr("level", 1)
    jQuery("nav #mega-menu-wrap-main-menu .mega-toggle-blocks-left a.mega-icon").show()
    jQuery(this).hide();
  
    jQuery('.mega-menu-toggle.mega-menu-open').addClass('active');
    jQuery('.mega-menu-open .mega-toggle-blocks-center').show();
    var backIconCode = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 19L8 12L15 5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    var backIcon = "data:image/svg+xml," + encodeURIComponent(backIconCode);
    jQuery(".mega-toggle-blocks-left").css('background-image', 'url(' + backIcon + ')');
  }
});

jQuery("nav #mega-menu-wrap-main-menu li.mega-menu-item a .mega-indicator").on("click", function() { 
  jQuery(".mega-toggle-blocks-center").text(jQuery(this).parent().text())
  jQuery(this).parent().parent().siblings().addClass("d-none");
  jQuery(".mega-toggle-blocks-left a.mega-icon").attr("level", 1)
  jQuery("nav #mega-menu-wrap-main-menu .mega-toggle-blocks-left a.mega-icon").show()
  jQuery(this).parent().hide()
});

jQuery('body').on('click', 'nav #mega-menu-wrap-main-menu .mega-category-sub-menu ul.mega-sub-menu li a', function (e) {
  if(jQuery(this).attr('href') === undefined) {
    let parentText = jQuery(this).parents("li.mega-menu-item").eq(1).find("a").eq(0).text()
    jQuery("nav #mega-menu-wrap-main-menu .mega-toggle-blocks-left a.mega-icon").attr("parent-text", parentText)
    jQuery(this).parent().parent().parent().siblings().addClass("d-none");
    jQuery(".mega-toggle-blocks-left a.mega-icon").attr("level", 2)
    jQuery(this).hide();
  }
});

jQuery('nav #mega-menu-wrap-main-menu .mega-category-sub-menu ul.mega-sub-menu li a .mega-indicator').on('click', function () {
  let parentText = jQuery(this).parents("li.mega-menu-item").eq(1).find("a").eq(0).text()
  jQuery("nav #mega-menu-wrap-main-menu .mega-toggle-blocks-left a.mega-icon").attr("parent-text", parentText)
  jQuery(this).parent().parent().parent().parent().siblings().addClass("d-none");
  jQuery(".mega-toggle-blocks-left a.mega-icon").attr("level", 2)
  jQuery(this).parent().hide()
});

jQuery('body').on('click', 'nav #mega-menu-wrap-main-menu .mega-toggle-blocks-left ', function (e) {
  var level = jQuery(this).attr("level")
  if (level == 1) {
    jQuery("#mega-menu-wrap-main-menu li.mega-menu-item").removeClass("d-none mega-toggle-on")
    jQuery("#mega-menu-wrap-main-menu li.mega-menu-item").find("a").attr("aria-expanded", "false")
    jQuery("#mega-menu-wrap-main-menu li.mega-menu-item").find("a").show()
    jQuery(this).attr("level", '')
    jQuery(this).hide()
    jQuery(".mega-toggle-blocks-center").text('');
    jQuery(".mega-toggle-blocks-left").css('background-image', 'none');
    jQuery('.mega-menu-toggle.mega-menu-open').removeClass('active');
  }
  if (level == 2) {
    jQuery("#mega-menu-wrap-main-menu .mega-category-sub-menu").removeClass("d-none mega-toggle-on")
    jQuery("#mega-menu-wrap-main-menu .mega-category-sub-menu li").removeClass("d-none mega-toggle-on")
    jQuery("#mega-menu-wrap-main-menu .mega-category-sub-menu").find("a").attr("aria-expanded", "false")
    jQuery("#mega-menu-wrap-main-menu .mega-category-sub-menu").find("a").show()
    jQuery(this).attr("level", 1)
    jQuery(".mega-toggle-blocks-center").text(jQuery(this).attr("parent-text"))      
  }
})


//Header mobile select first element with class hide-on-desktop
jQuery( document ).ready(function() {
  jQuery('.mega-menu-toggle').on('click', function() {
  if(jQuery(this).hasClass('mega-menu-open')) {
    jQuery('.mobile-menu-toggle').css('position','relative');
    jQuery('.mobile-logo .navbar-brand').css('margin-left','auto');
    jQuery('.search-icon').show();
    jQuery('.phone-icon').show();
    jQuery('.calendar-icon').show();
  } else {
    jQuery('.mobile-menu-toggle').css('position','static');
    jQuery('.mobile-logo .navbar-brand').css('margin-left',0);
    jQuery('.search-icon').hide();
    jQuery('.phone-icon').hide();
    jQuery('.calendar-icon').hide();
  }
 })
	jQuery('.mobile-menu-toggle ul#mega-menu-main-menu > .mega-hide-on-desktop:first').addClass('first-gray-elem');
});

function resetMobileMenu(){
  jQuery("#mega-menu-wrap-main-menu li.mega-menu-item").removeClass("d-none mega-toggle-on")
  jQuery("#mega-menu-wrap-main-menu li.mega-menu-item").find("a").attr("aria-expanded", "false")
  jQuery("#mega-menu-wrap-main-menu .mega-category-sub-menu").removeClass("d-none mega-toggle-on")
  jQuery("#mega-menu-wrap-main-menu .mega-category-sub-menu li").removeClass("d-none mega-toggle-on")
  jQuery("#mega-menu-wrap-main-menu .mega-category-sub-menu").find("a").attr("aria-expanded", "false")
  jQuery("#mega-menu-wrap-main-menu li.mega-menu-item").find("a").show()
  jQuery("nav #mega-menu-wrap-main-menu .mega-toggle-blocks-left a.mega-icon").attr("level", '')
  jQuery("nav #mega-menu-wrap-main-menu .mega-toggle-blocks-left a.mega-icon").hide()
  jQuery(".mega-toggle-blocks-center").text('');
  jQuery(".mega-toggle-blocks-left").css('background-image', 'none');
  jQuery('.mega-menu-toggle.mega-menu-open').removeClass('active');
}

jQuery('body').on('shown.bs.modal', '#globalSearchModal', function () {
  isSticked = jQuery("header.header-main").hasClass("add-shadow")
  if (window.matchMedia('(min-width: 768px)').matches && window.matchMedia('(max-width: 1439px)').matches) {
    if (isSticked) {
      jQuery("#globalSearchModal").css("top", "7%")
      jQuery(".modal-backdrop.show").css("top", "15%")
    } else {
      jQuery("#globalSearchModal").css("top", "13%")
      jQuery(".modal-backdrop.show").css("top", "15%")
    }
  }
  if (window.matchMedia('(min-width: 1440px)').matches) {
    if (isSticked) {
      jQuery("#globalSearchModal").css("top", "6%")
      jQuery(".modal-backdrop.show").css("top", "15%")
    } else {
      jQuery("#globalSearchModal").css("top", "11%")
      jQuery(".modal-backdrop.show").css("top", "15%")
    }
  }
});

jQuery(document).on('change blur', 'input[name="shipping_first_name"]', function () {
  jQuery('input[name="shipping_first_name"]').attr('required', 'required');
  if (tt_validate_name(jQuery('input[name="shipping_first_name"]').val()) == false) {
    jQuery(`input[name="shipping_first_name"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="shipping_first_name"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="shipping_first_name"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="shipping_first_name"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="shipping_last_name"]', function () {
  jQuery('input[name="shipping_last_name"]').attr('required', 'required');
  if (tt_validate_name(jQuery('input[name="shipping_last_name"]').val()) == false) {
    jQuery(`input[name="shipping_last_name"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="shipping_last_name"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="shipping_last_name"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="shipping_last_name"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="shipping_phone"]', function () {
  jQuery('input[name="shipping_phone"]').attr('required', 'required');
  if (tt_validate_phone(jQuery('input[name="shipping_phone"]').val()) == false) {
    jQuery(`input[name="shipping_phone"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="shipping_phone"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="shipping_phone"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="shipping_phone"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="custentity_birthdate"]', function () {
  jQuery('input[name="custentity_birthdate"]').attr('required', 'required');
  if (tt_validate_age(jQuery('input[name="custentity_birthdate"]').val()) == false || jQuery('input[name="custentity_birthdate"]').val().length == 0) {
    jQuery(`input[name="custentity_birthdate"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="custentity_birthdate"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="custentity_birthdate"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="custentity_birthdate"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="shipping_address_1"]', function () {
  jQuery('input[name="shipping_address_1"]').attr('required', 'required');
  if (jQuery('input[name="shipping_address_1"]').val() == false) {
    jQuery(`input[name="shipping_address_1"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="shipping_address_1"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="shipping_address_1"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="shipping_address_1"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="shipping_city"]', function () {
  jQuery('input[name="shipping_city"]').attr('required', 'required');
  if (jQuery('input[name="shipping_city"]').val() == false) {
    jQuery(`input[name="shipping_city"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="shipping_city"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="shipping_city"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="shipping_city"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="shipping_postcode"]', function () {
  jQuery('input[name="shipping_postcode"]').attr('required', 'required');
  if (jQuery('input[name="shipping_postcode"]').val() == false) {
    jQuery(`input[name="shipping_postcode"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="shipping_postcode"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="shipping_postcode"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="shipping_postcode"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change', 'select[name="shipping_country"]', function () {
  jQuery(`select[name="shipping_state"]`).closest('div.form-row').removeClass('woocommerce-invalid');
  jQuery(`select[name="shipping_state"]`).closest('div.form-row').removeClass('woocommerce-validated');
  jQuery(`select[name="shipping_state"]`).closest('div.form-row').find(".invalid-feedback").css("display", "none");
});


jQuery(document).on('select2:close', 'select[name="shipping_country"]', function () {
  if (jQuery('select[name="shipping_country"]').val() == 'Select a country / region…' || jQuery('select[name="shipping_country"]').val() == '' ) {
    jQuery(`select[name="shipping_country"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`select[name="shipping_country"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(`select[name="shipping_country"]`).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`select[name="shipping_country"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`select[name="shipping_country"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(`select[name="shipping_country"]`).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('select2:close', 'select[name="shipping_state"]', function () {
  if (jQuery('select[name="shipping_state"]').val() == null || jQuery('select[name="shipping_state"]').val() == '' ) {
    jQuery(`select[name="shipping_state"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`select[name="shipping_state"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(`select[name="shipping_state"]`).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`select[name="shipping_state"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`select[name="shipping_state"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(`select[name="shipping_state"]`).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name*="[guest_fname]"]', function () {
  jQuery(this).attr('required', 'required'); // Add the required attribute to the current input
  if (tt_validate_name(jQuery(this).val()) == false) {
    jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(this).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name*="[guest_lname]"]', function () {
  jQuery(this).attr('required', 'required'); // Add the required attribute to the current input
  if (tt_validate_name(jQuery(this).val()) == false) {
    jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(this).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name*="[guest_email]"]', function () {
  jQuery(this).attr('required', 'required'); // Add the required attribute to the current input
  if (tt_validate_email(jQuery(this).val()) == false || jQuery(this).val() == '') {
    jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(this).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name*="[guest_phone]"]', function () {
  jQuery(this).attr('required', 'required'); // Add the required attribute to the current input
  if (tt_validate_phone(jQuery(this).val()) == false) {
    jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(this).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name*="[guest_gender]"]', function () {
  jQuery(this).attr('required', 'required'); // Add the required attribute to the current input
  if (tt_validate_phone(jQuery(this).val()) == false) {
    jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(this).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name*="[guest_dob]"]', function () {
  jQuery(this).attr('required', 'required'); // Add the required attribute to the current input
  if (tt_validate_age(jQuery(this).val()) == false || jQuery(this).val() == "" ) {
    jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(this).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="billing_first_name"]', function () {
  jQuery('input[name="billing_first_name"]').attr('required', 'required');
  if (tt_validate_name(jQuery('input[name="billing_first_name"]').val()) == false) {
    jQuery(`input[name="billing_first_name"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="billing_first_name"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="billing_first_name"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="billing_first_name"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="billing_last_name"]', function () {
  jQuery('input[name="billing_last_name"]').attr('required', 'required');
  if (tt_validate_name(jQuery('input[name="billing_last_name"]').val()) == false) {
    jQuery(`input[name="billing_last_name"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="billing_last_name"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="billing_last_name"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="billing_last_name"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="billing_address_1"]', function () {
  jQuery('input[name="billing_address_1"]').attr('required', 'required');
  if (jQuery('input[name="billing_address_1"]').val() == false) {
    jQuery(`input[name="billing_address_1"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="billing_address_1"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="billing_address_1"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="billing_address_1"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="billing_state"]', function () {
  jQuery('input[name="billing_state"]').attr('required', 'required');
  if (jQuery('input[name="billing_state"]').val() == false) {
    jQuery(`input[name="billing_state"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="billing_state"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="billing_state"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="billing_state"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="billing_city"]', function () {
  jQuery('input[name="billing_city"]').attr('required', 'required');
  if (jQuery('input[name="billing_city"]').val() == false) {
    jQuery(`input[name="billing_city"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="billing_city"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="billing_city"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="billing_city"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="billing_postcode"]', function () {
  jQuery('input[name="billing_postcode"]').attr('required', 'required');
  if (jQuery('input[name="billing_postcode"]').val() == false) {
    jQuery(`input[name="billing_postcode"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="billing_postcode"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`input[name="billing_postcode"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="billing_postcode"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'select[name="billing_country"]', function () {
  jQuery('select[name="billing_country"]').attr('required', 'required');
  if (jQuery('select[name="billing_country"]').val() == false) {
    jQuery(`select[name="billing_country"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`select[name="billing_country"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`select[name="billing_country"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`select[name="billing_country"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).ready(function () {
  if (jQuery('input[name="shipping_first_name"]').val()) {
    jQuery(`input[name="shipping_first_name"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="shipping_last_name"]').val()) {
    jQuery(`input[name="shipping_last_name"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="shipping_phone"]').val()) {
    jQuery(`input[name="shipping_phone"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="custentity_birthdate"]').val()) {
    jQuery(`input[name="custentity_birthdate"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="shipping_address_1"]').val()) {
    jQuery(`input[name="shipping_address_1"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="shipping_address_1"]').val()) {
    jQuery(`input[name="shipping_address_1"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="shipping_city"]').val()) {
    jQuery(`input[name="shipping_city"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="shipping_address_1"]').val()) {
    jQuery(`input[name="shipping_address_1"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="shipping_postcode"]').val()) {
    jQuery(`input[name="shipping_postcode"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="custentity_gender"]')) {
    jQuery(`input[name="custentity_gender"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="email"]').val()) {
    jQuery(`input[name="email"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="billing_first_name"]').val()) {
    jQuery(`input[name="billing_first_name"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="billing_last_name"]').val()) {
    jQuery(`input[name="billing_last_name"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="billing_address_1"]').val()) {
    jQuery(`input[name="billing_address_1"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="billing_address_2"]').val()) {
    jQuery(`input[name="billing_address_2"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="billing_city"]').val()) {
    jQuery(`input[name="billing_city"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
  if (jQuery('input[name="billing_postcode"]').val()) {
    jQuery(`input[name="billing_postcode"]`).closest('div.form-row').addClass('woocommerce-validated');
  }
});

function initialDatalayerCall() {
  var priceText = jQuery(".checkout-summary__price span.woocommerce-Price-amount.amount").first().text().replace(/[^\w\s]/g, "")
  var formatPrice = parseInt(priceText) / 100
  const regex = /[^a-zA-Z0-9 ]/g;
  const text = jQuery("h5.checkout-summary__title").text()
  const cleanText = text.replace(regex, "");

  dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
  dataLayer.push({
    'event':'begin_checkout',
    'ecommerce': {
      'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
      'checkout': {
        'actionField': {
          'step': 1
        },
        'products': [{
          'name': cleanText, // Please remove special characters
          'id': trek_JS_obj.checkoutParentId, // Parent ID
          'price': formatPrice, // per unit price displayed to the user - no format is ####.## (no '$' or ',')
          'brand': '', //
          'category': '', // populate with the 'country,continent' separating with a comma
          'variant': trek_JS_obj.checkoutSku, //this is the SKU of the product
          'quantity': '1' //the number of products added to the cart
        }]
      }
    }
  })
}

function checkoutPaymentAnalytics() {
  var priceText = jQuery(".checkout-summary__price span.woocommerce-Price-amount.amount").first().text().replace(/[^\w\s]/g, "")
  var formatPrice = parseInt(priceText) / 100
  const regex = /[^a-zA-Z0-9 ]/g;
  const text = jQuery("h5.checkout-summary__title").text()
  const cleanText = text.replace(regex, "");
  dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
  dataLayer.push({
    'event':'checkout_payment',
      'ecommerce': {
        'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
        'checkout': {
          'actionField': {
            'step': 1
          },
          'products': [{
            'name': cleanText, // Please remove special characters
            'id': trek_JS_obj.checkoutParentId, // Parent ID
            'price': formatPrice, // per unit price displayed to the user - no format is ####.## (no '$' or ',')
            'brand': '', //
            'category': '', // populate with the 'country,continent' separating with a comma
            'variant': trek_JS_obj.checkoutSku, //this is the SKU of the product
            'quantity': '1' //the number of products added to the cart
          }]
        }
      }
    })
}

function checkoutShippingAnalytics() {
  var priceText = jQuery(".checkout-summary__price span.woocommerce-Price-amount.amount").first().text().replace(/[^\w\s]/g, "")
  var formatPrice = parseInt(priceText) / 100
  const regex = /[^a-zA-Z0-9 ]/g;
  const text = jQuery("h5.checkout-summary__title").text()
  const cleanText = text.replace(regex, "");
  dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
  dataLayer.push({
    'event':'add_shipping_info',
      'ecommerce': {
        'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
        'checkout': {
          'actionField': {
            'step': 1
          },
          'products': [{
            'name': cleanText, // Please remove special characters
            'id': trek_JS_obj.checkoutParentId, // Parent ID
            'price': formatPrice, // per unit price displayed to the user - no format is ####.## (no '$' or ',')
            'brand': '', //
            'category': '', // populate with the 'country,continent' separating with a comma
            'variant': trek_JS_obj.checkoutSku, //this is the SKU of the product
            'quantity': '1' //the number of products added to the cart
          }]
        }
      }
    })
}

function getURLParameter(name) {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(name);
}

window.addEventListener("popstate", function () {
  const step = getURLParameter("step");

  if (step !== null) {
    if (step === '4') {
      var button = document.querySelector('.checkout-summary__button');
      var checkbox = document.querySelector('.guest-checkout__checkbox-gap');


      // Remove the 'd-none' class
      button.classList.remove('d-none');
      checkbox.classList.remove('d-none');
    }
  }
});

jQuery('body').on('click', 'button.btn.btn-number.btn-plus-private', function () {
  jQuery('.container.checkout-hotel-modal').addClass('d-none');
  // Trigger a click event on the target button
  jQuery('button.d-none.plus-button-private.btn.btn-md.rounded-1.checkout-step-two-hotel__add-occupants-btn').click();

  let occupantsModal = document.querySelector('#addOccupantsModal');

  // Listen for the custom "occupant.popup.html.ready" event on the modal. This event Wait for the modal content to load.
  occupantsModal.addEventListener('occupant.popup.html.ready', function(){
    // Find the first option with a value other than "none" in the select menu
    let $select = jQuery('select[name="occupants[private][0]"]');
    let $options = $select.find('option[value!="none"]').first();
    
    if ($options.length > 0) {
      // Select the first non-"none" option
      $select.val($options.val());
    }
    
    // Click on the "Done" button
    jQuery('#tt-occupants-btn').click();
  });

  setTimeout(function () {
    jQuery('#private').removeClass('d-none');
  } , 2000);

});

jQuery('body').on('click', 'button.btn.btn-number.btn-plus-roommate', function () {
  jQuery('.container.checkout-hotel-modal').addClass('d-none');
  // Trigger a click event on the target button
  jQuery('button.d-none.plus-button-roommate.btn.btn-md.rounded-1.checkout-step-two-hotel__add-occupants-btn').click();

  let occupantsModal = document.querySelector('#addOccupantsModal');

  // Listen for the custom "occupant.popup.html.ready" event on the modal. This event Wait for the modal content to load.
  occupantsModal.addEventListener('occupant.popup.html.ready', function(){
    // Find the first option with a value other than "none" in the select menu
    let $select = jQuery('select[name="occupants[roommate][0]"]');
    let $options = $select.find('option[value!="none"]').first();
    
    if ($options.length > 0) {
        // Select the first non-"none" option
        $select.val($options.val());
    }

    // Click on the "Done" button
    jQuery('#tt-occupants-btn').click();
  });
  setTimeout(function () {
    jQuery('#roommate').removeClass('d-none');
  } , 2000);
});

jQuery('body').on('click', 'button.btn.btn-number.btn-plus-single', function () {
  jQuery('.container.checkout-hotel-modal').addClass('d-none');
  // Trigger a click event on the target button
  jQuery('button.d-none.plus-button-single.btn.btn-md.rounded-1.checkout-step-two-hotel__add-occupants-btn').click();

  let occupantsModal = document.querySelector('#addOccupantsModal');

  // Listen for the custom "occupant.popup.html.ready" event on the modal. This event Wait for the modal content to load.
  occupantsModal.addEventListener('occupant.popup.html.ready', function(){
    // Find the first option with a value other than "none" in the select menu
    let $select1 = jQuery('select[name="occupants[single][0]"]');
    let $options1 = $select1.find('option[value!="none"]').first();

    let $select2 = jQuery('select[name="occupants[single][1]"]');
    let $options2 = $select2.find('option[value!="none"]').eq(1); // Select the second non-"none" option
        
    if ($options1.length > 0) {
        // Select the first non-"none" option
        $select1.val($options1.val());
    }

    if ($options2.length > 0) {
      // Select the second non-"none" option
      $select2.val($options2.val());
    }

    // Click on the "Done" button
    jQuery('#tt-occupants-btn').click();
  });
});

jQuery('body').on('click', 'button.btn.btn-number.btn-plus-double', function () {
  jQuery('.container.checkout-hotel-modal').addClass('d-none');
  // Trigger a click event on the target button
  jQuery('button.d-none.plus-button-double.btn.btn-md.rounded-1.checkout-step-two-hotel__add-occupants-btn').click();

  let occupantsModal = document.querySelector('#addOccupantsModal');

  // Listen for the custom "occupant.popup.html.ready" event on the modal. This event Wait for the modal content to load.
  occupantsModal.addEventListener('occupant.popup.html.ready', function(){
    // Find the first option with a value other than "none" in the select menu
    let $select1 = jQuery('select[name="occupants[double][0]"]');
    let $options1 = $select1.find('option[value!="none"]').first();

    let $select2 = jQuery('select[name="occupants[double][1]"]');
    let $options2 = $select2.find('option[value!="none"]').eq(1); // Select the second non-"none" option
        
    if ($options1.length > 0) {
        // Select the first non-"none" option
        $select1.val($options1.val());
    }

    if ($options2.length > 0) {
      // Select the second non-"none" option
      $select2.val($options2.val());
    }

    // Click on the "Done" button
    jQuery('#tt-occupants-btn').click();
  });
});

jQuery('body').on('click', '.open-roommate-popup', function() {
  jQuery('.open-to-roommate-popup-container').css('display', 'flex');
  jQuery('header').css('z-index','0');
  jQuery('body').css('overflow','hidden');
  jQuery('html').addClass('no-scroll');
})

jQuery('.open-to-roommate-popup-container .close-btn').on('click', function() {
  jQuery('.open-to-roommate-popup-container').fadeOut();
  jQuery('header').css('z-index','1020');
  jQuery('html').removeClass('no-scroll');
})

jQuery('.open-to-roommate-popup-container').on('click', function(e) {
  if(jQuery(e.target).hasClass('open-to-roommate-popup-container')) {
    jQuery('.open-to-roommate-popup-container').fadeOut();
    jQuery('header').css('z-index','1020');
    jQuery('html').removeClass('no-scroll');
  }
})

jQuery('body').on('click', '.checkout-private-popup', function() {
  jQuery('.private-popup-container').css('display', 'flex');
  jQuery('header').css('z-index','0');
  jQuery('body').css('overflow','hidden');
  jQuery('html').addClass('no-scroll');
})

jQuery('.private-popup-container .close-btn').on('click', function() {
  jQuery('.private-popup-container').fadeOut();
  jQuery('header').css('z-index','1020');
  jQuery('html').removeClass('no-scroll');
})

jQuery('.private-popup-container').on('click', function(e) {
  if(jQuery(e.target).hasClass('private-popup-container')) {
    jQuery('.private-popup-container').fadeOut();
    jQuery('header').css('z-index','1020');
    jQuery('html').removeClass('no-scroll');
  }
})

jQuery('body').on('click', '.checkout-double-occupancy', function() {
  jQuery('.private-popup-container').css('display', 'flex');
  jQuery('header').css('z-index','0');
  jQuery('body').css('overflow','hidden');
  jQuery('html').addClass('no-scroll');
})

// Check waiver status, after the modal with the waiver document close.
jQuery( '#waiver_modal' ).on( 'hidden.bs.modal', function () {
  const nsBookingId = this.dataset.nsBookingId;
  const action = 'tt_ajax_get_waiver_info_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: "action=" + action + "&ns-booking-id=" + nsBookingId,
    dataType: 'json',
    beforeSend: function () {
      // Set loader.
      jQuery.blockUI({
        css: {
          border: 'none',
          padding: '15px',
          backgroundColor: '#000',
          '-webkit-border-radius': '10px',
          '-moz-border-radius': '10px',
          opacity: .5,
          color: '#fff'
        }
      });
    },
    success: function (response) {
      // If waiver is signed successfully, show success feedback in the trip waiver status section.
      if(response.waiver_accepted) {
        jQuery('.waiver-not-signed-ctr').html(response.waiver_signed_html)
      }
      // Remove loader.
      jQuery.unblockUI();
    }
  });
})

jQuery('body').on('click', '.checkout-travel-protection-tooltip', function() {
  jQuery('.travel-protection-tooltip-container').css('display', 'flex');
  jQuery('header').css('z-index','0');
  jQuery('body').css('overflow','hidden');
  jQuery('html').addClass('no-scroll');
})

jQuery('.travel-protection-tooltip-container .close-btn').on('click', function() {
  jQuery('.travel-protection-tooltip-container').fadeOut();
  jQuery('header').css('z-index','1020');
  jQuery('html').removeClass('no-scroll');
})

jQuery('.travel-protection-tooltip-container').on('click', function(e) {
  if(jQuery(e.target).hasClass('travel-protection-tooltip-container')) {
    jQuery('.travel-protection-tooltip-container').fadeOut();
    jQuery('header').css('z-index','1020');
    jQuery('html').removeClass('no-scroll');
  }
})

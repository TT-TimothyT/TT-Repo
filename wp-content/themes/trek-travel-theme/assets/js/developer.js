const TT_CHECKOUT_LOADER_ATTRIBUTES = {
  css: {
    position: 'fixed',
    top: '50%',
    left: '50%',
    transform: 'translate(-50%, -50%)',
    maxWidth: '200px',
    background: 'transparent',
    border: 'none'
  },
  message: trek_JS_obj.tt_loader_img
};

const TT_BLOCK_UI_LOADER_ATTRIBUTES = {
  css: {
    border: 'none',
    padding: '15px',
    backgroundColor: '#000',
    '-webkit-border-radius': '10px',
    '-moz-border-radius': '10px',
    opacity: .5,
    color: '#fff'
  }
};

jQuery(document).ready(function($) {
  if ( trek_JS_obj && trek_JS_obj.is_checkout == true ) {
    /**
     * blockUI plugin - clear out plugin default styling on the checkout page.
     *
     * @link https://malsup.com/jquery/block/overlay.html
     */
    $.blockUI.defaults.overlayCSS = {
      zIndex: '1000',
      border: 'none',
      margin: '0px',
      padding: '0px',
      width: '100%',
      height: '100%',
      top: '0px',
      left: '0px',
      backgroundColor: 'rgb(255, 255, 255)',
      opacity: '0.7',
      cursor: 'default'
    }
  }
});

const ttLoader = {
  /**
   * Show the loader.
   *
   * @param {string} targetElement The HTML element selector for which to add the loader.
   */
  show: function( targetElement = '' ) {
    if ( trek_JS_obj && trek_JS_obj.is_checkout == true ) {
      // The checkout loader style.
      if( 'string' === typeof targetElement && 0 < targetElement.length ) {
        // Make the loader align inside, depending on the container.
        TT_CHECKOUT_LOADER_ATTRIBUTES.css.position = 'absolute';
        jQuery(targetElement).block(TT_CHECKOUT_LOADER_ATTRIBUTES);
        // Restore alignment to the default state.
        TT_CHECKOUT_LOADER_ATTRIBUTES.css.position = 'fixed';
      } else {
        jQuery.blockUI(TT_CHECKOUT_LOADER_ATTRIBUTES);
      }
    } else {
      // Across the website default blockUI loader style.
      if( 'string' === typeof targetElement && 0 < targetElement.length ) {
        jQuery(targetElement).block(TT_BLOCK_UI_LOADER_ATTRIBUTES);
      } else {
        jQuery.blockUI(TT_BLOCK_UI_LOADER_ATTRIBUTES);
      }
    }
  },
  /**
   * Hide the loader.
   *
   * @param {int|string} delay A numeric value in ms for the unblock UI delay.
   * @param {string} targetElement The HTML element selector from which to remove the loader.
   */
  hide: function( delay = 0, targetElement = '' ) {
    if( ! isNaN( parseInt( delay ) ) && 0 < parseInt( delay ) ) {
      setTimeout( jQuery.unblockUI, parseInt( delay ) );
    } else {
      if( 'string' === typeof targetElement && 0 < targetElement.length ) {
        jQuery(targetElement).unblock()
      } else {
        jQuery.unblockUI();
      }
    }
  }
}

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
 * Highlight the max online booking message.
 * 
 * @param {bool} highlight Whether should highlight the message.
 */
function highlightingMaxOnlineMessage( highlight = false ) {
  if( highlight ) {
    // Highlight the message if the capacity is 4 or more guests.
    jQuery('.max-online-booking-message').addClass('bounce');
    jQuery('.max-online-booking-message').addClass('checkout-timeline__warning');
    jQuery('.max-online-booking-message').removeClass('checkout-timeline__info');
    jQuery('.max-online-booking-message .warning-img').removeClass('d-none');
    jQuery('.max-online-booking-message .info-img').addClass('d-none');
    jQuery('.max-online-booking-message p').addClass('fw-bold');
    setTimeout(function() {
      jQuery('.max-online-booking-message').removeClass('bounce');
    }, 1000);
  } else {
    // Remove the highlight from the message if the capacity is 4 or more guests.
    jQuery('.max-online-booking-message').removeClass('checkout-timeline__warning');
    jQuery('.max-online-booking-message').addClass('checkout-timeline__info');
    jQuery('.max-online-booking-message .warning-img').addClass('d-none');
    jQuery('.max-online-booking-message .info-img').removeClass('d-none');
    jQuery('.max-online-booking-message p').removeClass('fw-bold');
  }
}

function tripCapacityValidation(is_return = true) {
  jQuery('input#wc-cybersource-credit-card-tokenize-payment-method').prop('checked', true);
  if (trek_JS_obj && trek_JS_obj.is_checkout == true) {
    var no_of_guests = jQuery('input[name="no_of_guests"]').val();
    if (trek_JS_obj.trip_booking_limit.remaining <= 0 || parseInt(no_of_guests) >= parseInt(trek_JS_obj.trip_booking_limit.remaining)) {
      jQuery('div[id="plus"]').css('pointer-events', 'none');
      jQuery('.limit-reached-feedback').css('display', 'block');
      // Highlight the message if the capacity is 4 or more guests.
      if( trek_JS_obj.trip_booking_limit.remaining >= 4 ) {
        highlightingMaxOnlineMessage(true);
      }
      if (is_return == false) {
        return false;
      }
    } else {
      jQuery('div[id="plus"]').css('pointer-events', 'auto');
      jQuery('.limit-reached-feedback').css('display', 'none');
      // Remove the highlight from the message if the capacity is 4 or more guests.
      if( trek_JS_obj.trip_booking_limit.remaining >= 4 ) {
        highlightingMaxOnlineMessage(false);
      }
    }
  }
}
function tt_validate_email(email = '') {
  var isValid = true;
  if (email) {
    let regex = /^(?!.*?[._%+\-]{2,})(?=[a-zA-Z0-9@._%+\-]{6,254}$)(^[a-zA-Z0-9])[a-zA-Z0-9._%+\-]{0,64}([^._%+\-]{1,})@(?:[a-zA-Z0-9\-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/;
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
    let regex = /^\s*(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})(?: *x(\d+))?\s*$/;
    isValid = regex.test(phone);
  }
  if ( phone.length === 0 ) {
    isValid = false;
  }
  return isValid;
}

// AGE VALIDATION
function tt_validate_age(dob = null, startDate = null) {
  let isValid = true;
  let dobTypeError = '';

  if (dob && startDate) {
    var dob = new Date(dob);
    var start = new Date(startDate);

    // Check if DOB is in the future
    if (dob > start) {
      isValid = false;
      dobTypeError = 'invalid-max-year';
    } else {
      var age = start.getUTCFullYear() - dob.getUTCFullYear();
      var monthDiff = start.getUTCMonth() - dob.getUTCMonth();
      var dayDiff = start.getUTCDate() - dob.getUTCDate();

      // Adjust age if the birth date hasn't occurred yet this year
      if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
        age--;
      }

      if (age < 18) {
        isValid = false;
        dobTypeError = 'invalid-age';
      } else if (dob.getUTCFullYear() < 1900) {
        isValid = false;
        dobTypeError = 'invalid-min-year';
      }
    }
  }

  return { isValid, dobTypeError };
}


// jQuery code to validate date of birth fields
jQuery(document).ready(function($) {
  if ($('body').hasClass('trek-checkout')) {
    // Ensure start dates are available globally
    let startDates = trekData.startDates;

    // Function to validate DOB fields
    function validateDobField(dobField, startDates) {
        let dob = $(dobField).val();
        let errors = $(dobField).closest('div.form-row').find('.dob-error');
        errors.css("display", "none");

        startDates.forEach(startDate => {
            let validation = tt_validate_age(dob, startDate);
            if (!validation.isValid) {
                switch (validation.dobTypeError) {
                    case 'invalid-min-year':
                        $(dobField).closest('div.form-row').find(".invalid-feedback.invalid-min-year").css("display", "block");  
                        break;
                    case 'invalid-max-year':
                        $(dobField).closest('div.form-row').find(".invalid-feedback.invalid-max-year").css("display", "block");
                        break;
                    case 'invalid-age':
                    default:
                        $(dobField).closest('div.form-row').find(".invalid-feedback.invalid-age").css("display", "block");
                        break;
                }
                $(dobField).closest('div.form-row').addClass('woocommerce-invalid');
                $(dobField).closest('div.form-row').removeClass('woocommerce-validated');
            } else {
                $(dobField).closest('div.form-row').removeClass('woocommerce-invalid');
                $(dobField).closest('div.form-row').addClass('woocommerce-validated');
            }
        });
    }

    // Validate primary guest DOB field
    $('input[name="custentity_birthdate"]').on('change blur', function () {
        validateDobField(this, startDates);
    });

    // Validate additional guests' DOB fields
    $(document).on('change blur', 'input[name*="[guest_dob]"]', function () {
        $(this).attr('required', 'required'); // Add the required attribute to the current input
        validateDobField(this, startDates);
    });
  }
});




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
      } else {
        jQuery(this).closest('div.form-row').addClass('woocommerce-invalid')
        jQuery(this).closest('div.form-row').removeClass('woocommerce-validated')
        jQuery(this).closest('div.form-row').append('<p class="duplicateEmailError mb-0 mt-2 text-danger">This email address is already being used.</p>')
        emailStatus.push(false);
      }
    }
  });

  if (emailStatus.includes(false)) {
    emailValidate = true;
  }

  return emailValidate;
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
      let startDate = trekData.startDates;
      if (inputType == 'radio') {
        CurrentVal = jQuery(`input[name="${CurrentName}"]:checked`).val();
      }
      if (isRequired == 'required') {
        let dobValidation = tt_validate_age( CurrentVal, startDate );
        if (CurrentVal == '' || CurrentVal == undefined || CurrentVal == 'undefined' || ( CurrentName === 'custentity_birthdate' && dobValidation.isValid == false ) || ( typeof CurrentName === 'string' && CurrentName.includes( 'guest_dob' ) && dobValidation.isValid == false ) ) {
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-row').addClass('woocommerce-invalid');
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-row').removeClass('woocommerce-validated');
          jQuery(`input[name="${CurrentName}"]`).closest('div.form-floating').find(".rider-select").css("display", "block");
          jQuery(this).closest('div.form-row').find(".invalid-feedback.dob-error").css("display", "none");
          switch (dobValidation.dobTypeError) {
            case 'invalid-min-year':
              jQuery(this).closest('div.form-row').find(".invalid-feedback.invalid-min-year").css("display", "block");  
              break;
            case 'invalid-max-year':
              jQuery(this).closest('div.form-row').find(".invalid-feedback.invalid-max-year").css("display", "block");
              break;
            case 'invalid-age':
            default:
              jQuery(this).closest('div.form-row').find(".invalid-feedback.invalid-age").css("display", "block");
              break;
          }
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
    let hasInvalidEmail = false;
    jQuery(`div[data-step="${step}"] input[name*="[guest_email]"]`).each( function () {
      jQuery(this).attr('required', 'required'); // Add the required attribute to the current input
      var duplicatedEmail = tt_validate_duplicate_email();
      if (tt_validate_email(jQuery(this).val()) == false || jQuery(this).val() == '' || duplicatedEmail == true) {
        jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
        jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
        jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
        jQuery(this).closest('div.form-row').find('.form-floating').removeClass('woocommerce-validated');
        if( ! hasInvalidEmail ) {
          isValidated = true;
          hasInvalidEmail = true;
        }
      } else {
        jQuery(this).closest('div.form-row').removeClass('woocommerce-invalid');
        jQuery(this).closest('div.form-row').addClass('woocommerce-validated');
        jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
      }
    });
    jQuery(`div[data-step="${step}"] input[name*="[guest_phone]"]`).each( function () {
      jQuery(this).attr('required', 'required'); // Add the required attribute to the current input
      if (tt_validate_phone(jQuery(this).val()) == false) {
        jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
        jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
        jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
        jQuery(this).closest('div.form-row').find('.form-floating').removeClass('woocommerce-validated');
      } else {
        jQuery(this).closest('div.form-row').removeClass('woocommerce-invalid');
        jQuery(this).closest('div.form-row').addClass('woocommerce-validated');
        jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
      }
    });
    tt_validate_duplicate_email();
    // Trigger billing adress population if we have is_same_billing_as_mailing checked on step 3.
    let isMailingChecked  = jQuery('input[name="is_same_billing_as_mailing"]').is(':checked');
    if( isMailingChecked == true ){
      adjustBillingAdress();
      jQuery('.billing_checkbox').trigger('change');
    }
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
          jQuery(`select[name="${CurrentName}"]`).closest('div.form-floating').find(".rider-select").css("display", "none");
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
            jQuery(`select[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-invalid');
            jQuery(`select[name="${CurrentName}"]`).closest('div').addClass('woocommerce-validated');
        }else{
          if (CurrentVal == '' || CurrentVal == undefined) {
            jQuery(`input[name="${CurrentName}"]`).closest('div').addClass('woocommerce-invalid');
            jQuery(`input[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-validated');
            jQuery(`select[name="${CurrentName}"]`).closest('div').addClass('woocommerce-invalid');
            jQuery(`select[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-validated');
            isValidated = true;
            if (CurrentName == "tt_waiver") {
              jQuery('.tt_waiver_required').css('display', "block")
            }
            validationMessages.push(`Step 3: Field [name: ${CurrentName}, Value: ${CurrentVal}]`);
          } else {
            jQuery(`input[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-invalid');
            jQuery(`input[name="${CurrentName}"]`).closest('div').addClass('woocommerce-validated');
            jQuery(`select[name="${CurrentName}"]`).closest('div').removeClass('woocommerce-invalid');
            jQuery(`select[name="${CurrentName}"]`).closest('div').addClass('woocommerce-validated');
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
    // jQuery('.navigation-sticky').css('padding-left', headerMargin + 10 + 'px');
    
  } else if(jQuery(window).width() > 1440) {
    jQuery('#similar-trips').css('padding-left', headerMargin + 'px');
    // jQuery('.navigation-sticky').css('padding-left', headerMargin + 'px');
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
  let addingGuestsInProgress = false;
  jQuery('#minus').addClass('qtydisable');
  function addfields(gcount, glen) {
    if( ! addingGuestsInProgress ) {
      addingGuestsInProgress = true;
      let action = 'get_add_guest_template_action';

      jQuery.ajax({
        type: 'POST',
        url: trek_JS_obj.ajaxURL,
        data: "action=" + action + "&guest_count=" + gcount + "&guest_length=" + glen,
        dataType: 'json',
        beforeSend : function(){
          // Set loader.
          ttLoader.show();
        },
        success: function ( response ) {
          if( true == response.status && response.checkout_guest_single_html ) {
            jQuery( '#qytguest' ).append( response.checkout_guest_single_html );
          }
          // Remove loader.
          ttLoader.hide();
          addingGuestsInProgress = false;
        },
      });
    }
  }
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
      jQuery('.guest-infoo , .guest-subinfo , #qytguest').removeClass('d-none');
      addfields(counter, glen);
    }
    tripCapacityValidation(false);
  });
  jQuery('#plus').on('click', function () {
    counter = jQuery('.guestnumber').val();
    jQuery('.guestnumber').val(++counter);
    if (counter > 1) {
      jQuery('#minus').removeClass('qtydisable');
    }
    var glen = jQuery('#qytguest .guests').length;
    jQuery('.guest-infoo , .guest-subinfo , #qytguest').removeClass('d-none');
    addfields(counter, glen);
    tripCapacityValidation(false);
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

  jQuery('.pdp_testimonial-slider').slick({
    dots: false,
    infinite: true,
    speed: 300,
    slidesToShow: 3.15,
    slidesToScroll: 1,
    autoplay: false,
    responsive: [
      {
        breakpoint: 1600,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          dots: true,
          arrows: false,
        }
      },
      {
        breakpoint: 1300,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2,
          dots: true,
          arrows: false,
        }
      },
      {
        breakpoint: 550,
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

jQuery(window).on('resize', function() {
  var headerMargin = parseInt(jQuery('#header .container').css("marginLeft").replace('px', ''))
  if(jQuery(window).width() > 768 && jQuery(window).width() <= 1440) {
    jQuery('#similar-trips').css('padding-left', headerMargin + 10 + 'px');
    // jQuery('.navigation-sticky').css('padding-left', headerMargin + 10 + 'px');
    
  } else if(jQuery(window).width() > 1440) {
    jQuery('#similar-trips').css('padding-left', headerMargin + 'px');
    // jQuery('.navigation-sticky').css('padding-left', headerMargin + 'px');
  }
} );

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
      ttLoader.show();
    },
    success: function (response) {
      jQuery('.checkout-payment__pre-address').html(response.address);
    },
    complete: function(){
      ttLoader.hide();
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

  var validationStatus;
  function StepValidation() {
      tripCapacityValidation(false);
      var currentStep = jQuery('input[name="step"]').val();
      var isHikingCheckout = jQuery('input[name="is_hiking_checkout"]').val();
      var targetStep = parseInt(currentStep) + parseInt(1);
      var targetStepId = jQuery('li.nav-item[data-step="' + targetStep + '"]').attr('data-step-id');
      var duplicatedGuestEmail = tt_validate_duplicate_email();
      var validationStatus = checkout_steps_validations(currentStep);
      if (duplicatedGuestEmail == true) {
        validationStatus = true;
      }
      if (validationStatus == true) {
        var firstInvalidField = jQuery('.woocommerce-invalid').eq(0);
        if(firstInvalidField) {
          jQuery('html, body').animate({
            scrollTop: firstInvalidField.offset().top - 120
          }, 500);
        } else {
          jQuery('html, body').animate({
            scrollTop: jQuery(window).offset().top - 120
          }, 500);
        }
      
        return false;
      }
      ttLoader.show();
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
          if ((targetStep == 2 || targetStep == 4 || ( isHikingCheckout && targetStep == 3 ) ) && response.stepHTML) {
            if( 4 == targetStep || ( isHikingCheckout && targetStep == 3 ) ) {
              jQuery(`#tt-checkout-reviews-inner-html`).html(response.stepHTML);
            } else {
              jQuery(`div.tab-pane[data-step="${targetStep}"]`).html(response.stepHTML);
              if( response.checkout_bikes && ! isHikingCheckout ) {
                jQuery('#tt-bikes-selection-inner-html').html(response.checkout_bikes);
              }
            }
            if( 2 == targetStep ) {
              // Set disabled attribute on the plus and minus buttons.
              validateGuestSelectionAdds();
            }
          }
          if (response.insuredHTMLPopup) {
            jQuery(`#tt-popup-insured-form`).html(response.insuredHTMLPopup);
          }
          if (response.guest_insurance_html) {
            jQuery(`#travel-protection-div`).html(response.guest_insurance_html);
          }
          if (response.review_order) {
            jQuery('#tt-review-order').html(response.review_order);
          }
          if( jQuery('.checkout-payment__options').length > 0 && response.payment_option ) {
            jQuery('.checkout-payment__options').html(response.payment_option);
          }
          ttLoader.hide(2000);
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
            jQuery('body').css('overflow','');
            jQuery('html').removeClass('no-scroll');
          })
          
          jQuery('.open-to-roommate-popup-container').on('click', function(e) {
            if(jQuery(e.target).hasClass('open-to-roommate-popup-container')) {
              jQuery('.open-to-roommate-popup-container').fadeOut();
              jQuery('header').css('z-index','1020');
              jQuery('body').css('overflow','');
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
            jQuery('body').css('overflow','');
            jQuery('html').removeClass('no-scroll');
          })
          
          jQuery('.private-popup-container').on('click', function(e) {
            if(jQuery(e.target).hasClass('private-popup-container')) {
              jQuery('.private-popup-container').fadeOut();
              jQuery('header').css('z-index','1020');
              jQuery('body').css('overflow','');
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
            jQuery('body').css('overflow','');
            jQuery('html').removeClass('no-scroll');
          })
          
          jQuery('.travel-protection-tooltip-container').on('click', function(e) {
            if(jQuery(e.target).hasClass('travel-protection-tooltip-container')) {
              jQuery('.travel-protection-tooltip-container').fadeOut();
              jQuery('header').css('z-index','1020');
              jQuery('body').css('overflow','');
              jQuery('html').removeClass('no-scroll');
            }
          })
        }
      });
  }

  jQuery('body').on('click', 'form.checkout.woocommerce-checkout .btn-next', function () {
    StepValidation();
  });

  jQuery('body').on('click', '.checkout-timeline .nav-link', function (e) {
    var $clickedLink = jQuery(this);
    var $clickedItem = jQuery(this).parent();
    var $activeLink = jQuery('.checkout-timeline .nav-item.active');
   
    //Check if the clicked link is not the active link and not before the active link
    if (!$clickedItem.hasClass('active') && $clickedItem.index() >= $activeLink.index()) {
      e.preventDefault();
      var navLink = $clickedLink.attr('href');
      StepValidation();
      if (validationStatus == true) {
        window.location.href = navLink;
      }
    }
  });

});
jQuery(document).ready(function () {

  var maxHeight = 0;
    jQuery('.rp4wp-related-posts li').each(function(){
        maxHeight = Math.max(maxHeight, jQuery(this).children('.rp4wp-related-post-content').children('a').height());
    });

    if(jQuery(window).width() > 498) {
      jQuery('.rp4wp-related-posts li .rp4wp-related-post-content a').height(maxHeight);
    }

    if(jQuery(window).width() < 499) {
      jQuery('.rp4wp-related-posts ul').slick({
        slidesToShow: 1,
      });
    }

    jQuery('.share-post').after(jQuery('.rp4wp-related-posts') );

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
      var is_rememberme = jQuery('form.woocommerce-form-login input[name="rememberme"]').is(':checked');
      jQuery.ajax({
        type: 'POST',
        url: trek_JS_obj.ajaxURL,
        data: formData + "&action=" + action + "&is_rememberme=" + is_rememberme,
        dataType: 'json',
        beforeSend: function () {
          console.log(2);
          jQuery('#trek-login-responses').html('');
          jQuery('form.woocommerce-form-login').removeClass('was-validated')
          ttLoader.show();
        },
        success: function (response) {
          console.log(3);
          var resMessage = '';
          if (response.status == true) {
            jQuery('form.woocommerce-form-login')[0].reset();
            resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`;
            ttLoader.hide(1000);
            window.location.href = response.redirect;
          } else {
            resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`;
            jQuery('#trek-login-responses').html(resMessage);
          }
          ttLoader.hide(500);
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
          ttLoader.show();
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
          ttLoader.hide(500);
          return false;
        }
      });
    
  });
  jQuery('body').on('submit', 'form[name^="tt-checklist-form"]', function (ev) {

    this.classList.add('was-validated');

    if (! this.checkValidity()) {
      ev.preventDefault()
      ev.stopPropagation()
      return false;
    }

    this.classList.remove('was-validated');
    // Take the confirmed section from the data attribute on the submit button that submits the form.
    let confirmedSection = jQuery( ev.originalEvent.submitter ).attr('data-confirm');
    var formData = jQuery(this).serialize();
    if( 'bike_section' === confirmedSection ) {
      let bikeIdObj = jQuery(this).serializeArray().find( data => data.name == "bikeId" );
      if( bikeIdObj ) {
        if( bikeIdObj.hasOwnProperty('value') ) {
          if( bikeIdObj.value.trim().length <= 0 ) {
            jQuery('select[name="tt-bike-size"]').val('');
            this.classList.add('was-validated');
            ev.preventDefault()
            ev.stopPropagation()
            return false;
          }
        }
      }
    }
    var action = 'update_trip_checklist_action';
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: formData + "&confirmed_section=" + confirmedSection + "&action=" + action,
      dataType: 'json',
      beforeSend: function () {
        jQuery('#my-trips-responses').html('');
        ttLoader.show();
      },
      success: function (response) {
        var resMessage = '';
        if (response.status == true) {
          resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`;

          switch (confirmedSection) {
            case 'medical_section':
              jQuery('.medical_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
              // Restore textarea values for submitted 'no' values.
              jQuery('.medical_validation_checkboxes').each(function(){
                if( 'no' == jQuery(this).val() && jQuery(this).is(':checked') ){
                  let textArea = jQuery(this).closest('.medical_item').find('textarea');
                  textArea.val('');
                }
              })
              // Prevent script-stop execution issues.
              try {
                // Store new state of Medical Info section.
                medicalInfoSectionHelper.confirmChanges();
              } catch (error) {
                console.log(error);
              }
              break;
            case 'emergency_section':
              jQuery('.emergency_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
              // Prevent script-stop execution issues.
              try {
                // Store new state of Emergency Info section.
                emergencyInfoSectionHelper.confirmChanges();
              } catch (error) {
                console.log(error);
              }
              break;
            case 'gear_section':
              jQuery('.gear_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
              // Prevent script-stop execution issues.
              try {
                // Store new state of Gear Info section.
                gearInfoSectionHelper.confirmChanges();
              } catch (error) {
                console.log(error);
              }
              break;
            case 'passport_section':
              jQuery('.passport_checklist-btn img').attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
              // Prevent script-stop execution issues.
              try {
                // Store new state of Passport Info section.
                passportInfoSectionHelper.confirmChanges();
              } catch (error) {
                console.log(error);
              }
              break;
            case 'bike_section':
              jQuery( '.bike_checklist-btn img' ).attr('src', trek_JS_obj.temp_dir + '/assets/images/success.png');
              // Prevent script-stop execution issues.
              try {
                // Store new state of Bike Info section.
                bikeInfoSectionHelper.confirmChanges();
              } catch (error) {
                console.log(error);
              }
              break;
            case 'gear_optional_section':
              // Prevent script-stop execution issues.
              try {
                // Store new state of Gear Info Optional section.
                gearInfoOptionalSectionHelper.confirmChanges();
              } catch (error) {
                console.log(error);
              }
              break;
            default:
              break;
          }
        } else {
          resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`;
        }

        jQuery('#my-trips-responses').html(resMessage);
        if (response.status == false) {
          alert(response.message);
        }
        ttLoader.hide(500);
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
        ttLoader.show();
      },
      success: function (response) {        
        jQuery('.medical-info-toast .toast-body').html(response.message);
        const toastMessage = document.querySelector('.medical-info-toast');
        toastMessage.classList.toggle('show');
        ttLoader.hide(500);
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
        ttLoader.show();
      },
      success: function (response) {
        var resMessage = '';
        if (response.status == true) {
          resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
        } else {
          resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
        }
        jQuery('#contact-information-responses').html(resMessage);
        ttLoader.hide(500);
        return false;
      }
    });
    return false;
  });
  jQuery('body').on('change', '.medical_item input[type="radio"]', function () {
    var is_checked = jQuery(this).val();
    var textArea = jQuery(this).closest('.medical_item').find('textarea');
    if( 'yes' == is_checked ) {
      textArea.show();
      textArea.attr('required', 'required');
    } else {
      textArea.hide();
      textArea.removeAttr('required');
    }
    return false;
  });
  jQuery('body').on('click', '#checkout-summary-mobile', function () {
    jQuery('.checkout-summary').toggleClass('d-none');
    if (jQuery('.checkout-summary').hasClass('d-none')) {
      jQuery(this).removeClass('is-opened');
    //   jQuery(this).addClass('checkout-summary__mobile');
      jQuery('.checkout-summary').removeClass('checkout-summary__toggle');
    } else {
      jQuery(this).addClass('is-opened');
    //   jQuery(this).removeClass('checkout-summary__mobile');
      jQuery('.checkout-summary').addClass('checkout-summary__toggle');
    }
  });

  jQuery('.book-trip-cta a').on('click', function () {
    dataLayer.push({
      'event': 'book_this_trip'
    });
  });

  jQuery('body').on('click', '.checkout-bikes__bike-selection', function () {
    dataLayer.push({
      'event': 'gear_selected'
    });
    //Check if there's no input with the name bike-clicked
    if (jQuery('input[name="bike-clicked"]').length === 0) {
      jQuery('<input>').attr({
          type: 'hidden',
          name: 'bike-clicked',
          value: 'true'
      }).appendTo('.checkout-bikes__bike-selection');
    }
  });

  jQuery('body').on('click', '.checkout-bikes__footer-step-btn .btn-next', function (e) {
      //Get the value of bike-clicked, if exists and defined
      var bikeClicked = jQuery('input[name="bike-clicked"]').val();
      if( bikeClicked === 'true' ) {
        dataLayer.push({
          'event': 'gear_selected'
        });
      }
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
    if (destination !== undefined) {
      jQuery('.trek-trip-finder-form').attr('action', `${destination}`);
    }
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
      ttLoader.show();
    },
    success: function (response) {
      var resMessage = '';
      if (response.status == true) {
        resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
      } else {
        resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
      }
      jQuery('#bike-gear-preferences-responses').html(resMessage);
      ttLoader.hide(500);
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
      ttLoader.show();
    },
    success: function (response) {
      var resMessage = '';
      if (response.status == true) {
        resMessage = `<div class="alert alert-success" role="alert">${response.message}</div>`
      } else {
        resMessage = `<div class="alert alert-danger" role="alert">${response.message}</div>`
      }
      jQuery('#communication-preferences-responses').html(resMessage);
      ttLoader.hide(500);
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

/**
 * This is a helper for Travel protection modal,
 * that store initial information about the state
 * and provide "undo changes" functionality.
 */
let travelProtectionModalHelper = {
  infoCtr: document.querySelector('#protection_modal'),
  initialState: {},
  stored: false,
  storeInfo: function() {
    // Section not found.
    if( null === this.infoCtr ){
      return;
    }

    // Do not overwrite the saved Initial information.
    if( this.stored ) {
      return
    }

    // Catch fields.
    let isTravelProtectionInputs = this.infoCtr.querySelectorAll('input[name*=is_travel_protection]');

    isTravelProtectionInputs.forEach(radio => {
      if( radio.checked ) {
        // Keep checked radio buttons.
        this.initialState[radio.name] = radio.value;
      }
    });
    
    // Initial info stored successfully.
    this.stored = true;
  },
  undoChanges: function() {
    // Restore the radio buttons state.
    for ( const key in this.initialState ) {
      this.infoCtr.querySelector(`[name="${key}"][value="${this.initialState[key]}"]`).click();
    }
  },
  confirmChanges: function() {
    this.stored = false;
    // Store new confirmed state.
    this.storeInfo();
  }
}

/**
 * Get quote travel protection action.
 *
 * @returns void
 */
function get_quote_travel_protection() {
  var formData = jQuery('form.checkout.woocommerce-checkout').serialize();
  var action = 'get_quote_travel_protection_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: formData + "&action=" + action,
    dataType: 'json',
    beforeSend: function () {
      ttLoader.show();
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
      ttLoader.hide(500);
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
      // Prevent script-stop execution issues.
      try {
        // Store new state of Travel protection modal choices.
        travelProtectionModalHelper.confirmChanges();
      } catch (error) {
        console.log(error);
      }
    },
    complete: function(){
      jQuery("#currency_switcher").trigger("change");
    }
  });
  return false;
}
// Submit the travel protection, regardless of using the submit or close button, and also when the modal closes with outside clicking.
jQuery('body').on('click', '.submit_protection, #protection_modal .btn-close:not(.cancel-submit-protection)', get_quote_travel_protection);
let isTPCancelBtnClicked = false;
jQuery('body').on('hidden.bs.modal', '#protection_modal', function(ev) {
  if( ! isTPCancelBtnClicked ) {
    // If not clicked the cancel button apply the changes.
    get_quote_travel_protection();
  }
  isTPCancelBtnClicked = false;
});
// Store the initial opening travel protection pop-up sate.
jQuery('body').on('shown.bs.modal', '#protection_modal', function (ev) {
  travelProtectionModalHelper.storeInfo();
});
// Restore the travel protection popup state.
jQuery('body').on('click', '#protection_modal .btn-close.cancel-submit-protection', function(ev) {
  // Cancel button is clicked.
  isTPCancelBtnClicked = true;
  travelProtectionModalHelper.undoChanges();
});

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
        ttLoader.show();
      },
      success: function (response) {
        var step = jQuery(".tab-pane.active.show").attr('data-step');
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
        jQuery("#currency_switcher").trigger("change");
        var isHikingCheckout = jQuery('input[name="is_hiking_checkout"]').val();
        if (parseInt(step) != 4 || ( isHikingCheckout && parseInt( step ) != 3 )) { 
          jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').addClass("d-none")
        }
        // Show tearms and conditiones checkbox and pay now button on step 4.
        if( parseInt( step ) === 4 || ( isHikingCheckout && parseInt( step ) === 3 ) ) {
          jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').removeClass("d-none");
        }
        if (response.status == false && dataString.type == 'add' ) {
          ttLoader.hide();
          
         if ( coupon_code !== '') {
            // jQuery(".promo-input").val(coupon_code)
            jQuery(".invalid-code").css("display", "block")
          }
        } else {
          // Nested second AJAX call
          var secondAction = 'tt_recalculate_travel_protection';
          jQuery.ajax({
            type: 'POST',
            url: trek_JS_obj.ajaxURL,
            data: "&action=" + secondAction,
            dataType: 'json',
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
                ttLoader.hide( 0, '#protection_modal .modal-content' );
                jQuery("#currency_switcher").trigger("change")
              }, 500);
              if (response.guest_insurance_html) {
                jQuery('#travel-protection-div').html(response.guest_insurance_html);
                jQuery('#travel-protection-summary').html(response.guest_insurance_html);
              }
              if (response.insuredHTMLPopup) {
                jQuery(`#tt-popup-insured-form`).html(response.insuredHTMLPopup);
              }
              if (jQuery('.checkout-payment__options').length > 0 && response.payment_option) {
                jQuery('.checkout-payment__options').html(response.payment_option);
              }
              if( parseInt( step ) === 4 ) {
                jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').removeClass("d-none");
              }
              ttLoader.hide();
            }
          });
        }
        jQuery("#currency_switcher").trigger("change");
      }
    });
  });
}

if (jQuery('#load-more').length > 0) {
  let currentPage = 1;
  jQuery('body').on('click', '#load-more', function () {
    var actionName = 'tt_load_more_blog_action';

    currentPage++;

    const urlParams = new URLSearchParams(window.location.search);
    var categoryId = urlParams.get('category');

    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: {
        action: actionName,
        paged: currentPage,
        catid: categoryId
      },
      dataType: 'json',
      beforeSend: function () {
        ttLoader.show();
      },
      success: function (response) {
        if (currentPage >= response.max) {
          jQuery('#load-more').hide();
        }
        jQuery('.blog-list-appendTo').append(response.html);
        ttLoader.hide(500);
      }
    });
    return false;
  });
}

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
        ttLoader.show();
      },
      success: function (response) {
        jQuery('#tt-occupants-btn-close').trigger('click');
        if (response.review_order) {
          jQuery('#tt-review-order').html(response.review_order);
        }
        if (response.step == 2 && response.stepHTML) {
          jQuery(`div.tab-pane[data-step="${response.step}"]`).html(response.stepHTML);
          var isHikingCheckout = jQuery('input[name="is_hiking_checkout"]').val();
          if( response.checkout_bikes && ! isHikingCheckout ) {
            jQuery('#tt-bikes-selection-inner-html').html(response.checkout_bikes);
          }
        }
        validateGuestSelectionAdds();
        ttLoader.hide();
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
    // jQuery('#progress-bar li.active').prevAll().css({ "background-color": "#28AAE1", "border": "2px solid #28AAE1" })
    jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').addClass("d-none")
    jQuery('body').removeClass('elementor-kit-14');
    jQuery("#currency_switcher").trigger("change")
    jQuery(".guest-checkout__primary-form input#email").prop("disabled", true)
    jQuery("#guest #shipping_state").attr("required", "required")
    switch (parseInt(currentStep)) {
      case 2:
        if(jQuery('body').hasClass('checkout-style-hiking')) {
          jQuery('#progress-bar .checkout-timeline__progress-bar-line').css("width", "50%")
        } else {
          jQuery('#progress-bar .checkout-timeline__progress-bar-line').css("width", "33%")
        }
        jQuery('.checkout-summary').addClass('d-none');
        // jQuery('#checkout-summary-mobile').removeClass('checkout-summary__mobile-open');
        jQuery('#checkout-summary-mobile').addClass('checkout-summary__mobile');
        jQuery('.checkout-summary').removeClass('checkout-summary__toggle');
        assignEditOccupantsCtaShowHide()
        var totalOccupatnsAssigned = jQuery('select[name^="occupants["]').length;
        var totalNoOfGuests = jQuery('input[name="no_of_guests"]').val();
        var remainingGuests = totalNoOfGuests - totalOccupatnsAssigned;
        jQuery(".checkout-step-two-hotel__guests-left-counter span.badge").html(remainingGuests)
        break;

      case 3:
        if(jQuery('body').hasClass('checkout-style-hiking')) {
          jQuery('#progress-bar .checkout-timeline__progress-bar-line').css("width", "99%");
          jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').removeClass("d-none")
          // jQuery('#checkout-summary-mobile').trigger("click")
          jQuery('.checkout-summary').removeClass('d-none');
          jQuery('#checkout-summary-mobile').removeClass('checkout-summary__mobile-open');
          jQuery('#checkout-summary-mobile').addClass('checkout-summary__mobile');
          jQuery('.checkout-summary').addClass('checkout-summary__toggle');
        } else {
          jQuery('#progress-bar .checkout-timeline__progress-bar-line').css("width", "66%")
          jQuery('.checkout-summary').addClass('d-none');
          jQuery('#checkout-summary-mobile').removeClass('checkout-summary__mobile-open');
          jQuery('#checkout-summary-mobile').addClass('checkout-summary__mobile');
          jQuery('.checkout-summary').removeClass('checkout-summary__toggle');
        }
        break;

      case 4:
        jQuery('#progress-bar .checkout-timeline__progress-bar-line').css("width", "99%")
        jQuery('.guest-checkout__checkbox-gap, .checkout-summary__button').removeClass("d-none")
        // jQuery('#checkout-summary-mobile').trigger("click")
        jQuery('.checkout-summary').removeClass('d-none');
        jQuery('#checkout-summary-mobile').removeClass('checkout-summary__mobile-open');
        jQuery('#checkout-summary-mobile').addClass('checkout-summary__mobile');
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
      if(jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_size]"] option:selected').index() <= 0) {
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
  var bikeCommentsDivID = `div[data-id-bc="${jQuery(this).attr('data-type-bc')}"]`;
  var dataType = jQuery(this).attr('data-type');
  if (dataType.includes("tt_my_own_bike_guest_") == true) {
    var guest_idArr = dataType.split('tt_my_own_bike_guest_');
    var guest_id = guest_idArr[1];
  } else {
    var guest_id = 'primary';
  }
  if (jQuery(this).is(':checked')) {
    // Show a warning modal with a checkbox.
    jQuery('#checkoutOwnBikeModal').modal('toggle');

    jQuery(divID).find('input').prop('required', false);
    jQuery(divID).find('select').prop('required', false);
    jQuery(bikeCommentsDivID).find('input').prop('required', true);
    jQuery(bikeCommentsDivID).find('select').prop('required', true);
    //empty/reset all Fields without bike type id fields.
    jQuery(divID).find('input:not([name$="[bikeTypeId]"])').val('');
    jQuery(divID).find('select').prop('selectedIndex',0);
    if (guest_id != 'primary') {
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').val(5270);
      // Set bike pedals to bring own.
      jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_pedal]"]').val(1);
      // Set helmet size to bring own.
      jQuery('select[name="bike_gears[guests][' + guest_id + '][helmet_size]"]').val(1);
    }else{
      jQuery('input[name="bike_gears[primary][bikeId]"]').val(5270)
      // Set bike pedals to bring own
      jQuery('select[name="bike_gears[primary][bike_pedal]"]').val(1);
      // Set helmet size to bring own.
      jQuery('select[name="bike_gears[primary][helmet_size]"]').val(1);
    }
    jQuery(divID).hide();
    jQuery(bikeCommentsDivID).show();
    if (guest_id != 'primary') {
      jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_size]"]').prop('required', false);
      jQuery('select[name="bike_gears[guests][' + guest_id + '][rider_height]"]').prop('required', false);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeTypeId]"]').prop('required', false);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').prop('required', false);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][type_of_bike]"]').prop('required', true);
      jQuery('select[name="bike_gears[guests][' + guest_id + '][transportation_options]"]').prop('required', true);
    } else {
      jQuery('select[name="bike_gears[primary][bike_size]"]').prop('required', false);
      jQuery('select[name="bike_gears[primary][rider_height]"]').prop('required', false);
      jQuery('input[name="bike_gears[primary][bikeTypeId]"]').prop('required', false);
      jQuery('input[name="bike_gears[primary][bikeId]"]').prop('required', false);
      jQuery('input[name="bike_gears[primary][type_of_bike]"]').prop('required', true);
      jQuery('select[name="bike_gears[primary][transportation_options]"]').prop('required', true);
    }
    let thisBikeSizeFieldName = jQuery(this).closest('.checkout-bikes__bike-selection').find('[name*="[bike_size]"]').attr('name');
    // This is async function so think how to prevent unbloking UI before finished.
    reBuildBikeSizeOptions(thisBikeSizeFieldName);
  } else {
    //empty/reset all bike comments fields.
    jQuery(bikeCommentsDivID).find('input').val('');
    jQuery(bikeCommentsDivID).find('select').prop('selectedIndex',0);
    if (guest_id != 'primary') {
      if(jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_size]"] option:selected').index() <= 0) {
        jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').val('');
      }
      // Unset bike pedals value.
      jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_pedal]"]').val('');
      // Unset helemt size value.
      jQuery('select[name="bike_gears[guests][' + guest_id + '][helmet_size]"]').val('');
    }else{
      if(jQuery('select[name="bike_gears[primary][bike_size]"] option:selected').index() <= 0) {
        jQuery('input[name="bike_gears[primary][bikeId]"]').val('')
      }
      // Unset bike pedals value.
      jQuery('select[name="bike_gears[primary][bike_pedal]"]').val('');
      // Unset helemt size value.
      jQuery('select[name="bike_gears[primary][helmet_size]"]').val('');
    }
    jQuery(divID).show();
    jQuery(bikeCommentsDivID).hide();
    if (guest_id != 'primary') {
      jQuery('select[name="bike_gears[guests][' + guest_id + '][bike_size]"]').prop('required', true);
      jQuery('select[name="bike_gears[guests][' + guest_id + '][rider_height]"]').prop('required', true);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeTypeId]"]').prop('required', true);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][bikeId]"]').prop('required', true);
      jQuery('input[name="bike_gears[guests][' + guest_id + '][type_of_bike]"]').prop('required', false);
      jQuery('select[name="bike_gears[guests][' + guest_id + '][transportation_options]"]').prop('required', false);
    } else {
      jQuery('select[name="bike_gears[primary][bike_size]"]').prop('required', true);
      jQuery('select[name="bike_gears[primary][rider_height]"]').prop('required', true);
      jQuery('input[name="bike_gears[primary][bikeTypeId]"]').prop('required', true);
      jQuery('input[name="bike_gears[primary][bikeId]"]').prop('required', true);
      jQuery('input[name="bike_gears[primary][type_of_bike]"]').prop('required', false);
      jQuery('select[name="bike_gears[primary][transportation_options]"]').prop('required', false);
    }
  }
});
jQuery(document).on('click', '.tt_change_checkout_step', function () {
  var step = jQuery(this).attr('data-step');
  tt_change_checkout_step(step);
});

// Bring own bike confirmation.
jQuery(document).on('click', '[name="bring_own_bike_confirmation"]', function() {
  if( jQuery(this).is(":checked") ) {
    // Allow the Proceed button.
    jQuery(this).closest('.modal-content').find('.proceed-btn').attr('disabled', false);
  } else {
    // Disable the Proceed button.
    jQuery(this).closest('.modal-content').find('.proceed-btn').attr('disabled', true);
  }
});

jQuery(document).on( 'hidden.bs.modal', '#checkoutOwnBikeModal', function () {
  console.log(this, 'modal closinggggg');
  // when the modal has finished being hidden from the user reset the checkbox and button states.
  jQuery('[name="bring_own_bike_confirmation"]').prop('checked', false);
  jQuery('#checkoutOwnBikeModal .proceed-btn').attr('disabled', true);
})

/**
 * Get all selected until now bikes.
 *
 * @returns {array} The current selected bikes.
 */
function catchSelectedBikes() {
    // Catch all bike size fields
    let bikeSizeFields = jQuery('.tt_bike_size_change');
    let selectedBikesArr = [];
    // Populate the array with the selected bikes.
    bikeSizeFields.each( function() {
      let bikeSizeFieldName  = jQuery(this).attr('name');
      let bikeSizeFieldValue = jQuery(this).find(":selected").val();
      let bikeTypeFieldValue = jQuery(this).closest('.checkout-bikes__bike-selection').find('[name*="[bikeTypeId]"]:checked').val(); // undefined initially.
  
      if( bikeTypeFieldValue && '' !== bikeSizeFieldValue.trim() ) {
        selectedBikesArr.push( {bike_type_id: bikeTypeFieldValue, bike_size_id: bikeSizeFieldValue} )
      }
    })
    return selectedBikesArr;
}

/**
 * Rebuild all bike size options fields on the 
 * tt_bike_selection_ajax_action and tt_bike_size_change_ajax_action events.
 *
 * TODO: Loader animation breaks up, because of the async type of implementation.
 *
 * @param {string} currentBikeChangeName The name of the bike size field that triggers the rebuild process for the other bike size options.
 */
function reBuildBikeSizeOptions(currentBikeChangeName = '') {
  // Catch all bike size fields
  let bikeSizeFields = jQuery('.tt_bike_size_change');
  let selectedBikesArr = catchSelectedBikes();

  bikeSizeFields.each( function() {
    let bikeSizeFieldName  = jQuery(this).attr('name');
    let bikeSizeFieldValue = jQuery(this).find(":selected").val();
    let bikeTypeFieldValue = jQuery(this).closest('.checkout-bikes__bike-selection').find('[name*="[bikeTypeId]"]:checked').val(); // undefined initially.
    if( bikeSizeFieldName !== currentBikeChangeName && bikeTypeFieldValue ) {
      // Should Rebuild bike size options.
      let actionName = 'tt_rebuild_bike_size_options_ajax_action';
      jQuery.ajax({
        type: 'POST',
        url: trek_JS_obj.ajaxURL,
        data: { 'action': actionName, 'bike_type_id': bikeTypeFieldValue, 'selected_bike_size': bikeSizeFieldValue, 'selected_bikes_arr': selectedBikesArr },
        dataType: 'json',
        beforeSend: function () {
          ttLoader.show();
        },
        success: function (response) {
          if (response.size_opts) {
            jQuery(`select[name="${bikeSizeFieldName}"]`).html(response.size_opts);
          }
          ttLoader.hide();
        }
      });
    }
  })
}

jQuery('body').on('click', '.bike_selectionElement', function () {
  let thisBikeSizeFieldName  = jQuery(this).closest('.checkout-bikes__bike-selection').find('[name*="[bike_size]"]').attr('name');
  let thisBikeSizeFieldValue = jQuery(thisBikeSizeFieldName).find(":selected").val();
  let selectedBikesArr       = catchSelectedBikes();
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
    data: { 'action': actionName, 'bikeTypeId': bikeTypeId, guest_number: guest_number, bikeUpgradeQty: bikeUpgradeQty, 'selected_bikes_arr': selectedBikesArr, 'selected_bike_size': thisBikeSizeFieldValue },
    dataType: 'json',
    beforeSend: function () {
      ttLoader.show();
    },
    success: function (response) {
      // Primary guest bike sizes.
      if (targetElement == 'tt_bike_selection_primary' && response) {
        jQuery('input[name="bike_gears[primary][bikeId]"]').val('')
        if (response.size_opts) {
          jQuery('select[name="bike_gears[primary][bike_size]"]').html(response.size_opts);
        }
        // This is async function so think how to prevent unbloking UI before finished.
        reBuildBikeSizeOptions(`bike_gears[primary][bike_size]`);
      }
      // Secondary guests bike sizes.
      if (targetElement != 'tt_bike_selection_primary' && response) {
        guest_num = targetElement.split('tt_bike_selection_guest_');
        if (guest_num[1] && guest_num[1] != 0) {
          jQuery('input[name="bike_gears[guests][' + guest_num[1] + '][bikeId]"]').val('');
          if (response.size_opts) {
            jQuery(`select[name="bike_gears[guests][${guest_num[1]}][bike_size]"]`).html(response.size_opts);
          }
          // This is async function so think how to prevent unbloking UI before finished.
          reBuildBikeSizeOptions(`bike_gears[guests][${guest_num[1]}][bike_size]`);
        }
      }
      if (response.review_order) {
        jQuery('#tt-review-order').html(response.review_order);
      }
      ttLoader.hide();
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
      ttLoader.show();
    },
    success: function (response) {
      ttLoader.hide();
    }
  });
  var myBookId = jQuery("#bookId").val();
  jQuery("#flush-collapse-" + myBookId + " .accordion-book-now form").first().submit()
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
      var isOpenToRoommateDisabled = jQuery(`input[name="is_open_to_roommate_disabled"]`).val();
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
            if (isOpenToRoommateDisabled == 1 && CurrentName == 'roommate') {
              jQuery(`button[data-type="plus"][data-field="${CurrentName}"]`).attr('disabled', true);
            } else {
              jQuery(`button[data-type="plus"][data-field="${CurrentName}"]`).attr('disabled', false);
            }
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
          if (isOpenToRoommateDisabled == 1 && CurrentName == 'roommate') {
            jQuery(`button[data-type="plus"][data-field="${CurrentName}"]`).attr('disabled', true);
          } else {
            jQuery(`button[data-type="plus"][data-field="${CurrentName}"]`).attr('disabled', false);
          }
        }
      }
    });
  }
}

//Validate if number of characters added to textarea with name special_needs is greater than 250, if so, disable tt_continue_bike_click_btn button
jQuery('body').on('keyup', 'textarea[name="special_needs"]', function () {
  if (jQuery(this).val().length > 250) {
    //Enable the max-limit-notice notice
    jQuery('#room-request-notice').show();
    jQuery('.tt_continue_bike_click_btn').prop('disabled', true);
  } else {
    jQuery('.tt_continue_bike_click_btn').prop('disabled', false);
    jQuery('#room-request-notice').hide();
  }
});

//Do the same on step 2 load
jQuery(document).ready(function () {
  if (jQuery('body').hasClass('trek-checkout')) {
    if (jQuery('textarea[name="special_needs"]').val().length > 250) {
      jQuery('#room-request-notice').show();
      jQuery('.tt_continue_bike_click_btn').prop('disabled', true);
    } else {
      jQuery('.tt_continue_bike_click_btn').prop('disabled', false);
      jQuery('#room-request-notice').hide();
    }
  }
});

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
    var single     = jQuery('input[name="single"]').val();
    var double     = jQuery('input[name="double"]').val();
    var private    = jQuery('input[name="private"]').val();
    var roommate   = jQuery('input[name="roommate"]').val();
    var formData   = {
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
        ttLoader.show();
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
        ttLoader.hide();
        jQuery("#currency_switcher").trigger("change")
      },
      complete: function() {
        // Trigger auto assign for one guest, when chosen room is private or roommate, or for two guests, when the chosen room is single or double.
        if( ( 0 < no_of_guests && 2 == no_of_guests && ( 1 == single || 1 == double ) ) || ( 0 < no_of_guests && 1 == no_of_guests && ( 1 == private || 1 == roommate ) ) ) {
          jQuery('#tt-occupants-btn').click();
        }
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
        ttLoader.show();
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
        ttLoader.hide();
        validateGuestSelectionAdds();
        jQuery("#currency_switcher").trigger("change")
      },
      complete: function(){
        // On removing occupant from the room - trigger recalculate, and obtain the review order html.
        var actionName = 'tt_save_occupants_ajax_action';
        var formData = jQuery('form.checkout.woocommerce-checkout').serialize();
        jQuery.ajax({
          type: 'POST',
          url: trek_JS_obj.ajaxURL,
          data: formData + "&action=" + actionName,
          dataType: 'json',
          beforeSend: function () {
            ttLoader.show();
          },
          success: function (response) {
            if (response.review_order) {
              jQuery('#tt-review-order').html(response.review_order);
              jQuery("#currency_switcher").trigger("change");
            }
            if( jQuery('.checkout-payment__options').length > 0 && response.payment_option ) {
              jQuery('.checkout-payment__options').html(response.payment_option);
            }
            ttLoader.hide();
          }
        });
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
  let thisBikeSizeFieldName = jQuery(this).attr('name');
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
      ttLoader.show();
    },
    success: function (response) {
      if (response.status == true) {
        jQuery(targetInput).val(response.bike_id);
      }
      // Rebuild the other bike size options.
      // This is async function so think how to prevent unbloking UI before finished.
      reBuildBikeSizeOptions(thisBikeSizeFieldName);
      ttLoader.hide();
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
        ttLoader.show();
      },
      success: function (response) {
        if (response.status == true && response.review_order) {
          jQuery('#tt-review-order').html(response.review_order);
        }
        ttLoader.hide();
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
        ttLoader.show();
      },
      success: function (response) {
        if (response.status == true && response.html) {
          jQuery('#tt-room-bikes-selection').html(response.html);
        }
        ttLoader.hide();
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
        ttLoader.show();
      },
      success: function (response) {
        if (response.status == true && response.review_order) {
          jQuery('#tt-review-order').html(response.review_order);
        }
        if (jQuery('.checkout-payment__options').length > 0 && response.payment_option) {
          jQuery('.checkout-payment__options').html(response.payment_option);
        }
        ttLoader.hide();
        jQuery("#currency_switcher").trigger("change")
      }
    });
  });
}

jQuery('label[for="wc-cybersource-credit-card-use-new-payment-method"]').addClass('btn btn-primary');

jQuery('body').on('click', function (e) {
  var selector = jQuery('nav.mobile-nav div#navbar button.mega-toggle-animated')
  var isExpanded = jQuery(selector).attr("aria-expanded")
  if (isExpanded == "true") {
    // jQuery(".mobile-menu-toggle").addClass("position-absolute w-100 p-0")
    jQuery("nav.mobile-nav div#navbar").addClass("w-100");
    if(!jQuery(e.target).hasClass('mega-toggle-animated-inner') && !jQuery(e.target).hasClass('mega-toggle-animated-box') && !jQuery(e.target).hasClass('mega-menu-link')) {
      jQuery('html, body').css('overflow', 'hidden');
    }
    if (jQuery("header.header-main").hasClass("add-shadow")) {
      var screenHeight = jQuery(window).outerHeight() - 90
    } else {
      var screenHeight = jQuery(window).outerHeight() - 170
    }   
    jQuery("div#navbar ul#mega-menu-main-menu").height(screenHeight)
    // jQuery("div#navbar .mega-menu-toggle.mega-menu-open").css("background", "#000")
  }
  else{
    if(jQuery(window).width() < 768 && !jQuery('body').hasClass('single-product')) {
      jQuery('html, body').css('overflow', 'unset');
      // jQuery(".mobile-menu-toggle").removeClass("position-absolute w-100 p-0")
      jQuery("nav.mobile-nav div#navbar").removeClass("w-100")
      resetMobileMenu()
      // jQuery("div#navbar .mega-menu-toggle.mega-menu-open").css("background", "#fff")
    }
  }
  jQuery(".overview-menu-mobile .nav-link").on('click', function(e) {
    isScrollingByClick = true;
    // Add code to scroll to the element
    // Example: jQuery('html, body').animate({scrollTop: jQuery(jQuery(this).attr('href')).offset().top}, 1000);
    setTimeout(function() {
      isScrollingByClick = false;
    }, 1000); // Assuming the scroll animation takes 1 second
  });
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
jQuery(document).on('click', 'input[name="is_same_billing_as_mailing"]', adjustBillingAdress);
function adjustBillingAdress() {
  var isMailingChecked  = jQuery('input[name="is_same_billing_as_mailing"]').is(':checked');

  var shipping_fname     = jQuery('input[name="shipping_first_name"]').val();
  var shipping_lname     = jQuery('input[name="shipping_last_name"]').val();
  var shipping_add1      = jQuery('input[name="shipping_address_1"]').val();
  var shipping_add2      = jQuery('input[name="shipping_address_2"]').val();
  var shipping_country   = jQuery('select[name="shipping_country"]').val();
  var shipping_city      = jQuery('input[name="shipping_city"]').val();
  var shipping_state_sel = jQuery('select[name="shipping_state"]').val();
  var shipping_state_inp = jQuery('input[name="shipping_state"]').val();
  var shipping_postcode  = jQuery('input[name="shipping_postcode"]').val();
  
  var billingFirstName = jQuery('input[name="billing_first_name"]').val();
  var billingLastName  = jQuery('input[name="billing_last_name"]').val();
  var billingAddress1  = jQuery('input[name="billing_address_1"]').val();
  var billingAddress2  = jQuery('input[name="billing_address_2"]').val();
  var billingCountry   = jQuery('select[name="billing_country"]').val()
  var billingCity      = jQuery('input[name="billing_city"]').val();
  var billingStateSel  = jQuery('select[name="billing_state"]').val()
  var billingStateInp  = jQuery('input[name="billing_state"]').val()
  var billingPostcode  = jQuery('input[name="billing_postcode"]').val();
  
  if( isMailingChecked == true ) {
    jQuery('input[name="billing_first_name"]').val(shipping_fname);
    jQuery('input[name="billing_last_name"]').val(shipping_lname);
    jQuery('input[name="billing_address_1"]').val(shipping_add1);
    jQuery('input[name="billing_address_2"]').val(shipping_add2);
    jQuery('select[name="billing_country"]').val(shipping_country).trigger('change');
    jQuery('input[name="billing_city"]').val(shipping_city);
    jQuery('select[name="billing_state"]').val(shipping_state_sel);
    jQuery('input[name="billing_state"]').val(shipping_state_inp);
    jQuery('input[name="billing_postcode"]').val(shipping_postcode);
  } else {
    if ( billingCountry == '' ) {
      jQuery('input[name="billing_first_name"]').val('').trigger('change');
      jQuery('input[name="billing_last_name"]').val('').trigger('change');
      jQuery('select[name="billing_country"]').val('').trigger('change');
      jQuery('select[name="billing_state"]').val('').trigger('change');
      jQuery('input[name="billing_state"]').val('').trigger('change');
      jQuery('input[name="billing_address_1"]').val('').trigger('change');
      jQuery('input[name="billing_address_2"]').val('').trigger('change');
      jQuery('input[name="billing_city"]').val('').trigger('change');
      jQuery('input[name="billing_postcode"]').val('').trigger('change');
    } else {
      jQuery('input[name="billing_first_name"]').val(billingFirstName);
      jQuery('input[name="billing_last_name"]').val(billingLastName);
      jQuery('select[name="billing_country"]').val(billingCountry).trigger('change');
      jQuery('select[name="billing_state"]').val(billingStateSel);
      jQuery('input[name="billing_state"]').val(billingStateInp);
      jQuery('input[name="billing_address_1"]').val(billingAddress1);
      jQuery('input[name="billing_address_2"]').val(billingAddress2);
      jQuery('input[name="billing_city"]').val(billingCity);
      jQuery('input[name="billing_postcode"]').val(billingPostcode);
    }
  }
}
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
  // Set bike type id for Bike & Gear Preferences.
  var bikeTypeIdPreferences = jQuery(this).attr('data-type-id');
  jQuery('[name="bike_type_id_preferences"]').val(bikeTypeIdPreferences);
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: { 'action': actionName, 'bikeTypeId': bikeTypeId, order_id: order_id },
    dataType: 'json',
    beforeSend: function () {
      ttLoader.show();
    },
    success: function (response) {
      if (response.size_opts) {
        jQuery('select[name="tt-bike-size"]').html(response.size_opts);
      }
      jQuery('input[name="bikeTypeId"]').val(bikeTypeId);
      ttLoader.hide();
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
      ttLoader.show();
    },
    success: function (response) {
      if (response.status == true) {
        jQuery('input[name="bikeId"]').val(response.bike_id);
      }
      ttLoader.hide();
    }
  });
});

function waymark_refresh(Waymark) {	
  jQuery('.pdp-itinerary .nav-link').on('click', function() {
	  Waymark.reset_map_view();
  });
}

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

  

  window.print();
});

function openOlarkChat(obj) {
  console.log("Button clicked, openOlarkChat function called!");
    olark('api.box.expand');
    console.log("Button clicked, openOlarkChat work!!");
}



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

jQuery('body').on('change', '.tt_activity_level_select', function () {
  var selectedActivityLevel = parseInt(jQuery(this).val(), 10);
  console.log(selectedActivityLevel);
  if (selectedActivityLevel && selectedActivityLevel > 0) {
    jQuery(this).closest('div.form-floating').find(".activity-select").css("display", "none");
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').addClass('woocommerce-validated');
  } else {
    jQuery(this).closest('div.form-floating').find(".activity-select").css("display", "block");
    jQuery(this).closest('div.form-floating').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-validated');
  }
})

jQuery('body').on('change', '.tt_rider_level_select, .tt_activity_level_select', function () {
  var selectedRiderLevel = parseInt(jQuery(this).val(), 10);
  if (selectedRiderLevel && selectedRiderLevel > 0) {
    var tripRiderLevel = trek_JS_obj.rider_level; // This can be a string like "2&3" or "3&4"
    var riderLevelText = trek_JS_obj.rider_level_text;

    // Add spaces before and after the '&' in tripRiderLevel
    var formattedTripRiderLevel = tripRiderLevel.replace(/&/g, ' & ');

    // Split the tripRiderLevel string by '&' and find the minimum number
    var tripRiderLevels = tripRiderLevel.split('&').map(function(level) {
      return parseInt(level, 10);
    });
    var minTripRiderLevel = Math.min.apply(null, tripRiderLevels);

    // Compare selectedRiderLevel with tripRiderLevels
    if (tripRiderLevels.length > 1) {
      // If there are multiple levels, show modal if selectedRiderLevel is less than or equal to minTripRiderLevel
      if (selectedRiderLevel <= minTripRiderLevel) {
        jQuery(".modal-rider-level-warning #rider_level_text").text(formattedTripRiderLevel);
        jQuery('#checkoutRiderLevelModal').modal('toggle');
      }
    } else {
      // If there is only one level, show modal if selectedRiderLevel is less than tripRiderLevel
      if (selectedRiderLevel < minTripRiderLevel) {
        jQuery(".modal-rider-level-warning #rider_level_text").text(formattedTripRiderLevel);
        jQuery('#checkoutRiderLevelModal').modal('toggle');
      }
    }

    jQuery(this).closest('div.form-floating').find(".rider-select").css("display", "none");
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').addClass('woocommerce-validated');
  } else {
    jQuery(this).closest('div.form-floating').find(".rider-select").css("display", "block");
    jQuery(this).closest('div.form-floating').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-floating').removeClass('woocommerce-validated');
  }
});



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
      jQuery('.travel-protection-feedback').each(function() {
        jQuery(this).css('display', '');
      })
      ttLoader.show('#protection_modal .modal-content');
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
        ttLoader.hide( 0, '#protection_modal .modal-content' );
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
      ttLoader.show();
    },
    success: function (response) {
      if (response.status == true) {
        jQuery(jerseySizeElement).html(response.opts);
      }
      ttLoader.hide();
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

  jQuery('#testimonials p').each(function() {
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
      $this.siblings('.long-text').toggleClass("is-expanded");
      if($this.siblings('.long-text').hasClass("is-expanded")){
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
    // Highlight the message if the capacity is 4 or more guests.
    if( tripLimit >= 4 ) {
      highlightingMaxOnlineMessage(true);
    }
  } else{
    jQuery('div[id="plus"]').css('pointer-events', 'auto');
    jQuery('.limit-reached-feedback').css('display', 'none');
    // Remove the highlight from the message if the capacity is 4 or more guests.
    if(tripLimit >= 4) {
      highlightingMaxOnlineMessage(false);
    }
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
    if(jQuery(window).width() < 768) {
      jQuery(this).parent().siblings().addClass("d-none");
    }
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
    jQuery('html, body').css('overflow', 'unset');
  } else {
    jQuery('.mobile-menu-toggle').css('position','static');
    jQuery('.mobile-logo .navbar-brand').css('margin-left',0);
    jQuery('.search-icon').hide();
    jQuery('.phone-icon').hide();
    jQuery('.calendar-icon').hide();
    jQuery('html, body').css('overflow', 'unset');
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
    jQuery(this).closest('div.form-row').find('.form-floating').removeClass('woocommerce-validated');
  } else {
    jQuery(`input[name="shipping_phone"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="shipping_phone"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="custentity_birthdate"]', function () {
  jQuery('input[name="custentity_birthdate"]').attr('required', 'required');
  let startDate = trekData.startDates;
  let dobValidation = tt_validate_age( jQuery( 'input[name="custentity_birthdate"]' ).val(), startDate );
  if ( dobValidation.isValid == false || jQuery('input[name="custentity_birthdate"]').val().length == 0) {
    jQuery(`input[name="custentity_birthdate"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`input[name="custentity_birthdate"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback.dob-error").css("display", "none");
    switch (dobValidation.dobTypeError) {
      case 'invalid-min-year':
        jQuery(this).closest('div.form-row').find(".invalid-feedback.invalid-min-year").css("display", "block");  
        break;
      case 'invalid-max-year':
        jQuery(this).closest('div.form-row').find(".invalid-feedback.invalid-max-year").css("display", "block");
        break;
      case 'invalid-age':
      default:
        jQuery(this).closest('div.form-row').find(".invalid-feedback.invalid-age").css("display", "block");
        break;
    }
  } else {
    jQuery(`input[name="custentity_birthdate"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`input[name="custentity_birthdate"]`).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'input[name="account_dob"]', function () {
  jQuery('input[name="account_dob"]').attr('required', 'required');
  let startDate = trekData.startDates;
  let dobValidation = tt_validate_age( jQuery( 'input[name="account_dob"]' ).val(), startDate );
  if ( dobValidation.isValid == false || jQuery('input[name="account_dob"]').val().length == 0) {
    this.setCustomValidity('Please enter the correct date of birth.');
    jQuery(`input[name="account_dob"]`).closest('div.form-floating').addClass('woocommerce-invalid');
    jQuery(`input[name="account_dob"]`).closest('div.form-floating').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-floating').find(".invalid-feedback.dob-error").css("display", "none");
    switch (dobValidation.dobTypeError) {
      case 'invalid-min-year':
        jQuery(this).closest('div.form-floating').find(".invalid-feedback.invalid-min-year").css("display", "block");  
        break;
      case 'invalid-max-year':
        jQuery(this).closest('div.form-floating').find(".invalid-feedback.invalid-max-year").css("display", "block");
        break;
      case 'invalid-age':
      default:
        jQuery(this).closest('div.form-floating').find(".invalid-feedback.invalid-age").css("display", "block");
        break;
    }
  } else {
    this.setCustomValidity('');
    jQuery(`input[name="account_dob"]`).closest('div.form-floating').removeClass('woocommerce-invalid');
    jQuery(`input[name="account_dob"]`).closest('div.form-floating').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-floating').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'select[name="custentity_gender"]', function () {
  jQuery('select[name="custentity_gender"]').attr('required', 'required');
  if ( jQuery('select[name="custentity_gender"]').val().length == 0 ) {
    jQuery(`select[name="custentity_gender"]`).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(`select[name="custentity_gender"]`).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
  } else {
    jQuery(`select[name="custentity_gender"]`).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(`select[name="custentity_gender"]`).closest('div.form-row').addClass('woocommerce-validated');
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
  var duplicatedEmail = tt_validate_duplicate_email();
  if (tt_validate_email(jQuery(this).val()) == false || jQuery(this).val() == '' || duplicatedEmail == true) {
    jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "block");
    jQuery(this).closest('div.form-row').find('.form-floating').removeClass('woocommerce-validated');
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
    jQuery(this).closest('div.form-row').find('.form-floating').removeClass('woocommerce-validated');
  } else {
    jQuery(this).closest('div.form-row').removeClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').addClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback").css("display", "none");
  }
});

jQuery(document).on('change blur', 'select[name*="[guest_gender]"]', function () {
  jQuery(this).attr('required', 'required'); // Add the required attribute to the current input
  if ( jQuery(this).val().length == 0 ) {
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
  let startDate = trekData.startDates;
  let dobValidation = tt_validate_age( jQuery( this ).val(), startDate );
  if ( dobValidation.isValid == false || jQuery(this).val() == "" ) {
    jQuery(this).closest('div.form-row').addClass('woocommerce-invalid');
    jQuery(this).closest('div.form-row').removeClass('woocommerce-validated');
    jQuery(this).closest('div.form-row').find(".invalid-feedback.dob-error").css("display", "none");
    switch (dobValidation.dobTypeError) {
      case 'invalid-min-year':
        jQuery(this).closest('div.form-row').find(".invalid-feedback.invalid-min-year").css("display", "block");  
        break;
      case 'invalid-max-year':
        jQuery(this).closest('div.form-row').find(".invalid-feedback.invalid-max-year").css("display", "block");
        break;
      case 'invalid-age':
      default:
        jQuery(this).closest('div.form-row').find(".invalid-feedback.invalid-age").css("display", "block");
        break;
    }
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

jQuery('body').on('click', '.open-roommate-popup', function() {
  jQuery('.open-to-roommate-popup-container').css('display', 'flex');
  jQuery('header').css('z-index','0');
  jQuery('body').css('overflow','hidden');
  jQuery('html').addClass('no-scroll');
})

jQuery('.open-to-roommate-popup-container .close-btn').on('click', function() {
  jQuery('.open-to-roommate-popup-container').fadeOut();
  jQuery('header').css('z-index','1020');
  jQuery('body').css('overflow','');
  jQuery('html').removeClass('no-scroll');
})

jQuery('.open-to-roommate-popup-container').on('click', function(e) {
  if(jQuery(e.target).hasClass('open-to-roommate-popup-container')) {
    jQuery('.open-to-roommate-popup-container').fadeOut();
    jQuery('header').css('z-index','1020');
    jQuery('body').css('overflow','');
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
  jQuery('body').css('overflow','');
  jQuery('html').removeClass('no-scroll');
})

jQuery('.private-popup-container').on('click', function(e) {
  if(jQuery(e.target).hasClass('private-popup-container')) {
    jQuery('.private-popup-container').fadeOut();
    jQuery('header').css('z-index','1020');
    jQuery('body').css('overflow','');
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
  const orderId = this.dataset.orderId;
  const action = 'tt_ajax_get_waiver_info_action';
  jQuery.ajax({
    type: 'POST',
    url: trek_JS_obj.ajaxURL,
    data: "action=" + action + "&ns-booking-id=" + nsBookingId + "&order-id=" + orderId,
    dataType: 'json',
    beforeSend: function () {
      // Set loader.
      ttLoader.show();
    },
    success: function (response) {
      // If waiver is signed successfully, show success feedback in the trip waiver status section.
      if(response.waiver_accepted) {
        jQuery('.waiver-not-signed-ctr').html(response.waiver_signed_html)
      }
      // Remove loader.
      ttLoader.hide();
    }
  });
})

jQuery('body').on('click', '.checkout-travel-protection-tooltip', function() {
  jQuery('.travel-protection-tooltip-container').css('display', 'flex');
  jQuery('header').css('z-index','0');
  jQuery('.header-main.sticky-top').css('top','unset');
  jQuery('body').css('overflow','hidden');
  jQuery('html').addClass('no-scroll');
})

jQuery('.travel-protection-tooltip-container .close-btn').on('click', function() {
  jQuery('.travel-protection-tooltip-container').fadeOut();
  jQuery('header').css('z-index','1020');
  jQuery('.header-main.sticky-top').css('top','var(--admin-bar-h)');
  jQuery('body').css('overflow','');
  jQuery('html').removeClass('no-scroll');
})

jQuery('.travel-protection-tooltip-container').on('click', function(e) {
  if(jQuery(e.target).hasClass('travel-protection-tooltip-container')) {
    jQuery('.travel-protection-tooltip-container').fadeOut();
    jQuery('header').css('z-index','1020');
    jQuery('.header-main.sticky-top').css('top','var(--admin-bar-h)');
    jQuery('body').css('overflow','');
    jQuery('html').removeClass('no-scroll');
  }
})

/* My trip checklist undo current cahnges functionality START */

/**
 * This is a helper for Medical Info section,
 * that store initial information about section state
 * and provide "undo changes" functionality.
 */
let medicalInfoSectionHelper = {
  infoCtr: document.querySelector('#flush-collapse-medicalInfo'),
  initialState: {
    checkboxes: {},
    textareas: {}
  },
  stored: false,
  storeInfo: function() {
    // Section not found.
    if( null === this.infoCtr ){
      return;
    }

    // Do not overwrite the saved Initial information.
    if( this.stored ) {
      return
    }

    // Catch checkboxes and textareas
    let medicalInfoCheckboxes = this.infoCtr.querySelectorAll('input.medical_validation_checkboxes');
    let medicalInfoTextareas = this.infoCtr.querySelectorAll('textarea');

    medicalInfoCheckboxes.forEach(checkbox => {
      if( checkbox.checked ){
        // Keep checked checkboxes.
        this.initialState['checkboxes'][checkbox.name] = checkbox.value;
      }
    });
  
    medicalInfoTextareas.forEach(textarea => {
      // Keep textareas values.
      this.initialState['textareas'][textarea.name] = textarea.value;
    });

    // Initial info stored successfully.
    this.stored = true;
  },
  undoChanges: function() {
    // Restore the checkboxes state.
    for ( const key in this.initialState.checkboxes ) {
      this.infoCtr.querySelector(`[name="${key}"][value="${this.initialState.checkboxes[key]}"]`).click();
    }
  
    // Restore the textareas info.
    for ( const key in this.initialState.textareas ) {
      this.infoCtr.querySelector(`[name="${key}"]`).value = this.initialState.textareas[key];
    }
  },
  confirmChanges: function() {
    this.stored = false;
    // Store new confirmed state of the section.
    this.storeInfo();
  }
}

/**
 * This is a helper for Emergency Info section,
 * that store initial information about section state
 * and provide "undo changes" functionality.
 */
let emergencyInfoSectionHelper = {
  infoCtr: document.querySelector('#flush-collapse-emergencyInfo'),
  initialState: {},
  stored: false,
  storeInfo: function() {
    // Section not found.
    if( null === this.infoCtr ){
      return;
    }

    // Do not overwrite the saved Initial information.
    if( this.stored ) {
      return
    }

    // Catch inputs.
    let emInfoInputs = this.infoCtr.querySelectorAll('input.emergency_validation_inputs');

    emInfoInputs.forEach(input => {
      // Keep input values.
      this.initialState[input.name] = input.value;
    })
    
    // Initial info stored successfully.
    this.stored = true;
  },
  undoChanges: function() {
    // Restore the inputs info.
    for ( const key in this.initialState ) {
      this.infoCtr.querySelector(`[name="${key}"]`).value = this.initialState[key];
    }
  },
  confirmChanges: function() {
    this.stored = false;
    // Store new confirmed state of the section.
    this.storeInfo();
  }
}

/**
 * This is a helper for Gear Info section,
 * that store initial information about section state
 * and provide "undo changes" functionality.
 */
let gearInfoSectionHelper = {
  infoCtr: document.querySelector('#flush-collapse-gearInfo'),
  initialState: {},
  stored: false,
  storeInfo: function() {
    // Section not found.
    if( null === this.infoCtr ){
      return;
    }

    // Do not overwrite the saved Initial information.
    if( this.stored ) {
      return
    }

    // Catch select options.
    let gearInfoSelectOptions = this.infoCtr.querySelectorAll('select.gear_validation_inputs');

    gearInfoSelectOptions.forEach(select => {
      let selectedValue = select.options[select.selectedIndex].value;

      // Keep only visible select option values.
      if( selectedValue.length > 0 && selectedValue !== 'none' ) {

        this.initialState[select.name] = selectedValue;
      }

    })
    
    // Initial info stored successfully.
    this.stored = true;
  },
  undoChanges: function() {
    // Restore the inputs info.
    for ( const key in this.initialState ) {

      // Skip jersey size, as it will be set on jersey style key.
      if( 'tt-jerrsey-size' === key ) {
        continue;
      }

      // If is jersey style, obtain jersey size options first, and set both of them.
      if( 'tt-jerrsey-style' === key ) {
        // Keep a reference to this object.
        let self = this;
        // Set jersey style value.
        this.infoCtr.querySelector(`[name="${key}"]`).value = this.initialState[key];

        // Take Size options before set the size option value.
        let actionName   = 'tt_jersey_change_action';
        let jersey_style = this.initialState[key]; // value.

        jQuery.ajax({
          type: 'POST',
          url: trek_JS_obj.ajaxURL,
          data: { 'action': actionName, 'jersey_style': jersey_style },
          dataType: 'json',
          success: function ( res ) {
            if ( res.status == true ) {
              // Append options to select/option field.
              self.infoCtr.querySelector('[name="tt-jerrsey-size"]').innerHTML = res.opts;
              // Set selected size.
              self.infoCtr.querySelector('[name="tt-jerrsey-size"]').value = self.initialState['tt-jerrsey-size'];
            }
          }
        });

      } else {

        this.infoCtr.querySelector(`[name="${key}"]`).value = this.initialState[key];
      }
    }
  },
  confirmChanges: function() {
    this.stored = false;
    // Store new confirmed state of the section.
    this.storeInfo();
  }
}

/**
 * This is a helper for Passport Info section,
 * that store initial information about section state
 * and provide "undo changes" functionality.
 */
let passportInfoSectionHelper = {
  infoCtr: document.querySelector('#flush-collapse-passportInfo'),
  initialState: {},
  stored: false,
  storeInfo: function() {
    // Section not found.
    if( null === this.infoCtr ){
      return;
    }

    // Do not overwrite the saved Initial information.
    if( this.stored ) {
      return
    }

    // Catch inputs.
    let passportInfoInputs = this.infoCtr.querySelectorAll('input.passport_validation_inputs');

    passportInfoInputs.forEach(input => {
      // Keep input values.
      this.initialState[input.name] = input.value;
    })
    
    // Initial info stored successfully.
    this.stored = true;
  },
  undoChanges: function() {
    // Restore the inputs info.
    for ( const key in this.initialState ) {
      this.infoCtr.querySelector(`[name="${key}"]`).value = this.initialState[key];
    }
  },
  confirmChanges: function() {
    this.stored = false;
    // Store new confirmed state of the section.
    this.storeInfo();
  }
}

/**
 * This is a helper for Gear Info Optional section,
 * that store initial information about section state
 * and provide "undo changes" functionality.
 */
let gearInfoOptionalSectionHelper = {
  infoCtr: document.querySelector('#flush-collapse-gearInfo-optional'),
  initialState: {},
  stored: false,
  storeInfo: function() {
    // Section not found.
    if( null === this.infoCtr ){
      return;
    }

    // Do not overwrite the saved Initial information.
    if( this.stored ) {
      return
    }

    // Catch fields.
    let gearInfoOptionalInputs = this.infoCtr.querySelectorAll('input.gear_optional_validation_inputs, select.gear_optional_validation_inputs');

    gearInfoOptionalInputs.forEach(field => {
      // Keep field values.
      this.initialState[field.name] = field.value;
    })
    
    // Initial info stored successfully.
    this.stored = true;
  },
  undoChanges: function() {
    // Restore the fields info.
    for ( const key in this.initialState ) {
      this.infoCtr.querySelector(`[name="${key}"]`).value = this.initialState[key];
    }
  },
  confirmChanges: function() {
    this.stored = false;
    // Store new confirmed state of the section.
    this.storeInfo();
  }
}

/**
 * This is a helper for Bike Info section,
 * that store initial information about section state
 * and provide "undo changes" functionality.
 */
let bikeInfoSectionHelper = {
  infoCtr: document.querySelector('#flush-collapse-bikeInfo'),
  initialState: {},
  stored: false,
  storeInfo: function() {
    // Section not found.
    if( null === this.infoCtr ){
      return;
    }

    // Do not overwrite the saved Initial information.
    if( this.stored ) {
      return
    }

    // Catch fields.
    let bikeInfoModelInputs = this.infoCtr.querySelectorAll('input.bike_validation_inputs');
    let bikeInfoSizeSelect = this.infoCtr.querySelector('select.bike_validation_select');
    let bikeInfoIdInput = this.infoCtr.querySelector('input[name="bikeId"]');
    let bikeInfoModelHiddenInput = this.infoCtr.querySelector('input[name="bikeTypeId"]');
    let bikeTypeIdPreferencesInput = this.infoCtr.querySelector('input[name="bike_type_id_preferences"]');

    bikeInfoModelInputs.forEach(input => {
      // Keep selected bike model.
      if( input.checked ) {
        this.initialState[input.name] = input.value;
      }
    })

    // Keep selected bike size.
    if( null !== bikeInfoSizeSelect ){
      if( bikeInfoSizeSelect.options.length > 0 ) {
        let selectedBikeSizeValue = bikeInfoSizeSelect.options[bikeInfoSizeSelect.selectedIndex].value;

        if( selectedBikeSizeValue.length > 0 && selectedBikeSizeValue !== 'none' ) {
          this.initialState[bikeInfoSizeSelect.name] = selectedBikeSizeValue
        }
      }
    }

    // Keep selected bike id.
    if( null !== bikeInfoIdInput ) {

      this.initialState[bikeInfoIdInput.name] = bikeInfoIdInput.value;
    }

    // Keep hidden bike model id.
    if( null !== bikeInfoModelHiddenInput ) {

      this.initialState[bikeInfoModelHiddenInput.name] = bikeInfoModelHiddenInput.value;
    }

    // Keep bike id preferences.
    if( null !==  bikeTypeIdPreferencesInput ) {

      this.initialState[bikeTypeIdPreferencesInput.name] = bikeTypeIdPreferencesInput.value;
    }

    // Initial info stored successfully.
    this.stored = true;
  },
  undoChanges: function() {
    // Restore the fields info.
    for ( const key in this.initialState ) {
      // Skip bike size, as it will be set on bike model key.
      if( 'tt-bike-size' === key ) {
        continue;
      }

      // If is bike model, obtain bike size options first, and set both of them.
      if( 'bikeModelId' === key ) {
        // Keep a reference to this object.
        let self = this;
        // Set bike model value.
        let bikeModelInput = this.infoCtr.querySelector(`[name="${key}"][value="${this.initialState[key]}"]`);
        bikeModelInput.checked = true;

        // Set selected state.
        let bikeModelsWrapper = bikeModelInput.closest('.checkout-bikes__bike-grid');
        let bikeModelCtrs = bikeModelsWrapper.querySelectorAll('.bike_selectionElementchk');
        bikeModelCtrs.forEach( ctr => {
          let icon = ctr.querySelector('.radio-selection');

          if( this.initialState[key] == ctr.dataset.id ) {
            ctr.classList.add('bike-selected');
            icon.classList.add('checkout-bikes__selected-bike-icon');
          } else {
            ctr.classList.remove('bike-selected');
            icon.classList.remove('checkout-bikes__selected-bike-icon');
          }
        });

        // Take Size options before set the size option value.
        let actionName = 'tt_chk_bike_selection_ajax_action';
        let bikeTypeId = this.initialState[key];
        let orderId = this.infoCtr.querySelector('[name="wc_order_id"]').value;

        jQuery.ajax({
          type: 'POST',
          url: trek_JS_obj.ajaxURL,
          data: { 'action': actionName, 'bikeTypeId': bikeTypeId, order_id: orderId },
          dataType: 'json',
          success: function ( res ) {
            if ( res.size_opts ) {
              // Append options to select/option field.
              self.infoCtr.querySelector('[name="tt-bike-size"]').innerHTML = res.size_opts;
              // Set selected size.
              self.infoCtr.querySelector('[name="tt-bike-size"]').value = self.initialState['tt-bike-size'];
            }
          }
        });

      } else {

        this.infoCtr.querySelector(`[name="${key}"]`).value = this.initialState[key];
      }
    }
  },
  confirmChanges: function() {
    this.stored = false;
    // Store new confirmed state of the section.
    this.storeInfo();
  }
}

// Collect initial values in the medical info section when it is expanded.
jQuery('#flush-collapse-medicalInfo').on('shown.bs.collapse', function() {
  medicalInfoSectionHelper.storeInfo();
});

// Collect initial values in the emergency info section when it is expanded.
jQuery('#flush-collapse-emergencyInfo').on('shown.bs.collapse', function() {
  emergencyInfoSectionHelper.storeInfo();
});

// Collect initial values in the gear info section when it is expanded.
jQuery('#flush-collapse-gearInfo').on('shown.bs.collapse', function() {
  gearInfoSectionHelper.storeInfo();
});

// Collect initial values in the passport info section when it is expanded.
jQuery('#flush-collapse-passportInfo').on('shown.bs.collapse', function() {
  passportInfoSectionHelper.storeInfo();
});

// Collect initial values in the gear info optional section when it is expanded.
jQuery('#flush-collapse-gearInfo-optional').on('shown.bs.collapse', function() {
  gearInfoOptionalSectionHelper.storeInfo();
});

// Collect initial values in the bike info optional section when it is expanded.
jQuery('#flush-collapse-bikeInfo').on('shown.bs.collapse', function() {
  bikeInfoSectionHelper.storeInfo();
});

// Restore initial values for any Post Booking Checklist section.
jQuery('.pb-checklist-cancel').on('click', function(ev) {
  let cancelTrgger = ev.target;

  switch ( cancelTrgger.dataset.bsTarget ) {
    case '#flush-collapse-medicalInfo':
      medicalInfoSectionHelper.undoChanges();
      break;
    case '#flush-collapse-emergencyInfo':
      emergencyInfoSectionHelper.undoChanges();
      break;
    case '#flush-collapse-gearInfo':
      gearInfoSectionHelper.undoChanges();
      break;
    case '#flush-collapse-passportInfo':
      passportInfoSectionHelper.undoChanges();
      break;
    case '#flush-collapse-gearInfo-optional':
      gearInfoOptionalSectionHelper.undoChanges();
      break;
    case '#flush-collapse-bikeInfo':
      bikeInfoSectionHelper.undoChanges();
      break;
    default:
      break;
  }

  // Catch parent section container.
  let sectionCtr = cancelTrgger.closest(cancelTrgger.dataset.bsTarget);
  // Catch save preferences checkbox in this section.
  let savePreferencesCheckbox = sectionCtr.querySelector('input[name^="tt_save_"]');
  // Restore save preferences value.
  if ( null !== savePreferencesCheckbox ) {
    savePreferencesCheckbox.checked = false;
  }

  // Catch section form.
  let sectionForm = cancelTrgger.closest('form[name^="tt-checklist-form"]');
  // Remove validation class from form.
  if( null !== sectionForm ){
    sectionForm.classList.remove('was-validated');
  }

});


var navLinks = jQuery('.overview-menu-mobile .nav-link');
var elementsArray = [];
var isScrollingByClick = false;

navLinks.each(function() {
  var id = jQuery(this).attr('href').substring(1);
  var element = jQuery('#' + id);
  elementsArray.push(element);
});

function isElementInViewport(el) {
  var rect = el[0].getBoundingClientRect();
  return (
    rect.top >= 0 &&
    rect.left >= 0 &&
    rect.bottom <= (window.innerHeight - 200 || document.documentElement.clientHeight) &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
  );
}

function handleScroll() {
  if (!isScrollingByClick) {
    var elementsInViewport = [];
    for (var i = 0; i < elementsArray.length; i++) {
      if (isElementInViewport(elementsArray[i])) {
        elementsInViewport.push(elementsArray[i].attr('id'));
      }
    }
    if (elementsInViewport.length > 0) {
      jQuery('.overview-menu-mobile .accordion-body .nav-link').each(function() {
        if (jQuery(this).attr('href') == '#' + elementsInViewport) {
          var activeSection = jQuery(this).text();
          jQuery('.overview-menu-mobile .accordion-button').text(activeSection);
        }
      });
    }
  }
}

jQuery(window).scroll(function() {
  handleScroll();
});

// Add event listener when the DOM is fully loaded
document.addEventListener("DOMContentLoaded", function() {
    // Get the input element
    var saddleHeight = document.getElementById("saddle_height");
    var barReach = document.getElementById("bar_reach");
    var barHeight = document.getElementById("bar_height");
    //Loop for each input

    if( null !== saddleHeight ) {
      // Add input event listener
      saddleHeight.addEventListener("input", function() {
          // Call the validateInput function
          validateInput(this);
      });

      saddleHeight.addEventListener("keyup", function() {
          // Call the roundInput function
          //Wait for 0.5 seconds before calling the roundInput function
          setTimeout(function() {
              roundInput(saddleHeight);
          }
          , 300);
      });
    }

    if( null !== barReach ) {
      // Add input event listener
      barReach.addEventListener("input", function() {
          // Call the validateInput function
          validateInput(this);
      });

      barReach.addEventListener("keyup", function() {
          // Call the roundInput function
          //Wait for 0.5 seconds before calling the roundInput function
          setTimeout(function() {
              roundInput(barReach);
          }
          , 300);
      });
    }

    if( null !== barHeight ) {
      // Add input event listener
      barHeight.addEventListener("input", function() {
          // Call the validateInput function
          validateInput(this);
      });
  
      barHeight.addEventListener("keyup", function() {
          // Call the roundInput function
          //Wait for 0.5 seconds before calling the roundInput function
          setTimeout(function() {
              roundInput(barHeight);
          }
          , 300);
      });
    }
});

function validateInput(input) {
    // Remove non-numeric characters except for periods (decimal points)
    input.value = input.value.replace(/[^0-9.]/g, '');

    // Ensure only one decimal point is present
    var decimalCount = (input.value.match(/\./g) || []).length;
    if (decimalCount > 1) {
        // Remove excess decimal points
        input.value = input.value.replace(/\.(?=.*\.)/g, '');
    }
}

function roundInput(input) {
  // Round to the closest first digit after the decimal point
  var dotIndex = input.value.indexOf('.');
  if (dotIndex !== -1 && dotIndex < input.value.length - 2) {
      var secondDigit = parseInt(input.value.charAt(dotIndex + 2));
      if (secondDigit >= 5) {
          // Round up using Number.EPSILON
          var floatValue = parseFloat(input.value);
          floatValue = Math.round((floatValue + Number.EPSILON) * 10) / 10;

          // Set the input value to the rounded up value
          input.value = floatValue.toFixed(1);
      } else {
          // Round down
          var floatValue = parseFloat(input.value);
          floatValue = Math.floor((floatValue + Number.EPSILON) * 10) / 10;

          // Set the input value to the rounded down value
          input.value = floatValue.toFixed(1);
      }
  }
}
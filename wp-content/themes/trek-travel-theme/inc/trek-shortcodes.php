<?php

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Custom Register form
 **/

 add_shortcode('trek-register', 'trek_registration_form_cb');
 function trek_registration_form_cb()
 {
     update_option('woocommerce_registration_generate_password', 'no');
     if (is_admin() || is_user_logged_in()) return;
 
     do_action('woocommerce_before_customer_login_form');
     $google_api_key = G_CAPTCHA_SITEKEY ?: '6LfNqogpAAAAAEoQ66tbnh01t0o_2YXgHVSde0zV';
     ?>
      <div class="row">
             <div class="col-12 login-form">

               <div class="sign-text"><div class="h5">Join Trek Travel Today!</div>
                     <p class="my-1">Sign up to personalize your experience and trips.</p>
               </div>

                 <form method="post" class="woocommerce-form woocommerce-form-register needs-validation" novalidate <?php do_action('woocommerce_register_form_tag'); ?>>
                     <?php do_action('woocommerce_register_form_start'); ?>
 
                     <div class="field-group my-auto">
 
                        <div class="form-group form-floating">
                           <input type="text" class="input-text form-control" name="billing_first_name" id="InputFname"
                              pattern="[A-Za-zÀ-ÿĀ-žḀ-ỿ'’\- ]+" placeholder="First Name" required />
                           <label class="label-for" for="InputFname">First Name*</label>
                           <div class="invalid-feedback">Only valid characters are allowed (letters, spaces, hyphens, apostrophes).</div>
                        </div>

                        <div class="form-group form-floating my-1">
                           <input type="text" class="input-text form-control" name="billing_last_name" id="InputLname"
                              pattern="[A-Za-zÀ-ÿĀ-žḀ-ỿ'’\- ]+" placeholder="Last Name" required />
                           <label class="label-for" for="InputLname">Last Name*</label>
                           <div class="invalid-feedback">Only valid characters are allowed (letters, spaces, hyphens, apostrophes).</div>
                        </div>
   
                        <div class="form-group form-floating my-1">
                           <input type="email" class="input-text form-control" name="email" id="InputEmail" placeholder="Email" required />
                           <label class="label-for" for="InputEmail">Email*</label>
                           <div class="invalid-feedback">Enter a valid email.</div>
                        </div>
   
                        <div class="form-group form-floating my-1">
                           <input type="password" class="input-text form-control" name="password" id="InputPassword" placeholder="Password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" />
                           <label class="label-for" for="InputPassword">Password*</label>
                           <div class="invalid-feedback">Must be 8+ characters with uppercase, lowercase, and a digit.</div>
                        </div>
   
                        <div class="form-group my-1">
                           <input class="form-check-input" name="is_subscribed" type="checkbox" value="yes" id="newsletterCheck">
                           <label for="newsletterCheck">Sign up for our Trek Travel Newsletter</label>
                        </div>
                        
                        <input type="text" name="tt-ba" style="display:none;" tabindex="-1" autocomplete="off" />

                        <input type="hidden" name="form_start_time" value="<?php echo time(); ?>">
                     </div>

                     <?php do_action('woocommerce_register_form'); ?>

                     <div class="f-btm form-group">
                        <div class="cf-turnstile" data-sitekey="0x4AAAAAABWi6ZkGeYPlL4wS" data-callback="javascriptCallback" data-theme="light">
                        </div>
                         <div class="invalid-feedback">Please verify you're human.</div>
                         <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                         <button type="submit" class="btn btn-bb" name="register">
                             <?php esc_html_e('Sign up', 'trek-travel-theme'); ?>
                         </button>
                     </div>
                     <?php do_action('woocommerce_register_form_end'); ?>
                 </form>
             </div>
         </div>
 
     <!-- Turnstile JS -->
     <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

     <?php
 }
 

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Custom Login form
 **/
add_shortcode('trek-login', 'trek_login_form');

function trek_login_form()
{
   if (is_admin() || is_user_logged_in()) return;
   do_action('woocommerce_before_customer_login_form');
   $http_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
   $ref_sourceUrl = parse_url($http_referer);
   $site_urlParse = parse_url(site_url());
   $redirect_url = site_url('my-account');
   if( $ref_sourceUrl && isset($ref_sourceUrl['host']) &&
      $site_urlParse && isset($site_urlParse['host']) && 
      $ref_sourceUrl['host'] == $site_urlParse['host'] ){
        $redirect_url = $http_referer;
   }

?>
 <script type="text/javascript">
   jQuery(document).ready(function($) {
      $('body').removeClass('elementor-kit-14');
   });
</script>
   <div class="row">

      <div class="col-12 login-form">

            <div class="sign-text"><div class="h5">Welcome!<br> Find your vacation of a lifetime.</div> 
               <p class="re-register-info">Log in to see upcoming trips, preferences, and much more.</strong></p>
            </div>

         
         <form id="login-form" class="woocommerce-form woocommerce-form-login needs-validation" method="post" name="trek-login-form" novalidate>
            <?php do_action('woocommerce_login_form_start'); ?>
            <div class="field-group my-auto">

               <div class="form-group form-floating">
                  <input type="email" class="input-text form-control" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" name="email" placeholder="Email" id="InputEmail" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" required />
                  <label for="InputEmail" class="label-for">Email*</label>
                  <div class="invalid-feedback">
                     <img class="invalid-icon" />
                     Please enter valid email address.
                  </div>
               </div>

               <div class="form-group">
                  <div class="form-floating password-div flex-grow-1">
                     <input type="password" class="input-text form-control" name="password" placeholder="Password" id="InputPassword" required />
                     <label for="password" class="label-for">Password*</label>
                     <span class="password-eye px-2"><i class="bi bi-eye-slash" id="togglePassword"></i></span>
                     <div class="invalid-feedback">
                        <img class="invalid-icon" />
                        Please enter your password.
                     </div>
                  </div>
               </div>
               <div class="form-group forgot-pwd justify-content-between">
                  <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme mb-3">
                     <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> 
                     <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
                  </label>
               
                  <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Forgot password?', 'trek-travel-theme'); ?></a>
               </div>
         </div>   
            <?php do_action('woocommerce_login_form'); ?>
            <div class="f-btm form-group">
               <div class="cf-turnstile" data-sitekey="0x4AAAAAABWi6ZkGeYPlL4wS" data-callback="javascriptCallback" data-theme="light"></div>
                  <div class="invalid-feedback">Please verify you're human.</div>
                  <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                  <button type="submit" class="woocommerce-button button woocommerce-form-login__submit login-submit btn btn-bb" name="login" value="<?php esc_attr_e('Sign in', 'trek-travel-theme'); ?>"><?php esc_html_e('Log in', 'trek-travel-theme'); ?></button>
                  <input type="hidden" name="http_referer" value="<?php echo $redirect_url; ?>">
            </div>
            <?php do_action('woocommerce_login_form_end'); ?>
         </form>
      </div>
   </div>
   <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php
   do_action('woocommerce_after_customer_login_form');
}

// add_action('wp_authenticate', 'trek_login_with_username_or_email', 10, 2);
// function trek_login_with_username_or_email($username, $password)
// {
//     // Check if the input is an email address
//     if (is_email($username)) {
//         // Get the user by email
//         $user = get_user_by('email', $username);
//         if ($user) {
//             $username = $user->user_login; // Replace the email with the username
//         }
//     }

//     return $username; // Proceed with authentication
// }


/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Custom Forgot password form
 **/
add_shortcode('trek-forgot-password', 'trek_forgot_password_form');
function trek_forgot_password_form()
{
   do_action('woocommerce_before_lost_password_form');
   $google_api_key = ( G_CAPTCHA_SITEKEY ? G_CAPTCHA_SITEKEY : '6LfNqogpAAAAAEoQ66tbnh01t0o_2YXgHVSde0zV' );
?>
   <div class="row">
      <div class="offset-lg-4 col-lg-4 reset-form">
         <h2 class="reset-title"><?php _e('Reset password', 'trek-travel-theme'); ?></h2>
         <form method="post" class="woocommerce-ResetPassword lost_reset_password needs-validation" novalidate>
            <p class="reset-p"><?php echo apply_filters('woocommerce_lost_password_message', esc_html__('Enter email address associated with your account and we will email you a link to reset your password.', 'trek-travel-theme')); ?></p>
            <div class="form-group form-floating">
               <input type="email" class="input-text form-control reset-email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" name="user_login" placeholder="Email" id="InputEmail" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" required />
               <label for="InputEmail" class="label-for">Email*</label>
               <div class="invalid-feedback">
                  <img class="invalid-icon" />
                  Please enter valid email address.
               </div>
            </div>
            <div class="form-group my-4">
               <div class="g-recaptcha" data-sitekey="<?php echo $google_api_key; ?>"></div>
               <div class="invalid-feedback invalid-captcha">
                  <img class="invalid-icon" />
                  Please complete captcha verification.
               </div>
            </div>
            <?php do_action('woocommerce_lostpassword_form'); ?>
            <div class="form-group">
               <input type="hidden" name="wc_reset_password" value="true" />
               <button type="submit" class="btn btn-primary reset-submit" value="<?php esc_attr_e('Send reset link', 'trek-travel-theme'); ?>"><?php esc_html_e('Send reset link', 'trek-travel-theme'); ?></button>
            </div>
            <?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>
            <div class="form-group text-center register-link">
               <span>Don't have an account? <a href="<?php echo esc_url(site_url('register')); ?>"><?php esc_html_e('Sign Up', 'trek-travel-theme'); ?></a></span>
            </div>
         </form>
      </div>
   </div>
   <script src="https://www.google.com/recaptcha/api.js?render=6LfNqogpAAAAAEoQ66tbnh01t0o_2YXgHVSde0zV"></script>
<?php
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : TripFinder Form on Homepage HeroBanner ShortCode
 **/
add_shortcode('trek-tripfinder-form', 'trek_herobaner_tripfinder_shortcode_cb',);
function trek_herobaner_tripfinder_shortcode_cb()
{
   $taxo_name = 'destination';
   $destination_obj = get_term_by('slug', 'destinations', $taxo_name); //uncategorized, destinations
   $destination_id = $destination_obj->term_id;
   $categories = get_terms(
      ['taxonomy' => $taxo_name,
       'hide_empty' => false,
       //'child_of' => $destination_id,
       'parent' => $destination_id
      ]
   );
   $child_term_ids = get_term_children( $destination_id, $taxo_name );
   $cat_opts = '<option value="">'.__('Select Destinations', 'trek-travel-theme').'</option>';
   if ($child_term_ids) {
      foreach ($child_term_ids as $child_term_id) {
         $child_term = get_term_by( 'id', $child_term_id, $taxo_name );
         $category_link = get_category_link( $child_term_id );
         $category_link = str_ireplace(site_url(),'', $category_link);
         $thumbnail_id = get_term_meta( $child_term_id, 'thumbnail_id', true );
         // get the image URL
         $image = wp_get_attachment_url( $thumbnail_id );
         $image = ( $image ? $image : 'https://via.placeholder.com/50' );
         $cat_opts .= '<option style="background-image:url('.$image.');" data-link="'.$category_link.'" value="' . $child_term->slug . '">'.$child_term->name.'</option>';
      }
   }
   $custom_logo_id = get_theme_mod( 'custom_logo' );
   $logo_image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
   $logo_url = ( $logo_image ? $logo_image[0] : ''  );
   $logo_url = ( $logo_url ? $logo_url : 'https://via.placeholder.com/50?text=Trek%20Travel' );
?>
<form action="<?php echo site_url('tours/all/') ?>" method="get" class="trek-trip-finder-form home-trip-finder-form" id="trek-trip-finder-form">

    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-center align-items-center">

            <div class="col-8 col-md-4 col-lg-3 mb-4 mb-md-0 me-0 me-md-4" id="trip-finder-daterange">
                <div class="input-group w-100 bg-secondary border border-gray-400 rounded py-1">
                    <span class="input-group-text bg-secondary border-0" id="basic-addon1"><i class="bi bi-calendar"></i></span>
                    <span class="dates-placeholder-text">Select Date</span>
                    <input type="text" aria-describedby="basic-addon1" class="input-text form-control border-0" id="home-daterange" value="" placeholder="Select Dates" />
                     <i class="toggle bi bi-chevron-up"></i>
                     <i class="toggle bi bi-chevron-down"></i>
                </div>
            </div>

            <div class="col-8 col-md-4 col-lg-3 mb-4 mb-md-0 me-0 me-md-4" id="trip-finder-destination">
                
                <!-- <div class="input-group w-100 bg-secondary border border-gray-400 rounded py-1">
                    <span class="input-group-text bg-secondary border-0"><i class="bi bi-pin-map"></i></span>
                    <select id="trip_destination" class="form-control border-0">
                        <?php echo $cat_opts; ?>
                    </select>
                </div> -->
               

                <div class="input-group w-100 bg-secondary border border-gray-400 rounded py-1">

                    <div class="tf-cat-select">
                        <input type="checkbox" name="trek_destination" value="false">
                        <i class="toggle bi bi-chevron-up"></i>
                        <i class="toggle bi bi-chevron-down"></i>
                        <span class="placeholder"><i class="bi bi-geo-alt me-4"></i> <span class="placeholder-text">Select Destination</span> <span class="selected-destination"></span></span>
                        <div class="destination-option">
                           <div class="header-popup">
                              <h4 class="popup-title position-relative text-center" id="myModalLabel">Select Destination
                              <span type="button" class="btn-close close-popup" data-bs-dismiss="modal" aria-label="Close">
                                 <i type="button" class="bi bi-x"></i>
                              </span>
                              </h4>
                           </div>
                           <?php foreach ($categories as $category) {
                              $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
                              // get the image URL
                              $filter_image = get_field('filter_image', $category);
                              // $image = wp_get_attachment_url( $thumbnail_id ); 
                              $child_term = get_term_by( 'id', $category->term_id, $taxo_name );
                              $category_link = get_category_link( $category->term_id );
                              $category_link = str_ireplace(site_url(),'', $category_link);
                              $image = ( $image ? $image : $logo_url );
                           ?>
                           <label class="option">
                               <input type="radio" name="trek_destination" id="<?php echo $category_link; ?>" value="true">
                               <div class="title align-items-center">
                                 <span class="cat-image d-inline-block me-4">
                                    <img src="<?php echo $filter_image['url']; ?>" alt="<?php echo $category->name; ?>">
                                 </span>
                                 <span class="category-name">
                                    <?php echo $category->name; ?>
                                 </span>
                              </div>
                           </label>
                           <?php } ?>
                           <div class="footer-popup">
                              <button type="button" class="btn btn-primary close-popup disabled">Apply</button>
                           </div>
                        </div>
                    </div>
                        
                </div>
            </div>

            <div class="col col-md-2 show-trips-btn">
                <input type="hidden" id="start_time" name="start_time">
                <input type="hidden" id="end_time" name="end_time">
                <button type="submit" class="btn btn-primary">Show trips</button>
            </div>

        </div><!-- /.row -->
    </div><!-- /.container -->

</form>
<?php
}

add_shortcode('trek-change-password', 'trek_change_password_shortcode_cb');
function trek_change_password_shortcode_cb(){
   $reset_password = TREK_PATH . '/woocommerce/myaccount/password-reset.php';
   if ( is_readable( $reset_password ) ) {
      wc_get_template('woocommerce/myaccount/password-reset.php');
   }else{
      echo '<div class="alert alert-danger" role="alert">The <code>password-reset.php</code> is missing. Please create file at <code>woocommerce/myaccount/password-reset.php</code></div>';
   }
}
add_shortcode('trek-medical-information', 'trek_medical_information_shortcode_cb');
function trek_medical_information_shortcode_cb(){
   $medical_information = TREK_PATH . '/woocommerce/myaccount/form-edit-medical-informations.php';
   if ( is_readable( $medical_information ) ) {
      wc_get_template('woocommerce/myaccount/form-edit-medical-informations.php');
   }else{
      echo '<div class="alert alert-danger" role="alert">The <code>form-edit-medical-informations.php</code> is missing. Please create file at <code>woocommerce/myaccount/form-edit-medical-informations.php</code></div>';
   }
}
add_shortcode('trek-bike-gear-preferences', 'trek_bike_gear_preferences_shortcode_cb');
function trek_bike_gear_preferences_shortcode_cb(){
   $bike_gear_preferences = TREK_PATH . '/woocommerce/myaccount/gear-preferences.php';
   if ( is_readable( $bike_gear_preferences ) ) {
      wc_get_template('woocommerce/myaccount/gear-preferences.php');
   }else{
      echo '<div class="alert alert-danger" role="alert">The <code>gear-preferences.php</code> is missing. Please create file at <code>woocommerce/myaccount/gear-preferences.php</code></div>';
   }
}
add_shortcode('trek-communication-preferences', 'trek_communication_preferences_shortcode_cb');
function trek_communication_preferences_shortcode_cb(){
   $communication_preferences = TREK_PATH . '/woocommerce/myaccount/communication-preferences.php';
   if ( is_readable( $communication_preferences ) ) {
      wc_get_template('woocommerce/myaccount/communication-preferences.php');
   }else{
      echo '<div class="alert alert-danger" role="alert">The <code>communication-preferences.php</code> is missing. Please create file at <code>woocommerce/myaccount/communication-preferences.php</code></div>';
   }
}
add_shortcode('trek-my-trips', 'trek_my_trips_shortcode_cb');
function trek_my_trips_shortcode_cb(){
   $my_trips = TREK_PATH . '/woocommerce/myaccount/my-trips.php';
   if ( is_readable( $my_trips ) ) {
      wc_get_template('woocommerce/myaccount/my-trips.php');
   }else{
      echo '<div class="alert alert-danger" role="alert">The <code>my-trips.php</code> is missing. Please create file at <code>woocommerce/myaccount/my-trips.php</code></div>';
   }
}
add_shortcode('trek-my-trip', 'trek_my_trip_shortcode_cb');
function trek_my_trip_shortcode_cb() {
   $ns_user_id         = get_user_meta(get_current_user_id(), 'ns_customer_internal_id', true);
   $order_id           = $_REQUEST['order_id'];
   $order              = wc_get_order( $order_id );
   $trip_status        = trek_get_guest_trip_status( get_current_user_id(), $order_id );
   $my_trip            = TREK_PATH . '/woocommerce/myaccount/my-trip-past-details.php';
   $current_user_email = wp_get_current_user()->data->user_email;
   $guest_emails       = trek_get_guest_emails( $order_id );
   $guest_emails_arr   = array();

   if( is_string( $guest_emails ) ) {
      $guest_emails_arr   = explode(', ', $guest_emails);
   }

   if( $trip_status['is_upcoming'] == 1 && ( $trip_status['days_1'] >= 0 || $trip_status['days_2'] >= 0 ) ) {
      $my_trip = TREK_PATH . '/woocommerce/myaccount/my-trip-checklist.php';
   }

   if( is_readable( $my_trip ) ) {
      // See order details, only if you are logged in.
      if( is_user_logged_in() ) {
         // See is user belongs to the order.
         if( $order && ! empty( $current_user_email ) && in_array( $current_user_email, $guest_emails_arr ) ) {
            // Get Order Status.
            $wc_order_status = $order->get_status();
            if( 'cancelled' === $wc_order_status || 'trash' === $wc_order_status ) {
               // Redirect to My trips page, if order is trashed or cancelled wia the WP admin panel.
               wp_redirect( 'my-account/my-trips' );
               exit();
            }
            // Show the checklist template or Past Details template.
            if( $trip_status['is_upcoming'] == 1 && ( $trip_status['days_1'] >= 0 || $trip_status['days_2'] >= 0 ) ) {
               wc_get_template('woocommerce/myaccount/my-trip-checklist.php');
            } else {
               wc_get_template('woocommerce/myaccount/my-trip-past-details.php');
            }
         } else {
            // Redirect to My trips page, if you are logged in, but this order not belongs to you.
            wp_redirect( 'my-account/my-trips' );
            exit();
         }
      } else {
         // Redirect to Customer login page, if you are logged out.
         wp_redirect( 'login' );
         exit();
      }
   } else {
      echo '<div class="alert alert-danger" role="alert">The <code>my-trip.php</code> is missing. Please create file at <code>woocommerce/myaccount/my-trip.php</code></div>';
   }
}


add_shortcode('trek-login-register-modal', 'trek_login_register_modal');
function trek_login_register_modal() {

    ob_start();
    ?>

<!-- Modal Content -->
<div id="login-register-modal" class="lity-hide">
    <div class="container modal-container">
    <div class="row"> 
    <div class="col"> 
         <div class="row"> 
            <div id="trek-login-responses"></div>
            <div class="col-12 col-xl-9 mx-auto">
                  <div class="sign-logo">
                     <img src="/wp-content/themes/trek-travel-theme/assets/images/TT-stacked-black-blue-crop.png" alt="">
                  </div>
            </div>
         </div>
        <ul class="nav nav-tabs mb-3" id="loginTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="login-tab" data-bs-toggle="tab" href="#login" role="tab">Log In</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="register-tab" data-bs-toggle="tab" href="#register" role="tab">Sign Up</a>
            </li>
        </ul>

        <div class="tab-content fixed-tab-content" id="loginTabContent">
            <div class="tab-pane fade show active" id="login" role="tabpanel">
                <?php trek_login_form(); ?>
            </div>
            <div class="tab-pane fade" id="register" role="tabpanel">
                <?php trek_registration_form_cb(); ?>
            </div>
        </div>
    </div>
    </div>
    </div>
</div>

    <?php
    return ob_get_clean();
}

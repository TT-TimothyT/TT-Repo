<?php 
/* Template Name: Newsletter signup */ 
?>
<?php 
//Submit newsletter hook
// $newsletter_res = array();
// $newsletter_res['status'] = false;
// $is_ns_trigger = false;
// if( isset($_POST['action']) && $_POST['action'] == 'newsletter'  ){
//   $customer_email = sanitize_email( $_POST['email'] );
//   if( isset($customer_email) && !empty($customer_email)  ){
//     $user = get_user_by( 'email', $customer_email );
//     if( $user ){
//       $customer_id = $user->ID;
//       $globalsubscriptionstatus = get_user_meta($customer_id, 'globalsubscriptionstatus', true );
//       if( $globalsubscriptionstatus == 1 ){
//         $newsletter_res['message'] = __('You have already subscribed with newsletter!', 'trek-travel-theme');
//       }else{
//         update_user_meta($customer_id, 'globalsubscriptionstatus', 1 );
//         $newsletter_res['message'] = __('You have successfully subscribed with the newsletter!', 'trek-travel-theme');
//         $is_ns_trigger = true;
//       }
//     }else{
//       $userdata = array(
//           'user_login' => $customer_email,
//           'user_pass' => NULL,
//           'user_email' => $customer_email,
//           'role' => 'customer'
//       );
//       $customer_id = wp_insert_user($userdata);
//       update_user_meta($customer_id, 'globalsubscriptionstatus', 1 );
//       $is_ns_trigger = true;
//       $newsletter_res['message'] = __('You have successfully subscribed with the newsletter!', 'trek-travel-theme');
//     }
//     if( $is_ns_trigger == true && $customer_id ){
//       sleep(2);
//       $ns_user_id = get_user_meta($customer_id, 'ns_customer_internal_id', true);
//       as_schedule_single_action(time(), 'tt_cron_syn_usermeta_ns', array( $customer_id, '[Newsletter]' ));
//     }
//     $newsletter_res['status'] = true;
//   }else{
//     $newsletter_res['message'] = __('Please enter email address!', 'trek-travel-theme');
//   }
// }

if( ! empty( get_field( 'newsletter_page', 'option' ) ) ) {
  $nl_page = get_field( 'newsletter_page', 'option' );
}
?>
<?php 
$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

if (strpos($url,'newsletter') !== false) {
return;
 } else { ?>
  <!-- newsletter component start -->		
<div class="newsletter-subscribe py-5 container">
	<div class="container">
    <div class="row">
      <div class="col-12">
        <div class="intro text-center">
          <p class="newsletter">Subscribe to our Newsletter</p>
          <a href="<?php echo $nl_page; ?>" class="btn btn-white">Subscribe</a>					
        </div>
      </div>
    </div>
	</div>
</div>
<?php } ?>

<!-- <script>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        console.log(form)
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        else{
          jQuery('#valid-feedback-div').html('<img class="success-icon" /> Success !')
          var currentPath = window.location.pathname;
          if (currentPath.indexOf("blog") != -1) {
            gtm_newsletter_signup("newsletter_signup", "signup_blog")                        
          } else {
            gtm_newsletter_signup("newsletter_signup", "signup_footer")            
          }
        }
        // form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
</script> -->
<!-- newsletter component end -->
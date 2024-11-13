<?php

use WebDevStudios\WPSWA\Algolia\AlgoliaSearch\SearchClient;
use TTNetSuite\NetSuiteClient;

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Create dynamic page function
 **/
function trek_create_page($title, $content, $parent_id = NULL)
{
    $is_page = get_page_by_title($title, 'OBJECT', 'page');
    if (empty($is_page)) {
        wp_insert_post(
            array(
                'comment_status' => 'close',
                'ping_status' => 'close',
                'post_author' => 1,
                'post_title' => $title,
                'post_name' => sanitize_title($title),
                'post_status' => 'publish',
                'post_content' => $content,
                'post_type' => 'page',
                'post_parent' => $parent_id
            )
        );
    }
    return $is_page;
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Calling Add new page function for Default pages in TT
 * @deprecated Removing these functions, as we already have the pages
 */
/*
trek_create_page('Register', '[trek-register]');
trek_create_page('Login', '[trek-login]');
trek_create_page('Reset password', '[trek-forgot-password]');
trek_create_page('Change password', '[trek-change-password]', TREK_MY_ACCOUNT_PID);
trek_create_page('Medical information', '[trek-medical-information]', TREK_MY_ACCOUNT_PID);
trek_create_page('Bike & Gear Preferences', '[trek-bike-gear-preferences]', TREK_MY_ACCOUNT_PID);
trek_create_page('Communication Preferences', '[trek-communication-preferences]', TREK_MY_ACCOUNT_PID);
$my_trips_page_ID = trek_create_page('My Trips', '[trek-my-trips]', TREK_MY_ACCOUNT_PID);
trek_create_page('My Trip', '[trek-my-trip]', $my_trips_page_ID);
*/

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Added validation for Woocommerce register form fields
 **/
add_action('woocommerce_register_post', 'trek_validate_extra_register_fields', 10, 3);
function trek_validate_extra_register_fields($username, $email, $validation_errors)
{
    if (isset($_POST['billing_first_name']) && empty($_POST['billing_first_name'])) {
        $validation_errors->add('billing_first_name_error', __('<strong>Error</strong>: First name is required!', 'trek-travel-theme'));
    }
    if (isset($_POST['billing_last_name']) && empty($_POST['billing_last_name'])) {
        $validation_errors->add('billing_last_name_error', __('<strong>Error</strong>: Last name is required!.', 'trek-travel-theme'));
    }
    if (isset($_POST['password']) && empty($_POST['password'])) {
        $validation_errors->add('password_name_error', __('<strong>Error</strong>: Password is required!.', 'trek-travel-theme'));
    }
    return $validation_errors;
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Added "save" logic for Woocommerce register form extra fields 
 **/
add_action('woocommerce_created_customer', 'trek_extra_register_fields');
function trek_extra_register_fields($customer_id)
{
    if (isset($_REQUEST['billing_first_name'])) {
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_REQUEST['billing_first_name']));
        update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_REQUEST['billing_first_name']));
    }
    if (isset($_REQUEST['billing_last_name'])) {
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_REQUEST['billing_last_name']));
        update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_REQUEST['billing_last_name']));
    }
    if (isset($_REQUEST['is_subscribed']) && $_REQUEST['is_subscribed'] == 'yes') {
        update_user_meta($customer_id, 'custentity_addtotrektravelemaillist', 1);
    }
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Rediect hook for Login & Register pages if users is already loggedin
 **/
function trek_template_redirect()
{
    if (is_checkout() && !is_user_logged_in()) {
        wp_redirect('login');
        exit();
    }
    if (is_user_logged_in() && (is_page('login') || is_page('register'))) {
        $return_url = esc_url(home_url('my-account'));
        wp_redirect($return_url);
        exit;
    }
}
add_action('template_redirect', 'trek_template_redirect');


/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Authentication logic changes
 **/
add_filter('login_errors', 'trek_login_error_message');
function trek_login_error_message($error)
{
    $pos = strpos($error, 'username,');
    if (is_int($pos)) {
        //its the right error so you can overwrite it
        $error = sprintf(__('<strong>ERROR</strong>: Invalid email address or incorrect password.'));
    }
    return $error;
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Enqueue Scripts for datepicker
 **/
add_action('wp_enqueue_scripts', 'trek_wp_enqueue_scripts_cb');
function trek_wp_enqueue_scripts_cb()
{
    global $wp;
    $order_id  = isset($wp->query_vars['order-received']) ? absint( $wp->query_vars['order-received'] ) : '';
    $trip_booking_limit = get_trip_capacity_info();
    $cart_product_info = tt_get_trip_pid_sku_from_cart();
    $checkout_loader = TREK_PATH . '/woocommerce/checkout/trek-travel-loader.php';
    if ( is_readable( $checkout_loader ) ) {
        $checkout_loader_html = wc_get_template_html('woocommerce/checkout/trek-travel-loader.php');
    } else {
        $checkout_loader_html =  '<p>Checkout Loader is missing!</p>';
    }
    wp_register_script('trek-moment', TREK_DIR . '/assets/js/moment.min.js', array(), time(), true);
    
    wp_register_script('trek-dp', TREK_DIR . '/assets/js/daterangepicker.js', array(), time(), true);
    
    wp_register_script('trek-date', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js', array(), time(), true);
    wp_register_script('trek-developer', TREK_DIR . '/assets/js/developer.js', array(), time(), true);
    wp_register_script('trek-custom-calendar', TREK_DIR . '/assets/js/trek-daterangepicker.js', array(), time(), true);
    wp_register_script('trek-trips-compare', TREK_DIR . '/assets/js/trek-compare-trips.js', array(), time(), true);
    
    wp_register_script('trek-instantsearch', TREK_DIR . '/assets/js/instantsearch.js', array(), time(), true);
    wp_register_script('trek-algoliasearch', TREK_DIR . '/assets/js/algoliasearch.umd.js', array(), time(), true);
    wp_register_script('trek-pdp-slick', TREK_DIR . '/assets/js/slick.min.js', array(), time(), true);
    wp_register_script('bootstrap-wizard', TREK_DIR . '/assets/js/jquery.bootstrap.wizard.min.js', array(), time(), true);
    wp_register_script('trek-blockUI', TREK_DIR . '/assets/js/jquery.blockUI.min.js', array(), time(), true);
    wp_register_script('trek-validation', TREK_DIR . '/assets/js/jquery.validate.min.js', array(), time(), true);
    wp_register_script('trek-validation-method', TREK_DIR . '/assets/js/additional-methods.min.js', array(), time(), true);
    wp_enqueue_script('wp-util');
    wp_enqueue_script('trek-date');
    wp_enqueue_script('trek-moment');
    wp_enqueue_script('trek-dp');
    wp_enqueue_script('trek-instantsearch');
    wp_enqueue_script('trek-algoliasearch');
    wp_enqueue_script('trek-pdp-slick');
    wp_enqueue_script('bootstrap-wizard');
    wp_enqueue_script('trek-blockUI');
    wp_enqueue_script('trek-validation');
    wp_enqueue_script('trek-validation-method');
    wp_enqueue_script('trek-developer');
    wp_enqueue_script('trek-custom-calendar');
    wp_enqueue_script('trek-trips-compare');
    wp_localize_script('trek-developer', 'trek_JS_obj', array(
        'ajaxURL' => admin_url('admin-ajax.php'),
        /**
         * Most likely we won't need this, as this function is looping all products for no obvious reason
         */
        // 'product_images' => tt_get_product_image(),
        'blank_image'               => DEFAULT_IMG,
        'temp_dir'                  => get_template_directory_uri(),
        'trip_booking_limit'        => $trip_booking_limit,
        'is_checkout'               => is_checkout(),
        'is_archive'                => is_archive(),
        'is_search'                 => is_search(),
        'rider_level'               => $cart_product_info['parent_rider_level'],
        'rider_level_text'          => $cart_product_info['rider_level_text'],
        'checkoutParentId'          => $cart_product_info['parent_product_id'],
        'checkoutSku'               => $cart_product_info['sku'],
        'checkout_product_line_obj' => json_decode( tt_get_local_trips_detail( 'product_line', '', $cart_product_info['sku'], true ) ),
        // 'review_order' => tt_get_review_order_html(), // I'm commenting this out for the moment, as I don't see a place where it can be used, and it's called on every page load, which is redundant.
        'is_order_received'         => is_wc_endpoint_url( 'order-received' ),
        'order_id'                  => $order_id,
        'tt_loader_img'             => $checkout_loader_html
    ));

    if( is_search() || is_archive() ) {
        // Load the assets only on the archive and search pages.
        wp_register_script( 'trek-quick-look', TREK_DIR . '/assets/js/trek-quick-look.js', array('jquery'), time(), true );
        wp_enqueue_script( 'trek-quick-look' );
        wp_localize_script( 'trek-quick-look', 'trek_quick_look_assets', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( '_tt_quick_look_nonce' ),
        ));
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get grouped products by ids
 **/
function get_child_products($linked_products = array(), $is_quick_look = false )
{
    $linked_product_arr = array();
    $status_not_in = ['Hold - Not on Web', 'Cancelled', 'Initial Build'];
    if ($linked_products) {
        foreach ($linked_products as $linked_product) {
            $p_obj = wc_get_product($linked_product);
            if ($p_obj && $p_obj->get_status() == 'publish' && $p_obj->get_stock_status() == 'instock' ) {
                $start_date = $p_obj->get_attribute('start-date');
                $end_date = $p_obj->get_attribute('end-date');
                $p_id = $p_obj->get_id();
                $trip_status = tt_get_custom_product_tax_value( $p_id, 'trip-status', true );
                if ($start_date && $end_date && !in_array($trip_status, $status_not_in)) {
                    $sdate_obj = explode('/', $start_date);
                    $sku = $p_obj->get_sku();
                    // Take the singleSupplementPrice from the post meta fields.
                    $singleSupplementPrice = get_post_meta( $linked_product, TT_WC_META_PREFIX . 'singleSupplementPrice', true);
                    $sdate_info = array(
                        'd' => $sdate_obj[0],
                        'm' => $sdate_obj[1],
                        'y' => substr(date('Y'), 0, 2) . $sdate_obj[2]
                    );
                    $edate_obj = explode('/', $end_date);
                    $edate_info = array(
                        'd' => $edate_obj[0],
                        'm' => $edate_obj[1],
                        'y' => substr(date('Y'), 0, 2) . $edate_obj[2]
                    );
                    $start_date_text = date('F j', strtotime(implode('-', $sdate_info)));
                    $end_date_text_1 = date('F j, Y', strtotime(implode('-', $edate_info)));
                    $end_date_text_2 = date('j, Y', strtotime(implode('-', $edate_info)));
                    $date_range_1 = $start_date_text . ' - ' . $end_date_text_2;
                    $date_range_2 = $start_date_text . ' - ' . $end_date_text_1;
                    if ( $is_quick_look ) {
                        $start_date_text_range_2 = date( 'M j', strtotime( implode( '-', $sdate_info ) ) );
                        $start_date_text_range_1 = date( 'M j', strtotime( implode( '-', $sdate_info ) ) );
                        $end_date_text_range_2   = date( 'M j, Y', strtotime( implode( '-', $edate_info ) ) );
                        $end_date_text_range_1   = date( 'j, Y', strtotime( implode( '-', $edate_info ) ) );

                        $date_range_1            = $start_date_text_range_1 . ' - ' . $end_date_text_range_1;
                        $date_range_2            = $start_date_text_range_2 . ' - ' . $end_date_text_range_2;
                    }
                    $grouped_product = array(
                        'product_id' => $linked_product,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'price' => $p_obj->get_price(),
                        'trip_status' => $trip_status,
                        'date_range' => $date_range_1,
                        'sku' => $sku,
                        'singleSupplementPrice' => $singleSupplementPrice
                    );
                    if ($sdate_info['m'] != $edate_info['m']) {
                        $grouped_product['date_range'] = $date_range_2;
                    }
                    $month_range = range($sdate_info['m'], $edate_info['m']);
                    // if (count($month_range) == 1) {
                    //     if (strlen($sdate_info['m']) == 1) {
                    //         $sdate_info['m'] = '0' . $sdate_info['m'];
                    //     }
                        $linked_product_arr[$sdate_info['y']][$sdate_info['m']][] = $grouped_product;
                    // } 
                    // else {
                    //     foreach ($month_range as $month_range_num) {
                    //         if (strlen($month_range_num) == 1) {
                    //             $month_range_num = '0' . $month_range_num;
                    //         }
                    //         $linked_product_arr[$sdate_info['y']][$month_range_num][] = $grouped_product;
                    //     }
                    // }
                }
            }
        }
    }
    krsort($linked_product_arr, 1);
    return $linked_product_arr;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get month name & shorten by number
 **/
function trek_get_month_info()
{
    $months = array(
        '01' => array(0 => 'Jan', 1 => 'January'),
        '02' => array(0 => 'Feb', 1 => 'February'),
        '03' => array(0 => 'Mar', 1 => 'March'),
        '04' => array(0 => 'Apr', 1 => 'April'),
        '05' => array(0 => 'May', 1 => 'May'),
        '06' => array(0 => 'Jun', 1 => 'June'),
        '07' => array(0 => 'Jul', 1 => 'July'),
        '08' => array(0 => 'Aug', 1 => 'August'),
        '09' => array(0 => 'Sep', 1 => 'September'),
        '10' => array(0 => 'Oct', 1 => 'October'),
        '11' => array(0 => 'Nov', 1 => 'November'),
        '12' => array(0 => 'Dec', 1 => 'December'),
    );
    return $months;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Woocommerce action hooks To add chekckout fields
 **/
add_filter('woocommerce_checkout_fields', 'custom_woocommerce_shipping_fields');
function custom_woocommerce_shipping_fields($fields)
{
    $fields['shipping']['email'] = array(
        'label' => __('Email', 'woocommerce'),
        'placeholder' => __('Email', 'woocommerce'),
        'required' => true,
        'clear' => false,
        'type' => 'email',
        'priority' => 21
    );
    $fields['shipping']['custentity_birthdate'] = array(
        'label' => __('Date of Birth', 'woocommerce'),
        'required' => true,
        'clear' => false,
        'type' => 'date',
        'priority' => 23
    );
    $fields['shipping']['custentity_gender'] = array(
        'label' => __('Gender', 'woocommerce'),
        'placeholder' => __('Gender', 'woocommerce'),
        'required' => true,
        'clear' => false,
        'type' => 'select',
        'priority' => 24,
        'options' => array('' => 'Select Gender', 1 => 'Male', 2 => 'Female')
    );

    return $fields;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Woocommerce action hooks To update Shipping fields
 **/
add_filter('woocommerce_shipping_fields', 'woocommerce_shipping_fields_add_cb');
function woocommerce_shipping_fields_add_cb($address_fields)
{
    $address_fields['shipping_phone']['priority'] = 22;
    $address_fields['shipping_address_1']['priority'] = 25;
    $address_fields['shipping_address_2']['priority'] = 26;
    $address_fields['shipping_state']['priority'] = 41;
    $address_fields['shipping_city']['priority'] = 42;
    $address_fields['shipping_city']['placeholder'] = "City";
    $address_fields['shipping_city']['label'] = "City";
    $address_fields['shipping_phone']['placeholder'] = "Phone";
    $address_fields['shipping_phone']['label'] = "Phone";
    unset($address_fields['shipping_company']);

    return $address_fields;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Woocommerce action hooks To update Billing fields
 **/
add_filter('woocommerce_billing_fields', 'woocommerce_billing_fields_add_cb');
function woocommerce_billing_fields_add_cb($address_fields)
{
    $address_fields['billing_address_1']['priority'] = 25;
    $address_fields['billing_address_2']['priority'] = 26;
    $address_fields['billing_state']['priority'] = 41;
    $address_fields['billing_city']['priority'] = 42;
    $address_fields['billing_city']['placeholder'] = "City";
    $address_fields['billing_city']['label'] = "City";
    unset($address_fields['billing_first_name']['validate']);
    unset($address_fields['billing_last_name']['validate']);
    unset($address_fields['billing_phone']['validate']);
    unset($address_fields['billing_email']['validate']);
    unset($address_fields['billing_first_name']['required']);
    unset($address_fields['billing_last_name']['required']);
    unset($address_fields['billing_phone']['required']);
    unset($address_fields['billing_email']['required']);
    return $address_fields;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : AJAX action hook for Save checkout steps value
 **/
add_action('wp_ajax_save_checkout_steps_action', 'save_checkout_steps_action_cb');
add_action('wp_ajax_nopriv_save_checkout_steps_action', 'save_checkout_steps_action_cb');
function save_checkout_steps_action_cb( $return_response = false )
{
    $total_guests_req = 0;
    $user_id = get_current_user_id();
    $step = (isset($_REQUEST['targetStep']) ? $_REQUEST['targetStep'] : 1);
    $redirect_url = trek_checkout_step_link($step);
    $accepted_p_ids = tt_get_line_items_product_ids();
    WC()->session->set('trek-checkout-data', $_REQUEST);
    $cart = WC()->cart->get_cart_contents();
    $bikes_cart_item_data = $guests_bikes_data = array();
    $is_hiking_checkout = false;
    $single_supplement_price = 0;
    foreach ($cart as $cart_item_id => $cart_item) {
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_id);
        $_product_name = $_product->get_name();
        $product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
        if (!in_array($product_id, $accepted_p_ids)) {
            //Reset Occupant data if any changes for guests in step 1
            if ($step == 2 && isset($_REQUEST['step']) && $_REQUEST['step'] == 1) {
                $total_guests_req += isset($_REQUEST['single']) ? $_REQUEST['single'] * 2 : 0;
                $total_guests_req += isset($_REQUEST['double']) ? $_REQUEST['double'] * 2 : 0;
                $total_guests_req += isset($_REQUEST['roommate']) ? $_REQUEST['roommate'] : 0;
                $total_guests_req += isset($_REQUEST['private']) ? $_REQUEST['private'] : 0;
                if (isset($_REQUEST['no_of_guests']) && $_REQUEST['no_of_guests'] != $total_guests_req) {
                    $_REQUEST['single'] = $_REQUEST['double'] = $_REQUEST['roommate'] = $_REQUEST['private'] = 0;
                    $_REQUEST['occupants'] = [];
                }
            }
            // Check if the cart contains an already applied coupon and fix the missing coupon code.
            if( WC()->cart->get_applied_coupons() && isset( $_REQUEST['coupon_code'] ) && empty( $_REQUEST['coupon_code'] ) ) {
                $applied_coupons = WC()->cart->get_applied_coupons();
                $_REQUEST['coupon_code'] = $applied_coupons[0];
            }
            $cart_item['trek_user_checkout_data'] = wp_unslash( $_REQUEST );
            //Trip Parent ID
            $parent_product_id = tt_get_parent_trip_id_by_child_sku($_product->get_sku());
            $cart_item['trek_user_checkout_data']['parent_product_id'] = $parent_product_id;
            $cart_item['trek_user_checkout_data']['product_id'] = $product_id;
            $cart_item['trek_user_checkout_data']['sku'] = $_product->get_sku();
            $bikeUpgradePrice = get_post_meta( $product_id, TT_WC_META_PREFIX . 'bikeUpgradePrice', true);
            $single_supplement_price = get_post_meta( $product_id, TT_WC_META_PREFIX . 'singleSupplementPrice', true);
            $cart_item['trek_user_checkout_data']['bikeUpgradePrice'] = $bikeUpgradePrice;
            $cart_item['trek_user_checkout_data']['singleSupplementPrice'] = $single_supplement_price;
            $cart_posted_data = $cart_item['trek_user_checkout_data'];
            $guest_req = isset($_REQUEST['guests']) ? $_REQUEST['guests'] : $cart_posted_data['guests'];
            $guest_req = $guest_req && is_array($guest_req) ? $guest_req : array();
            $bikes_cart_item_data = array(
                'cart_item' => $_product_name,
                'quantity' => count($guest_req) + 1
            );
            $guests_p_arr = array(
                "guest_fname" => $cart_posted_data['shipping_first_name'],
                "guest_lname" => $cart_posted_data['shipping_last_name'],
                "guest_email" => $cart_posted_data['email'],
                "guest_phone" => $cart_posted_data['shipping_phone'],
                "guest_gender" => $cart_posted_data['custentity_gender'],
                "guest_dob" => $cart_posted_data['custentity_birthdate'],
            );
            if ($cart_posted_data && !empty($cart_posted_data)) {
                foreach ($cart_posted_data['bike_gears'] as $trek_bike_gear_k => $trek_bike_gear) {
                    if ($trek_bike_gear_k == 'primary') {
                        $guests_bikes_data[] = array_merge($guests_p_arr, $trek_bike_gear);
                    }
                    if ($trek_bike_gear_k == 'guests' && $trek_bike_gear) {
                        foreach ($trek_bike_gear as $inner_k => $trek_bike_gear_inner_guest) {
                            $inner_guest_arr = $guest_req[$inner_k];
                            if(is_array($inner_guest_arr)){
                                $guests_bikes_data[] = array_merge($inner_guest_arr, $trek_bike_gear_inner_guest);
                            }
                        }
                    }
                }
                $bikes_cart_item_data['cart_item_data'] = $guests_bikes_data;
            }
            $cart_item['trek_user_formatted_checkout_data'][1] = $bikes_cart_item_data;
            $cart[$cart_item_id]                               = $cart_item;
            if ($step == 2 || $step == 1) {
                $cart[$cart_item_id]['quantity'] = isset( $_REQUEST['no_of_guests'] ) ? $_REQUEST['no_of_guests'] : 1;
            }
            // Set the Check for hiking checkout.
            $is_hiking_checkout = tt_is_product_line( 'Hiking', $_product->get_sku() );
            
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    if ( 2 == $step && isset( $_REQUEST['step'] ) && 1 == $_REQUEST['step'] ) {
        // Check and repair the missing Single Supplement in step 1.
        $occupants_private  = isset( $_REQUEST['occupants']['private'] ) ? $_REQUEST['occupants']['private'] : array();
        $occupants_roommate = isset( $_REQUEST['occupants']['roommate'] ) ? $_REQUEST['occupants']['roommate'] : array();
        $suppliment_counts  = count( $occupants_private ) + count( $occupants_roommate );
        if ( 0 < $suppliment_counts && ! empty( $single_supplement_price ) ) {
            $supplement_fees_product_id = tt_create_line_item_product( 'TTWP23SUPP' );
            $supplement_product_cart_id = WC()->cart->generate_cart_id( $supplement_fees_product_id, 0, array(), array( 'tt_cart_custom_fees_price' => $single_supplement_price ) ); // !!! Keep in mind that this will work when the arguments are the same passed during add_to_cart.

            if ( ! WC()->cart->find_product_in_cart( $supplement_product_cart_id ) ) {
                // Needs add to cart single supplement.
                WC()->cart->add_to_cart( $supplement_fees_product_id, $suppliment_counts, 0, array(), array( 'tt_cart_custom_fees_price' => $single_supplement_price ) );
            }
        }
    }

    $gearData = $_REQUEST['bike_gears']['primary'];
    if (isset($gearData['save_preferences']) && $gearData['save_preferences'] == 'yes' && isset($step) && $step == 4) {
        $p_bike = isset($gearData['bike_type_id_preferences']) ? $gearData['bike_type_id_preferences'] : '';
        $p_rider_height = isset($gearData['rider_height']) ? $gearData['rider_height'] : '';
        $p_bike_pedal = isset($gearData['bike_pedal']) ? $gearData['bike_pedal'] : '';
        $p_helmet_size = isset($gearData['helmet_size']) ? $gearData['helmet_size'] : '';
        $p_jersey_style = isset($gearData['jersey_style']) ? $gearData['jersey_style'] : '';
        $p_jersey_size = isset($gearData['jersey_size']) ? $gearData['jersey_size'] : '';
        if ($p_bike) {
            update_user_meta($user_id, 'gear_preferences_bike_type', $p_bike);
        }
        if ($p_rider_height) {
            update_user_meta($user_id, 'gear_preferences_rider_height', $p_rider_height);
        }
        if ($p_bike_pedal) {
            update_user_meta($user_id, 'gear_preferences_select_pedals', $p_bike_pedal);
        }
        if ($p_helmet_size) {
            update_user_meta($user_id, 'gear_preferences_helmet_size', $p_helmet_size);
        }
        if ($p_jersey_style) {
            update_user_meta($user_id, 'gear_preferences_jersey_style', $p_jersey_style);
        }
        if ($p_jersey_size) {
            update_user_meta($user_id, 'gear_preferences_jersey_size', $p_jersey_size);
        }
    }
    $stepHTML            = '';
    $checkout_bikes_html = '';
    if (isset($step) && $step == 2) {
        $checkout_hotel = TREK_PATH . '/woocommerce/checkout/checkout-hotel.php';
        if (is_readable($checkout_hotel)) {
            $stepHTML .= '<div id="tt-hotel-occupant-inner-html">';
            $stepHTML .= wc_get_template_html('woocommerce/checkout/checkout-hotel.php');
            $stepHTML .= '</div>';
        } else {
            $stepHTML .=  'Checkout Hotel form code is missing!';
        }
        $checkout_bikes = TREK_PATH . '/woocommerce/checkout/checkout-bikes.php';
        if (is_readable($checkout_bikes)) {
            $checkout_bikes_html .= wc_get_template_html('woocommerce/checkout/checkout-bikes.php');
        } else {
            $checkout_bikes_html .=  '<h3>Step 2</h3><p>Checkout Bike form code is missing!</p>';
        }
    }
    if (isset($step) && ( $step == 4  || ( $step == 3 && $is_hiking_checkout ) ) ) {
        $checkout_review = TREK_PATH . '/woocommerce/checkout/checkout-reviews.php';
        if (is_readable($checkout_review)) {
            $stepHTML .= wc_get_template_html('woocommerce/checkout/checkout-reviews.php');
        } else {
            $stepHTML .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
        }
    }
    $insuredHTMLPopup = '';
    if (1 == 1) {
        $checkout_insured_users = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php';
        if (is_readable($checkout_insured_users)) {
            $insuredHTMLPopup .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php');
        } else {
            $insuredHTMLPopup .= '<h3>Step 4</h3><p>checkout-insured-guests-popup.php form code is missing!</p>';
        }
    }
    $guest_insurance_html          = '';
    $guest_insurance_html_template = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-summary.php';
    if ( is_readable( $guest_insurance_html_template ) ) {
        $guest_insurance_html .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-summary.php');
    } else {
        $guest_insurance_html .= '<h3>Step 4</h3><p>checkout-insured-summary.php form code is missing!</p>';
    }
    $review_order_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if (is_readable($review_order)) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $payment_option_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php';
    if (is_readable($review_order)) {
        $payment_option_html .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php');
    } else {
        $payment_option_html .= '<h3>Step 4</h3><p>Checkout payment option file is missing!</p>';
    }

    $save_checkout_steps_response =  array(
        'status'               => true,
        'stepHTML'             => $stepHTML,
        'redirectURL'          => $redirect_url,
        'checkout_bikes'       => $checkout_bikes_html,
        'guest_insurance_html' => $guest_insurance_html,
        'insuredHTMLPopup'     => $insuredHTMLPopup,
        'review_order'         => $review_order_html,
        'payment_option'       => $payment_option_html,
        'message'              => 'Trek checkout steps data saved!'
    );

    if( $return_response ) {
        return $save_checkout_steps_response;
    } else {
        echo json_encode(
            array(
                'status'               => true,
                'stepHTML'             => $stepHTML,
                'redirectURL'          => $redirect_url,
                'checkout_bikes'       => $checkout_bikes_html,
                'guest_insurance_html' => $guest_insurance_html,
                'insuredHTMLPopup'     => $insuredHTMLPopup,
                'review_order'         => $review_order_html,
                'payment_option'       => $payment_option_html,
                'message'              => 'Trek checkout steps data saved!'
            )
        );
        exit;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Algolia Popular Search results data
 **/
function trek_algolia_popular_search()
{
    $popular1 = $popular2 = $popular3 = $popular4 = $results = array();
    if ( class_exists( 'Psr\Log\AbstractLogger' ) && class_exists( 'WebDevStudios\WPSWA\Algolia\AlgoliaSearch\SearchClient' ) ) {
        $upload_dir = wp_upload_dir();
        $baseDir = $upload_dir['basedir'] . '/algolia/';
        $jsonPath1 = $baseDir . 'popular1.json';
        $jsonPath2 = $baseDir . 'popular2.json';
        $jsonPath3 = $baseDir . 'popular3.json';
        $jsonPath4 = $baseDir . 'popular4.json';
        if (file_exists($jsonPath1) && file_exists($jsonPath2) && file_exists($jsonPath3) && file_exists($jsonPath4)) {
            $arr1 = file_get_contents($jsonPath1, true);
            $arr2 = file_get_contents($jsonPath2, true);
            $arr3 = file_get_contents($jsonPath3, true);
            $arr4 = file_get_contents($jsonPath4, true);
            $popular1 = json_decode($arr1);
            $popular2 = json_decode($arr2);
            $popular3 = json_decode($arr3);
            $popular4 = json_decode($arr4);
        }
        $algolia_application_id = get_option('algolia_application_id');
        $algolia_search_api_key = get_option('algolia_search_api_key');
        if (isset($algolia_search_api_key) && isset($algolia_application_id)) {
            $client = SearchClient::create($algolia_application_id, $algolia_search_api_key);
            $index = $client->initIndex("wp_searchable_posts_query_suggestions");
            $results = $index->search("");
            if( !is_array($results) ){
                $results = json_decode($results, true);
            }
        }
    }
    return array(
        'results' => $results,
        'popular_1' => $popular1,
        'popular_2' => $popular2,
        'popular_3' => $popular3,
        'popular_4' => $popular4,
    );
}
/**
 * @author  : Ehsan Khakbaz
 * @version : 1.0.0
 * @return  : PDP Weather Data
 **/
function trek_weather_data()
{
    if (class_exists('Psr\Log\AbstractLogger')) {
        $upload_dir = wp_upload_dir();
        $baseDir = $upload_dir['basedir'] . '/weather/';
        $jsonWeatherPath = $baseDir . 'locations-weather.csv';

        $weatherArray = array();
        $row = 0;
        $country = '';
        $city = '';
        $weatherMapping = array(
            2 => "jan-max", 
            3 => "jan-min", 
            4 => "jan-pre",
            5 => "feb-max", 
            6 => "feb-min", 
            7 => "feb-pre",
            8 => "mar-max", 
            9 => "mar-min",
            10 => "mar-pre", 
            11 => "apr-max", 
            12 => "apr-min", 
            13 => "apr-pre",
            14 => "may-max", 
            15 => "may-min", 
            16 => "may-pre",
            17 => "jun-max", 
            18 => "jun-min", 
            19 => "jun-pre",
            20 => "jul-max", 
            21 => "jul-min", 
            22 => "jul-pre",
            23 => "aug-max", 
            24 => "aug-min",
            25 => "aug-pre", 
            26 => "sep-max",
            27 => "sep-min", 
            28 => "sep-pre",
            29 => "oct-max", 
            30 => "oct-min",
            31 => "oct-pre", 
            32 => "nov-max", 
            33 => "nov-min",
            34 => "nov-pre", 
            35 => "dec-max", 
            36 => "dec-min",
            37 => "dec-pre",
        );

        if (($handle = fopen($jsonWeatherPath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                
                $row++;
                $country = $data[0];
                $city = $data[1];
                $c = 2;

                foreach ($data as $c => $cValue) {

                    if (str_contains($weatherMapping[$c], 'min') || str_contains($weatherMapping[$c], 'max')){

                        $farUnit = number_format((int)$cValue, 1);
                        $celUnit = number_format(($farUnit - 32) * (5/9), 1);
                        $weatherArray[$country][$city]["f"][$weatherMapping[$c]] = $farUnit;
                        $weatherArray[$country][$city]["c"][$weatherMapping[$c]] = $celUnit;
                    } elseif (str_contains($weatherMapping[$c], 'pre')) {
                        $weatherArray[$country][$city]["pre"][$weatherMapping[$c]] = $data[$c];
                    }
                    $c++;
                }
            }
            fclose($handle);
        }
    }
    return $weatherArray;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Change password [ My account page ]
 **/
add_action('wp_ajax_change_password_action', 'trek_change_password_action_cb');
add_action('wp_ajax_nopriv_change_password_action', 'trek_change_password_action_cb');
function trek_change_password_action_cb()
{
    $res = array(
        'status' => false,
        'message' => ''
    );
    $user = wp_get_current_user();
    $cpass = $_REQUEST['password_current'];
    $password_1 = $_REQUEST['password_1'];
    $password_2 = $_REQUEST['password_2'];
    if (!isset($cpass) && empty($cpass)) {
        $res['message'] = "Please enter your current password";
    } elseif (!isset($password_1) && empty($password_1)) {
        $res['message'] = "Please enter your new password";
    } elseif (!isset($password_2) && empty($password_2)) {
        $res['message'] = "Please enter your confirm new password";
    } elseif (!isset($_POST['reset_password_nonce']) || !wp_verify_nonce($_POST['reset_password_nonce'], 'reset_password_action')) {
        $res['message'] = "Sorry, your nonce did not verify.";
    } else {
        if ($user && wp_check_password($cpass, $user->data->user_pass, $user->ID)) {
            wp_set_password($password_1, $user->ID);
            $res['message'] = "Your password has been changed successfully!";
            $res['status'] = true;
        } else {
            $res['message'] = "Incorrect current password!";
        }
    }
    echo json_encode($res);
    exit;
}

/**
 * Send Post Booking checklist data to NetSuite.
 * 
 * Using NS Scripts 1304 to retrieve booking info from NS first,
 * and 1292 to publish updated data.
 * 
 * @param string  $order_id The ID of the order.
 * @param string  $ns_user_id The ID of the user in NetSuite.
 * @param array   $user Current user - converted to array WP_User instance from wp_get_current_user().
 * @param array   $ns_user_booking_args Array with user booking checklist data.
 * 
 * @return void
 */
function tt_update_trip_checklist_ns_cb( $order_id, $ns_user_id, $user, $ns_user_booking_args ) {

    // Check for empty required parameters.
    if( empty( $order_id ) || empty( $ns_user_id ) || empty( $ns_user_booking_args ) ) {
        tt_add_error_log( '[SuiteScript:1292] - Post booking', array( 'success' => false, 'message' => 'Some of the required parameters $order_id, $ns_user_id or $ns_user_booking_args are empty!' ), array( '$order_id' => $order_id, '$ns_user_id' => $ns_user_id, '$ns_user_booking_args' => $ns_user_booking_args ) );
        return;
    }

    $net_suite_client      = new NetSuiteClient();
    $ns_booking_info       = tt_get_ns_booking_details_by_order( $order_id, $user );
    $booking_id            = $ns_booking_info['booking_id'];
    $guest_registration_id = $ns_booking_info['guest_registration_id'];

    if ( $booking_id && $guest_registration_id ) {

        $ns_user_booking_args['registrationId'] = $guest_registration_id;
        $ns_posted_booking_info                 = $net_suite_client->post( CHECKLIST_SCRIPT_ID, json_encode( $ns_user_booking_args ) );

        tt_add_error_log( '[SuiteScript:1292] - Post booking', $ns_user_booking_args, $ns_posted_booking_info );

    } else {
        tt_add_error_log( '[WP] - No Guest Booking ID found', array( 'ns_user_id' => $ns_user_id, 'user_email' => $user['data']['user_email'], 'order_id' => $order_id, 'wp_user_id' => $user['ID'] ), array( 'ns_booking_info' => $ns_booking_info ) );
    }
}
add_action( 'tt_update_trip_checklist_ns', 'tt_update_trip_checklist_ns_cb', 10, 4 );

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Trip checklist saving
 **/
add_action('wp_ajax_update_trip_checklist_action', 'trek_update_trip_checklist_action_cb');
add_action('wp_ajax_nopriv_update_trip_checklist_action', 'trek_update_trip_checklist_action_cb');
function trek_update_trip_checklist_action_cb()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_bookings';
    $res        = array(
        'status'  => false,
        'message' => ''
    );
    $user                = wp_get_current_user();
    $user_order_info     = trek_get_user_order_info( $user->ID, isset( $_REQUEST['order_id'] ) ? $_REQUEST['order_id'] : '' );
    $guest_is_primary    = isset( $user_order_info[0]['guest_is_primary'] ) ? $user_order_info[0]['guest_is_primary'] : '';
    $guest_email_address = isset( $user_order_info[0]['guest_email_address'] ) ? $user_order_info[0]['guest_email_address'] : '';
    $waiver_signed       = isset( $user_order_info[0]['waiver_signed'] ) ? $user_order_info[0]['waiver_signed'] : false;
    $trip_info           = tt_get_trip_pid_sku_from_cart( $_REQUEST['order_id'] );
    $is_hiking_checkout  = tt_is_product_line( 'Hiking', $trip_info['sku'] );
    $trip_style          = json_decode( tt_get_local_trips_detail( 'subStyle', '', $trip_info['sku'], true ) );
    $trip_style_name     = $trip_style ? $trip_style->name : '';
    // The trip sub-style includes either "Training", "Discover", or "Self-Guided" = hide jersey options.
    $hide_jersey_for_arr = array( 'Training', 'Discover', 'Self-Guided' );


    // One of those medical_section, emergency_section, gear_section, passport_section, bike_section, gear_optional_section.
    $confirmed_section   = isset( $_REQUEST['confirmed_section'] ) ? $_REQUEST['confirmed_section'] : '';

    $form_nonce_name     = 'edit_trip_checklist_' . $confirmed_section . '_nonce';
    $form_nonce_action   = 'edit_trip_checklist_' . $confirmed_section . '_action';

    if ( !isset( $_POST[ $form_nonce_name ] ) || !wp_verify_nonce( $_POST[ $form_nonce_name ], $form_nonce_action ) ) {
        $res['message'] = "Sorry, your nonce did not verify.";
    } else {
        $lock_bike   = tt_is_registration_locked( $user->ID, $user_order_info[0]['guestRegistrationId'], 'bike' );
        $lock_record = tt_is_registration_locked( $user->ID, $user_order_info[0]['guestRegistrationId'], 'record' );

        if( $lock_record == true ) {
            $res['message'] = "Sorry, your can't update the information.";
            $res['status']  = false;
            echo json_encode($res);
            exit;
        }

        $order_id = isset( $_REQUEST['order_id'] ) ? $_REQUEST['order_id'] : '';

        $is_section_confirmed = array(
            'medical_section'       => 'medical_section' === $confirmed_section ? true : false,
            'emergency_section'     => 'emergency_section' === $confirmed_section ? true : false,
            'gear_section'          => 'gear_section' === $confirmed_section ? true : false,
            'passport_section'      => 'passport_section' === $confirmed_section ? true : false,
            'bike_section'          => 'bike_section' === $confirmed_section ? true : false,
            'gear_optional_section' => 'gear_optional_section' === $confirmed_section ? true : false,
        );

        // Take current user-confirmed sections info.
        $confirmed_info_user         = get_user_meta( $user->ID, 'pb_checklist_cofirmations', true );
        $confirmed_info_unserialized = maybe_unserialize( $confirmed_info_user );

        if( empty( $confirmed_info_unserialized ) ) {
            // User confirms a section for the first time.
            $confirmed_info_unserialized                                    = array();
            $confirmed_info_unserialized[ $order_id ][ $confirmed_section ] = true;
        } else {
            // Apply sent section as confirmed.
            $confirmed_info_unserialized[ $order_id ][ $confirmed_section ] = true;
        }

        // Serialize again and store into the user meta.
        $confirmed_info_serialized = maybe_serialize( $confirmed_info_unserialized );
        update_user_meta( $user->ID, 'pb_checklist_cofirmations', $confirmed_info_serialized );

        // Collect only confirmed data.
        // Array with the Data for DB.
        $booking_data         = array();
        // Array with the Data for NS.
        $ns_user_booking_data = array();

        // If the confirmed section is 'medical_section', add medical data.
        if( $is_section_confirmed['medical_section'] ) {

            $medical_conditions_value   = 'None';
            $medications_value          = 'None';
            $allergies_value            = 'None';
            $dietary_restrictions_value = 'None';

            if( isset( $_REQUEST['custentity_medicalconditions']['value'] ) && isset( $_REQUEST['custentity_medicalconditions']['boolean'] ) && ! empty( $_REQUEST['custentity_medicalconditions']['value'] ) && 'yes' == $_REQUEST['custentity_medicalconditions']['boolean'] ) {
                $medical_conditions_value = $_REQUEST['custentity_medicalconditions']['value'];
            }

            if( isset( $_REQUEST['custentity_medications']['value'] ) && isset( $_REQUEST['custentity_medications']['boolean'] ) && ! empty( $_REQUEST['custentity_medications']['value'] ) && 'yes' == $_REQUEST['custentity_medications']['boolean'] ) {
                $medications_value = $_REQUEST['custentity_medications']['value'];
            }

            if( isset( $_REQUEST['custentity_allergies']['value'] ) && isset( $_REQUEST['custentity_allergies']['boolean'] ) && ! empty( $_REQUEST['custentity_allergies']['value'] ) && 'yes' == $_REQUEST['custentity_allergies']['boolean'] ) {
                $allergies_value = $_REQUEST['custentity_allergies']['value'];
            }

            if( isset( $_REQUEST['custentity_dietaryrestrictions']['value'] ) && isset( $_REQUEST['custentity_dietaryrestrictions']['boolean'] ) && ! empty( $_REQUEST['custentity_dietaryrestrictions']['value'] ) && 'yes' == $_REQUEST['custentity_dietaryrestrictions']['boolean'] ) {
                $dietary_restrictions_value = $_REQUEST['custentity_dietaryrestrictions']['value'];
            }

            // Data for DB.
            $booking_data['medical_conditions']          = $medical_conditions_value;
            $booking_data['medications']                 = $medications_value;
            $booking_data['allergies']                   = $allergies_value;
            $booking_data['dietary_restrictions']        = $dietary_restrictions_value;

            // Data for NS.
            $ns_user_booking_data['medicalConditions']   = $medical_conditions_value;
            $ns_user_booking_data['medications']         = $medications_value;
            $ns_user_booking_data['allergies']           = $allergies_value;
            $ns_user_booking_data['dietaryRestrictions'] = $dietary_restrictions_value;
        }

        // If the confirmed section is 'emergency_section', add emergency contact data.
        if( $is_section_confirmed['emergency_section'] ) {
            $booking_data['emergency_contact_first_name']   = tt_validate( $_REQUEST['emergency_contact_first_name'] );
            $booking_data['emergency_contact_last_name']    = tt_validate( $_REQUEST['emergency_contact_last_name'] );
            $booking_data['emergency_contact_phone']        = tt_validate( $_REQUEST['emergency_contact_phone'] );
            $booking_data['emergency_contact_relationship'] = tt_validate( $_REQUEST['emergency_contact_relationship'] );

            $ns_user_booking_data['ecFirstName']            = tt_validate( $_REQUEST['emergency_contact_first_name'] );
            $ns_user_booking_data['ecLastName']             = tt_validate( $_REQUEST['emergency_contact_last_name'] );
            $ns_user_booking_data['ecPhone']                = tt_validate( $_REQUEST['emergency_contact_phone'] );
            $ns_user_booking_data['ecRelationship']         = tt_validate( $_REQUEST['emergency_contact_relationship'] );
        }

        // If the confirmed section is 'gear_section', add gear data.
        if( $is_section_confirmed['gear_section'] ) {
            $booking_data['rider_height']              = tt_validate( $_REQUEST['tt-rider-height'] );
            $booking_data['pedal_selection']           = tt_validate( $_REQUEST['tt-pedal-selection'] );
            $booking_data['helmet_selection']          = tt_validate( $_REQUEST['tt-helmet-size'] );
            if ( ! in_array( $trip_style_name, $hide_jersey_for_arr ) ) {
                // Store Jersey fields only when they are applicable for the trip.
                $booking_data['jersey_style'] = tt_validate( $_REQUEST['tt-jerrsey-style'] );
            }

            if ( $is_hiking_checkout ) {
                $booking_data['tshirt_size'] = tt_validate( $_REQUEST['tt-jerrsey-size'] );
            } elseif ( ! in_array( $trip_style_name, $hide_jersey_for_arr ) ) {
                // Store Jersey fields only when they are applicable for the trip.
                $booking_data['tt_jersey_size'] = tt_validate( $_REQUEST['tt-jerrsey-size'] );
            }
            
            $ns_user_booking_data['heightId']          = tt_validate( $_REQUEST['tt-rider-height'] );
            $ns_user_booking_data['helmetId']          = tt_validate( $_REQUEST['tt-helmet-size'] );
            $ns_user_booking_data['pedalsId']          = tt_validate( $_REQUEST['tt-pedal-selection'] );

            if ( $is_hiking_checkout ) {
                $ns_user_booking_data['tshirtSizeId'] = tt_validate( $_REQUEST['tt-jerrsey-size'] );
            } elseif ( ! in_array( $trip_style_name, $hide_jersey_for_arr ) ) {
                $ns_user_booking_data['jerseyId'] = tt_validate( $_REQUEST['tt-jerrsey-size'] );
            }
        }

        // If the confirmed section is 'passport_section', add passport data.
        if( $is_section_confirmed['passport_section'] ) {
            $booking_data['passport_number']          = tt_validate( $_REQUEST['passport_number'] );
            $booking_data['passport_issue_date']      = tt_validate( $_REQUEST['passport_issue_date'] ); // This is not populated from the my trip checklist form.
            $booking_data['passport_expiration_date'] = tt_validate( $_REQUEST['passport_expiration_date'] );
            $booking_data['passport_place_of_issue']  = tt_validate( $_REQUEST['passport_place_of_issue'] );
            $booking_data['full_name_on_passport']    = tt_validate( $_REQUEST['full_name_on_passport'] );

            // Collect passport information to send to NetSuite.

            // The NS Script 1292 expects first and last names as separate fields, but we collect the full name from the user's form.
            // So let's split the names by space and extract first and last names.
            $guest_names                                = explode( ' ', tt_validate( $_REQUEST['full_name_on_passport'] ) );
            $guest_last_name                            = array_pop( $guest_names );
            $guest_first_name                           = implode( ' ', $guest_names );
            $ns_user_booking_data['passportFirstName']  = $guest_first_name;
            $ns_user_booking_data['passportLastName']   = $guest_last_name;

            $ns_user_booking_data['passportNumber']     = tt_validate( $_REQUEST['passport_number'] );
            $ns_user_booking_data['passportIssuePlace'] = tt_validate( $_REQUEST['passport_place_of_issue'] );
            $ns_user_booking_data['passportExpDate']    = tt_validate( $_REQUEST['passport_expiration_date'] );
        }

        // If the confirmed section is 'bike_section', add bike data.
        if( $is_section_confirmed['bike_section'] ) {
            // If $_REQUEST['bikeId'] is with value 0, we need send 0 to NS, that means customer selected "I don't know" option for $_REQUEST['tt-bike-size'].
            $default_bike_id = '';
            if ( isset( $_REQUEST['bikeId'] ) && is_numeric( $_REQUEST['bikeId'] ) && 0 === (int) $_REQUEST['bikeId'] ) {
                $default_bike_id = 0;

            }

            $booking_data['bike_selection'] = tt_validate( $_REQUEST['bikeId'], $default_bike_id );
            $booking_data['bike_type_id']   = tt_validate( $_REQUEST['bikeTypeId'] );
            $booking_data['bike_id']        = tt_validate( $_REQUEST['bikeId'], $default_bike_id );
            $booking_data['bike_size']      = tt_validate( $_REQUEST['tt-bike-size'] );

            $ns_user_booking_data['bikeId']       = tt_validate( $_REQUEST['bikeId'], $default_bike_id );
            $ns_user_booking_data['bikeTypeName'] = tt_ns_get_bike_type_name( tt_validate( $_REQUEST['bikeTypeId'] ) );
        }

        // If the confirmed section is 'gear_optional_section', add gear optional data.
        if( $is_section_confirmed['gear_optional_section'] ) {
            $booking_data['saddle_height']                       = tt_validate( $_REQUEST['saddle_height'] );
            $booking_data['saddle_bar_reach_from_saddle']        = tt_validate( $_REQUEST['bar_reach'] );
            $booking_data['saddle_bar_height_from_wheel_center'] = tt_validate( $_REQUEST['bar_height'] );

            $ns_user_booking_data['saddleHeight']                = tt_validate( $_REQUEST['saddle_height'] );
            $ns_user_booking_data['barReachFromSaddle']          = tt_validate( $_REQUEST['bar_reach'] );
            $ns_user_booking_data['barHeightFromWheelCenter']    = tt_validate( $_REQUEST['bar_height'] );
        }

        if ( empty( $guest_email_address ) ) {
            $booking_data['guest_email_address'] = $user->user_email;
        }

        $shipping_add1     = isset( $_REQUEST['shipping_address_1'] ) ? $_REQUEST['shipping_address_1'] : '';
        $shipping_add2     = isset( $_REQUEST['shipping_address_2'] ) ? $_REQUEST['shipping_address_2'] : '';
        $shipping_city     = isset( $_REQUEST['shipping_city'] ) ? $_REQUEST['shipping_city'] : '';
        $shipping_state    = isset( $_REQUEST['shipping_state'] ) ? $_REQUEST['shipping_state'] : '';
        $shipping_country  = isset( $_REQUEST['shipping_country'] ) ? $_REQUEST['shipping_country'] : '';
        $shipping_postcode = isset( $_REQUEST['shipping_postcode'] ) ? $_REQUEST['shipping_postcode'] : '';

        if ( $guest_is_primary != 1 ) {
            $booking_data['shipping_address_1']       = $shipping_add1;
            $booking_data['shipping_address_2']       = $shipping_add2;
            $booking_data['shipping_address_city']    = $shipping_city;
            $booking_data['shipping_address_state']   = $shipping_state;
            $booking_data['shipping_address_country'] = $shipping_country;
            $booking_data['shipping_address_zipcode'] = $shipping_postcode;
        }

        // Update user meta data for preferences.
        $save_prefs_for_future_use = true; // Duplicate the logic from the NS Script 1292 to keep consistency on the website like in NetSuite. If you set it to false, it will continue to execute the old logic where you need the checkbox to be checked by the user to keep the information.

        if ( $save_prefs_for_future_use || ( isset( $_REQUEST['tt_save_medical_info'] ) && $_REQUEST['tt_save_medical_info'] == 'yes' && $is_section_confirmed['medical_section'] ) ) {
            $input_posted = array( 'custentity_medications', 'custentity_medicalconditions', 'custentity_allergies', 'custentity_dietaryrestrictions' );
            if ( $input_posted && $_REQUEST ) {
                foreach ( $input_posted as $input_post ) {
                    $medical_input = $_REQUEST[$input_post];
                    if ( isset( $medical_input ) && $medical_input['boolean'] == 'yes' && !empty( $medical_input['value'] ) ) {
                        update_user_meta( $user->ID, $input_post, $medical_input['value'] );
                    } else if ( isset( $medical_input ) && $medical_input['boolean'] == 'no' ) {
                        update_user_meta( $user->ID, $input_post, 'None' );
                    } else {
                        update_user_meta( $user->ID, $input_post, '' );
                    }
                }
            }
        }

        if ( $save_prefs_for_future_use || ( isset( $_REQUEST['tt_save_shipping_info'] ) && $_REQUEST['tt_save_shipping_info'] == 'yes' ) ) {
            update_user_meta( $user->ID, "shipping_address_1", $shipping_add1 );
            update_user_meta( $user->ID, "shipping_address_2", $shipping_add2 );
            update_user_meta( $user->ID, "shipping_city", $shipping_city );
            update_user_meta( $user->ID, "shipping_state", $shipping_state );
            update_user_meta( $user->ID, "shipping_postcode", $shipping_postcode );
            update_user_meta( $user->ID, "shipping_country", $shipping_country );
        }

        if ( $save_prefs_for_future_use || ( isset( $_REQUEST['tt_save_emergency_info'] ) && $_REQUEST['tt_save_emergency_info'] == 'yes' && $is_section_confirmed['emergency_section'] ) ) {
            update_user_meta( $user->ID, 'custentity_emergencycontactfirstname', $_REQUEST['emergency_contact_first_name']);
            update_user_meta( $user->ID, 'custentityemergencycontactlastname', $_REQUEST['emergency_contact_last_name']);
            update_user_meta( $user->ID, 'custentity_emergencycontactphonenumber', $_REQUEST['emergency_contact_phone']);
            update_user_meta( $user->ID, 'custentity_emergencycontactrelationship', $_REQUEST['emergency_contact_relationship']);
        }

        if ( $save_prefs_for_future_use || ( isset( $_REQUEST['tt_save_gear_info'] ) && $_REQUEST['tt_save_gear_info'] == 'yes'  && $is_section_confirmed['gear_section'] ) ) {
            update_user_meta( $user->ID, 'gear_preferences_rider_height', $_REQUEST['tt-rider-height'] );
            update_user_meta( $user->ID, 'gear_preferences_select_pedals', $_REQUEST['tt-pedal-selection'] );
            update_user_meta( $user->ID, 'gear_preferences_helmet_size', $_REQUEST['tt-helmet-size'] );
            if( ! empty( $_REQUEST['tt-jerrsey-style'] ) ) {

                update_user_meta( $user->ID, 'gear_preferences_jersey_style', $_REQUEST['tt-jerrsey-style'] );
            }
            if( ! empty( $_REQUEST['tt-jerrsey-size'] ) ) {
                
                update_user_meta( $user->ID, 'gear_preferences_jersey_size', $_REQUEST['tt-jerrsey-size'] );
            }
        }

        if ( $save_prefs_for_future_use || ( isset( $_REQUEST['tt_save_passport_info'] ) && $_REQUEST['tt_save_passport_info'] == 'yes' ) ) {
            update_user_meta( $user->ID, 'custentity_passport_number', isset( $_REQUEST['passport_number'] ) ? $_REQUEST['passport_number'] : '' );
            update_user_meta( $user->ID, 'custentity_passport_exp_date', isset( $_REQUEST['passport_expiration_date'] ) ? ( new DateTime( $_REQUEST['passport_expiration_date'] ) )->format( 'm/d/Y' ) : '' );
            update_user_meta( $user->ID, 'custentity_passport_issue_place', isset( $_REQUEST['passport_place_of_issue'] ) ? $_REQUEST['passport_place_of_issue'] : '' );
            update_user_meta( $user->ID, 'custentity_full_name_on_passport', isset( $_REQUEST['full_name_on_passport'] ) ? $_REQUEST['full_name_on_passport'] : '' );
        }

        if ( $save_prefs_for_future_use || ( isset( $_REQUEST['tt_save_bike_info'] ) && $_REQUEST['tt_save_bike_info'] == 'yes' && $is_section_confirmed['bike_section'] ) ) {
            update_user_meta( $user->ID, 'gear_preferences_bike_type', $_REQUEST['bike_type_id_preferences'] );
            update_user_meta( $user->ID, 'gear_preferences_bike_size', $_REQUEST['tt-bike-size'] );
            update_user_meta( $user->ID, 'gear_preferences_bike', $_REQUEST['bikeId'] );
        }

        if ( $save_prefs_for_future_use || ( isset( $_REQUEST['tt_save_gear_info_optional'] ) && $_REQUEST['tt_save_gear_info_optional'] == 'yes' ) ) {
            update_user_meta( $user->ID, 'gear_preferences_saddle_height', $_REQUEST['saddle_height'] );
            update_user_meta( $user->ID, 'gear_preferences_bar_reach', $_REQUEST['bar_reach'] );
            update_user_meta( $user->ID, 'gear_preferences_bar_height', $_REQUEST['bar_height'] );
        }

        $ns_user_id = get_user_meta( $user->ID, 'ns_customer_internal_id', true );

        if ( $ns_user_id ) {
            // Update guest booking information in NetSuite.
            as_schedule_single_action( time(), 'tt_update_trip_checklist_ns', array( $order_id, $ns_user_id, $user, $ns_user_booking_data ) );

        } else {
            tt_add_error_log( '[NetSuite] - User not found', array( 'user_id' => $user->ID, 'First name' => $user->first_name ), array() );
        }
        $booking_data['modified_at'] = date('Y-m-d H:i:s');
        $where['order_id']           = $_REQUEST['order_id'];
        if( $guest_email_address ) {
            $where['guest_email_address'] = $user->user_email;
        } else {
            if( $user->ID ){
                $where['user_id'] = $user->ID;
            }
        }
        $is_updated          = $wpdb->update( $table_name, $booking_data, $where );
        if ( $wpdb->last_error ) {
            tt_add_error_log( '[Faild] Update Booking From Post-Booking', array( 'order_id' => $order_id, 'ns_user_id' => $ns_user_id ), array( 'last_error' => $wpdb->last_error ) );
        }
        $res['status']       = true;
        $res['error']        = $wpdb->last_query;
        $res['booking_data'] = $booking_data;
        $res['where']        = $where;
        $res['message']      = "Your Checklist information has been changed successfully!";
        $res['is_primary']   = $guest_is_primary && $guest_is_primary == 1 ? true : false;
        tt_add_error_log('Post booking Log', $res, ['user_id' => $user->ID,'ns_user_id' => $ns_user_id, 'date' => date('Y-m-d H:i:s')]);
    }
    echo json_encode($res);
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Edit medical information [ My account page ]
 **/
add_action('wp_ajax_update_medical_information_action', 'trek_update_medical_information_action_cb');
add_action('wp_ajax_nopriv_update_medical_information_action', 'trek_update_medical_information_action_cb');
function trek_update_medical_information_action_cb()
{
    $res = array(
        'status' => false,
        'message' => ''
    );
    if (!isset($_POST['edit_user_medical_info_nonce']) || !wp_verify_nonce($_POST['edit_user_medical_info_nonce'], 'edit_medical_info_action')) {
        $res['message'] = "Sorry, your nonce did not verify.";
    } else {
        $user = wp_get_current_user();
        $input_posted = array('custentity_medications', 'custentity_medicalconditions', 'custentity_allergies', 'custentity_dietaryrestrictions');
        if ($input_posted) {
            foreach ($input_posted as $input_post) {
                $medical_input = $_REQUEST[$input_post];
                if (isset($medical_input) && $medical_input['boolean'] == 'yes' && !empty($medical_input['value'])) {
                    update_user_meta($user->ID, $input_post, $medical_input['value']);
                } else if ( isset( $medical_input ) && $medical_input['boolean'] == 'no' ) {
                    update_user_meta( $user->ID, $input_post, 'None' );
                } else {
                    update_user_meta($user->ID, $input_post, '');
                }
            }
            as_schedule_single_action(time(), 'tt_cron_syn_usermeta_ns', array( $user->ID, '[Medical information]' ));
            $res['status'] = true;
            $res['message'] = "Your medical information has been changed successfully!";
        } else {
            $res['message'] = "Something went while updating informations!";
        }
    }
    echo json_encode($res);
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Add to cart button action Redirection to checkout page
 **/
add_filter('woocommerce_add_to_cart_redirect', 'trek_redirect_to_checkout');
function trek_redirect_to_checkout()
{
    return add_query_arg(array('step' => 1), wc_get_checkout_url());
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Body class filter for adding new classes on pages
 **/
add_filter('body_class', 'custom_class');
function custom_class($classes)
{
    if (is_page()) {
        global $post;
        $classes[] = 'trek-' . $post->post_name;
    }
    return $classes;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get Trek user checkout saved data from steps
 **/
function get_trek_user_checkout_data() {
    $accepted_p_ids = tt_get_line_items_product_ids();
    $tt_posted      = $tt_formatted = array();

    if ( ! WC()->cart->is_empty() ) {
        // Cart has items.
        // Get the current cart contents.
        $cart_contents = WC()->cart->get_cart_contents();
        foreach ( $cart_contents as $cart_item ) {
            if ( isset( $cart_item['product_id'] ) && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
                $tt_posted    = isset( $cart_item['trek_user_checkout_data'] ) ? $cart_item['trek_user_checkout_data'] : array();
                $tt_formatted = isset( $cart_item['trek_user_formatted_checkout_data'] ) ? $cart_item['trek_user_formatted_checkout_data'] : array();
            }
        }
    }

    return array(
        'posted'    => $tt_posted,
        'formatted' => $tt_formatted
    );
}

/**
 * Allow only 1 Product/Package in the cart and replace it with the new product.
 *
 * @param boolean $passed_validation True if the item passed validation.
 * @param integer $product_id        Product ID being validated.
 * @param integer $quantity          Quantity added to the cart.
 *
 * @return bool
 */
function trek_woocom_cart_validation_cb( $passed_validation, $product_id, $quantity ) {
    $is_tt_valid       = tt_get_cart_products_ids();
    $custom_fees_p_ids = tt_get_line_items_product_ids();

    if ( $custom_fees_p_ids && ! in_array( $product_id, $custom_fees_p_ids ) ) {
        if ( ! empty( WC()->cart->get_cart() ) && false == $is_tt_valid ) {
            do_action( 'tt_clear_persistent_cart' );
        }
    }

    return $passed_validation;
}
add_filter( 'woocommerce_add_to_cart_validation', 'trek_woocom_cart_validation_cb', 20, 3 );

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Login Feature
 **/
add_action( 'wp_ajax_trek_login_action', 'trek_trek_login_action_cb' );
add_action( 'wp_ajax_nopriv_trek_login_action', 'trek_trek_login_action_cb' );
function trek_trek_login_action_cb() {
    $res = array(
        'status' => false,
        'message' => ''
    );
    $email         = $_REQUEST['email'];
    $password      = $_REQUEST['password'];
    $is_rememberme = isset( $_REQUEST['is_rememberme'] ) && 'true' === $_REQUEST['is_rememberme'] ? true : false;
    if ( ! isset( $_POST['woocommerce-login-nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce-login-nonce'], 'woocommerce-login' ) ) {
        $res['message'] = __( 'Sorry, your nonce did not verify.', 'trek-travel-theme' );
    } elseif ( ! isset( $email ) && empty( $email ) ) {
        $res['message'] = __( 'Please enter your email address', 'trek-travel-theme' );
    } elseif ( ! email_exists( $email ) ) {
        $res['message'] = __( "That E-mail doesn't belong to any registered users on this site.", 'trek-travel-theme' );
    } else {
        $user_login = $email;
        if ( is_email( $email ) ) {
            $current_user = get_user_by('email', $email);
            $user_login   = $current_user->user_login;
        }
        $creds = array(
            'user_login'    => $user_login,
            'user_password' => $password,
            'remember'      => true
        );
        $user = wp_signon( $creds, false );
        if ( is_wp_error( $user ) ) {
            $res['message'] = $user->get_error_message();
        } else {
            if( isset( $user->ID ) && ! empty( $user->ID ) ) {
                wp_clear_auth_cookie();
                wp_set_auth_cookie( $user->ID, $is_rememberme ); // Set auth details in cookie.
            }
            $res['status']  = true;
            $res['message'] = __( 'You have successfully loggedin!', 'trek-travel-theme' );

            if ( tt_should_redirect_user_to_checkout() ) {
                $res['redirect'] = trek_checkout_step_link(1);
            } else {
                $http_referer   = $_REQUEST['http_referer'];
                $page_id        = url_to_postid( $http_referer );
                $ref_source_url = parse_url( $http_referer );
                $site_url_parse = parse_url( site_url() );
                if ( $ref_source_url['host'] === $site_url_parse['host'] && 'product' === get_post_type( $page_id ) ) {
                    $res['redirect'] = $http_referer;
                } else {
                    $res['redirect'] = site_url( 'my-account' );
                }
            }
        }
    }
    echo json_encode( $res );
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get checkout steps URL
 **/
function trek_checkout_step_link($step = 1)
{
    return add_query_arg(
        array(
            'step' => $step
        ),
        site_url('checkout')
    );
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Get Insurance quote
 **/
add_action('wp_ajax_get_quote_travel_protection_action', 'trek_get_quote_travel_protection_action_cb');
add_action('wp_ajax_nopriv_get_quote_travel_protection_action', 'trek_get_quote_travel_protection_action_cb');
function trek_get_quote_travel_protection_action_cb()
{
    $res = array(
        'status'       => false,
        'message'      => '',
        'review_order' => ''
    );
    $accepted_p_ids  = tt_get_line_items_product_ids();
    $fees_product_id = tt_create_line_item_product( 'TTWP23FEES' );
    $is_fees_exist   = array();
    $plan_id         = 'TREKTRAVEL23';

    if( ! empty( get_field( 'plan_id', 'option' ) ) ) {
        $plan_id = get_field( 'plan_id', 'option' );
    }

    // Add travels data to Cart object.
    $cart                                 = WC()->cart->get_cart_contents();
    $guest_insurance_data_arr             = array();
    $guests_insurance_data                = array();
    $guests_insurance_data['guest_email'] = array();
    foreach ( $cart as $cart_item_id => $cart_item ) {
        $product_id = $cart_item['product_id'];
        $product    = wc_get_product($product_id);
        $sku        = $product->get_sku();
        if ( ! in_array( $product_id, $accepted_p_ids ) ) {
            // Do not overwrite trek_user_checkout_data; instead, merge it with the information already collected in trek_user_checkout_data.
            $cart_req_data = wp_unslash( $_REQUEST );
            $cart_item['trek_user_checkout_data'] = array_merge( $cart_item['trek_user_checkout_data'], $cart_req_data );
            $cart_posted_data = $cart_item['trek_user_checkout_data'];
            $insuredReq  = isset($_REQUEST['trek_guest_insurance']) ? $_REQUEST['trek_guest_insurance'] : [];
            $insuredReqGuests = isset($insuredReq['guests']) ? $insuredReq['guests'] : [];
            $insuredPosted  = isset($cart_posted_data['trek_guest_insurance']) ? $cart_posted_data['trek_guest_insurance'] : [];
            $insuredPostedGuests = isset($insuredPosted['guests']) ? $insuredPosted['guests'] : [];
            $guest_travel_req = $insuredReqGuests ? $insuredReqGuests : $insuredPostedGuests;
            $guest_travel_req = $guest_travel_req && is_array($guest_travel_req) ? $guest_travel_req : array();
            $guest_insurance_data_arr = array(
                'cart_item' => 'Travel protection fee',
                'quantity' => count($guest_travel_req) + 1
            );
            $guestReq = isset($_REQUEST['guests']) ? $_REQUEST['guests'] : [];
            $guestPosted = isset($cart_posted_data['guests']) ? $cart_posted_data['guests'] : [];
            $guest_req = $guestReq ? $guestReq : $guestPosted;
            $guest_req = $guest_req && is_array($guest_req) ? $guest_req : array();
            if ($cart_posted_data && !empty($cart_posted_data)) {
                foreach ($cart_posted_data['trek_guest_insurance'] as $guest_insurance_k => $guest_insurance) {
                    if ($guest_insurance_k == 'primary' && $guest_insurance['is_travel_protection'] == '1') {
                        if (isset($cart_posted_data['email']) && $cart_posted_data['email']) {
                            $guests_insurance_data['guest_email'][] = $cart_posted_data['email'];
                        }
                    }
                    if ($guest_insurance_k == 'guests' && $guest_insurance) {
                        foreach ($guest_insurance as $guest_insurance_inner_k => $guest_insurance_inner) {
                            if ($guest_insurance_inner['is_travel_protection'] == '1') {
                                $inner_guest_arr = $guest_req[$guest_insurance_inner_k];
                                if ($inner_guest_arr && isset($inner_guest_arr['guest_email']) && $inner_guest_arr['guest_email']) {
                                    $guests_insurance_data['guest_email'][] = $inner_guest_arr['guest_email'];
                                }
                            }
                        }
                    }
                }
                $guest_insurance_data_arr['cart_item_data'] = $guests_insurance_data;
            }
            $cart_item['trek_user_formatted_checkout_data'][2] = $guest_insurance_data_arr;
            $cart[$cart_item_id] = $cart_item;
        }

        if ( 'TTWP23FEES' === $sku ) {
            $is_fees_exist[] = true;
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    // Preparing insurance HTML.
    $tt_checkoutData =  get_trek_user_checkout_data();
    $tt_posted       = isset( $tt_checkoutData['posted'] ) ? $tt_checkoutData['posted'] : array();
    $product_id      = null;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
            $product_id = $cart_item['product_id'];
        }
        $supplement_fees_product_id = tt_create_line_item_product( 'TTWP23SUPP' );
        if( isset( $cart_item['product_id'] ) && $cart_item['product_id'] == $supplement_fees_product_id ) {
            $supplement_fees = wc_get_product( $supplement_fees_product_id );
        }
    }
    $product = wc_get_product( $product_id );

    $individualTripCost = 0;
    $sdate_info = $edate_info = '';
    if( $product ){
        $individualTripCost = $product->get_price();

        if( ! empty( $supplement_fees ) ) {
            $single_supplement_price = $supplement_fees->get_price();
            $individualTripCost = $individualTripCost + $single_supplement_price;
        }

        $trip_sdate = $product->get_attribute('pa_start-date');
        $sdate_obj = explode('/', $trip_sdate);
        $sdate_info = array(
            'd' => $sdate_obj[0],
            'm' => $sdate_obj[1],
            'y' => substr(date('Y'),0,2).$sdate_obj[2]
        );
        $trip_edate = $product->get_attribute('pa_end-date');
        $edate_obj = explode('/', $trip_edate);
        $edate_info = array(
            'd' => $edate_obj[0],
            'm' => $edate_obj[1],
            'y' => substr(date('Y'),0,2).$edate_obj[2]
        );
    }
    $insuredPerson        = array();
    $insuredPerson_single = array();
    $effectiveDate        = '';
    $expirationDate       = '';
    if ( $sdate_info && is_array( $sdate_info ) ) {
        $effectiveDate = date( 'Y-m-d', strtotime(implode('-', $sdate_info ) ) );
    }
    if ( $edate_info && is_array( $edate_info ) ) {
        $expirationDate = date( 'Y-m-d', strtotime(implode( '-', $edate_info ) ) );
    }

    // Current date minus 3 hours to match with the Arch time.
    $current_date = date('Y-m-d', strtotime('-3 hours' ) );

    $trek_insurance_args = [
        "coverage" => [
            "effectiveDate" => $effectiveDate,
            "expirationDate" => $expirationDate,
            "depositDate" => $current_date,
            "destinations" => [
                [
                    "countryCode" => $tt_posted['shipping_country']
                ]
            ]
        ],
        "planID" => $plan_id,
        "language" => "en-us",
        "returnTravelerQuotes" => true
    ];
    $guest_insurance = isset($tt_posted['trek_guest_insurance']) ? $tt_posted['trek_guest_insurance'] : [];
    $is_travel_protection_count = 0;
    $tt_total_insurance_amount = 0;
    if (isset($guest_insurance) && !empty($guest_insurance)) {
        foreach ($guest_insurance as $guest_insurance_k => $guest_insurance_val) {
            $trek_insurance_args["insuredPerson"] = array();
            if ($guest_insurance_k == 'primary') {
                if ($guest_insurance_val['is_travel_protection'] == 1) {
                    $is_travel_protection_count++;
                }
                $insuredPerson[] = array(
                    "address" => [
                        "stateAbbreviation" => $tt_posted['shipping_state'],
                        "countryAbbreviation" => $tt_posted['shipping_country']
                    ],
                    "dob" => $tt_posted['custentity_birthdate'],
                    "individualTripCost" => $individualTripCost
                );
                $insuredPerson_single = [];
                $insuredPerson_single[] = array(
                    "address" => [
                        "stateAbbreviation" => $tt_posted['shipping_state'],
                        "countryAbbreviation" => $tt_posted['shipping_country']
                    ],
                    "dob" => $tt_posted['custentity_birthdate'],
                    "individualTripCost" => $individualTripCost
                );
                $trek_insurance_args["insuredPerson"] = $insuredPerson_single;
                $archinsuranceResPP = $insuredReq['primary'];
                $arcBasePremiumPP = isset($archinsuranceResPP['basePremium']) ? $archinsuranceResPP['basePremium'] : 0;
                $guest_insurance['primary']['basePremium'] = $arcBasePremiumPP;
                if ($guest_insurance_val['is_travel_protection'] == 1) {
                    $tt_total_insurance_amount += $arcBasePremiumPP ;
                }
            } else {
                foreach ($guest_insurance_val as $guest_key => $guest_insurance_Data) {
                    $guestInfo = $tt_posted['guests'][$guest_key];
                    if ($guest_insurance_Data['is_travel_protection'] == 1) {
                        $is_travel_protection_count++;
                    }
                    $insuredPerson[] = array(
                        "address" => [
                            "stateAbbreviation" => $tt_posted['shipping_state'],
                            "countryAbbreviation" => $tt_posted['shipping_country']
                        ],
                        "dob" => $guestInfo['guest_dob'],
                        "individualTripCost" => $individualTripCost
                    );
                    $insuredPerson_single = [];
                    $insuredPerson_single[] = array(
                        "address" => [
                            "stateAbbreviation" => $tt_posted['shipping_state'],
                            "countryAbbreviation" => $tt_posted['shipping_country']
                        ],
                        "dob" => $guestInfo['guest_dob'],
                        "individualTripCost" => $individualTripCost
                    );
                    $trek_insurance_args["insuredPerson"] = $insuredPerson_single;
                    $archinsuranceResPG = $insuredReq['guests'];
                    $arcBasePremiumPG = isset($archinsuranceResPG[$guest_key]['basePremium']) ? $archinsuranceResPG[$guest_key]['basePremium'] : 0;
                    $guest_insurance['guests'][$guest_key]['basePremium'] = $arcBasePremiumPG;
                    if ($guest_insurance_Data['is_travel_protection'] == 1) {
                        $tt_total_insurance_amount += $arcBasePremiumPG ;
                    }
                }
            }
        }
    }
    $trek_insurance_args["insuredPerson"] = $insuredPerson;
    $insuredPersonCount = $is_travel_protection_count; //count($insuredPerson);
    $arcBasePremium = $tt_total_insurance_amount && $tt_total_insurance_amount > 0 ? $tt_total_insurance_amount : 0;
    if( $insuredPersonCount > 0 ){
        if( ! in_array( true, $is_fees_exist ) ){
            WC()->cart->add_to_cart( $fees_product_id, 1, 0, array(), array( 'tt_cart_custom_fees_price' => $arcBasePremium ) );
        }
    }
    // Save cart Logic. Take the cart again, because above we added Travel Protection to the cart.
    $cart = WC()->cart->get_cart_contents();
    foreach ( $cart as $cart_item_id => $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && $cart_item['product_id'] == $fees_product_id ) {
            $product             = wc_get_product( $cart_item['product_id'] );
            $cart[$cart_item_id] = $cart_item;
            $sku                 = $product->get_sku();
            if ( $sku == 'TTWP23FEES' ) {
                if ( isset( $cart_item['tt_cart_custom_fees_price'] ) && $cart_item['tt_cart_custom_fees_price'] > 0 ) {
                    if( in_array( true, $is_fees_exist ) ) {
                        $cart_item['data']->set_price( $arcBasePremium );
                    } else {
                        $cart_item['data']->set_price( $cart_item['tt_cart_custom_fees_price'] );
                    }
                }
                $cart[$cart_item_id]['tt_cart_custom_fees_price'] = $arcBasePremium;
            }
            $cart[$cart_item_id]['quantity'] = 1;
        }
        if ( isset( $cart_item['product_id'] )  && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
            $cart_item['trek_user_checkout_data']['trek_guest_insurance']       = $guest_insurance;
            $cart_item['trek_user_checkout_data']['insuredPerson']              = $is_travel_protection_count;
            $cart_item['trek_user_checkout_data']['tt_insurance_total_charges'] = $arcBasePremium;
            $cart_item['trek_user_checkout_data']['is_protection_modal_showed'] = true;
            $cart[$cart_item_id]                                                = $cart_item;
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    // End: Save cart logic.
    $review_order_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if ( is_readable( $review_order ) ) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $payment_option_html = '';
    $review_order        = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php';
    if ( is_readable( $review_order ) ) {
        $payment_option_html .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php');
    } else {
        $payment_option_html .= '<h3>Step 4</h3><p>Checkout payment option file is missing!</p>';
    }
    $insuredHTMLPopup       = '';
    $checkout_insured_users = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php';
    if ( is_readable( $checkout_insured_users ) ) {
        $insuredHTMLPopup .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php');
    } else {
        $insuredHTMLPopup .= '<h3>Step 4</h3><p>checkout-insured-guests-popup.php form code is missing!</p>';
    }
    $guest_insurance_html          = '';
    $guest_insurance_html_template = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-summary.php';
    if ( is_readable( $guest_insurance_html_template ) ) {
        $guest_insurance_html .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-summary.php');
    } else {
        $guest_insurance_html .= '<h3>Step 4</h3><p>checkout-insured-summary.php form code is missing!</p>';
    }
    $res['status']               = true;
    $res['fees_product_id']      = $fees_product_id;
    $res['guest_insurance_html'] = $guest_insurance_html;
    $res['insuredHTMLPopup']     = $insuredHTMLPopup;
    $res['review_order']         = $review_order_html;
    $res['payment_option']       = $payment_option_html;
    $res['arcBasePremium']       = $arcBasePremium;
    $res['message']              = "Your information has been changed successfully!";
    echo json_encode( $res );
    exit;
}
/* @return  : Ajax action for Bike & Gear [ My account page ]
 **/
add_action('wp_ajax_update_bike_gear_info_action', 'trek_update_bike_gear_info_action_cb');
add_action('wp_ajax_nopriv_update_bike_gear_info_action', 'trek_update_bike_gear_info_action_cb');
function trek_update_bike_gear_info_action_cb()
{
    $res = array(
        'status' => false,
        'message' => ''
    );
    if (!isset($_POST['edit_bike_gear_info_nonce']) || !wp_verify_nonce($_POST['edit_bike_gear_info_nonce'], 'edit_bike_gear_form_action')) {
        $res['message'] = "Sorry, your nonce did not verify.";
    } else {
        $user = wp_get_current_user();
        $bike_references_fields = array(
            'gear_preferences_bike_type',
            'gear_preferences_rider_height',
            'gear_preferences_select_pedals',
            'gear_preferences_helmet_size',
            'gear_preferences_jersey_style',
            'gear_preferences_jersey_size',
            'gear_preferences_saddle_height',
            'gear_preferences_bar_reach',
            'gear_preferences_bar_height'
        );
        if ($bike_references_fields) {
            foreach ($bike_references_fields as $bike_references_field) {
                if (isset($_REQUEST[$bike_references_field])) {
                    update_user_meta($user->ID, $bike_references_field, $_REQUEST[$bike_references_field]);
                }
            }
            as_schedule_single_action(time(), 'tt_cron_syn_usermeta_ns', array( $user->ID, '[Bike & Gear Update]' ));
        }
        $res['status'] = true;
        $res['message'] = "Your information has been updated successfully!";
    }
    echo json_encode($res);
    exit;
}

/**
 * Generate the options HTML for the Select/Options in the occupancy popup.
 *
 * @param int|string $key The selected option value.
 *
 * @return string The options HTML.
 */
function trek_occupants_options( $key ) {
    $trek_user_checkout_data    = get_trek_user_checkout_data();
    $trek_user_checkout_posted  = $trek_user_checkout_data['posted'];
    $occupants_opt              = '';
    $primary_name               = $trek_user_checkout_posted['shipping_first_name'] . ' ' . $trek_user_checkout_posted['shipping_last_name'];
    $occupants_opt             .= '<option value="none" ' . ( $key == 'none' ? 'selected' : '') . '>Please select</option>';
    $occupants_opt             .= '<option value="0" ' . ( $key == '0' ? 'selected' : '' ) . '>' . esc_html( $primary_name ) . '</option>';
    if( isset( $trek_user_checkout_posted['guests'] ) && ! empty( $trek_user_checkout_posted['guests'] ) ) {
        foreach( $trek_user_checkout_posted['guests'] as $guest_id => $guest ) {
            $selected_attr  = $guest_id == $key ? 'selected' : '';
            $occupants_opt .= '<option value="' . $guest_id . '" ' . $selected_attr . '>' . esc_html( $guest['guest_fname'] ) . ' ' . esc_html( $guest['guest_lname'] ) . '</option>';
        }
    }
    return $occupants_opt;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Communication Preferences [ My account page ]
 **/
add_action('wp_ajax_update_communication_preferences_action', 'trek_update_communication_preferences_action_cb');
add_action('wp_ajax_nopriv_update_communication_preferences_action', 'trek_update_communication_preferences_action_cb');
function trek_update_communication_preferences_action_cb()
{
    $res = array(
        'status' => false,
        'message' => ''
    );
    if (!isset($_POST['edit_communication_preferences_nonce']) || !wp_verify_nonce($_POST['edit_communication_preferences_nonce'], 'edit_communication_preferences_action')) {
        $res['message'] = "Sorry, your nonce did not verify.";
    } else {
        $user = wp_get_current_user();
        $communication_preferences_fields = array(
            'custentity_addtotrektravelemaillist',
            'custentity_receivetripplanner',
            'custentity_contactmethod'
        );
        if ($communication_preferences_fields) {
            foreach ($communication_preferences_fields as $communication_preferences_field) {
                if (isset($_REQUEST[$communication_preferences_field]) && !empty($_REQUEST[$communication_preferences_field])) {
                    if( 'custentity_addtotrektravelemaillist' == $communication_preferences_field ) {
                        update_user_meta($user->ID, $communication_preferences_field, '1');
                    } elseif ( 'custentity_receivetripplanner' == $communication_preferences_field ) {
                        update_user_meta($user->ID, $communication_preferences_field, 'T');
                    } else {

                        update_user_meta($user->ID, $communication_preferences_field, $_REQUEST[$communication_preferences_field]);
                    }
                } else {
                    if( 'custentity_addtotrektravelemaillist' == $communication_preferences_field ) {
                        update_user_meta($user->ID, $communication_preferences_field, '2');
                    } elseif ( 'custentity_receivetripplanner' == $communication_preferences_field ) {
                        update_user_meta($user->ID, $communication_preferences_field, 'F');
                    } else {

                        update_user_meta($user->ID, $communication_preferences_field, '');
                    }
                }
            }
            as_schedule_single_action(time(), 'tt_cron_syn_usermeta_ns', array( $user->ID, '[Communication Preferences]' ));
        }
        $res['status'] = true;
        $res['message'] = "Your information has been updated successfully!";
    }
    echo json_encode($res);
    exit;
}
add_action('init', 'trek_remove_wp_hooks_cb');
function trek_remove_wp_hooks_cb()
{
    remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
}
add_action('woocommerce_checkout_create_order_line_item', 'add_product_custom_field_to_order_item_meta', 10, 4);
function add_product_custom_field_to_order_item_meta($item, $cart_item_key, $values, $order)
{
    if (array_key_exists('trek_user_checkout_data', $values)) {
        $item->add_meta_data('trek_user_checkout_data', $values['trek_user_checkout_data']);
    }
    if (array_key_exists('trek_user_formatted_checkout_data', $values)) {
        $item->add_meta_data('trek_user_formatted_checkout_data', $values['trek_user_formatted_checkout_data']);
    }
}
function trek_get_backto_pdp_link()
{
    $res = array('p_link' => '');
    $product_id = null;
    $accepted_p_ids = tt_get_line_items_product_ids();
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            $product_id = $cart_item['product_id'];
        }
    }
    if ($product_id) {
        $res['p_link'] = get_the_permalink($product_id);
        $product = wc_get_product($product_id);
        $parent_id = $product->get_parent_id();
        if ($parent_id) {
            $res['p_link'] = get_the_permalink($parent_id);
        }
    }
    return $res;
}
function trek_get_guest_trips($user_id = '', $is_upcoming = 1, $order_id = '',$is_log = false)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_bookings';
    $current_date = time();
    $userInfo = get_user_by('ID', $user_id);
    $wp_user_email = $userInfo->user_email;
    if ($is_upcoming == 1) {
        $sql = "SELECT DISTINCT gb.order_id,gb.product_id, gb.user_id, gb.trip_name, gb.trip_start_date, gb.trip_end_date from {$table_name} as gb WHERE gb.trip_start_date > '{$current_date}' AND  gb.trip_end_date > '{$current_date}' ";
    } else {
        $sql = "SELECT DISTINCT gb.order_id,gb.product_id, gb.user_id, gb.trip_name, gb.trip_start_date, gb.trip_end_date from {$table_name} as gb WHERE gb.trip_start_date < '{$current_date}' AND  gb.trip_end_date < '{$current_date}' ";
    }
    $sql .= " AND gb.trip_name != '' ";
    $sql .= " AND gb.is_guestreg_cancelled != '1' ";
    if ($wp_user_email || $user_id) {
        $sql .= " AND ( gb.guest_email_address = '{$wp_user_email}' OR gb.user_id = '{$user_id}' )";
    }
    $sql .= " ORDER BY gb.id DESC";
    if ($order_id) {
        $sql .= " AND gb.order_id = {$order_id}";
        $sql .= " ORDER BY gb.order_id DESC";
    }
    $results = $wpdb->get_results($sql, ARRAY_A);
    $res = array(
        'count' => count($results),
        'data' => $results
    );
    if( $is_log == true ){
        tt_add_error_log('[SQL] My Trips Dashboard', ['sql'=>$sql], $res);
    }
    return $res;
}
function trek_get_guest_trip_status($user_id, $order_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_bookings';
    $is_upcoming = false;
    $current_date = time();
    $res = [];
    $userInfo = get_user_by('ID', $user_id);
    $guest_email_address = $userInfo->user_email;
    $sql = "SELECT * from {$table_name} as gb WHERE gb.order_id = {$order_id} AND ( gb.user_id = '{$user_id}' OR gb.guest_email_address = '{$guest_email_address}' ) AND gb.trip_start_date > '{$current_date}' AND  gb.trip_end_date > '{$current_date}' ";
    $results = $wpdb->get_results($sql, ARRAY_A);
    if ($results && count($results) > 0) {
        $start_date = $results[0]['trip_start_date'];
        $end_date = $results[0]['trip_end_date'];
        $timeDiff_1 = abs($start_date - $current_date);
        $timeDiff_2 = abs($end_date - $current_date);
        $numberDays1 = intval($timeDiff_1 / 86400);
        $numberDays2 = intval($timeDiff_2 / 86400);
        $is_upcoming = true;
    }
    $res = [
        'days_1' => $numberDays1,
        'days_2' => $numberDays2,
        'is_upcoming' => $is_upcoming
    ];
    return $res;
}
function trek_get_guest_emails($order_id)
{
    $emails = [];
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_bookings';
    $sql = "SELECT gb.guest_email_address,gb.user_id from {$table_name} as gb WHERE gb.order_id = {$order_id} ";
    $results = $wpdb->get_results($sql, ARRAY_A);
    if( $results ){
        foreach($results as $result){
            $user_id = isset($result['user_id']) ? $result['user_id'] : '';
            $author_obj = get_user_by('id', $user_id);
            $emails[] = $author_obj->user_email;
            $emails[] = isset($result['guest_email_address']) ? $result['guest_email_address'] : '';
        }
    }
    $emails = array_unique($emails);
    $emails = array_filter($emails);
    if( $emails && is_array($emails) ){
        $emails = implode(', ', $emails);
    }
    return $emails;
}
function trek_get_user_order_info($user_id, $order_id)
{
    global $wpdb;
    $author_obj = get_user_by('id', $user_id);
    $ns_user_id = get_user_meta($user_id, 'ns_customer_internal_id', true);
    $table_name = $wpdb->prefix . 'guest_bookings';
    $sql = "SELECT * from {$table_name} as gb WHERE 1=1 ";
    // if( $ns_user_id ){
    //     $sql .= " AND gb.netsuite_guest_registration_id = {$ns_user_id}";
    // }
    if ($user_id) {
        $sql .= " AND ( gb.guest_email_address = '{$author_obj->user_email}' OR  gb.user_id = '{$user_id}' )";
    }
    if( $order_id ){
        $sql .= " AND gb.order_id = {$order_id} ";
    }
    if( $order_id || $ns_user_id ){
        $results = $wpdb->get_results($sql, ARRAY_A);
    }else{
        $results = [];
    }
    return $results;
}
function tt_checkbooking_status( $ns_user_id, $ns_order_id ) //old - $ns_user_id var in 1st args
{
    global $wpdb;
    $count = 0;
    $table_name = $wpdb->prefix . 'guest_bookings';
    $sql = "SELECT * from {$table_name} as gb WHERE 1=1";
    if( $ns_user_id ){
        $sql .= " AND gb.netsuite_guest_registration_id = {$ns_user_id} ";
    }
    // if ($user_email) {
    //     $sql .= " AND gb.guest_email_address = '{$user_email}'";
    // }
    if( $ns_order_id ){
        $sql .= " AND gb.ns_trip_booking_id = {$ns_order_id} ";
    }
    if( $ns_order_id && $ns_user_id ){
        $results = $wpdb->get_results($sql, ARRAY_A);
    }else{
        $results = [];
    }
    if ($results) {
        $count = count($results);
    }
    tt_add_error_log(
        'Check Booking Status', 
        ['ns_order_id' => $ns_order_id, 'ns_user_id' => $ns_user_id ],
        ['sql' => $sql, 'results' => $results, 'count' => $count]
    );
    return $count;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Communication Preferences [ My account page ]
 **/
add_action('wp_ajax_tt_load_more_blog_action', 'trek_tt_load_more_blog_action_cb');
add_action('wp_ajax_nopriv_tt_load_more_blog_action', 'trek_tt_load_more_blog_action_cb');
function trek_tt_load_more_blog_action_cb()
{

    $cat_id = $_POST['catid'];

    $other_blog_html = '';
    $blog_args = array(
        'post_type' => 'post',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'paged' => $_POST['paged'],
        'cat' => array( $cat_id )

    );
    $blogs = new WP_Query($blog_args);
    $max_pages = $blogs->max_num_pages;
    if ($blogs->have_posts()) {
        while ($blogs->have_posts()) {
            $blogs->the_post();
            $featured_Image = '/wp-content/themes/trek-travel-theme/assets/images/posts-thumbnail-placeholder.svg';
            if (has_post_thumbnail(get_the_ID())) {
                $featured_Image = get_the_post_thumbnail_url(get_the_ID(), 'full');
            }
            $other_blog_html .= '<div class="list-item">
                <div class="image">
                <img src="' . $featured_Image . '">				
                </div>
                <p class="fw-normal fs-sm lh-sm">' . get_the_date('F Y') . '</p>
                <p class="fw-bold fs-xl lh-xl">' . get_the_title() . '</p>
                <p class="fw-normal fs-md lh-md">' . get_the_excerpt() . '</p>	
                <a href="' . get_the_permalink() . '" class="btn btn-secondary btn-sm btn-outline-dark rounded-1">Learn more</a>		
            </div>';
        }
        wp_reset_postdata();
    }
    $result = [
        'max' => $max_pages,
        'html' => $other_blog_html,
    ];
    echo json_encode($result);
    exit;
}

/**
 * tt_get_product_image
 * 
 * Most likely we do not need this function and it's a leftover.
 * It loops all published products for no apparent reason and it's making a HUGE call
 *
 * @deprecated We do not need this function as it does not make any sense
 * @return void
 */
function tt_get_product_image()
{
    $args = ['post_type' => 'product', 'posts_per_page' => -1, 'post_status' => 'publish'];
    $products = new WP_Query($args);
    $product_info = [];
    if ($products->have_posts()) {
        // while($products->have_posts()){
        //     $products->the_post();
        //     $p_id = get_the_ID();
        //     $product_info[$p_id] = array(
        //         'image' => get_the_post_thumbnail_url($p_id, 'full'),
        //         'id' => $p_id
        //     );
        // }
        return $product_info;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for adding compare product Ids in @Session
 **/
add_action('wp_ajax_add_compare_product_ids_action', 'trek_add_compare_product_ids_action_cb');
add_action('wp_ajax_nopriv_add_compare_product_ids_action', 'trek_add_compare_product_ids_action_cb');
function trek_add_compare_product_ids_action_cb()
{
    $compared_Ids = [];
    if (isset($_REQUEST['product_ids']) && !empty($_REQUEST['product_ids'])) {
        $compared_Ids = array_unique($_REQUEST['product_ids']);
        setcookie('wc_products_compare_products', implode(',', $compared_Ids), strtotime('+10 day'));
    }
    $output = tt_compare_trips_html($compared_Ids, false);
    // $output = '';
    // if (isset($_REQUEST['product_ids']) && !empty($_REQUEST['product_ids'])) {
    //     setcookie('wc_products_compare_products', array_unique($_REQUEST['product_ids']), strtotime('+1 day'));
    //     $compared_Ids = array_unique($_REQUEST['product_ids']);
    //     $args = array(
    //         'post_type' => 'product',
    //         'post__in' => $compared_Ids,
    //         'post_status' => 'publish'
    //     );
    //     $compared_Trips = new WP_Query($args);
    //     if( $compared_Trips->have_posts() ){
    //         while($compared_Trips->have_posts()){
    //             $compared_Trips->the_post();
    //             $trip_id = get_the_ID();
    //             $trip_image = get_the_post_thumbnail_url($trip_id);
    //             $output .= '<div class="compare-product" id="product-'.$trip_id.'"><img src="'.$trip_image.'" class="" alt="Placeholder" /><i class="bi bi-x-lg"></i></div>';
    //         }
    //     }
    // }
    echo json_encode(
        array(
            'status' => true,
            //'tt_compare_products' => $_COOKIE['wc_products_compare_products'],
            'output' => $output
        )
    );
    exit;
}
function get_bearer_token_insurance()
{
    $token_args = array(
        'userName' => urlencode(TREK_INSURANCE_UNAME),
        'password' => urlencode(TREK_INSURANCE_PASS)
    );
    $api_url = add_query_arg($token_args, TREK_INRURANCE_API_URL . '/Token/');
    $token = '';
    $res['token'] = '';
    $res['status'] = false;
    $res['message'] = 'error';
    try {
        $response = wp_remote_get($api_url);
        if (is_array($response) && !is_wp_error($response)) {
            $body_decode = json_decode($response['body']);
            $token = $body_decode->token->access_token;
            $token = 'Bearer ' . $token;
            $res['status'] = true;
            $res['message'] = 'success';
            $res['token'] = $token;
        }
    } catch (Exception $e) {
        $res['status'] = true;
        $res['message'] = $e->getMessage();
    }
    $res['result'] = $response;
    //tt_add_error_log('[Token] - Arch insurance', $token_args, $res);
    return $res;
}
function tt_set_calculate_insurance_fees_api($trek_insurance_args)
{
    $res['message'] = 'error';
    $res['status'] = false;
    $res['basePremium'] = 0;
    $trek_insurance_result = array();
    $bearer_token = get_bearer_token_insurance();
    if ($bearer_token && isset($bearer_token['token']) && isset($bearer_token['status']) && $bearer_token['status'] == true) {
        $api_args = array(
            'headers' => array(
                'Authorization' => $bearer_token['token'],
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($trek_insurance_args)
        );
        try {
            $trek_insurance_result = wp_remote_post(
                TREK_INRURANCE_API_URL . '/Quote/GetQuotes',
                $api_args
            );
            $res['message'] =  $trek_insurance_result;
        } catch (Exception $e) {
            $res['message'] =  $e->getMessage();
        }
    }
    $basePremium = 0;
    if (is_array($trek_insurance_result) && !empty($trek_insurance_result) && !is_wp_error($trek_insurance_result)) {
        $trek_insurance_result_decode = json_decode($trek_insurance_result['body']);
        $is_success = $trek_insurance_result_decode->success;
        if ($is_success == true && $trek_insurance_result_decode->quotes) {
            $basePremium = $trek_insurance_result_decode->quotes[0]->basePremium;
            if ($basePremium && $basePremium > 0) {
                $res['status'] = true;
            }
        }
        $res['responseCode'] = isset( $trek_insurance_result_decode->responseCode ) ? $trek_insurance_result_decode->responseCode : '';
    }
    $res['basePremium']  = $basePremium;
    $res_mess_arr        = json_decode( isset( $res['message']['body'] ) ? $res['message']['body'] : '', true );
    if( isset( $res_mess_arr['quotes'] ) ) {
        unset( $res_mess_arr['quotes'] );
    }
    tt_add_error_log( '[Arch insurance]', $trek_insurance_args, array( 'message->body' => $res_mess_arr ) );
    return $res;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get OptionValue/ItemName of Script method data using OptionID
 **/
if (!function_exists('tt_get_custom_item_name')) {
    function tt_get_custom_item_name($scriptMethod = "", $optionId = 'none')
    {
        $itemInfo = [];
        if ($scriptMethod) {
            $option_key = TT_OPTION_PREFIX . $scriptMethod;
            $option_value = get_option($option_key);
            $item_data = json_decode($option_value);
            $itemInfo = json_decode(json_encode($item_data), true);
            if ($itemInfo && $optionId != 'none') {
                if ($optionId == '') {
                    return '-';
                }
                if ($itemInfo && $itemInfo['options'] && is_array($itemInfo['options'])) {
                    $option_index = array_search($optionId, array_column($itemInfo['options'], 'optionId'));
                    $itemName = $itemInfo['options'][$option_index]['optionValue'];
                    return $itemName;
                }
            }
        }
        return $itemInfo;
    }
}
function tt_items_select_options($item_name = "", $optionId="")
{
    $opts = '';
    if ($item_name) {
        $itemData = tt_get_custom_item_name($item_name);
        $listName = isset($itemData['listName']) ? $itemData['listName'] : '';
        $options = isset($itemData['options']) ? $itemData['options'] : array();

        // Build a custom array with options for the Transportation Options dropdown, when selecting bring own bike.
        if( 'syncTransportationOptions' === $item_name ) {
            $listName = 'Transportation Option';
            $options  = array(
                array(
                    'optionId' => 'hard case',
                    'optionValue' => 'Hard Case'
                ),
                array(
                    'optionId' => 'soft case',
                    'optionValue' => 'Soft Case'
                ),
                array(
                    'optionId' => 'shipping',
                    'optionValue' => 'Shipping'
                ),
                array(
                    'optionId' => 'i am driving',
                    'optionValue' => "I'm driving"
                ),
            );
        }

        if ($options) {
            $opts .= '<option value="" data-value="' . $optionId . '">Select ' . $listName . '</option>';

            /**
             * Reorder options order if item name is "syncPedals".
             *
             * 1 - Bringing own
             * 2 - Cages
             * 3 - Flat
             * 9 - I don't know
             * 4 - Non-Rider
             * 7 - Shimano Mountain SPD Pedals
             * 6 - Shimano Road SPD SL Pedals
             */
            if ('syncPedals' === $item_name) {
                // Find the "I don't know" option.
                $donKnowOption = null;
                foreach ($options as $option) {
                    if ($option['optionId'] == 9) {
                        $donKnowOption = $option;
                        break;
                    }
                }

                // Remove the "I don't know" option from options list.
                if ( $donKnowOption ) {
                    $index = array_search( $donKnowOption, $options );
                    unset( $options[$index] );
                }

                // Sort the options alphabetically in descending order.
                $compare_by_option_value = function ( $a, $b ) {
                    return strcmp( $b["optionValue"], $a["optionValue"] );
                };

                usort( $options, $compare_by_option_value );
            }

            /**
             * Reorder options order if item name is "syncHelmets".
             *
             * 1 - Bringing own
             * 4 - Kid's
             * 6 - Large (58-63cm)
             * 2 - Medium (54-60cm)
             * 5 - Non-Rider
             * 3 - Small (51-57cm)
             * 7 - X-Large (60-66cm)
             */
            if ( 'syncHelmets' === $item_name ) {
                // Find the "Kid's" option.
                $kids_option = null;
                foreach ( $options as $option ) {
                    if ( 4 == $option['optionId'] ) {
                        $kids_option = $option;
                        break;
                    }
                }

                // Find the "Non-Rider" option.
                $non_rider_option = null;
                foreach ( $options as $option ) {
                    if ( 5 == $option['optionId'] ) {
                        $non_rider_option = $option;
                        break;
                    }
                }

                // Remove the "Kid's" option from options list.
                if( $kids_option ) {
                    $index = array_search( $kids_option, $options );
                    unset( $options[$index] );
                }

                if( $non_rider_option ) {
                    $index = array_search( $non_rider_option, $options );

                    // Remove the "Non-Rider" option from options list.
                    unset( $options[$index] );

                    // Add the "Non-Rider" option before last option.
                    array_splice( $options, count( $options ) - 1, 0, array( $non_rider_option ) );
                }
            }

            // Sort options ASC by value in optionValue key if item name is "syncHeights" - only for Rider Height is available this.
            if( 'syncHeights' === $item_name ){
                // Regex to match ft and inches.
                $re = '/.*(\d)\'(\d+).*/m';
                // ASC sort function.
                $sortFunc = function($a, $b) use ($re) {
                    if (stripos($b['optionValue'], 'Under') !== false) {
                        return 1;
                    } elseif (stripos($a['optionValue'], 'Under') !== false) {
                        return -1;
                    } else if (preg_match($re, $a['optionValue'], $matchesA) && preg_match($re, $b['optionValue'], $matchesB)) {
                        $aHeight = (int)$matchesA[1] * 12 + (int)$matchesA[2];
                        $bHeight = (int)$matchesB[1] * 12 + (int)$matchesB[2];

                        if ($aHeight == $bHeight) {
                            return strcmp($a['optionValue'], $b['optionValue']);
                        }

                        return $aHeight - $bHeight;
                    }

                    return 0;
                };
                // Sort options with custom calback function for sorting.
                usort($options, $sortFunc);
            }

            if ( 'syncJerseySizes' === $item_name ) {
                foreach ( $options as $option ) {
                    if ( 20 == $option['optionId'] || 47 == $option['optionId'] || 48 == $option['optionId' ] ) {
                        continue;
                    } else {
                        $selected = ( $optionId == $option['optionId'] ? 'selected' : '' );
                        $opts .= '<option value="' . $option['optionId'] . '" ' . $selected . '>' . $option['optionValue'] . '</option>';
                    }
                }
            } elseif ( 'syncHelmets' === $item_name ) {
                foreach ( $options as $option ) {
                    if ( 5 == $option['optionId'] ) {
                        continue;
                    } else {
                        $selected = ( $optionId == $option['optionId'] ? 'selected' : '' );
                        $opts .= '<option value="' . $option['optionId'] . '" ' . $selected . '>' . $option['optionValue'] . '</option>';
                    }
                }
            } else {
                foreach ( $options as $option ) {
                    $selected = ( $optionId == $option['optionId'] ? 'selected' : '' );
                    $opts .= '<option value="' . $option['optionId'] . '" ' . $selected . '>' . $option['optionValue'] . '</option>';
                }
            }
        }
    }
    return $opts;
}

/**
 * Check if is coupon applied.
 *
 * @param string $coupon_code The name of the coupon code.
 *
 * @return bool
 */
function tt_is_coupon_applied( $coupon_code = '' ) {
    if( empty( $coupon_code ) ) {
        return false;
    }

    foreach ( WC()->cart->get_applied_coupons() as $applied_coupon ) {
        if ( strcasecmp( $applied_coupon, $coupon_code ) === 0 ) {
            return true;
        }
    }

    // Coupon not found in the applied coupons list.
    return false;
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for adding compare product Ids in @Session
 **/
add_action( 'wp_ajax_tt_apply_remove_coupan_action', 'trek_tt_apply_remove_coupan_action_cb' );
add_action( 'wp_ajax_nopriv_tt_apply_remove_coupan_action', 'trek_tt_apply_remove_coupan_action_cb' );
function trek_tt_apply_remove_coupan_action_cb()
{
    $res['status']  = false;
    $res['message'] = '';
    $res['html']    = '';
    $coupon_code    = $_REQUEST['coupon_code'];
    $type           = $_REQUEST['type'];
    $resHtml        = '';
    $is_applied     = false;
    if ( $coupon_code && $type ) {
        if ( $type == 'add' ) {
            if ( ! WC()->cart->has_discount( $coupon_code ) ) {
                $coupon      = new WC_Coupon( $coupon_code );
                $coupon_post = get_post( $coupon->id );
                if ( $coupon_post ) {
                    WC()->cart->apply_coupon( $coupon_code );
                    if ( ! WC()->cart->has_discount( $coupon_code ) ) {
                        $is_applied = false;
                    } else {
                        $is_applied = true;
                    }
                }
            }
        } else {
            WC()->cart->remove_coupon( $coupon_code );
            $is_applied = false;
        }
        WC()->cart->calculate_totals();
    }
    $res['is_applied'] = $is_applied;
    // Begin: Save coupan code Data in Cart Session.
    $accepted_p_ids = tt_get_line_items_product_ids();
    // Get the current cart contents.
    $cart = WC()->cart->get_cart_contents();

    foreach ( $cart as $cart_item_id => $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
            if ( $is_applied == true ) {
                $cart_item['trek_user_checkout_data']['coupon_code'] = $coupon_code;
            } else {
                $cart_item['trek_user_checkout_data']['coupon_code'] = '';
            }
            $cart[$cart_item_id] = $cart_item;
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();
    // End: Save coupan code Data in Cart Session.

    $review_order = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if (is_readable($review_order)) {
        $resHtml .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $resHtml .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $payment_option_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php';
    if (is_readable($review_order)) {
        $payment_option_html .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php');
    } else {
        $payment_option_html .= '<h3>Step 4</h3><p>Checkout payment option file is missing!</p>';
    }
    if ($is_applied == true) {
        $res['status'] = true;
    } else {
        $res['status'] = false;
        $res['message'] = wc_print_notices(true);
    }
    $res['html'] = $resHtml;
    $res['payment_option'] = $payment_option_html;
    echo json_encode($res);
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Create Trek common log info Table
 **/
if (!function_exists('tt_create_error_log_table')) {
    function tt_create_error_log_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tt_common_error_logs';
        $charset_collate = '';
        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }
        if ($wpdb->get_var($wpdb->prepare('show tables like %s', $table_name)) != $table_name) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $sql = 'CREATE TABLE `' . $table_name . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(100) NULL,
                `args` text NULL,
                `response` text NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : TT Add Trek log info
 **/
function tt_add_error_log($type, $args, $response)
{
    tt_create_error_log_table();
    global $wpdb;
    $table_name = $wpdb->prefix . 'tt_common_error_logs';
    $args = ($args ? $args : array());
    $response = ($response ? $response : array());
    $args = [
        'type' => $type,
        'args' => json_encode($args),
        'response' => json_encode($response)
    ];
    if ($args && is_array($args)) {
        $inserted_id = $wpdb->insert($table_name, $args);
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Calculate cart Fees & Set tt_guest_insurance in session
 **/
function tt_woocommerce_cart_calculate_fees_cb( $cart ) {
    
    $trek_user_checkout_data = get_trek_user_checkout_data();
    $tt_posted               = isset( $trek_user_checkout_data['posted'] ) ? $trek_user_checkout_data['posted'] : [];
    $arch_info               = tt_get_insurance_info( $tt_posted );
    $insuredPerson           = isset( $arch_info['count'] ) ? $arch_info['count'] : 0;
    $insurance_amount        = isset( $arch_info['amount'] ) ? $arch_info['amount'] : 0;
    // Get the current cart contents.
    $cartobj                 = WC()->cart->get_cart_contents();
    $occupants_private       = isset( $tt_posted['occupants']['private'] ) ? $tt_posted['occupants']['private'] : array();
    $occupants_roommate      = isset( $tt_posted['occupants']['roommate'] ) ? $tt_posted['occupants']['roommate'] : array();
    $suppliment_counts       = intval( count( $occupants_private ) ) + intval( count( $occupants_roommate ) );
    foreach ( $cartobj as $cartobj_item_id => $cartobj_item ) {
        $_product = apply_filters('woocommerce_cart_item_product', $cartobj_item['data'], $cartobj_item, $cartobj_item_id );
        $sku      = $_product->get_sku();
        if ( isset( $cartobj_item['product_id'] ) ) {

            if ( 'TTWP23FEES' === $sku ) {
                if ( $insuredPerson > 0 && $insurance_amount > 0  ) {
                    $cartobj[$cartobj_item_id]['quantity'] = 1;
                    $cartobj_item['data']->set_price( $insurance_amount );
                } else {
                    $cartobj[$cartobj_item_id]['quantity'] = 0;
                    $cartobj_item['data']->set_price(0);
                }
            }

            if ( 'TTWP23SUPP' === $sku || 'TTWP23UPGRADES' === $sku ) {
                if ( isset( $cartobj_item['tt_cart_custom_fees_price'] ) && $cartobj_item['tt_cart_custom_fees_price'] > 0 ) {
                    $cartobj_item['data']->set_price( $cartobj_item['tt_cart_custom_fees_price'] );
                }
            }

            if ( 'TTWP23SUPP' === $sku ) {
                $cartobj[$cartobj_item_id]['quantity'] = $suppliment_counts;
            }
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cartobj );
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();
}
add_action( 'woocommerce_cart_calculate_fees', 'tt_woocommerce_cart_calculate_fees_cb' );

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get booking details of guests by order_id
 **/
if (!function_exists('tt_get_booking_details')) {
    function tt_get_booking_details($order_id = NULL, $booking_status = true)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'guest_bookings';
        $sql = "SELECT * from {$table_name}";
        $has_and = false;
        if ($order_id) {
            $sql .= " WHERE order_id={$order_id}";
            $has_and = true;
        }
        if ($booking_status == true) {
            if ($has_and == true) {
                $sql .= " AND ns_booking_status != 1 ";
            } else {
                $sql .= " WHERE ns_booking_status != 1 ";
            }
        }
        $results = $wpdb->get_results($sql);
        return $results;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get booking guests index by order_id
 **/
if (!function_exists('tt_get_booking_guests_indexes')) {
    function tt_get_booking_guests_indexes($order_id = NULL)
    {
        global $wpdb;
        $guest_index_ids = [];
        $table_name = $wpdb->prefix . 'guest_bookings';
        $sql = "SELECT guest_index_id from {$table_name} WHERE order_id={$order_id}";
        $results = $wpdb->get_results($sql);
        if ($results) {
            $guest_index_ids = array_column($results, 'guest_index_id');
        }
        return $guest_index_ids;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get Field from booking by order_id
 **/
if (!function_exists('tt_get_booking_field')) {
    function tt_get_booking_field($where_field = "order_id", $where_field_value = NULL, $fields_name = "guest_index_id", $is_json = false)
    {
        global $wpdb;
        $fields_val = '';
        if ($is_json == true) {
            $fields_val = '{}';
        }
        $table_name = $wpdb->prefix . 'guest_bookings';
        $sql = "SELECT {$fields_name} from {$table_name} WHERE {$where_field}={$where_field_value}";
        $results = $wpdb->get_results($sql, ARRAY_A);
        if ($results) {
            $fields_val = $results[0][$fields_name];
        }
        $resultData = $fields_val;
        if ($is_json == true && $fields_val) {
            $resultData = json_decode($fields_val, true);
        }
        return $resultData;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Find User Room index function
 **/
if (!function_exists('tt_get_user_room_index_by_user_key')) {
    function tt_get_user_room_index_by_user_key( $array, $key ) {
        $room_index = null;
        if ( $array ) {
            foreach ( $array as $room_type => $user_ids ) {
                if ( ( 'double1Bed' === $room_type || 'double2Beds' === $room_type ) ) {
                    if ( isset( $user_ids ) && $user_ids ) {
                        foreach ( $user_ids as $room_id => $user_inner_id ) {
                            if ( in_array( $key, $user_inner_id ) ) {
                                $room_index = $room_id;
                            }
                        }
                    }
                } else {
                    if ( $user_ids ) {
                        foreach ( $user_ids as $room_id => $user_inner_ids ) {
                            if ($key == $user_inner_ids) {
                                $room_index = $room_id;
                            }
                        }
                    }
                }
            }
        }
        return $room_index;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get Field from booking by order_id
 **/
if (!function_exists('tt_create_booking_rooms_arr')) {
    function tt_create_booking_rooms_arr($users, $occupants)
    {
        $rooms = [];
        $rooms_new = [];
        $room_index = 0;
        if ($occupants) {
            foreach ($occupants as $room_type => $occupant) {
                if ($occupant && $room_type == 'single') {
                    $singleArray = array_chunk($occupant, 2);
                    foreach ($singleArray as $singleArr) {
                        $rooms_new['double1Bed'][$room_index] = $singleArr;
                        $rooms[$room_index]  = "double1Bed";
                        $room_index++;
                    }
                }
                if ($occupant && $room_type == 'double') {
                    $doubleArray = array_chunk($occupant, 2);
                    foreach ($doubleArray as $doubleArr) {
                        $rooms_new['double2Beds'][$room_index] = $doubleArr;
                        $rooms[$room_index]  = "double2Beds";
                        $room_index++;
                    }
                }
                if ($occupant && $room_type == 'roommate') {
                    //$rooms_new['double2Beds'][$room_index] = $occupant;
                    foreach ($occupant as $roommateArr) {
                        $rooms[$room_index]  = "double2Beds";
                        $rooms_new['double2Beds_1'][$room_index] = $roommateArr;
                        $room_index++;
                    }
                }
                if ($occupant && $room_type == 'private') {
                    //$rooms_new['single'][$room_index] = $occupant;
                    foreach ($occupant as $privateArr) {
                        $rooms[$room_index]  = "single";
                        $rooms_new['single'][$room_index] = $privateArr;
                        $room_index++;
                    }
                }
            }
        }
        $roomsResults = [
            'rooms' => $rooms,
            'users_in_rooms' => $rooms_new
        ];
        return $roomsResults;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : TT Valid with isset/empty & set value
 **/
if (!function_exists('tt_validate')) {
    function tt_validate($var = '', $default = '')
    {
        return (isset($var) && $var ? $var : $default);
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Add/Update NS results of booking for Guests by order_id, email_address
 **/
if (!function_exists('tt_update_user_booking_info')) {
    function tt_update_user_booking_info( $order_id = NULL, $ns_booking_response="", $guests_email_addresses = [] )
    {
        tt_add_error_log('[Start] - Update Booking', ['order_id' => $order_id], ['dateTime' => date('Y-m-d H:i:s')]);
        global $wpdb;
        $table_name = $wpdb->prefix . 'guest_bookings';
        $where = ['order_id' => $order_id]; //'guest_email_address' => $email
        $ns_booking_status = 0;
        $bookingId = $isDraftBooking = $ConfirmEmail = '';
        $guests = [];
        $ns_booking_response = json_encode($ns_booking_response);
        $ns_booking_response_arr = json_decode($ns_booking_response, true);
        if ($ns_booking_response_arr && isset($ns_booking_response_arr['success']) && $ns_booking_response_arr['success'] == true) {
            $ns_booking_status = 1;
            do_action( 'tt_set_ns_booking_status', $order_id, 'booking_success' );
            if (isset($ns_booking_response_arr['savedData']) && !empty($ns_booking_response_arr['savedData'])) {
                $bookingId = isset($ns_booking_response_arr['savedData']['bookingId']) ? $ns_booking_response_arr['savedData']['bookingId'] : '';
                $isDraftBooking = isset($ns_booking_response_arr['savedData']['isDraftBooking']) ? $ns_booking_response_arr['savedData']['isDraftBooking'] : '';
                $ConfirmEmail = isset($ns_booking_response_arr['savedData']['shouldSendDraftConfirmEmail']) ? $ns_booking_response_arr['savedData']['shouldSendDraftConfirmEmail'] : '';
                $guests = isset($ns_booking_response_arr['savedData']['guests']) ? $ns_booking_response_arr['savedData']['guests'] : array();
            }
        } else {
            // Booking creation Failure. Init the trek email notification sysytem.
            do_action( 'netsuite_booking_failed', $order_id, $ns_booking_response_arr );
            do_action( 'tt_set_ns_booking_status', $order_id, 'booking_failed' );
        }
        if ($bookingId == '' || $bookingId == null) {
            $ns_booking_status = 0;
        }
        $bookingData = [
            'ns_trip_booking_id' => $bookingId,
            'isDraftBooking' => $isDraftBooking,
            'shouldSendDraftConfirmEmail' => $ConfirmEmail,
            'ns_booking_response' => json_encode($ns_booking_response_arr),
            'ns_booking_status' => $ns_booking_status,
            'modified_at' => date('Y-m-d H:i:s')
        ];
        update_post_meta($order_id, TT_WC_META_PREFIX.'guest_booking_id', $bookingId);

        // Take referral info from `wp_postmeta` table for post_id = order_id and meta_key = tt_meta_referral_info.
        $ns_referral_args = maybe_unserialize( get_post_meta( $order_id, TT_WC_META_PREFIX . 'referral_info', true ) );

        if( ! empty( $ns_referral_args ) ) {

            $ns_referral_args['bookingId'] = $bookingId;

            // Send to NS.
            $ns_referral_info_response = tt_send_referral_info_to_ns( $ns_referral_args );

            if( isset( $ns_referral_info_response->success ) ) {
                if( true == $ns_referral_info_response->success ) {

                    // If info successfully sent to the NS > delete stored in `wp_postmeta` meta value for referral source info.
                    delete_post_meta( $order_id, TT_WC_META_PREFIX . 'referral_info' );
                }
            }
        }
          
        if ($guests && is_array($guests) && !empty($guests)) {
            foreach ($guests as $guest_index => $guest) {
                $releaseFormId = isset($guests[$guest_index]['releaseFormId']) ? $guests[$guest_index]['releaseFormId'] : '';
                $bookingData['releaseFormId'] = $releaseFormId;
                if( $guest_index == 0 ){
                    update_post_meta($order_id, TT_WC_META_PREFIX.'releaseFormId', $releaseFormId);
                }
                $bookingData['guestRegistrationId']            = isset($guests[$guest_index]['guestRegistrationId']) ? $guests[$guest_index]['guestRegistrationId'] : '';
                $bookingData['netsuite_guest_registration_id'] = isset($guests[$guest_index]['guestId']) ? $guests[$guest_index]['guestId'] : '';
                $where['guest_index_id']                       = $guest_index;
                $wpdb->update($table_name, $bookingData, $where);
                if( $wpdb->last_error ) {
                    tt_add_error_log( '[Faild] Update Booking', array( 'order_id' => $order_id, 'bookingId' => $bookingId ), array( 'last_error' => $wpdb->last_error ) );
                }
                // Try to repair the missing NS User ID, during booking creation in NetSuite, as a response we receive the NS User IDs.
                if( ! empty( $guests_email_addresses ) ) {
                    // Check if user exists in WP.
                    $user = get_user_by( 'email', $guests_email_addresses[ $guest_index ] );
                    if( $user ) {
                        $wc_user_id = $user->ID;
                        $ns_user_id = get_user_meta( $wc_user_id, 'ns_customer_internal_id', true );

                        if( empty( $ns_user_id ) ) {
                            $ns_customer_internal_id = tt_validate( $guests[ $guest_index ][ 'guestId' ] );
                            // Update the NS User ID for WP User.
                            update_user_meta( $wc_user_id, 'ns_customer_internal_id', $ns_customer_internal_id );
                        }
                    }
                }
            }
        } else {
            $wpdb->update($table_name, $bookingData, $where);
            if( $wpdb->last_error ) {
                tt_add_error_log( '[Faild] Update Booking (No guests)', array( 'order_id' => $order_id, 'bookingId' => $bookingId ), array( 'last_error' => $wpdb->last_error ) );
            }
        }
        tt_add_error_log('[End] - Update Booking', ['order_id' => $order_id, 'bookingId' => $bookingId], ['dateTime' => date('Y-m-d H:i:s')]);
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get/check trip in local DB 
 **/
if (!function_exists('tt_get_trip_by_idCode')) {
    function tt_get_trip_by_idCode($table_name, $tripId = '', $tripCode = '')
    {
        global $wpdb;
        $count = 0;
        if (!empty($tripCode) && !empty($tripId)) {
            $sql = "SELECT ts.id from {$table_name} as ts WHERE ts.tripId = '{$tripId}' AND ts.tripCode = '{$tripCode}' ";
            $results = $wpdb->get_results($sql, ARRAY_A);
            $count = count($results);
        }
        return $count;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get/check trip in local DB 
 **/
if (!function_exists('tt_get_field_by_ID')) {
    function tt_get_field_by_ID($table_name, $compareKey = 'bikeId', $compareVal = '', $tripId = '')
    {
        global $wpdb;
        $count = 0;
        if (!empty($compareKey) && !empty($compareVal) && !empty($tripId)) {
            $sql = "SELECT ts.id from {$table_name} as ts WHERE ts.{$compareKey} = '{$compareVal}' AND ts.tripId = '{$tripId}' ";
            $results = $wpdb->get_results($sql, ARRAY_A);
            $count = count($results);
        }
        return $count;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get all trips IDs from local DB
 **/
if (!function_exists('tt_get_local_trip_ids')) {
    function tt_get_local_trip_ids($modified_Trips_Ids=[])
    {
        $trip_Ids = array();
        global $wpdb;
        $table_name = $wpdb->prefix . 'netsuite_trips';
        $sql = "SELECT DISTINCT tripId from {$table_name}";
        if( $modified_Trips_Ids && is_array($modified_Trips_Ids) ){
            $sql .=" WHERE tripId IN (".implode(', ', $modified_Trips_Ids).")";
        }
        $results = $wpdb->get_results($sql, ARRAY_A);
        if ($results) {
            $trip_Ids = array_column($results, 'tripId');
        }
        $trip_Ids = array_chunk($trip_Ids, 10);
        return $trip_Ids;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get all trips from local DB
 **/
if (!function_exists('tt_get_local_trips')) {
    function tt_get_local_trips( $tt_last_modified_trip_ids = [], $bulk = false )
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'netsuite_trips';
        $sql = "SELECT DISTINCT tripId, tripCode from {$table_name}";
        if( $tt_last_modified_trip_ids && is_array( $tt_last_modified_trip_ids ) ) {
            $sql .=" WHERE tripId IN (" . implode( ', ', $tt_last_modified_trip_ids ) . ")";
        }

        $results = $wpdb->get_results($sql);

        if( true === $bulk ) {
            $results = array_chunk($results, 10);
        }

        return $results;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Set custom attribute value in WC
 **/
if (!function_exists('tt_set_wc_attribute_value')) {
    function tt_set_wc_attribute_value($product_id, $attribute_name, $attribute_value)
    {
        $product = wc_get_product($product_id);
        if ($product) {
            wp_set_object_terms($product_id, $attribute_value, $attribute_name, false);
            $data = array(
                $attribute_name => array(
                    'name' => $attribute_name,
                    'value' => $attribute_value,
                    'is_visible' => 1,
                    'is_variation' => 0,
                    'is_taxonomy' => 1
                )
            );
            $_product_attributes = get_post_meta($product_id, '_product_attributes', TRUE);
            if ($_product_attributes && is_array($_product_attributes)) {
                update_post_meta($product_id, '_product_attributes', array_merge($_product_attributes, $data));
            } else {
                update_post_meta($product_id, '_product_attributes', $data);
            }
        }
    }
}

if ( ! function_exists( 'tt_set_custom_product_tax_value' ) ) {
    /**
     * Set custom taxonomy value for product.
     *
     * @param int|string $product_id The product ID.
     * @param string     $tax_name   The type of taxonomy.
     * @param string     $tax_value  The tax value.
     *
     * @return array|bool Term taxonomy IDs of the affected terms or false if there is no product or has error.
     **/
    function tt_set_custom_product_tax_value( $product_id = 0, $tax_name = '', $tax_value = '' ) {
        if( empty( $product_id ) || empty( $tax_name ) || empty( $tax_value ) ) {
            // Missing some essentials arguments.
            return false;
        }

        $product = wc_get_product( $product_id );

        // If is product.
        if ( $product ) {
            // Keep only one value of custom taxonomy.
            $result = wp_set_object_terms( $product_id, $tax_value, $tax_name, false );

            if( is_wp_error( $result ) ) {
                // (WP_Error) The WordPress Error object on invalid taxonomy (‘invalid_taxonomy’).
                error_log( wp_json_encode( $result ) );
                return false;
            }
            return $result;
        }

        return false;
    }
}

if ( ! function_exists( 'tt_get_custom_product_tax_value' ) ) {
    /**
     * Get custom product taxonomy value.
     *
     * @param int|string $product_id  The product ID.
     * @param string     $tax_name    The type of taxonomy.
     * @param bool       $as_string   Whether to return terms as single string splitted by comma.
     * @param bool       $single_term Whether to return a single term or all product terms.
     *
     * @return WP_Term|bool The term object or false if there is no product.
     */
    function tt_get_custom_product_tax_value( $product_id = 0, $tax_name = '', $as_string = false , $single_term = false ) {
        if( empty( $product_id ) || empty( $tax_name ) ) {
            // Missing some essentials arguments.
            return false;
        }

        $product = wc_get_product( $product_id );

        // If is product.
        if ( $product ) {

            $terms = wp_get_object_terms( $product_id, $tax_name );
            if ( ! empty( $terms )  && ! is_wp_error( $terms ) ) {
                if( $single_term ) {
                    if( $as_string ) {
                        return $terms[0]->name;
                    }
                    return $terms[0];
                }
                if( $as_string ) {
                    $terms_names = [];
                    foreach( $terms as $term ) {
                        $terms_names[] = $term->name;
                    }

                    return implode( ', ', $terms_names );
                }
                return $terms;
            }
        }

        return false;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get all trip details from local DB
 **/
if (!function_exists('tt_get_local_trips_detail')) {
    function tt_get_local_trips_detail($field = 'tripId', $tripId = '', $tripCode = '', $return = false)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'netsuite_trip_detail';

        if( !empty( $tripCode ) ) {
            // Get a trip code without suffix.
            $tripCode = tt_get_local_trip_code( $tripCode );
        }

        $sql = "SELECT ts.{$field} from {$table_name} as ts WHERE ts.tripCode = '{$tripCode}' ";
        if ($tripId) {
            $sql .= " AND ts.tripId = '{$tripId}'";
        }
        $results = $wpdb->get_results($sql);
        if ($return == true) {
            return (isset($results[0]->$field) ? $results[0]->$field : '');
        } else {
            return $results;
        }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get all trip details from local DB
 **/
if (!function_exists('tt_get_line_items_product_ids')) {
    function tt_get_line_items_product_ids()
    {

        // @TODO: temp soluton for storring the logic in a transient, as it's huge call
        $ids = get_transient( 'tt_line_item_fees_product' );

        if ( ! empty( $ids ) ) {
            return $ids;
        } else {
            global $wpdb;
            $sql = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='tt_line_item_fees_product' AND meta_value = '1' ";
            $results = $wpdb->get_results($sql, ARRAY_A);
            if ( ! empty ( $results ) ) {
                $ids = array_column($results, 'post_id');
                set_transient( 'tt_line_item_fees_product', $ids, DAY_IN_SECONDS );
            } else {
                /*
                * adding a check for this, as tt_get_line_items_product_ids() is used a lot
                * and it is working with in_array() and a few similar functions so it
                * is thorwing a fatal
                *
                * @TODO: find a long-term solition
                */
                return array();
            }

            return $ids;
        }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get Product ID by SKU
 **/
if (!function_exists('tt_get_product_by_sku')) {
    function tt_get_product_by_sku($sku, $p_id = false)
    {
        $result = null;
        global $wpdb;
        $postData = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku));
        if ($postData) {
            if ($p_id == false) {
                $result =  new WC_Product($postData);
            } else {
                $result = $postData;
            }
        }
        return $result;
    }
}
if (!function_exists('tt_create_line_item_product')) {
    function tt_create_line_item_product($sku)
    {
        $product_id = tt_get_product_by_sku($sku, true);
        $product_name = TT_LINE_ITEMS_PRODUCTS[$sku]['name'];
        $product_price = TT_LINE_ITEMS_PRODUCTS[$sku]['price'];
        if ($product_id == null && $sku) {
            if ($product_name) {
                $inserted_id = wp_insert_post(array(
                    'post_title' => $product_name,
                    'post_type' => 'product',
                    'post_status' => 'publish'
                ));
                if ($inserted_id) {
                    $product_id = $inserted_id;
                }
            }
        }
        update_post_meta($product_id, '_sku', $sku);
        update_post_meta($product_id, '_regular_price', (float)$product_price);
        update_post_meta($product_id, '_price', (float)$product_price);
        update_post_meta($product_id, '_sold_individually', 'no');
        update_post_meta($product_id, 'tt_line_item_fees_product', true);
        return $product_id;
    }
}

/**
 * Modify the cart_contents before calculate totals.
 *
 * ! This function makes very similar things with tt_woocommerce_cart_calculate_fees_cb(),
 * so think about how to combine them or find what is the difference.
 *
 * @param WC_Cart $cart_object The WC_Cart instance.
 */
function tt_woocommerce_before_calculate_totals_cb( $cart_object ) {
    $accepted_p_ids     = tt_get_line_items_product_ids();
    $bike_upgrade_qty   = 0;
    $tt_checkoutData    = get_trek_user_checkout_data();
    $tt_posted          = isset( $tt_checkoutData['posted'] ) ? $tt_checkoutData['posted'] : array();
    $arch_info          = tt_get_insurance_info( $tt_posted );
    $insuredPerson      = isset( $arch_info['count'] ) ? $arch_info['count'] : 0;
    $insurance_amount   = isset( $arch_info['amount'] ) ? $arch_info['amount'] : 0;
    $occupants_private  = isset( $tt_posted['occupants']['private'] ) ? $tt_posted['occupants']['private'] : array();
    $occupants_roommate = isset( $tt_posted['occupants']['roommate'] ) ? $tt_posted['occupants']['roommate'] : array();
    $suppliment_counts  = intval( count( $occupants_private ) ) + intval( count( $occupants_roommate ) );
    if ( isset( $tt_posted['bike_gears'] ) && $tt_posted['bike_gears'] ) {
        foreach ( $tt_posted['bike_gears'] as $bike_gear_type => $bike_gear ) {
            if ( 'primary' === $bike_gear_type ) {
                $bike_type_id = isset( $bike_gear['bikeTypeId'] ) ? $bike_gear['bikeTypeId'] : '';
                if ( $bike_type_id ) {
                    $bikeTypeInfo = tt_ns_get_bike_type_info( $bike_type_id );
                    if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
                        $bike_upgrade_qty++;
                    }
                }
            } else {
                if ( $bike_gear ) {
                    foreach ( $bike_gear as $guestData ) {
                        $bike_type_id = isset( $guestData['bikeTypeId'] ) ? $guestData['bikeTypeId'] : '';
                        if ( $bike_type_id ) {
                            $bikeTypeInfo = tt_ns_get_bike_type_info( $bike_type_id );
                            if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
                                $bike_upgrade_qty++;
                            }
                        }
                    }
                }
            }
        }
    }
    // Get the current cart contents.
    $cart_contents = WC()->cart->get_cart_contents();
    foreach ( $cart_contents as $item_key => $item ) {
        if ( isset( $item['product_id'] ) && in_array( $item['product_id'], $accepted_p_ids ) ) {
            $product = wc_get_product( $item['product_id'] );
            if ( $product ) {
                $sku = $product->get_sku();
                if ( 'TTWP23FEES' === $sku ) {
                    if ( $insuredPerson > 0 && $insurance_amount > 0  ) {
                        $cart_contents[$item_key]['quantity'] = 1;
                        $item['data']->set_price( $insurance_amount );
                    } else {
                        $cart_contents[$item_key]['quantity'] = 0;
                        $item['data']->set_price(0);
                    }
                }
                if ( 'TTWP23SUPP' === $sku || 'TTWP23UPGRADES' === $sku ) {
                    if ( isset( $item['tt_cart_custom_fees_price'] ) && $item['tt_cart_custom_fees_price'] > 0 ) {
                        $item['data']->set_price( $item['tt_cart_custom_fees_price'] );
                    }
                    if ( 'TTWP23SUPP' === $sku && $suppliment_counts > 0 ) {
                        $cart_contents[$item_key]['quantity'] = $suppliment_counts;
                    }
                    if ( 'TTWP23UPGRADES' === $sku && $bike_upgrade_qty > 0 ) {
                        $cart_contents[$item_key]['quantity'] = $bike_upgrade_qty;
                    }
                }
            }
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart_contents );
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();
}
add_action( 'woocommerce_before_calculate_totals', 'tt_woocommerce_before_calculate_totals_cb' );

/**
 * Save occupants AJAX callback.
 */
function trek_tt_save_occupants_ajax_action_cb() {
    $accepted_p_ids        = tt_get_line_items_product_ids();
    $s_product_id          = tt_create_line_item_product( 'TTWP23SUPP' );
    $occupants_private     = isset( $_REQUEST['occupants']['private'] ) ? $_REQUEST['occupants']['private'] : array();
    $occupants_roommate    = isset( $_REQUEST['occupants']['roommate'] ) ? $_REQUEST['occupants']['roommate'] : array();
    $suppliment_counts     = count( $occupants_private ) + count( $occupants_roommate );
    $trip_sku              = tt_get_trip_pid_sku_from_cart();
    $singleSupplementPrice = get_post_meta( $trip_sku['product_id'], TT_WC_META_PREFIX . 'singleSupplementPrice', true );
    if ( $singleSupplementPrice && $singleSupplementPrice > 0 ) {
        WC()->cart->add_to_cart( $s_product_id, $suppliment_counts, 0, array(), array( 'tt_cart_custom_fees_price' => $singleSupplementPrice ) );
    }
    // Get the current cart contents.
    $cart                  = WC()->cart->get_cart_contents();
    foreach ( $cart as $cart_item_id => $cart_item ) {
        $_product      = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_id);
        $_product_name = $_product->get_name();
        $product_id    = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
        $sku           = $_product->get_sku();
        if ( isset( $cart_item['product_id'] ) && $cart_item['product_id'] == $s_product_id ) {
            $cart[$cart_item_id] = $cart_item;
            if ( 'TTWP23SUPP' === $sku ) {
                if ( isset( $cart_item['tt_cart_custom_fees_price'] ) && $cart_item['tt_cart_custom_fees_price'] > 0 ) {
                    $cart_item['data']->set_price( $cart_item['tt_cart_custom_fees_price'] );
                    $singleSupplementPrice = $cart_item['tt_cart_custom_fees_price'];
                }
                $cart[$cart_item_id]['quantity'] = $suppliment_counts;
            }
        }
        if ( ! in_array( $product_id, $accepted_p_ids ) ) {
            $cart_posted_data     = $cart_item['trek_user_checkout_data'];
            $guest_req            = isset( $_REQUEST['guests'] ) ? $_REQUEST['guests'] : $cart_posted_data['guests'];
            $guest_req            = $guest_req && is_array( $guest_req ) ? $guest_req : array();
            $bikes_cart_item_data = array(
                'cart_item' => $_product_name,
                'quantity'  => count( $guest_req ) + 1
            );
            $guests_p_arr = array(
                "guest_fname"  => $cart_posted_data['shipping_first_name'],
                "guest_lname"  => $cart_posted_data['shipping_last_name'],
                "guest_email"  => $cart_posted_data['email'],
                "guest_phone"  => $cart_posted_data['shipping_phone'],
                "guest_gender" => $cart_posted_data['custentity_gender'],
                "guest_dob"    => $cart_posted_data['custentity_birthdate'],
            );
            if ( $cart_posted_data && ! empty( $cart_posted_data ) ) {
                foreach ( $cart_posted_data['bike_gears'] as $trek_bike_gear_k => $trek_bike_gear ) {
                    if ( 'primary' === $trek_bike_gear_k ) {
                        $guests_bikes_data[] = array_merge( $guests_p_arr, $trek_bike_gear );
                    }
                    if ( 'guests' === $trek_bike_gear_k && $trek_bike_gear ) {
                        foreach ( $trek_bike_gear as $inner_k => $trek_bike_gear_inner_guest ) {
                            $inner_guest_arr = $guest_req[$inner_k];
                            if( is_array( $inner_guest_arr ) ) {
                                $guests_bikes_data[] = array_merge( $inner_guest_arr, $trek_bike_gear_inner_guest );
                            }
                        }
                    }
                }
                $bikes_cart_item_data['cart_item_data'] = $guests_bikes_data;
            }
            $cart_item['trek_user_formatted_checkout_data'][1]                    = $bikes_cart_item_data;
            $cart_item['trek_user_checkout_data']['occupants']                    = isset($_REQUEST['occupants']) ? $_REQUEST['occupants'] : array();
            $cart_item['trek_user_checkout_data']['private']                      = isset($_REQUEST['private']) ? $_REQUEST['private'] : 0;
            $cart_item['trek_user_checkout_data']['roommate']                     = isset($_REQUEST['roommate']) ? $_REQUEST['roommate'] : 0;
            $cart_item['trek_user_checkout_data']['tt_single_supplement_charges'] = $singleSupplementPrice;
            $cart_item['trek_user_checkout_data']['tt_single_supplement_qty']     = $suppliment_counts;
            $cart[$cart_item_id]                                                  = $cart_item;
        }
    }
    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    $review_order_html = '';
    $review_order      = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if ( is_readable( $review_order ) ) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $stepHTML            = '';
    $checkout_bikes_html = '';
    if (isset($_REQUEST['step']) && $_REQUEST['step'] == 2) {
        $checkout_hotel = TREK_PATH . '/woocommerce/checkout/checkout-hotel.php';
        $stepHTML .= '<div id="tt-hotel-occupant-inner-html">';
        if (is_readable($checkout_hotel)) {
            $stepHTML .= wc_get_template_html('woocommerce/checkout/checkout-hotel.php');
        } else {
            $stepHTML .=  'Checkout Hotel form code is missing!';
        }
        $stepHTML .= '</div>';
        $checkout_bikes = TREK_PATH . '/woocommerce/checkout/checkout-bikes.php';
        if (is_readable($checkout_bikes)) {
            $checkout_bikes_html .= wc_get_template_html('woocommerce/checkout/checkout-bikes.php');
        } else {
            $checkout_bikes_html .=  '<h3>Step 2</h3><p>Checkout Bike form code is missing!</p>';
        }
    }
    $payment_option_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php';
    if (is_readable($review_order)) {
        $payment_option_html .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php');
    } else {
        $payment_option_html .= '<h3>Step 4</h3><p>Checkout payment option file is missing!</p>';
    }

    echo json_encode(
        array(
            'status'         => true,
            'review_order'   => $review_order_html,
            'checkout_bikes' => $checkout_bikes_html,
            'payment_option' => $payment_option_html,
            'stepHTML'       => $stepHTML,
            'step'           => $_REQUEST['step'],
            'message'        => 'Trek checkout occupants data saved!'
        )
    );
    exit;
}
add_action( 'wp_ajax_tt_save_occupants_ajax_action', 'trek_tt_save_occupants_ajax_action_cb' );
add_action( 'wp_ajax_nopriv_tt_save_occupants_ajax_action', 'trek_tt_save_occupants_ajax_action_cb' );

if ( ! function_exists( 'tt_get_local_bike_detail' ) ) {
    /**
     * Get the bikes details from the `netsuite_trip_detail` table.
     *
     * @param string     $trip_id   The trip NS ID.
     * @param string     $trip_code The trip code/product sku.
     * @param string|int $bike_id The bike_id.
     *
     * @return array Bike details for the given trip.
     */
    function tt_get_local_bike_detail( $trip_id = '', $trip_code = '', $bike_id = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'netsuite_trip_detail';

        if ( ! empty( $trip_code ) ) {
            // Get a trip code without suffix.
            $trip_code = tt_get_local_trip_code( $trip_code );
        }

        $results = array();

        // Check for a required parameter.
        if( empty( $trip_code ) ) {
            return $results;
        }

        $sql = "SELECT bikes, tripId, tripCode from {$table_name} as ts WHERE ts.tripCode = '{$trip_code}' AND ts.tripId = '{$trip_id}'";

        $sql_trip_id   = '';
        $sql_trip_code = '';
        $sql_result    = $wpdb->get_results( $sql, ARRAY_A );

        if ( ! empty( $sql_result[0]['tripId'] ) ) {
            $sql_trip_id = $sql_result[0]['tripId'];
        }

        if ( ! empty( $sql_result[0]['tripCode'] ) ) {
            $sql_trip_code = $sql_result[0]['tripCode'];
        }

        if ( $sql_result && ! empty( $sql_result[0]['bikes'] ) ) {
            $results = array_map( function( $bike_details ) use ( $sql_trip_id, $sql_trip_code ) { 
                $bike_detail = array();
                foreach ( $bike_details as $key => $detail ) {
                    $bike_detail['tripId']   = $sql_trip_id;
                    $bike_detail['tripCode'] = $sql_trip_code;
                    if ( is_array( $detail ) ) {
                        $bike_detail[$key] = wp_unslash( json_encode( $detail ) );
                    } else {
                        $bike_detail[$key] = strval( $detail );
                    }
                }
                return $bike_detail;
            }, json_decode( $sql_result[0]['bikes'], true ) );
        }

        if ( $bike_id && ! empty( $results )) {
            $results = array_values( array_filter( $results, function( $bike ) use ( $bike_id ) {
                return strval( $bike_id ) ===  $bike['bikeId'];
            }));
        }
        
        return $results;
    }
}


// TT

if ( ! function_exists( 'wc_get_parent_grouped_id' ) ) {

    function wc_get_parent_grouped_id( $id ){
    
        global $wpdb;
    
        $cdata = wp_cache_get( __FUNCTION__, 'woocommerce' );
    
        if ( ! is_array($cdata) )
            $cdata = array();
    
        if ( ! isset($cdata[$id]) ) {
    
            $cdata[$id] = $parent_id = $children = false;
    
            $qdata = $wpdb->get_row("SELECT post_id, meta_value
                                     FROM $wpdb->postmeta
                                     WHERE meta_key = '_children' 
                                     AND meta_value LIKE '%$id%'");
    
            if ( is_object($qdata) ) {
    
                $parent_id = $qdata->post_id;
                $children = $qdata->meta_value;
    
                if ( is_string($children) )
                    $children = unserialize($children);
    
                if ( is_array($children) && count($children) > 0 )
                    foreach ($children as $child_id)
                        $cdata[$child_id] = $parent_id;
            }
    
            wp_cache_set( __FUNCTION__, apply_filters( __FUNCTION__ . '_filter', $cdata, $id, $parent_id, $children, $qdata ), 'woocommerce' );
        }
    
        return $cdata[$id];
    }
}


function tt_get_parent_trip_id_by_child_sku($sku) {
    global $wpdb;

    // Get the product ID from the SKU
    $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $sku));
    
    // Check if the product ID was found
    if (!$product_id) {
        return "No product found with SKU: {$sku}";
    }

    // Prepare arguments to find grouped products
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 1,  // Query only the first grouped product
        'meta_query' => array(
            array(
                'key' => '_children',
                'value' => $product_id,  // Direct product ID
                'compare' => 'LIKE'
            )
        )
    );

    // Use get_posts to fetch the first post
    $grouped_products = get_posts($args);

    // Check if we have at least one grouped product
    if (!empty($grouped_products)) {
        return $grouped_products[0]->ID;
    } else {
        return "No grouped products found for this SKU.";
    }
}

// TT

// OLD GET GROUPED PROD ID FROM CHILD SKU
// function tt_get_parent_trip_id_by_child_sku( $child_sku = '', $is_nested_dates_trip = false ) {
//     $parent_product_id  = '';
//     $itinerary_code_arr = [];

//     if( $child_sku && wc_get_product_id_by_sku( $child_sku ) ) {

//         $child_product_id = wc_get_product_id_by_sku( $child_sku );

//         if( $child_product_id ) {

//             $itinerary_code = get_post_meta( $child_product_id, TT_WC_META_PREFIX . 'itineraryCode', true );

//             if( $itinerary_code && $child_sku ) {
//                 $itinerary_code_arr = explode( $itinerary_code, $child_sku );
//             }
            
//             if( $itinerary_code_arr && isset( $itinerary_code_arr[0] ) && $itinerary_code ) {
//                 $parent_product_sku = $itinerary_code_arr[0] . $itinerary_code;
//                 $parent_product_id  = wc_get_product_id_by_sku( $itinerary_code );
//                 if( ! $parent_product_id ) {

//                     if( $is_nested_dates_trip ) {
//                         $itinerary_code .= '-4';
//                     }

//                     $parent_product_id = wc_get_product_id_by_sku( $itinerary_code );
//                 }
//             }
//         }
//     }

//     return $parent_product_id;
// }

function tt_get_trip_pid_sku_from_cart($order_id = null)
{
    $sku = $product_id = $ns_trip_Id = '';
    $tt_posted = array();
    if ($order_id) {
        $accepted_p_ids = tt_get_line_items_product_ids();
        $order = wc_get_order($order_id);
        $order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
        foreach ($order_items as $item_id => $item) {
            $product_id = isset($item['product_id']) ? $item['product_id'] : '';
            if (!in_array($product_id, $accepted_p_ids)) {
                $tt_posted = wc_get_order_item_meta($item_id, 'trek_user_checkout_data', true);
                $tt_formatted_data = wc_get_order_item_meta($item_id, 'trek_user_formatted_checkout_data', true);
            }
        }
    } else {
        $tt_checkout_data =  get_trek_user_checkout_data();
        $tt_posted = isset($tt_checkout_data['posted']) ? $tt_checkout_data['posted'] : array();
        $tt_formatted_data = isset($tt_checkout_data['formatted']) ? $tt_checkout_data['formatted'] : array();
    }
    if (isset($tt_posted['sku'])) {
        $sku = $tt_posted['sku'];
    }
    if (isset($tt_posted['product_id'])) {
        $product_id = $tt_posted['product_id'];
        $ns_trip_Id = get_post_meta($product_id, 'tt_meta_tripId', true);
    }
    //Trip Parent ID
    $parent_rider_level = tt_get_local_trips_detail('riderType', '', $sku, true);
    $parent_rider_level = json_decode($parent_rider_level);
    $parent_rider_level_id = is_object($parent_rider_level) ? $parent_rider_level->id : 0;
    $itemData = tt_get_custom_item_name('syncRiderLevels');    
    $options = isset($itemData['options']) ? $itemData['options'] : array();
    $keys = array_keys(array_column($options, 'optionId'), $parent_rider_level_id);
    $rider_level_text = isset($keys[0]) && isset($options[$keys[0]]['optionValue']) ? $options[$keys[0]]['optionValue'] : '';
    $parent_product_id = '';
    $parent_trip_link = 'javascript:';
    $product_image_url = get_template_directory_uri() . '/assets/images/TT-Logo.png';
    $parent_product_id = tt_get_parent_trip_id_by_child_sku($sku);
    if ($parent_product_id) {
        if( has_post_thumbnail($parent_product_id) ){
            $product_image_url = get_the_post_thumbnail_url($parent_product_id);
        }
        $parent_trip_link = get_the_permalink($parent_product_id) ? get_the_permalink($parent_product_id) : 'javascript:';
    }

    return [
        'sku'                => $sku,  
        'parent_rider_level' => isset($parent_rider_level->level) ? $parent_rider_level->level : '',
        'rider_level_text'   => $rider_level_text,
        'product_id'         => $product_id,
        'ns_trip_Id'         => $ns_trip_Id,
        'parent_product_id'  => $parent_product_id,
        'parent_trip_link'   => $parent_trip_link,
        'parent_trip_image'  => $product_image_url,
        'tt_posted'          => $tt_posted,
        'tt_formatted_data'  => $tt_formatted_data,
        'product_line_obj'   => json_decode( tt_get_local_trips_detail( 'product_line', '', $sku, true ) ),
    ];
}
function tt_get_trip_pid_sku_by_orderId($order_id)
{
    $sku = $product_id = $ns_trip_Id = $wc_trip_id ='';
    $tt_data = $tt_formatted_data = $guest_emails =array();
    if( $order_id ){
        $order = wc_get_order($order_id);
        if( $order ){
            $order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
            $accepted_p_ids = tt_get_line_items_product_ids();
            if ($order_items) {
                foreach ($order_items as $item_id => $item) {
                    $product_id = isset($item['product_id']) ? $item['product_id'] : '';
                    if ($product_id && !in_array($product_id, $accepted_p_ids)) {
                        $wc_trip_id = $product_id;
                        $product = $item->get_product();
                        $tt_data = wc_get_order_item_meta($item_id, 'trek_user_checkout_data', true);
                        $tt_formatted_data = wc_get_order_item_meta($item_id, 'trek_user_formatted_checkout_data', true);
                        $sku = $product->get_sku();
                        $ns_trip_Id = get_post_meta($product_id, 'tt_meta_tripId', true);
                    }
                }
            }
        }
        $guests = isset($tt_data['guests']) && is_array($tt_data['guests']) ? $tt_data['guests'] : []; 
        $guest_emails = array_column($guests, 'guest_email');
    }

    return [
        'sku' => $sku,
        'product_id' => $wc_trip_id,
        'ns_trip_Id' => $ns_trip_Id,
        'tt_data' => $tt_data,
        'tt_formatted_data' => $tt_formatted_data,
        'guest_emails' => $guest_emails
    ];
}
/**
 * Build an array with bike size and bike type options.
 *
 * @return array Bike size and bike type options html.
 */
function tt_get_bikes_by_trip_info( $trip_id = '', $tripCode = '', $bikeTypeId = '', $s_bike_size_id = '', $s_bike_type_id = '', $bikeID='', $selected_bikes_arr = array() ) {
    $bike_size_opts       = '<option value="">Select bike size</option>';
    $i_dont_know_selected = ( 33 == (int) $s_bike_size_id ? 'selected' : '');
    $bike_type_info       = tt_ns_get_bike_type_info( $bikeTypeId );
    // Remove "I don't know" for upgrade bikes and Electric Assist bike types in Checkout.
    if ( ! empty( $bike_type_info ) && is_array( $bike_type_info ) && isset( $bike_type_info['name'] ) && isset( $bike_type_info['isBikeUpgrade'] ) && stripos( $bike_type_info['name'], 'electric-assist' ) === false && $bike_type_info['isBikeUpgrade'] != 1 ) {
        $bike_size_opts .= '<option ' . $i_dont_know_selected . ' value="33">I don\'t know</option>'; // Insert the "I don't know" option for bike sizes in the first position.
    }
    $bike_Type_opts       = '<option value="">Select bike type</option>';
    $bikes_arr            = tt_get_local_bike_detail( $trip_id, $tripCode, $bikeID );
    if ($bikes_arr) {
        foreach ($bikes_arr as $bike_info) {
            $bike_available = $bike_info['available'];
            $bikeSizeObj = json_decode($bike_info['bikeSize'], true);
            $bikeTypeObj = json_decode($bike_info['bikeModel'], true);
            $bike_size_id = $bikeSizeObj['id'];
            $bike_size_name = $bikeSizeObj['name'];
            $bike_type_id = $bikeTypeObj['id'];
            if ( $bike_type_id == $bikeTypeId ) {
                $bike_type_name = $bikeTypeObj['name'];
                $loop_bikeId = $bike_info['bikeId'];
                if ( 0 < $bike_available ) {

                    $this_bike_selected_count = 0;

                    foreach( $selected_bikes_arr as $guest => $selected_bike ) {
                        if( (int) $selected_bike['bike_type_id'] === (int) $bike_type_id && (int) $selected_bike['bike_size_id'] === (int) $bike_size_id ) {
                            $this_bike_selected_count++;
                        }
                    }

                    if( $this_bike_selected_count >= (int) $bike_available && (int) $s_bike_size_id !== (int) $bike_size_id ) {
                        $option_disabled = 'disabled';
                    } else {
                        $option_disabled = '';
                    }
                } else {
                    $option_disabled = 'disabled';
                }
                if ($bike_size_id && $bike_size_name) {
                    $selected = ($bike_size_id == $s_bike_size_id ? 'selected' : '');
                    $bike_size_opts .= '<option ' . $selected . ' value="' . $bike_size_id . '" ' . $option_disabled . '>' . $bike_size_name . '</option>';
                }
                if ($bike_type_id && $bike_type_name) {
                    $selected1 = ($loop_bikeId == $s_bike_type_id ? 'selected' : '');
                    $bike_Type_opts .= '<option ' . $selected1 . ' value="' . $bike_type_id . '" ' . $option_disabled . '>' . $bike_type_name . '</option>';
                }
            }
        }
    }
    return [
        'size_opts' => $bike_size_opts,
        'type_opts' => $bike_Type_opts
    ];
    exit;
}
/**
 * Build an array with bike size and bike type options in Post-Booking Checklist.
 *
 * @return array Bike size and bike type options html.
 */
function tt_get_bikes_by_trip_info_pbc( $trip_id = '', $tripCode = '', $bikeTypeId = '', $s_bike_size_id = '', $s_bike_type_id = '',$bikeID='') {
    $bike_size_opts       = '<option value="">Select bike size</option>';
    $i_dont_know_selected = ( 33 == $s_bike_size_id ? 'selected' : '');
    if ( 33 == $s_bike_size_id ) {
        $bike_size_opts      .= '<option ' . $i_dont_know_selected . ' value="33">I don\'t know</option>'; // Insert the "I don't know" option for bike sizes in the first position.
    }
    $bike_Type_opts       = '<option value="">Select bike type</option>';
    $bikes_arr            = tt_get_local_bike_detail( $trip_id, $tripCode, $bikeID );
    if ( $bikes_arr ) {
        foreach ( $bikes_arr as $bike_info ) {
            $bike_available = $bike_info['available'];
            $bikeSizeObj = json_decode($bike_info['bikeSize'], true);
            $bikeTypeObj = json_decode($bike_info['bikeModel'], true);
            $bike_size_id = $bikeSizeObj['id'];
            $bike_size_name = $bikeSizeObj['name'];
            $bike_type_id = $bikeTypeObj['id'];
            if ($bike_type_id == $bikeTypeId ) {
                $bike_type_name = $bikeTypeObj['name'];
                $loop_bikeId = $bike_info['bikeId'];
                if ( 0 < $bike_available ) {
                    $option_disabled = '';
                } else {
                    $option_disabled = 'disabled';
                }
                if ($bike_size_id && $bike_size_name) {
                    $selected = ($bike_size_id == $s_bike_size_id ? 'selected' : '');
                    $bike_size_opts .= '<option ' . $selected . ' value="' . $bike_size_id . '" ' . $option_disabled . '>' . $bike_size_name . '</option>';
                }
                if ($bike_type_id && $bike_type_name) {
                    $selected1 = ($loop_bikeId == $s_bike_type_id ? 'selected' : '');
                    $bike_Type_opts .= '<option ' . $selected1 . ' value="' . $bike_type_id . '" ' . $option_disabled . '>' . $bike_type_name . '</option>';
                }
            }
        }
    }
    return [
        'size_opts' => $bike_size_opts,
        'type_opts' => $bike_Type_opts
    ];
    exit;
}
/**
 * Get the Bike ID for a trip by given Bike Type and Bike Szie.
 *
 * @param string $trip_code The trip code/product sku.
 * @param string|int $bike_type The Bike Type ID.
 * @param string|int $bike_size The Bike Size ID.
 *
 * @return array Status and Bike ID.
 */
function tt_get_bike_id_by_args( $trip_id = '', $trip_code = '', $bike_type = '', $bike_size = '' ) {
    $bikes_arr = tt_get_local_bike_detail( $trip_id, $trip_code );

    if ( $bikes_arr ) {
        foreach ( $bikes_arr as $bike_info ) {
            $bike_size_obj = json_decode( $bike_info['bikeSize'], true );
            $bike_type_obj = json_decode( $bike_info['bikeModel'], true );
            $bike_size_id  = $bike_size_obj['id'];
            $bike_type_id  = $bike_type_obj['id'];
            if ( $bike_type_id == $bike_type && $bike_size_id == $bike_size ) {
                $bike_id = $bike_info['bikeId'];
            }
        }
    }

    // If bike size is 33, means "I don't know" option is selected, and need send to NS bike_id with value 0.
    if( 33 === (int) $bike_size ){
        $bike_id = 0;
    }

    return [
        'status' => true,
        'bike_id' => $bike_id
    ];
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get NS bookings by NS User ID
 **/
if (!function_exists('tt_get_ns_bookings_by_ns_userId')) {
    function tt_get_ns_bookings_by_ns_userId($guest_id = '')
    {
        //Get bookings by guest ID
        $bookings_info = [];
        $netSuiteClient = new NetSuiteClient();
        $ns_result = $netSuiteClient->get(USER_BOOKINGS_SCRIPT_ID, array('guestId' => $guest_id, 'includeBookingInfo' => 1));
        //tt_add_error_log('NS_SCRIPT_ID:'.USER_BOOKINGS_SCRIPT_ID, array('guestId' => $guest_id, 'includeBookingInfo' => 1), $ns_result);
        if ($ns_result && $ns_result->bookings) {
            $bookings_info = $ns_result->bookings;
        }
        return $bookings_info;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get User by by Usermeta
 **/
if (!function_exists('tt_get_userid_by_meta_key_value')) {
    function tt_get_userid_by_meta_key_value($meta_key = '', $meta_value = '')
    {
        global $wpdb;
        $user_id = null;
        $table_name = $wpdb->prefix . 'usermeta';
        $sql = "SELECT um.user_id from {$table_name} as um WHERE ts.{$meta_key} = '{$meta_value}' ";
        $results = $wpdb->get_results($sql, ARRAY_A);
        if ($results && isset($results[0])) {
            $user_id = $results[0]['user_id'];
        }
        return $user_id;
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get User by by Postmeta
 **/
if (!function_exists('tt_get_postid_by_meta_key_value')) {
    function tt_get_postid_by_meta_key_value($meta_key = '', $meta_value = '')
    {
        global $wpdb;
        $post_id = null;
        $table_name = $wpdb->prefix . 'postmeta';
        $sql = "SELECT pm.post_id from {$table_name} as pm WHERE pm.meta_key = '{$meta_key}' AND pm.meta_value = '{$meta_value}'";
        $results = $wpdb->get_results($sql, ARRAY_A);
        if ($results && isset($results[0])) {
            $post_id = $results[0]['post_id'];
        }
        return $post_id;
    }
}
function tt_get_user_checkout_data_by_order_id($order_id)
{
    $accepted_p_ids = tt_get_line_items_product_ids();
    $order = new WC_Order($order_id);
    $items = $order->get_items();
    $checkout_data = $formatted_checkout_data = array();
    $accepted_p_ids = tt_get_line_items_product_ids();
    foreach ($items as $item_id => $item) {
        if ( isset($item['product_id']) && !in_array($item['product_id'], $accepted_p_ids)) {
            $formatted_checkout_data = wc_get_order_item_meta($item_id, 'trek_user_formatted_checkout_data', true);
            $checkout_data = wc_get_order_item_meta($item_id, 'trek_user_checkout_data', true);
        }
    }
    return array(
        'posted' => $checkout_data,
        'formatted' => $formatted_checkout_data
    );
}
add_action( 'woocommerce_created_customer', 'tt_sync_user_metadata_from_ns_cb', 999, 1 );
function tt_sync_user_metadata_from_ns_cb( $user_id )
{
    // Use TM NetSuite plugin to register customer in NetSuite.
    if ( class_exists('TMWNI_Loader') ) {
        $netsuite_user_client = new TMWNI_Loader();
        $ns_user_id           = $netsuite_user_client->addUpdateNetsuiteCustomer( $user_id );

        if( ! empty( $ns_user_id ) ) {
            // We have NS User ID.
            tt_add_error_log('[TM Netsuite] NEW USER', array( 'wp_user_id' => $user_id ), array( 'status' => true, 'ns_user_id' => $ns_user_id ) );

            // 1) single_guest, 2) ns_new_guest_id, 3) wc_user_id, 4) time_range, 5) is_sync_process.
            as_enqueue_async_action( 'tt_trigger_cron_ns_guest_booking_details', array( true, $ns_user_id, $user_id, DEFAULT_TIME_RANGE, true ), '[Sync] - Adding NS Trips for new register guest' );
        } else {
            // TM NetSuite returns 0 if something fails.
            tt_add_error_log('[TM Netsuite] NEW USER', array( 'wp_user_id' => $user_id ), array( 'status' => false, 'ns_user_id' => $ns_user_id, 'message' => 'TM NetSuite plugin Failed. Check the logs in TM NetSuite::Settings::NetSuite API Logs for more information.' ) );

            // Try after 3 minutes again.
            as_schedule_single_action( time() + 180, 'tt_cron_try_sync_user_ns_again', array( $user_id, 1 ) );
        }
    } else {
        tt_add_error_log('[TM Netsuite] NEW USER', array( 'wp_user_id' => $user_id ), array( 'status' => false, 'message' => 'The TMWNI_Loader class does not exist. Verify that the TM NetSuite plugin is enabled and that its API configuration is working.' ) );
    }
}

add_action( 'tt_cron_try_sync_user_ns_again', 'tt_cron_try_sync_user_ns_again_cb', 10, 2 );
function tt_cron_try_sync_user_ns_again_cb( $user_id, $attempt_number )
{
    // Use TM NetSuite plugin to register customer in NetSuite.
    if ( class_exists('TMWNI_Loader') ) {
        $netsuite_user_client = new TMWNI_Loader();
        $ns_user_id           = $netsuite_user_client->addUpdateNetsuiteCustomer( $user_id );

        if( ! empty( $ns_user_id ) ) {
            // We have NS User ID.
            tt_add_error_log('[TM Netsuite] NEW USER Retry', array( 'wp_user_id' => $user_id, 'attempt_number' => $attempt_number ), array( 'status' => true, 'ns_user_id' => $ns_user_id ) );

            // 1) single_guest, 2) ns_new_guest_id, 3) wc_user_id, 4) time_range, 5) is_sync_process.
            as_enqueue_async_action( 'tt_trigger_cron_ns_guest_booking_details', array( true, $ns_user_id, $user_id, DEFAULT_TIME_RANGE, true ), '[Sync] - Adding NS Trips for new register guest' );
        } else {
            // TM NetSuite returns 0 if something fails.
            tt_add_error_log('[TM Netsuite] NEW USER Retry', array( 'wp_user_id' => $user_id, 'attempt_number' => $attempt_number ), array( 'status' => false, 'ns_user_id' => $ns_user_id, 'message' => 'TM NetSuite plugin Failed. Check the logs in TM NetSuite::Settings::NetSuite API Logs for more information.' ) );

            // Stop Retry after 3 attempts.
            if( (int) $attempt_number >= 3 ) {
                return;
            }

            $attempt_number = (int) $attempt_number + 1;

            // Try after 3 minutes again.
            as_schedule_single_action( time() + 180, 'tt_cron_try_sync_user_ns_again', array( $user_id, $attempt_number ) );
        }
    } else {
        tt_add_error_log('[TM Netsuite] NEW USER', array( 'wp_user_id' => $user_id ), array( 'status' => false, 'message' => 'The TMWNI_Loader class does not exist. Verify that the TM NetSuite plugin is enabled and that its API configuration is working.' ) );
    }
}

/**
 * Bike selection AJAX callback.
 *
 * ! This function makes very similar things with tt_woocommerce_before_calculate_totals_cb(),
 * so think about how to combine them or find what is the difference.
 */
function trek_tt_bike_selection_ajax_action_cb() {
    $bikeTypeId              = $_REQUEST['bikeTypeId'];
    $isBikeUpgrade           = false;
    $bikeUpgradeQty          = $_REQUEST['bikeUpgradeQty'];
    $bike_upgrade_qty        = 0;
    $guest_number            = $_REQUEST['guest_number'];
    $trek_user_checkout_data = get_trek_user_checkout_data();
    $postedData              = isset( $trek_user_checkout_data['posted'] ) ? $trek_user_checkout_data['posted'] : array();
    if ( isset( $postedData['bike_gears'] ) && $postedData['bike_gears'] ) {
        foreach ( $postedData['bike_gears'] as $bike_gear_type => $bike_gear ) {
            if ( 'primary' === $bike_gear_type ) {
                if ( $guest_number != 0 ) {
                    $bike_type_id = isset( $bike_gear['bikeTypeId'] ) ? $bike_gear['bikeTypeId'] : '';
                    if ( $bike_type_id ) {
                        $bikeTypeInfo = tt_ns_get_bike_type_info( $bike_type_id );
                        if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
                            $bike_upgrade_qty++;
                        }
                    }
                }
            } else {
                if ( $bike_gear ) {
                    foreach ( $bike_gear as $guest_key => $guestData ) {
                        if ( $guest_number != $guest_key ) {
                            $bike_type_id = isset( $guestData['bikeTypeId']) ? $guestData['bikeTypeId'] : '';
                            if ( $bike_type_id ) {
                                $bikeTypeInfo = tt_ns_get_bike_type_info( $bike_type_id );
                                if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
                                    $bike_upgrade_qty++;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if ( $bikeTypeId ) {
        $bikeTypeInfo = tt_ns_get_bike_type_info( $bikeTypeId );
        if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
            $bike_upgrade_qty++;
            $isBikeUpgrade = true;
        }
    }
    $tripInfo           = tt_get_trip_pid_sku_from_cart();
    $selected_bikes_arr = tt_validate( $_REQUEST['selected_bikes_arr'] );
    $selected_bike_size = tt_validate( $_REQUEST['selected_bike_size'] );
    $opts               = tt_get_bikes_by_trip_info( $tripInfo['ns_trip_Id'], $tripInfo['sku'], $bikeTypeId, $selected_bike_size, '', '', $selected_bikes_arr );
    $accepted_p_ids     = tt_get_line_items_product_ids();
    $product_id         = tt_create_line_item_product('TTWP23UPGRADES');
    $bikeUpgradePrice   = get_post_meta( $tripInfo['product_id'], TT_WC_META_PREFIX . 'bikeUpgradePrice', true);
    if ( $bikeUpgradePrice && $bikeUpgradePrice > 0 ) {
        WC()->cart->add_to_cart( $product_id, $bike_upgrade_qty, 0, array(), array( 'tt_cart_custom_fees_price' => $bikeUpgradePrice ) );
    }
    // Get the current cart contents.
    $cart_contents = WC()->cart->get_cart_contents();
    foreach ( $cart_contents as $cart_item_id => $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && $cart_item['product_id'] == $product_id ) {
            $product                      = wc_get_product($product_id);
            $cart_contents[$cart_item_id] = $cart_item;
            $sku                          = $product->get_sku();
            if ( 'TTWP23UPGRADES' === $sku ) {
                if ( isset( $cart_item['tt_cart_custom_fees_price'] ) && $cart_item['tt_cart_custom_fees_price'] > 0 ) {
                    $cart_item['data']->set_price($cart_item['tt_cart_custom_fees_price']);
                    $bikeUpgradePrice = $cart_item['tt_cart_custom_fees_price'];
                }
                $cart_contents[$cart_item_id]['quantity'] = $bike_upgrade_qty;
            }
        }
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            if ( $guest_number == 0 ) {
                $cart_item['trek_user_checkout_data']['bike_gears']['primary']['bikeTypeId'] = $bikeTypeId;
            } else {
                $cart_item['trek_user_checkout_data']['bike_gears']['guests'][$guest_number]['bikeTypeId'] = $bikeTypeId;
            }
            if ( $guest_number == 0 && $isBikeUpgrade == true ) {
                $cart_item['trek_user_checkout_data']['bike_gears']['primary']['upgrade'] = 'yes';
            }
            if ( $guest_number != 0 && is_numeric( $guest_number ) && $guest_number > 0 && $isBikeUpgrade == true) {
                $cart_item['trek_user_checkout_data']['bike_gears']['guests'][$guest_number]['upgrade'] = 'yes';
            }
            $cart_item['trek_user_checkout_data']['tt_bike_upgrade_qty']     = $bike_upgrade_qty;
            $cart_item['trek_user_checkout_data']['tt_bike_upgrade_charges'] = $bikeUpgradePrice;
            $cart_contents[$cart_item_id]                                    = $cart_item;
        }
    }
    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart_contents );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    $review_order_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if ( is_readable( $review_order ) ) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $opts['review_order']     = $review_order_html;
    $opts['bike_upgrade_qty'] = $bike_upgrade_qty;
    $opts['isBikeUpgrade']    = $isBikeUpgrade;
    echo json_encode( $opts );
    exit;
}
add_action( 'wp_ajax_tt_bike_selection_ajax_action', 'trek_tt_bike_selection_ajax_action_cb' );
add_action( 'wp_ajax_nopriv_tt_bike_selection_ajax_action', 'trek_tt_bike_selection_ajax_action_cb' );

/**
 * Update occupant popup HTML AJAX callback.
 */
function trek_tt_update_occupant_popup_html_ajax_action_cb() {
    $accepted_p_ids = tt_get_line_items_product_ids();
    // Get the current cart contents.
    $cart           = WC()->cart->get_cart_contents();
    $single         = intval( tt_validate( $_REQUEST['single'], 0 ) );
    $double         = intval( tt_validate( $_REQUEST['double'], 0 ) );
    $private        = intval( tt_validate( $_REQUEST['private'], 0 ) );
    $roommate       = intval( tt_validate( $_REQUEST['roommate'], 0 ) );
    foreach ( $cart as $cart_item_id => $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
            $cart_item['trek_user_checkout_data']['single']   = $single;
            $cart_item['trek_user_checkout_data']['double']   = $double;
            $cart_item['trek_user_checkout_data']['private']  = $private;
            $cart_item['trek_user_checkout_data']['roommate'] = $roommate;
            if( 0 === $single ) {
                $cart_item['trek_user_checkout_data']['occupants']['single'] = [];
            }
            if( 0 === $double ) {
                $cart_item['trek_user_checkout_data']['occupants']['double'] = [];
            }
            if( 0 === $private ) {
                $cart_item['trek_user_checkout_data']['occupants']['private'] = [];
            }
            if( 0 === $roommate ) {
                $cart_item['trek_user_checkout_data']['occupants']['roommate'] = [];
            }
            $cart[$cart_item_id] = $cart_item;
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    $trek_user_checkout_data       = get_trek_user_checkout_data();
    $trek_user_checkout_posted     = $trek_user_checkout_data['posted'];
    $occupant_popup_html           = '';
    $checkout_hotel_occupant_popup = TREK_PATH . '/woocommerce/checkout/checkout-hotel-occupant-popup.php';
    if ( is_readable( $checkout_hotel_occupant_popup ) ) {
        $occupant_popup_html = wc_get_template_html('woocommerce/checkout/checkout-hotel-occupant-popup.php', $trek_user_checkout_posted );
    } else {
        $occupant_popup_html = '<p>The checkout-hotel-occupant-popup.php template is missing!</p>';
    }
    $checkout_hotel = TREK_PATH . '/woocommerce/checkout/checkout-hotel.php';
    $hotel_html     = '';
    if( is_readable( $checkout_hotel ) ) {
        $hotel_html = wc_get_template_html('woocommerce/checkout/checkout-hotel.php');
    } 
    echo json_encode(
        array(
            'status'     => true,
            'html'       => $occupant_popup_html,
            'hotel_html' => $hotel_html
        )
    );
    exit;
}
add_action( 'wp_ajax_tt_update_occupant_popup_html_ajax_action', 'trek_tt_update_occupant_popup_html_ajax_action_cb' );
add_action( 'wp_ajax_nopriv_tt_update_occupant_popup_html_ajax_action', 'trek_tt_update_occupant_popup_html_ajax_action_cb' );

/**
 * Bike size change AJAX callback.
 */
function trek_tt_bike_size_change_ajax_action_cb() {
    $bike_type_id = $_REQUEST['bikeTypeId'];
    $bike_size_id = $_REQUEST['bike_size'];
    $trip_info    = tt_get_trip_pid_sku_from_cart();
    $result       = tt_get_bike_id_by_args( $trip_info['ns_trip_Id'], $trip_info['sku'], $bike_type_id, $bike_size_id );
    echo json_encode( $result );
    exit;
}
add_action( 'wp_ajax_tt_bike_size_change_ajax_action', 'trek_tt_bike_size_change_ajax_action_cb' );
add_action( 'wp_ajax_nopriv_tt_bike_size_change_ajax_action', 'trek_tt_bike_size_change_ajax_action_cb' );

/**
 * Bike upgrade fees AJAX callback.
 *
 * ! This function has duplicate code like the other functions above. Needs Optimisation here.
 */
function trek_tt_bike_upgrade_fees_ajax_action_cb() {
    $res              = ['status' => false];
    $upgrade_count    = isset( $_REQUEST['upgrade_count'] ) ? $_REQUEST['upgrade_count'] : 0;
    $guest_index      = isset( $_REQUEST['guest_index'] ) ? $_REQUEST['guest_index'] : '';
    $accepted_p_ids   = tt_get_line_items_product_ids();
    $tripInfo         = tt_get_trip_pid_sku_from_cart();
    $product_id       = tt_create_line_item_product('TTWP23UPGRADES');
    $bikeUpgradePrice = tt_get_local_trips_detail( 'bikeUpgradePrice', '', $tripInfo['sku'], true );

    if ( $bikeUpgradePrice && $bikeUpgradePrice > 0 ) {
        WC()->cart->add_to_cart( $product_id, $upgrade_count, 0, array(), array('tt_cart_custom_fees_price' => $bikeUpgradePrice ) );
    }

    // Get the current cart contents.
    $cart_contents = WC()->cart->get_cart_contents();
    foreach ( $cart_contents as $cart_item_id => $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && $cart_item['product_id'] == $product_id ) {
            $product                      = wc_get_product( $product_id );
            $cart_contents[$cart_item_id] = $cart_item;
            $sku                          = $product->get_sku();
            if ( 'TTWP23UPGRADES' === $sku ) {
                if ( isset( $cart_item['tt_cart_custom_fees_price']) && $cart_item['tt_cart_custom_fees_price'] > 0 ) {
                    $cart_item['data']->set_price( $cart_item['tt_cart_custom_fees_price'] );
                    $bikeUpgradePrice = $cart_item['tt_cart_custom_fees_price'];
                }
            }
            $cart_contents[$cart_item_id]['quantity'] = $upgrade_count;
        }
        if ( isset( $cart_item['product_id'] ) && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
            if ( $guest_index == 0 ) {
                $cart_item['trek_user_checkout_data']['bike_gears']['primary']['upgrade'] = 'yes';
            }
            if ( $guest_index != 0 && is_numeric( $guest_index ) && $guest_index > 0 ) {
                $cart_item['trek_user_checkout_data']['bike_gears']['guests'][$guest_index]['upgrade'] = 'yes';
            }
            $cart_item['trek_user_checkout_data']['tt_bike_upgrade_qty']     = 1;
            $cart_item['trek_user_checkout_data']['tt_bike_upgrade_charges'] = $bikeUpgradePrice;
            $cart_contents[$cart_item_id]                                    = $cart_item;
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart_contents );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    $review_order_html = '';
    $review_order     = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if ( is_readable( $review_order ) ) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $res['review_order'] = $review_order_html;
    $res['status']       = true;
    echo json_encode( $res );
    exit;
}
add_action( 'wp_ajax_tt_bike_upgrade_fees_ajax_action', 'trek_tt_bike_upgrade_fees_ajax_action_cb' );
add_action( 'wp_ajax_nopriv_tt_bike_upgrade_fees_ajax_action', 'trek_tt_bike_upgrade_fees_ajax_action_cb' );

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : tt_ns_get_bike_type_info
 **/
if ( ! function_exists( 'tt_ns_get_bike_type_info' ) ) {
    function tt_ns_get_bike_type_info( $bike_type_id ) {
        $result = array();
        $ns_bike_type_info = get_option(TT_OPTION_PREFIX . 'ns_bikeType_info');
        if ( empty( $ns_bike_type_info ) ) {
            tt_ns_fetch_bike_type_info();
            $ns_bike_type_info = get_option( TT_OPTION_PREFIX . 'ns_bikeType_info' );
        }

        if ( $ns_bike_type_info && $bike_type_id ) {
            $ns_bike_type_result = json_decode( $ns_bike_type_info, true );
            $keys                = array_column( $ns_bike_type_result, 'id' );
            $index               = array_search( $bike_type_id, $keys );
            $result = array(
                'name'          => $ns_bike_type_result[$index]['name'],
                'isActive'      => $ns_bike_type_result[$index]['isActive'],
                'isBikeUpgrade' => $ns_bike_type_result[$index]['isBikeUpgrade']
            );
        }

        return $result;
    }
}

/**
 * Guest rooms selection AJAX callback.
 */
function trek_tt_guest_rooms_selection_ajax_action_cb() {
    $res                       = array( 'status' => false );
    $trek_user_checkout_data   =  get_trek_user_checkout_data();
    $trek_user_checkout_posted = $trek_user_checkout_data['posted'];
    $output                    = tt_rooms_output( $trek_user_checkout_posted, true, true );
    $accepted_p_ids            = tt_get_line_items_product_ids();
    $single                    = isset( $_REQUEST['single'] ) ? $_REQUEST['single'] : 0;
    $double                    = isset( $_REQUEST['double'] ) ? $_REQUEST['double'] : 0;
    $roommate                  = isset( $_REQUEST['roommate'] ) ? $_REQUEST['roommate'] : 0;
    $private                   = isset( $_REQUEST['private'] ) ? $_REQUEST['private'] : 0;
    $special_needs             = isset( $_REQUEST['special_needs'] ) ? $_REQUEST['special_needs'] : 0;
    if ( strlen( $special_needs ) > 250 ) {
        $special_needs = substr( $special_needs, 0, 250 );
    }
    // Get the current cart contents.
    $cart_contents = WC()->cart->get_cart_contents();
    foreach ( $cart_contents as $cart_item_id => $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
            $cart_item['trek_user_checkout_data']['single']        = $single;
            $cart_item['trek_user_checkout_data']['double']        = $double;
            $cart_item['trek_user_checkout_data']['roommate']      = $roommate;
            $cart_item['trek_user_checkout_data']['private']       = $private;
            $cart_item['trek_user_checkout_data']['special_needs'] = $special_needs;
            $cart_contents[$cart_item_id]                          = $cart_item;
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart_contents );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    $res['status'] = true;
    $res['html']   = $output;
    echo json_encode( $res );
    exit;
}
add_action( 'wp_ajax_tt_guest_rooms_selection_ajax_action', 'trek_tt_guest_rooms_selection_ajax_action_cb' );
add_action( 'wp_ajax_nopriv_tt_guest_rooms_selection_ajax_action', 'trek_tt_guest_rooms_selection_ajax_action_cb' );

/**
 * Pay amount change AJAX callback.
 */
function trek_tt_pay_amount_change_ajax_action_cb() {
    $res            = array( 'status' => false );
    $paymentType    = isset($_REQUEST['paymentType']) ? $_REQUEST['paymentType'] : 0;
    $accepted_p_ids = tt_get_line_items_product_ids();
    // Get the current cart contents.
    $cart_contents = WC()->cart->get_cart_contents();
    foreach ( $cart_contents as $cart_item_id => $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
            $cart_item['trek_user_checkout_data']['pay_amount'] = $paymentType;
            $cart_contents[$cart_item_id] = $cart_item;
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart_contents );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    $review_order_html = '';
    $review_order      = TREK_PATH . '/woocommerce/checkout/review-order.php';

    if ( is_readable( $review_order ) ) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }

    $payment_option_html = '';
    $review_order        = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php';

    if (is_readable($review_order)) {
        $payment_option_html .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php');
    } else {
        $payment_option_html .= '<h3>Step 4</h3><p>Checkout payment option file is missing!</p>';
    }

    $res['review_order']   = $review_order_html;
    $res['payment_option'] = $payment_option_html;
    $res['status']         = true;
    echo json_encode( $res );
    exit;
}
add_action( 'wp_ajax_tt_pay_amount_change_ajax_action', 'trek_tt_pay_amount_change_ajax_action_cb' );
add_action( 'wp_ajax_nopriv_tt_pay_amount_change_ajax_action', 'trek_tt_pay_amount_change_ajax_action_cb' );

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Filter Tax rate if available for Trip in NS
 **/
add_filter('woocommerce_matched_tax_rates', 'tt_woocommerce_matched_tax_rates_filter', 10, 6);
function tt_woocommerce_matched_tax_rates_filter($matched_tax_rates, $country, $state, $postcode, $city, $tax_class)
{
    $cart_product_info = tt_get_trip_pid_sku_from_cart();
    $product_sku = $cart_product_info['sku'];
    $ns_trip_Id = $cart_product_info['ns_trip_Id'];
    $taxRate = tt_get_local_trips_detail('taxRate', $ns_trip_Id, $product_sku, true);
    if ($matched_tax_rates && $taxRate) {
        foreach ($matched_tax_rates as $tax_index => $matched_tax_rate) {
            if (isset($matched_tax_rate['label']) && $matched_tax_rate['label'] == 'Tax' && isset($matched_tax_rate['shipping']) && $matched_tax_rate['shipping'] == 'yes') {
                $matched_tax_rates[$tax_index]['rate'] = $taxRate;
            }
        }
    }
    return $matched_tax_rates;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : tt_get_itinerary_link
 **/
if (!function_exists('tt_get_itinerary_link')) {
    function tt_get_itinerary_link($trip_name)
    {
        global $wpdb;
        $itinerary_id = '';
        $itinerary_link = '';
        $table_name = $wpdb->prefix . 'posts';
        $trip_names = explode(' ', $trip_name);
        if ($trip_names && isset($trip_names[0])) {
            $trip_name = $trip_names[0] . ' ' . $trip_names[1];
        }
        $sql = "SELECT ID FROM {$table_name} WHERE post_title LIKE '%{$trip_name}%' AND post_type = 'itinerary' limit 1";
        $results = $wpdb->get_results($sql, ARRAY_A);
        if ($results && isset($results[0]) && isset($results[0]['ID'])) {
            $itinerary_id = $results[0]['ID'];
        }
        if ($itinerary_id) {
            $itinerary_link = get_permalink($itinerary_id);
        }
        return $itinerary_link;
    }
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : get_trip_capacity_info
 **/
if (!function_exists('get_trip_capacity_info')) {
    function get_trip_capacity_info()
    {
        $res = [
            'capacity' => 0,
            'booked' => 0,
            'remaining' => 0
        ];
        $cart_product_info = tt_get_trip_pid_sku_from_cart();
        $product_sku = $cart_product_info['sku'];
        $ns_trip_Id = $cart_product_info['ns_trip_Id'];
        $capacity = tt_get_local_trips_detail('capacity', $ns_trip_Id, $product_sku, true);
        $booked = tt_get_local_trips_detail('booked', $ns_trip_Id, $product_sku, true);
        $remaining = tt_get_local_trips_detail('remaining', $ns_trip_Id, $product_sku, true);
        if ($booked) {
            $res['booked'] = $booked;
        }
        if ($capacity) {
            $res['capacity'] = $capacity;
        }
        if ($remaining) {
            $res['remaining'] = $remaining;
            if( 4 < intval( $remaining ) ) {
                // Limit guest capacity to 4 only available per trip.
                $res['remaining'] = 4;
            }
        }
        return $res;
    }
}

/**
 * Add to cart callback.
 * Triggering on the Book Now button click via JS and form submit.
 *
 * Initialize the trek_user_checkout_data item meta for the trip
 * with essential basic info which will be used on the checkout page.
 */
function tt_woocommerce_add_to_cart_cb() {
    $accepted_p_ids = tt_get_line_items_product_ids();
    // Get the current cart contents.
    $cart_contents = WC()->cart->get_cart_contents();
    if ( $cart_contents ) {
        foreach ( $cart_contents as $cart_item_id => $cart_item ) {
            $wc_data    = isset( $cart_item['data'] ) ? $cart_item['data'] : '';
            $_product   = apply_filters( 'woocommerce_cart_item_product', $wc_data, $cart_item, $cart_item_id );
            $product_id = isset( $cart_item['product_id'] ) ? $cart_item['product_id'] : '';
            if ( $product_id && ! in_array( $product_id, $accepted_p_ids ) ) {
                // Trip Parent ID.
                $cart_item['trek_user_checkout_data']['parent_product_id']     = tt_get_parent_trip_id_by_child_sku( $_product->get_sku() );
                $cart_item['trek_user_checkout_data']['product_id']            = $product_id;
                $cart_item['trek_user_checkout_data']['sku']                   = $_product->get_sku();
                $cart_item['trek_user_checkout_data']['bikeUpgradePrice']      = get_post_meta( $product_id, TT_WC_META_PREFIX . 'bikeUpgradePrice', true);
                $cart_item['trek_user_checkout_data']['singleSupplementPrice'] = get_post_meta( $product_id, TT_WC_META_PREFIX . 'singleSupplementPrice', true);
                

                // Set a redirect to checkout flag to true if the user is not logged in.
                if ( ! is_user_logged_in() ) {
                    $cart_item['trek_user_redirect_to_checkout'] = true;
                }
                $cart_contents[$cart_item_id] = $cart_item;

                // This will add the date the first time only, if the date is missing.
                do_action( 'tt_set_add_to_cart_date' );
            }
        }

        // Store the updated cart.
        WC()->cart->set_cart_contents( $cart_contents );
        // Recalculate the totals after modifying the cart.
        WC()->cart->calculate_totals();
        // Save the updated cart to the session.
        WC()->cart->set_session();
        // Update persistent_cart.
        WC()->cart->persistent_cart_update();
    }
}
add_action( 'woocommerce_add_to_cart', 'tt_woocommerce_add_to_cart_cb' );

/**
 * Add to cart message filter callback.
 *
 * @param mixed     $message The message.
 * @param int|array $products Product ID list or single product ID.
 * @param bool      $show_qty Should quantities be shown? Added in 2.6.0.
 */
function tt_wc_add_to_cart_message_html( $message, $products, $show_qty ) {
    $message = '';
    return $message;
}
add_filter( 'wc_add_to_cart_message_html', 'tt_wc_add_to_cart_message_html', 10, 3 );

function trek_isMobile()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
function tt_checkout_validation()
{
    $tt_chekout_data =  get_trek_user_checkout_data();
    $tt_posted = $tt_chekout_data['posted'];
    $no_of_guests = isset($tt_posted['no_of_guests']) ? $tt_posted['no_of_guests'] : 0;
}
function tt_hotel_rooms_info()
{
    $cart_product_info = tt_get_trip_pid_sku_from_cart();
    $product_sku = $cart_product_info['sku'];
    $ns_trip_Id = $cart_product_info['ns_trip_Id'];
    $trek_trip_details = tt_get_local_trips_detail('hotels', $ns_trip_Id, $product_sku);
    if (!empty($trek_trip_details) && isset($trek_trip_details[0]->hotels)) {
        $hotels = json_decode($trek_trip_details[0]->hotels);
        if ($hotels) {
            foreach ($hotels as $hotel) {
                $tripsData = array(
                    'hotelId' => $hotel->hotelId,
                    'hotelName' => $hotel->hotelName,
                    'rooms' => json_encode($hotel->rooms),
                );
            }
        }
    }
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : TT Checkout custom validations 
 **/
add_action('woocommerce_after_checkout_validation', 'tt_checkout_fields_error_messages', 9999, 2);
function tt_checkout_fields_error_messages($fields, $errors)
{
    $currentUser = wp_get_current_user();
    $trip_booking_limit = get_trip_capacity_info();
    $no_of_guests = isset($_REQUEST['no_of_guests']) ? $_REQUEST['no_of_guests'] : 0;
    $trip_capacity = $trip_booking_limit['remaining'];
    $bike_gears = isset($_REQUEST['bike_gears']) && $_REQUEST['bike_gears'] ? $_REQUEST['bike_gears'] : array();
    $sf_name = isset($_REQUEST['shipping_first_name']) ? $_REQUEST['shipping_first_name'] : '';
    $sl_name = isset($_REQUEST['shipping_last_name']) ? $_REQUEST['shipping_last_name'] : '';
    $s_email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
    $s_email = $currentUser->user_email;
    $sp_phone = isset($_REQUEST['shipping_phone']) ? $_REQUEST['shipping_phone'] : '';
    $s_dob = isset($_REQUEST['custentity_birthdate']) ? $_REQUEST['custentity_birthdate'] : '';
    $s_gender = isset($_REQUEST['custentity_gender']) && $_REQUEST['custentity_gender'] != 0 ? $_REQUEST['custentity_gender'] : '';
    $s_addr1 = isset($_REQUEST['shipping_address_1']) ? $_REQUEST['shipping_address_1'] : '';
    $s_country = isset($_REQUEST['shipping_country']) ? $_REQUEST['shipping_country'] : '';
    $s_state = isset($_REQUEST['shipping_state']) ? $_REQUEST['shipping_state'] : '';
    $s_city = isset($_REQUEST['shipping_city']) ? $_REQUEST['shipping_city'] : '';
    $s_postcode = isset($_REQUEST['shipping_postcode']) ? $_REQUEST['shipping_postcode'] : '';
    $guests = isset($_REQUEST['guests']) ? $_REQUEST['guests'] : array();
    $is_same_billing_as_mailing = isset($_REQUEST['is_same_billing_as_mailing']) ? $_REQUEST['is_same_billing_as_mailing'] : 0;

    // Correct the billing_info array to have unique fields
    $billing_info = [
        'billing_first_name',
        'billing_last_name',
        'billing_address_1',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_postcode'
    ];

    $p_fields = [];
    $p_user_1_error = $g_user_1_error = false;
    $p_user_2_error = $g_user_2_error = $billing_error = false;

    if ($no_of_guests > $trip_capacity) {
        $errors->add('woocommerce_trip_capacity_error', __('You cannot add more guests than the Trip capacity.'));
    }

    // Define countries without states
    $countries_without_states = ['LU', 'MC', 'SG', 'VA', 'IS', 'CY', 'MT', 'MO', 'LI', 'SM', 'AD', 'QA', 'BH', 'MV', 'SC', 'BN', 'BB', 'LC', 'DK'];

    // Shipping fields validation
    if (
        empty($sf_name) || empty($sl_name) || empty($s_email) || empty($s_addr1) || empty($sp_phone)
        || empty($s_dob) || empty($s_gender) || $s_gender == 0 || empty($s_country) || 
        (!empty($s_country) && !in_array($s_country, $countries_without_states) && empty($s_state)) ||
        empty($s_city) || empty($s_postcode)
    ) {
        $p_fields[] = empty($sf_name) ? 'First Name' : '';
        $p_fields[] = empty($sl_name) ? 'Last Name' : '';
        $p_fields[] = empty($s_email) ? 'Email' : '';
        $p_fields[] = empty($s_addr1) ? 'Address' : '';
        $p_fields[] = empty($sp_phone) ? 'Phone' : '';
        $p_fields[] = empty($s_dob) ? 'DOB' : '';
        $p_fields[] = empty($s_gender) || $s_gender == 0 ? 'Gender' : '';
        $p_fields[] = empty($s_country) ? 'Country' : '';
        $p_fields[] = (!empty($s_country) && !in_array($s_country, $countries_without_states) && empty($s_state)) ? 'State' : '';
        $p_fields[] = empty($s_city) ? 'City' : '';
        $p_fields[] = empty($s_postcode) ? 'Postcode' : '';
        $p_user_1_error = true;
    }

    // Billing fields validation
    if ($billing_info && $is_same_billing_as_mailing != 1) {
        $billing_country = isset($_REQUEST['billing_country']) ? $_REQUEST['billing_country'] : '';

        foreach ($billing_info as $billing_field) {
            if ($billing_field == 'billing_state') {
                if (!in_array($billing_country, $countries_without_states) && empty($_REQUEST[$billing_field])) {
                    $billing_error = true;
                    $errors->add('woocommerce_billing_state_error', __("<strong>Billing State</strong> is a required field."));
                    break;
                }
            } elseif ($billing_field != 'billing_state' && (!isset($_REQUEST[$billing_field]) || empty($_REQUEST[$billing_field]))) {
                $billing_error = true;
                break;
            }
        }
    }

    if (isset($_REQUEST['tt_waiver']) == false) {
        $errors->add('woocommerce_waiver_error', __("Please check Waiver checkbox."));
    }
    if (isset($_REQUEST['tt_terms']) == false) {
        $errors->add('woocommerce_tt_terms_error', __("Please check the Terms and Conditions and Cancellation Policy."));
    }
    if ($p_user_1_error == true) {
        $p_fields_string = is_array($p_fields) && $p_fields ? implode(', ', array_filter($p_fields)) : '';
        $errors->add('woocommerce_primary_guests_error', __('Please fill fields '.$p_fields_string.' of Primary user in step 1.'));
    }
    if ($g_user_1_error == true) {
        $errors->add('woocommerce_guests_error', __("Please fill all fields of Guest user in step 1."));
    }
    if ($billing_error == true && $is_same_billing_as_mailing != 1) {
        $errors->add('woocommerce_tt_billing_error', __("Please fill all billing fields in step 3"));
    } elseif ( $is_same_billing_as_mailing != 1 ) {
        // Let's store the billing address here, before completing the checkout process.
        // This will fire on click the Pay Now button and if there is no error with the billing address, we can store it.
        $save_billing_data = array( 'is_same_billing_as_mailing' => $is_same_billing_as_mailing );

        $billing_info_fields = array(
            'billing_first_name',
            'billing_last_name',
            'billing_address_1',
            'billing_address_2',
            'billing_country',
            'billing_state',
            'billing_city',
            'billing_postcode'
        );

        foreach ( $billing_info_fields as $billing_field ) {
            if( isset( $_REQUEST[$billing_field] ) ) {
                $save_billing_data[$billing_field] = sanitize_text_field( wp_unslash( $_REQUEST[$billing_field] ) );
            }
        }

        tt_update_trek_user_checkout_data( $save_billing_data );
    }
    if ($p_user_2_error == true) {
        $errors->add('woocommerce_gears_error', __('Please fill Primary user`s Bike & Gears fields.'));
    }
    if ($g_user_2_error == true) {
        $errors->add('woocommerce_guest_gears_error', __('Please fill Guest user`s Bike & Gears fields.'));
    }
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : TT Post booking checklist Bike selection Ajax
 **/
add_action('wp_ajax_tt_chk_bike_selection_ajax_action', 'trek_tt_chk_bike_selection_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_chk_bike_selection_ajax_action', 'trek_tt_chk_bike_selection_ajax_action_cb');
function trek_tt_chk_bike_selection_ajax_action_cb()
{
    $bikeTypeId = $_REQUEST['bikeTypeId'];
    $order_id = $_REQUEST['order_id'];
    $trip_info = tt_get_trip_pid_sku_by_orderId( $order_id );
    $opts = tt_get_bikes_by_trip_info_pbc( $trip_info['ns_trip_Id'], $trip_info['sku'], $bikeTypeId  );
    echo json_encode($opts);
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : TT Post booking checklist Bike size selection Ajax
 **/
add_action('wp_ajax_tt_chk_bike_size_change_ajax_action', 'trek_tt_chk_bike_size_change_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_chk_bike_size_change_ajax_action', 'trek_tt_chk_bike_size_change_ajax_action_cb');
function trek_tt_chk_bike_size_change_ajax_action_cb() {
    $bike_type_id = $_REQUEST['bikeTypeId'];
    $bike_size_id = $_REQUEST['bike_size'];
    $order_id     = $_REQUEST['order_id'];
    $trip_info    = tt_get_trip_pid_sku_by_orderId( $order_id );
    $result       = tt_get_bike_id_by_args( $trip_info['ns_trip_Id'], $trip_info['sku'], $bike_type_id, $bike_size_id );
    echo json_encode( $result );
    exit;
}
function tt_rooms_output($tt_posted = [], $is_all = false, $is_header=true)
{
    $shipping_name = '';
    $guests = array();
    if ($tt_posted) {
        $shipping_name  = $tt_posted['shipping_first_name'] . ' ' . $tt_posted['shipping_last_name'];
        $guests = isset($tt_posted['guests']) ? $tt_posted['guests'] : array();
    }
    $single_rooms_html = $double_rooms_html = $private_rooms_html = $roommate_rooms_html = '';
    $guest_occupants = isset($tt_posted['occupants']) ? $tt_posted['occupants'] : array();
    $room_single = isset($tt_posted['single']) ? $tt_posted['single'] : 0;
    $room_double = isset($tt_posted['double']) ? $tt_posted['double'] : 0;
    $room_private = isset($tt_posted['private']) ? $tt_posted['private'] : 0;
    $room_roommate = isset($tt_posted['roommate']) ? $tt_posted['roommate'] : 0;
    if (isset($guest_occupants)) {
        foreach ($guest_occupants as $room_type => $rooms_info) {
            $guest_names = [];
            if ($room_type == 'single' && $rooms_info && is_array($rooms_info)) {
                foreach ($rooms_info as $room_count => $guest_id) {
                    if ($guest_id == 0) {
                        $guest_names[] = $shipping_name;
                    } else {
                        if ($guest_id && $guest_id !== 'none') {
                            $guest_fname = isset($guests[$guest_id]) ? $guests[$guest_id]['guest_fname'] : '';
                            $guest_lname = isset($guests[$guest_id]) ? $guests[$guest_id]['guest_lname'] : '';
                            $guest_names[] = $guest_fname . ' ' . $guest_lname;
                        }
                    }
                }
                if ($guest_names && is_array($guest_names)) {
                    $single_rooms_html .= '<div class="checkout-bikes__selected-rooms">';
                    if( $is_header == true){
                        $single_rooms_html .= '<p class="fw-medium fs-xl lh-lg mb-0">Room with 1 Beds<span class="checkout-bikes__bed-icon"></span></p>';
                    }
                    $is_multiple_s_rooms = false;
                    if ($room_single > 1) {
                        $single_roomsArr = array_chunk($guest_names, $room_single);
                        $is_multiple_s_rooms = true;
                    }
                    if ($is_multiple_s_rooms == true) {
                        if ($single_roomsArr) {
                            foreach ($single_roomsArr as $room_index => $single_room) {
                                $single_rooms_html .= '<ul class="list-inline mb-0">
                                    <li class="list-inline-item"><img src="' . get_template_directory_uri() . '/assets/images/person_check.svg" /></li>
                                    <li class="list-inline-item fw-medium fs-sm lh-sm">Room ' . ($room_index + 1) . '</li>
                                    <li class="list-inline-item fw-normal fs-sm lh-sm">' . (implode(', ', $single_room)) . '</li>
                                </ul>';
                            }
                        }
                    } else {
                        $single_rooms_html .= '<ul class="list-inline mb-0">
                                    <li class="list-inline-item"><img src="' . get_template_directory_uri() . '/assets/images/person_check.svg" /></li>
                                    <li class="list-inline-item fw-medium fs-sm lh-sm">Room 1</li>
                                    <li class="list-inline-item fw-normal fs-sm lh-sm">' . (implode(', ', $guest_names)) . '</li>
                                </ul>';
                    }
                    $single_rooms_html .= '</div>';
                }
            }
            if ($room_type == 'double' && $rooms_info && is_array($rooms_info)) {
                foreach ($rooms_info as $room_count => $guest_id) {
                    if ($guest_id == 0) {
                        $guest_names[] = $shipping_name;
                    } else {
                        if ($guest_id && $guest_id !== 'none') {
                            $guest_fname = isset($guests[$guest_id]) ? $guests[$guest_id]['guest_fname'] : '';
                            $guest_lname = isset($guests[$guest_id]) ? $guests[$guest_id]['guest_lname'] : '';
                            $guest_names[] = $guest_fname . ' ' . $guest_lname;
                        }
                    }
                }
                if ($guest_names && is_array($guest_names)) {
                    $double_rooms_html .= '<div class="checkout-bikes__selected-rooms">';
                    if( $is_header == true){
                        $double_rooms_html .='<p class="fw-medium fs-xl lh-lg mb-0">Room with 2 Beds<span class="checkout-bikes__bed-icon"></span><span class="checkout-bikes__bed-icon"></span></p>';
                    }
                    $is_multiple_d_rooms = false;
                    if ($room_double > 1) {
                        $double_roomsArr = array_chunk($guest_names, $room_double);
                        $is_multiple_d_rooms = true;
                    }
                    if ($is_multiple_d_rooms == true) {
                        if ($double_roomsArr) {
                            foreach ($double_roomsArr as $room_index => $double_room) {
                                $double_rooms_html .= '<ul class="list-inline mb-0">
                                <li class="list-inline-item"><img src="' . get_template_directory_uri() . '/assets/images/person_check.svg" /></li>
                                <li class="list-inline-item fw-medium fs-sm lh-sm">Room ' . ($room_index + 1) . '</li>
                                <li class="list-inline-item fw-normal fs-sm lh-sm">' . (implode(', ', $double_room)) . '</li>
                            </ul>';
                            }
                        }
                    } else {
                        $double_rooms_html .= '<ul class="list-inline mb-0">
                                <li class="list-inline-item"><img src="' . get_template_directory_uri() . '/assets/images/person_check.svg" /></li>
                                <li class="list-inline-item fw-medium fs-sm lh-sm">Room 1</li>
                                <li class="list-inline-item fw-normal fs-sm lh-sm">' . (implode(', ', $guest_names)) . '</li>
                            </ul>';
                    }
                    $double_rooms_html .= '</div>';
                }
            }
            if ($room_type == 'private' && $rooms_info && is_array($rooms_info)) {
                foreach ($rooms_info as $room_count => $guest_id) {
                    if ($guest_id == 0) {
                        $guest_names[] = $shipping_name;
                    } else {
                        if ($guest_id && $guest_id !== 'none') {
                            $guest_fname = isset($guests[$guest_id]) ? $guests[$guest_id]['guest_fname'] : '';
                            $guest_lname = isset($guests[$guest_id]) ? $guests[$guest_id]['guest_lname'] : '';
                            $guest_names[] = $guest_fname . ' ' . $guest_lname;
                        }
                    }
                }
                if ($guest_names && is_array($guest_names)) {
                    $private_rooms_html .= '<div class="checkout-bikes__selected-rooms">';
                    if( $is_header == true){
                        $private_rooms_html.= '<p class="fw-medium fs-xl lh-lg mb-0">Private room<span class="checkout-bikes__bed-icon"></span></p>';
                    }
                    foreach ($guest_names as $room_index => $guest_name) {
                        $private_rooms_html .= '<ul class="list-inline mb-0">
                            <li class="list-inline-item"><img src="' . get_template_directory_uri() . '/assets/images/person_check.svg" /></li>
                            <li class="list-inline-item fw-medium fs-sm lh-sm">Room ' . ($room_index + 1) . '</li>
                            <li class="list-inline-item fw-normal fs-sm lh-sm">' . $guest_name . '</li>
                        </ul>';
                    }
                    $private_rooms_html .= '</div>';
                }
            }
            if ($room_type == 'roommate' && $rooms_info && is_array($rooms_info)) {
                foreach ($rooms_info as $room_count => $guest_id) {
                    if ($guest_id == 0) {
                        $guest_names[] = $shipping_name;
                    } else {
                        if ($guest_id && $guest_id !== 'none') {
                            $guest_fname = isset($guests[$guest_id]) ? $guests[$guest_id]['guest_fname'] : '';
                            $guest_lname = isset($guests[$guest_id]) ? $guests[$guest_id]['guest_lname'] : '';
                            $guest_names[] = $guest_fname . ' ' . $guest_lname;
                        }
                    }
                }
                if ($guest_names && is_array($guest_names)) {
                    $roommate_rooms_html .= '<div class="checkout-bikes__selected-rooms">';
                    if( $is_header == true){
                        $roommate_rooms_html .='<p class="fw-medium fs-xl lh-lg mb-0">Roommate<span class="checkout-bikes__bed-icon"></span><span class="checkout-bikes__bed-icon ms-1"></span></p>';
                    }
                    foreach ($guest_names as $room_index => $guest_name) {
                        $roommate_rooms_html .= '<ul class="list-inline mb-0">
                            <li class="list-inline-item"><img src="' . get_template_directory_uri() . '/assets/images/person_check.svg" /></li>
                            <li class="list-inline-item fw-medium fs-sm lh-sm">Room ' . ($room_index + 1) . '</li>
                            <li class="list-inline-item fw-normal fs-sm lh-sm">' . $guest_name . '</li>
                        </ul>';
                    }
                    $roommate_rooms_html .= '</div>';
                }
            }
        }
    }
    if ($is_all == false) {
        return [
            'single' => $single_rooms_html,
            'double' => $double_rooms_html,
            'private' => $private_rooms_html,
            'roommate' => $roommate_rooms_html
        ];
    } else {
        $final_output = '';
        $final_output .= $single_rooms_html;
        $final_output .= $double_rooms_html;
        $final_output .= $private_rooms_html;
        $final_output .= $roommate_rooms_html;
        return $final_output;
    }
}

/**
 * Take the guest insurance output HTML.
 *
 * @param array  $tt_posted   Posted order guest data, that are stored into trek_user_checkout_data.
 * @param string $before_html The opening wrapper html. By default: <div id="travel-protection-summary">
 * @param string $after_html  The closing wrapper html. By default: </div>
 *
 * @uses checkout-insured-summary.php template file.
 *
 * @return string Guest insurance HTML.
 */
function tt_guest_insurance_output( $tt_posted = array(), $before_html = '<div id="travel-protection-summary">', $after_html = '</div>' ) {
    $template_args = array(
        'before_html' => $before_html,
        'tt_posted'   => $tt_posted,
        'after_html'  => $after_html
    );

    $guest_insurance_html              = ''; 
    $checkout_insured_summary_template = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-summary.php';

    if( is_readable( $checkout_insured_summary_template ) ) {
        $guest_insurance_html = wc_get_template_html( 'woocommerce/checkout/checkout-ajax-templates/checkout-insured-summary.php', $template_args );
    } else {
        $guest_insurance_html = '<h3>Step 4</h3><p>checkout-insured-summary.php form code is missing!</p>';
    }

    return $guest_insurance_html;
}
function tt_guest_details($tt_posted = [])
{
    $userInfo = wp_get_current_user();
    $guest_emails = [];
    $guest_html = $bike_gear_html = $hiking_gear_html = '';
    if ($tt_posted) {
        $iter = 0;
        $cols = 2;
        $guest_count = 1;
        $guest_count += isset($tt_posted['guests']) && $tt_posted['guests'] ? count($tt_posted['guests']) : 0;
        $bike_gears = isset($tt_posted['bike_gears']) ? $tt_posted['bike_gears'] : [];
        $fname = isset($tt_posted['shipping_first_name']) ? $tt_posted['shipping_first_name'] : '';
        $lname = isset($tt_posted['shipping_last_name']) ? $tt_posted['shipping_last_name'] : '';
        $email = isset($tt_posted['email']) ? $tt_posted['email'] : $userInfo->user_email;
        $guest_emails[] = $email;
        $phone = isset($tt_posted['shipping_phone']) ? $tt_posted['shipping_phone'] : '';
        $gender = isset($tt_posted['custentity_gender']) && $tt_posted['custentity_gender'] == 1 ? 'Male' : 'Female';
        $dob =  isset($tt_posted['custentity_birthdate']) ? $tt_posted['custentity_birthdate'] : '';
        $addr1 = isset($tt_posted['shipping_address_1']) ? $tt_posted['shipping_address_1'] : '';
        $addr2 = isset($tt_posted['shipping_address_2']) ? $tt_posted['shipping_address_2'] : '';
        $country = isset($tt_posted['shipping_country']) ? $tt_posted['shipping_country'] : '';
        $state = isset($tt_posted['shipping_state']) ? $tt_posted['shipping_state'] : '';
        $city = isset($tt_posted['shipping_city']) ? $tt_posted['shipping_city'] : '';
        $postcode = isset($tt_posted['shipping_postcode']) ? $tt_posted['shipping_postcode'] : '';
        $sku = isset($tt_posted['sku']) ? $tt_posted['sku'] : '';
        $product_id = $tt_posted['product_id'];
        $ns_trip_id = get_post_meta($product_id, 'tt_meta_tripId', true);
        $local_bike_details = tt_get_local_bike_detail( $ns_trip_id, $sku );
        $local_bike_models_info = array_column( $local_bike_details, 'bikeModel', 'bikeId' );
        for ($iter = 0; $iter < $guest_count; $iter++) {
            if ($iter % $cols == 0) {
                $guest_html .= '<div class="d-flex order-details__flex order-details__flexmulti">';
                $bike_gear_html .= '<div class="d-flex order-details__flex order-details__flexmulti">';
                $hiking_gear_html .= '<div class="d-flex order-details__flex order-details__flexmulti">';
            }
            $rider_levelVal    = tt_validate( $bike_gears['primary']['rider_level'] );
            $activity_levelVal = tt_validate( $bike_gears['primary']['activity_level'] );
            // If $bike_id is with value 0, we need send 0 to NS, that means customer selected "I don't know" option for $bike_size.
            $default_p_bike_id = '';
            if( 0 == $bike_gears['primary']['bikeId'] ){
                $default_p_bike_id = 0;
            }
            $bikeId = tt_validate($bike_gears['primary']['bikeId'], $default_p_bike_id);
            $bike_size = tt_validate($bike_gears['primary']['bike_size']);
            $bike = tt_validate($bike_gears['primary']['bike']);
            $rider_height = tt_validate($bike_gears['primary']['rider_height']);
            $user_height = tt_validate($bike_gears['primary']['rider_height']);
            $bike_pedal = tt_validate($bike_gears['primary']['bike_pedal']);
            $helmet_size = tt_validate($bike_gears['primary']['helmet_size']);
            $jersey_style = tt_validate($bike_gears['primary']['jersey_style']);
            $jersey_size = tt_validate($bike_gears['primary']['jersey_size']);
            $tshirt_size = tt_validate($bike_gears['primary']['tshirt_size']);
            $bikeTypeId = tt_validate($bike_gears['primary']['bikeTypeId']);
            $own_bike = tt_validate($bike_gears['primary']['own_bike']);
            if ($iter != 0 && isset($tt_posted['guests']) && $tt_posted['guests'] && $tt_posted['guests'][$iter]) {
                $guest_info = $tt_posted['guests'][$iter];
                $fname = isset($guest_info['guest_fname']) ? $guest_info['guest_fname'] : '';
                $lname = isset($guest_info['guest_lname']) ? $guest_info['guest_lname'] : '';
                $email = isset($guest_info['guest_email']) ? $guest_info['guest_email'] : '';
                $guest_emails[] = $email;
                $phone = isset($guest_info['guest_phone']) ? $guest_info['guest_phone'] : '';
                $dob = isset($guest_info['guest_dob']) ? $guest_info['guest_dob'] : '';
                $gender = isset($guest_info['guest_gender']) && $guest_info['guest_gender'] == 1 ? 'Male' : 'Female';
                $rider_levelVal    = tt_validate( $bike_gears['guests'][$iter]['rider_level'] );
                $activity_levelVal = tt_validate( $bike_gears['guests'][$iter]['activity_level'] );
                // If $bike_id is with value 0, we need send 0 to NS, that means customer selected "I don't know" option for $bike_size.
                $default_bike_id = '';
                if( 0 == $bike_gears['guests'][$iter]['bikeId'] ){
                    $default_bike_id = 0;
                }
                $bikeId = tt_validate($bike_gears['guests'][$iter]['bikeId'], $default_bike_id);
                $bike_size = tt_validate($bike_gears['guests'][$iter]['bike_size']);
                $bike = tt_validate($bike_gears['guests'][$iter]['bike']);
                $rider_height = tt_validate($bike_gears['guests'][$iter]['rider_height']);
                $user_height = tt_validate($bike_gears['guests'][$iter]['rider_height']);
                $bike_pedal = tt_validate($bike_gears['guests'][$iter]['bike_pedal']);
                $helmet_size = tt_validate($bike_gears['guests'][$iter]['helmet_size']);
                $jersey_style = tt_validate($bike_gears['guests'][$iter]['jersey_style']);
                $jersey_size = tt_validate($bike_gears['guests'][$iter]['jersey_size']);
                $tshirt_size = tt_validate($bike_gears['guests'][$iter]['tshirt_size']);
                $bikeTypeId = tt_validate($bike_gears['guests'][$iter]['bikeTypeId']);
                $own_bike = tt_validate($bike_gears['guests'][$iter]['own_bike']);
            }

            $wheel_upgrade = 'No';
            $bikeTypeInfo = tt_ns_get_bike_type_info( $bikeTypeId );
            if ( $bikeTypeInfo && isset( $bikeTypeInfo['isBikeUpgrade'] ) && $bikeTypeInfo['isBikeUpgrade'] == 1 ) {
                $wheel_upgrade = 'Yes';
            }
            $guest_html .= '<div>';
            if ($iter == 0) {
                $guest_label = "Primary Guest";
            }else{
                $guest_label = "Guest ".($iter+1);
            }
            $guest_html .= '<p class="mb-2 fs-md lh-md fw-medium">' . $guest_label . '</p>';
            $guest_html .= '<p class="mb-2 fs-md lh-md fw-medium">' . $fname . ' ' . $lname . '</p>
            <p class="mb-0 fs-sm lh-sm fw-normal">' . $email . '</p>
            <p class="mb-0 fs-sm lh-sm fw-normal">' . $phone . '</p>
            <p class="mb-0 fs-sm lh-sm fw-normal">' . $gender . '</p>
            <p class="mb-0 fs-sm lh-sm fw-normal">' . $dob . '</p>';
            if ($iter == 0) {
                $guest_details_states = WC()->countries->get_states( $country );
                $billing_state_name   = isset( $guest_details_states[$state] ) ? $guest_details_states[$state] : $state;
                $billing_country_name = WC()->countries->countries[$country];
                $guest_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">'.$addr1.'</p>
                <p class="mb-0 fs-sm lh-sm fw-normal">'.$addr2.'</p>
                <p class="mb-0 fs-sm lh-sm fw-normal">'.$billing_state_name.', '.$city.', '.$postcode.'</p>
                <p class="mb-0 fs-sm lh-sm fw-normal">'.$billing_country_name.'</p>';
            }
            $guest_html .= '</div>';
            $bike_gear_html .= '<div>';
            //Gear information
            $rider_level = tt_get_custom_item_name('syncRiderLevels', $rider_levelVal);
            $bike_size = tt_get_custom_item_name('syncBikeSizes', $bike_size);
            $rider_height = tt_get_custom_item_name('syncHeights', $rider_height);
            $helmet_size = tt_get_custom_item_name('syncHelmets', $helmet_size);
            $bikeTypeId = tt_get_custom_item_name('syncBikeTypes', $bikeTypeId);
            $bike_pedal = tt_get_custom_item_name('syncPedals', $bike_pedal);
            $jersey_size = tt_get_custom_item_name('syncJerseySizes', $jersey_size);
            $jersey_style = tt_get_custom_item_name('syncJerseySizes', $jersey_style);

            // Set the bike name based on bikeId value.
            $bike_name = '';
            if( ( isset($bikeId) && $bikeId ) || 0 == $bikeId ){
                switch ( $bikeId ) {
                    case 5270: // I am bringing my own bike.
                        $bike_name = 'Bringing own';
                        break;
                    case 0: // If set to 0, it means "I don't know" was picked for bike size and the bikeTypeName property will be used.
                        $bike_name = $bikeTypeId;
                        break;
                    default: // Take the name of the bike.
                        $bike_name = json_decode( $local_bike_models_info[ $bikeId ], true)[ 'name' ];
                        break;
                }
            }

            $bike_gear_html .= '<p class="mb-2 fs-md lh-md fw-medium">' . $guest_label . '</p>';
            $bike_gear_html .= '<p class="mb-2 fs-md lh-md fw-medium">' . $fname . ' ' . $lname . '</p>';
            $bike_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Rider Level: ' . $rider_level . '</p>';                
            if(  $rider_levelVal != 5 ){
                if( !empty( $bike_name ) ){

                    $bike_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Bike: ' . $bike_name . '</p>';
                }
                if( 'yes' !== $own_bike || 0 == $bikeId){
                    $bike_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Bike Size: ' . $bike_size . '</p>
                    <p class="mb-0 fs-sm lh-sm fw-normal">Rider Height: ' . $rider_height . '</p>';
                }
                $bike_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Pedals: ' . $bike_pedal . '</p>
                <p class="mb-0 fs-sm lh-sm fw-normal">Helmet Size: ' . $helmet_size . '</p>';
                if( !empty( $jersey_size ) && ! is_array( $jersey_size )  && '-' != $jersey_size ) {
                    $bike_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Jersey: ' . $jersey_size . '</p>';
                }
            }
            $bike_gear_html .= '</div>';
            if (($iter % $cols == $cols - 1) || ($iter == $guest_count - 1)) {
                $guest_html .= '</div>';
                $bike_gear_html .= '</div>';
            }

            $hiking_gear_html .= '<div>';
            $activity_level = tt_get_custom_item_name( 'syncRiderLevels', $activity_levelVal );
            $user_height    = tt_get_custom_item_name( 'syncHeights', $user_height );
            $tshirt_size    = tt_get_custom_item_name( 'syncJerseySizes', $tshirt_size );

            $hiking_gear_html .= '<p class="mb-2 fs-md lh-md fw-medium">' . $guest_label . '</p>';
            $hiking_gear_html .= '<p class="mb-2 fs-md lh-md fw-medium">' . $fname . ' ' . $lname . '</p>';
            $hiking_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Activity Level: ' . $activity_level . '</p>';
            $hiking_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Guest Height: ' . $user_height . '</p>';
            $hiking_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">T-Shirt Size: ' . $tshirt_size . '</p>';
            $hiking_gear_html .= '</div>';
            if (($iter % $cols == $cols - 1) || ($iter == $guest_count - 1)) {
                $guest_html .= '</div>';
                $hiking_gear_html .= '</div>';
            }

        }
    }
    return [
        'guests' => $guest_html,
        'bike_gears' => $bike_gear_html,
        'guest_emails' => $guest_emails,
        'hiking_gears' => $hiking_gear_html,
    ];
}
function tt_get_parent_trip($sku = "") {
    // Trip Parent ID
    $parent_product_id = tt_get_parent_trip_id_by_child_sku($sku);
    $product_image_url = '';
    $parent_trip_link = 'javascript:';

    // Check for product image by SKU first
    if ($sku) {
        $product_id = wc_get_product_id_by_sku($sku);
        if ($product_id && has_post_thumbnail($product_id)) {
            $product_image_url = get_the_post_thumbnail_url($product_id);
        }
    }

    // If no image found for SKU, use the parent product's image
    if (!$product_image_url && $parent_product_id) {
        if (has_post_thumbnail($parent_product_id)) {
            $product_image_url = get_the_post_thumbnail_url($parent_product_id);
        }
        $parent_trip_link = get_the_permalink($parent_product_id);
    }

    // Use default image if no image found
    if (!$product_image_url) {
        $product_image_url = get_template_directory_uri() . '/assets/images/TT-Logo.png';
    }

    return [
        'id' => $parent_product_id,
        'image' => $product_image_url,
        'link' => $parent_trip_link
    ];
}

function tt_get_hotel_bike_list( $sku ) {
    $bike_list   = $hotel_list = '';

    $hotels_data = tt_get_local_trips_detail( 'hotels', '', $sku, true );
    $hotels      = json_decode($hotels_data, true);
    if ( $hotels ) {
        $hotel_list .= '<ol>';
        foreach ( $hotels as $hotel ) {
            $hotel_list .= '<li class="fw-normal fs-sm lh-sm">' . $hotel['hotelName'] . '</li>';
        }
        $hotel_list .= '</ol>';
    }

    $bikes_data   = tt_get_local_trips_detail( 'bikes', '', $sku, true );
    $bikes        = json_decode( $bikes_data, true );
    $bikes_models = [];
    if ( $bikes ) {
        $bike_list .= '<ol>';
        foreach ( $bikes as $bike ) {
            $bike_post_name = tt_validate( $bike['bikeModel']['name'] );
            $bike_type_id   = $bike['bikeType']['id']; // Prevent showing Own Bike and Non-Rider as they don't have a bike type id.
            if ( $bike_type_id && $bike_post_name && ! in_array( $bike_post_name, $bikes_models ) ) {
                $bike_list .= '<li class="fw-normal fs-sm lh-sm">' . $bike_post_name . '</li>';
                $bikes_models[] = $bike_post_name;
            }
        }
        $bike_list .= '</ol>';
    }
    return [
        'bikes' => $bike_list,
        'hotels' => $hotel_list
    ];
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : trek_tt_get_product_data_ajax_action_cb
 **/
add_action('wp_ajax_tt_get_product_data_ajax_action', 'trek_tt_get_product_data_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_get_product_data_ajax_action', 'trek_tt_get_product_data_ajax_action_cb');
function trek_tt_get_product_data_ajax_action_cb()
{
    $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : '';
    $product_info = [
        'product_id' => $product_id,
        'image' => ''
    ];
    if ($product_id && get_post($product_id)) {
        $product_info['image'] = get_the_post_thumbnail_url($product_id, 'full');
    }
    echo json_encode($product_info);
    exit;
}
function tt_compare_trips_html($Ids = [], $is_header = true)
{
    // if ($Ids) {
    //     $tt_compare_products = $Ids;
    // } else {
    //     $tt_compare_products = [];
    //     if (isset($_COOKIE['wc_products_compare_products']) && !empty($_COOKIE['wc_products_compare_products'])) {
    //         $tt_compare_products = explode(',', $_COOKIE['wc_products_compare_products']);
    //     }
    // }
    // $compare_div_style = ($tt_compare_products && count($tt_compare_products) > 0 ? 'display:flex;' : 'display:none;');
    // if ($is_header == true) {
    //     $output = '<div class="sticky-bottom bg-white compare-products-footer-bar" style="' . $compare_div_style . '">
    //                 <p class="fw-bold fs-sm lh-sm mb-0">Compare Trips</p>
    //                 <div id="tt_compare_product">';
    // }
    // if ($tt_compare_products) {
    //     foreach ($tt_compare_products as $tt_compare_product) {
    //         $image_URL = get_the_post_thumbnail_url($tt_compare_product, 'full');
    //         $image_URL = ($image_URL ? $image_URL : DEFAULT_IMG);
    //         $output .= '<div class="compare-product" id="product-' . $tt_compare_product . '">
    //                     <img src="' . $image_URL . '" class="object-fit-cover" alt="" />
    //                     <a href="#" title="Remove" class="remove-compare-page-product" data-remove-id="' . $tt_compare_product . '"><i class="bi bi-x-lg"></i></a>
    //                 </div>';
    //     }
        
        

    // }
    // if ($is_header == true) {
    //     $output .= '</div>
    //             <a href="#" title="Remove all" class="clear-all-link compare-remove-all-products">Clear All</a>
    //             <a href="/products-compare" title="Compare Page" class="woocommerce-products-compare-compare-link btn btn-primary rounded-1">Compare</a>
    //             <i class="bi bi-x-lg remove-all compare-remove-all-products"></i>
    //         </div>';
    // }
    // return $output;
}

// Add CSS class for logged out users
add_filter('body_class', 'er_logged_in_filter');
function er_logged_in_filter($classes)
{
    if (!(is_user_logged_in())) {
        $classes[] = 'logged-out';
    }
    return $classes;
}

function get_trip_link_by_itinerary_id($itinerary_id=''){
    $trip_link = 'javascript:';
    if($itinerary_id){
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'itineraries',
                    'value' => $itinerary_id,
                    'compare' => 'LIKE'
                )
            )
        );
        $tripInfo = new WP_Query($args);
        if($tripInfo->have_posts()){
            while( $tripInfo->have_posts() ){
                $tripInfo->the_post();
                $trip_link = get_the_permalink();
            }
        }
        wp_reset_postdata();
    }
    return $trip_link;
} 
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Edit Emergency Contact [ My account page ]
 **/
add_action('wp_ajax_update_emergency_contact_action', 'trek_update_emergency_contact_action_cb');
add_action('wp_ajax_nopriv_update_emergency_contact_action', 'trek_update_emergency_contact_action_cb');
function trek_update_emergency_contact_action_cb()
{
    $res = array(
        'status' => false,
        'message' => ''
    );
    if (!isset($_REQUEST['edit_user_contact_info_nonce']) || !wp_verify_nonce($_REQUEST['edit_user_contact_info_nonce'], 'edit_contact_info_action')) {
        $res['message'] = "Sorry, your nonce did not verify.";
    } else {
        $user = wp_get_current_user();
        update_user_meta($user->ID, 'custentity_emergencycontactfirstname', $_REQUEST['emergency_contact_first_name']);
        update_user_meta($user->ID, 'custentityemergencycontactlastname', $_REQUEST['emergency_contact_last_name']);
        update_user_meta($user->ID, 'custentity_emergencycontactphonenumber', $_REQUEST['emergency_contact_phone']);
        update_user_meta($user->ID, 'custentity_emergencycontactrelationship', $_REQUEST['emergency_contact_relationship']);
        as_schedule_single_action(time(), 'tt_cron_syn_usermeta_ns', array( $user->ID, '[Contact information]' ));
        $res['status'] = true;
        $res['message'] = "Your Contact information has been changed successfully!";
    }
    echo json_encode($res);
    exit;
}
function tt_get_review_order_html(){
    $review_order_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if (is_readable($review_order)) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    return $review_order_html;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Generate Arc insurance & save into Checkout object 
 **/
add_action( 'wp_ajax_tt_generate_save_insurance_quote', 'tt_generate_save_insurance_quote_cb' );
add_action( 'wp_ajax_nopriv_tt_generate_save_insurance_quote', 'tt_generate_save_insurance_quote_cb' );
add_action( 'wp_ajax_tt_recalculate_travel_protection', 'tt_generate_save_insurance_quote_cb' );
add_action( 'wp_ajax_nopriv_tt_recalculate_travel_protection', 'tt_generate_save_insurance_quote_cb' );
function tt_generate_save_insurance_quote_cb() {
    $accepted_p_ids = tt_get_line_items_product_ids();
    // Add travels data to Cart object.
    // Get the current cart contents.
    $cart = WC()->cart->get_cart_contents();
    // Preparing insurance HTML
    $tt_checkoutData = get_trek_user_checkout_data();
    $tt_posted       = isset($tt_checkoutData['posted']) ? $tt_checkoutData['posted'] : [];
    // Check if the cart contains an already applied coupon and fix the missing coupon code.
    if( WC()->cart->get_applied_coupons() && isset( $tt_posted['coupon_code'] ) && empty( $tt_posted['coupon_code'] ) ) {
        $applied_coupons = WC()->cart->get_applied_coupons();
        $tt_posted['coupon_code'] = $applied_coupons[0];
    }
    $coupon_code     = strtolower( $tt_posted['coupon_code'] );
    $coupon          = new WC_Coupon( $coupon_code );
    $product_id      = null;
    if( isset($tt_posted['product_id']) ){
        $product_id = $tt_posted['product_id'];
    }
    $product = wc_get_product($product_id);
    $individualTripCost = 0;
    $sdate_info = $edate_info = '';

    if( $product ){
        $individualTripCost = $product->get_price();

        $trip_sdate = $product->get_attribute('pa_start-date');
        $sdate_obj = explode('/', $trip_sdate);
        $sdate_info = array(
            'd' => $sdate_obj[0],
            'm' => $sdate_obj[1],
            'y' => substr(date('Y'),0,2).$sdate_obj[2]
        );
        $trip_edate = $product->get_attribute('pa_end-date');
        $edate_obj = explode('/', $trip_edate);
        $edate_info = array(
            'd' => $edate_obj[0],
            'm' => $edate_obj[1],
            'y' => substr(date('Y'),0,2).$edate_obj[2]
        );
    }

    $plan_id = 'TREKTRAVEL23';

    if( ! empty( get_field( 'plan_id', 'option' ) ) ) {
        $plan_id = get_field( 'plan_id', 'option' );
    }

    $insuredPerson = $insuredPerson_single = array();
    $effectiveDate = $expirationDate = '';
    if( $sdate_info && is_array($sdate_info) ){
        $effectiveDate = date('Y-m-d', strtotime(implode('-', $sdate_info)));
    }
    if( $edate_info && is_array($edate_info) ){
        $expirationDate = date('Y-m-d', strtotime(implode('-', $edate_info)));
    }

    //Current date minus 3 hours to match with the Arch time
    $current_date = date('Y-m-d', strtotime('-3 hours' ) );

    $trek_insurance_args = [
        "coverage" => [
            "effectiveDate" => $effectiveDate,
            "expirationDate" => $expirationDate,
            "depositDate" => $current_date,
            "destinations" => [
                [
                    "countryCode" => $tt_posted['shipping_country']
                ]
            ]
        ],
        "language" => "en-us",
        "planID" => $plan_id,
        "returnTravelerQuotes" => true
    ];
    $guest_insurance = isset($tt_posted['trek_guest_insurance']) ? $tt_posted['trek_guest_insurance'] : [];
    $tt_total_insurance_amount = 0;
    $is_travel_protection_count = 0;
    if (isset($guest_insurance) && !empty($guest_insurance)) {
        $singleSupplementPrice = isset($tt_posted['singleSupplementPrice']) ? $tt_posted['singleSupplementPrice'] : 0;

        // Remove dollar sign and commas
        $amount_string = str_replace(array('$', ','), '', $singleSupplementPrice);

        // Convert the string to a float
        $amount_float = (float) $amount_string;

        // Convert to an integer (removing the decimal part)
        $amount_int = (int) $amount_float;
        foreach ($guest_insurance as $guest_insurance_k => $guest_insurance_val) {
            $individualTripCost = $product->get_price();
            $occupants = $tt_posted['occupants'];
            $trek_insurance_args["insuredPerson"] = array();
            $bike_gears = $tt_posted['bike_gears'];
            if ( isset( $tt_posted['bikeUpgradePrice'] ) ) {
                $bike_upgrade_price = (int) $tt_posted['bikeUpgradePrice'];
            } else {
                $bike_upgrade_price =  0;
            }
            if ($guest_insurance_k == 'primary') {
                $primary_guest_bike = $bike_gears['primary'];
                $bike_type_info     = tt_ns_get_bike_type_info( $primary_guest_bike['bikeTypeId'] );
                if ($guest_insurance_val['is_travel_protection'] == 1) {
                    $is_travel_protection_count++;
                }
                if ( ( isset( $occupants['private'] ) && is_array( $occupants['private'] ) && in_array( 0, $occupants['private'] ) )
                || ( isset( $occupants['roommate'] ) && is_array( $occupants['roommate'] ) && in_array( 0, $occupants['roommate'] ) ) ) {
                    $individualTripCost = $individualTripCost + $amount_int;
                }
                if ( $bike_type_info && isset( $bike_type_info['isBikeUpgrade'] ) && $bike_type_info['isBikeUpgrade'] == 1 ) {
                    $individualTripCost = $individualTripCost + $bike_upgrade_price;
                }
                if ( $coupon && tt_is_coupon_applied( $coupon_code ) ) {
                    // Check if the coupon exists
                    if ( $coupon->get_id() > 0 ) {
                        // Coupon exists, retrieve its details
                        $coupon_amount = $coupon->get_amount();
                    }
            
                    $individualTripCost = $individualTripCost - $coupon_amount;
                }
                //if ($guest_insurance_val['is_travel_protection'] == 1) {
                $insuredPerson[] = array(
                    "address" => [
                        "stateAbbreviation" => $tt_posted['shipping_state'],
                        "countryAbbreviation" => $tt_posted['shipping_country']
                    ],
                    "dob" => $tt_posted['custentity_birthdate'],
                    "individualTripCost" => $individualTripCost
                );
                $insuredPerson_single = [];
                $insuredPerson_single[] = array(
                    "address" => [
                        "stateAbbreviation" => $tt_posted['shipping_state'],
                        "countryAbbreviation" => $tt_posted['shipping_country']
                    ],
                    "dob" => $tt_posted['custentity_birthdate'],
                    "individualTripCost" => $individualTripCost
                );
                $trek_insurance_args["insuredPerson"] = $insuredPerson_single;
                $archinsuranceResPP = tt_set_calculate_insurance_fees_api($trek_insurance_args);
                if ( isset( $archinsuranceResPP['responseCode'] ) && ! empty( $archinsuranceResPP['responseCode'] ) && 'InvalidDepositDate' === $archinsuranceResPP['responseCode'] ) {
                    // Try Again with an empty Deposit Date.
                    $trek_insurance_args['coverage']['depositDate'] = '';
                    $archinsuranceResPP = tt_set_calculate_insurance_fees_api( $trek_insurance_args );
                }
                $arcBasePremiumPP = isset($archinsuranceResPP['basePremium']) ? $archinsuranceResPP['basePremium'] : 0;
                $guest_insurance['primary']['basePremium'] = $arcBasePremiumPP;
                if ($guest_insurance_val['is_travel_protection'] == 1) {
                    $tt_total_insurance_amount += $arcBasePremiumPP;
                }
            } else {
                foreach ($guest_insurance_val as $guest_key => $guest_insurance_Data) {
                    $individualTripCost = $product->get_price();
                    $guestInfo          = $tt_posted['guests'][$guest_key];
                    $guest_bike         = $bike_gears['guests'][$guest_key];
                    $bike_type_info     = tt_ns_get_bike_type_info( $guest_bike['bikeTypeId'] );
                    if ($guest_insurance_Data['is_travel_protection'] == 1) {
                        $is_travel_protection_count++;
                    }
                    if ( ( isset( $occupants['private'] ) && is_array( $occupants['private'] ) && in_array( $guest_key, $occupants['private'] ) )
                    || ( isset( $occupants['roommate'] ) && is_array( $occupants['roommate'] ) && in_array( $guest_key, $occupants['roommate'] ) ) ) {
                        $individualTripCost = $individualTripCost + $amount_int;
                    }
                    if ( $bike_type_info && isset( $bike_type_info['isBikeUpgrade'] ) && $bike_type_info['isBikeUpgrade'] == 1 ) {
                        $individualTripCost = $individualTripCost + $bike_upgrade_price;
                    }
                    if ( $coupon && tt_is_coupon_applied( $coupon_code ) ) {
                        // Check if the coupon exists
                        if ( $coupon->get_id() > 0 ) {
                            // Coupon exists, retrieve its details
                            $coupon_amount = $coupon->get_amount();
                        }
                
                        $individualTripCost = $individualTripCost - $coupon_amount;
                    }
                    $insuredPerson[] = array(
                        "address" => [
                            "stateAbbreviation" => $tt_posted['shipping_state'],
                            "countryAbbreviation" => $tt_posted['shipping_country']
                        ],
                        "dob" => $guestInfo['guest_dob'],
                        "individualTripCost" => $individualTripCost
                    );
                    $insuredPerson_single = [];
                    $insuredPerson_single[] = array(
                        "address" => [
                            "stateAbbreviation" => $tt_posted['shipping_state'],
                            "countryAbbreviation" => $tt_posted['shipping_country']
                        ],
                        "dob" => $guestInfo['guest_dob'],
                        "individualTripCost" => $individualTripCost
                    );
                    $trek_insurance_args["insuredPerson"] = $insuredPerson_single;
                    $archinsuranceResPG = tt_set_calculate_insurance_fees_api($trek_insurance_args);
                    if ( isset( $archinsuranceResPG['responseCode'] ) && ! empty( $archinsuranceResPG['responseCode'] ) && 'InvalidDepositDate' === $archinsuranceResPG['responseCode'] ) {
                        // Try Again with an empty Deposit Date.
                        $trek_insurance_args['coverage']['depositDate'] = '';
                        $archinsuranceResPG = tt_set_calculate_insurance_fees_api( $trek_insurance_args );
                    }
                    $arcBasePremiumPG = isset($archinsuranceResPG['basePremium']) ? $archinsuranceResPG['basePremium'] : 0;
                    $guest_insurance['guests'][$guest_key]['basePremium'] = $arcBasePremiumPG;
                    if ($guest_insurance_Data['is_travel_protection'] == 1) {
                        $tt_total_insurance_amount += $arcBasePremiumPG;
                    }
                }
            }
        }
    }
    $trek_insurance_args["insuredPerson"] = $insuredPerson;
    $arcBasePremium                       = $tt_total_insurance_amount && $tt_total_insurance_amount > 0 ? $tt_total_insurance_amount : 0;
    $fees_product_id                      = tt_create_line_item_product( 'TTWP23FEES' );
    // Save cart Logic.
    foreach ( $cart as $cart_item_id => $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && $cart_item['product_id'] == $fees_product_id ) {
            $product             = wc_get_product( $cart_item['product_id'] );
            $sku                 = $product->get_sku();
            if ( 'TTWP23FEES' === $sku ) {
                if ( isset( $cart_item['tt_cart_custom_fees_price'] ) && $cart_item['tt_cart_custom_fees_price'] > 0 ) {
                    $cart_item['data']->set_price( $cart_item['tt_cart_custom_fees_price'] );
                }
            }
            $cart[$cart_item_id]['quantity'] = 1;
        }
        if ( isset( $cart_item['product_id'] )  && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
            $cart_item['trek_user_checkout_data']['trek_guest_insurance']       = $guest_insurance;
            $cart_item['trek_user_checkout_data']['insuredPerson']              = count( $insuredPerson );
            $cart_item['trek_user_checkout_data']['tt_insurance_total_charges'] = $arcBasePremium;
            $cart_item['trek_user_checkout_data']['is_protection_modal_showed'] = true;
            $cart[$cart_item_id]                                                = $cart_item;
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart );
    // Recalculate the totals after modifying the cart.
    WC()->cart->calculate_totals();
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    //End: Save cart logic
    $review_order_html = '';
    $review_order      = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if ( is_readable( $review_order ) ) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $payment_option_html = '';
    $review_order        = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php';
    if ( is_readable( $review_order ) ) {
        $payment_option_html .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-payment-options.php');
    } else {
        $payment_option_html .= '<h3>Step 4</h3><p>Checkout payment option file is missing!</p>';
    }
    $insuredHTMLPopup       = '';
    $checkout_insured_users = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php';
    if ( is_readable( $checkout_insured_users ) ) {
        $insuredHTMLPopup .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php');
    } else {
        $insuredHTMLPopup .= '<h3>Step 4</h3><p>checkout-insured-guests-popup.php form code is missing!</p>';
    }
    $guest_insurance_html          = '';
    $guest_insurance_html_template = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-summary.php';
    if ( is_readable( $guest_insurance_html_template ) ) {
        $guest_insurance_html .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-summary.php');
    } else {
        $guest_insurance_html .= '<h3>Step 4</h3><p>checkout-insured-summary.php form code is missing!</p>';
    }
    $res['status']               = true;
    $res['guest_insurance_html'] = $guest_insurance_html;
    $res['insuredHTMLPopup']     = $insuredHTMLPopup;
    $res['review_order']         = $review_order_html;
    $res['payment_option']       = $payment_option_html;
    $res['message']              = "Your information has been changed successfully!";
    echo json_encode( $res );
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Function which will send User Data to NS forSync on user update action
 **/
add_action('tt_cron_syn_usermeta_ns', 'tt_cron_syn_usermeta_ns_cb',10, 2);
function tt_cron_syn_usermeta_ns_cb( $user_id, $type ) {
    if ( class_exists( 'TMWNI_Loader' ) ) {
        $netsuite_user_client = new TMWNI_Loader();
        $ns_user_id           = $netsuite_user_client->addUpdateNetsuiteCustomer( $user_id );

        // TM NetSuite returns 0 if something fails.
        if( empty( $ns_user_id ) ) {
            tt_add_error_log( 'User update - ' . $type, array( 'user_id' => $user_id ), array( 'status' => false, 'ns_user_id' => $ns_user_id, 'message' => 'TM NetSuite plugin Failed. Check the logs in TM NetSuite::Settings::NetSuite API Logs for more information.', 'dateTime' => date('Y-m-d H:i:s') ) );
        } else {
            // We have NS User ID.
            tt_add_error_log( 'User update - ' . $type, array( 'user_id' => $user_id ), array( 'status' => true, 'ns_user_id' => $ns_user_id, 'dateTime' => date('Y-m-d H:i:s') ) );
        }
    } else {
        tt_add_error_log( 'User update - ' . $type, array( 'user_id' => $user_id ), array( 'status' => false, 'message' => 'The TMWNI_Loader class does not exist. Verify that the TM NetSuite plugin is enabled and that its API configuration is working.' ) );
    }
}
add_action('ns_trips_sync_to_wc_product', 'ns_trips_sync_to_wc_product_cb', 10, 2);
function ns_trips_sync_to_wc_product_cb($is_all = true, $trips_IDs=[]){
    $type = "Sync All Trips (Products)";
    if( $trips_IDs && is_array($trips_IDs) && !empty($trips_IDs) ){
        $type = "Single Trip (Product) Sync";
    }
    tt_sync_wc_products_from_ns($is_all, $trips_IDs);
    tt_add_error_log('[End]', ['type'=> $type], ['dateTime' => date('Y-m-d H:i:s')]);
}
add_action('admin_head', 'tt_product_box_css_cb');
function tt_product_box_css_cb(){
    ?>
    <style>th#ns_last_synced {width: 50px;word-wrap: break-word;}</style>
    <?php
}
//Generate meta box.
add_action( 'add_meta_boxes', 'ns_products_tt_add_custom_box' );
function ns_products_tt_add_custom_box(){
    add_meta_box(
        'tt_ns_product_mbox', 
        'NS Fields',
        'ns_products_tt_add_custom_box_html',
        'product',
        'side'
    );
}
function ns_products_tt_add_custom_box_html( $post ) {
    $synced_datetime = get_post_meta($post->ID, 'ns_last_synced_date_time', true);
	?>
	<strong>Last Synced: </strong>
	<code><?php echo ( $synced_datetime ? $synced_datetime : '-' ); ?></code>
	<?php
}
add_filter('manage_product_posts_columns', 'tt_product_columns_cb');
function tt_product_columns_cb( $columns ) {
    $columns['ns_last_synced']  = 'Last Synced';
    return $columns;

}
add_action( 'manage_product_posts_custom_column', 'tt_product_custom_column_value', 10, 2 );
function tt_product_custom_column_value( $column_name, $post_id ) {
    switch ( $column_name ) {
        case 'ns_last_synced' :
            echo get_post_meta( $post_id , 'ns_last_synced_date_time' , true ); 
            break;
    }
}

function tt_get_jersey_sizes($gender="", $jersey_size=""){
    $opts = "";
    $opts .= '<option value="">Select Jersey Size</option>';
    $itemData = tt_get_custom_item_name('syncJerseySizes');

    // Define the desired size order
    $sizeOrder = [
        'XX-Small',
        'X-Small',
        'Small',
        'Medium',
        'Large',
        'X-Large',
        '2X-Large',
        '3X-Large',
        '4X-Large'
    ];

    if ($itemData && isset($itemData['options']) && $itemData['options']) {
        // Reorder the options based on the size order after processing them
        $itemData['options'] = specific_reorder($itemData['options'], $sizeOrder);

        // Loop through the options and build the <option> list
        foreach ($itemData['options'] as $jerseyOptions) {
            $jersey_Size = isset($jerseyOptions['optionValue']) ? $jerseyOptions['optionValue'] : '';
            $jersey_id = isset($jerseyOptions['optionId']) ? $jerseyOptions['optionId'] : '';
            if (str_contains($jersey_Size, ' ')) {
                $jersey_size_Arr = explode(' ', $jersey_Size);
                $loopGender = isset($jersey_size_Arr[0]) ? $jersey_size_Arr[0] : '';
                $loopSize = isset($jersey_size_Arr[1]) ? $jersey_size_Arr[1] : '';
                $selected = ($jersey_id == $jersey_size ? 'selected' : '');

                if ($gender == "men" && $loopGender == "Men's") {
                    $opts .= '<option value="' . $jersey_id . '" ' . $selected . '>' . $loopSize . '</option>';
                }
                if ($gender == "women" && $loopGender == "Women's") {
                    $opts .= '<option value="' . $jersey_id . '" ' . $selected . '>' . $loopSize . '</option>';
                }
            }
        }
    }

    return $opts;
}

// Helper function to reorder an array in specific order
function specific_reorder($options, $sizeOrder) {
    usort($options, function($a, $b) use ($sizeOrder) {
        // Extract the size (second word in optionValue) from the optionValue
        $sizeA = explode(' ', isset($a['optionValue']) ? $a['optionValue'] : '')[1] ?? '';
        $sizeB = explode(' ', isset($b['optionValue']) ? $b['optionValue'] : '')[1] ?? '';

        // Compare the position of the sizes in the custom size order array
        $posA = array_search($sizeA, $sizeOrder);
        $posB = array_search($sizeB, $sizeOrder);

        return $posA <=> $posB; // Sort based on the position in the sizeOrder array
    });
    
    return $options;
}

/**
 * Take jersey style by given jersey size id.
 * Jersey Style is gender equivalent, man or women.
 *
 * @param int $jersey_size_id Jersey ID from NS.
 *
 * @return string Jersey Style or empty string.
 */
function tt_get_jersey_style( $jersey_size_id = null ) {
    $jersey_style = '';

    $item_data    = tt_get_custom_item_name('syncJerseySizes');

    if( $item_data && isset( $item_data['options'] ) && $item_data['options'] ) {

        foreach( $item_data['options'] as $jersey_options ) {

            $jersey_size = isset( $jersey_options['optionValue'] ) ? $jersey_options['optionValue'] : '';
            $jersey_id   = isset( $jersey_options['optionId'] ) ? $jersey_options['optionId'] : '';

            if( str_contains( $jersey_size, ' ' ) ) {

                if( $jersey_id == $jersey_size_id ) {

                    $jersey_size_arr = explode( ' ', $jersey_size );
                    $gender          = isset( $jersey_size_arr[0] ) ? $jersey_size_arr[0] : '';

                    switch ( $gender ) {
                        case "Men's":
                            $jersey_style = 'men';
                            break;
                        case "Women's":
                            $jersey_style = 'women';
                            break;
                        default:
                            break;
                    }
                    // Exit from loop.
                    break;
                }
            }
        }
    }

    return $jersey_style;
}
add_action('wp_ajax_tt_jersey_change_action', 'trek_tt_jersey_change_action_cb');
add_action('wp_ajax_nopriv_tt_jersey_change_action', 'trek_tt_jersey_change_action_cb');
function trek_tt_jersey_change_action_cb()
{
    $jersey_style = $_REQUEST['jersey_style'];
    $result = tt_get_jersey_sizes($jersey_style);
    echo json_encode(
        [
            'status' => true,
            'opts' => $result
        ]
    );
    exit;
}
/**
 * @author  : Jayash
 * @version : 1.0.0
 * @return  : Updated product max for Comparision
 **/
add_filter( 'woocommerce_products_compare_max_products', 'tt_woocommerce_products_compare_max_products_cb');
function tt_woocommerce_products_compare_max_products_cb(){
    $max_product = 3;
    return $max_product;
}
function tt_get_waiver_status($order_id=""){
    $netSuiteClient = new NetSuiteClient();
    $waiverAccepted = false;
    $user = wp_get_current_user();
    $order_details = trek_get_user_order_info($user->ID, $order_id);
    $guestRegistrationId = isset($order_details[0]['guestRegistrationId']) ? $order_details[0]['guestRegistrationId'] : 0;
    if($guestRegistrationId){
        $guestRegistrationIdArray = array($guestRegistrationId);
        $ns_booking_result = $netSuiteClient->get('1294:2', array('registrationIds' => implode(',', $guestRegistrationIdArray)));
        if( $ns_booking_result && isset($ns_booking_result[0]) ){
            $waiverAccepted = $ns_booking_result[0]->waiverAccepted;
        }
    }
    return $waiverAccepted;
}

/**
 * Take waiver information from NS and generate a waiver link for signing document.
 * Store waiver signed status info into the `guest_bookings` table.
 *
 * @uses NetSuiteClient
 * @uses NS Script 1304 - This script returns in the same time guests and release forms information for the guests in the given booking.
 * @uses NetSuite Integration for WooCommerce - Indirect dependencies,
 * this plugin save in users meta the ns_customer_internal_id value, during users registration.
 * 
 * @param string|int $ns_booking_id The booking ID. We take it from post meta for given order ID with meta key `tt_meta_guest_booking_id`
 * @param string|int $order_id The Order ID.
 * 
 * @return array A waiver document link for the current user and waiver accepted status.
 */
function tt_get_store_waiver_info( $ns_booking_id, $order_id ) {
    $net_suite_client = new NetSuiteClient();
    $user             = wp_get_current_user();
    $ns_user_id       = get_user_meta( $user->ID, 'ns_customer_internal_id', true );
    $waiver_info      = array(
        'waiver_accepted' => false,
        'status'          => false
    );
    if ( $ns_user_id ) {

        if ( $ns_booking_id ) {
            $booking_info          = $net_suite_client->get( GET_BOOKING_SCRIPT_ID, array( 'bookingId' => $ns_booking_id ) );
            $booking_release_forms = isset( $booking_info->releaseForms ) && $booking_info->releaseForms ? $booking_info->releaseForms : [];
            if ( $booking_release_forms ) {
                foreach ( $booking_release_forms as $booking_release_form ) {
                    $guest_id = $booking_release_form->guestId;
                    if ( $guest_id == $ns_user_id ) {
                        $waiver_info['waiver_accepted'] = $booking_release_form->releaseFormAccepted;

                        // Store waiver signed status.
                        global $wpdb;
                        $table_name          = $wpdb->prefix . 'guest_bookings';
                        $user_order_info     = trek_get_user_order_info( $user->ID, isset( $_REQUEST['order_id'] ) ? $_REQUEST['order_id'] : '' );
                        $guest_email_address = isset( $user_order_info[0]['guest_email_address'] ) ? $user_order_info[0]['guest_email_address'] : '';
                        
                        // Collect booking data.
                        $booking_data                  = array();
                        $booking_data['modified_at']   = date('Y-m-d H:i:s');
                        $booking_data['waiver_signed'] = $booking_release_form->releaseFormAccepted == 1 ? 1 : 0;

                        // Create where clause.
                        $where['order_id']             = $order_id;
                        if( $guest_email_address ) {
                            $where['guest_email_address'] = $user->user_email;
                        } else {
                            if( $user->ID ){
                                $where['user_id'] = $user->ID;
                            }
                        }
                        $is_updated                      = $wpdb->update( $table_name, $booking_data, $where );
                        $waiver_info['status']           = $is_updated;
                        $waiver_info['wpdb->last_query'] = $wpdb->last_query;
                        if( $wpdb->last_error ) {
                            $waiver_info['wpdb->last_error'] = $wpdb->last_error;
                        }
                        $waiver_info['booking_data']     = $booking_data;
                        $waiver_info['where']            = $where;
                    }
                }
            }
        } else {
            $waiver_info['message'] = 'ns_booking_id not found.';
        }
    } else {
        $waiver_info['message'] = 'ns_user_id not found.';
    }
    return $waiver_info;
}

/**
 * Take waiver status on the front end via AJAX request.
 * 
 * @param string|int $_POST['ns-booking-id'] The booking ID.
 * 
 * @return array Array with waiver accepted status, and the html for successfully signed of the document. 
 */
function tt_ajax_get_waiver_info(){
    $res = array(
        'waiver_accepted' => false,
        'waiver_signed_html' => '<p class="fw-medium fs-lg lh-lg status-signed">Signed</p><p class="fw-normal fs-sm lh-sm">You\'re all set here!</p>'
    );

    if( isset( $_POST['ns-booking-id'] ) && ! empty( $_POST['ns-booking-id'] ) && isset( $_POST['order-id'] ) && ! empty( $_POST['order-id'] )) {
        $waiver_info        = tt_get_store_waiver_info( $_POST['ns-booking-id'], $_POST['order-id'] );
        $res['waiver_info'] = $waiver_info;
        tt_add_error_log( '[Post Booking] - Sign Waiver', array( 'order_id' => $_POST['order-id'], 'bookingId' => $_POST['ns-booking-id'] ), array( 'waiver_info' => $waiver_info ) );
        if( isset( $waiver_info['waiver_accepted'] ) ) {
            if( $waiver_info['waiver_accepted'] == 1 ) {
                $res['waiver_accepted'] = true;
            }
        }
    }

    echo json_encode($res);
    exit;
}
add_action('wp_ajax_tt_ajax_get_waiver_info_action', 'tt_ajax_get_waiver_info');
add_action('wp_ajax_nopriv_tt_ajax_get_waiver_info_action', 'tt_ajax_get_waiver_info');

add_action('tt_trigger_cron_ns_guest_booking_details', 'tt_trigger_cron_ns_guest_booking_details_cb', 10, 5);
function tt_trigger_cron_ns_guest_booking_details_cb($single_req,$ns_user_id, $wc_user_id, $time_range = DEFAULT_TIME_RANGE, $is_sync_process = false){
    tt_add_error_log('[Start] - Adding Trips', [ 'ns_user_id' => $ns_user_id, 'wc_user_id' => $wc_user_id, 'is_sync_process' => $is_sync_process ], ['dateTime' => date('Y-m-d H:i:s')]);
    tt_ns_guest_booking_details( $single_req, $ns_user_id, $wc_user_id, $time_range, $is_sync_process );
    tt_add_error_log('[End] - Adding Trips', [ 'ns_user_id' => $ns_user_id, 'wc_user_id' => $wc_user_id, 'is_sync_process' => $is_sync_process ], ['dateTime' => date('Y-m-d H:i:s')]);
}
function tt_get_ns_booking_details_by_order( $order_id, $user_info = null ){
    $release_form_id = $booking_id = $waiver_link = "";

    if( ! empty( $user_info ) ) {
        // If we have $user_info as a non-empty argument, take the user object from there.
        $userInfo = (object) $user_info;
    } else {
        // Retrieves the current user object.
        $userInfo = wp_get_current_user();
    }

    $user_order_info = trek_get_user_order_info( $userInfo->ID, $order_id );
    if( isset( $user_order_info[0]['releaseFormId'] ) && $user_order_info[0]['releaseFormId'] ) {
        $release_form_id = $user_order_info[0]['releaseFormId'];
    } else {
        $release_form_id = get_post_meta( $order_id, TT_WC_META_PREFIX . 'releaseFormId', true );
    }
    if( isset( $user_order_info[0] ) && isset( $user_order_info[0]['ns_trip_booking_id'] ) && $user_order_info[0]['ns_trip_booking_id'] ) {
        $booking_id = $user_order_info[0]['ns_trip_booking_id'];
    } else {
        $booking_id = get_post_meta( $order_id, TT_WC_META_PREFIX . 'guest_booking_id', true );
    }
    if( ! empty( $release_form_id ) ) {
        $waiver_link = add_query_arg(
            array(
                'custpage_releaseFormId' => $release_form_id
            ),
            TT_WAIVER_URL
        );
    }
    return array(
        'booking_id'            => $booking_id,
        'releaseFormId'         => $release_form_id,
        'waiver_link'           => $waiver_link,
        'guest_registration_id' => $user_order_info[0]['guestRegistrationId']
    );
}

add_filter( 'woocommerce_registration_error_email_exists', 'woocommerce_registration_error_email_exists_cb');
function woocommerce_registration_error_email_exists_cb($email) {
   return __( 'An account is already registered with your email address. <a href="'. site_url('login') .'" class="showlogin">Please log in.</a>', 'woocommerce' );
}

/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Added Cancellation Policy Page option in > WP Admin > Settings > Reading
 **/
add_action('admin_init', 'tt_add_field_settings_admin_cb');
function tt_add_field_settings_admin_cb(){
    $args = array(
        'type' => 'string', 
        'sanitize_callback' => 'sanitize_text_field',
        'default' => NULL,
    );
    register_setting( 
        'reading',
        'tt_opt_cancellation_policy_page_id',
        $args 
    );
    add_settings_field(
        'tt_opt_cancellation_policy_page_id',
        __('Cancellation Policy Page', 'trek-travel'),
        'tt_opt_cancellation_policy_page_callback',
        'reading',
        'default',
        array( 'label_for' => 'tt_opt_cancellation_policy_page_id' )
    );
}
function tt_opt_cancellation_policy_page_callback(){
    $page_id = get_option('tt_opt_cancellation_policy_page_id');
    $args = array(
        'posts_per_page'   => -1,
        'orderby'          => 'name',
        'order'            => 'ASC',
        'post_type'        => 'page',
    );
    $items = get_posts( $args );
    $tt_pages = '<select id="tt_opt_cancellation_policy_page_id" name="tt_opt_cancellation_policy_page_id">';
    $tt_pages .= '<option value="0">'.__('— Select —', 'trek-travel').'</option>';
    foreach($items as $item) {
        $selected = ($page_id == $item->ID) ? 'selected="selected"' : '';
        $tt_pages .= '<option value="'.$item->ID.'" '.$selected.'>'.$item->post_title.'</option>';
    }
    $tt_pages .= '</select>';
    echo $tt_pages;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get payment Type Deposite/Full using NS field $depositBeforeDate
 **/
function tt_get_trip_payment_mode($depositBeforeDate=''){
    $pay_amount = false;
    if( $depositBeforeDate ){
        $tt_today = strtotime(date('m/d/Y'));
        $diff = strtotime($depositBeforeDate) - $tt_today;
        $days = floor($diff / (60 * 60 * 24));
        if( $days >= 1 ){
            $pay_amount = true;
        }
    }
    return $pay_amount;
}
add_action( 'woocommerce_checkout_order_processed', 'tt_woocommerce_checkout_update_order_meta_cb', 10, 3 );
function tt_woocommerce_checkout_update_order_meta_cb($order_id, $posted_data, $order){
    $guest_emails = [];
    $order_info = tt_get_trip_pid_sku_by_orderId($order_id);
    if( $order_info && isset($order_info['guest_emails']) && is_array($order_info['guest_emails']) ){
        $guest_emails = array_filter($order_info['guest_emails']);
    }
    update_post_meta($order_id, 'tt_wc_order_trip_user_emails', $guest_emails);
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Mailing address append Ajax
 **/
add_action('wp_ajax_tt_ajax_mailing_address_action', 'tt_ajax_mailing_address_action_cb');
add_action('wp_ajax_nopriv_tt_ajax_mailing_address_action', 'tt_ajax_mailing_address_action_cb');
function tt_ajax_mailing_address_action_cb() {
    $res['status']         = true;
    $tt_checkoutData       =  get_trek_user_checkout_data();
    $tt_posted             = isset($tt_checkoutData['posted']) ? $tt_checkoutData['posted'] : [];
    $primary_address_1     = isset($tt_posted['shipping_address_1']) ? $tt_posted['shipping_address_1'] : '';
    $primary_address_2     = isset($tt_posted['shipping_address_2']) ? $tt_posted['shipping_address_2'] : '';
    $primary_country       = isset($tt_posted['shipping_country']) ? $tt_posted['shipping_country'] : '';
    $shipping_fname        = isset($tt_posted['shipping_first_name']) ? $tt_posted['shipping_first_name']  :'';
    $shipping_lname        = isset($tt_posted['shipping_last_name']) ? $tt_posted['shipping_last_name']  :'';
    $shipping_name         = $shipping_fname.' '.$shipping_lname; 
    $shipping_postcode     = isset($tt_posted['shipping_postcode']) ? $tt_posted['shipping_postcode']  :'';
    $shipping_state        = isset($tt_posted['shipping_state']) ? $tt_posted['shipping_state']  :'';
    $shipping_city         = isset($tt_posted['shipping_city']) ? $tt_posted['shipping_city']  :'';
    $guest_details_states  = WC()->countries->get_states( $primary_country );
    $shipping_state_name   = isset( $guest_details_states[$shipping_state] ) ? $guest_details_states[$shipping_state] : $shipping_state;
    $shipping_country_name = WC()->countries->countries[$primary_country];
    $output                     = '<p class="mb-0">'.$shipping_name.'</p>
        <p class="mb-0">'.$primary_address_1.'</p>
        <p class="mb-0">'.$primary_address_2.'</p>
        <p class="mb-0">'.$shipping_city.', '.$shipping_state_name.', '.$shipping_postcode.'</p>
        <p class="mb-0">'.$shipping_country_name.'</p>
        <p class="mb-0"></p>';
    $res['address'] = $output;
    echo json_encode($res);    
    exit;
}
function tt_get_upgrade_qty($tt_posted){
    $bike_upgrade_qty = 0;
    if ( $tt_posted && isset($tt_posted['bike_gears']) && $tt_posted['bike_gears']) {
        foreach ($tt_posted['bike_gears'] as $bike_gear_type => $bike_gear) {
            if ($bike_gear_type == 'primary') {
                $bike_type_id = isset($bike_gear['bikeTypeId']) ? $bike_gear['bikeTypeId'] : '';
                if ($bike_type_id) {
                    $bikeTypeInfo = tt_ns_get_bike_type_info($bike_type_id);
                    if ($bikeTypeInfo && isset($bikeTypeInfo['isBikeUpgrade']) && $bikeTypeInfo['isBikeUpgrade'] == 1) {
                        $bike_upgrade_qty++;
                    }
                }
            } else {
                if ($bike_gear) {
                    foreach ($bike_gear as $guest_key => $guestData) {
                        $bike_type_id = isset($guestData['bikeTypeId']) ? $guestData['bikeTypeId'] : '';
                        if ($bike_type_id) {
                            $bikeTypeInfo = tt_ns_get_bike_type_info($bike_type_id);
                            if ($bikeTypeInfo && isset($bikeTypeInfo['isBikeUpgrade']) && $bikeTypeInfo['isBikeUpgrade'] == 1) {
                                $bike_upgrade_qty++;
                            }
                        }
                    }
                }
            }
        }
    }
    return $bike_upgrade_qty;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Mailing address append Ajax
 **/
add_action('wp_ajax_tt_display_order_emails_action', 'tt_display_order_emails_action_cb');
add_action('wp_ajax_nopriv_tt_display_order_emails_action', 'tt_display_order_emails_action_cb');
function tt_display_order_emails_action_cb()
{
    $order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
    $guest_emails = [];
    $res['status'] = false;
    if( $order_id ){
        $guest_emails = trek_get_guest_emails($order_id);
    }
    if( $guest_emails ){
        $res['guest_emails'] = $guest_emails;
        $res['status'] = true;
    }
    echo json_encode($res);    
    exit;
}
function tt_get_insurance_info($tt_posted){
    $guest_insurance = isset($tt_posted['trek_guest_insurance']) ? $tt_posted['trek_guest_insurance'] : [];
    $is_travel_protection_count = 0;
    $tt_total_insurance_amount = 0;
    if (isset($guest_insurance) && !empty($guest_insurance)) {
        foreach ($guest_insurance as $guest_insurance_k => $guest_insurance_val) {
            $trek_insurance_args["insuredPerson"] = array();
            if ($guest_insurance_k == 'primary') {
                if ($guest_insurance_val['is_travel_protection'] == 1) {
                    $is_travel_protection_count++;
                }
                $arcBasePremiumPP = isset($guest_insurance_val['basePremium']) ? $guest_insurance_val['basePremium'] : 0;
                if ($guest_insurance_val['is_travel_protection'] == 1) {
                    $tt_total_insurance_amount += $arcBasePremiumPP ;
                }
            } else {
                foreach ($guest_insurance_val as $guest_key => $guest_insurance_Data) {
                    if ($guest_insurance_Data['is_travel_protection'] == 1) {
                        $is_travel_protection_count++;
                    }
                    $arcBasePremiumPG = isset($guest_insurance_Data['basePremium']) ? $guest_insurance_Data['basePremium'] : 0;
                    if ($guest_insurance_Data['is_travel_protection'] == 1) {
                        $tt_total_insurance_amount += $arcBasePremiumPG ;
                    }
                    
                }
            }
        }
    }
    $insuredPersonCount = $is_travel_protection_count;
    $arcBasePremium = $tt_total_insurance_amount && $tt_total_insurance_amount > 0 ? $tt_total_insurance_amount : 0;
    return [
        'count' => $insuredPersonCount,
        'amount' => $arcBasePremium
    ];
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get cart products IDs
 **/
function tt_get_cart_products_ids(){
    $products_ids_array = array();
    $custom_fees_p_ids = tt_get_line_items_product_ids();
    foreach( WC()->cart->get_cart() as $cart_item ){
        $product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
        if( $custom_fees_p_ids && !in_array($product_id, $custom_fees_p_ids) ){
            $products_ids_array[] = $product_id;
        }
    }
    $products_ids_array = array_filter($products_ids_array);
    $cart_total_products = count($products_ids_array);
    if( $cart_total_products == 0 ){
        return true;
    }else{
        return false;
    }
}
//add_action('init', 'drms');
function drms(){
    if( $_REQUEST['drms'] == 1 ){
        // $dr = tt_get_trip_pid_sku_by_orderId(74425); 
        // pr($dr['guest_emails']);
        // pr($dr); exit;
        $netSuiteClient = new NetSuiteClient();
        $trip_Ids_arr = ['31592'];
        $trek_script_args = array('tripIds' => implode(',', $trip_Ids_arr) );
        $trek_trips = $netSuiteClient->get(TRIP_DETAIL_SCRIPT_ID, $trek_script_args);
        pr($trek_trips); exit;
    }
}

add_filter("gform_address_types", "ca_address", 10, 2);
function ca_address($address_types, $form_id){

    $canadian_provinces = array(
		['value' => 'AB', 'text' => 'Alberta'],
		['value' => 'BC', 'text' => 'British Columbia'],
		['value' => 'MB', 'text' => 'Manitoba'],
		['value' => 'NB', 'text' => 'New Brunswick'],
		['value' => 'NF', 'text' => 'Newfoundland'],
		['value' => 'NT', 'text' => 'Northwest Territories'],
		['value' => 'NS', 'text' => 'Nova Scotia'],
		['value' => 'NU', 'text' => 'Nunavut'],
		['value' => 'ON', 'text' => 'Ontario'],
		['value' => 'PE', 'text' => 'Prince Edward Island'],
		['value' => 'QC', 'text' => 'Quebec'],
		['value' => 'SK', 'text' => 'Saskatchewan'],
		['value' => 'YT', 'text' => 'Yukon'],
	);

    $address_types["ca"] = array(
        "label" => "CA",
        "country" => "Canada",
        "zip_label" => "Postcode",
        "state_label" => "County",
        "states" => $canadian_provinces
    );
    return $address_types;
}
add_action('wp_login', 'tt_remove_persistent_cart_cb', 8, 2);
//add_action('init', 'tt_remove_persistent_cart_cb', 999);
function tt_remove_persistent_cart_cb($user_login, $user){
        wc_update_user_last_active( $user->ID );
	    update_user_meta( $user->ID, '_woocommerce_load_saved_cart_after_login', 0 );
        $guest_cart = WC()->session->get('cart', null);
        if( !empty($guest_cart) && $guest_cart ){
            //add_filter( 'woocommerce_persistent_cart_enabled', '__return_false' );
            update_user_meta(
                $user->ID,
                '_woocommerce_persistent_cart_' . get_current_blog_id(),
                array()
            );
        }
}
add_filter( 'woocommerce_checkout_redirect_empty_cart', '__return_false' );
add_action( 'woocommerce_customer_save_address', 'tt_woocommerce_customer_save_address_cb', 10, 2 );
function tt_woocommerce_customer_save_address_cb($user_id, $load_address){
    as_schedule_single_action(time(), 'tt_cron_syn_usermeta_ns', array( $user_id, '[Save Address]' ));
}
add_action( 'woocommerce_save_account_details', 'tt_woocommerce_save_account_details_cb', 10, 1 );
function tt_woocommerce_save_account_details_cb($user_id){
    as_schedule_single_action(time(), 'tt_cron_syn_usermeta_ns', array( $user_id, '[Save account details]' ));
}
/**
 * Change the default state and country on the checkout page
 */
add_filter( 'default_checkout_billing_country', 'tt_default_checkout_billing_country_state_cb' );
add_filter( 'default_checkout_shipping_country', 'tt_default_checkout_billing_country_state_cb' );
function tt_default_checkout_billing_country_state_cb() {
  return '';
}

// Function to calculate total tax for the products in the cart
function calculate_cart_total_tax( $cart ) {
    $total_tax = 0;
    
    // Get the first product in the cart
    $first_cart_item = reset( $cart->get_cart() );
    if ( $first_cart_item ) {
        $product_id              = $first_cart_item['product_id'];
        $product_tax_rate        = floatval( get_post_meta( $product_id, 'tt_meta_taxRate', true ) );
        $first_product_price     = get_post_meta( $product_id, '_price', true );
        $first_product_price     = str_replace( ',', '', $first_product_price );
        $single_supplement_price = floatval( get_post_meta( $product_id, 'tt_meta_singleSupplementPrice', true ) );
        $bike_upgrade_price      = floatval( get_post_meta( $product_id, 'tt_meta_bikeUpgradePrice', true ) );
        $discount_total          = $cart->get_cart_discount_total();
        if ( isset( $discount_total ) && ! empty( $discount_total ) && 0 < $discount_total ) {
            $first_product_price = floatval( $first_product_price ) - floatval( $discount_total );
        }
        
        if ( $product_tax_rate ) {
            $total_tax     = 0;
            $first_product = false;
            foreach ( $cart->get_cart() as $cart_item ) {
                $item_id            = $cart_item['product_id'];
                $product_tax_status = get_post_meta( $item_id, '_tax_status', true );
                if ( 'taxable' === $product_tax_status ) {
                    $product_price = $cart_item['data']->get_price();
                    if ( 73798 === $item_id ) {
                        $product_price = $single_supplement_price;
                    }
                    if ( 78972 === $item_id ) {
                        $product_price = $bike_upgrade_price;
                    }
                    if ( $product_id === $item_id & $first_product === false ) {
                        $first_product = true;
                        $product_price = $first_product_price;
                    }
                    $product_quantity = $cart_item['quantity'];
                    $product_tax      = ($product_tax_rate / 100) * $product_price * $product_quantity;
                    $total_tax       += $product_tax;
                }
            }
        }
    }

    // Set the updated total tax for the cart
    $cart->set_cart_contents_taxes(array('total' => $total_tax));

    return $total_tax;
}

// Save the calculated tax as custom meta in the order
add_action( 'woocommerce_checkout_create_order', 'save_custom_order_meta_tt_order_tax', 10, 2 );
function save_custom_order_meta_tt_order_tax( $order, $data ) {
    $cart      = WC()->cart;
    $total_tax = calculate_cart_total_tax( $cart );

    // Round the total tax to two decimal places
    $total_tax = round( $total_tax, 2 );

    // Save the calculated tax in custom meta field 'tt_order_tax'
    $order->update_meta_data( 'tt_order_tax', $total_tax );
}

// Recalculate the tax when cart totals are calculated
add_action( 'woocommerce_calculate_totals', 'recalculate_tax_on_cart_update', 10, 1 );
function recalculate_tax_on_cart_update( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    // Remove previous tax and add the updated tax
    $cart->remove_taxes();
}

/**
 * Filter to adjust the cart subtotal.
 *
 * @param int|float $cart_total The Cart total.
 * @param WC_Cart   $cart Reference to cart object.
 */
function update_cart_subtotal( $cart_total, $cart ) {
    $cart      = WC()->cart;
    $total_tax = calculate_cart_total_tax( $cart );

    $cart_total = floatval( $cart->cart_contents_total );

    // Add the calculated tax to the cart subtotal.
    $cart_total += $total_tax;

    $trek_user_checkout_data = get_trek_user_checkout_data();
    $tt_posted               = $trek_user_checkout_data['posted'];
    $tt_coupon_code          = ( isset( $tt_posted['coupon_code'] ) && $tt_posted['coupon_code'] ? $tt_posted['coupon_code'] : '' );
    // Check if the cart contains an already applied coupon and fix the missing coupon code.
    if( WC()->cart->get_applied_coupons() && isset( $tt_coupon_code ) && empty( $tt_coupon_code ) ) {
        $applied_coupons = WC()->cart->get_applied_coupons();
        $tt_coupon_code  = $applied_coupons[0];
    }
    $no_of_guests = isset( $tt_posted['no_of_guests'] ) ? $tt_posted['no_of_guests'] : 1;
    if ( ! $cart->is_empty() ) {
        // Get the cart items.
        $cart_items = $cart->get_cart();
    
        // Get the first cart item.
        $first_cart_item = reset( $cart_items );
    
        // Access the SKU of the first item.
        $sku                   = $first_cart_item['data']->get_sku();
        $pay_amount            = isset( $tt_posted['pay_amount'] ) ? $tt_posted['pay_amount'] : '';
        $deposit_amount        = tt_get_local_trips_detail( 'depositAmount', '', $sku, true );
        $trek_guests_insurance = $tt_posted['trek_guest_insurance'];
        $insuarance_amount     = 0;
        $primary_insuarance    = $trek_guests_insurance['primary'];
        if ( '1' == $primary_insuarance['is_travel_protection'] ) {
            $insuarance_amount += floatval( $primary_insuarance['basePremium'] );
        }
        if ( ! empty ( $trek_guests_insurance['guests'] ) ) {
            foreach ( $trek_guests_insurance['guests'] as $trek_guest_insurance ) {
                if ( 1 == $trek_guest_insurance['is_travel_protection'] ) {
                    $insuarance_amount += floatval( $trek_guest_insurance['basePremium'] );
                }
            }
        }
    }
    
    if ( ! empty( $tt_coupon_code ) && tt_is_coupon_applied( $tt_coupon_code ) ) {
        $coupon = new WC_Coupon( $tt_coupon_code );

        // Check if the coupon is valid
        if ( $coupon->is_valid() ) {
            $coupon_amount = $coupon->get_amount();
            $coupon_amount = floatval( $coupon_amount );
        }
        if ( 1 < $no_of_guests ) {
            $cart_total -= ( $no_of_guests - 1 ) * $coupon_amount;
        }
    }

    $accepted_p_ids = tt_get_line_items_product_ids();
    // Get the current cart contents.
    $cart_contents = WC()->cart->get_cart_contents();
    foreach ( $cart_contents as $cart_item_id => $cart_item ) {
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            // Take the Cart total for the full amount only we need to keep it for reference and calculations.
            $cart_item['trek_user_checkout_data']['cart_total_full_amount'] = $cart_total;
            $cart_contents[$cart_item_id]                                   = $cart_item;
        }
    }

    // Store the updated cart.
    WC()->cart->set_cart_contents( $cart_contents );
    // Save the updated cart to the session.
    WC()->cart->set_session();
    // Update persistent_cart.
    WC()->cart->persistent_cart_update();

    // Adjust the cart total if we choose to pay only the deposit. Deposit eligible trips with travel protection should charge deposit amount + the travel protection amount.
    if ( isset( $tt_posted['pay_amount'] ) ) {
        if ( 'deposite' === $pay_amount ) {
            $cart_total = ( $no_of_guests * floatval( $deposit_amount ) ) + $insuarance_amount;
        }
    }

    return $cart_total;
}
add_filter( 'woocommerce_calculated_total', 'update_cart_subtotal', 10, 2 );

add_action('woocommerce_admin_order_totals_after_tax', 'add_custom_line_before_tax');
function add_custom_line_before_tax() {
    global $post;        

    // Check if it's an order edit page
    if ( get_post_type( $post ) === 'shop_order' ) {
        $order_id                     = $post->ID;
        $order                        = wc_get_order($order_id);
        $trek_user_checkout_data      = get_post_meta( $order_id, 'trek_user_checkout_data', true);
        $pay_amount                   = isset( $trek_user_checkout_data['pay_amount'] ) ? $trek_user_checkout_data['pay_amount'] : '';
        $is_order_transaction_deposit = get_post_meta( $order_id, '_is_order_transaction_deposit', true );
        $applied_coupons              = $order->get_coupon_codes();
        $first_item                   = $order->get_items();
        if ( ! empty( $first_item ) ) {
            $first_item    = reset( $first_item );
            $fisrt_product = $first_item->get_product();
            if ( $fisrt_product ) {
                $fisrt_product_sku = $fisrt_product->get_sku();
            }
    
            // Get the quantity of the first product
            $first_item_quantity = $first_item->get_quantity();
        }

        foreach ( $applied_coupons as $coupon_code ) {
            $coupon = new WC_Coupon( $coupon_code );

            // Check if the coupon is valid
            if ( $coupon->is_valid() ) {
                $coupon_amount = $coupon->get_amount();
                $coupon_amount = floatval( $coupon_amount );
            }
        }
        if ( 1 < $first_item_quantity ) {
            $additional_discount = ( $first_item_quantity - 1 ) * $coupon_amount;
        }
        ?>
        <?php if ( 1 < $first_item_quantity && 0 < $additional_discount ) : ?>
            <tr>
                <td class="label"><?php _e('Secondary Guests Discount:', 'trek-travel-theme'); ?></td>
                <td width="1%"></td>
                <td class="total">
                    <span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">- </span><?php echo wc_price( $additional_discount ); ?></bdi></span>
                </td>
            </tr>
        <?php endif; ?>
        <?php if ( 'deposite' === $pay_amount && '1' === $is_order_transaction_deposit ) : ?>
            <?php
            $deposit_amount         = tt_get_local_trips_detail( 'depositAmount', '', $fisrt_product_sku, true );
            $cart_total_full_amount = isset( $trek_user_checkout_data['cart_total_full_amount'] ) ? $trek_user_checkout_data['cart_total_full_amount'] : '';
            $remaining_due          = floatval( $cart_total_full_amount ) - floatval( $order->get_total() );
            if ( $first_item_quantity ) {
                $deposit_amount = ( $first_item_quantity ) * floatval( $deposit_amount ); // + travel protection
            }
            ?>
            <tr>
                <td class="label"><?php _e('Deposit Amount:', 'trek-travel-theme'); ?></td>
                <td width="1%"></td>
                <td class="total">
                    <span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol"></span><?php echo wc_price( $deposit_amount ); ?></bdi></span>
                </td>
            </tr>
            <tr>
                <td class="label" style="font-weight: 700;"><?php _e('Order Total Full Amount:', 'trek-travel-theme'); ?></td>
                <td width="1%"></td>
                <td class="total">
                    <span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol"></span><?php echo wc_price( $cart_total_full_amount ); ?></bdi></span>
                </td>
            </tr>
            <tr>
                <td class="label"><?php _e('Remaining Due:', 'trek-travel-theme'); ?></td>
                <td width="1%"></td>
                <td class="total">
                    <span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol"></span><?php echo wc_price( $remaining_due ); ?></bdi></span>
                </td>
            </tr>
        <?php endif; ?>
        <?php
    }
}

// Display the updated total tax in the template
add_action( 'woocommerce_review_order_before_shipping', 'display_total_tax' );
function display_total_tax() {
    $cart      = WC()->cart;
    $total_tax = calculate_cart_total_tax( $cart );

    echo '<p class="mb-0 fw-bold fs-lg">' . wc_price( $total_tax ) . '</p>';
}

/**
 * Send referral info from form on last step from checkout.
 * Using Netsuite client and NS script with ID 1475:2 - REFERRAL_SOURCE_SCRIPT_ID.
 * 
 * @param array $ns_referral_args Array with required key/values: bookingId, referralSourceType and referralSourceName.
 * 
 * @return object of type stdClass.
 */
function tt_send_referral_info_to_ns( $ns_referral_args )
{
    // Cerate NS client.
    $netSuiteClient = new NetSuiteClient();

    // Make post request to NS with JSON object with required fields.
    $ns_referral_info_response = $netSuiteClient->post(REFERRAL_SOURCE_SCRIPT_ID, json_encode($ns_referral_args));

    // Log result.
    tt_add_error_log('[NetSuiteScript-1475:2] - Post referral', $ns_referral_args, $ns_referral_info_response);

    // Return result with response from NS Script execution.
    return $ns_referral_info_response;
}

/**
 * Catch GF Submissions, check for the form on last step from checkout
 * and send referral info to NetSuite via NS script 1475:2.
 * 
 * The ID for this form will be stored in ACF fields
 * in option with id - 'order_details_page_form_id'
 */
function tt_after_submission_referral_form( $entry, $form ) {
    // Take Form ID for referral info form, from ACF Field in Options page.
    $form_id = get_field( 'order_details_page_form_id', 'option' );

    // If this is the referral form need to send info to NetSuite.
    if($form_id == $entry['form_id']){
        global $wp;

        $order_id  = isset($wp->query_vars['order-received']) ? absint( $wp->query_vars['order-received'] ) : '';

        $ns_referral_args = array();

        // Access the entry by looping through the form fields.
        foreach ( $form['fields'] as $index => $field ) {
            $inputs = $field->get_entry_inputs();
            if ( ! is_array( $inputs ) ) {
                $value = rgar( $entry, (string) $field->id );
                
                switch ($index) {
                    case 0:
                        // The First field is a dropdown/options and hold information for referralSourceType.
                        $ns_referral_args['referralSourceType'] = $value;
                        break;
                    case 1:
                        // The Second field is a input field type text and hold information for referralSourceName.
                        $ns_referral_args['referralSourceName'] = $value;
                        break;
                    
                    default:
                        // Nothing...
                        break;
                }
            }
        }

        // Save Referral info in `wp_postmeta` table for post_id = order_id and meta_key = tt_meta_referral_info.
        update_post_meta( $order_id, TT_WC_META_PREFIX . 'referral_info', maybe_serialize( $ns_referral_args ) );

    }
}
add_action( 'gform_after_submission', 'tt_after_submission_referral_form', 10, 2 );

function dx_get_current_user_bike_preferences_cb() {
    $current_user = wp_get_current_user();
    $user_id      = $current_user->ID;

    // Get post meta for the current user
    $gear_preferences_bike_type     = get_user_meta( $user_id, 'gear_preferences_bike_type', true );
    $gear_preferences_rider_height  = get_user_meta( $user_id, 'gear_preferences_rider_height', true );
    $gear_preferences_select_pedals = get_user_meta( $user_id, 'gear_preferences_select_pedals', true );
    $gear_preferences_helmet_size   = get_user_meta( $user_id, 'gear_preferences_helmet_size', true );
    $gear_preferences_jersey_style  = get_user_meta( $user_id, 'gear_preferences_jersey_style', true );
    $gear_preferences_jersey_size   = get_user_meta( $user_id, 'gear_preferences_jersey_size', true );

    // Prepare response
    $response = array(
        'gear_preferences_bike_type'     => $gear_preferences_bike_type,
        'gear_preferences_rider_height'  => $gear_preferences_rider_height,
        'gear_preferences_select_pedals' => $gear_preferences_select_pedals,
        'gear_preferences_helmet_size'   => $gear_preferences_helmet_size,
        'gear_preferences_jersey_style'  => $gear_preferences_jersey_style,
        'gear_preferences_jersey_size'   => $gear_preferences_jersey_size,
    );

    // Send JSON response
	wp_send_json($response);
}
add_action( 'wp_ajax_dx_get_current_user_bike_preferences', 'dx_get_current_user_bike_preferences_cb' );
add_action( 'wp_ajax_nopriv_dx_get_current_user_bike_preferences', 'dx_get_current_user_bike_preferences_cb' );

/**
 * Check for already started bookings,
 * that you have in the cart and see if they are out of date to remove them.
 *
 * For unavailable trips will be considered a trips with status "Remove from Stella" and
 * trips with specific status from woocommerce.
 *
 * This function will be used in the Checkout Form Template located in
 * /trek-travel-theme/woocommerce/checkout/form-checkout.php
 */
function tt_check_and_remove_old_trips_in_persistent_cart_cb() {

	if ( apply_filters( 'tt_is_persistent_cart', true ) ) {
        if ( ! apply_filters( 'tt_is_persistent_cart_valid', true ) ) {
            // Clear the cart if it is not valid based on the datetime expiration.
            do_action( 'tt_clear_persistent_cart' );
        }

        // We have started trip alredy. Now check if is out of date.
        $product_id = ''; // Something like this  ( int )  85028.

        // Line item product IDs.
        $accepted_p_ids = tt_get_line_items_product_ids();

        // Get the cart.
	    $cart = WC()->session->get( 'cart', null );

        foreach ( $cart as $cart_item ) {
            if ( $cart_item ) {
                if( isset( $cart_item['product_id'] ) && ! empty( $cart_item['product_id'] ) && ! in_array( $cart_item['product_id'], $accepted_p_ids ) ) {
                    // Take the ID of the trip product.
                    $product_id = $cart_item['product_id'];
                }
            }
        }

        $product = wc_get_product( $product_id );

        // Check for WC_Product existing.
        if ( ! $product ) {
            return;
        }

        // Trip Code: For example 24MAR0512.
        $sku = $product->get_sku();

        // Trip Status: Limited Availability, Sold Out, Group Hold, Sales Hold or Hold
        $trip_status = tt_get_custom_product_tax_value( $product_id, 'trip-status', true );

        // Remove from stela status.
        $remove_from_stella = tt_get_local_trips_detail( 'removeFromStella', '', $sku, true );

        // Statuses that lock trip for booking.
        $in_status = [
            // "Limited Availability",
            "Sold Out",
            "Group Hold",
            "Sales Hold",
            "Hold"
        ];

        if ( in_array( $trip_status , $in_status ) || $remove_from_stella == true ) {
            // Trip not available for booking already. Need to remove it from the cart.
            do_action( 'tt_clear_persistent_cart' );
        }

        // The trip can stay in the cart.
    }

    // There is no trip in the cart.
}
add_action( 'tt_check_and_remove_old_trips_in_persistent_cart', 'tt_check_and_remove_old_trips_in_persistent_cart_cb' );

/**
 * Function to get real local Trip Code (SKU)
 *
 * Since for the Ride Camp integration we use additional products
 * for the half periods to which we add a suffix (-FIRST, -SECOND),
 * when we need to get information about the amount of bikes, hotels, etc.,
 * we need to make a reference to the main product that stores 
 * this information in the trip details table in DB.
 * 
 * @param string $trip_code Trip Code or SKU like this 24CARC0122 or 24NC0908-3 or 24CARC0122-FIRST
 * 
 * @return string A modified trip code that is without the suffix, if any.
 */
function tt_get_local_trip_code( $trip_code ) {

    if( is_string( $trip_code ) ) {
        
        $trip_code_parts = explode( '-', $trip_code );

        // Take the base of SKU if is it with suffix like this 24CARC0122-FIRST or 24CARC0122-SECOND.
        if( ! empty( $trip_code_parts[1] ) && ( 'FIRST' === $trip_code_parts[1] || 'SECOND' === $trip_code_parts[1] ) ) {
            $trip_code = $trip_code_parts[0];
        }
    }

    return $trip_code;
}

/**
 * Take user preferences, saved in to user post meta,
 * for given user.
 *
 * @param int $user_id User ID.
 *
 * @return null|array Return null if user ID not presented or array with the user preferences data.
 */
function dx_get_user_pb_preferences( $user_id = 0 ) {
    // Check for empty user ID.
    if( empty( $user_id ) ) {
        return null;
    }

    // Get post meta for the given user.
    $user_pb_preferences = array();

    // Get Medical Information.
    $user_pb_preferences['med_info_medications']          = get_user_meta( $user_id, 'custentity_medications', true ); // Value of meta data field | false | empty string.
    $user_pb_preferences['med_info_medical_conditions']   = get_user_meta( $user_id, 'custentity_medicalconditions', true );
    $user_pb_preferences['med_info_allergies']            = get_user_meta( $user_id, 'custentity_allergies', true );
    $user_pb_preferences['med_info_dietary_restrictions'] = get_user_meta( $user_id, 'custentity_dietaryrestrictions', true );

    // Get Emergency Contact.
    $user_pb_preferences['em_info_em_contact_firstname']    = get_user_meta( $user_id, 'custentity_emergencycontactfirstname', true );
    $user_pb_preferences['em_info_em_contact_lastname']     = get_user_meta( $user_id, 'custentityemergencycontactlastname', true );
    $user_pb_preferences['em_info_em_contact_phonenumber']  = get_user_meta( $user_id, 'custentity_emergencycontactphonenumber', true );
    $user_pb_preferences['em_info_em_contact_relationship'] = get_user_meta( $user_id, 'custentity_emergencycontactrelationship', true );

    // Return user preferences.
    return $user_pb_preferences;
}

// Add a filter to change the thumbnail size
add_filter('rp4wp_thumbnail_size', 'tt_custom_thumbnail_size');

function tt_custom_thumbnail_size($thumbnail_size) {
    // Change the thumbnail size to your desired size
    $thumbnail_size = 'medium'; // Change 'your_custom_size' to the size you want
    return $thumbnail_size;
}

/**
 * Function to take the lowest price
 * from available trips in the Grouped product.
 *
 * @param int|string $id The Grouped product ID.
 *
 * @return string|boolean The lowest price Or False if there is no ID given.
 */
function tt_get_lowest_starting_from_price( $id = 0 ) {
    if( empty( $id ) ) {
        return false;
    }
    
    $start_price     = 0;
    $grouped_product = wc_get_product( $id );

    if( $grouped_product ) {
        $linked_products  = $grouped_product->get_children();
        $child_products   = get_child_products( $linked_products );
        $available_prices = array();

        if( $child_products ) {

            foreach( $child_products as $year => $child_product ){
                ksort( $child_product, 1 );

                if( $child_product ) {

                    foreach( $child_product as $month => $child_product_data ){
                        ksort( $child_product_data, 1 );

                        $current_month = date( 'm', strtotime( date( 'Y-m-d H:i:s' ) ) );
                        $current_year  = date( 'Y', strtotime( date( 'Y-m-d H:i:s' ) ) );

                        // Check for year to skip the trip if it's in the past.
                        if ( (int) $month < (int) $current_month && (int) $year <= (int) $current_year ) {
                            continue;
                        }

                        if( $child_product_data ) {

                            foreach( $child_product_data as $index => $child_product_details ){
                                ksort( $child_product_details, 1 );

                                $today_date = new DateTime('now');

                                // 'start_date' => string '11/12/23' d/m/y
                                $trip_start_date = DateTime::createFromFormat('d/m/y', $child_product_details['start_date']);

                                // If the date of the trip is today or in the past, skip the trip;
                                if( $trip_start_date && $trip_start_date <= $today_date ) {
                                    continue;
                                }

                                // Check if the child product is marked as Private/Custom trip.
                                $is_private_custom_trip = get_field( 'is_private_custom_trip', $child_product_details['product_id'] );

                                // If the child product is marked as a private/custom trip, continue to the next one.
                                if( true == $is_private_custom_trip ) {
                                    continue;
                                }

                                // If Trip Status is Private, skip for pricing
                                if( $child_product_details['trip_status'] == 'Private' ) {
                                    continue;
                                }
                                
                                // Store the prices of all available trips.
                                array_push( $available_prices, $child_product_details['price'] );
                            }
                        }
                    }
                }
            }
        }

        // If we have any price in the available prices array, take the lowest.
        if( ! empty( $available_prices ) ) {
            $start_price = min( $available_prices );
        } else {
            return false; // No valid price found
        }
    }

    return floatval( $start_price );
}

/**
 * Take the lowest price in the available trips
 * and overwrite algolia Start Price attribute,
 * that displays the price on the destination archive page.
 *
 * @uses tt_get_lowest_starting_from_price function to take
 * the lowest price for given grouped product.
 *
 * @param array   $shared_attributes Array with Algolia shared attributes.
 * @param WP_Post $post The post.
 */
function tt_algolia_modify_starting_from_price( $shared_attributes, $post ) {

    $shared_attributes['Start Price'] = 0;

    if( isset( $shared_attributes['post_id'] ) ) {

        $shared_attributes['Start Price'] = tt_get_lowest_starting_from_price( $shared_attributes['post_id'] );
    }

    return $shared_attributes;
}
add_filter( 'algolia_searchable_post_shared_attributes', 'tt_algolia_modify_starting_from_price', 10, 2 );

/**
 * Take the correct itinerary link from itineraries ACF Field in admin panel.
 *
 * @param string $trip_sku The Trip Code or SKU, should be not modified to can catch nested dates trips (ex.: 24CARC0129-FIRST).
 * @param int $parent_product_id ID of the grouped product that holds the trips for different periods.
 * 
 * @return string The itinerary link or empty string
 */
function tt_get_itinerary_link_from_trip_itineraries($trip_sku, $parent_product_id) {
    $is_ride_camp = tt_get_local_trips_detail('isRideCamp', '', $trip_sku, true);
    $is_nested_dates_trip = false;
    $itinerary_link = '';
    $current_year = date('Y');
    $next_year = date('Y', strtotime('+1 year'));
    $sku_suffix = substr($trip_sku, 0, 2);  // Get the first 2 characters of $trip_sku
    $nested_dates_period = explode('-', $trip_sku)[1];
    $date_id = tt_get_product_by_sku($trip_sku, true);

    if ($nested_dates_period) {
        $is_nested_dates_trip = true;
    }

    // Look for itineraries relation field on the product.
    $itinerary_posts = get_field('itineraries', $date_id);
    if (!$itinerary_posts) {
        $itinerary_posts = get_field('itineraries', $parent_product_id);
    }

    if ( empty( $itinerary_posts ) ) {
        return '';
    }

    // Filter itineraries by the current year and next year in one pass.
    $all_active_itineraries = array_filter($itinerary_posts, function($itinerary) use ($current_year, $next_year) {
        $itinerary_title = $itinerary->post_title;
        return strpos($itinerary_title, $current_year) !== false || strpos($itinerary_title, $next_year) !== false;
    });

    if (empty($all_active_itineraries)) {
        // No active itineraries.
        return '';
    }

    // Filter itineraries by SKU suffix.
    $matching_itineraries = array_values(array_filter($all_active_itineraries, function($itinerary) use ($sku_suffix) {
        return strpos($itinerary->post_title, $sku_suffix) !== false;
    }));

    // Determine the appropriate itinerary based on ride camp and period.
    if ($is_ride_camp && !empty($matching_itineraries)) {
        if ($is_nested_dates_trip) {
            switch ($nested_dates_period) {
                case 'FIRST':
                    $trip_itinerary_post = $matching_itineraries[0] ?? null;  // Use the first item.
                    break;
                case 'SECOND':
                    $trip_itinerary_post = $matching_itineraries[1] ?? null;  // Use the second item if available.
                    break;
                default:
                    $trip_itinerary_post = $matching_itineraries[0] ?? null;  // Default to the first item.
                    break;
            }
        } else {
            // Not a nested dates trip, use the first available itinerary.
            $trip_itinerary_post = $matching_itineraries[0] ?? null;
        }
        $itinerary_link = $trip_itinerary_post ? get_permalink($trip_itinerary_post) : '';
    } else {
        // Standard trip or no matching itineraries found.
        $trip_itinerary_post = reset($all_active_itineraries);
        $itinerary_link = get_permalink($trip_itinerary_post);
    }

    return $itinerary_link;
}


/**
 * Check the Post Booking Checklist status.
 *
 * @param int|string $user_id     The user ID.
 * @param int|string $order_id    The order ID.
 * @param int|string $rider_level The rider level, taken from guest_bookings table.
 * @param int|string $product_id  The product ID, to can check for the passport required.
 * @param int|string $bike_id     The bike ID, taken from guest_bookings table.
 * @param bool $guest_is_primary  Is Guest a primary guest.
 * @param bool $waiver_signed     Is guest has signed waiver.
 *
 * @return boolean Is the checklist complete.
 */
function tt_is_checklist_completed( $user_id, $order_id, $rider_level, $product_id, $bike_id, $guest_is_primary, $waiver_signed ) {
	$is_checklist_completed = true;
    $trip_info              = tt_get_trip_pid_sku_from_cart( $order_id );
    $is_hiking_checkout     = tt_is_product_line( 'Hiking', $trip_info['sku'] );


	// Get info for completed PB checklist sections from the user meta.
	$confirmed_info_user         = get_user_meta( $user_id, 'pb_checklist_cofirmations', true );
	$confirmed_info_unserialized = maybe_unserialize( $confirmed_info_user );

    // There is no information yet for confirmed sections.
	if( ! $confirmed_info_unserialized ) {
		return false;
	}

	$confirmed_info_order = isset( $confirmed_info_unserialized[ $order_id ] ) ? $confirmed_info_unserialized[ $order_id ] : null;

    // There is no information yet for confirmed sections for the given order.
	if( ! $confirmed_info_order ) {
		return false;
	}

    // Collect available checklist sections.
	$available_pb_checklist_sections= array(
        'medical_section',
		'emergency_section'
	);

    $is_passport_required = get_post_meta( $product_id, TT_WC_META_PREFIX . 'isPassportRequired', true );

	if ( isset( $is_passport_required ) && true == $is_passport_required ) {
		array_push( $available_pb_checklist_sections, 'passport_section' );
	}

	/**
	 * Rider Level -> 5 = Non Rider. We can't rely on this anymore, because the value comes from the guest preferences.
	 * Bike ID -> 5270  = Bring own bike.
	 * Bike ID -> 5257  = Non Rider.
	 */
	if( 5257 != $bike_id ) {
		array_push( $available_pb_checklist_sections, 'gear_section' );
	}

	if ( 5257 != $bike_id && 5270 != $bike_id && ! $is_hiking_checkout ) {
		array_push( $available_pb_checklist_sections, 'bike_section' );
	}

    // We keep waiver signed status into guest_bookings table only. Check waiver signed status.
    if( 1 != $waiver_signed ) {
        // Waiver not signed. Need to return false.
        return false;
    }

    // Loop the available sections and check for confirmations.
	foreach ( $available_pb_checklist_sections as $section ) {

		if( ! isset( $confirmed_info_order[ $section ] ) ) {
			$is_checklist_completed = false;
			break;
		}

		if( false == $confirmed_info_order[ $section ] ) {
			$is_checklist_completed = false;
			break;
		}
	}

	return $is_checklist_completed;
}

/**
 * Function to detect if we have results from $customerSearchResponseByEmail
 * to take the phone number from there, if it's not empty.
 *
 * @param array $customer_data Array with user data from WP.
 * @param $customer_id The WP User ID.
 * @param object $customerSearchResponseByEmail The response from TM NetSuite searchCustomer().
 * 
 * Note: $customerSearchResponseByEmail will be not empty when we don't have ns_user_id in to DB.
 * That means when new user registers on the site, we will have data from the TM NetSuite Search by email.
 */
function tt_netsuite_customer_data_cb( $customer_data, $customer_id, $customerSearchResponseByEmail ) {
    if( ! empty( $customerSearchResponseByEmail ) ){
        if( isset( $customerSearchResponseByEmail->searchResult->recordList->record[0]->phone ) && ! empty( $customerSearchResponseByEmail->searchResult->recordList->record[0]->phone ) ){
            // We have existing phone in NS for this user.
            if( empty( $customer_data['phone'] ) ) {
                // If the phone we send to NS is Empty, but in NS we have the same user with the phone existing, prevent overwriting the phone.
                $customer_data['phone'] = $customerSearchResponseByEmail->searchResult->recordList->record[0]->phone;
                tt_add_error_log('[TM Netsuite] Modify customer_data', array( 'customer_data' => $customer_data, 'customer_id' => $customer_id ), array( 'status' => true, 'message' => 'Keep the existing phone in NS.' ) );
            }
        }
    }

    return $customer_data;
}
add_filter( 'tm_netsuite_customer_data', 'tt_netsuite_customer_data_cb', 10, 3 );

/**
 * Prevent creating dummy addresses in NS.
 * Prevent TM NetSuite errors when dealing with billing_phone.
 *
 * @param array $address_data Array with NS class based objects.
 * @param int $customer_id The WP User ID
 * 
 * @return array The modified $address_data
 */
function tt_netsuite_customer_address_data_cb( $address_data, $customer_id ) {
    $address_indexes_for_remove = array();
    $is_data_modified           = false;

    if( ! empty( $address_data ) ) {
        // Keep original data for the log.
        $incoming_address_data = $address_data;

        foreach( $address_data as $index => $address ) {
            if( isset( $address->addressbookAddress->zip ) && empty( $address->addressbookAddress->zip ) ) {
                // We have a zip, but is empty, so need to remove this address.
                $address_indexes_for_remove[] = $index;
            }
        }

        // If all addresses don't have zip codes no need for address_data to be sent, to prevent dummy data in NS for addresses and prevent TM NetSuite errors.
        if( count( $address_data ) === count( $address_indexes_for_remove ) ) {
            $address_data     = null;
            $is_data_modified = true;
        } else {

            // If we have something to remove, remove it.
            if( ! empty( $address_indexes_for_remove ) ) {
                foreach( $address_indexes_for_remove as $index ) {
                    // Remove empty address.
                    unset( $address_data[ $index ] );
                }

                $reindex          = array_values($address_data); // normalize index.
                $address_data     = $reindex;
                $is_data_modified = true;
            }
        }

        // Log info if we make any manipulations.
        if( $is_data_modified ) {

            tt_add_error_log( '[TM Netsuite] Modify customer_address_data', array( 'address_data' => $incoming_address_data, 'customer_id' => $customer_id ), array( 'status' => true, 'message' => 'Adjusting address data.', 'address_data(modified)' => $address_data ) );
        }
    }

    return $address_data;
}
add_filter( 'tm_netsuite_customer_address_data', 'tt_netsuite_customer_address_data_cb', 10, 2 );

/**
 * Sync the user with NetSuite,
 * obtain ns_user_id and take all info about the preferences and existing bookings.
 *
 * @uses TM NetSuite plugin methods to obtain the ns_user_id.
 *
 * @param WP_User $user The user.
 *
 * @link https://developer.wordpress.org/reference/hooks/password_reset/
 */
function tt_password_reset_action( $user ) {
    $user_id = $user->ID;

    if( ! empty( $user_id ) ) {
        tt_sync_user_metadata_from_ns_cb( $user_id );
    }
}
add_action( 'password_reset', 'tt_password_reset_action', 10 );

/**
 * git opA little easter egg function to check if the user is admin and insert failed bookings to the guest_bookings_table
 *
 * @return void
 */
function tt_insert_failed_bookings() {
    if ( current_user_can( 'administrator' ) && isset( $_GET['insertfailedbookingid'] ) && isset( $_GET['insertfailedbookinguser'] ) ) {
        $booking_id  = $_GET['insertfailedbookingid'];
        $custom_user = $_GET['insertfailedbookinguser'];
        insert_records_guest_bookings_cb( $booking_id, $custom_user, 'true' );
    }
}
add_action( 'init', 'tt_insert_failed_bookings' );

/**
 * Take Bike Model Name from options table by given bike type ID.
 * 
 * @param int|string $bike_type_id The bike model ID.
 * 
 * @return string The name of the bike model.
 */
function tt_ns_get_bike_type_name( $bike_type_id ) {
    $bike_type_name    = '';
    $ns_bike_type_info = get_option( TT_OPTION_PREFIX . 'ns_bikeType_info' );

    // If options not found, run the bike type info sync process.
    if( empty( $ns_bike_type_info ) ) {
        tt_ns_fetch_bike_type_info();
        $ns_bike_type_info = get_option( TT_OPTION_PREFIX . 'ns_bikeType_info' );
    }

    // Take the bike model name.
    if( $ns_bike_type_info && $bike_type_id ) {
        $ns_bike_type_result = json_decode( $ns_bike_type_info, true );
        $keys                = array_column( $ns_bike_type_result, 'id' );
        $index               = array_search( $bike_type_id, $keys );
        $bike_type_name      = $ns_bike_type_result[$index]['name'];
    }

    return $bike_type_name;
}

function dx_disable_algolia_for_spefic_posts( $flag, WP_Post $post ) {

    $excluded_ids = array( 97732, 90498, 90492, 90490 );
    if ( in_array( $post->ID, $excluded_ids ) ) {
        return false;
    }

    return $flag;
}

// Commented out because of the Slack sync here - https://devrix.slack.com/archives/C06M2D9EGCX/p1711375691091659
// add_filter( 'algolia_should_index_post', 'dx_disable_algolia_for_spefic_posts', 10, 2 );
// add_filter( 'algolia_should_index_searchable_post', 'dx_disable_algolia_for_spefic_posts', 10, 2 );

/**
 * Take the full insurance amount.
 *
 * @param array $guest_insurance Array with info for insured persons and insurance amounts.
 *
 * @return int|float The full insurance amount.
 */
function tt_get_full_insurance_amount( $guest_insurance ) {
    $insurance_amount = 0;

    if ( ! empty( $guest_insurance ) ) {
        $primary_insurance = $guest_insurance['primary'];

        if ( '1' == $primary_insurance['is_travel_protection'] ) {
            $insurance_amount += floatval( $primary_insurance['basePremium'] );
        }
        if ( ! empty( $guest_insurance['guests'] ) ) {
            foreach ( $guest_insurance['guests'] as $trek_guest_insurance ) {
                if ( '1' == $trek_guest_insurance['is_travel_protection'] ) {
                    $insurance_amount += floatval( $trek_guest_insurance['basePremium'] );
                }
            }
        }
    }

    return $insurance_amount;
}

/**
 * Take deposit info with the included insurance amount.
 *
 * Note: Deposit eligible trips with travel protection should charge the deposit amount + the travel protection amount.
 *
 * @param string    $sku The trip code / product SKU.
 * @param int       $guests_number The number of guests
 * @param float|int $insurance_amount
 *
 * @return array The deposit_amount ( with travel protection included ),
 * is_deposited weather the trip is allow for deposit based on deposit before date
 * and deposit_allowed - true if has deposit amount and is_deposited.
 */
function tt_get_deposit_info( $sku = '', $guests_number = 1, $insurance_amount = 0 ) {
    $deposit_amount      = 0;
    $deposit_before_date = '';
    $is_deposited        = false;
    $deposit_allowed     = false;

    if( isset( $sku ) && ! empty( $sku ) ) {
        $deposit_amount      = tt_get_local_trips_detail( 'depositAmount', '', $sku, true );
        $deposit_amount      = $deposit_amount ? str_ireplace( ',', '', $deposit_amount ) : 0;
        if( $deposit_amount ) {
            $deposit_amount = floatval( $deposit_amount ) * intval( $guests_number ) + $insurance_amount;
        }
        $deposit_before_date = tt_get_local_trips_detail( 'depositBeforeDate', '', $sku, true );
    }

    $is_deposited = tt_get_trip_payment_mode( $deposit_before_date ); // true/false.

    if( $deposit_amount && $deposit_amount > 0 && $is_deposited == 1 ) {
        $deposit_allowed = true;
    }

    return array(
        'deposit_amount' => $deposit_amount,
        'deposit_allowed'=> $deposit_allowed
    );
}

/**
 * Set noindex and nofollow Yoast meta,
 * if any product marked as Private/Custom trip.
 *
 * Will set a flag for modified Yoast meta
 * to restore initial values after unchecking the trip.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated.
 *
 * @return void
 */
function tt_on_insert_update_post_cb( $post_id, $post, $update ) {
    if ( $post->post_status != 'publish' || $post->post_type != 'product' ) {
        return;
    }

    // If the post is not product return.
    if ( ! $product = wc_get_product( $post ) ) {
        return;
    }

    // Check product is marked as Private/Custom trip.
    $is_private_custom_trip = get_field( 'is_private_custom_trip', $post );

    if( $is_private_custom_trip ) {
        // Update Yoast meta for noindex.
        update_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', 1 );
        // Update Yoast meta for nofollow.
        update_post_meta( $post_id, '_yoast_wpseo_meta-robots-nofollow', 1 );

        // Create a Flag for Modified Yoast meta.
        update_post_meta( $post_id, 'tt_yoast_wpseo_meta-robots-has-been-modified', 1 );
        // Add a log in the algolia debug file for products marked as private/custom trips.
        tt_algolia_searchable_posts_index_post_updated( $post, array() );
    } else {
        // Add check if has modified Yoast meta, to restore initial values.
        $is_yoast_meta_has_been_modified = get_post_meta( $post_id, 'tt_yoast_wpseo_meta-robots-has-been-modified', true );

        if( $is_yoast_meta_has_been_modified ) {
            // Return the initial state of the Yoast meta robots tags.
            delete_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex' );
            delete_post_meta( $post_id, '_yoast_wpseo_meta-robots-nofollow' );

            // Remove the modified flag.
            delete_post_meta( $post_id, 'tt_yoast_wpseo_meta-robots-has-been-modified' );
        }
    }
}
add_action( 'wp_insert_post', 'tt_on_insert_update_post_cb', 10, 3 );

/**
 * Disable indexing in Algolia for posts that are marked as Private/Custom trips.
 *
 * @param bool $flag Current state of the post.
 * @param WP_Post $post The post.
 *
 * @return bool Is it should index the post in Algolia.
 */
function tt_disable_algolia_for_private_custom_trips( $flag, WP_Post $post ) {

    $is_private_custom_trip = get_field( 'is_private_custom_trip', $post->ID );

    if( true == $is_private_custom_trip ) {
        return false;
    }

    return $flag;
}

add_filter( 'algolia_should_index_post', 'tt_disable_algolia_for_private_custom_trips', 10, 2 );
add_filter( 'algolia_should_index_searchable_post', 'tt_disable_algolia_for_private_custom_trips', 10, 2 );

/**
 * Prevent execution of [trek-my-trip] shortcode on My Trips Page,
 * during Algolia reindexes process.
 *
 * @param string  $post_content The post content.
 * @param WP_Post $post         The post to get records for.
 *
 * @return bool Is it should index the post in Algolia.
 */
function tt_modify_algolia_searchable_post_content( $post_content, WP_Post $post ) {

    if( strpos( $post_content, '[trek-my-trip]' ) !== false ) {
        // Return empty content to prevent shortcode execution.
        return '';
    }

    return $post_content;
}

add_filter( 'algolia_searchable_post_content', 'tt_modify_algolia_searchable_post_content', 10, 2 );

/** Take the status for Record Locking and Bike Locking from user's meta.
 *
 * @param string|int $wc_user_id   Current User ID.
 * @param string|int $guest_reg_id NS Guest Registration ID.
 * @param string     $type         The registration locked type.
 *
 * @return array|bool If the Type is specified and exists will return a single true/false response else will return an array with all possible statuses.
 */
function tt_is_registration_locked( $wc_user_id = '', $guest_reg_id = '', $type = '' ) {
    if( empty( $wc_user_id ) || empty( $guest_reg_id ) ) {
        return false;
    }

    $reg_locked_status = array(
        'record' => 0,
        'bike'   => 0
    );

    // Get stored registrations values.
    $lock_record_user_regs = get_user_meta( $wc_user_id, 'lock_record_registration_ids', true );
    $lock_bike_user_regs   = get_user_meta( $wc_user_id, 'lock_bike_registration_ids', true );

    if( is_array( $lock_record_user_regs ) && in_array( $guest_reg_id, $lock_record_user_regs ) ) {
        $reg_locked_status['record'] = 1;
    }

    if( is_array( $lock_bike_user_regs ) && in_array( $guest_reg_id, $lock_bike_user_regs ) ) {
        $reg_locked_status['bike'] = 1;
    }

    if( isset( $reg_locked_status[ $type ] ) ) {
        // Return single status, based on the given type.
        return $reg_locked_status[ $type ];
    }

    // Return Array with all possible locked statuses.
    return $reg_locked_status;
}

/**
 * Get the Cancelled trips.
 *
 * @param int|string $user_id  The WP user ID.
 * @param int|string $order_id The order ID.
 * @param bool       $is_log   Whether to log the request and response.
 *
 * @return array The cancelled trip data and count.
 */
function tt_get_cancelled_guest_trips( $user_id = '', $order_id = '', $is_log = false ) {
    global $wpdb;

    $table_name    = $wpdb->prefix . 'guest_bookings';
    $user_info     = get_user_by( 'ID', $user_id );
    $wp_user_email = $user_info->user_email;

    $sql = "SELECT DISTINCT gb.order_id,gb.product_id, gb.user_id, gb.trip_name, gb.trip_start_date, gb.trip_end_date from {$table_name} as gb WHERE gb.is_guestreg_cancelled = '1' ";
    $sql .= " AND gb.trip_name != '' ";

    if ( $wp_user_email || $user_id ) {
        $sql .= " AND ( gb.guest_email_address = '{$wp_user_email}' OR gb.user_id = '{$user_id}' )";
    }

    $sql .= " ORDER BY gb.id DESC";

    if ( $order_id ) {
        $sql .= " AND gb.order_id = {$order_id}";
        $sql .= " ORDER BY gb.order_id DESC";
    }

    $results = $wpdb->get_results( $sql, ARRAY_A );
    $res     = array(
        'count' => count( $results ),
        'data'  => $results
    );

    if( $is_log == true ){
        tt_add_error_log('[SQL] My Cancelled Trips Dashboard', ['sql'=>$sql], $res );
    }

    return $res;
}

/**
 * Check if the post should be indexed by Algolia.
 *
 * @param bool    $should_index Current state of the post.
 * @param WP_Post $post         The post.
 *
 * Set the priority of 9, so it can run before checking for Private/Custom trips.
 *
 * @return bool Is it should index the post in Algolia.
 */
function tt_algolia_should_index_searchable_post( $should_index, WP_Post $post ) {

    // Moved initial customizations in the plugin here...
    $product = wc_get_product( $post->ID );

    if ( $post->post_type == 'product' || $post->post_type == 'page' || $post->post_type == 'post' ) {
        // Indexing only products, pages and posts.
        if ( $product ) {
            if ( $product->get_type() != 'grouped' ) {
                // Indexing only the grouped products.
                $should_index = false;
            }
        }
    } else {
        $should_index = false;
    }

    return $should_index;
}
add_filter( 'algolia_should_index_searchable_post', 'tt_algolia_should_index_searchable_post', 9, 2 );

/**
 * Get post shared attributes for Algolia.
 *
 * @param array   $shared_attributes Array with Algolia shared attributes.
 * @param WP_Post $post              The post.
 */
function tt_algolia_get_post_shared_attributes( $shared_attributes, $post ) {
    $json_data      = array();
    $product        = wc_get_product( $post->ID );
    $child_products = [];
    $upload_dir     = wp_upload_dir();
    $json_path1     = $upload_dir['basedir'].'/algolia/popular1.json';
    $json_path2     = $upload_dir['basedir'].'/algolia/popular2.json';
    $json_path3     = $upload_dir['basedir'].'/algolia/popular3.json';
    $json_path4     = $upload_dir['basedir'].'/algolia/popular4.json';
    $yotpo_app_id   = '4488jd7QVtY0HrLS8BYsAC3fel6zpMyyxIyl9wLW';

    //Fetch YotPo Review Data for Product.
    $curl = curl_init();

    curl_setopt_array( $curl, [
        CURLOPT_URL            => 'https://api-cdn.yotpo.com/v1/widget/' . $yotpo_app_id . '/products/' . $post->ID . '/reviews.json',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "GET",
        CURLOPT_HTTPHEADER     => [
            "Accept: application/json",
            "Content-Type: application/json"
        ],
    ]);

    try {
        $response = curl_exec( $curl );
        $err      = curl_error( $curl );
        curl_close( $curl );

        if ( is_string( $response ) ) {

            $json_response = json_decode( $response, true );

            if ( $json_response )  {
                if ( $json_response['response'] ) {
                    if ( $json_response['response']['bottomline'] ) {
                        if ( $json_response['response']['bottomline']['average_score'] ) {

                            if ( $json_response['response']['bottomline']['average_score'] != 0 ) {
                                $shared_attributes['review_score'] = $json_response['response']['bottomline']['average_score'];
                            }
                        }
                        if ( $json_response['response']['bottomline']['total_review'] ) {

                            if ($json_response['response']['bottomline']['total_review'] != 0) {
                                $shared_attributes['total_review'] = $json_response['response']['bottomline']['total_review'];
                            }
                        }
                    }
                }
            }
        }

    } catch ( Exception $e ) {
        error_log( "Error: " , $e->getMessage() );
    }
    // End Fetch YotPo Review Data for Product.

    if ( $product ) {
        if ( $product->get_attribute('Popular') ) {
            if ( $product->get_attribute('Popular') === '1' ) {
                $json_data['Title']     = $product->get_title();
                $json_data['Permalink'] = $product->get_permalink();

                $attachment_ids         = $product->get_gallery_image_ids();
                

                foreach( $attachment_ids as $index=>$attachment_id )
                {
                    $json_data['gallery_images'][$index] = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];

                }

                $json_string = json_encode( $json_data, JSON_PRETTY_PRINT );
                // Write in the file
                $fp = fopen( $json_path1, 'w' );
                fwrite( $fp, $json_string );
                fclose( $fp );
            }
            if ( $product->get_attribute('Popular') === '2' ) {
                $json_data['Title']     = $product->get_title();
                $json_data['Permalink'] = $product->get_permalink();

                $attachment_ids         = $product->get_gallery_image_ids();

                foreach( $attachment_ids as $index=>$attachment_id )
                {
                    $json_data['gallery_images'][$index] = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];

                }

                $json_string = json_encode( $json_data, JSON_PRETTY_PRINT );
                // Write in the file
                $fp = fopen( $json_path2, 'w' );
                fwrite( $fp, $json_string );
                fclose( $fp );
            }
            if ( $product->get_attribute('Popular') === '3' ) {
                $json_data['Title']     = $product->get_title();
                $json_data['Permalink'] = $product->get_permalink();

                $attachment_ids         = $product->get_gallery_image_ids();

                foreach( $attachment_ids as $index=>$attachment_id )
                {
                    $json_data['gallery_images'][$index] = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];

                }

                $json_string = json_encode( $json_data, JSON_PRETTY_PRINT );
                // Write in the file
                $fp = fopen( $json_path3, 'w' );
                fwrite( $fp, $json_string );
                fclose( $fp );
            }
            if ( $product->get_attribute('Popular') === '4' ) {
                $json_data['Title']     = $product->get_title();
                $json_data['Permalink'] = $product->get_permalink();

                $attachment_ids         = $product->get_gallery_image_ids();

                foreach( $attachment_ids as $index=>$attachment_id )
                {
                    $json_data['gallery_images'][$index] = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];

                }

                $json_string = json_encode( $json_data, JSON_PRETTY_PRINT );
                // Write in the file
                $fp = fopen( $json_path4, 'w' );
                fwrite( $fp, $json_string );
                fclose( $fp );
            }
        }

        $children = $product->get_children();
        if ( $children ) {

            // Take all child products that are not marked as Private/Custom trips.
            $filtered_children = array_values(
                array_filter(
                    $children,
                    function( $child_product_id ) {
                        // Check child product is marked as Private/Custom trip.
                        $is_private_custom_trip = get_field( 'is_private_custom_trip', $child_product_id );

                        return ( true != $is_private_custom_trip );
                    }
                )
            );

            foreach ( $filtered_children as $index => $child ) {
                $child_products[$index] = wc_get_product( $child );

                if ( $child_products[$index] ) {

                    if ($child_products[$index]->get_regular_price()) {
                        $rolling_price[$index] = $child_products[$index]->get_regular_price();
                        if ( ! $shared_attributes['Start Price'] ) {
                            $shared_attributes['Start Price'] = intval( $rolling_price[$index] );
                        } else if ( $shared_attributes['Start Price'] > $rolling_price[$index] ) {
                            $shared_attributes['Start Price'] = intval( $rolling_price[$index] );
                        }
                    }

                    if ( $child_products[$index]->get_attribute( 'Start Date' ) ) {
                        $shared_attributes['Start Date'][$index]      = $child_products[$index]->get_attribute( 'Start Date' );
                        $tempdate                                     = $child_products[$index]->get_attribute( 'Start Date' );
                        $sdate_obj                                    = explode('/', $tempdate);
                        $sdate_info                                   = array(
                            'd' => $sdate_obj[0],
                            'm' => $sdate_obj[1],
                            'y' => substr(date('Y'), 0, 2) . $sdate_obj[2]
                        );
                        $tempunix                                     = strtotime(implode('-', $sdate_info));
                        //$tempunix = strtotime($tempdate);
                        $shared_attributes['start_date_unix'][$index] = $tempunix;
                    }
                    if ( $child_products[$index]->get_attribute( 'End Date' ) ) {
                        $shared_attributes['End Date'][$index]      = $child_products[$index]->get_attribute( 'End Date' );
                        $tempdate                                   = $child_products[$index]->get_attribute( 'End Date' );
                        $edate_obj                                  = explode('/', $tempdate);
                        $edate_info                                 = array(
                            'd' => $edate_obj[0],
                            'm' => $edate_obj[1],
                            'y' => substr(date('Y'), 0, 2) . $edate_obj[2]
                        );
                        //$tempunix = strtotime($tempdate);
                        $tempunix                                   = strtotime(implode('-', $edate_info));
                        $shared_attributes['end_date_unix'][$index] = $tempunix;
                    }
                }
            }
        }
    }

    if ( 'Products' === $shared_attributes['post_type_label'] ) {
        $shared_attributes['post_type_label'] = 'Trips';
    }

    if ( 'Posts' === $shared_attributes['post_type_label'] ) {
        $shared_attributes['post_type_label'] = 'Articles';
    }

    if ( wc_get_product( $post->ID ) ) {

        $gallery_image_ids = $product->get_gallery_image_ids();

        foreach ( $gallery_image_ids as $index => $gallery_image_id ) {
            $gallery_image_url = wp_get_attachment_image_src( $gallery_image_id, 'featured-archive' )[0];

            $shared_attributes['gallery_images'][$index] = tt_get_trip_carousel_image_url( $gallery_image_url );

        }
        
        $activity_level = tt_get_custom_product_tax_value( $post->ID, 'activity-level', true );

        if ( $activity_level ) {
            $shared_attributes['Activity Level'] = $activity_level;
        }

        $trip_style = tt_get_custom_product_tax_value( $post->ID, 'trip-style', true );

        if ( $trip_style ) {
            $shared_attributes['Trip Style'] = $trip_style;
        }

        $hotel_level = tt_get_custom_product_tax_value( $post->ID, 'hotel-level', true );

        if ( $hotel_level ) {
            $shared_attributes['Hotel Level'] = $hotel_level;
        }

        $trip_duration = tt_get_custom_product_tax_value( $post->ID, 'trip-duration', false, true ); // Returns the term object.

        if ( $trip_duration ) {
            $trip_duration_pdp_name     = get_term_meta( $trip_duration->term_id, 'pdp_name', true ); // Get the value from the ACF Field with name pdp_name.
            if( $trip_duration_pdp_name ) {
                $shared_attributes['Duration'] = esc_html( $trip_duration_pdp_name );
            } else {
                // Fall Back to the Default name.
                $shared_attributes['Duration'] = esc_html( $trip_duration->name );
            }
        }

        $badge = tt_get_custom_product_tax_value( $post->ID, 'product_tag', true );

        if ( $badge ) {
            $shared_attributes['Badge'] = $badge;
        }

        $product_subtitle = get_field( 'product_overview_product_subtitle', $post->ID, true, true );

        if ( $product_subtitle ) {
            $shared_attributes['Product Subtitle'] = $product_subtitle;
        }
    }

    return $shared_attributes;
}
add_filter( 'algolia_searchable_post_shared_attributes', 'tt_algolia_get_post_shared_attributes', 9, 2 );

/**
 * Filters the HTML rendered for a saved payment method.
 *
 * @param string                              $html  saved payment method HTML.
 * @param SV_WC_Payment_Gateway_Payment_Token $token payment token.
 *
 * @return string modified payment method HTML.
 */
function tt_get_saved_payment_method_html( $html, $token) {
    $html = str_replace( 'type="radio"', 'type="radio" data-card-type="' . $token->get_card_type() . '"', $html );

    $token_data = $token->to_datastore_format();

    if ( isset( $token_data['first_six'] ) && $token_data['first_six'] ) {
        $html = str_replace( 'type="radio"', 'type="radio" data-card-bin="' . $token_data['first_six'] . '"', $html );
    }

    if ( ! empty( $token_data['exp_year'] ) && ! empty( $token_data['exp_month'] ) ) {
        $html = str_replace( 'type="radio"', 'type="radio" data-card-expiration-month="' . $token_data['exp_month'] . '" data-card-expiration-year="' . $token_data['exp_year'] . '"', $html );
    }

    return $html;
}
add_filter( 'wc_cybersource_credit_card_payment_form_payment_method_html', 'tt_get_saved_payment_method_html', 10, 2 );

/**
 * Remove the "Manage Payment Methods" button from the checkout.
 *
 * @param string $html Payment Gateway Payment Form Manage Payment Methods Button HTML.
 *
 * @return string manage payment methods button html. 
 */
function tt_get_manage_payment_methods_button_html( $html ) {
    return '';
}
add_filter( 'wc_cybersource_credit_card_payment_form_manage_payment_methods_button_html', 'tt_get_manage_payment_methods_button_html', 10 );

/**
 * Add the missing categories to the child products for coupon validations,
 * taken from the parent product.
 *
 * NOTE: The Child product will be modified dynamically during the coupon validations,
 * so this can break some logic that uses the child product categories in the feature.
 *
 * @param object[]     $items     The cart item objects.
 * @param WC_Discounts $discounts Discounts class.
 *
 * @return object[] Only the child products items without line items or original passed as parameter items.
 */
function tt_woocommerce_coupon_get_items_to_validate( $items, $discounts ) {
    $accepted_p_ids = tt_get_line_items_product_ids();
    $parent_product = '';
    // This array will contain only the real trip products without line items like Travel Protection, Single Supplеment Fees, etc because we need to validate the trips only.
    $items_to_apply = array();

    foreach ( $items as $item_id => $item ) {
        if( isset( $item->product ) ) {
            $product_id = $item->product->get_id();
            if ( ! in_array( $product_id, $accepted_p_ids ) ) {
                $product_sku = $item->product->get_sku();
    
                $is_ride_camp = tt_get_local_trips_detail( 'isRideCamp',  '', $product_sku, true );
    
                // Find the product ID by the parent SKU.
                $parent_product_id = tt_get_parent_trip_id_by_child_sku( $product_sku, $is_ride_camp );

                if( ! empty( $parent_product_id ) ) {
                    $parent_product    = wc_get_product( $parent_product_id );
                    $parent_categories = get_the_terms( $parent_product_id, 'product_cat' );
                    $item_to_apply     = clone $item; // Clone the item so changes to this item do not affect the originals.
                    $categories_to_add = array();

                    if ( ! empty( $parent_categories ) ) {
                        $categories_to_add = array_column( $parent_categories, 'term_id' );
                    }

                    if( ! empty( $categories_to_add ) ) {
                        // The parent product has categories, that we need to add to the child product, so to can the coupon validation works properly.
                        // !!! Keep in mind that will break some logic using the child categories in the feature.
                        $item_to_apply->product->set_category_ids( [] );
                        $item_to_apply->product->set_category_ids( $categories_to_add );
                        $item_to_apply->product->save();

                        $items_to_apply[] = $item_to_apply;
                    }

                }
            }
        }
    }

    return ! empty( $items_to_apply ) ? $items_to_apply : $items;
    
    
}
add_filter( 'woocommerce_coupon_get_items_to_validate', 'tt_woocommerce_coupon_get_items_to_validate', 10, 2 );

/**
 * Function to check coupon minimum amount restriction,
 * based on the single trip base price,
 * without any additions like Travel Protection, Single Supplement, etc.
 *
 * @param bool      $is_valid      Whether the coupon minimum amount restriction is bigger from the cart subtotal [ $coupon->get_minimum_amount() > $subtotal ].
 * @param WC_Coupon $coupon        Coupon data.
 * @param float     $cart_subtotal The cart subtotal amount.
 */
function tt_woocommerce_coupon_validate_minimum_amount( $is_valid, $coupon, $cart_subtotal ) {
    // Take an array with ids of the line item products.
    $accepted_p_ids = tt_get_line_items_product_ids();

    // Check if WC()->cart is not null.
    if ( ! WC()->cart ) {
        return $is_valid;
    }

    // Take the cart contents.
    $cart = WC()->cart->get_cart();

    if( $cart ) {
        foreach( $cart as $cart_item_id => $cart_item ) {
            $product_id = $cart_item['product_id'];

            // Check for trip product in the cart.
            if( $product_id && ! in_array( $product_id, $accepted_p_ids ) ) {
                $product = wc_get_product( $product_id );

                if( $product ) {
                    $trip_base_price   = floatval( $product->get_price() );
                    $coupon_min_amount = floatval( $coupon->get_minimum_amount() );

                    // Compare coupon minimum amount restriction with the trip base price.
                    $is_valid          = $coupon_min_amount > $trip_base_price;
                    break;
                }
            }
        }
    }
    
    return $is_valid;
}
add_filter( 'woocommerce_coupon_validate_minimum_amount', 'tt_woocommerce_coupon_validate_minimum_amount', 10, 3 );

/**
 * Catch algolia post updated and log info in to a file using WC Logger.
 *
 * @param WP_Post $post    Updated post for indexing.
 * @param array   $records Algolia records associated with this post.
 *
 * @uses WC Logger
 *
 * @return void
 */
function tt_algolia_searchable_posts_index_post_updated( $post, $records ) {
    
    try {

        $post_short_info = array(
            'ID'                     => $post->ID,
            'post_title'             => $post->post_title,
            'post_type'              => $post->post_type,
            'post_status'            => $post->post_status,
            'is_private_custom_trip' => get_field( 'is_private_custom_trip', $post->ID ) // Check whether the product is marked as a Private/Custom trip.
        );

        $current_user    = wp_get_current_user();

        $user_short_info = array(
            'ID'           => $current_user->ID,
            'display_name' => $current_user->display_name,
            'roles'        => json_encode( $current_user->roles )
        );

        wc_get_logger()->debug( 'Algolia post updated >>> Post Short Info::' . json_encode( $post_short_info ) . '; User Short Info::' . json_encode( $user_short_info ), array( 'source' => 'ALGOLIA DEBUG LOGS', 'backtrace' => true, 'arguments' => array( 'post' => $post, 'records' => $records ) ) );

    } catch ( Error $err ) {
        // Log the error.
        error_log( $err->getMessage() ); // phpcs:ignore -- Legacy.
    }
}
add_action( 'algolia_searchable_posts_index_post_updated', 'tt_algolia_searchable_posts_index_post_updated', 10, 2 );

/**
 * AJAX Function to take guest template
 * for adding guests on the checkout page, step 1.
 *
 * @uses checkout-guest-single.php - Template for every Secondary Guest.
 */
function get_add_guest_template_cb() {
    $status                     = true;
    $trip_booking_limit         = get_trip_capacity_info();
    $guest_count                = isset( $_POST['guest_count'] ) && is_numeric( $_POST['guest_count'] ) ? (int) $_POST['guest_count'] : 0;
    $guest_length               = isset( $_POST['guest_length'] ) && is_numeric( $_POST['guest_length'] ) ? (int) $_POST['guest_length'] : 0;
    $checkout_guest_single      = TREK_PATH . '/woocommerce/checkout/checkout-guest-single.php';
    $checkout_guest_single_html = '';

    if( is_readable( $checkout_guest_single ) ) {
        if( $guest_count > $trip_booking_limit['remaining'] ) {
            $guest_count = $trip_booking_limit['remaining'];
        }
        if( $guest_count > $guest_length ) {
            // Guest to add number is bigger from the guest fields in the form, so need to add more guest fields.
            $guest_to_add = $guest_count - $guest_length;
            for ( $i = 1; $i < $guest_to_add; $i++ ) { 
                $checkout_guest_single_html .= wc_get_template_html( 'woocommerce/checkout/checkout-guest-single.php', array( 'guest_num' => $i + $guest_length , 'show_mailing_checkbox' => false ) );
            }
        }
    } else {
        $checkout_guest_single_html = '<h3>Step 1</h3><p>Checkout add single guest template is missing!</p>';
        $status                     = false;
    }

    echo json_encode(
        array(
            'status'                     => $status,
            'checkout_guest_single_html' => $checkout_guest_single_html,
        )
    );
    exit;
}
add_action('wp_ajax_get_add_guest_template_action', 'get_add_guest_template_cb');
add_action('wp_ajax_nopriv_get_add_guest_template_action', 'get_add_guest_template_cb');

/**
 * Function to update dynamically
 * the review order sidebar on the checkout.
 *
 * @uses save_checkout_steps_action_cb() function.
 */
function after_add_remove_guest_template_cb() {
    $save_checkout_steps_response =  save_checkout_steps_action_cb( true ); // Array.
    echo json_encode( $save_checkout_steps_response );
    exit;
}
add_action( 'wp_ajax_after_add_remove_guest_template_action', 'after_add_remove_guest_template_cb' );
add_action( 'wp_ajax_nopriv_after_add_remove_guest_template_action', 'after_add_remove_guest_template_cb' );

/**
 * Check if the trip is from the given product line.
 *
 * @param string $product_line_name The name of the check product line.
 * @param string $sku               The product SKU.
 *
 * @uses tt_get_local_trips_detail() helper function.
 * @uses tt_validate() helper function.
  *
 * @return bool|null Whether the trip is from the given product line or null if the product line is not found in the trip_details table.
 */
function tt_is_product_line( $product_line_name = '', $sku = '' ) {
    $product_line = tt_get_local_trips_detail( 'product_line', '', $sku, true ); // The value or empty string.

    // Product line not found for given SKU.
    if( empty( $product_line ) ) {
        return null;
    }

    $product_line_obj   = json_decode( $product_line ); // The object has 'id' and 'level'.
    $product_line_level = is_object( $product_line_obj ) ? tt_validate( $product_line_obj->name ) : ''; // The name of the product line.

    if( $product_line_name === $product_line_level ) {
        return true;
    }

    return false;
}

/**
 * Add a checkout style class to the body on the checkout page.
 *
 * @param string[] $classes An array of body class names.
 *
 * @uses tt_is_product_line()
 * @uses tt_get_line_items_product_ids()
 *
 * @see https://developer.wordpress.org/reference/hooks/body_class/
 *
 * @return string[] $classes An probably modified array of body class names.
 */
function tt_add_checkout_style_body_class( $classes ) {
    // Only on checkout page
    if( is_checkout() && ! is_wc_endpoint_url() ) {
        $accepted_p_ids = tt_get_line_items_product_ids();

        // Check if WC()->cart is not null.
        if ( ! WC()->cart ) {
            return $classes;
        }

        // Take the cart contents.
        $cart = WC()->cart->get_cart();

        if( ! $cart ) {
            return $classes;
        }

        foreach( $cart as $cart_item_id => $cart_item ) {
            $product_id = $cart_item['product_id'];
            // Check for trip product in the cart.
            if( $product_id && ! in_array( $product_id, $accepted_p_ids ) ) {
                $product     = wc_get_product( $product_id );
                $product_sku = $product->get_sku();

                if( tt_is_product_line( 'Hiking', $product_sku ) ) {
                    $classes[] = 'checkout-style-hiking';
                } else {
                    $classes[] = 'checkout-style-cycling';
                }

            }
        }
    }

    // Only on Thank You Page
    if ( is_checkout() && is_wc_endpoint_url('order-received') ) {
        global $wp;
        $order_id = absint( $wp->query_vars['order-received'] );
        if( $order_id ) {
            $order = wc_get_order( $order_id );
            if( $order ) {
                $trip_info          = tt_get_trip_pid_sku_from_cart( $order_id );
                $is_hiking_checkout = tt_is_product_line( 'Hiking', $trip_info['sku'] );
                if( $is_hiking_checkout ) {
                    $classes[] = 'checkout-style-hiking';
                } else {
                    $classes[] = 'checkout-style-cycling';
                }
            }
        }
    }

    return $classes;
}
add_filter( 'body_class', 'tt_add_checkout_style_body_class' );

/**
 * Function that returns the room type for a given guest index.
 *
 * @param int $index Index of the guest
 * @param array $occupants Array with information about rooms and guests
 * @return string|null Room type or null if the guest is not found
 */
function tt_get_room_type_by_guest_index( $index, $occupants ) {
    $room_types = array(
        'roommate' => __( 'Open to a Roommate', 'trek-trevel-theme' ),
        'private'  => __( 'Enjoy a room all to yourself', 'trek-trevel-theme' ),
        'single'   => __( 'Room with 1 Bed', 'trek-trevel-theme' ),
        'double'   => __( 'Room with 2 Beds', 'trek-trevel-theme' )
    );
    // Iterate through each room type in the occupants array.
    foreach ( $occupants as $room_type => $guests ) {
        // Check if the given index is in the current room type array.
        if ( in_array( (string) $index, $guests ) ) {
            // Return the room type if the guest index is found.
            return $room_types[$room_type];
        }
    }
    // Return null if the guest index is not found in any room type.
    return null;
}

/**
 * Rebuild the bike size options,
 * to can disable bike sizes that are going to be under availability,
 * during the bike selection per guest.
 *
 * @param string|int $bike_type_id The bike type for which want to take the bike size options.
 * @param string|int $selected_bike_size The selected bike size for the field that is under rebuilding.
 * @param array $selected_bikes_arr The bike size and bike type for all selected current bikes.
 * @return array With the bike size and bike type options html.
 */
function tt_rebuild_bike_size_options_cb() {
    $bike_type_id       = tt_validate( $_REQUEST['bike_type_id'] );
    $selected_bike_size = tt_validate( $_REQUEST['selected_bike_size'] );
    $selected_bikes_arr = tt_validate( $_REQUEST['selected_bikes_arr'] );
    $trip_info          = tt_get_trip_pid_sku_from_cart();

    $bike_size_options_html = tt_get_bikes_by_trip_info( $trip_info['ns_trip_Id'], $trip_info['sku'], $bike_type_id, $selected_bike_size, '', '', $selected_bikes_arr );

    echo json_encode( $bike_size_options_html );
    exit;
}
add_action('wp_ajax_tt_rebuild_bike_size_options_ajax_action', 'tt_rebuild_bike_size_options_cb');
add_action('wp_ajax_nopriv_tt_rebuild_bike_size_options_ajax_action', 'tt_rebuild_bike_size_options_cb');

add_action( 'woocommerce_coupon_options_usage_restriction', 'tt_add_grouped_products_fields_to_usage_restriction', 10, 0 );
function tt_add_grouped_products_fields_to_usage_restriction() {
    global $post;

    // Get selected grouped products for include and exclude
    $include_grouped_products = get_post_meta( $post->ID, 'include_grouped_products', true );
    $exclude_grouped_products = get_post_meta( $post->ID, 'exclude_grouped_products', true );

    $include_grouped_products = is_array( $include_grouped_products ) ? $include_grouped_products : array();
    $exclude_grouped_products = is_array( $exclude_grouped_products ) ? $exclude_grouped_products : array();

    // Get all grouped products
    $grouped_products = get_posts( array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_type',
                'field'    => 'slug',
                'terms'    => 'grouped',
            ),
        ),
    ));

    if ( ! empty( $grouped_products ) ) {
        ?>
        <div class="options_group">
            <p class="form-field">
                <label for="include_grouped_products"><?php esc_html_e( 'Grouped Products - Include', 'woocommerce' ); ?></label>
                <select id="include_grouped_products" name="include_grouped_products[]" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select grouped products', 'woocommerce' ); ?>">
                    <?php
                    foreach ( $grouped_products as $product ) {
                        echo '<option value="' . esc_attr( $product->ID ) . '"' . ( in_array( $product->ID, $include_grouped_products ) ? ' selected' : '' ) . '>' . esc_html( $product->post_title ) . '</option>';
                    }
                    ?>
                </select>
                <?php echo wc_help_tip( __( 'Select grouped products that this coupon should apply to.', 'woocommerce' ) ); ?>
            </p>

            <p class="form-field">
                <label for="exclude_grouped_products"><?php esc_html_e( 'Grouped Products - Exclude', 'woocommerce' ); ?></label>
                <select id="exclude_grouped_products" name="exclude_grouped_products[]" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select grouped products that this coupon should not apply to.', 'woocommerce' ); ?>">
                    <?php
                    foreach ( $grouped_products as $product ) {
                        echo '<option value="' . esc_attr( $product->ID ) . '"' . ( in_array( $product->ID, $exclude_grouped_products ) ? ' selected' : '' ) . '>' . esc_html( $product->post_title ) . '</option>';
                    }
                    ?>
                </select>
                <?php echo wc_help_tip( __( 'Select grouped products that this coupon should not apply to.', 'woocommerce' ) ); ?>
            </p>
        </div>
        <?php
    }
}

add_action( 'woocommerce_coupon_options_save', 'tt_save_grouped_products_fields', 10, 2 );
function tt_save_grouped_products_fields( $post_id, $coupon ) {
    // Save Grouped Products Include
    if ( isset( $_POST['include_grouped_products'] ) ) {
        $include_grouped_products = array_map( 'intval', $_POST['include_grouped_products'] );
        update_post_meta( $post_id, 'include_grouped_products', $include_grouped_products );
    } else {
        delete_post_meta( $post_id, 'include_grouped_products' );
    }

    // Save Grouped Products Exclude
    if ( isset( $_POST['exclude_grouped_products'] ) ) {
        $exclude_grouped_products = array_map( 'intval', $_POST['exclude_grouped_products'] );
        update_post_meta( $post_id, 'exclude_grouped_products', $exclude_grouped_products );
    } else {
        delete_post_meta( $post_id, 'exclude_grouped_products' );
    }
}

add_filter( 'woocommerce_coupon_is_valid', 'tt_validate_coupon_grouped_products', 10, 3 );
function tt_validate_coupon_grouped_products( $valid, $coupon, $discounts ) {
    // Ensure WooCommerce cart is available
    if ( ! WC()->cart ) {
        return $valid;
    }

    // Get cart object
    $cart = WC()->cart;

    // Get included and excluded grouped products from the coupon
    $include_grouped_products = get_post_meta( $coupon->get_id(), 'include_grouped_products', true );
    $exclude_grouped_products = get_post_meta( $coupon->get_id(), 'exclude_grouped_products', true );

    $include_grouped_products = is_array( $include_grouped_products ) ? $include_grouped_products : array();
    $exclude_grouped_products = is_array( $exclude_grouped_products ) ? $exclude_grouped_products : array();

    // Loop through each cart item
    foreach ( $cart->get_cart() as $cart_item ) {
        $product_id = $cart_item['product_id'];
        $product_sku = get_post_meta( $product_id, '_sku', true );

        // Get the parent (grouped) product for the current product
        $parent_id = tt_get_parent_trip_id_by_child_sku( $product_sku );

        // If the parent is a grouped product, validate against the parent
        if ( $parent_id && get_post_type( $parent_id ) === 'product' ) {
            $parent_product = wc_get_product( $parent_id );

            if ( $parent_product && $parent_product->get_type() === 'grouped' ) {
                // Validate inclusion
                if ( ! empty( $include_grouped_products ) && ! in_array( $parent_id, $include_grouped_products ) ) {
                    return false; // Invalid if the parent is not in the include list
                }

                // Validate exclusion
                if ( ! empty( $exclude_grouped_products ) && in_array( $parent_id, $exclude_grouped_products ) ) {
                    return false; // Invalid if the parent is in the exclude list
                }
            }
        }
    }

    // If all checks pass, return valid
    return $valid;
}

// Add custom taxonomy fields to the Usage Restrictions tab
add_action( 'woocommerce_coupon_options_usage_restriction', 'tt_add_custom_taxonomy_fields_to_usage_restriction', 20, 0 );
function tt_add_custom_taxonomy_fields_to_usage_restriction() {
    global $post;

    // Define the custom taxonomies
    $custom_taxonomies = array(
        'trip-style'     => 'Trip Styles',
        'activity'       => 'Activity',
        'destination'    => 'Destinations',
        'activity-level' => 'Activity Levels',
        'hotel-level'    => 'Hotel Levels',
        'trip-class'     => 'Trip Classes',
        'trip-duration'  => 'Trip Durations',
        'trip-status'    => 'Trip Statuses',
    );

    foreach ( $custom_taxonomies as $taxonomy => $label ) {
        // Retrieve selected terms for include and exclude
        $include_terms = get_post_meta( $post->ID, 'include_' . $taxonomy, true );
        $exclude_terms = get_post_meta( $post->ID, 'exclude_' . $taxonomy, true );

        $include_terms = is_array( $include_terms ) ? $include_terms : array();
        $exclude_terms = is_array( $exclude_terms ) ? $exclude_terms : array();

        // Get all terms for the current taxonomy
        $terms = get_terms( array(
            'taxonomy'   => $taxonomy,
            'orderby'    => 'name',
            'hide_empty' => false,
        ));

        if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
            ?>
            <div class="options_group">
                <p class="form-field">
                    <label for="include_<?php echo $taxonomy; ?>"><?php echo esc_html( $label . ' - Include' ); ?></label>
                    <select id="include_<?php echo $taxonomy; ?>" name="include_<?php echo $taxonomy; ?>[]" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'woocommerce' ); ?>">
                        <?php
                        foreach ( $terms as $term ) {
                            echo '<option value="' . esc_attr( $term->term_id ) . '"' . ( in_array( $term->term_id, $include_terms ) ? ' selected' : '' ) . '>' . esc_html( $term->name ) . '</option>';
                        }
                        ?>
                    </select>
                    <?php echo wc_help_tip( __( 'Select ' . $label . ' that this coupon should apply to.', 'woocommerce' ) ); ?>
                </p>

                <p class="form-field">
                    <label for="exclude_<?php echo $taxonomy; ?>"><?php echo esc_html( $label . ' - Exclude' ); ?></label>
                    <select id="exclude_<?php echo $taxonomy; ?>" name="exclude_<?php echo $taxonomy; ?>[]" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'woocommerce' ); ?>">
                        <?php
                        foreach ( $terms as $term ) {
                            echo '<option value="' . esc_attr( $term->term_id ) . '"' . ( in_array( $term->term_id, $exclude_terms ) ? ' selected' : '' ) . '>' . esc_html( $term->name ) . '</option>';
                        }
                        ?>
                    </select>
                    <?php echo wc_help_tip( __( 'Select ' . $label . ' that this coupon should not apply to.', 'woocommerce' ) ); ?>
                </p>
            </div>
            <?php
        }
    }
}

// Save the custom taxonomy field values
add_action( 'woocommerce_coupon_options_save', 'tt_save_custom_taxonomy_fields', 20, 2 );
function tt_save_custom_taxonomy_fields( $post_id, $coupon ) {
    $custom_taxonomies = array(
        'trip-style',
        'activity',
        'destination',
        'activity-level',
        'hotel-level',
        'trip-class',
        'trip-duration',
        'trip-status',
    );

    foreach ( $custom_taxonomies as $taxonomy ) {
        if ( isset( $_POST['include_' . $taxonomy] ) ) {
            update_post_meta( $post_id, 'include_' . $taxonomy, array_map( 'intval', $_POST['include_' . $taxonomy] ) );
        } else {
            delete_post_meta( $post_id, 'include_' . $taxonomy );
        }

        if ( isset( $_POST['exclude_' . $taxonomy] ) ) {
            update_post_meta( $post_id, 'exclude_' . $taxonomy, array_map( 'intval', $_POST['exclude_' . $taxonomy] ) );
        } else {
            delete_post_meta( $post_id, 'exclude_' . $taxonomy );
        }
    }
}

add_filter( 'woocommerce_coupon_is_valid', 'tt_validate_coupon_custom_taxonomies', 20, 3 );
function tt_validate_coupon_custom_taxonomies( $valid, $coupon, $discounts ) {
    // Ensure WooCommerce cart is available
    if ( ! WC()->cart ) {
        return $valid;
    }

    // Get the cart object
    $cart = WC()->cart;

    // Define the taxonomies to check
    $parent_product_taxonomies = array(
        'trip-style'      => 'Trip Styles',
        'activity-level'  => 'Activity Levels',
        'activity'        => 'Activity',
        'destination'     => 'Destinations',
        'hotel-level'     => 'Hotel Levels',
        'trip-class'      => 'Trip Classes',
        'trip-duration'   => 'Trip Durations',
    );

    $product_taxonomies = array(
        'trip-status'     => 'Trip Statuses',
    );

    // Loop through each cart item
    foreach ( $cart->get_cart() as $cart_item ) {
        $product_id  = $cart_item['product_id'];
        $product_sku = get_post_meta( $product_id, '_sku', true );

        // Check taxonomies related to parent/grouped product
        foreach ( $parent_product_taxonomies as $taxonomy => $label ) {
            $include_meta_key = 'include_' . $taxonomy;
            $exclude_meta_key = 'exclude_' . $taxonomy;

            // Retrieve and normalize include/exclude terms from the coupon
            $include_terms = get_post_meta( $coupon->get_id(), $include_meta_key, true );
            $exclude_terms = get_post_meta( $coupon->get_id(), $exclude_meta_key, true );

            $include_terms = is_array( $include_terms ) ? $include_terms : array();
            $exclude_terms = is_array( $exclude_terms ) ? $exclude_terms : array();

            // Get the parent product ID if it's a grouped product
            $parent_id = tt_get_parent_trip_id_by_child_sku( $product_sku );

            // Check if the parent_id is valid and retrieve terms for the parent product
            if ( is_numeric( $parent_id ) && $parent_id != "No grouped products found for this SKU." ) {
                $parent_terms = wp_get_post_terms( $parent_id, $taxonomy, array( 'fields' => 'ids' ) );

                // Check if there are terms to include
                if ( ! empty( $include_terms ) ) {
                    $intersection = array_intersect( $parent_terms, $include_terms );
                    if ( empty( $intersection ) ) {
                        return false;
                    }
                }

                // Check if there are terms to exclude
                if ( ! empty( $exclude_terms ) ) {
                    $intersection = array_intersect( $parent_terms, $exclude_terms );
                    if ( ! empty( $intersection ) ) {
                        return false;
                    }
                }
            }
        }

        // Check `Trip Status` taxonomy for the purchased product
        foreach ( $product_taxonomies as $taxonomy => $label ) {
            $include_meta_key = 'include_' . $taxonomy;
            $exclude_meta_key = 'exclude_' . $taxonomy;

            // Retrieve and normalize include/exclude terms from the coupon
            $include_terms = get_post_meta( $coupon->get_id(), $include_meta_key, true );
            $exclude_terms = get_post_meta( $coupon->get_id(), $exclude_meta_key, true );

            $include_terms = is_array( $include_terms ) ? $include_terms : array();
            $exclude_terms = is_array( $exclude_terms ) ? $exclude_terms : array();

            // Get terms for the purchased product
            $product_terms = wp_get_post_terms( $product_id, $taxonomy, array( 'fields' => 'ids' ) );

            // Check if there are terms to include
            if ( ! empty( $include_terms ) ) {
                $intersection = array_intersect( $product_terms, $include_terms );
                if ( empty( $intersection ) ) {
                    return false;
                }
            }

            // Check if there are terms to exclude
            if ( ! empty( $exclude_terms ) ) {
                $intersection = array_intersect( $product_terms, $exclude_terms );
                if ( ! empty( $intersection ) ) {
                    return false;
                }
            }
        }
    }

    // If all checks pass, the coupon is valid
    return $valid;
}

function tt_custom_modify_biiling_state_field( $fields ) {
    // Make the billing state field optional by default
    $fields['billing']['billing_state']['required'] = false;

    // Return the modified fields
    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'tt_custom_modify_biiling_state_field' );

/**
 * Repair the missing discount on an existing coupon code.
 */
function tt_repair_coupon_code_cb() {
    $trek_user_checkout_data = get_trek_user_checkout_data();
    $tt_posted               = $trek_user_checkout_data['posted'];
    $tt_coupon_code          = tt_validate( $tt_posted['coupon_code'] );
    if( ! empty( $tt_coupon_code ) && ! WC()->cart->has_discount( $tt_coupon_code ) ) {
        // There is a coupon_code stored in the cart item meta but it's not applied. Fix that.
        $coupon      = new WC_Coupon( $tt_coupon_code );
        $coupon_post = get_post( $coupon->id );
        if ( $coupon_post ) {
            WC()->cart->apply_coupon( $tt_coupon_code );
            // Recalculate the totals after modifying the cart.
            WC()->cart->calculate_totals();
            // Save the updated cart to the session.
            WC()->cart->set_session();
            // Update persistent_cart.
            WC()->cart->persistent_cart_update();
        }
    }
}
add_action( 'tt_repair_coupon_code', 'tt_repair_coupon_code_cb' );

/**
 * Update the trek_user_checkout_data item meta of the trip for objects from the first level.
 * If you want to update a multidimensional array should send the $data in the proper structure.
 *
 * @param array $data Array with trek_user_checkout_data keys and for value whatever you want.
 *
 * @return bool Whether the cart item meta was updated.
 */
function tt_update_trek_user_checkout_data( $data ) {
    // Check for data.
    if( empty( $data ) || ! is_array( $data ) || WC()->cart->is_empty() ) {
        return false;
    }

    $cart_updated   = false;
    $accepted_p_ids = tt_get_line_items_product_ids();
    $cart_content   = WC()->cart->get_cart_contents();
    foreach ( $cart_content as $cart_item_key => $cart_item ) {
        $product_id = isset( $cart_item['product_id'] ) ? $cart_item['product_id'] : '';
        if ( ! in_array( $product_id, $accepted_p_ids ) ) {
            $cart_posted_data = $cart_item['trek_user_checkout_data'];

            // Ensure the trek_user_checkout_data exists as meta to the trip.
            if ( ! $cart_posted_data || empty( $cart_posted_data ) ) {
                $cart_item['trek_user_checkout_data'] = array();
            }

            foreach ( $data as $key => $value ) {
                $cart_item['trek_user_checkout_data'][$key] = $value;
            }

            $cart_content[$cart_item_key] = $cart_item;

            // Cart item meta for the trip was updated.
            $cart_updated = true;

            break; // Stop the loop once the product is found and updated.
        }
    }

    if ( $cart_updated ) {

        // Store the updated cart.
        WC()->cart->set_cart_contents( $cart_content );
        // Recalculate the totals after modifying the cart.
        WC()->cart->calculate_totals();
        // Save the updated cart to the session.
        WC()->cart->set_session();
        // Update persistent_cart.
        WC()->cart->persistent_cart_update();
    }

    return $cart_updated;
}

/**
 * @param string $postcode_validation_notice
 */
function tt_woocommerce_checkout_postcode_validation_notice( $postcode_validation_notice ) {
    $postcode_validation_notice .= esc_html__( ' If you have selected the option "Same as my mailing address," please check the ZIP code from step one "Guest Info".', 'trek-travel-theme' );
    return $postcode_validation_notice;
}
add_filter( 'woocommerce_checkout_postcode_validation_notice', 'tt_woocommerce_checkout_postcode_validation_notice' );

/**
 * Validate the post code using the WC_Validation.
 */
function tt_validate_post_code( $raw_post_code, $country_code ) {
    // Validate input.
    if( empty( $raw_post_code ) || empty( $country_code ) ) {
        return false;
    }

    $post_code = wc_format_postcode( $raw_post_code, $country_code );

    if ( ! empty( $post_code ) && ! WC_Validation::is_postcode( $post_code, $country_code ) ) {
        return false;
    }

    return true;
}

/**
 * Post Code Validation AJAX handler callback.
 */
function tt_validate_post_code_cb() {
    // Check for data exist.
    if ( empty( $_POST['post_code'] ) || empty( $_POST['country_code'] ) ) {
        wp_send_json_error( array( 'status' => false, 'result' => 'Data not found!' ) );
        exit;
    }

    $post_code    = sanitize_text_field( wp_unslash( $_POST['post_code'] ) );
    $country_code = sanitize_text_field( wp_unslash( $_POST['country_code'] ) );

    $result = tt_validate_post_code( $post_code, $country_code );

    $success_response = array(
        'status' => true,
        'result' => $result
    );

    wp_send_json_success( $success_response );
    exit;
}
add_action( 'wp_ajax_tt_validate_post_code', 'tt_validate_post_code_cb' );
add_action( 'wp_ajax_nopriv_tt_validate_post_code', 'tt_validate_post_code_cb' );

/**
 * Adjust the image sizes in the carousels on the Archive and Search pages.
 *
 * @param string|bool|null $source_image_url The source image URL.
 *
 * @return string
 */
function tt_get_trip_carousel_image_url( $source_image_url ) {
    $image_url = str_replace( '-300x300', '-886x664', $source_image_url );

    $file_headers = @get_headers( $image_url );

    if ( ! $file_headers || 'HTTP/1.1 200 OK' !== $file_headers[0] ) {
        // File not found. Fallback to the original image size.
        $image_url = str_replace( '-300x300', '', $source_image_url );
    }

    return $image_url;
}

/**
 * Filters the Yoast SEO Page title.
 *
 * @link https://developer.yoast.com/features/seo-tags/titles/api/
 *
 * @param string $title The current page's generated title.
 *
 * @return string The filtered title.
 */
function tt_filter_wpseo_title( $title ) {
    if ( is_search() ) {
        // Change the title on the Search page.
        $title = __( 'Search - Trek Travel', 'trek-travel-theme' );
    }
    return $title;
}
add_filter( 'wpseo_title', 'tt_filter_wpseo_title' );

/**
 * AJAX Function to take Quick Look tempalte
 * for adding Dates and Price section on archive and search pages.
 *
 * @uses content-quick-view.php - Template for every Secondary Guest. 
 */
function tt_quick_look_cb() {
    // Security check.
    if ( ! isset( $_POST['nonce'] ) ) {
        wp_send_json_error( array( 'status' => false, 'message' => 'Nonce Verification not available!' ) );
        exit;
    }

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), '_tt_quick_look_nonce' ) ) {
        wp_send_json_error( array( 'status' => false, 'message' => 'Nonce Verification fail!' ) );
        exit;
    }

    // Check for data exist.
    if ( ! isset( $_POST['product_id'] ) || empty( $_POST['product_id'] ) || ! is_numeric( $_POST['product_id'] ) ) {
        wp_send_json_error( array( 'status' => false, 'message' => 'Product ID not found!' ) );
        exit;
    }

    $quick_look_response = array(
        'status' => true,
    );

    $tt_quick_look_tpl  = TREK_PATH . '/woocommerce/quick-view/content-quick-view.php';

    if( is_readable( $tt_quick_look_tpl ) ) {
        // Parent/Grouped Product ID.
        $product_id                                = (int) sanitize_text_field( wp_unslash( $_POST['product_id'] ) );
        $trip_data                                 = sanitize_text_field( wp_unslash( $_POST['trip_data'] ) );
        $current_product                           = wc_get_product( $product_id );
        $quick_look_response['tt_quick_look_html'] = wc_get_template_html( 'woocommerce/quick-view/content-quick-view.php', array( 'product_id' => $product_id, 'trip_data' => $trip_data ) );
        $start_price                               = tt_get_lowest_starting_from_price( $product_id );
        $quick_look_response['start_price']        = wc_price( $start_price );
        $quick_look_response['view_trip_link']     = $current_product ? $current_product->get_permalink() : '';
    } else {
        wp_send_json_error( array( 'status' => false, 'message' => 'Quick View Template not found!' ) );
        exit;
    }

    wp_send_json_success( $quick_look_response );
    exit;
}
add_action('wp_ajax_tt_quick_look_action', 'tt_quick_look_cb');
add_action('wp_ajax_nopriv_tt_quick_look_action', 'tt_quick_look_cb');

/**
 * Check if should redirect the user to the checkout after login/register.
 *
 * Read the item meta data from the date trip product,
 * check for the `trek_user_redirect_to_checkout` meta,
 * assign to the flag and clear the item meta key.
 *
 * @return bool Whether should redirect the user to the checkout.
 */
function tt_should_redirect_user_to_checkout() {
    $should_redirect_user = false;
    $accepted_p_ids       = tt_get_line_items_product_ids();
    $cart_contents        = WC()->cart->get_cart_contents(); // Get the current cart contents.
    if ( $cart_contents ) {
        foreach ( $cart_contents as $cart_item_id => $cart_item ) {
            $product_id = isset( $cart_item['product_id'] ) ? $cart_item['product_id'] : '';
            if ( $product_id && ! in_array( $product_id, $accepted_p_ids ) ) {
                // Date Trip Info.
                $should_redirect_user = isset( $cart_item['trek_user_redirect_to_checkout'] ) ? (bool) $cart_item['trek_user_redirect_to_checkout'] : false;

                if( $should_redirect_user ) {
                    // Remove the cart item meta key.
                    unset( $cart_item['trek_user_redirect_to_checkout'] );
                    $cart_contents[$cart_item_id] = $cart_item;
                }
            }
        }

        if ( $should_redirect_user ) {
            // Store the updated cart.
            WC()->cart->set_cart_contents( $cart_contents );
            // Recalculate the totals after modifying the cart.
            WC()->cart->calculate_totals();
            // Save the updated cart to the session.
            WC()->cart->set_session();
            // Update persistent_cart.
            WC()->cart->persistent_cart_update();
        }
    }

    return $should_redirect_user;
}

/**
 * Modify the default woocommerce redirect URL after login.
 *
 * @param string $redirect Redirect URL.
 *
 * @return string Modified Redirect URL.
 */
function tt_woocommerce_login_redirect( $redirect ) {
    if ( tt_should_redirect_user_to_checkout() ) {
        return trek_checkout_step_link(1);
    }
    return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'tt_woocommerce_login_redirect' );

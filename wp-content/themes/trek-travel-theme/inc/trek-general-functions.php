<?php

use Algolia\AlgoliaSearch\SearchClient;
use TTNetSuite\NetSuiteClient;
$cancellation_policy_page_id = get_option('tt_opt_cancellation_policy_page_id') ? get_option('tt_opt_cancellation_policy_page_id') : NULL;
$cancellation_policy_page_link = $cancellation_policy_page_id ? get_the_permalink($cancellation_policy_page_id) : '';
define('TREK_DIR', get_template_directory_uri());
define('TREK_PATH', get_template_directory());
define('TT_WAIVER_URL', 'https://661527.extforms.netsuite.com/app/site/hosting/scriptlet.nl?script=40&deploy=1&compid=661527&h=1d9367cf147b5322893e&whence=');
define('TREK_MY_ACCOUNT_PID', get_option('woocommerce_myaccount_page_id'));
define('DEFAULT_IMG', 'https://via.placeholder.com/90?text=Trek%20Travel');
define('G_CAPTCHA_SITEKEY', '6LfJg7MiAAAAAITw-hl0U0r2E8gSGUimzUh8-9Q0');
define('TREK_INSURANCE_UNAME', 'APIUSERTREKTRAV@test.roamright.com');
define('TREK_INSURANCE_PASS', 'Hosing+Chips+raps1');
define('TREK_INRURANCE_API_URL', 'https://testservices.archinsurancesolutions.com/PartnerService/api');
define('TT_WC_META_PREFIX', 'tt_meta_');
define('TT_OPTION_PREFIX', 'tt_option_');
define('TT_CAN_POLICY_PAGE', $cancellation_policy_page_link);
define('TT_LINE_ITEMS_PRODUCTS', ['TTWP23FEES' => ['name' => 'Insurance Fees', 'price' => 999], 'TTWP23SUPP' => ['name' => 'Single Supplement Fees', 'price' => 1200], 'TTWP23UPGRADES' => ['name' => 'Bike Upgrades', 'price' => 399]]);
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
        update_user_meta($customer_id, 'globalsubscriptionstatus', 1);
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
        'blank_image' => DEFAULT_IMG,
        'temp_dir' => get_template_directory_uri(),
        'trip_booking_limit' => $trip_booking_limit,
        'is_checkout' => is_checkout(),
        'rider_level' => $cart_product_info['parent_rider_level'],
        'rider_level_text' => $cart_product_info['rider_level_text'],
        'checkoutParentId' => $cart_product_info['parent_product_id'],
        'checkoutSku' => $cart_product_info['sku'],
        'review_order' => tt_get_review_order_html(),
        'is_order_received' => is_wc_endpoint_url( 'order-received' ),
        'order_id' => $order_id
    ));
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get grouped products by ids
 **/
function get_child_products($linked_products = array())
{
    $linked_product_arr = array();
    $status_not_in = ['Hold - Not on Web', 'Cancelled'];
    if ($linked_products) {
        foreach ($linked_products as $linked_product) {
            $p_obj = wc_get_product($linked_product);
            if ($p_obj && $p_obj->get_status() == 'publish' && $p_obj->get_stock_status() == 'instock' ) {
                $start_date = $p_obj->get_attribute('start-date');
                $end_date = $p_obj->get_attribute('end-date');
                $trip_status = $p_obj->get_attribute('trip-status');
                if ($start_date && $end_date && !in_array($trip_status, $status_not_in)) {
                    $sdate_obj = explode('/', $start_date);
                    $sku = $p_obj->get_sku();
                    $singleSupplementPrice = tt_get_local_trips_detail('singleSupplementPrice', '', $sku, true);
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
                    $date_range_1 = $start_date_text . '-' . $end_date_text_2;
                    $date_range_2 = $start_date_text . '-' . $end_date_text_1;
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
                    if (count($month_range) == 1) {
                        if (strlen($sdate_info['m']) == 1) {
                            $sdate_info['m'] = '0' . $sdate_info['m'];
                        }
                        $linked_product_arr[$sdate_info['y']][$sdate_info['m']][] = $grouped_product;
                    } else {
                        foreach ($month_range as $month_range_num) {
                            if (strlen($month_range_num) == 1) {
                                $month_range_num = '0' . $month_range_num;
                            }
                            $linked_product_arr[$sdate_info['y']][$month_range_num][] = $grouped_product;
                        }
                    }
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
        'placeholder' => __('Date of Birth', 'woocommerce'),
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
        'options' => array(1 => 'Male', 2 => 'Female')
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
function save_checkout_steps_action_cb()
{
    $total_guests_req = 0;
    $user_id = get_current_user_id();
    $step = (isset($_REQUEST['targetStep']) ? $_REQUEST['targetStep'] : 1);
    $redirect_url = trek_checkout_step_link($step);
    $accepted_p_ids = tt_get_line_items_product_ids();
    WC()->session->set('trek-checkout-data', $_REQUEST);
    $cart = WC()->cart->cart_contents;
    $bikes_cart_item_data = $guests_bikes_data = array();
    foreach ($cart as $cart_item_id => $cart_item) {
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_id);
        $_product_name = $_product->get_name();
        $product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
        if (!in_array($product_id, $accepted_p_ids)) {
            //Reset Occupant data if any changes for guests in step 1
            if ($step == 2 && isset($_REQUEST['step']) && $_REQUEST['step'] == 1) {
                $total_guests_req += isset($_REQUEST['single']) ? $_REQUEST['single'] : 0;
                $total_guests_req += isset($_REQUEST['double']) ? $_REQUEST['double'] : 0;
                $total_guests_req += isset($_REQUEST['roommate']) ? $_REQUEST['roommate'] : 0;
                $total_guests_req += isset($_REQUEST['private']) ? $_REQUEST['private'] : 0;
                if (isset($_REQUEST['no_of_guests']) && $_REQUEST['no_of_guests'] != $total_guests_req) {
                    $_REQUEST['single'] = $_REQUEST['double'] = $_REQUEST['roommate'] = $_REQUEST['private'] = 0;
                    $_REQUEST['occupants'] = [];
                }
            }
            $cart_item['trek_user_checkout_data'] = $_REQUEST;
            //Trip Parent ID
            $parent_product_id = tt_get_parent_trip_id_by_child_sku($_product->get_sku());
            $cart_item['trek_user_checkout_data']['parent_product_id'] = $parent_product_id;
            $cart_item['trek_user_checkout_data']['product_id'] = $product_id;
            $cart_item['trek_user_checkout_data']['sku'] = $_product->get_sku();
            $bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', '', $_product->get_sku(), true);
            $singleSupplementPrice = tt_get_local_trips_detail('singleSupplementPrice', '', $_product->get_sku(), true);
            $cart_item['trek_user_checkout_data']['bikeUpgradePrice'] = $bikeUpgradePrice;
            $cart_item['trek_user_checkout_data']['singleSupplementPrice'] = $singleSupplementPrice;
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
                            $guests_bikes_data[] = array_merge($inner_guest_arr, $trek_bike_gear_inner_guest);
                        }
                    }
                }
                $bikes_cart_item_data['cart_item_data'] = $guests_bikes_data;
            }
            $cart_item['trek_user_formatted_checkout_data'][1] = $bikes_cart_item_data;
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
            if ($step == 2) {
                WC()->cart->cart_contents[$cart_item_id]['quantity'] = isset($_REQUEST['no_of_guests']) ? $_REQUEST['no_of_guests'] : 1;
            }
        }
    }
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
    $gearData = $_REQUEST['bike_gears']['primary'];
    if (isset($gearData['save_preferences']) && $gearData['save_preferences'] == 'yes' && isset($step) && $step == 3) {
        $p_bike = isset($gearData['bike']) ? $gearData['bike'] : '';
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
    $stepHTML = '';
    if (isset($step) && $step == 2) {
        $checkout_hotel = TREK_PATH . '/woocommerce/checkout/checkout-hotel.php';
        if (is_readable($checkout_hotel)) {
            $stepHTML .= wc_get_template_html('woocommerce/checkout/checkout-hotel.php');
        } else {
            $stepHTML .=  'Checkout Hotel form code is missing!';
        }
        $checkout_bikes = TREK_PATH . '/woocommerce/checkout/checkout-bikes.php';
        if (is_readable($checkout_bikes)) {
            $stepHTML .= wc_get_template_html('woocommerce/checkout/checkout-bikes.php');
        } else {
            $stepHTML .=  '<h3>Step 2</h3><p>Checkout Bike form code is missing!</p>';
        }
    }
    if (isset($step) && $step == 4) {
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
    $insuredHTML = '';
    $checkout_insured_users = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests.php';
    if (is_readable($checkout_insured_users)) {
        $insuredHTML .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests.php');
    } else {
        $insuredHTML .= '<h3>Step 4</h3><p>checkout-insured-guests.php form code is missing!</p>';
    }
    $review_order_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if (is_readable($review_order)) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    echo json_encode(
        array(
            'status' => true,
            'stepHTML' => $stepHTML,
            'redirectURL' => $redirect_url,
            'insuredHTML' => $insuredHTML,
            'insuredHTMLPopup' => $insuredHTMLPopup,
            'review_order' => $review_order_html,
            'message' => 'Trek checkout steps data saved!'
        )
    );
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Algolia Popular Search results data
 **/
function trek_algolia_popular_search()
{
    $popular1 = $popular2 = $popular3 = $popular4 = $results = array();
    if (class_exists('Psr\Log\AbstractLogger')) {
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
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Trip checklist saving
 **/
add_action('wp_ajax_update_trip_checklist_action', 'trek_update_trip_checklist_action_cb');
add_action('wp_ajax_nopriv_update_trip_checklist_action', 'trek_update_trip_checklist_action_cb');
function trek_update_trip_checklist_action_cb()
{
    $netSuiteClient = new NetSuiteClient();
    global $wpdb;
    $table_name = $wpdb->prefix . 'guest_bookings';
    $res = array(
        'status' => false,
        'message' => ''
    );
    $bookingData = [];
    $user = wp_get_current_user();
    $User_order_info = trek_get_user_order_info($user->ID, isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '');
    $guest_is_primary = isset($User_order_info[0]['guest_is_primary']) ? $User_order_info[0]['guest_is_primary'] : '';
    $guest_email_address = isset($User_order_info[0]['guest_email_address']) ? $User_order_info[0]['guest_email_address'] : '';
    if (!isset($_POST['edit_trip_checklist_nonce']) || !wp_verify_nonce($_POST['edit_trip_checklist_nonce'], 'edit_trip_checklist_action')) {
        $res['message'] = "Sorry, your nonce did not verify.";
    } else {
        $lockBike = get_user_meta($user->ID, 'gear_preferences_lock_bike', true);
        $lockRecord = get_user_meta($user->ID, 'gear_preferences_lock_record', true);
        if ($lockRecord == true) {
            $res['message'] = "Sorry, your can't update the information.";
            $res['status'] = false;
            echo json_encode($res);
            exit;
        }
        $update_to_ns = false;
        if (isset($_REQUEST['tt_save_medical_info']) && $_REQUEST['tt_save_medical_info'] == 'yes') {
            $input_posted = array('custentity_medications', 'custentity_medicalconditions', 'custentity_allergies', 'custentity_dietaryrestrictions');
            if ($input_posted && $_REQUEST) {
                foreach ($input_posted as $input_post) {
                    $medical_input = $_REQUEST[$input_post];
                    if (isset($medical_input) && $medical_input['boolean'] == 'yes' && !empty($medical_input['value'])) {
                        update_user_meta($user->ID, $input_post, $medical_input['value']);
                        $update_to_ns = true;
                    } else {
                        update_user_meta($user->ID, $input_post, '');
                    }
                }
            }
        }
        $bookingData = [
            'medical_conditions' => $_REQUEST['custentity_medicalconditions']['value'],
            'medications' => $_REQUEST['custentity_medications']['value'],
            'allergies' => $_REQUEST['custentity_allergies']['value'],
            'dietary_restrictions' => $_REQUEST['custentity_dietaryrestrictions']['value'],
            'emergency_contact_first_name' => $_REQUEST['emergency_contact_first_name'],
            'emergency_contact_last_name' => $_REQUEST['emergency_contact_last_name'],
            'emergency_contact_phone' => $_REQUEST['emergency_contact_phone'],
            'emergency_contact_relationship' => $_REQUEST['emergency_contact_relationship'],
            'rider_height' => $_REQUEST['tt-rider-height'],
            'pedal_selection' => $_REQUEST['tt-pedal-selection'],
            'helmet_selection' => $_REQUEST['tt-helmet-size'],
            'jersey_style' => $_REQUEST['tt-jerrsey-style'],
            'tt_jersey_size' => $_REQUEST['tt-jerrsey-size'],
            'passport_number' => $_REQUEST['passport_number'],
            'passport_issue_date' => $_REQUEST['passport_issue_date'],
            'passport_expiration_date' => $_REQUEST['passport_expiration_date'],
            'passport_place_of_issue' => $_REQUEST['passport_place_of_issue'],
            'full_name_on_passport' => $_REQUEST['full_name_on_passport'],
            'bike_selection' => $_REQUEST['bikeId'],
            'bike_type_id' => $_REQUEST['bikeTypeId'],
            'bike_id' => $_REQUEST['bikeId'],
            'bike_size' => $_REQUEST['tt-bike-size']
        ];
        if( empty($guest_email_address) ){
            $bookingData['guest_email_address'] = $user->user_email;
        }
        $shipping_add1 = isset($_REQUEST['shipping_address_1']) ? $_REQUEST['shipping_address_1'] : '';
        $shipping_add2 = isset($_REQUEST['shipping_address_2']) ? $_REQUEST['shipping_address_2'] : '';
        $shipping_city = isset($_REQUEST['shipping_city']) ? $_REQUEST['shipping_city'] : '';
        $shipping_state = isset($_REQUEST['shipping_state']) ? $_REQUEST['shipping_state'] : '';
        $shipping_country = isset($_REQUEST['shipping_country']) ? $_REQUEST['shipping_country'] : '';
        $shipping_postcode = isset($_REQUEST['shipping_postcode']) ? $_REQUEST['shipping_postcode'] : '';
        if( $guest_is_primary != 1 ){
            $bookingData['shipping_address_1'] = $shipping_add1;
            $bookingData['shipping_address_2'] = $shipping_add2;
            $bookingData['shipping_address_city'] = $shipping_city;
            $bookingData['shipping_address_state'] = $shipping_state;
            $bookingData['shipping_address_country'] = $shipping_country;
            $bookingData['shipping_address_zipcode'] = $shipping_postcode;
        }
        if (isset($_REQUEST['tt_save_shipping_info']) && $_REQUEST['tt_save_shipping_info'] == 'yes') {
            update_user_meta( $user->ID, "shipping_address_1", $shipping_add1 );
            update_user_meta( $user->ID, "shipping_address_2", $shipping_add2 );
            update_user_meta( $user->ID, "shipping_city", $shipping_city );
            update_user_meta( $user->ID, "shipping_state", $shipping_state );
            update_user_meta( $user->ID, "shipping_postcode", $shipping_postcode );
            update_user_meta( $user->ID, "shipping_country", $shipping_country );
        }
        if (isset($_REQUEST['tt_save_emergency_info']) && $_REQUEST['tt_save_emergency_info'] == 'yes') {
            update_user_meta($user->ID, 'custentity_emergencycontactfirstname', $_REQUEST['emergency_contact_first_name']);
            update_user_meta($user->ID, 'custentityemergencycontactlastname', $_REQUEST['emergency_contact_last_name']);
            update_user_meta($user->ID, 'custentity_emergencycontactphonenumber', $_REQUEST['emergency_contact_phone']);
            update_user_meta($user->ID, 'custentity_emergencycontactrelationship', $_REQUEST['emergency_contact_relationship']);
            $update_to_ns = true;
        }
        if (isset($_REQUEST['tt_save_gear_info']) && $_REQUEST['tt_save_gear_info'] == 'yes') {
            update_user_meta($user->ID, 'gear_preferences_rider_height', $_REQUEST['tt-rider-height']);
            update_user_meta($user->ID, 'gear_preferences_select_pedals', $_REQUEST['tt-pedal-selection']);
            update_user_meta($user->ID, 'gear_preferences_helmet_size', $_REQUEST['tt-helmet-size']);
            update_user_meta($user->ID, 'gear_preferences_jersey_style', $_REQUEST['tt-jerrsey-style']);
            update_user_meta($user->ID, 'gear_preferences_jersey_size', $_REQUEST['tt-jerrsey-size']);
            $update_to_ns = true;
        }
        $is_passport_update = true;
        if ($is_passport_update == true) {
            update_user_meta($user->ID, 'custentity_passport_number', $_REQUEST['passport_number']);
            update_user_meta($user->ID, 'custentity_passport_exp_date', $_REQUEST['passport_issue_date']);
            update_user_meta($user->ID, 'custentity_passport_issue_place', $_REQUEST['passport_expiration_date']);
            update_user_meta($user->ID, 'custentity_placeofbirth', $_REQUEST['passport_place_of_issue']);
            update_user_meta($user->ID, 'custentity_full_name_on_passport', $_REQUEST['full_name_on_passport']);
            $update_to_ns = true;
        }
        if (isset($_REQUEST['tt_save_bike_info']) && $_REQUEST['tt_save_bike_info'] == 'yes') {
            update_user_meta($user->ID, 'gear_preferences_bike_type', $_REQUEST['bikeTypeId']);
            update_user_meta($user->ID, 'gear_preferences_bike_size', $_REQUEST['tt-bike-size']);
            update_user_meta($user->ID, 'gear_preferences_bike', $_REQUEST['bikeId']);
            $update_to_ns = true;
        }
        //update user data to the NS
        if ($update_to_ns == true) {
            as_schedule_single_action(time(), 'tt_cron_syn_usermeta_ns', array( $user->ID, '[WP] - Update user from post booking' ));
        }
        $ns_user_id = get_user_meta($user->ID, 'ns_customer_internal_id', true);  
        if ($ns_user_id) {
            $order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
            $ns_bookingInfo = tt_get_ns_booking_details_by_order($order_id);
            $bookingId = $ns_bookingInfo['booking_id'];
            if ($bookingId) {
                $bookingInfo = $netSuiteClient->get('1304:2', array('bookingId' => $bookingId));
                $booking_Guests = isset($bookingInfo->guests) && $bookingInfo->guests ? $bookingInfo->guests : [];
                if ($booking_Guests) {
                    foreach ($booking_Guests as $booking_guest) {
                        $guestId = $booking_guest->guestId;
                        if ($guestId == $ns_user_id) {
                            $registrationId = $booking_guest->registrationId;
                            $ns_user_booking_args = [
                                'registrationId' => $registrationId,
                                'bikeId' => isset($_REQUEST['bikeId']) ? $_REQUEST['bikeId'] : '',
                                'saddleId' => isset($_REQUEST['saddleId']) ? $_REQUEST['saddleId'] : '',
                                'custentity_height' => isset($_REQUEST['tt-rider-height']) ? $_REQUEST['tt-rider-height'] : '',
                                'helmetId' => isset($_REQUEST['tt-helmet-size']) ? $_REQUEST['tt-helmet-size'] : '',
                                "pedalsId" => isset($_REQUEST['tt-pedal-selection']) ? $_REQUEST['tt-pedal-selection'] : '',
                                'jerseyId' => isset($_REQUEST['tt-jerrsey-size']) ? $_REQUEST['tt-jerrsey-size'] : '',
                                'ecFirstName' => isset($_REQUEST['emergency_contact_first_name']) ? $_REQUEST['emergency_contact_first_name'] : '',
                                'ecLastName' => isset($_REQUEST['emergency_contact_last_name']) ? $_REQUEST['emergency_contact_last_name'] : '',
                                'ecPhone' => isset($_REQUEST['emergency_contact_phone']) ? $_REQUEST['emergency_contact_phone'] : '',
                                'ecRelationship' => isset($_REQUEST['emergency_contact_relationship']) ? $_REQUEST['emergency_contact_relationship'] : '',
                                'medicalConditions' => isset($_REQUEST['custentity_medicalconditions']['value']) ? $_REQUEST['custentity_medicalconditions']['value'] : '',
                                'medications' => isset($_REQUEST['custentity_medications']['value']) ? $_REQUEST['custentity_medications']['value'] : '',
                                'allergies' => isset($_REQUEST['custentity_allergies']['value']) ? $_REQUEST['custentity_allergies']['value'] : 'allergies Demo content ',
                                'dietaryRestrictions' => isset($_REQUEST['custentity_dietaryrestrictions']['value']) ? $_REQUEST['custentity_dietaryrestrictions']['value'] : '',
                                'barReachFromSaddle' => isset($_REQUEST['bar_reach']) ? $_REQUEST['bar_reach'] : '',
                                'barHeightFromWheelCenter' => isset($_REQUEST['bar_height']) ? $_REQUEST['bar_height'] : '',
                            ];
                            $ns_bookingInfo = $netSuiteClient->post('1292:2', json_encode($ns_user_booking_args));
                            tt_add_error_log('[SuiteScript:1292] - Post booking', $ns_user_booking_args, $ns_bookingInfo);
                        }else{
                            //tt_add_error_log('GuestID from booking & WP NS_User_id doesnt match', array('ns_user_id' => $ns_user_id, 'First name' => $user->first_name, 'guestId' => $guestId, 'bookingId' => $bookingId), []);
                        }
                    }
                }
            }else{
                tt_add_error_log('[WP] - No Guest Booking ID found', array('ns_user_id' => $ns_user_id, 'First name' => $user->first_name), []);
            }
            //End : Update data in User Booking profile in NS
        }else{
            tt_add_error_log('[NetSuite] - User not found', array('user_id' => $user->ID, 'First name' => $user->first_name), []);
        }
        $bookingData['modified_at'] = date('Y-m-d H:i:s');
        $where['order_id'] = $_REQUEST['order_id'];
        if( $guest_email_address ){
            $where['guest_email_address'] = $user->user_email;
        }else{
            if( $user->ID ){
                $where['user_id'] = $user->ID;
            }
        }
        $is_updated = $wpdb->update($table_name, $bookingData, $where);
        $res['status'] = true;
        $res['error'] = $wpdb->last_query;
        $res['bookingData'] = $bookingData;
        $res['where'] = $where;
        $res['message'] = "Your Checklist information has been changed successfully!";
        $res['is_primary'] = $guest_is_primary && $guest_is_primary == 1 ? true: false;
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
function get_trek_user_checkout_data()
{
    $accepted_p_ids = tt_get_line_items_product_ids();
    $tt_posted = $tt_formatted = array();
    if (isset(WC()->cart->cart_contents)) {
        $cart = WC()->cart->cart_contents;
        foreach ($cart as $cart_item_id => $cart_item) {
            if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
                $tt_posted = isset($cart_item['trek_user_checkout_data']) ? $cart_item['trek_user_checkout_data'] : [];
                $tt_formatted = isset($cart_item['trek_user_formatted_checkout_data']) ? $cart_item['trek_user_formatted_checkout_data'] : [];
            }
        }
    }
    return array(
        'posted' => $tt_posted,
        'formatted' => $tt_formatted
    );
}
/* @return  : Allow only 1 product/Package in cart and replace it with new product.
 **/
add_filter('woocommerce_add_to_cart_validation', 'trek_woocom_cart_validation_cb', 10, 3);
function trek_woocom_cart_validation_cb($valid, $product_id, $quantity)
{
    $is_tt_valid = tt_get_cart_products_ids();
    $custom_fees_p_ids = tt_get_line_items_product_ids();
    if ($custom_fees_p_ids && !in_array($product_id, $custom_fees_p_ids)) {
        if (!empty(WC()->cart->get_cart()) && $is_tt_valid == false ) {
            WC()->cart->empty_cart();
        }
    }
    return $valid;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for Login Feature
 **/
add_action('wp_ajax_trek_login_action', 'trek_trek_login_action_cb');
add_action('wp_ajax_nopriv_trek_login_action', 'trek_trek_login_action_cb');
function trek_trek_login_action_cb()
{
    $http_referer = $_REQUEST['http_referer'];
    $page_id = url_to_postid($http_referer);
    $ref_sourceUrl = parse_url($http_referer);
    $site_urlParse = parse_url(site_url());
    $redirect_url = site_url('my-account');
    $tt_Data = tt_get_trip_pid_sku_from_cart();
    if ($ref_sourceUrl['host'] == $site_urlParse['host'] && get_post_type($page_id) == 'product' ) {
        if( isset($tt_Data['sku']) && $tt_Data['sku'] && isset($tt_Data['product_id']) && $tt_Data['product_id'] ){
            $redirect_url = trek_checkout_step_link(1);
        }else{
            $redirect_url = $http_referer;
        }
    }
    $res = array(
        'status' => false,
        'message' => '',
        'redirect' => $redirect_url
    );
    $email = $_REQUEST['email'];
    $password = $_REQUEST['password'];
    if (!isset($_POST['woocommerce-login-nonce']) || !wp_verify_nonce($_POST['woocommerce-login-nonce'], 'woocommerce-login')) {
        $res['message'] = "Sorry, your nonce did not verify.";
    } elseif (!isset($email) && empty($email)) {
        $res['message'] = "Please enter your email address";
    } elseif (!email_exists($email)) {
        $res['message'] = "That E-mail doesn't belong to any registered users on this site.";
    } else {
        $user_login = $email;
        if (is_email($email)) {
            $currentUser = get_user_by('email', $email);
            $user_login = $currentUser->user_login;
        }
        $creds = array(
            'user_login' => $user_login,
            'user_password' => esc_attr($password),
            'remember' => true
        );
        $user = wp_signon($creds, false);
        if (is_wp_error($user)) {
            $res['message'] = $user->get_error_message();
        } else {
            $res['status'] = true;
            $res['message'] = "You have successfully loggedin!";
        }
    }
    echo json_encode($res);
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
    'status' => false,
        'message' => '',
        'review_order' => ''
    );
    $accepted_p_ids = tt_get_line_items_product_ids();
    $fees_product_id = tt_create_line_item_product('TTWP23FEES');
    $is_fees_exist = [];
    $plan_id = 'TREKTRAVEL23';

    if( ! empty( get_field( 'plan_id', 'option' ) ) ) {
        $plan_id = get_field( 'plan_id', 'option' );
    }

    //Add travels data to Cart object
    $cart = WC()->cart->get_cart();
    $guest_insurance_data_arr = $guests_insurance_data = array();
    $guests_insurance_data['guest_email'] = array();
    foreach ($cart as $cart_item_id => $cart_item) {
        $product_id = $cart_item['product_id'];
        $product = wc_get_product($product_id);
        $sku = $product->get_sku();
        if (!in_array($product_id, $accepted_p_ids)) {
            $cart_item['trek_user_checkout_data'] = $_REQUEST;
            $cart_item['trek_user_checkout_data']['product_id'] = $product_id;
            $cart_item['trek_user_checkout_data']['sku'] = $product->get_sku();
            $cart_posted_data = $cart_item['trek_user_checkout_data'];
            $insuredReq  = isset($_REQUEST['trek_guest_insurance']) ? $_REQUEST['trek_guest_insurance'] : [];
            error_log("insuredReq " . json_encode($insuredReq));
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
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
        if( $sku == 'TTWP23FEES' ){
            //if ( $cart_item_id ) WC()->cart->remove_cart_item( $cart_item_id );
            //WC()->cart->cart_contents[$cart_item_id]['quantity'] = 0;
            $is_fees_exist[] = true;
        }
    }
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    //WC()->cart->maybe_set_cart_cookies();
    //Preparing insurance HTML
    $tt_checkoutData =  get_trek_user_checkout_data();
    $tt_posted = isset($tt_checkoutData['posted']) ? $tt_checkoutData['posted'] : [];
    $product_id = null;
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            $product_id = $cart_item['product_id'];
        }
        if( isset( $cart_item['product_id'] ) && $cart_item['product_id'] == 73798 ) {
            $supplement_fees = wc_get_product( 73798 );
        }
    }
    $product = wc_get_product($product_id);

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
    $insuredPerson = $insuredPerson_single = array();
    $effectiveDate = $expirationDate = '';
    if( $sdate_info && is_array($sdate_info) ){
        $effectiveDate = date('Y-m-d', strtotime(implode('-', $sdate_info)));
    }
    if( $edate_info && is_array($edate_info) ){
        $expirationDate = date('Y-m-d', strtotime(implode('-', $edate_info)));
    }
    $trek_insurance_args = [
        "coverage" => [
            "effectiveDate" => $effectiveDate,
            "expirationDate" => $expirationDate,
            "depositDate" => date('Y-m-d'),
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
    //$archinsuranceRes = tt_set_calculate_insurance_fees_api($trek_insurance_args);
    //$arcBasePremium = isset($archinsuranceRes['basePremium']) ? $archinsuranceRes['basePremium'] : 0;
    $arcBasePremium = $tt_total_insurance_amount && $tt_total_insurance_amount > 0 ? $tt_total_insurance_amount : 0;
    //echo 'demo 3242 3992';
    if( $insuredPersonCount > 0 ){
        if( !in_array(true, $is_fees_exist) ){
            WC()->cart->add_to_cart($fees_product_id, 1, 0, array(), array('tt_cart_custom_fees_price' => $arcBasePremium));
        }
    }
    //save cart Logic
    foreach ($cart as $cart_item_id => $cart_item) {
        if (isset($cart_item['product_id']) && $cart_item['product_id'] == $fees_product_id) {
            $product = wc_get_product($cart_item['product_id']);
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
            $sku = $product->get_sku();
            if ($sku == 'TTWP23FEES') {
                if (isset($cart_item['tt_cart_custom_fees_price']) && $cart_item['tt_cart_custom_fees_price'] > 0) {
                    if( in_array(true, $is_fees_exist) ){
                        $cart_item['data']->set_price($arcBasePremium);
                    }else{
                        $cart_item['data']->set_price($cart_item['tt_cart_custom_fees_price']);
                    }
                }
                WC()->cart->cart_contents[$cart_item_id]['tt_cart_custom_fees_price'] = $arcBasePremium;
            }
            WC()->cart->cart_contents[$cart_item_id]['quantity'] = 1;
        }
        if ( isset($cart_item['product_id'])  && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            $cart_item['trek_user_checkout_data']['trek_guest_insurance'] = $guest_insurance;
            $cart_item['trek_user_checkout_data']['insuredPerson'] = $is_travel_protection_count;
            $cart_item['trek_user_checkout_data']['tt_insurance_total_charges'] = $arcBasePremium;
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
    }
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    //WC()->cart->maybe_set_cart_cookies();
    //End: Save cart logic
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
    $insuredHTMLPopup = '';
    $checkout_insured_users = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php';
    if (is_readable($checkout_insured_users)) {
        $insuredHTMLPopup .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php');
    } else {
        $insuredHTMLPopup .= '<h3>Step 4</h3><p>checkout-insured-guests-popup.php form code is missing!</p>';
    }
    $insuredHTML = '';
    $checkout_insured_users = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests.php';
    if (is_readable($checkout_insured_users)) {
        $insuredHTML .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests.php');
    } else {
        $insuredHTML .= '<h3>Step 4</h3><p>checkout-insured-guests.php form code is missing!</p>';
    }
    $res['status'] = true;
    $res['fees_product_id'] = $fees_product_id;
    $res['guest_insurance_html'] = $insuredHTML;
    $res['insuredHTMLPopup'] = $insuredHTMLPopup;
    $res['review_order'] = $review_order_html;
    $res['payment_option'] = $payment_option_html;
    $res['arcBasePremium'] = $arcBasePremium;
    $res['message'] = "Your information has been changed successfully!";
    echo json_encode($res);
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

function trek_occupants($key)
{
    $trek_user_checkout_data = get_trek_user_checkout_data();
    $trek_user_checkout_posted = $trek_user_checkout_data['posted'];
    $occupants_opt = '';
    $primary_name = $trek_user_checkout_posted['shipping_first_name'] . ' ' . $trek_user_checkout_posted['shipping_last_name'];
    $occupants_opt .= '<option value="none" ' . ($key == 'none' ? 'selected' : '') . '>Please select</option>';
    $occupants_opt .= '<option value="0" ' . ($key == '0' ? 'selected' : '') . '>' . $primary_name . '</option>';
    if (isset($trek_user_checkout_posted['guests']) && !empty($trek_user_checkout_posted['guests'])) {
        foreach ($trek_user_checkout_posted['guests'] as $guest_id => $guest) {
            $selectedAtt = $guest_id == $key ? 'selected' : '';
            $occupants_opt .= '<option value="' . $guest_id . '" ' . $selectedAtt . '>' . $guest['guest_fname'] . ' ' . $guest['guest_lname'] . '</option>';
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
            'globalsubscriptionstatus',
            'custentity_addtotrektravelmailinglist',
            'custentity_contactmethod'
        );
        if ($communication_preferences_fields) {
            foreach ($communication_preferences_fields as $communication_preferences_field) {
                if (isset($_REQUEST[$communication_preferences_field]) && !empty($_REQUEST[$communication_preferences_field])) {
                    update_user_meta($user->ID, $communication_preferences_field, $_REQUEST[$communication_preferences_field]);
                } else {
                    update_user_meta($user->ID, $communication_preferences_field, '');
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
function tt_checkbooking_status($user_email, $ns_order_id) //old - $ns_user_id var in 1st args
{
    global $wpdb;
    $count = 0;
    $table_name = $wpdb->prefix . 'guest_bookings';
    $sql = "SELECT * from {$table_name} as gb WHERE 1=1";
    // if( $ns_user_id ){
    //     $sql .= " AND gb.netsuite_guest_registration_id = {$ns_user_id} ";
    // }
    if ($user_email) {
        $sql .= " AND gb.guest_email_address = '{$user_email}'";
    }
    if( $ns_order_id ){
        $sql .= " AND gb.guest_booking_id = {$ns_order_id} ";
    }
    if( $ns_order_id || $user_email ){
        $results = $wpdb->get_results($sql, ARRAY_A);
    }else{
        $results = [];
    }
    if ($results) {
        $count = count($results);
    }
    tt_add_error_log(
        'Check Booking Status', 
        ['ns_order_id' => $ns_order_id, 'user_email' => $user_email ],
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
    $other_blog_html = '';
    $blog_args = array(
        'post_type' => 'post',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'paged' => $_POST['paged'],
        'offset' => 1
    );
    $blogs = new WP_Query($blog_args);
    $max_pages = $blogs->max_num_pages;
    if ($blogs->have_posts()) {
        while ($blogs->have_posts()) {
            $blogs->the_post();
            $featured_Image = 'https://via.placeholder.com/70?text=Trek%20Travel';
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
    }
    $res['basePremium'] = $basePremium;
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
        if ($options) {
            $opts .= '<option value="" data-value="' . $optionId . '">Select ' . $listName . '</option>';

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

            foreach ($options as $option) {
                $selected = ($optionId == $option['optionId'] ? 'selected' : '');
                $opts .= '<option value="' . $option['optionId'] . '" ' . $selected . '>' . $option['optionValue'] . '</option>';
            }
        }
    }
    return $opts;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Ajax action for adding compare product Ids in @Session
 **/
add_action('wp_ajax_tt_apply_remove_coupan_action', 'trek_tt_apply_remove_coupan_action_cb');
add_action('wp_ajax_nopriv_tt_apply_remove_coupan_action', 'trek_tt_apply_remove_coupan_action_cb');
function trek_tt_apply_remove_coupan_action_cb()
{
    $res['status'] = false;
    $res['message'] = '';
    $res['html'] = '';
    $coupon_code = $_REQUEST['coupon_code'];
    $type = $_REQUEST['type'];
    $resHtml = '';
    $is_applied = false;
    if ($coupon_code && $type) {
        if ($type == 'add') {
            if (!WC()->cart->has_discount($coupon_code)) {
                $coupon = new WC_Coupon($coupon_code);
                $coupon_post = get_post($coupon->id);
                if ($coupon_post) {
                    WC()->cart->apply_coupon($coupon_code);
                    $is_applied = true;
                }
            }
        } else {
            WC()->cart->remove_coupon($coupon_code);
            $is_applied = false;
        }
        WC()->cart->calculate_totals();
    }
    $res['is_applied'] = $is_applied;
    //Begin: Save coupan code Data in Cart Session
    $accepted_p_ids = tt_get_line_items_product_ids();
    $cart = WC()->cart->cart_contents;
    foreach ($cart as $cart_item_id => $cart_item) {
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            if ($is_applied == true) {
                $cart_item['trek_user_checkout_data']['coupon_code'] = $coupon_code;
            } else {
                $cart_item['trek_user_checkout_data']['coupon_code'] = '';
            }
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
    }
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
    //End: Save coupan code Data in Cart Session
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
add_action('woocommerce_cart_calculate_fees', 'tt_woocommerce_cart_calculate_fees_cb');
function tt_woocommerce_cart_calculate_fees_cb($cart)
{
    
    $trek_user_checkout_data =  get_trek_user_checkout_data();
    $tt_posted = isset($trek_user_checkout_data['posted']) ? $trek_user_checkout_data['posted'] : [];
    $arch_info = tt_get_insurance_info($tt_posted);
    $insuredPerson = isset($arch_info['count']) ? $arch_info['count'] : 0;
    $insurance_amount = isset($arch_info['amount']) ? $arch_info['amount'] : 0;
    $cartobj = WC()->cart->cart_contents;
    $s_product_id = tt_create_line_item_product('TTWP23SUPP');
    $occupants_private = (isset($tt_posted['occupants']['private']) ? $tt_posted['occupants']['private'] : array());
    $occupants_roommate = (isset($tt_posted['occupants']['roommate']) ? $tt_posted['occupants']['roommate'] : array());
    $suppliment_counts = intval(count($occupants_private)) + intval(count($occupants_roommate));
    foreach ($cartobj as $cartobj_item_id => $cartobj_item) {
        $_product = apply_filters('woocommerce_cart_item_product', $cartobj_item['data'], $cartobj_item, $cartobj_item_id);
        $sku = $_product->get_sku();
        //if ( isset($cartobj_item['product_id']) && $cartobj_item['product_id'] == $s_product_id) {
        if ( isset($cartobj_item['product_id']) ) {    
            WC()->cart->cart_contents[$cartobj_item_id] = $cartobj_item;
            if ($sku == 'TTWP23FEES'){
                if( $insuredPerson > 0 && $insurance_amount > 0  ){
                    WC()->cart->cart_contents[$cartobj_item_id]['quantity'] = 1;
                    $cartobj_item['data']->set_price($insurance_amount);
                }else{
                    WC()->cart->cart_contents[$cartobj_item_id]['quantity'] = 0;
                    $cartobj_item['data']->set_price(0);
                }
            }
            if ( $sku == 'TTWP23SUPP' || $sku == 'TTWP23UPGRADES') {
                if (isset($cartobj_item['tt_cart_custom_fees_price']) && $cartobj_item['tt_cart_custom_fees_price'] > 0) {
                    $cartobj_item['data']->set_price($cartobj_item['tt_cart_custom_fees_price']);
                }
            }
            if( $sku == 'TTWP23SUPP' ){
                WC()->cart->cart_contents[$cartobj_item_id]['quantity'] = $suppliment_counts;
            }
        }
    }
    
}
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
    function tt_get_user_room_index_by_user_key($array, $key)
    {
        $room_index = null;
        if ($array) {
            foreach ($array as $room_type => $user_ids) {
                if (($room_type == 'double1Bed' || $room_type == 'double2Beds')) {
                    if (isset($user_ids) && $user_ids) {
                        foreach ($user_ids as $room_id => $user__inner_id) {
                            if (in_array($key, $user__inner_id)) {
                                $room_index = $room_id;
                            }
                        }
                    }
                } else {
                    if ($user_ids) {
                        foreach ($user_ids as $room_id => $user__inner_ids) {
                            if ($key ==  $user__inner_ids) {
                                $room_index = $room_id;
                            }
                        }
                    }
                }
                //&& is_array($user_ids[0]) == true 
                // if (( $room_type == 'single' || $room_type == 'double2Beds' ))  { 
                //     if (isset($user_ids) && $user_ids) {
                //         foreach ($user_ids as $room_id => $user__inner_id) {
                //             if (in_array($key, $user__inner_id)) {
                //                 $room_index = $room_id;
                //             }
                //         }
                //     }
                // } else {
                //     if ($user_ids) {
                //         foreach ($user_ids as $room_id => $user__inner_ids) {
                //             if (in_array($key, $user__inner_ids)) {
                //                 $room_index = $room_id;
                //             }
                //         }
                //     }
                // }
            }
        }
        $roomsResults = [
            'rooms' => $array,
            'room_index' => $room_index,
            'key' => $key
        ];
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
    function tt_update_user_booking_info($order_id = NULL, $ns_booking_response="")
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
            if (isset($ns_booking_response_arr['savedData']) && !empty($ns_booking_response_arr['savedData'])) {
                $bookingId = isset($ns_booking_response_arr['savedData']['bookingId']) ? $ns_booking_response_arr['savedData']['bookingId'] : '';
                $isDraftBooking = isset($ns_booking_response_arr['savedData']['isDraftBooking']) ? $ns_booking_response_arr['savedData']['isDraftBooking'] : '';
                $ConfirmEmail = isset($ns_booking_response_arr['savedData']['shouldSendDraftConfirmEmail']) ? $ns_booking_response_arr['savedData']['shouldSendDraftConfirmEmail'] : '';
                $guests = isset($ns_booking_response_arr['savedData']['guests']) ? $ns_booking_response_arr['savedData']['guests'] : array();
            }
        }
        if ($bookingId == '' || $bookingId == null) {
            $ns_booking_status = 0;
        }
        $bookingData = [
            'guest_booking_id' => $bookingId,
            'isDraftBooking' => $isDraftBooking,
            'shouldSendDraftConfirmEmail' => $ConfirmEmail,
            'ns_booking_response' => json_encode($ns_booking_response_arr),
            'ns_booking_status' => $ns_booking_status,
            'modified_at' => date('Y-m-d H:i:s')
        ];
        update_post_meta($order_id, TT_WC_META_PREFIX.'guest_booking_id', $bookingId);
        if ($guests && is_array($guests) && !empty($guests)) {
            foreach ($guests as $guest_index => $guest) {
                $releaseFormId = isset($guests[$guest_index]['releaseFormId']) ? $guests[$guest_index]['releaseFormId'] : '';
                $bookingData['releaseFormId'] = $releaseFormId;
                if( $guest_index == 0 ){
                    update_post_meta($order_id, TT_WC_META_PREFIX.'releaseFormId', $releaseFormId);
                }
                $bookingData['guestRegistrationId'] = isset($guests[$guest_index]['guestRegistrationId']) ? $guests[$guest_index]['guestRegistrationId'] : '';
                if ($guest_index != 0) {
                    $bookingData['netsuite_guest_registration_id'] = isset($guests[$guest_index]['guestId']) ? $guests[$guest_index]['guestId'] : '';
                    $where['guest_index_id'] = $guest_index;
                }
                $wpdb->update($table_name, $bookingData, $where);
            }
        } else {
            $wpdb->update($table_name, $bookingData, $where);
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
            if ($results) {
                $ids = array_column($results, 'post_id');
                set_transient( 'tt_line_item_fees_product', $ids, DAY_IN_SECONDS );
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
add_action('woocommerce_before_calculate_totals', 'tt_woocommerce_before_calculate_totals_cb');
function tt_woocommerce_before_calculate_totals_cb($cart_object)
{
    $accepted_p_ids = tt_get_line_items_product_ids();
    $bike_upgrade_qty = 0;
    $tt_checkoutData =  get_trek_user_checkout_data();
    $tt_posted = isset($tt_checkoutData['posted']) ? $tt_checkoutData['posted'] : array();
    $arch_info = tt_get_insurance_info($tt_posted);
    $insuredPerson = isset($arch_info['count']) ? $arch_info['count'] : 0;
    $insurance_amount = isset($arch_info['amount']) ? $arch_info['amount'] : 0;
    $occupants_private = (isset($tt_posted['occupants']['private']) ? $tt_posted['occupants']['private'] : array());
    $occupants_roommate = (isset($tt_posted['occupants']['roommate']) ? $tt_posted['occupants']['roommate'] : array());
    $suppliment_counts = intval(count($occupants_private)) + intval(count($occupants_roommate));
    if (isset($tt_posted['bike_gears']) && $tt_posted['bike_gears']) {
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
    foreach ($cart_object->get_cart() as $item_key => $item) {
        if( isset($item['product_id']) && in_array($item['product_id'], $accepted_p_ids) ){
            $product = wc_get_product($item['product_id']);
            if( $product ){
                $sku = $product->get_sku();
                if ($sku == 'TTWP23SUPP' && $suppliment_counts > 0 ) {
                    WC()->cart->cart_contents[$item_key]['quantity'] = $suppliment_counts;
                }
                if ($sku == 'TTWP23UPGRADES' && $bike_upgrade_qty > 0 ) {
                    WC()->cart->cart_contents[$item_key]['quantity'] = $bike_upgrade_qty;
                }
                if ($sku == 'TTWP23FEES') {
                    if( $insuredPerson > 0 && $insurance_amount > 0  ){
                        WC()->cart->cart_contents[$item_key]['quantity'] = 1;
                        $item['data']->set_price($insurance_amount);
                    }else{
                        WC()->cart->cart_contents[$item_key]['quantity'] = 0;
                        $item['data']->set_price(0);
                    }
                }
                if ( $sku == 'TTWP23SUPP' || $sku == 'TTWP23UPGRADES') {
                    if (isset($item['tt_cart_custom_fees_price']) && $item['tt_cart_custom_fees_price'] > 0) {
                        $item['data']->set_price($item['tt_cart_custom_fees_price']);
                    }
                    if ($sku == 'TTWP23SUPP' && $suppliment_counts > 0 ) {
                        WC()->cart->cart_contents[$item_key]['quantity'] = $suppliment_counts;
                    }
                    if ($sku == 'TTWP23UPGRADES' && $bike_upgrade_qty > 0 ) {
                        WC()->cart->cart_contents[$item_key]['quantity'] = $bike_upgrade_qty;
                    }
                }
            }
        }
    }
}
add_action('wp_ajax_tt_save_occupants_ajax_action', 'trek_tt_save_occupants_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_save_occupants_ajax_action', 'trek_tt_save_occupants_ajax_action_cb');
function trek_tt_save_occupants_ajax_action_cb()
{
    $accepted_p_ids = tt_get_line_items_product_ids();
    $cart = WC()->cart->cart_contents;
    $s_product_id = tt_create_line_item_product('TTWP23SUPP');
    $occupants_private = (isset($_REQUEST['occupants']['private']) ? $_REQUEST['occupants']['private'] : array());
    $occupants_roommate = (isset($_REQUEST['occupants']['roommate']) ? $_REQUEST['occupants']['roommate'] : array());
    $suppliment_counts = count($occupants_private) + count($occupants_roommate);
    $trip_sku = tt_get_trip_pid_sku_from_cart();
    $singleSupplementPrice = tt_get_local_trips_detail('singleSupplementPrice', '', $trip_sku['sku'], true);
    if ($singleSupplementPrice && $singleSupplementPrice > 0) {
        WC()->cart->add_to_cart($s_product_id, $suppliment_counts, 0, array(), array('tt_cart_custom_fees_price' => $singleSupplementPrice));
    }
    foreach ($cart as $cart_item_id => $cart_item) {
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_id);
        $_product_name = $_product->get_name();
        $product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
        $sku = $_product->get_sku();
        if ( isset($cart_item['product_id']) && $cart_item['product_id'] == $s_product_id) {
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
            if ($sku == 'TTWP23SUPP') {
                if (isset($cart_item['tt_cart_custom_fees_price']) && $cart_item['tt_cart_custom_fees_price'] > 0) {
                    $cart_item['data']->set_price($cart_item['tt_cart_custom_fees_price']);
                    $singleSupplementPrice = $cart_item['tt_cart_custom_fees_price'];
                }
                WC()->cart->cart_contents[$cart_item_id]['quantity'] = $suppliment_counts;
            }
        }
        if (!in_array($product_id, $accepted_p_ids)) {
            $cart_posted_data = $cart_item['trek_user_checkout_data'];
            $guest_req = (isset($_REQUEST['guests']) ? $_REQUEST['guests'] : $cart_posted_data['guests']);
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
                            $guests_bikes_data[] = array_merge($inner_guest_arr, $trek_bike_gear_inner_guest);
                        }
                    }
                }
                $bikes_cart_item_data['cart_item_data'] = $guests_bikes_data;
            }
            $cart_item['trek_user_formatted_checkout_data'][1] = $bikes_cart_item_data;
            $cart_item['trek_user_checkout_data']['occupants'] = isset($_REQUEST['occupants']) ? $_REQUEST['occupants'] : array();
            $cart_item['trek_user_checkout_data']['private'] = isset($_REQUEST['private']) ? $_REQUEST['private'] : 0;
            $cart_item['trek_user_checkout_data']['roommate'] = isset($_REQUEST['roommate']) ? $_REQUEST['roommate'] : 0;
            $cart_item['trek_user_checkout_data']['tt_single_supplement_charges'] = $singleSupplementPrice;
            $cart_item['trek_user_checkout_data']['tt_single_supplement_qty'] = $suppliment_counts;
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
    } 
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
    $review_order_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if (is_readable($review_order)) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $stepHTML = '';
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
            $stepHTML .= wc_get_template_html('woocommerce/checkout/checkout-bikes.php');
        } else {
            $stepHTML .=  '<h3>Step 2</h3><p>Checkout Bike form code is missing!</p>';
        }
    }
    echo json_encode(
        array(
            'status' => true,
            'review_order' => $review_order_html,
            'stepHTML' => $stepHTML,
            'step' => $_REQUEST['step'],
            'message' => 'Trek checkout occupants data saved!'
        )
    );
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Get all bike details from local DB
 **/
if (!function_exists('tt_get_local_bike_detail')) {
    function tt_get_local_bike_detail($tripCode = '', $bikeId = '', $tripId = '')
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'netsuite_trip_bikes';
        $sql = "SELECT * from {$table_name} as ts WHERE ts.tripCode = '{$tripCode}' ";
        if ($bikeId) {
            $sql .= " AND ts.bikeId = '{$bikeId}'";
        }
        if ($tripId) {
            $sql .= " AND ts.tripId = '{$tripId}'";
        }
        $results = $wpdb->get_results($sql, ARRAY_A);
        return $results;
    }
}
function tt_get_parent_trip_id_by_child_sku($child_sku=""){
    $parent_product_id = '';
    $itinerary_code_arr = [];
    if( $child_sku && wc_get_product_id_by_sku($child_sku) ){
        $child_product_id = wc_get_product_id_by_sku($child_sku);
        if( $child_product_id ){
            $itineraryCode = get_post_meta($child_product_id, TT_WC_META_PREFIX.'itineraryCode', true);
            if($itineraryCode && $child_sku ){
                $itinerary_code_arr = explode($itineraryCode, $child_sku);
            }
            if($itinerary_code_arr && isset($itinerary_code_arr[0]) && $itineraryCode ){
                $parent_product_sku = $itinerary_code_arr[0] . $itineraryCode;
                $parent_product_id = wc_get_product_id_by_sku($parent_product_sku);
                if( !$parent_product_id){
                    $parent_product_id = wc_get_product_id_by_sku($itineraryCode);
                }
            }
        }
    }
    return $parent_product_id;
}
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
    $product_image_url = 'https://via.placeholder.com/150?text=Trek Travel';
    $parent_product_id = tt_get_parent_trip_id_by_child_sku($sku);
    if ($parent_product_id) {
        if( has_post_thumbnail($parent_product_id) ){
            $product_image_url = get_the_post_thumbnail_url($parent_product_id);
        }
        $parent_trip_link = get_the_permalink($parent_product_id) ? get_the_permalink($parent_product_id) : 'javascript:';
    }
    return [
        'sku' => $sku,
        'parent_rider_level' => isset($parent_rider_level->level) ? $parent_rider_level->level : '',
        'rider_level_text' => $rider_level_text,
        'product_id' => $product_id,
        'ns_trip_Id' => $ns_trip_Id,
        'parent_product_id' => $parent_product_id,
        'parent_trip_link' => $parent_trip_link,
        'parent_trip_image' => $product_image_url,
        'tt_posted' => $tt_posted,
        'tt_formatted_data' => $tt_formatted_data
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
function tt_get_bikes_by_trip_info($tripId = '', $tripCode = '', $bikeTypeId = '', $s_bike_size_id = '', $s_bike_type_id = '',$bikeID='')
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'netsuite_trip_bikes';
    $sql = "SELECT * from {$table_name} as ts WHERE ts.tripCode = '{$tripCode}' ";
    // if ($tripId) {
    //     $sql .= " AND ts.tripId = '{$tripId}'";
    // }
    if ($bikeID) {
        $sql .= " AND ts.bikeId = '{$bikeID}'";
    }
    $bike_size_opts = '<option value="">Select bike size</option>';
    $bike_Type_opts = '<option value="">Select bike type</option>';
    $bikes_arr = $wpdb->get_results($sql, ARRAY_A);
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
                    $option_disabled = '';
                } else {
                    $option_disabled = 'disabled';
                }
                if ($bike_size_id && $bike_size_name) {
                    $selected = ($bike_size_id == $s_bike_size_id ? 'selected' : '');
                    $bike_size_opts .= '<option ' . $option_disabled . $selected . ' value="' . $bike_size_id . '">' . $bike_size_name . '</option>';
                }
                if ($bike_type_id && $bike_type_name) {
                    $selected1 = ($loop_bikeId == $s_bike_type_id ? 'selected' : '');
                    $bike_Type_opts .= '<option ' . $option_disabled . $selected1 . ' value="' . $bike_type_id . '">' . $bike_type_name . '</option>';
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
function tt_get_bikes_by_trip_info_pbc($tripId = '', $tripCode = '', $bikeTypeId = '', $s_bike_size_id = '', $s_bike_type_id = '',$bikeID='')
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'netsuite_trip_bikes';
    $sql = "SELECT * from {$table_name} as ts WHERE ts.tripCode = '{$tripCode}' ";
    if ($tripId) {
        $sql .= " AND ts.tripId = '{$tripId}'";
    }
    if ($bikeID) {
        $sql .= " AND ts.bikeId = '{$bikeID}'";
    }
    $bike_size_opts = '<option value="">Select bike size</option>';
    $bike_Type_opts = '<option value="">Select bike type</option>';
    $bikes_arr = $wpdb->get_results($sql, ARRAY_A);
    if ($bikes_arr) {
        foreach ($bikes_arr as $bike_info) {
            $bike_available = $bike_info['available'];
            $bikeSizeObj = json_decode($bike_info['bikeSize'], true);
            $bikeTypeObj = json_decode($bike_info['bikeModel'], true);
            $bike_size_id = $bikeSizeObj['id'];
            $bike_size_name = $bikeSizeObj['name'];
            $bike_type_id = $bikeTypeObj['id'];
            if ($bike_type_id == $bikeTypeId && $bike_available > 0) {
                $bike_type_name = $bikeTypeObj['name'];
                $loop_bikeId = $bike_info['bikeId'];
                if ($bike_size_id && $bike_size_name) {
                    $selected = ($bike_size_id == $s_bike_size_id ? 'selected' : '');
                    $bike_size_opts .= '<option ' . $selected . ' value="' . $bike_size_id . '">' . $bike_size_name . '</option>';
                }
                if ($bike_type_id && $bike_type_name) {
                    $selected1 = ($loop_bikeId == $s_bike_type_id ? 'selected' : '');
                    $bike_Type_opts .= '<option ' . $selected1 . ' value="' . $bike_type_id . '">' . $bike_type_name . '</option>';
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
function tt_get_bike_id_by_args($tripId = '', $tripCode = '', $bikeTypeId = '', $bike_size = '')
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'netsuite_trip_bikes';
    $sql = "SELECT * from {$table_name} as ts WHERE ts.tripCode = '{$tripCode}' ";
    if ($tripId) {
        $sql .= " AND ts.tripId = '{$tripId}'";
    }
    $bikes_arr = $wpdb->get_results($sql, ARRAY_A);
    if ($bikes_arr) {
        foreach ($bikes_arr as $bike_info) {
            $bikeSizeObj = json_decode($bike_info['bikeSize'], true);
            $bikeTypeObj = json_decode($bike_info['bikeModel'], true);
            $bike_size_id = $bikeSizeObj['id'];
            $bike_type_id = $bikeTypeObj['id'];
            if ($bike_type_id == $bikeTypeId && $bike_size_id ==  $bike_size) {
                $bike_id = $bike_info['bikeId'];
            }
        }
    }
    return [
        'status' => true,
        'bike_id' => $bike_id
    ];
}
function get_bike_sizes_by_sku($sku = '', $option_id="")
{
    $bike_opts = '';
    if ($sku) {
        $bikes = tt_get_local_trips_detail('bikes', '', $sku, true);
        $bikes_arr = json_decode($bikes, true);
        if ($bikes_arr) {
            $bike_opts .= '<option value="">Select bike size</option>';
            foreach ($bikes_arr as $bike_info) {
                $bike_available = $bike_info['available'];
                $bike_size_id = $bike_info['bikeSize']['id'];
                $bike_size_name = $bike_info['bikeSize']['name'];
                if ($bike_size_id) {
                    $selected = ($option_id == $bike_size_id ? 'selected' : '');
                    $disabled = ($option_id ? '' : 'disabled');
                    $bike_opts .= '<option ' . $selected . ' ' . $disabled . ' value="' . $bike_size_id . '">' . $bike_size_name . '</option>';
                } else {
                    $bike_opts .= '<option value="">Not available</option>';
                }
            }
        }
    } else {
        $bike_opts .= '<option value="">Not available</option>';
    }
    return $bike_opts;
}
function get_bike_sizes_by_sku_bikeID($sku = '', $bike_id="", $selected_bike_id="")
{
    $bike_opts = '';
    if ($sku) {
        $bikes = tt_get_local_bike_detail($sku, $bike_id);
        if ($bikes) {
            $bike_opts .= '<option value="">Select bike size</option>';
            foreach ($bikes as $bike_info) {
                $bikeSize = json_decode($bike_info['bikeSize']);
                $bikeSizeId = $bikeSize['id'];
                $bikeSizeName = $bikeSize['name'];
                if ($bikeSizeId) {
                    $selected = ($selected_bike_id == $bikeSizeId ? 'selected' : '');
                    $bike_opts .= '<option ' . $selected . ' value="' . $bikeSizeId . '">' . $bikeSizeName . '</option>';
                }
            }
        }
    } else {
        $bike_opts .= '<option value="">Not available</option>';
    }
    return $bike_opts;
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
        $sql = "SELECT pm.post_id from {$table_name} as pm WHERE pm.{$meta_key} = '{$meta_value}' ";
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
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Occupancy selection popup
 **/
if (!function_exists('tt_occupant_selection_popup')) {
    function tt_occupant_selection_popup($request)
    {
        $occupant_popup_html = '';
        $has_occupants = false;
        $single = isset($request['single']) ? $request['single'] : 0;
        $double = isset($request['double']) ? $request['double'] : 0;
        $private = isset($request['private']) ? $request['private'] : 0;
        $roommate = isset($request['roommate']) ? $request['roommate'] : 0;
        if ($single) {
            $iter = 0;
            $cols = 2;
            $fields_size = $single - 1;
            for ($i = 0; $i < ($single * 2); $i++) {
                if ($iter % $cols == 0) {
                    $occupant_popup_html .= '<div class="checkout-hotel-modal__occupants-selection">';
                    $occupant_popup_html .= '<p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type">Room with 1 Beds <span class="bed-icon"></span></p>
                            <p class="fw-medium fs-md lh-md">Who will be in this room?</p>';
                }
                $single_selected = isset($request['occupants']['single'][$i]) ? $request['occupants']['single'][$i] : "none";
                $occupant_popup_html .= '<div class="form-floating">
                        <select required="required" name="occupants[single][' . $i . ']" class="form-select" id="floatingSelectSingle" aria-label="Floating label select example">
                            ' . trek_occupants($single_selected) . '
                        </select>
                        <label for="floatingSelectSingle">Select Occupant</label>
                    </div>';
                if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                    $occupant_popup_html .= '</div>';
                }
                $iter++;
            }
            $has_occupants = true;
        }
        if ($double) {
            $iter = 0;
            $cols = 2;
            $fields_size = $double - 1;
            for ($i = 0; $i < ($double * 2); $i++) {
                if ($iter % $cols == 0) {
                    $occupant_popup_html .= '<div class="checkout-hotel-modal__occupants-selection">';
                    $occupant_popup_html .= '<p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type">Room with 2 Beds <span class="bed-icon"></span><span class="bed-icon ms-1"></span></p>
                            <p class="fw-medium fs-md lh-md">Who will be in this room?</p>';
                }
                $double_selected = isset($request['occupants']['double'][$i]) ? $request['occupants']['double'][$i] : "none";
                $occupant_popup_html .= '<div class="form-floating">
                        <select name="occupants[double][' . $i . ']" class="form-select" id="floatingSelectdouble" aria-label="Floating label select example">
                            ' . trek_occupants($double_selected) . '
                        </select>
                        <label for="floatingSelectdouble">Select Occupant</label>
                    </div>';
                if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                    $occupant_popup_html .= '</div>';
                }
                $iter++;
            }
            $has_occupants = true;
        }
        if ($private) {
            for ($i = 0; $i < $private; $i++) {
                $private_selected = isset($request['occupants']['private'][$i]) ? $request['occupants']['private'][$i] : "none";
                $occupant_popup_html .= '<div class="checkout-hotel-modal__occupants-selection">';
                $occupant_popup_html .= '<p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type">Private <span class="bed-icon"></span></p>
                    <p class="fw-medium fs-md lh-md">Who will be in this room?</p>';
                $occupant_popup_html .= '<div class="form-floating">
                        <select name="occupants[private][' . $i . ']" class="form-select" id="floatingSelectprivate" aria-label="Floating label select example">
                            ' . trek_occupants($private_selected) . '
                        </select>
                        <label for="floatingSelectprivate">Select Occupant</label>
                    </div>';
                $occupant_popup_html .= '</div>';
            }
            $has_occupants = true;
        }
        if ($roommate) {
            for ($i = 0; $i < $roommate; $i++) {
                $roommate_selected = isset($request['occupants']['roommate'][$i]) ? $request['occupants']['roommate'][$i] : "none";
                $occupant_popup_html .= '<div class="checkout-hotel-modal__occupants-selection">';
                $occupant_popup_html .= '<p class="fw-medium fs-xl lh-lg checkout-step-two-hotel__room-type">Open to Roommate <span class="bed-icon"></span><span class="bed-icon ms-1"></span></p>
                    <p class="fw-medium fs-md lh-md">Who will be in this room?</p>';
                $occupant_popup_html .= '<div class="form-floating">
                        <select name="occupants[roommate][' . $i . ']" class="form-select" id="floatingSelectroommate" aria-label="Floating label select example">
                            ' . trek_occupants($roommate_selected) . '
                        </select>
                        <label for="floatingSelectroommate">Select Occupant</label>
                    </div>';
                $occupant_popup_html .= '</div>';
            }
            $has_occupants = true;
        }
        if ($has_occupants == false) {
            $occupant_popup_html .= '<h3>Please add room first for occupant selection!</h3>';
        }
        return $occupant_popup_html;
    }
}
add_action('user_register', 'tt_sync_user_metadata_from_ns_cb', 10, 1);
function tt_sync_user_metadata_from_ns_cb($user_id)
{
    $netSuiteClient = new NetSuiteClient();
    sleep(5);
    $ns_user_id = get_user_meta($user_id, 'ns_customer_internal_id', true);
    if ($ns_user_id) {
        $ns_user_ids = array($ns_user_id);
        $ns_booking_result = $netSuiteClient->get('1294:2', array('registrationIds' => implode(',', $ns_user_ids)));
        tt_add_error_log('NEW USER', ['wp_user_id' => $user_id, 'ns_user_id' => $ns_user_id ], $ns_booking_result);
        if ($ns_booking_result) {
            foreach ($ns_booking_result as $ns_guest_info) {
                $registrationId = $ns_guest_info->registrationId;
                $isPrimary = $ns_guest_info->isPrimary;
                $guestId = $ns_guest_info->guestId;
                $guestName = $ns_guest_info->guestName;
                $bikeId = $ns_guest_info->bikeId;
                $helmetId = $ns_guest_info->helmetId;
                $pedalsId = $ns_guest_info->pedalsId;
                $saddleId = $ns_guest_info->saddleId;
                $saddleHeight = $ns_guest_info->saddleHeight;
                $barReachFromSaddle = $ns_guest_info->barReachFromSaddle;
                $barHeightFromWheelCenter = $ns_guest_info->barHeightFromWheelCenter;
                $jerseyId = $ns_guest_info->jerseyId;
                $tshirtSizeId = $ns_guest_info->tshirtSizeId;
                $raceFitJerseyId = $ns_guest_info->raceFitJerseyId;
                $shortsBibSizeId = $ns_guest_info->shortsBibSizeId;
                $ecFirstName = $ns_guest_info->ecFirstName;
                $ecLastName = $ns_guest_info->ecLastName;
                $ecPhone = $ns_guest_info->ecPhone;
                $ecRelationship = $ns_guest_info->ecRelationship;
                $medicalConditions = $ns_guest_info->medicalConditions;
                $medications = $ns_guest_info->medications;
                $allergies = $ns_guest_info->allergies;
                $dietaryRestrictions = $ns_guest_info->dietaryRestrictions;
                $dietaryPreferences = $ns_guest_info->dietaryPreferences;
                $lockRecord = $ns_guest_info->lockRecord;
                $lockBike = $ns_guest_info->lockBike;
                $waiverAccepted = $ns_guest_info->waiverAccepted;
                if ($ecFirstName) {
                    update_user_meta($user_id, 'custentity_emergencycontactfirstname', $ecFirstName);
                }
                if ($ecLastName) {
                    update_user_meta($user_id, 'custentity_emergencycontactlastname', $ecLastName);
                }
                if ($ecPhone) {
                    update_user_meta($user_id, 'custentity_emergencycontactphonenumber', $ecPhone);
                }
                if ($ecRelationship) {
                    update_user_meta($user_id, 'custentity_emergencycontactrelationship', $ecRelationship);
                }
                if ($medicalConditions) {
                    update_user_meta($user_id, 'custentity_medicalconditions', $medicalConditions);
                }
                if ($medications) {
                    update_user_meta($user_id, 'custentity_medications', $medications);
                }
                if ($allergies) {
                    update_user_meta($user_id, 'custentity_allergies', $allergies);
                }
                if ($dietaryRestrictions) {
                    update_user_meta($user_id, 'custentity_dietaryrestrictions', $dietaryRestrictions);
                }
                if ($dietaryPreferences) {
                    update_user_meta($user_id, 'custentity_dietary_preferences', $dietaryPreferences);
                }
                if ($bikeId) {
                    update_user_meta($user_id, 'gear_preferences_bike_type', $bikeId);
                }
                if ($helmetId) {
                    update_user_meta($user_id, 'gear_preferences_helmet_size', $helmetId);
                }
                if ($pedalsId) {
                    update_user_meta($user_id, 'gear_preferences_select_pedals', $pedalsId);
                }
                if ($saddleId) {
                    update_user_meta($user_id, 'gear_preferences_select_saddles', $saddleId);
                }
                if ($saddleHeight) {
                    update_user_meta($user_id, 'gear_preferences_saddle_height', $saddleHeight);
                }
                if ($barReachFromSaddle) {
                    update_user_meta($user_id, 'gear_preferences_bar_reach', $barReachFromSaddle);
                }
                if ($barHeightFromWheelCenter) {
                    update_user_meta($user_id, 'gear_preferences_bar_height', $barHeightFromWheelCenter);
                }
                if ($jerseyId) {
                    update_user_meta($user_id, 'gear_preferences_jersey_style', $jerseyId);
                }
                if ($tshirtSizeId) {
                    update_user_meta($user_id, 'gear_preferences_jersey_size', $tshirtSizeId);
                }
                if ($lockBike) {
                    update_user_meta($user_id, 'gear_preferences_lock_bike', $lockBike);
                }
                if ($lockRecord) {
                    update_user_meta($user_id, 'gear_preferences_lock_record', $lockRecord);
                }
                if ($waiverAccepted) {
                    update_user_meta($user_id, 'custentity_waiver_accepted', $waiverAccepted);
                }
                if ($raceFitJerseyId) {
                    update_user_meta($user_id, 'gear_preferences_race_fit_jersey_id', $raceFitJerseyId);
                }
                if ($shortsBibSizeId) {
                    update_user_meta($user_id, 'gear_preferences_shorts_bib_size_id', $shortsBibSizeId);
                }
                update_user_meta($user_id, 'gear_preferences_rider_height', '');
            }
        }
        as_enqueue_async_action('tt_trigger_cron_ns_guest_booking_details', array( true, $ns_user_id, $user_id ), '[Sync] - Adding NS Trips for new register guest');
        //tt_ns_guest_booking_details(true, $ns_user_id,$user_id);
    }
}

add_action('wp_ajax_tt_bike_selection_ajax_action', 'trek_tt_bike_selection_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_bike_selection_ajax_action', 'trek_tt_bike_selection_ajax_action_cb');
function trek_tt_bike_selection_ajax_action_cb()
{
    $bikeTypeId = $_REQUEST['bikeTypeId'];
    $isBikeUpgrade = false;
    $bikeUpgradeQty = $_REQUEST['bikeUpgradeQty'];
    $bike_upgrade_qty = 0;
    $guest_number = $_REQUEST['guest_number'];
    $trek_user_checkout_data =  get_trek_user_checkout_data();
    $postedData = isset($trek_user_checkout_data['posted']) ? $trek_user_checkout_data['posted'] : array();
    if (isset($postedData['bike_gears']) && $postedData['bike_gears']) {
        foreach ($postedData['bike_gears'] as $bike_gear_type => $bike_gear) {
            if ($bike_gear_type == 'primary') {
                if ($guest_number != 0) {
                    $bike_type_id = isset($bike_gear['bikeTypeId']) ? $bike_gear['bikeTypeId'] : '';
                    if ($bike_type_id) {
                        $bikeTypeInfo = tt_ns_get_bike_type_info($bike_type_id);
                        if ($bikeTypeInfo && isset($bikeTypeInfo['isBikeUpgrade']) && $bikeTypeInfo['isBikeUpgrade'] == 1) {
                            $bike_upgrade_qty++;
                        }
                    }
                }
            } else {
                if ($bike_gear) {
                    foreach ($bike_gear as $guest_key => $guestData) {
                        if ($guest_number != $guest_key) {
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
    }
    if ($bikeTypeId) {
        $bikeTypeInfo = tt_ns_get_bike_type_info($bikeTypeId);
        if ($bikeTypeInfo && isset($bikeTypeInfo['isBikeUpgrade']) && $bikeTypeInfo['isBikeUpgrade'] == 1) {
            $bike_upgrade_qty++;
            $isBikeUpgrade = true;
        }
    }
    $tripInfo = tt_get_trip_pid_sku_from_cart();
    $opts = tt_get_bikes_by_trip_info($tripInfo['ns_trip_Id'], $tripInfo['sku'], $bikeTypeId);
    $accepted_p_ids = tt_get_line_items_product_ids();
    $product_id = tt_create_line_item_product('TTWP23UPGRADES');
    $bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', '', $tripInfo['sku'], true);
    if ($bikeUpgradePrice && $bikeUpgradePrice > 0) {
        WC()->cart->add_to_cart($product_id, $bike_upgrade_qty, 0, array(), array('tt_cart_custom_fees_price' => $bikeUpgradePrice));
    }
    foreach (WC()->cart->get_cart() as $cart_item_id => $cart_item) {
        if ( isset($cart_item['product_id']) && $cart_item['product_id'] == $product_id) {
            $product = wc_get_product($product_id);
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
            $sku = $product->get_sku();
            if ($sku == 'TTWP23UPGRADES') {
                if (isset($cart_item['tt_cart_custom_fees_price']) && $cart_item['tt_cart_custom_fees_price'] > 0) {
                    $cart_item['data']->set_price($cart_item['tt_cart_custom_fees_price']);
                    $bikeUpgradePrice = $cart_item['tt_cart_custom_fees_price'];
                }
                WC()->cart->cart_contents[$cart_item_id]['quantity'] = $bike_upgrade_qty;
            }
        }
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            if ($guest_number == 0) {
                $cart_item['trek_user_checkout_data']['bike_gears']['primary']['bikeTypeId'] = $bikeTypeId;
            } else {
                $cart_item['trek_user_checkout_data']['bike_gears']['guests'][$guest_number]['bikeTypeId'] = $bikeTypeId;
            }
            if ($guest_number == 0 && $isBikeUpgrade == true) {
                $cart_item['trek_user_checkout_data']['bike_gears']['primary']['upgrade'] = 'yes';
            }
            if ($guest_number != 0 && is_numeric($guest_number) && $guest_number > 0 && $isBikeUpgrade == true) {
                $cart_item['trek_user_checkout_data']['bike_gears']['guests'][$guest_number]['upgrade'] = 'yes';
            }
            $cart_item['trek_user_checkout_data']['tt_bike_upgrade_qty'] = $bike_upgrade_qty;
            $cart_item['trek_user_checkout_data']['tt_bike_upgrade_charges'] = $bikeUpgradePrice;
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
    }
    $review_order_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if (is_readable($review_order)) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $opts['review_order'] = $review_order_html;
    $opts['bike_upgrade_qty'] = $bike_upgrade_qty;
    $opts['isBikeUpgrade'] = $isBikeUpgrade;
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
    echo json_encode($opts);
    exit;
}
add_action('wp_ajax_tt_update_occupant_popup_html_ajax_action', 'trek_tt_update_occupant_popup_html_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_update_occupant_popup_html_ajax_action', 'trek_tt_update_occupant_popup_html_ajax_action_cb');
function trek_tt_update_occupant_popup_html_ajax_action_cb()
{
    ob_start();
    $accepted_p_ids = tt_get_line_items_product_ids();
    $trip_capacity = get_trip_capacity_info();
    $cart = WC()->cart->cart_contents;
    $single = isset($_REQUEST['single']) ? $_REQUEST['single'] : 0;
    $double = isset($_REQUEST['double']) ? $_REQUEST['double'] : 0;
    $private = isset($_REQUEST['private']) ? $_REQUEST['private'] : 0;
    $roommate = isset($_REQUEST['roommate']) ? $_REQUEST['roommate'] : 0;
    foreach ($cart as $cart_item_id => $cart_item) {
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            $cart_item['trek_user_checkout_data']['single'] = $single;
            $cart_item['trek_user_checkout_data']['double'] = $double;
            $cart_item['trek_user_checkout_data']['private'] = $private;
            $cart_item['trek_user_checkout_data']['roommate'] = $roommate;
            if( $single == 0 ){
                $cart_item['trek_user_checkout_data']['occupants']['single'] = [];
            }
            if( $double == 0 ){
                $cart_item['trek_user_checkout_data']['occupants']['double'] = [];
            }
            if( $private == 0 ){
                $cart_item['trek_user_checkout_data']['occupants']['private'] = [];
            }
            if( $roommate == 0 ){
                $cart_item['trek_user_checkout_data']['occupants']['roommate'] = [];
            }
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
    }
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
    $occupant_popup_html = tt_occupant_selection_popup($_REQUEST);
    $checkout_hotel = TREK_PATH . '/woocommerce/checkout/checkout-hotel.php';
    $hotelHtml = '';
    if (is_readable($checkout_hotel)) {
        $hotelHtml .= wc_get_template_html('woocommerce/checkout/checkout-hotel.php');
    } 
    echo json_encode(
        array(
            'status' => true,
            'html' => $occupant_popup_html,
            'hotel_html' => $hotelHtml
        )
    );
    exit;
}
add_action('wp_ajax_tt_bike_size_change_ajax_action', 'trek_tt_bike_size_change_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_bike_size_change_ajax_action', 'trek_tt_bike_size_change_ajax_action_cb');
function trek_tt_bike_size_change_ajax_action_cb()
{
    $bikeTypeId = $_REQUEST['bikeTypeId'];
    $bike_size = $_REQUEST['bike_size'];
    $tripInfo = tt_get_trip_pid_sku_from_cart();
    $result = tt_get_bike_id_by_args($tripInfo['ns_trip_Id'], $tripInfo['sku'], $bikeTypeId, $bike_size);
    echo json_encode($result);
    exit;
}
add_action('wp_ajax_tt_bike_upgrade_fees_ajax_action', 'trek_tt_bike_upgrade_fees_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_bike_upgrade_fees_ajax_action', 'trek_tt_bike_upgrade_fees_ajax_action_cb');
function trek_tt_bike_upgrade_fees_ajax_action_cb()
{
    $res = ['status' => false];
    $upgrade_count = isset($_REQUEST['upgrade_count']) ? $_REQUEST['upgrade_count'] : 0;
    $guest_index = isset($_REQUEST['guest_index']) ? $_REQUEST['guest_index'] : '';
    $accepted_p_ids = tt_get_line_items_product_ids();
    $tripInfo = tt_get_trip_pid_sku_from_cart();
    $product_id = tt_create_line_item_product('TTWP23UPGRADES');
    $bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', '', $tripInfo['sku'], true);
    if ($bikeUpgradePrice && $bikeUpgradePrice > 0) {
        WC()->cart->add_to_cart($product_id, $upgrade_count, 0, array(), array('tt_cart_custom_fees_price' => $bikeUpgradePrice));
    }
    foreach (WC()->cart->get_cart() as $cart_item_id => $cart_item) {
        if ( isset($cart_item['product_id']) && $cart_item['product_id'] == $product_id) {
            $product = wc_get_product($product_id);
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
            $sku = $product->get_sku();
            if ($sku == 'TTWP23UPGRADES') {
                if (isset($cart_item['tt_cart_custom_fees_price']) && $cart_item['tt_cart_custom_fees_price'] > 0) {
                    $cart_item['data']->set_price($cart_item['tt_cart_custom_fees_price']);
                    $bikeUpgradePrice = $cart_item['tt_cart_custom_fees_price'];
                }
            }
            WC()->cart->cart_contents[$cart_item_id]['quantity'] = $upgrade_count;
        }
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            if ($guest_index == 0) {
                $cart_item['trek_user_checkout_data']['bike_gears']['primary']['upgrade'] = 'yes';
            }
            if ($guest_index != 0 && is_numeric($guest_index) && $guest_index > 0) {
                $cart_item['trek_user_checkout_data']['bike_gears']['guests'][$guest_index]['upgrade'] = 'yes';
            }
            $cart_item['trek_user_checkout_data']['tt_bike_upgrade_qty'] = 1;
            $cart_item['trek_user_checkout_data']['tt_bike_upgrade_charges'] = $bikeUpgradePrice;
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
    }
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
    $review_order_html = '';
    $review_order = TREK_PATH . '/woocommerce/checkout/review-order.php';
    if (is_readable($review_order)) {
        $review_order_html .= wc_get_template_html('woocommerce/checkout/review-order.php');
    } else {
        $review_order_html .= '<h3>Step 4</h3><p>Checkout review form code is missing!</p>';
    }
    $res['review_order'] = $review_order_html;
    $res['status'] = true;
    echo json_encode($res);
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : tt_ns_get_bike_type_info
 **/
if (!function_exists('tt_ns_get_bike_type_info')) {
    function tt_ns_get_bike_type_info($bikeTypeId)
    {
        $result = [];
        $ns_bikeType_Info = get_option(TT_OPTION_PREFIX . 'ns_bikeType_info');
        if (empty($ns_bikeType_Info)) {
            tt_ns_fetch_bike_type_info();
            $ns_bikeType_Info = get_option(TT_OPTION_PREFIX . 'ns_bikeType_info');
        }
        if ($ns_bikeType_Info && $bikeTypeId) {
            $ns_bikeType_result = json_decode($ns_bikeType_Info, true);
            $keys = array_column($ns_bikeType_result, 'id');
            $index =  array_search($bikeTypeId, $keys);
            $result = [
                'isActive' => $ns_bikeType_result[$index]['isActive'],
                'isBikeUpgrade' => $ns_bikeType_result[$index]['isBikeUpgrade']
            ];
        }
        return $result;
    }
}
add_action('wp_ajax_tt_guest_rooms_selection_ajax_action', 'trek_tt_guest_rooms_selection_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_guest_rooms_selection_ajax_action', 'trek_tt_guest_rooms_selection_ajax_action_cb');
function trek_tt_guest_rooms_selection_ajax_action_cb()
{
    $res = ['status' => false];
    $trek_user_checkout_data =  get_trek_user_checkout_data();
    $trek_user_checkout_posted = $trek_user_checkout_data['posted'];
    $output = tt_rooms_output($trek_user_checkout_posted, true, true);
    $accepted_p_ids = tt_get_line_items_product_ids();
    $single = isset($_REQUEST['single']) ? $_REQUEST['single'] : 0;
    $double = isset($_REQUEST['double']) ? $_REQUEST['double'] : 0;
    $roommate = isset($_REQUEST['roommate']) ? $_REQUEST['roommate'] : 0;
    $private = isset($_REQUEST['private']) ? $_REQUEST['private'] : 0;
    $special_needs = isset($_REQUEST['special_needs']) ? $_REQUEST['special_needs'] : 0;
    foreach (WC()->cart->get_cart() as $cart_item_id => $cart_item) {
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            $cart_item['trek_user_checkout_data']['single'] = $single;
            $cart_item['trek_user_checkout_data']['double'] = $double;
            $cart_item['trek_user_checkout_data']['roommate'] = $roommate;
            $cart_item['trek_user_checkout_data']['private'] = $private;
            $cart_item['trek_user_checkout_data']['special_needs'] = $special_needs;
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
    }
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
    $res['status'] = true;
    $res['html'] = $output;
    echo json_encode($res);
    exit;
}
add_action('wp_ajax_tt_pay_amount_change_ajax_action', 'trek_tt_pay_amount_change_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_pay_amount_change_ajax_action', 'trek_tt_pay_amount_change_ajax_action_cb');
function trek_tt_pay_amount_change_ajax_action_cb()
{
    $res = ['status' => false];
    $paymentType = isset($_REQUEST['paymentType']) ? $_REQUEST['paymentType'] : 0;
    $accepted_p_ids = tt_get_line_items_product_ids();
    foreach (WC()->cart->get_cart() as $cart_item_id => $cart_item) {
        if ( isset($cart_item['product_id']) && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            $cart_item['trek_user_checkout_data']['pay_amount'] = $paymentType;
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
    }
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();
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
    $res['review_order'] = $review_order_html;
    $res['payment_option'] = $payment_option_html;
    $res['status'] = true;
    echo json_encode($res);
    exit;
}
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
add_action('wp_ajax_tt_clear_cart_ajax_action', 'trek_tt_clear_cart_ajax_action_cb');
add_action('wp_ajax_nopriv_tt_clear_cart_ajax_action', 'trek_tt_clear_cart_ajax_action_cb');
function trek_tt_clear_cart_ajax_action_cb()
{
    global $woocommerce;
    $woocommerce->cart->empty_cart();
    $res['status'] = true;
    echo json_encode($res);
    exit;
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
        }
        return $res;
    }
}
add_action('woocommerce_add_to_cart', 'tt_woocommerce_add_to_cart_cb');
function tt_woocommerce_add_to_cart_cb()
{
    $accepted_p_ids = tt_get_line_items_product_ids();
    $cart = WC()->cart->cart_contents;
    if ($cart) {
        foreach ($cart as $cart_item_id => $cart_item) {
            $wcData = isset($cart_item['data']) ? $cart_item['data'] : '';
            $_product = apply_filters('woocommerce_cart_item_product', $wcData, $cart_item, $cart_item_id);
            $product_id =  isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
            if ($product_id && !in_array($product_id, $accepted_p_ids)) {
                //Trip Parent ID
                $parent_product_id = tt_get_parent_trip_id_by_child_sku($_product->get_sku());
                $cart_item['trek_user_checkout_data']['parent_product_id'] = $parent_product_id;
                $cart_item['trek_user_checkout_data']['product_id'] = $product_id;
                $cart_item['trek_user_checkout_data']['sku'] = $_product->get_sku();
                $bikeUpgradePrice = tt_get_local_trips_detail('bikeUpgradePrice', '', $_product->get_sku(), true);
                $singleSupplementPrice = tt_get_local_trips_detail('singleSupplementPrice', '', $_product->get_sku(), true);
                $cart_item['trek_user_checkout_data']['bikeUpgradePrice'] = $bikeUpgradePrice;
                $cart_item['trek_user_checkout_data']['singleSupplementPrice'] = $singleSupplementPrice;
                WC()->cart->cart_contents[$cart_item_id] = $cart_item;
            }
        }
        WC()->cart->set_session();
        //WC()->cart->calculate_totals();
        //WC()->cart->maybe_set_cart_cookies();
    }
}
add_filter('wc_add_to_cart_message_html', 'tt_wc_add_to_cart_message_html', 10, 3);
function tt_wc_add_to_cart_message_html($message, $products, $show_qty)
{
    $message = '';
    return $message;
}

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
    $s_gender = isset($_REQUEST['custentity_gender']) && $_REQUEST['custentity_gender'] != 0  ? $_REQUEST['custentity_gender'] : '';
    $s_addr1 = isset($_REQUEST['shipping_address_1']) ? $_REQUEST['shipping_address_1'] : '';
    $s_country = isset($_REQUEST['shipping_country']) ? $_REQUEST['shipping_country'] : '';
    $s_state = isset($_REQUEST['shipping_state']) ? $_REQUEST['shipping_state'] : '';
    $s_city = isset($_REQUEST['shipping_city']) ? $_REQUEST['shipping_city'] : '';
    $s_postcode = isset($_REQUEST['shipping_postcode']) ? $_REQUEST['shipping_postcode'] : '';
    $guests = isset($_REQUEST['guests']) ? $_REQUEST['guests'] : array();
    $is_same_billing_as_mailing = isset($_REQUEST['is_same_billing_as_mailing']) ? $_REQUEST['is_same_billing_as_mailing'] : 0;
    $billing_info = [
        'billing_first_name',
        'billing_first_name',
        'billing_address_1',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_city'
    ];
    $p_fields = [];
    $p_user_1_error = $g_user_1_error = false;
    $p_user_2_error = $g_user_2_error = $billing_error = false;
    if ($no_of_guests > $trip_capacity) {
        $errors->add('woocommerce_trip_capacity_error', __('You cannot add more guests than the Trip capacity.'));
    }
    if (
        empty($sf_name) || empty($sl_name) || empty($s_email) || empty($s_addr1) || empty($sp_phone)
        || empty($s_dob) || empty($s_gender) || $s_gender == 0 || empty($s_country) || empty($s_state) || empty($s_city) || empty($s_postcode)
    ) {
        $p_fields[] = empty($sf_name) ? 'First Name' : '';
        $p_fields[] = empty($sl_name) ? 'Last Name' : '';
        $p_fields[] = empty($s_email) ? 'Email' : '';
        $p_fields[] = empty($s_addr1) ? 'Address' : '';
        $p_fields[] = empty($sp_phone) ? 'Phone' : '';
        $p_fields[] = empty($s_dob) ? 'DOB' : '';
        $p_fields[] = empty($s_gender) || $s_gender == 0 ? 'Gender' : '';
        $p_fields[] = empty($s_country) ? 'Country' : '';
        $p_fields[] = empty($s_state) ? 'State' : '';
        $p_fields[] = empty($s_city) ? 'City' : '';
        $p_fields[] = empty($s_postcode) ? 'Postcode' : '';
        $p_user_1_error = true;
    }
    if ($bike_gears) {
        foreach ($bike_gears as $guest_type => $bike_gear) {
            if ($guest_type == 'primary') {
                if ($bike_gear) {
                    foreach ($bike_gear as $bike_gear_key => $bike_gear_data) {
                        if ($bike_gear_key == 'rider_level' && $bike_gear_data != 5) {
                            if ($bike_gear_key == 'own_bike' && $bike_gear_data == 'yes') {
                                if ($bike_gear_key != 'helmet_size' && $bike_gear_key != 'bikeTypeId' && $bike_gear_key != 'bikeId' && $bike_gear_key != 'rider_height' && $bike_gear_data == '') {
                                    $p_user_2_error = true;
                                }
                            } else {
                                if ($bike_gear_key != 'helmet_size' && $bike_gear_data == '') {
                                    $p_user_2_error = true;
                                }
                            }
                        }
                    }
                }
            }
            if ($guest_type == 'guests') {
                if ($bike_gear) {
                    foreach ($bike_gear as $bike_gear_key => $bike_gear_data) {
                        if ($bike_gear_data) {
                            foreach ($bike_gear_data as $bike_gear_data_k => $bike_gear_data_v) {
                                if ($bike_gear_data_k == 'rider_level' && $bike_gear_data_v != 5) {
                                    if ($bike_gear_data_k == 'own_bike' && $bike_gear_data_v == 'yes') {
                                        if ($bike_gear_data_k != 'helmet_size' && $bike_gear_data_k != 'bikeTypeId' && $bike_gear_data_k != 'bikeId' && $bike_gear_data_k != 'rider_height' && $bike_gear_data_v == '') {
                                            $g_user_2_error = true;
                                        }
                                    } else {
                                        if ($bike_gear_data_k != 'helmet_size' && $bike_gear_data_v == '') {
                                            $g_user_2_error = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if ($guests) {
        foreach ($guests as $guest_key => $guest_data) {
            $guest_fname = isset($guest_data['guest_fname']) ? $guest_data['guest_fname'] : '';
            $guest_lname = isset($guest_data['guest_lname']) ? $guest_data['guest_lname'] : '';
            $guest_email = isset($guest_data['guest_email']) ? $guest_data['guest_email'] : '';
            $guest_phone = isset($guest_data['guest_phone']) ? $guest_data['guest_phone'] : '';
            $guest_gender = isset($guest_data['guest_gender']) && $guest_data['guest_gender'] && $guest_data['guest_gender'] != 0 ? $guest_data['guest_gender'] : '';
            $guest_dob = isset($guest_data['guest_dob']) ? $guest_data['guest_dob'] : '';
            if (
                empty($guest_fname) || empty($guest_lname) || empty($guest_email) || empty($guest_phone) || empty($guest_gender)
                ||  empty($guest_dob)
            ) {
                $g_user_1_error = true;
            }
        }
    }
    if ($billing_info && $is_same_billing_as_mailing !=1 ) {
        foreach ($billing_info as $billing_field) {
            if (!isset($_REQUEST[$billing_field]) || empty($_REQUEST[$billing_field])) {
                $billing_error = true;
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
        $p_fields_string = is_array($p_fields) && $p_fields ? implode(', ',array_filter($p_fields)) : '';
        $errors->add('woocommerce_primary_guests_error', __('Please fill fields '.$p_fields_string.' of Primary user in step 1.'));
    }
    if ($g_user_1_error == true) {
        $errors->add('woocommerce_guests_error', __("Please fill all fields of Guest user in step 1."));
    }
    if ($billing_error == true && $is_same_billing_as_mailing != 1 ) {
        $errors->add('woocommerce_tt_billing_error', __("Please fill all billing fields in step 3"));
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
    $tripInfo = tt_get_trip_pid_sku_by_orderId($order_id);
    $opts = tt_get_bikes_by_trip_info($tripInfo['ns_trip_Id'], $tripInfo['sku'], $bikeTypeId);
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
function trek_tt_chk_bike_size_change_ajax_action_cb()
{
    $bikeTypeId = $_REQUEST['bikeTypeId'];
    $bike_size = $_REQUEST['bike_size'];
    $order_id = $_REQUEST['order_id'];
    $tripInfo = tt_get_trip_pid_sku_by_orderId($order_id);
    $result = tt_get_bike_id_by_args($tripInfo['ns_trip_Id'], $tripInfo['sku'], $bikeTypeId, $bike_size);
    echo json_encode($result);
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
                        if ($guest_id) {
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
                        if ($guest_id) {
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
                        if ($guest_id) {
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
                        if ($guest_id) {
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
function tt_guest_insurance_output($tt_posted = [])
{
    //Trek Insurance
    $iter = 0;
    $cols = 2;
    $shipping_name = '';
    $guests = array();
    if ($tt_posted) {
        $shipping_name  = $tt_posted['shipping_first_name'] . ' ' . $tt_posted['shipping_last_name'];
        $guests = isset($tt_posted['guests']) ? $tt_posted['guests'] : array();
    }
    $guest_insurance_html = '';
    if (isset($tt_posted['trek_guest_insurance']) && !empty($tt_posted['trek_guest_insurance'])) {
        $guest_insurance = $tt_posted['trek_guest_insurance'];
        $fields_size = 1;
        $fields_size += isset($guest_insurance['guests']) && $guest_insurance['guests'] ? sizeof($guest_insurance['guests']) : 0;
        foreach ($guest_insurance as $guest_insurance_k => $guest_insurance_val) {
            if ($guest_insurance_k == 'primary') {
                $p_insurance_amount = isset($guest_insurance_val['basePremium']) ? $guest_insurance_val['basePremium'] : 0;
                $p_insurance_amount_curr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>'.$p_insurance_amount.'</span>';
                if ($iter % $cols == 0) {
                    $guest_insurance_html .= '<div class="d-flex order-details__flex order-details__flexmulti">';
                }
                $guest_insurance_html .= '<div>';
                $guest_insurance_html .= '<p class="fw-medium mb-2">Primary Guest: ' . $shipping_name . '</p>
                <p class="fs-sm lh-sm mb-0">' . ($guest_insurance_val['is_travel_protection'] == 1 ? 'Added Travel Protection ('.$p_insurance_amount_curr.')' : 'Declined Travel Protection') . '</p>';
                $guest_insurance_html .= '</div>';
                if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                    $guest_insurance_html .= '</div>';
                }
                $iter++;
            } else {
                foreach ($guest_insurance_val as $guest_key => $guest_insurance_Data) {
                    $g_insurance_amount = isset($guest_insurance_Data['basePremium']) ? $guest_insurance_Data['basePremium'] : 0;
                    $g_insurance_amount_curr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>'.$g_insurance_amount.'</span>';
                    if ($iter % $cols == 0) {
                        $guest_insurance_html .= '<div class="d-flex order-details__flex order-details__flexmulti">';
                    }
                    $guestInfo = $guests[$guest_key];
                    $fullname = $guestInfo['guest_fname'] . ' ' . $guestInfo['guest_lname'];
                    $guest_insurance_html .= '<div>';
                    $guest_insurance_html .= '<p class="fw-medium mb-2">Guest ' . $guest_key . ': ' . $fullname . '</p>
                    <p class="fs-sm lh-sm mb-0">' . ($guest_insurance_Data['is_travel_protection'] == 1 ? 'Added Travel Protection ('.$g_insurance_amount_curr.')' : 'Declined Travel Protection') . '</p>';
                    $guest_insurance_html .= '</div>';
                    if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                        $guest_insurance_html .= '</div>';
                    }
                    $iter++;
                }
            }
        }
    }
    return $guest_insurance_html;
}
function tt_guest_details($tt_posted = [])
{
    $userInfo = wp_get_current_user();
    $guest_emails = [];
    $guest_html = $bike_gear_html = '';
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
        for ($iter = 0; $iter < $guest_count; $iter++) {
            if ($iter % $cols == 0) {
                $guest_html .= '<div class="d-flex order-details__flex order-details__flexmulti">';
                $bike_gear_html .= '<div class="d-flex order-details__flex order-details__flexmulti">';
            }
            $rider_levelVal = tt_validate($bike_gears['primary']['rider_level']);
            $bikeId = tt_validate($bike_gears['primary']['bikeId']);
            $bike_size = tt_validate($bike_gears['primary']['bike_size']);
            $bike = tt_validate($bike_gears['primary']['bike']);
            $rider_height = tt_validate($bike_gears['primary']['rider_height']);
            $bike_pedal = tt_validate($bike_gears['primary']['bike_pedal']);
            $helmet_size = tt_validate($bike_gears['primary']['helmet_size']);
            $jersey_style = tt_validate($bike_gears['primary']['jersey_style']);
            $jersey_size = tt_validate($bike_gears['primary']['jersey_size']);
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
                $rider_levelVal = tt_validate($bike_gears['guests'][$iter]['rider_level']);
                $bikeId = tt_validate($bike_gears['guests'][$iter]['bikeId']);
                $bike_size = tt_validate($bike_gears['guests'][$iter]['bike_size']);
                $bike = tt_validate($bike_gears['guests'][$iter]['bike']);
                $rider_height = tt_validate($bike_gears['guests'][$iter]['rider_height']);
                $bike_pedal = tt_validate($bike_gears['guests'][$iter]['bike_pedal']);
                $helmet_size = tt_validate($bike_gears['guests'][$iter]['helmet_size']);
                $jersey_style = tt_validate($bike_gears['guests'][$iter]['jersey_style']);
                $jersey_size = tt_validate($bike_gears['guests'][$iter]['jersey_size']);
                $bikeTypeId = tt_validate($bike_gears['guests'][$iter]['bikeTypeId']);
                $own_bike = tt_validate($bike_gears['guests'][$iter]['own_bike']);
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
                $guest_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">'.$addr1.'</p>
                <p class="mb-0 fs-sm lh-sm fw-normal">'.$addr2.'</p>
                <p class="mb-0 fs-sm lh-sm fw-normal">'.$state.', '.$city.', '.$postcode.'</p>
                <p class="mb-0 fs-sm lh-sm fw-normal">'.$country.'</p>';
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
            $bike_gear_html .= '<p class="mb-2 fs-md lh-md fw-medium">' . $guest_label . '</p>';
            $bike_gear_html .= '<p class="mb-2 fs-md lh-md fw-medium">' . $fname . ' ' . $lname . '</p>';
            $bike_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Rider Level: ' . $rider_level . '</p>';
            // if ($rider_level == 'Non-Rider') {
                if ($own_bike == 'yes') {
                    $bike_size = $rider_height = "Bringing own";
                }
                if( $rider_levelVal != 5 && is_array( $jersey_size ) ) {
                    $bike_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Bike: ' . $bike_size . '</p>
                        <p class="mb-0 fs-sm lh-sm fw-normal">Bike Size: ' . $bike_size . '</p>
                        <p class="mb-0 fs-sm lh-sm fw-normal">Rider Height: ' . $rider_height . '</p>
                        <p class="mb-0 fs-sm lh-sm fw-normal">Pedals: ' . $bike_pedal . '</p>
                        <p class="mb-0 fs-sm lh-sm fw-normal">Helmet Size: ' . $helmet_size . '</p>';
                } elseif ( $rider_levelVal != 5 ) {
                    $bike_gear_html .= '<p class="mb-0 fs-sm lh-sm fw-normal">Bike: ' . $bike_size . '</p>
                        <p class="mb-0 fs-sm lh-sm fw-normal">Bike Size: ' . $bike_size . '</p>
                        <p class="mb-0 fs-sm lh-sm fw-normal">Rider Height: ' . $rider_height . '</p>
                        <p class="mb-0 fs-sm lh-sm fw-normal">Pedals: ' . $bike_pedal . '</p>
                        <p class="mb-0 fs-sm lh-sm fw-normal">Helmet Size: ' . $helmet_size . '</p>
                        <p class="mb-0 fs-sm lh-sm fw-normal">Jersey: ' . $jersey_size . '</p>';
                    }
            $bike_gear_html .= '</div>';
            if (($iter % $cols == $cols - 1) || ($iter == $guest_count - 1)) {
                $guest_html .= '</div>';
                $bike_gear_html .= '</div>';
            }
        }
    }
    return [
        'guests' => $guest_html,
        'bike_gears' => $bike_gear_html,
        'guest_emails' => $guest_emails
    ];
}
function tt_get_parent_trip($sku="")
{
    //Trip Parent ID
    $parent_product_id = tt_get_parent_trip_id_by_child_sku($sku);
    $product_image_url = 'https://via.placeholder.com/150?text=Trek Travel';
    $parent_trip_link = 'javascript:';
    if ( $parent_product_id ) {
        if( has_post_thumbnail($parent_product_id) ){
            $product_image_url = get_the_post_thumbnail_url($parent_product_id);
        }
        $parent_trip_link = get_the_permalink($parent_product_id);
    }
    return [
        'id' => $parent_product_id,
        'image' => $product_image_url,
        'link' => $parent_trip_link
    ];
}
function tt_get_hotel_bike_list($sku)
{
    $bike_list = $hotel_list = '';
    $hotelsData = tt_get_local_trips_detail('hotels', '', $sku, true);
    $hotels = json_decode($hotelsData, true);
    if ($hotels) {
        $hotel_list .= '<ol>';
        foreach ($hotels as $hotel) {
            $hotel_list .= '<li class="fw-normal fs-sm lh-sm">' . $hotel['hotelName'] . '</li>';
        }
        $hotel_list .= '</ol>';
    }
    $bikesData = tt_get_local_trips_detail('bikes', '', $sku, true);
    $bikes = json_decode($bikesData, true);
    $bikes_type_id_in = [];
    if ($bikes) {
        $bike_list .= '<ol>';
        foreach ($bikes as $bikes) {
            $bikeDescr = $bikes['bikeDescr'];
            $bikeTypeId = $bikes['bikeType']['id'];
            $bike_post_name_arr = explode(' ', $bikeDescr);
            unset($bike_post_name_arr[0]);
            $bike_post_name = implode(' ', $bike_post_name_arr);
            if ($bikeTypeId && $bike_post_name && !in_array($bike_post_name, $bikes_type_id_in)) {
                $bike_list .= '<li class="fw-normal fs-sm lh-sm">' . $bike_post_name . '</li>';
                $bikes_type_id_in[] = $bike_post_name;
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
    if ($Ids) {
        $tt_compare_products = $Ids;
    } else {
        $tt_compare_products = [];
        if (isset($_COOKIE['wc_products_compare_products']) && !empty($_COOKIE['wc_products_compare_products'])) {
            $tt_compare_products = explode(',', $_COOKIE['wc_products_compare_products']);
        }
    }
    $compare_div_style = ($tt_compare_products && count($tt_compare_products) > 0 ? 'display:flex;' : 'display:none;');
    if ($is_header == true) {
        $output = '<div class="sticky-bottom bg-white compare-products-footer-bar" style="' . $compare_div_style . '">
                    <p class="fw-bold fs-sm lh-sm mb-0">Compare Trips</p>
                    <div id="tt_compare_product">';
    }
    if ($tt_compare_products) {
        foreach ($tt_compare_products as $tt_compare_product) {
            $image_URL = get_the_post_thumbnail_url($tt_compare_product, 'full');
            $image_URL = ($image_URL ? $image_URL : DEFAULT_IMG);
            $output .= '<div class="compare-product" id="product-' . $tt_compare_product . '">
                        <img src="' . $image_URL . '" class="object-fit-cover" alt="" />
                        <a href="#" title="Remove" class="remove-compare-page-product" data-remove-id="' . $tt_compare_product . '"><i class="bi bi-x-lg"></i></a>
                    </div>';
        }
        
        

    }
    if ($is_header == true) {
        $output .= '</div>
                <a href="#" title="Remove all" class="clear-all-link compare-remove-all-products">Clear All</a>
                <a href="/products-compare" title="Compare Page" class="woocommerce-products-compare-compare-link btn btn-primary rounded-1">Compare</a>
                <i class="bi bi-x-lg remove-all compare-remove-all-products"></i>
            </div>';
    }
    return $output;
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
add_action('run_cron_tt_ns_booking', 'run_cron_tt_ns_booking_cb', 10, 1);
function run_cron_tt_ns_booking_cb($order_id){
    do_action('tt_trigger_cron_ns_booking', $order_id, null);
    tt_add_error_log('[End] - NS Trip Booking', [$order_id], ['dateTime' => date('Y-m-d H:i:s')]);
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
add_action('wp_ajax_tt_generate_save_insurance_quote', 'tt_generate_save_insurance_quote_cb');
add_action('wp_ajax_nopriv_tt_generate_save_insurance_quote', 'tt_generate_save_insurance_quote_cb');
function tt_generate_save_insurance_quote_cb()
{
    $accepted_p_ids = tt_get_line_items_product_ids();
    //Add travels data to Cart object
    $cart = WC()->cart->get_cart();
    //Preparing insurance HTML
    $tt_checkoutData =  get_trek_user_checkout_data();
    $tt_posted = isset($tt_checkoutData['posted']) ? $tt_checkoutData['posted'] : [];
    $product_id = null;
    if( isset($tt_posted['product_id']) ){
        $product_id = $tt_posted['product_id'];
    }
    $product = wc_get_product($product_id);
    $individualTripCost = 0;
    $sdate_info = $edate_info = '';

    if( $product ){
        $individualTripCost = $product->get_price();
        $singleSupplementPrice = isset($tt_posted['singleSupplementPrice']) ? $tt_posted['singleSupplementPrice'] : 0;

        // Remove dollar sign and commas
        $amount_string = str_replace(array('$', ','), '', $singleSupplementPrice);

        // Convert the string to a float
        $amount_float = (float) $amount_string;

        // Convert to an integer (removing the decimal part)
        $amount_int = (int) $amount_float;

        $individualTripCost = $individualTripCost + $amount_int;

        //error_log( 'Single Supplement' . $tt_posted['singleSupplementPrice'] );
        error_log( 'Single Supplement' . $singleSupplementPrice );
        error_log( "trip Cost:" . $individualTripCost );

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
    $trek_insurance_args = [
        "coverage" => [
            "effectiveDate" => $effectiveDate,
            "expirationDate" => $expirationDate,
            "depositDate" => date('Y-m-d'),
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
        foreach ($guest_insurance as $guest_insurance_k => $guest_insurance_val) {
            $trek_insurance_args["insuredPerson"] = array();
            if ($guest_insurance_k == 'primary') {
                if ($guest_insurance_val['is_travel_protection'] == 1) {
                    $is_travel_protection_count++;
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
                $arcBasePremiumPP = isset($archinsuranceResPP['basePremium']) ? $archinsuranceResPP['basePremium'] : 0;
                $guest_insurance['primary']['basePremium'] = $arcBasePremiumPP;
                if ($guest_insurance_val['is_travel_protection'] == 1) {
                    $tt_total_insurance_amount += $arcBasePremiumPP;
                }
            } else {
                foreach ($guest_insurance_val as $guest_key => $guest_insurance_Data) {
                    $guestInfo = $tt_posted['guests'][$guest_key];
                    //if ($guest_insurance_Data['is_travel_protection'] == 1) {
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
                    $archinsuranceResPG = tt_set_calculate_insurance_fees_api($trek_insurance_args);
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
    $arcBasePremium = $tt_total_insurance_amount && $tt_total_insurance_amount > 0 ? $tt_total_insurance_amount : 0;
    $fees_product_id = tt_create_line_item_product('TTWP23FEES');
    //save cart Logic
    foreach ($cart as $cart_item_id => $cart_item) {
        if (isset($cart_item['product_id']) && $cart_item['product_id'] == $fees_product_id) {
            $product = wc_get_product($cart_item['product_id']);
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
            $sku = $product->get_sku();
            if ($sku == 'TTWP23FEES') {
                if (isset($cart_item['tt_cart_custom_fees_price']) && $cart_item['tt_cart_custom_fees_price'] > 0) {
                    $cart_item['data']->set_price($cart_item['tt_cart_custom_fees_price']);
                }
            }
            WC()->cart->cart_contents[$cart_item_id]['quantity'] = 1;
        }
        if ( isset($cart_item['product_id'])  && !in_array($cart_item['product_id'], $accepted_p_ids)) {
            $cart_item['trek_user_checkout_data']['trek_guest_insurance'] = $guest_insurance;
            $cart_item['trek_user_checkout_data']['insuredPerson'] = count($insuredPerson);
            $cart_item['trek_user_checkout_data']['tt_insurance_total_charges'] = $arcBasePremium;
            WC()->cart->cart_contents[$cart_item_id] = $cart_item;
        }
    }
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    //WC()->cart->maybe_set_cart_cookies();
    //End: Save cart logic
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
    $insuredHTMLPopup = '';
    $checkout_insured_users = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php';
    if (is_readable($checkout_insured_users)) {
        $insuredHTMLPopup .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests-popup.php');
    } else {
        $insuredHTMLPopup .= '<h3>Step 4</h3><p>checkout-insured-guests-popup.php form code is missing!</p>';
    }
    $insuredHTML = '';
    $checkout_insured_users = TREK_PATH . '/woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests.php';
    if (is_readable($checkout_insured_users)) {
        $insuredHTML .= wc_get_template_html('woocommerce/checkout/checkout-ajax-templates/checkout-insured-guests.php');
    } else {
        $insuredHTML .= '<h3>Step 4</h3><p>checkout-insured-guests.php form code is missing!</p>';
    }
    $res['status'] = true;
    $res['guest_insurance_html'] = $insuredHTML;
    $res['insuredHTMLPopup'] = $insuredHTMLPopup;
    $res['review_order'] = $review_order_html;
    $res['payment_option'] = $payment_option_html;
    $res['message'] = "Your information has been changed successfully!";
    echo json_encode($res);
    exit;
}
/**
 * @author  : Dharmesh Panchal
 * @version : 1.0.0
 * @return  : Function which will send User Data to NS forSync on user update action
 **/
add_action('tt_cron_syn_usermeta_ns', 'tt_cron_syn_usermeta_ns_cb',10, 2);
function tt_cron_syn_usermeta_ns_cb($user_id, $type){
    if (class_exists('TMWNI_Loader')) {
        $netsuiteUserClient = new TMWNI_Loader();
        ob_start();
        $netsuiteUserClient->addUpdateNetsuiteCustomer($user_id);
        ob_clean();
        tt_add_error_log('User update - ' . $type, ['user_id' => $user_id], ['dateTime' => date('Y-m-d H:i:s')]);
    }
}
add_action('ns_trips_sync_to_wc_product', 'ns_trips_sync_to_wc_product_cb', 10, 2);
function ns_trips_sync_to_wc_product_cb($is_all = true, $trips_IDs=[]){
    $type = "Sync All Trips";
    if( $trips_IDs && is_array($trips_IDs) && !empty($trips_IDs) ){
        $type = "Single Trip Sync";
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
    $opts .= '<option value="">Select Clothing Size</option>';
    $itemData = tt_get_custom_item_name('syncJerseySizes');
    if( $itemData && isset($itemData['options']) && $itemData['options'] ){
        foreach($itemData['options'] as $jerseyOptions){
            $jersey_Size = isset($jerseyOptions['optionValue']) ? $jerseyOptions['optionValue'] : '';
            $jersey_id = isset($jerseyOptions['optionId']) ? $jerseyOptions['optionId'] : '';
            if( str_contains($jersey_Size, ' ') ){
                $jersey_size_Arr = explode(' ', $jersey_Size);
                $loopGender =  isset($jersey_size_Arr[0]) ? $jersey_size_Arr[0] : '';
                $loopSize =  isset($jersey_size_Arr[1]) ? $jersey_size_Arr[1] : '';
                $selected = ($jersey_id == $jersey_size ? 'selected' : '');
                if( $gender == "men" && $loopGender == "Men's" ){
                    $opts .= '<option value="' . $jersey_id . '" ' . $selected . '>' . $loopSize . '</option>';
                }
                if( $gender == "women" && $loopGender == "Women's" ){
                    $opts .= '<option value="' . $jersey_id . '" ' . $selected . '>' . $loopSize . '</option>';
                }
            }
        }
    }
    return $opts;
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
add_action('tt_trigger_cron_ns_guest_booking_details', 'tt_trigger_cron_ns_guest_booking_details_cb', 10, 3);
function tt_trigger_cron_ns_guest_booking_details_cb($single_req,$ns_user_id, $wc_user_id){
    tt_add_error_log('[Start] - Adding Trips', ['ns_user_id' => $ns_user_id, 'wc_user_id' => $wc_user_id], ['dateTime' => date('Y-m-d H:i:s')]);
    tt_ns_guest_booking_details($single_req, $ns_user_id,$wc_user_id);
    tt_add_error_log('[End] - Adding Trips', ['ns_user_id' => $ns_user_id, 'wc_user_id' => $wc_user_id], ['dateTime' => date('Y-m-d H:i:s')]);
}
function tt_get_ns_booking_details_by_order($order_id){
    $releaseFormId = $booking_id = $waiver_link = "";
    $userInfo = wp_get_current_user();
    $User_order_info = trek_get_user_order_info($userInfo->ID, $order_id);
    if( isset($User_order_info[0]['releaseFormId']) && $User_order_info[0]['releaseFormId'] ){
        $releaseFormId = $User_order_info[0]['releaseFormId'];
    }else{
        $releaseFormId = get_post_meta($order_id, TT_WC_META_PREFIX.'releaseFormId', true);
    }
    $order_details = trek_get_user_order_info($userInfo->ID, $order_id);
    if( isset($order_details[0]) && isset($order_details[0]['guest_booking_id']) && $order_details[0]['guest_booking_id'] ){
        $booking_id = $order_details[0]['guest_booking_id'];
    }else{
        $booking_id = get_post_meta($order_id, TT_WC_META_PREFIX.'guest_booking_id', true);
    }
    $waiver_link = add_query_arg(
        array(
            'custpage_releaseFormId' => $releaseFormId
        ),
        TT_WAIVER_URL
    );
    return [
        'booking_id' => $booking_id,
        'releaseFormId' => $releaseFormId,
        'waiver_link' => $waiver_link
    ];
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
function tt_ajax_mailing_address_action_cb()
{
    $res['status'] = true;
    $tt_checkoutData =  get_trek_user_checkout_data();
    $tt_posted = isset($tt_checkoutData['posted']) ? $tt_checkoutData['posted'] : [];
    $primary_address_1 = isset($tt_posted['shipping_address_1']) ? $tt_posted['shipping_address_1'] : '';
    $primary_address_2 = isset($tt_posted['shipping_address_2']) ? $tt_posted['shipping_address_2'] : '';
    $primary_country = isset($tt_posted['shipping_country']) ? $tt_posted['shipping_country'] : '';
    $shipping_fname = isset($tt_posted['shipping_first_name']) ? $tt_posted['shipping_first_name']  :'';
    $shipping_lname = isset($tt_posted['shipping_last_name']) ? $tt_posted['shipping_last_name']  :'';
    $shipping_name = $shipping_fname.' '.$shipping_lname; 
    $shipping_postcode = isset($tt_posted['shipping_postcode']) ? $tt_posted['shipping_postcode']  :'';
    $shipping_state = isset($tt_posted['shipping_state']) ? $tt_posted['shipping_state']  :'';
    $shipping_city = isset($tt_posted['shipping_city']) ? $tt_posted['shipping_city']  :'';
    $output = '<p class="mb-0">'.$shipping_name.'</p>
        <p class="mb-0">'.$primary_address_1.'</p>
        <p class="mb-0">'.$primary_address_2.'</p>
        <p class="mb-0">'.$shipping_city.', '.$shipping_state.', '.$shipping_postcode.'</p>
        <p class="mb-0">'.$primary_country.'</p>
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
        $product_id = $first_cart_item['product_id'];
        $product_tax_rate = get_post_meta($product_id, 'tt_meta_taxRate', true);
        
        if ( $product_tax_rate ) {
            foreach ( $cart->get_cart() as $cart_item ) {
                if ( 'taxable' === $cart_item["data"]->tax_status ) {
                    $product_price    = $cart_item['data']->get_price();
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

// Recalculate the tax when cart totals are calculated
add_action( 'woocommerce_calculate_totals', 'recalculate_tax_on_cart_update', 10, 1 );
function recalculate_tax_on_cart_update( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    // Remove previous tax and add the updated tax
    $cart->remove_taxes();
}

// Filter to adjust the cart subtotal
add_filter( 'woocommerce_calculated_total', 'update_cart_subtotal', 10, 2 );
function update_cart_subtotal( $cart_total, $cart ) {
    $total_tax = calculate_cart_total_tax( $cart );

    // Add the calculated tax to the cart subtotal
    $cart_total += $total_tax;

    return $cart_total;
}

// Display the updated total tax in the template
add_action( 'woocommerce_review_order_before_shipping', 'display_total_tax' );
function display_total_tax() {
    $cart      = WC()->cart;
    $total_tax = calculate_cart_total_tax( $cart );

    echo '<p class="mb-0 fw-medium">' . wc_price( $total_tax ) . '</p>';
}


/**
 * Custom Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function custom_login_redirect( $redirect_to, $request, $user ) {
    // If is admin panel login page leave default behavior of the function.
    if( $GLOBALS['pagenow'] === 'wp-login.php' ){
        //is there a user to check?
	    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
	    	//check for admins
	    	if ( in_array( 'administrator', $user->roles ) ) {
	    		// redirect them to the default place
	    		return $redirect_to;
	    	} else {
	    		return home_url();
	    	}
	    } else {
	    	return $redirect_to;
	    }
    }

    $return_url = isset($_GET['return_to']) ? sanitize_text_field($_GET['return_to']) : '';
    if (!empty($return_url)) {
        return home_url('/checkout/?step=1');
    }

    // Check for session variable to redirect and clear it. For now this is using only for redirect after register
    if( isset( $_SESSION["return_url"] ) && !empty( $_SESSION["return_url"]) ) {
        // Clear session variable.
        unset( $_SESSION["return_url"] );
    }

    return $redirect_to;
}
add_filter('login_redirect', 'custom_login_redirect', 10, 3);

/**
 * If user is not logged in, on some submission start session to keep a flag,
 * that we need to redirect user after login or register to the checkout step.
 */
function tt_redirect_after_signin_signup_action_cb()
{
    if( !is_user_logged_in() ){
        // Start Session
        session_start();
        // Assign redirect url to session variable
        $_SESSION["return_url"]='/checkout/?step=1';
    }
    exit;
}
add_action('wp_ajax_tt_redirect_after_signin_signup_action', 'tt_redirect_after_signin_signup_action_cb');
add_action('wp_ajax_nopriv_tt_redirect_after_signin_signup_action', 'tt_redirect_after_signin_signup_action_cb');

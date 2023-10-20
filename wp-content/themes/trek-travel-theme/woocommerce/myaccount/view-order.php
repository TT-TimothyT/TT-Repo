<?php

/**
 * View Order
 *
 * Shows the details of a particular order on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/view-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

defined('ABSPATH') || exit;
$notes = $order->get_customer_order_notes();
?>
<?php if ($notes) : ?>
    <h2><?php esc_html_e('Order updates', 'woocommerce'); ?></h2>
    <ol class="woocommerce-OrderUpdates commentlist notes">
        <?php foreach ($notes as $note) : ?>
            <li class="woocommerce-OrderUpdate comment note">
                <div class="woocommerce-OrderUpdate-inner comment_container">
                    <div class="woocommerce-OrderUpdate-text comment-text">
                        <p class="woocommerce-OrderUpdate-meta meta"><?php echo date_i18n(esc_html__('l jS \o\f F Y, h:ia', 'woocommerce'), strtotime($note->comment_date)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                                                                        ?></p>
                        <div class="woocommerce-OrderUpdate-description description">
                            <?php echo wpautop(wptexturize($note->comment_content)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                            ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<?php

$order = wc_get_order($order_id);
$userInfo = wp_get_current_user();
$user_id = $userInfo->ID;
$order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$userInfo = wp_get_current_user();
$accepted_p_ids = tt_get_line_items_product_ids();
$guest_emails_arr = trek_get_guest_emails($user_id, $order_id);
$User_order_info = trek_get_user_order_info($user_id, $order_id);
$guest_emails = implode(', ', $guest_emails_arr);
$trek_formatted_checkoutData = $trek_checkoutData = array();
$trip_name = $trip_order_date = '';
$trip_name = $trip_sdate = $trip_edate = $trip_sku = '';
$order_item;
$booked_trip_id = null;
$product_quantity = '';
foreach ($order_items as $item_id => $item) {
    $product_id = $item['product_id'];
    $product_quantity = $item['quantity'];
    if (!in_array($product_id, $accepted_p_ids)) {
        $order_item = $item;
        $booked_trip_id = $product_id;
        $product = $item->get_product();
        $trek_checkoutData = wc_get_order_item_meta($item_id, 'trek_user_checkout_data', true);
        $trek_formatted_checkoutData = wc_get_order_item_meta($item_id, 'trek_user_formatted_checkout_data', true);
        if ($product) {
            $trip_status = $product->get_attribute('pa_trip-status');
            $trip_sdate = $product->get_attribute('pa_start-date');
            $trip_edate = $product->get_attribute('pa_end-date');
            $trip_name = $product->get_name();
            $trip_sku = $product->get_sku();
            $sdate_obj = explode('/', $trip_sdate);
            $sdate_info = array(
                'd' => $sdate_obj[0],
                'm' => $sdate_obj[1],
                'y' => substr(date('Y'), 0, 2) . $sdate_obj[2]
            );
            $edate_obj = explode('/', $trip_edate);
            $edate_info = array(
                'd' => $edate_obj[0],
                'm' => $edate_obj[1],
                'y' => substr(date('Y'), 0, 2) . $edate_obj[2]
            );
            $start_date_text = date('F jS, Y', strtotime(implode('-', $sdate_info)));
            $end_date_text_1 = date('F jS, Y', strtotime(implode('-', $edate_info)));
            $end_date_text_2 = date('jS, Y', strtotime(implode('-', $edate_info)));
            $date_range_1 = $start_date_text . ' - ' . $end_date_text_2;
            $date_range_2 = $start_date_text . ' - ' . $end_date_text_1;
            $date_range = $date_range_1;
            if ($sdate_info['m'] != $edate_info['m']) {
                $date_range = $date_range_2;
            }
            $product_image_url = 'https://via.placeholder.com/150?text=Trek Travel';
            if (has_post_thumbnail($product_id) && $product_id) {
                $product_image_url = get_the_post_thumbnail_url($product_id);
            }
        }
    }
}
$primary_address_1 = $trek_checkoutData['shipping_address_1'];
$primary_address_2 = $trek_checkoutData['shipping_address_2'];
$primary_country = $trek_checkoutData['shipping_country'];
$billing_add_1 = $trek_checkoutData['billing_address_1'];
$billing_add_2 = $trek_checkoutData['billing_address_2'];
$billing_country = $trek_checkoutData['billing_country'];
$billing_state = $trek_checkoutData['billing_state'];
$billing_city = $trek_checkoutData['billing_city'];
$billing_postcode = $trek_checkoutData['billing_postcode'];
$billing_name = ($trek_checkoutData['billing_first_name'] ? $trek_checkoutData['billing_first_name'] . ' ' . $trek_checkoutData['billing_last_name'] : '');
$shipping_name = ($trek_checkoutData['shipping_first_name'] ? $trek_checkoutData['shipping_first_name'] . ' ' . $trek_checkoutData['shipping_last_name'] : '');
$biller_name = (!empty($billing_name) ? $billing_name : $shipping_name);
$emergence_cfname = isset($User_order_info[0]['emergency_contact_first_name']) ? $User_order_info[0]['emergency_contact_first_name'] : '';
$emergence_clname = isset($User_order_info[0]['emergency_contact_last_name']) ? $User_order_info[0]['emergency_contact_last_name'] : '';
$emergence_cphone = isset($User_order_info[0]['emergency_contact_phone']) ? $User_order_info[0]['emergency_contact_phone'] : '';
$emergence_crelationship = isset($User_order_info[0]['emergency_contact_relationship']) ? $User_order_info[0]['emergency_contact_relationship'] : '';
$medicalconditions = isset($User_order_info[0]['medical_conditions']) ? $User_order_info[0]['medical_conditions'] : '';
$medications = isset($User_order_info[0]['medications']) ? $User_order_info[0]['medications'] : '';
$allergies = isset($User_order_info[0]['allergies']) ? $User_order_info[0]['allergies'] : '';
$dietaryrestrictions = isset($User_order_info[0]['dietary_restrictions']) ? $User_order_info[0]['dietary_restrictions'] : '';

$waiver_signed = isset($User_order_info[0]['waiver_signed']) ? $User_order_info[0]['waiver_signed'] : '';
$passport_number = isset($User_order_info[0]['passport_number']) ? $User_order_info[0]['passport_number'] : '';
$passport_issue_date = isset($User_order_info[0]['passport_issue_date']) ? $User_order_info[0]['passport_issue_date'] : '';
$passport_expiration_date = isset($User_order_info[0]['passport_expiration_date']) ? $User_order_info[0]['passport_expiration_date'] : '';
$passport_place_of_issue = isset($User_order_info[0]['passport_place_of_issue']) ? $User_order_info[0]['passport_place_of_issue'] : '';
$full_name_on_passport = isset($User_order_info[0]['full_name_on_passport']) ? $User_order_info[0]['full_name_on_passport'] : '';
$rider_height = isset($User_order_info[0]['rider_height']) ? $User_order_info[0]['rider_height'] : '';
$bike_id = isset($User_order_info[0]['bike_id']) ? $User_order_info[0]['bike_id'] : '';
$bike_size = isset($User_order_info[0]['bike_size']) ? $User_order_info[0]['bike_size'] : '';
$pedal_selection = isset($User_order_info[0]['pedal_selection']) ? $User_order_info[0]['pedal_selection'] : '';
$bikeTypeId = isset($User_order_info[0]['bikeTypeId']) ? $User_order_info[0]['bikeTypeId'] : '';
$helmet_selection = isset($User_order_info[0]['helmet_selection']) ? $User_order_info[0]['helmet_selection'] : '';
$saddle_height = isset($User_order_info[0]['saddle_height']) ? $User_order_info[0]['saddle_height'] : '';
$saddle_bar_reach_from_saddle = isset($User_order_info[0]['saddle_bar_reach_from_saddle']) ? $User_order_info[0]['saddle_bar_reach_from_saddle'] : '';
$saddle_bar_height_from_wheel_center = isset($User_order_info[0]['saddle_bar_height_from_wheel_center']) ? $User_order_info[0]['saddle_bar_height_from_wheel_center'] : '';
$jersey_style = isset($User_order_info[0]['jersey_style']) ? $User_order_info[0]['jersey_style'] : '';
$tt_jersey_size = isset($User_order_info[0]['tt_jersey_size']) ? $User_order_info[0]['tt_jersey_size'] : '';
$tshirt_size = isset($User_order_info[0]['tshirt_size']) ? $User_order_info[0]['tshirt_size'] : '';
$shorts_bib_size = isset($User_order_info[0]['shorts_bib_size']) ? $User_order_info[0]['shorts_bib_size'] : '';
$trip_room_selection = isset($User_order_info[0]['trip_room_selection']) ? $User_order_info[0]['trip_room_selection'] : '';
$guest_is_primary = isset($User_order_info[0]['guest_is_primary']) ? $User_order_info[0]['guest_is_primary'] : '';
$cc_account_four = get_post_meta($order_id, '_wc_cybersource_credit_card_account_four', true);
$cc_expiry_date = get_post_meta($order_id, '_wc_cybersource_credit_card_expiry_date', true);
$cc_card_type = get_post_meta($order_id, '_wc_cybersource_credit_card_type', true);
$cc_card_type = isset($trek_checkoutData['wc-cybersource-credit-card-card-type']) ? $trek_checkoutData['wc-cybersource-credit-card-card-type'] : '';
$cc_expiry_date = isset($trek_checkoutData['wc-cybersource-credit-card-expiry']) ? $trek_checkoutData['wc-cybersource-credit-card-expiry'] : '';
$guest_insurance_html = tt_guest_insurance_output($trek_checkoutData);
$rooms_html = tt_rooms_output($trek_checkoutData, true);
$guests_gears_data = tt_guest_details($trek_checkoutData);
$trip_information = tt_get_trip_pid_sku_from_cart($order_id);
$product_image_url = $trip_information['parent_trip_image'];
//deposite due vars
$depositAmount = tt_get_local_trips_detail('depositAmount', '', $trip_sku, true);

//get the supliment fees
$supplementFees = tt_get_local_trips_detail('singleSupplementPrice', '', $trip_sku, true);

//Get the products from the order
$products = $order->get_items();

//Loop through the products
foreach( $products as $product ) {
    //Get the product name
    $product_name = $product->get_name();
    if( $product_name == "Single Suppliment Fees" ) {
        // Get product quantity
        $product_quantity = $product->get_quantity();
        $supplementFees = $supplementFees * $product_quantity;
    }

    if( $product_name == 'Insurance Fees' ) {
        // Get product quantity
        $product_quantity = $product->get_quantity();
        $insuranceFees = $product->get_total();
    }
}

if( ! empty( $supplementFees ) ) {
    $depositAmount = $depositAmount ? str_ireplace(',', '', $depositAmount) : 0;
    $depositAmount = floatval($depositAmount) * intval(isset($trek_checkoutData['no_of_guests']) ? $trek_checkoutData['no_of_guests'] : 1);
    $depositAmount = $depositAmount + floatval( $supplementFees );
    $depositAmount = $depositAmount + floatval( $insuranceFees );
} else {
    $depositAmount = $depositAmount ? str_ireplace(',', '', $depositAmount) : 0;
    $depositAmount = floatval($depositAmount) * intval(isset($trek_checkoutData['no_of_guests']) ? $trek_checkoutData['no_of_guests'] : 1);
    $depositAmount = $depositAmount + floatval( $insuranceFees );
}

$taxes_amount = floatval( $order->get_cart_tax() );

if( ! empty( $taxes_amount ) ) {
    $depositAmount = $depositAmount + floatval( $taxes_amount );
}

$depositAmount = round( $depositAmount, 3 );

$cart_total = $order->get_total();
$remaining_amount = $cart_total - ($depositAmount ? $depositAmount : 0);
$remaining_amountCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $remaining_amount . '</span>';
$cart_totalCurr = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $cart_total . '</span>';
$depositAmountCurr  = '<span class="amount"><span class="woocommerce-Price-currencySymbol"></span>' . $depositAmount . '</span>';
$tripRegion = tt_get_local_trips_detail('tripRegion', '', $trip_sku, true);
$parent_product_id = tt_get_parent_trip_id_by_child_sku($trip_sku);
$pa_city = "";
if( $parent_product_id ){
    $p_product = wc_get_product($parent_product_id);
    if($p_product){
        $pa_city = $p_product->get_attribute('pa_city');
    }
}
?>
<div class="container my-trip-order-summary my-4">
    <div class="row mx-0 flex-column flex-lg-row">
        <div class="col-lg-6 my-trips__back order-1 order-lg-0">
            <a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
        </div>
        <div class="col-lg-6 d-flex dashboard__log">
            <p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
            <a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
        </div>
    </div>

    <div id="my-trips-responses"></div>

    <div class="row mx-0">
        <div class="col-lg-10">
            <div class="card my-trip-order-summary__card rounded-1">

                <div class="trips-list-item">
                    <div class="trip-image">
                        <img src="<?php echo $product_image_url; ?>" />
                    </div>
                    <div class="trip-info">
                        <p class="fw-normal fs-sm lh-sm mb-0 mt-4">
                        <?php
                            $trip_address = [$pa_city,$tripRegion];
                            $trip_address = array_filter($trip_address);
                            echo implode(', ', $trip_address);
                        ?>
                        </p>
                        <h5 class="fw-semibold"><?php echo $trip_name; ?></h5>
                        <p class="fw-medium fs-sm lh-sm"><?php echo $date_range; ?></p>

                    </div>
                    <div class="trip-details-cta my-4">
                        <a href="" onclick="printThis('order-details-page');" class="btn btn-md fw-semibold w-100 btn-secondary btn-outline-dark rounded-1">Print Summary</a>
                    </div>
                </div>

                <div class="container order-details p-0" id="order-details-page">
                    <div class="row mx-0">
                        <div class="col-12">
                            <div class="order-details__summary">
                                <div class="order-details__content">
                                    <div class="d-flex">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td colspan="2">
                                                        <p class="fs-xl lh-xl fw-semibold">Order Details</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p class="mb-0 fw-normal order-details__text">Purchase Date</p>
                                                    </td>
                                                    <td>
                                                        <p class="mb-0 fw-normal order-details__text"><?php echo date('M d, Y', strtotime($order->get_date_created())) ?></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p class="mb-0 fw-normal order-details__text"><?php echo $trip_name; ?> x <?php echo $product_quantity; ?></p>
                                                    </td>
                                                    <td>
                                                        <p class="mb-0 fw-normal order-details__text"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $order->get_subtotal(); ?></span></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p class="mb-0 fw-normal order-details__text">Subtotal</p>
                                                    </td>
                                                    <td>
                                                        <p class="mb-0 fw-normal order-details__text"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $order->get_subtotal(); ?></span></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p class="mb-0 fw-normal order-details__text">Taxes</p>
                                                    </td>
                                                    <td>
                                                    <?php
                                                    $local_tax = $order->get_cart_tax();
                                                    if ( '0' === $local_tax ) {
                                                        $local_tax = $order->get_total() - $order->get_subtotal();
                                                    }
                                                    ?>
                                                        <p class="mb-0 fw-normal order-details__text"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $local_tax; ?></span></p>
                                                    </td>
                                                </tr>
                                                <tr class="border-white">
                                                    <td>
                                                        <p class="mb-0 fw-semibold fs-md lh-md">Trip Total</p>
                                                    </td>
                                                    <td>
                                                        <p class="mb-0 fw-semibold fs-md lh-md"><span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo $order->get_total(); ?></span></p>
                                                    </td>
                                                </tr>
                                                <tr class="border-white">
                                                    <td>
                                                        <p class="mb-0 fw-semibold fs-md lh-md">Remaining Due</p>
                                                    </td>
                                                    <td>
                                                        <p class="mb-0 fw-semibold fs-md lh-md"><?php echo $remaining_amountCurr; ?></p>
                                                    </td>
                                                </tr>
                                                <tr class="border-white">
                                                    <td class="w-50">
                                                        <p class="mb-0 fs-sm lh-sm fw-normal w-75 order-details__duesp">You will be responsible for paying the remaining amount on your trip before the trip start date. Our team will reach out to collect final payment.</p>
                                                    </td>
                                                </tr>

                                            </tbody>
                                        </table>

                                    </div>

                                </div>
                                <hr>
                                <div class="order-details__content">
                                    <p class="fs-xl lh-xl fw-semibold order-details__subheading">Payment Details</p>
                                    <div class="d-flex order-details__flex">
                                        <div>
                                            <p class="mb-2 fs-md lh-md fw-medium">Billing Method</p>
                                            <p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $biller_name; ?></p>
                                            <p class="mb-0 fs-sm lh-sm fw-normal"><?php echo ($cc_card_type ? $cc_card_type  : '') ?> <?php echo ($cc_account_four ? '****' . $cc_account_four  : '') ?></p>
                                            <p class="mb-0 fs-sm lh-sm fw-normal"><?php echo ($cc_expiry_date ? 'Exp: ' . $cc_expiry_date  : '') ?></p>
                                        </div>
                                        <div>
                                            <p class="mb-2 fs-md lh-md fw-medium">Billing Address</p>
                                            <p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_add_1; ?></p>
                                            <p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_add_2; ?></p>
                                            <p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_state; ?>, <?php echo $billing_city; ?>, <?php echo $billing_postcode; ?></p>
                                            <p class="mb-0 fs-sm lh-sm fw-normal"><?php echo $billing_country; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="order-details__content">
                                    <p class="fs-xl lh-xl fw-semibold order-details__subheading">Guest Details</p>
                                    <?php
                                    echo $guests_gears_data['guests'];
                                    ?>
                                </div>
                                <hr>
                                <div class="order-details__content">
                                    <p class="fs-xl lh-xl fw-semibold order-details__subheading">Room Details</p>
                                    <div class="d-flex order-details__flex">
                                        <?php echo $rooms_html; ?>
                                    </div>
                                </div>
                                <hr>
                                <div class="order-details__content">
                                    <p class="fs-xl lh-xl fw-semibold order-details__subheading">Bikes &amp; Gear Details</p>
                                    <?php echo $guests_gears_data['bike_gears']; ?>
                                </div>
                                <hr>
                                <div class="order-details__content">
                                    <p class="fs-xl lh-xl fw-semibold order-details__subheading">Travel Protection Details</p>
                                    <?php echo $guest_insurance_html; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
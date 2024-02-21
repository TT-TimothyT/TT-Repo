<?php

/**
 * Edit address form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

$page_title = ('billing' === $load_address) ? esc_html__('Billing Address', 'woocommerce') : esc_html__('Shipping Address', 'woocommerce');
global $woocommerce;
$trek_user_checkout_data =  get_trek_user_checkout_data();
$trek_user_checkout_posted = $trek_user_checkout_data['posted'];
$userInfo = wp_get_current_user();
?>
<div class="container shipping-address px-0">
    <div class="row mx-0 flex-column flex-lg-row">
        <div class="col-lg-6 medical-information__back order-1 order-lg-0">
            <a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
        </div>
        <div class="col-lg-6 d-flex dashboard__log">
            <p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
            <a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
        </div>
    </div>
    <div class="row mx-0">
        <div class="col-12 col-md-10 px-0">
            <div class="d-flex shipping-address__title-div align-items-lg-baseline">
                <h3 class="shipping-address__title fw-bold"><?php echo $page_title; ?></h3>
                <p class="shipping-address__required fw-normal">Required *</p>
            </div>
            <form id="formID" method="post">
                <div class="shipping-address__card rounded-1">
                    <?php
                    $add_key = ($load_address === 'billing' ? 'billing' : 'shipping');
                    $fields = $woocommerce->checkout->get_checkout_fields($add_key);
                    $iter = 0;
                    $field_html = '';
                    $fields_size = sizeof($fields);
                    $cols = 2;
                    $field_includes = array(''.$add_key.'_first_name',''.$add_key.'_last_name',''.$add_key.'_address_1', ''.$add_key.'_address_2', ''.$add_key.'_country', ''.$add_key.'_state', ''.$add_key.'_city', ''.$add_key.'_postcode');
                    foreach ($fields as $key => $field) {
                        if (in_array($key, $field_includes)) {
                            if ($iter % $cols == 0) {
                                $field_html .= '<div class="row mx-0 guest-checkout__primary-form-row billing_row">';
                            }
                            $field_html .= '<div class="col-md px-0 form-row"><div class="form-floating">';
                            $field['placeholder'] = $field['label'];
                            $field['label'] = '';
                            $field['input_class'] = array('form-control');
                            $field['return'] = true; ?>
                            <?php 
                            $shipping_address_1 = get_user_meta($userInfo->ID, 'shipping_address_1', true);
                            $billing_address_1 = get_user_meta($userInfo->ID, 'billing_address_1', true);
                            $is_billing_page = ('billing' === $load_address);
                            $is_shipping_page = ('shipping' === $load_address);
                            ?>
                            <?php if($shipping_address_1 && $is_shipping_page): ?>
                            <?php $woo_field_value = $woocommerce->checkout->get_value($key); ?>
                            <?php elseif ($billing_address_1 && $is_billing_page) : ?>
                            <?php $woo_field_value = $woocommerce->checkout->get_value($key); ?>
                            <?php else : ?>
                            <?php $woo_field_value = ''; // Initialize with empty value ?>
                            <?php endif; ?><?php
                            if( $key == 'billing_state' ) {
                                $country_val = get_user_meta( get_current_user_id(), 'billing_country', true );
                                $state_val   = get_user_meta( get_current_user_id(), 'billing_state', true );
                                $field['country'] = ! empty( $country_val ) ? $country_val : '';
                                $woo_field_value =  $state_val;
                            }
                            if ( $key == 'billing_country' ) {
                                $country_val = get_user_meta( get_current_user_id(), 'billing_country', true );
                                $field['country'] = ! empty( $country_val ) ? $country_val : '';
                                $woo_field_value =  $country_val;
                            }
                            if( $key == 'shipping_state' ) {
                                $country_val = get_user_meta( get_current_user_id(), 'shipping_country', true );
                                $state_val   = get_user_meta( get_current_user_id(), 'shipping_state', true );
                                $field['country'] = ! empty( $country_val ) ? $country_val : '';
                                $woo_field_value =  $state_val;
                            }
                            if ( $key == 'shipping_country' ) {
                                $country_val = get_user_meta( get_current_user_id(), 'shipping_country', true );
                                $field['country'] = ! empty( $country_val ) ? $country_val : '';
                                $woo_field_value =  $country_val;
                            }
                            $field_input = woocommerce_form_field($key, $field, $woo_field_value);
                            $field_input = str_ireplace('<span class="woocommerce-input-wrapper">', '', $field_input);
                            $field_input = str_ireplace('</span>', '', $field_input);
                            $sort            = $field['priority'] ? $field['priority'] : '';
                            if (isset($field['required'])) {
                                $field['class'][] = 'validate-required';
                            }
                            if (isset($field['validate'])) {
                                foreach ($field['validate'] as $validate_name) {
                                    $field['class'][] = 'validate-' . $validate_name . '';
                                }
                            }
                            $container_class = isset($field['class']) ? esc_attr(implode(' ', $field['class'])) : '';
                            $container_id    = esc_attr($key) . '_field';
                            $pfield_container = '<p class="form-row ' . $container_class . '" id="' . $container_id . '" data-priority="' . esc_attr($sort) . '">';
                            $field_input = str_ireplace($pfield_container, '', $field_input);
                            $field_input = str_ireplace('<p class="form-row form-row-wide address-field" id="' . ''.$add_key.'_address_2_field" data-priority="26">', '', $field_input);
                            $field_input = str_ireplace('<p class="form-row form-row-wide address-field validate-postcode" id="' . ''.$add_key.'_postcode_field" data-priority="90">', '', $field_input);
                            $field_input = str_ireplace('</p>', '', $field_input);
                            $field_html .= $field_input;
                            if($key == "billing_address_2" || $key == "shipping_address_2"){
                                $field_html .= '<label for="' . ''.$add_key.'_' . $key . '">' . $field['placeholder'] . '</label>';
                            } else {
                                $field_html .= '<label for="' . ''.$add_key.'_' . $key . '">' . $field['placeholder'] . '*</label>';
                            }
                            $field_html .= '</div></div>';
                            if (($iter % $cols == $cols - 1) || ($iter == $fields_size - 1)) {
                                $field_html .= '</div>';
                            }
                            $iter++;
                        }
                    }
                    echo $field_html;
                    ?>
                    <?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
                    <input type="hidden" name="billing_phone" value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'custentity_phone_number', true ) ); ?>" />
                    <input type="hidden" name="action" value="edit_address" />
                    <div class="shipping-address__button d-flex align-items-lg-center">
                        <div class="d-flex align-items-center shipping-address__flex w-100">
                            <button type="submit" class="btn btn-lg btn-primary fs-md lh-md shipping-address__save">Save</button>
                            <a href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>" class="shipping-address__cancel">Cancel</a>
                            <!--<a type="button" data-bs-toggle="modal" data-bs-target="#deleteAddressModal" class="shipping-address__delete">Delete this address</a> -->
                           <button type="button" class="delete-address-btn shipping-address__delete">Delete this address</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php //endif; 
?>

<?php do_action('woocommerce_after_edit_account_address_form'); ?>
<div class="container">
    <!-- Modal -->
    <div class="modal fade modal-delete-address" id="deleteAddressModal" tabindex="-1" aria-labelledby="deleteAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="deleteAddressModalLabel">Filters</h5>
                    <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i type="button" class="bi bi-x"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <p class="fw-medium fs-xl lh-xl">Delete <?php echo ucfirst($add_key); ?> Address?</p>
                    <p class="fw-normal fs-md lh-md mb-1"><?php echo $woocommerce->checkout->get_value($add_key.'_first_name')." ". $woocommerce->checkout->get_value($add_key.'_last_name'); ?></p>
                    <p class="fw-normal fs-md lh-md mb-1"><?php echo $woocommerce->checkout->get_value($add_key.'_address_1')." ". $woocommerce->checkout->get_value($add_key.'_address_2'); ?></p>
                    <p class="fw-normal fs-md lh-md mb-1"><?php echo $woocommerce->checkout->get_value($add_key.'_city')." ". $woocommerce->checkout->get_value($add_key.'_state')." ". $woocommerce->checkout->get_value($add_key.'_postcode'); ?></p>
                    <p class="fw-normal fs-md lh-md"><?php echo $woocommerce->checkout->get_value($add_key.'_country'); ?></p>
                    <input type="hidden" id="addressId">
                </div>
                <div class="modal-footer">
                    <div class="container">
                        <div class="row align-items-center">                                            
                            <div class="col text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>                                                                                
                                <!--<button type="button" class="btn btn-primary proceed-delete-address-btn" data-bs-dismiss="modal">Proceed</button>-->
                      			<?php
                                    $address_type = ($load_address === 'billing' ? 'billing' : 'shipping');

                                    if (is_array($address_type)) {
                                    $address_type = implode(',', $address_type); // Convert the array to a string
                                    }
                                ?>
                                <button type="button" class="btn btn-primary proceed-delete-address-btn" data-address-type="<?php echo esc_attr($address_type); ?>" data-bs-dismiss="modal">Proceed</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- / .modal-content -->
        </div><!-- / .modal-dialog -->
    </div><!-- / .modal -->
</div> <!-- / Modal .container -->
<script>
    // Use jQuery to handle the button click event
    jQuery(document).ready(function($) {
        <?php 
        //if form is empty disabled delete address button
        $shipping_address_1 = get_user_meta($userInfo->ID, 'shipping_address_1', true);
        $billing_address_1 = get_user_meta($userInfo->ID, 'billing_address_1', true);
        $is_billing_page = ('billing' === $load_address);
        $is_shipping_page = ('shipping' === $load_address);
        ?>
        <?php if(empty($shipping_address_1) && $is_shipping_page): ?>
            $('.delete-address-btn').prop('disabled', true);
        <?php elseif (empty($billing_address_1) && $is_billing_page) : ?>
            $('.delete-address-btn').prop('disabled', true);
        <?php else : ?>
            $('.delete-address-btn').prop('disabled', false);
        <?php endif; ?>
        $('.delete-address-btn').on('click', function() {
            // Show the popup when the "Delete Address" button is clicked
            $('#deleteAddressModal').modal('show');
        });

        // Handle the "Proceed" button click event in the popup
        $('.proceed-delete-address-btn').on('click', function() {
            var addressType = $(this).data('address-type'); // Get the address type from the data attribute

            // Send an AJAX request to the server to delete the address
            $.ajax({
                url: ajaxurl, // Use 'ajaxurl' provided by WordPress
                type: 'POST',
                data: {
                    action: 'delete_user_address', // This will be the name of your server-side callback function
                    addressType: addressType // Pass the address type to the server
                },
                dataType: 'json',
                success: function(response) {
                    // Handle the AJAX response here
                    if (response.success) {
                        // Address deleted successfully
                        alert('Address deleted successfully.');

                        // Clear form fields (optional)
                        $('#formID')[0].reset();

                        // Redirect the user to the dashboard page
                        window.location.href = '/my-account/'; // Replace with the actual dashboard URL
                    } else {
                        // Address deletion failed
                        alert('Failed to delete address.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Handle AJAX error here
                    console.error('AJAX Request Error:', textStatus, errorThrown);
                }
            });
        });
    });
</script>

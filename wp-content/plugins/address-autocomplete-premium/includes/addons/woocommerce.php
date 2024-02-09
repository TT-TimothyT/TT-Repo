<?php
class WPSunshine_Address_Autocomplete_WooCommerce {

    public function __construct() {

        add_filter( 'wps_aa_addons', array( $this, 'register' ), 99 );

        if ( !class_exists( 'WooCommerce' ) ) {
            return;
        }

        add_action( 'wps_aa_instances', array( $this, 'add_instances' ) );

    }

    public function register( $addons ) {
        $addons['woocommerce_checkout'] = __( 'WooCommerce Checkout', 'address-autocomplete-anything' );
        $addons['woocommerce_myaccout'] = __( 'WooCommerce My Account', 'address-autocomplete-anything' );
        return $addons;
    }

    public function add_instances( $instances ) {

        $addons = get_option( 'wps_aa_addons' );
        if ( empty( $addons ) ) {
            return $instances;
        }

        $build_instances = false;

        if ( in_array( 'woocommerce_checkout', $addons ) && ( is_checkout() || is_admin() ) ) {
            $build_instances = true;
        }

        if ( in_array( 'woocommerce_myaccout', $addons ) && ( is_wc_endpoint_url( 'edit-address' ) || is_admin() ) ) {
            $build_instances = true;
        }

        if ( $build_instances ) {

            $countries = new WC_Countries();
            $countries = $countries->get_allowed_countries();

            if ( !array_key_exists( 'woocommerce_checkout_billing', $instances ) ) {

                $fields = array();
                // Build instance data for Billing
                $fields[] = array(
                    'selector' => '#billing_country',
                    'data' => '{country:short_name}'
                );
                $fields[] = array(
                    'selector' => '#billing_address_1',
                    'data' => '{address1:long_name}'
                );
                $fields[] = array(
                    'selector' => '#billing_address_2',
                    'data' => '{address2:long_name}'
                );
                $fields[] = array(
                    'selector' => '#billing_city',
                    'data' => '{locality:long_name}'
                );
                $fields[] = array(
                    'selector' => '#billing_state',
                    'data' => '{administrative_area_level_1:short_name}'
                );
                $fields[] = array(
                    'selector' => '#billing_postcode',
                    'data' => '{postal_code:long_name}'
                );

                $instances[ 'woocommerce_checkout_billing' ] = array(
                    'label' => 'WooCommerce Billing',
                    'init' => '#billing_address_1',
                    'page' => '',
                    'allowed_countries' => ( count( $countries ) <= 5 ) ? array_keys( $countries ) : '',
                    'fields' => $fields
                );

            }

            if ( !array_key_exists( 'woocommerce_checkout_shipping', $instances ) ) {

                $fields = array();
                // Build instance data for Shipping
                $fields[] = array(
                    'selector' => '#shipping_country',
                    'data' => '{country:short_name}'
                );
                $fields[] = array(
                    'selector' => '#shipping_address_1',
                    'data' => '{address1:long_name}'
                );
                $fields[] = array(
                    'selector' => '#shipping_address_2',
                    'data' => '{address2:long_name}'
                );
                $fields[] = array(
                    'selector' => '#shipping_city',
                    'data' => '{locality:long_name}'
                );
                $fields[] = array(
                    'selector' => '#shipping_state',
                    'data' => '{administrative_area_level_1:short_name}'
                );
                $fields[] = array(
                    'selector' => '#shipping_postcode',
                    'data' => '{postal_code:long_name}'
                );

                $instances[ 'woocommerce_checkout_shipping' ] = array(
                    'label' => 'WooCommerce Shipping',
                    'init' => '#shipping_address_1',
                    'page' => '',
                    'allowed_countries' => ( count( $countries ) <= 5 ) ? array_keys( $countries ) : '',
                    'fields' => $fields
                );

            }

        }

        return $instances;

    }

}

$wps_aa_woocommerce = new WPSunshine_Address_Autocomplete_WooCommerce();

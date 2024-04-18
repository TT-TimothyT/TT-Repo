import { CART_STORE_KEY } from '@woocommerce/block-data';
import { select, subscribe } from '@wordpress/data';

const store =  select ( CART_STORE_KEY );

const debounce = (callback, wait) => {
	let timeoutId = null;
	return (...args) => {
	  window.clearTimeout(timeoutId);
	  timeoutId = window.setTimeout(() => {
		callback.apply(null, args);
	  }, wait);
	};
}

var can_run = false;
var gdpr_consent = true;

const checkKeyPress = debounce((ev) => {
	can_run = true;
}, 1000 );

window.addEventListener('keypress', checkKeyPress );

const checkStorage = ( event ) => {
	if ( 'wcap_sms_consent' === event.key || 'wcap_gdpr_no_thanks' === event.key ) {

		if ( 'wcap_sms_consent' == event.key ) {
			var data = {
				action : 'wcap_sms_consent',
				consent: event.newValue
			};
		} else if ( 'wcap_gdpr_no_thanks' === event.key ) {
			if ( event.newValue ) {
				gdpr_consent = false;
			}
			var data = {
	            action : 'wcap_gdpr_refused'
	        };
		}

		jQuery.post( wcap_guest_capture_blocks_params.ajax_url, data, function( response ) {
		});

	}
}
window.addEventListener('storage', checkStorage );

const unsubscribe = subscribe( () => {
	// Figure out a way to store the old addresses from the previous `subscribe` call and compare them to the new addresses to know if they've changed.
	const { billingAddress, shippingAddress } = store.getCustomerData();

	// Prepopulate the fields if the user is recovering a cart.
	if ( wcap_guest_capture_blocks_params.user_id >= 63000000 && ! localStorage.getItem( 'wcap_data_populated' ) ) {

		// Email.
		billingAddress.email = wcap_guest_capture_blocks_params.email;
		localStorage.setItem( 'wcap_user_email', wcap_guest_capture_blocks_params.email );
		// First Name.
		billingAddress.first_name = wcap_guest_capture_blocks_params.first_name;
		shippingAddress.first_name = wcap_guest_capture_blocks_params.first_name;
		localStorage.setItem( 'wcap_user_firstname', wcap_guest_capture_blocks_params.first_name );
		// Last Name.
		billingAddress.last_name = wcap_guest_capture_blocks_params.last_name;
		shippingAddress.last_name = wcap_guest_capture_blocks_params.last_name;
		localStorage.setItem( 'wcap_user_lastname', wcap_guest_capture_blocks_params.last_name );
		// Phone.
		billingAddress.phone = wcap_guest_capture_blocks_params.phone;
		shippingAddress.phone = wcap_guest_capture_blocks_params.phone;
		localStorage.setItem( 'wcap_user_phone', wcap_guest_capture_blocks_params.phone );
		localStorage.setItem( 'wcap_data_populated', true );
	}
	// Data captured in ATC needs to be prepopulated.
	if ( localStorage.wcap_hidden_email_id !== 'undefined' && localStorage.wcap_hidden_email_id && '' === billingAddress.email ) {
		billingAddress.email = localStorage.wcap_hidden_email_id;
	}
	if( localStorage.wcap_atc_phone_number !== 'undefined' && localStorage.wcap_atc_phone_number && '' === shippingAddress.phone ){
		shippingAddress.phone = localStorage.wcap_atc_phone_number;
	}
	if ( can_run && gdpr_consent ) {
		var saved_email = localStorage.getItem( 'wcap_user_email' );
		var saved_phone = localStorage.getItem( 'wcap_user_phone' );
		var saved_firstname = localStorage.getItem( 'wcap_user_firstname' );
		var saved_lastname = localStorage.getItem( 'wcap_user_lastname' );
		var saved_country = localStorage.getItem( 'wcap_user_country' );
		var saved_billing_postcode = localStorage.getItem( 'wcap_billing_postcode' );
		var saved_shipping_postcode = localStorage.getItem( 'wcap_shipping_postcode' );

		var page_email = billingAddress.email;
		var page_phone = billingAddress.phone;
		var page_firstname = billingAddress.first_name;
		var page_lastname = billingAddress.last_name;
		var page_country = billingAddress.country;
		var page_billing_postcode = billingAddress.postcode;
		var page_shipping_postcode = shippingAddress.postcode;

		var data_updated = false;
		if ( saved_email !== page_email ) {
			data_updated = true;
			localStorage.setItem( 'wcap_user_email', page_email );
		}

		if ( saved_phone != page_phone ) {
			data_updated = true;
			localStorage.setItem( 'wcap_user_phone', page_phone );
		}

		if ( saved_firstname != page_firstname ) {
			data_updated = true;
			localStorage.setItem( 'wcap_user_firstname', page_firstname );
		}

		if ( saved_lastname != page_lastname ) {
			data_updated = true;
			localStorage.setItem( 'wcap_user_lastname', page_lastname );
		}

		if ( saved_country != page_country ) {
			data_updated = true;
			localStorage.setItem( 'wcap_user_country', page_country );
		}

		if ( saved_billing_postcode != page_billing_postcode ) {
			data_updated = true;
			localStorage.setItem( 'wcap_billing_postcode', page_billing_postcode );
		}

		if ( saved_shipping_postcode != page_shipping_postcode ) {
			data_updated = true;
			localStorage.setItem( 'wcap_shipping_postcode', page_shipping_postcode );
		}
		if ( data_updated ) {

			var data = {
				billing_first_name  : localStorage.getItem( 'wcap_user_firstname' ),
				billing_last_name   : localStorage.getItem( 'wcap_user_lastname' ),
				billing_country     : localStorage.getItem( 'wcap_user_country' ),
				billing_phone       : localStorage.getItem( 'wcap_user_phone' ),
				billing_email       : localStorage.getItem( 'wcap_user_email' ),
				billing_postcode    : localStorage.getItem( 'wcap_billing_postcode' ),
				shipping_postcode   : localStorage.getItem( 'wcap_shipping_postcode' ),
				action              : 'wcap_save_guest_data'
			};

			if ( localStorage.wcap_abandoned_id ) {
				data.wcap_abandoned_id = localStorage.wcap_abandoned_id;
			}

			var wcap_record_added = false;
			if ( localStorage.wcap_atc_user_action && localStorage.wcap_atc_user_action === 'yes' ) {
				wcap_record_added = true;
			}

			data.wcap_record_added = wcap_record_added;

			jQuery.post( wcap_guest_capture_blocks_params.ajax_url, data, function( response ) {

				wcap_record_added = true;
			} );
		}
		can_run = false;
	}

}, CART_STORE_KEY );

/**
 * WooCommerce CyberSource 3D Secure handler.
 */
jQuery( document ).ready( ( $ ) => {

	'use strict';

	/**
	 * CyberSource Credit Card 3D Secure handler.
	 *
	 * @since 2.3.0
	 */
	window.WC_Cybersource_ThreeD_Secure_Handler = class WC_Cybersource_ThreeD_Secure_Handler {


		/**
		 * Instantiates 3D Secure handler.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} args
		 */
		constructor( args ) {

			this.order_id                = args.order_id;
			this.ajax_url                = args.ajax_url;
			this.logging_enabled         = args.logging_enabled;
			this.setup_action            = args.setup_action;
			this.setup_nonce             = args.setup_nonce;
			this.check_enrollment_action = args.check_enrollment_action;
			this.check_enrollment_nonce  = args.check_enrollment_nonce;
			this.enabled_card_types      = args.enabled_card_types;
			this.enabled_card_type_names = args.enabled_card_type_names;
			this.i18n                    = args.i18n;

			// enable Cardinal logging if enabled in the gateway
			if ( this.logging_enabled ) {

				Cardinal.configure( {
					logging: {
						level: 'on'
					}
				} );
			}

			Cardinal.on( 'payments.setupComplete', ( setupCompleteData ) => {

				let maskedCardNumber = this.handler.card_number;

				if ( this.handler.saved_payment_method_selected ) {
					maskedCardNumber = this.handler.form.find('input#wc-cybersource-credit-card-payment-token-' + this.handler.saved_payment_method_selected).data('card-bin').toString();
				}

				Cardinal.trigger( 'bin.process', maskedCardNumber.substring( 0, 6 ) ).then( ( result ) => {

					if ( result.Status ) {

						this.log( 'BIN detection successful' );

						if ( this.handler.saved_payment_method_selected ) {
							// using existing tokenized card
							this.check_enrollment( this.handler.saved_payment_method_selected, false );
						} else {
							// new card being tokenized
							this.check_enrollment( this.token, true );
						}

					} else {

						this.log( 'BIN detection failed', 'error' );

						this.handler.render_errors( [ this.i18n.error_general ] );
					}

					this.log( result );
				} );
			} );

			Cardinal.on( 'payments.validated', ( data, jwt ) => {
				this.validate_results( data, jwt );
			} );

			this.has_validated = false;

			$( document.body ).on( 'wc_cybersource_flex_form_submitted', ( event, data ) => {

				this.handler = data.payment_form;

				// assume new card
				let cardType = this.handler.card_type;
				let enabledCardTypes = this.enabled_card_types;

				// tokenized payment method handling
				if ( this.handler.saved_payment_method_selected ) {
					cardType = this.handler.form.find('input#wc-cybersource-credit-card-payment-token-' + this.handler.saved_payment_method_selected).data('card-type');
					enabledCardTypes = this.enabled_card_type_names;
				}

				// if the card type is not enabled for 3D Secure, bail for regular processing
				if ( ! enabledCardTypes.includes( cardType ) ) {
					return true;
				}

				// if already validated, proceed with form submission
				if ( this.has_validated ) {
					return true;
				}

				this.token = this.handler.jti;

				if ( this.handler.saved_payment_method_selected ) {
					this.setup( this.handler.saved_payment_method_selected, false );
				} else {
					this.setup( this.token, true );
				}

				return false;

			} );


		}


		/**
		 * Sets up the 3D Secure JS.
		 *
		 * @since 2.3.0
		 *
		 * @param {String} token
		 * @param {Boolean} isTransient
		 */
		setup( token, isTransient ) {
			$.post( this.ajax_url, {
				order_id:     this.order_id,
				action:       this.setup_action,
				nonce:        this.setup_nonce,
				token:        token,
				is_transient: isTransient
			}, ( response ) => {

				if ( response.success && response.data && response.data.jwt ) {

					this.reference_id = response.data.reference_id;

					Cardinal.setup( 'init', {
						jwt: response.data.jwt
					} );

				} else {

					this.log( 'JWT is missing', 'error' );

					this.handler.render_errors( [ this.i18n.error_general ] );
				}

			} ).fail( ( response ) => {

				this.handle_ajax_error( response );

			} );
		}


		/**
		 * Checks the given token for 3D Secure enrollment.
		 *
		 * @since 2.3.0
		 *
		 * @param {String} token
		 * @param {Boolean} isTransient
		 */
		check_enrollment( token, isTransient ) {

			$.post( this.ajax_url, {
				order_id:     this.order_id,
				reference_id: this.reference_id,
				action:       this.check_enrollment_action,
				nonce:        this.check_enrollment_nonce,
				token:        token,
				is_transient: isTransient
			}, ( response ) => {

				if ( response.success && response.data && response.data.order ) {

					this.handler.form.find( '#wc_cybersource_threed_secure_ecommerce_indicator' ).val( response.data.order.OrderDetails.ecommerceIndicator );
					this.handler.form.find( '#wc_cybersource_threed_secure_ucaf_collection_indicator' ).val( response.data.order.OrderDetails.ucafCollectionIndicator );
					this.handler.form.find( '#wc_cybersource_threed_secure_cavv' ).val( response.data.order.OrderDetails.cavv );
					this.handler.form.find( '#wc_cybersource_threed_secure_ucaf_authentication_data' ).val( response.data.order.OrderDetails.ucafAuthenticationData );
					this.handler.form.find( '#wc_cybersource_threed_secure_xid' ).val( response.data.order.OrderDetails.xid );
					this.handler.form.find( '#wc_cybersource_threed_secure_veres_enrolled' ).val( response.data.order.OrderDetails.veresEnrolled );
					this.handler.form.find( '#wc_cybersource_threed_secure_specification_version' ).val( response.data.order.OrderDetails.specificationVersion);
					this.handler.form.find( '#wc_cybersource_threed_secure_directory_server_transaction_id' ).val( response.data.order.OrderDetails.directoryServerTransactionId );
					this.handler.form.find( '#wc_cybersource_threed_secure_card_type' ).val( response.data.order.OrderDetails.cardType );

					if ( response.data.continue && response.data.continue.AcsUrl ) {

						this.log( 'calling Cardinal.continue()' );

						Cardinal.continue( 'cca', response.data.continue, response.data.order  );

					} else {

						this.log( 'No ACS URL, submitting form' );

						this.set_transaction_id( response.data.order.OrderDetails.TransactionId );
						this.set_reference_id( this.reference_id );

						// no redirect? consider it validated
						this.has_validated = true;

						this.handler.form.submit();
					}

				} else {

					this.log( 'Invalid data', 'error' );
					this.log( response );

					this.handler.render_errors( [ this.i18n.error_general ] );
				}


			} ).fail( ( response ) => {

				this.handle_ajax_error( response );

			} );
		}


		/**
		 * Validates the 3D Secure results.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} data
		 * @param {String} jwt
		 */
		validate_results( data, jwt ) {

			this.set_transaction_id( data.Payment.ProcessorTransactionId );
			this.set_reference_id( this.reference_id );
			this.handler.form.find( '#wc_cybersource_threed_secure_eci_flag' ).val( data.Payment.ExtendedData.ECIFlag );

			// if available from the challenge request, override the CAVV from the check enrollment response
			if ( data.Payment.ExtendedData.CAVV ) {
				this.handler.form.find( '#wc_cybersource_threed_secure_cavv' ).val( data.Payment.ExtendedData.CAVV );
			}

			this.handler.form.find( '#wc_cybersource_threed_secure_jwt' ).val( jwt );

			this.has_validated = true;

			this.handler.form.submit();
		}


		/**
		 * Sets the transaction ID.
		 *
		 * @since 2.3.0
		 *
		 * @param {String} transaction_id
		 */
		set_transaction_id( transaction_id ) {

			this.handler.form.find( '#wc_cybersource_threed_secure_transaction_id' ).val( transaction_id );
		}


		/**
		 * Sets the reference ID to the payment form.
		 *
		 * @since 2.5.1
		 *
		 * @param {String} reference_id
		 */
		set_reference_id( reference_id ) {

			this.handler.form.find( '#wc_cybersource_threed_secure_reference_id' ).val( reference_id );
		}


		/**
		 * Handles an AJAX error response.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} response
		 */
		handle_ajax_error( response ) {

			this.log( response.responseJSON.data ? response.responseJSON.data : 'Unknown error', 'error' );

			this.handler.render_errors( [ this.i18n.error_general ] );
		}


		/**
		 * Logs a message if enabled.
		 *
		 * @since 2.3.0
		 *
		 * @param message
		 * @param type
		 */
		log( message, type = '' ) {

			if ( ! this.logging_enabled ) {
				return;
			}

			if ( 'error' === type ) {
				console.error( message );
			} else {
				console.log( message );
			}
		}


	}

} );

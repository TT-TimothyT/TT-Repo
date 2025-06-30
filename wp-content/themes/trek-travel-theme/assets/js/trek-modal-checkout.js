/**
 * Quick Look Modal Checkout Functionality.
 *
 * Load this file on the my account pages on the front-end.
 *
 * @global tt_modal_checkout_params custom object
 * @global ttLoader defined in the developer.js custom object with show and hide methods.
 */

/**
 * Travel Protection Purchase Handler
 * 
 * Use this to add travel protection products to the cart via AJAX
 */
const TrekModalCheckout = {
	/**
	 * Add travel protection to cart
	 * 
	 * @param {number} productId - The protection product ID
	 * @param {string} origin - Where was the protection added from (checkout, my account, etc.)
	 * @param {Object} options - Additional options (quantity, variation_id, etc.)
	 * @return {Promise} - Returns a promise with the result
	 */
	addProtection: function(productId, orderId, origin, options = {}) {
		return new Promise((resolve, reject) => {
			// Default options
			const defaultOptions = {
				quantity: 1,
				variation_id: 0,
				variation: {}
			};

			// Merge options
			const settings = {...defaultOptions, ...options};

			// Create form data
			const formData = new FormData();
			formData.append('action', 'tt_add_travel_protection');
			formData.append('security', tt_modal_checkout_params.nonce || '');
			formData.append('product_id', productId);
			formData.append('order_id', orderId);
			formData.append('request_origin', origin);
			formData.append('quantity', settings.quantity);
			formData.append('variation_id', settings.variation_id);

			// Add variations if any
			if (settings.variation) {
				Object.keys(settings.variation).forEach(key => {
					formData.append(`variation[${key}]`, settings.variation[key]);
				});
			}

			// Make AJAX request
			fetch(tt_modal_checkout_params.ajax_url, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Update cart fragments if available
					if (data.fragments) {
						jQuery.each(data.fragments, function(key, value) {
							jQuery(key).replaceWith(value);
						});
					}
					resolve(data);
				} else {
					reject(data);
				}
			})
			.catch(error => {
				reject({
					success: false,
					message: 'AJAX request failed',
					error: error
				});
			});
		});
	},
	updateCart: function(guestType, guestIndex, insuranceOption) {
		return new Promise((resolve, reject) => {
			// Create form data
			const formData = new FormData();
			formData.append('action', 'tt_modal_checkout_update_cart');
			formData.append('security', tt_modal_checkout_params.nonce || '');
			formData.append('guest_type', guestType);
			formData.append('guest_index', guestIndex);
			formData.append('insurance_option', insuranceOption);
			// Make AJAX request
			fetch(tt_modal_checkout_params.ajax_url, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Update cart fragments if available
					if (data.fragments) {
						jQuery.each(data.fragments, function(key, value) {
							jQuery(key).replaceWith(value);
						});
					}
					resolve(data);
				} else {
					reject(data);
				}
			})
			.catch(error => {
				reject({
					success: false,
					message: 'AJAX request failed',
					error: error
				});
			});
		});
	},
	addRequest: function(action, data) {
		switch (action) {
		case 'tt_add_travel_protection':
			// Maybe Set loader.
			ttLoader.show('#quickLookModalCheckout .modal-content');
			this.addProtection(data.productId, data.orderId, data.origin)
			.then(response => {
				if ( response.data ) {
					jQuery( document.body ).trigger( 'added_to_cart', [ response.data?.fragments, response.data?.cart_hash ] );
					jQuery( document.body ).trigger( 'tt_modal_checkout_added_to_cart', [ response.data?.fragments, response.data?.cart_hash ] );
				}

				if ( response.data && response.data.insured_guests_html ) {
					jQuery('#tt-popup-insured-form').html(response.data.insured_guests_html);
				}

				// Total Price.
				if( response.data.total_price ) {
					jQuery( '#total-price' ).html( '<span class="amount"><span class="woocommerce-Price-currencySymbol">$</span>' + response.data.total_price + '</span>' );

					if ( parseFloat(response.data.total_price) <= 0 ) {
						jQuery( '#place_order' ).prop( 'disabled', true );
					} else {
						jQuery( '#place_order' ).prop( 'disabled', false );
					}
				}

				jQuery('.saved-cart').remove();

				jQuery("#currency_switcher").trigger("change");

				jQuery('body').trigger('update_checkout');

				ttLoader.hide( 0, '#quickLookModalCheckout .modal-content' );
			})
			.catch(error => {
				console.error('Error:', error);
				ttLoader.hide( 0, '#quickLookModalCheckout .modal-content' );
			});
			break;

		case 'tt_modal_checkout_update_cart':
			// Maybe Set loader.
			ttLoader.show('#quickLookModalCheckout .modal-content');
			this.updateCart(data.guestType, data.guestIndex, data.insuranceOption)
			.then(response => {

				if ( response.data && response.data.insured_guests_html ) {
					jQuery('#tt-popup-insured-form').html(response.data.insured_guests_html);
				}

				// Total Price.
				if( response.data.total_price ) {
					jQuery( '#total-price' ).html( '<span class="amount"><span class="woocommerce-Price-currencySymbol">$</span>' + response.data.total_price + '</span>' );

					if ( parseFloat(response.data.total_price) <= 0 ) {
						jQuery( '#pay_now' ).prop( 'disabled', true );
					} else {
						jQuery( '#pay_now' ).prop( 'disabled', false );
					}
				}

				jQuery("#currency_switcher").trigger("change");
				jQuery('body').trigger('update_checkout');

				ttLoader.hide( 0, '#quickLookModalCheckout .modal-content' );
			})
			.catch(error => {
				console.error('Error:', error);
				ttLoader.hide( 0, '#quickLookModalCheckout .modal-content' );
			});

			break;
		
		default:
			console.log('Unknown action:', action);
			break;
		}
	},
	onModalShow: function(ev) {
		var $thisbutton = jQuery( ev.relatedTarget );

		if ( $thisbutton.is( '.trek-add-to-cart' ) ) {
			if ( ! $thisbutton.attr( 'data-product_id' ) ) {
				return true;
			}

			$thisbutton.removeClass( 'added' );

			var data = {};

			// Fetch changes that are directly added by calling $thisbutton.data( key, value )
			jQuery.each( $thisbutton.data(), function( key, value ) {
				data[ key ] = value;
			});

			// Fetch data attributes in $thisbutton. Give preference to data-attributes because they can be directly modified by javascript
			// while `.data` are jquery specific memory stores.
			jQuery.each( $thisbutton[0].dataset, function( key, value ) {
				data[ key ] = value;
			});

			// Trigger event.
			jQuery( document.body ).trigger( 'adding_to_cart', [ $thisbutton, data ] );

			// Add to cart.
			TrekModalCheckout.addRequest( 'tt_add_travel_protection', {
				productId: data.product_id,
				orderId: data.order_id,
				origin: data.origin,
			});
		}
	},
	onModalShown: function(ev) {
		return true;
		// This function can be used to perform actions after the modal is fully shown.
	},
	onModalHidden: function(ev) {
		// This function can be used to perform actions after the modal is fully hidden.
		// For example, clearing the modal content or resetting form fields.
		jQuery('#pay_now').prop('disabled', false);
	},
	/**
	 * Parses a log line to extract whether the insurance change
	 * is for the 'primary' guest or a numbered 'guest' (e.g., guest 1, 2, etc.).
	 *
	 * @param {string} line - The log line to parse.
	 * @returns {object|null} - An object with guest type and index if applicable, or null if the line doesn't match.
	 */
	parseInsuranceChange(line) {
		// Regular expression to match the relevant pattern
		// Matches:
		// - 'guests' or 'primary' inside the first []
		// - An optional index number (e.g., [1]) only if it's 'guests'
		// - Followed by [is_travel_protection]
		const regex = /trek_guest_insurance\[(guests|primary)](?:\[(\d+)])?\[is_travel_protection]/;
		
		// Apply the regex to the input line
		const match = line.match(regex);
		
		if (match) {
			const type = match[1];       // Either 'guests' or 'primary'
			const index = match[2];      // Will be undefined if 'primary'
		
			if (type === 'primary') {
			// It's the primary guest, no index needed
			return { type: 'primary', index: null };
			} else {
			// It's a guest, return their index as a number
			return { type: 'guests', index: parseInt(index, 10) };
			}
		}
		
		// If the line doesn't match the expected format
		return null;
	},

	/**
	 * Declines travel protection for a specific product and order.
	 * This function sends an AJAX request to the server to handle the decline action.
	 *
	 * @param {*} productId 
	 * @param {*} orderId 
	 * @param {*} origin 
	 * @returns {Promise} - Returns a promise that resolves with the server response.
	 * @throws {Error} - Throws an error if the AJAX request fails or if the response indicates failure.
	 */
	declineProtection: function(productId, orderId, origin) {
		return new Promise((resolve, reject) => {
			// Create form data
			const formData = new FormData();
			formData.append('action', 'tt_decline_travel_protection');
			formData.append('security', tt_modal_checkout_params.nonce || '');
			formData.append('product_id', productId);
			formData.append('order_id', orderId);
			formData.append('request_origin', origin);

			// Make AJAX request
			fetch(tt_modal_checkout_params.ajax_url, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					resolve(data);
				} else {
					reject(data);
				}
			})
			.catch(error => {
				reject({
					success: false,
					message: 'AJAX request failed',
					error: error
				});
			});
		});
	}
};

jQuery( document ).ready( function( $ ) {
	const quickLookModalCheckout = document.querySelector('#quickLookModalCheckout');

	// Modal Not Found.
	if( null === quickLookModalCheckout ) {
		return;
	}

	/**
	 * Buttons Controller
	 */
	const buttonsController = {
		/**
		 * Disable a button and add loading state.
		 *
		 * @param {HTMLElement} button
		 */
		disableButton: function(button) {
			if (button) {
				button.disabled = true; // Disable the button
				button.classList.add('loading'); // Add loading class for visual feedback
			}
		},
		/**
		 * Enable a button and remove loading state.
		 *
		 * @param {HTMLElement} button
		 */
		enableButton: function(button) {
			if (button) {
				button.disabled = false; // Enable the button
				button.classList.remove('loading'); // Remove loading class
			}
		},
		/**
		 * Remove a button from the DOM.
		 *
		 * @param {HTMLElement} button
		 */
		removeButton: function(button) {
			if (button) {
				button.remove(); // Remove the button from the DOM
			}
		},
		/**
		 * Swap two buttons in the DOM.
		 *
		 * @param {HTMLElement} oldButton
		 * @param {HTMLElement} newButton
		 */
		swapButton: function(oldButton, newButton) {
			if (oldButton && newButton) {
				oldButton.parentNode.replaceChild(newButton, oldButton); // Replace old button with new button
			}
		}
	};

	// Clear the modal content before modal being visible.
	quickLookModalCheckout.addEventListener( 'show.bs.modal', TrekModalCheckout.onModalShow );
	quickLookModalCheckout.addEventListener( 'shown.bs.modal', TrekModalCheckout.onModalShown );
	quickLookModalCheckout.addEventListener( 'hidden.bs.modal', TrekModalCheckout.onModalHidden );

	jQuery( document.body ).on( 'tt_modal_checkout_added_to_cart', function(ev, ...args) {
		const [fragments, cartHash] = args;
	});

	jQuery(document).on('change', 'input[type=radio][name^="trek_guest_insurance"]', function() {
		const name = $(this).attr('name');
		const value = $(this).val();
		const data = TrekModalCheckout.parseInsuranceChange(name);
		if (!data) {
			console.error('Invalid insurance change data:', name);
			return;
		}
		const guestType = data.type;
		const guestIndex = data.index;
		TrekModalCheckout.addRequest( 'tt_modal_checkout_update_cart', {
			guestType: guestType,
			guestIndex: guestIndex,
			insuranceOption: value,
		});
		console.log('Insurance option changed:', name, value);
	});

	jQuery('.pre-billing-checkbox').on('change', function (e) {
		var action = 'tt_pre_billing_address';
		jQuery.ajax({
			type: 'POST',
			url: trek_JS_obj.ajaxURL,
			data: "action=" + action + "&is_pre_billing_address=" + e.target.checked + "&security=" + tt_modal_checkout_params.nonce,
			dataType: 'json',
			beforeSend : function(){
				ttLoader.show('#quickLookModalCheckout .modal-content');
			},
			success: function (response) {
				if (response.success) {
					jQuery('.billing-address-section').html(response.data.billing_address);
					// Clean up the address text
					var $addressDiv = jQuery('.checkout-payment__pre-address');

					$addressDiv.find('p.mb-0').each(function() {
						var text = jQuery(this).text();

						// Remove unnecessary commas
						text = text.replace(/,\s*,/, ',').replace(/\s*,\s*$/, '').replace(/,\s*$/, '');
						
						// Set the cleaned text back
						jQuery(this).text(text);
					});
				} else {
					console.log('Something went wrong fetching pre-billing address:', response);
				}
			},
			error: function (error) {
				console.log('Error fetching pre-billing address:', error);
			},
			complete: function(){
				ttLoader.hide( 0, '#quickLookModalCheckout .modal-content' );
			}
		});
	});

	const tpDeclineWarningModal = document.getElementById('tpDeclineWarningModal');

	jQuery(document).on('click', '.confirm-tp-decline-btn', function(e) {
		e.preventDefault();

		const btn       = this;
		const productId = btn.getAttribute('data-product_id');
		const orderId   = btn.getAttribute('data-order_id');
		const origin    = btn.getAttribute('data-origin');
		const page      = btn.getAttribute('data-page');

		buttonsController.disableButton(btn);

		const declineButton       = document.querySelector('.trek-decline-travel-protection[data-order_id="' + orderId + '"]');
		const addProtectionButton = document.querySelector('.add-travel-protection-btn[data-order_id="' + orderId + '"]');

		buttonsController.disableButton(declineButton);
		buttonsController.disableButton(addProtectionButton);

		TrekModalCheckout.declineProtection(productId, orderId, origin)
			.then(response => {
				if ( response.success ) {
					// Handle successful response
					jQuery( document.body ).trigger( 'tt_modal_checkout_declined_protection', [ response ] );
					jQuery('#tpDeclineWarningModal').modal('hide');
					// No status badge, just remove the decline button.
					buttonsController.removeButton(declineButton);

					// Re-enable the add protection button.
					buttonsController.enableButton(addProtectionButton);

				} else {
					// Handle error response
					console.log('Error declining travel protection:', response);
					buttonsController.enableButton(btn);
					buttonsController.enableButton(declineButton);
					buttonsController.enableButton(addProtectionButton);
				}
			})
			.catch(error => {
				buttonsController.enableButton(btn);
				buttonsController.enableButton(declineButton);
				buttonsController.enableButton(addProtectionButton);
				console.error('Error declining travel protection:', error);
			});
	});

	tpDeclineWarningModal && tpDeclineWarningModal.addEventListener('show.bs.modal', function (event) {
		// Button that triggered the modal
		const button = event.relatedTarget;
		// Extract info from data-* attributes
		const productId = button.getAttribute('data-product_id');
		const orderId   = button.getAttribute('data-order_id');
		const origin    = button.getAttribute('data-origin');
		const page      = button.getAttribute('data-page');

		// Decline Travel Protection button
		dataLayer.push({
			'event': 'my_button_click',
			'type': 'decline_travel_protection',
			'order_id': orderId,
			'page': page,
		});

		const proceedButton = tpDeclineWarningModal.querySelector('.confirm-tp-decline-btn');

		// Pass the data attributes to the proceed button
		proceedButton.setAttribute('data-product_id', productId);
		proceedButton.setAttribute('data-order_id', orderId);
		proceedButton.setAttribute('data-origin', origin);
		proceedButton.setAttribute('data-page', page);
	});

	tpDeclineWarningModal && tpDeclineWarningModal.addEventListener('hidden.bs.modal', function (event) {
		// Reset the proceed button data attributes
		const proceedButton = tpDeclineWarningModal.querySelector('.confirm-tp-decline-btn');
		proceedButton.removeAttribute('data-product_id');
		proceedButton.removeAttribute('data-order_id');
		proceedButton.removeAttribute('data-origin');
		proceedButton.removeAttribute('data-page');
		buttonsController.enableButton(proceedButton);
	});
});
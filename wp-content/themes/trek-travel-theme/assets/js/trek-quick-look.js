/**
 * Quick Look Functionality.
 * Show more information about the Trip and bookable dates, dynamically in a modal.
 *
 * Load this file on the archive and search pages on the front-end.
 *
 * @gloabl trek_quick_look_assets custom object
 * @global ttLoader defined in the developer.js custom object with show and hide methods.
 */
jQuery( document ).ready( function( $ ) {
	const quickLookModal = document.querySelector('#quickLookModal');

	// Modal Not Found.
	if( null === quickLookModal ) {
		return;
	}

	// Clear the modal content before modal being visible.
	quickLookModal.addEventListener('show.bs.modal', function (ev) {
		// Clear modal body.
		jQuery( '#quickLookModal .modal-body' ).empty();
		// Clear Start Price.
		jQuery( '#quickLookModal .modal-footer .starting-from-price' ).empty();
		// Reset View Trip Link.
		jQuery( '#quickLookModal .modal-body .view-trip-link' ).attr( 'href', '#' );
		
		if(jQuery(window).width() < 768) {
			var element = jQuery('header')[0]; // Get the DOM element from jQuery object
			var rect = element.getBoundingClientRect(); // Get the bounding rectangle
			var spaceFromTopOfScreen = rect.top; // Distance from the top of the viewport
			
			var modalTop = $('header').height() + spaceFromTopOfScreen; // Calculate modal top position

			$('#quickLookModal .modal-dialog').css('top', modalTop + 'px');
			$('#quickLookModal .modal-dialog').css('height', 'calc(100% - ' + modalTop + 'px)');
		}
	});

	quickLookModal.addEventListener('shown.bs.modal', function (ev) {
		// Button that triggered the modal
		const button = ev.relatedTarget;
		// Extract info from data-bs-* attributes
		const productId = button.getAttribute('data-bs-product-id')
		const allData   = jQuery(button).data();
		// If necessary, you could initiate an AJAX request here
		const action = 'tt_quick_look_action';
		jQuery.ajax({
			type: 'POST',
			url: trek_quick_look_assets.ajaxurl,
			data: "action=" + action + "&product_id=" + productId + "&trip_data=" + JSON.stringify( allData ) + "&nonce=" + trek_quick_look_assets.nonce,
			dataType: 'json',
			beforeSend : function() {
				// Maybe Set loader.
				ttLoader.show('#quickLookModal .modal-content');
			},
			success: function ( response ) {
				if( response.success ) {
					// Dates and Pricing.
					if( response.data.tt_quick_look_html ) {
						jQuery( '#quickLookModal .modal-body' ).html( response.data.tt_quick_look_html );
					}
					jQuery('body').removeAttr('style');

					// Set Start Price.
					if( response.data.start_price ) {
						jQuery( '#quickLookModal .modal-footer .starting-from-price' ).html( '<span class="amount"><span class="woocommerce-Price-currencySymbol">$</span>' + response.data.start_price + '</span>' );
					}

					// Set View Trip Link.
					if( response.data.view_trip_link ) {
						jQuery( '#quickLookModal .modal-body .view-trip-link' ).attr( 'href', response.data.view_trip_link );


						jQuery( '#quickLookModal .modal-body .view-details' ).each(function() {
							
							var currentHref = $(this).attr('href');
	
							var updatedLink = response.data.view_trip_link + currentHref;
	
							$(this).attr( 'href', updatedLink );
						})

						jQuery('.share-link').click(function (ev) {
							ev.preventDefault()
							jQuery('.mobile-link-copied').addClass("show")
							navigator.clipboard.writeText(response.data.view_trip_link);

							$(".quick-look-modal").focus();

							setTimeout(() => {
								jQuery('.mobile-link-copied').removeClass("show")
							}, 2000);
						})
					}
					
				} else {
					// Validation failure or not found.
					jQuery( '#quickLookModal .modal-body' ).html( `<div class="alert alert-danger text-danger" role="alert"><i class="bi bi-info-circle me-3"></i> ${ response.data.message ? response.data.message : 'Something went wrong! Please try again later.' }</div>` );
					console.log( 'TT_QL_ERROR', response );
				}

				ttLoader.hide( 0, '#quickLookModal .modal-content' );
			},
			error: function( err ) {
				console.log(err);
				jQuery( '#quickLookModal .modal-body' ).html( '<div class="alert alert-danger text-danger" role="alert"><i class="bi bi-info-circle me-3"></i> Something went wrong! Please try again later.</div>' );
				ttLoader.hide( 0, '#quickLookModal .modal-content' );
			},
			complete: function(){
				jQuery("#currency_switcher").trigger("change");
			}
		});
	});
})
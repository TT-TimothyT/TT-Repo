/**
 * Fix Quick Edit to can check the selected option for Trip Status.
 *
 * @link https://wordpress.stackexchange.com/questions/139269/wordpress-taxonomy-radio-buttons
 *
 * @gloabl tt_inline_edit_assets custom object
 * @global inlineEditPost wp object
 */
jQuery(document).ready(function($) {
	let taxonomies = {};
	if( ! tt_inline_edit_assets || tt_inline_edit_assets.allowed_taxonomies.length <= 0 ) {
		return;
	}
	tt_inline_edit_assets.allowed_taxonomies.forEach( allowed_taxonomy => {
		taxonomies[allowed_taxonomy] = '';
	});
	let post_id    = null;
	$(document).on('click', '.editinline', function() {
		post_id = inlineEditPost.getId(this);
		$.ajax({
			url: tt_inline_edit_assets.ajaxurl,
			data: {
				action: 'tt_trips_status_inline_edit_radio_checked',
				'tt_edit_nonce': tt_inline_edit_assets.nonce,
				'tt_edit_taxonomies': taxonomies,
				'tt_edit_post_id': post_id
			},
			type: 'POST',
			dataType: 'json',
			success: function (response) {
				if( response.success ) {
					if( response.data.result ) {
						tt_inline_edit_assets.allowed_taxonomies.forEach( allowed_taxonomy => {
							if( response.data.result[allowed_taxonomy].length > 0 ) {
								let taxonomy = allowed_taxonomy;
								let term_id  = response.data.result[allowed_taxonomy][0];
								let li_ele_id = 'in-' + taxonomy + '-' + term_id;
								$( '#edit-'+ post_id +' input[id="'+li_ele_id+'"]' ).attr( 'checked', 'checked' );
							}
						});
					} else {
						// No result.
						console.log( 'TT_INLINE_EDIT_ERROR', response );
					}
				} else {
					// Validation failure or not found.
					console.log( 'TT_INLINE_EDIT_ERROR', response );
				}
			},
			error: function( err ) {
				console.log( 'TT_INLINE_EDIT_ERROR', err );
			}
		});
	});
});

/**
 *
 */
jQuery( document ).ready( function ( jQuery ) {
	
	jQuery( '.gfp_gfutil_toggle-all-fields-required_link' ).on( 'click', gfp_gfutil_toggle_all_fields_required );

});


function gfp_gfutil_toggle_all_fields_required( event ) {

	event.preventDefault();

	if ( false === form['fields'][0]['isRequired'] ) {

		var required = '<span class="gfield_required">' + gform_form_strings.requiredIndicator + '</span>';

		jQuery.each( form['fields'], gfp_gfutil_set_field_required );

		jQuery( '.gfield_label' ).append( required );

		alert( gfp_gfutil_toggle_all_fields_required_vars.set );

	}
	else {

		jQuery.each( form['fields'], gfp_gfutil_unset_field_required );

		jQuery( ' .gfield_required' ).remove();

		alert( gfp_gfutil_toggle_all_fields_required_vars.unset );

	}

}

function gfp_gfutil_set_field_required( index, value ) {

	value['isRequired'] = true;

}

function gfp_gfutil_unset_field_required( index, value ) {

	value['isRequired'] = false;

}
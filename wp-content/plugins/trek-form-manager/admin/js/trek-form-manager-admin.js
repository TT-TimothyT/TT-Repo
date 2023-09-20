jQuery(document).ready(function($){

	// form select box
	var $gfSelectForm = $('#tt_form_add_form_id');

	// hidden field that may contain the mapping id
	var $gfFormMap    = $('#tt_form_map_option_id');

    /**
     * used to pre-load mapped forms (edit)
     *
     * we don't care if a formID is passed, this will override it
     */
    if ( $gfFormMap && '' !== $gfFormMap.val() ) {
        var MapOptionId = $gfFormMap.val();
		if ( parseInt( MapOptionId ) > 0 ) {
            buildTableForMap( parseInt(MapOptionId) );
		}
	}

	/**
	 * used to load a form's fields on page load (pre-select)
	 * 
	 * we don't want to do this if we have a map to load
	 */
	if ( $gfSelectForm && ( ! $gfFormMap || '' === $gfFormMap.val() ) ) {
		var selectedFormId = $("option:selected", this).val();
		if ( parseInt( selectedFormId ) > 0 ) {
			buildTableForForm( parseInt(selectedFormId) );
		}
	}

	/**
	 * triggered when a form is selected
	 */
	$gfSelectForm.on('change', function(){
		$('#tt_form_add_form_ns_id').val();
		var selectedFormId = $("option:selected", this).val();
		if ( parseInt(selectedFormId) > 0 ) {
			buildTableForForm( parseInt(selectedFormId) );
		}
	});

	/**
	 * triggered when the "add extra field" link is clicked to add another row
	 */
	$(document).on('click', '#tt_form_add_extra', function(e){
		e.preventDefault();

		var tableBodySelector = '#form-field-mapping-list .form-table tbody';
		var $formTableBody    = $( tableBodySelector );
		var id                = $( tableBodySelector + ' tr[id^="tt_ext"').length + 1;
		var out               = buildAddFieldRow( '', '', id );

		$formTableBody.append( out );

		return false;
	});

});

/**
 * Builds an "extra field" row
 *
 * @param fieldNameLabel
 * @param fieldNameData
 * @param rowId
 * @returns {string}
 */
function buildAddFieldRow( fieldNameLabel, fieldNameData, rowId ) {
	return '<tr scope="row" id="tt_ext_' + rowId + '"><th>&nbsp;</th><td id="tt_extra_lbl_' + rowId + '"><input type="text" name="form_mapping[ext_r' + rowId + '][lbl]" value="' + fieldNameLabel + '" /></td><td id="tt_extra_dta_' + rowId + '"><input type="text" name="form_mapping[ext_r' + rowId + '][data]" value="' + fieldNameData + '" /></td><td>&nbsp;</td></tr>';
}

/**
 * Build a mappable field row (gravity form field)
 *
 * @param rowLabel
 * @param fieldId
 * @param fieldLabel
 * @param inputValue
 * @returns {string}
 */
function buildMapFieldRow(rowLabel, fieldId, fieldLabel, inputValue) {
	var out = '';

	// make sure we have an input value and that it's clean
	if ( ! inputValue ) {
		inputValue = '';
	}

	out += '<tr><th scope="row">' + rowLabel + '</th>';
	out += '<td>' + fieldId + ' : ' + fieldLabel + '<input type="hidden" name="form_mapping[gff_' + fieldId + '][gf_lbl]" value="' + fieldLabel + '"></td>';
	out += '<td><input type="text" name="form_mapping[gff_' + fieldId + '][ns_name]" value="' + inputValue + '" /></td><td>&nbsp;</td></tr>';

	return out;
}

/**
 * build the form mapping table of fields based on form ID
 *
 * this will be populated if a map exists for the specified form
 *
 * @param loadFormId
 */
function buildTableForForm( loadFormId ) {
	var $ = jQuery;
	$.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'tt_form_add_fetch_fields',
			formid: loadFormId
		},
		success: function(msg){

			if ( undefined === msg || ! msg.hasOwnProperty( 'status' ) || 'success' !== msg.status ) {
				return false;
			}

			$("#form-field-mapping-list").html( doBuildTable( msg ) );

			if ( msg.netsuite_form_id ) {
				$('#tt_form_add_form_ns_id').val( msg.netsuite_form_id );
			}
		}
	});
}

/**
 * build a populated table of fields for the specified map
 *
 * @param mapOptionId
 */
function buildTableForMap( mapOptionId ) {
	var $ = jQuery;
	$.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'tt_form_add_fetch_mapped_fields',
			mapid: mapOptionId
		}
	}).success(function(msg){

		if ( undefined === msg || ! msg.hasOwnProperty( 'status' ) || 'success' !== msg.status ) {
			return false;
		}

		$("#form-field-mapping-list").html( doBuildTable( msg ) );

		$('#tt_form_add_form_id').val( msg.gravity_form_id );
		$('#tt_form_add_form_ns_id').val( msg.netsuite_form_id );

	});
}

/**
 * do the work of actually building the table to be displayed
 *
 * @param formData
 * @returns {string}
 */
function doBuildTable( formData ) {
	var $ = jQuery;

	var out = '';

	if ( formData.hasOwnProperty('fields') ) {
		var fieldsPrefix = 'gf_field_id_';

		out += '<table class="form-table"><thead><tr><th></th><th>GF Form Field</th><th>NetSuite Field Name</th></tr></thead><tbody>';

		var rowLbl = 'Form Field Mappings';
		for ( var $fld in formData.fields ) {

			if ( $fld.length <= fieldsPrefix.length || $fld.substr(0, fieldsPrefix.length ) !== fieldsPrefix ) {
				continue;
			}

			out += buildMapFieldRow( rowLbl, formData.fields[$fld].gf_field_id, formData.fields[$fld].gf_field_label, formData.fields[$fld].ns_field_name );

			rowLbl = '';
		}

		out += '<tr><th scope="row">Additional Fields to send</th><td><a href="#" id="tt_form_add_extra">Add Extra Field</a></td><td colspan="2">"h" is a special field and is used as part of the URL for form submission and is usually required.</td></tr>';
		out += '<tr><th scope="row">&nbsp;</th><td>Field Name</td><td>Value</td><td>&nbsp;</td>';

		if (formData.hasOwnProperty('extrafields') && $.isArray(formData.extrafields) && formData.extrafields.length > 0) {
			$(formData.extrafields).each(function(){
				out += buildAddFieldRow( this.fieldName, this.fieldData, this.id )
			});
		}

		out += '</tbody></table>';
	}

	return out;
}
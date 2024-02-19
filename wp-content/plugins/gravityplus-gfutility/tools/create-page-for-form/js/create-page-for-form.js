/**
 *
 */
jQuery(document).ready(function (jQuery) {

    jQuery('.gfp_gfutil_create-page-button_link').on('click', gfp_gfutil_toggle_create_page);

    jQuery('.gfp_gfutil_create-page-for-form_link').on('click',

        function () {
            resetNewUtilityPageModal();

            loadNewUtilityPageModal();
        }
    );

    jQuery(document).on('submit', '#gfp_gfutil_create_page_modal_form',

        function (e) {
            handleNewUtilityPageSubmission(e);
        }
    );
});


function gfp_gfutil_toggle_create_page(e) {

    e.preventDefault();

    resetNewUtilityPageModal();

    loadNewUtilityPageModal();
}


function loadNewUtilityPageModal() {

    jQuery('body').addClass('gfpgf-utility')

    resetNewUtilityPageModal();

    tb_show('Create a New Page', '#TB_inline?width=500&amp;inlineId=gfp_gfutil_create_page_modal');

    jQuery('#new_page_title').focus();

    return false;
}

function handleNewUtilityPageSubmission(e) {

    e.preventDefault();

    saveNewPage();

}

async function saveNewPage() {

    var pageTitle = jQuery('#new_page_title').val();

    if (pageTitle === '' || pageTitle === 'undefined') {

        jQuery('#gfp_gfutility-error').html('<p>Please add a title to your page.</p>');

        addUtilityInputErrorIcon('#new_page_title');
        // spinner.destroy();
        return false;
    }

    var createButton = jQuery('#save_new_page_utility');

    jQuery('.setting-row').hide();

    jQuery('#save_new_page_utility').hide();

    var spinner = new gfp_gf_util_AjaxSpinner(createButton, gfp_gfutil_create_page_for_form_strings.spinner, '', 'Creating Page...');

    jQuery('#gfp_gfutility-error').html('');


    var page_title = jQuery('#new_page_title').val();

    var content = '';

    if (gfp_gfutil_create_page_for_form_strings.editor_support === 'yes') {

        content = '<!-- wp:gravityforms/form {"formId":"' + gfp_gfutil_create_page_for_form_strings.form_id + '"} /-->';

    } else {

        content = '[gravityform id="' + gfp_gfutil_create_page_for_form_strings.form_id + '" title="true" ]';
    }

    return await postData(page_title, content, spinner);

}

function postData(page_title, content, spinner) {

    var result = jQuery.post(
        {
            url: gfp_gfutil_create_page_for_form_strings.root + 'wp/v2/pages',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', gfp_gfutil_create_page_for_form_strings.nonce);
            },
            data: {
                'title': page_title,
                'status': 'publish',
                'content': content
            }

        }
        ).then(function (response) {

        jQuery('.gfutility-ajaxspinner').html('Page created successfully. <a target="_blank" href="' + response.guid.rendered + '">View page</a>');

    }).fail(function (xhr, status, error) {

        jQuery('#gfp_gfutility-error').html('<p>Something went wrong with saving</p>');

        jQuery('.setting-row').show();

        jQuery('#save_new_page_utility').show();

        spinner.destroy();

        return false;
    });

    return result;
}


function resetNewUtilityPageModal() {

    jQuery('#new_page_title').val('');

    jQuery('#gfp_gfutility-error').html('');

    jQuery('.setting-row').show();

    jQuery('.gfutility-ajaxspinner').remove();

    jQuery('#save_new_page_utility').show();

    removeUtilityInputErrorIcons('.gfp_gfutil_create_page_modal_container');

}

function addUtilityInputErrorIcon(elem) {

    var elem = jQuery(elem);

    elem.before('<span class="gf_input_error_icon"></span>');

}

function removeUtilityInputErrorIcons(elem) {

    elem = jQuery(elem);

    elem.find('span.gf_input_error_icon').remove();
}

function gfp_gf_util_AjaxSpinner(elem, imageSrc, inlineStyles, textLabel) {

    var imageSrc = typeof imageSrc == 'undefined' ? '/images/ajax-loader.gif' : imageSrc;

    var inlineStyles = typeof inlineStyles != 'undefined' ? inlineStyles : '';

    this.elem = elem;

    this.image = '<div class="gfutility-ajaxspinner" style="text-align:center;"><img class="gfspinner" src="' + imageSrc + '" style="' + inlineStyles + '" /> &nbsp; <label>' + textLabel + '</label></div>';

    this.init = function () {
        this.spinner = jQuery(this.image);
        jQuery(this.elem).after(this.spinner);
        return this;
    }

    this.destroy = function () {
        jQuery(this.spinner).remove();
    }

    return this.init();
}

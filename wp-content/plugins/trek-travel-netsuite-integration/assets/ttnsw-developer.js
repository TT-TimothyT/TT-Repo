jQuery(document).on('click', 'form[name="tt-logs"] input[submit-type="delete"]',function(){
    let text = "Are you sure you want to delete the logs? ?";
    if (confirm(text) == true) {
        return true;
    } else {
        return false;
    }
})

jQuery(document).ready(function () {
    jQuery('.expand-single').on('click', function(){
        jQuery(this).closest('.expandable-cell').toggleClass('expanded')
    })
    jQuery('.expand-all').on('click', function(){
        if( jQuery(this).hasClass('all-expanded') ){
            jQuery(this).removeClass('all-expanded')
            jQuery('td.expandable-cell').removeClass('expanded')
        } else {
            jQuery(this).addClass('all-expanded')
            jQuery('td.expandable-cell').addClass('expanded')
        }
    })
})
jQuery(document).on('click', 'form[name="tt-logs"] input[submit-type="delete"]',function(){
    let text = "Are you sure you want to delete the logs? ?";
    if (confirm(text) == true) {
        return true;
    } else {
        return false;
    }
})
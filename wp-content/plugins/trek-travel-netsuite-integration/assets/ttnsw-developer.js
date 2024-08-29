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
    jQuery('.dx-show-hidden').on('click', function() {
        jQuery("#dx-repair-tools").toggle();
    })
    // Manual Sync filters
    jQuery('.tt-wp-manual-sync select[name="type"]').change(function () {
        console.log(this, jQuery(this).val())
        switch (jQuery(this).val()) {
            case 'trip':
            case 'trip-details':
            case 'bikes':
            case 'hotels':
            case 'addons':
            case 'product-sync':
                // Required filter type.
                jQuery('select[name="filter_type"], select[name="time_range"], select[name="trip_year"], select[name="itinerary_code"], select[name="itinerary_id"]').prop('required', true).val('').hide();
                jQuery('select[name="filter_type"]').show(); // This select will show or hide any of time range and itinerary filters.
                break;
            case 'product-sync-all':
            case 'custom-items':
                // Not required filter type.
                jQuery('select[name="filter_type"], select[name="time_range"], select[name="trip_year"], select[name="itinerary_code"], select[name="itinerary_id"]').prop('required', false).val('').hide();
                break;
            case 'ns-wc-booking':
                // Allow only modified after filter type.
                jQuery('select[name="filter_type"], select[name="trip_year"], select[name="itinerary_code"], select[name="itinerary_id"]').prop('required', false).val('').hide();
                jQuery('select[name="time_range"]').prop('required', true).val('').show();
                break;
            default:
                // Empty string.
                jQuery('select[name="filter_type"], select[name="time_range"], select[name="trip_year"], select[name="itinerary_code"], select[name="itinerary_id"]').prop('required', true).val('').hide();
                break;
        }
    });
    jQuery('.tt-wp-manual-sync select[name="filter_type"]').change(function () {
        console.log(this, jQuery(this).val())
        switch (jQuery(this).val()) {
            case 'modifiedAfter':
                // For the modifiedAfter filter type the required value is time_range.
                jQuery('select[name="trip_year"], select[name="itinerary_code"], select[name="itinerary_id"]').prop('required', false).val('').hide();
                jQuery('select[name="time_range"]').prop('required', true).show();
                break;
            case 'tripYear':
                // For the tripYear filter type the required value is trip_year.
                jQuery('select[name="time_range"], select[name="itinerary_code"], select[name="itinerary_id"]').prop('required', false).val('').hide();
                jQuery('select[name="trip_year"]').prop('required', true).show();
                break;
            case 'itineraryCode':
                // For the itineraryCode filter type the required value is itinerary_code.
                jQuery('select[name="time_range"], select[name="trip_year"], select[name="itinerary_id"]').prop('required', false).val('').hide();
                jQuery('select[name="itinerary_code"]').prop('required', true).show();
                break;
            case 'itineraryId':
                // For the itineraryId filter type the required value is itinerary_id.
                jQuery('select[name="time_range"], select[name="trip_year"], select[name="itinerary_code"]').prop('required', false).val('').hide();
                jQuery('select[name="itinerary_id"]').prop('required', true).show();
                break;
            default:
                // Empty string. Set all required, until select any type of filter.
                jQuery('select[name="time_range"], select[name="trip_year"], select[name="itinerary_code"], select[name="itinerary_id"]').prop('required', true).val('').hide();
                break;
        }
    });
})
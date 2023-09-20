jQuery('#mobile-header-calendar').on('click',function (e) {
    e.preventDefault();
    jQuery('#mobileCalendarModal').modal('toggle');
  })
  jQuery('#trip-finder-daterange i.toggle').on('click',function (e) {
    e.preventDefault();
    jQuery("input#home-daterange").trigger("click")
  })

  jQuery('#filterModal').on('show.bs.modal', function (event) {
    jQuery('#search-daterange').trigger('click')
  })

  jQuery('#home-daterange').on('click', function (ev) {
    if (isMobileDevice()) {
      jQuery('#homeHeaderModal').modal('toggle');    
    }
  })
  
  jQuery('#homeHeaderModal').on('show.bs.modal', function (event) {
    jQuery('#dateRangePickerHeader').trigger('click')
  })
  jQuery('#mobileCalendarModal').on('show.bs.modal', function (event) {
    jQuery('#dateRangePickerMobileCalendar').daterangepicker({
      "linkedCalendars": false,
      "singleDatePicker": false,
      "autoApply": true,
      "autoUpdateInput": false,
      // "opens": "center",
      // "drops": "up",
      "parentEl": "#mobileCalendarTrigger",
      "locale": {
        "format": "MMMM D",
        "separator": " - ",
        "applyLabel": false,
        "cancelLabel": "Clear Dates"
      }
  });
    jQuery('#dateRangePickerMobileCalendar').trigger('click')
  })
  
  jQuery('#dateRangePickerMobileCalendar').on('apply.daterangepicker', function (ev, picker) {
    startTime = picker.startDate._d.valueOf();
    endTime = picker.endDate._d.valueOf();
    startTime = startTime / 1000;
    endTime = endTime / 1000;
    jQuery('#start_time').val(Math.round(startTime));
    jQuery('#end_time').val(Math.round(endTime));
  })

  jQuery('#dateRangePickerMobileCalendar').on('cancel.daterangepicker', function (ev, picker) {
    jQuery('.mobile-calendar-form #start_time').val('');
    jQuery('.mobile-calendar-form #end_time').val('');
    jQuery("#mobileCalendarTrigger tbody tr td.active").removeClass("active");
    jQuery("#mobileCalendarTrigger tbody tr td.today").addClass("active");
  })
  
  jQuery('#dateRangePickerHeader').on('apply.daterangepicker', function (ev, picker) {
    jQuery("#home-daterange").val(picker.startDate.format('MMMM D') + ' - ' + picker.endDate.format('MMMM D'));
    startTime = picker.startDate._d.valueOf();
    endTime = picker.endDate._d.valueOf();
    startTime = startTime / 1000;
    endTime = endTime / 1000;
    jQuery('.home-trip-finder-form #start_time').val(Math.round(startTime));
    jQuery('.home-trip-finder-form #end_time').val(Math.round(endTime));
    
    jQuery('#homeHeaderModal').modal('toggle');
    // datalayer event
    dataLayer.push({
      'event': 'trip_finder',
      'finder_step': 'select a date' //find a trip, select a date, select a destination, show trips
    });
  });
  jQuery('#dateRangePickerHeader').on('cancel.daterangepicker', function (ev, picker) {
    jQuery("#home-daterange").val('');
    jQuery('.home-trip-finder-form #start_time').val('');
    jQuery('.home-trip-finder-form #end_time').val('');
    jQuery("#rangeDateVal").remove();
  });
  
  jQuery('#dateRangePickerHeader').on('show.daterangepicker', function (ev, picker) {
    jQuery('#trip-finder-daterange .bi-chevron-down').css("display", "none")
    jQuery('#trip-finder-daterange .bi-chevron-up').css("display", "block")
  })
  
  jQuery('#dateRangePickerHeader').on('hide.daterangepicker', function (ev, picker) {
    jQuery('#trip-finder-daterange .bi-chevron-down').css("display", "block")
    jQuery('#trip-finder-daterange .bi-chevron-up').css("display", "none")
  })
  
  jQuery('.mobile-calendar-apply').on("click", function (e) {
    jQuery('form.mobile-calendar-form').submit()
  })

  jQuery(document).ready(function () {
    if (jQuery('#home-daterange').length > 0) {
        // For Homepage Trip finder
        if (window.matchMedia('(max-width: 767px)').matches) {
          jQuery('#dateRangePickerHeader').daterangepicker({
            "linkedCalendars": false,
            "singleDatePicker": false,
            "autoApply": true,
            "autoUpdateInput": false,
            // "opens": "center",
            // "drops": "up",
            "parentEl": "#headerCTrigger",
            "locale": {
              "format": "MMMM D",
              "separator": " - ",
              "applyLabel": false,
              "cancelLabel": "Clear Dates"
            }
        });
      } else {
          jQuery('#home-daterange').daterangepicker({
            'alwaysShowCalendars': true,
            "singleDatePicker": false,
            "autoApply": true,
            "autoUpdateInput": false,
            "locale": {
              "format": "MMMM D",
              "separator": " - ",
              "applyLabel": false,
              "cancelLabel": "Clear Dates"
            }
        });
      }
        jQuery('#home-daterange').on('apply.daterangepicker', function (ev, picker) {
          jQuery(this).val(picker.startDate.format('MMMM D') + ' - ' + picker.endDate.format('MMMM D'));
          startTime = picker.startDate._d.valueOf();
          endTime = picker.endDate._d.valueOf();
          startTime = startTime / 1000;
          endTime = endTime / 1000;
          jQuery('.home-trip-finder-form #start_time').val(Math.round(startTime));
          jQuery('.home-trip-finder-form #end_time').val(Math.round(endTime));
          if (jQuery("#rangeDateVal").parent().length) {
            jQuery("#rangeDateVal").html(picker.startDate.format('MMMM D') + " - " + picker.endDate.format('MMMM D'))
          }
          else {
            jQuery(".range_inputs").prepend("<span id='rangeDateVal'>" + picker.startDate.format('MMMM D') + " - " + picker.endDate.format('MMMM D') + "</span>")
          }
          // datalayer event
          dataLayer.push({
            'event': 'trip_finder',
            'finder_step': 'select a date' //find a trip, select a date, select a destination, show trips
          });
        });
        jQuery('#home-daterange').on('cancel.daterangepicker', function (ev, picker) {
          jQuery(this).val('');
          jQuery('.home-trip-finder-form #start_time').val('');
          jQuery('.home-trip-finder-form #end_time').val('');
          jQuery("#rangeDateVal").remove();
        });
    
        jQuery('#home-daterange').on('show.daterangepicker', function (ev, picker) {
          jQuery('#trip-finder-daterange .bi-chevron-down').css("display", "none")
          jQuery('#trip-finder-daterange .bi-chevron-up').css("display", "block")
        })
    
        jQuery('#home-daterange').on('hide.daterangepicker', function (ev, picker) {
          jQuery('#trip-finder-daterange .bi-chevron-down').css("display", "block")
          jQuery('#trip-finder-daterange .bi-chevron-up').css("display", "none")
        })
      }
  })

  function convertEpochToDate(epoch) {
    var convertedDate = ''
    if (epoch) {      
      epochTime = epoch
      epochTime *= 1000
      epochTime = epochTime + (new Date().getTimezoneOffset() * -1)
      convertedDate = new Date(epochTime)
    }
    return convertedDate
  }
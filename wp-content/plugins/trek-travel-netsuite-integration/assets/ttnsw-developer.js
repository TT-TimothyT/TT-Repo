jQuery(document).on('click', 'form[name="tt-logs"] input[submit-type="delete"]',function(){
    let text = "Are you sure you want to delete the logs? ?";
    if (confirm(text) == true) {
        return true;
    } else {
        return false;
    }
})

jQuery(document).ready(function ($) {
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

    // Handle column group visibility toggles
    $('.column-groups input[type="checkbox"]').on('change', function() {
        var group   = $(this).attr('name').replace('bookings_group_', '');
        var visible = $(this).is(':checked');
        var form    = $(this).closest('form');
        var submit  = form.find('input[type="submit"]');

        function onError( response ) {
            console.log( response );
            // Re-enable the submit button
            submit.prop('disabled', false);
            submit.removeClass('disabled');
            submit.val('Apply');
            submit.css('cursor', 'pointer');
            // Re-enable all checkboxes
            form.find('.column-groups input[type="checkbox"]').prop('disabled', false);
        }

        // Save via AJAX
        $.ajax({
            type: 'POST',
            url: ttnsw_JS_obj.ajaxurl,
            data: {
                action: 'save_column_group_visibility',
                group: group,
                visible: visible,
                _wpnonce: ttnsw_JS_obj.nonce
            },
            dataType: 'json',
            beforeSend: function () {
                // Disable the submit button
                submit.prop('disabled', true);
                submit.addClass('disabled');
                submit.val('Saving...');
                submit.css('cursor', 'loading');
                // Disable all checkboxes
                form.find('.column-groups input[type="checkbox"]').prop('disabled', true);

            },
            success: function(response) {
                if( response.success ) {
                    // Refresh the page to show/hide columns
                    window.location.reload();
                } else {
                    onError( response );
                }
            },
            error: function( error ) {
                onError( error );
            }
        });
    });

    // Modal functionality
    var modal = $('#content-modal');
    var modalTitle = $('.ttnsw-modal-title');
    var modalBody = $('.ttnsw-modal-body');
    
    // Helper function to recursively decode and format JSON values
    function recursivelyParseJSON(value) {
        if (typeof value === 'string') {
            try {
                // Try to parse as JSON
                const parsed = JSON.parse(value);
                // If successful, format recursively
                return recursivelyParseJSON(parsed);
            } catch (e) {
                // Not JSON, return as is
                return value;
            }
        } else if (Array.isArray(value)) {
            // Handle arrays
            return value.map(item => recursivelyParseJSON(item));
        } else if (typeof value === 'object' && value !== null) {
            // Handle objects
            const processed = {};
            for (const [key, val] of Object.entries(value)) {
                processed[key] = recursivelyParseJSON(val);
            }
            return processed;
        }
        return value;
    }

    // Helper function to format JSON with recursive parsing
    function formatJSON(json) {
        try {
            const parsed = typeof json === 'string' ? JSON.parse(json) : json;
            const recursivelyParsed = recursivelyParseJSON(parsed);
            return JSON.stringify(recursivelyParsed, null, 2);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            return json;
        }
    }

    // Helper function to get formatted content for modal.
    function getFormattedContent(content, isJson) {
        if (isJson) {
            try {
                const decodedContent = atob(content);
                return '<pre>' + formatJSON(decodedContent) + '</pre>';
            } catch (e) {
                console.error('Base64 Decode Error:', e);
                return '<div class="error">Error decoding content</div>';
            }
        }
        return '<div>' + content + '</div>';
    }

    // Open modal on expand icon click
    $(document).on('click', '.expand-modal', function() {
        var content = $(this).data('full-content');
        var isJson = $(this).data('is-json') === 1;
        var $row = $(this).closest('tr');
        var columnName = $(this).closest('td').attr('data-colname');

        // Check if we're in the bookings table or logs table
        var isLogsTable = $row.closest('table').hasClass('tt-common-logs');

        if (isLogsTable) {
            // For logs table - show both args and response
            var id = $row.find('td[data-colname="ID"]').contents().filter(function() {
                return this.nodeType === 3;
            }).text().trim();
            var type = $row.find('td[data-colname="Type"]').text().trim();
            var createdAt = $row.find('td[data-colname="Created At"]').text().trim();

            // Get content for both columns
            var argsContent = $row.find('td[data-colname="Args"]').find('.expand-modal').data('full-content');
            var responseContent = $row.find('td[data-colname="Response"]').find('.expand-modal').data('full-content');
            var argsIsJson = $row.find('td[data-colname="Args"]').find('.expand-modal').data('is-json') === 1;
            var responseIsJson = $row.find('td[data-colname="Response"]').find('.expand-modal').data('is-json') === 1;

            // Format modal title
            var titleHtml = `
                <div class="ttnsw-modal-title-main">Log Details</div>
                <div class="ttnsw-modal-meta">
                    <span class="ttnsw-modal-meta-item">
                        <span class="ttnsw-modal-meta-label">#</span>
                        <span class="ttnsw-modal-meta-value">${id}</span>
                    </span>
                    <span class="ttnsw-modal-meta-item">
                        <span class="ttnsw-modal-meta-label">Type:</span>
                        <span class="ttnsw-modal-meta-value">${type}</span>
                    </span>
                    <span class="ttnsw-modal-meta-item">
                        <span class="ttnsw-modal-meta-label">Created At:</span>
                        <span class="ttnsw-modal-meta-value">${createdAt}</span>
                    </span>
                </div>
            `;

            modalTitle.html(titleHtml);
            // Set split view content
            modalBody.addClass('split-view').html(`
                <div class="column">
                    <div class="column-title">Arguments</div>
                    ${getFormattedContent(argsContent, argsIsJson)}
                </div>
                <div class="column">
                    <div class="column-title">Response</div>
                    ${getFormattedContent(responseContent, responseIsJson)}
                </div>
            `);
        } else {
            // Original code for bookings table
            var basicInfo = {
                id: $row.find('td[data-colname="ID"]').contents().filter(function() {
                    return this.nodeType === 3;
                }).text().trim(),
                orderId: $row.find('td[data-colname="WC Order ID"]').text(),
                bookingId: $row.find('td[data-colname="Booking ID"]').text()
            };

            var titleHtml = `
                <div class="ttnsw-modal-title-main">${columnName} Details</div>
                <div class="ttnsw-modal-meta">
                    <span class="ttnsw-modal-meta-item">
                        <span class="ttnsw-modal-meta-label">#</span>
                        <span class="ttnsw-modal-meta-value">${basicInfo.id || 'N/A'}</span>
                    </span>
                    <span class="ttnsw-modal-meta-item">
                        <span class="ttnsw-modal-meta-label">Order:</span>
                        <span class="ttnsw-modal-meta-value">${basicInfo.orderId || 'N/A'}</span>
                    </span>
                    <span class="ttnsw-modal-meta-item">
                        <span class="ttnsw-modal-meta-label">Booking:</span>
                        <span class="ttnsw-modal-meta-value">${basicInfo.bookingId || 'N/A'}</span>
                    </span>
                </div>
            `;

            modalTitle.html(titleHtml);

            // Set content
            modalBody.html(getFormattedContent(content, isJson));
        }

        modal.show();
    });

    // Close modal on X click
    $('.ttnsw-modal-close').click(function() {
        modal.hide();
    });

    // Close modal on outside click
    $(window).click(function(e) {
        if ($(e.target).is(modal)) {
            modal.hide();
        }
    });

    // Row selection functionality
    $(document).on('click', '.tt-bookings-table-ctr table.bookings tbody tr', function(e) {
        // Don't trigger if clicking on a link or expand button
        if($(e.target).is('a') || $(e.target).is('.expand-modal') || $(e.target).closest('.expand-modal').length) {
            return;
        }
        
        // Toggle selected class
        $(this).toggleClass('selected').siblings().removeClass('selected');
    });
});

// Handle total count calculation
jQuery(document).ready(function($) {
    function initTotalCount() {
        const $totalCount = $('.ttnsw-logs-total-count');
        if (!$totalCount.length) return;

        const nonce = $totalCount.data('nonce');
        const $container = $('.ttnsw-is-exact-count');
        const isExactCount = $container.data('is-exact-count') === true;

        // No need to calculate if exact count is already displayed.
        if ( isExactCount ) return;

        // Start background calculation
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ttnsw_calculate_exact_count',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update display with exact count
                    $totalCount.html(`Total (refreshes every 5min): ${response.data.count.toLocaleString()} records`);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error calculating exact count:', error);
            }
        });

        // Poll for updates
        const checkExactCount = setInterval(function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ttnsw_get_exact_count',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success && response.data.count && ! response.data.is_calculating) {
                        // Update display and stop polling
                        $totalCount.html(`Total (refreshes every 5min): ${response.data.count.toLocaleString()} records`);
                        clearInterval(checkExactCount);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error getting exact count:', error);
                }
            });
        }, 5000); // Check every 5 seconds

        // Stop checking after 5 minutes
        setTimeout(function() {
            clearInterval(checkExactCount);
        }, 300000);
    }

    initTotalCount();
});

// Handle approximate count display
jQuery(document).ready(function($) {
    const $container = $('.ttnsw-is-approximate');
    if ( ! $container.length ) return;

    const isApproximate = $container.data('is-approximate') === true;

    if( ! isApproximate ) return;   

    const $displayingNum = $('.displaying-num');
    if ($displayingNum.length) {
        // Add approximate indicator and tooltip
        $displayingNum
            .addClass('approximate-count')
            .prepend('~')
            .append(`
                <span class="ttnsw-count-info">
                    <span class="dashicons dashicons-info"></span>
                    <span class="ttnsw-count-tooltip">
                        ${ttnsw_JS_obj.i18n.approximateCountInfo}
                    </span>
                </span>
            `);
    }
});

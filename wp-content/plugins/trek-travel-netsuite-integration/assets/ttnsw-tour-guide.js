jQuery(document).ready(function($) {
    // Add tour guide button
    $('body').append(`
        <button class="tt-tour-guide-btn" title="Start Tour Guide">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg>
        </button>
    `);

    // Save tour seen status
    function storeSeenStatus() {
        if(! ttnsw_tour_JS_obj.show_tour) { 
            return;
        }
        $.ajax({
            url: ttnsw_tour_JS_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'tt_save_tour_status',
                nonce: ttnsw_tour_JS_obj.nonce,
                tour_name: ttnsw_tour_JS_obj.tour_name
            },
            success: function(response) {
                console.log('Tour status saved');
            }
        });
    }

    function startLogsTableTour() {
        const tour = new Shepherd.Tour({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shepherd-theme-arrows',
                scrollTo: true,
                cancelIcon: {
                    enabled: true
                }
            }
        });

        tour.addStep({
            id: 'welcome',
            text: `<h3>Hi ${ttnsw_tour_JS_obj.user_name}! 👋</h3>
                  <div>Welcome to the Logs page! Let's take a quick tour of the main features.</div>`,
            buttons: [
                {
                    text: 'Skip',
                    action: function() {
                        // Save that user has seen the tour
                        storeSeenStatus();
                        tour.complete();
                    }
                },
                {
                    text: 'Start',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'help-tab',
            text: 'Click on Help tab to view detailed documentation about the logs features and overview.',
            attachTo: {
                element: '#contextual-help-link',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'screen-options',
            text: 'Customize how many logs to display per page using Screen Options.',
            attachTo: {
                element: '#show-settings-link',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'search',
            text: 'Search through logs by type, arguments or response.',
            attachTo: {
                element: '.search-box',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'expand',
            text: 'Click on expand icons to view full content in a modal window.',
            attachTo: {
                element: '.expand-modal',
                on: 'right'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Finish',
                    action: function() {
                        // Save that user has seen the tour
                        storeSeenStatus();
                        tour.complete();
                    }
                }
            ]
        });

        return tour;
    }

    function startBookingsTableTour() {
        const tour = new Shepherd.Tour({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shepherd-theme-arrows',
                scrollTo: true,
                cancelIcon: {
                    enabled: true
                }
            }
        });

        tour.addStep({
            id: 'welcome',
            text: `<h3>Hi ${ttnsw_tour_JS_obj.user_name}! 👋</h3>
                  <div>Welcome to the Bookings page! Let's explore the powerful features for trip bookings table.</div>`,
            buttons: [
                {
                    text: 'Skip',
                    action: function() {
                        // Save that user has seen the tour
                        storeSeenStatus();
                        tour.complete();
                    }
                },
                {
                    text: 'Start',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'help-tab',
            text: 'Access the Help tab for detailed documentation about working with bookings and available features.',
            attachTo: {
                element: '#contextual-help-link',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next', 
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'screen-options',
            text: 'Use Screen Options to customize your view - adjust items per page and toggle column groups visibility.',
            attachTo: {
                element: '#show-settings-link',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: function() {
                        // open column groups
                        $('#show-settings-link').click();
                        tour.next();
                    }
                }
            ]
        });

        tour.addStep({
            id: 'column-groups',
            text: 'Organize your view using Column Groups. Toggle different information sections like Basic Info, Trip Info, Guest Info, etc.',
            attachTo: {
                element: '.column-groups',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: function() {
                        // close column groups
                        $('#show-settings-link').click();
                        setTimeout(() => {
                            tour.back();
                        }, 200);
                    }
                },
                {
                    text: 'Next',
                    action: function() {
                        // close column groups
                        $('#show-settings-link').click();
                        setTimeout(() => {
                            tour.next();
                        }, 200);
                    }
                }
            ]
        });

        tour.addStep({
            id: 'search',
            text: 'Powerful search functionality - filter bookings by Booking ID, Order ID, Guest Email, Names or Trip Code.',
            attachTo: {
                element: '.search-box',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: function() {
                        // open column groups
                        $('#show-settings-link').click();
                        setTimeout(() => {
                            tour.back();
                        }, 200);
                    }
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'table-links',
            text: 'Click on IDs to open related items in NetSuite - Booking Details, Guest Profile, or Release Forms.',
            attachTo: {
                element: '.wp-list-table .ns_trip_booking_id',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'expand-cells',
            text: 'Use expand icons to view full content for fields like WC Order Meta in a modal window.',
            attachTo: {
                element: '.expand-modal',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Finish',
                    action: function() {
                        storeSeenStatus();
                        tour.complete();
                    }
                }
            ]
        });

        return tour;
    }

    function startSyncTabTour() {
        const is_data_admin = ttnsw_tour_JS_obj.user_roles && ttnsw_tour_JS_obj.user_roles.includes('ecomm_user_data_admin');

        const tour = new Shepherd.Tour({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shepherd-theme-arrows',
                scrollTo: true,
                cancelIcon: {
                    enabled: true
                }
            }
        });

        tour.addStep({
            id: 'welcome',
            text: `<h3>Hi ${ttnsw_tour_JS_obj.user_name}! 👋</h3>
                  <div>Welcome to the NetSuite Integration dashboard! Let's explore the sync features between WooCommerce and NetSuite.</div>`,
            buttons: [
                {
                    text: 'Skip',
                    action: function() {
                        // Save that user has seen the tour
                        storeSeenStatus();
                        tour.complete();
                    }
                },
                {
                    text: 'Start',
                    action: tour.next
                }
            ]
        });

        if ( is_data_admin ) {
            // Allowing only data admin to see this step
            tour.addStep({
                id: 'guest-sync',
                text: 'Synchronize guest bookings and preferences using their NetSuite User ID.',
                attachTo: {
                    element: '#tt-bookings-sync-admin',
                    on: 'bottom'
                },
                buttons: [
                    {
                        text: 'Back',
                        action: tour.back
                    },
                    {
                        text: 'Finish',
                        action: function() {
                            storeSeenStatus();
                            tour.complete();
                        }
                    }
                ]
            });

            return tour;
        }

        tour.addStep({
            id: 'help-tab',
            text: 'The Help tab contains detailed documentation about sync functionality and CRON jobs configuration.',
            attachTo: {
                element: '#contextual-help-link',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'sync-section',
            text: 'The manual sync section lets you synchronize different types of data between WooCommerce and NetSuite.',
            attachTo: {
                element: '#tt-ns-sync-class',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'sync-types',
            text: 'Choose from different sync types: Get All Trips, Trip Details, Create WC Products, Custom Items, and NS<>WC Booking Sync.',
            attachTo: {
                element: '#tt-ns-sync-class select[name="type"]',
                on: 'right'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'order-sync',
            text: 'Use this section to manually sync specific WooCommerce orders to NetSuite.',
            attachTo: {
                element: '#tt-order-sync-admin',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'trip-sync-details',
            text: 'Sync individual trip details using their Trip ID in this section.',
            attachTo: {
                element: '#tt-trip-details-sync-admin',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'trip-sync-product',
            text: 'Sync individual trip product using their Trip Code/SKU in this section.',
            attachTo: {
                element: '#tt-trip-product-sync-admin',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'guest-sync',
            text: 'Synchronize guest bookings and preferences using their NetSuite User ID.',
            attachTo: {
                element: '#tt-bookings-sync-admin',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'checklist-sync',
            text: 'Update user meta and checklist locking statuses for the specified time range.',
            attachTo: {
                element: '#tt-checklist-sync',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Back',
                    action: tour.back
                },
                {
                    text: 'Finish',
                    action: function() {
                        storeSeenStatus();
                        tour.complete();
                    }
                }
            ]
        });

        return tour;
    }

    // Tour for pages without guide.
    function startNoGuideTourYet() {
        const tour = new Shepherd.Tour({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shepherd-theme-arrows',
                scrollTo: true,
                cancelIcon: {
                    enabled: true
                }
            }
        });

        tour.addStep({
            id: 'welcome',
            text: `<h3>Hi ${ttnsw_tour_JS_obj.user_name}! 👋</h3>
                  <div>Welcome to Trek Travel Netsuite Integration! Unfortunately, there is no tour guide available for this page yet.</div>`,
            buttons: [
                {
                    text: 'Skip',
                    action: function() {
                        // Save that user has seen the tour
                        storeSeenStatus();
                        tour.complete();
                    }
                },
                {
                    text: 'Finish',
                    action: function() {
                        // Save that user has seen the tour
                        storeSeenStatus();
                        tour.complete();
                    }
                }
            ]
        });

        return tour;
    }

    // Initialize tour based on current screen
    function initGuideTour() {
        let tour = null;
        switch (ttnsw_tour_JS_obj.current_screen) {
            case 'netsuitewc_page_tt-common-logs':
                tour = startLogsTableTour();
                break;

            case 'netsuitewc_page_tt-bookings':
                tour = startBookingsTableTour();
                break;

            case 'toplevel_page_trek-travel-ns-wc':
                tour = startSyncTabTour();
                break;
        
            default:
                tour = startNoGuideTourYet();
                break;
        }

        if ( tour ) {
            tour.start();
        }
    }

    // Start tour when button clicked
    $('.tt-tour-guide-btn').on('click', function() {
        initGuideTour();
    });

    // Auto start tour for new users
    if(ttnsw_tour_JS_obj.show_tour) {
        initGuideTour();
    }
});

/**
 * Trek Account Page Tour
 * 
 * Implements a guided tour of the My Account page using Shepherd.js
 */
jQuery(document).ready(function($) {
	// Check if user is logged in
	if (!trek_tour_JS_obj.is_logged_in) {
		// User is not logged in. Tour will not start.
		return;
	}

	// Check if tour guide elements exist on the page
	if ($('[data-tour-guide]').length === 0) {
		// No tour guide elements found on the page.
		return;
	}

	// Remove Elementor kit class if it exists
	$('body').removeClass('elementor-kit-14');

	// Add tour guide button
	$('body').append(`
		<button class="tt-tour-guide-btn" title="Start Tour Guide">
			<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg>
		</button>
	`);
	
	let screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

	$(window).on('resize', function() {
		// Update screen width on resize
		screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
	});

	/**
	 * Section toggle handler helper object
	 * Manages opening/closing sections via toggle buttons
	 */
	const sectionToggler = {
		lastToggleBtn: null,

		/**
		 * Opens a section by its toggle button if it's closed
		 * 
		 * @param {string|jQuery} selector - CSS selector or jQuery object for the toggle button
		 * @param {boolean} closeLastToggled - Whether to close the previously toggled section
		 * @return {boolean} - Whether a section was toggled
		 */
		openSection: function(selector, closeLastToggled = false) {
			// Close last section if requested and exists
			if (closeLastToggled && this.lastToggleBtn) {
				this.closeLastSection();
			}
			
			// Get button from selector (can be jQuery object or string selector)
			const btn = typeof selector === 'string' ? $(selector) : selector;
			
			if (btn.length && btn.attr('aria-expanded') === 'false') {
				btn.click(); // Open section if it's closed
				this.lastToggleBtn = btn; // Store last button clicked
				return true;
			}

			return false;
		},
		
		/**
		 * Closes the last toggled section if it exists
		 * 
		 * @return {boolean} - Whether a section was closed
		 */
		closeLastSection: function() {
			if (this.lastToggleBtn) {
				this.lastToggleBtn.click();
				this.lastToggleBtn = null;
				return true;
			}
			return false;
		},
		
		/**
		 * Gets the appropriate account details button based on screen size
		 * 
		 * @return {jQuery} - jQuery object for the account details button
		 */
		getAccountDetailsButton: function() {
			if (screenWidth < 769) {
				return $('.my-profile-mobile .mobile-toggle-heading');
			} else {
				return $('.my-profile-desktop .mobile-toggle-heading');
			}
		}
	};

	// Save tour seen status
	function storeSeenStatus() {
		if (!trek_tour_JS_obj.show_tour) {
			return;
		}

		$.ajax({
			url: trek_tour_JS_obj.ajaxurl,
			type: 'POST',
			data: {
				action: 'tt_save_welcome_tour_status',
				nonce: trek_tour_JS_obj.nonce,
				tour_name: trek_tour_JS_obj.tour_name
			},
			success: function(response) {
				
				if ( response.success ) {
					console.log('Tour status saved');
					// Update the tour visibility status
					trek_tour_JS_obj.show_tour = '';

					// Track tutorial completion
					if ( typeof dataLayer !== 'undefined' ) {
						dataLayer.push({
							'event': 'my_tutorial_action',
							'action': 'completed',
							'type': 'manual',
							'name': trek_tour_JS_obj.tour_name,
						});
					}
				}
			}
		});
	}

	// Initialize the account page tour
	function startAccountPageTour() {
		const defaultScrollOffset = -200;

		// Configure the tour
		const tour = new Shepherd.Tour({
			useModalOverlay: true,
			defaultStepOptions: {
				classes: 'shepherd-theme-arrows',
				// scrollTo: true,
				modalOverlayOpeningPadding: 20,
				modalOverlayOpeningRadius: 4,
				cancelIcon: {
					enabled: true
				},
				scrollTo: false,
				when: {
					show: function () {
						const el = this.options.attachTo && document.querySelector(this.options.attachTo.element);
						if (el) {
							const y = el.getBoundingClientRect().top + window.pageYOffset + defaultScrollOffset;
							window.scrollTo({ top: y, behavior: 'smooth' });
						}
					}
				}
			}
		});

		// Welcome step
		tour.addStep({
			id: 'welcome',
			title: 'Welcome to Your Account 🎉',
			text: `<strong>Hi, ${trek_tour_JS_obj.user_name}!</strong> 👋 <br> Let's take a quick tour of your account page.`,
			buttons: [
				{
					text: trek_tour_JS_obj.i18n.not_now,
					action: function() {
						// Track tutorial dismissed
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_dismissed',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'welcome',
								'tutorial_step_position': 1
							});
						}
						tour.cancel();
					},
					classes: 'shepherd-button-secondary'
				},
				{
					text: trek_tour_JS_obj.i18n.start_tour,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'welcome',
								'tutorial_step_position': 1,
								'action': 'next'
							});
						}
						// Open My Trips section if it's closed
						sectionToggler.openSection('.my-trips-card .mobile-toggle-heading');
						tour.next();
					}
				}
			]
		});

		// Nearest Upcoming Trip step
		tour.addStep({
			id: 'nearest-upcoming-trip',
			title: 'Nearest Upcoming Trip 🗓️',
			text: 'Here you\'ll see your nearest upcoming trip!',
			attachTo: {
				element: '[data-tour-guide="nearest-upcoming-trip"]',
				on: screenWidth < 1440 ? 'bottom' : 'right'
			},
			buttons: [
				{
					text: trek_tour_JS_obj.i18n.back,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'dashboard',
								'tutorial_step_position': 2,
								'action': 'back'
							});
						}
						// Close My Trips section if it was opened
						sectionToggler.closeLastSection();
						tour.back();
					},
					classes: 'shepherd-button-secondary'
				},
				{
					text: trek_tour_JS_obj.i18n.next,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'dashboard',
								'tutorial_step_position': 2,
								'action': 'next'
							});
						}
						tour.next();
					}
				}
			]
		});

		// View Checklist step
		tour.addStep({
			id: 'view-checklist',
			title: 'View Checklist 📝',
			text: 'We\'ll need a few pieces of information from you before you travel to make sure we create the trip of a lifetime for you! View your checklist to confirm your bike selection, dietary needs, and other preferences.',
			attachTo: {
				element: '[data-tour-guide="view-checklist"]',
				on: 'bottom'
			},
			buttons: [
				{
					text: trek_tour_JS_obj.i18n.back,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'navigation',
								'tutorial_step_position': 3,
								'action': 'back'
							});
						}
						tour.back();
					},
					classes: 'shepherd-button-secondary'
				},
				{
					text: trek_tour_JS_obj.i18n.next,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'navigation',
								'tutorial_step_position': 3,
								'action': 'next'
							});
						}
						// Close My Trips section and open Resource Center
						sectionToggler.openSection('.resource-center .mobile-toggle-heading', true);
						tour.next();
					}
				}
			],
			// highlightClass: 'shepherd-highlighted'
		});

		// Resource Center step
		tour.addStep({
			id: 'resource-center',
			title: 'Resource Center 📚',
			text: 'Get ready for your trip by reading or watching videos in our resource center. Here you\'ll find commonly asked questions, packing lists, and links to resources you might need before the trip starts!',
			attachTo: {
				element: '[data-tour-guide="resource-center"]',
				on: screenWidth < 992 ? 'top' : 'left'
			},
			buttons: [
				{
					text: trek_tour_JS_obj.i18n.back,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'orders',
								'tutorial_step_position': 4,
								'action': 'back'
							});
						}
						// Close Resource Center and open My Trips
						sectionToggler.openSection('.my-trips-card .mobile-toggle-heading', true);
						tour.back();
					},
					classes: 'shepherd-button-secondary'
				},
				{
					text: trek_tour_JS_obj.i18n.next,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'orders',
								'tutorial_step_position': 4,
								'action': 'next'
							});
						}
						// Close Resource Center and open Account Details
						sectionToggler.openSection(sectionToggler.getAccountDetailsButton(), true);
						// Move to account details step
						tour.next();
					}
				}
			],
		});

		// Account details step
		tour.addStep({
			id: 'account-details',
			title: 'Account Details 👤',
			text: 'You can update your contact information and communication preferences at any time under your profile information.',
			attachTo: {
				element: screenWidth < 769 ? '[data-tour-guide="account-details-mobile"]' : '[data-tour-guide="account-details"]',
				on: screenWidth < 992 ? 'bottom' : 'left'
			},
			buttons: [
				{
					text: trek_tour_JS_obj.i18n.back,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'account-details',
								'tutorial_step_position': 5,
								'action': 'back'
							});
						}
						// Close Account Details and open Resource Center
						sectionToggler.openSection('.resource-center .mobile-toggle-heading', true);
						// Move back to resource center step
						tour.back();
					},
					classes: 'shepherd-button-secondary'
				},
				{
					text: trek_tour_JS_obj.i18n.next,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'account-details',
								'tutorial_step_position': 5,
								'action': 'next'
							});
						}
						// Close Account Details section
						sectionToggler.closeLastSection();
						// Move to final step
						tour.next();
					}
				}
			],
		});

		// Final step
		tour.addStep({
			id: 'tour-complete',
			title: 'Tour Complete! ✨',
			text: '<strong>That\'s it for now! Happy travels!</strong><br>If you need to view this tour again, click the guide button in the bottom left corner of the screen.',
			buttons: [
				{
					text: trek_tour_JS_obj.i18n.finish,
					action: function() {
						// Track tutorial step interaction
						if (typeof dataLayer !== 'undefined') {
							dataLayer.push({
								'event': 'my_tutorial_step_interaction',
								'name': trek_tour_JS_obj.tour_name,
								'tutorial_step': 'tour-complete',
								'tutorial_step_position': 6,
								'action': 'finish'
							});
						}
						tour.complete();
					}
				}
			]
		});

		// Track when users cancel the tour
		tour.on('cancel', function() {
			// Track tutorial canceled
			if (typeof dataLayer !== 'undefined') {
				dataLayer.push({
					'event': 'my_tutorial_action',
					'action': 'canceled',
					'type': 'manual',
					'name': trek_tour_JS_obj.tour_name
				});
			}
			storeSeenStatus();
		});

		// Save status when tour is completed
		tour.on('complete', storeSeenStatus);

		// Start the tour
		tour.start();
	}

	// Start tour when button clicked
	$('.tt-tour-guide-btn').on('click', function() {
		// Track tutorial start from button
		if (typeof dataLayer !== 'undefined') {
			dataLayer.push({
				'event': 'my_tutorial_action',
				'action': 'started',
				'type': 'manual',
				'name': trek_tour_JS_obj.tour_name,
			});
		}
		startAccountPageTour();
	});

	// Auto start tour for new users
	if (trek_tour_JS_obj.show_tour) {
		// Delay the start slightly to ensure page is fully loaded
		setTimeout(function() {
			// Track tutorial start
			if (typeof dataLayer !== 'undefined') {
				dataLayer.push({
					'event': 'my_tutorial_action',
					'action': 'started',
					'type': 'automatic',
					'name': trek_tour_JS_obj.tour_name
				});
			}
			startAccountPageTour();
		}, 1000);
	}
});

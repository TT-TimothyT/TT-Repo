// Not really needed
import './scss/main.scss';

// bootstrap components
import { Carousel, Modal, Collapse, Toast } from 'bootstrap';

(function () {
	'use strict';

	//const promoBanner = new bootstrap.Carousel('#promoBanner');

	// Focus input if Searchform is empty
	[].forEach.call(document.querySelectorAll('.search-form'), (el) => {
		el.addEventListener('submit', function (e) {
			var search = el.querySelector('input');
			if (search.value.length < 1) {
				e.preventDefault();
				search.focus();
			}
		});
	});

	// Initialize Popovers: https://getbootstrap.com/docs/5.0/components/popovers
	var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
	var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
		return new bootstrap.Popover(popoverTriggerEl, {
			trigger: 'focus',
		});
	});

	// Header dropshadow on scroll
	window.addEventListener('scroll', (e) => {
		const header = document.querySelector('header');
		if (window.pageYOffset > 0) {
			header.classList.add("add-shadow");
		} else {
			header.classList.remove("add-shadow");
		}

    	const overviewMenu = document.querySelector('.overview-menu-mobile');

		// Dynamically calculate the height of the header
		const headerHeight = header.offsetHeight; // Get dynamic height of the header

		// Calculate the top offset for the mobile and desktop menu
		const drawerTopMobile = headerHeight + 0; // Add a small margin if necessary
		const drawerTopDesktop = headerHeight + 10;
	
		// Position the overview-menu-mobile and navigation-sticky based on the header's height
		jQuery(".overview-menu-mobile").css({ top: drawerTopMobile + 'px' });
		jQuery(".navigation-sticky").css({ top: drawerTopDesktop + 'px' });

		// pdp nav bar sticky
		// var $el = jQuery('#header');
		// var drawerTopMobile = $el.position().top + $el.outerHeight() - 70;
		// var drawerTopDesktop = $el.position().top + $el.outerHeight(true);
		// jQuery(".overview-menu-mobile").css({ top: drawerTopMobile + 'px' });
		// jQuery(".navigation-sticky").css({ top: drawerTopDesktop + 'px' });
		

	});

	jQuery('.share-link').click(function (e) {
		e.preventDefault()
		// console.log("heerrroooo...........")
		// toastLiveExample = jQuery('.link-copied')
		var toastText = new Toast(jQuery('.mobile-link-copied'))
		jQuery('.mobile-link-copied').addClass("show")
		// jQuery('.link-copied').fadeOut("slow")
		navigator.clipboard.writeText(window.location.href);
	})

	// pdp overview nav bar mobile
	jQuery(".overview-menu-mobile .accordion-body .nav-link").click(function () {
		var selectedItem = jQuery(this).text()
		jQuery('.overview-menu-mobile .accordion-body .nav-link').not(this).removeClass('active');
		jQuery(this).addClass("active")
		jQuery('.overview-menu-mobile .accordion-button').text(selectedItem)
		jQuery('.overview-menu-mobile .accordion-button').click()
	});

	// pdp dates and pricing TREK-278
	jQuery('#select-month').on('change', function (e) {
		jQuery("#" + jQuery(this).val()).click()
	});

	// TREK-304 CSS and Icons for Fake Dropdowns / Filter Modal Triggers
	jQuery("#ais-sort-selector li a").click(function () {
		var selectedItem = jQuery(this).text()
		jQuery('#ais-sort-selector li a').not(this).removeClass('fw-semibold');
		jQuery('.sort-selection').text(selectedItem)
		jQuery(this).addClass("fw-semibold")
	});

	
	//jQuery('.input-number').focusin(function(){
	jQuery('body').on('focusin', '.input-number', function (e) {
		jQuery(this).data('oldValue', jQuery(this).val());
	});
	jQuery('body').on('change', '.input-number', function (e) {
		//jQuery('.input-number').change(function() {	
		var minValue = parseInt(jQuery(this).attr('min'));
		var maxValue = parseInt(jQuery(this).attr('max'));
		var valueCurrent = parseInt(jQuery(this).val());

		var name = jQuery(this).attr('name');
		if (valueCurrent >= minValue) {
			jQuery(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
		}
		if (valueCurrent <= maxValue) {
			jQuery(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
		}


	});
	jQuery('body').on('keydown', '.input-number', function (e) {
		//jQuery(".input-number").keydown(function (e) {
		if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
			(e.keyCode == 65 && e.ctrlKey === true) ||
			(e.keyCode >= 35 && e.keyCode <= 39)) {
			return;
		}
		if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
			e.preventDefault();
		}
	});

	// my account password change
	if (jQuery('#togglePassword1').length > 0) {
		const togglePassword1 = document.querySelector('#togglePassword1');
		const password_current = document.querySelector('#password_current');

		togglePassword1.addEventListener('click', () => {
			const type = password_current.getAttribute('type') === 'password' ? 'text' : 'password';
			password_current.setAttribute('type', type);
			togglePassword1.classList.toggle('bi-eye');
		});
	}

	if (jQuery('#togglePassword2').length > 0) {
		const togglePassword2 = document.querySelector('#togglePassword2');
		const password_1 = document.querySelector('#password_1');

		togglePassword2.addEventListener('click', () => {
			const type = password_1.getAttribute('type') === 'password' ? 'text' : 'password';
			password_1.setAttribute('type', type);
			togglePassword2.classList.toggle('bi-eye');
		});
	}

	if (jQuery('#togglePassword3').length > 0) {
		const togglePassword3 = document.querySelector('#togglePassword3');
		const password_2 = document.querySelector('#password_2');

		togglePassword3.addEventListener('click', () => {
			const type = password_2.getAttribute('type') === 'password' ? 'text' : 'password';
			password_2.setAttribute('type', type);
			togglePassword3.classList.toggle('bi-eye');
		});
	}

	jQuery('.mobile-toggle-heading').on('click', function() {

		// Track click event for Dashboard accordeon items clicks
		dataLayer.push({
			'event': 'my_dashboard_accordeon_click',
			'name': jQuery(this).find('h5').text(),
		});
	})

	// Set cookie for 7 days
	function setCookie(name, value, days) {
		const d = new Date();
		d.setTime(d.getTime() + (days*24*60*60*1000));
		document.cookie = name + "=" + value + ";expires=" + d.toUTCString() + ";path=/";
	}

	// Get cookie by name
	function getCookie(name) {
		const cname = name + "=";
		const decodedCookie = decodeURIComponent(document.cookie);
		const ca = decodedCookie.split(';');
		for (let i = 0; i < ca.length; i++) {
			let c = ca[i].trim();
			if (c.indexOf(cname) === 0) {
			return c.substring(cname.length, c.length);
			}
		}
		return "";
	}

	// Find sibling with .dashboard__trip class
	function findSiblingDashboardTrip(element) {
		const siblings = element.parentElement.children;
		for (let i = 0; i < siblings.length; i++) {
			if (siblings[i] !== element && siblings[i].classList.contains("dashboard__trip")) {
			return siblings[i];
			}
		}
		return null;
	}

	document.addEventListener("DOMContentLoaded", function () {

		document.querySelectorAll(".hide-insurance-btn").forEach(function(btn) {
			btn.addEventListener("click", function(e) {
				e.preventDefault();
				// Get the order ID from data attribute
				const orderId = btn.getAttribute("data-order_id");
				const page    = btn.getAttribute("data-page");

				// Hide Travel Protection info link
				dataLayer.push({
					'event': 'my_button_click',
					'type': 'hide_travel_protection',
					'order_id': orderId,
					'page': page,
				});

				const section = btn.closest(".trip-insurance-info");
				if (section) {
					section.style.display = "none";

					// Set cookie with dynamic name based on order ID.
					if (orderId) {
						setCookie("hide_trip_insurance_info_" + orderId, true, 7);
					}
				}
			});
		});

		jQuery('.travel-protection-list.show-mobile li').on('click', function() {
			jQuery(this).toggleClass('active');
			jQuery(this).children('div').children('span').children('span').slideToggle();
		})

		jQuery(document).on('click', '.order-details-btn', function() {
			// Order Details button
			dataLayer.push({
				'event': 'my_button_click',
        		'type': 'order_details_click',
				'order_id': jQuery(this).data('order_id'),
				'page': jQuery(this).data('page'),
			});
		});

		jQuery(document).on('click', '.view-checklist-btn', function() {
			// View Checklist button
			dataLayer.push({
				'event': 'my_button_click',
        		'type': 'view_checklist',
				'order_id': jQuery(this).data('order_id'),
				'page': jQuery(this).data('page'),
			});
		})

		jQuery(document).on('click', '.add-travel-protection-btn', function() {
			// Add Travel Protection button
			dataLayer.push({
				'event': 'my_button_click',
        		'type': 'add_travel_protection',
				'order_id': jQuery(this).data('order_id'),
				'page': jQuery(this).data('page'),
			});
		})

		jQuery(document).on('click', '.trip-info-list a', function() {
			// Checklist item link click
			dataLayer.push({
				'event': 'my_checklist_click',
				'name': jQuery(this).text(),
				'order_id': jQuery(this).data('order_id'),
				'page': jQuery(this).data('page'),
			});
		})

		jQuery(document).on('click', '.view-full-details-link', function() {
			// View Full Insurance Details link click
			dataLayer.push({
				'event': 'my_view_full_insurance_details_click',
				'page': jQuery(this).data('page'),
			});
		})

	});

})();
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
		const header = document.querySelector('.header-main');
		if (window.pageYOffset > 0) {
			header.classList.add("add-shadow");
		} else {
			header.classList.remove("add-shadow");
		}

		// pdp nav bar sticky
		var $el = jQuery('#header');
		var drawerTopMobile = $el.position().top + $el.outerHeight() - 10;
		var drawerTopDesktop = $el.position().top + $el.outerHeight(true);
		jQuery(".overview-menu-mobile").css({ top: drawerTopMobile + 'px' });
		jQuery(".navigation-sticky").css({ top: drawerTopDesktop + 'px' });

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

})();
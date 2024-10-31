/**
 * On slide of the slider load the next or previous image.
 */
jQuery(document).ready(function($) {
	jQuery(document).on('slide.bs.carousel', '.carousel.lazy-load', function (e) {
		var image = jQuery(e.relatedTarget).find('img[data-src]');
		image.attr('src', image.data('src'));
		image.removeAttr('data-src');
	});
});

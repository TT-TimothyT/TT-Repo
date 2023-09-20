jQuery(document).ready(function($) {
	var btn = 'input[type="submit"].gform_button';
	$(document).on('click', btn, function(e){
		$(btn).css('display','none');
		$('.gform_footer').prepend('<p class="gfield_label gfield_wait">Submitting, please wait&hellip;</p>');
	});
});


<?php
/**
 * Algolia Template for the instantsearch-date on the search product page.
 */

?>

<script type="text/html" id="tmpl-instantsearch-date-template">
	<div class="menu-facet-container">
		<a class="ais-anchor" href="{{data.url}}">
			<span class="f-check"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkback.png" /></span>
			<span class="f-check-active"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkactive.png" /></span>
			<span class="algolia-start-dates">{{data.label}}</span>
		</a>
	</div>
</script>

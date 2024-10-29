<?php
/**
 * Algolia Template for the refine-hierarchy on the search and archive product pages.
 */

?>

<script type="text/html" id="tmpl-instantsearch-menu-template">
	<div class="menu-facet-container">
		<a class="ais-anchor" href="{{data.url}}">
			<span class="f-check"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkback.png" /></span>
			<span class="f-check-active"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkactive.png" /></span>
			<span class="filter-name">{{data.label}}</span>
		</a>
	</div>
</script>

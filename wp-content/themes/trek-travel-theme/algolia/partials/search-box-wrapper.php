<?php
/**
 * Template part for the algolia Search Box Wrapper on search and archive product pages.
 */

?>

<div class="container algolia-search-box-wrapper">
	<div id="algolia-search-box" style="display: none;"></div>
	<div class="sf-box d-flex">
		<label class="me-4 align-center" for="ais-SortBy-select"><?php esc_html_e( 'Sort by:', 'trek-travel-theme' ); ?></label>
		<div id="ais-sortBy"></div>
	</div>
	<div class="sf-box d-flex">
		<label class="me-4 align-center"><?php esc_html_e( 'Filters:', 'trek-travel-theme' ); ?></label>
		<div class="f-box">
			<div id="ais-date-selector" class="mobile-hideme fs-date">
				<button type="button" class="fake-selector" id="filter-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
					<?php esc_html_e( 'Date', 'trek-travel-theme' ); ?>
				</button>
			</div>
			<div id="ais-destination-selector" class="mobile-hideme">
				<button type="button" class="fake-selector fs-destination" id="filter-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
					<?php esc_html_e( 'Destination', 'trek-travel-theme' ); ?>
				</button>
			</div>
			<div id="ais-more-selector" class="mobile-hideme">
				<button type="button" class="fake-selector" id="filter-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
					<?php esc_html_e( 'More', 'trek-travel-theme' ); ?>
				</button>
			</div>
			<div id="ais-more-selector" class="desktop-hideme">
				<button type="button" class="fake-selector" id="filter-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
					<?php esc_html_e( 'Filters', 'trek-travel-theme' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

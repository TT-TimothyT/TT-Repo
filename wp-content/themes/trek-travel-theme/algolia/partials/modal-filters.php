<?php
/**
 * Template part for the algolia filters on search and archive product pages.
 */

?>

<div class="modal fade modal-search-filter" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title text-center" id="filterModalLabel"><?php esc_html_e( 'Filters', 'trek-travel-theme' ); ?></h5>
				<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
					<i type="button" class="bi bi-x"></i>
				</span>
			</div>

			<div class="modal-body">
				<div class="accordion" id="accordionAlgoliaFilters">
					<div class="accordion-item">
						<h5 class="accordion-header" id="algoliaFilters-headingOne">
							<button class="accordion-button shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#algoliaFilters-collapseOne" aria-expanded="true" aria-controls="algoliaFilters-collapseOne">
								<?php esc_html_e( 'Date Range', 'trek-travel-theme' ); ?>
							</button>
						</h5>
						<div id="algoliaFilters-collapseOne" class="accordion-collapse collapse show" aria-labelledby="algoliaFilters-headingOne">
							<div class="accordion-body">
								<span id="search-daterange"></span>
								<div class="row mx-0" id="calendarTrigger"></div>
								<div id="range-input" style="position: absolute; bottom: 10000px; overflow: hidden;"></div>
							</div>
						</div>
					</div>

					<div class="accordion-item">
						<h5 class="accordion-header" id="algoliaFilters-headingTwo">
							<button class="accordion-button shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#algoliaFilters-collapseTwo" aria-expanded="true" aria-controls="algoliaFilters-collapseTwo">
								<?php esc_html_e( 'Destinations', 'trek-travel-theme' ); ?>
							</button>
						</h5>
						<div id="algoliaFilters-collapseTwo" class="accordion-collapse collapse show" aria-labelledby="algoliaFilters-headingTwo">
							<div class="accordion-body">
								<div id="destinations">
									<?php
										foreach( $args['dest_filters'] as $dest_slug => $dest_name ) {
											?>
											<div class="dest-<?php echo esc_attr( $dest_slug ); ?>-ctr">
												<div class="d-flex mb-3 form-check">
													<input name="select-all-<?php echo esc_attr( $dest_slug ); ?>" class="form-check-input shadow-none me-3" type="checkbox" id="select-all-toggle-<?php echo esc_attr( $dest_slug ); ?>"/>
													<label class="form-check-label" for="select-all-toggle-<?php echo esc_attr( $dest_slug ); ?>"><?php echo esc_html( $dest_name ); ?></label>
													<span class="f-check"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkback.png" /></span>
													<span class="f-check-active"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkactive.png" /></span>
												</div>
												<div id="dest-<?php echo esc_attr( $dest_slug ); ?>"></div>
											</div>
											<?php
										}
									?>
								</div>
							</div>
						</div>
					</div>

					<div class="accordion-item">
						<h5 class="accordion-header" id="algoliaFilters-headingThree">
							<button class="accordion-button shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#algoliaFilters-collapseThree" aria-expanded="true" aria-controls="algoliaFilters-collapseThree">
								<?php esc_html_e( 'Duration', 'trek-travel-theme' ); ?>
							</button>
						</h5>
						<div id="algoliaFilters-collapseThree" class="accordion-collapse collapse show" aria-labelledby="algoliaFilters-headingThree">
							<div class="accordion-body">
								<div id="duration-facet"></div>
							</div>
						</div>
					</div>

					<div class="accordion-item">
						<h5 class="accordion-header" id="algoliaFilters-headingFour">
							<button class="accordion-button shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#algoliaFilters-collapseFour" aria-expanded="true" aria-controls="algoliaFilters-collapseFour">
								<?php esc_html_e( 'Trip Style', 'trek-travel-theme' ); ?>
							</button>
						</h5>
						<div id="algoliaFilters-collapseFour" class="accordion-collapse collapse show" aria-labelledby="algoliaFilters-headingFour">
							<div class="accordion-body">
								<div id="trip-style-facet"></div>
							</div>
						</div>
					</div>

					<div class="accordion-item">
						<h5 class="accordion-header" id="algoliaFilters-headingFive">
							<button class="accordion-button shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#algoliaFilters-collapseFive" aria-expanded="true" aria-controls="algoliaFilters-collapseFive">
								<?php esc_html_e( 'Activity Level', 'trek-travel-theme' ); ?>
							</button>
						</h5>
						<div id="algoliaFilters-collapseFive" class="accordion-collapse collapse show" aria-labelledby="algoliaFilters-headingFive">
							<div class="accordion-body">
								<div id="rider-level-facet"></div>
							</div>
						</div>
					</div>

					<div class="accordion-item">
						<h5 class="accordion-header" id="algoliaFilters-headingSix">
							<button class="accordion-button shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#algoliaFilters-collapseSix" aria-expanded="true" aria-controls="algoliaFilters-collapseSix">
								<?php esc_html_e( 'Hotel Level', 'trek-travel-theme' ); ?>
							</button>
						</h5>
						<div id="algoliaFilters-collapseSix" class="accordion-collapse collapse show" aria-labelledby="algoliaFilters-headingSix">
							<div class="accordion-body">
								<div id="hotel-level-facet"></div>
							</div>
						</div>
					</div>

					<div class="accordion-item">
						<h5 class="accordion-header" id="algoliaFilters-headingOne">
							<button class="accordion-button shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#algoliaFilters-collapseSeven" aria-expanded="true" aria-controls="algoliaFilters-collapseSeven">
								<?php esc_html_e( 'Price', 'trek-travel-theme' ); ?>
							</button>
						</h5>
						<div id="algoliaFilters-collapseSeven" class="accordion-collapse collapse show" aria-labelledby="algoliaFilters-headingOne">
							<div class="accordion-body">
								<div id="price-input-facet" class="mb-4"></div>
								<div id="price-slider-facet"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<div class="container">
					<div class="row mx-0 align-items-center">
						<div class="col-3 clear-all-btn">
							<span class="modal-a" id="clear-refinements"></span>
						</div>
						<div class="col d-lg-flex justify-content-lg-end align-items-lg-baseline apply-filters-info">
							<button type="button" class="btn btn-secondary d-none" data-bs-dismiss="modal"><?php esc_html_e( 'Close', 'trek-travel-theme' ); ?></button>
							<span class="filter-results-number" id="algolia-stats"></span>
							<button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php esc_html_e( 'Done', 'trek-travel-theme' ); ?></button>
						</div>
					</div>
				</div>
			</div>

		</div><!-- / .modal-content -->
	</div><!-- / .modal-dialog -->
</div><!-- / .modal -->

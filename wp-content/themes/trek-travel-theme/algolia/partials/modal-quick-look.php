<?php
/**
 * Template part for the Quick Look Modal on search and archive product pages.
 */

?>

<!-- Quick Look Modal -->
<div class="modal fade quick-look-modal" id="quickLookModal" tabindex="-1" aria-labelledby="quickLookModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-lg-down">
		<div class="modal-content">
			<!-- Dates -->
			<div class="modal-body"></div><!-- .modal-body -->
			<div class="modal-footer">
				<div class="me-auto ps-3">
					<p class="fw-normal fs-sm lh-sm mb-0 text-muted starting-from"><?php esc_html_e( 'Starting from', 'trek-travel-theme' ) ?></p>
					<p class="fw-bold fs-xl lh-xl mb-0">
						<span class="starting-from-price"></span>
						<span class="fw-normal fs-sm"><?php esc_html_e( 'per person', 'trek-travel-theme' ) ?></span>
					</p>
				</div>
			</div><!-- .modal-footer -->
		</div><!-- .modal-content -->
	</div><!-- .modal-dialog -->
</div><!-- .modal -->
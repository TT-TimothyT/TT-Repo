<?php
/**
 * Template part for the Travel Protection Decline Warning Modal.
 *
 * Displays a confirmation modal when users attempt to decline travel protection for all guests.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="container">
	<!-- Modal -->
	<div class="modal fade modal-tp-decline-warning" id="tpDeclineWarningModal" tabindex="-1" aria-labelledby="tpDeclineWarningModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-center" id="tpDeclineWarningModalLabel"><?php esc_html_e( 'Notice', 'trek-travel-theme' ); ?></h5>
					<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'trek-travel-theme' ); ?>">
						<i type="button" class="bi bi-x"></i>
					</span>
				</div>
				<div class="modal-body">
					<p class="fw-medium fs-xl lh-xl"><?php esc_html_e( 'You are declining travel protection for all guests on your booking. Would you like to proceed?', 'trek-travel-theme' ); ?></p>
					<p class="fw-normal fs-md lh-md"><?php esc_html_e( 'You can call us to add Travel Protection up to 14 days before your trip if you change your mind.', 'trek-travel-theme' ); ?></p>
					<input type="hidden" id="tpDeclineId">
				</div>
				<div class="modal-footer">
					<div class="container">
						<div class="row align-items-center">
							<div class="col text-end">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e( 'Cancel', 'trek-travel-theme' ); ?></button>
								<button type="button" class="btn btn-primary confirm-tp-decline-btn"><?php esc_html_e( 'Proceed', 'trek-travel-theme' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div><!-- / .modal-content -->
		</div><!-- / .modal-dialog -->
	</div><!-- / .modal -->
</div> <!-- / Modal .container -->

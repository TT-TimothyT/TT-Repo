<?php
/**
 * Template part for the Trip Booking Warning Modal on search and archive product pages.
 */

?>

<div class="container">
	<!-- Modal -->
	<div class="modal fade modal-trip-booking-warning" id="tripBookingModal" tabindex="-1" aria-labelledby="tripBookingModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-center" id="tripBookingModalLabel">Notice</h5>
					<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						<i type="button" class="bi bi-x"></i>
					</span>
				</div>
				<div class="modal-body">
					<p class="fw-medium fs-xl lh-xl"><?php esc_html_e( 'You have another booking already in progress', 'trek-travel-theme' ); ?></p>
					<p class="fw-normal fs-md lh-md"><?php esc_html_e( 'Booking a new trip will cancel all of your previous booking progress. Continue your previous booking or proceed with your new booking.', 'trek-travel-theme' ); ?></p>
					<input type="hidden" id="bookId">
				</div>
				<div class="modal-footer">
					<div class="container">
						<div class="row align-items-center">                                            
							<div class="col text-end">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e( 'Cancel', 'trek-travel-theme' ); ?></button>
								<button type="button" class="btn btn-primary proceed-booking-btn" data-bs-dismiss="modal"><?php esc_html_e( 'Proceed', 'trek-travel-theme' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div><!-- / .modal-content -->
		</div><!-- / .modal-dialog -->
	</div><!-- / .modal -->
</div> <!-- / Modal .container -->
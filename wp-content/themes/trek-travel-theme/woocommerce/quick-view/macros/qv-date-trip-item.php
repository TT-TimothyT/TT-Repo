<?php
/**
 * Template macro for the quick view single date trip item (accordion-item).
 *
 * @param array $args Array with the date trip arguments.
 *
 */

?>
<div class="accordion-item" data-sku="<?php echo esc_attr( $args['sku'] ); ?>" data-stella="<?php echo esc_attr( $args['remove_from_stella'] ); ?>" data-status="<?php echo esc_attr( $args['trip_status'] ); ?>">
	<?php if ( 'Private' === $args['trip_web_status'] && true != $args['is_pc_trip'] ) : ?>
		<h6 class="accordion-header" id="flush-heading-<?php echo esc_attr( $args['accordion_id'] ); ?>">
			<div class="pvt-box">
				<div class="d-box">
					<span class="fw-medium w-40 fs-lg lh-lg"><?php echo esc_html( $args['date_range'] ); ?></span>
					<span class="fw-normal fs-sm lh-sm <?php echo esc_attr( $args['trip_web_status_class'] ); ?>"><?php echo esc_html( $args['trip_web_status'] ); ?></span>
				</div>
				<span class="ms-auto fw-medium fs-sm lh-sm d-rsv"><?php esc_html_e( 'Reserved', 'trek-travel-theme' ); ?></span>
			</div>
		</h6>
	<?php else : ?>
		<h6 class="accordion-header" id="flush-heading-<?php echo esc_attr( $args['accordion_id'] ); ?>">
			<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-<?php echo esc_attr( $args['accordion_id'] ); ?>" aria-expanded="false" aria-controls="flush-collapse-<?php echo esc_attr( $args['accordion_id'] ); ?>">
			<div class="d-box">
				<span class="fw-medium w-40 fs-lg lh-lg"><?php echo esc_html( $args['date_range'] ); ?></span>
				<span class="fw-normal fs-sm lh-sm <?php echo ( 'Not Guaranteed' === $args['trip_web_status'] ? 'available' : esc_attr( $args['trip_web_status_class'] ) ); ?>"><?php echo ( 'Not Guaranteed' === $args['trip_web_status'] ? esc_html_e( 'Available', 'trek-travel-theme') : esc_html( $args['trip_web_status'] ) ); ?></span>
			</div>
			</button>
		</h6>
		<div id="flush-collapse-<?php echo esc_attr( $args['accordion_id'] ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading-<?php echo esc_attr( $args['accordion_id'] ); ?>" data-bs-parent="#accordionFlushExample-<?php echo esc_attr( $args['month_year'] ) ?>">
			<hr>
			<div class="accordion-body d-flex justify-content-between flex-wrap <?php echo esc_attr( strtolower( $args['trip_status'] ) ) ?>">
				<div class="accordion-hotels">
					<p class="fw-medium fs-sm lh-sm"><?php esc_html_e( 'Hotels:', 'trek-travel-theme' ); ?></p>
					<?php echo $args['trip_hotels']; ?>
					<a class="fs-sm view-details" href="#hotels" target="_blank"><?php esc_html_e( 'View hotels', 'trek-travel-theme' ); ?></a>
				</div>
				<?php if ( ! empty( $args['trip_activity'] ) && TT_ACTIVITY_DASHBOARD_NAME_BIKING === $args['trip_activity'] ) : ?>
					<div class="accordion-bikes">
						<p class="fw-medium fs-sm lh-sm"><?php esc_html_e( 'Available bikes:', 'trek-travel-theme' ); ?></p>
						<?php echo $args['trip_bikes']; ?>
						<a class="fs-sm view-details" href="#bikes-guides" target="_blank"><?php esc_html_e( 'View bikes', 'trek-travel-theme' ); ?></a>
					</div>
				<?php endif; ?>
				<div class="accordion-book-now">
					<form class="cart grouped_form" action="<?php echo esc_url( $args['parent_trip_permalink'] ); ?>" method="post" enctype="multipart/form-data" target="_blank">
						<h5 class="fw-semibold"><span class="amount"><span class="woocommerce-Price-currencySymbol">$</span><?php echo esc_html( $args['product_price'] ); ?> </span> <span class="fw-normal fs-md lh-md"><?php esc_html_e( 'per person', 'trek-travel-theme' ); ?></span></h5>
						<p class="fw-normal fs-xs lh-xs text-muted"><?php esc_html_e( 'Double Occupancy', 'trek-travel-theme' ); ?></p>
						<?php echo $args['book_now_btn_html']; ?>
						<p class="fw-normal fs-sm lh-sm text-muted"><?php esc_html_e( 'Single Occupancy from:', 'trek-travel-theme' ); ?> +<span class="amount"><span class="woocommerce-Price-currencySymbol">$</span><?php echo esc_html( $args['single_supplement_price'] ) ?><span> <i class="bi bi-info-circle pdp-single-occupancy"></i></p>
						<input type="hidden" name="quantity[<?php echo esc_attr( $args['product_id'] ) ?>]" value="1" class="wc-grouped-product-add-to-cart-checkbox" />
						<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $args['product_id'] ) ?>" />
					</form>
				</div>
				<?php if ( ! empty( $args['trip_date_web_note'] ) ) : ?>
					<div class="accordion-date-note w-100">
						<div class="date-note">
							<p class="fw-bold fs-xs lh-xs mb-0"><?php esc_html_e( 'Date note:', 'trek-travel-theme' ); ?></p>
							<p class="fw-normal fs-xs lh-xs mb-0"><?php echo esc_html( $args['trip_date_web_note'] ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php
/**
 * Template file for a single guest review, Thank you page.
 *
 * The $args comming as arguments from the wc_get_template_html() or wc_get_template() functions.
 */

?>
<div class="row mx-0 mb-0">
	<div class="col-lg-12 px-0 checkout-review__col <?php echo esc_html( $args['is_primary'] ? 'mt-0' : 'mt-4'); ?>">
		<div class="d-flex order-details__flex">
			<div class="mb-2 fs-md lh-sm fw-bold"><?php echo esc_html( $args['is_primary'] ? 'Primary Guest' : 'Guest ' . $args['guest_num'] ); ?></div>
			<div class="mb-2 fs-md lh-sm fw-bold text-end"><?php echo esc_html( $args['fullname'] ); ?></div>
		</div>
		<ul class="mb-0">
			<?php if( $args['is_non_rider'] ) : ?>
				<li class="mb-0 fw-normal order-details__text">
					<?php echo esc_html( 'Activity Level: ' . $args['bike_info']['Activity Level:'] ); ?>
				</li>
			<?php else : ?>
				<?php foreach( $args['bike_info'] as $item_key => $bike_info ) : ?>
					<?php if( ! empty( $bike_info ) && '-' !== $bike_info ) : ?>
						<li class="mb-0 fw-normal order-details__text">
							<?php echo esc_html( $item_key . ' ' . $bike_info ); ?>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</div>
</div>
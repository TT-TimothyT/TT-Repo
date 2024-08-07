<?php
/**
 * Template file for a single guest review, Thank you page.
 *
 * The $args comming as arguments from the wc_get_template_html() or wc_get_template() functions.
 */

?>
<div class="row mx-0 mb-0 <?php echo esc_html( $args['is_primary'] ? 'mt-0' : 'mt-4'); ?>">
	<div class="col-lg-12 px-0 checkout-review__col">
		<div class="d-flex order-details__flex">
			<div class="mb-2 fs-md lh-sm fw-bold"><?php echo esc_html( $args['is_primary'] ? 'Primary Guest' : 'Guest ' . $args['guest_num'] ); ?></div>
			<div class="mb-2 fs-md lh-sm fw-bold text-end"><?php echo esc_html( $args['fullname'] ); ?></div>
		</div>
		<ul class="mb-0">
			<?php foreach( $args['guest_info'] as $guest_info ) : ?>
				<?php if( ! empty( $guest_info ) ) : ?>
					<li class="mb-0 fw-normal order-details__text">
						<?php echo esc_html( $guest_info ); ?>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<?php if ( ! empty( $args['hiking_info'] ) ) : ?>
			<ul class="mb-0">
				<?php foreach( $args['hiking_info'] as $item_key => $hiking_info ) : ?>
					<?php if( ! empty( $hiking_info ) && '-' !== $hiking_info ) : ?>
						<li class="mb-0 fw-normal order-details__text">
							<?php echo esc_html( $item_key . ' ' . $hiking_info ); ?>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</div>
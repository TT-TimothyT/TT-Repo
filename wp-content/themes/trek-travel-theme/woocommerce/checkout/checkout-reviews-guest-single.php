<?php
/**
 * Template file for a single guest review, step 4.
 *
 * The $args comming as arguments from the wc_get_template_html() or wc_get_template() functions.
 */

if( ! $args['is_primary'] ) :
	?>
		<hr>
	<?php
endif;
?>
<div class="row mx-0 mb-0">
	<div class="col-lg-12 px-0 checkout-review__col">
		<p class="fw-medium mb-3 fs-lg lh-sm"><?php echo esc_html( $args['is_primary'] ? 'Primary Guest' : 'Guest ' . $args['guest_num'] ); ?>: <?php echo esc_html( $args['fullname'] ); ?></p>
		<ul class="mb-0">
			<li class="fs-md lh-sm mb-0">
				<?php echo esc_html( $args['room_type'] ); ?>
			</li>
		</ul>
	</div>
</div>
<hr>
<div class="row mx-0 mb-0">
	<div class="col-lg-12 px-0 checkout-review__col">
		<ul class="mb-0">
			<?php foreach( $args['guest_info'] as $guest_info ) : ?>
				<?php if( ! empty( $guest_info ) ) : ?>
					<li class="fs-md lh-sm mb-0 mb-md-1">
						<?php echo esc_html( $guest_info ); ?>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<hr>
<div class="row mx-0 mb-0">
	<div class="col-lg-12 px-0 checkout-review__col">
		<ul class="mb-0">
			<?php if( $args['is_non_rider'] ) : ?>
				<li class="fs-md lh-sm mb-0 mb-md-1">
					<?php echo esc_html( 'Rider Level: ' . $args['bike_info']['Rider Level:'] ); ?>
				</li>
			<?php else : ?>
				<?php foreach( $args['bike_info'] as $item_key => $bike_info ) : ?>
					<?php if( ! empty( $bike_info ) && '-' !== $bike_info ) : ?>
						<li class="fs-md lh-sm mb-0 mb-md-1">
							<?php echo wp_kses_post( $item_key . ' ' . $bike_info ); ?>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</div>
</div>
<a href="javascript:" class="btn btn-md btn-outline-primary d-block d-lg-inline-block mt-sm-5 tt_change_checkout_step" data-step="1"><?php esc_html_e( 'Edit Info', 'trek-travel-theme' ) ?></a>

<?php
/**
 * Template part for the Quick Look Modal component.
 */

?>

<!-- Quick Look Modal -->
<div class="modal fade quick-look-modal <?php echo esc_attr( $args['additional_class'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" tabindex="-1" aria-labelledby="quickLookModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-lg-down">
		<div class="modal-content">
			<div class="modal-header">
				<?php
				/**
				 * Fires inside the quick look modal body.
				 *
				 * @param array $args Arguments passed to the template.
				 */
				do_action( 'tt_quick_look_modal_header', $args );
				?>
			</div><!-- .modal-header -->
			<div class="modal-body">
				<?php
				/**
				 * Fires inside the quick look modal body.
				 *
				 * @param array $args Arguments passed to the template.
				 */
				do_action( 'tt_quick_look_modal_body', $args );
				?>
			</div><!-- .modal-body -->
			<div class="modal-footer">
				<?php
				/**
				 * Fires inside the quick look modal footer.
				 *
				 * @param array $args Arguments passed to the template.
				 */
				do_action( 'tt_quick_look_modal_footer', $args );
				?>
			</div><!-- .modal-footer -->
		</div><!-- .modal-content -->
	</div><!-- .modal-dialog -->
</div><!-- .modal -->

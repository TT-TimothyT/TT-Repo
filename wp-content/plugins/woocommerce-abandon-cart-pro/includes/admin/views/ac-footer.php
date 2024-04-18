<?php
/**
 * Footer Template
 *
 * @package order-delivery-date/footer
 */
?>

<!-- Footer -->
	<div class="ac-footer">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="footer-wrap">
							
							<div class="ft-text">
								<p>
									<a href="<?php echo esc_url( 'https://support.tychesoftwares.com/help/2285384554/?utm_source=acprofooter&utm_medium=link&utm_campaign=ACPRoPlugin' ); ?>" target="_blank"><?php esc_html_e( 'Need Support?', 'woocommerce-ac' ); ?></a> <strong><?php esc_html_e( 'We’re always happy to help you.', 'woocommerce-ac' ); ?></strong></p>
								</p>								
								
								<p>
								<?php printf(
    								/* translators: %s: Name of a city */
    								esc_html__( 'If this plugin helped you, %s please rate it %s %s', 'my-plugin' ),
    								'<a href="' . esc_url( 'https://www.tychesoftwares.com/submit-review/?utm_source=acprofooter&utm_medium=link&utm_campaign=ACPRoPlugin' ) .'"  target="_blank" />',
									'</a>',
									'<span class="rating">★★★★★</span>'
									)
									?>
								<br/>
								<?php esc_html_e( 'Thanks for your support', 'woocommerce-ac' ); ?>
								</p>
								
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Footer End -->
</div>
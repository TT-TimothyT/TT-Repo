<?php
/**
 * Template Name: Page (Default)
 * Description: Default page template
 *
 */
if( is_checkout() ){
	get_header('checkout');
}else{
	get_header();
}

the_post();
?>

<main id="main" class="container">
	<div class="row">
		<div class="col-md-12 col-sm-12">

			<div id="post-<?php the_ID(); ?>" <?php post_class( 'content' ); ?>>
				<?php
					the_content();

					wp_link_pages( array(
						'before' => '<div class="page-links">' . __( 'Pages:', 'trek-travel-theme' ),
						'after'  => '</div>',
					) );
					edit_post_link( __( 'Edit', 'trek-travel-theme' ), '<span class="edit-link">', '</span>' );
				?>
			</div><!-- /#post-<?php the_ID(); ?> -->
			<?php
				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
			?>

		</div><!-- /.col -->
	</div><!-- /.row -->
</main><!-- /#main -->

<?php
if( is_checkout() ){
	get_footer('checkout');
}else{
	get_footer();
}

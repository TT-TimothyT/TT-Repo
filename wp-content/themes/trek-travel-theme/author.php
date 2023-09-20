<?php
/**
 * The Template for displaying Author pages.
 */

get_header();
?>

<main id="main" class="container">
	<div class="row">
		<div class="col-md-12 col-sm-12">

		<?php
		if ( have_posts() ) :
			/**
			 * Queue the first post, that way we know
			 * what author we're dealing with (if that is the case).
			 *
			 * We reset this later so we can run the loop
			 * properly with a call to rewind_posts().
			 */
			the_post();
		?>
			<header class="page-header">
				<h1 class="page-title author">
					<?php
						printf( esc_html__( 'Author Archives: %s', 'trek-travel-theme' ), get_the_author() );
					?>
				</h1>
			</header>
		<?php
			get_template_part( 'author', 'bio' );

			/**
			 * Since we called the_post() above, we need to
			 * rewind the loop back to the beginning that way
			 * we can run the loop properly, in full.
			 */
			rewind_posts();

			get_template_part( 'archive', 'loop' );
		else :
			// 404.
			get_template_part( 'content', 'none' );
		endif;

		wp_reset_postdata(); // End of the loop.
		?>

		</div><!-- /.col -->
	</div><!-- /.row -->
</main><!-- /#main -->

<?php
get_footer();

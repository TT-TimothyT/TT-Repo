<?php
/**
 * The Template for displaying Archive pages.
 */

get_header();
?>

<main id="main" class="container">
	<div class="row">
		<div class="col-md-12 col-sm-12">

		<?php
		if ( have_posts() ) :
		?>
		<header class="page-header">
			<h1 class="page-title">
				<?php
					if ( is_day() ) :
						printf( esc_html__( 'Daily Archives: %s', 'trek-travel-theme' ), get_the_date() );
					elseif ( is_month() ) :
						printf( esc_html__( 'Monthly Archives: %s', 'trek-travel-theme' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'trek-travel-theme' ) ) );
					elseif ( is_year() ) :
						printf( esc_html__( 'Yearly Archives: %s', 'trek-travel-theme' ), get_the_date( _x( 'Y', 'yearly archives date format', 'trek-travel-theme' ) ) );
					else :
						esc_html_e( 'Blog Archives', 'trek-travel-theme' );
					endif;
				?>
			</h1>
		</header>
		<?php
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

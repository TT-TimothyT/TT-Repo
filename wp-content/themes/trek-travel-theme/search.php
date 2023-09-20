<?php
/**
 * The Template for displaying Search Results pages.
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
				<h1 class="page-title"><?php printf( esc_html__( 'Search Results for: %s', 'trek-travel-theme' ), get_search_query() ); ?></h1>
			</header>
		<?php
			get_template_part( 'archive', 'loop' );
		else :
		?>
			<article id="post-0" class="post no-results not-found">
				<header class="entry-header">
					<h1 class="entry-title"><?php esc_html_e( 'Nothing Found', 'trek-travel-theme' ); ?></h1>
				</header><!-- /.entry-header -->
				<p><?php esc_html_e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'trek-travel-theme' ); ?></p>
				<?php
					get_search_form();
				?>
			</article><!-- /#post-0 -->
		<?php
		endif;
		wp_reset_postdata();
		?>

		</div><!-- /.col -->
	</div><!-- /.row -->
</main><!-- /#main -->

<?php
get_footer();

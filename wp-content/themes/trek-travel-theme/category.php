<?php
/**
 * The Template for displaying Category Archive pages.
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
				<h1 class="page-title"><?php printf( esc_html__( 'Category Archives: %s', 'trek-travel-theme' ), single_cat_title( '', false ) ); ?></h1>
				<?php
					$category_description = category_description();
					if ( ! empty( $category_description ) ) :
						echo apply_filters( 'category_archive_meta', '<div class="category-archive-meta">' . $category_description . '</div>' );
					endif;
				?>
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

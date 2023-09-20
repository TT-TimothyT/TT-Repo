<?php



/**
 * Template Name: Blog Index
 * Description: The template for displaying the Blog index /blog.
 *
 */

get_header();

$page_id = get_option( 'page_for_posts' );
?>
<main id="main" class="container">
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<div class="row">
				<div class="col-md-12">
					<?php
						echo apply_filters( 'the_content', get_post_field( 'post_content', $page_id ) );

						edit_post_link( __( 'Edit', 'trek-travel-theme' ), '<span class="edit-link">', '</span>', $page_id );
					?>
				</div><!-- /.col -->
				<div class="col-md-12">
					<?php
						get_template_part( 'archive', 'loop' );
					?>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</main><!-- /#main -->
<?php
get_footer();

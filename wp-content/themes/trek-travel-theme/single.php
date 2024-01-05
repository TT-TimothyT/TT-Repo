<?php
/**
 * The Template for displaying all single posts.
 */

get_header();
?>

<main id="main" class="container">
	<div class="row">
		<div class="col-md-12 col-sm-12">

		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				get_template_part( 'content', 'single' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
			endwhile;
		endif;

		wp_reset_postdata();

		$count_posts = wp_count_posts();

		if ( $count_posts->publish > '1' ) :
			$next_post = get_next_post();
			$prev_post = get_previous_post();
		?>
		<hr class="mt-5">
		<div class="post-navigation d-flex justify-content-between">
			<?php
				if ( $prev_post ) {
					$prev_title = get_the_title( $prev_post->ID );
			?>
				<div class="pr-3">
					<a class="previous-post btn btn-lg btn-outline-secondary" href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" title="<?php echo esc_attr( $prev_title ); ?>">
						<span class="arrow">&larr;</span>
						<span class="title"><?php echo wp_kses_post( $prev_title ); ?></span>
					</a>
				</div>
			<?php
				}
				if ( $next_post ) {
					$next_title = get_the_title( $next_post->ID );
			?>
				<div class="pl-3">
					<a class="next-post btn btn-lg btn-outline-secondary" href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" title="<?php echo esc_attr( $next_title ); ?>">
						<span class="title"><?php echo wp_kses_post( $next_title ); ?></span>
						<span class="arrow">&rarr;</span>
					</a>
				</div>
			<?php
				}
			?>
		</div><!-- /.post-navigation -->
		<div class="share-post d-flex justify-content-center gap-4 my-5">
			<p class="fw-normal fs-xl lh-xl">Share:</p>
			<p class="fw-normal fs-xl lh-xl"><a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" target="_blank" rel="noopener noreferrer"><img alt="facebook" src="<?php echo get_template_directory_uri(); ?>/assets/images/social/facebook.png" /></a></p>
			<p class="fw-normal fs-xl lh-xl"><a href="https://twitter.com/intent/tweet?url=<?php the_permalink(); ?>&text=<?php the_title(); ?>" target="_blank" rel="noopener noreferrer"><img alt="twitter" src="<?php echo get_template_directory_uri(); ?>/assets/images/social/twitter.png" /></a></p>
			<p class="fw-normal fs-xl lh-xl"><a href="https://pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>&description=<?php the_title(); ?>" target="_blank" rel="noopener noreferrer"><img alt="pinterest" src="<?php echo get_template_directory_uri(); ?>/assets/images/social/pinterest.png" /></a></p>
			<p class="fw-normal fs-xl lh-xl"><a href="mailto:?subject=<?php the_title(); ?>&body=<?php the_permalink(); ?>"><img alt="mail" src="<?php echo get_template_directory_uri(); ?>/assets/images/social/mail.png" /></a></p>
		</div>
	
		<?php
		endif;
		?>

		</div><!-- /.col -->
	</div><!-- /.row -->
</main><!-- /#main -->

<?php
get_footer();

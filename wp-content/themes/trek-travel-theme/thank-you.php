<?php

/**
 * Template Name: Thank you page
 * Description: Page template.
 *
 */

get_header();

$search_enabled = get_theme_mod('search_enabled', '1'); // Get custom meta-value.
$thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full'); // Get thumbnail URL.

?>
<main id="main" class="thank-you-page">
	<?php if( ! empty( $thumbnail_url ) ) { ?>
		<div class="col-md-12 col-sm-12">
			<div class="thankyou-hero">
				<img src="<?php echo $thumbnail_url; ?>" alt="">
				<?php if( ! empty( get_the_title() ) ) { ?>
					<div class="page-title">
						<h1 class="thankyou-title"><?php echo get_the_title(); ?></h1>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	<div class="breadcrumb-container">
		<div class="col-md-12 col-sm-12">
			<nav class="mb-0" aria-label="breadcrumb">
				<ol class="breadcrumb mb-1">
					<li class="breadcrumb-item fs-sm"><a href="<?php echo get_site_url(); ?>">Home</a></li>
					<li class="breadcrumb-item active fs-sm" aria-current="page"><?php echo get_the_title(); ?></li>
				</ol>
			</nav>
		</div>
	</div>
	<?php if( ! empty( get_the_content() ) ) { ?>
		<div class="thank-you-info">
			<div class="container">
				<div class="row">
					<div class="col-md-12 col-sm-12">
						<?php echo apply_filters( 'the_content', get_the_content() ); ?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</main>
<?php
get_footer();

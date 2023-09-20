<?php
/**
 * Template Name: Blog Index(Listing - Load more)
 * Description: The template for displaying the Blog index /blog.
 *
 */
get_header();
$page_id = get_option( 'page_for_posts' );
$featured_blog_html = '';
$featured_args = array(
	'post_type' => 'post',
	'posts_per_page' => 1,
	'post_status' => 'publish',
	'orderby' => 'date',
  	'order' => 'DESC',
);
$featured_blog = new WP_Query($featured_args);
if( $featured_blog->have_posts() ){
	while($featured_blog->have_posts()){
		$featured_blog->the_post();
		$featured_Image = '';
		if( has_post_thumbnail(get_the_ID()) ){
			$featured_Image = get_the_post_thumbnail_url(get_the_ID(), 'full');
			if (isset($featured_Image) && !empty($featured_Image)) {
				# code...
				break;
			}
		}
	}
		$featured_blog_html .= '<div class="featured">
		<img src="'.$featured_Image.'">
		<div class="info">
			<p class="fw-normal fs-sm lh-sm">'.get_the_date('F Y').'</p>
			<h5 class="fw-semibold">'.get_the_title().'</h5>
			<p class="fw-normal fs-md lh-md info-description">'.substr(get_the_excerpt(), 0, 100).'...</p>
			<a href="'.get_the_permalink().'" class="btn btn-secondary btn-sm btn-outline-dark rounded-1">Learn more</a>
		</div>
	</div>';
	wp_reset_postdata();
}
//Blog list posts
$other_blog_html = '';
$blog_args = array(
	'post_type' => 'post',
	'posts_per_page' => 1,
	'post_status' => 'publish',
	'orderby' => 'date',
  	'order' => 'DESC',
	'paged' => 1
);
$blogs = new WP_Query($blog_args);
if( $blogs->have_posts() ){
	while($blogs->have_posts()){
		$blogs->the_post();
		$featured_Image = '';
		if( has_post_thumbnail(get_the_ID()) ){
			$featured_Image = get_the_post_thumbnail_url(get_the_ID(), 'full');
		}
		$other_blog_html .= '<div class="list-item">
			<div class="image">
			<img src="'.$featured_Image.'">				
			</div>
			<p class="fw-normal fs-sm lh-sm">'.get_the_date('F Y').'</p>
			<p class="fw-bold fs-xl lh-xl">'.get_the_title().'</p>
			<p class="fw-normal fs-md lh-md">'.substr(get_the_excerpt(), 0, 150).'...</p>
					
		</div>';
	}
	wp_reset_postdata();
}
?>
<div class="container-fluid blog-listing">
	<div class="header">
		<h1 class="fw-semibold">Trek Travel Blog</h1>
		<p class="fw-normal fs-lg lh-lg">Read stories from around the world</p>
	</div>
	<?php echo $featured_blog_html;?>
	
	<div class="blog-list-appendTo list d-flex flex-column flex-lg-row flex-nowrap flex-lg-wrap">
		<?php echo $other_blog_html; ?>
	</div>
	<div class="more d-flex">
		<button class="btn btn-primary btn-lg rounded-1 view-more-btn" id="load-more">View more</button>
	</div>
</div>
<?php
get_footer();

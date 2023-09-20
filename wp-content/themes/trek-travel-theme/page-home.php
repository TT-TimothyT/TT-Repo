<?php
/**
 * Template Name: Home Page (Elementor)
 * Description: Home page to be built with Elementor.
 *
 */

get_header();

the_post();
?>

<div id="main">

	<?php
	// Elementor Content
	the_content();
	?>

</div>

<?php
get_footer();

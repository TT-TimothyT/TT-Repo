<?php
// Inclusion Section Template Part

// Inclusion Section
$inclusion_header = get_field('inclusion_header');
$inclusion_image = get_field('inclusion_image');
$inclusion_content = get_field('inclusion_content');

?>

<?php 
// if (!empty($inclusion_header) || !empty($inclusion_image) || !empty($inclusion_content)): 
    ?>
    <section class="inclusion-sect">
        <div class="container">
            <div class="row">
                <h3 class="h2 text-center fw-semibold inc-header"><?php echo esc_html($inclusion_header); ?></h2>
            <?php if (!empty($inclusion_image)): ?>
                <div class="inc-img col-12 col-xl-5 col-xxl-4 offset-xl-1" style="background-image: url(<?php echo $inclusion_image['url'];?>)">

                
               
                </div>
            <?php endif; ?>
                <div class="col-12 col-xl-4  offset-xl-1">
                    <div class="wysiwyg inc-content">
                    <?php echo $inclusion_content; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php 
// endif; 
?>


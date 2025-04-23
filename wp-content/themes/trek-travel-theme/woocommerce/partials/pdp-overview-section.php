<?php
defined( 'ABSPATH' ) || exit;

global $product;
$attachment_ids = $product->get_gallery_image_ids();
if(count($attachment_ids) > 4){
	$attachment_ids = array_slice($attachment_ids, 4);
}

$message_icon = get_field('message_icon'); // Font Awesome field
$message_text = get_field('message_text'); // WYSIWYG editor field


if(!empty(get_the_excerpt()) && !empty(get_the_content())) {

?>



<div class="mobile-share-wishlist desktop-hideme">
    <div class="overview-icons d-flex">
        <button type="button" class="btn btn-outline-dark share-link">
            <i class="bi bi-link-45deg"></i>
        </button>
        <div class="toast bg-white link-copied align-items-center w-auto start-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Link copied
                    <i class="bi bi-x-lg align-self-center" data-bs-dismiss="toast" aria-label="Close"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container pdp-section overview-section-container" id="overview">
    <div class="row">
        <div class="col-12">

            <?php
            // Global Fields (from ACF Options Page)
            $global_icon    = get_field('global_message_icon', 'option');
            $g_msg_border = get_field('global_message_border_color', 'option');
            $global_message = get_field('global_message_text', 'option');

            // Product-Specific Fields
            $product_icon   = get_field('product_icon');
            $msg_border = get_field('message_border_color');
            $product_message = get_field('product_message');
            $replace_global = get_field('replace__add_product_message');
            ?>
            
            <?php
            // Replace Global (Show Only Product Icon & Message)
            if (!empty($replace_global)) {
                if (!empty($product_message)) {
            ?>
                <div class="message-container" <?php if(!empty($msg_border)) { ?> style="border-color:<?php echo $msg_border; }?>">
                    <?php if(!empty($product_icon)){ ?>
                        <i class="<?php echo $product_icon; ?>"></i>
                    <?php } ?>
                    <div class="message-text">
                        <?php echo $product_message; ?>
                    </div>
                </div>
            <?php 
                }
            } else {
                if (!empty($global_message)) {
                ?>
                <div class="message-container"<?php if(!empty($g_msg_border)) { ?> style="border-color:<?php echo $g_msg_border; }?>">
                    <?php if(!empty($global_icon)){ ?>
                        <i class="<?php echo $global_icon; ?>"></i>
                    <?php } ?>
                    <div class="message-text">
                        <?php echo $global_message; ?>
                    </div>
                </div>
                <?php
                }
                if (!empty($product_message)) {
                    ?>
                    <div class="message-container"<?php if(!empty($msg_border)) { ?> style="border-color:<?php echo $msg_border; }?>">
                        <?php if(!empty($product_icon)){ ?>
                            <i class="<?php echo $product_icon; ?>"></i>
                        <?php } ?>
                        <div class="message-text">
                            <?php echo $product_message; ?>
                        </div>
                    </div>       
            <?php 
                }
            }
            ?>

            <?php 
            if(get_the_excerpt()):
                $short_description =  get_the_excerpt();
            ?>
                <h3 class="fw-semibold pdp-section__title"><?php echo $short_description; ?></h3>
            <?php endif; ?>
            <?php 
            if(get_the_content()):
                $description =  get_the_content();
            ?>
                <p class="fw-normal fs-md lh-md"><?php echo $description; ?></p>
            <?php endif; ?>

            <?php if(!empty($attachment_ids)) : ?>
                <div class="product-slider overview-section-gallery">
                    <!-- Carousel -->
                    <div id="overview-gallery" class="carousel slide" data-bs-ride="carousel">
                        <!-- Indicators/dots -->
                        <div class="carousel-indicators">
                            <?php 
                            $j=0;
                            foreach( $attachment_ids as $attachment_id ) : ?>
                                <button type="button" data-bs-target="#overview-gallery" data-bs-slide-to="<?php echo $j;?>" class="<?php echo ($j == 0 ? 'active' : ''); ?>"></button>
                            <?php 
                            $j++;
                            endforeach; ?>
                        </div>
                        <!-- The slideshow/carousel -->
                        <div class="carousel-inner">
                            <?php 
                            $i=0;
                            foreach( $attachment_ids as $attachment_id ) :
                                $image_link =wp_get_attachment_url( $attachment_id );
                            ?>
                                <div class="carousel-item <?php echo ($i == 0 ? 'active' : ''); ?>">
                                    <img src="<?php echo $image_link;?>" alt="slide 2" class="d-block" style="width:100%">
                                </div>
                            <?php 
                            $i++;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                    <!-- Left and right controls/icons -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#overview-gallery" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#overview-gallery" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php } ?>
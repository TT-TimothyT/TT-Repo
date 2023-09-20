<?php $wishlist = new WC_Wishlists_Wishlist( $_GET['wlid'] ); ?>

<?php
$current_owner_key = WC_Wishlists_User::get_wishlist_key();
$sharing           = $wishlist->get_wishlist_sharing();
$sharing_key       = $wishlist->get_wishlist_sharing_key();
$wl_owner          = $wishlist->get_wishlist_owner();
$userInfo = wp_get_current_user();
$notifications = get_post_meta( $wishlist->id, '_wishlist_owner_notifications', true );
if ( empty( $notifications ) ) {
	$notifications = 'yes';
}
$wishlist_items = WC_Wishlists_Wishlist_Item_Collection::get_items( $wishlist->id, true );
$treat_as_registry = false;
?>

<?php
if ( $wl_owner != WC_Wishlists_User::get_wishlist_key() && !current_user_can( 'manage_woocommerce' ) ) :

	die();

endif;
?>

<?php do_action( 'woocommerce_wishlists_before_wrapper' ); ?>
<div id="wl-wrapper" class="product woocommerce"> <!-- product class so woocommerce stuff gets applied in tabs -->

    <div class="container account-wishlist-edit my-4">
        <div class="row mx-0 flex-column flex-lg-row">
            <div class="col-lg-6 medical-information__back order-1 order-lg-0">
                <a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
            </div>
            <div class="col-lg-6 d-flex dashboard__log">
                <p class="fs-lg lh-lg fw-bold">Hi <?php echo $userInfo->first_name; ?>!</p>
                <a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
            </div>
        </div>
        <div id="account-wishlist-responses"></div>
        <div class="row mx-0">
            <div class="col-lg-12">
                <h3 class="account-wishlist__title fw-semibold">Wishlist</h3>
            </div>
        </div>

        <!-- new loop start -->
        
        <div class="row mx-0">
            <?php if ( sizeof( $wishlist_items ) > 0 ) : ?>
                <form action="<?php $wishlist->the_url_edit(); ?>" method="post" class="wl-form" id="wl-items-form">
                    <input type="hidden" name="wlid" value="<?php echo $wishlist->id; ?>"/>
                    <?php WC_Wishlists_Plugin::nonce_field( 'manage-list' ); ?>
                    <?php echo WC_Wishlists_Plugin::action_field( 'manage-list' ); ?>
                    <input type="hidden" name="wlmovetarget" id="wlmovetarget" value="0"/>

                        <?php
                        foreach ( $wishlist_items as $wishlist_item_key => $item ) {
                            //$_product   = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $wishlist_item_key );
                            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $item['product_id'], $item, $wishlist_item_key );
                            $_product   = wc_get_product( $item['data'] );
                            
                            if ( $_product->exists() && $item['quantity'] > 0 ) {
                                $attachment_ids = $_product->get_gallery_image_ids();
                                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $item ) : '', $item, $wishlist_item_key );
                                $tripRegion = tt_get_local_trips_detail('tripRegion', '', $_product->get_sku(), true);
                                $parent_product_id = $pa_city = '';
                                $parent_product_id = tt_get_parent_trip_id_by_child_sku($_product->get_sku());
                                $pa_city = '';
                                if( $parent_product_id ){
                                    $p_product = wc_get_product( $parent_product_id );
                                    $product = wc_get_product( $parent_product_id );
                                    $pa_city = $p_product->get_attribute( 'pa_city' );
                                }
                            ?>

                            <div class="card mt-4 border-0">
                                <div class="row m-0 g-0">
                                    <div class="col-md-5">
                                        <div id="carouselExampleIndicators<?php echo $wishlist_item_key; ?>" class="carousel slide h-100" data-bs-ride="carousel">
                                            <div class="carousel-indicators">
                                                <?php 
                                                $j=0;
                                                foreach( $attachment_ids as $attachment_id ) : ?>
                                                    <button type="button" data-bs-target="#carouselExampleIndicators<?php echo $wishlist_item_key; ?>" data-bs-slide-to="<?php echo $j;?>" class="<?php echo ($j == 0 ? 'active' : ''); ?>"></button>
                                                <?php 
                                                $j++;
                                                endforeach; ?>
                                            </div>
                                            <div class="carousel-inner h-100">
                                                <?php 
                                                $i=0;
                                                foreach( $attachment_ids as $attachment_id ) :
                                                    $image_link =wp_get_attachment_url( $attachment_id );
                                                ?>                                                            
                                                <div class="carousel-item h-100 <?php echo ($i == 0 ? 'active' : ''); ?>">
                                                    <a href="<?php echo $product_permalink; ?>" title="" class="">
                                                        <img width="522" height="389" src="<?php echo $image_link;?>" alt="" title="" class="d-block" />
                                                    </a>
                                                </div>
                                                <?php 
                                                $i++;
                                                endforeach; 
                                                ?>
                                            </div>
                                            <button class="carousel-control-prev mobile-hideme" type="button" data-bs-target="#carouselExampleIndicators<?php echo $wishlist_item_key; ?>" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next mobile-hideme" type="button" data-bs-target="#carouselExampleIndicators<?php echo $wishlist_item_key; ?>" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                            <span class="wishlist-item-remove">
                                                <a rel="nofollow"
                                                href="<?php echo woocommerce_wishlist_url_item_remove( $wishlist->id, $wishlist_item_key ); ?>"
                                                class="wlconfirm w-100 h-100 d-block"
                                                title="<?php _e( 'Remove this item from your wishlist', 'wc_wishlist' ); ?>"
                                                data-message="<?php esc_attr( _e( 'Are you sure you would like to remove this item from your list?', 'wc_wishlist' ) ); ?>">
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-5 d-lg-flex flex-lg-column">
                                        <div class="card-body ms-md-1">                                            
                                            <span class="badge bg-dark">Featured</span>
                                            <p class="mb-0">
                                                <small class="text-muted">
                                                  <?php 
                                                  $city = $region = "";
                                                      if( $_product ){
                                                        $city = $_product->get_attribute('pa_city');
                                                        $region = $_product->get_attribute('pa_region');
                                                      }
                                                      if ($city && $region) {
                                                          echo $city . ' ' . ',' . ' ' . $region;
                                                      } elseif (!$city) {
                                                          echo $region;
                                                      } elseif(!$region) {
                                                          echo $city;
                                                      }
                                                  ?>  
                                                </small>
                                            </p>
                                            <?php
                                            if ( !$product_permalink ) {
                                                $productTitle = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $item, $wishlist_item_key ) . '&nbsp;';
                                            } else {
                                                $productTitle = apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $item, $wishlist_item_key );
                                            }
                                            ?>
                                            <a href="{{ data.permalink }}" title="{{ data.post_title }}"  class="ais-hits--title-link text-decoration-none" itemprop="url">
                                                <h4 class="card-title fw-semibold"><?php echo $productTitle; ?></h4>
                                            </a>
                                            <p><small><?php echo $_product->short_description ?></small></p>                                            
                                            <ul class="list-inline mb-0">
                                                <li class="list-inline-item"><i class="bi bi-bicycle"></i></li>
                                                <li class="list-inline-item fs-sm listValue"><?php echo $_product->get_attribute( 'pa_trip-style' ); ?></li>
                                                <li class="list-inline-item mobile-hideme"><i class="bi bi-info-circle pdp-trip-styles"></i></li>
                                            </ul>                                            
                                            <ul class="list-inline mb-0">
                                                <li class="list-inline-item"><i class="bi bi-calendar"></i></li>
                                                <li class="list-inline-item fs-sm listValue"><?php echo $_product->get_attribute( 'pa_duration' ); ?></li>
                                                <li class="list-inline-item mobile-hideme"></li>
                                            </ul>                                            
                                            <ul class="list-inline mb-0">
                                                <li class="list-inline-item"><i class="bi bi-briefcase"></i></li>
                                                <li class="list-inline-item fs-sm listValue"><?php echo $_product->get_attribute( 'pa_trip-style' ); ?></li>
                                                <li class="list-inline-item mobile-hideme"><i class="bi bi-info-circle pdp-rider-level"></i></li>
                                            </ul>                                            
                                            <ul class="list-inline mb-0">
                                                <li class="list-inline-item"><i class="bi bi-house"></i></li>
                                                <li class="list-inline-item fs-sm listValue"><?php echo $_product->get_attribute( 'pa_hotel-level' ); ?></li>
                                                <li class="list-inline-item mobile-hideme"><i class="bi bi-info-circle pdp-hotel-levels"></i></li>
                                            </ul>
                                        </div>
                                         <div style="display: none;" class="yotpo yotpo-main-widget fw-semibold" data-product-id="<?php echo $product_id; ?>"></div>
                                        <p class="card-footer bg-transparent border-0 ms-md-1">
                                            <span class="fw-semibold"><i class="bi bi-star"></i>
                                            <span id="displayed-average-score-<?php echo $wishlist_item_key; ?>"></span>
                                            </span>
                                            <span id="percentage-value-<?php echo $wishlist_item_key; ?>" class="text-muted review-text"></span>
                                        </p>
                                        <script type="text/javascript">
                                            function updateAverageScore_<?php echo $wishlist_item_key; ?>() {
                                                var yotpoReviewSection = document.querySelector(".yotpo[data-product-id='<?php echo $product_id; ?>']");
                                                
                                                if (yotpoReviewSection) {
                                                    var averageScoreElement = yotpoReviewSection.querySelector(".yotpo-regular-box .avg-score");
                                                    var averageScoreValue = averageScoreElement ? parseFloat(averageScoreElement.textContent.trim()) : null;

                                                    var displayedAverageScore = document.getElementById("displayed-average-score-<?php echo $wishlist_item_key; ?>");
                                                    var percentage = calculatePercentage(averageScoreValue);
                                                    var percentageDiv = document.getElementById("percentage-value-<?php echo $wishlist_item_key; ?>");
                                                    var formattedAverageScore = averageScoreValue ? averageScoreValue.toFixed(1) : '0';
                                                    displayedAverageScore.textContent = " " + formattedAverageScore + " / 5";
                                                    percentageDiv.textContent = " " + percentage + "% Would Recommend";
                                                }
                                            }

                                            function calculatePercentage(score) {
                                                if (score === null || score === ' ') {
                                                    return '0';
                                                }

                                                // Calculate the percentage based on your scoring system
                                                var maxScore = 5; // Change this to the maximum score in your system
                                                var percentage = (score / maxScore) * 100;
                                                return percentage.toFixed(1);
                                            }

                                            // Load the rest of the page content after displaying the value
                                            window.addEventListener("load", function() {
                                                updateAverageScore_<?php echo $wishlist_item_key; ?>();
                                            });
                                        </script>
                                    </div>
                                    <div class="col-md-2 position-relative pricing-section">                                        
                                        <div class="card-body mt-5 pricing">
                                            <?php
                                            if ( WC_Wishlist_Compatibility::is_wc_version_gte_2_1() ) {
                                                $price = WC()->cart->get_product_price( $item['data'] );
                                                $price = apply_filters( 'woocommerce_cart_item_price', $price, $item, $wishlist_item_key );
                                            } else {
                                                $product_price = ( get_option( 'woocommerce_display_cart_prices_excluding_tax' ) == 'yes' ) ? wc_get_price_excluding_tax( $_product ) : $_product->get_price();
                                                $price         = apply_filters( 'woocommerce_cart_item_price_html', wc_price( $product_price ), $item, $wishlist_item_key );
                                            }
                                            ?>
                                            <small class="text-muted">Starting from</small>
                                            <h4><?php echo apply_filters( 'woocommerce_wishlist_list_item_price', $price, $item, $wishlist ); ?><span class="fw-normal fs-sm lh-sm">pp</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <!-- <hr class="card-divider" /> -->
                            </div>
                            <!-- ------------------------------------------- -->
                                <?php
                            }
                        }
                        ?>
                </form>

            <?php else : ?>
                <?php $shop_url = get_permalink( wc_get_page_id( 'shop' ) ); ?>
                <p class="no-item-text fw-normal fs-xl lh-xl">You have 0 items in your wishlist. View our trips to start adding to your list now. </p>
                <a class="btn btn-primary rounded-1 view-all-trips-btn" href="<?php echo $shop_url; ?>"><?php _e( 'View all trips', 'wc_wishlist' ); ?></a>.

            <?php endif; ?>
        </div>
        
        <!-- new loop end -->
             
    </div><!-- container account-wishlist my-4 -->

	<?php woocommerce_wishlists_get_template( 'wishlist-email-form.php', array( 'wishlist' => $wishlist ) ); ?>
</div><!-- wl-wrapper -->
<?php do_action( 'woocommerce_wishlists_after_wrapper' ); ?>
<script>
    yotpo.initWidgets();
    jQuery(document).ready(function () {
        jQuery('body').removeClass('elementor-kit-14');
    })
</script>

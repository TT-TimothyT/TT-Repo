<?php do_action( 'woocommerce_wishlists_before_wrapper' );
$userInfo = wp_get_current_user();
 ?>
<div id="wl-wrapper" class="woocommerce">

    <div class="container account-wishlist my-4">
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
                <h3 class="account-wishlist__title fw-semibold">Wishlists</h3>
            </div>
        </div>
        <div class="row mx-0">
            <div class="col-lg-10">
                <div class="card account-wishlist__card rounded-1">

                    <table class="table">
                        <thead>
                        <tr>
                            <th class="product-name"><?php _e( 'List Name', 'wc_wishlist' ); ?></th>
                            <th class="wl-date-added"><?php _e( 'Date Added', 'wc_wishlist' ); ?></th>
                            <th class="wl-privacy-col"><?php _e( 'Privacy Settings', 'wc_wishlist' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $lists = WC_Wishlists_User::get_wishlists(); ?>
                        <?php if ( $lists && count( $lists ) ) : ?>
                            <?php foreach ( $lists as $list ) : ?>
                                <?php $sharing = $list->get_wishlist_sharing(); ?>
                                <tr class="cart_table_item">
                                    <td class="product-name"  data-title="<?php _e( 'Product', 'wc_wishlist' ); ?>">
                                        <a href="<?php $list->the_url_edit(); ?>"><?php $list->the_title(); ?></a>
                                        <div class="row-actions"></div>
                                        <?php if ( $sharing == 'Public' || $sharing == 'Shared' ) : ?>
                                            <?php woocommerce_wishlists_get_template( 'wishlist-sharing-menu.php', array( 'id' => $list->id ) ); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="wl-date-added"  data-title="<?php _e( 'Date Added', 'wc_wishlist' ); ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $list->post->post_date ) ); ?></td>
                                    <td class="wl-privacy-col"  data-title="<?php _e( 'Privacy', 'wc_wishlist' ); ?>">
                                        <?php echo $list->get_wishlist_sharing( true ); ?>
                                    </td>
                                </tr>

                                <?php
                                //Registers the email form modal to be printed in the footer.
                                woocommerce_wishlists_get_template( 'wishlist-email-form.php', array( 'wishlist' => $list ) );
                                ?>
                            <?php endforeach; ?>
                            <tr>

                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

</div><!-- /wishlist-wrapper -->
<?php do_action( 'woocommerce_wishlists_after_wrapper' ); ?>

<?php
$currentScreen = get_current_screen();
if ($currentScreen->base == 'netsuitewc_page_tt-common-logs') {
    $tabName = 'Logs';
}elseif ($currentScreen->base == 'netsuitewc_page_tt-bookings') {
    $tabName = 'NS Bookings';
} elseif ($currentScreen->base == 'netsuitewc_page_tt-dev-tools') {
    $tabName = 'Dev Tools';
} else {
    $tabName = 'Sync';
}

// Check user role
$is_ecomm_admin = ttnsw_is_ecomm_user_data_admin();
$capability     = $is_ecomm_admin ? 'manage_guest_data' : 'manage_options';

?>
<div class="tt-admin-option-page">
    <div class="tt-layout">
        <div class="tt-layout_header">
            <div class="tt-layout__header-wrapper">
                <h1 class="tt-block-tag">TT WC<>NS &rtrif; <?php echo $tabName; ?></h1>
            </div>
        </div>
        <div id="ttnsw-admin-notice-ctr">
            <?php settings_errors( 'ttnsw-admin-notice' ); ?>
        </div>
        <div class="tt-contents">
            <div class="tt-tabs">
                <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                    <?php if ( current_user_can( $capability ) ) : ?>
                        <a href="<?php echo admin_url('admin.php?page=trek-travel-ns-wc'); ?>" class="nav-tab <?php echo ($currentScreen->base == 'toplevel_page_trek-travel-ns-wc' ? 'nav-tab-active' : ''); ?>">
                            <span class="dashicons dashicons-update-alt"></span>
                            <?php esc_html_e('Sync', 'trek-travel-ns-wc'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <a href="<?php echo admin_url('admin.php?page=tt-common-logs'); ?>" class="nav-tab <?php echo ($currentScreen->base == 'netsuitewc_page_tt-common-logs' ? 'nav-tab-active' : ''); ?>">
                            <span class="dashicons dashicons-list-view"></span>
                            <?php esc_html_e('Logs', 'trek-travel-ns-wc'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <a href="<?php echo admin_url('admin.php?page=tt-bookings'); ?>" class="nav-tab <?php echo ($currentScreen->base == 'netsuitewc_page_tt-bookings' ? 'nav-tab-active' : ''); ?>">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php esc_html_e('NS Bookings', 'trek-travel-ns-wc'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <a href="<?php echo admin_url('admin.php?page=tt-dev-tools'); ?>" class="nav-tab <?php echo ($currentScreen->base == 'netsuitewc_page_tt-dev-tools' ? 'nav-tab-active' : ''); ?>" style="float: right;">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php esc_html_e('Dev Tools', 'trek-travel-ns-wc'); ?>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
</div>
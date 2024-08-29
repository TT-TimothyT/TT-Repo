<?php
$currentScreen = get_current_screen();
if ($currentScreen->base == 'netsuitewc_page_tt-common-logs') {
    $tabName = 'Logs';
}elseif ($currentScreen->base == 'netsuitewc_page_tt-bookings') {
    $tabName = 'NS Bookings';
} else {
    $tabName = 'Sync';
}
?>
<div class="tt-admin-option-page">
    <div class="tt-layout">
        <div class="tt-layout_header">
            <div class="tt-layout__header-wrapper">
                <h1 class="tt-block-tag">TT WC<>NS &rtrif; <?php echo $tabName; ?></h1>
            </div>
        </div>
        <?php settings_errors( 'ttnsw-admin-notice' ); ?>
        <div class="tt-contents">
            <div class="tt-tabs">
                <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                    <a href="<?php echo admin_url('admin.php?page=trek-travel-ns-wc'); ?>" class="nav-tab <?php echo ($currentScreen->base == 'toplevel_page_trek-travel-ns-wc' ? 'nav-tab-active' : ''); ?>">Sync</a>
                    <a href="<?php echo admin_url('admin.php?page=tt-common-logs'); ?>" class="nav-tab <?php echo ($currentScreen->base == 'netsuitewc_page_tt-common-logs' ? 'nav-tab-active' : ''); ?>">Logs</a>
                    <a href="<?php echo admin_url('admin.php?page=tt-bookings'); ?>" class="nav-tab <?php echo ($currentScreen->base == 'netsuitewc_page_tt-bookings' ? 'nav-tab-active' : ''); ?>">NS Bookings</a>
                </nav>
            </div>
        </div>
    </div>
</div>
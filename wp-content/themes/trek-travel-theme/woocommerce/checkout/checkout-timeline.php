<?php
$tripInfo = tt_get_trip_pid_sku_from_cart();
$tripSpecificMessage = '';
$checkoutGenericMessage = '';
if( $tripInfo ){
    $trip_sku = isset($tripInfo['sku']) ? $tripInfo['sku'] : '';
    $ns_trip_Id = isset($tripInfo['ns_trip_Id']) ? $tripInfo['ns_trip_Id']  : '';
    if( $trip_sku && $ns_trip_Id ){
        $tripSpecificMessage = tt_get_local_trips_detail('tripSpecificMessage', $ns_trip_Id, $trip_sku, true);
    }
}
$checkoutGenericMessage = get_field('checkout_generic_message', 'option'); 
?>
<div class="checkout-timeline">
    <div class="checkout-timeline__progress-bar" id="progress-bar">
        <ul class="d-flex align-items-center justify-content-between">
            <li class="nav-item guest-info <?php if (isset($_GET['step']) && $_GET['step'] == 1) echo 'active'; ?>" data-step-id="guest" data-step="1">
                <a class="nav-link" href="<?php echo trek_checkout_step_link(1); ?>" data-toggle="tab"></a>
            </li>
            <li class="nav-item rooms-gear <?php if (isset($_GET['step']) && $_GET['step'] == 2) echo 'active'; ?>" data-step-id="rooms" data-step="2">
                <a class="nav-link" href="<?php echo trek_checkout_step_link(2); ?>" data-toggle="tab"></a>
            </li>
            <li class="nav-item payment <?php if (isset($_GET['step']) && $_GET['step'] == 3) echo 'active'; ?>" data-step-id="trip-payment" data-step="3">
                <a class="nav-link" href="<?php echo trek_checkout_step_link(3); ?>" data-toggle="tab"></a>
            </li>
            <li class="nav-item review <?php if (isset($_GET['step']) && $_GET['step'] == 4) echo 'active'; ?>" data-step-id="review" data-step="4">
                <a class="nav-link" href="<?php echo trek_checkout_step_link(4); ?>" data-toggle="tab"></a>
            </li>
        </ul>
        <div class="checkout-timeline__progress">
            <div class="checkout-timeline__progress-bar-line d-flex justify-content-center" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
    <?php if ( $checkoutGenericMessage ) :?>
    <div class="d-flex align-items-center checkout-timeline__info rounded-1 mb-3">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkout/checkout-info.png">
        <p class="mb-0 fs-sm lh-sm"><?php echo $checkoutGenericMessage; ?></p>
    </div>
    <?php endif; ?>
    <?php if( $tripSpecificMessage ) :  ?>
    <div class="d-flex align-items-center checkout-timeline__warning rounded-1">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkout/checkout-warning.png">
        <p class="mb-0 fs-sm lh-sm"><?php echo $tripSpecificMessage; ?></p>
    </div>
    <?php endif; ?>
</div>
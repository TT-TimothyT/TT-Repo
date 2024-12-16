<?php
/**
 * Template file for the guest insurance pop up modal.
 */

$trek_checkout_data      = get_trek_user_checkout_data();
$tt_posted               = tt_validate( $trek_checkout_data['posted'], [] );
$guests                  = tt_validate( $tt_posted['guests'], [] );
$guest_insurance         = tt_validate( $tt_posted['trek_guest_insurance'], [] );
$base_premium_pr         = tt_validate( $guest_insurance['primary']['basePremium'], 0 );
$is_travel_protection_pr = $guest_insurance['primary']['is_travel_protection'];
$shipping_fname          = tt_validate( $tt_posted['shipping_first_name'] );
$shipping_lname          = tt_validate( $tt_posted['shipping_last_name'] );
$primary_name            = esc_html( $shipping_fname . ' ' . $shipping_lname );
$is_protection_modal_showed = (bool) tt_validate( $tt_posted['is_protection_modal_showed'], false );

// Primary Guest HTML.
?>

<div class="modal-body__guest">
    <p class="mb-4 fw-medium"><?php printf( esc_html__( 'Primary Guest: %s', 'trek-travel-theme' ), $primary_name ); ?></p>
    <div class="d-flex align-items-center mb-4">
        <input id="trek_guest_insurance_pr_add" type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="1" <?php echo esc_html( $is_travel_protection_pr != 0 ? 'checked' : '' ) ?>>
        <input type="hidden"  name="trek_guest_insurance[primary][basePremium]" value="<?php echo esc_attr( $base_premium_pr ) ?>">
        <label for="trek_guest_insurance_pr_add"><?php esc_html_e( 'Add Travel Protection', 'trek-travel-theme' ); ?> <span class="fw-bold">(<?php echo wc_price( floatval( esc_attr( $base_premium_pr ) ) ); ?>)</span></label>
    </div>
    <div class="d-flex align-items-center">
        <input id="trek_guest_insurance_pr_decline" type="radio" class="guest_radio" name="trek_guest_insurance[primary][is_travel_protection]" value="0" <?php echo esc_html( $is_travel_protection_pr == 0 ? 'checked' : '' ); ?>>
        <label for="trek_guest_insurance_pr_decline"><?php esc_html_e( 'Decline Travel Protection', 'trek-travel-theme' ); ?></label>
    </div>
    <?php if( empty( $guest_insurance['primary']['basePremium'] ) ) : ?>
        <div class="invalid-feedback travel-protection-feedback" style="display:block;">
            <img class="invalid-icon">
            <?php esc_html_e( 'Something went wrong during the calculation of the Travel Protection amount. Please double-check date of birth and address from step one to ensure they are entered correctly, and try again.', 'trek-travel-theme' ); ?>
        </div>
    <?php endif; ?>
    <input type="hidden" name="is_protection_modal_showed" value="<?php echo esc_attr( $is_protection_modal_showed ); ?>">
</div>

<?php

if( $guests ) :
    foreach ( $guests as $guest_k => $guest ) :
        $base_premium_guest         = tt_validate( $guest_insurance['guests'][$guest_k]['basePremium'], 0 );
        $guest_fname                = tt_validate( $guest['guest_fname'] );
        $guest_lname                = tt_validate( $guest['guest_lname'] );
        $guest_full_name            = esc_html( $guest_fname . ' ' . $guest_lname );
        $is_travel_protection_guest = $guest_insurance['guests'][$guest_k]['is_travel_protection'];

        // Each Next Guest HTML.
        ?>
            <div class="modal-body__guest">
                <p class="mb-4 fw-medium"><?php printf( esc_html__( 'Guest: %s', 'trek-travel-theme' ), $guest_full_name ) ?></p>
                <div class="d-flex align-items-center mb-4">
                    <input id="trek_guest_insurance_radio_add_<?php echo esc_attr( $guest_k ); ?>" type="radio" class="guest_radio" name="trek_guest_insurance[guests][<?php echo esc_attr( $guest_k ); ?>][is_travel_protection]" value="1" <?php echo esc_html( $is_travel_protection_guest != 0 ? 'checked' : '' ); ?>>
                    <input type="hidden" name="trek_guest_insurance[guests][<?php echo esc_attr( $guest_k ); ?>][basePremium]" value="<?php echo esc_attr( $base_premium_guest ); ?>">
                    <label for="trek_guest_insurance_radio_add_<?php echo esc_attr( $guest_k ); ?>"><?php esc_html_e( 'Add Travel Protection', 'trek-travel-theme' ); ?> <span class="fw-bold">(<?php echo wc_price( floatval( esc_html( $base_premium_guest ) ) ); ?>)</span></label>
                </div>
                <div class="d-flex align-items-center">
                    <input id="trek_guest_insurance_radio_decline_<?php echo esc_attr( $guest_k ); ?>" type="radio" class="guest_radio" name="trek_guest_insurance[guests][<?php echo esc_attr( $guest_k ); ?>][is_travel_protection]" value="0" <?php echo esc_html( $is_travel_protection_guest == 0 ? 'checked' : '' ); ?>>
                    <label for="trek_guest_insurance_radio_decline_<?php echo esc_attr( $guest_k ); ?>"><?php esc_html_e( 'Decline Travel Protection', 'trek-travel-theme' ); ?></label>
                </div>
            </div>
            <?php if( empty( $guest_insurance["guests"][$guest_k]['basePremium'] ) ) : ?>
                <div class="invalid-feedback travel-protection-feedback" style="display:block;">
                    <img class="invalid-icon">
                    <?php esc_html_e( 'Something went wrong during the calculation of the Travel Protection amount. Please double-check date of birth and address from step one to ensure they are entered correctly, and try again.', 'trek-travel-theme' ); ?>
                </div>
            <?php endif; ?>
        <?php
    endforeach;
endif;

?>

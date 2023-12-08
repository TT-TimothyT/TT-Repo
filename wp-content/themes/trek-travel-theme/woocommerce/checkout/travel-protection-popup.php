<?php
/**
 * Template part for the "Travel Protection" Tooltip on the checkout page
 */

$travel_protection_popup_title = get_field( 'travel_protection_popup_title', 'option' ); 
$travel_protection_popup_info  = get_field( 'travel_protection_popup_info', 'option' ); 
?>
<?php if ( ! empty( $travel_protection_popup_title ) || ! empty( $travel_protection_popup_info ) ) : ?>
    <div class="travel-protection-tooltip-container">
        <div class="travel-protection-tooltip">
            <?php if ( ! empty( $travel_protection_popup_title ) ) : ?>
                <h3><?php echo esc_html( $travel_protection_popup_title ); ?></h3>
            <?php endif; ?>
            <?php if ( ! empty( $travel_protection_popup_info ) ) : ?>
                <div><?php echo $travel_protection_popup_info ; ?></div>
            <?php endif; ?>
            <span class="btn close-btn">OK</span>
        </div>
    </div>
<?php endif; ?>

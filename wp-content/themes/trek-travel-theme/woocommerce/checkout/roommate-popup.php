<?php
/**
 * Template part for the "Open to Roommate" Popup on the checkout page
 */

$roommate_popup_title = get_field( 'popup_title', 'option' ); 
$roommate_popup_info  = get_field( 'popup_info', 'option' ); 
?>
<?php if ( ! empty( $roommate_popup_title ) || ! empty( $roommate_popup_info ) ) : ?>
    <div class="open-to-roommate-popup-container">
        <div class="open-to-roommate-popup">
            <?php if ( ! empty( $roommate_popup_title ) ) : ?>
                <h3><?php echo esc_html( $roommate_popup_title ); ?></h3>
            <?php endif; ?>
            <?php if ( ! empty( $roommate_popup_info ) ) : ?>
                <div><?php echo $roommate_popup_info ; ?></div>
            <?php endif; ?>
            <span class="btn close-btn">OK</span>
        </div>
    </div>
<?php endif; ?>

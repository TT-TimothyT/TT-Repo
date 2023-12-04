<?php
/**
 * Template part for the Private Room Popup on the checkout page
 */

$private_double_popup_title = get_field( 'private_double_popup_title', 'option' ); 
$private_double_popup_info  = get_field( 'private_double_popup_info', 'option' ); 
?>
<?php if ( ! empty( $private_double_popup_title ) || ! empty( $private_double_popup_info ) ) : ?>
    <div class="private-popup-container">
        <div class="private-popup">
            <?php if ( ! empty( $private_double_popup_title ) ) : ?>
                <h3><?php echo esc_html( $private_double_popup_title ); ?></h3>
            <?php endif; ?>
            <?php if ( ! empty( $private_double_popup_info ) ) : ?>
                <div><?php echo $private_double_popup_info ; ?></div>
            <?php endif; ?>
            <span class="btn close-btn">OK</span>
        </div>
    </div>
<?php endif; ?>

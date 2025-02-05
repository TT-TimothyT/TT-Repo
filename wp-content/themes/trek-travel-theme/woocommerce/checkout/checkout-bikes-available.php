<?php
/**
 * Template file for the available bikes on the checkout page, step 3.
 */

$available_bikes = tt_get_local_bike_detail( tt_validate( $args['ns_trip_id'] ), tt_validate( $args['sku'] ) );

?>
<div class="checkout-bikes__bike-grid-guests checkout-bikes__bike-grid d-flex flex-column flex-lg-row flex-nowrap flex-lg-wrap">
<?php
if ( $available_bikes ) :
	$is_primary          = isset( $args['is_primary'] ) && is_bool( $args['is_primary'] ) ? $args['is_primary'] : true;
	$bike_upgrade_price  = get_post_meta( $args['product_id'], TT_WC_META_PREFIX . 'bikeUpgradePrice', true);
	$posted_bike_id      = is_numeric( $args['posted_bike_id'] ) ? (int) $args['posted_bike_id'] : '';
	$posted_bike_type_id = tt_validate( $args['posted_bike_type_id'] );
	$non_rider_bike_id   = 5257;
	$own_bike_id         = 5270;

	if( $is_primary ) {
		$bike_available_fields = array(
			'bike_type_wrp' => array(
				'data_selector' => 'tt_bike_selection_primary',
				'data_guest_id' => 0,
				'class'         => ''
			),
			'bike_type_id' => array(
				'id'   => 'tt_bike_selection_primary',
				'name' => 'bike_gears[primary][bikeTypeId]',
			),
			'bike_id' => array(
				'name' => 'bike_gears[primary][bikeId]',
			),
		);
	} else {
		$guest_num             = tt_validate( $args['guest_num'], 1 );

		$bike_available_fields = array(
			'bike_type_wrp' => array(
				'data_selector' => 'tt_bike_selection_guest_' . $guest_num,
				'data_guest_id' => $guest_num,
				'class'         => 'tt_bike_selection_guest_' . $guest_num
			),
			'bike_type_id' => array(
				'id'   => 'tt_bike_selection_guest_' . $guest_num,
				'name' => 'bike_gears[guests][' . $guest_num . '][bikeTypeId]',
			),
			'bike_id' => array(
				'name' => 'bike_gears[guests][' . $guest_num . '][bikeId]',
			),
		);
	}

	$bikes_model_id_in = array();

	foreach ( $available_bikes as $available_bike ) :
		$bike_model    = json_decode( $available_bike['bikeModel'], true );
		$bike_model_id = $bike_model['id'];
		if ( ! in_array( $bike_model_id, $bikes_model_id_in ) && $bike_model_id ) :
			$bike_type              = json_decode( $available_bike['bikeType'], true );
			$is_selected_bike_model = ( int ) $posted_bike_type_id === ( int ) $bike_model_id;
			$bike_posts = get_posts(
				array(
					'post_type' => 'bikes',
					'title'     => $bike_model['name'],
				)
			);
			if ( ! empty( $bike_posts ) && is_array( $bike_posts ) ) {
				$bike_post_id   = $bike_posts[0]->ID;
				$bike_image_id  = get_post_meta( $bike_post_id, 'bike_image', true );   
				$bike_image_src = wp_get_attachment_image_url( $bike_image_id, 'medium' );
			} else {
				$bike_image_src = get_template_directory_uri() . "/assets/images/bike-placehoder-image.png";
			}
			$bike_type_info = tt_ns_get_bike_type_info( $bike_model_id );
			?>
				<div class="checkout-bikes__bike bike_selectionElement<?php echo esc_attr(  $is_selected_bike_model ? ' ' . 'bike-selected' : '' ); echo esc_attr( ! empty( $bike_available_fields['bike_type_wrp']['class'] ) ? ' ' . $bike_available_fields['bike_type_wrp']['class'] : '' ); ?>" data-selector="<?php echo esc_attr( $bike_available_fields['bike_type_wrp']['data_selector'] ); ?>" data-id="<?php echo esc_attr( $bike_model_id ); ?>" data-guest-id="<?php echo esc_attr( $bike_available_fields['bike_type_wrp']['data_guest_id'] ); ?>" data-type-id="<?php echo esc_attr( $bike_type['id'] ) ?>">
					<input id="<?php echo esc_attr( $bike_available_fields['bike_type_id']['id'] . '_' . $bike_model_id ); ?>" name="<?php echo esc_attr( $bike_available_fields['bike_type_id']['name'] ); ?>" type="radio" value="<?php echo esc_attr( $bike_model_id ); ?>"<?php echo esc_attr( $own_bike_id !== $posted_bike_id && $non_rider_bike_id !== $posted_bike_id ? ' ' . 'required' : '' ); echo esc_attr( $is_selected_bike_model ? ' ' . 'checked' : '' ); ?>>
					<label for="<?php echo esc_attr( $bike_available_fields['bike_type_id']['id'] . '_' . $bike_model_id ); ?>">
						<div class="checkout-bikes__image d-flex justify-content-center align-content-center">
							<img src="<?php echo esc_url( $bike_image_src ); ?>" alt="<?php echo esc_attr( $available_bike['bikeDescr'] ); ?>">
							<span class="checkout-bikes__badge checkout-bikes__badge--ebike"><?php echo esc_html( $bike_type['name'] ); ?></span>
						</div>
						<div class="checkout-bikes__title d-flex justify-content-between">
							<p class="fw-medium fs-md lh-1"><?php echo esc_html( $bike_model['name'] ) ?></p>
							<span class="radio-selection<?php echo esc_attr( $is_selected_bike_model ? ' ' . 'checkout-bikes__selected-bike-icon' : ' ' . 'checkout-bikes__select-bike-icon' ); ?>"></span>
						</div>
						<?php if( $bike_type_info && isset( $bike_type_info['isBikeUpgrade'] ) && $bike_type_info['isBikeUpgrade'] == 1 ) : ?>
							<div class="d-flex ms-4">
								<p class="fw-normal fs-sm lh-sm"><?php esc_html_e( 'Limited Quantities Available!', 'trek-travel-theme' ); ?></p>
							</div>
							<div class="checkout-bikes__price-upgrade d-flex ms-4">
								<p class="fw-normal fs-sm lh-sm"><?php esc_html_e( 'Upgrade now', 'trek-travel-theme' ); ?></p>
								<p class="fw-bold fs-sm lh-sm">&nbsp;+<span class="amount"><span class="woocommerce-Price-currencySymbol"></span><?php echo esc_attr( $bike_upgrade_price );?></span></p>
							</div>
						<?php endif; ?>
					</label>
				</div>
			<?php
		endif;
		$bikes_model_id_in[] = $bike_model_id;
	endforeach;
	?>
		<!-- Selected Bike ID -->
		<input name="<?php echo esc_attr( $bike_available_fields['bike_id']['name'] ); ?>" type="hidden" value="<?php echo esc_attr( $posted_bike_id ) ?>" required>
		<?php if( $is_primary ) : ?>
			<!-- Bike Type ID preferences for primary guest only -->
			<input name="bike_gears[primary][bike_type_id_preferences]" type="hidden" value="">
			<?php
		endif;
else :
	?>
		<strong><?php esc_html_e( 'No bikes available!', 'trek-travel-theme' );?></strong>;
	<?php
endif;
?>
</div>

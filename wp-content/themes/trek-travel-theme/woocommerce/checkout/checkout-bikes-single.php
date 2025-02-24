<?php
/**
 * Template file for the bikes selection on the checkout page, step 3.
 *
 * @uses checkout-bikes-available.php template for the bike selecting grid box listing.
 */

$auto_sel_bm_id    = tt_get_auto_select_bike_model_id( $args );
if ( empty( $args['posted_bike_type_id'] ) && ! empty( $auto_sel_bm_id ) ) {
	// Set the auto selected bike model id.
	$args['posted_bike_type_id'] = $auto_sel_bm_id;
}
$is_primary        = isset( $args['is_primary'] ) && is_bool( $args['is_primary'] ) ? $args['is_primary'] : true;
$guest_name        = tt_validate( $args['guest_name'] );
$guest_num         = intval( tt_validate( $args['guest_num'], 1 ) );
$posted_bike_id    = is_numeric( $args['posted_bike_id'] ) ? (int) $args['posted_bike_id'] : '';
$bike_type_info    = tt_ns_get_bike_type_info( $args['posted_bike_type_id'] );
$is_bike_upgrade   = $bike_type_info && isset( $bike_type_info['isBikeUpgrade'] ) && $bike_type_info['isBikeUpgrade'] == 1 ? true : false;
$bike_arr          = tt_get_local_bike_detail( $args['ns_trip_id'], $args['sku'], $posted_bike_id );
$is_bike_available = $bike_arr && isset( $bike_arr[0]['available'] ) && (int) $bike_arr[0]['available'] > 0 ? true : false;
$non_rider_bike_id = 5257;
$own_bike_id       = 5270;

/**
 * The Jersey Style and Jersey Size selecting
 * Should Only displayed if the trip style is NOT Self-Guided, Discover, Ride Camp.
 * 
 * Old approach was with the category check of the parent product.
 * Now will use the Trip Style attribute first, after deploying the product slug changes,
 * need to change this to take the custom taxonomy for the trip style
 * instead of the product attribute.
 */
$should_show_jersey_select = true;

//Get the trip style object, if it matches one of the items from the array, then we hide the jersey options.
$trip_style                = json_decode( tt_get_local_trips_detail( 'subStyle', $args['ns_trip_id'], $args['sku'], true ) );

$trip_style_name           = $trip_style ? $trip_style->name : '';

// The trip sub-style includes either "Training", "Discover", or "Self-Guided" = hide jersey options.
$hide_jersey_for_arr       = array( 'Training', 'Discover', 'Self-Guided' );

if( in_array( $trip_style_name, $hide_jersey_for_arr ) ) {
	// Hide the Jersey options.
	$should_show_jersey_select = false;
}

/**
 * Fields settings.
 */
if( $is_primary ) {
	// Set specific fields settings for the primary guest.
	$bike_gears_fields = array(
		'guest_name'  => sprintf( __( 'Primary Guest: %s', 'trek-travel-theme' ), $guest_name ),
		'rider_level' => array(
			'id'           => 'floatingSelectRiderLevelPrimary',
			'name'         => 'bike_gears[primary][rider_level]',
			'data_type'    => 'tt_rider_level_primary',
			'options_html' => tt_items_select_options( 'syncRiderLevels', tt_validate( $args['rider_level'] ) )
		),
		'bike_selection_wrp' => array(
			'id' => 'tt_rider_level_primary'
		),
		'own_bike' => array(
			'id'           => 'myOwnBikeCheckboxPrimary',
			'name'         => 'bike_gears[primary][own_bike]',
			'data_type'    => 'tt_my_own_bike_primary',
			'data_type_bc' => 'tt_my_own_bike_primary',
			'checked'      => isset( $args['own_bike'] ) && 'yes' === $args['own_bike'] ? 'checked' : '',
			'style'        => $own_bike_id === $posted_bike_id ? 'display:none;' : 'display:block;'
		),
		'bike_size' => array(
			'id'               => 'bikeSizeSelectPrimary',
			'name'             => 'bike_gears[primary][bike_size]',
			'data_guest_index' => 0,
			'options_html'     => tt_get_bikes_by_trip_info( $args['ns_trip_id'], $args['sku'], tt_validate( $args['posted_bike_type_id'] ), tt_validate( $args['bike_size'] ), tt_validate( $args['posted_bike_type_id'] ), '', $args['selected_bikes_arr'] )
		),
		'rider_height' => array(
			'id'           => 'riderHeightSelectPrimary',
			'name'         => 'bike_gears[primary][rider_height]',
			'options_html' => tt_items_select_options( 'syncHeights', tt_validate( $args['rider_height'] ) )
		),
		'bike_pedal' => array(
			'id'           => 'bikePedalSelectPrimary',
			'name'         => 'bike_gears[primary][bike_pedal]',
			'options_html' => tt_items_select_options( 'syncPedals', tt_validate( $args['bike_pedal'] ) )
		),
		'helmet_size' => array(
			'id'           => 'helmetSizeSelectPrimary',
			'name'         => 'bike_gears[primary][helmet_size]',
			'options_html' => tt_items_select_options( 'syncHelmets', tt_validate( $args['helmet_size'] ) )
		),
		'jersey_style' => array(
			'id'               => 'jerseyStyleSelectPrimary',
			'name'             => 'bike_gears[primary][jersey_style]',
			'value'            => tt_validate( $args['jersey_style'] ),
			'data_guest_index' => 0,
		),
		'jersey_size' => array(
			'id'    => 'jerseySizeSelectPrimary',
			'name'  => 'bike_gears[primary][jersey_size]',
			'value' => tt_validate( $args['jersey_size'] )
		),
		'transportation_options' => array(
			'id'           => 'transportationOptionsSelectPrimary',
			'name'         => 'bike_gears[primary][transportation_options]',
			'options_html' => tt_items_select_options( 'syncTransportationOptions', tt_validate( $args['transportation_options'] ) ),
			'style'        => $own_bike_id === $posted_bike_id ? 'display:block;' : 'display:none;'
		),
		'type_of_bike' => array(
			'id'    => 'typeOfBikePrimary',
			'name'  => 'bike_gears[primary][type_of_bike]',
			'style' => $own_bike_id === $posted_bike_id ? 'display:block;' : 'display:none;',
			'value' => tt_validate( $args['type_of_bike'] )
 		),
	);
} else {
	// Set specific fields settings for the secondary guests.
	$bike_gears_fields = array(
		'guest_name'  => sprintf( __( 'Guest %d: %s', 'trek-travel-theme' ), $guest_num + 1, $guest_name ),
		'rider_level' => array(
			'id'           => 'floatingSelectRiderLevelGuest' . $guest_num,
			'name'         => 'bike_gears[guests][' . $guest_num . '][rider_level]',
			'data_type'    => 'tt_rider_level_guest_' . $guest_num,
			'options_html' => tt_items_select_options( 'syncRiderLevels', tt_validate( $args['rider_level'] ) )
		),
		'bike_selection_wrp' => array(
			'id' => 'tt_rider_level_guest_' . $guest_num
		),
		'own_bike' => array(
			'id'           => 'myOwnBikeCheckboxGuest' . $guest_num,
			'name'         => 'bike_gears[guests][' . $guest_num . '][own_bike]',
			'data_type'    => 'tt_my_own_bike_guest_' . $guest_num,
			'data_type_bc' => 'tt_my_own_bike_guest_' . $guest_num,
			'checked'      => isset( $args['own_bike'] ) && 'yes' === $args['own_bike'] ? 'checked' : '',
			'style'        => $own_bike_id === $posted_bike_id ? 'display:none;' : 'display:block;'
		),
		'bike_size' => array(
			'id'               => 'bikeSizeSelectGuest' . $guest_num,
			'name'             => 'bike_gears[guests][' .  $guest_num . '][bike_size]',
			'data_guest_index' => $guest_num,
			'options_html'     => tt_get_bikes_by_trip_info( $args['ns_trip_id'], $args['sku'], tt_validate( $args['posted_bike_type_id'] ), tt_validate( $args['bike_size'] ), tt_validate( $args['posted_bike_type_id'] ), '', $args['selected_bikes_arr'] )
		),
		'rider_height' => array(
			'id'           => 'riderHeightSelectGuest' . $guest_num,
			'name'         => 'bike_gears[guests][' . $guest_num . '][rider_height]',
			'options_html' => tt_items_select_options( 'syncHeights', tt_validate( $args['rider_height'] ) )
		),
		'bike_pedal' => array(
			'id'           => 'bikePedalSelectGuest' . $guest_num,
			'name'         => 'bike_gears[guests][' . $guest_num . '][bike_pedal]',
			'options_html' => tt_items_select_options( 'syncPedals', tt_validate( $args['bike_pedal'] ) )
		),
		'helmet_size' => array(
			'id'           => 'helmetSizeSelectGuest' . $guest_num,
			'name'         => 'bike_gears[guests][' . $guest_num . '][helmet_size]',
			'options_html' => tt_items_select_options('syncHelmets', tt_validate( $args['helmet_size'] ) )
		),
		'jersey_style' => array(
			'id'               => 'helmetSizeSelectGuest' . $guest_num,
			'name'             => 'bike_gears[guests][' . $guest_num . '][jersey_style]',
			'value'            => tt_validate( $args['jersey_style'] ),
			'data_guest_index' => $guest_num,
		),
		'jersey_size' => array(
			'id'    => 'helmetSizeSelectGuest' . $guest_num,
			'name'  => 'bike_gears[guests][' . $guest_num . '][jersey_size]',
			'value' => tt_validate( $args['jersey_size'] )
		),
		'transportation_options' => array(
			'id'           => 'transportationOptionsSelectGuest' . $guest_num,
			'name'         => 'bike_gears[guests][' . $guest_num . '][transportation_options]',
			'options_html' => tt_items_select_options( 'syncTransportationOptions', tt_validate( $args['transportation_options'] ) ),
			'style'        => $own_bike_id === $posted_bike_id ? 'display:block;' : 'display:none;'
		),
		'type_of_bike' => array(
			'id'    => 'typeOfBikeGuest' . $guest_num,
			'name'  => 'bike_gears[guests][' . $guest_num . '][type_of_bike]',
			'style' => $own_bike_id === $posted_bike_id ? 'display:block;' : 'display:none;',
			'value' => tt_validate( $args['type_of_bike'] )
 		),
	);
}

if( ! $is_primary ) :
?>
<hr>
<?php endif; ?>
<div class="d-flex show-msg">
	<p class="fw-medium fs-xl lh-lg"><?php echo esc_html( $bike_gears_fields['guest_name'] ) ?></p>
	<p class="fw-medium fs-xl lh-lg woocommerce-invalid"><?php esc_html_e( 'Please make a bike model and gear selection for this guest', 'trek-travel-theme' ); ?></p>
</div>
<?php if( $non_rider_bike_id ===  $posted_bike_id ) : ?>
	<?php $non_rider_level_name = tt_get_custom_item_name( 'syncRiderLevels', tt_validate( $args['rider_level'] ) ); ?>
	<div class="row">
		<div class="col-12 col-lg-6 d-flex align-items-center">
			<?php printf( __( 'Rider Level: %1$s', 'trek-travel-theme' ), esc_html( $non_rider_level_name ) ); ?>
		</div>
		<div class="col-12 col-lg-6 text-lg-end">
			<a href="javascript:" class="btn btn-md btn-outline-primary checkout-review__edit tt_change_checkout_step" data-step="1"><?php esc_html_e( 'Edit Guest Info', 'trek-travel-theme' ); ?></a>
		</div>
	</div>
<?php endif; ?>

<div class="checkout-bikes__bike-selection" id="<?php echo esc_attr( $bike_gears_fields['bike_selection_wrp']['id'] ) ?>"<?php echo wp_strip_all_tags( $non_rider_bike_id === $posted_bike_id ? ' ' . 'style="display:none;"' : ' ' . 'style="display:block;"' ) ?>>
	<div class="bike-selection-checkbox">
		<p class="fw-medium fs-xl lh-md"><?php esc_html_e( 'Select Your Bike of Choice', 'trek-travel-theme' ); ?></p>
		<!-- Own Bike Checkbox -->
		<div class="form-check">
			<input name="<?php echo esc_attr( $bike_gears_fields['own_bike']['name'] ); ?>" class="form-check-input checkout-bikes__own-bike-check tt_my_own_bike_checkbox" data-type="<?php echo esc_attr( $bike_gears_fields['own_bike']['data_type'] ); ?>" data-type-bc="<?php echo esc_attr( $bike_gears_fields['own_bike']['data_type_bc'] ); ?>" type="checkbox" value="yes" id="<?php echo esc_attr( $bike_gears_fields['own_bike']['id'] ); ?>"<?php echo esc_attr( ! empty( $bike_gears_fields['own_bike']['checked'] ) ? ' ' . $bike_gears_fields['own_bike']['checked'] : '' ) ?>>
			<label class="form-check-label fw-normal fs-md lh-lg" for="<?php echo esc_attr( $bike_gears_fields['own_bike']['id'] ); ?>">
				<?php esc_html_e( 'I am bringing my own bike', 'trek-travel-theme' ); ?>
			</label>
		</div>
	</div>
	<!-- Available Bikes Grid List -->
	<div class="checkout-bikes__grid-list my-5" data-id="<?php echo esc_attr( $bike_gears_fields['own_bike']['data_type'] ); ?>" style="<?php echo esc_attr( $bike_gears_fields['own_bike']['style'] ); ?>">
		<?php
			$checkout_bikes_available_template = TREK_PATH . '/woocommerce/checkout/checkout-bikes-available.php';
			if( is_readable( $checkout_bikes_available_template ) ) {
				wc_get_template( 'woocommerce/checkout/checkout-bikes-available.php', array( 'is_primary' => $is_primary, 'ns_trip_id' => $args['ns_trip_id'], 'sku' => $args['sku'], 'guest_num' => $guest_num, 'product_id' => $args['product_id'], 'posted_bike_id' => $args['posted_bike_id'], 'posted_bike_type_id' => $args['posted_bike_type_id'] ) );
			} else {
				?>
					<h3><?php esc_html_e( 'Step 3', 'trek-travel-theme' ); ?></h3>
					<p><?php esc_html_e( 'checkout-bikes-available.php template is missing!', 'trek-travel-theme' ); ?></p>
				<?php
			}
			?>
	</div>
	<!-- Additional Bike and Gear Info -->
	<div class="checkout-bikes__additional-bike-info my-4">
		<p class="fw-medium fs-xl lh-md"><?php esc_html_e( 'Additional Bike & Gear Information', 'trek-travel-theme' ); ?></p>
		<?php if ( $is_bike_upgrade ) : ?>
			<div class="checkout-timeline__info rounded-1 mb-20 w-100 bike-size-disclaimer-ctr" style="display: flex;">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-info.svg' ); ?>">
				<p class="mb-0 fs-sm lh-sm bike-size-disclaimer">
				<?php
					// Limited quantities available for each trip date. Call for more info.
					printf(
						wp_kses(
							/* translators: %1$s: Phone number; */
							__( 'Limited quantities available for each trip date. <a href="%1$s">Call</a> for more info.', 'trek-travel-theme' ), 
							array(
								'a' => array(
									'class'  => array(),
									'href'   => array(),
									'target' => array()
								)
							)
						),
						esc_url( 'tel:8664648735' ),
					);
					?>
				</p>
			</div>
		<?php elseif ( $posted_bike_id && ! $is_bike_upgrade && ! $is_bike_available ) : ?>
			<div class="checkout-timeline__info rounded-1 mb-20 w-100 bike-size-disclaimer-ctr" style="display: flex;">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-info.svg' ); ?>">
				<p class="mb-0 fs-sm lh-sm bike-size-disclaimer"><?php esc_html_e( 'Your request for this bike size is being processed and is not guaranteed. We will follow up with you in 1 business day.', 'trek-travel-theme' ); ?></p>
			</div>
		<?php else : ?>
			<div class="checkout-timeline__info rounded-1 mb-20 w-100 bike-size-disclaimer-ctr" style="display: none;">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/checkout/checkout-info.svg' ); ?>">
				<p class="mb-0 fs-sm lh-sm bike-size-disclaimer"></p>
			</div>
		<?php endif; ?>
		<!-- Bike Size -->
		<div class="form-floating checkout-bikes__bike-size" data-id="<?php echo esc_attr( $bike_gears_fields['own_bike']['data_type'] ); ?>" style="<?php echo esc_attr( $bike_gears_fields['own_bike']['style'] ); ?>">
			<select name="<?php echo esc_attr( $bike_gears_fields['bike_size']['name'] ); ?>" class="form-select tt_bike_size_change" data-guest-index="<?php echo esc_attr( $bike_gears_fields['bike_size']['data_guest_index'] ); ?>" id="<?php echo esc_attr( $bike_gears_fields['bike_size']['id'] ); ?>" aria-label="<?php esc_html_e( 'Bike size select', 'trek-travel-theme' ); ?>"<?php echo esc_attr( $own_bike_id !== $posted_bike_id && $non_rider_bike_id !== $posted_bike_id ? ' ' . 'required' : '' ); ?>>
				<?php
					if ( $bike_gears_fields['bike_size']['options_html'] && $bike_gears_fields['bike_size']['options_html']['size_opts'] ) {
						print wp_kses(
							$bike_gears_fields['bike_size']['options_html']['size_opts'],
							array(
								'option' => array(
									'class'    => array(),
									'value'    => array(),
									'selected' => array(),
									'disabled' => array(),
								)
							),
						);
					}
					?>
			</select>
			<label for="<?php echo esc_attr( $bike_gears_fields['bike_size']['id'] ); ?>"><?php esc_html_e( 'Bike size', 'trek-travel-theme' ); ?></label>
		</div>
		<!-- Rider height -->
		<div class="form-floating checkout-bikes__bike-size" data-id="<?php echo esc_attr( $bike_gears_fields['own_bike']['data_type'] ); ?>" style="<?php echo esc_attr( $bike_gears_fields['own_bike']['style'] ); ?>">
			<select name="<?php echo esc_attr( $bike_gears_fields['rider_height']['name'] ); ?>" class="form-select" id="<?php echo esc_attr( $bike_gears_fields['rider_height']['id'] ); ?>" aria-label="<?php esc_html_e( 'Rider height select', 'trek-travel-theme' ); ?>"<?php echo esc_attr( $own_bike_id !== $posted_bike_id && $non_rider_bike_id !== $posted_bike_id ? ' ' . 'required' : '' ); ?>>
				<?php
					print wp_kses(
						$bike_gears_fields['rider_height']['options_html'],
						array(
							'option' => array(
								'class'    => array(),
								'value'    => array(),
								'selected' => array(),
								'disabled' => array(),
							)
						),
					);
					?>
			</select>
			<label for="<?php echo esc_attr( $bike_gears_fields['rider_height']['id'] ); ?>"><?php esc_html_e( 'Rider Height', 'trek-travel-theme' ); ?></label>
		</div>
		<!-- Select Pedals -->
		<div class="form-floating checkout-bikes__bike-size"data-id="<?php echo esc_attr( $bike_gears_fields['own_bike']['data_type'] ); ?>" style="<?php echo esc_attr( $bike_gears_fields['own_bike']['style'] ); ?>">
			<select name="<?php echo esc_attr( $bike_gears_fields['bike_pedal']['name'] ); ?>" class="form-select" id="<?php echo esc_attr( $bike_gears_fields['bike_pedal']['id'] ); ?>" aria-label="<?php esc_html_e( 'Bike pedal select', 'trek-travel-theme' ); ?>"<?php echo esc_attr( $non_rider_bike_id !== $posted_bike_id ? ' ' . 'required' : '' ); ?>>
				<?php
					print wp_kses(
						$bike_gears_fields['bike_pedal']['options_html'],
						array(
							'option' => array(
								'class'    => array(),
								'value'    => array(),
								'selected' => array(),
								'disabled' => array(),
							)
						),
					);
					?>
			</select>
			<label for="<?php echo esc_attr( $bike_gears_fields['bike_pedal']['id'] ); ?>"><?php esc_html_e( 'Select Pedals', 'trek-travel-theme' ); ?></label>
		</div>
		<!-- Helmet size -->
		<div class="form-floating checkout-bikes__bike-size">
			<select name="<?php echo esc_attr( $bike_gears_fields['helmet_size']['name'] ); ?>" class="form-select" id="<?php echo esc_attr( $bike_gears_fields['helmet_size']['id'] ); ?>" aria-label="<?php esc_html_e( 'Helmet size select', 'trek-travel-theme' ); ?>" <?php echo esc_attr( $non_rider_bike_id !== $posted_bike_id ? ' ' . 'required' : '' ); ?>>
				<?php
					print wp_kses(
						$bike_gears_fields['helmet_size']['options_html'],
						array(
							'option' => array(
								'class'    => array(),
								'value'    => array(),
								'selected' => array(),
								'disabled' => array(),
							)
						),
					);
					?>
			</select>
			<label for="<?php echo esc_attr( $bike_gears_fields['helmet_size']['id'] ); ?>"><?php esc_html_e( 'Helmet Size', 'trek-travel-theme' ); ?></label>
		</div>
		<!-- Jersey Style ( Only displayed if the trip style is NOT Self-Guided, Discover, Ride Camp ) -->
		<div class="form-floating checkout-bikes__bike-size<?php echo esc_attr( $should_show_jersey_select ? '' : ' ' . 'd-none' ); ?>">
			<select name="<?php echo esc_attr( $bike_gears_fields['jersey_style']['name'] ); ?>" class="form-select tt_jersey_style_change" id="<?php echo esc_attr( $bike_gears_fields['jersey_style']['id'] ); ?>" aria-label="<?php esc_html_e( 'Jersey Style select', 'trek-travel-theme' ); ?>" data-guest-index="<?php echo esc_attr( $bike_gears_fields['jersey_style']['data_guest_index'] ); ?>" data-is-required="<?php echo esc_attr( $should_show_jersey_select && $non_rider_bike_id !== $posted_bike_id ? 'true' : 'false' ) ?>"<?php echo esc_attr( $should_show_jersey_select && $non_rider_bike_id !== $posted_bike_id ? ' ' . 'required' : '' ) ?>>
				<option value=""><?php esc_html_e( 'Select Jersey Style', 'trek-travel-theme' ); ?></option>
				<?php if( ! $should_show_jersey_select ) : ?>
					<option selected value=""><?php esc_html_e( 'None', 'trek-travel-theme' ); ?></option>
				<?php endif; ?>
				<option value="men"<?php echo esc_attr( 'men' === $bike_gears_fields['jersey_style']['value'] ? ' ' . 'selected' : '' ); ?>><?php esc_html_e( 'Men\'s', 'trek-travel-theme' ); ?></option>
				<option value="women"<?php echo esc_attr( 'women' === $bike_gears_fields['jersey_style']['value'] ? ' ' . 'selected' : '' ); ?>><?php esc_html_e( 'Women\'s', 'trek-travel-theme' ); ?></option>
			</select>
			<label for="<?php echo esc_attr( $bike_gears_fields['jersey_style']['id'] ); ?>"><?php esc_html_e( 'Jersey Style', 'trek-travel-theme' ); ?></label>
		</div>
		<!-- Jersey Size ( Only displayed if the trip style is NOT Self-Guided, Discover, Ride Camp ) -->
		<div class="form-floating checkout-bikes__bike-size<?php echo esc_attr( $should_show_jersey_select ? '' : ' ' . 'd-none' ); ?>">
			<select name="<?php echo esc_attr( $bike_gears_fields['jersey_size']['name'] ); ?>" class="form-select" id="<?php echo esc_attr( $bike_gears_fields['jersey_size']['id'] ); ?>" aria-label="<?php esc_html_e( 'Jersey Size select', 'trek-travel-theme' ); ?>" data-is-required="<?php echo esc_attr( $should_show_jersey_select && $non_rider_bike_id !== $posted_bike_id ? 'true' : 'false' ) ?>"<?php echo esc_attr( $should_show_jersey_select && $non_rider_bike_id !== $posted_bike_id ? ' ' . 'required' : '' ) ?>>
				<?php if ( ! $should_show_jersey_select ) : ?>
					<option selected value=""><?php esc_html_e( 'None', 'trek-travel-theme' ); ?></option>
				<?php endif; ?>
				<?php
					print wp_kses(
						tt_get_jersey_sizes( $bike_gears_fields['jersey_style']['value'], $bike_gears_fields['jersey_size']['value'] ),
						array(
							'option' => array(
								'class'    => array(),
								'value'    => array(),
								'selected' => array(),
								'disabled' => array(),
							)
						),
					);
					?>
			</select>
			<label for="<?php echo esc_attr( $bike_gears_fields['jersey_size']['id'] ); ?>"><?php esc_html_e( 'Jersey Size', 'trek-travel-theme' ); ?></label>
		</div>
		<!-- Transportation Options -->
		<div class="form-floating checkout-bikes__bike-transportation-options tt_my_own_bike_transportation_options" data-id-bc="<?php echo esc_attr( $bike_gears_fields['own_bike']['data_type_bc'] ); ?>" style="<?php echo esc_attr( $bike_gears_fields['transportation_options']['style'] ); ?>">
			<select name="<?php echo esc_attr( $bike_gears_fields['transportation_options']['name'] ); ?>" class="form-select" id="<?php echo esc_attr( $bike_gears_fields['transportation_options']['id'] ); ?>" aria-label="<?php esc_html_e( 'Transportation Options select', 'trek-travel-theme' ); ?>"<?php echo esc_attr( $own_bike_id === $posted_bike_id ? ' ' . 'required' : '' ); ?>>
				<?php
					print wp_kses(
						$bike_gears_fields['transportation_options']['options_html'],
						array(
							'option' => array(
								'class'    => array(),
								'value'    => array(),
								'selected' => array(),
								'disabled' => array(),
							)
						),
					);
					?>
			</select>
			<label for="<?php echo esc_attr( $bike_gears_fields['transportation_options']['id'] ); ?>"><?php esc_html_e( 'Transportation Options', 'trek-travel-theme' ); ?></label>
		</div>
		<div class="form-floating checkout-bikes__bike-type tt_my_own_bike_type_of_bike" data-id-bc="<?php echo esc_attr( $bike_gears_fields['own_bike']['data_type_bc'] ); ?>" style="<?php echo esc_attr( $bike_gears_fields['transportation_options']['style'] ); ?>">
			<input placeholder="<?php esc_html_e( 'Type of bike', 'trek-travel-theme' ); ?>" name="<?php echo esc_attr( $bike_gears_fields['type_of_bike']['name'] ); ?>" id="<?php echo esc_attr( $bike_gears_fields['type_of_bike']['id'] ); ?>" type="text"<?php echo esc_attr( $own_bike_id === $posted_bike_id ? ' ' . 'required' : '' ); ?> class="form-control" value="<?php echo esc_attr( $bike_gears_fields['type_of_bike']['value'] ); ?>">
			<!-- <label for="<?php echo esc_attr( $bike_gears_fields['type_of_bike']['id'] ); ?>"><?php esc_html_e( 'Type of bike', 'trek-travel-theme' ); ?></label> -->
		</div>
	</div>
	<?php if( $is_primary ) : ?>	
		<div class="container checkout-bikes__save-preferences my-4 mx-0">
			<div class="form-check">
				<input name="bike_gears[primary][save_preferences]" class="form-check-input checkout-bikes__own-bike-check" type="checkbox" value="yes" id="saveMyBikePrefCheckbox"<?php echo esc_attr( isset( $args['save_preferences'] ) && 'yes' === $args['save_preferences'] ? ' ' . 'checked' : ''); ?>>
				<label class="form-check-label fw-normal fs-md lh-sm" for="saveMyBikePrefCheckbox">
					<?php  esc_html_e( 'Save my bike & gear preferences for future use. This will override your existing preferences saved on your profile.', 'trek-travel-theme' ) ?>
				</label>
			</div>
		</div>
	<?php endif; ?>
</div>

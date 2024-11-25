<?php
/**
 * Template file for the quick view content in the modal on the Archive and Search pages.
 */

$trip_data = array();
if ( isset( $args['trip_data'] ) && ! empty( $args['trip_data'] ) ) {
	// Trip taxonomy terms - Activity Level, Hotel Level, Trip Style ...
	$trip_data = json_decode( $args['trip_data'], true );
}

$product            = wc_get_product( $args['product_id'] );
$p_id               = $product->get_id();
$linked_products    = $product->get_children();
$get_child_products = get_child_products( $linked_products, true );
$trip_style         = tt_get_custom_product_tax_value( $p_id, 'trip-style', true );
$activity_terms     = get_the_terms( $p_id, 'activity' );

foreach ( $activity_terms as $activity_term) {
	$activity = $activity_term->name;
}

// Product Cats.
$pcats = array();
$product_cat_terms = get_the_terms( $p_id, 'product_cat' );
foreach ( $product_cat_terms as $term ) {
	$product_cat = $term->name;
	$pcats[]     = $product_cat;
}

/**
 * Function that returns the status to display for specific NS Statuses.
 *
 * @param string $status The Trip status from NetSuite.
 *
 * @return string Trip Status to display.
 */
function get_web_dispaly_status( $status ) {
	$web_dispaly_arr = array(
		'Hold'                 => 'Limited Availability', 
		'Sales Hold'           => 'Limited Availability',
		'Group Hold'           => 'Limited Availability',
		'Limited Availability' => 'Limited Availability',
		'SOLD OUT'             => 'Join Waitlist'
	);

	if ( array_key_exists( $status, $web_dispaly_arr ) ) {
		return $web_dispaly_arr[$status];
	}

	return $status;
}

/**
 * Function that sorts two dates, ascending.
 *
 * @param array  $a Array with objects, we need ['start_date'] in this format dd/mm/yy.
 * @param array  $b Array with objects.
 * @param string $d String with the delimeter.
 */
function date_sort( $a, $b, $d = '/' ) {

	if ( $a == $b ) {

		return 0;
	} else {

		// Convert into dates and compare.
		list( $ad, $am, $ay ) = explode( $d, $a['start_date'] );

		list( $bd, $bm, $by ) = explode( $d, $b['start_date'] );

		if ( mktime( 0, 0, 0, $am, $ad, $ay ) < mktime( 0, 0, 0, $bm, $bd, $by ) ) {

			return -1;
		} else {

			return 1;
		}
	}
}

$available_child_products = array();

// Sort the trips and store only available trips into a new array.
foreach ( $get_child_products as $year => $get_child_product ) {

	// Sort trips by year ascending.
	ksort( $get_child_product, 1 );

	foreach ( $get_child_product as $month => $get_child_product_data) {

		// Sort trips by date ascending.
		usort( $get_child_product_data, 'date_sort' );

		foreach ( $get_child_product_data as $index => $child_product_data ) {
			$today_date = new DateTime( 'now' );

			// 'start_date' => string '11/12/23' dd/mm/yy.
			$trip_start_date = DateTime::createFromFormat('d/m/y', $child_product_data['start_date']);

			if ( $trip_start_date && $trip_start_date > $today_date ) {

				// Check child product is marked as Private/Custom trip.
				$is_private_custom_trip = get_field( 'is_private_custom_trip', $child_product_data['product_id'] );

				// If the child product is marked as a private/custom trip, continue to the next one.
				if ( true == $is_private_custom_trip ) {
					continue;
				}

				if ( ! isset( $available_child_products[ $year ] ) ) {
					// Make a new array for every year.
					$available_child_products[ $year ] = array();
				}


				if ( ! isset( $available_child_products[ $year ][ $month ] ) ) {
					// Make a new array for every month.
					$available_child_products[ $year ][ $month ] = array();
				}

				// Store the available trip into the new array.
				array_push( $available_child_products[ $year ][ $month ], $child_product_data );

			}
		}
	}
}

$content_flag         = false;
$nav_year_tab         = '';
$nav_year_tab_content = '';

if ( $available_child_products ) {

	ksort( $available_child_products );
	
	$iter = 1;
	foreach ( $available_child_products as $year=>$get_child_product ) {

		// nav year tabs & button HTML creation.
		$nav_year_tab .= '<button class="nav-link '.($iter == 1 ? 'active' : '').'" id="nav-year'.$year.'-tab" data-bs-toggle="tab" data-bs-target="#nav-year'.$year.'" type="button" role="tab" aria-controls="nav-year'.$year.'" aria-selected="true"><span>'.$year.' Tours</span></button>';
		// nav year tab content HTML creation.
		$nav_year_tab_content .= '<div class="tab-pane fade show '.($iter == 1 ? 'active' : '').'" id="nav-year'.$year.'" role="tabpanel" aria-labelledby="nav-year'.$year.'-tab" tabindex="0">';

		$all_month_content_output     = '';
		$month_nav_desktop_btn_output = ''; // <!-- months nav desktop -->
		$month_nav_mobile_btn_output  = '';
		$month_content_output         = '';
		if ( $get_child_product ) {
			$m_iter = 1;

			$month_nav_desktop_btn_output .= '<button class="nav-link '.$m_iter.' '.($m_iter == 1 ? 'active' : '').'" id="nav-all-'.$year.'-tab" data-bs-toggle="tab" data-bs-target="#nav-all-'.$year.'" type="button" role="tab" aria-controls="nav-all-'.$year.'" aria-selected="false">All</button>';

			$month_nav_mobile_btn_output  .= '<option value="nav-all-'.$year.'">All '.$year.'</option>';

			$all_month_content_output     .= '<div class="tab-pane fade show '.($m_iter == 1 ? 'active' : '').'" id="nav-all-'.$year.'" role="tabpanel" aria-labelledby="nav-all-'.$year.'-tab" tabindex="0"><div class="accordion accordion-flush" id="accordionFlushExample-'.$year.'-a">';

			foreach ( $get_child_product as $month=>$get_child_product_data ) {
				$m_iter++;

				$my         = $month.$year;
				$month_info = trek_get_month_info( $month );
				
				$month_nav_desktop_btn_output .= '<button class="nav-link '.$m_iter.'  '.($m_iter == 1 ? 'active' : '').'" id="nav-'.$my.'-tab" data-bs-toggle="tab" data-bs-target="#nav-'.$my.'" type="button" role="tab" aria-controls="nav-'.$my.'" aria-selected="true">'.$month_info[$month][0].'</button>';

				$month_nav_mobile_btn_output .= '<option value="nav-'.$my.'">'.$month_info[$month][0].'</option>';

				$month_content_output .= '<div class="tab-pane fade show '.($m_iter == 1 ? 'active' : '').'" id="nav-'.$my.'" role="tabpanel" aria-labelledby="nav-'.$my.'-tab" tabindex="0"><div class="accordion accordion-flush" id="accordionFlushExample-'.$my.'">';

				if ( $get_child_product_data ) {
					foreach ( $get_child_product_data as $index => $child_product_data ) {

						$content_flag                 = true;
						$accordion_item_id            = $my.$child_product_data['product_id'];
						$date_range                   = $child_product_data['start_date'].' - '.$child_product_data['end_date'];
						$date_range                   = $child_product_data['date_range'];
						$trip_status                  = $child_product_data['trip_status'];
						$bike_hotels                  = tt_get_hotel_bike_list( $child_product_data['sku'] );
						$remove_from_stella           = tt_get_local_trips_detail( 'removeFromStella', '', $child_product_data['sku'], true );
						$single_supplement_price      = isset( $child_product_data['singleSupplementPrice'] ) ? $child_product_data['singleSupplementPrice'] : 0;
						$trip_web_status              = get_web_dispaly_status( $trip_status );
						$trip_web_status_class        = strtolower( str_ireplace( ' ', '-', $trip_web_status ) );
						$is_pc_trip                   = get_field( 'is_private_custom_trip', $child_product_data['product_id'] ); // Check child product is marked as Private/Custom trip.
						$res_status                   = array(
							// 'Limited Availability',
							'Group Hold',
							'Sales Hold',
							'Hold'
						);
						$wait_status                  = array( 'SOLD OUT' );
						$form_url_path                = '';

						if ( in_array( $trip_status, $res_status ) || $remove_from_stella == true ) {
							switch ( $activity ) {
								case TT_ACTIVITY_DASHBOARD_NAME_HW:
									$form_url_path = "reserve-a-trip-hw";
									break;
								case TT_ACTIVITY_DASHBOARD_NAME_BIKING:
								default:
									// By default the URL will be this.
									$form_url_path = "reserve-a-trip";
									break;
							}
						}

						if ( in_array( $trip_status, $wait_status ) ) {
							$form_url_path = "waitlist";
						}

						if ( ! empty( $form_url_path ) ) {
							// The trip date is not available for web booking.
							$form_url_args = array(
								'tripname' => $product->name,
								'tripdate' => $date_range
							);
							$form_url        = add_query_arg( $form_url_args, home_url( $form_url_path ) );
							$book_now_button = '<a href="' . esc_url( $form_url ) . '" class="btn btn-primary btn-md rounded-1 mb-4 dates-pricing-book-now qv-book-now-btn">Book now</a>';
						} else {
							$cart_result           = get_user_meta( get_current_user_id(),'_woocommerce_persistent_cart_' . get_current_blog_id(), true ); 
							$cart                  = WC()->session->get( 'cart', null );
							$persistent_cart_count = isset( $cart_result['cart'] ) && $cart_result['cart'] ? count( $cart_result['cart'] ) : 0;

							if ( ! is_null( $cart ) && $persistent_cart_count > 0 ) {
								// Already has started the booking process. Show the warning modal.
								$book_now_button = '<button type="button" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now qv-book-now-btn" id="trip-booking-modal" data-bs-toggle="modal" data-bs-target="#tripBookingModal" data-form-id="' . $accordion_item_id . '" data-return-url="/?trip=' . $product->name . '">Book now</button>';
							} else {
								$book_now_button = '<button type="submit" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now qv-book-now-btn" data-return-url="/?trip=' . $product->name . '">Book now</button>';
							}
						}

						$date_trip_item_args = array(
							'sku'                     => $child_product_data['sku'],
							'remove_from_stella'      => $remove_from_stella,
							'trip_status'             => $trip_status,
							'accordion_id'            => $accordion_item_id,
							'month_year'              => $my,
							'date_range'              => $date_range,
							'trip_web_status_class'   => $trip_web_status_class,
							'trip_web_status'         => $trip_web_status,
							'trip_hotels'             => $bike_hotels['hotels'],
							'trip_bikes'              => $bike_hotels['bikes'],
							'trip_activity'           => $activity,
							'is_pc_trip'              => $is_pc_trip,
							'book_now_btn_html'       => $book_now_button,
							'parent_trip_permalink'   => apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ),
							'product_id'              => $child_product_data['product_id'],
							'product_price'           => $child_product_data['price'],
							'single_supplement_price' => $single_supplement_price
						);

						$month_content_output     .= wc_get_template_html( 'woocommerce/quick-view/macros/qv-date-trip-item.php', $date_trip_item_args );
						$all_month_content_output .= wc_get_template_html( 'woocommerce/quick-view/macros/qv-date-trip-item.php', $date_trip_item_args );
					}
				}
				$month_content_output .= '</div></div>';
			
			}
		}

		if ( $content_flag ) {
			$month_nav_desktop_output = '<nav class="nav-months-desktop"><div class="nav nav-tabs-months" id="nav-tab-month" role="tablist">';

			

			$month_nav_desktop_output .= $month_nav_desktop_btn_output;
			$month_nav_desktop_output .='</div></nav>';
			// Months nav mobile
			$month_nav_mobile_output = '<form><select class="form-select select-months" id="select-month-'.$year.'">';
			$month_nav_mobile_output .= $month_nav_mobile_btn_output;
			$month_nav_mobile_output .='</select></form>';
			
			$nav_year_tab_content .= $month_nav_desktop_output;
			$nav_year_tab_content .= $month_nav_mobile_output;

			
			
			$nav_year_tab_content .= '<div class="tab-content nav-tabContent-months" id="nav-tabContent-months-'.$year.'">';
				$nav_year_tab_content .= $all_month_content_output;
					$nav_year_tab_content .= '</div></div>';
				$nav_year_tab_content .= $month_content_output;
					$nav_year_tab_content .= '</div>';
			$nav_year_tab_content .= '</div>';
			
			$iter++;
		} else {
			$nav_year_tab         = '';
			$nav_year_tab_content = '';
		}
	}

}

$product_image_url = get_template_directory_uri() . '/assets/images/TT-Logo.png';
if ($p_id) {
	if( has_post_thumbnail($p_id) ){
		$product_image_url = get_the_post_thumbnail_url($p_id);
	}
}

// Trip Short Description.
$product_subtitle = get_field( 'product_overview_product_subtitle', $p_id, true, true );
if ( $product_subtitle ) {
	$trip_short_description = $product_subtitle;
} else {
	$trip_content           = get_the_content( null, false, $p_id );
	$trip_short_description = substr( $trip_content, 0, 200 );
	if ( ! empty( $trip_short_description) ) {
		$trip_short_description .= '...';
	}
}

// Trip Bikes.
$pdp_bikes = get_field( 'bikes', $p_id );
?>
<div class="checkout-summary__card-header px-3<?php echo esc_attr( TT_ACTIVITY_DASHBOARD_NAME_HW === $activity ? ' ' . 'style-hiking' : '' ); ?>">
	<div class="checkout-summary__card">
		<p class="text-center trip-product-line mb-0"><?php echo esc_html( $activity ); ?></p>
		<span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
			<i type="button" class="bi bi-x"></i>
		</span>
	</div>
	<div class="trip-title-copy-container">
		<h2 class="checkout-summary__title mb-1 "><?php echo $product->get_title(); ?></h2>
		<button type="button" class="btn btn-secondary share-link mobile-share-link  btn-outline-dark">
				<i class="bi bi-link-45deg"></i>
			</button>
		<div class="toast bg-white mobile-link-copied align-items-center w-auto start-0" role="alert" aria-live="assertive" aria-atomic="true">
			<div class="d-flex">
				<div class="toast-body">
				<?php esc_html_e( 'Link copied ', 'trek-travel-theme' ); ?>
					<i class="bi bi-x-lg align-self-center" data-bs-dismiss="toast" aria-label="Close"></i>
				</div>
			</div>
		</div>
	</div>
	<?php if ( ! empty( $trip_short_description ) ) : ?>
		<h5 class="trip-desc"><?php echo esc_html( $trip_short_description ); ?></h5>
	<?php endif; ?>
	<?php if ( ! empty( $trip_data ) ) : ?>
		<div class="overview-details d-inline-flex">
			<?php if( isset( $trip_data[ 'bsTripStyle' ] ) && ! empty( $trip_data[ 'bsTripStyle' ] ) ) : ?>
				<ul class="list-inline mb-1 me-2 pe-2 border-end border-2">
					<li class="list-inline-item"><i class="bi bi-briefcase"></i></li>
					<li class="list-inline-item fs-sm"><?php echo esc_html( $trip_data[ 'bsTripStyle' ] ); ?></li>
					<li class="list-inline-item"><i class="bi bi-info-circle pdp-trip-styles"></i></li>
				</ul>
			<?php endif; ?>

			<?php if( isset( $trip_data[ 'bsTripDuration' ] ) && ! empty( $trip_data[ 'bsTripDuration' ] ) ) : ?>
				<ul class="list-inline mb-1 me-2 pe-2 border-end border-2">
					<li class="list-inline-item"><i class="bi bi-calendar"></i></li>
					<li class="list-inline-item fs-sm"><?php echo esc_html( $trip_data[ 'bsTripDuration' ] ); ?></li>
					<li class="list-inline-item"></li>
				</ul>
			<?php endif; ?>

			<?php if( isset( $trip_data[ 'bsActivityLevel' ] ) && ! empty( $trip_data[ 'bsActivityLevel' ] ) ) : ?>
				<ul class="list-inline mb-1 me-2 pe-2 border-end border-2">
					<?php if( isset( $trip_data[ 'bsActivityIcon' ] ) && ! empty( $trip_data[ 'bsActivityIcon' ] ) ) :?>
						<li class="list-inline-item hw"><i class="fa-solid <?php echo esc_attr( $trip_data[ 'bsActivityIcon' ] ); ?>"></i></li>
					<?php endif; ?>
					<li class="list-inline-item fs-sm dl-riderlevel"><?php echo esc_html( $trip_data[ 'bsActivityLevel' ] ); ?></li>
					<li class="list-inline-item"><i class="bi bi-info-circle pdp-rider-level"></i></li>
				</ul>
			<?php endif; ?>

			<?php if( isset( $trip_data[ 'bsHotelLevel' ] ) && ! empty( $trip_data[ 'bsHotelLevel' ] ) ) : ?>
				<ul class="list-inline mb-1">
					<li class="list-inline-item"><i class="bi bi-house"></i></li>
					<li class="list-inline-item fs-sm"><?php echo esc_html( $trip_data[ 'bsHotelLevel' ] ); ?></li>
					<li class="list-inline-item"><i class="bi bi-info-circle pdp-hotel-levels"></i></li>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $pdp_bikes ) : ?>
		<div class="bikes-container">
			<p class="bikes fw-normal fs-sm lh-sm mb-0 text-muted pb-0"><?php esc_html_e( 'Bikes: ', 'trek-travel-theme' ); ?></p>
			<p class="fw-medium fw-normal fs-sm lh-sm mb-0">
			<?php
				foreach ( $pdp_bikes as $key => $bike ) {
					if ( $key < 4 ) {
						setup_postdata( $bike );
						echo esc_html( $bike->post_title ) . '&nbsp;&nbsp;<span style="color: #dee2e6; border-left: 1px solid #dee2e6 "></span>&nbsp;&nbsp;';
					}
				}
				wp_reset_postdata();
				?>
			</p>
		</div>
	<?php endif; ?>

	<div class="quick-view-buttons">
		<a href="#" class="btn btn-primary view-trip-link"><?php esc_html_e( 'Full Trip Details', 'trek-travel-theme' ) ?></a>
		<div class="overview-icons d-flex">
			<button type="button" class="btn btn-secondary share-link  btn-outline-dark">
				<i class="bi bi-link-45deg"></i>
			</button>
		</div>
		<!-- toasts message -->
	
	</div>
</div>
<div class="container pdp-section dates-pricing-container px-3 pt-0" id="dates-pricing">
	<div class="dates-pricing">
		<?php if ( empty( $available_child_products ) ) { ?>
			<hr class="pdp-section__divider mt-3 mb-3">
			<div class="pdp-no-date pt-5">
				<?php if ( in_array( 'Pro Race Bike Tours', $pcats ) ) { ?>
					<span class="h5 text-center"><?php esc_html_e( "Place a deposit to hold your spot for next year's Pro Race bike tour", 'trek-travel-theme' ); ?></span>
					<a href="<?php echo esc_url( home_url( 'pro-race-difference/pro-race-reservations/' ) ); ?>" class="btn btn-primary" target="_blank"><?php esc_html_e( 'Place Deposit', 'trek-travel-theme' ); ?></a>
				<?php } else { ?>
					<span class="h5 text-center">Dates are coming soon for <?php echo $product->get_title(); ?>. Please submit your trip inquiry below.</span>
					<a href="<?php echo esc_url( add_query_arg( array( 'tripname' => $product->name, 'tripdate' => 'To Be Determined' ), home_url( 'reserve-a-trip' ) ) ); ?>" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now qv-book-now-btn" target="_blank"><?php esc_html_e( 'Inquire Now', 'trek-travel-theme' ); ?></a>
				<?php } ?>
			</div>
		<?php } else { ?>
			<!-- main nav year tour -->
			<nav>
				<div class="nav nav-tabs" id="nav-tab-year" role="tablist">
					<?php if ( $content_flag ) { echo $nav_year_tab; } ?>
				</div>
			</nav>
		<?php } ?>
		<!-- year tour tab content -->
		<div class="tab-content" id="nav-tabContent">
			<!-- year tour tab content -->
			<?php if ( $content_flag ) { echo $nav_year_tab_content; } ?>
		</div>
	</div>
</div>

<?php //Set up hidden fields for the Analytics data collected via JS on add_to_cart event. ?>
<input type="hidden" name="parent_product_name" value="<?php echo $product->name; ?>">
<input type="hidden" name="parent_product_id" value="<?php echo $product->id; ?>">

<script>
	// Get all select dropdown elements that start with "select-month-"
	var monthSelects = document.querySelectorAll('[id^="select-month-"]');

	// Iterate over each select dropdown
	monthSelects.forEach(function(monthSelect) {
		// console.log(monthSelect);

		// Add a change event listener to each select dropdown
		monthSelect.addEventListener('change', function() {
			// Get the value of the selected option
			var selectedMonthId = this.value;
			// console.log('selected: ' + selectedMonthId);

			// Assuming each dropdown's corresponding tab content is grouped within a parent
			// with a matching pattern in ID, like "nav-tabContent-months-123" for "select-month-123"
			// First, find this dropdown's corresponding tab content container
			var pattern = this.id.replace('select-month-', ''); // Get the unique part of ID
			var tabContentId = 'nav-tabContent-months-' + pattern;
			var tabContentContainer = document.getElementById(tabContentId);

			if (tabContentContainer) {
				// Hide all tab panes within this specific tab content container
				tabContentContainer.querySelectorAll('.tab-pane').forEach(function(pane) {
					pane.classList.remove('show', 'active');
				});

				// Show the selected tab pane within this container
				var selectedPane = tabContentContainer.querySelector('#' + selectedMonthId);
				if (selectedPane) {
					// console.log(selectedPane);
					selectedPane.classList.add('show', 'active');
					// console.log('shown');
				}
			} else {
				// console.log("Corresponding tab content container not found for " + this.id);
			}
		});
	});

	if (monthSelects.length === 0) {
		// console.log("No dropdown select elements found.");
	}
</script>

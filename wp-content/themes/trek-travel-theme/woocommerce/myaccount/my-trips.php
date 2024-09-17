<?php
$userInfo = wp_get_current_user();
$ns_user_id = get_user_meta(get_current_user_id(), 'ns_customer_internal_id', true);
$is_log = isset($_REQUEST['log']) && $_REQUEST['log'] == 1 ? true : false;
$wp_user_email = $userInfo->user_email;
?>
<div class="container my-trips my-4">
	<div class="row mx-0 flex-column flex-lg-row">
		<div class="col-lg-6 my-trips__back order-1 order-lg-0">
			<a class="text-decoration-none" href="<?php echo get_the_permalink(TREK_MY_ACCOUNT_PID); ?>"><i class="bi bi-chevron-left"></i><span class="fw-medium fs-md lh-md">Back to Dashboard</span></a>
		</div>
		<div class="col-lg-6 d-flex dashboard__log">
			<p class="fs-lg lh-lg fw-bold">Hi, <?php echo $userInfo->first_name; ?>!</p>
			<a href="<?php echo wp_logout_url('login'); ?>">Log out</a>
		</div>
	</div>

	<div id="my-trips-responses"></div>
	<div class="row mx-0">
		<div class="col-lg-12">
			<h3 class="dashboard__title fw-semibold">My Trips</h3>
		</div>
	</div>
	<div class="row mx-0">
		<div class="col-lg-10">
			<div class="card dashboard__card rounded-1">
				<h5 class="fw-semibold upcoming-title">Upcoming Trips</h5>
				<?php
				$trips_html = '';
				$trips = trek_get_guest_trips(get_current_user_id(), 1, '', $is_log);
				if (!empty($trips) && isset($trips['count']) && $trips['count'] != 0 && is_user_logged_in()) {
					if ( $trips && isset( $trips['data'] ) ) {
						foreach ( $trips['data'] as $trip ) {
							$product_id = $trip['product_id'];
							$order_id   = $trip['order_id'];
							$order      = wc_get_order( $order_id );
							if ( ! $order ) {
								// Skip the trip if does not exist the order on the website.
								continue;
							}
							// Get Order Status.
							$wc_order_status = $order->get_status();
							if( 'cancelled' === $wc_order_status || 'trash' === $wc_order_status ) {
								// Skip the trip if the order trashed or with canceled status.
								continue;
							}
							// Get The booking status.
							$booking_status = tt_get_booking_status( $order_id );
							if( $booking_status && in_array( $booking_status, TT_HIDE_ORDER_BOOKING_STATUSES ) ) {
								// Skip the trip if the booking status is not allowed to show the order.
								continue;
							}
							$order_details = trek_get_user_order_info($userInfo->ID, $order_id);
							$is_primary = isset( $order_details[0]['guest_is_primary'] ) ? $order_details[0]['guest_is_primary'] : 0;
							$waiver_signed = isset( $order_details[0]['waiver_signed'] ) ? $order_details[0]['waiver_signed'] : false;
                            $is_secondary_user = $is_primary == 0;
							$product = wc_get_product($product_id);
							$trip_name = $trip_sdate = $trip_edate = $trip_sku = '';
							$trip_name = $trip['trip_name'];
							if ($trip_name) {
								$product_id = $trip['product_id'];
								$order_id = $trip['order_id'];
								$product = wc_get_product($product_id);
								$trip_sdate = $product->get_attribute( 'pa_start-date' ); 
								$trip_edate = $product->get_attribute( 'pa_end-date' );
								$sdate_obj = explode('/', $trip_sdate);
                                $sdate_info = array(
                                    'd' => $sdate_obj[0],
                                    'm' => $sdate_obj[1],
                                    'y' => substr(date('Y'),0,2).$sdate_obj[2]
                                );
                                $edate_obj = explode('/', $trip_edate);
                                $edate_info = array(
                                    'd' => $edate_obj[0],
                                    'm' => $edate_obj[1],
                                    'y' => substr(date('Y'),0,2).$edate_obj[2]
                                );
                                $start_date_text = date('F jS, Y', strtotime(implode('-', $sdate_info)));
                                $end_date_text_1 = date('F jS, Y', strtotime(implode('-', $edate_info)));
                                $end_date_text_2 = date('jS, Y', strtotime(implode('-', $edate_info)));
                                $date_range_1 = $start_date_text. ' - '.$end_date_text_2;
                                $date_range_2 = $start_date_text. ' - '.$end_date_text_1;
                                $date_range = $date_range_1;
                                if( $sdate_info['m'] != $edate_info['m'] ){
                                    $date_range = $date_range_2;
                                }
								$trip_sku = $product ? $product->get_sku() : '';
								$parentTrip = tt_get_parent_trip($trip_sku);
								$trip_link = 'javascript:';
								$tripRegion = tt_get_local_trips_detail('tripRegion', '', $trip_sku, true);
								$pa_city = '';
								$parent_product_id = tt_get_parent_trip_id_by_child_sku($trip_sku);
								if( $parent_product_id ){
									$p_product = wc_get_product( $parent_product_id );
									$pa_city = $p_product ? $p_product->get_attribute( 'pa_city' ) : '';
								}
								if ($order_id) {
									$trip_link = esc_url(add_query_arg('order_id', $order_id, get_permalink(TREK_MY_ACCOUNT_PID) . 'my-trip'));
								}
								$link_html = '';
                              	$msg_html = '';
								if ($is_secondary_user) {
									$msg_html .= '<p class="fw-normal fs-sm lh-sm my-3">You`ve been added to this trip</p>';
								}
								if ($order_id && $trip_link != 'javascript:') {
										$link_html .= '<div class="trip-details-cta my-4 my-lg-0">
															<a class="btn btn-lg btn-primary rounded-1" href="' . $trip_link . '">View details</a>
														</div>';
								} 
								$trip_address = [$pa_city,$tripRegion];
								$trip_address = array_filter($trip_address);
								$is_checklist_completed = tt_is_checklist_completed( $userInfo->ID, $order_id, $order_details[0]['rider_level'], $product_id, $order_details[0]['bike_id'], $is_primary, $waiver_signed );
								$trips_html .= '<div class="trips-list-item">
                                <div class="trip-image">
                                    <img src="' . $parentTrip['image'] . '">
                                </div>
                                <div class="trip-info">
                                    <p class="fw-normal fs-sm lh-sm mb-0 mt-4">'.implode(', ', $trip_address).'</p>
                                    <h5 class="fw-semibold"><a href="' . $trip_link . '">' . $trip_name . '</a></h5>
                                    <p class="fw-medium fs-sm lh-sm">' . $date_range . '</p>';

								$lockedUserRecord = tt_is_registration_locked( $userInfo->ID, $order_details[0]['guestRegistrationId'], 'record' );
								$lockedUserBike   = tt_is_registration_locked( $userInfo->ID, $order_details[0]['guestRegistrationId'], 'bike' );

									if( ! empty( $order_details ) && ! $is_checklist_completed && 1 != $lockedUserRecord && 1 != $lockedUserBike ) {
										$trips_html .= '<i class="bi bi-info-circle me-3 text-danger"></i><p class="fw-normal fs-sm lh-sm d-inline text-danger">You have items pending confirmation</p>';
									} else {
										// Show this message on locked record or locked bike.
										if ( $lockedUserRecord || $lockedUserBike ) {
											$trips_html .= '<p class="fw-normal fs-sm lh-sm d-inline text-danger"><i class="fa fa-lock fa-lg" aria-hidden="true"></i> Your Checklist or a checklist item is locked. <a href="tel:8664648735">Call us</a> if you have any questions or concerns.</p>';
										}
									}

								$trips_html .= '</div>' . $link_html . '</div><hr>';
							}
						}
					}
				} else {
					$trips_html .= '<p class="no-trip-text mt-2">Your trips are on the way. To view them, please reload the page after a few seconds.</p>';
				}
				echo $trips_html;
				?>
			</div>
		</div>
	</div>

	<div class="row mx-0">
		<div class="col-lg-10">
			<div class="card dashboard__card rounded-1">
				<h5 class="fw-semibold past-title">Past Trips</h5>
				<?php
				$past_trips_html = '';
				$past_trips = trek_get_guest_trips(get_current_user_id(), 0, '', $is_log);
				if (!empty($past_trips) && isset($past_trips['count']) && $past_trips['count'] != 0 && is_user_logged_in()) {
					if ($past_trips && isset($past_trips['data'])) {
						foreach ($past_trips['data'] as $past_trip) {
							$trip_name = $trip_sdate = $trip_edate = $trip_sku = '';
							$product_id = $past_trip['product_id'];
							$trip_name = $past_trip['trip_name'];
							if ($trip_name) {
								$order_id = $past_trip['order_id'];
								$order    = wc_get_order( $order_id );
								if ( ! $order ) {
									// Skip the trip if does not exist the order on the website.
									continue;
								}
								// Get Order Status.
								$wc_order_status = $order->get_status();
								if( 'cancelled' === $wc_order_status || 'trash' === $wc_order_status ) {
									// Skip the trip if the order trashed or with canceled status.
									continue;
								}
								$order_details = trek_get_user_order_info($userInfo->ID, $order_id);
								$is_primary = isset($order_details[0]['guest_is_primary']) ? $order_details[0]['guest_is_primary'] : 0; 
								$product = wc_get_product($product_id);
								$trip_sdate = $product->get_attribute( 'pa_start-date' ); 
								$trip_edate = $product->get_attribute( 'pa_end-date' );
								$sdate_obj = explode('/', $trip_sdate);
									$sdate_info = array(
										'd' => $sdate_obj[0],
										'm' => $sdate_obj[1],
										'y' => substr(date('Y'),0,2).$sdate_obj[2]
									);
									$edate_obj = explode('/', $trip_edate);
									$edate_info = array(
										'd' => $edate_obj[0],
										'm' => $edate_obj[1],
										'y' => substr(date('Y'),0,2).$edate_obj[2]
									);
									$start_date_text = date('F jS, Y', strtotime(implode('-', $sdate_info)));
									$end_date_text_1 = date('F jS, Y', strtotime(implode('-', $edate_info)));
									$end_date_text_2 = date('jS, Y', strtotime(implode('-', $edate_info)));
									$date_range_1 = $start_date_text. ' - '.$end_date_text_2;
									$date_range_2 = $start_date_text. ' - '.$end_date_text_1;
									$date_range = $date_range_1;
									if( $sdate_info['m'] != $edate_info['m'] ){
										$date_range = $date_range_2;
									}
								$product_image_url = get_template_directory_uri() . '/assets/images/TT-Logo.png';
								$trip_sku = $product ? $product->get_sku() : '';
								$parentTrip = tt_get_parent_trip($trip_sku);
								$trip_link = 'javascript:';
								$tripRegion = tt_get_local_trips_detail('tripRegion', '', $trip_sku, true);
								$parent_product_id = $pa_city = '';
								$parent_product_id = tt_get_parent_trip_id_by_child_sku($trip_sku);
								if( $parent_product_id ){
									$p_product = wc_get_product( $parent_product_id );
									$pa_city = $p_product ? $p_product->get_attribute( 'pa_city' ) : '';
								}
								if ($order_id) {
									$trip_link = esc_url(add_query_arg('order_id', $order_id, get_permalink(TREK_MY_ACCOUNT_PID) . 'my-trip'));
								}
								$link_html = '';
								if ($order_id && $trip_link != 'javascript:' && $is_primary == 1 ) {
                                  	$product_permalink = get_permalink($product_id); 
    								$review_link = $product_permalink . '#reviews';
									$link_html .= '<div class="trip-details-cta my-4 my-lg-0">
										<button class="btn btn-lg btn-primary rounded-1"><a href="' . $trip_link . '">View details</a></button>
										<p><a class="fw-normal fs-sm lh-sm ms-2" href="' . esc_url($review_link) . '" target="_blank">Leave a review</a></p>
									</div>';
								}
								$trip_address = [$pa_city,$tripRegion];
								$trip_address = array_filter($trip_address);
								$past_trips_html .= '<div class="trips-list-item past-trip-item">
							<div class="trip-image">
								<img src="' . $parentTrip['image'] . '">
							</div>
							<div class="trip-info">
								<p class="fw-normal fs-sm lh-sm mb-0 mt-4">'.implode(', ', $trip_address).'</p>
								<h5 class="fw-semibold"><a href="' . $trip_link . '">' . $trip_name . '</a></h5>
								<p class="fw-medium fs-sm lh-sm">' . $date_range . '</p>
							</div>
							<div class="trip-details-cta my-4 my-lg-0">
								<a class="btn btn-lg btn-primary rounded-1" href="' . $trip_link . '">View details</a>
								<p><a class="fw-normal fs-sm lh-sm ms-2" href="' . esc_url($review_link) . '" target="_blank">Leave a review</a></p>
							</div>
						</div><hr>';
							}
						}
					}
				} else {
					$past_trips_html .= '<p class="no-trip-text mt-2">Past Trips coming soon!</p>';
				}
				echo $past_trips_html;
				?>
			</div>
		</div>
	</div>

	<div class="row mx-0">
		<div class="col-lg-10">
			<div class="card dashboard__card rounded-1">
				<h5 class="fw-semibold past-title">Cancelled Trips</h5>
				<?php
					$cancelled_trips_html = '';
					$cancelled_trips      = tt_get_cancelled_guest_trips( get_current_user_id(), '', $is_log );
					if ( ! empty( $cancelled_trips ) && isset( $cancelled_trips['count'] ) && $cancelled_trips['count'] != 0 && is_user_logged_in() ) {
						if ( $cancelled_trips && isset( $cancelled_trips['data'] ) ) {
							foreach ( $cancelled_trips['data'] as $cancelled_trip ) {
								$trip_name  = $trip_sdate = $trip_edate = $trip_sku = '';
								$product_id = $cancelled_trip['product_id'];
								$trip_name  = $cancelled_trip['trip_name'];
								if ( $trip_name ) {
									$order_id      = $cancelled_trip['order_id'];
									$order_details = trek_get_user_order_info( $userInfo->ID, $order_id );
									$is_primary    = isset( $order_details[0]['guest_is_primary']) ? $order_details[0]['guest_is_primary'] : 0; 
									$product       = wc_get_product( $product_id );
									$trip_sdate    = $product->get_attribute( 'pa_start-date' ); 
									$trip_edate    = $product->get_attribute( 'pa_end-date' );
									$sdate_obj     = explode( '/', $trip_sdate );
									$sdate_info    = array(
										'd' => $sdate_obj[0],
										'm' => $sdate_obj[1],
										'y' => substr(date('Y'),0,2).$sdate_obj[2]
									);
									$edate_obj  = explode( '/', $trip_edate );
									$edate_info = array(
										'd' => $edate_obj[0],
										'm' => $edate_obj[1],
										'y' => substr(date('Y'),0,2).$edate_obj[2]
									);
									$start_date_text = date('F jS, Y', strtotime(implode('-', $sdate_info)));
									$end_date_text_1 = date('F jS, Y', strtotime(implode('-', $edate_info)));
									$end_date_text_2 = date('jS, Y', strtotime(implode('-', $edate_info)));
									$date_range_1    = $start_date_text. ' - '.$end_date_text_2;
									$date_range_2    = $start_date_text. ' - '.$end_date_text_1;
									$date_range      = $date_range_1;
									if( $sdate_info['m'] != $edate_info['m'] ) {
										$date_range = $date_range_2;
									}
									$product_image_url = get_template_directory_uri() . '/assets/images/TT-Logo.png';
									$trip_sku          = $product ? $product->get_sku() : '';
									$parentTrip        = tt_get_parent_trip($trip_sku);
									$trip_link         = 'javascript:';
									$tripRegion        = tt_get_local_trips_detail( 'tripRegion', '', $trip_sku, true );
									$parent_product_id = $pa_city = '';
									$parent_product_id = tt_get_parent_trip_id_by_child_sku($trip_sku);
									if( $parent_product_id ){
										$p_product = wc_get_product( $parent_product_id );
										$pa_city   = $p_product ? $p_product->get_attribute( 'pa_city' ) : '';
									}
									if ( $order_id ) {
										$trip_link = esc_url(add_query_arg('order_id', $order_id, get_permalink(TREK_MY_ACCOUNT_PID) . 'my-trip'));
									}
									$trip_address = [$pa_city,$tripRegion];
									$trip_address = array_filter( $trip_address );
									$cancelled_trips_html .= '<div class="trips-list-item past-trip-item">
										<div class="trip-image">
											<img src="' . $parentTrip['image'] . '">
										</div>
										<div class="trip-info">
											<p class="fw-normal fs-sm lh-sm mb-0 mt-4">'.implode( ', ', $trip_address ).'</p>
											<h5 class="fw-semibold">' . $trip_name . '</h5>
											<p class="fw-medium fs-sm lh-sm">' . $date_range . '</p>
											<i class="bi bi-info-circle me-3 text-danger"></i><p class="fw-normal fs-sm lh-sm d-inline text-danger">This trip was cancelled!</p>
										</div>
									</div><hr>';
								}
							}
						}
					} else {
						$cancelled_trips_html .= '<p class="no-trip-text mt-2">You don&apos;t have Cancelled Trips Yet.</p>';
					}
					echo $cancelled_trips_html;
					?>
			</div>
		</div>
	</div>
</div>

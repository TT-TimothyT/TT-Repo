<?php
/**
 * NetSuite booking status control system
 * for better visibility on the Orders listing page in the admin panel.
 *
 * Adding custom columns to the table with the orders for the Booking ID and Booking Status.
 *
 * Adding NetSuite Bookings Details section in the Order Edit page with links to NetSuite items.
 * 
 * Store the booking status in the order meta,
 * through different stages of the booking creation process lifecycle in NetSuite,
 * after the successful order.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Booking Status Controller Class.
 *
 * @since 1.0.0
 */
class TT_Booking_Status_Controller {
	private static $instance                  = null;
	private static $booking_id_meta_key       = TT_WC_META_PREFIX . 'guest_booking_id';
	private static $bookings_table_key        = 'guest_bookings';
	private static $tt_ns_bookings_path       = 'app/accounting/transactions/salesord.nl';
	private static $tt_ns_cust_rec_entry_path = 'app/common/custom/custrecordentry.nl';
	private static $tt_ns_guest_path          = 'app/common/entity/custjob.nl';
	private static $tt_ns_service_item_path   = 'app/common/item/item.nl';
	private static $tt_ns_base_url            = '';

	/**
	 * Get class instance.
	 */
	public static function ttnsw_get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new TT_Booking_Status_Controller();
		}
		return self::$instance;
	}

	/**
	 * Consturct Function.
	 */
	public function __construct() {

		add_action( 'manage_edit-shop_order_columns', array( $this, 'tt_booking_status_column_set' ), 20, 1 );

		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'tt_booking_status_column_display' ), 20, 2 );

		add_filter( 'manage_edit-shop_order_sortable_columns', array( $this, 'tt_booking_status_column_sort' ) );

		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'tt_editable_order_meta_general' ) );

		add_action( 'tt_set_ns_booking_status', array( $this, 'tt_set_ns_booking_status_cb' ), 10, 2 );

		add_action( 'tt_set_ns_tpp_status', array( $this, 'tt_set_ns_tpp_status_cb' ), 10, 2 );

		if ( ! defined( 'DX_DEV' ) ) {
			// Live NetSuite
			self::$tt_ns_base_url = 'https://661527.app.netsuite.com/';
		} else {
			// Sandbox NetSuite
			self::$tt_ns_base_url = 'https://661527-sb2.app.netsuite.com/';
		}
	}

	/**
	 * Maybe get the related order ID.
	 *
	 * If the order is a Travel Protection order,
	 * then get the related order ID from the protection data,
	 * otherwise return the original order ID.
	 * 
	 * @param int $order_id The original order ID.
	 * @return int The related order ID or the original if no protection data found.
	 */
	private function tt_maybe_get_related_order_id( $order_id ) {
		// Check if there is protection data.
		$protection_data = tt_get_protection_data_by_order_id( $order_id );

		if ( empty( $protection_data ) || ! is_array( $protection_data ) ) {
			// No protection data found, return the original order ID.
			return $order_id;
		}

		if ( ! isset( $protection_data['order_id'] ) || empty( $protection_data['order_id'] ) ) {
			// No related order ID found, return the original order ID.
			return $order_id;
		}

		return (int) $protection_data['order_id'];
	}

	/**
	 * Adding columns.
	 *
	 * @param array $columns Table columns.
	 *
	 * @return array
	 */
	public function tt_booking_status_column_set( $columns ) {
		unset( $columns['origin'] );
		$columns['order_type']     = $this->tt_get_formatted_booking_detail_heading( 'order_type' );
		$columns['related_orders'] = $this->tt_get_formatted_booking_detail_heading( 'related_orders' );
		$columns['trip_code']      = $this->tt_get_formatted_booking_detail_heading( 'trip_code' );
		$columns['ns_status']      = $this->tt_get_formatted_booking_detail_heading( 'ns_status' );
		$columns['booking_id']     = $this->tt_get_formatted_booking_detail_heading( 'booking_id' );
		$columns['guest_count']    = $this->tt_get_formatted_booking_detail_heading( 'guest_count' );

		return $columns;
	}

	/**
	 * Populating columns with data.
	 *
	 * @param string $column_name Column name.
	 * @param int    $order_id Order ID
	 *
	 * @return void
	 */
	public function tt_booking_status_column_display( $column_name, $order_id ) {
		// Keep this before the order ID is changed.
		if ( 'order_type' === $column_name ) {
			$order_type = $this->tt_get_order_type( $order_id );

			if ( ! empty( $order_type ) ) {
				printf( '<span style="color:#50575e;font-weight:bold;">%s</span>', $order_type['title'] );
			}
		}

		// Display related orders column
		if ( 'related_orders' === $column_name ) {
			$related_orders = tt_get_related_orders( $order_id );
			
			if ( ! empty( $related_orders ) ) {
				echo '<div class="related-orders-list">';
				foreach ( $related_orders as $related_order_id ) {
					if ( $related_order_id === $order_id ) {
						continue; // Skip self
					}
					
					$related_order = wc_get_order( $related_order_id );
					if ( $related_order ) {
						$order_type = $this->tt_get_order_type( $related_order_id );
						$order_type_class = sanitize_html_class( strtolower( str_replace( ' ', '-', $order_type['title'] ) ) );

						printf(
							'<a href="%s" class="related-order-link %s tips" data-tip="%s" target="_blank">#%s</a>',
							esc_url( admin_url( 'post.php?post=' . absint( $related_order_id ) . '&action=edit' ) ),
							esc_attr( $order_type_class ),
							esc_attr( sprintf( __( 'Related %s Order #%s', 'trek-travel-theme' ), $order_type['title'], $related_order_id ) ),
							esc_html( $related_order_id )
						);
					}
				}
				echo '</div>';
			} else {
				echo '<span class="na">—</span>';
			}
		}

		// Store the original order ID to use it later.
		$this_order_id = $order_id;
		// Get related order ID if the order is a Travel Protection order.
		$order_id = $this->tt_maybe_get_related_order_id( $order_id );

		if ( 'booking_id' === $column_name ) {
			$ns_booking_id = get_post_meta( $order_id, self::$booking_id_meta_key, true );

			if ( $ns_booking_id ) {
				printf( '<strong style="color:#50575e;">%s</strong>', $ns_booking_id );
			}
		}

		if ( 'ns_status' === $column_name ) {
			$order_type = $this->tt_get_order_type( $this_order_id );
			switch ( $order_type['type'] ) {
				case 'booking_order':
					// Booking Order.
					$this->tt_print_the_booking_status_badge( $order_id );
					break;
				case 'tpp_order':
					// Travel Protection Order.
					$this->tt_print_the_tpp_status_badge( $this_order_id );
					break;
				default:
					// Other order types.
					echo '<span class="na">—</span>';
			}
		}

		if ( 'guest_count' === $column_name ) {
			$guest_reg_data = $this->tt_get_guest_registrations( $order_id );

			if( ! empty( $guest_reg_data ) ) {
				printf( '<span style="%s" class="tips" data-tip="%s"><strong style="color:#50575e;">%s</strong></span>', 'border: 1px dashed #50575e;border-radius: 50%;width: 1.3rem;height: 1.3rem;display: flex;align-items: center;justify-content: center;cursor: inherit !important;', 'Guest Registrations: ' . implode( ',&nbsp;', $guest_reg_data['guest_registrations'] ), (int) $guest_reg_data['guest_count'] );
			}
		}

		if ( 'trip_code' === $column_name ) {
			$trip_code = $this->tt_get_trip_code( $order_id );

			if( ! empty( $trip_code ) ) {
				printf( '<span style="color:#50575e;">%s</span>', $trip_code );
			}
		}
	}

	/**
	 * Make sortable columns.
	 *
	 * @param array $sortable_columns Sortable columns.
	 *
	 * @return array
	 */
	public function tt_booking_status_column_sort( $sortable_columns )  {
		$sortable_columns['booking_id']  = 'booking_id';

		return $sortable_columns;
	}

	/**
	 * Add additional fields with Booking details
	 * under the General column on the order editing page.
	 *
	 * @uses tt_get_trip_pid_sku_by_orderId();
	 *
	 * @param object $order The currently editing shop order.
	 */
	public function tt_editable_order_meta_general( $order ) {
		$this_order_id  = $order->get_id();
		$order_type     = $this->tt_get_order_type( $this_order_id );
		$related_orders = tt_get_related_orders( $this_order_id );
		$order_id       = $this->tt_maybe_get_related_order_id( $this_order_id );
		$order          = wc_get_order( $order_id );
		$ns_booking_id  = $order->get_meta( self::$booking_id_meta_key );
		$guest_reg_data = $this->tt_get_guest_registrations( $order_id );
		$ns_guests_data = $this->tt_get_ns_guests( $order_id );
		$trip_code      = $this->tt_get_trip_code( $order_id );

		?>
			<br class="clear" />
			<h3><?php esc_html_e( 'NetSuite Booking Details', 'trek-travel-theme' ); ?></h3>
			<div class="address">
				<table class="wp-list-table widefat fixed striped table-view-list posts" style="border-radius: 4px;">
					<tbody>
						<tr>
							<th><strong><?php $this->tt_get_formatted_booking_detail_heading( 'order_type', true ) ?></strong></th>
							<td><strong><?php echo esc_html( $order_type['title'] ); ?></strong></td>
						</tr>
						<?php if ( ! empty( $related_orders ) ) : ?>
							<tr>
								<th><strong><?php $this->tt_get_formatted_booking_detail_heading( 'related_orders', true ) ?></strong></th>
								<td class="related-orders-list">
									<?php foreach ( $related_orders as $related_order_id ) :
										$related_order = wc_get_order( $related_order_id );
										if ( $related_order ) :
											$related_order_type = $this->tt_get_order_type( $related_order_id );
									?>
										<a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $related_order_id ) . '&action=edit' ) ); ?>" target="_blank"
											class="button <?php echo esc_attr( $related_order_type['type'] === 'tpp_order' ? 'button-secondary' : 'button-primary' ); ?> tips" 
											data-tip="<?php echo esc_attr( sprintf( __( 'See the related %s order #%s', 'trek-travel-theme' ), $related_order_type['title'], $related_order_id ) ); ?>">
											<strong>#<?php echo esc_html( $related_order_id ); ?> (<?php echo esc_html( $related_order_type['title'] ); ?>)</strong>
										</a>
									<?php endif; endforeach; ?>
								</td>
							</tr>
						<?php endif; ?>
						<tr>
							<th><strong><?php $this->tt_get_formatted_booking_detail_heading( 'ns_status', true ) ?></strong></th>
							<td>
								<?php
									switch ( $order_type['type'] ) {
										case 'booking_order':
											// Booking Order.
											$this->tt_print_the_booking_status_badge( $order_id );
											break;
										case 'tpp_order':
											// Travel Protection Order.
											$this->tt_print_the_tpp_status_badge( $this_order_id );
											break;
										default:
											// Other order types.
											echo '<span class="na">—</span>';
									}
								?>
							</td>
						</tr>
						<?php
							if( $ns_booking_id ) :
								$booking_url_args = array( 'id' => (int) $ns_booking_id, 'whence' => '' );
								$booking_url      = add_query_arg( $booking_url_args, self::$tt_ns_base_url . self::$tt_ns_bookings_path );
							?>
								<tr>
									<th><strong><?php $this->tt_get_formatted_booking_detail_heading( 'booking_id', true ) ?></strong></th>
									<td><a href="<?php echo esc_url( $booking_url ) ?>" target="_blank" rel="noopener noreferrer" class="button button-primary tips" data-tip="<?php echo esc_attr( __( 'See the Booking in NetSuite', 'trek-travel-theme' ) ); ?>"><strong><?php echo esc_html( $ns_booking_id ) ?></strong></a></td>
								</tr>
							<?php
							endif;
						?>
						<?php
							if( $guest_reg_data ) :
							?>
								<tr>
									<th><strong><?php $this->tt_get_formatted_booking_detail_heading( 'guest_registrations', true ) ?></strong></th>
									<td class="order-guest-reg-list">
										<?php
											foreach( $guest_reg_data['guest_registrations'] as $index => $guest_reg_id ) :
												if ( ! empty( $guest_reg_id ) ) :
													$guest_reg_url_args = array( 'rectype' => 246, 'id' => (int) $guest_reg_id, 'whence' => '' );
													$guest_reg_url      = add_query_arg( $guest_reg_url_args, self::$tt_ns_base_url . self::$tt_ns_cust_rec_entry_path );
													$is_guest_protected = tt_get_protected_status( $order_id, $guest_reg_id );
												?>
													<a href="<?php echo esc_url( $guest_reg_url ) ?>" target="_blank" rel="noopener noreferrer" class="button <?php echo esc_attr( 0 === $index ? 'button-primary' : 'button-secondary' ); ?> tips order-guest-reg" data-tip="<?php echo esc_attr( 0 === $index ? __( 'See the Primary Guest Registration in NetSuite', 'trek-travel-theme' ) : __( 'See the Secondary Guest Registrations in NetSuite', 'trek-travel-theme' ) ); ?>"><strong><?php echo esc_html( $guest_reg_id ) ?></strong>
														<?php if ( $is_guest_protected ) : ?>
															<span class="tpp-protected-status guest-protected"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/netsuite/tpp-protected.svg' ) ?>" alt="shield-icon"></span>
														<?php else : ?>
															<span class="tpp-protected-status guest-not-protected"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/netsuite/tpp-not-protected.svg' ) ?>" alt="shield-icon"></span>
														<?php endif; ?>
													</a>
												<?php
												endif;
											endforeach;
										?>
									</td>
								</tr>
							<?php
							endif;
						?>
						<?php
							if( $ns_guests_data ) :
							?>
								<tr>
									<th><strong><?php $this->tt_get_formatted_booking_detail_heading( 'ns_guests', true ) ?></strong></th>
									<td>
										<?php 
											foreach( $ns_guests_data['ns_guests'] as $index => $ns_guest_id ) :
												if ( ! empty( $ns_guest_id ) ) :
													$ns_guest_url_args = array( 'id' => (int) $ns_guest_id, 'whence' => '' );
													$ns_guest_url      = add_query_arg( $ns_guest_url_args, self::$tt_ns_base_url . self::$tt_ns_guest_path );
												?>
													<a href="<?php echo esc_url( $ns_guest_url ) ?>" target="_blank" rel="noopener noreferrer" class="button <?php echo esc_attr( 0 === $index ? 'button-primary' : 'button-secondary' ); ?> tips" data-tip="<?php echo esc_attr( 0 === $index ? __( 'See the Primary Guest in NetSuite', 'trek-travel-theme' ) : __( 'See the Secondary Guests in NetSuite', 'trek-travel-theme' ) ); ?>"><strong><?php echo esc_html( $ns_guest_id ) ?></strong></a>
												<?php
												endif;
											endforeach;
										?>
									</td>
								</tr>
							<?php
							endif;
						?>
						<?php
							if( $trip_code ) :
								$trip_info             = tt_get_trip_pid_sku_by_orderId( $order_id );
								$trip_id               = $trip_info['ns_trip_Id'];
								$service_item_url_args = array( 'id' => (int) $trip_id );
								$service_item_url      = add_query_arg( $service_item_url_args, self::$tt_ns_base_url . self::$tt_ns_service_item_path );
							?>
								<tr>
									<th><strong><?php $this->tt_get_formatted_booking_detail_heading( 'trip_code', true ) ?></strong></th>
									<td><a href="<?php echo esc_url( $service_item_url ) ?>" target="_blank" rel="noopener noreferrer" class="button button-primary tips" data-tip="<?php echo esc_attr( __( 'See the Service Item in NetSuite', 'trek-travel-theme' ) ); ?>"><strong><?php echo esc_html( $trip_code ) ?></strong></a></td>
								</tr>
							<?php
							endif;
						?>
					</tbody>
				</table>
			</div>
		<?php 
	}

	/**
	 * Get Booking cancelled status from the bookings table.
	 *
	 * @param int $order_id The Order ID.
	 *
	 * @return mixed null on validation failure or no results; string if the entire booking is cancelled, or array if only registrations are cancelled.
	 */
	private function tt_get_booking_cancelled_status( $order_id = 0 ) {
		if( empty( $order_id ) ) {
			return null;
		}

		global $wpdb;
		$table_name           = $wpdb->prefix . 'guest_bookings';
		$sql                  = "SELECT guestRegistrationId, is_guestreg_cancelled FROM {$table_name} WHERE order_id={$order_id}";
		$results              = $wpdb->get_results( $sql, ARRAY_A );

		if( empty( $results ) ) {
			// Order not found in the bookings table.
			return null;
		}

		$guest_count          = count( $results );
		$cancelled_guest_regs = array();
		$active_guest_regs    = array();

		foreach( $results as $guest_reg_status_arr ) {
			if( 1 == $guest_reg_status_arr['is_guestreg_cancelled'] ) {
				// Guest reg is cancelled.
				$cancelled_guest_regs[] = $guest_reg_status_arr['guestRegistrationId'];
				continue;
			}
			$active_guest_regs[] = $guest_reg_status_arr['guestRegistrationId'];
		}

		if( $guest_count === count( $cancelled_guest_regs ) ) {
			// Booking is cancelled.
			return 'booking_cancelled';
		}

		if( $guest_count === count( $active_guest_regs ) ) {
			// Booking is active or any other status.
			return null;
		}

		// Particular registrations are cancelled.
		return array(
			'status'         => 'registration_cancelled',
			'guest_count'    => $guest_count,
			'active_regs'    => $active_guest_regs,
			'cancelled_regs' => $cancelled_guest_regs
		);
	}

	/**
	 * Print the Booking status badge.
	 *
	 * @param int $order_id The id Of the Order.
	 */
	private function tt_print_the_booking_status_badge( $order_id ) {
		// Get related order ID if the order is a Travel Protection order.
		$order_id = $this->tt_maybe_get_related_order_id( $order_id );

		// First check for cancelled status from the bookings table.
		$cancelled_status = $this->tt_get_booking_cancelled_status( $order_id );

		if ( ! empty( $cancelled_status ) ) {
			if ( is_array( $cancelled_status ) ) {
				// Registration Cancelled.
				printf( '<mark class="order-status %s tips" data-tip="%s" style="%s"><span>%s</span></mark>', $cancelled_status['status'], wp_kses_post( TT_BOOKING_STATUSES[$cancelled_status['status']]['tooltip'] ), esc_attr( TT_BOOKING_STATUSES[$cancelled_status['status']]['style'] ), esc_html( TT_BOOKING_STATUSES[$cancelled_status['status']]['title'] ) );
				printf( '<ul><li style="color: #761919;">Cancelled: %s</li><li style="color: #5b841b;">Active: %s</li></ul>', implode( ', ', $cancelled_status['cancelled_regs'] ), implode( ', ', $cancelled_status['active_regs'] ) );
			} else {
				// Booking Cancelled.
				printf( '<mark class="order-status %s tips" data-tip="%s" style="%s"><span>%s</span></mark>', $cancelled_status, wp_kses_post( TT_BOOKING_STATUSES[$cancelled_status]['tooltip'] ), esc_attr( TT_BOOKING_STATUSES[$cancelled_status]['style'] ), esc_html( TT_BOOKING_STATUSES[$cancelled_status]['title'] ) );
			}
		} else {
			// Some status
			$ns_booking_status = get_post_meta( $order_id, TT_WC_META_PREFIX . 'guest_booking_status', true );

			if ( ! empty( $ns_booking_status ) ) {
				// Has some status in the order's meta, so we can work with it.
				if( in_array( $ns_booking_status, array_keys( TT_BOOKING_STATUSES ) ) ) {
					printf( '<mark class="order-status %s tips" data-tip="%s" style="%s"><span>%s</span></mark>', $ns_booking_status, wp_kses_post( TT_BOOKING_STATUSES[$ns_booking_status]['tooltip'] ), esc_attr( TT_BOOKING_STATUSES[$ns_booking_status]['style'] ), esc_html( TT_BOOKING_STATUSES[$ns_booking_status]['title'] ) );
				} else {
					// This Status not defined in the TT_BOOKING_STATUSES.
					printf( '<mark class="order-status %s tips" data-tip="%s" style="%s"><span>%s</span></mark>', esc_attr( TT_BOOKING_STATUSES['booking_unknown'] ), wp_kses_post( TT_BOOKING_STATUSES['booking_unknown']['tooltip'] ), esc_attr( TT_BOOKING_STATUSES['booking_unknown']['style'] ), esc_html( TT_BOOKING_STATUSES['booking_unknown']['title'] ) );
				}

			} else {
				// Can Build status based on the booking id in the order's meta. Here are available two possible status, success or faild.
				$ns_booking_id = get_post_meta( $order_id, TT_WC_META_PREFIX . 'guest_booking_id', true );

				if( ! empty( $ns_booking_id ) ) {
					// Success.
					printf( '<mark class="order-status booking_success tips" data-tip="%s" style="%s"><span>%s</span></mark>', wp_kses_post( TT_BOOKING_STATUSES['booking_success']['tooltip'] ), esc_attr( TT_BOOKING_STATUSES['booking_success']['style'] ), esc_html( TT_BOOKING_STATUSES['booking_success']['title'] ) );
				} else {
					// Faild.
					printf( '<mark class="order-status booking_failed tips" data-tip="%s" style="%s"><span>%s</span></mark>', wp_kses_post( TT_BOOKING_STATUSES['booking_failed']['tooltip'] ), esc_attr( TT_BOOKING_STATUSES['booking_failed']['style'] ), esc_html( TT_BOOKING_STATUSES['booking_failed']['title'] ) );
				}
			}
		}
	}

	/**
	 * Print the Travel Protection status badge.
	 *
	 * @param int $order_id The id Of the Order.
	 */
	private function tt_print_the_tpp_status_badge( $order_id ) {
		// Some status
		$ns_tpp_status = get_post_meta( $order_id, TT_WC_META_PREFIX . 'tpp_status', true );

		if ( ! empty( $ns_tpp_status ) ) {
			// Has some status in the order's meta, so we can work with it.
			if ( in_array( $ns_tpp_status, array_keys( TT_TPP_STATUSES ) ) ) {
				printf( '<mark class="order-status %s tips" data-tip="%s" style="%s"><span>%s</span></mark>', $ns_tpp_status, wp_kses_post( TT_TPP_STATUSES[$ns_tpp_status]['tooltip'] ), esc_attr( TT_TPP_STATUSES[$ns_tpp_status]['style'] ), esc_html( TT_TPP_STATUSES[$ns_tpp_status]['title'] ) );
			} else {
				// This Status not defined in the TT_TPP_STATUSES.
				printf( '<mark class="order-status %s tips" data-tip="%s" style="%s"><span>%s</span></mark>', esc_attr( TT_TPP_STATUSES['tpp_unknown'] ), wp_kses_post( TT_TPP_STATUSES['tpp_unknown']['tooltip'] ), esc_attr( TT_TPP_STATUSES['tpp_unknown']['style'] ), esc_html( TT_TPP_STATUSES['tpp_unknown']['title'] ) );
			}
		} else {
			echo '<span class="na">—</span>';
		}
	}

	/**
	 * Get or Print formatted heading for given booking detail heading with an icon.
	 *
	 * @param string $heading_id The ID for booking detail heading.
	 *
	 * @return string|null HTML with the formatted heading with an icon or null if the heading not found.
	 */
	private function tt_get_formatted_booking_detail_heading( $heading_id, $display = false ) {
		$formatted_heading = '';

		switch ( $heading_id ) {
			case 'trip_code':
				$formatted_heading = sprintf( '<span style="%s"><img width="25" src="%s"/><span>%s</span></span>', esc_attr( 'display: flex;align-items: center;gap:6px;' ), esc_url( get_template_directory_uri() . '/assets/images/netsuite/item.svg' ), __( 'Trip Code', 'trek-travel-theme' ) );
				break;
			case 'ns_status':
				$formatted_heading = sprintf( '<span style="%s"><img width="25" src="%s" style="%s"/><span>%s</span></span>', esc_attr( 'display: flex;align-items: center;gap:6px;' ), esc_url( get_template_directory_uri() . '/assets/images/netsuite/netsuite-fav.png' ), esc_attr( 'border: 1px solid #36677d;border-radius: 50%;' ), __( 'NS Status', 'trek-travel-theme' ) );
				break;
			case 'booking_id':
				$formatted_heading = sprintf( '<span style="%s"><img width="25" src="%s"/><span>%s</span></span>', esc_attr( 'display: flex;align-items: center;gap:6px;' ), esc_url( get_template_directory_uri() . '/assets/images/netsuite/salesorder.svg' ), __( 'Booking ID', 'trek-travel-theme' ) );
				break;
			case 'guest_count':
				$formatted_heading = sprintf( '<span style="%s"><img width="25" src="%s"/><span>%s</span></span>', esc_attr( 'display: flex;align-items: center;gap:6px;' ), esc_url( get_template_directory_uri() . '/assets/images/netsuite/customer.svg' ), __( 'Guest Count', 'trek-travel-theme' ) );;
				break;
			case 'guest_registrations':
				$formatted_heading = sprintf( '<span style="%s"><img width="25" src="%s"/><span>%s</span></span>', esc_attr( 'display: flex;align-items: center;gap:6px;' ), esc_url( get_template_directory_uri() . '/assets/images/netsuite/file.svg' ), __( 'Guest Registrations', 'trek-travel-theme' ) );;
				break;
			case 'ns_guests':
				$formatted_heading = sprintf( '<span style="%s"><img width="25" src="%s"/><span>%s</span></span>', esc_attr( 'display: flex;align-items: center;gap:6px;' ), esc_url( get_template_directory_uri() . '/assets/images/netsuite/customer.svg' ), __( 'NS Guests', 'trek-travel-theme' ) );;
				break;
			case 'order_type':
				$formatted_heading = sprintf( '<span style="%s"><img width="25" src="%s"/><span>%s</span></span>', esc_attr( 'display: flex;align-items: center;gap:6px;' ), esc_url( get_template_directory_uri() . '/assets/images/netsuite/order-type.svg' ), __( 'Order Type', 'trek-travel-theme' ) );;
				break;
			case 'related_orders':
				$formatted_heading = sprintf( '<span style="%s"><img width="25" src="%s"/><span>%s</span></span>', esc_attr( 'display: flex;align-items: center;gap:6px;' ), esc_url( get_template_directory_uri() . '/assets/images/netsuite/related-orders.svg' ), __( 'Related Orders', 'trek-travel-theme' ) );;
				break;
			default:
				// Nothing by default.
				break;
		}

		if( $display ) {
			echo $formatted_heading;
			return;
		}
		return $formatted_heading;
	}

	/**
	 * Take Guest Count and Registrations from the bookings table.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return array|null Array with (int) guest_count and (array) guest_registrations or null if there is no results.
	 */
	private function tt_get_guest_registrations( $order_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$bookings_table_key;
		$sql        = "SELECT guestRegistrationId, trip_number_of_guests FROM {$table_name} WHERE order_id={$order_id} ORDER BY guest_index_id ASC";
		$results    = $wpdb->get_results( $sql, ARRAY_A );

		if( ! empty( $results ) ) {
			return array(
				'guest_count'         => (int) $results[0]['trip_number_of_guests'],
				'guest_registrations' => array_column( $results, 'guestRegistrationId' ),
			);
		}

		return null;
	}

	/**
	 * Take The Trip Code from the bookings table.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return string|null Trip Code or null if there is no results.
	 */
	private function tt_get_trip_code( $order_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$bookings_table_key;
		$sql        = "SELECT DISTINCT trip_code FROM {$table_name} WHERE order_id={$order_id}";
		$results    = $wpdb->get_results( $sql, ARRAY_A );
		if( ! empty( $results ) ) {
			$trip_code = $results[0]['trip_code'];

			if( ! empty( $trip_code ) ) {
				// Get a trip code without suffix.
				$trip_code = tt_get_local_trip_code( $trip_code );
				return $trip_code;
			}
		}

		return null;
	}

	/**
	 * Take Guest Count and NS Guests from the bookings table.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return array|null Array with (int) guest_count and (array) guest_registrations or null if there is no results.
	 */
	private function tt_get_ns_guests( $order_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$bookings_table_key;
		$sql        = "SELECT netsuite_guest_registration_id, trip_number_of_guests FROM {$table_name} WHERE order_id={$order_id} ORDER BY guest_index_id ASC";
		$results    = $wpdb->get_results( $sql, ARRAY_A );

		if( ! empty( $results ) ) {
			return array(
				'guest_count' => (int) $results[0]['trip_number_of_guests'],
				'ns_guests'   => array_column( $results, 'netsuite_guest_registration_id' ),
			);
		}

		return null;
	}

	/**
	 * Take The booking status for the given order,
	 * based on the stored order meta with the status
	 * or rely on the booking ID stored to the order's meta.
	 *
	 * @param int $order_id The Order ID.
	 *
	 * @return string Booking status.
	 */
	public function tt_get_booking_status( $order_id ) {
		 // Get related order ID checking for travel protection
		$order_id = $this->tt_maybe_get_related_order_id( $order_id );

		// First check for cancelled status from the bookings table.
		$cancelled_status = $this->tt_get_booking_cancelled_status( $order_id );

		if( ! empty( $cancelled_status ) ) {
			if ( is_array( $cancelled_status ) ) {
				// Registration Cancelled.
				return 'registration_cancelled';
			} else {
				// Booking Cancelled.
				return 'booking_cancelled';
			}
		} else {
			// Some status
			$ns_booking_status = get_post_meta( $order_id, TT_WC_META_PREFIX . 'guest_booking_status', true );

			if( ! empty( $ns_booking_status ) ) {
				// Has some status in the order's meta, so we can work with it.
				if( in_array( $ns_booking_status, array_keys( TT_BOOKING_STATUSES ) ) ) {
					return $ns_booking_status; // booking_success, booking_failed, booking_pending or booking_onhold.
				} else {
					// This Status not defined in the TT_BOOKING_STATUSES.
					return 'booking_unknown';
				}
			} else {
				// Can Build status based on the booking id in the order's meta. Here are available two possible status, success or faild.
				$ns_booking_id = get_post_meta( $order_id, TT_WC_META_PREFIX . 'guest_booking_id', true );

				if( ! empty( $ns_booking_id ) ) {
					// Success.
					return 'booking_success';
				} else {
					// Faild.
					return 'booking_failed';
				}
			}
		}
	}

	/**
	 * Get the order type based on the order ID.
	 *
	 * @param int $order_id The Order ID.
	 *
	 * @uses tt_has_travel_protection()
	 *
	 * @return array Order type from TT_ORDER_TYPES.
	 *              'booking' for regular bookings,
	 *              'tpp' for Travel Protection orders.
	 */
	public function tt_get_order_type( $order_id ) {
		return tt_has_travel_protection( $order_id ) ? TT_ORDER_TYPES['tpp'] : TT_ORDER_TYPES['booking'];
	}

	/**
	 * Store the Booking status in the orders meta.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $booking_status Current Booking status.
	 *
	 * @return void
	 */
	public function tt_set_ns_booking_status_cb( $order_id = 0,  $booking_status = 'booking_unknown' ) {
		if( empty( $order_id ) ) {
			return;
		}

		update_post_meta( $order_id, TT_WC_META_PREFIX . 'guest_booking_status', $booking_status );
	}

	/**
	 * Store the TPP status in the orders meta.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $tpp_status Current TPP status.
	 *
	 * @return void
	 */
	public function tt_set_ns_tpp_status_cb( $order_id = 0, $tpp_status = 'tpp_unknown' ) {
		if ( empty( $order_id ) ) {
			return;
		}

		update_post_meta( $order_id, TT_WC_META_PREFIX . 'tpp_status', $tpp_status );
	}
}

/**
 * Make an instance of TT Booking Status Controller.
 */
function TT_Booking_Status_Controller() {
	return TT_Booking_Status_Controller::ttnsw_get_instance();
}

TT_Booking_Status_Controller();

/**
 * Function to take the booking status based on the order ID.
 *
 * @param int $order_id The order ID
 */
function tt_get_booking_status( $order_id = 0 ) {
	if( empty( $order_id ) ) {
		// Order ID is a required parameter.
		return null;
	}

	if( ! method_exists( 'TT_Booking_Status_Controller', 'tt_get_booking_status') ) {
		// Can't proceed if the instance of class or method doesn't exist.
		return null;
	}

	return TT_Booking_Status_Controller()->tt_get_booking_status( $order_id );
}

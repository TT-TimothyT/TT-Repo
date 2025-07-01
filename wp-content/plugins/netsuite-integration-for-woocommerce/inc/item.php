<?php

/**
 * This class handles all API operations related to creating inventory update CRON
 * API Ref : http://tellsaqib.github.io/NSPHP-Doc/index.html
 *
 * Author : Manish Gautam
 */

require_once TMWNI_DIR . 'inc/NS_Restlet/netsuiteRestAPI.php';

use NetSuite\NetSuiteService;

class ItemClient extends CommonIntegrationFunctions {

	public $netsuiteService;
	public $object_id;

	public function __construct() {
		if ( empty( $this->netsuiteService ) && TMWNI_Settings::areCredentialsDefined() ) {
			// Initializing NetSuite service
			$this->netsuiteService = new NetSuiteService( null, array( 'exceptions' => true ) );
		}
	}
	/**
	 * Get product data from NetSuite
	 *
	 * @param string $skus
	 * @param string $map_field_name
	 * @param string $price_level_name
	 * @param string $price_currency
	 * @param string $urlAPIEndPoint
	 */
	public function getProductPriceData( $skus, $map_field_name, $price_level_name, $price_currency, $urlAPIEndPoint, $by_queue ) {
		global $TMWNI_OPTIONS;

		$select_fields = "item.id AS item_internal_id, item.$map_field_name, pricing.*";

		$from_clause = 'FROM item 
		LEFT JOIN pricing ON pricing.item = item.id';

		$where_conditions = array(
			"item.$map_field_name IN ($skus)",
			"pricing.pricelevel = '$price_level_name'",
		);

		if ( ! empty( $price_currency ) ) {
			$where_conditions[] = "currency.id = '$price_currency'";

			$select_fields .= ', currency.id AS currency_code';

			$from_clause .= ' LEFT JOIN currency ON currency.id = pricing.currency';
		}

		$pricing_query = array(
			'q' => "SELECT $select_fields $from_clause WHERE " . implode( ' AND ', $where_conditions ),
		);

		$this->NetsuiteRestAPIClient = new NetsuiteRestAPI();
		$response = $this->NetsuiteRestAPIClient->nsRESTRequest( 'post', $urlAPIEndPoint, true, $pricing_query );

		if ( isset( $response['items'] ) ) {
			update_option( 'tm_rest_web_service_enable', 'yes' );
		}
		if ( isset( $response['items'] ) && ! empty( $response['items'] ) ) {
			$this->updateProductPrice( $response['items'], $skus, $map_field_name, $by_queue );
			/**
					*  Fires after the price update process has been completed, allowing you to modify or log the status.
					*
					* @since 1.6.6
			*/
					do_action( 'tm_ns_after_update_price', $response, $skus, $map_field_name, $urlAPIEndPoint );

		} else {
			if ( isset( $response['o:errorDetails'][0]['o:errorCode'] ) ) {
				$errorCode = $response['o:errorDetails'][0]['o:errorCode'];
			} elseif ( isset( $response['items'] ) && empty( $response['items'] ) ) {
				$errorCode = 'Multiple item sku not found on netsuite';
			} else {
				$errorCode = 'something wrong for price search';

			}
			if ( 'INVALID_LOGIN' == $errorCode ) {
				update_option( 'tm_rest_web_service_enable', 'no' );
			}

			$this->handleLog( 0, 0, 'price_search', $errorCode );
		}

		if ( ! empty( $response['hasMore'] ) ) {
			$count = $response['count'];
			$offset = $response['offset'] + $count;
			$urlAPIEndPoint = "/suiteql?limit=$count&offset=$offset";
			$this->getProductPriceData( $skus, $map_field_name, $price_level_name, $price_currency, $urlAPIEndPoint, $by_queue );
		}

				return true;
	}

	/**
	 * Update product price and quantity
	 *
	 * @param array  $items
	 * @param string $skus
	 * @param string $map_field_name
	 */
	public function updateProductPrice( $items, $all_skus, $map_field_name, $by_queue ) {
		global $TMWNI_OPTIONS;

		foreach ( $items as $item ) {
			$price_update_status = true;
			$sku = $item[ strtolower( $TMWNI_OPTIONS['sku_mapping_field'] ) ];
			$product_id = $this->getProductIdBySku( $sku );
			if ( ! empty( $product_id ) ) {
				/**
					* Filter to modify the status of the price update process.
					*
					* @since 1.6.6
				*/
					$price_update_status  = apply_filters( 'tm_netsuite_price_update_status', $price_update_status, $product_id, $item );
				if ( true == $price_update_status ) {
					$main_price = $item['unitprice'];
					update_post_meta( $product_id, '_regular_price', $main_price );
					$sale_price = get_post_meta( $product_id, '_sale_price', true );
					if ( empty( $sale_price ) ) {
						update_post_meta( $product_id, '_price', $main_price );
					}
					if ( true == $by_queue ) {
						$file_dir = wp_upload_dir();
						$log_file = $file_dir['basedir'] . '/' . TMWNI_Settings::$ns_price_log_file;
						$content = '<p>Product SKU ' . $sku . ' Price updated</p>';
						file_put_contents( $log_file, $content . PHP_EOL, FILE_APPEND );
						$old_count = get_option( 'updated_products_count' );
						$new_count = $old_count + 1;
						$this->updateLogFileContent( $content, $new_count );
					}
					/**
						* Action perform after update price.
						*
						* @since 1.0.0
					*/
					do_action( 'tm_netsuite_after_update_price', $main_price, $product_id );

				}
			}
		}
	}

	public function updateLogFileContent( $content, $new_count ) {
		if ( 0 != $new_count ) {
			update_option( 'updated_products_count', $new_count );
		}
	}

	/**
	 * Get default location inventory
	 *
	 * @param string $skus
	 * @param string $mapFieldName
	 * @param string $urlAPIEndPoint
	 */
	public function getDefaultLocationInventory( $skus, $mapFieldName, $urlAPIEndPoint, $by_queue ) {
		global $TMWNI_OPTIONS;

		$qtyField = strtolower( $TMWNI_OPTIONS['inventorySyncField'] );
		$selected_location_query = array(
			'q' => "SELECT item.$mapFieldName, inventoryitemlocations.location, inventoryitemlocations.$qtyField FROM inventoryitemlocations LEFT JOIN item ON inventoryitemlocations.item = item.id WHERE item.$mapFieldName IN ($skus) AND inventoryitemlocations.location = item.location",
		);

		$this->NetsuiteRestAPIClient = new NetsuiteRestAPI();
		$response = $this->NetsuiteRestAPIClient->nsRESTRequest( 'post', $urlAPIEndPoint, true, $selected_location_query );

		if ( ! empty( $response['items'] ) ) {
			$item_quantity_array = $this->getQuantity( $response['items'], $skus );
			update_option( 'tm_rest_web_service_enable', 'yes' );
			$this->updateWooQuantity( $item_quantity_array, $by_queue );
			/**
					* Action Perform after update inventory.
					*
					* @since 1.0.0
			*/
					do_action( 'tm_ns_after_update_inventory', $response, $skus, $mapFieldName, $urlAPIEndPoint );
		} elseif ( isset( $response['o:errorDetails'][0]['o:errorCode'] ) && 'INVALID_LOGIN' == $response['o:errorDetails'][0]['o:errorCode'] ) {
			update_option( 'tm_rest_web_service_enable', 'no' );
		}

		if ( ! empty( $response['hasMore'] ) ) {
			$count = $response['count'];
			$offset = $response['offset'] + $count;
			$urlAPIEndPoint = "/suiteql?limit=$count&offset=$offset";
			$this->getDefaultLocationInventory( $skus, $mapFieldName, $urlAPIEndPoint, $by_queue );
		}
	}

	/**
	 * Get inventory for all locations
	 *
	 * @return string
	 */
	public function inventoryAllLocations() {
		$inventory_locations = get_option( 'netstuite_locations' );
		$locations = array_keys( $inventory_locations );
		return implode( ', ', array_map( fn( $key ) => "'$key'", $locations ) );
	}

	/**
	 * Get inventory for selected locations
	 *
	 * @return string
	 */
	public function inventorySelectedLocations() {
		global $TMWNI_OPTIONS;
		$unique_netsuite_location_array = array_unique( $TMWNI_OPTIONS['netstuite_locations'] );
		return "'" . join( "', '", $unique_netsuite_location_array ) . "'";
	}

	/**
	 * Get quantity from location data
	 *
	 * @param array  $location_data
	 * @param string $all_sku
	 * @return array
	 */
	public function getQuantity( $location_data, $all_sku ) {
		global $TMWNI_OPTIONS;

		$sku_array = explode( ', ', str_replace( "'", '', $all_sku ) );
		$quantity_field_name = strtolower( $TMWNI_OPTIONS['inventorySyncField'] );
		$map_field_name = strtolower( $TMWNI_OPTIONS['sku_mapping_field'] );
		$grouped_data = array();

		foreach ( $location_data as $item ) {
			$itemid = $item[ $map_field_name ];
			if ( array_key_exists( $quantity_field_name, $item ) ) {
				$quantity = $item[ $quantity_field_name ];
				$grouped_data[ $itemid ] = isset( $grouped_data[ $itemid ] ) ? $grouped_data[ $itemid ] + $quantity : $quantity;
			}
		}

		return $grouped_data;
	}

	/**
	 * Perform NetSuite location query
	 *
	 * @param string $skus
	 * @param string $mapFieldName
	 * @param string $urlAPIEndPoint
	 */
	public function getInventoryFromNetsuite( $skus, $mapFieldName, $urlAPIEndPoint, $by_queue ) {
		global $TMWNI_OPTIONS;

		$qtyField = strtolower( $TMWNI_OPTIONS['inventorySyncField'] );

		$locations = $this->getInventoryLocations( $skus, $mapFieldName, $urlAPIEndPoint );

		$selected_location_query = array(
			'q' =>
			'SELECT item.' . $mapFieldName . ', inventoryitemlocations.location, inventoryitemlocations.' . $qtyField . ',from inventoryitemlocations LEFT JOIN item ON inventoryitemlocations.item = item.id  where item.' . $mapFieldName . ' in (' . $skus . ') and inventoryitemlocations.location in (' . $locations . ')',
		);

		$this->NetsuiteRestAPIClient = new NetsuiteRestAPI();
		$response = $this->NetsuiteRestAPIClient->nsRESTRequest( 'post', $urlAPIEndPoint, true, $selected_location_query );

		if ( ! empty( $response['items'] ) ) {
			$item_quantity_array = $this->getQuantity( $response['items'], $skus );
			update_option( 'tm_rest_web_service_enable', 'yes' );
			$this->updateWooQuantity( $item_quantity_array, $by_queue );
			/**
					* Action perform after update quantity.
					*
					* @since 1.0.0
			*/
					do_action( 'tm_ns_after_update_inventory', $response, $skus, $mapFieldName, $urlAPIEndPoint );
		} elseif ( isset( $response['o:errorDetails'][0]['o:errorCode'] ) && 'INVALID_LOGIN' == $response['o:errorDetails'][0]['o:errorCode'] ) {
			update_option( 'tm_rest_web_service_enable', 'no' );
		}

		if ( ! empty( $response['hasMore'] ) ) {
			$count = $response['count'];
			$offset = $response['offset'] + $count;
			$urlAPIEndPoint = "/suiteql?limit=$count&offset=$offset";
			$this->getInventoryFromNetsuite( $skus, $mapFieldName, $urlAPIEndPoint, $by_queue );
		}
	}

	 /**
	  * Get inventory locations
	  *
	  * @param string $skus
	  * @param string $mapFieldName
	  * @param string $urlAPIEndPoint
	  * @return string
	  */
	private function getInventoryLocations( $skus, $mapFieldName, $urlAPIEndPoint ) {
		global $TMWNI_OPTIONS;

		if ( ! empty( $TMWNI_OPTIONS['inventoryDefaultLocation'] ) ) {
			if ( 1 == $TMWNI_OPTIONS['inventoryDefaultLocation'] ) {
				return $this->inventoryAllLocations();
			} elseif ( 3 == $TMWNI_OPTIONS['inventoryDefaultLocation'] ) {
				return $this->inventorySelectedLocations();
			}
		}
	}

	/**
	 * Update WooCommerce quantity
	 *
	 * @param array $item_quantity_array
	 */
	public function updateWooQuantity( $item_quantity_array, $by_queue ) {
		global $TMWNI_OPTIONS;

		foreach ( $item_quantity_array as $sku => $quantity ) {
			$product_id = $this->getProductIdBySku( $sku );
			if ( ! empty( $product_id ) ) {
				/**
					* Filter to modify the status of update quantity process.
					*
					* @since 1.0.0
				*/
					$quantity = apply_filters( 'tm_ns_last_item_quantity', $quantity, $product_id );
				if ( ! empty( $quantity ) ) {
					update_post_meta( $product_id, '_stock', $quantity );
				} else if ( ! empty( $TMWNI_OPTIONS['updateStockZero'] ) && 'on' === $TMWNI_OPTIONS['updateStockZero'] ) {
					update_post_meta( $product_id, '_stock', 0 );
				}
				if ( ! empty( $TMWNI_OPTIONS['overrideManageStock'] ) && 'on' === $TMWNI_OPTIONS['overrideManageStock'] ) {
					update_post_meta( $product_id, '_manage_stock', 'yes' );
				}
				if ( 'no' !== $TMWNI_OPTIONS['updateStockStatus'] ) {
					$this->updateStock( $product_id, $quantity );
				}
					$file_dir = wp_upload_dir();
					$log_file = $file_dir['basedir'] . '/' . TMWNI_Settings::$ns_inventory_log_file;
					$content = '<p>Product SKU ' . $sku . ' Inventory updated</p>';
					file_put_contents( $log_file, $content . PHP_EOL, FILE_APPEND );
				if ( true == $by_queue && ! isset( $TMWNI_OPTIONS['enablePriceSync'] ) ) {
					$old_count = get_option( 'updated_products_count' );
					$new_count = $old_count + 1;
					$this->updateLogFileContent( $content, $new_count );
				}
			}
		}
		/**
			* Action perform after update the quantity.
			*
			* @since 1.0.0
		*/
			do_action( 'tm_ns_after_update_item_quantity_data', $item_quantity_array );
	}

	/**
	 * Update WooCommerce stock status
	 *
	 * @param int $product_id
	 * @param int $quantity
	 */
	public function updateStock( $product_id, $quantity ) {
		if ( get_post_meta( $product_id, '_stock_status', true ) !== 'onbackorder' ) {
			$stock_status = $quantity > 0 ? 'instock' : 'outofstock';
			update_post_meta( $product_id, '_stock_status', $stock_status );
		}
	}

	/**
	 * Get product ID by SKU
	 *
	 * @param string $sku
	 * @return int|null
	 */
	public function getProductIdBySku( $sku ) {
		global $wpdb;
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_sku'
			AND meta_value = %s
			LIMIT 1
			",
				$sku
			)
		);
		return null !== $result ? $result : null;
	}
}

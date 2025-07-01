<?php

require_once TMWNI_DIR . 'inc/NS_Restlet/netsuiteRestAPI.php';

use Automattic\WooCommerce\Utilities\OrderUtil;
use NetSuite\NetSuiteService;

class ProductClient {

	public $sync_woo_products;

	public function __construct() {
		global $TMWNI_OPTIONS;
		require_once TMWNI_DIR . 'inc/background-process/class-sync-woo-product.php';
		$this->sync_woo_products = new Sync_Woo_Product();

		if (
			empty( $this->netsuiteService ) &&
			TMWNI_Settings::areCredentialsDefined()
		) {
			// Initializing NetSuite service
			$this->netsuiteService = new NetSuiteService(
				null,
				array(
					'exceptions' => true,
				)
			);
			add_action( 'init', array( $this, 'sync_product_manually' ) );
			if (
				isset( $TMWNI_OPTIONS['enableProductSync'] ) && 'on' == $TMWNI_OPTIONS['enableProductSync']
			) {
				if ( ! wp_next_scheduled( 'tm_ns_fetch_product' ) ) {
					wp_schedule_event(
						time(),
						$TMWNI_OPTIONS['productSyncFrequency'],
						'tm_ns_fetch_product'
					);
				}

				add_action( 'tm_ns_fetch_product', array( $this, 'fetchProductdata' ) );
			}
		}
	}

	public function sync_product_manually() {
		global $TMWNI_OPTIONS;
		if ( isset( $_GET['product'] ) && 1 == $_GET['product'] ) {

			$this->fetchProductdata();
			
		}
	}

	public function fetchProductdata() {
		global $TMWNI_OPTIONS;
		if (
				isset( $TMWNI_OPTIONS['enableProductSync'] ) &&
				'on' == $TMWNI_OPTIONS['enableProductSync']
			) {
				$this->getProductFromNetSuite();
				die( 'one' );
		}
	}



	public function getAllProductsOfNs(
		$map_field_name,
		$urlAPIEndPoint,
		$ns_fields_to_search,
		$sku_mapping_field
	) {
		global $TMWNI_OPTIONS;

		// Build the product query
		if ( ! empty( $TMWNI_OPTIONS['ns_woo_identifier'] ) ) {
			$ns_woo_identifier = 'item.' . $TMWNI_OPTIONS['ns_woo_identifier'];
			$product_query = array(
				'q' => "SELECT item.id, $sku_mapping_field, $ns_woo_identifier, $ns_fields_to_search 
                FROM item  
                WHERE $ns_woo_identifier = 'T' AND item.itemtype = 'InvtPart' AND (item.matrixtype != 'CHILD' or item.matrixtype is null)",
			);
		} else {
			$product_query = array(
				'q' => "SELECT item.id, $sku_mapping_field, $ns_fields_to_search 
                FROM item  
                WHERE item.itemtype = 'InvtPart' AND (item.matrixtype != 'CHILD' or item.matrixtype is null)",
			);
		}

		// Make the API request
		$this->NetsuiteRestAPIClient = new NetsuiteRestAPI();
		$response = $this->NetsuiteRestAPIClient->nsRESTRequest(
			'post',
			$urlAPIEndPoint,
			true,
			$product_query
		);

		// Check for API errors
		if ( isset( $response['error'] ) ) {
			error_log( "NetSuite API error at $urlAPIEndPoint: " . $response['error'] );
			return;
		}

		// Queue only the current response items
		if ( ! empty( $response['items'] ) ) {

			$this->sync_woo_products->push_to_queue(
				array( 'items' => $response['items'] )
			);
			$this->sync_woo_products->save()->dispatch();

		}

		// Handle pagination
		if ( ! empty( $response['hasMore'] ) ) {
			$count = $response['count'];
			$offset = $response['offset'] + $count;
			$urlAPIEndPoint = "/suiteql?limit=$count&offset=$offset";

			$this->getAllProductsOfNs(
				$map_field_name,
				$urlAPIEndPoint,
				$ns_fields_to_search,
				$sku_mapping_field
			);
		}

		// only once
	}

	public function getProductFromNetSuite() {
		global $TMWNI_OPTIONS;
		$urlAPIEndPoint = '/suiteql';

		$map_field_name = isset( $TMWNI_OPTIONS['sku_mapping_field'] ) ? $TMWNI_OPTIONS['sku_mapping_field'] : TMWNI_Settings::$sku_mapping_field;

		$ns_fields_to_search = $this->getProductFields();

		$get_new_products_from_netsuite = $this->getAllProductsOfNs(
			$map_field_name,
			$urlAPIEndPoint,
			$ns_fields_to_search,
			$TMWNI_OPTIONS['sku_mapping_field']
		);
	}

	public function getProductFields() {
		global $TMWNI_OPTIONS;

		$ns_field_values = array();

		foreach ( $TMWNI_OPTIONS['fields'] as $section ) {
			foreach ( $section as $key => $value ) {
				if ( strpos( $key, 'ns_field_' ) === 0 ) {
					$ns_field_values[] = 'item.' . $value;
				}
			}
		}

		if ( ! empty( $ns_field_values ) ) {
			$fields = join( ', ', array_unique( $ns_field_values ) );
		}

		return $fields;
	}

	public function syncProductsOnWoo( $products_data ) {
		// Debugging options
		global $TMWNI_OPTIONS;

		require_once TMWNI_DIR . 'inc/item.php';
		$netsuiteItemClient = new ItemClient();

		foreach ( $products_data as $product_data ) {

			$sku_field_key = strtolower( $TMWNI_OPTIONS['sku_mapping_field'] );

			if ( isset( $product_data[ $sku_field_key ] ) ) {

				$sku = $product_data[ $sku_field_key ];

				$product_id = $netsuiteItemClient->getProductIdBySku( $sku );

				if ( ! empty( $product_id ) ) {
					$this->updateWooProduct( $product_data, $product_id, $sku );
				} else {
					$this->createWooProduct( $product_data, $sku );
				}
			}
		}
	}





	public function createWooProduct( $product_data, $sku ) {
		global $TMWNI_OPTIONS;

		$postData = array(
			'post_author' => '',
			'post_content' => '',
			'post_status' => $TMWNI_OPTIONS['ns_product_autosync_status'],
			'post_title' => $sku,
			'post_parent' => 0,
			'post_type' => 'product',
		);

		$product_id = wp_insert_post( $postData );

		if ( ! empty( $product_id ) ) {
			update_post_meta( $product_id, '_sku', $sku );
			$this->syncWooProductFields( $product_data, $product_id, 'create' );
		}
	}

	public function updateWooProduct( $product_data, $product_id, $sku ) {
		$this->syncWooProductFields( $product_data, $product_id, 'update' );
	}

	/**
	 * Unified method to sync product fields during creation or update.
	 */
	public function syncWooProductFields(
		$product_data,
		$product_id,
		$mode = 'update'
	) {
		global $TMWNI_OPTIONS;

		$post_updates = array();
		$gallery_urls = array();

		foreach ( $TMWNI_OPTIONS['fields'] as $woo_field => $fieldData ) {
			$product_field = 'ns_field_' . $woo_field;
			$mode_field = $mode . '_' . $woo_field;

			if (
				empty( $fieldData[ $product_field ] ) ||
				empty( $fieldData[ $mode_field ] )
			) {
				continue;
			}

			$woo_product_field = $fieldData[ $product_field ];
			if (
				! isset( $product_data[ $woo_product_field ] ) && 'product_gallery_images'
				 != $woo_field
			) {
				continue;
			}

			$value = $product_data[ $woo_product_field ];

			switch ( $woo_field ) {
				case 'title':
					$post_updates['post_title'] = $value;
					break;

				case 'description':
					$post_updates['post_content'] = $value;
					break;

				case 'short_description':
					$post_updates['post_excerpt'] = $value;
					break;

				case 'tags':
					$this->createAndUpdateTags( $product_id, $value );
					break;

				case 'product_image':
					if ( is_numeric( $value ) ) {
						$value = $this->getImageUrlFromInternalId( $value );
					}
					$this->createAndUpdateProductImage(
						$product_id,
						$value,
						$filename
					);
					break;

				case 'product_gallery_images':
					$image_fields = array_map(
						'trim',
						explode( ',', $fieldData[ $product_field ] )
					);
					foreach ( $image_fields as $field_key ) {
						if ( ! empty( $product_data[ $field_key ] ) ) {
							$gallery_urls[] = $product_data[ $field_key ];
						}
					}
					break;

				default:
					update_post_meta( $product_id, '_' . $woo_field, $value );
					break;
			}
		}

		if ( ! empty( $post_updates ) ) {
			$post_updates['ID'] = $product_id;
			wp_update_post( $post_updates );
		}

		if ( ! empty( $gallery_urls ) ) {
			$this->setProductGalleryImagesFromUrls( $product_id, $gallery_urls );
		}
	}

	public function getImageUrlFromInternalId( $ImageId ) {
		global $TMWNI_OPTIONS;
		$file_data = array();
		$account_id = $TMWNI_OPTIONS['ns_account'];
		$account_id = str_replace( '_', '-', $account_id );
		$file_query = array(
			'q' => "SELECT id, url FROM file WHERE id = $ImageId",
		);

		$urlAPIEndPoint = '/suiteql';

		$this->NetsuiteRestAPIClient = new NetsuiteRestAPI();
		$response = $this->NetsuiteRestAPIClient->nsRESTRequest(
			'post',
			$urlAPIEndPoint,
			true,
			$file_query
		);
		if ( isset( $response['items'] ) && ! empty( $response['items'] ) ) {
			$full_url = "https://{$account_id}.app.netsuite.com{$response["items"][0]["url"]}";

			return $full_url;
		}
	}

	public function get_filename_from_headers( $url ) {
		$headers = get_headers( $url, 1 );

		if ( isset( $headers['Content-Disposition'] ) ) {
			$cd = $headers['Content-Disposition'];

			// Handle if Content-Disposition is returned as an array (some servers do this)
			if ( is_array( $cd ) ) {
				$cd = end( $cd );
			}

			if ( preg_match( '/filename="?([^"]+)"?/', $cd, $matches ) ) {
				return $matches[1]; // Return the filename from the header
			}
		}

		return null; // No filename found in headers
	}

	public function checkImageAlreadyExist( $image_url, $post_id = 0 ) {
		if ( empty( $image_url ) ) {
			return false;
		}

		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$filename = $this->get_filename_from_headers( $image_url );

		// Check if already uploaded
		$attachment_id = $this->findImageByFilename( $filename );
		if ( $attachment_id ) {
			return $attachment_id;
		}

		// Not uploaded yet — download and sideload
		$tmp = download_url( $image_url );
		if ( is_wp_error( $tmp ) ) {
			error_log( "Download failed: $image_url" );
			return false;
		}

		$file_array = array(
			'name' => $filename,
			'tmp_name' => $tmp,
		);

		$attachment_id = media_handle_sideload( $file_array, $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			return false;
		}

		return $attachment_id;
	}

	public function createAndUpdateProductImage( $product_id, $image_url ) {
		$attachment_id = $this->checkImageAlreadyExist( $image_url, $product_id );

		if ( $attachment_id ) {
			set_post_thumbnail( $product_id, $attachment_id );
		}
	}

	public function setProductGalleryImagesFromUrls(
		$product_id,
		$image_urls = array()
	) {
		if ( empty( $product_id ) || empty( $image_urls ) ) {
			return;
		}

		$existing_gallery_ids = get_post_meta(
			$product_id,
			'_product_image_gallery',
			true
		);
		$existing_gallery_ids = ! empty( $existing_gallery_ids )
			? explode( ',', $existing_gallery_ids )
			: array();

		$new_gallery_ids = array();

		foreach ( $image_urls as $image_url ) {
			if ( is_numeric( $image_url ) ) {
				$value = $this->getImageUrlFromInternalId( $image_url );
			}

			$attachment_id = $this->checkImageAlreadyExist(
				$image_url,
				$product_id
			);

			if (
				$attachment_id &&
				! in_array( $attachment_id, $existing_gallery_ids ) &&
				! in_array( $attachment_id, $new_gallery_ids )
			) {
				$new_gallery_ids[] = $attachment_id;
			}
		}

		$final_gallery_ids = array_unique(
			array_merge( $existing_gallery_ids, $new_gallery_ids )
		);
		update_post_meta(
			$product_id,
			'_product_image_gallery',
			implode( ',', $final_gallery_ids )
		);
	}

	public function findImageByFilename( $filename ) {
		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'meta_query' => array(
				array(
					'key' => '_wp_attached_file',
					'value' => $filename,
					'compare' => 'LIKE',
				),
			),
			'posts_per_page' => 1,
			'fields' => 'ids',
		);

		$query = new WP_Query( $args );
		return ! empty( $query->posts ) ? $query->posts[0] : false;
	}

	public function createAndUpdateTags( $product_id, $tags ) {
		$input_tags = array_map( 'trim', explode( ',', $tags ) );
		$tag_ids = array();

		foreach ( $input_tags as $tag_name ) {
			$term = get_term_by( 'name', $tag_name, 'product_tag' );

			if ( $term && ! is_wp_error( $term ) ) {
				$tag_ids[] = $term->term_id;
			} else {
				$new_term = wp_insert_term( $tag_name, 'product_tag' );

				if ( ! is_wp_error( $new_term ) && isset( $new_term['term_id'] ) ) {
					$tag_ids[] = $new_term['term_id'];
				}
			}
		}

		if ( ! empty( $tag_ids ) ) {
			wp_set_object_terms( $product_id, $tag_ids, 'product_tag' );
		}
	}
}

new ProductClient();

<?php
/**
 * Custom Trek Travel specific WP-CLI commands.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Implements Trek Travel command for WP-CLI.
	 */
	class Trek_Travel_Command extends WP_CLI_Command {

		/**
		 * Trek_Travel_Command constructor.
		 */
		public function __construct() {}

		/**
		 * Provide actions for Trek Travel products.
		 * It can display the IDs of products or only the count.
		 * It can add or remove Yost metadata for indexing, on simple products (date trips).
		 *
		 * ## OPTIONS
		 *
		 * <action>
		 * : The desired action. Supports: list | noindex-simple-products | index-simple-products
		 *
		 * [--product_type=<term>]
		 * : Product Type term.
		 * 
		 * [--line-item]
		 * : List only the Line Item product ids like Travel Protection, Single Supplement, etc.
		 *
		 * [--count]
		 * : Returns only the number of the products.
		 *
		 * ## EXAMPLES
		 *
		 *     wp ttnsw product list --product_type=simple
		 *     wp ttnsw product list --product_type=old_trip_date
		 *     wp ttnsw product list --product_type=simple,grouped
		 *     wp ttnsw product list --product_type=simple --count
		 *     wp ttnsw product list --line-item
		 *     wp ttnsw product list --without-product-type
		 *
		 *     wp ttnsw product noindex-products
		 *     wp ttnsw product index-products
		 *
		 * @synopsis <action> [--product_type=<term>] [--line-item] [--count] [--without-product-type]
		 * @param array $args All positional arguments for the command.
		 * @param array $assoc_args All associative arguments for the command.
		 */
		public function product( $args, $assoc_args ) {

			switch ( $args[0] ) {

				case 'list':
					$product_type = \WP_CLI\Utils\get_flag_value( $assoc_args, 'product_type', '' );
					$count_only   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'count', false );
					$is_line_item = \WP_CLI\Utils\get_flag_value( $assoc_args, 'line-item', false );
					$no_pr_type   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'without-product-type', false );

					WP_CLI::log( 'Found products: ' . json_encode( $this->get_products( $product_type, $is_line_item, $count_only, $no_pr_type ) ) );
					break;

				case 'noindex-products':
					$product_type = \WP_CLI\Utils\get_flag_value( $assoc_args, 'product_type', '' );
					$no_pr_type   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'without-product-type', false );

					if ( empty( $product_type ) && ! $no_pr_type ) {
						WP_CLI::error( 'Please provide the --product_type=... or --without-product-type flag for the action!' );
					}

					$products = $this->get_products( $product_type, false, false, $no_pr_type );

					if ( empty( $products ) || ! is_countable( $products ) ) {
						WP_CLI::error( $product_type . ' products are not found!' );
					}

					$products_count = count( $products );

					WP_CLI::log( 'Found ' . $products_count . ' ' . $product_type . ' products.' );
					WP_CLI::confirm( __( 'Do you want to proceed with the adding of "_yoast_wpseo_meta-robots-noindex" and "_yoast_wpseo_meta-robots-nofollow" meta, so to can\'t be indexed these products?', 'trek-travel' ) );

					$progress = \WP_CLI\Utils\make_progress_bar( 'Updating meta', $products_count );

					for ( $i = 0; $i < $products_count; $i++ ) {
						update_post_meta( $products[$i], '_yoast_wpseo_meta-robots-noindex', 1 ); // Update Yoast meta for noindex.
						update_post_meta( $products[$i], '_yoast_wpseo_meta-robots-nofollow', 1 ); // Update Yoast meta for nofollow.
						wp_update_post( array( 'ID' => $products[$i] ) );
						$progress->tick();
					}

					$progress->finish();

					WP_CLI::success( 'The products meta "_yoast_wpseo_meta-robots-noindex" and "_yoast_wpseo_meta-robots-nofollow" were updated!' );

					// Flush the cache.
					$cache_flushed = wp_cache_flush();
					if ( $cache_flushed ) {
						WP_CLI::success( 'The cache was flushed!' );
					} else {
						WP_CLI::warning( 'The cache was not flushed!' );
					}
					break;

				case 'index-products':
					$product_type = \WP_CLI\Utils\get_flag_value( $assoc_args, 'product_type', '' );
					$no_pr_type   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'without-product-type', false );

					if ( empty( $product_type ) && ! $no_pr_type ) {
						WP_CLI::error( 'Please provide the --product_type=... or --without-product-type flag for the action!' );
					}

					$products = $this->get_products( $product_type, false, false, $no_pr_type );

					if ( empty( $products ) || ! is_countable( $products ) ) {
						WP_CLI::error( $product_type . ' products are not found!' );
					}

					$products_count = count( $products );

					WP_CLI::log( 'Found ' . $products_count . ' ' . $product_type . ' products.' );
					WP_CLI::confirm( __( 'Do you want to proceed with the deleting of "_yoast_wpseo_meta-robots-noindex" and "_yoast_wpseo_meta-robots-nofollow" meta, so can be indexed these products?', 'trek-travel' ) );

					$progress = \WP_CLI\Utils\make_progress_bar( 'Deleting meta', $products_count );

					for ( $i = 0; $i < $products_count; $i++ ) {
						delete_post_meta( $products[$i], '_yoast_wpseo_meta-robots-noindex' ); // Delete Yoast meta for noindex.
						delete_post_meta( $products[$i], '_yoast_wpseo_meta-robots-nofollow' ); // Delete Yoast meta for nofollow.
						wp_update_post( array( 'ID' => $products[$i] ) );
						$progress->tick();
					}

					$progress->finish();

					WP_CLI::success( 'The products meta "_yoast_wpseo_meta-robots-noindex" and "_yoast_wpseo_meta-robots-nofollow" were deleted!' );

					// Flush the cache.
					$cache_flushed = wp_cache_flush();
					if ( $cache_flushed ) {
						WP_CLI::success( 'The cache was flushed!' );
					} else {
						WP_CLI::warning( 'The cache was not flushed!' );
					}
					break;
				
				default:
					WP_CLI::warning( 'Oops, this action is not supported by the `product` command.' );
					break;
			}

		}

		/**
		 * Get WC products.
		 *
		 * @param string $product_type The product type value or values separated by a comma.
		 * @param bool   $is_line_item Whether to get only the Line Item products for a given product type.
		 * @param bool   $count_only To return only the number of the products.
		 *
		 * @return array|int Array with the product IDs or number of the products.
		 */
		protected function get_products( $product_type = '', $is_line_item = false, $count_only = false, $no_pr_type = false ) {

			$products_query_args = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids'
			);

			if ( $is_line_item ) {
				$products_query_args['meta_query'] = array(
					array(
						'key'     => 'tt_line_item_fees_product',
						'value'   => '1',
						'compare' => '='
					)
				);
			}

			if ( $no_pr_type ) {
				$products_query_args['tax_query'] = array(
					array(
						'taxonomy' => 'product_type',
						'operator' => 'NOT EXISTS'
					)
				);
			} elseif ( $product_type ) {
				$products_query_args['tax_query'] = array(
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => explode( ',', $product_type ),
						'operator' => 'IN'
					),
				);
			}

			$products_query = new WP_Query( $products_query_args );

			if ( isset( $products_query->posts ) ) {
				if ( $count_only ) {
					return count( $products_query->posts );
				}

				return $products_query->posts;
			}

			return array();
		}
	}

	WP_CLI::add_command( 'ttnsw', 'Trek_Travel_Command' );
}

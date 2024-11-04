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
		 *     wp ttnsw product list --product_type=simple,grouped
		 *     wp ttnsw product list --product_type=simple --count
		 *     wp ttnsw product list --line-item
		 *
		 *     wp ttnsw product noindex-simple-products
		 *     wp ttnsw product index-simple-products
		 *
		 * @synopsis <action> [--product_type=<term>] [--line-item] [--count]
		 * @param array $args All positional arguments for the command.
		 * @param array $assoc_args All associative arguments for the command.
		 */
		public function product( $args, $assoc_args ) {

			switch ( $args[0] ) {

				case 'list':
					$product_type = \WP_CLI\Utils\get_flag_value( $assoc_args, 'product_type', '' );
					$count_only   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'count', false );
					$is_line_item = \WP_CLI\Utils\get_flag_value( $assoc_args, 'line-item', false );

					WP_CLI::log( 'Found products: ' . json_encode( $this->get_products( $product_type, $is_line_item, $count_only ) ) );
					break;

				case 'noindex-simple-products':
					$simple_products = $this->get_products( 'simple' );

					if ( empty( $simple_products ) || ! is_countable( $simple_products ) ) {
						WP_CLI::error( 'Simple products are not found!' );
					}

					$simple_products_count = count( $simple_products );

					WP_CLI::log( 'Found ' . $simple_products_count . ' simple products.' );
					WP_CLI::confirm( __( 'Do you want to proceed with the adding of "_yoast_wpseo_meta-robots-noindex" and "_yoast_wpseo_meta-robots-nofollow" meta, so to can\'t be indexed these products?', 'trek-travel' ) );

					$progress = \WP_CLI\Utils\make_progress_bar( 'Updating meta', $simple_products_count );

					for ( $i = 0; $i < $simple_products_count; $i++ ) {
						update_post_meta( $simple_products[$i], '_yoast_wpseo_meta-robots-noindex', 1 ); // Update Yoast meta for noindex.
						update_post_meta( $simple_products[$i], '_yoast_wpseo_meta-robots-nofollow', 1 ); // Update Yoast meta for nofollow.
						$progress->tick();
					}

					$progress->finish();

					WP_CLI::success( 'The products meta "_yoast_wpseo_meta-robots-noindex" and "_yoast_wpseo_meta-robots-nofollow" were updated!' );
					break;

				case 'index-simple-products':
					$simple_products = $this->get_products( 'simple' );

					if ( empty( $simple_products ) || ! is_countable( $simple_products ) ) {
						WP_CLI::error( 'Simple products are not found!' );
					}

					$simple_products_count = count( $simple_products );

					WP_CLI::log( 'Found ' . $simple_products_count . ' simple products.' );
					WP_CLI::confirm( __( 'Do you want to proceed with the deleting of "_yoast_wpseo_meta-robots-noindex" and "_yoast_wpseo_meta-robots-nofollow" meta, so can be indexed these products?', 'trek-travel' ) );

					$progress = \WP_CLI\Utils\make_progress_bar( 'Deleting meta', $simple_products_count );

					for ( $i = 0; $i < $simple_products_count; $i++ ) {
						delete_post_meta( $simple_products[$i], '_yoast_wpseo_meta-robots-noindex' ); // Delete Yoast meta for noindex.
						delete_post_meta( $simple_products[$i], '_yoast_wpseo_meta-robots-nofollow' ); // Delete Yoast meta for nofollow.
						$progress->tick();
					}

					$progress->finish();

					WP_CLI::success( 'The products meta "_yoast_wpseo_meta-robots-noindex" and "_yoast_wpseo_meta-robots-nofollow" were deleted!' );
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
		protected function get_products( $product_type = '', $is_line_item = false, $count_only = false ) {

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

			if ( $product_type ) {
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

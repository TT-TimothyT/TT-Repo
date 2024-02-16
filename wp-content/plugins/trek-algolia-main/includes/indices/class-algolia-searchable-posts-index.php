<?php

/**
 * Algolia_Searchable_Posts_Index class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Algolia_Searchable_Posts_Index
 *
 * @since 1.0.0
 */
final class Algolia_Searchable_Posts_Index extends Algolia_Index {

	/**
	 * What this index contains.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	protected $contains_only = 'posts';

	/**
	 * Array of post types for the searchable posts index.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var array
	 */
	private $post_types;

	/**
	 * Algolia_Searchable_Posts_Index constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param array $post_types The post types.
	 */
	public function __construct( array $post_types ) {
		$this->post_types = $post_types;
	}

	/**
	 * Check if this index supports the given item.
	 *
	 * A performing function that return true if the item can potentially
	 * be subject for indexation or not. This will be used to determine if an item is part of the index
	 * As this function will be called synchronously during other operations,
	 * it has to be as lightweight as possible. No db calls or huge loops.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to check against.
	 *
	 * @return bool
	 */
	public function supports( $item ) {
		return $item instanceof WP_Post && in_array( $item->post_type, $this->post_types, true );
	}

	/**
	 * Get the admin name for this index.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string The name displayed in the admin UI.
	 */
	public function get_admin_name() {
		return __( 'All posts', 'wp-search-with-algolia' );
	}

	/**
	 * Check if the item should be indexed.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to check.
	 *
	 * @return bool
	 */
	protected function should_index( $item ) {
		return $this->should_index_post( $item );
	}

	/**
	 * Check if the post should be indexed.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post The post to check.
	 *
	 * @return bool
	 */



	private function should_index_post( WP_Post $post ) {

		$this_id = $post->ID;
		$type = $post->post_type;
		$product = wc_get_product($this_id);

		if ($type == 'product' || $type == 'page' || $type == 'post') {
			if ($product) {
				if ($product->get_type() == 'grouped') {
					$should_index = 'publish' === $post->post_status && empty( $post->post_password );
				}else {
					$should_index = false;
				}
			}else {
				$should_index = 'publish' === $post->post_status && empty( $post->post_password );
			}
		}else {
			$should_index = false;
		}

		return (bool) apply_filters( 'algolia_should_index_searchable_post', $should_index, $post );
	}

	/**
	 * Get records for the item.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to get records for.
	 *
	 * @return array
	 */
	protected function get_records( $item ) {
		return $this->get_post_records( $item );
	}

	/**
	 * Get records for the post.
	 *
	 * Turns a WP_Post in a collection of records to be pushed to Algolia.
	 * Given every single post is splitted into several Algolia records,
	 * we also attribute an objectID that follows a naming convention for
	 * every record.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post The post to get records for.
	 *
	 * @return array
	 */
	private function get_post_records( WP_Post $post ) {
		$shared_attributes = $this->get_post_shared_attributes( $post );

		$removed = remove_filter( 'the_content', 'wptexturize', 10 );

		$post_content = apply_filters( 'algolia_searchable_post_content', $post->post_content, $post );
		$post_content = apply_filters( 'the_content', $post_content ); // phpcs:ignore -- Legitimate use of Core hook.

		if ( true === $removed ) {
			add_filter( 'the_content', 'wptexturize', 10 );
		}

		$post_content = Algolia_Utils::prepare_content( $post_content );
		$parts        = Algolia_Utils::explode_content( $post_content );

		if ( defined( 'ALGOLIA_SPLIT_POSTS' ) && false === ALGOLIA_SPLIT_POSTS ) {
			$parts = array( array_shift( $parts ) );
		}

		$records = array();
		foreach ( $parts as $i => $part ) {
			$record                 = $shared_attributes;
			$record['objectID']     = $this->get_post_object_id( $post->ID, $i );
			$record['content']      = $part;
			$record['record_index'] = $i;
			$records[]              = $record;
		}

		$records = (array) apply_filters( 'algolia_searchable_post_records', $records, $post );
		$records = (array) apply_filters( 'algolia_searchable_post_' . $post->post_type . '_records', $records, $post );

		return $records;
	}

	/**
	 * Get post shared attributes.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post The post to get shared attributes for.
	 *
	 * @return array
	 *
	 * @throws RuntimeException If post type information unknown.
	 */
	private function get_post_shared_attributes( WP_Post $post ) {
			$this_id                        = $post->ID;
			$shared_attributes              = array();
			$jsonData = array();
			$product = wc_get_product($this_id);
			$child_products=[];
			$upload_dir = wp_upload_dir();
			$jsonPath1 = $upload_dir['basedir'].'/algolia/popular1.json';
			$jsonPath2 = $upload_dir['basedir'].'/algolia/popular2.json';
			$jsonPath3 = $upload_dir['basedir'].'/algolia/popular3.json';
			$jsonPath4 = $upload_dir['basedir'].'/algolia/popular4.json';
			$yotpoAppID = '4488jd7QVtY0HrLS8BYsAC3fel6zpMyyxIyl9wLW';
			//Fetch YotPo Review Data for Product
			$curl = curl_init();

			curl_setopt_array($curl, [
				CURLOPT_URL => "https://api-cdn.yotpo.com/v1/widget/".$yotpoAppID."/products/".$this_id."/reviews.json",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => [
					"Accept: application/json",
					"Content-Type: application/json"
				],
			]);

			try{
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);


				if (is_string($response)) {

					error_log('yotpo response');
					error_log($response);
					$jsonResponse = json_decode($response, true);
					if ($jsonResponse)  {
						error_log('jsonResponse is '. $jsonResponse);
						if ($jsonResponse['response']){
							error_log('response array is ' . $jsonResponse['response']);
							if ($jsonResponse['response']['bottomline']) {
								error_log('response.bottomline array is ' . $jsonResponse['response']['bottomline']);
								if ($jsonResponse['response']['bottomline']['average_score']) {
									error_log('response.bottomline.average_score array is ' . $jsonResponse['response']['bottomline']['average_score']);
									error_log('decoded average_score');
									error_log($jsonResponse['response']['bottomline']['average_score']);
									if ($jsonResponse['response']['bottomline']['average_score'] != 0) {
										$shared_attributes['review_score'] = $jsonResponse['response']['bottomline']['average_score'];
									}
								}
								if ($jsonResponse['response']['bottomline']['total_review']) {
									error_log('response.bottomline.average_score array is ' . $jsonResponse['response']['bottomline']['total_review']);
									error_log('decoded total_review');
									error_log($jsonResponse['response']['bottomline']['total_review']);
									if ($jsonResponse['response']['bottomline']['total_review'] != 0) {
										$shared_attributes['total_review'] = $jsonResponse['response']['bottomline']['total_review'];
									}
								}
							}
						}
					}

				}
			}
			catch (Exception $e) {
				error_log("Error: " , $e->getMessage());
			}

			// End Fetch YotPo Review Data for Product

			if ($product) {
					if ($product->get_attribute('Popular')) {
						if ($product->get_attribute('Popular') === '1') {
							$jsonData['Title'] = $product->get_title();
							$jsonData['Permalink'] = $product->get_permalink();

							$attachment_ids = $product->get_gallery_attachment_ids();

							foreach( $attachment_ids as $index=>$attachment_id )
							{
								$jsonData['gallery_images'][$index] = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];

							}

							$jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
							// Write in the file
							$fp = fopen($jsonPath1, 'w');
							fwrite($fp, $jsonString);
							fclose($fp);
						}
						if ($product->get_attribute('Popular') === '2') {
							$jsonData['Title'] = $product->get_title();
							$jsonData['Permalink'] = $product->get_permalink();

							$attachment_ids = $product->get_gallery_attachment_ids();

							foreach( $attachment_ids as $index=>$attachment_id )
							{
								$jsonData['gallery_images'][$index] = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];

							}

							$jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
							// Write in the file
							$fp = fopen($jsonPath2, 'w');
							fwrite($fp, $jsonString);
							fclose($fp);
						}
						if ($product->get_attribute('Popular') === '3') {
							$jsonData['Title'] = $product->get_title();
							$jsonData['Permalink'] = $product->get_permalink();

							$attachment_ids = $product->get_gallery_attachment_ids();

							foreach( $attachment_ids as $index=>$attachment_id )
							{
								$jsonData['gallery_images'][$index] = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];

							}

							$jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
							// Write in the file
							$fp = fopen($jsonPath3, 'w');
							fwrite($fp, $jsonString);
							fclose($fp);
						}
						if ($product->get_attribute('Popular') === '4') {
							$jsonData['Title'] = $product->get_title();
							$jsonData['Permalink'] = $product->get_permalink();

							$attachment_ids = $product->get_gallery_attachment_ids();

							foreach( $attachment_ids as $index=>$attachment_id )
							{
								$jsonData['gallery_images'][$index] = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];

							}

							$jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
							// Write in the file
							$fp = fopen($jsonPath4, 'w');
							fwrite($fp, $jsonString);
							fclose($fp);
						}
					}
					$children = $product->get_children();
					if ($children) {
						foreach ($children as $index => $child) {
							$child_products[$index] = wc_get_product($child);
							if ($child_products[$index]){

								if ($child_products[$index]->get_regular_price()) {
									$rolling_price[$index] = $child_products[$index]->get_regular_price();
									if (!$shared_attributes['Start Price']) {
										$shared_attributes['Start Price'] = intval($rolling_price[$index]);
									}
									else if ($shared_attributes['Start Price'] > $rolling_price[$index]) {
										$shared_attributes['Start Price'] = intval($rolling_price[$index]);
									}
								}

								if ($child_products[$index]->get_attribute( 'Start Date' )) {
									$shared_attributes['Start Date'][$index] = $child_products[$index]->get_attribute( 'Start Date' );
									$tempdate = $child_products[$index]->get_attribute( 'Start Date' );
									$sdate_obj = explode('/', $tempdate);
									$sdate_info = array(
										'd' => $sdate_obj[0],
										'm' => $sdate_obj[1],
										'y' => substr(date('Y'), 0, 2) . $sdate_obj[2]
									);
									$tempunix = strtotime(implode('-', $sdate_info));
									//$tempunix = strtotime($tempdate);
									$shared_attributes['start_date_unix'][$index] = $tempunix;
								}
								if ($child_products[$index]->get_attribute( 'End Date' )) {
									$shared_attributes['End Date'][$index] = $child_products[$index]->get_attribute( 'End Date' );
									$tempdate = $child_products[$index]->get_attribute( 'End Date' );
									$edate_obj = explode('/', $tempdate);
									$edate_info = array(
										'd' => $edate_obj[0],
										'm' => $edate_obj[1],
										'y' => substr(date('Y'), 0, 2) . $edate_obj[2]
									);
									//$tempunix = strtotime($tempdate);
									$tempunix = strtotime(implode('-', $edate_info));
									$shared_attributes['end_date_unix'][$index] = $tempunix;
								}
							}
						}
					}
				}

			$shared_attributes['post_id']   = $post->ID;
			$shared_attributes['post_type'] = $post->post_type;
			$post_type = get_post_type_object( $post->post_type );
			if ( null === $post_type ) {
				throw new RuntimeException( 'Unable to fetch the post type information.' );
			}
			$shared_attributes['post_type_label']     = $post_type->labels->name;
			if ($shared_attributes['post_type_label'] == 'Products') {
				$shared_attributes['post_type_label'] = 'Trips';
			}
			if ($shared_attributes['post_type_label'] == 'Posts') {
				$shared_attributes['post_type_label'] = 'Articles';
			}
			$shared_attributes['post_title']          = $post->post_title;
			$shared_attributes['post_excerpt']        = apply_filters( 'the_excerpt', $post->post_excerpt ); // phpcs:ignore -- Legitimate use of Core hook.
			$shared_attributes['post_date']           = get_post_time( 'U', false, $post );
			$shared_attributes['post_date_formatted'] = get_the_date( '', $post );
			$shared_attributes['post_modified']       = get_post_modified_time( 'U', false, $post );
			$shared_attributes['comment_count']       = (int) $post->comment_count;
			$shared_attributes['menu_order']          = (int) $post->menu_order;
			if (wc_get_product($this_id)) {

				$attachment_ids = $product->get_gallery_attachment_ids();

				foreach( $attachment_ids as $index=>$attachment_id )
				{
					$shared_attributes['gallery_images'][$index] = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];

				}
				if ($product->get_attribute( 'Trip Style' )) {
					$shared_attributes['Trip Style']      = $product->get_attribute( 'Trip Style' );
				}
				if ($product->get_attribute( 'Hotel Level' )) {
					$shared_attributes['Hotel Level']      = $product->get_attribute( 'Hotel Level' );
				}
				if ($product->get_attribute( 'Rider Level' )) {
					$shared_attributes['Rider Level']      = $product->get_attribute( 'Rider Level' );
				}
				if ($product->get_attribute( 'Duration' )) {
					$shared_attributes['Duration']      = $product->get_attribute( 'Duration' );
				}
				if ($product->get_attribute( 'Badge' )) {
					$shared_attributes['Badge']      = $product->get_attribute( 'Badge' );
				}
              	if ($product->get_attribute( 'Bike Type' )) {
					$shared_attributes['Bike Type']      = $product->get_attribute( 'Bike Type' );
				}
				if ($product->get_attribute( 'Region' )) {
					$shared_attributes['Region']      = $product->get_attribute( 'Region' );
				}

			}

			$author = get_userdata( $post->post_author );
			if ( $author ) {
				$shared_attributes['post_author'] = array(
					'user_id'      => (int) $post->post_author,
					'display_name' => $author->display_name,
					'user_url'     => $author->user_url,
					'user_login'   => $author->user_login,
				);
			}

			$shared_attributes['images'] = Algolia_Utils::get_post_images( $post->ID );

			$shared_attributes['permalink']      = get_permalink( $post );
			$shared_attributes['post_mime_type'] = $post->post_mime_type;

			// Push all taxonomies by default, including custom ones.
			$taxonomy_objects = get_object_taxonomies( $post->post_type, 'objects' );

			$shared_attributes['taxonomies']              = array();
			$shared_attributes['taxonomies_hierarchical'] = array();
			foreach ( $taxonomy_objects as $taxonomy ) {
				$terms = wp_get_object_terms( $post->ID, $taxonomy->name );
				$terms = is_array( $terms ) ? $terms : array();

				// Here we collect hierarchical taxonomies. We can show Destinations using product_cat taxonomy and from there we will use only Destinations.
				if ( $taxonomy->hierarchical ) {
					$hierarchical_taxonomy_values = Algolia_Utils::get_taxonomy_tree( $terms, $taxonomy->name );
					if ( ! empty( $hierarchical_taxonomy_values ) ) {
						$shared_attributes['taxonomies_hierarchical'][ $taxonomy->name ] = $hierarchical_taxonomy_values;
					}
				}

				// The code below in the if check makes nothing... Also 'echo' doesn't have an effect in this loop.
              	if ( $taxonomy->hierarchical ) {
					// Get the term object for the parent category.
					$parent_category = get_term_by( 'slug', 'destinations', $taxonomy->name );
				
					// If the parent category is found and has children, display the child categories.
					if ( $parent_category && ! is_wp_error( $parent_category ) ) {
						$child_terms = get_terms( array(
							'taxonomy'   => $taxonomy->name,
							'child_of'   => $parent_category->term_id,
							'hide_empty' => false,
						) );
				
						// Loop through and display the child terms.
						if ( ! empty( $child_terms ) ) {
							foreach ( $child_terms as $term ) {
								echo '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a><br>';
							}
						}
					}
				}

				$taxonomy_values = wp_list_pluck( $terms, 'name' );
				if ( ! empty( $taxonomy_values ) ) {
					$shared_attributes['taxonomies'][ $taxonomy->name ] = $taxonomy_values;
				}
			}

			$shared_attributes['is_sticky'] = is_sticky( $post->ID ) ? 1 : 0;

			$shared_attributes = (array) apply_filters( 'algolia_searchable_post_shared_attributes', $shared_attributes, $post );
			$shared_attributes = (array) apply_filters( 'algolia_searchable_post_' . $post->post_type . '_shared_attributes', $shared_attributes, $post );

			return $shared_attributes;








	}

	/**
	 * Get settings.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return array
	 */
	protected function get_settings() {
		$settings = array(
			'searchableAttributes'  => array(
				'unordered(post_title)',
				'unordered(taxonomies)',
				'unordered(content)',
			),
			'customRanking'         => array(
				'desc(is_sticky)',
				'desc(post_date)',
				'asc(record_index)',
			),
			'attributeForDistinct'  => 'post_id',
			'distinct'              => true,
			'attributesForFaceting' => array(
				'taxonomies',
				'taxonomies_hierarchical',
				'post_author.display_name',
				'post_type_label',
				'date_ranges',
				'searchable(taxonomies)'
			),
			'attributesToSnippet'   => array(
				'post_title:30',
				'content:' . intval( apply_filters( 'excerpt_length', 55 ) ), // phpcs:ignore -- Legitimate use of Core hook.
			),
			'snippetEllipsisText'   => '…',
		);

		$settings = (array) apply_filters( 'algolia_searchable_posts_index_settings', $settings );

		/**
		 * Replacing `attributesToIndex` with `searchableAttributes` as
		 * it has been replaced by Algolia.
		 *
		 * @link  https://www.algolia.com/doc/api-reference/api-parameters/searchableAttributes/
		 * @since 2.2.0
		 */
		if (
			array_key_exists( 'attributesToIndex', $settings )
			&& is_array( $settings['attributesToIndex'] )
		) {
			$settings['searchableAttributes'] = array_merge(
				$settings['searchableAttributes'],
				$settings['attributesToIndex']
			);
			unset( $settings['attributesToIndex'] );
		}

		return $settings;
	}

	/**
	 * Get synonyms.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return array
	 */
	protected function get_synonyms() {
		$synonyms = (array) apply_filters( 'algolia_searchable_posts_index_synonyms', array() );

		return $synonyms;
	}

	/**
	 * Get post object ID.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param int $post_id      The WP_Post ID.
	 * @param int $record_index The split record index.
	 *
	 * @return string
	 */
	private function get_post_object_id( $post_id, $record_index ) {
		/**
		 * This filter is documented in includes/indices/class-algolia-posts-index.php
		 *
		 * @since 1.3.0
		 *
		 * @see Algolia_Posts_Index::get_post_object_id()
		 */
		return apply_filters(
			'algolia_get_post_object_id',
			$post_id . '-' . $record_index,
			$post_id,
			$record_index
		);
	}

	/**
	 * Update records.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item    The item to update records for.
	 * @param array $records The records.
	 */
	protected function update_records( $item, array $records ) {
		$this->update_post_records( $item, $records );
	}

	/**
	 * Update post records.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post    The post to update records for.
	 * @param array   $records The records.
	 */
	private function update_post_records( WP_Post $post, array $records ) {
		// If there are no records, parent `update_records` will take care of the deletion.
		// In case of posts, we ALWAYS need to delete existing records.
		if ( ! empty( $records ) ) {
			$this->delete_item( $post );
		}

		parent::update_records( $post, $records );

		// Keep track of the new record count for future updates relying on the objectID's naming convention .
		$new_records_count = count( $records );
		$this->set_post_records_count( $post, $new_records_count );

		do_action( 'algolia_searchable_posts_index_post_updated', $post, $records );
		do_action( 'algolia_searchable_posts_index_post_' . $post->post_type . '_updated', $post, $records );
	}

	/**
	 * Get ID.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_id() {
		return 'searchable_posts';
	}

	/**
	 * Get re-index items count.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return int
	 */
	protected function get_re_index_items_count() {
		$query = new WP_Query(
			array(
				'post_type'              => $this->post_types,
				'post_status'            => 'any', // Let the `should_index` take care of the filtering.
				'suppress_filters'       => true,
				'cache_results'          => false,
				'lazy_load_term_meta'    => false,
				'update_post_term_cache' => false,
			)
		);

		return (int) $query->found_posts;
	}

	/**
	 * Get items.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param int $page       The page.
	 * @param int $batch_size The batch size.
	 *
	 * @return array
	 */
	protected function get_items( $page, $batch_size ) {
		$query = new WP_Query(
			array(
				'post_type'              => $this->post_types,
				'posts_per_page'         => 24,
				'post_status'            => 'any',
				'order'                  => 'ASC',
				'orderby'                => 'ID',
				'paged'                  => $page,
				'suppress_filters'       => true,
				'cache_results'          => false,
				'lazy_load_term_meta'    => false,
				'update_post_term_cache' => false,
			)
		);

		return $query->posts;
	}

	/**
	 * Delete item.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to delete.
	 */
	public function delete_item( $item ) {
		$this->assert_is_supported( $item );

		$records_count = $this->get_post_records_count( $item->ID );
		$object_ids    = array();
		for ( $i = 0; $i < $records_count; $i++ ) {
			$object_ids[] = $this->get_post_object_id( $item->ID, $i );
		}

		if ( ! empty( $object_ids ) ) {
			$this->get_index()->deleteObjects( $object_ids );
		}
	}

	/**
	 * Get post records count.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int
	 */
	private function get_post_records_count( $post_id ) {
		return (int) get_post_meta( (int) $post_id, 'algolia_' . $this->get_id() . '_records_count', true );
	}
	/**
	 * Set post records count.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post  The post.
	 * @param int     $count The count of records.
	 */
	private function set_post_records_count( WP_Post $post, $count ) {
		update_post_meta( (int) $post->ID, 'algolia_' . $this->get_id() . '_records_count', (int) $count );
	}
}

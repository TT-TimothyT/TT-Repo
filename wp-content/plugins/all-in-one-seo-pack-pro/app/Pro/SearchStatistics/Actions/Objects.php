<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics\Actions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Pro\Models\SearchStatistics as Models;
use AIOSEO\Plugin\Common\Models\Post as Post;

/**
 * Handles the objects scan.
 *
 * @since 4.3.0
 */
class Objects {
	/**
	 * The action name.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $action = 'aioseo_search_statistics_objects_scan';

	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'update_option_permalink_structure', [ $this, 'reset' ], 10, 2 );

		add_action( $this->action, [ $this, 'scanForPosts' ] );
	}

	/**
	 * Initialize the objects.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function init() {
		if ( ! aioseo()->searchStatistics->api->auth->isConnected() ) {
			return;
		}

		$this->scheduleInitialScan();

		add_action( 'save_post', [ $this, 'updatePost' ], 100 );
		add_action( 'delete_post', [ $this, 'updatePost' ], 100 );
		add_action( 'wp_trash_post', [ $this, 'updatePost' ], 100 );
	}

	/**
	 * Schedules the initial scan.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	private function scheduleInitialScan() {
		if ( aioseo()->actionScheduler->isScheduled( $this->action ) ) {
			return;
		}

		aioseo()->actionScheduler->scheduleAsync( $this->action );
	}

	/**
	 * Drops all object rows if the permalink structure changes.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $oldValue The old permalink structure.
	 * @param  string $newValue The new permalink structure.
	 * @return void
	 */
	public function reset( $oldValue, $newValue ) {
		if ( $oldValue === $newValue ) {
			return;
		}

		aioseo()->core->db->start( 'aioseo_search_statistics_objects' )
			->truncate()
			->run();

		as_unschedule_all_actions( $this->action );
		$this->scheduleInitialScan();
	}

	/**
	 * Checks if posts need to be updated/inserted.
	 *
	 * @since 4.3.0
	 *
	 * @param  int $postId The post ID.
	 * @return void
	 */
	public function scanForPosts() {
		$postTypes = aioseo()->helpers->getPublicPostTypes( true );

		$missingPosts = aioseo()->core->db->start( 'posts as p' )
			->select( 'DISTINCT p.ID, p.post_type, asso.object_id, ap.seo_score' )
			->leftJoin( 'aioseo_search_statistics_objects as asso', 'p.ID = asso.object_id' )
			->leftJoin( 'aioseo_posts as ap', 'p.ID = ap.post_id' )
			->where( 'p.post_status', 'publish' )
			->whereIn( 'p.post_type', $postTypes )
			->whereRaw( '(
				asso.object_id IS NULL OR
				asso.updated < p.post_modified_gmt
			)' )
			->limit( 50 )
			->run()
			->result();

		if ( empty( $missingPosts ) ) {
			aioseo()->actionScheduler->scheduleSingle( $this->action, DAY_IN_SECONDS, [], true );

			return;
		}

		$objectsToInsert = [];
		foreach ( $missingPosts as $post ) {
			$object = [
				'object_id'      => $post->ID,
				'object_type'    => 'post',
				'object_subtype' => $post->post_type,
				'object_path'    => wp_make_link_relative( get_permalink( $post->ID ) ),
				'seo_score'      => $post->seo_score
			];

			if ( ! empty( $post->object_id ) ) {
				Models\WpObject::update( $object );
			} else {
				$objectsToInsert[] = $object;
			}
		}

		Models\WpObject::bulkInsert( $objectsToInsert );

		aioseo()->actionScheduler->scheduleSingle( $this->action, MINUTE_IN_SECONDS, [], true );
	}

	/**
	 * Updates or deletes the data for the given post.
	 *
	 * @since 4.3.0
	 *
	 * @param  int  $postId The post ID.
	 * @return void
	 */
	public function updatePost( $postId ) {
		if ( wp_is_post_autosave( $postId ) || wp_is_post_revision( $postId ) ) {
			return;
		}

		if ( 'publish' !== get_post_status( $postId ) ) {
			aioseo()->core->db->delete( 'aioseo_search_statistics_objects' )
				->where( 'object_id', $postId )
				->where( 'object_type', 'post' )
				->run();

			return;
		}

		$post = get_post( $postId );
		if ( ! is_a( $post, 'WP_Post' ) || ! in_array( $post->post_type, aioseo()->searchStatistics->helpers->getIncludedPostTypes(), true ) ) {
			return;
		}

		$object = [
			'object_id'      => $postId,
			'object_type'    => 'post',
			'object_subtype' => $post->post_type,
			'object_path'    => wp_make_link_relative( get_permalink( $postId ) ),
			'seo_score'      => Post::getPost( $postId )->seo_score
		];

		Models\WpObject::update( $object );
	}
}
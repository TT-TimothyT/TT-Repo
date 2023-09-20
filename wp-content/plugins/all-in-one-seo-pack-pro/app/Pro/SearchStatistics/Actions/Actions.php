<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics\Actions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers our actions.
 *
 * @since 4.3.0
 */
class Actions {
	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		new Objects();
	}

	/**
	 * Kills all scheduled Search Statistics related actions.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function kill() {
		$actions = [
			'aioseo_search_statistics_objects_scan'
		];
		foreach ( $actions as $actionName ) {
			as_unschedule_all_actions( $actionName );
		}
	}
}
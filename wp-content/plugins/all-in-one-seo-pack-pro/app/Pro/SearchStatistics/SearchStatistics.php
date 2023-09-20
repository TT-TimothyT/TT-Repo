<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\SearchStatistics as CommonSearchStatistics;

/**
 * Class that holds our Search Statistics feature.
 *
 * @since 4.3.0
 */
class SearchStatistics extends CommonSearchStatistics\SearchStatistics {
	/**
	 * Holds the instance of the API class.
	 *
	 * @since 4.3.0
	 *
	 * @var Api\Api
	 */
	public $api = null;

	/**
	 * Holds the instance of the Stats class.
	 *
	 * @since 4.3.0
	 *
	 * @var Stats\Stats
	 */
	public $stats = null;

	/**
	 * Holds the instance of the Helpers class.
	 *
	 * @since 4.3.0
	 *
	 * @var Helpers
	 */
	public $helpers = null;

	/**
	 * Holds the instance of the Actions class.
	 *
	 * @since 4.3.0
	 *
	 * @var Actions\Actions
	 */
	public $actions = null;

	/**
	 * Holds the instance of the PageSpeed class.
	 *
	 * @since 4.3.0
	 *
	 * @var PageSpeed
	 */
	public $pageSpeed;

	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		$this->api       = new Api\Api();
		$this->actions   = new Actions\Actions();
		$this->stats     = new Stats\Stats();
		$this->helpers   = new Helpers();
		$this->actions   = new Actions\Actions();
		$this->pageSpeed = new PageSpeed();
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.3.0
	 *
	 * @return array The data for Vue.
	 */
	public function getVueData() {
		$dateRange = aioseo()->searchStatistics->stats->getDateRange();

		$data = [
			'isConnected'         => aioseo()->searchStatistics->api->auth->isConnected(),
			'latestAvailableDate' => aioseo()->searchStatistics->stats->latestAvailableDate,
			'unverifiedSite'      => aioseo()->searchStatistics->stats->unverifiedSite,
			'range'               => $dateRange,
			'rolling'             => aioseo()->internalOptions->internal->searchStatistics->rolling,
			'authedSite'          => aioseo()->searchStatistics->api->auth->getAuthedSite(),
			'data'                => [
				'seoStatistics' => $this->getSeoOverviewData( $dateRange ),
				'keywords'      => $this->getKeywordsData( $dateRange )
			]
		];

		return $data;
	}

	/**
	 * Returns the SEO Overview data.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $dateRange The date range.
	 * @return array            The SEO Overview data.
	 */
	protected function getSeoOverviewData( $dateRange = [] ) {
		if (
			! aioseo()->license->hasCoreFeature( 'search-statistics', 'seo-statistics' ) ||
			! aioseo()->searchStatistics->api->auth->isConnected()
		) {
			return parent::getSeoOverviewData( $dateRange );
		}

		$cacheArgs = [
			aioseo()->searchStatistics->api->auth->getAuthedSite(),
			$dateRange['start'],
			$dateRange['end'],
			aioseo()->settings->tablePagination['searchStatisticsSeoStatistics'],
			'0',
			'all',
			'',
			'DESC',
			'clicks'
		];

		$cacheHash  = sha1( implode( ',', $cacheArgs ) );
		$cachedData = aioseo()->core->cache->get( "aioseo_search_statistics_seo_statistics_{$cacheHash}" );
		if ( $cachedData ) {
			if ( ! empty( $cachedData['pages']['paginated']['rows'] ) ) {
				$cachedData = aioseo()->searchStatistics->stats->posts->addPostData( $cachedData, 'statistics' );

				$cachedData['pages']['paginated']['filters'] = aioseo()->searchStatistics->stats->posts->getFilters( 'all', '' );
			}

			return $cachedData;
		}

		return [];
	}

	/**
	 * Returns the Keywords data.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $dateRange The date range.
	 * @return array            The Keywords data.
	 */
	protected function getKeywordsData( $dateRange = [] ) {
		if (
			! aioseo()->license->hasCoreFeature( 'search-statistics', 'keyword-rankings' ) ||
			! aioseo()->searchStatistics->api->auth->isConnected()
		) {
			return parent::getKeywordsData( $dateRange );
		}

		$cacheArgs = [
			aioseo()->searchStatistics->api->auth->getAuthedSite(),
			$dateRange['start'],
			$dateRange['end'],
			aioseo()->settings->tablePagination['searchStatisticsKeywordRankings'],
			'0',
			'all',
			'',
			'DESC',
			'clicks'
		];

		$cacheHash  = sha1( implode( ',', $cacheArgs ) );
		$cachedData = aioseo()->core->cache->get( "aioseo_search_statistics_keywords_{$cacheHash}" );
		if ( $cachedData ) {
			return $cachedData;
		}

		return [];
	}
}
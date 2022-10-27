<?php

namespace BlueSpice\SmartList\Mode;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BsNamespaceHelper;
use MediaWiki\Permissions\PermissionManager;
use TitleFactory;
use Wikimedia\Rdbms\ILoadBalancer;

class ToplistMode extends SmartListBaseMode {

	public const ATTR_PERIOD = 'period';
	public const ATTR_PORTLET_PERIOD = 'portletperiod';

	/** @var PermissionManager */
	private $permissionManager = null;

	/** @var ILoadBalancer */
	private $lb;

	/** @var TitleFactory */
	private $titleFactory = null;

	/**
	 *
	 * @param PermissionManager $permissionManager
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		PermissionManager $permissionManager,
		ILoadBalancer $lb,
		TitleFactory $titleFactory
	) {
		$this->permissionManager = $permissionManager;
		$this->lb = $lb;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'toplist';
	}

	/**
	 * @inheritDoc
	 */
	public function getListType(): string {
		return 'ol';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getParams(): array {
		$parentParams = parent::getParams();
		return array_merge( $parentParams, [
			new ParamDefinition(
				ParamType::INTEGER,
				static::ATTR_COUNT,
				10
			),
			new ParamDefinition(
				ParamType::STRING,
				static::ATTR_PERIOD,
				'alltime'
			),
			new ParamDefinition(
				ParamType::INTEGER,
				static::ATTR_PORTLET_PERIOD,
				0
			)
		] );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getList( $args, $context ): array {
		$args['namespaces'] = $args[self::ATTR_NS];
		$args['portletPeriod'] = $args[self::ATTR_PORTLET_PERIOD];
		$args['period'] = $args[self::ATTR_PERIOD];
		$args['alltime'] = true;

		$objectList = [];
		$dbr = $this->lb->getConnectionRef( DB_REPLICA );
		if ( in_array( $args['period'], [ 'week', 'month' ] ) || in_array( $args['portletPeriod'], [ 7, 30 ] ) ) {
			$tables = [ 'bs_whoisonline' ];
			$columns = [
				'COUNT( wo_page_title ) AS page_counter',
				'wo_page_title',
				'wo_page_namespace'
			];
			$conditions = [ 'wo_action' => 'view' ];
			$options = [
				'GROUP BY' => 'wo_page_title',
				'ORDER BY' => 'page_counter DESC'
			];
			$joinConditions = [];

			if ( $args['period'] === 'week' || $args['portletPeriod'] === 7 ) {
				$maxTS = \BlueSpice\Timestamp::getInstance();
				$maxTS->timestamp->modify( "- 7 days" );
				$conditions[] = 'wo_log_ts >= ' . $maxTS->getTimestamp( TS_MW );
			}
			$alltime = false;
		} else {
			$tables         = [ 'h' => 'hit_counter', 'p' => 'page' ];
			$columns        = [ 'p.page_title', 'h.page_counter', 'p.page_namespace' ];
			$conditions     = [ 'h.page_id = p.page_id' ];
			$options        = [ 'ORDER BY' => 'h.page_counter DESC' ];
			$joinConditions = [];
		}

		if ( !empty( $categories ) ) {
			$cat = explode( ',', $categories );
			$cat = array_map( 'trim', $cat );
			$cat = str_replace( ' ', '_', $cat );

			if ( $alltime === false ) {
				$columns[] = 'wo_page_id';
				$joinConditions = [ 'categorylinks' => [ 'INNER JOIN ', 'wo_page_id = cl_from' ] ];
				$tables[]            = 'categorylinks';
				$conditions['cl_to'] = $cat;
			} else {
				$tables[]            = 'categorylinks';
				$conditions[]        = 'p.page_id = cl_from';
				$conditions['cl_to'] = $cat;
			}
		}

		// string 0 is empty
		if ( !empty( $args['namespaces'] ) || $args['namespaces'] === '0' ) {
			$nsIds = BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $args['namespaces'] );
			if ( !empty( $nsIds ) ) {
				$sField = $alltime ? 'page_namespace' : 'wo_page_namespace';
				$conditions[$sField] = $nsIds;
			}
		}

		$res = $dbr->select(
			$tables,
			$columns,
			$conditions,
			__METHOD__,
			$options,
			$joinConditions
		);

		if ( $dbr->numRows( $res ) > 0 ) {
			$hasCategories = false;
			if ( !empty( $cat ) ) {
				$hasCategories = true;
				$prefixedCategories = [];
				foreach ( $cat as $category ) {
					$category = str_replace( ' ', '_', $category );
					$categoryTitle = $this->titleFactory->makeTitle( NS_CATEGORY, $category );
					$prefixedCategories[] = $categoryTitle->getPrefixedDBKey();
				}
			}

			$list = [];
			$inList = [];
			$currCount = 0;
			if ( $args['alltime'] === false ) {
				foreach ( $res as $row ) {
					if ( $currCount === $args['count'] ) {
						break;
					}
					if ( empty( $row->wo_page_title ) ) {
						continue;
					}
					$title = $this->titleFactory->makeTitle( $row->wo_page_namespace, $row->wo_page_title );

					if ( !$this->permissionManager->quickUserCan(
						'read',
						$context->getUser(),
						$title
					) ) {
						continue;
					}

					if ( $hasCategories === true ) {
						$parents = array_keys( $title->getParentCategories() );
						$result  = array_diff( $prefixedCategories, $parents );
						if ( !empty( $result ) ) {
							continue;
						}
					}
					if ( in_array( $title->getPrefixedText(), $inList ) ) {
						continue;
					}
					$inList[] = $title->getPrefixedText();
					$data = [
						'PREFIXEDTITLE' => $title->getPrefixedText()
					];
					$objectList[] = $data;
					$currCount++;
				}
			} else {
				$list[] = '<ol>';
				foreach ( $res as $row ) {
					if ( $currCount == $args['count'] ) {
						break;
					}
					if ( $row->page_counter == '0' ) {
						continue;
					}

					$title = $this->titleFactory->makeTitle( $row->page_namespace, $row->page_title );
					if ( !$this->permissionManager->quickUserCan(
						'read',
						$context->getUser(),
						$title
					) ) {
						continue;
					}

					if ( $hasCategories === true ) {
						$aParents = array_keys( $title->getParentCategories() );
						$aResult  = array_diff( $prefixedCategories, $aParents );
						if ( !empty( $aResult ) ) {
							continue;
						}
					}

					if ( in_array( $title->getPrefixedText(), $inList ) ) {
						continue;
					}
					$inList[] = $title->getPrefixedText();

					$data = [
						'PREFIXEDTITLE' => $title->getPrefixedText()
					];
					$objectList[] = $data;
					$currCount++;
				}
			}
		}

		$dbr->freeResult( $res );
		return $objectList;
	}

}

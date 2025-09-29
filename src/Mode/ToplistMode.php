<?php

namespace BlueSpice\SmartList\Mode;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BsInvalidNamespaceException;
use BsNamespaceHelper;
use MediaWiki\Context\RequestContext;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
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

	/** @var MessageLocalizer */
	private $messageLocalizer;

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
		$this->messageLocalizer = RequestContext::getMain();
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
			),
			new ParamDefinition(
				ParamType::STRING,
				static::ATTR_CAT,
				''
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
		$args['categories'] = $args[self::ATTR_CAT];
		$alltime = true;

		$objectList = [];
		$dbr = $this->lb->getConnection( DB_REPLICA );
		if ( in_array( $args['period'], [ 'day', 'week', 'month' ] ) ||
			in_array( $args['portletPeriod'], [ 7, 30 ] ) ) {
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

			if ( $args['period'] === 'day' ) {
				$maxTS = \BlueSpice\Timestamp::getInstance();
				$maxTS->timestamp->modify( '- 1 day' );
				$conditions[] = 'wo_log_ts >= ' . $maxTS->getTimestamp( TS_MW );
			}
			if ( $args['period'] === 'week' || $args['portletPeriod'] === 7 ) {
				$maxTS = \BlueSpice\Timestamp::getInstance();
				$maxTS->timestamp->modify( "- 7 days" );
				$conditions[] = 'wo_log_ts >= ' . $maxTS->getTimestamp( TS_MW );
			}
			if ( $args['period'] === 'month' || $args['portletPeriod'] === 30 ) {
				$maxTS = \BlueSpice\Timestamp::getInstance();
				$maxTS->timestamp->modify( "- 30 days" );
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

		if ( !empty( $args['categories'] ) ) {
			$cat = explode( ',', $args['categories'] );
			$cat = array_map( 'trim', $cat );
			$cat = str_replace( ' ', '_', $cat );

			if ( $alltime === false ) {
				$columns[] = 'wo_page_id';
				$joinConditions = [ 'categorylinks' => [ 'INNER JOIN ', 'wo_page_id = cl_from' ] ];
				$tables[] = 'categorylinks';
				$conditions['cl_to'] = $cat;
			} else {
				$tables[] = 'categorylinks';
				$conditions[] = 'p.page_id = cl_from';
				$conditions['cl_to'] = $cat;
			}
		}

		// string 0 is empty
		if ( !empty( $args['namespaces'] ) || $args['namespaces'] === '0' ) {
			try {
				$nsIds = BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $args['namespaces'] );
				$sField = $alltime ? 'page_namespace' : 'wo_page_namespace';
				$conditions[$sField] = $nsIds;
			} catch ( BsInvalidNamespaceException $ex ) {
				$sInvalidNamespaces = implode( ', ', $ex->getListOfInvalidNamespaces() );
				return [ 'error' =>
					$this->messageLocalizer->msg( 'bs-smartlist-invalid-namespaces' )
						->numParams( count( $ex->getListOfInvalidNamespaces() ) )
						->params( $sInvalidNamespaces )
						->text()
				];
			}
		}

		$options['LIMIT'] = $args[self::ATTR_COUNT];
		$res = $dbr->select(
			$tables,
			$columns,
			$conditions,
			__METHOD__,
			$options,
			$joinConditions
		);

		if ( $dbr->affectedRows() > 0 ) {
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

			$inList = [];
			$currCount = 0;
			foreach ( $res as $row ) {
				if ( $currCount === $args['count'] ) {
					break;
				}
				if ( $row->page_counter == '0' ) {
					continue;
				}

				if ( !$alltime ) {
					if ( empty( $row->wo_page_title ) ) {
						continue;
					}
					$title = $this->titleFactory->makeTitle( $row->wo_page_namespace, $row->wo_page_title );
				} else {
					$title = $this->titleFactory->makeTitle( $row->page_namespace, $row->page_title );
				}

				if ( !$this->userCanRead( $title, $context->getUser(), $this->permissionManager ) ) {
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
					'PREFIXEDTITLE' => $title->getPrefixedText(),
					'META' => '(' . $row->page_counter . ')'
				];
				$objectList[] = $data;
				$currCount++;
			}
		}

		return $objectList;
	}

}

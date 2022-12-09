<?php

namespace BlueSpice\SmartList\Mode;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BsInvalidNamespaceException;
use MediaWiki\Permissions\PermissionManager;
use RecentChange;
use RequestContext;
use TitleFactory;
use Wikimedia\Rdbms\ILoadBalancer;

class RecentChangesMode extends GenericSmartlistMode {

	public const ATTR_PERIOD = 'period';
	public const ATTR_SHOW_MINOR = 'minor';
	public const ATTR_SHOW_ARTICLES = 'new';

	/** @var PermissionManager */
	private $permissionManager;

	/** @var ILoadBalancer */
	private $lb;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 *
	 * @param PermissionManager $permissionManager
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( PermissionManager $permissionManager, ILoadBalancer $lb, TitleFactory $titleFactory ) {
		$this->permissionManager = $permissionManager;
		$this->lb = $lb;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'recentchanges';
	}

	/**
	 *
	 * @return string
	 */
	public function getProviderKey(): string {
		return 'generic-smartlist';
	}

	/**
	 * @return IParamDefinition[]
	 */
	public function getParams(): array {
		$parentParams = parent::getParams();
		return array_merge( $parentParams, [
			new ParamDefinition(
				ParamType::STRING,
				static::ATTR_PERIOD,
				'-'
			),
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::ATTR_SHOW_MINOR,
				true
			),
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::ATTR_SHOW_ARTICLES,
				false
			)
		] );
	}

	/**
	 *
	 * @inheritDoc
	 */
	protected function getMeta( $item, $context ): string {
		$metaInfo = ' - <i>(' . $item->username . ', '
			. $context->getLanguage()->date( $item->time, true, true ) . ')</i>';
		return $metaInfo;
	}

	/**
	 * @param array $args
	 * @param RequestContext $context
	 * @return array
	 */
	public function getItems( $args, $context ): array {
		$args['showMinorChanges'] = $args[self::ATTR_SHOW_MINOR];
		$args['period'] = $args[self::ATTR_PERIOD];
		$args['showOnlyNewArticles'] = $args[self::ATTR_SHOW_ARTICLES];

		$dbr = $this->lb->getConnectionRef( DB_REPLICA );
		$conditions = [];

		switch ( $args['period'] ) {
			case 'month':
				$minTimestamp = $dbr->timestamp( time() - 30 * 24 * 60 * 60 );
				break;
			case 'week':
				$minTimestamp = $dbr->timestamp( time() - 7 * 24 * 60 * 60 );
				break;
			case 'day':
				$minTimestamp = $dbr->timestamp( time() - 24 * 60 * 60 );
				break;
			default:
				break;
		}

		try {
			$namespaceIds = $this->makeNamespaceArrayDiff( $args );
			$conditions[] = 'rc_namespace IN (' . implode( ',', $namespaceIds ) . ')';
		} catch ( BsInvalidNamespaceException $ex ) {
			// what to do here
		}

		$this->makeCategoriesFilterCondition( $conditions, 'rc_cur_id', $args );

		switch ( $args['sort'] ) {
			case 'title':
				$orderSQL = 'rc_title';
				break;
			default:
				// ORDER BY MAX() - this one was tricky. It makes sure, only the
				// changes with the maximum date are selected.
				$orderSQL = 'MAX(rc_timestamp)';
				break;
		}

		switch ( $args['order'] ) {
			case 'ASC':
				$orderSQL .= ' ASC';
				break;
			default:
				$orderSQL .= ' DESC';
				break;
		}

		if ( !$args['showMinorChanges'] ) {
			$conditions[] = 'rc_minor = 0';
		}
		if ( $args['showOnlyNewArticles'] ) {
			$orderSQL = 'MIN(rc_timestamp) DESC';
			$conditions['rc_source'] = RecentChange::SRC_NEW;
		}
		if ( !empty( $args['period'] ) && $args['period'] !== '-' ) {
			$conditions[] = "rc_timestamp > '" . $minTimestamp . "'";
		}

		// prevent display of deleted articles
		$conditions[] = 'rc_title = page_title AND rc_namespace = page_namespace';
		// include files
		$conditions[] = 'NOT ( rc_type = 3 AND NOT ( rc_namespace = 6 ) )';

		$fields = [ 'rc_title as title', 'rc_namespace as namespace' ];

		if ( isset( $args['meta'] ) && $args['meta'] == true ) {
			$conditions[] = 'rc_actor=actor_id';
			$fields[] = 'MAX(rc_timestamp) as time, actor_name as username';
		}

		$conditions[ 'page_content_model' ] = [ '', 'wikitext' ];
		$res = $dbr->select(
			[
				'recentchanges',
				'page',
				'actor'
			],
			$fields,
			$conditions,
			__METHOD__,
			[
				'GROUP BY' => 'rc_title, rc_namespace',
				'ORDER BY' => $orderSQL
			]
		);

		$count = 0;
		foreach ( $res as $row ) {
			if ( $count == $args['count'] ) {
				break;
			}

			$title = $this->titleFactory->makeTitleSafe( $row->namespace, $row->title );
			$userCanRead = $this->permissionManager->quickUserCan(
				'read',
				$context->getUser(),
				$title
			);
			if ( !$title || !$userCanRead ) {
				continue;
			}

			$objectList[] = $row;
			$count++;
		}
		$dbr->freeResult( $res );
		return $objectList;
	}

}

<?php

namespace BlueSpice\SmartList\Mode;

use BsInvalidNamespaceException;
use MediaWiki\Context\RequestContext;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\InputProcessor\Processor\BooleanValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordValue;
use RecentChange;
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

	/** @var MessageLocalizer */
	private $messageLocalizer;

	/** @var UserFactory */
	private $userFactory;

	/**
	 *
	 * @param PermissionManager $permissionManager
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 * @param UserFactory $userFactory
	 */
	public function __construct( PermissionManager $permissionManager, ILoadBalancer $lb,
		TitleFactory $titleFactory, UserFactory $userFactory ) {
		parent::__construct();
		$this->permissionManager = $permissionManager;
		$this->lb = $lb;
		$this->titleFactory = $titleFactory;
		$this->userFactory = $userFactory;
		$this->messageLocalizer = RequestContext::getMain();
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
	 * @return array
	 */
	public function getParams(): array {
		$parentParams = parent::getParams();
		return array_merge( $parentParams, [
			static::ATTR_PERIOD => ( new KeywordValue() )
				->setKeywords( [ '-', 'day', 'week', 'month' ] )
				->setDefaultValue( '-' ),
			static::ATTR_SHOW_MINOR => ( new BooleanValue() )->setDefaultValue( true ),
			static::ATTR_SHOW_ARTICLES => ( new BooleanValue() )->setDefaultValue( false ),
		] );
	}

	/**
	 *
	 * @inheritDoc
	 */
	protected function getMeta( $item, $context ): string {
		$user = $this->userFactory->newFromName( $item->username );
		$userName = $user->getRealName();
		if ( !$userName ) {
			$userName = $user->getName();
		}
		$metaInfo = ' - <i>(' . $userName . ', '
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

		$dbr = $this->lb->getConnection( DB_REPLICA );
		$conditions = [];

		$minTimestamp = '';
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
			$sInvalidNamespaces = implode( ', ', $ex->getListOfInvalidNamespaces() );

			return [ 'error' =>
				$this->messageLocalizer->msg( 'bs-smartlist-invalid-namespaces' )
					->numParams( count( $ex->getListOfInvalidNamespaces() ) )
					->params( $sInvalidNamespaces )
					->text()
			];
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
		if ( !empty( $minTimestamp ) ) {
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
		$objectList = [];
		foreach ( $res as $row ) {
			if ( $count == $args['count'] ) {
				break;
			}

			$title = $this->titleFactory->makeTitleSafe( $row->namespace, $row->title );
			if ( !$title ) {
				continue;
			}
			if ( !$this->userCanRead( $title, $context->getUser(), $this->permissionManager ) ) {
				continue;
			}

			$objectList[] = $row;
			$count++;
		}

		return $objectList;
	}

}

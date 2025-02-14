<?php

namespace BlueSpice\SmartList\Mode;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BsInvalidNamespaceException;
use MediaWiki\Context\RequestContext;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
use MessageLocalizer;
use Wikimedia\Rdbms\ILoadBalancer;

class WhatLinksHereMode extends GenericSmartlistMode {

	public const ATTR_TARGET = 'target';

	/** @var PermissionManager */
	private $permissionManager;

	/** @var ILoadBalancer */
	private $lb;

	/** @var TitleFactory */
	private $titleFactory;

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
		parent::__construct();
		$this->permissionManager = $permissionManager;
		$this->lb = $lb;
		$this->titleFactory = $titleFactory;
		$this->messageLocalizer = RequestContext::getMain();
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'whatlinkshere';
	}

	/**
	 * @return IParamDefinition[]
	 */
	public function getParams(): array {
		$parentParams = parent::getParams();
		return array_merge( $parentParams, [
			new ParamDefinition(
				ParamType::STRING,
				static::ATTR_TARGET,
				''
			)
		] );
	}

	/**
	 * @param array $args
	 * @param RequestContext $context
	 * @return array
	 */
	public function getItems( $args, $context ): array {
		$args['target'] = $args[self::ATTR_TARGET];

		if ( empty( $args['target'] ) ) {
			$targetTitle = $context->getTitle();
			if ( !$targetTitle || !$targetTitle->exists() ) {
				// edit mode API context
				$titleText = $context->getRequest()->getRawVal( 'page' );
				$targetTitle = $this->titleFactory->newFromText( $titleText );
			}
		} else {
			$targetTitle = $this->titleFactory->newFromText( $args['target'] );
		}

		if ( $targetTitle === null ) {
			return [];
		}

		$dbr = $this->lb->getConnection( DB_REPLICA );
		$conditions = [
			"pl_from NOT IN ({$targetTitle->getArticleID()})",
		];
		$options = [];

		try {
			$conditions['page_namespace'] = $this->makeNamespaceArrayDiff( $args );
		} catch ( BsInvalidNamespaceException $ex ) {
			$sInvalidNamespaces = implode( ', ', $ex->getListOfInvalidNamespaces() );

			return [ 'error' =>
				$this->messageLocalizer->msg( 'bs-smartlist-invalid-namespaces' )
					->numParams( count( $ex->getListOfInvalidNamespaces() ) )
					->params( $sInvalidNamespaces )
					->text()
			];
		}

		$this->makeCategoriesFilterCondition( $conditions, 'page_id', $args );

		// Default: time
		$options['ORDER BY'] = $args['sort'] == 'title'
			? 'page_title'
			: 'page_id';

		// Default DESC
		$options['ORDER BY'] .= $args['order'] == 'ASC'
			? ' ASC'
			: ' DESC';

		$conditions[ 'page_content_model' ] = [ '', 'wikitext' ];
		$res = $dbr->newSelectQueryBuilder()
			->from( 'page', 'p' )
			->select( [
				'p.page_title AS title',
				'p.page_namespace AS namespace'
			] )
			->join( 'pagelinks', 'pl', [ 'p.page_id = pl.pl_from' ] )
			->join( 'linktarget', 'lt', [
				'pl.pl_target_id = lt.lt_id',
				'lt.lt_namespace' => $targetTitle->getNamespace(),
				'lt.lt_title' => $targetTitle->getDBkey(),
			] )
			->where( $conditions )
			->options( $options )
			->caller( __METHOD__ )
			->fetchResultSet();

		$count = 0;
		$objectList = [];
		foreach ( $res as $row ) {
			if ( $count === $args['count'] ) {
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

		return $objectList;
	}

}

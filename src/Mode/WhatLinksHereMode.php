<?php

namespace BlueSpice\SmartList\Mode;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BsInvalidNamespaceException;
use MediaWiki\Permissions\PermissionManager;
use MessageLocalizer;
use RequestContext;
use TitleFactory;
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

		$targetTitle = empty( $args['target'] )
			? $context->getTitle()
			: $this->titleFactory->newFromText( $args['target'] );

		if ( $targetTitle === null ) {
			return [];
		}

		$dbr = $this->lb->getConnectionRef( DB_REPLICA );
		$tables = [
			'pagelinks',
			'page',
		];
		$fields = [
			'title' => 'page_title',
			'namespace' => 'page_namespace',
		];
		$conditions = [
			"page_id = pl_from",
			"pl_namespace = {$targetTitle->getNamespace()}",
			"pl_from NOT IN ({$targetTitle->getArticleID()})",
			"pl_title = '{$targetTitle->getDBkey()}'",
		];
		$options = [];

		try {
			$conditions['page_namespace'] = $this->makeNamespaceArrayDiff( $args );
		} catch ( BsInvalidNamespaceException $ex ) {
			$sInvalidNamespaces = implode( ', ', $ex->getListOfInvalidNamespaces() );

			return [
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
		$res = $dbr->select(
			$tables,
			$fields,
			$conditions,
			__METHOD__,
			$options
		);

		$count = 0;
		$objectList = [];
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

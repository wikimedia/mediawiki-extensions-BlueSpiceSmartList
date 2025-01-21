<?php

namespace BlueSpice\SmartList\Mode;

use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use Wikimedia\Rdbms\ILoadBalancer;

class NewbiesMode extends BaseMode {

	/** @var ILoadBalancer */
	private $lb;

	/** @var UserFactory */
	private $userFactory;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 *
	 * @param ILoadBalancer $lb
	 * @param UserFactory $userFactory
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( ILoadBalancer $lb, UserFactory $userFactory, TitleFactory $titleFactory ) {
		$this->lb = $lb;
		$this->userFactory = $userFactory;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'newbies';
	}

	/**
	 * @inheritDoc
	 */
	public function getListType(): string {
		return 'csv';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getList( $args, $context ): array {
		$dbr = $this->lb->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'user',
			'user_id',
			[],
			__METHOD__,
			[
				'ORDER BY' => 'user_id DESC',
				'LIMIT' => $args[ 'count' ]
			]
		);

		$out = [];
		foreach ( $res as $row ) {
			$user = $this->userFactory->newFromId( $row->user_id );
			$title = $this->titleFactory->makeTitle( NS_USER, $user->getName() );

			$prefixedTitle = $title->getPrefixedText();

			$data = [
				'PREFIXEDTITLE' => $prefixedTitle
			];

			$out[] = $data;
		}
		return $out;
	}

}

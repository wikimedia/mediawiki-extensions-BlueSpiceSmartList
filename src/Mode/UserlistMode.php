<?php

namespace BlueSpice\SmartList\Mode;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;

class UserlistMode extends SmartListBaseMode {

	public const ATTR_GROUPS = 'groups';

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'userlist';
	}

	/**
	 * @inheritDoc
	 */
	public function getListType(): string {
		return 'ul';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getParams(): array {
		$parentParams = parent::getParams();
		return array_merge( $parentParams, [
			new ParamDefinition(
				ParamType::STRING,
				static::ATTR_GROUPS,
				''
			)
		] );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getList( $args, $context ): array {
		$groups = [];
		$limit = 10;

		if ( isset( $args[ 'groups' ] ) ) {
			$groups = explode( ',', $args['groups'] );
		}
		if ( isset( $args[ 'count' ] ) ) {
			$limit = $args['count'];
		}
		$limitCounter = 0;
		if ( count( $groups ) > 0 ) {
			$list = [];
			$usersInGroup = false;
			foreach ( $groups as $group ) {
				$users = User::findUsersByGroup( trim( $group ) );
				foreach ( $users as $user ) {
					if ( $limitCounter < $limit ) {
						$title = $this->titleFactory->makeTitle( NS_USER, $user->getName() );
						$data = [
							'PREFIXEDTITLE' => $title->getPrefixedText()
						];

						$list[] = $data;
						if ( !$usersInGroup ) {
							$usersInGroup = true;
						}
					}
					$limitCounter++;
				}
			}
			if ( !$usersInGroup ) {
				$list = [];
			}
			return $list;
		}
		return [];
	}
}

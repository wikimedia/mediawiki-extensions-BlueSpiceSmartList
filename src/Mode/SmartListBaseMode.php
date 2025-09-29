<?php

namespace BlueSpice\SmartList\Mode;

use MediaWiki\Linker\LinkTarget;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\User\User;

abstract class SmartListBaseMode extends BaseMode {

	public const ATTR_CAT = 'cat';
	public const ATTR_NS = 'ns';

	/** @var array */
	private array $restrictedNamespaces = [];

	/**
	 *
	 * @inheritDoc
	 */
	public function getParams(): array {
		$params = parent::getParams();
		$params[ static::ATTR_NS ] = [
			'type' => 'string',
			'required' => false,
		];
		$params[ static::ATTR_CAT ] = [
			'type' => 'category',
			'required' => false,
		];
		return $params;
	}

	/**
	 * @param LinkTarget $page
	 * @param User $user
	 * @param PermissionManager $permissionManager
	 * @return bool
	 */
	protected function userCanRead( LinkTarget $page, User $user, PermissionManager $permissionManager ): bool {
		if ( in_array( $page->getNamespace(), $this->restrictedNamespaces ) ) {
			return false;
		}
		if ( !$permissionManager->quickUserCan( 'read', $user, $page ) ) {
			$this->restrictedNamespaces[] = $page->getNamespace();
			return false;
		}
		return true;
	}

}

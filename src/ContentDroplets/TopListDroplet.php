<?php

namespace BlueSpice\SmartList\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

class TopListDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'bs-smartlist-droplet-toplist-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'bs-smartlist-droplet-toplist-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-toplist';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [
			'ext.bluespice.smartList.toplist.visualEditor',
			'ext.bluespice.smartList.dropelts.toplist'
		];
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return [ 'lists', 'data', 'navigation' ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getTagName(): string {
		return 'toplist';
	}

	/**
	 * @return array
	 */
	protected function getAttributes(): array {
		return [];
	}

	/**
	 * @return bool
	 */
	protected function hasContent(): bool {
		return false;
	}

	/**
	 * @return string|null
	 */
	public function getVeCommand(): ?string {
		return 'topListCommand';
	}

}

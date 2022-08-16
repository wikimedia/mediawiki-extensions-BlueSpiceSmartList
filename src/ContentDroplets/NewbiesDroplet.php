<?php

namespace BlueSpice\SmartList\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use Message;
use RawMessage;

class NewbiesDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return new RawMessage( 'Newbies' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return new RawMessage( "Newbies description" );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'flag';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModule(): string {
		return 'ext.bluespice.smartList.newbies.visualEditor';
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
		return 'bs:newbies';
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
		return 'newbiesCommand';
	}

}

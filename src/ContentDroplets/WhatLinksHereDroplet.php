<?php

namespace BlueSpice\SmartList\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use Message;

class WhatLinksHereDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'bs-smartlist-droplet-what-links-here-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'bs-smartlist-droplet-what-links-here-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-whatlinkshere';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.bluespice.smartList.visualEditor',
			'ext.bluespice.smartList.droplets.whatlinkshere'
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
		return 'whatlinkshere';
	}

	/**
	 *
	 * @return array
	 */
	protected function getAttributes(): array {
		return [
			'count' => 5,
			'cat' => '-',
			'ns' => 'all',
			'target' => '',
			'catmode' => 'OR',
			'heading' => '',
			'trim' => 30,
			'showtext' => false,
			'trimtext' => 50,
			'sort' => 'time',
			'order' => 'DESC',
			'showns' => true,
			'numwithtext' => 100,
			'excludens' => '',
			'meta' => false
		];
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
		return 'whatlinkshereCommand';
	}
}

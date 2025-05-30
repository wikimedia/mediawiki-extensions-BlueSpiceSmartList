<?php

namespace BlueSpice\SmartList\Tag;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;

class WhatLinksHere extends SmartlistTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'whatlinkshere' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		return new ClientTagSpecification(
			'WhatLinksHere',
			new RawMessage( '' ),
			$this->getFormSpec(),
			Message::newFromKey( 'bs-smartlist-ve-whatlinkshere-title' )
		);
	}
}

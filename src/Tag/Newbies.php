<?php

namespace BlueSpice\SmartList\Tag;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;

class Newbies extends SmartlistTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'newbies', 'bs:newbies' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'number',
				'name' => 'count',
				'value' => 5,
				'label' => Message::newFromKey( 'bs-smartlist-ve-inspector-count-label' )->text(),
			]
		] );

		return new ClientTagSpecification(
			'Newbies',
			new RawMessage( '' ),
			$formSpec,
			Message::newFromKey( 'bs-smartlist-ve-newbies-title-label' )
		);
	}
}

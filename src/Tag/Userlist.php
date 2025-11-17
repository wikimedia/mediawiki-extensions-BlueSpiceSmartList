<?php

namespace BlueSpice\SmartList\Tag;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;

class Userlist extends SmartlistTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'userlist', 'bs:userlist' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();

		$formSpec->setItems( [
			[
				'type' => 'group_multiselect',
				'name' => 'groups',
				'label' => Message::newFromKey( 'bs-smartlist-ve-userlistinspector-groups' )->text(),
			],
			[
				'type' => 'number',
				'name' => 'count',
				'value' => 10,
				'label' => Message::newFromKey( 'bs-smartlist-ve-inspector-count-label' )->text(),
			],
		] );

		return new ClientTagSpecification(
			'Userlist',
			new RawMessage( '' ),
			$formSpec,
			Message::newFromKey( 'bs-smartlist-ve-userlist-title' )
		);
	}
}

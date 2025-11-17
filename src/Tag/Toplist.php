<?php

namespace BlueSpice\SmartList\Tag;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;

class Toplist extends SmartlistTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'toplist', 'bs:toplist' ];
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
			],
			[
				'type' => 'text',
				'name' => 'ns',
				'label' => Message::newFromKey( 'bs-smartlist-ve-inspector-ns-label' )->text(),
			],
			[
				'type' => 'text',
				'name' => 'cat',
				'label' => Message::newFromKey( 'bs-smartlist-ve-inspector-cat-label' )->text(),
			],
			[
				'type' => 'dropdown',
				'name' => 'period',
				'label' => Message::newFromKey( 'bs-smartlist-ve-inspector-period-label' )->text(),
				'options' => [
					[
						'data' => 'alltime',
						'label' => '-'
					],
					[
						'data' => 'day',
						'label' => Message::newFromKey( 'bs-smartlist-ve-period-day-label' )->text()
					],
					[
						'data' => 'week',
						'label' => Message::newFromKey( 'bs-smartlist-ve-period-week-label' )->text()
					],
					[
						'data' => 'month',
						'label' => Message::newFromKey( 'bs-smartlist-ve-period-month-label' )->text()
					]
				]
			]
		] );

		return new ClientTagSpecification(
			'Toplist',
			new RawMessage( '' ),
			$formSpec,
			Message::newFromKey( 'bs-smartlist-ve-toplist-title' )
		);
	}
}

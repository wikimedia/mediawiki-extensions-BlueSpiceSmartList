<?php

namespace BlueSpice\SmartList\Tag;

use BlueSpice\SmartList\Mode\IMode;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

abstract class SmartlistTag extends GenericTag {

	/**
	 * @param IMode|null $mode
	 */
	public function __construct(
		protected readonly ?IMode $mode
	) {
	}

	/**
	 * @return bool
	 */
	public function hasContent(): bool {
		return false;
	}

	/**
	 * @return bool
	 */
	public function shouldParseInput(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new SmartlistHandler(
			$services->getHookContainer(),
			RequestContext::getMain(),
			$services->getParserFactory(),
			$services->getTitleFactory(),
			$services->getPageProps(),
			$services->getService( 'BlueSpiceSmartList.SmartlistMode' ),
			$this->mode
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		return $this->mode?->getParams();
	}

	/**
	 * @return StandaloneFormSpecification
	 */
	protected function getFormSpec(): StandaloneFormSpecification {
		$formSpec = new StandaloneFormSpecification();

		$tabMain = [
			[
				'type' => 'number',
				'name' => 'count',
				'widget_min' => 1,
				'widget_max' => 250,
				'value' => 5,
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-count' )->text(),
			],
			[
				'type' => 'text',
				'name' => 'ns',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-ns' )->text(),
			],
			[
				'type' => 'text',
				'name' => 'cat',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-cat' )->text(),
			],
			[
				'type' => 'checkbox',
				'labelAlign' => 'inline',
				'name' => 'minor',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-minor' )->text(),
			],
			[
				'type' => 'dropdown',
				'name' => 'catmode',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-catmode' )->text(),
				'options' => [
					[ 'data' => '', 'label' => '' ],
					[
						'data' => 'OR',
						'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-catmode-or-label' )->text()
					],
					[
						'data' => 'AND',
						'label' => Message::newFromKey(
							'bs-smartlist-ve-smartlistinspector-catmode-and-label'
						)->text()
					],
				]
			],
			[
				'type' => 'dropdown',
				'name' => 'period',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-period' )->text(),
				'options' => [
					[ 'data' => '-', 'label' => '-' ],
					[ 'data' => 'day', 'label' => Message::newFromKey( 'bs-smartlist-ve-period-day-label' )->text() ],
					[ 'data' => 'week', 'label' => Message::newFromKey( 'bs-smartlist-ve-period-week-label' )->text() ],
					[
						'data' => 'month',
						'label' => Message::newFromKey( 'bs-smartlist-ve-period-month-label' )->text()
					],
				]
			],
			[
				'type' => 'checkbox',
				'name' => 'new',
				'labelAlign' => 'inline',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-new' )->text(),
			],
		];
		$tabAppearance = [
			[
				'type' => 'text',
				'name' => 'heading',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-heading' )->text(),
			],
			[
				'type' => 'number',
				'name' => 'trim',
				'widget_min' => 1,
				'widget_max' => 250,
				'value' => 5,
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-trim' )->text(),
			],
			[
				'type' => 'checkbox',
				'name' => 'showtext',
				'labelAlign' => 'inline',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-showtext' )->text(),
			],
			[
				'type' => 'number',
				'name' => 'trimtext',
				'widget_min' => 1,
				'widget_max' => 1000,
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-trimtext' )->text(),
			],
			[
				'type' => 'dropdown',
				'name' => 'sort',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-sort' )->text(),
				'options' => [
					[ 'data' => 'time', 'label' => Message::newFromKey( 'bs-smartlist-ve-sort-time-label' )->text() ],
					[ 'data' => 'title', 'label' => Message::newFromKey( 'bs-smartlist-ve-sort-title-label' )->text() ],
				]
			],
			[
				'type' => 'dropdown',
				'name' => 'order',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-order' )->text(),
				'options' => [
					[ 'data' => '', 'label' => '' ],
					[ 'data' => 'DESC', 'label' => Message::newFromKey( 'bs-smartlist-ve-order-desc-label' )->text() ],
					[ 'data' => 'ASC', 'label' => Message::newFromKey( 'bs-smartlist-ve-order-asc-label' )->text() ],
				]
			],
			[
				'type' => 'checkbox',
				'name' => 'showns',
				'labelAlign' => 'inline',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-showns' )->text(),
			],
			[
				'type' => 'number',
				'name' => 'numwithtext',
				'widget_min' => 1,
				'widget_max' => 1000,
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-numwithtext' )->text(),
			],
			[
				'type' => 'checkbox',
				'name' => 'meta',
				'labelAlign' => 'inline',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-meta' )->text(),
			],
		];
		$tabAdvanced = [
			[
				'type' => 'title',
				'name' => 'target',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-target' )->text(),
			],
			[
				'type' => 'text',
				'name' => 'excludens',
				'label' => Message::newFromKey( 'bs-smartlist-ve-smartlistinspector-excludens' )->text(),
			],
		];
		$formSpec->setItems( [
			[
				'type' => 'layout_index',
				'name' => 'tab_index',
				'tabs' => [
					[
						'type' => 'layout_index_tab',
						'name' => 'main',
						'label' => Message::newFromKey( 'bs-smartlist-ve-tab-main' )->text(),
						'items' => $tabMain,
					],
					[
						'type' => 'layout_index_tab',
						'name' => 'appearance',
						'label' => Message::newFromKey( 'bs-smartlist-ve-tab-appearance' )->text(),
						'items' => $tabAppearance,
					],
					[
						'type' => 'layout_index_tab',
						'name' => 'advanced',
						'label' => Message::newFromKey( 'bs-smartlist-ve-tab-advanced' )->text(),
						'items' => $tabAdvanced,
					]
				]
			]
		] );

		return $formSpec;
	}
}

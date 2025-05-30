<?php

namespace BlueSpice\SmartList\Tag;

use BlueSpice\SmartList\BlueSpiceSmartListModeFactory;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;

class Smartlist extends SmartlistTag {

	/**
	 * @param BlueSpiceSmartListModeFactory $modeFactory
	 */
	public function __construct(
		private readonly BlueSpiceSmartListModeFactory $modeFactory
	) {
		parent::__construct( null );
	}

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'smartlist', 'bs:smartlist', 'infobox', 'bs:infobox' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		return new ClientTagSpecification(
			'Smartlist',
			new RawMessage( '' ),
			$this->getFormSpec(),
			Message::newFromKey( 'bs-smartlist-ve-smartlist-title' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$params = [];
		foreach ( $this->modeFactory->getAllModes() as $mode ) {
			$params = array_merge( $params, $mode->getParams() );
		}
		return $params;
	}
}

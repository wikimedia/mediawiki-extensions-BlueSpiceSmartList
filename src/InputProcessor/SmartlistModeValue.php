<?php

namespace BlueSpice\SmartList\InputProcessor;

use BlueSpice\SmartList\BlueSpiceSmartListModeFactory;
use BlueSpice\SmartList\Mode\IMode;
use MWStake\MediaWiki\Component\InputProcessor\GenericProcessor;
use StatusValue;

class SmartlistModeValue extends GenericProcessor {

	/**
	 * @param BlueSpiceSmartListModeFactory $modeFactory
	 */
	public function __construct(
		private readonly BlueSpiceSmartListModeFactory $modeFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function process( mixed $value, string $fieldKey ): StatusValue {
		$parentStatus = parent::process( $value, $fieldKey );
		if ( !$parentStatus->isGood() ) {
			return $parentStatus;
		}
		if ( !$this->isRequired() && $value === null ) {
			return StatusValue::newGood( $this->getDefaultValue() );
		}

		$allModes = $this->modeFactory->getAllModes();
		/** @var IMode $mode */
		foreach ( $allModes as $mode ) {
			if ( $mode->getKey() === $value ) {
				return StatusValue::newGood( $mode->getKey() );
			}
		}
		return StatusValue::newFatal(
			'bs-smartlist-input-validator-invalid mode', $fieldKey, $value
		);
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(): mixed {
		return array_merge( parent::jsonSerialize(), [
			'type' => 'smartlist_mode',
		] );
	}
}

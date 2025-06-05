<?php

namespace BlueSpice\SmartList;

use BlueSpice\SmartList\Mode\IMode;
use InvalidArgumentException;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class BlueSpiceSmartListModeFactory {

	/** @var ObjectFactory */
	private $objectFactory;

	/** @var array */
	public $modeRegistry;

	/** @var array */
	private $modes;

	/**
	 *
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( ObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;
		$this->modeRegistry = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceSmartListModeRegistry'
		);
	}

	/**
	 * @param string $mode
	 * @return IMode|null
	 * @throws InvalidArgumentException
	 */
	public function createMode( $mode ): IMode {
		if ( !isset( $this->modeRegistry[$mode] ) ) {
			throw new InvalidArgumentException( 'No such mode: ' . $mode );
		}
		if ( !isset( $this->modes[$mode] ) ) {
			$this->modes[$mode] = $this->objectFactory->createObject( $this->modeRegistry[$mode] );
		}
		return $this->modes[$mode];
	}

	/**
	 * @return array
	 */
	public function getAllModes(): array {
		foreach ( $this->modeRegistry as $key => $spec ) {
			$this->createMode( $key );
		}

		return $this->modes;
	}

}

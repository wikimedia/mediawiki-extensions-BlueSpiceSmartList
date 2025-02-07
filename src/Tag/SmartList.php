<?php

namespace BlueSpice\SmartList\Tag;

use BlueSpice\SmartList\BlueSpiceSmartListModeFactory;
use BlueSpice\Tag\Tag;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use Throwable;

class SmartList extends Tag {

	/** @var MediaWikiServices */
	private $services = [];

	/** @var BlueSpiceSmartListModeFactory */
	private $factory;

	/** @var array */
	private $modes = [];

	/**
	 *
	 */
	public function __construct() {
		$this->services = MediaWikiServices::getInstance();
		$this->factory = $this->services->getService( 'BlueSpiceSmartList.SmartlistMode' );

		$this->modes = $this->factory->getAllModes();
	}

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return PageBreakHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		$context = RequestContext::getMain();
		$titleFactory = $this->services->getTitleFactory();
		$hookContainer = $this->services->getHookContainer();

		if ( isset( $processedArgs['mode'] ) ) {
			try {
				$mode = $this->factory->createMode( $processedArgs['mode'] );
			} catch ( Throwable $e ) {
				$mode = null;
			}

		} else {
			$mode = $this->factory->createMode( 'recentchanges' );
		}

		return new SmartListHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame,
			$context,
			$titleFactory,
			$hookContainer,
			$mode
		);
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTagNames() {
		return [
			'smartlist',
			'bs:smartlist',
			'infobox',
			'bs:infobox'
		];
	}

	/**
	 * @return IParamDefinition[]
	 */
	public function getArgsDefinitions() {
		$allModesParams = [];
		foreach ( $this->modes as $mode ) {
			$allModesParams = array_merge( $allModesParams, $mode->getParams() );
		}

		return $allModesParams;
	}

}

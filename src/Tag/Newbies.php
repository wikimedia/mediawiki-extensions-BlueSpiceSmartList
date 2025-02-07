<?php

namespace BlueSpice\SmartList\Tag;

use BlueSpice\SmartList\BlueSpiceSmartListModeFactory;
use BlueSpice\SmartList\Mode\IMode;
use BlueSpice\Tag\MarkerType\NoWiki;
use BlueSpice\Tag\Tag;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

class Newbies extends Tag {

	/** @var MediaWikiServices */
	private $services;

	/** @var BlueSpiceSmartListModeFactory */
	private $factory;

	/** @var IMode */
	private $mode;

	/**
	 */
	public function __construct() {
		$this->services = MediaWikiServices::getInstance();
		$this->factory = $this->services->getService( 'BlueSpiceSmartList.SmartlistMode' );
		$this->mode = $this->factory->createMode( 'newbies' );
	}

	/**
	 *
	 * @return bool
	 */
	public function needsParsedInput() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	public function needsParseArgs() {
		return false;
	}

	/**
	 *
	 * @return MarkerType
	 */
	public function getMarkerType() {
		return new NoWiki();
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

		return new SmartListHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame,
			$context,
			$titleFactory,
			$hookContainer,
			$this->mode
		);
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTagNames() {
		return [
			'newbies',
			'bs:newbies',
		];
	}

	/**
	 * @return IParamDefinition[]
	 */
	public function getArgsDefinitions() {
		return $this->mode->getParams();
	}

}

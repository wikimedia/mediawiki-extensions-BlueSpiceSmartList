<?php

namespace BlueSpice\SmartList\Tag;

use BlueSpice\SmartList\ListRenderer;
use BlueSpice\SmartList\Parser\ParserObjectWrapper;
use BlueSpice\Tag\Handler;
use FormatJson;
use MediaWiki\HookContainer\HookContainer;
use MWException;
use PageProps;
use Parser;
use PPFrame;
use RequestContext;
use TitleFactory;

class SmartListHandler extends Handler {

	/** @var RequestContext */
	private $context = null;

	/** @var TitleFactory */
	private $titleFactory = null;

	/** @var IMode */
	private $mode;

	/**
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param RequestContext $context
	 * @param TitleFactory $titleFactory
	 * @param HookContainer $hookContainer
	 * @param IMode $mode
	 */
	public function __construct( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame, RequestContext $context, TitleFactory $titleFactory, HookContainer $hookContainer, $mode ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$this->context = $context;
		$this->titleFactory = $titleFactory;
		$this->hookContainer = $hookContainer;
		$this->mode = $mode;
	}

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->updateCacheExpiry( 0 );
		$this->parser->getOutput()->setProperty( 'bs-tag-smartlist', 1 );

		if ( !isset( $this->mode ) ) {
			throw new MWException( 'No mode' );
		}

		$this->processedArgs['count'] = $this->processedArgs[$this->mode::ATTR_COUNT];

		foreach ( $this->processedArgs as $arg => $val ) {
			// Allow Magic Words (Variables) and Parser Functions as arguments
			$this->processedArgs[$arg] = $this->parser->recursivePreprocess( $val );
		}

		$this->parser->getOutput()->setProperty( 'bs-smartlist', FormatJson::encode( $this->processedArgs ) );
		$this->processedArgs['listType'] = $this->mode->getListType();
		$this->processedArgs['mode'] = $this->mode->getKey();

		$outputList = $this->mode->getList( $this->processedArgs, $this->context );
		$parser = new ParserObjectWrapper( $this->parser );
		$listRenderer = new ListRenderer(
			$parser,
			PageProps::getInstance(),
			$this->titleFactory,
			$this->hookContainer
		);
		return $listRenderer->render( $outputList, $this->processedArgs );
	}

}

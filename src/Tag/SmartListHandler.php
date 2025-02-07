<?php

namespace BlueSpice\SmartList\Tag;

use BlueSpice\SmartList\ListRenderer;
use BlueSpice\SmartList\Parser\ParserObjectWrapper;
use BlueSpice\Tag\Handler;
use MediaWiki\Context\RequestContext;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\TitleFactory;
use OOUI\MessageWidget;

class SmartListHandler extends Handler {

	/** @var RequestContext */
	private $context = null;

	/** @var TitleFactory */
	private $titleFactory = null;

	/** @var HookContainer */
	private $hookContainer;

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
		$this->parser->getOutput()->setPageProperty( 'bs-tag-smartlist', 1 );

		if ( !isset( $this->mode ) ) {
			OutputPage::setupOOUI();
			$this->parser->getOutput()->setEnableOOUI( true );
			return ( new MessageWidget( [
				'label' => wfMessage( 'bs-smartlist-error-mode-not-found' )->text(),
				'type' => 'error',
			] ) )->toString();
		}

		$this->processedArgs['count'] = $this->processedArgs[$this->mode::ATTR_COUNT];

		foreach ( $this->processedArgs as $arg => $val ) {
			// Allow Magic Words (Variables) and Parser Functions as arguments
			$this->processedArgs[$arg] = $this->parser->recursivePreprocess( $val );
		}

		$this->parser->getOutput()->setPageProperty( 'bs-smartlist', FormatJson::encode( $this->processedArgs ) );
		$this->processedArgs['mode'] = $this->mode->getKey();

		$outputList = $this->mode->getList( $this->processedArgs, $this->context );
		if ( isset( $outputList['error'] ) ) {
			return $outputList['error'];
		}

		$this->processedArgs['listType'] = $this->mode->getListType();

		// Not injected as to not break compatibility for all other handlers
		// Use fresh parser, to avoid side effects from parsing SL content in the same parser
		$parser = MediaWikiServices::getInstance()->getParserFactory()->create();
		$parser->setOptions( ParserOptions::newFromAnon() );
		$parser->clearState();
		$parser->setOutputType( Parser::OT_HTML );

		$parser = new ParserObjectWrapper( $parser );
		$listRenderer = new ListRenderer(
			$parser,
			MediaWikiServices::getInstance()->getPageProps(),
			$this->titleFactory,
			$this->hookContainer
		);
		return $listRenderer->render( $outputList, $this->processedArgs );
	}

}

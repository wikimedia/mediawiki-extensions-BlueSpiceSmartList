<?php

namespace BlueSpice\SmartList\Tag;

use BlueSpice\SmartList\BlueSpiceSmartListModeFactory;
use BlueSpice\SmartList\ListRenderer;
use BlueSpice\SmartList\Mode\IMode;
use BlueSpice\SmartList\Parser\ParserObjectWrapper;
use MediaWiki\Context\RequestContext;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Json\FormatJson;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\PageProps;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\TitleFactory;
use MWException;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use Throwable;

class SmartlistHandler implements ITagHandler {

	/**
	 * @param HookContainer $hookContainer
	 * @param RequestContext $context
	 * @param ParserFactory $parserFactory
	 * @param TitleFactory $titleFactory
	 * @param PageProps $pageProps
	 * @param BlueSpiceSmartListModeFactory $modeFactory
	 * @param IMode|null $mode
	 */
	public function __construct(
		private readonly HookContainer $hookContainer,
		private readonly RequestContext $context,
		private readonly ParserFactory $parserFactory,
		private readonly TitleFactory $titleFactory,
		private readonly PageProps $pageProps,
		private readonly BlueSpiceSmartListModeFactory $modeFactory,
		private ?IMode $mode
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		if ( !$this->mode ) {
			$this->mode = $this->tryCreateMode( $params );
		}
		$parser->getOutput()->updateCacheExpiry( 0 );
		$parser->getOutput()->setPageProperty( 'bs-tag-smartlist', 1 );

		if ( !$this->mode ) {
			OutputPage::setupOOUI();
			$parser->getOutput()->setEnableOOUI( true );
			return ( new MessageWidget( [
				'label' => wfMessage( 'bs-smartlist-error-mode-not-found' )->text(),
				'type' => 'error',
			] ) )->toString();
		}

		$params['count'] = $params[$this->mode::ATTR_COUNT];

		foreach ( $params as $arg => $val ) {
			if ( is_string( $val ) ) {
				// Allow Magic Words (Variables) and Parser Functions as arguments
				$params[$arg] = $parser->recursivePreprocess( $val );
			}
		}

		$parser->getOutput()->setPageProperty( 'bs-smartlist', FormatJson::encode( $params ) );
		$params['mode'] = $this->mode->getKey();

		$outputList = $this->mode->getList( $params, $this->context );
		if ( isset( $outputList['error'] ) ) {
			return $outputList['error'];
		}

		$params['listType'] = $this->mode->getListType();

		// Not injected as to not break compatibility for all other handlers
		// Use fresh parser, to avoid side effects from parsing SL content in the same parser
		$parser = $this->parserFactory->create();
		$parser->setOptions( ParserOptions::newFromAnon() );
		$parser->clearState();
		$parser->setOutputType( Parser::OT_HTML );

		$parser = new ParserObjectWrapper( $parser );
		$listRenderer = new ListRenderer(
			$parser,
			$this->pageProps,
			$this->titleFactory,
			$this->hookContainer
		);
		return $listRenderer->render( $outputList, $params );
	}

	/**
	 * @param array $params
	 * @return IMode|null
	 * @throws MWException
	 */
	private function tryCreateMode( array $params ): ?IMode {
		if ( isset( $params['mode'] ) && $params['mode'] ) {
			try {
				return $this->modeFactory->createMode( $params['mode'] );
			} catch ( Throwable $e ) {
				return null;
			}
		} else {
			return $this->modeFactory->createMode( 'recentchanges' );
		}
	}
}

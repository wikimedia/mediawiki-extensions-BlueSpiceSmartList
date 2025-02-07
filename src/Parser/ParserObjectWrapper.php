<?php

namespace BlueSpice\SmartList\Parser;

use BlueSpice\SmartList\IParser;
use MediaWiki\Parser\Parser as MediaWikiParser;

class ParserObjectWrapper implements IParser {

	/**
	 * @var MediaWikiParser
	 */
	private $parser = null;

	/**
	 *
	 * @param MediaWikiParser $parser
	 */
	public function __construct( MediaWikiParser $parser ) {
		$this->parser = $parser;
	}

	/**
	 *
	 * @param string $wikitext
	 * @return string
	 */
	public function parse( $wikitext ): string {
		return $this->parser->recursiveTagParseFully( $wikitext );
	}
}

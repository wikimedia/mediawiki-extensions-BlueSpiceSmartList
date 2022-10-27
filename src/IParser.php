<?php

namespace BlueSpice\SmartList;

interface IParser {

	/**
	 * @param string $wikitext
	 * @return string The resulting HTML
	 */
	public function parse( $wikitext ): string;
}

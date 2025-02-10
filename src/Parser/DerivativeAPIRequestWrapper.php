<?php

namespace BlueSpice\SmartList\Parser;

use BlueSpice\SmartList\IParser;
use MediaWiki\Api\ApiMain;
use MediaWiki\Request\DerivativeRequest;
use MediaWiki\Request\WebRequest;

class DerivativeAPIRequestWrapper implements IParser {

	/**
	 * @var WebRequest
	 */
	private $request = null;

	/**
	 *
	 * @param WebRequest $request
	 */
	public function __construct( WebRequest $request ) {
		$this->request = $request;
	}

	/**
	 *
	 * @param string $wikitext
	 * @return string
	 */
	public function parse( $wikitext ): string {
		$params = new DerivativeRequest(
			$this->request,
			[
				'action' => 'parse',
				'text' => $wikitext,
				'contentmodel' => 'wikitext'
			]
		);
		$api = new ApiMain( $params );
		$api->execute();
		$data = $api->getResult()->getResultData();

		return $data['parse']['text'];
	}
}

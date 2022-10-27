<?php

namespace BlueSpice\SmartList\Tests;

use BlueSpice\SmartList\ListRenderer;
use BlueSpice\SmartList\Parser\DerivativeAPIRequestWrapper;
use BlueSpice\SmartList\Parser\ParserObjectWrapper;
use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;
use PageProps;
use Parser;
use Title;
use WebRequest;

/**
 * @covers \BlueSpice\SmartList\ListRenderer
 */
class ListRendererTest extends MediaWikiIntegrationTestCase {

	/**
	 * @param IParser $parser
	 * @param array $items
	 * @param array $args
	 * @param string $expect
	 * @dataProvider provideRenderTestData
	 * @covers \BlueSpice\SmartList\ListRenderer::render
	 */
	public function testrender( $parser, $items, $args, $expect ) {
		$propsMock = $this->createMock( PageProps::class );
		$titleFactory = MediaWikiServices::getInstance()->getTitleFactory();
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();

		$listRenderer = new ListRenderer( $parser, $propsMock, $titleFactory, $hookContainer );
		$actualContent = $listRenderer->render( $items, $args );
		$this->assertEquals( $expect, $actualContent );
	}

	public function provideRenderTestData() {
		$mediawikiParserMock = $this->createMock( Parser::class );
		$mediawikiParserMock->method( 'recursiveTagParseFully' )
			->will( $this->returnCallback(
				static function ( $wikitext ) {
					return $wikitext;
				}
		) );
		$parserWrapper = new ParserObjectWrapper( $mediawikiParserMock );

		$mockRequest = $this->createMock( WebRequest::class );
		$derivativeWrapper = new DerivativeAPIRequestWrapper( $mockRequest );
		return [
			'parser-wrapper-with-no-visible-text' => [
				// parser
				$parserWrapper,
				// items to parse
				[
					[
						'PREFIXEDTITLE' => Title::newFromText( 'Dummy' )->getPrefixedText(),
						'TEXT' => 'Lorem ipsum text'
					]
				],
				// args for items
				[
					'heading' => 'ListRendererTest',
					'count' => 10,
					'listType' => 'ul',
					'mode' => 'recentchanges'
				],
				// output what is expected
				'<div class="bs-smartlist"><h3>ListRendererTest</h3>* [[Dummy|Dummy]]
</div>'
			],
			'parser-wrapper-with-visible-text' => [
				$parserWrapper,
				[
					[
						'PREFIXEDTITLE' => Title::newFromText( 'Dummy' )->getPrefixedText(),
						'TEXT' => 'Lorem ipsum text'
					]
				],
				[
					'heading' => 'ListRendererTest - 2',
					'count' => 10,
					'listType' => 'ul',
					'mode' => 'recentchanges',
					'showtext' => true,
					'numwithtext' => 10
				],
				'<div class="bs-smartlist"><h3>ListRendererTest - 2</h3>* [[Dummy|Dummy]]<br/>Lorem ipsum text
</div>'
			],
			'parser-wrapper-newbies' => [
				$parserWrapper,
				[
					[
						'PREFIXEDTITLE' => Title::newFromText( 'User 1' )->getPrefixedText()
					],
					[
						'PREFIXEDTITLE' => Title::newFromText( 'User 2' )->getPrefixedText()
					],
					[
						'PREFIXEDTITLE' => Title::newFromText( 'User 3' )->getPrefixedText()
					]
				],
				[
					'heading' => 'Newbie',
					'count' => 10,
					'listType' => 'csv',
					'mode' => 'newbies'
				],
				'<div class="bs-smartlist"><h3>Newbie</h3>[[User 1|User 1]],[[User 2|User 2]],[[User 3|User 3]],</div>'
			],
			'parser-wrapper-with-more-items-than-shown' => [
				$parserWrapper,
				[
					[
						'PREFIXEDTITLE' => Title::newFromText( 'Dummy 1' )->getPrefixedText()
					],
					[
						'PREFIXEDTITLE' => Title::newFromText( 'Dummy 2' )->getPrefixedText()
					],
					[
						'PREFIXEDTITLE' => Title::newFromText( 'Dummy 3' )->getPrefixedText()
					]
				],
				[
					'heading' => 'ListRendererTest',
					'count' => 2,
					'listType' => 'ul',
					'mode' => 'recentchanges'
				],
				'<div class="bs-smartlist"><h3>ListRendererTest</h3>* [[Dummy 1|Dummy 1]]
* [[Dummy 2|Dummy 2]]
</div>'
			]
		];
	}

}

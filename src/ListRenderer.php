<?php

namespace BlueSpice\SmartList;

use BsStringHelper;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Page\PageProps;
use MediaWiki\Title\TitleFactory;
use ViewBaseElement;

class ListRenderer {

	/**
	 * @var IParser
	 */
	private $parser = null;

	/**
	 * @var PageProps
	 */
	private $pageProps = null;

	/**
	 *
	 * @var TitleFactory
	 */
	private $titleFactory = null;

	/**
	 *
	 * @var HookContainer
	 */
	private $hookContainer = null;

	/**
	 *
	 * @param IParser $parser
	 * @param PageProps $pageProps
	 * @param TitleFactory $titleFactory
	 * @param HookContainer $hookContainer
	 */
	public function __construct( $parser, $pageProps, TitleFactory $titleFactory, HookContainer $hookContainer ) {
		$this->parser = $parser;
		$this->pageProps = $pageProps;
		$this->titleFactory = $titleFactory;
		$this->hookContainer = $hookContainer;
	}

	/**
	 *
	 * @param array $items
	 * @param array $args
	 * @return string
	 */
	public function render( $items, $args ) {
		$args = array_merge( [
			'trim' => 50,
			'showns' => false,
			'showtext' => false,
			'trimtext' => 50,
			'listType' => 'ul'
		], $args );

		$count = 0;
		$smartListView = new ViewBaseElement();
		if ( isset( $args['heading'] ) && !empty( $args['heading'] ) ) {
			$smartListView->setTemplate(
				'<div class="bs-smartlist"><h3>{HEADING}</h3>{LIST}</div>'
			);
		} else {
			$smartListView->setTemplate( '<div class="bs-smartlist">{LIST}</div>' );
		}

		if ( empty( $items ) ) {
			$smartListView->addData( [
				'HEADING' => !empty( $args['heading'] )
					? $args['heading']
					: wfMessage( 'bs-smartlist-recent-changes' )->text(),
				'LIST' => wfMessage( 'bs-smartlist-no-entries' )->text()
				] );

			return $smartListView->execute();
		}

		$smartListListView = new ViewBaseElement();
		$smartListListView->setAutoElement( false );
		foreach ( $items as $item ) {
			if ( $count >= $args['count'] ) {
				break;
			}

			$title = $this->titleFactory->newFromText( $item['PREFIXEDTITLE'] );

			if ( !$args['showns'] ) {
				$displayTitle = $title->getText();
			} else {
				$displayTitle = $title->getFullText();
			}

			$properties = $this->pageProps->getProperties( $title, 'displaytitle' );
			$pageId = $title->getArticleID();

			if ( isset( $properties[ $pageId ] ) ) {
				$displayTitle = $properties[ $pageId ];
			}

			$displayTitle = BsStringHelper::shorten(
				$displayTitle,
				[ 'max-length' => $args['trim'], 'position' => 'middle' ]
			);
			$item['DISPLAYTITLE'] = $displayTitle;

			$smartListListEntryView = new ViewBaseElement();

			$listType = "* ";
			if ( $args['listType'] === 'ol' ) {
				$listType = "# ";
			}
			if ( $args['listType'] === 'csv' ) {
				$listType = '';
			}

			$template = "$listType";
			/*
			 * escaping of links necessary for category links
			 * to link to the page instead of adding it
			 * https://www.mediawiki.org/wiki/Help:Categories#Linking_to_a_category
			*/
			$template .= "[[:{PREFIXEDTITLE}|{DISPLAYTITLE}]]";

			if ( isset( $item[ 'COMMENT' ] ) ) {
				$template .= " {COMMENT} ";
			}

			if ( isset( $item[ 'META' ] ) ) {
				$template .= " {META} ";
			}

			if ( isset( $item[ 'TEXT' ] ) && $args[ 'showtext' ] == true ) {
				if ( $count <= $args['numwithtext'] ) {
					$template .= "<br/>{TEXT}";
				}
			}
			if ( $args['listType'] === 'csv' ) {
				if ( $count + 1 < $args['count'] || $count + 1 < count( $items ) ) {
					$template .= ', ';
				}
			} else {
				$template .= "\n";
			}

			$smartListListEntryView->setTemplate( $template );
			$this->hookContainer->run(
				'BSSmartListBeforeEntryViewAddData',
				[
					&$item,
					$args,
					$smartListListEntryView,
					$item
				]
			);

			$smartListListEntryView->addData( $item );
			$smartListListView->addItem( $smartListListEntryView );
			$count++;
		}

		$smartListView->addData( [
			'HEADING' => !empty( $args['heading'] )
				? $args['heading']
				: wfMessage( 'bs-smartlist-recent-changes' )->text(),
			'LIST' => $this->parser->parse( $smartListListView->execute() )
			]
		);

		return $smartListView->execute();
	}

}

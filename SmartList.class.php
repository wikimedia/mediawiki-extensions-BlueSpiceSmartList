<?php

/**
 * SmartList extension for BlueSpice
 *
 * Displays a list of pages, i.e. recently changed articles.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Patric Wirth
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    BlueSpiceSmartList
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\SmartList\ListRenderer;
use BlueSpice\SmartList\Parser\DerivativeAPIRequestWrapper;
use MediaWiki\MediaWikiServices;

/**
 * Base class for SmartList extension
 * @package BlueSpiceSmartList
 */
class SmartList extends \BlueSpice\Extension {

	/**
	 * Initialization of ShoutBox extension
	 */
	protected function initExt() {
		$this->setHook( 'ParserFirstCallInit', 'onParserFirstCallInit' );
	}

	/**
	 * Returns list of most visited pages called via Ajax
	 * @param int $iCount number of items
	 * @param string $sTime timespan
	 * @return string most visited pages
	 */
	public static function getMostVisitedPages( $iCount, $sTime ) {
		$services = MediaWikiServices::getInstance();
		$args['count'] = $iCount;
		$args['portletperiod' ] = $sTime;
		$args['ns'] = '';
		$args['period' ] = 'alltime';
		$context = RequestContext::getMain();

		try {
			$factory = $services->getService( 'BlueSpiceSmartList.SmartlistMode' );
			$mode = $factory->createMode( 'toplist' );
			$content = $mode->getList( $args, $context );
			$parser = new DerivativeAPIRequestWrapper( $context->getRequest() );
			$listRenderer = new ListRenderer( $parser, PageProps::getInstance(), $services->getTitleFactory() );
			$sContent = $listRenderer->render( $content, $args );
		} catch ( Exception $e ) {
			$oErrorListView = new ViewTagErrorList();
			$oErrorListView->addItem( new ViewTagError( $e->getMessage() ) );
			$sContent = $oErrorListView->execute();
		}
		return $sContent;
	}

	/**
	 * Returns list of most edited pages called via Ajax
	 * @param int $iCount number of items
	 * @param string $sTime timespan
	 * @return string most edited pages
	 */
	public static function getMostEditedPages( $iCount, $sTime ) {
		return MediaWikiServices::getInstance()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getEditedPages( $iCount, $sTime );
	}

	/**
	 * @param int $iCount number of items
	 * @param string $sTime timespan
	 * @return string
	 */
	public static function getMostActivePortlet( $iCount, $sTime ) {
		return MediaWikiServices::getInstance()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getActivePortlet( $iCount, $sTime );
	}

	/**
	 * @param int $iCount number of items
	 * @return string
	 */
	public static function getYourEditsPortlet( $iCount ) {
		return MediaWikiServices::getInstance()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getYourEdits( $iCount );
	}

	/**
	 * Generates list of your edits
	 * @param int $iCount
	 * @param string $sOrigin
	 * @param int $iDisplayLength
	 * @return string list of edits
	 */
	public function getYourEdits( $iCount, $sOrigin = 'dashboard', $iDisplayLength = 18 ) {
		$aEditTitles = $this->getYourEditsTitles(
			$this->getUser(),
			$iCount,
			$sOrigin,
			$iDisplayLength
		);
		if ( count( $aEditTitles ) === 0 ) {
			return '<ul><li>' . wfMessage( 'bs-smartlist-noedits' )->plain() . '</ul></li>';
		}

		$aEdits = [];
		foreach ( $aEditTitles as $aEdit ) {
			$sHtml = '';
			$oTitle = $aEdit['title'];
			$sHtml = $aEdit['displayText'];
			$sLink = $this->services->getLinkRenderer()->makeLink(
				$oTitle,
				new HtmlArmor( $sHtml )
			);
			$aEdits[] = Html::openElement( 'li' ) . $sLink . Html::closeElement( 'li' );
		}

		$sEdits = '<ul>' . implode( '', $aEdits ) . '</ul>';

		return $sEdits;
	}

	/**
	 * @param User $user
	 * @param int $iCount
	 * @param string $sOrigin
	 * @param int $iDisplayLength
	 * @return \Title[]
	 */
	public static function getYourEditsTitles( $user, $iCount, $sOrigin = 'dashboard',
		$iDisplayLength = 18 ) {
		$iCount = BsCore::sanitize( $iCount, 0, BsPARAMTYPE::INT );

		$oDbr = wfGetDB( DB_REPLICA );
		$query = MediaWikiServices::getInstance()->getRevisionStore()->getQueryInfo();
		$actor = ActorMigration::newMigration()->getWhere( $oDbr, 'rev_user', $user );
		$query['tables'][] = 'page';
		$query['joins']['page'] = [ 'JOIN', 'rev_page = page_id' ];
		$query['fields'][] = 'page_id';
		$query['fields'][] = 'page_content_model';
		$conds = [
			'page_content_model' => [ '', 'wikitext' ],
			$actor['conds']
		];
		$options = [
			'GROUP BY' => 'page_id',
			'ORDER BY' => 'MAX(rev_timestamp) DESC',
			'LIMIT' => $iCount
		];
		$res = $oDbr->select(
			$query['tables'],
			$query['fields'],
			$conds,
			__METHOD__,
			$options,
			$query['joins']
		);

		$aEdits = [];
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->rev_page );
				if ( !( $oTitle instanceof Title ) ) {
					continue;
				}
				if ( $sOrigin === 'dashboard' ) {
					$displayText = $oTitle->getPrefixedText();
				} else {
					$displayText = BsStringHelper::shorten(
						$oTitle->getPrefixedText(),
						[ 'max-length' => $iDisplayLength, 'position' => 'middle' ]
					);
				}
				$aEdits[] = [
					'title' => $oTitle,
					'displayText' => $displayText
				];
			}
		}

		return $aEdits;
	}

	/**
	 * Generates list of most edited pages
	 * @param int $iCount
	 * @param int $iTime
	 * @return string list of pages or empty
	 */
	public function getEditedPages( $iCount, $iTime ) {
		$oDbr = wfGetDB( DB_REPLICA );
		$iCount = BsCore::sanitize( $iCount, 10, BsPARAMTYPE::INT );
		$iTime = BsCore::sanitize( $iTime, 0, BsPARAMTYPE::INT );

		$aConditions = [];
		if ( $iTime !== 0 ) {
			$this->getTimestampForQuery( $aConditions, $iTime );
		}

		$aConditions['page_content_model'] = [ '', 'wikitext' ];
		$res = $oDbr->select(
				[ 'revision', 'page' ],
				[
					'COUNT(rev_page) as page_counter',
					'rev_page'
				],
				$aConditions,
				__METHOD__,
				[
					'GROUP BY' => 'rev_page',
					'ORDER BY' => 'page_counter DESC',
					'LIMIT' => $iCount
				]
		);

		$aList = [];
		if ( $res->numRows() > 0 ) {
			$aList[] = '<ol>';

			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->rev_page );
				$sLink = $this->services->getLinkRenderer()->makeLink( $oTitle );
				$aList[] = '<li>' . $sLink . ' (' . $row->page_counter . ')</li>';
			}

			$aList[] = '</ol>';
		}

		return implode( "\n", $aList );
	}

	/**
	 * Generates list of most edited pages
	 * @param int $iCount
	 * @param int $iTime
	 * @return string list of pages or empty
	 */
	public function getActivePortlet( $iCount, $iTime ) {
		$oDbr = wfGetDB( DB_REPLICA );
		$iCount = BsCore::sanitize( $iCount, 10, BsPARAMTYPE::INT );
		$iTime = BsCore::sanitize( $iTime, 0, BsPARAMTYPE::INT );
		$services = MediaWikiServices::getInstance();

		$aConditions = [];
		if ( $iTime !== 0 ) {
			$this->getTimestampForQuery( $aConditions, $iTime );
		}

		$query = $this->services->getRevisionStore()->getQueryInfo();
		$query['fields'][] = 'COUNT(actor_rev_user.actor_name) as edit_count';
		$options = [
			'GROUP BY' => 'rev_user',
			'ORDER BY' => 'edit_count DESC'
		];
		$res = $oDbr->select(
			$query['tables'],
			$query['fields'],
			$aConditions,
			__METHOD__,
			$options,
			$query['joins']
		);

		$aList = [];
		if ( $res->numRows() > 0 ) {
			$aList[] = '<ol>';

			$i = 1;
			$userFactory = $this->services->getUserFactory();
			$userNameUtils = $this->services->getUserNameUtils();
			foreach ( $res as $row ) {
				if ( $i > $iCount ) {
					break;
				}
				$oUser = $userFactory->newFromId( $row->rev_user );
				if ( $userNameUtils->isIP( $oUser->getName() ) ) {
					continue;
				}

				$oTitle = Title::makeTitle( NS_USER, $oUser->getName() );
				$sLink = $this->services->getLinkRenderer()->makeLink( $oTitle );
				$aList[] = '<li>' . $sLink . ' (' . $row->edit_count . ')</li>';
				$i++;
			}

			$aList[] = '</ol>';
		}

		return implode( "\n", $aList );
	}

	/**
	 * Returns timestamp for portlet queries, at at moment just for month
	 *
	 * @param array &$aConditions reference to array of conditions
	 * @param int $iTime
	 * @return bool always true
	 */
	public function getTimestampForQuery( &$aConditions, $iTime ) {
		$iTimeInSec = $iTime * 24 * 60 * 60;
		$iTimeStamp = wfTimestamp( TS_UNIX ) - $iTimeInSec;
		$iTimeStamp = wfTimestamp( TS_MW, $iTimeStamp );
		$aConditions = [ 'rev_timestamp >= ' . $iTimeStamp ];

		return true;
	}

}

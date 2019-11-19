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
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    BlueSpiceSmartList
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\Services;

/**
 * Base class for SmartList extension
 * @package BlueSpiceSmartList
 */
class SmartList extends BsExtensionMW {
	/**
	 * Initialization of ShoutBox extension
	 */
	protected function initExt() {
		$this->setHook( 'ParserFirstCallInit', 'onParserFirstCallInit' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		$this->setHook( 'BSDashboardsAdminDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsAdminDashboardPortalPortlets' );
		$this->setHook( 'BSDashboardsUserDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsUserDashboardPortalPortlets' );
		$this->setHook( 'BSUsageTrackerRegisterCollectors' );
	}

	/**
	 * Hook-Handler for Hook 'LoadExtensionSchemaUpdates'
	 * @param DatabaseUpdater $updater Updater
	 * @return bool Always true
	 */
	public static function getSchemaUpdates( $updater ) {
		$odbw = wfGetDB( DB_MASTER );
		if ( $odbw->tableExists( 'bs_settings' ) === true ) {
			$res = $odbw->select(
				'bs_settings',
				$odbw->addIdentifierQuotes( 'value' ),
				[
					$odbw->addIdentifierQuotes( 'key' ) => 'MW::SmartList::Order'
				]
			);
			if ( $odbw->numRows( $res ) === 1 ) {
				$obj = $odbw->fetchObject( $res );
				if ( $obj->value === serialize( 'time' ) || $obj->value === serialize( 'title' ) ) {
					$odbw->delete(
					'bs_settings',
					[
						$odbw->addIdentifierQuotes( 'key' ) => 'MW::SmartList::Order'
					]
				);
				}
			}
		}
		return true;
	}

	/**
	 * Returns list of most visited pages called via Ajax
	 * @param int $iCount number of items
	 * @param string $sTime timespan
	 * @return string most visited pages
	 */
	public static function getMostVisitedPages( $iCount, $sTime ) {
		try {
			$sContent =
				\MediaWiki\MediaWikiServices::getInstance()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getToplist(
					'',
					[
						'count' => $iCount,
						'portletperiod' => $sTime
					],
					null
				);
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
		return \MediaWiki\MediaWikiServices::getInstance()
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
		return \MediaWiki\MediaWikiServices::getInstance()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getActivePortlet( $iCount, $sTime );
	}

	/**
	 * @param int $iCount number of items
	 * @return string
	 */
	public static function getYourEditsPortlet( $iCount ) {
		return \MediaWiki\MediaWikiServices::getInstance()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getYourEdits( $iCount );
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return bool always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = [
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain()
			],
			'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mosteditedpagesdesc' )->plain()
		];
		$aPortlets[] = [
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain()
			],
			'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mostvisitedpagesdesc' )->plain()
		];
		$aPortlets[] = [
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain()
			],
			'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mostactiveusersdesc' )->plain()
		];

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param bool $bIsDefault default
	 * @return bool always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = [
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain()
			]
		];
		$aPortalConfig[1][] = [
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain()
			]
		];
		$aPortalConfig[2][] = [
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain()
			]
		];

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsUserDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return bool always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = [
			'type'  => 'BS.SmartList.YourEditsPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-lastedits' )->plain()
			],
			'title' => wfMessage( 'bs-smartlist-lastedits' )->plain(),
			'description' => wfMessage( 'bs-smartlist-lasteditsdesc' )->plain()
		];
		$aPortlets[] = [
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain()
			],
			'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mosteditedpagesdesc' )->plain()
		];
		$aPortlets[] = [
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain()
			],
			'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mostvisitedpagesdesc' )->plain()
		];
		$aPortlets[] = [
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain()
			],
			'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mostactiveusersdesc' )->plain()
		];

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsUserDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param bool $bIsDefault default
	 * @return bool always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = [
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain()
			]
		];
		$aPortalConfig[0][] = [
			'type'  => 'BS.SmartList.YourEditsPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-lastedits' )->plain()
			]
		];
		$aPortalConfig[1][] = [
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain()
			]
		];
		$aPortalConfig[2][] = [
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => [
				'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain()
			]
		];

		return true;
	}

	/**
	 * Registers a tag "bs:smartlist" with the parser. For legacy reasons with
	 * HalloWiki, also "smartlist" is supported. Called by ParserFirstCallInit
	 * hook.
	 * @param Parser &$parser MediaWiki parser object
	 * @return bool true to allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$parser ) {
		// for legacy reasons
		$parser->setHook( 'infobox', [ $this, 'onTagSmartList' ] );
		$parser->setHook( 'bs:infobox', [ $this, 'onTagSmartList' ] );

		$parser->setHook( 'smartlist', [ $this, 'onTagSmartList' ] );
		$parser->setHook( 'bs:smartlist', [ $this, 'onTagSmartList' ] );
		$parser->setHook( 'newbies', [ $this, 'onTagBsNewbies' ] );
		$parser->setHook( 'bs:newbies', [ $this, 'onTagBsNewbies' ] );
		$parser->setHook( 'toplist', [ $this, 'onTagToplist' ] );
		$parser->setHook( 'bs:toplist', [ $this, 'onTagToplist' ] );

		return true;
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object &$oResponse reference
	 * @param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if ( $type != 'tags' ) {
			return true;
		}

		$extension = \BlueSpice\Services::getInstance()->getBSExtensionFactory()
				->getExtension( 'BlueSpiceSmartList' );
		$helplink = $extension->getUrl();

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:smartlist';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'smartlist';
		$oDescriptor->desc = wfMessage( 'bs-smartlist-tag-smartlist-desc' )->plain();
		$oDescriptor->code = '<bs:smartlist />';
		$oDescriptor->mwvecommand = 'smartListCommand';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = [
			[
				'label' => wfMessage( 'bs-smartlist-tag-smartlist-example-rc' )->plain(),
				'code' => '<bs:smartlist new="true" count="7" ns="104" trim="false" />'
			],
			[
				'label' => wfMessage( 'bs-smartlist-tag-smartlist-example-whatlinkshere' )->plain(),
				'code' => '<bs:smartlist mode="whatlinkshere" target="ARTICLENAME" />'
			]
		];
		$oDescriptor->helplink = $helplink;
		$oResponse->result[] = $oDescriptor;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:newbies';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'newbies';
		$oDescriptor->desc = wfMessage( 'bs-smartlist-tag-newbies-desc' )->plain();
		$oDescriptor->code = '<bs:newbies />';
		$oDescriptor->mwvecommand = 'newbiesCommand';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = [
			[
				'code' => '<bs:newbies count="3" />'
			]
		];
		$oDescriptor->helplink = $helplink;
		$oResponse->result[] = $oDescriptor;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:toplist';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'toplist';
		$oDescriptor->desc = wfMessage( 'bs-smartlist-tag-toplist-desc' )->plain();
		$oDescriptor->code = '<bs:toplist />';
		$oDescriptor->mwvecommand = 'topListCommand';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = [
			[
				'code' => '<bs:toplist count="4" cat="Wiki" period="month" />'
			]
		];
		$oDescriptor->helplink = $helplink;
		$oResponse->result[] = $oDescriptor;

		return true;
	}

	/**
	 * Renders the SmartList tag. Called by parser function.
	 * @param string $sInput Inner HTML of SmartList tag. Not used.
	 * @param array $aArgs List of tag attributes.
	 * @param Parser $oParser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function onTagSmartList( $sInput, $aArgs, $oParser ) {
		$oParser->disableCache();
		$oParser->getOutput()->setProperty( 'bs-tag-smartlist', 1 );

		foreach ( $aArgs as $sArg => $sVal ) {
			// Allow Magic Words (Variables) and Parser Functions as arguments
			$aArgs[$sArg] = $oParser->recursivePreprocess( $sVal );
		}

		// Get arguments
		$aArgs['count'] = BsCore::sanitizeArrayEntry( $aArgs, 'count', 5, BsPARAMTYPE::INT );
		$aArgs['namespaces'] = BsCore::sanitizeArrayEntry( $aArgs, 'ns', 'all', BsPARAMTYPE::SQL_STRING );
		$aArgs['categories'] = BsCore::sanitizeArrayEntry( $aArgs, 'cat', '-', BsPARAMTYPE::SQL_STRING );
		$aArgs['categoryMode'] = BsCore::sanitizeArrayEntry( $aArgs, 'catmode', 'OR', BsPARAMTYPE::SQL_STRING );
		$aArgs['showMinorChanges'] = BsCore::sanitizeArrayEntry( $aArgs, 'minor', true, BsPARAMTYPE::BOOL );
		$aArgs['period'] = BsCore::sanitizeArrayEntry( $aArgs, 'period', '-', BsPARAMTYPE::SQL_STRING );
		$aArgs['mode'] = BsCore::sanitizeArrayEntry( $aArgs, 'mode', 'recentchanges', BsPARAMTYPE::STRING );
		$aArgs['showOnlyNewArticles'] = BsCore::sanitizeArrayEntry( $aArgs, 'new', false, BsPARAMTYPE::BOOL );
		$aArgs['heading'] = BsCore::sanitizeArrayEntry( $aArgs, 'heading', '', BsPARAMTYPE::STRING );
		$aArgs['trim'] = BsCore::sanitizeArrayEntry( $aArgs, 'trim', 30, BsPARAMTYPE::NUMERIC );
		$aArgs['showtext'] = BsCore::sanitizeArrayEntry( $aArgs, 'showtext', false, BsPARAMTYPE::BOOL );
		$aArgs['trimtext'] = BsCore::sanitizeArrayEntry( $aArgs, 'trimtext', 50, BsPARAMTYPE::NUMERIC );
		$aArgs['sort'] = BsCore::sanitizeArrayEntry( $aArgs, 'sort', 'time', BsPARAMTYPE::SQL_STRING );
		$aArgs['order'] = BsCore::sanitizeArrayEntry( $aArgs, 'order', 'DESC', BsPARAMTYPE::SQL_STRING );
		$aArgs['showns'] = BsCore::sanitizeArrayEntry( $aArgs, 'showns', true, BsPARAMTYPE::BOOL );
		$aArgs['numwithtext'] = BsCore::sanitizeArrayEntry( $aArgs, 'numwithtext', 100, BsPARAMTYPE::INT );
		$aArgs['meta'] = BsCore::sanitizeArrayEntry( $aArgs, 'meta', false, BsPARAMTYPE::BOOL );
		$aArgs['target'] = BsCore::sanitizeArrayEntry( $aArgs, 'target', '', BsPARAMTYPE::STRING );
		$aArgs['excludens'] = BsCore::sanitizeArrayEntry( $aArgs, 'excludens', '', BsPARAMTYPE::SQL_STRING );

		$oSmartListView = new ViewBaseElement();
		if ( !empty( $aArgs['heading'] ) ) {
			$oSmartListView->setTemplate( '<div class="bs-smartlist"><h3>{HEADING}</h3>{LIST}</div>' );
		} else {
			$oSmartListView->setTemplate( '<div class="bs-smartlist">{LIST}</div>' );
		}

		$sCustomList = $this->getCustomList( $aArgs, $oParser );

		if ( empty( $sCustomList ) ) {
			$sCustomList = wfMessage( 'bs-smartlist-no-entries' )->plain();
		}

		$oSmartListView->addData( [
			'HEADING' => !empty( $aArgs['heading'] ) ? $aArgs['heading'] : wfMessage( 'bs-smartlist-recent-changes' )->plain(),
			'LIST' => $sCustomList
			]
		);

		$oParser->getOutput()->setProperty( 'bs-smartlist', FormatJson::encode( $aArgs ) );
		return $oSmartListView->execute();
	}

	/**
	 * Actually renders the SmartList list view.
	 * @param int $aArgs['count'] Maximum number of items in list.
	 * @param string $aArgs['namespaces'] Comma separated list of namespaces that should be considered.
	 * @param string $aArgs['categories'] Comma separated list of categories that should be considered.
	 * @param string $aArgs['period'] Period of time that should be considered (-|day|week|month)
	 * @param string $aArgs['mode'] Defines the basic criteria of pages that should be considered. Default: recentchanges. Other Extensions can hook into SmartList and define their own mode.
	 * @param bool $aArgs['showMinorChanges'] Should minor changes be considered
	 * @param bool $aArgs['showOnlyNewArtiles'] Should edits be considered or only page creations
	 * @param int $aArgs['trim'] Maximum number of title characters.
	 * @param bool $aArgs['showtext'] Also display article text.
	 * @param int $aArgs['trimtext'] Maximum number of text characters.
	 * @param string $aArgs['order'] Sort order for list. (time|title)
	 * @param string $aArgs['excludens'] Comma separated list of excluded namespaces.
	 * @param bool $aArgs['showns'] Show namespace befor title.
	 * @return string HTML output that is to be displayed.
	 */
	private function getCustomList( $aArgs, \Parser $parser ) {
		/*
		 * Contains the items that need to be displayed
		 * @var List of objects with three properties: title, namespace and timestamp
		 */
		$aObjectList = [];
		$aNamespaceIds = [];

		$oErrorListView = new ViewTagErrorList( $this );
		$oValidationResult = BsValidator::isValid( 'ArgCount', $aArgs['count'], [ 'fullResponse' => true ] );
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}
		/*
		 * Validation of namespaces and categories
		 */
		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$aArgs['categoryMode'],
			[
				'fullResponse' => true,
				'setname' => 'catmode',
				'set' => [
					'AND',
					'OR'
				]
			]
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$aArgs['period'],
			[
				'fullResponse' => true,
				'setname' => 'period',
				'set' => [
					'-',
					'day',
					'week',
					'month'
				]
			]
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'PositiveInteger',
			$aArgs['trim'],
			[ 'fullResponse' => true ]
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'PositiveInteger',
			$aArgs['trimtext'],
			[ 'fullResponse' => true ]
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$aArgs['sort'],
			[
				'fullResponse' => true,
				'setname' => 'sort',
				'set' => [
					'time',
					'title'
				]
			]
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$aArgs['order'],
			[
				'fullResponse' => true,
				'setname' => 'order',
				'set' => [
					'ASC',
					'DESC'
				]
			]
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		if ( $aArgs['mode'] == 'recentchanges' ) {
			$dbr = wfGetDB( DB_REPLICA );
			$aConditions = [];

			switch ( $aArgs['period'] ) {
				case 'month': $sMinTimestamp = $dbr->timestamp( time() - 30 * 24 * 60 * 60 );
					break;
				case 'week': $sMinTimestamp = $dbr->timestamp( time() - 7 * 24 * 60 * 60 );
					break;
				case 'day': $sMinTimestamp = $dbr->timestamp( time() - 24 * 60 * 60 );
					break;
				default:
					break;
			}

			try {
				$aNamespaceIds = $this->makeNamespaceArrayDiff( $aArgs );
				$aConditions[] = 'rc_namespace IN (' . implode( ',', $aNamespaceIds ) . ')';
			} catch ( BsInvalidNamespaceException $ex ) {
				$sInvalidNamespaces = implode( ', ', $ex->getListOfInvalidNamespaces() );
				$oErrorListView->addItem(
					new ViewTagError(
						wfMessage( 'bs-smartlist-invalid-namespaces' )
							->numParams( count( $ex->getListOfInvalidNamespaces() ) )
							->params( $sInvalidNamespaces )
							->text()
					)
				);
			}

			$this->makeCategoriesFilterCondition( $aConditions, $aArgs, 'rc_cur_id' );

			switch ( $aArgs['sort'] ) {
				case 'title':
					$sOrderSQL = 'rc_title';
					break;
				default:
					// ORDER BY MAX() - this one was tricky. It makes sure, only the changes with the maximum date are selected.
					$sOrderSQL = 'MAX(rc_timestamp)';
					break;
			}

			switch ( $aArgs['order'] ) {
				case 'ASC':
					$sOrderSQL .= ' ASC';
					break;
				default:
					$sOrderSQL .= ' DESC';
					break;
			}

			if ( !$aArgs['showMinorChanges'] ) {
				$aConditions[] = 'rc_minor = 0';
			}
			if ( $aArgs['showOnlyNewArticles'] ) {
				$sOrderSQL = 'MIN(rc_timestamp) DESC';
				$aConditions[] = 'rc_new = 1';
			}
			if ( !empty( $aArgs['period'] ) && $aArgs['period'] !== '-' ) {
				$aConditions[] = "rc_timestamp > '" . $sMinTimestamp . "'";
			}

			$aConditions[] = 'rc_title = page_title AND rc_namespace = page_namespace'; // prevent display of deleted articles
			$aConditions[] = 'NOT ( rc_type = 3 AND NOT ( rc_namespace = 6 ) )'; // include files

			$aFields = [ 'rc_title as title', 'rc_namespace as namespace' ];
			if ( isset( $aArgs['meta'] ) && $aArgs['meta'] == true ) {
				$aFields[] = 'MAX(rc_timestamp) as time, rc_user_text as username';
			}

			$aConditions[ 'page_content_model' ] = [ '', 'wikitext' ];
			$res = $dbr->select(
				[
					'recentchanges',
					'page'
				],
				$aFields,
				$aConditions,
				__METHOD__,
				[
					'GROUP BY' => 'rc_title, rc_namespace',
					'ORDER BY' => $sOrderSQL
				]
			);

			$iCount = 0;
			foreach ( $res as $row ) {
				if ( $iCount == $aArgs['count'] ) {
					break;
				}

				$oTitle = Title::makeTitleSafe( $row->namespace, $row->title );

				if ( !$oTitle || !$oTitle->quickUserCan( 'read' ) ) {
					continue;
				}

				$aObjectList[] = $row;
				$iCount++;
			}
			$dbr->freeResult( $res );

		} elseif ( $aArgs['mode'] == 'whatlinkshere' ) {
			// PW(25.02.2015) TODO:
			// There could be filters - see Special:Whatlinkshere
			$oTargetTitle = empty( $aArgs['target'] )
				? $this->context->getTitle()
				: Title::newFromText( $aArgs['target'] );

			if ( is_null( $oTargetTitle ) ) {
				$oErrorListView->addItem(
					new ViewTagError(
						wfMessage( 'bs-smartlist-invalid-target' )->text()
					)
				);
				return $oErrorListView->execute();
			}

			$dbr = wfGetDB( DB_REPLICA );
			$aTables = [
				'pagelinks',
				'page',
			];
			$aFields = [
				'title' => 'page_title',
				'namespace' => 'page_namespace',
			];
			$aConditions = [
				"page_id = pl_from",
				"pl_namespace = {$oTargetTitle->getNamespace()}",
				"pl_from NOT IN ({$oTargetTitle->getArticleID()})",
				"pl_title = '{$oTargetTitle->getDBkey()}'",
			];
			$aOptions = [];

			try {
				$aConditions['page_namespace'] = $this->makeNamespaceArrayDiff( $aArgs );
			} catch ( BsInvalidNamespaceException $ex ) {
				$sInvalidNamespaces = implode( ', ', $ex->getListOfInvalidNamespaces() );
				$oErrorListView->addItem(
					new ViewTagError(
						wfMessage( 'bs-smartlist-invalid-namespaces' )
							->numParams( count( $ex->getListOfInvalidNamespaces() ) )
							->params( $sInvalidNamespaces )
							->text()
					)
				);
				return $oErrorListView->execute();
			}

			$this->makeCategoriesFilterCondition( $aConditions, $aArgs, 'page_id' );

			// Default: time
			$aOptions['ORDER BY'] = $aArgs['sort'] == 'title'
				? 'page_title'
				: 'page_id';

			// Default DESC
			$aOptions['ORDER BY'] .= $aArgs['order'] == 'ASC'
				? ' ASC'
				: ' DESC';

			$aConditions[ 'page_content_model' ] = [ '', 'wikitext' ];
			$res = $dbr->select(
				$aTables,
				$aFields,
				$aConditions,
				__METHOD__,
				$aOptions
			);

			$iCount = 0;
			foreach ( $res as $row ) {
				if ( $iCount == $aArgs['count'] ) {
					break;
				}

				$oTitle = Title::makeTitleSafe( $row->namespace, $row->title );
				if ( !$oTitle || !$oTitle->quickUserCan( 'read' ) ) {
					continue;
				}

				$aObjectList[] = $row;
				$iCount++;
			}

			$dbr->freeResult( $res );

		} else {
			Hooks::run(
				'BSSmartListCustomMode', [ &$aObjectList, $aArgs, $this ]
			);
		}

		if ( $oErrorListView->hasEntries() ) {
			return $oErrorListView->execute();
		}

		$oSmartListListView = new ViewBaseElement();
		$oSmartListListView->setAutoElement( false );
		$iItems = 1;
		if ( count( $aObjectList ) ) {
			foreach ( $aObjectList as $row ) {
				$oTitle = Title::makeTitleSafe( $row->namespace, $row->title );

				// Security here: only show pages the user can read.
				$sText = '';
				$sMeta = '';
				$sPrefixedTitle = $oTitle->getPrefixedText();
				$sComment = '';

				if ( isset( $aArgs['meta'] ) && $aArgs['meta'] == true ) {
					$sMeta = ' - <i>(' . $row->username . ', ' . $this->getLanguage()->date( $row->time, true, true ) . ')</i>';
				}
				$oSmartListListEntryView = new ViewBaseElement();
				if ( $aArgs['showtext'] && ( $iItems <= $aArgs['numwithtext'] ) ) {
					$oSmartListListEntryView->setTemplate( '*[[:{PREFIXEDTITLE}|{DISPLAYTITLE}]]{META}<br/>{TEXT}' . "\n" );
					$sText = BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle );
					$sText = Sanitizer::stripAllTags( $sText );
					$sText = BsStringHelper::shorten( $sText, [ 'max-length' => $aArgs['trimtext'], 'position' => 'end' ] );
					$sText = '<nowiki>' . $sText . '</nowiki>';
				} else {
					$oSmartListListEntryView->setTemplate( '*[[:{PREFIXEDTITLE}|{DISPLAYTITLE}]] {COMMENT} {META}' . "\n" );
				}

				if ( $aArgs['showns'] == true ) {
					$sDisplayTitle = $oTitle->getFullText();
				} else {
					$sDisplayTitle = $oTitle->getText();
				}
				$sDisplayTitle = BsStringHelper::shorten( $sDisplayTitle, [ 'max-length' => $aArgs['trim'], 'position' => 'middle' ] );

				$aData = [
					'PREFIXEDTITLE' => $sPrefixedTitle,
					'DISPLAYTITLE' => $sDisplayTitle,
					'COMMENT' => $sComment,
					'META' => $sMeta,
					'TEXT' => $sText
				];
				Hooks::run( 'BSSmartListBeforeEntryViewAddData', [ &$aData, $aArgs, $oSmartListListEntryView, $row ] );
				$oSmartListListEntryView->addData( $aData );
				$oSmartListListView->addItem( $oSmartListListEntryView );
				$iItems++;
			}
		} else {
			return '';
		}
		return $parser->recursiveTagParseFully( $oSmartListListView->execute() );
	}

	/**
	 * Generates list of your edits
	 * @param int $iCount
	 * @param string $sOrigin
	 * @param int $iDisplayLength
	 * @return string list of edits
	 */
	public function getYourEdits( $iCount, $sOrigin = 'dashboard', $iDisplayLength = 18 ) {
		$aEditTitles = $this->getYourEditsTitles( $this->getUser(), $iCount, $sOrigin, $iDisplayLength );
		if ( count( $aEditTitles ) === 0 ) {
			return '<ul><li>' . wfMessage( 'bs-smartlist-noedits' )->plain() . '</ul></li>';
		}

		$aEdits = [];
		foreach ( $aEditTitles as $aEdit ) {
			$sHtml = '';
			$oTitle = $aEdit['title'];
			$sHtml = $aEdit['displayText'];
			$sLink = Linker::link( $oTitle, $sHtml );
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
	public static function getYourEditsTitles( $user, $iCount, $sOrigin = 'dashboard', $iDisplayLength = 18 ) {
		$iCount = BsCore::sanitize( $iCount, 0, BsPARAMTYPE::INT );

		$oDbr = wfGetDB( DB_REPLICA );
		$res = $oDbr->select(
			[ 'revision', 'page' ],
			[ 'rev_page' ],
			[
				'rev_user' => $user->getId(),
				'rev_page = page_id',
				'page_content_model' => [ '', 'wikitext' ]
			],
			__METHOD__,
			[
				'GROUP BY' => 'page_id',
				'ORDER BY' => 'MAX(rev_timestamp) DESC',
				'LIMIT' => $iCount
			]
		);

		$aEdits = [];
		if ( $oDbr->numRows( $res ) > 0 ) {
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->rev_page );
				if ( !( $oTitle instanceof Title ) ) {
					continue;
				}
				if ( $sOrigin === 'dashboard' ) {
					$displayText = $oTitle->getPrefixedText();
				} else {
					$displayText = BsStringHelper::shorten( $oTitle->getPrefixedText(), [ 'max-length' => $iDisplayLength, 'position' => 'middle' ] );
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
	 * Renders the BsNewbies tag. Called by parser function.
	 * @param string $sInput Inner HTML of BsNewbies tag. Not used.
	 * @param array $aArgs List of tag attributes.
	 * @param Parser $oParser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function onTagBsNewbies( $sInput, $aArgs, $oParser ) {
		$oParser->disableCache();
		$iCount = BsCore::sanitizeArrayEntry( $aArgs, 'count', 5, BsPARAMTYPE::INT );

		$oDbr = wfGetDB( DB_REPLICA );
		$res = $oDbr->select(
			'user',
			'user_id',
			[],
			__METHOD__,
			[
				'ORDER BY' => 'user_id DESC',
				'LIMIT' => $iCount
			]
		);

		$aOut = [];
		foreach ( $res as $row ) {
			$oUser = User::newFromId( $row->user_id );
			$oTitle = Title::makeTitle( NS_USER, $oUser->getName() );

			$aOut[] = Services::getInstance()->getLinkRenderer()->makeKnownLink(
				$oTitle
			);
		}

		$oDbr->freeResult( $res );
		$oParser->getOutput()->setProperty( 'bs-newbies', FormatJson::encode( $aArgs ) );
		return implode( ', ', $aOut );
	}

	/**
	 * Renders the BsTagToplist tag. Called by parser function.
	 * @param string $sInput Inner HTML of BsTagMToplist tag. Not used.
	 * @param array $aArgs List of tag attributes.
	 * @param Parser $oParser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function onTagToplist( $sInput, $aArgs, $oParser ) {
		$oParser->disableCache();

		$oParser->getOutput()->setProperty( 'bs-toplist', FormatJson::encode( $aArgs ) );

		try {
			$sContent = $this->getToplist( $sInput, $aArgs, $oParser );
		} catch ( Exception $e ) {
			$oErrorListView = new ViewTagErrorList();
			$oErrorListView->addItem( new ViewTagError( $e->getMessage() ) );
			$sContent = $oErrorListView->execute();
		}
		return $sContent;
	}

	/**
	 * Deprecated! page.page_counter was completely removed in MediaWiki 1.25
	 * Generates a list of the most visisted pages
	 * @deprecated since version 2.23.3
	 * @param string $sInput Inner HTML of BsTagMToplist tag. Not used.
	 * @param array $aArgs List of tag attributes.
	 * @param Parser $oParser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function getToplist( $sInput, $aArgs, $oParser ) {
		global $wgDBprefix;

		if ( !ExtensionRegistry::getInstance()->isLoaded( 'HitCounters' ) ) {
			return wfMessage( 'bs-smartlist-hitcounter-missing' )->plain();
		}
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'BlueSpiceWhoIsOnline' ) ) {
			throw new MWException(
				'Extension "BlueSpiceWhoIsOnline" is required for this tag'
			);
		}
		$sCat = BsCore::sanitizeArrayEntry( $aArgs, 'cat',           '', BsPARAMTYPE::STRING );
		$sNs = BsCore::sanitizeArrayEntry( $aArgs, 'ns',            '', BsPARAMTYPE::STRING );
		$iCount = BsCore::sanitizeArrayEntry( $aArgs, 'count',         10, BsPARAMTYPE::INT );
		$sPeriod = BsCore::sanitizeArrayEntry( $aArgs, 'period', 'alltime', BsPARAMTYPE::STRING );
		$iPortletPeriod = BsCore::sanitizeArrayEntry( $aArgs, 'portletperiod', 0, BsPARAMTYPE::INT );
		$bAlltime = true;

		$oDbr = wfGetDB( DB_REPLICA );
		if ( in_array( $sPeriod, [ 'week', 'month' ] ) || in_array( $iPortletPeriod, [ 7, 30 ] ) ) {
			$aTables = [ 'bs_whoisonline' ];
			$aColumns = [
				'COUNT( wo_page_title ) AS page_counter',
				'wo_page_title',
				'wo_page_namespace'
			];
			$aConditions = [ 'wo_action' => 'view' ];
			$aOptions = [
				'GROUP BY' => 'wo_page_title',
				'ORDER BY' => 'page_counter DESC'
			];
			$aJoinConditions = [];

			if ( $sPeriod === 'week' || $iPortletPeriod === 7 ) {
				$maxTS = \BlueSpice\Timestamp::getInstance();
				$maxTS->timestamp->modify( "- 7 days" );
				$aConditions[] = 'wo_log_ts >= ' . $maxTS->getTimestamp( TS_MW );
			}
			$bAlltime = false;
		} else {
			$aTables         = [ 'hit_counter', 'page' ];
			$aColumns        = [ 'page_title', 'page_counter', 'page_namespace' ];
			$aConditions     = [ $wgDBprefix . 'hit_counter.page_id = ' . $wgDBprefix . 'page.page_id' ];
			$aOptions        = [ 'ORDER BY' => 'page_counter DESC' ];
			$aJoinConditions = [];
		}

		if ( !empty( $sCat ) ) {
			$aCategories = explode( ',', $sCat );
			$aCategories = array_map( 'trim', $aCategories );
			$aCategories = str_replace( ' ', '_', $aCategories );

			if ( $bAlltime === false ) {
				$aColumns[] = 'wo_page_id';
				$aJoinConditions = [ 'categorylinks' => [ 'INNER JOIN ', 'wo_page_id = cl_from' ] ];
				$aTables[]            = 'categorylinks';
				$aConditions['cl_to'] = $aCategories;
			} else {
				$aTables[]            = 'categorylinks';
				$aConditions[]        = $wgDBprefix . 'page.page_id = cl_from';
				$aConditions['cl_to'] = $aCategories;
			}
		}

		if ( !empty( $sNs ) || $sNs === '0' ) { // string 0 is empty
			$aNamespaces = BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $sNs );
			if ( !empty( $aNamespaces ) ) {
				$sField = $bAlltime ? 'page_namespace' : 'wo_page_namespace';
				$aConditions[$sField] = $aNamespaces;
			}
		}

		$res = $oDbr->select(
			$aTables,
			$aColumns,
			$aConditions,
			__METHOD__,
			$aOptions,
			$aJoinConditions
		);

		if ( $oDbr->numRows( $res ) > 0 ) {
			$bCategories = false;
			if ( !empty( $aCategories ) ) {
				$bCategories = true;
				$aPrefixedCategories = [];
				foreach ( $aCategories as $sCategory ) {
					$sCategory = str_replace( ' ', '_', $sCategory );
					$sCat = Title::makeTitle( NS_CATEGORY, $sCategory );
					$aPrefixedCategories[] = $sCat->getPrefixedDBKey();
				}
			}

			$aList = [];
			$aInList = [];
			$iCurrCount = 0;
			if ( $bAlltime === false ) {
				foreach ( $res as $row ) {
					if ( $iCurrCount === $iCount ) {
						break;
					}
					if ( empty( $row->wo_page_title ) ) {
						continue;
					}
					$oTitle = Title::makeTitle( $row->wo_page_namespace, $row->wo_page_title );

					if ( !$oTitle->quickUserCan( 'read' ) ) {
						continue;
					}

					if ( $bCategories === true ) {
						$aParents = array_keys( $oTitle->getParentCategories() );
						$aResult  = array_diff( $aPrefixedCategories, $aParents );
						if ( !empty( $aResult ) ) {
							continue;
						}
					}
					if ( in_array( $oTitle->getPrefixedText(), $aInList ) ) {
						continue;
					}
					$aInList[] = $oTitle->getPrefixedText();
					$sLink = Services::getInstance()->getLinkRenderer()->makeLink(
						$oTitle
					);
					$aList['<li>' . $sLink . ' (' . $row->page_counter . ')</li>'] = (int)$row->page_counter;
					$iCurrCount++;
				}
				arsort( $aList );
				$aList = array_keys( $aList );
				array_unshift( $aList, '<ol>' );
			} else {
				$aList[] = '<ol>';
				foreach ( $res as $row ) {
					if ( $iCurrCount == $iCount ) {
						break;
					}
					if ( $row->page_counter == '0' ) {
						continue;
					}

					$oTitle = Title::makeTitle( $row->page_namespace, $row->page_title );
					if ( !$oTitle->quickUserCan( 'read' ) ) {
						continue;
					}

					if ( $bCategories === true ) {
						$aParents = array_keys( $oTitle->getParentCategories() );
						$aResult  = array_diff( $aPrefixedCategories, $aParents );
						if ( !empty( $aResult ) ) {
							continue;
						}
					}

					if ( in_array( $oTitle->getPrefixedText(), $aInList ) ) {
						continue;
					}
					$aInList[] = $oTitle->getPrefixedText();

					$sLink = Services::getInstance()->getLinkRenderer()->makeLink(
						$oTitle
					);
					$aList[] = '<li>' . $sLink . ' (' . $row->page_counter . ')</li>';
					$iCurrCount++;
				}
			}
			$aList[] = '</ol>';

			$oDbr->freeResult( $res );
			return "\n" . implode( "\n", $aList );
		}

		$oDbr->freeResult( $res );
		return wfMessage( 'bs-smartlist-no-entries' )->plain();
	}

	/**
	 * Generates list of most edited pages
	 * @param int $iCount
	 * @param int $iTime
	 * @return String list of pages or empty
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
		if ( $oDbr->numRows( $res ) > 0 ) {
			$aList[] = '<ol>';

			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->rev_page );
				$sLink = Services::getInstance()->getLinkRenderer()->makeLink(
					$oTitle
				);
				$aList[] = '<li>' . $sLink . ' (' . $row->page_counter . ')</li>';
			}

			$aList[] = '</ol>';
		}

		$oDbr->freeResult( $res );
		return implode( "\n", $aList );
	}

	/**
	 * Generates list of most edited pages
	 * @param int $iCount
	 * @param int $iTime
	 * @return String list of pages or empty
	 */
	public function getActivePortlet( $iCount, $iTime ) {
		$oDbr = wfGetDB( DB_REPLICA );
		$iCount = BsCore::sanitize( $iCount, 10, BsPARAMTYPE::INT );
		$iTime = BsCore::sanitize( $iTime, 0, BsPARAMTYPE::INT );

		$aConditions = [];
		if ( $iTime !== 0 ) {
			$this->getTimestampForQuery( $aConditions, $iTime );
		}

		$res = $oDbr->select(
				'revision',
				[
					'COUNT(rev_user) as edit_count',
					'rev_user'
				],
				$aConditions,
				__METHOD__,
				[
					'GROUP BY' => 'rev_user',
					'ORDER BY' => 'edit_count DESC'
				]
		);

		$aList = [];
		if ( $oDbr->numRows( $res ) > 0 ) {
			$aList[] = '<ol>';

			$i = 1;
			foreach ( $res as $row ) {
				if ( $i > $iCount ) {
					break;
				}
				$oUser = User::newFromId( $row->rev_user );
				if ( $oUser->isIP( $oUser->getName() ) ) {
					continue;
				}

				$oTitle = Title::makeTitle( NS_USER, $oUser->getName() );
				$sLink = Services::getInstance()->getLinkRenderer()->makeLink(
					$oTitle
				);
				$aList[] = '<li>' . $sLink . ' (' . $row->edit_count . ')</li>';
				$i++;
			}

			$aList[] = '</ol>';
		}

		$oDbr->freeResult( $res );
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

	public function makeCategoriesFilterCondition( &$aConditions, $aArgs, $sPageIdFileName ) {
		if ( $aArgs['categories'] != '-' && $aArgs['categories'] != '' ) {
			$aCategories = explode( ',', $aArgs['categories'] );
			$iCnt = count( $aCategories );
			for ( $i = 0; $i < $iCnt; $i++ ) {
				$oCategory = Category::newFromName( trim( $aCategories[$i] ) );
				if ( $oCategory === false ) {
					unset( $aCategories[$i] );
					continue;
				}
				$aCategories[$i] = "'" . $oCategory->getName() . "'";
			}
			$aArgs['categories'] = implode( ',', $aCategories );

			$dbr = wfGetDB( DB_REPLICA );
			if ( $aArgs['categoryMode'] == 'OR' ) {
				$aConditions[] = $sPageIdFileName . ' IN ( SELECT cl_from FROM ' . $dbr->tableName( 'categorylinks' ) . ' WHERE cl_to IN (' . $aArgs['categories'] . ') )';
			} else {
				foreach ( $aCategories as $sCategory ) {
					$aConditions[] = $sPageIdFileName . ' IN ( SELECT cl_from FROM ' . $dbr->tableName( 'categorylinks' ) . ' WHERE cl_to = ' . $sCategory . ' )';
				}
			}
		}
	}

	/**
	 * Remove the excluded namespaces from the list of namespaces.
	 *
	 * @param array &$aArgs Arguments of custom list
	 * @return int[]
	 */
	public function makeNamespaceArrayDiff( &$aArgs ) {
		if ( isset( $aArgs['excludens'] ) && $aArgs['excludens'] !== '' ) {
			$aNamespaceDiff = array_diff(
				BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $aArgs['namespaces'] ),
				BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $aArgs['excludens'] )
			);
		} else {
			$aNamespaceDiff = BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $aArgs['namespaces'] );
		}

		return $aNamespaceDiff;
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array &$aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:smartlist'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-smartlist'
			]
		];
		return true;
	}

}

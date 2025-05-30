<?php

namespace BlueSpice\SmartList\Mode;

use BsNamespaceHelper;
use BsPageContentProvider;
use BsStringHelper;
use MediaWiki\Category\Category;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\InputProcessor\Processor\BooleanValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordListValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordValue;

class GenericSmartlistMode extends SmartListBaseMode {

	public const ATTR_MODE_CATEGORY = 'catmode';
	public const ATTR_MODE = 'mode';
	public const ATTR_HEADING = 'heading';
	public const ATTR_TRIM = 'trim';
	public const ATTR_SHOW_TEXT = 'showtext';
	public const ATTR_TRIM_TEXT = 'trimtext';
	public const ATTR_SORT = 'sort';
	public const ATTR_ORDER = 'order';
	public const ATTR_SHOWNS = 'showns';
	public const ATTR_NUM_WITH_TEXT = 'numwithtext';
	public const ATTR_EXCLUDENS = 'excludens';
	public const ATTR_META = 'meta';

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 *
	 */
	public function __construct() {
		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'recentchanges';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getListType(): string {
		return 'ul';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getParams(): array {
		$params = parent::getParams();
		$params[ static::ATTR_MODE_CATEGORY ] = ( new KeywordListValue() )
			->setListSeparator( ',' )
			->setKeywords( [ 'AND', 'OR' ] )
			->setDefaultValue( 'OR' );
		$params[ static::ATTR_MODE ] = [
			'type' => 'smartlist_mode',
			'default' => 'recentchanges'
		];
		$params[ static::ATTR_SHOW_TEXT ] = ( new BooleanValue() )->setDefaultValue( false );
		$params[ static::ATTR_HEADING ] = [
			'type' => 'string',
			'default' => '',
		];
		$params[ static::ATTR_TRIM ] = [
			'type' => 'integer',
			'default' => 30,
			'min' => 0,
		];
		$params[ static::ATTR_TRIM_TEXT ] = [
			'type' => 'integer',
			'default' => 50,
			'min' => 0,
		];
		$params[ static::ATTR_SHOWNS ] = ( new BooleanValue() )->setDefaultValue( false );
		$params[ static::ATTR_SORT ] = ( new KeywordValue() )
			->setKeywords( [ 'time', 'title' ] )
			->setDefaultValue( 'time' );
		$params[ static::ATTR_ORDER ] = ( new KeywordValue() )
			->setKeywords( [ 'ASC', 'DESC' ] )
			->setDefaultValue( 'DESC' );
		$params[ static::ATTR_NUM_WITH_TEXT ] = [
			'type' => 'integer',
			'default' => 100,
			'min' => 0,
		];
		$params[ static::ATTR_EXCLUDENS ] = [
			'type' => 'string',
			'separator' => ',',
		];
		$params[ static::ATTR_META ] = ( new BooleanValue() )->setDefaultValue( false );

		return $params;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getList( $args, $context ): array {
		$args = $this->validateArgs( $args );
		$objectList = $this->getItems( $args, $context );

		if ( isset( $objectList['error'] ) ) {
			return $objectList;
		}

		$list = [];
		if ( count( $objectList ) ) {
			foreach ( $objectList as $row ) {
				$title = Title::makeTitleSafe( $row->namespace, $row->title );

				$text = '';
				$metaInfo = '';
				$prefixedTitle = $title->getPrefixedText();
				$comment = '';

				if ( isset( $args['meta'] ) && $args['meta'] == true ) {
					$metaInfo = $this->getMeta( $row, $context );
				}

				if ( isset( $args['showtext'] ) && $args['showtext' ] == true ) {
					$text = BsPageContentProvider::getInstance()->getContentFromTitle(
						$title
					);
					$text = Sanitizer::stripAllTags( $text );
					$text = BsStringHelper::shorten(
						$text,
						[ 'max-length' => $args['trimtext'], 'position' => 'end' ]
					);
					$text = '<nowiki>' . $text . '</nowiki>';
				}

				$data = [
					'PREFIXEDTITLE' => $prefixedTitle,
					'COMMENT' => $comment,
					'META' => $metaInfo,
					'TEXT' => $text
				];
				$list[] = $data;
			}
		}

		return $list;
	}

	/**
	 * @param array $args
	 * @param RequestContext $context
	 * @return array
	 */
	protected function getItems( $args, $context ): array {
		return [];
	}

	/**
	 * @param array $item
	 * @param RequestContext $context
	 * @return string
	 */
	protected function getMeta( $item, $context ): string {
		return '';
	}

	/**
	 *
	 * @param array $args
	 * @return array
	 */
	protected function validateArgs( $args ): array {
		$args['namespaces'] = $args[self::ATTR_NS];
		$args['categories'] = $args[self::ATTR_CAT];
		$args['categoryMode'] = $args[self::ATTR_MODE_CATEGORY];
		$args['heading'] = $args[self::ATTR_HEADING];
		$args['trim'] = $args[self::ATTR_TRIM];
		$args['showtext'] = $args[self::ATTR_SHOW_TEXT];
		$args['trimtext'] = $args[self::ATTR_TRIM_TEXT];
		$args['sort'] = $args[self::ATTR_SORT];
		$args['order'] = $args[self::ATTR_ORDER];
		$args['showns'] = $args[self::ATTR_SHOWNS];
		$args['numwithtext'] = $args[self::ATTR_NUM_WITH_TEXT];
		$args['excludens'] = $args[self::ATTR_EXCLUDENS];
		$args['meta'] = $args[self::ATTR_META];
		$args['mode'] = $args[self::ATTR_MODE];
		return $args;
	}

	/**
	 *
	 * @param array &$conditions
	 * @param string $pageIdFileName
	 * @param array $args
	 */
	protected function makeCategoriesFilterCondition( &$conditions, $pageIdFileName, $args ) {
		if ( $args['categories'] != '-' && $args['categories'] != '' ) {
			$categories = explode( ',', $args['categories'] );
			$cnt = count( $categories );
			for ( $i = 0; $i < $cnt; $i++ ) {
				$category = Category::newFromName( trim( $categories[$i] ) );
				if ( $category === false ) {
					unset( $categories[$i] );
					continue;
				}
				$categories[$i] = "'" . $category->getName() . "'";
			}
			$args['categories'] = implode( ',', $categories );

			$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
			if ( $args['categoryMode'] == 'OR' ) {
				$conditions[] = $pageIdFileName
					. ' IN ( SELECT cl_from FROM '
					. $dbr->tableName( 'categorylinks' )
					. ' WHERE cl_to IN (' . $args['categories'] . ') )';
			} else {
				foreach ( $categories as $category ) {
					$conditions[] = $pageIdFileName
						. ' IN ( SELECT cl_from FROM '
						. $dbr->tableName( 'categorylinks' )
						. ' WHERE cl_to = ' . $category . ' )';
				}
			}
		}
	}

	/**
	 * Remove the excluded namespaces from the list of namespaces.
	 * @param array $args
	 * @return int[]
	 * @throws \BsInvalidNamespaceException
	 */
	protected function makeNamespaceArrayDiff( $args ) {
		if ( isset( $args['excludens'] ) && $args['excludens'] !== '' ) {
			$namespaceDiff = array_diff(
				BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $args['namespaces'] ),
				BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $args['excludens'] )
			);
		} else {
			$namespaceDiff = BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString(
				$args['namespaces'] ?? ''
			);
		}

		return $namespaceDiff;
	}

}

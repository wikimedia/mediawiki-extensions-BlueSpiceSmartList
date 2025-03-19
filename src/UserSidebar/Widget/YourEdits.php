<?php

namespace BlueSpice\SmartList\UserSidebar\Widget;

use BlueSpice\UserSidebar\IWidget;
use BlueSpice\UserSidebar\Widget;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\ActorNormalization;
use MediaWiki\User\UserIdentity;
use Wikimedia\Rdbms\ILoadBalancer;

class YourEdits extends Widget {
	public const PARAM_COUNT = 'count';

	/**
	 * @var ILoadBalancer
	 */
	private $lb;

	/**
	 * @var ActorNormalization
	 */
	private $actorNormalization;

	/**
	 * @var TitleFactory
	 */
	private $titleFactory;

	/**
	 * @param string $key
	 * @param IContextSource $context
	 * @param Config $config
	 * @param array $params
	 * @return IWidget|static
	 */
	public static function factory(
		string $key, IContextSource $context, Config $config, array $params = []
	) {
		$services = MediaWikiServices::getInstance();
		return new static(
			$key, $context, $config, $params,
			$services->getDBLoadBalancer(), $services->getActorNormalization(), $services->getTitleFactory()
		);
	}

	public function __construct(
		string $key, IContextSource $context, Config $config, array $params,
		ILoadBalancer $lb, ActorNormalization $actorNormalization, TitleFactory $titleFactory
	) {
		parent::__construct( $key, $context, $config, $params );
		$this->lb = $lb;
		$this->actorNormalization = $actorNormalization;
		$this->titleFactory = $titleFactory;
	}

	/**
	 *
	 * @return bool
	 */
	public function shouldRender(): bool {
		return !$this->context->getUser()->isAnon();
	}

	/**
	 *
	 * @return Message
	 */
	public function getHeaderMessage(): Message {
		return $this->context->msg( 'bs-smartlist-lastedits' );
	}

	/**
	 *
	 * @return array
	 */
	public function getLinks(): array {
		if ( $this->context->getUser()->isAnon() ) {
			return [];
		}
		$count = 5;
		if ( isset( $this->params[static::PARAM_COUNT] ) ) {
			$count = (int)$this->params[static::PARAM_COUNT];
		}

		$edits = $this->getYourEditsTitles( $this->context->getUser(), $count );

		$links = [];
		foreach ( $edits as $edit ) {
			$link = [
				'href' => $edit['title']->getLocalURL(),
				'text' => $edit['displayText'],
				'title' => $edit['displayText'],
				'classes' => ' bs-usersidebar-internal '
			];
			$links[] = $link;
		}

		return $links;
	}

	/**
	 * @param UserIdentity $user
	 * @param int $count
	 * @return array
	 */
	private function getYourEditsTitles( UserIdentity $user, int $count ) {
		$dbr = $this->lb->getConnection( DB_REPLICA );
		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'page_id', 'page_namespace', 'page_title' ] )
			->from( 'revision' )
			->join( 'page', 'p', [ 'rev_page = page_id' ] )
			->where( [
				'page_content_model' => [ '', 'wikitext' ],
				'rev_actor' => $this->actorNormalization->findActorId( $user, $dbr )
			] )
			->groupBy( 'page_id' )
			->orderBy( 'MAX(rev_timestamp) DESC' )
			->limit( $count )
			->caller( __METHOD__ )
			->fetchResultSet();

		$edits = [];
		foreach ( $res as $row ) {
			$title = $this->titleFactory->newFromRow( $row );
			$edits[] = [
				'title' => $title,
				'displayText' => $title->getPrefixedText()
			];
		}

		return $edits;
	}
}

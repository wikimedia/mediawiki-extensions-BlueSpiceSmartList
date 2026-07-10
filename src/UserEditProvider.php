<?php

namespace BlueSpice\SmartList;

use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\ActorNormalization;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Utils\UtilityFactory;
use ObjectCacheFactory;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\Rdbms\ILoadBalancer;

class UserEditProvider {

	/** @var BagOStuff */
	private BagOStuff $cache;

	/**
	 * @param ObjectCacheFactory $cacheFactory
	 * @param ActorNormalization $actorNormalization
	 * @param ILoadBalancer $loadBalancer
	 * @param TitleFactory $titleFactory
	 * @param UtilityFactory $utilityFactory
	 */
	public function __construct(
		ObjectCacheFactory $cacheFactory,
		private readonly ActorNormalization $actorNormalization,
		private readonly ILoadBalancer $loadBalancer,
		private readonly TitleFactory $titleFactory,
		private readonly UtilityFactory $utilityFactory
	) {
		$this->cache = $cacheFactory->getLocalServerInstance();
	}

	/**
	 * @param UserIdentity $user
	 * @param int|null $count
	 * @return array
	 */
	public function getUserEdits( UserIdentity $user, ?int $count = null ): array {
		$cacheKey = $this->getCacheKey( $user );
		$edits = $this->cache->get( $cacheKey );
		if ( $edits === false ) {
			$edits = $this->updateUserEdits( $user );
		}
		if ( $count !== null ) {
			$edits = array_slice( $edits, 0, $count );
		}
		return $edits;
	}

	/**
	 * @param UserIdentity $user
	 * @return array
	 */
	public function updateUserEdits( UserIdentity $user ): array {
		$edits = $this->getEdits( $user );
		$this->cache->set( $this->getCacheKey( $user ), $edits, ExpirationAwareness::TTL_WEEK );
		return $edits;
	}

	/**
	 * @param Title $title
	 * @param UserIdentity $user
	 * @return void
	 */
	public function pushEdit( Title $title, UserIdentity $user ): void {
		$cacheKey = $this->getCacheKey( $user );
		$edits = $this->cache->get( $cacheKey );
		if ( !is_array( $edits ) ) {
			// Not cached, so no point in updating it now
			return;
		}
		// Push new edit to the beginning of the list and remove the last one if there are more than 50
		array_unshift( $edits, [
			'title' => $title,
			'displayText' => $this->utilityFactory->getDisplayTitleHelper()->getDisplayTitle( $title )
				?? $title->getPrefixedText(),
		] );
		if ( count( $edits ) > 50 ) {
			array_pop( $edits );
		}
		$this->cache->set( $cacheKey, $edits, ExpirationAwareness::TTL_WEEK );
	}

	/**
	 * @param UserIdentity $user
	 * @return string
	 */
	private function getCacheKey( UserIdentity $user ) {
		return $this->cache->makeKey( 'bs-smartlist', 'user-edits', $user->getId() );
	}

	/**
	 * @param UserIdentity $user
	 * @return array
	 */
	private function getEdits( UserIdentity $user ): array {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$actorId = $this->actorNormalization->findActorId( $user, $dbr );
		if ( !$actorId ) {
			return [];
		}
		$displayTitleHelper = $this->utilityFactory->getDisplayTitleHelper();
		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'page_id', 'page_namespace', 'page_title' ] )
			->from( 'revision' )
			->join( 'page', 'p', [ 'rev_page = page_id' ] )
			->where( [
				'page_content_model' => [ '', 'wikitext' ],
				'rev_actor' => $actorId
			] )
			->groupBy( 'page_id' )
			->orderBy( 'MAX(rev_timestamp) DESC' )
			->limit( 50 )
			->caller( __METHOD__ )
			->fetchResultSet();

		$edits = [];
		foreach ( $res as $row ) {
			$title = $this->titleFactory->newFromRow( $row );
			$edits[] = [
				'title' => $title,
				'displayText' => $displayTitleHelper->getDisplayTitle( $title ) ?? $title->getPrefixedText(),
			];
		}

		return $edits;
	}
}

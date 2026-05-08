<?php

namespace BlueSpice\SmartList\Hook;

use BlueSpice\SmartList\UserEditProvider;
use MediaWiki\Page\Hook\RevisionFromEditCompleteHook;

class UpdateUserEdits implements RevisionFromEditCompleteHook {

	/**
	 * @param UserEditProvider $userEditProvider
	 */
	public function __construct(
		private readonly UserEditProvider $userEditProvider
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onRevisionFromEditComplete( $wikiPage, $rev, $originalRevId, $user, &$tags ) {
		if ( !$user->isRegistered() ) {
			return;
		}
		$this->userEditProvider->pushEdit( $wikiPage->getTitle(), $user );
	}
}

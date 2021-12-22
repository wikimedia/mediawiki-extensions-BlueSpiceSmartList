<?php

namespace BlueSpice\SmartList\UserSidebar\Widget;

use BlueSpice\UserSidebar\Widget;
use Message;

class YourEdits extends Widget {
	public const PARAM_COUNT = 'count';

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

		$edits = \SmartList::getYourEditsTitles( $this->context->getUser(), $count );

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

}

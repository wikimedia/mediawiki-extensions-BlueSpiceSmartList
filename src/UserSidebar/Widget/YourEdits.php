<?php

namespace BlueSpice\SmartList\UserSidebar\Widget;

use BlueSpice\SmartList\UserEditProvider;
use BlueSpice\UserSidebar\IWidget;
use BlueSpice\UserSidebar\Widget;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\ActorNormalization;
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
	 * @var UserEditProvider
	 */
	private $userEditProvider;

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
			$services->getDBLoadBalancer(), $services->getActorNormalization(), $services->getTitleFactory(),
			$services->getService( 'BlueSpiceSmartList.UserEditProvider' )
		);
	}

	/**
	 * @param string $key
	 * @param IContextSource $context
	 * @param Config $config
	 * @param array $params
	 * @param ILoadBalancer $lb
	 * @param ActorNormalization $actorNormalization
	 * @param TitleFactory $titleFactory
	 * @param UserEditProvider $userEditProvider
	 */
	public function __construct(
		string $key, IContextSource $context, Config $config, array $params,
		ILoadBalancer $lb, ActorNormalization $actorNormalization,
		TitleFactory $titleFactory, UserEditProvider $userEditProvider
	) {
		parent::__construct( $key, $context, $config, $params );
		$this->lb = $lb;
		$this->actorNormalization = $actorNormalization;
		$this->titleFactory = $titleFactory;
		$this->userEditProvider = $userEditProvider;
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

		$edits = $this->userEditProvider->getUserEdits( $this->context->getUser(), $count );

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

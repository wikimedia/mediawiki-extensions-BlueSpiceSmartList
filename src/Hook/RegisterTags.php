<?php

namespace BlueSpice\SmartList\Hook;

use BlueSpice\SmartList\BlueSpiceSmartListModeFactory;
use BlueSpice\SmartList\InputProcessor\SmartlistModeValue;
use BlueSpice\SmartList\Tag\Newbies;
use BlueSpice\SmartList\Tag\RecentChanges;
use BlueSpice\SmartList\Tag\Smartlist;
use BlueSpice\SmartList\Tag\Toplist;
use BlueSpice\SmartList\Tag\Userlist;
use BlueSpice\SmartList\Tag\WhatLinksHere;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;
use MWStake\MediaWiki\Component\InputProcessor\MWStakeInputProcessorRegisterProcessorsHook;

class RegisterTags implements MWStakeGenericTagHandlerInitTagsHook, MWStakeInputProcessorRegisterProcessorsHook {

	/**
	 * @param BlueSpiceSmartListModeFactory $modeFactory
	 */
	public function __construct(
		private readonly BlueSpiceSmartListModeFactory $modeFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new Newbies( $this->modeFactory->createMode( 'newbies' ) );
		$tags[] = new RecentChanges( $this->modeFactory->createMode( 'recentchanges' ) );
		$tags[] = new Smartlist( $this->modeFactory );
		$tags[] = new Toplist( $this->modeFactory->createMode( 'toplist' ) );
		$tags[] = new Userlist( $this->modeFactory->createMode( 'userlist' ) );
		$tags[] = new WhatLinksHere( $this->modeFactory->createMode( 'whatlinkshere' ) );
	}

	/**
	 * @param array &$registry
	 * @return void
	 */
	public function onMWStakeInputProcessorRegisterProcessors( &$registry ): void {
		$registry['smartlist_mode'] = [
			'class' => SmartlistModeValue::class,
			'services' => [ 'BlueSpiceSmartList.SmartlistMode' ]
		];
	}
}

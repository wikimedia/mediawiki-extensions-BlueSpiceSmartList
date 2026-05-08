<?php

use BlueSpice\SmartList\BlueSpiceSmartListModeFactory;
use BlueSpice\SmartList\UserEditProvider;
use MediaWiki\MediaWikiServices;

return [
	'BlueSpiceSmartList.SmartlistMode' => static function ( MediaWikiServices $services ) {
		$objectFactory = $services->getObjectFactory();
		return new BlueSpiceSmartListModeFactory(
			$objectFactory
		);
	},
	'BlueSpiceSmartList.UserEditProvider' => static function ( MediaWikiServices $services ) {
		return new UserEditProvider(
			$services->getObjectCacheFactory(),
			$services->getActorNormalization(),
			$services->getDBLoadBalancer(),
			$services->getTitleFactory(),
			$services->getService( 'MWStakeCommonUtilsFactory' )
		);
	},
];

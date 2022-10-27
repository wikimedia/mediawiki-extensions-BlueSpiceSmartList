<?php

use BlueSpice\SmartList\BlueSpiceSmartListModeFactory;
use MediaWiki\MediaWikiServices;

return [
	'BlueSpiceSmartList.SmartlistMode' => static function ( MediaWikiServices $services ) {
		$objectFactory = $services->getObjectFactory();
		return new BlueSpiceSmartListModeFactory(
			$objectFactory
		);
	}
];

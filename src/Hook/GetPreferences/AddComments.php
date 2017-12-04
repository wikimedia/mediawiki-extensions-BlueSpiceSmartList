<?php

namespace BlueSpice\SmartList\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddComments extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-smartlist-pref-comments'] = array(
			'type' => 'check',
			'label-message' => 'bs-smartlist-pref-comments',
			'section' => 'bluespice/smartlist',
		);
		return true;
	}
}

<?php

namespace BlueSpice\SmartList\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddCount extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-smartlist-pref-count'] = array(
			'type' => 'int',
			'label-message' => 'bs-smartlist-pref-count',
			'section' => 'bluespice/smartlist',
		);
		return true;
	}
}

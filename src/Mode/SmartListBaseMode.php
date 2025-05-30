<?php

namespace BlueSpice\SmartList\Mode;

abstract class SmartListBaseMode extends BaseMode {

	public const ATTR_CAT = 'cat';
	public const ATTR_NS = 'ns';

	/**
	 *
	 * @inheritDoc
	 */
	public function getParams(): array {
		$params = parent::getParams();
		$params[ static::ATTR_NS ] = [
			'type' => 'string',
			'required' => false,
		];
		$params[ static::ATTR_CAT ] = [
			'type' => 'category',
			'required' => false,
		];
		return $params;
	}

}

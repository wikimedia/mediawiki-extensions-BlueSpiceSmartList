<?php

namespace BlueSpice\SmartList\Mode;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;

abstract class BaseMode implements IMode {

	public const ATTR_COUNT = 'count';

	/**
	 *
	 * @inheritDoc
	 */
	public function getParams(): array {
		return [
			new ParamDefinition(
				ParamType::INTEGER,
				static::ATTR_COUNT,
				5
			)
		];
	}
}

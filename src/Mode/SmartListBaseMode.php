<?php

namespace BlueSpice\SmartList\Mode;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;

abstract class SmartListBaseMode extends BaseMode {

	public const ATTR_CAT = 'cat';
	public const ATTR_NS = 'ns';

	/**
	 *
	 * @inheritDoc
	 */
	public function getParams(): array {
		$parentParams = parent::getParams();
		return array_merge( $parentParams, [
			new ParamDefinition(
				ParamType::STRING,
				static::ATTR_NS,
				''
			),
			new ParamDefinition(
				ParamType::STRING,
				static::ATTR_CAT,
				'-'
			)
		] );
	}

}

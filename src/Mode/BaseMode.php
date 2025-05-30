<?php

namespace BlueSpice\SmartList\Mode;

use MWStake\MediaWiki\Component\InputProcessor\Processor\IntValue;

abstract class BaseMode implements IMode {

	public const ATTR_COUNT = 'count';

	/**
	 *
	 * @inheritDoc
	 */
	public function getParams(): array {
		$count = ( new IntValue() )
			->setMin( 1 )
			->setDefaultValue( 5 );
		return [ 'count' => $count ];
	}
}

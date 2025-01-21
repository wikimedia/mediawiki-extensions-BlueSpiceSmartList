<?php

namespace BlueSpice\SmartList\Mode;

use MediaWiki\Context\RequestContext;

interface IMode {

	/**
	 * @return string
	 */
	public function getKey(): string;

	/**
	 * @param array $args
	 * @param RequestContext $context
	 * @return array
	 */
	public function getList( $args, $context ): array;

	/**
	 *
	 * @return IParamDefinition[]
	 */
	public function getParams(): array;

	/**
	 *
	 * @return string
	 */
	public function getListType(): string;

}

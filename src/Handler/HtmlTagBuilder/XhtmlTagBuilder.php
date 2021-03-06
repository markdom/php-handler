<?php

declare(strict_types=1);

namespace Markdom\Handler\HtmlTagBuilder;

use Markdom\Handler\Exception\HandlerException;

/**
 * Class XhtmlTagBuilder
 *
 * @package Markdom\Handler\HtmlTagBuilder
 */
class XhtmlTagBuilder extends HtmlTagBuilder
{

	/**
	 * @param string $type
	 * @param string $value
	 * @param array $attributes
	 * @param mixed $variant
	 * @return string
	 * @throws HandlerException
	 */
	public function buildTag(
		string $type,
		?string $value = null,
		?array $attributes = array(),
		$variant = null
	): string {
		switch ($type) {
			case self::TYPE_DIVISION:
				return '<hr' . $this->getAttributeString($attributes) . ' />';
			case self::TYPE_IMAGE:
				return '<img' . $this->getAttributeString($attributes) . ' />';
			case self::TYPE_LINE_BREAK:
				return '<br />';
			default:
				return parent::buildTag($type, $value, $attributes, $variant);
		}
	}

}

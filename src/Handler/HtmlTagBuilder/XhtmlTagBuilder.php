<?php

namespace Markdom\Handler\HtmlTagBuilder;

use Markdom\Handler\Exception\HandlerException;

/**
 * Class XhtmlTagBuilder.
 *
 * @package Markdom\Handler\HtmlTagBuilder
 */
class XhtmlTagBuilder extends HtmlTagBuilder
{
    /**
     * @param string $type
     * @param string $value
     * @param array  $attributes
     * @param string $variant
     *
     * @throws HandlerException
     *
     * @return string
     */
    public function buildTag($type, $value = null, array $attributes = [], $variant = null)
    {
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

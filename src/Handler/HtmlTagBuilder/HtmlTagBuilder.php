<?php

namespace Markdom\Handler\HtmlTagBuilder;

use Markdom\Handler\Exception\HandlerException;

/**
 * Class HtmlTagBuilder
 *
 * @package Markdom\Handler\HtmlTagBuilder
 */
class HtmlTagBuilder implements TagBuilderInterface
{

	/**
	 * @param string $type
	 * @param string $value
	 * @param array $attributes
	 * @param string $variant
	 * @return string
	 * @throws HandlerException
	 */
	public function buildTag($type, $value = null, array $attributes = array(), $variant = null)
	{
		switch ($type) {
			case self::TYPE_CODE_BLOCK:
				return '<pre><code' . $this->getAttributeString($attributes) . '>' . $value . '</code></pre>';
			case self::TYPE_CODE_INLINE:
				return '<code' . $this->getAttributeString($attributes) . '>' . $value . '</code>';
			case self::TYPE_COMMENT:
				return '<!-- ' . $value . ' -->';
			case self::TYPE_DIVISION:
				return '<hr' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_HEADING_BEGIN:
				switch ($variant) {
					case self::VARIANT_HEADING_2:
						return '<h2' . $this->getAttributeString($attributes) . '>';
					case self::VARIANT_HEADING_3:
						return '<h3' . $this->getAttributeString($attributes) . '>';
					case self::VARIANT_HEADING_4:
						return '<h4' . $this->getAttributeString($attributes) . '>';
					case self::VARIANT_HEADING_5:
						return '<h5' . $this->getAttributeString($attributes) . '>';
					case self::VARIANT_HEADING_6:
						return '<h6' . $this->getAttributeString($attributes) . '>';
					case self::VARIANT_HEADING_1:
					default:
						return '<h1' . $this->getAttributeString($attributes) . '>';
				}
			case self::TYPE_HEADING_END:
				switch ($variant) {
					case self::VARIANT_HEADING_2:
						return '</h2>';
					case self::VARIANT_HEADING_3:
						return '</h3>';
					case self::VARIANT_HEADING_4:
						return '</h4>';
					case self::VARIANT_HEADING_5:
						return '</h5>';
					case self::VARIANT_HEADING_6:
						return '</h6>';
					case self::VARIANT_HEADING_1:
					default:
						return '</h1>';
				}
			case self::TYPE_UNORDERED_LIST_BEGIN:
				return '<ul' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_UNORDERED_LIST_END:
				return '</ul>';
			case self::TYPE_ORDERED_LIST_BEGIN:
				return '<ol' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_ORDERED_LIST_END:
				return '</ol>';
			case self::TYPE_LIST_ITEM_BEGIN:
				return '<li' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_LIST_ITEM_END:
				return '</li>';
			case self::TYPE_PARAGRAPH_BEGIN:
				return '<p' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_PARAGRAPH_END:
				return '</p>';
			case self::TYPE_QUOTE_BEGIN:
				return '<blockquote' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_QUOTE_END:
				return '</blockquote>';
			case self::TYPE_EMPHASIS_LEVEL_1_BEGIN:
				return '<em' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_EMPHASIS_LEVEL_1_END:
				return '</em>';
			case self::TYPE_EMPHASIS_LEVEL_2_BEGIN:
				return '<strong' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_EMPHASIS_LEVEL_2_END:
				return '</strong>';
			case self::TYPE_IMAGE:
				return '<img' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_LINE_BREAK:
				return '<br>';
			case self::TYPE_LINK_BEGIN:
				return '<a' . $this->getAttributeString($attributes) . '>';
			case self::TYPE_LINK_END:
				return '</a>';
		}
		throw new HandlerException('Invalid tagname type');
	}

	/**
	 * @param array $attributes
	 * @return string
	 */
	protected function getAttributeString(array $attributes)
	{
		$attributeParts = array();
		foreach ($attributes as $key => &$value) {
			if (is_null($value)) {
				continue;
			}
			$value = trim($value);
			if (empty($value)) {
				continue;
			}
			$attributeParts[] = mb_strtolower($key) . '="' . $value . '"';
		}
		if (empty($attributeParts)) {
			return '';
		}
		return ' ' . implode(' ', $attributeParts);
	}

}

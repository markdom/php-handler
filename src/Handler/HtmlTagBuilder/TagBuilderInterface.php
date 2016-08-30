<?php

namespace Markdom\Handler\HtmlTagBuilder;

/**
 * Interface TagBuilderInterface
 *
 * @package Markdom\Handler\HtmlTagBuilder
 */
interface TagBuilderInterface
{

	const TYPE_CODE_BLOCK = 'CODE_BLOCK';
	const TYPE_CODE_INLINE = 'CODE_INLINE';
	const TYPE_COMMENT = 'COMMENT';
	const TYPE_DIVISION = 'DIVISION';
	const TYPE_HEADING_BEGIN = 'HEADING_BEGIN';
	const TYPE_HEADING_END = 'HEADING_END';
	const TYPE_UNORDERED_LIST_BEGIN = 'UNORDERED_LIST_BEGIN';
	const TYPE_UNORDERED_LIST_END = 'UNORDERED_LIST_END';
	const TYPE_ORDERED_LIST_BEGIN = 'ORDERED_LIST_BEGIN';
	const TYPE_ORDERED_LIST_END = 'ORDERED_LIST_END';
	const TYPE_LIST_ITEM_BEGIN = 'LIST_ITEM_BEGIN';
	const TYPE_LIST_ITEM_END = 'LIST_ITEM_END';
	const TYPE_PARAGRAPH_BEGIN = 'PARAGRAPH_BEGIN';
	const TYPE_PARAGRAPH_END = 'PARAGRAPH_END';
	const TYPE_QUOTE_BEGIN = 'QUOTE_BEGIN';
	const TYPE_QUOTE_END = 'QUOTE_END';
	const TYPE_EMPHASIS_LEVEL_1_BEGIN = 'EMPHASIS_LEVEL_1_BEGIN';
	const TYPE_EMPHASIS_LEVEL_1_END = 'EMPHASIS_LEVEL_1_END';
	const TYPE_EMPHASIS_LEVEL_2_BEGIN = 'EMPHASIS_LEVEL_2_BEGIN';
	const TYPE_EMPHASIS_LEVEL_2_END = 'EMPHASIS_LEVEL_2_END';
	const TYPE_IMAGE = 'IMAGE';
	const TYPE_LINE_BREAK = 'LINE_BREAK';
	const TYPE_LINK_BEGIN = 'LINK_BEGIN';
	const TYPE_LINK_END = 'LINK_END';

	/**
	 * @param string $type
	 * @param string $value
	 * @param array $attributes
	 * @param string $variant
	 * @return string
	 */
	public function buildTag($type, $value = null, array $attributes = array(), $variant = null);

}

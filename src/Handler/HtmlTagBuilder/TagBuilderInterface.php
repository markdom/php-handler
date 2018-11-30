<?php

namespace Markdom\Handler\HtmlTagBuilder;

/**
 * Interface TagBuilderInterface
 *
 * @package Markdom\Handler\HtmlTagBuilder
 */
interface TagBuilderInterface
{

	public const TYPE_CODE_BLOCK = 'CODE_BLOCK';
	public const TYPE_CODE_INLINE = 'CODE_INLINE';
	public const TYPE_COMMENT = 'COMMENT';
	public const TYPE_DIVISION = 'DIVISION';
	public const TYPE_HEADING_BEGIN = 'HEADING_BEGIN';
	public const TYPE_HEADING_END = 'HEADING_END';
	public const TYPE_UNORDERED_LIST_BEGIN = 'UNORDERED_LIST_BEGIN';
	public const TYPE_UNORDERED_LIST_END = 'UNORDERED_LIST_END';
	public const TYPE_ORDERED_LIST_BEGIN = 'ORDERED_LIST_BEGIN';
	public const TYPE_ORDERED_LIST_END = 'ORDERED_LIST_END';
	public const TYPE_LIST_ITEM_BEGIN = 'LIST_ITEM_BEGIN';
	public const TYPE_LIST_ITEM_END = 'LIST_ITEM_END';
	public const TYPE_PARAGRAPH_BEGIN = 'PARAGRAPH_BEGIN';
	public const TYPE_PARAGRAPH_END = 'PARAGRAPH_END';
	public const TYPE_QUOTE_BEGIN = 'QUOTE_BEGIN';
	public const TYPE_QUOTE_END = 'QUOTE_END';
	public const TYPE_EMPHASIS_LEVEL_1_BEGIN = 'EMPHASIS_LEVEL_1_BEGIN';
	public const TYPE_EMPHASIS_LEVEL_1_END = 'EMPHASIS_LEVEL_1_END';
	public const TYPE_EMPHASIS_LEVEL_2_BEGIN = 'EMPHASIS_LEVEL_2_BEGIN';
	public const TYPE_EMPHASIS_LEVEL_2_END = 'EMPHASIS_LEVEL_2_END';
	public const TYPE_IMAGE = 'IMAGE';
	public const TYPE_LINE_BREAK = 'LINE_BREAK';
	public const TYPE_LINK_BEGIN = 'LINK_BEGIN';
	public const TYPE_LINK_END = 'LINK_END';

	/**
	 * @param string $type
	 * @param string $value
	 * @param array $attributes
	 * @param string $variant
	 * @return string
	 */
	public function buildTag(string $type, ?string $value = null, ?array $attributes = array(), ?string $variant = null): string;

}

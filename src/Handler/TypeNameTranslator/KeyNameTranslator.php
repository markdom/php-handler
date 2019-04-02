<?php

declare(strict_types=1);

namespace Markdom\Handler\TypeNameTranslator;

/**
 * Class KeyNameTranslator
 *
 * @package Markdom\Handler\TypeNameTranslator
 */
final class KeyNameTranslator
{

	public const TYPE_DOCUMENT = 'Document';
	public const TYPE_CODE = 'Code';
	public const TYPE_COMMENT = 'Comment';
	public const TYPE_DIVISION = 'Division';
	public const TYPE_HEADING = 'Heading';
	public const TYPE_UNORDERED_LIST = 'UnorderedList';
	public const TYPE_ORDERED_LIST = 'OrderedList';
	public const TYPE_LIST_ITEM = 'ListItem';
	public const TYPE_PARAGRAPH = 'Paragraph';
	public const TYPE_QUOTE = 'Quote';
	public const TYPE_EMPHASIS = 'Emphasis';
	public const TYPE_IMAGE = 'Image';
	public const TYPE_LINE_BREAK = 'LineBreak';
	public const TYPE_LINK = 'Link';
	public const TYPE_TEXT = 'Text';

	public const ATTRIBUTE_COMMON_TYPE = 'type';
	public const ATTRIBUTE_COMMON_BLOCKS = 'blocks';
	public const ATTRIBUTE_COMMON_CONTENTS = 'contents';
	public const ATTRIBUTE_COMMON_LIST_ITEMS = 'items';

	public const ATTRIBUTE_DOCUMENT_VERSION = 'version';
	public const ATTRIBUTE_DOCUMENT_VERSION_MAJOR = 'major';
	public const ATTRIBUTE_DOCUMENT_VERSION_MINOR = 'minor';
	public const ATTRIBUTE_CODE_CODE = 'code';
	public const ATTRIBUTE_CODE_HINT = 'hint';
	public const ATTRIBUTE_COMMENT_COMMENT = 'comment';
	public const ATTRIBUTE_HEADING_LEVEL = 'level';
	public const ATTRIBUTE_ORDERED_LIST_START_INDEX = 'startIndex';
	public const ATTRIBUTE_IMAGE_URI = 'uri';
	public const ATTRIBUTE_IMAGE_TITLE = 'title';
	public const ATTRIBUTE_IMAGE_ALTERNATIVE = 'alternative';
	public const ATTRIBUTE_EMPHASIS_LEVEL = 'level';
	public const ATTRIBUTE_LINE_BREAK_HARD = 'hard';
	public const ATTRIBUTE_LINK_URI = 'uri';
	public const ATTRIBUTE_LINK_TITLE = 'title';
	public const ATTRIBUTE_TEXT_TEXT = 'text';

}

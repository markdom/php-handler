<?php

namespace Markdom\Handler\TypeNameTranslator;

/**
 * Class KeyNameTranslator
 *
 * @package Markdom\Handler\TypeNameTranslator
 */
final class KeyNameTranslator
{

	const TYPE_DOCUMENT = 'Document';
	const TYPE_CODE = 'Code';
	const TYPE_DIVISION = 'Division';
	const TYPE_HEADING = 'Heading';
	const TYPE_UNORDERED_LIST = 'UnorderedList';
	const TYPE_ORDERED_LIST = 'OrderedList';
	const TYPE_LIST_ITEM = 'ListItem';
	const TYPE_PARAGRAPH = 'Paragraph';
	const TYPE_QUOTE = 'Quote';
	const TYPE_EMPHASIS = 'Emphasis';
	const TYPE_IMAGE = 'Image';
	const TYPE_LINE_BREAK = 'LineBreak';
	const TYPE_LINK = 'Link';
	const TYPE_TEXT = 'Text';

	const ATTRIBUTE_COMMON_TYPE = 'type';
	const ATTRIBUTE_COMMON_BLOCKS = 'blocks';
	const ATTRIBUTE_COMMON_CONTENTS = 'contents';
	const ATTRIBUTE_COMMON_LIST_ITEMS = 'items';

	const ATTRIBUTE_DOCUMENT_VERSION = 'version';
	const ATTRIBUTE_DOCUMENT_VERSION_MAJOR = 'major';
	const ATTRIBUTE_DOCUMENT_VERSION_MINOR = 'minor';
	const ATTRIBUTE_CODE_CODE = 'code';
	const ATTRIBUTE_CODE_HINT = 'hint';
	const ATTRIBUTE_HEADING_LEVEL = 'level';
	const ATTRIBUTE_ORDERED_LIST_START_INDEX = 'startIndex';
	const ATTRIBUTE_IMAGE_URI = 'uri';
	const ATTRIBUTE_IMAGE_TITLE = 'title';
	const ATTRIBUTE_IMAGE_ALTERNATIVE = 'alternative';
	const ATTRIBUTE_EMPHASIS_LEVEL = 'level';
	const ATTRIBUTE_LINE_BREAK_HARD = 'hard';
	const ATTRIBUTE_LINK_URI = 'uri';
	const ATTRIBUTE_TEXT_TEXT = 'text';

}

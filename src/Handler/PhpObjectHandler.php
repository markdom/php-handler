<?php

namespace Markdom\Handler;

use Markdom\Handler\TypeNameTranslator\KeyNameTranslator;
use Markdom\HandlerInterface\HandlerInterface;
use Markenwerk\StackUtil\Stack;

/**
 * Class PhpObjectHandler
 *
 * @package Markdom\Handler
 */
class PhpObjectHandler implements HandlerInterface
{

	/**
	 * @var \StdClass
	 */
	private $document;

	/**
	 * @var Stack
	 */
	private $listBlocks;

	/**
	 * @var Stack
	 */
	private $blockParents;

	/**
	 * @var Stack
	 */
	private $contentParents;

	/**
	 * @return void
	 */
	public function onDocumentBegin()
	{
		$this->listBlocks = new Stack();
		$this->blockParents = new Stack();
		$this->contentParents = new Stack();
		$this->document = (object)array(
			KeyNameTranslator::ATTRIBUTE_DOCUMENT_VERSION => (object)array(
				KeyNameTranslator::ATTRIBUTE_DOCUMENT_VERSION_MAJOR => 1,
				KeyNameTranslator::ATTRIBUTE_DOCUMENT_VERSION_MINOR => 0,
			),
			KeyNameTranslator::ATTRIBUTE_COMMON_BLOCKS => array(),
		);
		$this->blockParents->push($this->document);
	}

	/**
	 * @return void
	 */
	public function onDocumentEnd()
	{
		$this->blockParents->pop();
	}

	/**
	 * @return void
	 */
	public function onBlocksBegin()
	{
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockBegin($type)
	{
	}

	/**
	 * @param string $code
	 * @param string $hint
	 * @return void
	 */
	public function onCodeBlock($code, $hint = null)
	{
		$parent = $this->blockParents->get();
		$parent->blocks[] = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_CODE,
			KeyNameTranslator::ATTRIBUTE_CODE_CODE => $code,
			KeyNameTranslator::ATTRIBUTE_CODE_HINT => $hint,
		);
	}

	/**
	 * @param string $comment
	 * @return void
	 */
	public function onCommentBlock($comment)
	{
		$parent = $this->blockParents->get();
		$parent->blocks[] = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_COMMENT,
			KeyNameTranslator::ATTRIBUTE_COMMENT_COMMENT => $comment,
		);
	}

	/**
	 * @return void
	 */
	public function onDivisionBlock()
	{
		$parent = $this->blockParents->get();
		$parent->blocks[] = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_DIVISION,
		);
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockBegin($level)
	{
		$heading = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_HEADING,
			KeyNameTranslator::ATTRIBUTE_HEADING_LEVEL => $level,
			KeyNameTranslator::ATTRIBUTE_COMMON_CONTENTS => array(),
		);
		$parent = $this->blockParents->get();
		$parent->blocks[] = $heading;
		$this->contentParents->push($heading);
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockEnd($level)
	{
		$this->contentParents->pop();
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockBegin()
	{
		$list = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_UNORDERED_LIST,
			KeyNameTranslator::ATTRIBUTE_COMMON_LIST_ITEMS => array(),
		);
		$parent = $this->blockParents->get();
		$parent->blocks[] = $list;
		$this->listBlocks->push($list);
	}

	/**
	 * @param int $startIndex
	 * @return void
	 */
	public function onOrderedListBlockBegin($startIndex)
	{
		$list = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_ORDERED_LIST,
			KeyNameTranslator::ATTRIBUTE_ORDERED_LIST_START_INDEX => $startIndex,
			KeyNameTranslator::ATTRIBUTE_COMMON_LIST_ITEMS => array(),
		);
		$parent = $this->blockParents->get();
		$parent->blocks[] = $list;
		$this->listBlocks->push($list);
	}

	/**
	 * @return void
	 */
	public function onListItemsBegin()
	{
	}

	/**
	 * @return void
	 */
	public function onListItemBegin()
	{
		$listItem = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_BLOCKS => array(),
		);
		$parent = $this->listBlocks->get();
		$parent->items[] = $listItem;
		$this->blockParents->push($listItem);
	}

	/**
	 * @return void
	 */
	public function onListItemEnd()
	{
		$this->blockParents->pop();
	}

	/**
	 * @return void
	 */
	public function onNextListItem()
	{
	}

	/**
	 * @return void
	 */
	public function onListItemsEnd()
	{
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockEnd()
	{
		$this->listBlocks->pop();
	}

	/**
	 * @param int
	 * @return void
	 */
	public function onOrderedListBlockEnd($startIndex)
	{
		$this->listBlocks->pop();
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockBegin()
	{
		$paragraph = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_PARAGRAPH,
			KeyNameTranslator::ATTRIBUTE_COMMON_CONTENTS => array(),
		);
		$parent = $this->blockParents->get();
		$parent->blocks[] = $paragraph;
		$this->contentParents->push($paragraph);
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockEnd()
	{
		$this->contentParents->pop();
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockBegin()
	{
		$quote = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_QUOTE,
			KeyNameTranslator::ATTRIBUTE_COMMON_BLOCKS => array(),
		);
		$parent = $this->blockParents->get();
		$parent->blocks[] = $quote;
		$this->blockParents->push($quote);
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockEnd()
	{
		$this->blockParents->pop();
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockEnd($type)
	{
	}

	/**
	 * @return void
	 */
	public function onNextBlock()
	{
	}

	/**
	 * @return void
	 */
	public function onBlocksEnd()
	{
	}

	/**
	 * @return void
	 */
	public function onContentsBegin()
	{
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onContentBegin($type)
	{
	}

	/**
	 * @param string $code
	 * @return void
	 */
	public function onCodeContent($code)
	{
		$code = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_CODE,
			KeyNameTranslator::ATTRIBUTE_CODE_CODE => $code,
		);
		$parent = $this->contentParents->get();
		$parent->contents[] = $code;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentBegin($level)
	{
		$emphasis = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_EMPHASIS,
			KeyNameTranslator::ATTRIBUTE_EMPHASIS_LEVEL => $level,
			KeyNameTranslator::ATTRIBUTE_COMMON_CONTENTS => array(),
		);
		$parent = $this->contentParents->get();
		$parent->contents[] = $emphasis;
		$this->contentParents->push($emphasis);
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentEnd($level)
	{
		$this->contentParents->pop();
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @param string $alternative
	 * @return void
	 */
	public function onImageContent($uri, $title = null, $alternative = null)
	{
		$image = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_IMAGE,
			KeyNameTranslator::ATTRIBUTE_IMAGE_URI => $uri,
			KeyNameTranslator::ATTRIBUTE_IMAGE_TITLE => $title,
			KeyNameTranslator::ATTRIBUTE_IMAGE_ALTERNATIVE => $alternative,
		);
		$parent = $this->contentParents->get();
		$parent->contents[] = $image;
	}

	/**
	 * @param bool $hard
	 * @return void
	 */
	public function onLineBreakContent($hard)
	{
		$linebreak = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_LINE_BREAK,
			KeyNameTranslator::ATTRIBUTE_LINE_BREAK_HARD => $hard,
		);
		$parent = $this->contentParents->get();
		$parent->contents[] = $linebreak;
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentBegin($uri, $title = null)
	{
		$link = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_LINK,
			KeyNameTranslator::ATTRIBUTE_LINK_URI => $uri,
			KeyNameTranslator::ATTRIBUTE_LINK_TITLE => $title,
			KeyNameTranslator::ATTRIBUTE_COMMON_CONTENTS => array(),
		);
		$parent = $this->contentParents->get();
		$parent->contents[] = $link;
		$this->contentParents->push($link);
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentEnd($uri, $title = null)
	{
		$this->contentParents->pop();
	}

	/**
	 * @param string $text
	 * @return void
	 */
	public function onTextContent($text)
	{
		$text = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_TEXT,
			KeyNameTranslator::ATTRIBUTE_TEXT_TEXT => $text,
		);
		$parent = $this->contentParents->get();
		$parent->contents[] = $text;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onContentEnd($type)
	{
	}

	/**
	 * @return void
	 */
	public function onNextContent()
	{
	}

	/**
	 * @return void
	 */
	public function onContentsEnd()
	{
	}

	/**
	 * @return \stdClass
	 */
	public function getResult()
	{
		return $this->document;
	}

}

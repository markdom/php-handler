<?php

declare(strict_types=1);

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
	 * @var bool
	 */
	private $handleComments = true;

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
	 * @return bool
	 */
	public function getHandleComments(): bool
	{
		return $this->handleComments;
	}

	/**
	 * @param bool $handleComments
	 * @return $this
	 */
	public function setHandleComments(bool $handleComments)
	{
		$this->handleComments = $handleComments;
		return $this;
	}

	/**
	 * @return void
	 */
	public function onDocumentBegin(): void
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
	public function onDocumentEnd(): void
	{
		$this->blockParents->pop();
	}

	/**
	 * @return void
	 */
	public function onBlocksBegin(): void
	{
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockBegin(string $type): void
	{
	}

	/**
	 * @param string $code
	 * @param string $hint
	 * @return void
	 */
	public function onCodeBlock(string $code, ?string $hint = null): void
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
	public function onCommentBlock(string $comment): void
	{
		if (!$this->getHandleComments()) {
			return;
		}
		$parent = $this->blockParents->get();
		$parent->blocks[] = (object)array(
			KeyNameTranslator::ATTRIBUTE_COMMON_TYPE => KeyNameTranslator::TYPE_COMMENT,
			KeyNameTranslator::ATTRIBUTE_COMMENT_COMMENT => $comment,
		);
	}

	/**
	 * @return void
	 */
	public function onDivisionBlock(): void
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
	public function onHeadingBlockBegin(int $level): void
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
	public function onHeadingBlockEnd(int $level): void
	{
		$this->contentParents->pop();
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockBegin(): void
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
	public function onOrderedListBlockBegin(int $startIndex): void
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
	public function onListItemsBegin(): void
	{
	}

	/**
	 * @return void
	 */
	public function onListItemBegin(): void
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
	public function onListItemEnd(): void
	{
		$this->blockParents->pop();
	}

	/**
	 * @return void
	 */
	public function onNextListItem(): void
	{
	}

	/**
	 * @return void
	 */
	public function onListItemsEnd(): void
	{
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockEnd(): void
	{
		$this->listBlocks->pop();
	}

	/**
	 * @param int
	 * @return void
	 */
	public function onOrderedListBlockEnd(int $startIndex): void
	{
		$this->listBlocks->pop();
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockBegin(): void
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
	public function onParagraphBlockEnd(): void
	{
		$this->contentParents->pop();
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockBegin(): void
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
	public function onQuoteBlockEnd(): void
	{
		$this->blockParents->pop();
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockEnd(string $type): void
	{
	}

	/**
	 * @return void
	 */
	public function onNextBlock(): void
	{
	}

	/**
	 * @return void
	 */
	public function onBlocksEnd(): void
	{
	}

	/**
	 * @return void
	 */
	public function onContentsBegin(): void
	{
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onContentBegin(string $type): void
	{
	}

	/**
	 * @param string $code
	 * @return void
	 */
	public function onCodeContent(string $code): void
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
	public function onEmphasisContentBegin(int $level): void
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
	public function onEmphasisContentEnd(int $level): void
	{
		$this->contentParents->pop();
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @param string $alternative
	 * @return void
	 */
	public function onImageContent(string $uri, ?string $title = null, ?string $alternative = null): void
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
	public function onLineBreakContent(bool $hard): void
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
	public function onLinkContentBegin(string $uri, ?string $title = null): void
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
	public function onLinkContentEnd(string $uri, ?string $title = null): void
	{
		$this->contentParents->pop();
	}

	/**
	 * @param string $text
	 * @return void
	 */
	public function onTextContent(string $text): void
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
	public function onContentEnd(string $type): void
	{
	}

	/**
	 * @return void
	 */
	public function onNextContent(): void
	{
	}

	/**
	 * @return void
	 */
	public function onContentsEnd(): void
	{
	}

	/**
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @return \stdClass
	 */
	public function getResult()
	{
		return $this->document;
	}

}

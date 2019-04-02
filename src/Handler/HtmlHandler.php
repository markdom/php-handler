<?php

declare(strict_types=1);

namespace Markdom\Handler;

use Markdom\Common\EmphasisLevel;
use Markdom\Handler\HtmlTagBuilder\HtmlTagBuilder;
use Markdom\Handler\HtmlTagBuilder\TagBuilderInterface;
use Markdom\HandlerInterface\HandlerInterface;
use Markenwerk\StackUtil\Stack;
use Markenwerk\StringBuilder\StringBuilder;

/**
 * Class HtmlHandler
 *
 * @package Markdom\Handler
 */
class HtmlHandler implements HandlerInterface
{

	private const LINE_BREAK = PHP_EOL;

	/**
	 * @var bool
	 */
	private $handleComments = true;

	/**
	 * @var TagBuilderInterface
	 */
	protected $tagBuilder = null;

	/**
	 * @var bool
	 */
	private $escapeHtml = true;

	/**
	 * @var bool
	 */
	private $breakSoftBreaks = false;

	/**
	 * @var StringBuilder
	 */
	protected $htmlBuilder;

	/**
	 * @var Stack
	 */
	private $blockStack;

	/**
	 * HtmlHandler constructor.
	 */
	public function __construct()
	{
		if (is_null($this->tagBuilder)) {
			$this->tagBuilder = new HtmlTagBuilder();
		}
		$this->htmlBuilder = new StringBuilder();
		$this->blockStack = new Stack();
	}

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
	 * @return TagBuilderInterface
	 */
	public function getTagBuilder(): TagBuilderInterface
	{
		return $this->tagBuilder;
	}

	/**
	 * @param TagBuilderInterface $tagBuilder
	 * @return $this
	 */
	public function setTagBuilder(TagBuilderInterface $tagBuilder)
	{
		$this->tagBuilder = $tagBuilder;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getEscapeHtml(): bool
	{
		return $this->escapeHtml;
	}

	/**
	 * @param bool $escapeHtml
	 * @return $this
	 */
	public function setEscapeHtml(bool $escapeHtml)
	{
		$this->escapeHtml = $escapeHtml;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getBreakSoftBreaks(): bool
	{
		return $this->breakSoftBreaks;
	}

	/**
	 * @param bool $breakSoftBreaks
	 * @return $this
	 */
	public function setBreakSoftBreaks(bool $breakSoftBreaks)
	{
		$this->breakSoftBreaks = $breakSoftBreaks;
		return $this;
	}

	/**
	 * @return Stack
	 */
	public function getBlockStack(): Stack
	{
		return $this->blockStack;
	}

	/**
	 * @return void
	 */
	public function onDocumentBegin(): void
	{
	}

	/**
	 * @return void
	 */
	public function onDocumentEnd(): void
	{
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
		if ($this->htmlBuilder->size() > 0) {
			$this->htmlBuilder->append(self::LINE_BREAK);
		}
		$this->blockStack->push($type);
	}

	/**
	 * @param string $code
	 * @param string $hint
	 * @return void
	 */
	public function onCodeBlock(string $code, ?string $hint = null): void
	{
		$this->htmlBuilder->append(
			$this->getTagBuilder()->buildTag(
				TagBuilderInterface::TYPE_CODE_BLOCK,
				$code,
				array('class' => $hint)
			)
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
		$this->htmlBuilder->append(
			$this->getTagBuilder()->buildTag(
				TagBuilderInterface::TYPE_COMMENT,
				$comment
			)
		);
	}

	/**
	 * @return void
	 */
	public function onDivisionBlock(): void
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_DIVISION));
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockBegin(int $level): void
	{
		$this->htmlBuilder->append(
			$this->getTagBuilder()->buildTag(
				TagBuilderInterface::TYPE_HEADING_BEGIN,
				null,
				array(),
				$level
			)
		);
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockEnd(int $level): void
	{
		$this->htmlBuilder->append(
			$this->getTagBuilder()->buildTag(
				TagBuilderInterface::TYPE_HEADING_END,
				null,
				array(),
				$level
			)
		);
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockBegin(): void
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_UNORDERED_LIST_BEGIN));
	}

	/**
	 * @param int $startIndex
	 * @return void
	 */
	public function onOrderedListBlockBegin(int $startIndex): void
	{
		$this->htmlBuilder->append(
			$this->getTagBuilder()->buildTag(
				TagBuilderInterface::TYPE_ORDERED_LIST_BEGIN,
				null,
				array('start' => (string)$startIndex)
			)
		);
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
		$this->htmlBuilder
			->append(self::LINE_BREAK)
			->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_LIST_ITEM_BEGIN));
		$this->blockStack->push('listitem');
	}

	/**
	 * @return void
	 */
	public function onListItemEnd(): void
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_LIST_ITEM_END));
		$this->blockStack->pop();
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
		if ($this->blockStack->size() > 0) {
			$this->htmlBuilder->append(self::LINE_BREAK);
		}
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockEnd(): void
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_UNORDERED_LIST_END));
	}

	/**
	 * @param int
	 * @return void
	 */
	public function onOrderedListBlockEnd(int $startIndex): void
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_ORDERED_LIST_END));
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockBegin(): void
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_PARAGRAPH_BEGIN));
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockEnd(): void
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_PARAGRAPH_END));
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockBegin(): void
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_QUOTE_BEGIN));
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockEnd(): void
	{
		$this->htmlBuilder
			->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_QUOTE_END));
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
		if ($this->blockStack->size() > 0) {
			$this->htmlBuilder->append(self::LINE_BREAK);
		}
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
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_CODE_INLINE, $code));
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentBegin(int $level): void
	{
		$tagType = TagBuilderInterface::TYPE_EMPHASIS_LEVEL_1_BEGIN;
		if ($level === EmphasisLevel::LEVEL_2) {
			$tagType = TagBuilderInterface::TYPE_EMPHASIS_LEVEL_2_BEGIN;
		}
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag($tagType));
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentEnd(int $level): void
	{
		$tagType = TagBuilderInterface::TYPE_EMPHASIS_LEVEL_1_END;
		if ($level === EmphasisLevel::LEVEL_2) {
			$tagType = TagBuilderInterface::TYPE_EMPHASIS_LEVEL_2_END;
		}
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag($tagType));
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @param string $alternative
	 * @return void
	 */
	public function onImageContent(string $uri, ?string $title = null, ?string $alternative = null): void
	{
		$this->htmlBuilder->append(
			$this->getTagBuilder()->buildTag(
				TagBuilderInterface::TYPE_IMAGE,
				null,
				array(
					'src' => $uri,
					'title' => $title,
					'alt' => $alternative
				)
			)
		);
	}

	/**
	 * @param bool $hard
	 * @return void
	 */
	public function onLineBreakContent(bool $hard): void
	{
		if ($hard || $this->getBreakSoftBreaks()) {
			$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_LINE_BREAK));
		} else {
			$this->htmlBuilder->append(self::LINE_BREAK);
		}
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentBegin(string $uri, ?string $title = null): void
	{
		$this->htmlBuilder->append(
			$this->getTagBuilder()->buildTag(
				TagBuilderInterface::TYPE_LINK_BEGIN,
				null,
				array(
					'href' => $uri,
					'title' => $title
				)
			)
		);
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentEnd(string $uri, ?string $title = null): void
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_LINK_END));
	}

	/**
	 * @param string $text
	 * @return void
	 */
	public function onTextContent(string $text): void
	{
		if ($this->getEscapeHtml()) {
			$text = htmlentities($text);
		}
		$this->htmlBuilder->append($text);
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
	 * @return string
	 */
	public function getResult(): string
	{
		return (string)$this->htmlBuilder->build();
	}

}

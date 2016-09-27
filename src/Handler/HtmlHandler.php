<?php

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

	const LINE_BREAK = PHP_EOL;

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
	private $escapeHtml = false;

	/**
	 * @var bool
	 */
	private $breakSoftBreaks = false;

	/**
	 * @var StringBuilder
	 */
	private $htmlBuilder;

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
	 * @return boolean
	 */
	public function getHandleComments()
	{
		return $this->handleComments;
	}

	/**
	 * @param boolean $handleComments
	 * @return $this
	 */
	public function setHandleComments($handleComments)
	{
		$this->handleComments = $handleComments;
		return $this;
	}

	/**
	 * @return TagBuilderInterface
	 */
	public function getTagBuilder()
	{
		return $this->tagBuilder;
	}

	/**
	 * @param TagBuilderInterface $tagBuilder
	 * @return $this
	 */
	public function setTagBuilder($tagBuilder)
	{
		$this->tagBuilder = $tagBuilder;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEscapeHtml()
	{
		return $this->escapeHtml;
	}

	/**
	 * @param boolean $escapeHtml
	 * @return $this
	 */
	public function setEscapeHtml($escapeHtml)
	{
		$this->escapeHtml = $escapeHtml;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getBreakSoftBreaks()
	{
		return $this->breakSoftBreaks;
	}

	/**
	 * @param boolean $breakSoftBreaks
	 * @return $this
	 */
	public function setBreakSoftBreaks($breakSoftBreaks)
	{
		$this->breakSoftBreaks = $breakSoftBreaks;
		return $this;
	}

	/**
	 * @return StringBuilder
	 */
	public function getHtmlBuilder()
	{
		return $this->htmlBuilder;
	}

	/**
	 * @return Stack
	 */
	public function getBlockStack()
	{
		return $this->blockStack;
	}

	/**
	 * @return void
	 */
	public function onDocumentBegin()
	{
	}

	/**
	 * @return void
	 */
	public function onDocumentEnd()
	{
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
	public function onCodeBlock($code, $hint = null)
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
	 * @param $comment
	 * @return void
	 */
	public function onCommentBlock($comment)
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
	public function onDivisionBlock()
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_DIVISION));
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockBegin($level)
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
	public function onHeadingBlockEnd($level)
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
	public function onUnorderedListBlockBegin()
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_UNORDERED_LIST_BEGIN));
	}

	/**
	 * @param int $startIndex
	 * @return void
	 */
	public function onOrderedListBlockBegin($startIndex)
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
	public function onListItemsBegin()
	{
	}

	/**
	 * @return void
	 */
	public function onListItemBegin()
	{
		$this->htmlBuilder
			->append(self::LINE_BREAK)
			->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_LIST_ITEM_BEGIN));
		$this->blockStack->push('listitem');
	}

	/**
	 * @return void
	 */
	public function onListItemEnd()
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_LIST_ITEM_END));
		$this->blockStack->pop();
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
		if ($this->blockStack->size() > 0) {
			$this->htmlBuilder->append(self::LINE_BREAK);
		}
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockEnd()
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_UNORDERED_LIST_END));
	}

	/**
	 * @param int
	 * @return void
	 */
	public function onOrderedListBlockEnd($startIndex)
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_ORDERED_LIST_END));
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockBegin()
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_PARAGRAPH_BEGIN));
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockEnd()
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_PARAGRAPH_END));
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockBegin()
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_QUOTE_BEGIN));
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockEnd()
	{
		$this->htmlBuilder
			->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_QUOTE_END));
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
		if ($this->blockStack->size() > 0) {
			$this->htmlBuilder->append(self::LINE_BREAK);
		}
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
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_CODE_INLINE, $code));
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentBegin($level)
	{
		$tagType = TagBuilderInterface::TYPE_EMPHASIS_LEVEL_1_BEGIN;
		if ($level == EmphasisLevel::LEVEL_2) {
			$tagType = TagBuilderInterface::TYPE_EMPHASIS_LEVEL_2_BEGIN;
		}
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag($tagType));
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentEnd($level)
	{
		$tagType = TagBuilderInterface::TYPE_EMPHASIS_LEVEL_1_END;
		if ($level == EmphasisLevel::LEVEL_2) {
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
	public function onImageContent($uri, $title = null, $alternative = null)
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
	public function onLineBreakContent($hard)
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
	public function onLinkContentBegin($uri, $title = null)
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
	public function onLinkContentEnd($uri, $title = null)
	{
		$this->htmlBuilder->append($this->getTagBuilder()->buildTag(TagBuilderInterface::TYPE_LINK_END));
	}

	/**
	 * @param string $text
	 * @return void
	 */
	public function onTextContent($text)
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
	 * @return string
	 */
	public function getResult()
	{
		return $this->htmlBuilder->build();
	}

}

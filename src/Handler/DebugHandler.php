<?php

namespace Markdom\Handler;

use Markdom\HandlerInterface\HandlerInterface;

/**
 * Class JsonHandler
 *
 * @package Markdom\Handler
 */
class DebugHandler implements HandlerInterface
{

	/**
	 * @var bool
	 */
	private $handleComments = true;

	/**
	 * @var int
	 */
	private $indentationLevel = 0;

	/**
	 * @var string[]
	 */
	private $output = array();

	/**
	 * @return bool
	 */
	public function getHandleComments()
	{
		return $this->handleComments;
	}

	/**
	 * @param bool $handleComments
	 * @return $this
	 */
	public function setHandleComments($handleComments)
	{
		$this->handleComments = $handleComments;
		return $this;
	}

	/**
	 * @return void
	 */
	public function onDocumentBegin()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
		$this->indentationLevel++;
	}

	/**
	 * @return void
	 */
	public function onDocumentEnd()
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onBlocksBegin()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
		$this->indentationLevel++;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockBegin($type)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $type;
		$this->indentationLevel++;
	}

	/**
	 * @param string $code
	 * @param string $hint
	 * @return void
	 */
	public function onCodeBlock($code, $hint = null)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $hint . ' ~ ' . $code;
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
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $comment;
	}

	/**
	 * @return void
	 */
	public function onDivisionBlock()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockBegin($level)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $level;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockEnd($level)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $level;
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockBegin()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @param int $startIndex
	 * @return void
	 */
	public function onOrderedListBlockBegin($startIndex)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $startIndex;
	}

	/**
	 * @return void
	 */
	public function onListItemsBegin()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onListItemBegin()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onListItemEnd()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onNextListItem()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onListItemsEnd()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockEnd()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @param int
	 * @return void
	 */
	public function onOrderedListBlockEnd($startIndex)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $startIndex;
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockBegin()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockEnd()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockBegin()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockEnd()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockEnd($type)
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $type;
	}

	/**
	 * @return void
	 */
	public function onNextBlock()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onBlocksEnd()
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onContentsBegin()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
		$this->indentationLevel++;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onContentBegin($type)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $type;
		$this->indentationLevel++;
	}

	/**
	 * @param string $code
	 * @return void
	 */
	public function onCodeContent($code)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $code;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentBegin($level)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $level;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentEnd($level)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $level;
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @param string $alternative
	 * @return void
	 */
	public function onImageContent($uri, $title = null, $alternative = null)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $uri . ' ~ ' . $title . ' ~ ' . $alternative;
	}

	/**
	 * @param bool $hard
	 * @return void
	 */
	public function onLineBreakContent($hard)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $hard;
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentBegin($uri, $title = null)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $uri . ' ~ ' . $title;
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentEnd($uri, $title = null)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $uri . ' ~ ' . $title;
	}

	/**
	 * @param string $text
	 * @return void
	 */
	public function onTextContent($text)
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $text;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onContentEnd($type)
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $type;
	}

	/**
	 * @return void
	 */
	public function onNextContent()
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onContentsEnd()
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return string
	 */
	public function getResult()
	{
		return implode(PHP_EOL, $this->output);
	}

	/**
	 * @return string
	 */
	private function getIndentation()
	{
		return str_pad('', $this->indentationLevel * 4, ' ', STR_PAD_LEFT);
	}

}

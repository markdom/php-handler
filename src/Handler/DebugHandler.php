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
		$this->output[] = $this->getIndentation() . __FUNCTION__;
		$this->indentationLevel++;
	}

	/**
	 * @return void
	 */
	public function onDocumentEnd(): void
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onBlocksBegin(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
		$this->indentationLevel++;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockBegin(string $type): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $type;
		$this->indentationLevel++;
	}

	/**
	 * @param string $code
	 * @param string $hint
	 * @return void
	 */
	public function onCodeBlock(string $code, ?string $hint = null): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $hint . ' ~ ' . $code;
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
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $comment;
	}

	/**
	 * @return void
	 */
	public function onDivisionBlock(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockBegin(int $level): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $level;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockEnd(int $level): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $level;
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockBegin(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @param int $startIndex
	 * @return void
	 */
	public function onOrderedListBlockBegin(int $startIndex): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $startIndex;
	}

	/**
	 * @return void
	 */
	public function onListItemsBegin(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onListItemBegin(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onListItemEnd(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onNextListItem(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onListItemsEnd(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockEnd(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @param int
	 * @return void
	 */
	public function onOrderedListBlockEnd(int $startIndex): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $startIndex;
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockBegin(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockEnd(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockBegin(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockEnd(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockEnd(string $type): void
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $type;
	}

	/**
	 * @return void
	 */
	public function onNextBlock(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onBlocksEnd(): void
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onContentsBegin(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
		$this->indentationLevel++;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onContentBegin(string $type): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $type;
		$this->indentationLevel++;
	}

	/**
	 * @param string $code
	 * @return void
	 */
	public function onCodeContent(string $code): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $code;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentBegin(int $level): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $level;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentEnd(int $level): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $level;
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @param string $alternative
	 * @return void
	 */
	public function onImageContent(string $uri, ?string $title = null, ?string $alternative = null): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $uri . ' ~ ' . $title . ' ~ ' . $alternative;
	}

	/**
	 * @param bool $hard
	 * @return void
	 */
	public function onLineBreakContent(bool $hard): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $hard;
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentBegin(string $uri, ?string $title = null): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $uri . ' ~ ' . $title;
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentEnd(string $uri, ?string $title = null): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $uri . ' ~ ' . $title;
	}

	/**
	 * @param string $text
	 * @return void
	 */
	public function onTextContent(string $text): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $text;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onContentEnd(string $type): void
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__ . ': ' . $type;
	}

	/**
	 * @return void
	 */
	public function onNextContent(): void
	{
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return void
	 */
	public function onContentsEnd(): void
	{
		$this->indentationLevel--;
		$this->output[] = $this->getIndentation() . __FUNCTION__;
	}

	/**
	 * @return string
	 */
	public function getResult(): string
	{
		return implode(PHP_EOL, $this->output);
	}

	/**
	 * @return string
	 */
	private function getIndentation(): string
	{
		return str_pad('', $this->indentationLevel * 4, ' ', STR_PAD_LEFT);
	}

}

<?php

namespace Markdom\Handler;

use Markdom\Common\BlockType;
use Markdom\Common\EmphasisLevel;
use Markdom\Handler\CommonmarkUtil\HandlerDelimiter;
use Markdom\HandlerInterface\HandlerInterface;
use Markenwerk\StackUtil\Stack;
use Markenwerk\StringBuilder\StringBuilder;

/**
 * Class CommonmarkHandler
 *
 * @package Markdom\Handler
 */
class CommonmarkHandler implements HandlerInterface
{

	/**
	 * @var bool
	 */
	private $handleComments = true;

	/**
	 * @var bool
	 */
	private $blocksAreEmpty;

	/**
	 * @var Stack
	 */
	private $listStyles;

	/**
	 * @var Stack
	 */
	private $listIndices;

	/**
	 * @var Stack
	 */
	private $lineStarts;

	/**
	 * @var Stack
	 */
	private $delimiters;

	/**
	 * @var bool
	 */
	private $lineStarted;

	/**
	 * @var bool
	 */
	private $onlyDigitsInLine;

	/**
	 * @var bool
	 */
	private $pendingSpace;

	/**
	 * @var bool
	 */
	private $inParagraphBlock = false;

	/**
	 * @var string
	 */
	private $lastEndedBlock;

	/**
	 * @var string
	 */
	private $output = '';

	/**
	 * @var string
	 */
	private $escapeCharacters = '\\`*_[]';

	/**
	 * @var string
	 */
	private $escapeLineStartCharacters = '#+-';

	/**
	 * @var array
	 */
	private $escapeCharacterList = array();

	/**
	 * @var array
	 */
	private $escapeLineStartCharacterList = array();

	/**
	 * MarkdownHandler constructor.
	 */
	public function __construct()
	{
		$this->listStyles = new Stack();
		$this->listIndices = new Stack();
		$this->lineStarts = new Stack();
		$this->delimiters = new Stack();
		$this->escapeCharacterList = str_split($this->escapeCharacters);
		$this->escapeLineStartCharacterList = str_split($this->escapeLineStartCharacters);
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
	 * @return string
	 */
	public function getEscapeCharacters(): string
	{
		return $this->escapeCharacters;
	}

	/**
	 * @param string $escapeCharacters
	 * @return $this
	 */
	public function setEscapeCharacters(string $escapeCharacters)
	{
		$this->escapeCharacters = $escapeCharacters;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEscapeLineStartCharacters(): string
	{
		return $this->escapeLineStartCharacters;
	}

	/**
	 * @param string $escapeCharacters
	 * @return $this
	 */
	public function setEscapeLineStartCharacters(string $escapeCharacters)
	{
		$this->escapeLineStartCharacters = $escapeCharacters;
		return $this;
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
		$this->blocksAreEmpty = true;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockBegin(string $type): void
	{
		$this->blocksAreEmpty = false;
	}

	/**
	 * @param string $code
	 * @param string $hint
	 * @return void
	 */
	public function onCodeBlock(string $code, ?string $hint = null): void
	{
		$backticksSequence = max(3, $this->longestBackticksSequence($code) + 1);
		$backtickString = str_repeat('`', $backticksSequence);
		$this
			->startLine()
			->append($backtickString)
			->append($hint)
			->terminateLine();
		$tokens = explode(PHP_EOL, $code);
		for ($i = 0, $n = count($tokens); $i < $n; $i++) {
			$this
				->startLine()
				->append($tokens[$i])
				->terminateLine();
		}
		$this
			->startLine()
			->append($backtickString)
			->terminateLine();
	}

	/**
	 * @param $comment
	 * @return void
	 */
	public function onCommentBlock(string $comment): void
	{
		if (!$this->getHandleComments()) {
			return;
		}
		$tokens = explode(PHP_EOL, $comment);
		if (count($tokens) > 1) {
			$this
				->startLine()
				->append('<!--')
				->terminateLine();
			for ($i = 0, $n = count($tokens); $i < $n; $i++) {
				$this
					->startLine()
					->append($tokens[$i])
					->terminateLine();
			}
			$this
				->startLine()
				->append('-->')
				->terminateLine();
		} else {
			$this
				->startLine()
				->append('<!-- ')
				->append($comment)
				->append(' -->')
				->terminateLine();
		}
	}

	/**
	 * @return void
	 */
	public function onDivisionBlock(): void
	{
		$this
			->startLine()
			->append('---')
			->terminateLine();
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockBegin(int $level): void
	{
		$this
			->startLine()
			->append(str_repeat('#', $level))
			->append(' ');
		$this->lineStarted = false;
		$this->onlyDigitsInLine = true;
		$this->pendingSpace = false;
		$delimiter = new HandlerDelimiter('');
		$delimiter->setEmpty(false);
		$this->delimiters->push($delimiter);
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockEnd(int $level): void
	{
		$this->terminateLine();
		$this->delimiters->pop();
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockBegin(): void
	{
		$this->displaceAdjacentLists();
		$this->listStyles->push(false);
		$this->listIndices->push(null);
	}

	/**
	 * @param int $startIndex
	 * @return void
	 */
	public function onOrderedListBlockBegin(int $startIndex): void
	{
		$this->displaceAdjacentLists();
		$this->listStyles->push(true);
		$this->listIndices->push($startIndex);
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
		if ($this->listStyles->get()) {
			$itemIndex = $this->listIndices->get();
			$itemIndicator = (string)$itemIndex . '. ';
			$this->listIndices->set($itemIndex + 1);
		} else {
			$itemIndicator = '* ';
		}
		$this->lineStarts->push($itemIndicator);
		$this
			->startLine()
			->terminateLine();
		$this->lineStarts->set(str_repeat(' ', mb_strlen($itemIndicator)));
		$this->lastEndedBlock = null;
	}

	/**
	 * @return void
	 */
	public function onListItemEnd(): void
	{
		$this->lineStarts->pop();
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
		$this->listStyles->pop();
		$this->listIndices->pop();
	}

	/**
	 * @param int
	 * @return void
	 */
	public function onOrderedListBlockEnd(int $startIndex): void
	{
		$this->listStyles->pop();
		$this->listIndices->pop();
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockBegin(): void
	{
		$this->startLine();
		$this->inParagraphBlock = true;
		$this->lineStarted = false;
		$this->onlyDigitsInLine = true;
		$this->pendingSpace = false;
		$delimiter = new HandlerDelimiter('');
		$delimiter->setEmpty(false);
		$this->delimiters->push($delimiter);
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockEnd(): void
	{
		$this->inParagraphBlock = false;
		$this->delimiters->pop();
		$this->terminateLine();
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockBegin(): void
	{
		$this->lineStarts->push('> ');
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockEnd(): void
	{
		$this->lineStarts->pop();
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockEnd(string $type): void
	{
		$this->lastEndedBlock = $type;
	}

	/**
	 * @return void
	 */
	public function onNextBlock(): void
	{
		$this
			->startLine()
			->terminateLine();
	}

	/**
	 * @return void
	 */
	public function onBlocksEnd(): void
	{
		if ($this->blocksAreEmpty) {
			$this
				->startLine()
				->terminateLine();
		}
		$this->blocksAreEmpty = false;
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
		$builder = new StringBuilder();
		$this
			->appendPendingDelimiters($builder)
			->append($builder->build())
			->append('`');
		if (empty($code)) {
			$this->append(' ');
		} else {
			$backticksSequence = $this->longestBackticksSequence($code);
			if ($backticksSequence === 0) {
				$this->append($code);
			} else {
				$this->appendCode($code, $backticksSequence);
			}
		}
		$this->append('`');
		$this->lineStarted = true;
		$this->onlyDigitsInLine = false;
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentBegin(int $level): void
	{
		switch ($level) {
			case EmphasisLevel::LEVEL_1:
				$this->delimiters->push(new HandlerDelimiter('*'));
				break;
			case EmphasisLevel::LEVEL_2:
				$this->delimiters->push(new HandlerDelimiter('**'));
				break;
		}
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentEnd(int $level): void
	{
		$delimiter = $this->delimiters->pop();
		if (!$delimiter->isEmpty()) {
			$this->append($delimiter->getLiteral());
		}
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @param string $alternative
	 * @return void
	 */
	public function onImageContent(string $uri, ?string $title = null, ?string $alternative = null): void
	{
		$this->append('![');
		if (!is_null($alternative)) {
			$this->append($alternative);
		}
		$this
			->append('](')
			->append($uri);
		if (!is_null($title)) {
			$this
				->append(' "')
				->append($title)
				->append('"');
		}
		$this->append(')');
		$this->lineStarted = true;
		$this->onlyDigitsInLine = false;
	}

	/**
	 * @param bool $hard
	 * @return void
	 */
	public function onLineBreakContent(bool $hard): void
	{
		if (!$this->inParagraphBlock) {
			$this->pendingSpace = $this->lineStarted;
			return;
		}
		if ($hard) {
			$this->append('  ');
		}
		$this
			->terminateLine()
			->startLine();
		$this->lineStarted = false;
		$this->onlyDigitsInLine = true;
		$this->pendingSpace = false;
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentBegin(string $uri, ?string $title = null): void
	{
		$this->delimiters->push(new HandlerDelimiter('['));
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentEnd(string $uri, ?string $title = null): void
	{
		$title = (!is_null($title)) ? ' "' . $title . '"' : '';
		$delimiter = $this->delimiters->get();
		if ($delimiter->isEmpty()) {
			$this->onTextContent($uri . $title);
		}
		$this->delimiters->pop();
		$this->append('](' . $uri . $title . ')');
	}

	/**
	 * @param string $text
	 * @return void
	 */
	public function onTextContent(string $text): void
	{
		$textSize = mb_strlen($text);
		$builder = new StringBuilder();
		for ($i = 0, $n = mb_strlen($text); $i < $n; $i++) {
			$character = mb_substr($text, $i, 1);
			$nextCharacter = null;
			if ($textSize > $i + 1) {
				$nextCharacter = mb_substr($text, $i + 1, 1);
			}
			if (!ctype_cntrl($character)) {
				if (
					(
						!$this->lineStarted
						&& $this->inParagraphBlock
						&& in_array($character, $this->escapeLineStartCharacterList, true)
						&& ($nextCharacter === ' ' || $nextCharacter === "\t")
					)
					|| in_array($character, $this->escapeCharacterList, true)
				) {
					$this->appendPendingDelimiters($builder);
					$builder->append('\\' . $character);
				} else if ($character === '.' && $this->onlyDigitsInLine) {
					$this->appendPendingDelimiters($builder);
					$builder->append('\\' . $character);
				} else if ($character === ' ') {
					$this->pendingSpace = $this->lineStarted;
				} else {
					$this->appendPendingDelimiters($builder);
					$builder->append($character);
				}
			} else if ($character === "\t") {
				$this->pendingSpace = $this->lineStarted;
			}
			$this->onlyDigitsInLine = $this->onlyDigitsInLine && ctype_digit($character);
		}
		$this->append($builder->build());
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
	 * @param string $html
	 * @return void
	 */
	public function onHtmlBlockBegin(string $html): void
	{
		$this
			->startLine()
			->append($html)
			->terminateLine();
	}

	/**
	 * @return void
	 */
	public function onHtmlBlockEnd(): void
	{
	}

	/**
	 * @param string $html
	 * @return void
	 */
	public function onHtmlContent(string $html): void
	{
		$this->append($html);
	}

	/**
	 * @return string
	 */
	public function getResult(): string
	{
		return $this->output;
	}

	/**
	 * @return void
	 */
	private function displaceAdjacentLists(): void
	{
		if (
			$this->lastEndedBlock === BlockType::TYPE_UNORDERED_LIST
			|| $this->lastEndedBlock === BlockType::TYPE_ORDERED_LIST
		) {
			$this->onBlockBegin(BlockType::TYPE_DIVISION);
			$this->onDivisionBlock();
			$this->onBlockEnd(BlockType::TYPE_DIVISION);
		}
	}

	/**
	 * @param string $code
	 * @return int
	 */
	private function longestBackticksSequence(string $code): int
	{
		$maxLength = 0;
		$currentLength = 0;
		for ($i = 0, $n = mb_strlen($code); $i < $n; $i++) {
			if ('`' === mb_substr($code, $i, 1)) {
				$currentLength++;
			} else {
				$maxLength = max($maxLength, $currentLength);
				$currentLength = 0;
			}
		}
		return max($maxLength, $currentLength);
	}

	/**
	 * @param string $code
	 * @param int $backticksSequence
	 * @return $this
	 */
	private function appendCode(string $code, int $backticksSequence)
	{
		$backtickString = str_repeat('`', $backticksSequence);
		$this->append($backtickString);
		if (mb_strpos($code, '`') === 0) {
			$this->append(' ');
		}
		$this->append($code);
		if (mb_substr($code, -1) === '`') {
			$this->append(' ');
		}
		$this->append($backtickString);
		return $this;
	}

	/**
	 * @param StringBuilder $builder
	 * @return $this
	 */
	private function appendPendingDelimiters(StringBuilder $builder)
	{
		if ($this->pendingSpace) {
			$builder->append(' ');
		}
		$this->pendingSpace = false;
		$this->lineStarted = true;
		for ($i = 0, $n = $this->delimiters->size(); $i < $n; $i++) {
			$delimiter = $this->delimiters->get($i);
			if ($delimiter->isEmpty()) {
				$builder->append($delimiter->getLiteral());
				$delimiter->setEmpty(false);
				$this->onlyDigitsInLine = false;
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	private function startLine()
	{
		for ($i = 0, $n = $this->lineStarts->size(); $i < $n; $i++) {
			$linePrefix = $this->lineStarts->get($i);
			$this->append($linePrefix);
		}
		return $this;
	}

	/**
	 * @param string $string
	 * @return $this
	 */
	private function append(string $string)
	{
		$this->output .= $string;
		return $this;
	}

	/**
	 * @return $this
	 */
	private function terminateLine()
	{
		$this->append(PHP_EOL);
		return $this;
	}

}

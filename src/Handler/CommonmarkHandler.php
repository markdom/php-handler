<?php

namespace Markdom\Handler;

use Markdom\Common\BlockType;
use Markdom\Common\EmphasisLevel;
use Markdom\Handler\CommonmarkUtil\HandlerDelimiter;
use Markdom\HandlerInterface\HandlerInterface;
use Markenwerk\StringBuilder\StringBuilder;
use Markenwerk\StackUtil\Stack;

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
	 * @return string
	 */
	public function getEscapeCharacters()
	{
		return $this->escapeCharacters;
	}

	/**
	 * @param string $escapeCharacters
	 * @return $this
	 */
	public function setEscapeCharacters($escapeCharacters)
	{
		$this->escapeCharacters = $escapeCharacters;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEscapeLineStartCharacters()
	{
		return $this->escapeLineStartCharacters;
	}

	/**
	 * @param string $escapeCharacters
	 * @return $this
	 */
	public function setEscapeLineStartCharacters($escapeCharacters)
	{
		$this->escapeLineStartCharacters = $escapeCharacters;
		return $this;
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
		$this->blocksAreEmpty = true;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockBegin($type)
	{
		$this->blocksAreEmpty = false;
	}

	/**
	 * @param string $code
	 * @param string $hint
	 * @return void
	 */
	public function onCodeBlock($code, $hint = null)
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
	public function onCommentBlock($comment)
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
	public function onDivisionBlock()
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
	public function onHeadingBlockBegin($level)
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
	public function onHeadingBlockEnd($level)
	{
		$this->terminateLine();
		$this->delimiters->pop();
	}

	/**
	 * @return void
	 */
	public function onUnorderedListBlockBegin()
	{
		$this->displaceAdjacentLists();
		$this->listStyles->push(false);
		$this->listIndices->push(null);
	}

	/**
	 * @param int $startIndex
	 * @return void
	 */
	public function onOrderedListBlockBegin($startIndex)
	{
		$this->displaceAdjacentLists();
		$this->listStyles->push(true);
		$this->listIndices->push($startIndex);
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
	public function onListItemEnd()
	{
		$this->lineStarts->pop();
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
		$this->listStyles->pop();
		$this->listIndices->pop();
	}

	/**
	 * @param int
	 * @return void
	 */
	public function onOrderedListBlockEnd($startIndex)
	{
		$this->listStyles->pop();
		$this->listIndices->pop();
	}

	/**
	 * @return void
	 */
	public function onParagraphBlockBegin()
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
	public function onParagraphBlockEnd()
	{
		$this->inParagraphBlock = false;
		$this->delimiters->pop();
		$this->terminateLine();
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockBegin()
	{
		$this->lineStarts->push('> ');
	}

	/**
	 * @return void
	 */
	public function onQuoteBlockEnd()
	{
		$this->lineStarts->pop();
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function onBlockEnd($type)
	{
		$this->lastEndedBlock = $type;
	}

	/**
	 * @return void
	 */
	public function onNextBlock()
	{
		$this
			->startLine()
			->terminateLine();
	}

	/**
	 * @return void
	 */
	public function onBlocksEnd()
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
	public function onEmphasisContentBegin($level)
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
	public function onEmphasisContentEnd($level)
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
	public function onImageContent($uri, $title = null, $alternative = null)
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
	public function onLineBreakContent($hard)
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
	public function onLinkContentBegin($uri, $title = null)
	{
		$this->delimiters->push(new HandlerDelimiter('['));
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentEnd($uri, $title = null)
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
	public function onTextContent($text)
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
						&& in_array($character, $this->escapeLineStartCharacterList)
						&& ($nextCharacter === ' ' || $nextCharacter === "\t")
					)
					|| in_array($character, $this->escapeCharacterList)
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
	 * @param string $html
	 * @return void
	 */
	public function onHtmlBlockBegin($html)
	{
		$this
			->startLine()
			->append($html)
			->terminateLine();
	}

	/**
	 * @return void
	 */
	public function onHtmlBlockEnd()
	{
	}

	/**
	 * @param string $html
	 * @return void
	 */
	public function onHtmlContent($html)
	{
		$this->append($html);
	}

	/**
	 * @return string
	 */
	public function getResult()
	{
		return $this->output;
	}

	/**
	 * @return void
	 */
	private function displaceAdjacentLists()
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
	private function longestBackticksSequence($code)
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
	private function appendCode($code, $backticksSequence)
	{
		$backtickString = str_repeat('`', $backticksSequence);
		$this->append($backtickString);
		if (mb_substr($code, 0, 1) === '`') {
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
	private function append($string)
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

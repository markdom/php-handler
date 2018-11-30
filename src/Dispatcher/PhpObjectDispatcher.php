<?php

namespace Markdom\Dispatcher;

use Markdom\Dispatcher\EventDispatcher\SimpleMarkdomEventDispatcher;
use Markdom\Dispatcher\Exception\DispatcherException;
use Markdom\DispatcherInterface\DispatcherInterface;
use Markdom\Handler\TypeNameTranslator\KeyNameTranslator;
use Markdom\HandlerInterface\HandlerInterface;

/**
 * Class PhpObjectDispatcher
 *
 * @package Markdom\Dispatcher
 */
class PhpObjectDispatcher implements DispatcherInterface
{

	/**
	 * @var SimpleMarkdomEventDispatcher
	 */
	private $eventDispatcher;

	/**
	 * @var \stdClass
	 */
	private $document;

	/**
	 * PhpObjectDispatcher constructor.
	 *
	 * @param \stdClass $document
	 */
	public function __construct($document)
	{
		$this->document = $document;
	}

	/**
	 * @param HandlerInterface $markdomHandler
	 * @return mixed
	 * @throws DispatcherException
	 */
	public function dispatchTo(HandlerInterface $markdomHandler)
	{
		// Init event dispatcher
		$this->eventDispatcher = new SimpleMarkdomEventDispatcher($markdomHandler);
		// Walk through the document
		if (!is_object($this->document)) {
			throw new DispatcherException('Markdom invalid: root node is no object.');
		}
		if (!isset($this->document->version) || !is_object($this->document->version)) {
			throw new DispatcherException('Markdom invalid: no document version specified.');
		}
		if (!isset($this->document->version->major, $this->document->version->minor)) {
			throw new DispatcherException('Markdom invalid: no document valid version specified.');
		}
		if ((int)$this->document->version->major !== 1 || (int)$this->document->version->minor !== 0) {
			throw new DispatcherException('Markdom invalid: version mismatch Expected version 1.0.');
		}
		$this->eventDispatcher->onDocumentBegin();
		$this->processBlocks($this->document->blocks);
		$this->eventDispatcher->onDocumentEnd();
		return $markdomHandler->getResult();
	}

	/**
	 * @return bool
	 */
	public function isReusable():bool
	{
		return true;
	}

	/**
	 * @param \stdClass[] $blocks
	 * @return $this
	 * @throws DispatcherException
	 */
	private function processBlocks(array $blocks)
	{
		for ($i = 0, $n = count($blocks); $i < $n; $i++) {
			$node = $blocks[$i];
			if (!is_object($node)) {
				throw new DispatcherException('Markdom invalid: block node is no object.');
			}
			if (!isset($node->type)) {
				throw new DispatcherException('Markdom invalid: block node has no type.');
			}
			switch ($node->type) {
				case KeyNameTranslator::TYPE_CODE:
					$hint = $node->hint ?? null;
					$this->eventDispatcher->onCodeBlock($node->code, $hint);
					break;
				case KeyNameTranslator::TYPE_COMMENT:
					$this->eventDispatcher->onCommentBlock($node->comment);
					break;
				case KeyNameTranslator::TYPE_DIVISION:
					$this->eventDispatcher->onDivisionBlock();
					break;
				case KeyNameTranslator::TYPE_HEADING:
					$this->eventDispatcher->onHeadingBlockBegin($node->level);
					$this->processContents($node->contents);
					$this->eventDispatcher->onHeadingBlockEnd();
					break;
				case KeyNameTranslator::TYPE_UNORDERED_LIST:
					$this->eventDispatcher->onUnorderedListBlockBegin();
					$this->processListItems($node->items);
					$this->eventDispatcher->onUnorderedListBlockEnd();
					break;
				case KeyNameTranslator::TYPE_ORDERED_LIST:
					$this->eventDispatcher->onOrderedListBlockBegin($node->startIndex);
					$this->processListItems($node->items);
					$this->eventDispatcher->onOrderedListBlockEnd();
					break;
				case KeyNameTranslator::TYPE_PARAGRAPH:
					$this->eventDispatcher->onParagraphBlockBegin();
					$this->processContents($node->contents);
					$this->eventDispatcher->onParagraphBlockEnd();
					break;
				case KeyNameTranslator::TYPE_QUOTE:
					$this->eventDispatcher->onQuoteBlockBegin();
					$this->processBlocks($node->blocks);
					$this->eventDispatcher->onQuoteBlockEnd();
					break;
				default:
					throw new DispatcherException('Markdom invalid: block node type ' . $node->type . ' is invalid.');
					break;
			}
		}
		return $this;
	}

	/**
	 * @param \stdClass[] $listItems
	 * @return $this
	 * @throws DispatcherException
	 */
	private function processListItems(array $listItems)
	{
		for ($i = 0, $n = count($listItems); $i < $n; $i++) {
			$node = $listItems[$i];
			if (!is_object($node)) {
				throw new DispatcherException('Markdom invalid: list item node is no object.');
			}
			$this->eventDispatcher->onListItemBegin();
			$this->processBlocks($node->blocks);
			$this->eventDispatcher->onListItemEnd();
		}
		return $this;
	}

	/**
	 * @param \stdClass[] $contents
	 * @return $this
	 * @throws DispatcherException
	 */
	private function processContents(array $contents)
	{
		for ($i = 0, $n = count($contents); $i < $n; $i++) {
			$node = $contents[$i];
			if (!is_object($node)) {
				throw new DispatcherException('Markdom invalid: content node is no object.');
			}
			if (!isset($node->type)) {
				throw new DispatcherException('Markdom invalid: content node has no type.');
			}
			switch ($node->type) {
				case KeyNameTranslator::TYPE_CODE:
					$this->eventDispatcher->onCodeContent($node->code);
					break;
				case KeyNameTranslator::TYPE_EMPHASIS:
					$this->eventDispatcher->onEmphasisContentBegin($node->level);
					$this->processContents($node->contents);
					$this->eventDispatcher->onEmphasisContentEnd();
					break;
				case KeyNameTranslator::TYPE_IMAGE:
					$title = $node->title ?? null;
					$alternative = $node->alternative ?? null;
					$this->eventDispatcher->onImageContent($node->uri, $title, $alternative);
					break;
				case KeyNameTranslator::TYPE_LINE_BREAK:
					$this->eventDispatcher->onLineBreakContent($node->hard);
					break;
				case KeyNameTranslator::TYPE_LINK:
					$title = $node->title ?? null;
					$this->eventDispatcher->onLinkContentBegin($node->uri, $title);
					$this->processContents($node->contents);
					$this->eventDispatcher->onLinkContentEnd();
					break;
				case KeyNameTranslator::TYPE_TEXT:
					$this->eventDispatcher->onTextContent($node->text);
					break;
				default:
					throw new DispatcherException('Markdom invalid: content node type ' . $node->type . ' is invalid.');
					break;
			}
		}
		return $this;
	}

}

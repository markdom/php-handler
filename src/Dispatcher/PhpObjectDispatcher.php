<?php

namespace Markdom\Dispatcher;

use Markdom\Dispatcher\Exception\DispatcherException;
use Markdom\DispatcherInterface\DispatcherInterface;
use Markdom\Handler\EventDispatcher\SimpleMarkdomEventDispatcher;
use Markdom\Handler\TypeNameTranslator\KeyNameTranslator;
use Markdom\HandlerInterface\HandlerInterface;

/**
 * Class PhpObjectDispatcher
 *
 * @package Markenwerk\Markdom\Dispatcher\Markdom
 */
class PhpObjectDispatcher implements DispatcherInterface
{

	/**
	 * @var HandlerInterface
	 */
	private $markdomHandler;

	/**
	 * @var SimpleMarkdomEventDispatcher
	 */
	private $eventDispatcher;

	/**
	 * Parser constructor.
	 *
	 * @param HandlerInterface $markdomHandler
	 */
	public function __construct(HandlerInterface $markdomHandler)
	{
		$this->markdomHandler = $markdomHandler;
	}

	/**
	 * @param \stdClass $source
	 * @return $this
	 * @throws DispatcherException
	 */
	public function process($source)
	{
		// Init event dispatcher
		$this->eventDispatcher = new SimpleMarkdomEventDispatcher($this->markdomHandler);
		// Walk through the document
		if (!is_object($source)) {
			throw new DispatcherException('Markdom invalid: root node is no object.');
		}
		if(!isset($source->version) || !is_object($source->version)){
			throw new DispatcherException('Markdom invalid: no document version specified.');
		}
		if(!isset($source->version->major) || !isset($source->version->minor)){
			throw new DispatcherException('Markdom invalid: no document valid version specified.');
		}
		if((int)$source->version->major !== 1 || (int)$source->version->minor !== 0){
			throw new DispatcherException('Markdom invalid: version mismatch Expected version 1.0.');
		}
		$this->eventDispatcher->onDocumentBegin();
		$this->processBlocks($source->blocks);
		$this->eventDispatcher->onDocumentEnd();
		return $this;
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
					$hint = isset($node->hint) ? $node->hint : null;
					$this->eventDispatcher->onCodeBlock($node->code, $hint);
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
					$title = isset($node->title) ? $node->title : null;
					$alternative = isset($node->alternative) ? $node->alternative : null;
					$this->eventDispatcher->onImageContent($node->uri, $title, $alternative);
					break;
				case KeyNameTranslator::TYPE_LINE_BREAK:
					$this->eventDispatcher->onLineBreakContent($node->hard);
					break;
				case KeyNameTranslator::TYPE_LINK:
					$this->eventDispatcher->onLinkContentBegin($node->uri);
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

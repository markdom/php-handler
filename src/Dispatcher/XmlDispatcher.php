<?php

namespace Markdom\Dispatcher;

use Markdom\Dispatcher\EventDispatcher\SimpleMarkdomEventDispatcher;
use Markdom\Dispatcher\Exception\DispatcherException;
use Markdom\DispatcherInterface\DispatcherInterface;
use Markdom\Handler\TypeNameTranslator\KeyNameTranslator;
use Markdom\HandlerInterface\HandlerInterface;

/**
 * Class XmlDispatcher
 *
 * @package Markdom\Dispatcher
 */
class XmlDispatcher implements DispatcherInterface
{

	/**
	 * @var \DOMDocument
	 */
	private $domDocument;

	/**
	 * @var SimpleMarkdomEventDispatcher
	 */
	private $eventDispatcher;

	/**
	 * XmlDispatcher constructor.
	 *
	 * @param \DOMDocument $domDocument
	 */
	public function __construct(\DOMDocument $domDocument)
	{
		$this->domDocument = $domDocument;
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
		if (!is_object($this->domDocument) || !$this->domDocument instanceof \DOMDocument) {
			throw new DispatcherException('Markdom invalid: root node is no DOMDocument instance.');
		}
		if (!$this->domDocument->hasChildNodes()) {
			throw new DispatcherException('Markdom invalid');
		}
		/* @var \DOMElement $document */
		$document = $this->domDocument->firstChild;
		if (!$document->hasAttribute(KeyNameTranslator::ATTRIBUTE_DOCUMENT_VERSION)) {
			throw new DispatcherException('Markdom invalid: no document version specified.');
		}
		if ((float)$document->getAttribute(KeyNameTranslator::ATTRIBUTE_DOCUMENT_VERSION) !== 1.0) {
			throw new DispatcherException('Markdom invalid: version mismatch Expected version 1.0.');
		}
		$this->eventDispatcher->onDocumentBegin();
		$this->processBlocks($document->childNodes);
		$this->eventDispatcher->onDocumentEnd();
		return $markdomHandler->getResult();
	}

	/**
	 * @return bool
	 */
	public function isReusable()
	{
		return true;
	}

	/**
	 * @param \DOMNodeList $blocks
	 * @return $this
	 * @throws DispatcherException
	 */
	private function processBlocks(\DOMNodeList $blocks)
	{
		for ($i = 0, $n = $blocks->length; $i < $n; $i++) {
			/* @var \DOMElement $node */
			$node = $blocks->item($i);
			switch ($node->nodeName) {
				case KeyNameTranslator::TYPE_CODE:
					$hint = null;
					if ($node->hasAttribute(KeyNameTranslator::ATTRIBUTE_CODE_HINT)) {
						$hint = $node->getAttribute(KeyNameTranslator::ATTRIBUTE_CODE_HINT);
					}
					$this->eventDispatcher->onCodeBlock($node->textContent, $hint);
					break;
				case KeyNameTranslator::TYPE_COMMENT:
					$this->eventDispatcher->onCommentBlock($node->textContent);
					break;
				case KeyNameTranslator::TYPE_DIVISION:
					$this->eventDispatcher->onDivisionBlock();
					break;
				case KeyNameTranslator::TYPE_HEADING:
					$this->eventDispatcher->onHeadingBlockBegin(
						(int)$node->getAttribute(KeyNameTranslator::ATTRIBUTE_HEADING_LEVEL)
					);
					$this->processContents($node->childNodes);
					$this->eventDispatcher->onHeadingBlockEnd();
					break;
				case KeyNameTranslator::TYPE_UNORDERED_LIST:
					$this->eventDispatcher->onUnorderedListBlockBegin();
					$this->processListItems($node->childNodes);
					$this->eventDispatcher->onUnorderedListBlockEnd();
					break;
				case KeyNameTranslator::TYPE_ORDERED_LIST:
					$this->eventDispatcher->onOrderedListBlockBegin(
						(int)$node->getAttribute(KeyNameTranslator::ATTRIBUTE_ORDERED_LIST_START_INDEX)
					);
					$this->processListItems($node->childNodes);
					$this->eventDispatcher->onOrderedListBlockEnd();
					break;
				case KeyNameTranslator::TYPE_PARAGRAPH:
					$this->eventDispatcher->onParagraphBlockBegin();
					$this->processContents($node->childNodes);
					$this->eventDispatcher->onParagraphBlockEnd();
					break;
				case KeyNameTranslator::TYPE_QUOTE:
					$this->eventDispatcher->onQuoteBlockBegin();
					$this->processBlocks($node->childNodes);
					$this->eventDispatcher->onQuoteBlockEnd();
					break;
				default:
					throw new DispatcherException('Markdom invalid: block node ' . $node->nodeName . ' is invalid in ' . $node->parentNode->nodeName . '.');
					break;
			}
		}
		return $this;
	}

	/**
	 * @param \DOMNodeList $listItems
	 * @return $this
	 * @throws DispatcherException
	 */
	private function processListItems(\DOMNodeList $listItems)
	{
		for ($i = 0, $n = $listItems->length; $i < $n; $i++) {
			$node = $listItems->item($i);
			$this->eventDispatcher->onListItemBegin();
			$this->processBlocks($node->childNodes);
			$this->eventDispatcher->onListItemEnd();
		}
		return $this;
	}

	/**
	 * @param \DOMNodeList $contents
	 * @return $this
	 * @throws DispatcherException
	 */
	private function processContents(\DOMNodeList $contents)
	{
		for ($i = 0, $n = $contents->length; $i < $n; $i++) {
			/* @var \DOMElement $node */
			$node = $contents->item($i);
			switch ($node->nodeName) {
				case KeyNameTranslator::TYPE_CODE:
					$this->eventDispatcher->onCodeContent($node->textContent);
					break;
				case KeyNameTranslator::TYPE_EMPHASIS:
					$this->eventDispatcher->onEmphasisContentBegin(
						(int)$node->getAttribute(KeyNameTranslator::ATTRIBUTE_EMPHASIS_LEVEL)
					);
					$this->processContents($node->childNodes);
					$this->eventDispatcher->onEmphasisContentEnd();
					break;
				case KeyNameTranslator::TYPE_IMAGE:
					$title = null;
					if ($node->hasAttribute(KeyNameTranslator::ATTRIBUTE_IMAGE_TITLE)) {
						$title = $node->getAttribute(KeyNameTranslator::ATTRIBUTE_IMAGE_TITLE);
					}
					$alternative = null;
					if ($node->hasAttribute(KeyNameTranslator::ATTRIBUTE_IMAGE_ALTERNATIVE)) {
						$alternative = $node->getAttribute(KeyNameTranslator::ATTRIBUTE_IMAGE_ALTERNATIVE);
					}
					$this->eventDispatcher->onImageContent(
						$node->getAttribute(KeyNameTranslator::ATTRIBUTE_IMAGE_URI),
						$title,
						$alternative
					);
					break;
				case KeyNameTranslator::TYPE_LINE_BREAK:
					$this->eventDispatcher->onLineBreakContent(
						$this->translateBoolean($node->getAttribute(KeyNameTranslator::ATTRIBUTE_LINE_BREAK_HARD))
					);
					break;
				case KeyNameTranslator::TYPE_LINK:
					$title = null;
					if ($node->hasAttribute(KeyNameTranslator::ATTRIBUTE_LINK_TITLE)) {
						$title = $node->getAttribute(KeyNameTranslator::ATTRIBUTE_LINK_TITLE);
					}
					$this->eventDispatcher->onLinkContentBegin(
						$node->getAttribute(KeyNameTranslator::ATTRIBUTE_LINK_URI),
						$title
					);
					$this->processContents($node->childNodes);
					$this->eventDispatcher->onLinkContentEnd();
					break;
				case KeyNameTranslator::TYPE_TEXT:
					$this->eventDispatcher->onTextContent($node->textContent);
					break;
				default:
					throw new DispatcherException('Markdom invalid: content node type ' . $node->nodeName . ' is invalid in ' . $node->parentNode->nodeName . '.');
					break;
			}
		}
		return $this;
	}

	/**
	 * @param string $xmlAttributeValue
	 * @return bool
	 */
	private function translateBoolean($xmlAttributeValue)
	{
		return mb_strtolower($xmlAttributeValue) === 'true';
	}

}

<?php

declare(strict_types=1);

namespace Markdom\Handler;

use ChromaX\StackUtil\Stack;
use Markdom\Handler\TypeNameTranslator\KeyNameTranslator;
use Markdom\HandlerInterface\HandlerInterface;

/**
 * Class XmlHandler
 *
 * @package Markdom\Handler
 */
class XmlHandler implements HandlerInterface
{

	/**
	 * @var bool
	 */
	private $handleComments = true;

	/**
	 * @var \DOMDocument
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
	 * @var bool
	 */
	private $prettyPrint = false;

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
	 * @return bool
	 */
	public function getPrettyPrint(): bool
	{
		return $this->prettyPrint;
	}

	/**
	 * @param bool $prettyPrint
	 * @return $this
	 */
	public function setPrettyPrint(bool $prettyPrint)
	{
		$this->prettyPrint = $prettyPrint;
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
		$this->document = new \DOMDocument('1.0');
		if ($this->prettyPrint) {
			$this->document->preserveWhiteSpace = false;
			$this->document->formatOutput = true;
		}
		$documentNode = $this->document->createElement(KeyNameTranslator::TYPE_DOCUMENT);
		$versionAttribute = $this->document->createAttribute('version');
		$versionAttribute->appendChild($this->document->createTextNode('1.0'));
		$documentNode->appendChild($versionAttribute);
		$namepsaceAttribute = $this->document->createAttribute('xmlns');
		$namepsaceAttribute->appendChild($this->document->createTextNode('http://schema.markenwerk.net/markdom-1.0.xsd'));
		$documentNode->appendChild($namepsaceAttribute);
		$this->document->appendChild($documentNode);
		$this->blockParents->push($documentNode);
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
		$codeNode = $this->document->createElement(KeyNameTranslator::TYPE_CODE);
		if (!empty($code)) {
			$codeNode->appendChild($this->createTextNode($code));
		}
		$hintAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_CODE_HINT);
		$hintAttribute->appendChild($this->document->createTextNode((string)$hint));
		$codeNode->appendChild($hintAttribute);
		/* @var \DOMElement $parent */
		$parent = $this->blockParents->get();
		$parent->appendChild($codeNode);
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
		$commentNode = $this->document->createElement(KeyNameTranslator::TYPE_COMMENT);
		if (!empty($comment)) {
			$commentNode->appendChild($this->createTextNode($comment));
		}
		/* @var \DOMElement $parent */
		$parent = $this->blockParents->get();
		$parent->appendChild($commentNode);
	}

	/**
	 * @return void
	 */
	public function onDivisionBlock(): void
	{
		/* @var \DOMElement $parent */
		$parent = $this->blockParents->get();
		$parent->appendChild($this->document->createElement(KeyNameTranslator::TYPE_DIVISION));
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onHeadingBlockBegin(int $level): void
	{
		$headingNode = $this->document->createElement(KeyNameTranslator::TYPE_HEADING);
		$levelAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_HEADING_LEVEL);
		$levelAttribute->appendChild($this->document->createTextNode((string)$level));
		$headingNode->appendChild($levelAttribute);
		/* @var \DOMElement $parent */
		$parent = $this->blockParents->get();
		$parent->appendChild($headingNode);
		$this->contentParents->push($headingNode);
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
		$listNode = $this->document->createElement(KeyNameTranslator::TYPE_UNORDERED_LIST);
		/* @var \DOMElement $parent */
		$parent = $this->blockParents->get();
		$parent->appendChild($listNode);
		$this->listBlocks->push($listNode);
	}

	/**
	 * @param int $startIndex
	 * @return void
	 */
	public function onOrderedListBlockBegin(int $startIndex): void
	{
		$listNode = $this->document->createElement(KeyNameTranslator::TYPE_ORDERED_LIST);
		$startIndexAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_ORDERED_LIST_START_INDEX);
		$startIndexAttribute->appendChild($this->document->createTextNode((string)$startIndex));
		$listNode->appendChild($startIndexAttribute);
		/* @var \DOMElement $parent */
		$parent = $this->blockParents->get();
		$parent->appendChild($listNode);
		$this->listBlocks->push($listNode);
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
		$listItemNode = $this->document->createElement(KeyNameTranslator::TYPE_LIST_ITEM);
		/* @var \DOMElement $parent */
		$parent = $this->listBlocks->get();
		$parent->appendChild($listItemNode);
		$this->blockParents->push($listItemNode);
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
		$paragraphNode = $this->document->createElement(KeyNameTranslator::TYPE_PARAGRAPH);
		/* @var \DOMElement $parent */
		$parent = $this->blockParents->get();
		$parent->appendChild($paragraphNode);
		$this->contentParents->push($paragraphNode);
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
		$quoteNode = $this->document->createElement(KeyNameTranslator::TYPE_QUOTE);
		/* @var \DOMElement $parent */
		$parent = $this->blockParents->get();
		$parent->appendChild($quoteNode);
		$this->blockParents->push($quoteNode);
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
		$codeNode = $this->document->createElement(KeyNameTranslator::TYPE_CODE);
		if (!empty($code)) {
			$codeNode->appendChild($this->createTextNode($code));
		}
		/* @var \DOMElement $parent */
		$parent = $this->contentParents->get();
		$parent->appendChild($codeNode);
	}

	/**
	 * @param int $level
	 * @return void
	 */
	public function onEmphasisContentBegin(int $level): void
	{
		$emphasisNode = $this->document->createElement(KeyNameTranslator::TYPE_EMPHASIS);
		$levelAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_EMPHASIS_LEVEL);
		$levelAttribute->appendChild($this->document->createTextNode((string)$level));
		$emphasisNode->appendChild($levelAttribute);
		/* @var \DOMElement $parent */
		$parent = $this->contentParents->get();
		$parent->appendChild($emphasisNode);
		$this->contentParents->push($emphasisNode);
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
		$imageNode = $this->document->createElement(KeyNameTranslator::TYPE_IMAGE);
		$uriAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_IMAGE_URI);
		$uriAttribute->appendChild($this->document->createTextNode($uri));
		$titleAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_IMAGE_TITLE);
		$titleAttribute->appendChild($this->document->createTextNode((string)$title));
		$alternativeAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_IMAGE_ALTERNATIVE);
		$alternativeAttribute->appendChild($this->document->createTextNode((string)$alternative));
		$imageNode->appendChild($uriAttribute);
		$imageNode->appendChild($titleAttribute);
		$imageNode->appendChild($alternativeAttribute);
		/* @var \DOMElement $parent */
		$parent = $this->contentParents->get();
		$parent->appendChild($imageNode);
	}

	/**
	 * @param bool $hard
	 * @return void
	 */
	public function onLineBreakContent(bool $hard): void
	{
		$hard = $hard ? 'true' : 'false';
		$linebreakNode = $this->document->createElement(KeyNameTranslator::TYPE_LINE_BREAK);
		$hardAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_LINE_BREAK_HARD);
		$hardAttribute->appendChild($this->document->createTextNode($hard));
		$linebreakNode->appendChild($hardAttribute);
		/* @var \DOMElement $parent */
		$parent = $this->contentParents->get();
		$parent->appendChild($linebreakNode);
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @return void
	 */
	public function onLinkContentBegin(string $uri, ?string $title = null): void
	{
		$linkNode = $this->document->createElement(KeyNameTranslator::TYPE_LINK);
		$uriAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_LINK_URI);
		$uriAttribute->appendChild($this->document->createTextNode($uri));
		$linkNode->appendChild($uriAttribute);
		$titleAttribute = $this->document->createAttribute(KeyNameTranslator::ATTRIBUTE_LINK_TITLE);
		$titleAttribute->appendChild($this->document->createTextNode((string)$title));
		$linkNode->appendChild($titleAttribute);
		/* @var \DOMElement $parent */
		$parent = $this->contentParents->get();
		$parent->appendChild($linkNode);
		$this->contentParents->push($linkNode);
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
		$textNode = $this->document->createElement(KeyNameTranslator::TYPE_TEXT);
		if (!empty($text)) {
			$textNode->appendChild($this->createTextNode($text));
		}
		/* @var \DOMElement $parent */
		$parent = $this->contentParents->get();
		$parent->appendChild($textNode);
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
	 * @return \DOMDocument
	 */
	public function getResult(): \DOMDocument
	{
		return $this->document;
	}

	/**
	 * @param string $text
	 * @return \DOMText
	 */
	private function createTextNode(string $text): \DOMText
	{
		if (
			mb_strpos($text, PHP_EOL) !== false
			|| mb_strpos($text, ' ') === 0
			|| mb_strrpos($text, ' ') === mb_strlen($text) - 1
		) {
			return $this->document->createCDATASection($text);
		}
		return $this->document->createTextNode($text);
	}

}

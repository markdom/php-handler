<?php

namespace Markdom\Dispatcher\CommonmarkUtil;

use League\CommonMark\Block\Element\Document;
use League\CommonMark\DocumentProcessorInterface;
use Markdom\Dispatcher\HtmlProcessor\HtmlProcessorInterface;
use Markdom\Dispatcher\HtmlProcessor\HtmlTextProcessor;
use Markdom\HandlerInterface\HandlerInterface;
use Markenwerk\StackUtil\Stack;

/**
 * Class CommonmarkDocumentProcessor
 *
 * @package Markdom\Dispatcher\CommonmarkUtil
 */
final class DocumentProcessor implements DocumentProcessorInterface
{

	const BLOCK_NODE_BLOCK_QUOTE = 'League\CommonMark\Block\Element\BlockQuote';
	const BLOCK_NODE_DOCUMENT = 'League\CommonMark\Block\Element\Document';
	const BLOCK_NODE_EMPHASIS = 'League\CommonMark\Inline\Element\Emphasis';
	const BLOCK_NODE_FENCED_CODE = 'League\CommonMark\Block\Element\FencedCode';
	const BLOCK_NODE_HEADING = 'League\CommonMark\Block\Element\Heading';
	const BLOCK_NODE_HTML_BLOCK = 'League\CommonMark\Block\Element\HtmlBlock';
	const BLOCK_NODE_IMAGE = 'League\CommonMark\Inline\Element\Image';
	const BLOCK_NODE_INDENTED_CODE = 'League\CommonMark\Block\Element\IndentedCode';
	const BLOCK_NODE_INLINE_CONTAINER = 'League\CommonMark\Block\Element\InlineContainer';
	const BLOCK_NODE_LINK = 'League\CommonMark\Inline\Element\Link';
	const BLOCK_NODE_LIST_BLOCK = 'League\CommonMark\Block\Element\ListBlock';
	const BLOCK_NODE_LIST_DATA = 'League\CommonMark\Block\Element\ListData';
	const BLOCK_NODE_LIST_ITEM = 'League\CommonMark\Block\Element\ListItem';
	const BLOCK_NODE_PARAGRAPH = 'League\CommonMark\Block\Element\Paragraph';
	const BLOCK_NODE_STRONG = 'League\CommonMark\Inline\Element\Strong';
	const BLOCK_NODE_THEMATIC_BREAK = 'League\CommonMark\Block\Element\ThematicBreak';

	const INLINE_NODE_CODE = 'League\CommonMark\Inline\Element\Code';
	const INLINE_NODE_HTML_INLINE = 'League\CommonMark\Inline\Element\HtmlInline';
	const INLINE_NODE_NEWLINE = 'League\CommonMark\Inline\Element\Newline';
	const INLINE_NODE_TEXT = 'League\CommonMark\Inline\Element\Text';

	/**
	 * @var HandlerInterface
	 */
	private $markdomHandler;

	/**
	 * @var HtmlProcessorInterface
	 */
	private $htmlProcessor;

	/**
	 * @var Stack
	 */
	private $imageStack;

	/**
	 * DocumentProcessor constructor.
	 *
	 * @param HandlerInterface $markdomHandler
	 * @param HtmlProcessorInterface $htmlProcessor
	 */
	public function __construct(HandlerInterface $markdomHandler, HtmlProcessorInterface $htmlProcessor = null)
	{
		$this->markdomHandler = $markdomHandler;
		$this->imageStack = new Stack();
		if (is_null($htmlProcessor)) {
			$htmlProcessor = new HtmlTextProcessor();
		}
		$this->htmlProcessor = $htmlProcessor;
	}

	/**
	 * @param Document $document
	 *
	 * @return void
	 */
	public function processDocument(Document $document)
	{
		$markdomHandlerEventDispatcher = new MarkdomEventBridge($this->markdomHandler, $this->htmlProcessor);
		$walker = $document->walker();
		while ($event = $walker->next()) {
			$node = $event->getNode();
			if ($event->isEntering()) {
				if ($this->imageStack->size() === 0) {
					$markdomHandlerEventDispatcher->dispatchMarkdomEvent($event);
				}
				if (get_class($node) === self::BLOCK_NODE_IMAGE) {
					$this->imageStack->push($node);
				}
			} else {
				if (get_class($node) === self::BLOCK_NODE_IMAGE) {
					$this->imageStack->pop();
				}
				if ($this->imageStack->size() === 0) {
					$markdomHandlerEventDispatcher->dispatchMarkdomEvent($event);
				}
			}
		}
	}

}

<?php

declare(strict_types=1);

namespace Markdom\Dispatcher\CommonmarkUtil;

use League\CommonMark\Block\Element\Document;
use League\CommonMark\DocumentProcessorInterface;
use Markdom\Dispatcher\Exception\DispatcherException;
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

	public const BLOCK_NODE_BLOCK_QUOTE = 'League\CommonMark\Block\Element\BlockQuote';
	public const BLOCK_NODE_DOCUMENT = 'League\CommonMark\Block\Element\Document';
	public const BLOCK_NODE_EMPHASIS = 'League\CommonMark\Inline\Element\Emphasis';
	public const BLOCK_NODE_FENCED_CODE = 'League\CommonMark\Block\Element\FencedCode';
	public const BLOCK_NODE_HEADING = 'League\CommonMark\Block\Element\Heading';
	public const BLOCK_NODE_HTML_BLOCK = 'League\CommonMark\Block\Element\HtmlBlock';
	public const BLOCK_NODE_IMAGE = 'League\CommonMark\Inline\Element\Image';
	public const BLOCK_NODE_INDENTED_CODE = 'League\CommonMark\Block\Element\IndentedCode';
	public const BLOCK_NODE_INLINE_CONTAINER = 'League\CommonMark\Block\Element\InlineContainer';
	public const BLOCK_NODE_LINK = 'League\CommonMark\Inline\Element\Link';
	public const BLOCK_NODE_LIST_BLOCK = 'League\CommonMark\Block\Element\ListBlock';
	public const BLOCK_NODE_LIST_DATA = 'League\CommonMark\Block\Element\ListData';
	public const BLOCK_NODE_LIST_ITEM = 'League\CommonMark\Block\Element\ListItem';
	public const BLOCK_NODE_PARAGRAPH = 'League\CommonMark\Block\Element\Paragraph';
	public const BLOCK_NODE_STRONG = 'League\CommonMark\Inline\Element\Strong';
	public const BLOCK_NODE_THEMATIC_BREAK = 'League\CommonMark\Block\Element\ThematicBreak';

	public const INLINE_NODE_CODE = 'League\CommonMark\Inline\Element\Code';
	public const INLINE_NODE_HTML_INLINE = 'League\CommonMark\Inline\Element\HtmlInline';
	public const INLINE_NODE_NEWLINE = 'League\CommonMark\Inline\Element\Newline';
	public const INLINE_NODE_TEXT = 'League\CommonMark\Inline\Element\Text';

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
	public function __construct(
		HandlerInterface $markdomHandler,
		?HtmlProcessorInterface $htmlProcessor = null
	) {
		$this->markdomHandler = $markdomHandler;
		if (is_null($htmlProcessor)) {
			$htmlProcessor = new HtmlTextProcessor();
		}
		$this->htmlProcessor = $htmlProcessor;
		$this->imageStack = new Stack();
	}

	/**
	 * @param Document $document
	 * @return void
	 * @throws DispatcherException
	 */
	public function processDocument(Document $document): void
	{
		$markdomHandlerEventDispatcher = new MarkdomEventBridge(
			$this->markdomHandler,
			$this->htmlProcessor
		);
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

<?php

namespace Markdom\Dispatcher\CommonmarkUtil;

use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\Heading;
use League\CommonMark\Block\Element\HtmlBlock;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\Block\Element\ListBlock;
use League\CommonMark\Block\Element\ListItem;
use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Emphasis;
use League\CommonMark\Inline\Element\HtmlInline;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Newline;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\NodeWalkerEvent;
use Markdom\Common\BlockType;
use Markdom\Common\ContentType;
use Markdom\Common\EmphasisLevel;
use Markdom\Dispatcher\Exception\DispatcherException;
use Markdom\Dispatcher\HtmlProcessor\HtmlProcessorInterface;
use Markdom\HandlerInterface\HandlerInterface;

/**
 * Class CommonmarkDispatcherMarkdomEventBridge.
 *
 * @package Markenwerk\Markdom\Dispatcher\Commonmark
 */
final class MarkdomEventBridge
{
    /**
     * @var HandlerInterface
     */
    private $markdomHandler;

    /**
     * @var HtmlProcessorInterface
     */
    private $htmlProcessor;

    /**
     * @var Node
     */
    private $recentInlineNode;

    /**
     * MarkdomHandlerEventDispatcher constructor.
     *
     * @param HandlerInterface       $commonmarkHandler
     * @param HtmlProcessorInterface $htmlProcessor
     */
    public function __construct(
		HandlerInterface $commonmarkHandler,
		HtmlProcessorInterface $htmlProcessor
	) {
        $this->markdomHandler = $commonmarkHandler;
        $this->htmlProcessor = $htmlProcessor;
    }

    /**
     * @param NodeWalkerEvent $commonMarkEvent
     *
     * @return $this
     */
    public function dispatchMarkdomEvent(NodeWalkerEvent $commonMarkEvent)
    {
        $node = $commonMarkEvent->getNode();
        $this->transmitInlineEndEvent();
        if ($commonMarkEvent->isEntering()) {
            if ($node->isContainer()) {
                $this->transmitContainerBeginEvent($node);
            } else {
                $this->transmitInlineBeginEvent($node);
            }
        } else {
            if ($node->isContainer()) {
                $this->transmitContainerEndEvent($node);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function dispatchBlocksBeginEvents()
    {
        $this->markdomHandler->onBlocksBegin();

        return $this;
    }

    /**
     * @return $this
     */
    private function dispatchBlocksEndEvents()
    {
        $this->markdomHandler->onBlocksEnd();

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    private function dispatchBlockBeginEvents($type)
    {
        $this->markdomHandler->onBlockBegin($type);

        return $this;
    }

    /**
     * @param Node   $node
     * @param string $type
     *
     * @return $this
     */
    private function dispatchBlockEndEvents($node, $type)
    {
        $this->markdomHandler->onBlockEnd($type);
        if (!is_null($node->next())) {
            $this->markdomHandler->onNextBlock();
        }

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    private function dispatchContentBeginEvents($type)
    {
        $this->markdomHandler->onContentBegin($type);

        return $this;
    }

    /**
     * @param Node   $node
     * @param string $type
     *
     * @return $this
     */
    private function dispatchContentEndEvents($node, $type)
    {
        $this->markdomHandler->onContentEnd($type);
        if (!is_null($node->next())) {
            $this->markdomHandler->onNextContent();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function dispatchContentsBeginEvents()
    {
        $this->markdomHandler->onContentsBegin();

        return $this;
    }

    /**
     * @return $this
     */
    private function dispatchContentsEndEvents()
    {
        $this->markdomHandler->onContentsEnd();

        return $this;
    }

    /**
     * @param Node $node
     *
     * @throws DispatcherException
     */
    private function transmitContainerBeginEvent(Node $node)
    {
        switch (get_class($node)) {
			case DocumentProcessor::BLOCK_NODE_BLOCK_QUOTE:
				$this->dispatchBlockBeginEvents(BlockType::TYPE_QUOTE);
				$this->markdomHandler->onQuoteBlockBegin();
				$this->dispatchBlocksBeginEvents();
				break;
			case DocumentProcessor::BLOCK_NODE_DOCUMENT:
				$this->markdomHandler->onDocumentBegin();
				$this->dispatchBlocksBeginEvents();
				break;
			case DocumentProcessor::BLOCK_NODE_EMPHASIS:
				$this->dispatchContentBeginEvents(ContentType::TYPE_EMPHASIS);
				/* @var Emphasis $node */
				$this->markdomHandler->onEmphasisContentBegin(EmphasisLevel::LEVEL_1);
				$this->dispatchContentsBeginEvents();
				break;
			case DocumentProcessor::BLOCK_NODE_FENCED_CODE:
				$this->dispatchBlockBeginEvents(BlockType::TYPE_CODE);
				/* @var FencedCode $node */
				$this->markdomHandler->onCodeBlock(trim($node->getStringContent()), $node->getInfo());
				break;
			case DocumentProcessor::BLOCK_NODE_HEADING:
				$this->dispatchBlockBeginEvents(BlockType::TYPE_HEADING);
				/* @var Heading $node */
				$this->markdomHandler->onHeadingBlockBegin($node->getLevel());
				$this->dispatchContentsBeginEvents();
				break;
			case DocumentProcessor::BLOCK_NODE_HTML_BLOCK:
				/** @var HtmlBlock $node */
				if ($node->getType() == $node::TYPE_2_COMMENT) {
				    $this->dispatchBlockBeginEvents(BlockType::TYPE_COMMENT);
				    $comment = $node->getStringContent();
				    if (mb_strpos($comment, '<!--') === 0) {
				        $comment = mb_substr($comment, 4);
				    }
				    if (mb_strrpos($comment, '-->') === mb_strlen($comment) - 3) {
				        $comment = mb_substr($comment, 0, -4);
				    }
				    $comment = trim($comment);
				    $this->markdomHandler->onCommentBlock($comment);
				} else {
				    $this->htmlProcessor->handleHtmlBlock($node, $this->markdomHandler);
				    if (!is_null($node->next())) {
				        $this->markdomHandler->onNextBlock();
				    }
				}
				break;
			case DocumentProcessor::BLOCK_NODE_IMAGE:
				$this->dispatchContentBeginEvents(ContentType::TYPE_IMAGE);
				/** @var Image $node */
				$plaintextBuilder = new PlaintextWalker();
				$alternativeText = $plaintextBuilder
					->processNode($node)
					->getPlaintext();
				$this->markdomHandler->onImageContent($node->getUrl(), $node->getData('title'), $alternativeText);
				break;
			case DocumentProcessor::BLOCK_NODE_INDENTED_CODE:
				$this->dispatchBlockBeginEvents(BlockType::TYPE_CODE);
				/* @var IndentedCode $node */
				$this->markdomHandler->onCodeBlock(trim($node->getStringContent()));
				break;
			case DocumentProcessor::BLOCK_NODE_INLINE_CONTAINER:
				break;
			case DocumentProcessor::BLOCK_NODE_LINK:
				$this->dispatchContentBeginEvents(ContentType::TYPE_LINK);
				/* @var Link $node */
				$this->markdomHandler->onLinkContentBegin($node->getUrl(), $node->getData('title'));
				$this->dispatchContentsBeginEvents();
				break;
			case DocumentProcessor::BLOCK_NODE_LIST_BLOCK:
				/** @var ListBlock $node */
				$ordered = $node->getListData()->type == ListBlock::TYPE_ORDERED;
				if ($ordered) {
				    $startIndex = $node->getListData()->start;
				    $this->dispatchBlockBeginEvents(BlockType::TYPE_ORDERED_LIST);
				    $this->markdomHandler->onOrderedListBlockBegin($startIndex);
				} else {
				    $this->dispatchBlockBeginEvents(BlockType::TYPE_UNORDERED_LIST);
				    $this->markdomHandler->onUnorderedListBlockBegin();
				}
				$this->markdomHandler->onListItemsBegin();
				break;
			case DocumentProcessor::BLOCK_NODE_LIST_DATA:
				break;
			case DocumentProcessor::BLOCK_NODE_LIST_ITEM:
				/* @var ListItem $node */
				$this->markdomHandler->onListItemBegin();
				$this->dispatchBlocksBeginEvents();
				break;
			case DocumentProcessor::BLOCK_NODE_PARAGRAPH:
				$this->dispatchBlockBeginEvents(BlockType::TYPE_PARAGRAPH);
				$this->markdomHandler->onParagraphBlockBegin();
				$this->dispatchContentsBeginEvents();
				break;
			case DocumentProcessor::BLOCK_NODE_STRONG:
				$this->dispatchContentBeginEvents(ContentType::TYPE_EMPHASIS);
				/* @var Emphasis $node */
				$this->markdomHandler->onEmphasisContentBegin(EmphasisLevel::LEVEL_2);
				$this->dispatchContentsBeginEvents();
				break;
			case DocumentProcessor::BLOCK_NODE_THEMATIC_BREAK:
				$this->dispatchBlockBeginEvents(BlockType::TYPE_DIVISION);
				$this->markdomHandler->onDivisionBlock();
				break;
			default:
				throw new DispatcherException('Block node ' . get_class($node) . ' is unknown');
				break;
		}
    }

    /**
     * @param Node $node
     *
     * @throws DispatcherException
     */
    private function transmitContainerEndEvent(Node $node)
    {
        switch (get_class($node)) {
			case DocumentProcessor::BLOCK_NODE_BLOCK_QUOTE:
				$this->dispatchBlocksEndEvents();
				$this->markdomHandler->onQuoteBlockEnd();
				$this->dispatchBlockEndEvents($node, BlockType::TYPE_QUOTE);
				break;
			case DocumentProcessor::BLOCK_NODE_DOCUMENT:
				$this->dispatchBlocksEndEvents();
				$this->markdomHandler->onDocumentEnd();
				break;
			case DocumentProcessor::BLOCK_NODE_EMPHASIS:
				$this->dispatchContentsEndEvents();
				$this->markdomHandler->onEmphasisContentEnd(EmphasisLevel::LEVEL_1);
				$this->dispatchContentEndEvents($node, ContentType::TYPE_EMPHASIS);
				break;
			case DocumentProcessor::BLOCK_NODE_FENCED_CODE:
				$this->dispatchBlockEndEvents($node, BlockType::TYPE_CODE);
				break;
			case DocumentProcessor::BLOCK_NODE_HEADING:
				/* @var Heading $node */
				$this->dispatchContentsEndEvents();
				$this->markdomHandler->onHeadingBlockEnd($node->getLevel());
				$this->dispatchBlockEndEvents($node, BlockType::TYPE_HEADING);
				break;
			case DocumentProcessor::BLOCK_NODE_HTML_BLOCK:
				/** @var HtmlBlock $node */
				if ($node->getType() == $node::TYPE_2_COMMENT) {
				    $this->dispatchBlockEndEvents($node, BlockType::TYPE_COMMENT);
				}
				break;
			case DocumentProcessor::BLOCK_NODE_IMAGE:
				$this->dispatchContentEndEvents($node, ContentType::TYPE_IMAGE);
				break;
			case DocumentProcessor::BLOCK_NODE_INDENTED_CODE:
				$this->dispatchBlockEndEvents($node, BlockType::TYPE_CODE);
				break;
			case DocumentProcessor::BLOCK_NODE_INLINE_CONTAINER:
				break;
			case DocumentProcessor::BLOCK_NODE_LINK:
				/* @var Link $node */
				$this->dispatchContentsEndEvents();
				$this->markdomHandler->onLinkContentEnd($node->getUrl());
				$this->dispatchContentEndEvents($node, ContentType::TYPE_LINK);
				break;
			case DocumentProcessor::BLOCK_NODE_LIST_BLOCK:
				/* @var ListBlock $node */
				$this->markdomHandler->onListItemsEnd();
				$ordered = $node->getListData()->type == ListBlock::TYPE_ORDERED;
				if ($ordered) {
				    $startIndex = $node->getListData()->start;
				    $this->markdomHandler->onOrderedListBlockEnd($startIndex);
				    $this->dispatchBlockEndEvents($node, BlockType::TYPE_ORDERED_LIST);
				} else {
				    $this->markdomHandler->onUnorderedListBlockEnd();
				    $this->dispatchBlockEndEvents($node, BlockType::TYPE_UNORDERED_LIST);
				}
				break;
			case DocumentProcessor::BLOCK_NODE_LIST_DATA:
				break;
			case DocumentProcessor::BLOCK_NODE_LIST_ITEM:
				$this->dispatchBlocksEndEvents();
				$this->markdomHandler->onListItemEnd();
				if (!is_null($node->next())) {
				    $this->markdomHandler->onNextListItem();
				}
				break;
			case DocumentProcessor::BLOCK_NODE_PARAGRAPH:
				$this->dispatchContentsEndEvents();
				$this->markdomHandler->onParagraphBlockEnd();
				$this->dispatchBlockEndEvents($node, BlockType::TYPE_PARAGRAPH);
				break;
			case DocumentProcessor::BLOCK_NODE_STRONG:
				$this->dispatchContentsEndEvents();
				$this->markdomHandler->onEmphasisContentEnd(EmphasisLevel::LEVEL_2);
				$this->dispatchContentEndEvents($node, ContentType::TYPE_EMPHASIS);
				break;
			case DocumentProcessor::BLOCK_NODE_THEMATIC_BREAK:
				$this->dispatchBlockEndEvents($node, BlockType::TYPE_DIVISION);
				break;
			default:
				throw new DispatcherException('Block node ' . get_class($node) . ' is unknown');
				break;
		}
    }

    /**
     * @param Node $node
     *
     * @throws DispatcherException
     */
    private function transmitInlineBeginEvent(Node $node)
    {
        switch (get_class($node)) {
			case DocumentProcessor::INLINE_NODE_CODE:
				$this->dispatchContentBeginEvents(ContentType::TYPE_CODE);
				/* @var Code $node */
				$this->markdomHandler->onCodeContent($node->getContent());
				break;
			case DocumentProcessor::INLINE_NODE_HTML_INLINE:
				/* @var HtmlInline $node */
				$this->htmlProcessor->handleInlineHtml($node, $this->markdomHandler);
				break;
			case DocumentProcessor::INLINE_NODE_NEWLINE:
				$this->dispatchContentBeginEvents(ContentType::TYPE_LINE_BREAK);
				/** @var Newline $node */
				$hard = $node->getType() == Newline::HARDBREAK;
				$this->markdomHandler->onLineBreakContent($hard);
				break;
			case DocumentProcessor::INLINE_NODE_TEXT:
				$this->dispatchContentBeginEvents(ContentType::TYPE_TEXT);
				/* @var Text $node */
				$this->markdomHandler->onTextContent($node->getContent());
				break;
			default:
				throw new DispatcherException('Inline node ' . get_class($node) . ' is unknown');
				break;
		}
        $this->recentInlineNode = $node;
    }

    /**
     * @throws DispatcherException
     */
    private function transmitInlineEndEvent()
    {
        if (is_null($this->recentInlineNode)) {
            return;
        }
        switch (get_class($this->recentInlineNode)) {
			case DocumentProcessor::INLINE_NODE_CODE:
				$this->dispatchContentEndEvents($this->recentInlineNode, ContentType::TYPE_CODE);
				break;
			case DocumentProcessor::INLINE_NODE_HTML_INLINE:
				break;
			case DocumentProcessor::INLINE_NODE_NEWLINE:
				$this->dispatchContentEndEvents($this->recentInlineNode, ContentType::TYPE_LINE_BREAK);
				break;
			case DocumentProcessor::INLINE_NODE_TEXT:
				$this->dispatchContentEndEvents($this->recentInlineNode, ContentType::TYPE_TEXT);
				break;
			default:
				throw new DispatcherException('Inline node ' . get_class($this->recentInlineNode) . ' is unknown');
				break;
		}
        $this->recentInlineNode = null;
    }
}

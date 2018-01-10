<?php

namespace Markdom\Dispatcher\EventDispatcher;

use Markdom\Common\BlockType;
use Markdom\Common\ContentType;
use Markdom\HandlerInterface\HandlerInterface;
use Markenwerk\StackUtil\Stack;

/**
 * Class SimpleMarkdomEventDispatcher.
 *
 * @package Markdom\Handler\EventDispatcher
 */
final class SimpleMarkdomEventDispatcher
{
    /**
     * @var HandlerInterface
     */
    private $markdomHandler;

    /**
     * @var Stack
     */
    private $blocksHasChildStack;

    /**
     * @var Stack
     */
    private $listHasChildStack;

    /**
     * @var Stack
     */
    private $contentsHasChildStack;

    /**
     * @var Stack
     */
    private $headingLevelStack;

    /**
     * @var Stack
     */
    private $orderedListStartIndexStack;

    /**
     * @var Stack
     */
    private $emphasisLevelStack;

    /**
     * @var Stack
     */
    private $linkUriStack;

    /**
     * @var Stack
     */
    private $linkTitleStack;

    /**
     * @param HandlerInterface $markdomHandler
     */
    public function __construct(HandlerInterface $markdomHandler)
    {
        $this->blocksHasChildStack = new Stack();
        $this->listHasChildStack = new Stack();
        $this->contentsHasChildStack = new Stack();
        $this->headingLevelStack = new Stack();
        $this->orderedListStartIndexStack = new Stack();
        $this->emphasisLevelStack = new Stack();
        $this->linkUriStack = new Stack();
        $this->linkTitleStack = new Stack();
        $this->markdomHandler = $markdomHandler;
    }

    /**
     * @return void
     */
    public function onDocumentBegin()
    {
        $this->markdomHandler->onDocumentBegin();
        $this->onBlocksBegin();
    }

    /**
     * @return void
     */
    public function onDocumentEnd()
    {
        $this->onBlocksEnd();
        $this->markdomHandler->onDocumentEnd();
    }

    /**
     * @param string $code
     * @param string $hint
     *
     * @return void
     */
    public function onCodeBlock($code, $hint = null)
    {
        $this->onBlockBegin(BlockType::TYPE_CODE);
        $this->markdomHandler->onCodeBlock($code, $hint);
        $this->onBlockEnd(BlockType::TYPE_CODE);
    }

    /**
     * @param string $comment
     *
     * @return void
     */
    public function onCommentBlock($comment)
    {
        $this->onBlockBegin(BlockType::TYPE_COMMENT);
        $this->markdomHandler->onCommentBlock($comment);
        $this->onBlockEnd(BlockType::TYPE_COMMENT);
    }

    /**
     * @return void
     */
    public function onDivisionBlock()
    {
        $this->onBlockBegin(BlockType::TYPE_DIVISION);
        $this->markdomHandler->onDivisionBlock();
        $this->onBlockEnd(BlockType::TYPE_DIVISION);
    }

    /**
     * @param int $level
     *
     * @return void
     */
    public function onHeadingBlockBegin($level)
    {
        $this->onBlockBegin(BlockType::TYPE_HEADING);
        $this->markdomHandler->onHeadingBlockBegin($level);
        $this->onContentsBegin();
        $this->headingLevelStack->push($level);
    }

    /**
     * @return void
     */
    public function onHeadingBlockEnd()
    {
        $this->onContentsEnd();
        $this->markdomHandler->onHeadingBlockEnd($this->headingLevelStack->pop());
        $this->onBlockEnd(BlockType::TYPE_HEADING);
    }

    /**
     * @return void
     */
    public function onUnorderedListBlockBegin()
    {
        $this->onBlockBegin(BlockType::TYPE_UNORDERED_LIST);
        $this->markdomHandler->onUnorderedListBlockBegin();
        $this->markdomHandler->onListItemsBegin();
        $this->listHasChildStack->push(false);
    }

    /**
     * @param int
     *
     * @return void
     */
    public function onOrderedListBlockBegin($startIndex)
    {
        $this->onBlockBegin(BlockType::TYPE_ORDERED_LIST);
        $this->markdomHandler->onOrderedListBlockBegin($startIndex);
        $this->markdomHandler->onListItemsBegin();
        $this->orderedListStartIndexStack->push($startIndex);
        $this->listHasChildStack->push(false);
    }

    /**
     * @return void
     */
    public function onListItemBegin()
    {
        if ($this->listHasChildStack->get() === true) {
            $this->markdomHandler->onNextListItem();
        }
        $this->markdomHandler->onListItemBegin();
        $this->onBlocksBegin();
        $this->listHasChildStack->set(true);
    }

    /**
     * @return void
     */
    public function onListItemEnd()
    {
        $this->onBlocksEnd();
        $this->markdomHandler->onListItemEnd();
    }

    /**
     * @return void
     */
    public function onUnorderedListBlockEnd()
    {
        $this->listHasChildStack->pop();
        $this->markdomHandler->onListItemsEnd();
        $this->markdomHandler->onUnorderedListBlockEnd();
        $this->onBlockEnd(BlockType::TYPE_UNORDERED_LIST);
    }

    /**
     * @return void
     */
    public function onOrderedListBlockEnd()
    {
        $this->listHasChildStack->pop();
        $this->markdomHandler->onListItemsEnd();
        $this->markdomHandler->onOrderedListBlockEnd($this->orderedListStartIndexStack->pop());
        $this->onBlockEnd(BlockType::TYPE_ORDERED_LIST);
    }

    /**
     * @return void
     */
    public function onParagraphBlockBegin()
    {
        $this->onBlockBegin(BlockType::TYPE_PARAGRAPH);
        $this->markdomHandler->onParagraphBlockBegin();
        $this->onContentsBegin();
    }

    /**
     * @return void
     */
    public function onParagraphBlockEnd()
    {
        $this->onContentsEnd();
        $this->markdomHandler->onParagraphBlockEnd();
        $this->onBlockEnd(BlockType::TYPE_PARAGRAPH);
    }

    /**
     * @return void
     */
    public function onQuoteBlockBegin()
    {
        $this->onBlockBegin(BlockType::TYPE_QUOTE);
        $this->markdomHandler->onQuoteBlockBegin();
        $this->onBlocksBegin();
    }

    /**
     * @return void
     */
    public function onQuoteBlockEnd()
    {
        $this->onBlocksEnd();
        $this->markdomHandler->onQuoteBlockEnd();
        $this->onBlockEnd(BlockType::TYPE_QUOTE);
    }

    /**
     * @param string $code
     *
     * @return void
     */
    public function onCodeContent($code)
    {
        $this->onContentBegin(ContentType::TYPE_CODE);
        $this->markdomHandler->onCodeContent($code);
        $this->onContentEnd(ContentType::TYPE_CODE);
    }

    /**
     * @param int $level
     *
     * @return void
     */
    public function onEmphasisContentBegin($level)
    {
        $this->onContentBegin(ContentType::TYPE_EMPHASIS);
        $this->markdomHandler->onEmphasisContentBegin($level);
        $this->onContentsBegin();
        $this->emphasisLevelStack->push($level);
    }

    /**
     * @return void
     */
    public function onEmphasisContentEnd()
    {
        $this->onContentsEnd();
        $this->markdomHandler->onEmphasisContentEnd($this->emphasisLevelStack->pop());
        $this->onContentEnd(ContentType::TYPE_EMPHASIS);
    }

    /**
     * @param string $uri
     * @param string $title
     * @param string $alternative
     *
     * @return void
     */
    public function onImageContent($uri, $title = null, $alternative = null)
    {
        $this->onContentBegin(ContentType::TYPE_IMAGE);
        $this->markdomHandler->onImageContent($uri, $title, $alternative);
        $this->onContentEnd(ContentType::TYPE_IMAGE);
    }

    /**
     * @param bool $hard
     *
     * @return void
     */
    public function onLineBreakContent($hard)
    {
        $this->onContentBegin(ContentType::TYPE_LINE_BREAK);
        $this->markdomHandler->onLineBreakContent($hard);
        $this->onContentEnd(ContentType::TYPE_LINE_BREAK);
    }

    /**
     * @param string $uri
     * @param string $title
     *
     * @return void
     */
    public function onLinkContentBegin($uri, $title)
    {
        $this->onContentBegin(ContentType::TYPE_LINK);
        $this->markdomHandler->onLinkContentBegin($uri, $title);
        $this->onContentsBegin();
        $this->linkUriStack->push($uri);
        $this->linkTitleStack->push($title);
    }

    /**
     * @return void
     */
    public function onLinkContentEnd()
    {
        $this->onContentsEnd();
        $this->markdomHandler->onLinkContentEnd($this->linkUriStack->pop(), $this->linkTitleStack->pop());
        $this->onContentEnd(ContentType::TYPE_LINK);
    }

    /**
     * @param string $text
     *
     * @return void
     */
    public function onTextContent($text)
    {
        $this->onContentBegin(ContentType::TYPE_TEXT);
        $this->markdomHandler->onTextContent($text);
        $this->onContentEnd(ContentType::TYPE_TEXT);
    }

    /**
     * @return void
     */
    private function onBlocksBegin()
    {
        $this->markdomHandler->onBlocksBegin();
        $this->blocksHasChildStack->push(false);
    }

    /**
     * @return void
     */
    private function onBlocksEnd()
    {
        $this->blocksHasChildStack->pop();
        $this->markdomHandler->onBlocksEnd();
    }

    /**
     * @param string
     *
     * @return void
     */
    private function onBlockBegin($type)
    {
        if ($this->blocksHasChildStack->get() === true) {
            $this->markdomHandler->onNextBlock();
        }
        $this->markdomHandler->onBlockBegin($type);
        $this->blocksHasChildStack->set(true);
    }

    /**
     * @param string
     *
     * @return void
     */
    private function onBlockEnd($type)
    {
        $this->markdomHandler->onBlockEnd($type);
    }

    /**
     * @return void
     */
    private function onContentsBegin()
    {
        $this->markdomHandler->onContentsBegin();
        $this->contentsHasChildStack->push(false);
    }

    /**
     * @return void
     */
    private function onContentsEnd()
    {
        $this->contentsHasChildStack->pop();
        $this->markdomHandler->onContentsEnd();
    }

    /**
     * @param string
     *
     * @return void
     */
    private function onContentBegin($type)
    {
        if ($this->contentsHasChildStack->get() === true) {
            $this->markdomHandler->onNextContent();
        }
        $this->markdomHandler->onContentBegin($type);
        $this->contentsHasChildStack->set(true);
    }

    /**
     * @param string
     *
     * @return void
     */
    private function onContentEnd($type)
    {
        $this->markdomHandler->onContentEnd($type);
    }
}

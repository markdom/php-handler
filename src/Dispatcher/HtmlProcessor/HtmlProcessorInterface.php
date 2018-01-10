<?php

namespace Markdom\Dispatcher\HtmlProcessor;

use League\CommonMark\Block\Element\HtmlBlock;
use League\CommonMark\Inline\Element\HtmlInline;
use Markdom\HandlerInterface\HandlerInterface;

/**
 * Interface HtmlProcessorInterface.
 *
 * @package Markdom\Dispatcher\HtmlProcessor
 */
interface HtmlProcessorInterface
{
    /**
     * @param HtmlBlock        $htmlBlock
     * @param HandlerInterface $markdomHandler
     *
     * @return void
     */
    public function handleHtmlBlock(HtmlBlock $htmlBlock, HandlerInterface $markdomHandler);

    /**
     * @param HtmlInline       $htmlInline
     * @param HandlerInterface $markdomHandler
     *
     * @return void
     */
    public function handleInlineHtml(HtmlInline $htmlInline, HandlerInterface $markdomHandler);
}

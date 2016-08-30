<?php

namespace Markdom\Dispatcher\HtmlProcessor;

use League\CommonMark\Block\Element\HtmlBlock;
use League\CommonMark\Inline\Element\HtmlInline;
use Markdom\Common\BlockType;
use Markdom\Common\ContentType;
use Markdom\HandlerInterface\HandlerInterface;

/**
 * Class HtmlTextProcessor
 *
 * @package Markdom\Dispatcher\HtmlProcessor
 */
class HtmlTextProcessor implements HtmlProcessorInterface
{

	/**
	 * @param HtmlBlock $htmlBlock
	 * @param HandlerInterface $markdomHandler
	 * @return void
	 */
	public function handleHtmlBlock(HtmlBlock $htmlBlock, HandlerInterface $markdomHandler)
	{
		$plaintext = strip_tags($htmlBlock->getStringContent());
		$markdomHandler->onBlockBegin(BlockType::TYPE_PARAGRAPH);
		$markdomHandler->onParagraphBlockBegin();
		$markdomHandler->onContentsBegin();
		$markdomHandler->onContentBegin(ContentType::TYPE_TEXT);
		$markdomHandler->onTextContent($plaintext);
		$markdomHandler->onContentEnd(ContentType::TYPE_TEXT);
		$markdomHandler->onContentsEnd();
		$markdomHandler->onParagraphBlockEnd();
		$markdomHandler->onBlockEnd(BlockType::TYPE_PARAGRAPH);
		// FIXME: Why the hack does the CommonmarkDispatcher not add a blank line after this paragraph?!
	}

	/**
	 * @param HtmlInline $htmlInline
	 * @param HandlerInterface $markdomHandler
	 * @return void
	 */
	public function handleInlineHtml(HtmlInline $htmlInline, HandlerInterface $markdomHandler)
	{
	}

}

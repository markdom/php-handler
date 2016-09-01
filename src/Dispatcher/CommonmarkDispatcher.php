<?php

namespace Markdom\Dispatcher;

use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use Markdom\Dispatcher\CommonmarkUtil\DocumentProcessor;
use Markdom\Dispatcher\Exception\DispatcherException;
use Markdom\Dispatcher\HtmlProcessor\HtmlProcessorInterface;
use Markdom\HandlerInterface\HandlerInterface;

/**
 * Class CommonmarkDispatcher
 *
 * @package Markdom\Dispatcher
 */
class CommonmarkDispatcher extends AbstractDispatcher
{

	/**
	 * @var HandlerInterface
	 */
	private $markdomHandler;

	/**
	 * @var HtmlProcessorInterface
	 */
	private $htmlProcessor = null;

	/**
	 * Parser constructor.
	 *
	 * @param HandlerInterface $commonmarkHandler
	 */
	public function __construct(HandlerInterface $commonmarkHandler)
	{
		$this->markdomHandler = $commonmarkHandler;
	}

	/**
	 * @return HtmlProcessorInterface
	 */
	public function getHtmlProcessor()
	{
		return $this->htmlProcessor;
	}

	/**
	 * @param HtmlProcessorInterface $htmlProcessor
	 * @return $this
	 */
	public function setHtmlProcessor($htmlProcessor)
	{
		$this->htmlProcessor = $htmlProcessor;
		return $this;
	}

	/**
	 * @param string $sourceFile
	 * @return $this
	 * @throws DispatcherException
	 */
	public function processFile($sourceFile)
	{
		if (!file_exists($sourceFile)) {
			throw new DispatcherException('Source file not found');
		}
		if (!is_readable($sourceFile)) {
			throw new DispatcherException('Source file not readable');
		}
		return $this->process(file_get_contents($sourceFile));
	}

	/**
	 * @param string $source
	 * @return $this
	 */
	public function process($source)
	{
		$commonMarkEnvironment = Environment::createCommonMarkEnvironment();
		$commonMarkEnvironment->addDocumentProcessor(
			new DocumentProcessor($this->markdomHandler, $this->getDispatchCommentBlocks(), $this->htmlProcessor)
		);
		$docParser = new DocParser($commonMarkEnvironment);
		$docParser->parse($source);
		return $this;
	}

}

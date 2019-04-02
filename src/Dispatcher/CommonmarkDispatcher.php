<?php

declare(strict_types=1);

namespace Markdom\Dispatcher;

use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use Markdom\Dispatcher\CommonmarkUtil\DocumentProcessor;
use Markdom\Dispatcher\HtmlProcessor\HtmlProcessorInterface;
use Markdom\DispatcherInterface\DispatcherInterface;
use Markdom\HandlerInterface\HandlerInterface;

/**
 * Class CommonmarkDispatcher
 *
 * @package Markdom\Dispatcher
 */
class CommonmarkDispatcher implements DispatcherInterface
{

	/**
	 * @var HtmlProcessorInterface
	 */
	private $htmlProcessor = null;

	/**
	 * @var string
	 */
	private $commonmarkString;

	/**
	 * CommonmarkDispatcher constructor.
	 *
	 * @param string $commonmarkString
	 */
	public function __construct(string $commonmarkString)
	{
		$this->commonmarkString = $commonmarkString;
	}

	/**
	 * @return HtmlProcessorInterface
	 */
	public function getHtmlProcessor(): HtmlProcessorInterface
	{
		return $this->htmlProcessor;
	}

	/**
	 * @param HtmlProcessorInterface $htmlProcessor
	 * @return $this
	 */
	public function setHtmlProcessor(HtmlProcessorInterface $htmlProcessor)
	{
		$this->htmlProcessor = $htmlProcessor;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isReusable(): bool
	{
		return true;
	}

	/**
	 * @param HandlerInterface $markdomHandler
	 * @return mixed
	 */
	public function dispatchTo(HandlerInterface $markdomHandler)
	{
		$commonMarkEnvironment = Environment::createCommonMarkEnvironment();
		$commonMarkEnvironment->addDocumentProcessor(
			new DocumentProcessor($markdomHandler, $this->htmlProcessor)
		);
		$docParser = new DocParser($commonMarkEnvironment);
		$docParser->parse($this->commonmarkString);
		return $markdomHandler->getResult();
	}

}

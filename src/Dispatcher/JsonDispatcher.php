<?php

namespace Markdom\Dispatcher;

use Markdom\Dispatcher\Exception\DispatcherException;

/**
 * Class JsonDispatcher
 *
 * @package Markdom\Dispatcher
 */
class JsonDispatcher extends PhpObjectDispatcher
{

	/**
	 * @param string $sourceFile
	 * @return $this
	 * @throws DispatcherException
	 */
	public function parseFile($sourceFile)
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
	 * @throws DispatcherException
	 */
	public function process($source)
	{
		$markdomObject = json_decode($source);
		parent::process($markdomObject);
		return $this;
	}

}

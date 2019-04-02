<?php

declare(strict_types=1);

namespace Markdom\Dispatcher;

/**
 * Class JsonDispatcher
 *
 * @package Markdom\Dispatcher
 */
class JsonDispatcher extends PhpObjectDispatcher
{

	/**
	 * JsonDispatcher constructor.
	 *
	 * @param string $jsonString
	 */
	public function __construct(string $jsonString)
	{
		$markdomObject = json_decode($jsonString);
		parent::__construct($markdomObject);
	}

}

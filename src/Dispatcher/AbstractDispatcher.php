<?php

namespace Markdom\Dispatcher;

use Markdom\DispatcherInterface\DispatcherInterface;

/**
 * Class AbstractDispatcher
 *
 * @package Markdom\Dispatcher
 */
abstract class AbstractDispatcher implements DispatcherInterface
{

	/**
	 * @var bool
	 */
	private $dispatchCommentBlocks = true;

	/**
	 * @return boolean
	 */
	public function getDispatchCommentBlocks()
	{
		return $this->dispatchCommentBlocks;
	}

	/**
	 * @param boolean $dispatchCommentBlocks
	 * @return $this
	 */
	public function setDispatchCommentBlocks($dispatchCommentBlocks)
	{
		$this->dispatchCommentBlocks = $dispatchCommentBlocks;
		return $this;
	}

}

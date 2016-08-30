<?php

namespace Markdom\Handler\CommonmarkUtil;

/**
 * Class HandlerDelimiter
 *
 * @package Markdom\Handler\CommonmarkUtil
 */
class HandlerDelimiter
{

	/**
	 * @var string
	 */
	private $literal;

	/**
	 * @var bool
	 */
	private $empty = true;

	/**
	 * MarkdownHandlerDelimiter constructor.
	 *
	 * @param string $literal
	 */
	public function __construct($literal)
	{
		$this->literal = $literal;
	}

	/**
	 * @return string
	 */
	public function getLiteral()
	{
		return $this->literal;
	}

	/**
	 * @return boolean
	 */
	public function isEmpty()
	{
		return $this->empty;
	}

	/**
	 * @param boolean $empty
	 * @return $this
	 */
	public function setEmpty($empty)
	{
		$this->empty = $empty;
		return $this;
	}

}

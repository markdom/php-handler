<?php

declare(strict_types=1);

namespace Markdom\Handler\CommonmarkUtil;

/**
 * Class HandlerDelimiter
 *
 * @package Markdom\Handler\CommonmarkUtil
 */
final class HandlerDelimiter
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
	public function __construct(string $literal)
	{
		$this->literal = $literal;
	}

	/**
	 * @return string
	 */
	public function getLiteral(): string
	{
		return $this->literal;
	}

	/**
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return $this->empty;
	}

	/**
	 * @param bool $empty
	 * @return $this
	 */
	public function setEmpty(bool $empty)
	{
		$this->empty = $empty;
		return $this;
	}

}

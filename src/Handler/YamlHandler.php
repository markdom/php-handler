<?php

declare(strict_types=1);

namespace Markdom\Handler;

/**
 * Class YamlHandler
 *
 * @package Markdom\Handler
 */
class YamlHandler extends PhpObjectHandler
{

	/**
	 * @var bool
	 */
	private $prettyPrint = false;

	/**
	 * @var bool
	 */
	private $wordWrap = false;

	/**
	 * @return bool
	 */
	public function getPrettyPrint(): bool
	{
		return $this->prettyPrint;
	}

	/**
	 * @param bool $prettyPrint
	 * @return $this
	 */
	public function setPrettyPrint(bool $prettyPrint)
	{
		$this->prettyPrint = $prettyPrint;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getWordWrap(): bool
	{
		return $this->wordWrap;
	}

	/**
	 * @param bool $wordWrap
	 * @return $this
	 */
	public function setWordWrap(bool $wordWrap)
	{
		$this->wordWrap = $wordWrap;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getResult(): string
	{
		$indent = ($this->prettyPrint !== false) ? 4 : false;
		/* @noinspection PhpUndefinedClassInspection */
		$yaml = new \Spyc();
		/* @noinspection PhpParamsInspection */
		return $yaml->YAMLDump(parent::getResult(), $indent, $this->getWordWrap());
	}

}

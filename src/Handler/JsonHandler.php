<?php

declare(strict_types=1);

namespace Markdom\Handler;

/**
 * Class JsonHandler
 *
 * @package Markdom\Handler
 */
class JsonHandler extends PhpObjectHandler
{

	/**
	 * @var bool
	 */
	private $prettyPrint = false;

	/**
	 * @var bool
	 */
	private $escapeUnicode = false;

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
	public function getEscapeUnicode(): bool
	{
		return $this->escapeUnicode;
	}

	/**
	 * @param bool $escapeUnicode
	 * @return $this
	 */
	public function setEscapeUnicode(bool $escapeUnicode)
	{
		$this->escapeUnicode = $escapeUnicode;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getResult(): string
	{
		if ($this->prettyPrint) {
			if ($this->escapeUnicode) {
				$options = JSON_PRETTY_PRINT;
			} else {
				$options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
			}
			return json_encode(parent::getResult(), $options);
		}
		if ($this->escapeUnicode) {
			return json_encode(parent::getResult());
		}
		return json_encode(parent::getResult(), JSON_UNESCAPED_UNICODE);
	}

}

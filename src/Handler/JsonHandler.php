<?php

namespace Markdom\Handler;

use Markenwerk\JsonPrettyPrinter\JsonPrettyPrinter;

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
	private $prettyPrint;

	/**
	 * @var bool
	 */
	private $escapeUnicode;

	/**
	 * JsonMarkdomHandler constructor.
	 *
	 * @param bool $prettyPrint
	 * @param bool $escapeUnicode
	 */
	public function __construct($prettyPrint = false, $escapeUnicode = false)
	{
		$this->prettyPrint = $prettyPrint;
		$this->escapeUnicode = $escapeUnicode;
	}

	/**
	 * @return string
	 */
	public function getResult()
	{
		if ($this->prettyPrint) {
			if (phpversion() && phpversion() >= 5.4) {
				if ($this->escapeUnicode) {
					$options = JSON_PRETTY_PRINT;
				} else {
					$options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
				}
				return json_encode(parent::getResult(), $options);
			}
			if ($this->escapeUnicode) {
				$jsonString = json_encode(parent::getResult());
			} else {
				$jsonString = json_encode(parent::getResult(), JSON_UNESCAPED_UNICODE);
			}
			$prettyPrinter = new JsonPrettyPrinter();
			return $prettyPrinter
				->setIndentationString('  ')
				->prettyPrint($jsonString);
		}
		if ($this->escapeUnicode) {
			return json_encode(parent::getResult());
		}
		return json_encode(parent::getResult(), JSON_UNESCAPED_UNICODE);
	}

}

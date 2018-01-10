<?php

namespace Markdom\Handler;

use Markenwerk\JsonPrettyPrinter\JsonPrettyPrinter;

/**
 * Class JsonHandler.
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
    public function getPrettyPrint()
    {
        return $this->prettyPrint;
    }

    /**
     * @param bool $prettyPrint
     *
     * @return $this
     */
    public function setPrettyPrint($prettyPrint)
    {
        $this->prettyPrint = $prettyPrint;

        return $this;
    }

    /**
     * @return bool
     */
    public function getEscapeUnicode()
    {
        return $this->escapeUnicode;
    }

    /**
     * @param bool $escapeUnicode
     *
     * @return $this
     */
    public function setEscapeUnicode($escapeUnicode)
    {
        $this->escapeUnicode = $escapeUnicode;

        return $this;
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

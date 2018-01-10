<?php

namespace Markdom\Handler;

/**
 * Class YamlHandler.
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
    public function getWordWrap()
    {
        return $this->wordWrap;
    }

    /**
     * @param bool $wordWrap
     *
     * @return $this
     */
    public function setWordWrap($wordWrap)
    {
        $this->wordWrap = $wordWrap;

        return $this;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        $indent = ($this->prettyPrint !== false) ? 4 : false;
        /** @noinspection PhpUndefinedClassInspection */
        $yaml = new \Spyc();
        /* @noinspection PhpParamsInspection */
        return $yaml->YAMLDump(parent::getResult(), $indent, $this->getWordWrap());
    }
}

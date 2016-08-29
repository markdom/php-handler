<?php

namespace Markdom\Handler;

use Markdom\Handler\HtmlTagBuilder\XhtmlTagBuilder;

/**
 * Class XhtmlHandler
 *
 * @package Markdom\Handler
 */
class XhtmlHandler extends HtmlHandler
{

	/**
	 * XhtmlHandler constructor.
	 */
	public function __construct()
	{
		$this->tagBuilder = new XhtmlTagBuilder();
		parent::__construct();
	}

}

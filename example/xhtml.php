<?php

namespace Markdom\Test;

use Markdom\Dispatcher\JsonDispatcher;
use Markdom\Handler\XhtmlHandler;

require_once(__DIR__ . '/../vendor/autoload.php');

$handler = new XhtmlHandler();
$handler
	->setEscapeHtml(true)
	->setBreakSoftBreaks(true);
$dispatcher = new JsonDispatcher($handler);
$dispatcher->processFile(__DIR__ . '/example-data.json');
fwrite(STDOUT, $handler->getResult());

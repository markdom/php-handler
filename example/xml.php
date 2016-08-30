<?php

namespace Markdom\Test;

use Markdom\Dispatcher\JsonDispatcher;
use Markdom\Handler\XmlHandler;

require_once(__DIR__ . '/../vendor/autoload.php');

$handler = new XmlHandler();
$handler
	->setPrettyPrint(true);
$dispatcher = new JsonDispatcher($handler);
$dispatcher->processFile(__DIR__ . '/example-data.json');
fwrite(STDOUT, $handler->getResult()->saveXML());

<?php

namespace Markdom\Test;

use Markdom\Dispatcher\JsonDispatcher;
use Markdom\Handler\JsonHandler;

require_once(__DIR__ . '/../vendor/autoload.php');

$handler = new JsonHandler();
$handler
	->setPrettyPrint(true)
	->setEscapeUnicode(true);
$dispatcher = new JsonDispatcher(file_get_contents(__DIR__ . '/example-data.json'));
$dispatcher->dispatchTo($handler);
fwrite(STDOUT, $handler->getResult());

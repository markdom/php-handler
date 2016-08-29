<?php

namespace Markdom\Test;

use Markdom\Dispatcher\JsonDispatcher;
use Markdom\Handler\DebugHandler;

require_once(__DIR__ . '/../vendor/autoload.php');

$handler = new DebugHandler();
$dispatcher = new JsonDispatcher($handler);
$dispatcher->parseFile(__DIR__ . '/example-data.json');
fwrite(STDOUT, $handler->getResult());

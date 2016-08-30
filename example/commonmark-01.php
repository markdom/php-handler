<?php

namespace Markdom\Test;

use Markdom\Dispatcher\JsonDispatcher;
use Markdom\Handler\CommonmarkHandler;

require_once(__DIR__ . '/../vendor/autoload.php');

$handler = new CommonmarkHandler();
$dispatcher = new JsonDispatcher($handler);
$dispatcher->processFile(__DIR__ . '/example-data-simple.json');
fwrite(STDOUT, $handler->getResult());
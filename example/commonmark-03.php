<?php

namespace Markdom\Test;

use Markdom\Dispatcher\CommonmarkDispatcher;
use Markdom\Dispatcher\JsonDispatcher;
use Markdom\Handler\CommonmarkHandler;

require_once(__DIR__ . '/../vendor/autoload.php');

$handler = new CommonmarkHandler();
$dispatcher = new JsonDispatcher(file_get_contents(__DIR__ . '/example-data.json'));
$dispatcher->dispatchTo($handler);
fwrite(STDOUT, $handler->getResult());

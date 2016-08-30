<?php

namespace Markdom\Test;

use Markdom\Dispatcher\CommonmarkDispatcher;
use Markdom\Handler\CommonmarkHandler;

require_once(__DIR__ . '/../vendor/autoload.php');

$handler = new CommonmarkHandler();
$dispatcher = new CommonmarkDispatcher($handler);
$dispatcher->processFile(__DIR__ . '/result.md');
fwrite(STDOUT, $handler->getResult());

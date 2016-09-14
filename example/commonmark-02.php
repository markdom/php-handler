<?php

namespace Markdom\Test;

use Markdom\Dispatcher\CommonmarkDispatcher;
use Markdom\Handler\CommonmarkHandler;

require_once(__DIR__ . '/../vendor/autoload.php');

$handler = new CommonmarkHandler();
$dispatcher = new CommonmarkDispatcher(file_get_contents(__DIR__ . '/example-data.md'));
$dispatcher->dispatchTo($handler);
fwrite(STDOUT, $handler->getResult());

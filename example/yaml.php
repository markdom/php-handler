<?php

declare(strict_types=1);

namespace Markdom\Test;

use Markdom\Dispatcher\JsonDispatcher;
use Markdom\Handler\YamlHandler;

require_once(__DIR__ . '/../vendor/autoload.php');

$handler = new YamlHandler();
$handler
	->setPrettyPrint(false)
	->setWordWrap(false);
$dispatcher = new JsonDispatcher(file_get_contents(__DIR__ . '/example-data.json'));
$dispatcher->dispatchTo($handler);
fwrite(STDOUT, $handler->getResult());

<?php

use firegate666\Logger\ConsoleLogger;
use firegate666\Daemon\Configuration;
use firegate666\Daemon\Daemon;
use firegate666\Daemon\Example\HandlerFactory;

declare(ticks = 1);

require_once __DIR__ . '/vendor/autoload.php';

$logger = new ConsoleLogger();

$handler_factory = new HandlerFactory();
$handler_factory->setLogger($logger);

$configuration = new Configuration();

$daemon = new Daemon($handler_factory, $configuration);
$daemon->setLogger($logger);

$daemon->run();

<?php

namespace firegate666\Daemon;

use Psr\Log\LoggerInterface;

interface HandlerFactoryInterface
{
    /**
     * @return HandlerInterface
     */
    public function create();

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger);
}

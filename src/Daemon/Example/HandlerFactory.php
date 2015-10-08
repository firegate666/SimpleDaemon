<?php

namespace firegate666\Daemon\Example;

use firegate666\Daemon\HandlerFactoryInterface;
use firegate666\Daemon\HandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Example daemon handler factory
 */
class HandlerFactory implements HandlerFactoryInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return HandlerInterface
     */
    public function create()
    {
        $handler = new Handler();
        $handler->setLogger($this->logger);

        return $handler;
    }
}

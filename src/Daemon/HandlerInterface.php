<?php

namespace firegate666\Daemon;

use Psr\Log\LoggerInterface;

interface HandlerInterface
{
    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger);

    /**
     *
     */
    public function runLoop();

    /**
     * @param int $signo
     */
    public function sigHandler($signo);
}

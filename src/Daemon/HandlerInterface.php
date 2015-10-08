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
     * This is the working loop of the daemon, implement your logic here
     */
    public function runLoop();

    /**
     * When the daemon goes down, he sending SIGTERM to his children, implement a proper handling
     *
     * @param int $signo
     */
    public function sigHandler($signo);
}

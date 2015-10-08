<?php

declare(ticks = 1);

namespace firegate666\Daemon\Example;

use firegate666\Daemon\HandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Example daemon handler
 */
class Handler implements HandlerInterface
{

    /** @var LoggerInterface */
    protected $logger;

    /** @var bool */
    protected $shutdown = false;

    /**
     * constructor initializes the handler
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Main working loop of the daemon
     * This example daemon does a sleep 1 every time
     */
    public function runLoop()
    {
        while (!$this->shutdown) {
            pcntl_signal_dispatch();
            sleep(1);
        }
    }

    /**
     * Send a shutdown log entry and switch to shutdown mode, will quit the loop
     */
    protected function shutdown()
    {
        $this->log(LogLevel::WARNING, 'child shutdown received');
        $this->shutdown = true;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    protected function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * This one is responsible for handling all incoming signals.
     * Has a handling for SIGTERM and SIGINT, initializes shutdown.
     *
     * @param int $signo
     */
    public function sigHandler($signo)
    {
        switch ($signo) {
            case SIGINT:
            case SIGTERM:
                $this->shutdown();
                break;
        }
    }

    /**
     * basic initializer
     */
    protected function initialize()
    {
        $this->registerSignals();
    }

    /**
     * register signals for SIGTERM and SIGINT
     */
    protected function registerSignals()
    {
        pcntl_signal(SIGINT, [$this, 'sigHandler']);
        pcntl_signal(SIGTERM, [$this, 'sigHandler']);
    }
}

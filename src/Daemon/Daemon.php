<?php

declare(ticks = 1);

namespace firegate666\Daemon;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * This is the main daemon loop, or the so called observer.
 * He is responsible for starting and stopping the handler children
 *
 * @example
 *  $handler_factory = new HandlerFactory();
 *  $configuration = new Configuration();
 *  $daemon = new Daemon($handler_factory, $configuration);
 *  $daemon->run();
 */
class Daemon
{
    /** @var Configuration */
    protected $configuration;

    /** @var HandlerFactoryInterface */
    protected $handler_factory;

    /** @var array */
    protected $childPids = [];

    /** @var bool */
    protected $shutdown = false;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param HandlerFactoryInterface $handler_factory
     * @param Configuration $configuration
     */
    public function __construct(HandlerFactoryInterface $handler_factory, Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->handler_factory = $handler_factory;

        $this->initialize();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    protected function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * The core loop of this daemon
     * Handles forking of daemon handlers up to a defined max and then waits for their termination
     */
    public function run()
    {
        $this->log(LogLevel::INFO, 'daemon started ' . posix_getpid());
        while (!$this->shutdown) {
            pcntl_signal_dispatch();

            if (count($this->childPids) < $this->configuration->numChildren) { // start children until we have enough
                $pid = pcntl_fork();
                if ($pid == -1) {
                    $this->log(LogLevel::ERROR, 'error forking child');
                } else {
                    if ($pid) {
                        $this->log(LogLevel::INFO, 'child forked ' . $pid);
                        $this->childPids[$pid] = $pid;
                    } else {
                        $handler = $this->handler_factory->create();
                        $handler->runLoop();
                        exit();
                    }
                }
            } else {
                $childPid = pcntl_waitpid(-1, $status, WNOHANG);

                if ($childPid) {
                    $this->log(LogLevel::WARNING, "child exit received " . $childPid);
                    unset($this->childPids[$childPid]);
                }

                sleep(1);
            }
        }

        if (count($this->childPids)) {
            $this->log(LogLevel::INFO, 'still ' . count($this->childPids) . ' living children');

            $time = time();

            while (count($this->childPids)) {
                $this->log(LogLevel::INFO, 'waiting for children to terminate: ' . var_export($this->childPids, true));
                pcntl_signal_dispatch();

                $childPid = pcntl_waitpid(-1, $status, WNOHANG);
                $this->onChildExit($childPid);

                if (time() - $time > 5) { // after waiting 5 seconds skip to kill
                    break;
                }

                usleep(100);
            }

            $this->log(LogLevel::INFO, 'trying to kill remaining children: ' . var_export($this->childPids));
            $this->sendSignalToChildren(SIGKILL);
        }

        $this->log(LogLevel::INFO, 'daemon shutdown');
        exit();
    }

    /**
     * handler if a child dies
     * removes the child from the list
     *
     * @param int $childPid
     */
    protected function onChildExit($childPid)
    {
        $this->log(LogLevel::INFO, 'child ' . $childPid . ' exited');
        unset($this->childPids[$childPid]);
    }

    /**
     * switch to shutdown mode, send shutdown to children
     */
    protected function shutdown()
    {
        $this->shutdown = true;
        $this->sendSignalToChildren(SIGTERM);
    }

    /**
     * send signal to all children
     */
    protected function sendSignalToChildren($signo)
    {
        foreach ($this->childPids as $childPid) {
            posix_kill($childPid, $signo);
        }
    }

    /**
     * signal handler for this daemon
     * reacts upon SIGINT, SIGTERM and SIGCHLD
     *
     * @param int $signo
     */
    public function sigHandler($signo)
    {
        $this->log(LogLevel::DEBUG, 'signal received ' . $signo);

        switch ($signo) {
            case SIGINT:
                $this->sigInt();
                break;
            case SIGTERM:
                $this->sigTerm();
                break;
            case SIGCHLD:
                $this->sigChild();
                break;
        }
    }

    /**
     * SIGCHLD handler
     */
    protected function sigChild()
    {
        $this->log(LogLevel::DEBUG, 'sigchild received');

        while (($childPid = pcntl_waitpid(-1, $status, WNOHANG)) > 0) {
            $this->onChildExit($childPid);
        }
    }

    /**
     * SIGINT handler, switch to shutdown
     */
    protected function sigInt()
    {
        $this->log(LogLevel::DEBUG, 'sigint received');
        $this->shutdown();
    }

    /**
     * SIGTERM handler, switch to shutdown
     */
    protected function sigTerm()
    {
        $this->log(LogLevel::DEBUG, 'sigterm received');
        $this->shutdown();
    }

    /**
     * basic initializer
     */
    protected function initialize()
    {
        $this->registerSignals();
    }

    /**
     * register signals
     */
    protected function registerSignals()
    {
        pcntl_signal(SIGINT, [$this, 'sigHandler']);
        pcntl_signal(SIGTERM, [$this, 'sigHandler']);
        pcntl_signal(SIGCHLD, [$this, 'sigHandler']);
    }
}

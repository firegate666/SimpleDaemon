<?php

declare(ticks = 1);

namespace firegate666\Daemon;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Daemon
{
	/** @var Configuration */
	protected $configuration;

	/** @var HandlerInterface */
	protected $handler_factory;

	/** @var array */
	protected $childPids = [];

	/** @var bool */
	protected $shutdown = false;

	/** @var LoggerInterface */
	protected $logger;

	/**
	 * @param HandlerInterface $handler_factory
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

	public function run()
	{
		while (!$this->shutdown) {
			if (count($this->childPids) < $this->configuration->numChildren) { // start children until we have enough
				$pid = pcntl_fork();
				if ($pid == -1) {
					$this->log(LogLevel::ERROR, 'error forking child');
				} else if ($pid) {
					$this->log(LogLevel::INFO, 'child forked ' .$pid);
					print "started child " . $pid . PHP_EOL;
					$this->childPids[$pid] = $pid;
				} else {
					$handler = $this->handler_factory->create();
					$handler->runLoop();
					exit();
				}
			} else {
				$childPid = pcntl_waitpid (-1, $status);
				$this->log(LogLevel::WARNING, "child exit received " . $childPid);
				unset($this->childPids[$childPid]);
				sleep(1);
			}
		}

		$this->log(LogLevel::INFO, 'waiting for children to terminate');

		while (count($this->childPids)) {
			$childPid = pcntl_waitpid (-1, $status);
			$this->onChildExit($childPid);
		}

		exit();
	}

	protected function onChildExit($childPid)
	{
		$this->log(LogLevel::INFO, 'child ' . $childPid . ' exited');
		unset($this->childPids[$childPid]);
	}

	protected function shutdown()
	{
		$this->shutdown = true;

		foreach ($this->childPids as $childPid) {
			posix_kill($childPid, SIGTERM);
		}
	}

	/**
	 * @param int $signo
	 */
	public function sigHandler($signo)
	{
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

	protected function sigChild()
	{
		$this->log(LogLevel::DEBUG, 'sigchild received');

		while(($childPid = pcntl_wait($status, WNOHANG)) > 0) {
			$this->onChildExit($childPid);
		}
	}

	protected function sigInt()
	{
		$this->log(LogLevel::DEBUG, 'sigint received');
		$this->shutdown();
	}

	protected function sigTerm()
	{
		$this->log(LogLevel::DEBUG, 'sigterm received');
		$this->shutdown();
	}

	protected function initialize()
	{
		$this->registerSignals();
	}

	protected function registerSignals()
	{
		pcntl_signal(SIGINT, [$this, 'sigHandler']);
		pcntl_signal(SIGTERM, [$this, 'sigHandler']);
		pcntl_signal(SIGCHLD, [$this, 'sigHandler']);
	}
}

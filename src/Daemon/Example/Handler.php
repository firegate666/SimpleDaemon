<?php

declare(ticks = 1);

namespace firegate666\Daemon\Example;

use firegate666\Daemon\HandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Handler implements HandlerInterface
{

	/** @var LoggerInterface */
	protected $logger;

	/** @var bool */
	protected $shutdown = false;

	/**
	 *
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
	 *
	 */
	public function runLoop()
	{
		while (!$this->shutdown) {
			pcntl_signal_dispatch();
			sleep(1);
		}
	}

	/**
	 *
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
	 *
	 */
	protected function initialize()
	{
		$this->registerSignals();
	}

	/**
	 *
	 */
	protected function registerSignals()
	{
		pcntl_signal(SIGINT, [$this, 'sigHandler']);
		pcntl_signal(SIGTERM, [$this, 'sigHandler']);
	}
}

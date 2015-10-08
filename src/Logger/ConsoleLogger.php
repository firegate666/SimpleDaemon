<?php

namespace firegate666\Logger;

/**
 * Simple implementation of logger interface, just logging everything to console, log level prepended
 */
class ConsoleLogger extends AbstractSimpleLogger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        print $level . ': ' . $message . PHP_EOL;
    }
}

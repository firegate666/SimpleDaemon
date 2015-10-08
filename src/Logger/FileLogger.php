<?php

namespace firegate666\Logger;

/**
 * Simple implementation of logger interface, just logging everything to a file, log level prepended
 */
class FileLogger extends AbstractSimpleLogger
{

    /** @var string */
    protected $filename;

    /** @var bool */
    private $directoryCreated = false;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

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
        $this->createLogDir();
        file_put_contents($this->filename, $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * create log directory
     */
    protected function createLogDir()
    {
        if (!$this->directoryCreated) {
            $dir = dirname($this->filename);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $this->directoryCreated = true;
        }
    }
}

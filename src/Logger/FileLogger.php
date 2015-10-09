<?php

namespace firegate666\Logger;

/**
 * Simple implementation of logger interface, just logging everything to a file, log level prepended
 */
class FileLogger extends AbstractSimpleLogger
{

    /** @var string */
    protected $filename;

    /** @var int */
    protected $filePermissions;

    /** @var int */
    protected $directoryPermissions;

    /** @var bool */
    private $directoryCreated = false;

    /** @var bool */
    private $fileCreated = false;

    /**
     * @param string $filename
     * @param int $directoryPermissions
     * @param int $filePermissions
     */
    public function __construct($filename, $directoryPermissions = 0755, $filePermissions = 0644)
    {
        $this->filename = $filename;
        $this->directoryPermissions = $directoryPermissions;
        $this->filePermissions = $filePermissions;
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
        $this->createLogDirIfNotExists();
        $this->createFileIfNotExists();
        file_put_contents($this->filename, $this->wrapMessage($level, $message), FILE_APPEND);
    }

    /**
     * create log directory
     */
    protected function createLogDirIfNotExists()
    {
        if (!$this->directoryCreated) {
            $dir = dirname($this->filename);
            if (!is_dir($dir)) {
                mkdir($dir, $this->directoryPermissions, true);
            }

            $this->directoryCreated = true;
        }
    }

    /**
     * create logfile if not exists and set permissions
     */
    private function createFileIfNotExists()
    {
        if (!$this->fileCreated) {
            if (!file_exists($this->filename)) {
                touch($this->filename);
                chmod($this->filename, $this->filePermissions);
            }

            $this->fileCreated = true;
        }
    }
}

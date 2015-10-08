<?php

namespace firegate666\Daemon;

/**
 * Configuration for the daemon
 */
class Configuration
{
    /**
     * How many children should the daemon fork and maintain
     *
     * @var int
     */
    public $numChildren = 4;
}

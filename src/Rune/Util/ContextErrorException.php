<?php

namespace uuf6429\Rune\Util;

/**
 * Error Exception with Variable Context.
 *
 * @author Christian Sciberras <uuf6429@gmail.com>
 */
class ContextErrorException extends \ErrorException
{
    private $context = array();

    public function __construct($message, $code, $severity, $filename, $lineno, $context = array())
    {
        parent::__construct($message, $code, $severity, $filename, $lineno);
        $this->context = $context;
    }

    /**
     * @return array Array of variables that existed when the exception occurred
     */
    public function getContext()
    {
        return $this->context;
    }
}

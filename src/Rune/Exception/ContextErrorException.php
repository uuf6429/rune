<?php

namespace uuf6429\Rune\Exception;

/**
 * Error Exception with Variable Context.
 *
 * @author Christian Sciberras <uuf6429@gmail.com>
 */
class ContextErrorException extends \ErrorException
{
    /**
     * @var array
     */
    private $context;

    public function __construct($message, $code, $severity, $filename, $lineno, $context = [])
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

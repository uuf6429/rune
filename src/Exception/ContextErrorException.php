<?php

namespace uuf6429\Rune\Exception;

use ErrorException;

class ContextErrorException extends ErrorException
{
    private array $context;

    public function __construct($message, $code, $severity, $filename, $lineno, array $context = [])
    {
        parent::__construct($message, $code, $severity, $filename, $lineno);

        $this->context = $context;
    }

    /**
     * @return array Array of variables that existed when the exception occurred
     */
    public function getContext(): array
    {
        return $this->context;
    }
}

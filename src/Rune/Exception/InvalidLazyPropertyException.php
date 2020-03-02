<?php

namespace uuf6429\Rune\Exception;

use RuntimeException;
use Throwable;

class InvalidLazyPropertyException extends RuntimeException
{
    /**
     * @param string $class
     * @param string $method
     * @param string $property
     * @param Throwable|null $previous
     */
    public function __construct(
        string $class,
        string $method,
        string $property,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'Missing property %s and method %s in class %s.',
                $property,
                $method,
                $class
            ),
            0,
            $previous
        );
    }
}

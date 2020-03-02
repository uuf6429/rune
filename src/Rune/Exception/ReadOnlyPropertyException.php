<?php

namespace uuf6429\Rune\Exception;

use RuntimeException;
use Throwable;

class ReadOnlyPropertyException extends RuntimeException
{
    /**
     * @param string $class
     * @param string $name
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $class,
        string $name,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'Property %s in class %s is read only and cannot be set.',
                $name,
                $class
            ),
            0,
            $previous
        );
    }
}

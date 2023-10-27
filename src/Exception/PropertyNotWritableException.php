<?php declare(strict_types=1);

namespace uuf6429\Rune\Exception;

use RuntimeException;
use Throwable;

class PropertyNotWritableException extends RuntimeException
{
    private string $class;
    private string $property;

    public function __construct(string $class, string $property, ?Throwable $previous = null)
    {
        parent::__construct("Property $property in class $class is not writable", 0, $previous);

        $this->class = $class;
        $this->property = $property;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}

<?php declare(strict_types=1);

namespace uuf6429\Rune\Exception;

use RuntimeException;
use Throwable;

class MissingGetterException extends RuntimeException
{
    private string $class;
    private string $property;
    private string $getter;

    public function __construct(string $class, string $property, string $getter, ?Throwable $previous = null)
    {
        parent::__construct(
            "Neither property $property nor (getter) method $getter were defined in class $class.",
            0,
            $previous
        );

        $this->class = $class;
        $this->property = $property;
        $this->getter = $getter;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getGetter(): string
    {
        return $this->getter;
    }
}

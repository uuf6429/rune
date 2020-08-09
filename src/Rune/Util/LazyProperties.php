<?php

namespace uuf6429\Rune\Util;

use uuf6429\Rune\Exception\InvalidLazyPropertyException;
use uuf6429\Rune\Exception\ReadOnlyPropertyException;

/**
 * This trait will delay loading of properties, helping in performance. Usage:
 * 1. Use the trait in your class. :)
 * 2. Add '@property' to class PHPDoc for your lazy properties.
 * 3. Add getter methods that initialize and return the actual property value.
 */
trait LazyProperties
{
    /**
     * @var string
     */
    private $methodVerb = 'get';

    /**
     * @var bool
     */
    private $readonlyLock = true;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $method = $this->methodVerb . ucfirst($name);

        if (!method_exists($this, $method)) {
            throw new InvalidLazyPropertyException(get_class($this), $method, $name);
        }

        $result = $this->$method();
        try {
            $this->readonlyLock = false;
            $this->$name = $result;
        } finally {
            $this->readonlyLock = true;
        }

        return $result;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        if ($this->readonlyLock) {
            throw new ReadOnlyPropertyException(get_class($this), $name);
        }

        $this->$name = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        $method = $this->methodVerb . ucfirst($name);

        return method_exists($this, $method) && $this->__get($name) !== null;
    }
}

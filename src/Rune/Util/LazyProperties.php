<?php

namespace uuf6429\Rune\Util;

/**
 * This trait will delay loading of properties, helping in performance. Usage:
 * 1. Use the trait in your class. :)
 * 2. Add '@property' to class PHPDoc for your lazy properties.
 * 3. Add getter methods that initialize and return the actual property value.
 */
trait LazyProperties
{
    /**
     * @var bool
     */
    private $readonlyLock = true;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);

        if (!method_exists($this, $method)) {
            throw new \RuntimeException(
                sprintf(
                    'Missing property %s and method %s in class %s.',
                    $name, $method, get_class($this)
                )
            );
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
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        if ($this->readonlyLock) {
            throw new \RuntimeException(
                sprintf(
                    'Property %s in class %s is read only and cannot be set.',
                    $name, get_class($this)
                )
            );
        }

        $this->$name = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        $method = 'get' . ucfirst($name);

        return method_exists($this, $method) && $this->__get($name) !== null;
    }
}

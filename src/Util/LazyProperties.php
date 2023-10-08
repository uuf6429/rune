<?php

namespace uuf6429\Rune\Util;

use RuntimeException;

/**
 * This trait will delay loading of properties, helping in performance.
 */
trait LazyProperties
{
    private array $lazyPropertyData = [];

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->lazyPropertyData)) {
            return $this->lazyPropertyData[$name];
        }

        $method = 'get' . ucfirst($name);
        if (!method_exists($this, $method)) {
            throw new RuntimeException(
                sprintf('Missing property %s and method %s in class %s.', $name, $method, get_class($this))
            );
        }

        return $this->lazyPropertyData[$name] = $this->$method();
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        throw new RuntimeException(
            sprintf('Property %s in class %s is read only and cannot be set.', $name, get_class($this))
        );
    }

    public function __isset(string $name): bool
    {
        $method = 'get' . ucfirst($name);

        return method_exists($this, $method) && isset($this->lazyPropertyData[$name]);
    }
}

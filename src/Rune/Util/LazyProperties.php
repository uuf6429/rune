<?php

namespace uuf6429\Rune\Util;

/**
 * This trait will delay loading of properties, helping in performance. Usage:
 * 1. Use the trait in your class. :)
 * 2. Add @property to class PHPDoc for your lazy properties.
 * 3. Add getter methods that initialize and return the actual property value.
 */
trait LazyProperties
{
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
        $this->$name = $result;

        return $result;
    }
}

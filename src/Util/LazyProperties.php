<?php declare(strict_types=1);

namespace uuf6429\Rune\Util;

use uuf6429\Rune\Exception\MissingGetterException;
use uuf6429\Rune\Exception\PropertyNotWritableException;

/**
 * This trait will delay loading of properties, helping in performance.
 */
trait LazyProperties
{
    private array $lazyPropertyData = [];

    /**
     * @return mixed
     * @throws MissingGetterException
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->lazyPropertyData)) {
            return $this->lazyPropertyData[$name];
        }

        $method = 'get' . ucfirst($name);
        if (!method_exists($this, $method)) {
            throw new MissingGetterException(static::class, $name, $method);
        }

        return $this->lazyPropertyData[$name] = $this->$method();
    }

    /**
     * @param mixed $value
     * @thorws PropertyNotWritableException
     */
    public function __set(string $name, $value): void
    {
        throw new PropertyNotWritableException(static::class, $name);
    }

    public function __isset(string $name): bool
    {
        try {
            $value = $this->__get($name);
            return isset($value);
        } catch (MissingGetterException $ex) {
            return false;
        }
    }

    public function __unset(string $name): void
    {
        unset($this->lazyPropertyData[$name]);
    }
}

<?php

namespace uuf6429\Rune\Model;

abstract class AbstractModel
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

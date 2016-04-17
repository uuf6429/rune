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
        if (isset($this->$name)) {
            return $this->$name;
        }

        $method = 'get' . ucfirst($name);

        if (!method_exists($this, $method)) {
            throw new \RuntimeException(
                sprintf(
                    'Method %s was expected in class %s.',
                    $method,
                    get_class($this)
                )
            );
        }

        $result = $this->$method();
        $this->$name = $result;

        return $result;
    }
}

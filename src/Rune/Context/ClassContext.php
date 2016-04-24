<?php

namespace uuf6429\Rune\Context;

abstract class ClassContext implements ContextInterface
{
    /**
     * @var ClassContextDescriptor
     */
    private $descriptor;

    /**
     * @return ClassContextDescriptor
     */
    public function getContextDescriptor()
    {
        if (!$this->descriptor) {
            $this->descriptor = new ClassContextDescriptor($this);
        }

        return $this->descriptor;
    }
}

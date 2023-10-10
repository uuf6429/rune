<?php

namespace uuf6429\Rune\Context;

abstract class ClassContext implements ContextInterface
{
    private ?ClassContextDescriptor $descriptor;

    public function getContextDescriptor(): ClassContextDescriptor
    {
        return $this->descriptor ?? ($this->descriptor = new ClassContextDescriptor($this));
    }
}

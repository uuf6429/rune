<?php

namespace uuf6429\Rune\Context;

interface ContextInterface
{
    /**
     * @return AbstractContextDescriptor
     */
    public function getContextDescriptor(): AbstractContextDescriptor;
}

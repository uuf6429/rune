<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Util\TypeInfoMember;

abstract class AbstractContextDescriptor
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @param ContextInterface $context
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    /**
     * @return mixed[string] An array of variables available in the context. Array index is the variable name.
     */
    abstract public function getVariables();

    /**
     * @return callable[string] An array of functions available in the context. Array index is the function name.
     */
    abstract public function getFunctions();

    /**
     * @return TypeInfoMember[] An array of type metadata of all variables and functions available in the context.
     */
    abstract public function getTypeInfo();
}

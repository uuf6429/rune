<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Util\TypeAnalyser;
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
     * @param TypeAnalyser|null $analyser
     *
     * @return TypeInfoMember[] An array of type metadata of all variables available in the context.
     */
    abstract public function getVariableTypeInfo($analyser = null);

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return TypeInfoMember[] An array of type metadata of all functions available in the context.
     */
    abstract public function getFunctionTypeInfo($analyser = null);

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return TypeInfoClass[string] An array of type metadata of all types available in the context.
     */
    abstract public function getDetailedTypeInfo($analyser = null);
}

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
     * @return array<string,mixed> An array of variables available in the context. Array index is the variable name.
     */
    abstract public function getVariables();

    /**
     * @return array<string,callable> An array of functions available in the context. Array index is the function name.
     */
    abstract public function getFunctions();

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return array<string,TypeInfoMember> An array of type metadata of all variables available in the context, indexed by member name.
     */
    abstract public function getVariableTypeInfo($analyser = null);

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return array<string,TypeInfoMember> An array of type metadata of all functions available in the context, indexed by member name.
     */
    abstract public function getFunctionTypeInfo($analyser = null);

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return array<string,TypeInfoClass> An array of type metadata of all types available in the context, indexed by FQN.
     */
    abstract public function getDetailedTypeInfo($analyser = null);
}

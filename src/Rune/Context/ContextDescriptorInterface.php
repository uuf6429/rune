<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Util\TypeAnalyser;
use uuf6429\Rune\Util\TypeInfoCollection;

interface ContextDescriptorInterface
{
    /**
     * @return array<string,mixed> An array of variables available in the context. Array index is the variable name.
     */
    public function getVariables();

    /**
     * @return array<string,callable> An array of functions available in the context. Array index is the function name.
     */
    public function getFunctions();

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return TypeInfoCollection a collection array of type metadata of all variables available in the context
     */
    public function getVariableTypeInfo($analyser = null);

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return TypeInfoCollection a collection of type metadata of all functions available in the context
     */
    public function getFunctionTypeInfo($analyser = null);

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return TypeInfoCollection a collection of type metadata of all types available in the context
     */
    public function getDetailedTypeInfo($analyser = null);
}

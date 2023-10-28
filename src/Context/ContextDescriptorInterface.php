<?php declare(strict_types=1);

namespace uuf6429\Rune\Context;

use uuf6429\Rune\TypeInfo\TypeInfoBase;
use uuf6429\Rune\TypeInfo\TypeInfoClass;

interface ContextDescriptorInterface
{
    /**
     * @return array<string,mixed> An array of variables available in the context. Array index is the variable name.
     */
    public function getVariables(): array;

    /**
     * @return array<string,callable|array{0:object,1:string}> An array of functions available in the context. Array index is the function name.
     */
    public function getFunctions(): array;

    /**
     * @return array<string,TypeInfoBase> an array of type metadata of all variables available in the context, indexed by member name
     */
    public function getVariableTypeInfo(): array;

    /**
     * @return array<string,TypeInfoBase> an array of type metadata of all functions available in the context, indexed by member name
     */
    public function getFunctionTypeInfo(): array;

    /**
     * @return array<string,TypeInfoClass> an array of type metadata of all types available in the context, indexed by FQN
     */
    public function getDetailedTypeInfo(): array;
}

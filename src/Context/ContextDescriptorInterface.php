<?php declare(strict_types=1);

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Util\TypeAnalyser;
use uuf6429\Rune\Util\TypeInfoClass;
use uuf6429\Rune\Util\TypeInfoMember;

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
     * @return array<string,TypeInfoMember> an array of type metadata of all variables available in the context, indexed by member name
     */
    public function getVariableTypeInfo(?TypeAnalyser $analyser = null): array;

    /**
     * @return array<string,TypeInfoMember> an array of type metadata of all functions available in the context, indexed by member name
     */
    public function getFunctionTypeInfo(?TypeAnalyser $analyser = null): array;

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return array<string,TypeInfoClass> an array of type metadata of all types available in the context, indexed by FQN
     */
    public function getDetailedTypeInfo(?TypeAnalyser $analyser = null): array;
}

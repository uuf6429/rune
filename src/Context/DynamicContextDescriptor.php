<?php declare(strict_types=1);

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Util\TypeAnalyser;
use uuf6429\Rune\Util\TypeInfoMember;

class DynamicContextDescriptor extends AbstractContextDescriptor
{
    protected DynamicContext $context;

    public function __construct(DynamicContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables(): array
    {
        return $this->context->getVariables();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return $this->context->getFunctions();
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableTypeInfo(?TypeAnalyser $analyser = null): array
    {
        $result = [];
        foreach ($this->context->getVariables() as $name => $value) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            $result[$name] = new TypeInfoMember($name, [$type]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionTypeInfo(?TypeAnalyser $analyser = null): array
    {
        $result = [];
        foreach (array_keys($this->context->getFunctions()) as $name) {
            $result[$name] = new TypeInfoMember($name, ['callable']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailedTypeInfo(?TypeAnalyser $analyser = null): array
    {
        $analyser = $analyser ?: new TypeAnalyser();

        /** @var TypeInfoMember[] $members */
        $members = array_merge($this->getVariableTypeInfo($analyser), $this->getFunctionTypeInfo($analyser));

        foreach ($members as $member) {
            $analyser->analyse($member->getTypes());
        }

        return $analyser->getTypes();
    }
}

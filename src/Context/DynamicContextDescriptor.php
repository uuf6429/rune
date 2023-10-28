<?php declare(strict_types=1);

namespace uuf6429\Rune\Context;

use uuf6429\Rune\TypeInfo\TypeAnalyser;
use uuf6429\Rune\TypeInfo\TypeInfoMethod;
use uuf6429\Rune\TypeInfo\TypeInfoProperty;

class DynamicContextDescriptor implements ContextDescriptorInterface
{
    protected DynamicContext $context;
    protected TypeAnalyser $analyser;

    public function __construct(DynamicContext $context, ?TypeAnalyser $analyser = null)
    {
        $this->context = $context;
        $this->analyser = $analyser ?: new TypeAnalyser();
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
    public function getVariableTypeInfo(): array
    {
        $result = [];
        foreach ($this->context->getVariables() as $name => $value) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            $result[$name] = new TypeInfoProperty($name, [$type]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionTypeInfo(): array
    {
        $result = [];
        foreach ($this->context->getFunctions() as $name => $call) {
            // TODO extract param/return info from $call
            $result[$name] = new TypeInfoMethod($name, [], []);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailedTypeInfo(): array
    {
        /** @var array<TypeInfoProperty|TypeInfoMethod> $members */
        $members = array_merge(
            $this->getVariableTypeInfo(),
            $this->getFunctionTypeInfo()
        );

        foreach ($members as $member) {
            $this->analyser->analyse($member->getTypes());
        }

        return $this->analyser->getTypes();
    }
}

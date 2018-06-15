<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Util\TypeAnalyser;
use uuf6429\Rune\Util\TypeInfoCollection;
use uuf6429\Rune\Util\TypeInfoMember;

class DynamicContextDescriptor implements ContextDescriptorInterface
{
    /**
     * @var DynamicContext
     */
    protected $context;

    /**
     * @param DynamicContext $context
     */
    public function __construct(DynamicContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables()
    {
        return $this->context->getVariables();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return $this->context->getFunctions();
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableTypeInfo($analyser = null)
    {
        $result = [];
        foreach ($this->context->getVariables() as $name => $value) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            $result[$name] = new TypeInfoMember($name, [$type]);
        }

        return new TypeInfoCollection($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionTypeInfo($analyser = null)
    {
        $result = [];
        foreach (array_keys($this->context->getFunctions()) as $name) {
            $result[$name] = new TypeInfoMember($name, ['callable']);
        }

        return new TypeInfoCollection($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailedTypeInfo($analyser = null)
    {
        $analyser = $analyser ?: new TypeAnalyser();

        $members = $this->getVariableTypeInfo($analyser);
        $members = $members->merge($this->getFunctionTypeInfo($analyser));

        foreach ($members as $member) {
            $analyser->analyse($member->getTypes());
        }

        return new TypeInfoCollection($analyser->getTypes());
    }
}

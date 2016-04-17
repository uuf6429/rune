<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Util\TypeAnalyser;
use uuf6429\Rune\Util\TypeInfoMember;

class ClassContextDescriptor extends AbstractContextDescriptor
{
    /**
     * @var ClassContext
     */
    protected $context;

    /**
     * @var TypeInfoMember[]
     */
    protected $memberTypeInfo;

    /**
     * @param ClassContext $context
     */
    public function __construct($context)
    {
        if (!($context instanceof ClassContext)) {
            throw new \InvalidArgumentException('Context must be or extends ClassContext.');
        }

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $inheritedMethods = get_class_methods(ContextInterface::class);

        $result = [];

        $names = array_filter(
            get_class_methods($this->context),
            function ($name) use ($inheritedMethods) {
                return substr($name, 0, 2) != '__'
                    && !in_array($name, $inheritedMethods);
            }
        );

        foreach ($names as $name) {
            $result[$name] = [$this->context, $name];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables()
    {
        return get_object_vars($this->context);
    }

    /**
     * @param TypeAnalyser $analyser
     *
     * @return TypeInfoMember[]
     */
    protected function getMemeberTypeInfo($analyser)
    {
        if ($this->memberTypeInfo === null) {
            $class = get_class($this->context);
            $analyser->analyse($class, false);
            $types = $analyser->getTypes();
            $this->memberTypeInfo = isset($types[$class]) ? $types[$class]->members : [];
        }

        return $this->memberTypeInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableTypeInfo($analyser = null)
    {
        $analyser = $analyser ?: new TypeAnalyser();

        return array_filter(
            $this->getMemeberTypeInfo($analyser),
            function (TypeInfoMember $member) {
                return !$member->isCallable();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionTypeInfo($analyser = null)
    {
        $analyser = $analyser ?: new TypeAnalyser();

        return array_filter(
            $this->getMemeberTypeInfo($analyser),
            function (TypeInfoMember $member) {
                return $member->isCallable();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailedTypeInfo($analyser = null)
    {
        $analyser = $analyser ?: new TypeAnalyser();
        $analyser->analyse(get_class($this->context));

        return $analyser->getTypes();
    }
}

<?php

namespace uuf6429\Rune\Context;

use InvalidArgumentException;
use uuf6429\Rune\Util\TypeAnalyser;
use uuf6429\Rune\Util\TypeInfoMember;

class ClassContextDescriptor extends AbstractContextDescriptor
{
    protected const CONTEXT_DESCRIPTOR_METHOD = 'getContextDescriptor';

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
        if (!$context instanceof ClassContext) {
            throw new InvalidArgumentException('Context must be or extends ClassContext.');
        }

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        $result = [];

        $names = array_filter(
            get_class_methods($this->context),
            static function ($name) {
                return substr($name, 0, 2) !== '__'
                    && $name !== self::CONTEXT_DESCRIPTOR_METHOD;
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
    public function getVariables(): array
    {
        return get_object_vars($this->context);
    }

    /**
     * @return TypeInfoMember[]
     */
    protected function getMemberTypeInfo(TypeAnalyser $analyser): array
    {
        if ($this->memberTypeInfo === null) {
            $class = get_class($this->context);
            $analyser->analyse($class, false);
            $types = $analyser->getTypes();
            $this->memberTypeInfo = array_filter(
                isset($types[$class]) ? $types[$class]->members : [],
                static function (TypeInfoMember $member) {
                    return $member->getName() !== self::CONTEXT_DESCRIPTOR_METHOD;
                }
            );
        }

        return $this->memberTypeInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableTypeInfo($analyser = null): array
    {
        $analyser = $analyser ?: new TypeAnalyser();

        return array_filter(
            $this->getMemberTypeInfo($analyser),
            static function (TypeInfoMember $member) {
                return !$member->isCallable();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionTypeInfo($analyser = null): array
    {
        $analyser = $analyser ?: new TypeAnalyser();

        return array_filter(
            $this->getMemberTypeInfo($analyser),
            static function (TypeInfoMember $member) {
                return $member->isCallable();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailedTypeInfo($analyser = null): array
    {
        $analyser = $analyser ?: new TypeAnalyser();
        $analyser->analyse(get_class($this->context));

        return $analyser->getTypes();
    }
}

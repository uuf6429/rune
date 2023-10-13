<?php declare(strict_types=1);

namespace uuf6429\Rune\Context;

use ReflectionException;
use uuf6429\Rune\TypeInfo\TypeAnalyser;
use uuf6429\Rune\TypeInfo\TypeInfoMember;

class ClassContextDescriptor implements ContextDescriptorInterface
{
    private const CONTEXT_DESCRIPTOR_METHOD = 'getContextDescriptor';

    /**
     * @var null|TypeInfoMember[]
     */
    protected ?array $memberTypeInfo = null;

    protected ClassContext $context;

    public function __construct(ClassContext $context)
    {
        $this->context = $context;
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
     * @throws ReflectionException
     */
    protected function getMemberTypeInfo(TypeAnalyser $analyser): array
    {
        if ($this->memberTypeInfo !== null) {
            return $this->memberTypeInfo;
        }

        $class = get_class($this->context);
        $analyser->analyse([$class], false);
        $types = $analyser->getTypes();
        $this->memberTypeInfo = array_filter(
            isset($types[$class]) ? $types[$class]->getMembers() : [],
            static function (TypeInfoMember $member) {
                return $member->getName() !== self::CONTEXT_DESCRIPTOR_METHOD;
            }
        );
        return $this->memberTypeInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableTypeInfo(?TypeAnalyser $analyser = null): array
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
    public function getFunctionTypeInfo(?TypeAnalyser $analyser = null): array
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
    public function getDetailedTypeInfo(?TypeAnalyser $analyser = null): array
    {
        $analyser = $analyser ?: new TypeAnalyser();
        $analyser->analyse([get_class($this->context)]);

        return $analyser->getTypes();
    }
}

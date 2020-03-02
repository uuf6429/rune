<?php

namespace uuf6429\Rune\Util;

use kamermans\Reflection\DocBlock;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use RuntimeException;

class TypeAnalyser
{
    protected static $simpleTypes = [
        'object', 'array', 'string', 'boolean', 'integer', 'double',
    ];

    /**
     * List of discovered types, key is the fully qualified type name.
     *
     * @var array<string,TypeInfoClass>
     */
    protected $types = [];

    /**
     * Enables deep analysis (recursively analyses class members and their types).
     *
     * @var bool
     */
    protected $deep = false;

    /**
     * @var bool
     */
    protected $canInspectReflectionParamType;

    /**
     * @var bool
     */
    protected $canInspectReflectionReturnType;

    public function __construct()
    {
        $this->canInspectReflectionParamType = method_exists(ReflectionParameter::class, 'getType');
        $this->canInspectReflectionReturnType = method_exists(ReflectionMethod::class, 'getReturnType');
    }

    /**
     * @param string|array $type
     *
     * @throws ReflectionException
     */
    public function analyse($type, bool $deep = true): void
    {
        if (is_array($type)) {
            foreach ($type as $aType) {
                $this->analyse($aType, $deep);
            }

            return;
        }

        $this->deep = $deep;
        $type = $this->normalise($type);

        if ($type && !isset($this->types[$type]) && !in_array($type, static::$simpleTypes, true)) {
            switch (true) {
                case @interface_exists($type):
                case @class_exists($type):
                    $this->analyseClassOrInterface($type);
                    break;

                case $type === 'callable':
                case $type === 'resource':
                    break;

                default:
                    throw new RuntimeException(sprintf('Type information for %s cannot be retrieved (unsupported type).', $type));
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    protected function analyseClassOrInterface(string $name): void
    {
        // .-- avoid infinite loop inspecting same type
        $this->types[$name] = 'IN_PROGRESS';

        $reflector = new ReflectionClass($name);

        $docb = new DocBlock($reflector);
        $hint = $docb->getComment() ?: '';
        $link = $docb->getTag('link', '') ?: '';

        if (is_array($link)) {
            $link = $link[0];
        }

        $members = array_filter(
            array_merge(
                array_map(
                    [$this, 'parseDocBlockPropOrParam'],
                    $docb->getTag('property', [], true)
                ),
                array_map(
                    [$this, 'propertyToTypeInfoMember'],
                    $reflector->getProperties(ReflectionProperty::IS_PUBLIC)
                ),
                array_map(
                    [$this, 'methodToTypeInfoMember'],
                    $reflector->getMethods(ReflectionMethod::IS_PUBLIC)
                )
            )
        );

        $this->types[$name] = new TypeInfoClass($name, $members, $hint, $link);
    }

    protected function parseDocBlockPropOrParam(string $line): ?TypeInfoMember
    {
        $regex = '/^([\\w\\|\\\\]+)\\s+(\\$\\w+)\\s*(.*)$/';
        if (preg_match($regex, trim($line), $result)) {
            $types = explode('|', $result[1]);
            $types = array_filter(array_map([$this, 'handleType'], $types));

            return new TypeInfoMember(
                substr($result[2], 1),
                $types,
                $result[3]
            );
        }

        return null;
    }

    protected function parseReflectedParams(ReflectionParameter $param): ?TypeInfoMember
    {
        $types = [];

        if ($this->canInspectReflectionParamType && (bool) ($type = $param->getType())) {
            $types[] = (string) $type;
            if ($type->allowsNull()) {
                $type[] = 'null';
            }
        }

        return new TypeInfoMember(
            $param->getName(),
            $types,
            ''
        );
    }

    protected function propertyToTypeInfoMember(ReflectionProperty $property): TypeInfoMember
    {
        $docb = new DocBlock($property);
        $hint = $docb->getComment();
        $link = $docb->getTag('link', '');
        $types = explode('|', $docb->getTag('var', ''));
        $types = array_filter(array_map([$this, 'handleType'], $types));

        return new TypeInfoMember($property->getName(), $types, $hint, $link);
    }

    protected function methodToTypeInfoMember(ReflectionMethod $method): ?TypeInfoMember
    {
        if (substr($method->name, 0, 2) === '__') {
            return null;
        }

        $docb = new DocBlock($method);
        $hint = $docb->getComment() ?: '';
        $link = $docb->getTag('link', '') ?: '';

        if (is_array($link)) {
            $link = $link[0];
        }

        if ($docb->tagExists('param')) {
            // detect return from docblock
            $return = explode(' ', $docb->getTag('return', 'void'), 2)[0];
        } else {
            // detect return from reflection
            $return = $this->canInspectReflectionReturnType
                ? $method->getReturnType() : '';
        }

        if ($docb->tagExists('param')) {
            // detect params from docblock
            $params = array_map(
                [$this, 'parseDocBlockPropOrParam'],
                $docb->getTag('param', [], true)
            );
        } else {
            // detect params from reflection
            $params = array_map(
                [$this, 'parseReflectedParams'],
                $method->getParameters()
            );
        }

        $signature = sprintf(
            '<div class="cm-signature">'
                    . '<span class="type">%s</span> <span class="name">%s</span>'
                    . '(<span class="args">%s</span>)</span>'
                . '</div>',
            $return,
            $method->name,
            implode(
                ', ',
                array_map(
                    static function (TypeInfoMember $param) {
                        $result = '???';

                        if ($param) {
                            $result = sprintf(
                                '<span class="%s" title="%s"><span class="type">%s</span>$%s</span>',
                                $param->hasHint() ? 'arg hint' : 'arg',
                                $param->getHint(),
                                $param->hasTypes() ? (implode('|', $param->getTypes()) . ' ') : '',
                                $param->getName()
                            );
                        }

                        return $result;
                    },
                    $params
                )
            )
        );

        return new TypeInfoMember($method->name, ['method'], $signature . $hint, $link);
    }

    /**
     * @throws ReflectionException
     */
    protected function handleType(string $name): string
    {
        $name = $this->normalise($name);

        if ($this->deep) {
            $this->analyse($name);
        }

        return $name;
    }

    protected function normalise(string $type): string
    {
        static $typeMap = [
            'int' => 'integer',
            'float' => 'double',
            'decimal' => 'double',
            'bool' => 'boolean',
            'stdClass' => 'object',
            'mixed' => '',
            'resource' => '',
        ];

        $type = ltrim($type, '\\');

        return $typeMap[$type] ?? $type;
    }

    /**
     * @return array<string,TypeInfoClass>
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}

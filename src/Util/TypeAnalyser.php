<?php

namespace uuf6429\Rune\Util;

use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use RuntimeException;

class TypeAnalyser
{
    protected static array $simpleTypes = [
        'object', 'array', 'string', 'boolean', 'integer', 'double',
    ];

    /**
     * List of discovered types, key is the fully qualified type name.
     *
     * @var array<string,TypeInfoClass>
     */
    protected array $types = [];

    /**
     * Enables deep analysis (recursively analyses class members and their types).
     */
    protected bool $deep = false;

    /**
     * @param string[] $types
     * @throws ReflectionException
     */
    public function analyse(array $types, bool $deep = true): void
    {
        $this->deep = $deep;
        foreach ($types as $type) {
            $this->analyseType($type);
        }
    }

    /**
     * @return array<string,TypeInfoClass>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @throws ReflectionException
     */
    private function analyseType(string $type): void
    {
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
                    throw new RuntimeException(
                        sprintf(
                            'Type information for %s cannot be retrieved (unsupported type).',
                            $type
                        )
                    );
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private function analyseClassOrInterface(string $name): void
    {
        // .-- avoid infinite loop inspecting same type
        $this->types[$name] = 'IN_PROGRESS';

        $reflector = new ReflectionClass($name);

        $docb = new DocBlock($reflector);
        $this->types[$name] = new TypeInfoClass(
            $name,
            array_filter(
                array_map(
                    [$this, 'extractTypeInfoMember'],
                    array_merge(
                        $reflector->getProperties(ReflectionProperty::IS_PUBLIC),
                        $reflector->getMethods(ReflectionMethod::IS_PUBLIC),
                        $docb->getTagsByName('property'),
                        $docb->getTagsByName('property-read'),
                        $docb->getTagsByName('method')
                    )
                )
            ),
            (string)$docb->getDescription() ?: null,
            $this->extractLinkURL($docb)
        );
    }

    /**
     * @throws ReflectionException
     */
    private function extractTypeInfoMember(object $element): ?TypeInfoMember
    {
        switch (true) {
            case $element instanceof ReflectionProperty:
                $docb = new DocBlock($element);
                return new TypeInfoMember(
                    $element->getName(),
                    array_filter(
                        array_map(
                            fn (DocBlock\Tags\Var_ $tag) => $this->handleType((string)$tag->getType()),
                            $docb->getTagsByName('var')
                        )
                    ),
                    (string)$docb->getDescription() ?: null,
                    $this->extractLinkURL($docb)
                );

            case $element instanceof ReflectionMethod:
                $docb = new DocBlock($element);
                return $this->handleMethod(
                    $element->name,
                    (string)$docb->getDescription() ?: null,
                    $this->extractLinkURL($docb),
                    $docb->hasTag('param')
                        ? // detect params from docblock
                        array_map(
                            [$this, 'extractTypeInfoMember'],
                            $docb->getTagsByName('param')
                        )
                        : // detect params from reflection
                        array_map(
                            [$this, 'extractTypeInfoMember'],
                            $element->getParameters()
                        ),
                    $docb->hasTag('return')
                        ? // detect return from docblock
                        implode('|', array_map(
                            fn (DocBlock\Tags\Return_ $tag) => $this->handleType((string)$tag->getType()),
                            $docb->getTagsByName('return')
                        ))
                        : // detect return from reflection
                        (string)$element->getReturnType()
                );

            case $element instanceof ReflectionParameter:
                $types = [];
                if (($type = $element->getType()) !== null) {
                    $types[] = (string)$type;
                    if ($type->allowsNull()) {
                        $types[] = 'null';
                    }
                }
                return new TypeInfoMember($element->getName(), $types);

            case $element instanceof DocBlock\Tags\Method:
                return $this->handleMethod(
                    $element->getMethodName(),
                    (string)$element->getDescription() ?: null,
                    null,
                    array_map(
                        static fn ($arg) => new TypeInfoMember($arg['name'], $arg['type']),
                        $element->getArguments()
                    ),
                    (string)$element->getReturnType()
                );

            case $element instanceof DocBlock\Tags\Property:
            case $element instanceof DocBlock\Tags\PropertyRead:
            case $element instanceof DocBlock\Tags\Param:
                return new TypeInfoMember(
                    $element->getVariableName(),
                    [(string)$element->getType()]
                );

            default:
                throw new RuntimeException('Unsupported element type: ' . get_class($element));
        }
    }

    private function handleMethod(string $name, ?string $description, ?string $link, array $params, string $return): ?TypeInfoMember
    {
        if (substr($name, 0, 2) === '__') {
            return null;
        }

        return new TypeInfoMember(
            $name,
            ['method'],
            sprintf(
                '<div class="cm-signature">'
                . '<span class="type">%s</span> <span class="name">%s</span>'
                . '(<span class="args">%s</span>)</span>'
                . '</div>%s',
                $return ?: 'void',
                $name,
                implode(
                    ', ',
                    array_map(
                        static function (?TypeInfoMember $param) {
                            if (!$param) {
                                return '???';
                            }

                            return sprintf(
                                '<span class="%s" title="%s"><span class="type">%s</span>$%s</span>',
                                $param->hasHint() ? 'arg hint' : 'arg',
                                $param->getHint(),
                                $param->hasTypes() ? (implode('|', $param->getTypes()) . ' ') : '',
                                $param->getName()
                            );
                        },
                        $params
                    )
                ),
                $description
            ),
            $link
        );
    }

    /**
     * @throws ReflectionException
     */
    private function handleType(string $name): string
    {
        $name = $this->normalise($name);

        if ($this->deep) {
            $this->analyseType($name);
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

    private function extractLinkURL(DocBlock $docb): ?string
    {
        $link = $docb->getTagsByName('link')[0] ?? null;
        return $link instanceof DocBlock\Tags\Link ? $link->getLink() : null;
    }
}

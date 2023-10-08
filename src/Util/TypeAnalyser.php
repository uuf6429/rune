<?php

namespace uuf6429\Rune\Util;

use phpDocumentor\Reflection as PhpDoc;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use RuntimeException;

class TypeAnalyser
{
    protected static array $basicTypes = [
        'object',
        'array',
        'string',
        'boolean',
        'integer',
        'double',
        'null',
        'mixed',
        'void',
    ];

    protected static array $aliasedTypes = [
        'int' => 'integer',
        'float' => 'double',
        'decimal' => 'double',
        'bool' => 'boolean',
        'stdClass' => 'object',
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

    private PhpDoc\DocBlockFactory $docBlockFactory;

    private array $prohibitedMethodNames = ['getContextDescriptor'];

    public function __construct()
    {
        $this->docBlockFactory = PhpDoc\DocBlockFactory::createInstance();
    }

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

        if ($type && !isset($this->types[$type]) && !in_array($type, static::$basicTypes, true)) {
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

        $docb = $this->getDocBlock($reflector);
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
            $this->extractSummary($reflector, $docb),
            $this->extractLinkURL($docb)
        );
    }

    private function extractSummary(Reflector $element, PhpDoc\DocBlock $docb): ?string
    {
        $result = trim($docb->getSummary()) ?: null;

        if (!in_array($result, ['@inheritdoc', '{@inheritdoc}'], true)) {
            return $result;
        }

        $parentElement = $this->findParentElement($element);
        if (!$parentElement) {
            return null;
        }

        return $this->extractSummary($parentElement, $this->getDocBlock($parentElement));
    }

    private function findParentElement(Reflector $element): ?Reflector
    {
        switch (true) {
            case $element instanceof ReflectionProperty:
                $parentClass = $element->getDeclaringClass();
                while ($parentClass = $parentClass->getParentClass()) {
                    try {
                        return $parentClass->getProperty($element->getName());
                    } catch (ReflectionException $ex) {
                    }
                }
                return null;

            case $element instanceof ReflectionMethod:
                $parentClass = $element->getDeclaringClass();
                while ($parentClass = $parentClass->getParentClass()) {
                    try {
                        return $parentClass->getMethod($element->getName());
                    } catch (ReflectionException $ex) {
                    }
                }
                return null;

            case $element instanceof ReflectionClass:
                return $element->getParentClass() ?: null;

            default:
                throw new RuntimeException('Unsupported reflection element: ' . get_class($element));
        }
    }

    /**
     * @throws ReflectionException
     */
    private function extractTypeInfoMember(object $element): ?TypeInfoMember
    {
        switch (true) {
            case $element instanceof ReflectionProperty:
                $docb = $this->getDocBlock($element);
                $type = $element->getType();
                return new TypeInfoMember(
                    $element->getName(),
                    array_map(
                        [$this, 'handleType'],
                        array_unique(
                            array_filter(
                                array_merge(
                                    array_map(
                                        static fn (PhpDoc\DocBlock\Tags\Var_ $tag) => (string)$tag->getType(),
                                        $docb->getTagsByName('var')
                                    ),
                                    [
                                        $type instanceof ReflectionNamedType ? $type->getName() : null,
                                        $type && $type->allowsNull() ? 'null' : null,
                                    ]
                                )
                            )
                        )
                    ),
                    $this->extractSummary($element, $docb),
                    $this->extractLinkURL($docb)
                );

            case $element instanceof ReflectionMethod:
                if (in_array($element->name, $this->prohibitedMethodNames, true)) {
                    return null;
                }
                $docb = $this->getDocBlock($element);
                return $this->handleMethod(
                    $element->name,
                    $this->extractSummary($element, $docb),
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
                    implode(
                        '|',
                        array_filter(
                            array_map(
                                [$this, 'handleType'],
                                $docb->hasTag('return')
                                    ? // detect return from docblock
                                    array_map(
                                        static fn (PhpDoc\DocBlock\Tags\Return_ $tag) => (string)$tag->getType(),
                                        $docb->getTagsByName('return')
                                    )
                                    : // detect return from reflection
                                    [
                                        ($type = $element->getReturnType()) instanceof ReflectionNamedType ? $type->getName() : null,
                                        $type && $type->allowsNull() ? 'null' : null,
                                    ]
                            )
                        )
                    )
                );

            case $element instanceof ReflectionParameter:
                return new TypeInfoMember(
                    $element->getName(),
                    array_filter(
                        array_map(
                            [$this, 'handleType'],
                            [
                                ($type = $element->getType()) instanceof ReflectionNamedType ? $type->getName() : null,
                                $type && $type->allowsNull() ? 'null' : null,
                            ]
                        )
                    )
                );

            case $element instanceof PhpDoc\DocBlock\Tags\Method:
                if (in_array($element->getMethodName(), $this->prohibitedMethodNames, true)) {
                    return null;
                }
                return $this->handleMethod(
                    $element->getMethodName(),
                    (string)$element->getDescription() ?: null,
                    null,
                    array_map(
                        static fn ($arg) => new TypeInfoMember($arg['name'], $arg['type']),
                        $element->getArguments()
                    ),
                    $this->handleType((string)$element->getReturnType())
                );

            case $element instanceof PhpDoc\DocBlock\Tags\Property:
            case $element instanceof PhpDoc\DocBlock\Tags\PropertyRead:
            case $element instanceof PhpDoc\DocBlock\Tags\Param:
                return new TypeInfoMember(
                    $element->getVariableName(),
                    array_map(
                        [$this, 'handleType'],
                        explode('|', (string)$element->getType())
                    )
                );

            default:
                throw new RuntimeException('Unsupported element type: ' . get_class($element));
        }
    }

    private function handleMethod(
        string  $name,
        ?string $description,
        ?string $link,
        array   $params,
        string  $return
    ): ?TypeInfoMember {
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
    protected function handleType(?string $name): ?string
    {
        if (!$name) {
            return null;
        }

        $name = $this->normalise($name);

        if ($this->deep) {
            $this->analyseType($name);
        }

        return $name;
    }

    protected function normalise(string $type): string
    {
        $type = ltrim($type, '\\?');
        return static::$aliasedTypes[$type] ?? $type;
    }

    private function extractLinkURL(PhpDoc\DocBlock $docb): ?string
    {
        $link = $docb->getTagsByName('link')[0] ?? null;
        return $link instanceof PhpDoc\DocBlock\Tags\Link ? $link->getLink() : null;
    }

    private function getDocBlock(Reflector $element): PhpDoc\DocBlock
    {
        return (method_exists($element, 'getDocComment') && ($docComment = $element->getDocComment()))
            ? $this->docBlockFactory->create($docComment)
            : new PhpDoc\DocBlock();
    }
}

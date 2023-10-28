<?php declare(strict_types=1);

namespace uuf6429\Rune\TypeInfo;

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
        'class',
        'method',
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
     * @var array<string,TypeInfoClass|'IN_PROGRESS'>
     */
    protected array $types = [];

    private PhpDoc\DocBlockFactory $docBlockFactory;

    private PhpDoc\Types\ContextFactory $docBlockContextFactory;

    private array $prohibitedMethodNames = ['getContextDescriptor'];

    public function __construct()
    {
        $this->docBlockFactory = PhpDoc\DocBlockFactory::createInstance();
        $this->docBlockContextFactory = new PhpDoc\Types\ContextFactory();
    }

    /**
     * @param string[] $types
     * @return $this
     * @throws ReflectionException
     */
    public function analyse(array $types): self
    {
        foreach ($types as $type) {
            $this->analyseType($type);
        }

        return $this;
    }

    /**
     * @return array<string,TypeInfoClass>
     */
    public function getTypes(): array
    {
        return array_filter($this->types, 'is_object');
    }

    /**
     * @throws ReflectionException
     */
    private function analyseType(string $type): void
    {
        $type = $this->normalise($type);

        switch (true) {
            case trim($type) === '':
            case isset($this->types[$type]):
            case in_array($type, static::$basicTypes, true):
            case $type === 'callable':
            case $type === 'resource':
                break;

            case @interface_exists($type):
            case @class_exists($type):
                $this->analyseClassOrInterface($type);
                break;

            default:
                throw new RuntimeException(
                    sprintf(
                        'Type information for "%s" cannot be retrieved (unsupported type).',
                        $type
                    )
                );
        }
    }

    /**
     * @param class-string $name
     * @return void
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
                    [$this, 'extractTypeInfo'],
                    array_merge(
                        $reflector->getProperties(ReflectionProperty::IS_PUBLIC),
                        $reflector->getMethods(ReflectionMethod::IS_PUBLIC),
                        $docb->getTagsByName('property'),
                        $docb->getTagsByName('property-read'),
                        $docb->getTagsByName('method')
                    )
                ),
                static fn ($item) => $item instanceof TypeInfoProperty || $item instanceof TypeInfoMethod,
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
    private function extractTypeInfo(object $element): ?TypeInfoBase
    {
        switch (true) {
            case $element instanceof ReflectionProperty:
                $docb = $this->getDocBlock($element);
                $type = $element->getType();
                return new TypeInfoProperty(
                    $element->getName(),
                    array_filter(
                        array_map(
                            [$this, 'handleType'],
                            array_unique(
                                array_merge(
                                    array_map(
                                        static fn ($tag) => $tag instanceof PhpDoc\DocBlock\Tags\Var_ ? (string)$tag->getType() : null,
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
                    $this->extractLinkURL($docb),
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
                    array_filter(
                        $docb->hasTag('param')
                            ? // detect params from docblock
                            array_map(
                                [$this, 'extractTypeInfo'],
                                $docb->getTagsByName('param')
                            )
                            : // detect params from reflection
                            array_map(
                                [$this, 'extractTypeInfo'],
                                $element->getParameters()
                            ),
                        static fn ($item) => $item instanceof TypeInfoParameter,
                    ),
                    array_filter(
                        array_map(
                            [$this, 'handleType'],
                            $docb->hasTag('return')
                                ? // detect return from docblock
                                array_map(
                                    static fn ($tag) => $tag instanceof PhpDoc\DocBlock\Tags\Return_ ? (string)$tag->getType() : null,
                                    $docb->getTagsByName('return')
                                )
                                : // detect return from reflection
                                [
                                    ($type = $element->getReturnType()) instanceof ReflectionNamedType ? $type->getName() : null,
                                    $type && $type->allowsNull() ? 'null' : null,
                                ]
                        )
                    ) ?: ['mixed']
                );

            case $element instanceof ReflectionParameter:
                return new TypeInfoParameter(
                    $element->getName(),
                    array_filter(
                        array_map(
                            [$this, 'handleType'],
                            [
                                ($type = $element->getType()) instanceof ReflectionNamedType ? $type->getName() : null,
                                $type && $type->allowsNull() ? 'null' : null,
                            ]
                        )
                    ),
                    // TODO we could extract this info from the method's PHPDoc
                    null,
                    null,
                );

            case $element instanceof PhpDoc\DocBlock\Tags\Method:
                if (in_array($element->getMethodName(), $this->prohibitedMethodNames, true)) {
                    return null;
                }
                return $this->handleMethod(
                    $element->getMethodName(),
                    (string)$element->getDescription() ?: null,
                    null,
                    array_filter(array_map(
                        fn ($arg) => new TypeInfoParameter(
                            $arg['name'],
                            array_filter([$this->handleType((string)$arg['type'])]),
                            // PHPDoc methods cannot define argument hint/link
                            null,
                            null,
                        ),
                        $element->getArguments()
                    )),
                    [$this->handleType((string)$element->getReturnType()) ?? 'mixed']
                );

            case $element instanceof PhpDoc\DocBlock\Tags\Property:
            case $element instanceof PhpDoc\DocBlock\Tags\PropertyRead:
                if (($paramName = $element->getVariableName()) === null) {
                    throw new RuntimeException('Property name must not be null');
                }
                return new TypeInfoProperty(
                    $paramName,
                    array_filter(array_map(
                        [$this, 'handleType'],
                        explode('|', (string)$element->getType())
                    )),
                    (string)$element->getDescription() ?: null,
                    null,
                );

            case $element instanceof PhpDoc\DocBlock\Tags\Param:
                if (($paramName = $element->getVariableName()) === null) {
                    throw new RuntimeException('Parameter name must not be null');
                }
                return new TypeInfoParameter(
                    $paramName,
                    array_filter(array_map(
                        [$this, 'handleType'],
                        explode('|', (string)$element->getType())
                    )),
                    (string)$element->getDescription() ?: null,
                    null,
                );

            default:
                throw new RuntimeException('Unsupported element type: ' . get_class($element));
        }
    }

    /**
     * @param TypeInfoParameter[] $params
     * Qparam string[] $return
     */
    private function handleMethod(
        string  $name,
        ?string $description,
        ?string $link,
        array   $params,
        array   $return
    ): ?TypeInfoMethod {
        if (substr($name, 0, 2) === '__') {
            return null;
        }

        return new TypeInfoMethod($name, $params, $return, $description, $link);
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
        $this->analyseType($name);

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
            ? $this->docBlockFactory->create($docComment, $this->docBlockContextFactory->createFromReflector($element))
            : new PhpDoc\DocBlock();
    }
}

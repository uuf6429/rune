<?php

namespace uuf6429\Rune\Util;

use kamermans\Reflection\DocBlock;

class TypeAnalyser
{
    protected $simpleTypes = [
        'object', 'array', 'string', 'boolean', 'integer', 'double',
    ];

    protected $excludedMethods = [
        '__construct', '__destruct', '__toString',
    ];

    /**
     * List of discovered types, key is the fully qualified type name.
     *
     * @var TypeInfoClass[string]
     */
    protected $types = [];

    /**
     * Enables deep analysis (recursively analyses class members and their types).
     * 
     * @var type
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
        $this->canInspectReflectionParamType = method_exists(\ReflectionParameter::class, 'getType');
        $this->canInspectReflectionReturnType = method_exists(\ReflectionMethod::class, 'getReturnType');
    }

    /**
     * @param string|array $type
     * @param bool         $deep
     */
    public function analyse($type, $deep = true)
    {
        if (is_array($type)) {
            foreach ($type as $aType) {
                $this->analyse($aType, $deep);
            }

            return;
        }

        $this->deep = $deep;
        $type = $this->normalise($type);

        if ($type && !in_array($type, $this->simpleTypes) && !isset($this->types[$type])) {
            switch (true) {
                case interface_exists($type):
                case class_exists($type):
                    $this->analyseClassOrInterface($type);
                    break;

                case $type == 'callable':
                case $type == 'resource':
                    break;

                default:
                    throw new \RuntimeException(
                        sprintf(
                            'Type information for %s cannot be retrieved (unsupported type).',
                            $type
                        )
                    );
            }
        }
    }

    /**
     * @param string $name
     */
    protected function analyseClassOrInterface($name)
    {
        // .-- avoid infinite loop inspecting same type
        $this->types[$name] = 'IN_PROGRESS';

        $reflector = new \ReflectionClass($name);

        $docb = new DocBlock($reflector);
        $hint = $docb->getComment() ?: '';
        $link = $docb->getTag('link', '');

        $members = array_filter(
            array_merge(
                array_map(
                    [$this, 'docBlockPropertyToTypeInfoMember'],
                    $docb->getTag('property', [], true)
                ),
                array_map(
                    [$this, 'propertyToTypeInfoMember'],
                    $reflector->getProperties(\ReflectionProperty::IS_PUBLIC)
                ),
                array_map(
                    [$this, 'methodToTypeInfoMember'],
                    $reflector->getMethods(\ReflectionMethod::IS_PUBLIC)
                )
            )
        );

        $this->types[$name] = new TypeInfoClass($name, $members, $hint, $link);
    }

    /**
     * @param string $line
     *
     * @return array|null Array with keys 'name', 'types', 'hint' OR null if not applicable.
     */
    protected function parseDocBlockPropOrParam($line)
    {
        $result = null;
        $regex = '/^([\\w\\|\\\\]+)\\s+(\\$\\w+)\\s*(.*)$/';
        if (preg_match($regex, trim($line), $result)) {
            $types = explode('|', $result[1]);
            $types = array_filter(array_map([$this, 'handleType'], $types));

            $result = [
                'name' => substr($result[2], 1),
                'types' => $types,
                'hint' => $result[3],
            ];
        }

        return $result;
    }

    protected function parseReflectedParams(\ReflectionParameter $param)
    {
        $types = [];

        if ($this->canInspectReflectionParamType && (bool) ($type = $param->getType())) {
            $types[] = (string) $type;
            if ($type->allowsNull()) {
                $type[] = 'null';
            }
        }

        return [
            'name' => $param->getName(),
            'types' => $types,
            'hint' => '',
        ];
    }

    /**
     * @param string $propertyDef
     */
    protected function docBlockPropertyToTypeInfoMember($propertyDef)
    {
        $result = $this->parseDocBlockPropOrParam($propertyDef);

        if ($result) {
            $result = new TypeInfoMember(
                $result['name'],
                $result['types'],
                $result['hint']
            );
        }

        return $result;
    }

    /**
     * @param \ReflectionProperty $property
     */
    protected function propertyToTypeInfoMember(\ReflectionProperty $property)
    {
        $docb = new DocBlock($property);
        $hint = $docb->getComment();
        $link = $docb->getTag('link', '');
        $types = explode('|', $docb->getTag('var', ''));
        $types = array_filter(array_map([$this, 'handleType'], $types));

        return new TypeInfoMember($property->getName(), $types, $hint, $link);
    }

    /**
     * @param \ReflectionMethod $method
     */
    protected function methodToTypeInfoMember(\ReflectionMethod $method)
    {
        if (substr($method->name, 0, 2) === '__') {
            return;
        }

        $docb = new DocBlock($method);
        $hint = $docb->getComment();
        $link = $docb->getTag('link', '');

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
                    function ($param) {
                        $result = '???';

                        if ($param) {
                            $result = sprintf(
                                '<span class="%s" title="%s"><span class="type">%s</span>$%s</span>',
                                $param['hint'] ? 'arg hint' : 'arg',
                                $param['hint'],
                                count($param['types']) ? (implode('|', $param['types']) . ' ') : '',
                                $param['name']
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
     * @param string $name
     */
    protected function handleType($name)
    {
        $name = $this->normalise($name);

        if ($this->deep) {
            $this->analyse($name);
        }

        return $name;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function normalise($type)
    {
        switch ($type) {
            case 'int':
                return 'integer';

            case 'float':
            case 'decimal':
                return 'double';

            case 'bool':
                return 'boolean';

            case 'stdClass':
                return 'object';

            case 'mixed':
            case 'resource':
                return '';

            default:
                return $type;
        }
    }

    /**
     * @return TypeInfoClass[string]
     */
    public function getTypes()
    {
        return $this->types;
    }
}

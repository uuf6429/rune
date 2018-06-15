<?php

namespace uuf6429\Rune\Util;

class TypeInfoMember implements TypeInfoInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $hint;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string[]
     */
    protected $types;

    /**
     * @param string   $name
     * @param string[] $types
     * @param string   $hint
     * @param string   $link
     *
     * @todo Change position of $types
     */
    public function __construct($name, array $types = [], $hint = '', $link = '')
    {
        $this->name = $name;
        $this->hint = $hint;
        $this->link = $link;
        $this->types = $types;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasHint()
    {
        return (bool) $this->hint;
    }

    /**
     * @return string
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return bool
     */
    public function hasTypes()
    {
        return (bool) count($this->types);
    }

    /**
     * @return array|string[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return bool
     */
    public function isCallable()
    {
        // Note: __invoke is not supported here... would have been nice of PHP to have an Invokable interface
        static $callableTypes = ['callable', 'Closure', 'method'];

        return !empty(array_intersect($this->types, $callableTypes));
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'hint' => $this->hint,
            'link' => $this->link,
            'types' => $this->types,
        ];
    }
}

<?php

namespace uuf6429\Rune\Util;

use JsonSerializable;

class TypeInfoMember implements JsonSerializable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $types;

    /**
     * @var string
     */
    protected $hint;

    /**
     * @var string
     */
    protected $link;

    /**
     * @param string   $name
     * @param string[] $types
     * @param string   $hint
     * @param string   $link
     */
    public function __construct($name, array $types = [], $hint = '', $link = '')
    {
        $this->name = $name;
        $this->types = $types;
        $this->hint = $hint;
        $this->link = $link;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasTypes(): bool
    {
        return (bool) count($this->types);
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function hasHint(): bool
    {
        return (bool) $this->hint;
    }

    public function getHint(): string
    {
        return $this->hint;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function isCallable(): bool
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
            'types' => $this->types,
            'hint' => $this->hint,
            'link' => $this->link,
        ];
    }
}

<?php

namespace uuf6429\Rune\TypeInfo;

use ArrayAccess;
use Closure;
use JetBrains\PhpStorm\ArrayShape;

abstract class TypeInfoBase
{
    protected string $name;

    protected ?string $hint;

    protected ?string $link;

    /**
     * @var string[]
     */
    protected array $types;

    /**
     * @param string[] $types
     */
    public function __construct(string $name, array $types, ?string $hint = null, ?string $link = null)
    {
        $this->name = $name;
        $this->hint = $hint;
        $this->link = $link;
        $this->types = $types;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasHint(): bool
    {
        return (bool)strlen(trim((string)$this->hint));
    }

    public function getHint(): ?string
    {
        return $this->hasHint() ? $this->hint : null;
    }

    public function hasLink(): bool
    {
        return (bool)strlen(trim((string)$this->link));
    }

    public function getLink(): ?string
    {
        return $this->hasLink() ? $this->link : null;
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function isIndexable(): bool
    {
        static $indexableTypes = ['array', ArrayAccess::class];

        return !empty(array_intersect($this->types, $indexableTypes));
    }

    public function isInvokable(): bool
    {
        static $invokableTypes = ['callable', 'method', Closure::class];

        return !empty(array_intersect($this->types, $invokableTypes))
            || method_exists($this->name, '__invoke');
    }

    #[ArrayShape(['name' => 'string', 'hint' => 'null|string', 'link' => 'null|string'])]
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'hint' => $this->getHint(),
            'link' => $this->getLink(),
            'types' => $this->getTypes(),
        ];
    }
}

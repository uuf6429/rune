<?php

namespace uuf6429\Rune\TypeInfo;

abstract class TypeInfoBase
{

    protected string $name;

    protected ?string $hint;

    protected ?string $link;

    public function __construct(string $name, ?string $hint = null, ?string $link = null)
    {
        $this->name = $name;
        $this->hint = $hint;
        $this->link = $link;
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

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'hint' => $this->getHint(),
            'link' => $this->getLink(),
        ];
    }
}

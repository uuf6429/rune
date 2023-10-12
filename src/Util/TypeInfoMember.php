<?php declare(strict_types=1);

namespace uuf6429\Rune\Util;

class TypeInfoMember
{
    protected string $name;

    /**
     * @var string[]
     */
    protected array $types;

    protected ?string $hint;

    protected ?string $link;

    /**
     * @param string[] $types
     */
    public function __construct(string $name, array $types, ?string $hint = null, ?string $link = null)
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
        return (bool)count($this->types);
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

    public function isCallable(): bool
    {
        // Note: __invoke is not supported here... would have been nice of PHP to have an Invokable interface
        static $callableTypes = ['callable', 'Closure', 'method'];

        return !empty(array_intersect($this->types, $callableTypes));
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'types' => $this->getTypes(),
            'hint' => $this->getHint(),
            'link' => $this->getLink(),
        ];
    }
}

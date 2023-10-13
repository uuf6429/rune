<?php declare(strict_types=1);

namespace uuf6429\Rune\TypeInfo;

class TypeInfoMember extends TypeInfoBase
{
    /**
     * @var string[]
     */
    protected array $types;

    /**
     * @param string[] $types
     */
    public function __construct(string $name, array $types, ?string $hint = null, ?string $link = null)
    {
        parent::__construct($name, $hint, $link);

        $this->types = $types;
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

    public function isCallable(): bool
    {
        // Note: __invoke is not supported here... would have been nice of PHP to have an Invokable interface
        static $callableTypes = ['callable', 'Closure', 'method'];

        return !empty(array_intersect($this->types, $callableTypes));
    }

    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'types' => $this->getTypes(),
        ];
    }
}

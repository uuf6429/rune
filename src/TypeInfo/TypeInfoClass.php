<?php declare(strict_types=1);

namespace uuf6429\Rune\TypeInfo;

use JetBrains\PhpStorm\ArrayShape;

class TypeInfoClass extends TypeInfoBase
{
    /**
     * @var array<TypeInfoProperty|TypeInfoMethod>
     */
    protected array $members = [];

    /**
     * @param array<TypeInfoProperty|TypeInfoMethod> $members
     */
    public function __construct(string $name, array $members, ?string $hint = null, ?string $link = null)
    {
        $types = [
            'class',
            ...array_values(class_parents($name) ?: []),
            ...array_values(class_implements($name) ?: []),
        ];

        parent::__construct($name, $types, $hint, $link);

        $this->members = array_combine(
            array_map(
                static fn ($member) => $member->getName(),
                $members
            ),
            $members
        );
    }

    /**
     * @return array<TypeInfoProperty|TypeInfoMethod>
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    #[ArrayShape(['name' => 'string', 'hint' => 'null|string', 'link' => 'null|string', 'members' => 'array'])]
    public function toArray(?callable $serializer = null): array
    {
        $result = array_merge(parent::toArray($serializer), [
            'members' => array_map(
                static fn ($member) => $member->toArray($serializer),
                $this->getMembers()
            ),
        ]);

        return $serializer ? $serializer($this, $result) : $result;
    }
}

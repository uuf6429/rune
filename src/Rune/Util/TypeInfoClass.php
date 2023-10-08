<?php

namespace uuf6429\Rune\Util;

class TypeInfoClass
{
    public string $name;

    /**
     * @var TypeInfoMember[]
     */
    public array $members = [];

    public ?string $hint;

    public ?string $link;

    /**
     * @param TypeInfoMember[] $members
     */
    public function __construct(string $name, array $members, ?string $hint = null, ?string $link = null)
    {
        $this->name = $name;
        $this->members = array_combine(
            array_map(
                static fn (TypeInfoMember $member) => $member->getName(),
                $members
            ),
            $members
        );
        $this->hint = $hint;
        $this->link = $link;
    }
}

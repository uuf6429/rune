<?php

namespace uuf6429\Rune\Util;

class TypeInfoClass
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var TypeInfoMember[]
     */
    public $members = [];

    /**
     * @var string
     */
    public $hint;

    /**
     * @var string
     */
    public $link;

    /**
     * @param TypeInfoMember[] $members
     */
    public function __construct(string $name, array $members = [], string $hint = '', string $link = '')
    {
        $this->name = $name;
        $this->hint = $hint;
        $this->link = $link;

        foreach ($members as $member) {
            $this->members[$member->getName()] = $member;
        }
    }
}

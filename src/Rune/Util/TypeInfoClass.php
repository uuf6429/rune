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
     * @param string         $name
     * @param TypeInfoMember $members
     * @param string         $hint
     * @param string         $link
     */
    public function __construct($name, $members = [], $hint = '', $link = '')
    {
        $this->name = $name;
        $this->hint = $hint;
        $this->link = $link;

        foreach ($members as $member) {
            $this->members[$member->name] = $member;
        }
    }
}

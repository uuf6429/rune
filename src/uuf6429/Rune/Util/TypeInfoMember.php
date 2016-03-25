<?php

namespace uuf6429\Rune\Util;

class TypeInfoMember
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $types;

    /**
     * @var string
     */
    public $hint;

    /**
     * @var string
     */
    public $link;

    /**
     * @param string   $name
     * @param string[] $types
     * @param string   $hint
     * @param string   $link
     */
    public function __construct($name, $types, $hint = '', $link = '')
    {
        $this->name = $name;
        $this->types = $types;
        $this->hint = $hint;
        $this->link = $link;
    }
}

<?php

namespace uuf6429\Rune\Model;

class Category extends AbstractModel
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /**
     * @param int    $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

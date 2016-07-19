<?php

namespace uuf6429\Rune\example\Model;

use uuf6429\Rune\Model\AbstractModel;

/**
 * @property uuf6429\Rune\example\Model\Category $parent
 */
class Category extends AbstractModel
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var int */
    protected $parentId;

    /** @var callable */
    protected $categoryProvider;

    /**
     * @param int      $id
     * @param string   $name
     * @param int      $parentId
     * @param callable $categoryProvider Returns category given $id as first param.
     */
    public function __construct($id, $name, $parentId, $categoryProvider)
    {
        $this->id = $id;
        $this->name = $name;

        $this->parentId = $parentId;
        $this->categoryProvider = $categoryProvider;
    }

    /**
     * @return Category
     */
    protected function getParent()
    {
        $call = $this->categoryProvider;

        return $call($this->parentId);
    }

    /**
     * Returns true if category name or any of its parents are identical to $name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function in($name)
    {
        return $this->findParent($name) ? true : false;
    }

    /**
     * Returns the first (parent) category that matches name.
     *
     * @param string $name
     *
     * @return \uuf6429\Rune\example\Model\Category|null
     */
    public function findParent($name)
    {
        if (strtolower($this->name) == strtolower($name)) {
            return $this;
        } elseif ($this->parent !== null) {
            return $this->parent->findParent($name);
        } else {
            return;
        }
    }
}

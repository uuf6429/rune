<?php

namespace uuf6429\Rune\Example\Model;

use uuf6429\Rune\Util\LazyProperties;

/**
 * @property \uuf6429\Rune\Example\Model\Category $category
 */
class Product
{
    use LazyProperties;

    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $colour;

    /** @var int */
    protected $categoryId;

    /** @var callable */
    protected $categoryProvider;

    /**
     * @param int      $id
     * @param string   $name
     * @param string   $colour
     * @param int      $categoryId
     * @param callable $categoryProvider returns category given $id as first param
     */
    public function __construct($id, $name, $colour, $categoryId, $categoryProvider)
    {
        $this->id = $id;
        $this->name = $name;
        $this->colour = $colour;

        $this->categoryId = $categoryId;
        $this->categoryProvider = $categoryProvider;
    }

    /**
     * @return Category
     */
    protected function getCategory()
    {
        $call = $this->categoryProvider;

        return $call($this->categoryId);
    }
}

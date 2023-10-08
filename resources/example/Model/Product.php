<?php

namespace uuf6429\Rune\Example\Model;

use uuf6429\Rune\Util\LazyProperties;

/**
 * @property \uuf6429\Rune\Example\Model\Category $category
 */
class Product
{
    use LazyProperties;

    public int $id;

    public string $name;

    public string $colour;

    protected int $categoryId;

    /** @var callable */
    protected $categoryProvider;

    public function __construct(int $id, string $name, string $colour, int $categoryId, callable $categoryProvider)
    {
        $this->id = $id;
        $this->name = $name;
        $this->colour = $colour;
        $this->categoryId = $categoryId;
        $this->categoryProvider = $categoryProvider;
    }

    protected function getCategory(): Category
    {
        $call = $this->categoryProvider;

        return $call($this->categoryId);
    }
}

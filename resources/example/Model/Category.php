<?php

namespace uuf6429\Rune\Example\Model;

use uuf6429\Rune\Util\LazyProperties;

/**
 * @property null|\uuf6429\Rune\Example\Model\Category $parent
 */
class Category
{
    use LazyProperties;

    public int $id;

    public string $name;

    protected int $parentId;

    /** @var callable */
    protected $categoryProvider;

    public function __construct(int $id, string $name, int $parentId, callable $categoryProvider)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parentId = $parentId;
        $this->categoryProvider = $categoryProvider;
    }

    protected function getParent(): ?Category
    {
        $call = $this->categoryProvider;

        return $call($this->parentId);
    }

    /**
     * Returns true if category name or any of its parents are identical to $name.
     */
    public function in(string $name): bool
    {
        if (strtolower($this->name) === strtolower($name)) {
            return true;
        }

        if ($this->parent !== null) {
            return $this->parent->in($name);
        }

        return false;
    }
}

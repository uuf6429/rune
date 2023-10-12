<?php declare(strict_types=1);

namespace uuf6429\Rune\Example\Model;

// We use a class alias for testing and showcasing phpdoc class resolving mechanism.
use uuf6429\Rune\Example\Model\Category as CategoryModel;
use uuf6429\Rune\Util\LazyProperties;

/**
 * @property CategoryModel $category
 */
class Product
{
    use LazyProperties;

    public int $id;

    public string $name;

    /**
     * A valid CSS color name.
     * @link https://www.w3.org/wiki/CSS/Properties/color/keywords
     */
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

    protected function getCategory(): CategoryModel
    {
        $call = $this->categoryProvider;

        return $call($this->categoryId);
    }
}

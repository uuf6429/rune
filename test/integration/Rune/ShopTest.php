<?php

namespace uuf6429\Rune;

use uuf6429\Rune\Example\Action;
use uuf6429\Rune\Example\Context;
use uuf6429\Rune\Example\Model;

class ShopTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleEngine()
    {
        $this->expectOutputString(implode(PHP_EOL, [
            'Rule 1 (Red Products) triggered for Red Bricks.',
            'Rule 5 (Toys) triggered for Red Bricks.',
            'Rule 3 (Green Socks) triggered for Green Soft Socks.',
            'Rule 4 (Socks) triggered for Green Soft Socks.',
            'Rule 6 (Clothes) triggered for Green Soft Socks.',
            'Rule 4 (Socks) triggered for Yellow Sporty Socks.',
            'Rule 6 (Clothes) triggered for Yellow Sporty Socks.',
            'Rule 5 (Toys) triggered for Lego Blocks.',
            'Rule 6 (Clothes) triggered for Black Adidas Jacket.',
        ]) . PHP_EOL);

        $engine = new Engine();
        $engine->execute($this->getContexts($this->getAction()), $this->getRules());

        $this->assertSame('', implode(PHP_EOL, $engine->getErrors()), 'RuleEngine should not generate errors.');
    }

    /**
     * @return GenericRule[]
     */
    protected function getRules()
    {
        return [
            new Rule\GenericRule(1, 'Red Products', 'product.colour == "red"'),
            new Rule\GenericRule(2, 'Red Socks', 'product.colour == "red" and (product.name matches "/socks/i") > 0'),
            new Rule\GenericRule(3, 'Green Socks', 'product.colour == "green" and (product.name matches "/socks/i") > 0'),
            new Rule\GenericRule(4, 'Socks', 'product.category.in("Socks")'),
            new Rule\GenericRule(5, 'Toys', 'product.category.in("Toys")'),
            new Rule\GenericRule(6, 'Clothes', 'product.category.in("Clothes")'),
        ];
    }

    /**
     * @return Model\Product
     */
    protected function getProducts()
    {
        $cp = $this->getCategoryProvider();

        return [
            new Model\Product(1, 'Bricks', 'red', 3, $cp),
            new Model\Product(2, 'Soft Socks', 'green', 6, $cp),
            new Model\Product(3, 'Sporty Socks', 'yellow', 6, $cp),
            new Model\Product(4, 'Lego Blocks', '', 3, $cp),
            new Model\Product(6, 'Adidas Jacket', 'black', 5, $cp),
        ];
    }

    /**
     * @return Model\Category[]
     */
    protected function getCategories()
    {
        $cp = $this->getCategoryProvider();

        return [
            new Model\Category(1, 'Root', 0, $cp),
            new Model\Category(2, 'Clothes', 1, $cp),
            new Model\Category(3, 'Toys', 1, $cp),
            new Model\Category(4, 'Underwear', 2, $cp),
            new Model\Category(5, 'Jackets', 2, $cp),
            new Model\Category(6, 'Socks', 4, $cp),
        ];
    }

    /**
     * @return callable
     */
    protected function getCategoryProvider()
    {
        return [$this, 'getCategory'];
    }

    /**
     * @param int $id
     *
     * @return Model\Category|null
     */
    public function getCategory($id)
    {
        foreach ($this->getCategories() as $category) {
            if ($category->id == $id) {
                return $category;
            }
        }

        return;
    }

    /**
     * @param Action\AbstractAction $action
     *
     * @return Context\ContextInterface[]
     */
    protected function getContexts($action)
    {
        return array_map(
            function ($product) use ($action) {
                return new Context\ProductContext($action, $product);
            },
            $this->getProducts()
        );
    }

    /**
     * @return Action\AbstractAction
     */
    protected function getAction()
    {
        return new Action\PrintAction();
    }
}

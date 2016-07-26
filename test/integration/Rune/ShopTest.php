<?php

namespace uuf6429\Rune;

use uuf6429\Rune\example\Action;
use uuf6429\Rune\example\Context;
use uuf6429\Rune\example\Model;

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

        $errors = [];
        $engine = new Engine();
        $action = new Action\PrintAction();

        foreach ($this->getProducts() as $product) {
            $context = new Context\ProductContext($product);
            $engine->execute($context, $this->getRules(), $action);
            $errors += $engine->getErrors();
        }

        $this->assertSame('', implode(PHP_EOL, $errors), 'RuleEngine should not generate errors.');
    }

    public function testExampleTypeInfo()
    {
        $context = new Context\ProductContext();
        $descriptor = $context->getContextDescriptor();

        $this->assertEquals(
            [
                'product' => new Util\TypeInfoMember('product', ['uuf6429\Rune\example\Model\Product']),
                'String' => new Util\TypeInfoMember('String', ['uuf6429\Rune\example\Model\StringUtils']),
            ],
            $descriptor->getVariableTypeInfo(),
            'Assert variable type information'
        );

        $this->assertEquals(
            [
                //'lower' => new Util\TypeInfoMember('lower', ['method'], '<div class="cm-signature"><span class="type">string</span> <span class="name">lower</span>(<span class="args"><span class="arg" title=""><span class="type">string </span>$text</span></span>)</span></div>Lowercases some text.'),
            ],
            $descriptor->getFunctionTypeInfo(),
            'Assert function type information'
        );

        $this->assertEquals(
            [
                'uuf6429\Rune\example\Context\ProductContext' => new Util\TypeInfoClass(
                    'uuf6429\Rune\example\Context\ProductContext',
                    [
                        'product' => new Util\TypeInfoMember('product', ['uuf6429\Rune\example\Model\Product']),
                        'getContextDescriptor' => new Util\TypeInfoMember('getContextDescriptor', ['method'], '<div class="cm-signature"><span class="type"></span> <span class="name">getContextDescriptor</span>(<span class="args"></span>)</span></div>'),
                        'String' => new Util\TypeInfoMember('String', ['uuf6429\Rune\example\Model\StringUtils'], ''),
                    ]
                ),
                'uuf6429\Rune\example\Model\Product' => new Util\TypeInfoClass(
                    'uuf6429\Rune\example\Model\Product',
                    [
                        'id' => new Util\TypeInfoMember('id', ['integer']),
                        'name' => new Util\TypeInfoMember('name', ['string']),
                        'colour' => new Util\TypeInfoMember('colour', ['string']),
                        'category' => new Util\TypeInfoMember('category', ['uuf6429\Rune\example\Model\Category']),
                    ]
                ),
                'uuf6429\Rune\example\Model\Category' => new Util\TypeInfoClass(
                    'uuf6429\Rune\example\Model\Category',
                    [
                        'id' => new Util\TypeInfoMember('id', ['integer']),
                        'name' => new Util\TypeInfoMember('name', ['string']),
                        'in' => new Util\TypeInfoMember('in', ['method'], '<div class="cm-signature"><span class="type">bool</span> <span class="name">in</span>(<span class="args"><span class="arg" title=""><span class="type">string </span>$name</span></span>)</span></div>Returns true if category name or any of its parents are identical to $name.'),
                        'parent' => new Util\TypeInfoMember('parent', ['uuf6429\Rune\example\Model\Category']),
                    ]
                ),
                'uuf6429\Rune\example\Model\StringUtils' => new Util\TypeInfoClass(
                    'uuf6429\Rune\example\Model\StringUtils',
                    [
                        'lower' => new Util\TypeInfoMember('lower', ['method'], '<div class="cm-signature"><span class="type">string</span> <span class="name">lower</span>(<span class="args"><span class="arg" title=""><span class="type">string </span>$text</span></span>)</span></div>Lowercases some text.'),
                        'upper' => new Util\TypeInfoMember('upper', ['method'], '<div class="cm-signature"><span class="type">string</span> <span class="name">upper</span>(<span class="args"><span class="arg" title=""><span class="type">string </span>$text</span></span>)</span></div>Uppercases some text.'),
                    ]
                ),
            ],
            $descriptor->getDetailedTypeInfo(),
            'Assert detailed type information'
        );
    }

    /**
     * @return GenericRule[]
     */
    protected function getRules()
    {
        return [
            new Rule\GenericRule(1, 'Red Products', 'product.colour == String.lower("Red")'),
            new Rule\GenericRule(2, 'Red Socks', 'String.upper(product.colour) == "RED" and (product.name matches "/socks/i") > 0'),
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
}

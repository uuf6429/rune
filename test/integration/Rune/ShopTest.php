<?php

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace uuf6429\Rune;

use PHPUnit\Framework\TestCase;
use uuf6429\Rune\example\Action;
use uuf6429\Rune\example\Context;
use uuf6429\Rune\example\Context\ProductContext;
use uuf6429\Rune\example\Model;
use uuf6429\Rune\example\Model\Category;
use uuf6429\Rune\example\Model\Product;
use uuf6429\Rune\example\Model\StringUtils;

class ShopTest extends TestCase
{
    public function testSimpleEngine(): void
    {
        $this->expectOutputString(
            implode(PHP_EOL, [
                'Rule 1 (Red Products) triggered for Red Bricks.',
                'Rule 5 (Toys) triggered for Red Bricks.',
                'Rule 3 (Green Socks) triggered for Green Soft Socks.',
                'Rule 4 (Socks) triggered for Green Soft Socks.',
                'Rule 6 (Clothes) triggered for Green Soft Socks.',
                'Rule 4 (Socks) triggered for Yellow Sporty Socks.',
                'Rule 6 (Clothes) triggered for Yellow Sporty Socks.',
                'Rule 5 (Toys) triggered for Lego Blocks.',
                'Rule 6 (Clothes) triggered for Black Adidas Jacket.',
            ]) . PHP_EOL
        );

        $exceptions = new Exception\ExceptionCollectorHandler();
        $engine = new Engine($exceptions);
        $action = new Action\PrintAction();

        foreach ($this->getProducts() as $product) {
            $context = new Context\ProductContext($product);
            $engine->execute($context, $this->getRules(), $action);
        }

        $this->assertSame(
            '',
            implode(PHP_EOL, $exceptions->getExceptions()),
            'RuleEngine should not generate errors.'
        );
    }

    public function testExampleTypeInfo(): void
    {
        $context = new Context\ProductContext();
        $descriptor = $context->getContextDescriptor();

        $this->assertEquals(
            [
                'product' => new Util\TypeInfoMember('product', [Product::class]),
                'String' => new Util\TypeInfoMember('String', [StringUtils::class]),
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
                ProductContext::class => new Util\TypeInfoClass(
                    ProductContext::class,
                    [
                        'product' => new Util\TypeInfoMember('product', [Product::class]),
                        'getContextDescriptor' => new Util\TypeInfoMember('getContextDescriptor', ['method'], '<div class="cm-signature"><span class="type"></span> <span class="name">getContextDescriptor</span>(<span class="args"></span>)</span></div>'),
                        'String' => new Util\TypeInfoMember('String', [StringUtils::class], ''),
                    ]
                ),
                Product::class => new Util\TypeInfoClass(
                    Product::class,
                    [
                        'id' => new Util\TypeInfoMember('id', ['integer']),
                        'name' => new Util\TypeInfoMember('name', ['string']),
                        'colour' => new Util\TypeInfoMember('colour', ['string']),
                        'category' => new Util\TypeInfoMember('category', [Category::class]),
                    ]
                ),
                Category::class => new Util\TypeInfoClass(
                    Category::class,
                    [
                        'id' => new Util\TypeInfoMember('id', ['integer']),
                        'name' => new Util\TypeInfoMember('name', ['string']),
                        'in' => new Util\TypeInfoMember('in', ['method'], '<div class="cm-signature"><span class="type">bool</span> <span class="name">in</span>(<span class="args"><span class="arg" title=""><span class="type">string </span>$name</span></span>)</span></div>Returns true if category name or any of its parents are identical to $name.'),
                        'parent' => new Util\TypeInfoMember('parent', [Category::class]),
                    ]
                ),
                StringUtils::class => new Util\TypeInfoClass(
                    StringUtils::class,
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
     * @return Rule\GenericRule[]
     */
    protected function getRules(): array
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
     * @return Model\Product[]
     */
    protected function getProducts(): array
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
    protected function getCategories(): array
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

    protected function getCategoryProvider(): callable
    {
        return [$this, 'getCategory'];
    }

    public function getCategory(int $id): ?Category
    {
        foreach ($this->getCategories() as $category) {
            if ($category->id === $id) {
                return $category;
            }
        }

        return null;
    }
}

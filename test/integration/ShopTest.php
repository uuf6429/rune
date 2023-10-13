<?php declare(strict_types=1);

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace uuf6429\Rune;

use PHPUnit\Framework\TestCase;
use uuf6429\Rune\Example\Action;
use uuf6429\Rune\Example\Context;
use uuf6429\Rune\Example\Context\ProductContext;
use uuf6429\Rune\Example\Model;
use uuf6429\Rune\Example\Model\Category;
use uuf6429\Rune\Example\Model\Product;
use uuf6429\Rune\Example\Model\StringUtils;

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

        $exceptions = new Engine\ExceptionHandler\CollectExceptions();
        $engine = new Engine(null, null, $exceptions);

        foreach ($this->getProducts() as $product) {
            $context = new Context\ProductContext($product);
            $engine->execute($context, $this->getRules());
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
                'product' => new TypeInfo\TypeInfoProperty('product', [Product::class, 'null']),
                'String' => new TypeInfo\TypeInfoProperty('String', [StringUtils::class]),
            ],
            $descriptor->getVariableTypeInfo(),
            'Check variable type information'
        );
        $this->assertEquals(
            [],
            $descriptor->getFunctionTypeInfo(),
            'Check function type information'
        );
        $this->assertEquals(
            [
                ProductContext::class => new TypeInfo\TypeInfoClass(
                    ProductContext::class,
                    [
                        'product' => new TypeInfo\TypeInfoProperty('product', [Product::class, 'null']),
                        'String' => new TypeInfo\TypeInfoProperty('String', [StringUtils::class], ''),
                    ]
                ),
                Product::class => new TypeInfo\TypeInfoClass(
                    Product::class,
                    [
                        'id' => new TypeInfo\TypeInfoProperty('id', ['integer']),
                        'name' => new TypeInfo\TypeInfoProperty('name', ['string']),
                        'colour' => new TypeInfo\TypeInfoProperty(
                            'colour',
                            ['string'],
                            'A valid CSS color name.',
                            'https://www.w3.org/wiki/CSS/Properties/color/keywords',
                        ),
                        'category' => new TypeInfo\TypeInfoProperty('category', [Category::class]),
                    ]
                ),
                Category::class => new TypeInfo\TypeInfoClass(
                    Category::class,
                    [
                        'id' => new TypeInfo\TypeInfoProperty('id', ['integer']),
                        'name' => new TypeInfo\TypeInfoProperty('name', ['string']),
                        'in' => new TypeInfo\TypeInfoMethod(
                            'in',
                            [
                                new TypeInfo\TypeInfoParameter('name', ['string'], null, null),
                            ],
                            <<<'HTML'
                            <div class="cm-signature">
                                <span class="name">in</span>(<span class="args"><span class="arg" title=""><span class="type">string </span>$name</span></span></span>): <span class="type">boolean</span>
                            </div>Returns true if category name or any of its parents are identical to `$name`.
                            HTML
                        ),
                        'parent' => new TypeInfo\TypeInfoProperty('parent', ['null', Category::class]),
                    ]
                ),
                StringUtils::class => new TypeInfo\TypeInfoClass(
                    StringUtils::class,
                    [
                        'lower' => new TypeInfo\TypeInfoMethod(
                            'lower',
                            [
                                new TypeInfo\TypeInfoParameter('text', ['mixed'], null, null),
                            ],
                            <<<'HTML'
                            <div class="cm-signature">
                                <span class="name">lower</span>(<span class="args"><span class="arg" title=""><span class="type">mixed </span>$text</span></span></span>): <span class="type">string</span>
                            </div>Lowercases some text.
                            HTML
                        ),
                        'upper' => new TypeInfo\TypeInfoMethod(
                            'upper',
                            [
                                new TypeInfo\TypeInfoParameter('text', ['string'], null, null),
                            ],
                            <<<'HTML'
                            <div class="cm-signature">
                                <span class="name">upper</span>(<span class="args"><span class="arg" title=""><span class="type">string </span>$text</span></span></span>): <span class="type">string</span>
                            </div>Uppercases some text.
                            HTML
                        ),
                    ]
                ),
            ],
            $descriptor->getDetailedTypeInfo(),
            'Check detailed type information'
        );
    }

    /**
     * @return Rule\GenericRule[]
     */
    protected function getRules(): array
    {
        $action = new Action\PrintAction();

        return [
            new Rule\GenericRule('1', 'Red Products', 'product.colour == String.lower("Red")', $action),
            new Rule\GenericRule('2', 'Red Socks', 'String.upper(product.colour) == "RED" and (product.name matches "/socks/i") > 0', $action),
            new Rule\GenericRule('3', 'Green Socks', 'product.colour == "green" and (product.name matches "/socks/i") > 0', $action),
            new Rule\GenericRule('4', 'Socks', 'product.category.in("Socks")', $action),
            new Rule\GenericRule('5', 'Toys', 'product.category.in("Toys")', $action),
            new Rule\GenericRule('6', 'Clothes', 'product.category.in("Clothes")', $action),
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

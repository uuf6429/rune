<?php

namespace uuf6429\Rune;

use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Context\DynamicContext;
use uuf6429\Rune\Rule\GenericRule;
use uuf6429\Rune\Util\ContextField;

class SimpleEngineTestProduct {
    /** @var string */
    public $name;

    /** @var string */
    public $colour;

    public function __construct($name, $colour) {
        $this->name = $name;
        $this->colour = $colour;
    }
}

class SimpleEngineTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleEngine()
    {
        $this->expectOutputString(implode('', [
            'Rule 1 (Red Products) triggered for Red Bricks' . PHP_EOL,
            'Rule 3 (Green Socks) triggered for Green Soft Socks' . PHP_EOL,
            'Rule 4 (Socks) triggered for Green Soft Socks' . PHP_EOL,
            'Rule 4 (Socks) triggered for Yellow Sporty Socks' . PHP_EOL,
        ]));

        $engine = new Engine($this->getContexts($this->getAction()), $this->getRules());
        $engine->execute();

        $this->assertEmpty($engine->getErrors(), 'RuleEngine should not generate errors.');
    }

    protected function getRules()
    {
        return [
            new GenericRule(1, 'Red Products', 'product.colour == "red"'),
            new GenericRule(2, 'Red Socks', 'product.colour == "red" and (product.name matches "/socks/i") > 0'),
            new GenericRule(3, 'Green Socks', 'product.colour == "green" and (product.name matches "/socks/i") > 0'),
            new GenericRule(4, 'Socks', '(product.name matches "/socks/i") > 0'),
        ];
    }

    protected function getProducts()
    {
        return [
            new SimpleEngineTestProduct('Bricks', 'red'),
            new SimpleEngineTestProduct('Soft Socks', 'green'),
            new SimpleEngineTestProduct('Sporty Socks', 'yellow'),
        ];
    }

    protected function getContexts($action)
    {
        return array_map(
            function ($product) use($action) {
                $fields = [
                    new ContextField('product', get_class($product), null, null, $product),
                ];
                return new DynamicContext($action, $fields);
            },
            $this->getProducts()
        );
    }

    protected function getAction() {
        return new CallbackAction(
            function ($eval, Context\AbstractContext $context, $rule) {
                /** @var SimpleEngineTestProduct $product */
                $product = $context->getValue('product');
                printf(
                    'Rule %s (%s) triggered for %s %s' . PHP_EOL,
                    $rule->getID(),
                    $rule->getName(),
                    ucwords($product->colour),
                    $product->name
                );
            }
        );
    }
}

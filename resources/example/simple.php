<?php declare(strict_types=1);

namespace uuf6429\Rune;

// A class whose instances will be available inside rule engine.
class Product
{
    public string $name;

    /** @var mixed */
    public $colour;

    /**
     * @param mixed $colour
     */
    public function __construct(string $name, $colour)
    {
        $this->name = $name;
        $this->colour = $colour;
    }
}

// A class that represents the rule engine execution context.
// Note that public properties will be available in the rule expressions,
// in this case rules will have access to "product" as a variable (and all of product's public properties).
class ProductContext extends Context\ClassContext
{
    /** @var Product */
    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}

// Declare an action to be triggered when a rule matches against a product.
$action = new Action\CallbackAction(
    function ($eval, ProductContext $context, $rule) {
        printf(
            'Rule %s triggered for %s %s<br/>',
            $rule->getId(),
            ucwords($context->product->colour),
            $context->product->name
        );
    }
);

// Declare some sample rules.
$rules = [
    new Rule\GenericRule('1', 'Red Products', 'product.colour == "red"', $action),
    new Rule\GenericRule('2', 'Red Socks', 'product.colour == "red" and product.name matches "/socks/i"', $action),
    new Rule\GenericRule('3', 'Green Socks', 'product.colour == "green" and product.name matches "/socks/i"', $action),
    new Rule\GenericRule('4', 'Socks', 'product.name matches "/socks/"', $action),
];

// Declare available products (to run rules against).
$products = [
    new Product('Bricks', 'red'),
    new Product('Soft Socks', 'green'),
    new Product('Sporty Socks', 'yellow'),
];

// Create rule engine.
$engine = new Engine();

// Run rules for each product. Note that each product exists in a separate context.
foreach ($products as $product) {
    $engine->execute(new ProductContext($product), $rules);
}

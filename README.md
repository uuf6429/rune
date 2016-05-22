Rune
====

[![Build Status](https://api.travis-ci.com/uuf6429/rune.svg?token=x4iDoZNEE7xwqHqGpu82)](https://travis-ci.com/uuf6429/rune)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/uuf6429/nice_r/master/LICENSE)
[![Coverage](https://codecov.io/gh/uuf6429/rune/branch/master/graph/badge.svg?token=Bu2nK2Kq77)](https://codecov.io/github/uuf6429/rune?branch=master)

Rune - A PHP <b>Ru</b>le Engi<b>ne</b> Toolkit.

This library is an implementation of a Business Rule Engine (a type of Business Process Automation software).

Table Of Contents
-----------------

- [Rune](#rune)
  - [Table Of Contents](#table-of-contents)
  - [Usage](#usage)
  - [Live Example](#live-example)
  - [Screenshot](#screenshot)
  - [Example Code](#example-code)


Usage
-----

The library is made up of several parts:

- Action - an object that performs an action when a rule condition is true. Actions in general can be reused.
- Context - an object that provides data to the rule engine and action to work with.
  You almost always have to implement your own context since this always depends on your scenario.
- Rule(s) - a list of rules, objects containing a string expression (for the rule engine) and data (for the action).
  For complicated scenarios, you might want to extend the rule (by implementing [`Rule\RuleInterface`](https://github.com/uuf6429/rune/blob/master/src/Rune/Rule/RuleInterface.php)), otherwise [`Rule\GenericRule`](https://github.com/uuf6429/rune/blob/master/src/Rune/Rule/GenericRule.php) should be enough.
- RuleEngine - the object that connects the others together to function.

Live Example
------------

[Click here](http://192.237.167.233/rune-demo/) to try out the engine and interactive editor!

Screenshot
----------

The following is a screen shot for the sample provided in [`example/` directory](https://github.com/uuf6429/rune/tree/master/example).
![Screenshot](http://i.imgur.com/YLFAwxI.png)

Example Code
------------

This is a [simple example](https://github.com/uuf6429/rune/tree/master/example/simple.php) on the practical use of the rule engine.

```php
namespace uuf6429\Rune;

// A class whose instances will be available inside rule engine.
class Product extends Model\AbstractModel
{
    /** @var string */
    public $name;

    /** @var string */
    public $colour;

    public function __construct($name, $colour)
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

// Declare some sample rules.
$rules = [
    new Rule\GenericRule(1, 'Red Products', 'product.colour == "red"'),
    new Rule\GenericRule(2, 'Red Socks', 'product.colour == "red" and product.name matches "/socks/i"'),
    new Rule\GenericRule(3, 'Green Socks', 'product.colour == "green" and product.name matches "/socks/i"'),
    new Rule\GenericRule(4, 'Socks', 'product.name matches "/socks/"'),
];

// Declare available products (to run rules against).
$products = [
    new Product('Bricks', 'red'),
    new Product('Soft Socks', 'green'),
    new Product('Sporty Socks', 'yellow'),
];

// Declare an action to be triggered when a rule matches against a product.
$action = new Action\CallbackAction(
    function ($eval, ProductContext $context, $rule)
    {
        printf(
            'Rule %s triggered for %s %s<br/>',
            $rule->getID(),
            ucwords($context->product->colour),
            $context->product->name
        );
    }
);

// Create rule engine.
$engine = new Engine();

// Run rules for each product. Note that each product exists in a separate context.
foreach ($products as $product) {
    $engine->execute(new ProductContext($product), $rules, $action);
}
```

# ᚱᚢᚾᛖ

[![Build Status](https://github.com/uuf6429/rune/actions/workflows/ci.yml/badge.svg)](https://github.com/uuf6429/rune/actions)
[![Latest Stable Version](https://poser.pugx.org/uuf6429/rune/version.svg)](https://packagist.org/packages/uuf6429/rune)
[![Latest Unstable Version](https://poser.pugx.org/uuf6429/rune/v/unstable.svg)](https://packagist.org/packages/uuf6429/rune)
[![PHP Version Require](http://poser.pugx.org/uuf6429/rune/require/php)](https://www.php.net/supported-versions.php)
[![License](https://poser.pugx.org/uuf6429/rune/license.svg)](https://raw.githubusercontent.com/uuf6429/rune/master/LICENSE)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=uuf6429_rune&metric=coverage)](https://sonarcloud.io/summary/new_code?id=uuf6429_rune)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=uuf6429_rune&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=uuf6429_rune)

Rune - A PHP <b>Ru</b>le Engi<b>ne</b> Toolkit.

This library is an implementation of a [Business Rule Engine](https://en.wikipedia.org/wiki/Business_rules_engine) (a
type of Business Process Automation software).

## Table Of Contents

- [ᚱᚢᚾᛖ](#ᚱᚢᚾᛖ)
    - [Table Of Contents](#table-of-contents)
    - [Installation](#installation)
    - [Usage](#usage)
    - [Live Example](#live-example)
    - [Screenshot](#screenshot)
    - [Example Code](#example-code)

## Installation

The recommended and easiest way to install Rune is through [Composer](https://getcomposer.org/):

```bash
composer require uuf6429/rune "^3"
```

## Usage

The library is made up of several parts:

- Action - an object that performs an action when a rule condition is true. Actions in general can be reused.
- Context - an object that provides data to the rule engine and action to work with.
  You almost always have to implement your own context since this always depends on your scenario.
- Rule(s) - a list of rules, objects containing a string expression (for the rule engine) and data (for the action).
  For complicated scenarios, you might want to extend the rule (by
  implementing [`Rule\RuleInterface`](https://github.com/uuf6429/rune/blob/master/src/Rune/Rule/RuleInterface.php)),
  otherwise [`Rule\GenericRule`](https://github.com/uuf6429/rune/blob/master/src/Rune/Rule/GenericRule.php) should be
  enough.
- RuleEngine - the object that connects the others together to function.

```plantuml
class TODO
```

## Live Example

[Click here](http://192.237.167.233/rune-demo/) to try out the engine and interactive editor!

## Screenshot

The following is a screenshot for the sample provided
in [`example/` directory](https://github.com/uuf6429/rune/tree/master/example).
![Screenshot](http://i.imgur.com/YLFAwxI.png)

## Example Code

This is a [simple example](https://github.com/uuf6429/rune/tree/master/example/simple.php) on the practical use of the
rule engine.

```php
namespace uuf6429\Rune;

// A class whose instances will be available inside rule engine.
class Product
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

// Declare an action to be triggered when a rule matches against a product.
$action = new Action\CallbackAction(
    function ($eval, ProductContext $context, $rule)
    {
        printf(
            "Rule %s triggered for %s %s\n",
            $rule->getId(),
            ucwords($context->product->colour),
            $context->product->name
        );
    }
);

// Declare some sample rules.
$rules = [
    new Rule\GenericRule(1, 'Red Products', 'product.colour == "red"', $action),
    new Rule\GenericRule(2, 'Red Socks', 'product.colour == "red" and product.name matches "/socks/i"', $action),
    new Rule\GenericRule(3, 'Green Socks', 'product.colour == "green" and product.name matches "/socks/i"', $action),
    new Rule\GenericRule(4, 'Socks', 'product.name matches "/socks/" > 0', $action),
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
```

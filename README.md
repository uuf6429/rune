Rune
====

[![Build Status](https://api.travis-ci.com/uuf6429/rune.svg?token=x4iDoZNEE7xwqHqGpu82)](https://travis-ci.com/uuf6429/rune)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/uuf6429/nice_r/master/LICENSE)
[![Coverage](https://img.shields.io/codecov/c/token/Bu2nK2Kq77/github/uuf6429/rune/master.svg)](https://codecov.io/github/uuf6429/rune?branch=master)

Rune - A PHP <b>Ru</b>le Engi<b>ne</b> Toolkit.

This library is an implementation of a Business Rule Engine (a type of Business Process Automation software).

Usage
-----

The library is made up of several parts:

- Action - an object that performs an action when a rule condition is true. Actions in general can be reused.
- Context - an object that provides data to the rule engine and action to work with.
  You almost always have to implement your own context since this always depends on your scenario.
- Rule(s) - a list of rules, objects containing a string expression (for the rule engine) and data (for the action).
  For complicated scenarios, you might want to extend the rule (by extending Rule\AbstractRule), otherwise Rule\Generic should be enough.
- RuleEngine - the object that connects the others together to function.

Screenshot
----------

A screen shot for the sample that is provided in [`example/` directory](https://github.com/uuf6429/rune/tree/master/example).
![Screenshot](http://i.imgur.com/UxOsE54.png)

Example
-------

```php
// A class whose instances will be available inside rule engine.
class Product extends AbstractModel
{
	/** @var string */
	public $name;

	/** @var string */
	public $colour;

	/**
     * @param string $name
     * @param string $colour
     */
	public function __construct($name, $colour)
	{
		$this->name = $name;
		$this->colour = $colour;
	}
}

// A class that represents the rule engine execution context.
class ProductContext extends ClassContext
{
	/** @var Product */
	public $product;

	/**
     * @param Product $product
     */
	public function __construct($product)
	{
		$this->product = $product;
	}
}

// Declare some sample rules.
$rules = [
	new GenericRule(1, 'Red Products', 'product.colour == "red"'),
	new GenericRule(2, 'Red Socks', 'product.colour == "red" AND product.name matches "/socks/"'),
	new GenericRule(3, 'Green Socks', 'product.colour == "green" AND product.name matches "/socks/"'),
	new GenericRule(4, 'Socks', 'product.name matches "/socks/"'),
];

// Declare available products (to run rules against).
$products = [
	new Product('Bricks', 'red'),
	new Product('Soft Socks', 'green'),
	new Product('Sporty Socks', 'yellow'),
];

// Declare an action to be triggered when a rule matches against a product.
$action = new CallbackAction(
	function ($eval, ProductContext $context, $rule)
	{
		printf(
			'Rule %s triggered for %s %s\n',
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

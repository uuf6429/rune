Rune
====

[![Build Status](https://img.shields.io/travis/uuf6429/rune.svg)](https://travis-ci.com/uuf6429/rune)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg?style=flat-square)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/uuf6429/nice_r/master/LICENSE)
[![Coverage](https://img.shields.io/codecov/c/token/Bu2nK2Kq77/github/uuf6429/rune.svg)](https://codecov.io/github/uuf6429/rune?branch=master)

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

Example
-------

```php
class Product {
	public $name;
	public $colour;
	public function __construct($name, $colour) {
		$this->name = $name;
		$this->colour = $colour;
	}
}

$rules = [
	new GenericRule(1, 'Red Products', 'product.colour == "red"'),
	new GenericRule(1, 'Red Socks', 'product.colour == "red" AND product.name matches "/socks/"'),
	new GenericRule(1, 'Green Socks', 'product.colour == "green" AND product.name matches "/socks/"'),
	new GenericRule(1, 'Socks', 'product.name matches "/socks/"'),
];

$products = [
	new Product('Bricks', 'red'),
	new Product('Soft Socks', 'green'),
	new Product('Sporty Socks', 'yellow'),
];

$action = new CallbackAction(
	function ($eval, $context, $rule) {
		/** @var Product $product */
		$product = $context->get('product');
		printf(
			'Rule %s triggered for %s %s\n',
			$rule->getID(),
			ucwords($product->colour),
			$product->name
		);
	}
);

$contexts = array_map(
	function ($product) use ($action) {
		return new DynamicContext('product', Product::class, null, null, $product);
	},
	$products
);

$engine = new Engine($contexts, $rules);
$engine->execute();
```
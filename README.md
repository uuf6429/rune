# ᚱᚢᚾᛖ

[![Build Status](https://github.com/uuf6429/rune/actions/workflows/ci.yml/badge.svg)](https://github.com/uuf6429/rune/actions)
[![Latest Stable Version](https://poser.pugx.org/uuf6429/rune/version.svg)](https://packagist.org/packages/uuf6429/rune)
[![Latest Unstable Version](https://poser.pugx.org/uuf6429/rune/v/unstable.svg)](https://packagist.org/packages/uuf6429/rune)
[![PHP Version Require](http://poser.pugx.org/uuf6429/rune/require/php)](https://www.php.net/supported-versions.php)
[![License](https://poser.pugx.org/uuf6429/rune/license.svg)](https://raw.githubusercontent.com/uuf6429/rune/master/LICENSE)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=uuf6429_rune&metric=coverage)](https://sonarcloud.io/summary/new_code?id=uuf6429_rune)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=uuf6429_rune&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=uuf6429_rune)

Rune - A PHP <b>Ru</b>le Engi<b>ne</b> Toolkit.

This library is an implementation of a [Business Rule Engine] (a type of Business Process Automation software).

## Table Of Contents

- [ᚱᚢᚾᛖ](#ᚱᚢᚾᛖ)
    - [Table Of Contents](#table-of-contents)
    - [Installation](#installation)
    - [Architecture](#architecture)
    - [Usage](#usage)
        - [Live Example](#live-example)
        - [Example Code](#example-code)

## Installation

The recommended and easiest way to install Rune is through [Composer]:

```shell
composer require uuf6429/rune
```

## Architecture

The library is made up of the following main parts:

- **Rule** (impl. [`Rule\RuleInterface`]) - object representing a business rule. For most use-cases, one can just
  use [`Rule\GenericRule`]. Each rule must have a unique id, descriptive name, a condition (as an expression that
  returns `true` or `false`) and the action (see below) to be triggered when the condition is met.
- **Action** (impl. [`Action\ActionInterface`]) - an object that does something when the associated rule is triggered.
  Actions in general can be reused by multiple rules.
  For example, if you're using the rule engine in product sales, an action might automatically add a fee to the bill
  when a certain condition applies (e.g. a fee for specific delivery countries, or a negative fee for a discount).
- **Context** (impl. [`Context\ContextInterface`]) - an object that provides data to the rule engine and action to work
  with. This can be thought of as a collection of all the available data for the current situation. For example, when
  the current situation is about a user buying a product, you would have data about the user, the product, offers,
  and perhaps also higher level information such as time, locality etc.
  You almost always have to implement your own context since this always depends on your (business) scenario.
- **RuleEngine** - essentially, the object that connects the others together to function.

<!-- @formatter:off -->
```mermaid
flowchart LR
    A("
<center><b>Rules</b></center>
Rule 1:
- Condition: <code>product.color == #quot;green#quot;</code>
- Action: <code>applyDiscount(10)</code>

Rule 2:
- Condition: <code>product.color == #quot;red#quot;</code>
- Action: <code>applyDiscount(20)</code>
    ") --> C

    B("
<center><b>Context</b></center><pre><code>{
    product: {
        name: #quot;Scarf#quot;,
        color: #quot;red#quot;
    }
}</code></pre>
    ") --> C

    subgraph RuleEngine ["<br><h2>Rule Engine</h2>"]
        C{"Filter Rules"} --> D(["Execute Action(s)"])
    end

    D --> E("<code>applyDiscount(20)</code>")

style A text-align: left
style B text-align: left
```
<!-- @formatter:on -->

## Usage

Various examples can be found in [uuf6429/rune-examples](https://github.com/uuf6429/rune-examples).

### Live Example

<a href="https://uuf6429.github.io/rune-examples/shop/">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://uuf6429.github.io/rune-examples/shop/screenshot-dark.png">
        <img alt="Try it out" src="https://uuf6429.github.io/rune-examples/shop/screenshot-light.png">
    </picture>
</a>

### Example Code

The following code is a very simple example of how Rune can be used. It defines one model (`Product`),
context (`ProductContext`) and uses [`CallbackAction`] to print out the rules that have been triggered.

```php
namespace MyApplication;

use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Context\ClassContext;
use uuf6429\Rune\Engine;
use uuf6429\Rune\Rule\GenericRule;
use uuf6429\Rune\Rule\RuleInterface;

// A class whose instances will be available inside the rule engine.
class Product
{
    public function __construct(
        public readonly string $name,
        public readonly string $colour,
    ) {
    }
}

// A class that represents the rule engine execution context.
// Note that public properties will be available in the rule expressions,
// in this case rules will have access to "product" as a variable (and all of product's public properties).
class ProductContext extends ClassContext
{
    public function __construct(
        public readonly Product $product
    ) {
    }
}

// Declare an action to be triggered when a rule matches against a product.
$action = new CallbackAction(
    static fn ($eval, ProductContext $context, RuleInterface $rule) => printf(
        "Rule %s triggered for %s %s\n",
        $rule->getId(),
        ucwords($context->product->colour),
        $context->product->name
    )
);

// Declare some sample rules.
$rules = [
    new GenericRule(1, 'Red Products', 'product.colour == "red"', $action),
    new GenericRule(2, 'Red Socks', 'product.colour == "red" and product.name matches "/socks/i"', $action),
    new GenericRule(3, 'Green Socks', 'product.colour == "green" and product.name matches "/socks/i"', $action),
    new GenericRule(4, 'Socks', 'product.name matches "/socks/" > 0', $action),
];

// Declare available products (to run rules against).
$products = [
    new Product('Bricks', 'red'),
    new Product('Soft Socks', 'green'),
    new Product('Sporty Socks', 'yellow'),
];

// Create rule engine.
$engine = new Engine();

// Run rules for each product. Note that each product should exist in a separate context.
foreach ($products as $product) {
    $engine->execute(new ProductContext($product), $rules);
}
```

[Business Rule Engine]: https://en.wikipedia.org/wiki/Business_rules_engine

[Composer]: https://getcomposer.org/

[`Rule\RuleInterface`]: src/Rule/RuleInterface.php

[`Rule\GenericRule`]: src/Rule/GenericRule.php

[`Action\ActionInterface`]: src/Action/ActionInterface.php

[`Context\ContextInterface`]: src/Context/ContextInterface.php

[`CallbackAction`]: src/Action/CallbackAction.php

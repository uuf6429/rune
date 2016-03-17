Introduction
============

This module is an implementation of a Business Rule Engine (a type of Business Process Automation software) for SellerCenter.

Usage
=====

The module is made up of these parts:

- Action - an object that performs an action when a rule condition is true. Actions in general can be reused.
- Context - an object that provides data to the rule engine and action to work with.
  You almost always have to implement your own context since this always depends on your scenario.
- Rule(s) - a list of rules, objects containing a string expression (for the rule engine) and data (for the action).
  For complicated scenarios, you might want to extend the rule (by extending Rule\AbstractRule), otherwise Rule\Generic should be enough.
- RuleEngine - the object that connects the others together to function.

Requirements
============

The RuleEngine has a hard dependency on Symfony ExpressionLAnguage component.
At this point, injecting this component (via DI) doesn't seem to provide any advantages.

Example
=======

```php
// load rule data from db and convert each into a know rule model
$ruleModel = new SellerCenter_Model_TransactionRule();
$rules = array_map(
    function($row){
      return new TransactionGeneratorRule($row);
    },
    $ruleModel->getActive()
);

// load target order from db
$orderModel = new SellerCenter_Model_Order();
$order = $orderModel->getByOrderNr('53252');

// load order items associated to order (note: we can be specific and only process particular order items)
$orderItemModel = new SellerCenter_Model_OrderItem();
$orderItems = $orderItemModel->getByOrderID($order['id_order']);

// the action to be executed for each matching rule
$action = new TransactionGeneratorAction();

$contexts = [];
foreach ($orderItems as $orderItem) {
    // the context provides information for the rule engine to function properly
    $context = new TransactionGeneratorContext($action);
    $context->load($order, $orderItem);
    $contexts[] = $context;
}

// run context action for all matching rules, for each context
$engine = new Engine($contexts, $rules);
$engine->execute();

// Note: Depending on use-case, you might want to use $action again here, for example to retrieve generated transactions. A specialized action can also persist generated transactions to db.
```
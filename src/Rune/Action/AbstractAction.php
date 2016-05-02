<?php

namespace uuf6429\Rune\Action;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\Evaluator;

abstract class AbstractAction
{
    /**
     * Do something with regards to triggering rule using data from context,
     * optionally using evaluator for further processing.
     *
     * @param Evaluator        $eval
     * @param ContextInterface $context
     * @param RuleInterface    $rule
     */
    abstract public function execute(Evaluator $eval, ContextInterface $context, RuleInterface $rule);
}

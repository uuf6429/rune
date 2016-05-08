<?php

namespace uuf6429\Rune\Action;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

interface ActionInterface
{
    /**
     * Do something with regards to triggering rule using data from context,
     * optionally using evaluator for further processing.
     *
     * @param EvaluatorInterface $eval
     * @param ContextInterface   $context
     * @param RuleInterface      $rule
     */
    public function execute($eval, $context, $rule);
}

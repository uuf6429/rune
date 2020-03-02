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
     * @param \uuf6429\Rune\Util\EvaluatorInterface  $eval
     * @param \uuf6429\Rune\Context\ContextInterface $context
     * @param \uuf6429\Rune\Rule\RuleInterface       $rule
     */
    public function execute(
        EvaluatorInterface $eval,
        ContextInterface $context,
        RuleInterface $rule
    ): void;
}

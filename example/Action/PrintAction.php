<?php

namespace uuf6429\Rune\example\Action;

use Stringable;
use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

class PrintAction implements ActionInterface
{
    public function execute(EvaluatorInterface $eval, ContextInterface $context, RuleInterface $rule): void
    {
        printf(
            'Rule %s (%s) triggered for %s.' . PHP_EOL,
            $rule->getId(),
            $rule->getName(),
            $context instanceof Stringable ? (string)$context : var_export($context, true)
        );
    }
}

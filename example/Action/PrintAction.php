<?php

namespace uuf6429\Rune\example\Action;

use uuf6429\Rune\Action\AbstractAction;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\Evaluator;

class PrintAction extends AbstractAction
{
    public function execute(Evaluator $eval, ContextInterface $context, RuleInterface $rule)
    {
        printf(
            'Rule %s (%s) triggered for %s.'.PHP_EOL,
            $rule->getID(),
            $rule->getName(),
            (string) $context
        );
    }
}

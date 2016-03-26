<?php

namespace uuf6429\Rune\example\Action;

use uuf6429\Rune\Action\AbstractAction;
use uuf6429\Rune\Context\AbstractContext;
use uuf6429\Rune\Rule\AbstractRule;
use uuf6429\Rune\Util\Evaluator;

class PrintAction extends AbstractAction
{
    public function execute(Evaluator $eval, AbstractContext $context, AbstractRule $rule)
    {
        printf(
            'Rule %s (%s) triggered for %s.'.PHP_EOL,
            $rule->getID(),
            $rule->getName(),
            (string) $context
        );
    }
}

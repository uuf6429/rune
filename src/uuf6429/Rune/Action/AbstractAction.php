<?php
namespace uuf6429\Rune\Action;

use uuf6429\Rune\Context\AbstractContext;
use uuf6429\Rune\Rule\AbstractRule;
use uuf6429\Rune\Util\Evaluator;

abstract class AbstractAction
{
    /**
     * Do something with regards to triggering rule using data from context,
     * optionally using evaluator for further processing.
     * @param Evaluator $eval
     * @param AbstractContext $context
     * @param AbstractRule $rule
     */
    abstract public function execute(Evaluator $eval, AbstractContext $context, AbstractRule $rule);
}

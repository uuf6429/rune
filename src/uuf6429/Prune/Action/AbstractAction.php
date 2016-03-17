<?php
namespace uuf6429\Prune\Action;

use uuf6429\Prune\Context\AbstractContext;
use uuf6429\Prune\Rule\AbstractRule;
use uuf6429\Prune\Util\Evaluator;

abstract class AbstractAction
{
    /**
     * Do something with regards to triggering rule using data from context,
     * optionally using evaluator for further processing.
     * 
     * @param Evaluator $eval
     * @param AbstractContext $context
     * @param AbstractRule $rule
     */
    abstract public function execute(Evaluator $eval, AbstractContext $context, AbstractRule $rule);
}

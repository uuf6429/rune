<?php declare(strict_types=1);

namespace uuf6429\Rune\Action;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

interface ActionInterface
{
    /**
     * Do something with regards to triggering rule using data from context,
     * optionally using evaluator for further processing.
     */
    public function execute(EvaluatorInterface $eval, ContextInterface $context, RuleInterface $rule): void;
}

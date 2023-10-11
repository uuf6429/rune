<?php

namespace uuf6429\Rune\Engine\RuleFilterHandler;

use RuntimeException;
use uuf6429\Rune\Engine\ExceptionHandlerInterface;
use uuf6429\Rune\Engine\RuleFilterHandlerInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

abstract class BaseFilterHandler implements RuleFilterHandlerInterface
{
    protected EvaluatorInterface $evaluator;
    protected ExceptionHandlerInterface $exceptionHandler;

    public function __construct(
        EvaluatorInterface        $evaluator,
        ExceptionHandlerInterface $exceptionHandler
    ) {
        $this->evaluator = $evaluator;
        $this->exceptionHandler = $exceptionHandler;
    }

    protected function filterRule(RuleInterface $rule): bool
    {
        $cond = $rule->getCondition();
        $match = ($cond === '') ?: $this->evaluator->evaluate($rule->getCondition());

        if (!is_bool($match)) {
            throw new RuntimeException(sprintf(
                'The condition result for rule %s (%s) should be boolean, not %s.',
                $rule->getId(),
                $rule->getName(),
                gettype($match)
            ));
        }

        return $match;
    }
}

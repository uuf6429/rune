<?php

namespace uuf6429\Rune\Engine\RuleFilterHandler;

use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Exception\RuleConditionExecutionFailedException;

class FirstMatchingRule extends BaseFilterHandler
{
    public function filterRules(ContextInterface $context, iterable $rules): iterable
    {
        foreach ($rules as $rule) {
            try {
                if ($this->filterRule($rule)) {
                    yield $rule;
                    break;
                }
            } catch (Throwable $ex) {
                $this->exceptionHandler->handle(
                    new RuleConditionExecutionFailedException($context, $rule, $ex)
                );
            }
        }
    }
}

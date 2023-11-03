<?php declare(strict_types=1);

namespace uuf6429\Rune\Engine;

use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Exception\InvalidExpressionException;
use uuf6429\Rune\Exception\RuleConditionExecutionFailedException;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

class FilterAllMatchingRules implements RuleFilterInterface
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

    public function filterRules(ContextInterface $context, iterable $rules): iterable
    {
        foreach ($rules as $rule) {
            try {
                if ($this->filterRule($rule)) {
                    yield $rule;
                }
            } catch (Throwable $ex) {
                $this->exceptionHandler->handle(
                    new RuleConditionExecutionFailedException($context, $rule, $ex)
                );
            }
        }
    }

    protected function filterRule(RuleInterface $rule): bool
    {
        if (trim($condition = $rule->getCondition()) === '') {
            throw new InvalidExpressionException("Condition expression of {$rule->getId()} must not be empty.");
        }

        try {
            return $this->evaluator->evaluate($condition);
        } catch (Throwable $ex) {
            throw new InvalidExpressionException("Expression `$condition` could not be evaluated: {$ex->getMessage()}", 0, $ex);
        }
    }
}

<?php

namespace uuf6429\Rune;

use RuntimeException;
use Throwable;
use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Exception\ActionExecutionFailedException;
use uuf6429\Rune\Exception\ContextErrorException;
use uuf6429\Rune\Exception\ExceptionHandlerInterface;
use uuf6429\Rune\Exception\ExceptionPropagatorHandler;
use uuf6429\Rune\Exception\RuleConditionExecutionFailedException;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;
use uuf6429\Rune\Util\SymfonyEvaluator;

class Engine
{
    protected ExceptionHandlerInterface $exceptionHandler;

    protected EvaluatorInterface $evaluator;

    public function __construct(?ExceptionHandlerInterface $exceptionHandler = null, ?EvaluatorInterface $evaluator = null)
    {
        $this->exceptionHandler = $exceptionHandler ?: new ExceptionPropagatorHandler();
        $this->evaluator = $evaluator ?: new SymfonyEvaluator();
    }

    /**
     * @param RuleInterface[] $rules
     * @throws Throwable
     */
    public function execute(ContextInterface $context, array $rules, ActionInterface $action): int
    {
        $descriptor = $context->getContextDescriptor();
        $this->evaluator->setVariables($descriptor->getVariables());
        $this->evaluator->setFunctions($descriptor->getFunctions());

        $matchingRules = [];

        $this->findMatches($matchingRules, $context, $rules);

        // TODO implement this some time in the future
        //$this->validateMatches($matchingRules);

        $this->executeActionForRules($action, $context, $matchingRules);

        return count($matchingRules);
    }

    /**
     * @param RuleInterface[] $result
     * @param RuleInterface[] $rules
     * @throws Throwable
     */
    protected function findMatches(array &$result, ContextInterface $context, array $rules): void
    {
        foreach ($rules as $rule) {
            try {
                $this->findMatchesForContextRule($result, $rule);
            } catch (Throwable $ex) {
                $this->exceptionHandler->handle(
                    new RuleConditionExecutionFailedException($context, $rule, $ex)
                );
            }
        }
    }

    /**
     * @param RuleInterface[] $result
     * @throws ContextErrorException
     */
    protected function findMatchesForContextRule(array &$result, RuleInterface $rule): void
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

        if ($match) {
            $result[] = $rule;
        }
    }

    /**
     * @param RuleInterface[] $rules
     * @throws Throwable
     */
    protected function executeActionForRules(ActionInterface $action, ContextInterface $context, array $rules): void
    {
        foreach ($rules as $rule) {
            try {
                $action->execute($this->evaluator, $context, $rule);
            } catch (Throwable $ex) {
                $this->exceptionHandler->handle(
                    new ActionExecutionFailedException($context, $rule, $action, $ex)
                );
            }
        }
    }
}

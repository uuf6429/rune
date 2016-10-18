<?php

namespace uuf6429\Rune;

use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Exception\ContextRuleActionException;
use uuf6429\Rune\Exception\ContextRuleException;
use uuf6429\Rune\Exception\ExceptionHandlerInterface;
use uuf6429\Rune\Exception\ExceptionPropagatorHandler;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;
use uuf6429\Rune\Util\SymfonyEvaluator;

class Engine
{
    /**
     * @var ExceptionHandlerInterface
     */
    protected $exceptionHandler;

    /**
     * @var EvaluatorInterface
     */
    protected $evaluator;

    /**
     * @param ExceptionHandlerInterface|null $exceptionHandler
     * @param EvaluatorInterface|null        $evaluator
     */
    public function __construct($exceptionHandler = null, $evaluator = null)
    {
        $this->exceptionHandler = $exceptionHandler ?: new ExceptionPropagatorHandler();
        $this->evaluator = $evaluator ?: new SymfonyEvaluator();
    }

    /**
     * @param ContextInterface $context
     * @param RuleInterface[]  $rules
     * @param ActionInterface  $action
     * 
     * @return int|false
     */
    public function execute($context, $rules, $action)
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
     * @param RuleInterface[]  $result
     * @param ContextInterface $context
     * @param RuleInterface[]  $rules
     */
    protected function findMatches(&$result, $context, $rules)
    {
        foreach ($rules as $rule) {
            try {
                $this->findMatchesForContextRule($result, $rule);
            } catch (\Exception $ex) {
                $this->exceptionHandler->handle(
                    new ContextRuleException($context, $rule, null, $ex)
                );
            }
        }
    }

    /**
     * @param RuleInterface[] $result
     * @param RuleInterface   $rule
     */
    protected function findMatchesForContextRule(&$result, $rule)
    {
        $cond = $rule->getCondition();
        $match = ($cond === '') ? true : $this->evaluator->evaluate($rule->getCondition());

        if (!is_bool($match)) {
            throw new \RuntimeException(sprintf(
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
     * @param ActionInterface  $action
     * @param ContextInterface $context
     * @param RuleInterface[]  $rules
     */
    protected function executeActionForRules($action, $context, $rules)
    {
        foreach ($rules as $rule) {
            try {
                $action->execute($this->evaluator, $context, $rule);
            } catch (\Exception $ex) {
                $this->exceptionHandler->handle(
                    new ContextRuleActionException($context, $rule, $action, null, $ex)
                );
            }
        }
    }
}

<?php

namespace uuf6429\Rune;

use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Engine\ActionExecutor\DefaultActionExecutor;
use uuf6429\Rune\Engine\ActionExecutorInterface;
use uuf6429\Rune\Engine\ExceptionHandler\ThrowExceptions;
use uuf6429\Rune\Engine\ExceptionHandlerInterface;
use uuf6429\Rune\Engine\RuleFilterHandler\AllMatchingRules;
use uuf6429\Rune\Engine\RuleFilterHandlerInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;
use uuf6429\Rune\Util\SymfonyEvaluator;

class Engine
{
    protected RuleFilterHandlerInterface $ruleFilterHandler;
    protected ActionExecutorInterface $actionExecutor;
    protected ExceptionHandlerInterface $exceptionHandler;
    protected EvaluatorInterface $evaluator;

    public function __construct(
        ?RuleFilterHandlerInterface $ruleFilterHandler = null,
        ?ActionExecutorInterface    $actionExecutor = null,
        ?ExceptionHandlerInterface  $exceptionHandler = null,
        ?EvaluatorInterface         $evaluator = null
    ) {
        $this->exceptionHandler = $exceptionHandler
            ?? new ThrowExceptions();
        $this->evaluator = $evaluator
            ?? new SymfonyEvaluator();
        $this->ruleFilterHandler = $ruleFilterHandler
            ?? new AllMatchingRules($this->evaluator, $this->exceptionHandler);
        $this->actionExecutor = $actionExecutor
            ?? new DefaultActionExecutor($this->evaluator, $this->exceptionHandler);
    }

    /**
     * @param iterable<RuleInterface> $rules
     * @throws Throwable
     */
    public function execute(ContextInterface $context, iterable $rules): int
    {
        $descriptor = $context->getContextDescriptor();
        $this->evaluator->setVariables($descriptor->getVariables());
        $this->evaluator->setFunctions($descriptor->getFunctions());

        return $this->actionExecutor->execute($context, $this->ruleFilterHandler->filterRules($context, $rules));
    }
}

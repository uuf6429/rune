<?php declare(strict_types=1);

namespace uuf6429\Rune;

use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Engine\ActionExecutor;
use uuf6429\Rune\Engine\ActionExecutorInterface;
use uuf6429\Rune\Engine\ExceptionHandler\ThrowExceptions;
use uuf6429\Rune\Engine\ExceptionHandlerInterface;
use uuf6429\Rune\Engine\FilterAllMatchingRules;
use uuf6429\Rune\Engine\RuleFilterInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;
use uuf6429\Rune\Util\SymfonyEvaluator;

class Engine
{
    protected RuleFilterInterface $ruleFilterHandler;
    protected ActionExecutorInterface $actionExecutor;
    protected ExceptionHandlerInterface $exceptionHandler;
    protected EvaluatorInterface $evaluator;

    public function __construct(
        ?RuleFilterInterface       $ruleFilterHandler = null,
        ?ActionExecutorInterface   $actionExecutor = null,
        ?ExceptionHandlerInterface $exceptionHandler = null,
        ?EvaluatorInterface        $evaluator = null
    ) {
        $this->exceptionHandler = $exceptionHandler
            ?? new ThrowExceptions();
        $this->evaluator = $evaluator
            ?? new SymfonyEvaluator();
        $this->ruleFilterHandler = $ruleFilterHandler
            ?? new FilterAllMatchingRules($this->evaluator, $this->exceptionHandler);
        $this->actionExecutor = $actionExecutor
            ?? new ActionExecutor($this->evaluator, $this->exceptionHandler);
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

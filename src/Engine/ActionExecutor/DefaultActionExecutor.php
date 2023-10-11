<?php

namespace uuf6429\Rune\Engine\ActionExecutor;

use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Engine\ActionExecutorInterface;
use uuf6429\Rune\Engine\ExceptionHandlerInterface;
use uuf6429\Rune\Exception\ActionExecutionFailedException;
use uuf6429\Rune\Util\EvaluatorInterface;

class DefaultActionExecutor implements ActionExecutorInterface
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

    public function execute(ContextInterface $context, iterable $rules): int
    {
        $executed = 0;

        foreach ($rules as $rule) {
            $action = $rule->getAction();
            try {
                $action->execute($this->evaluator, $context, $rule);
                $executed++;
            } catch (Throwable $ex) {
                $this->exceptionHandler->handle(
                    new ActionExecutionFailedException($context, $rule, $action, $ex)
                );
            }
        }

        return $executed;
    }
}

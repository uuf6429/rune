<?php

namespace uuf6429\Rune\Exception;

use RuntimeException;
use Throwable;
use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

class ActionExecutionFailedException extends RuntimeException
{
    private ContextInterface $context;

    private RuleInterface $rule;

    private ActionInterface $action;

    public function __construct(ContextInterface $context, RuleInterface $rule, ActionInterface $action, Throwable $previous)
    {
        $this->context = $context;
        $this->rule = $rule;
        $this->action = $action;

        parent::__construct(
            sprintf(
                '%s encountered while executing action %s for rule %s (%s) within %s: %s',
                get_class($previous),
                get_class($this->getAction()),
                $rule->getId(),
                $rule->getName(),
                get_class($context),
                $previous->getMessage()
            ),
            0,
            $previous
        );
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getRule(): RuleInterface
    {
        return $this->rule;
    }

    public function getAction(): ActionInterface
    {
        return $this->action;
    }
}

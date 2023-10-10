<?php

namespace uuf6429\Rune\Exception;

use Throwable;
use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

class ContextRuleActionException extends ContextRuleException
{
    private ActionInterface $action;

    public function __construct(ContextInterface $context, RuleInterface $rule, ActionInterface $action, ?string $message = null, ?Throwable $previous = null)
    {
        $this->action = $action;

        if ($message === null) {
            $message = sprintf(
                '%s encountered while executing action %s for rule %s (%s) within %s%s',
                $previous ? get_class($previous) : 'Error',
                get_class($this->getAction()),
                $rule->getId(),
                $rule->getName(),
                get_class($context),
                $previous ? (': ' . $previous->getMessage()) : ''
            );
        }

        parent::__construct($context, $rule, $message, $previous);
    }

    public function getAction(): ActionInterface
    {
        return $this->action;
    }
}

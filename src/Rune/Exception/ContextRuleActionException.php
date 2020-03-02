<?php

namespace uuf6429\Rune\Exception;

use Throwable;
use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

class ContextRuleActionException extends ContextRuleException
{
    /**
     * @var ActionInterface
     */
    private $action;

    /**
     * @param ContextInterface $context
     * @param RuleInterface $rule
     * @param ActionInterface $action
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct($context, $rule, $action, $message = null, $previous = null)
    {
        $this->action = $action;

        if ($message === null) {
            $message = sprintf(
                '%s encountered while executing action %s for rule %s (%s) within %s%s',
                (is_object($previous) ? get_class($previous) : 'Error'),
                get_class($this->getAction()),
                $rule->getId(),
                $rule->getName(),
                get_class($context),
                (is_object($previous) ? (': ' . $previous->getMessage()) : '')
            );
        }

        parent::__construct($context, $rule, $message, $previous);
    }

    public function getAction(): ActionInterface
    {
        return $this->action;
    }
}

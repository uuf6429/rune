<?php

namespace uuf6429\Rune\Exception;

use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

class ContextRuleActionException extends \ContextRuleException
{
    /**
     * @var ActionInterface
     */
    private $action;

    /**
     * @param ContextInterface $context
     * @param RuleInterface    $rule
     * @param ActionInterface  $action
     * @param string|null      $message
     * @param \Exception|null  $previous
     */
    public function __construct($context, $rule, $action, $message = null, $previous = null)
    {
        if ($message === null) {
            $message = sprintf(
                '%s encountered while executing action %s for rule %s (%s) within %s%s',
                (is_object($previous) ? get_class($previous) : 'Error'),
                get_class($action),
                $rule->getID(),
                $rule->getName(),
                get_class($context),
                (is_object($previous) ? (': ' . $previous->getMessage()) : '')
            );
        }

        $this->action = $action;

        parent::__construct($context, $rule, $message, 0, $previous);
    }

    /**
     * @return ActionInterface
     */
    public function getAction()
    {
        return $this->action;
    }
}

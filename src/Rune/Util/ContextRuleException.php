<?php

namespace uuf6429\Rune\Util;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

class ContextRuleException extends \RuntimeException
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var RuleInterface
     */
    private $rule;

    /**
     * @param ContextInterface $context
     * @param RuleInterface    $rule
     * @param string|null      $message
     * @param int              $code
     * @param \Exception|null  $previous
     */
    public function __construct($context, $rule, $message = null, $previous = null)
    {
        if ($message === null) {
            $message = sprintf(
                '%s encountered while processing rule %s (%s) within %s%s',
                (is_object($previous) ? get_class($previous) : 'Error'),
                $rule->getID(),
                $rule->getName(),
                get_class($context),
                (is_object($previous) ? (': ' . $previous->getMessage()) : '')
            );
        }

        $this->context = $context;
        $this->rule = $rule;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return RuleInterface
     */
    public function getRule()
    {
        return $this->rule;
    }
}

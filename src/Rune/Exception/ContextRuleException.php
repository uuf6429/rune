<?php

namespace uuf6429\Rune\Exception;

use RuntimeException;
use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

class ContextRuleException extends RuntimeException
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
     * @param Throwable|null  $previous
     */
    public function __construct($context, $rule, $message = null, $previous = null)
    {
        $this->context = $context;
        $this->rule = $rule;

        if ($message === null) {
            $message = sprintf(
                '%s encountered while processing rule %s (%s) within %s%s',
                (is_object($previous) ? get_class($previous) : 'Error'),
                $this->getRule()->getId(),
                $this->getRule()->getName(),
                get_class($this->getContext()),
                (is_object($previous) ? (': ' . $previous->getMessage()) : '')
            );
        }

        parent::__construct($message, 0, $previous);
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getRule(): RuleInterface
    {
        return $this->rule;
    }
}

<?php

namespace uuf6429\Rune\Exception;

use RuntimeException;
use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

class ContextRuleException extends RuntimeException
{
    private ContextInterface $context;

    private RuleInterface $rule;

    public function __construct(ContextInterface $context, RuleInterface $rule, ?string $message = null, ?Throwable $previous = null)
    {
        $this->context = $context;
        $this->rule = $rule;

        if ($message === null) {
            $message = sprintf(
                '%s encountered while processing rule %s (%s) within %s%s',
                $previous ? get_class($previous) : 'Error',
                $this->getRule()->getId(),
                $this->getRule()->getName(),
                get_class($this->getContext()),
                $previous ? (': ' . $previous->getMessage()) : ''
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

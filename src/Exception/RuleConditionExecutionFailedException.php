<?php declare(strict_types=1);

namespace uuf6429\Rune\Exception;

use RuntimeException;
use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

class RuleConditionExecutionFailedException extends RuntimeException
{
    private ContextInterface $context;

    private RuleInterface $rule;

    public function __construct(ContextInterface $context, RuleInterface $rule, Throwable $previous)
    {
        $this->context = $context;
        $this->rule = $rule;

        parent::__construct(
            sprintf(
                '%s encountered while processing rule %s (%s) within %s: %s',
                get_class($previous),
                $this->getRule()->getId(),
                $this->getRule()->getName(),
                get_class($this->getContext()),
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
}

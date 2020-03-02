<?php

namespace uuf6429\Rune\Exception;

use RuntimeException;
use Throwable;
use uuf6429\Rune\Rule\RuleInterface;

class InvalidRuleConditionException extends RuntimeException
{
    /**
     * @param RuleInterface  $rule
     * @param mixed          $result
     * @param Throwable|null $previous
     */
    public function __construct(RuleInterface $rule, $result, $previous = null)
    {
        parent::__construct(
            sprintf(
                'The condition result for rule %s (%s) should be boolean, not %s.',
                $rule->getId(),
                $rule->getName(),
                gettype($result)
            ),
            0,
            $previous
        );
    }
}

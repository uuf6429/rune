<?php

namespace uuf6429\Rune\Rule;

use uuf6429\Rune\Action\ActionInterface;

interface RuleInterface
{
    /**
     * Unique ID for the rule.
     */
    public function getId(): string;

    /**
     * Human-readable name for the rule.
     */
    public function getName(): string;

    /**
     * Rule condition expression.
     */
    public function getCondition(): string;

    /**
     * Action to execute when this rule matches.
     */
    public function getAction(): ActionInterface;
}

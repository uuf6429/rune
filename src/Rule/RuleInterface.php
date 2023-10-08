<?php

namespace uuf6429\Rune\Rule;

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
}

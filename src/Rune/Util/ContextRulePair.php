<?php

namespace uuf6429\Rune\Util;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

class ContextRulePair
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var RuleInterface
     */
    protected $rule;

    /**
     * @param ContextInterface $context
     * @param RuleInterface    $rule
     */
    public function __construct(ContextInterface $context, RuleInterface $rule)
    {
        $this->context = $context;
        $this->rule = $rule;
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
